<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AttributeBulkImporter
{
    protected $ci;
    protected $db;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->database();
        $this->db = $this->ci->db;
    }

    /**
     * Parse uploaded CSV or XLSX file into array of rows
     */
    public function parseFile($filePath, $originalName = '')
    {
        $ext = strtolower(pathinfo($originalName ?: $filePath, PATHINFO_EXTENSION));
        if ($ext === 'xlsx' || $ext === 'xls') {
            return $this->parseXlsx($filePath);
        }
        return $this->parseCsv($filePath);
    }

    /**
     * Parse CSV file with auto-delimiter detection and BOM removal
     */
    public function parseCsv($filePath)
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return ['status' => false, 'message' => 'Uploaded file could not be read.'];
        }

        $content = file_get_contents($filePath);
        if ($content === false || strlen(trim($content)) === 0) {
            return ['status' => false, 'message' => 'The uploaded file is empty.'];
        }

        // Remove UTF-8 BOM if present
        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            $content = substr($content, 3);
        }

        // Detect delimiter
        $firstLine = strtok($content, "\r\n");
        $delimiters = [',', ';', "\t", '|'];
        $bestDelimiter = ',';
        $maxCount = 0;
        foreach ($delimiters as $delim) {
            $count = substr_count($firstLine, $delim);
            if ($count > $maxCount) {
                $maxCount = $count;
                $bestDelimiter = $delim;
            }
        }

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $content);
        rewind($stream);

        $rawRows = [];
        $rowNum = 0;
        while (($data = fgetcsv($stream, 0, $bestDelimiter)) !== false) {
            $rowNum++;
            $rawRows[] = ['row_num' => $rowNum, 'data' => $data];
        }
        fclose($stream);

        return $this->processRawRows($rawRows);
    }

    /**
     * Parse XLSX file using built-in ZipArchive and XML parser
     */
    public function parseXlsx($filePath)
    {
        if (!class_exists('ZipArchive')) {
            return $this->parseCsv($filePath);
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return ['status' => false, 'message' => 'Could not open XLSX file.'];
        }

        // Load shared strings
        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml !== false) {
            $xml = @simplexml_load_string($sharedStringsXml);
            if ($xml) {
                foreach ($xml->si as $val) {
                    if (isset($val->t)) {
                        $sharedStrings[] = (string)$val->t;
                    } elseif (isset($val->r)) {
                        $text = '';
                        foreach ($val->r as $r) {
                            $text .= (string)$r->t;
                        }
                        $sharedStrings[] = $text;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            return ['status' => false, 'message' => 'Could not find worksheet in XLSX file.'];
        }

        $xml = @simplexml_load_string($sheetXml);
        if (!$xml || !isset($xml->sheetData)) {
            return ['status' => false, 'message' => 'Invalid XLSX sheet format.'];
        }

        $rawRows = [];
        $rowNum = 0;
        foreach ($xml->sheetData->row as $r) {
            $rowNum++;
            $rowArr = [];
            foreach ($r->c as $c) {
                $attr = $c->attributes();
                $cellType = isset($attr['t']) ? (string)$attr['t'] : '';
                $cellVal = isset($c->v) ? (string)$c->v : '';

                if ($cellType === 's' && isset($sharedStrings[(int)$cellVal])) {
                    $cellVal = $sharedStrings[(int)$cellVal];
                } elseif ($cellType === 'inlineStr' && isset($c->is->t)) {
                    $cellVal = (string)$c->is->t;
                }
                $rowArr[] = trim($cellVal);
            }
            $rawRows[] = ['row_num' => $rowNum, 'data' => $rowArr];
        }

        return $this->processRawRows($rawRows);
    }

    /**
     * Process raw rows into standardized key-value rows
     */
    protected function processRawRows($rawRows)
    {
        if (empty($rawRows)) {
            return ['status' => false, 'message' => 'No data found in uploaded file.'];
        }

        $header = null;
        $extractedRows = [];

        foreach ($rawRows as $item) {
            $rowNum = $item['row_num'];
            $data = $item['data'];

            // Skip empty rows
            $nonEmpty = array_filter($data, function ($v) {
                return trim($v) !== '';
            });
            if (empty($nonEmpty)) {
                continue;
            }

            // Skip section divider rows if present
            $firstCell = trim($data[0] ?? '');
            if (stripos($firstCell, 'Category:') === 0 || stripos($firstCell, 'Category :') === 0) {
                continue;
            }

            // Identify header row
            if ($header === null) {
                $firstCellNorm = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $firstCell));
                if (in_array($firstCellNorm, ['attributenameenglish', 'attributename', 'name', 'attributecode', 'variantnameenglish', 'variantname', 'code'], true)) {
                    $header = array_map([$this, 'normalizeHeaderKey'], $data);
                    continue;
                }
            }

            if ($header === null) {
                continue;
            }

            $rowData = [];
            foreach ($header as $idx => $key) {
                if ($key !== '') {
                    $rowData[$key] = isset($data[$idx]) ? trim($data[$idx]) : '';
                }
            }

            $rowData['_row_number'] = $rowNum;
            $extractedRows[] = $rowData;
        }

        if (empty($extractedRows)) {
            return ['status' => false, 'message' => 'No attribute/variant records found in the uploaded file.'];
        }

        return ['status' => true, 'rows' => $extractedRows];
    }

    /**
     * Map header names to standardized field keys
     */
    public function normalizeHeaderKey($header)
    {
        $clean = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $header)));

        $map = [
            'attributenameenglish'   => 'attr_name',
            'attributename'          => 'attr_name',
            'attrname'               => 'attr_name',
            'name'                   => 'attr_name',
            'variantnameenglish'     => 'attr_name',
            'variantname'            => 'attr_name',

            'attributenamefrench'    => 'lang_attr_name',
            'attributenamefr'        => 'lang_attr_name',
            'frenchname'             => 'lang_attr_name',
            'frenchtitle'            => 'lang_attr_name',
            'langattrname'           => 'lang_attr_name',
            'variantnamefrench'      => 'lang_attr_name',
            'french'                 => 'lang_attr_name',

            'attributecode'          => 'attr_code',
            'attrcode'               => 'attr_code',
            'code'                   => 'attr_code',
            'variantcode'            => 'attr_code',

            'attributetype'          => 'attr_type',
            'attrtype'               => 'attr_type',
            'type'                   => 'attr_type',

            'attributeproperties'    => 'attr_properties',
            'attributeproperty'      => 'attr_properties',
            'properties'             => 'attr_properties',
            'property'               => 'attr_properties',
            'inputtype'              => 'attr_properties',

            'attributevaluesenglish' => 'attr_values_en',
            'attributevalues'        => 'attr_values_en',
            'valuesenglish'          => 'attr_values_en',
            'values'                 => 'attr_values_en',
            'options'                => 'attr_values_en',
            'variantvalue'           => 'attr_values_en',
            'variantvalues'          => 'attr_values_en',

            'attributesvaluesfrench' => 'attr_values_fr',
            'attributevaluesfrench'  => 'attr_values_fr',
            'valuesfrench'           => 'attr_values_fr',
            'frenchvalues'           => 'attr_values_fr',
            'frenchoptions'          => 'attr_values_fr',

            'status'                 => 'status',
            'active'                 => 'status',

            'attributedescription'   => 'attr_description',
            'description'            => 'attr_description',
            'variantdescription'     => 'attr_description',

            'defaultvalue'           => 'default_value'
        ];

        return isset($map[$clean]) ? $map[$clean] : $clean;
    }

    /**
     * Resolve Attribute Type integer: 1 = Attribute, 2 = Variant
     */
    public function resolveAttributeType($type)
    {
        $clean = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', (string)$type)));
        if ($clean === '2' || $clean === 'variant' || $clean === 'var' || $clean === 'variants') {
            return 2;
        }
        return 1; // Default to Attribute (1)
    }

    /**
     * Map Property Name / Text to integer
     * 1: Text, 2: Textarea, 3: Date, 4: Yes/No, 5: Dropdown, 6: Multiselect
     */
    public function resolveAttributeProperty($prop)
    {
        $clean = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', (string)$prop)));

        switch ($clean) {
            case '1':
            case 'text':
            case 'textbox':
            case 'input':
                return 1;
            case '2':
            case 'textarea':
            case 'multiline':
                return 2;
            case '3':
            case 'date':
            case 'datetime':
                return 3;
            case '4':
            case 'yesno':
            case 'boolean':
            case 'checkbox':
                return 4;
            case '5':
            case 'dropdown':
            case 'select':
            case 'singleselect':
                return 5;
            case '6':
            case 'multiselect':
            case 'commaseparatedvalues':
            case 'commaseparated':
            case 'multiple':
            case 'tags':
                return 6;
            default:
                return 1;
        }
    }

    /**
     * Validate all attribute/variant rows without database changes
     */
    public function validateRecords($rows)
    {
        // 1. Load All Existing Attributes/Variants from eav_attributes
        $existingAttributes = $this->db->select('id, attr_code, attr_name, lang_attr_name, attr_type, attr_properties, status')
            ->from('eav_attributes')
            ->get()
            ->result_array();

        $attrByCode = [];
        foreach ($existingAttributes as $a) {
            $attrByCode[strtolower(trim($a['attr_code']))] = $a;
        }

        // 2. Load Existing Options from eav_attributes_options
        $existingOptions = $this->db->select('id, attr_id, attr_options_name, lang_attr_options_name, position')
            ->from('eav_attributes_options')
            ->get()
            ->result_array();

        $optionsByAttrId = [];
        foreach ($existingOptions as $opt) {
            $attrId = (int)$opt['attr_id'];
            if (!isset($optionsByAttrId[$attrId])) {
                $optionsByAttrId[$attrId] = [];
            }
            $optionsByAttrId[$attrId][strtolower(trim($opt['attr_options_name']))] = $opt;
        }

        // Counters
        $totalRows = count($rows);
        $newAttributesCount = 0;
        $newVariantsCount = 0;
        $existingAttributesCount = 0;
        $existingVariantsCount = 0;
        $duplicateRowsInUploadCount = 0;

        $newOptionsCount = 0;
        $existingOptionsCount = 0;

        $invalidRowsCount = 0;
        $validRowsCount = 0;

        $seenCodesInFile = [];
        $batchOptionsByAttrCode = [];
        $validatedRows = [];

        foreach ($rows as $index => $row) {
            $rowNum = $row['_row_number'] ?? ($index + 2);
            $errors = [];
            $warnings = [];

            $attrName = trim($row['attr_name'] ?? '');
            $langAttrName = trim($row['lang_attr_name'] ?? '');
            $attrCode = strtolower(trim($row['attr_code'] ?? ''));
            $rawType = trim($row['attr_type'] ?? 'Attribute');
            $rawProperty = trim($row['attr_properties'] ?? 'Text');
            $rawValuesEn = trim($row['attr_values_en'] ?? '');
            $rawValuesFr = trim($row['attr_values_fr'] ?? '');
            $rawStatus = trim($row['status'] ?? 'Active');
            $attrDesc = trim($row['attr_description'] ?? '');
            $defaultValue = trim($row['default_value'] ?? '');

            $attrType = $this->resolveAttributeType($rawType);
            $propertyInt = $this->resolveAttributeProperty($rawProperty);

            // Auto-generate code if empty
            if ($attrCode === '' && $attrName !== '') {
                $attrCode = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace([' ', '-'], '_', $attrName)));
            }

            if ($attrCode === '') {
                $errors[] = 'Attribute Code is required.';
            }

            if ($attrName === '') {
                $errors[] = 'Attribute Name is required.';
            }

            // Status normalization
            $status = 1;
            if (in_array(strtolower($rawStatus), ['0', 'inactive', 'disable', 'disabled', 'no', 'false'], true)) {
                $status = 0;
            }

            // 1. STRICT NO-DUPLICATE GLOBAL CHECK
            $isExistingMaster = false;
            $isDuplicateInFile = false;
            $existingMasterRecord = null;
            $action = 'new'; // 'new' | 'skip_existing' | 'skip_duplicate_file'

            if (!empty($attrCode)) {
                if (isset($attrByCode[$attrCode])) {
                    $isExistingMaster = true;
                    $existingMasterRecord = $attrByCode[$attrCode];
                    $action = 'skip_existing';
                    if ((int)$existingMasterRecord['attr_type'] === 2) {
                        $existingVariantsCount++;
                    } else {
                        $existingAttributesCount++;
                    }
                } elseif (isset($seenCodesInFile[$attrCode])) {
                    $isDuplicateInFile = true;
                    $action = 'skip_duplicate_file';
                    $duplicateRowsInUploadCount++;
                } else {
                    $action = 'new';
                    $seenCodesInFile[$attrCode] = $rowNum;
                    if ($attrType === 2) {
                        $newVariantsCount++;
                    } else {
                        $newAttributesCount++;
                    }
                }
            }

            // 2. Options Resolution for Dropdown (5) and Multiselect (6)
            $optionsList = [];
            $isOptionApplicable = in_array($propertyInt, [5, 6], true);

            if ($isOptionApplicable && !empty($rawValuesEn)) {
                $enOptions = array_map('trim', explode(',', $rawValuesEn));
                $frOptions = !empty($rawValuesFr) ? array_map('trim', explode(',', $rawValuesFr)) : [];

                $existingOptsForAttr = [];
                if ($isExistingMaster && $existingMasterRecord) {
                    $existingOptsForAttr = $optionsByAttrId[(int)$existingMasterRecord['id']] ?? [];
                }
                $batchOpts = $batchOptionsByAttrCode[$attrCode] ?? [];

                foreach ($enOptions as $posIdx => $optEn) {
                    if ($optEn === '') continue;
                    $optFr = $frOptions[$posIdx] ?? $optEn;
                    $lowerOpt = strtolower($optEn);

                    $optExists = isset($existingOptsForAttr[$lowerOpt]) || isset($batchOpts[$lowerOpt]);

                    if ($optExists) {
                        $existingOptionsCount++;
                        $optionsList[] = [
                            'name'      => $optEn,
                            'lang_name' => $optFr,
                            'position'  => $posIdx + 1,
                            'action'    => 'skip_existing',
                            'label'     => 'Option Exists - Skipped'
                        ];
                    } else {
                        $newOptionsCount++;
                        $batchOptionsByAttrCode[$attrCode][$lowerOpt] = true;
                        $optionsList[] = [
                            'name'      => $optEn,
                            'lang_name' => $optFr,
                            'position'  => $posIdx + 1,
                            'action'    => 'new',
                            'label'     => 'New Option'
                        ];
                    }
                }
            } elseif (!$isOptionApplicable && !empty($rawValuesEn)) {
                // For Text, Textarea, Date, Yes/No, values act as default_value if not explicitly set
                if (empty($defaultValue)) {
                    $defaultValue = $rawValuesEn;
                }
            }

            // Row result tag
            $resultTag = '';
            if (!empty($errors)) {
                $resultTag = 'ERROR';
                $invalidRowsCount++;
            } else {
                $validRowsCount++;
                if ($action === 'new') {
                    $resultTag = $attrType === 2 ? 'NEW VARIANT' : 'NEW ATTRIBUTE';
                } elseif ($action === 'skip_existing') {
                    $resultTag = 'ALREADY EXISTS - SKIPPED';
                } elseif ($action === 'skip_duplicate_file') {
                    $resultTag = 'DUPLICATE IN UPLOAD - SKIPPED';
                }
            }

            $validatedRows[] = [
                'row_number'          => $rowNum,
                'attr_name'           => $attrName,
                'lang_attr_name'      => $langAttrName,
                'attr_code'           => $attrCode,
                'attr_type'           => $attrType,
                'type_label'          => $attrType === 2 ? 'Variant' : 'Attribute',
                'attr_properties'     => $propertyInt,
                'property_label'      => $this->getPropertyLabel($propertyInt),
                'default_value'       => $defaultValue,
                'attr_values_en'      => $rawValuesEn,
                'attr_values_fr'      => $rawValuesFr,
                'status'              => $status,
                'attr_description'    => $attrDesc,
                'action'              => $action,
                'existing_attr_id'    => $existingMasterRecord ? (int)$existingMasterRecord['id'] : null,
                'options'             => $optionsList,
                'result_tag'          => $resultTag,
                'is_valid'            => empty($errors),
                'errors'              => $errors,
                'warnings'            => $warnings
            ];
        }

        return [
            'status'   => true,
            'summary'  => [
                'total_rows'                   => $totalRows,
                'new_attributes'               => $newAttributesCount,
                'new_variants'                 => $newVariantsCount,
                'existing_attributes'          => $existingAttributesCount,
                'existing_variants'            => $existingVariantsCount,
                'duplicate_rows_in_upload'     => $duplicateRowsInUploadCount,
                'new_options'                  => $newOptionsCount,
                'existing_options'             => $existingOptionsCount,
                'invalid_rows'                 => $invalidRowsCount,
                'valid_rows'                   => $validRowsCount
            ],
            'rows'     => $validatedRows
        ];
    }

    /**
     * Execute Transaction-based Database Import
     */
    public function importRecords($validatedRows, $fileName = 'attribute_import.csv', $adminId = 0, $ip = '')
    {
        $this->db->trans_begin();

        $newAttrsCreated = 0;
        $newVariantsCreated = 0;
        $existingAttrsSkipped = 0;
        $existingVariantsSkipped = 0;
        $duplicateRowsSkipped = 0;
        $newOptionsCreated = 0;
        $existingOptionsSkipped = 0;
        $failedCount = 0;
        $errorLog = [];

        $now = time();

        // 1. Preload Attribute ID Cache
        $attributeIdMap = [];
        $existingAttrRows = $this->db->select('id, attr_code, attr_type')->from('eav_attributes')->get()->result_array();
        foreach ($existingAttrRows as $ea) {
            $attributeIdMap[strtolower(trim($ea['attr_code']))] = [
                'id'        => (int)$ea['id'],
                'attr_type' => (int)$ea['attr_type']
            ];
        }

        // 2. Preload Existing Options Cache
        $existingOptionRows = $this->db->select('id, attr_id, attr_options_name')->from('eav_attributes_options')->get()->result_array();
        $optionCache = [];
        foreach ($existingOptionRows as $eo) {
            $aid = (int)$eo['attr_id'];
            if (!isset($optionCache[$aid])) {
                $optionCache[$aid] = [];
            }
            $optionCache[$aid][strtolower(trim($eo['attr_options_name']))] = (int)$eo['id'];
        }

        foreach ($validatedRows as $row) {
            if (!$row['is_valid']) {
                $failedCount++;
                $errorLog[] = [
                    'row'       => $row['row_number'],
                    'attr_code' => $row['attr_code'] ?? '-',
                    'attr_name' => $row['attr_name'] ?? '-',
                    'type'      => 'Validation Error',
                    'message'   => implode('; ', $row['errors'])
                ];
                continue;
            }

            $attrCode = strtolower($row['attr_code']);
            $attrId = null;

            // 1. Process Master Record
            if ($row['action'] === 'new' && !isset($attributeIdMap[$attrCode])) {
                $insertData = [
                    'attr_code'        => $attrCode,
                    'attr_name'        => $row['attr_name'],
                    'lang_attr_name'   => $row['lang_attr_name'],
                    'attr_type'        => (int)$row['attr_type'],
                    'attr_properties'  => (int)$row['attr_properties'],
                    'default_value'    => $row['default_value'] ?? '',
                    'attr_description' => $row['attr_description'] ?? '',
                    'status'           => (int)$row['status'],
                    'created_by'       => $adminId,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                    'ip'               => $ip
                ];

                $ok = $this->db->insert('eav_attributes', $insertData);
                if ($ok) {
                    $attrId = (int)$this->db->insert_id();
                    $attributeIdMap[$attrCode] = [
                        'id'        => $attrId,
                        'attr_type' => (int)$row['attr_type']
                    ];
                    if ((int)$row['attr_type'] === 2) {
                        $newVariantsCreated++;
                    } else {
                        $newAttrsCreated++;
                    }
                } else {
                    $failedCount++;
                    $errorLog[] = [
                        'row'       => $row['row_number'],
                        'attr_code' => $attrCode,
                        'attr_name' => $row['attr_name'],
                        'type'      => 'Database Insert Error',
                        'message'   => 'Failed to insert into eav_attributes.'
                    ];
                    continue;
                }
            } elseif (isset($attributeIdMap[$attrCode])) {
                // Strict: Existing record - DO NOT update, retrieve existing ID
                $attrId = $attributeIdMap[$attrCode]['id'];
                if ($attributeIdMap[$attrCode]['attr_type'] === 2) {
                    $existingVariantsSkipped++;
                } else {
                    $existingAttrsSkipped++;
                }
            } elseif ($row['action'] === 'skip_duplicate_file') {
                $duplicateRowsSkipped++;
                $attrId = isset($attributeIdMap[$attrCode]) ? $attributeIdMap[$attrCode]['id'] : null;
            }

            if (!$attrId) {
                continue;
            }

            // 2. Process Attribute Options (Dropdown / Multiselect)
            if (!empty($row['options']) && in_array((int)$row['attr_properties'], [5, 6], true)) {
                if (!isset($optionCache[$attrId])) {
                    $optionCache[$attrId] = [];
                }

                foreach ($row['options'] as $opt) {
                    $optName = trim($opt['name']);
                    $optLangName = trim($opt['lang_name'] ?? $optName);
                    $lowerOptName = strtolower($optName);

                    if (isset($optionCache[$attrId][$lowerOptName])) {
                        $existingOptionsSkipped++;
                        continue;
                    }

                    $optionInsert = [
                        'attr_id'                 => $attrId,
                        'attr_options_name'       => $optName,
                        'lang_attr_options_name'  => $optLangName,
                        'position'                => (int)($opt['position'] ?? 1),
                        'status'                  => 1,
                        'created_by'              => $adminId,
                        'created_at'              => $now,
                        'updated_at'              => $now,
                        'ip'                      => $ip
                    ];

                    $optOk = $this->db->insert('eav_attributes_options', $optionInsert);
                    if ($optOk) {
                        $newOptId = (int)$this->db->insert_id();
                        $optionCache[$attrId][$lowerOptName] = $newOptId;
                        $newOptionsCreated++;
                    } else {
                        $errorLog[] = [
                            'row'       => $row['row_number'],
                            'attr_code' => $attrCode,
                            'attr_name' => $row['attr_name'],
                            'type'      => 'Option Insert Error',
                            'message'   => "Failed to insert option: {$optName}"
                        ];
                    }
                }
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return [
                'status'  => false,
                'message' => 'Database transaction failed during bulk attribute import. All changes have been rolled back.'
            ];
        }

        $this->db->trans_commit();

        $overallStatus = 'Completed';
        if ($failedCount > 0 && $newAttrsCreated === 0 && $newVariantsCreated === 0 && $newOptionsCreated === 0) {
            $overallStatus = 'Failed';
        } elseif ($failedCount > 0) {
            $overallStatus = 'Partial';
        }

        // Log into history table
        $historyData = [
            'file_name'           => $fileName,
            'total_records'       => count($validatedRows),
            'new_attributes'      => $newAttrsCreated + $newVariantsCreated,
            'existing_attributes' => $existingAttrsSkipped + $existingVariantsSkipped,
            'new_options'         => $newOptionsCreated,
            'existing_options'    => $existingOptionsSkipped,
            'new_mappings'        => 0,
            'existing_mappings'   => 0,
            'failed_records'      => $failedCount,
            'status'              => $overallStatus,
            'error_log'           => !empty($errorLog) ? json_encode($errorLog) : null,
            'created_by'          => $adminId,
            'created_at'          => $now,
            'ip'                  => $ip
        ];

        $this->db->insert('attribute_bulk_upload_history', $historyData);
        $historyId = $this->db->insert_id();

        return [
            'status'     => true,
            'history_id' => $historyId,
            'summary'    => [
                'total_rows'                   => count($validatedRows),
                'new_attributes_created'       => $newAttrsCreated,
                'new_variants_created'         => $newVariantsCreated,
                'existing_attributes_skipped'  => $existingAttrsSkipped,
                'existing_variants_skipped'    => $existingVariantsSkipped,
                'duplicate_rows_skipped'       => $duplicateRowsSkipped,
                'new_options_created'          => $newOptionsCreated,
                'existing_options_skipped'     => $existingOptionsSkipped,
                'failed_rows'                  => $failedCount,
                'status'                       => $overallStatus
            ],
            'errors'     => $errorLog
        ];
    }

    /**
     * Get human-readable property label
     */
    public function getPropertyLabel($intVal)
    {
        $map = [
            1 => 'Text',
            2 => 'Textarea',
            3 => 'Date',
            4 => 'Yes/No',
            5 => 'Dropdown',
            6 => 'Multiselect'
        ];
        return $map[$intVal] ?? 'Text';
    }

    /**
     * Generate Sample CSV template
     */
    public function generateSampleCsv()
    {
        $headers = [
            'Attribute Name (English)',
            'Attribute Name (French)',
            'Attribute Code',
            'Attribute Type',
            'Attribute Properties',
            'Attribute Values (English)',
            'Attribute Values (French)',
            'Status',
            'Attribute Description',
            'Default Value'
        ];

        $sampleData = [
            [
                'Brand / Publisher',
                'Marque / éditeur',
                'brand_publisher',
                'Attribute',
                'Text',
                '',
                '',
                'Active',
                'Specify the brand, publisher or manufacturer.',
                'Enter publisher or brand'
            ],
            [
                'Platform Compatibility',
                'Compatibilité de plateforme',
                'platform_compatibility',
                'Attribute',
                'Multiselect',
                'Windows, macOS, Android, iOS, Web, Other',
                'Windows, macOS, Android, iOS, Web, Autre',
                'Active',
                'Specify supported platforms or operating systems.',
                ''
            ],
            [
                'Delivery Format',
                'Mode de livraison',
                'delivery_format',
                'Attribute',
                'Dropdown',
                'Download, Licence Key, Account Activation, Voucher Code, Online Access, Other',
                'Téléchargement, Clé de licence, Activation de compte, Code de bon, Accès en ligne, Autre',
                'Active',
                'Specify digital fulfillment and delivery format.',
                ''
            ],
            [
                'Language',
                'Langue',
                'language',
                'Attribute',
                'Multiselect',
                'English, French, Spanish, German, Other',
                'Anglais, Français, Espagnol, Allemand, Autre',
                'Active',
                'Supported interface and audio languages.',
                ''
            ],
            [
                'Color',
                'Couleur',
                'color',
                'Variant',
                'Dropdown',
                'Red, Blue, Green, Black, White, Silver',
                'Rouge, Bleu, Vert, Noir, Blanc, Argent',
                'Active',
                'Shopper-selectable color variant.',
                ''
            ],
            [
                'Size',
                'Taille',
                'size',
                'Variant',
                'Dropdown',
                'XS, S, M, L, XL, XXL, Free Size',
                'XS, S, M, L, XL, XXL, Taille Unique',
                'Active',
                'Shopper-selectable size variant.',
                ''
            ],
            [
                'Licence Type',
                'Type de licence',
                'licence_type',
                'Variant',
                'Dropdown',
                'Single User, Family, Team, Enterprise, Other',
                'Utilisateur unique, Famille, Équipe, Entreprise, Autre',
                'Active',
                'Shopper-selectable software licence rights.',
                ''
            ]
        ];

        $output = fopen('php://temp', 'r+');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, $headers);
        foreach ($sampleData as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }

    /**
     * Generate CSV Error Report
     */
    public function generateErrorReportCsv($errors)
    {
        $headers = ['Row Number', 'Attribute Code', 'Attribute Name', 'Error Type', 'Error Message'];
        $output = fopen('php://temp', 'r+');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, $headers);

        foreach ($errors as $err) {
            fputcsv($output, [
                $err['row'] ?? '',
                $err['attr_code'] ?? '',
                $err['attr_name'] ?? '',
                $err['type'] ?? 'Error',
                $err['message'] ?? ''
            ]);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }
}
