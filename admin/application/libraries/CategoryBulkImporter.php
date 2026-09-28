<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CategoryBulkImporter
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

        // Detect delimiter: comma, semicolon, tab
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

        $rows = [];
        $header = null;
        $rowNum = 0;

        while (($data = fgetcsv($stream, 0, $bestDelimiter)) !== false) {
            $rowNum++;
            // Check if entire row is empty
            $nonEmpty = array_filter($data, function ($val) {
                return trim($val) !== '';
            });
            if (empty($nonEmpty)) {
                continue;
            }

            if ($header === null) {
                $header = array_map([$this, 'normalizeHeaderKey'], $data);
                continue;
            }

            // Combine with header
            $rowData = [];
            foreach ($header as $idx => $key) {
                if ($key !== '') {
                    $rowData[$key] = isset($data[$idx]) ? trim($data[$idx]) : '';
                }
            }
            $rowData['_row_number'] = $rowNum;
            $rows[] = $rowData;
        }

        fclose($stream);

        if (empty($rows)) {
            return ['status' => false, 'message' => 'No data rows found in the uploaded CSV file.'];
        }

        return ['status' => true, 'rows' => $rows];
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

        // Load shared strings if available
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

        // Load sheet1
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            return ['status' => false, 'message' => 'Could not find worksheet in XLSX file.'];
        }

        $xml = @simplexml_load_string($sheetXml);
        if (!$xml || !isset($xml->sheetData)) {
            return ['status' => false, 'message' => 'Invalid XLSX sheet format.'];
        }

        $matrix = [];
        foreach ($xml->sheetData->row as $r) {
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
            $matrix[] = $rowArr;
        }

        if (empty($matrix)) {
            return ['status' => false, 'message' => 'No data rows found in XLSX.'];
        }

        $rawHeader = array_shift($matrix);
        $header = array_map([$this, 'normalizeHeaderKey'], $rawHeader);

        $rows = [];
        $rowNum = 1;
        foreach ($matrix as $data) {
            $rowNum++;
            $nonEmpty = array_filter($data, function ($val) {
                return trim($val) !== '';
            });
            if (empty($nonEmpty)) {
                continue;
            }

            $rowData = [];
            foreach ($header as $idx => $key) {
                if ($key !== '') {
                    $rowData[$key] = isset($data[$idx]) ? trim($data[$idx]) : '';
                }
            }
            $rowData['_row_number'] = $rowNum;
            $rows[] = $rowData;
        }

        if (empty($rows)) {
            return ['status' => false, 'message' => 'No category records found in XLSX file.'];
        }

        return ['status' => true, 'rows' => $rows];
    }

    /**
     * Map flexible/human-readable header names to standardized field keys
     */
    public function normalizeHeaderKey($header)
    {
        $clean = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $header)));

        $map = [
            'categorynameenglish' => 'cat_name',
            'categoryname'        => 'cat_name',
            'catname'             => 'cat_name',
            'name'                => 'cat_name',
            'title'               => 'cat_name',

            'categorynamefrench'  => 'lang_title',
            'frenchtitle'         => 'lang_title',
            'frenchname'          => 'lang_title',
            'langtitle'           => 'lang_title',
            'langname'            => 'lang_title',

            'categorytype'        => 'category_type',
            'cattype'             => 'category_type',
            'type'                => 'category_type',

            'slug'                => 'slug',
            'categoryslug'        => 'slug',

            'categorydescription' => 'cat_description',
            'catdescription'      => 'cat_description',
            'description'         => 'cat_description',

            'metatitle'           => 'meta_title',
            'seotitle'            => 'meta_title',

            'metakeyword'         => 'meta_keyword',
            'metakeywords'        => 'meta_keyword',
            'keywords'            => 'meta_keyword',

            'metadescription'     => 'meta_description',
            'seodescription'      => 'meta_description',

            'parentcategory'      => 'parent_category',
            'parentcategoryname'  => 'parent_category',
            'parentname'          => 'parent_category',
            'parentslug'          => 'parent_category',
            'parentid'            => 'parent_category',
            'parent'              => 'parent_category',

            'mainparentcategory'  => 'main_parent_category',
            'mainparent'          => 'main_parent_category',
            'maincategory'        => 'main_parent_category',
            'rootcategory'        => 'main_parent_category',

            'categorylevel'       => 'cat_level',
            'catlevel'            => 'cat_level',
            'level'               => 'cat_level',

            'categoryimage'       => 'cat_image',
            'catimage'            => 'cat_image',
            'image'               => 'cat_image',
            'imagefile'           => 'cat_image',

            'selectedattributes'  => 'selected_attributes',
            'attributes'          => 'selected_attributes',
            'attributeids'        => 'selected_attributes',

            'selectedvariants'    => 'selected_variants',
            'variants'            => 'selected_variants',
            'variantids'          => 'selected_variants',

            'status'              => 'status',
            'categorystatus'      => 'status',

            'position'            => 'position',
            'displayposition'     => 'position',
            'displayorder'        => 'position',
            'order'               => 'position'
        ];

        return isset($map[$clean]) ? $map[$clean] : $clean;
    }

    /**
     * Generate URL-friendly slug
     */
    public function generateSlug($name)
    {
        $slug = trim($name);
        $slug = str_replace(['&', '+', '/'], '-', $slug);
        $slug = str_replace(' ', '-', $slug);
        $slug = preg_replace('/[^A-Za-z0-9\-]/', '', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return strtolower(trim($slug, '-'));
    }

    /**
     * Validate all rows without modifying the database
     */
    public function validateRecords($rows, $duplicateAction = 'skip')
    {
        // Load existing categories from DB
        $dbCategories = $this->db->select('id, cat_name, slug, parent_id, main_parent_id, cat_level, status')
            ->from('category')
            ->get()
            ->result_array();

        $existingSlugs = [];
        $existingNames = [];
        $existingById = [];
        foreach ($dbCategories as $cat) {
            $existingSlugs[strtolower($cat['slug'])] = $cat;
            $existingNames[strtolower($cat['cat_name'])] = $cat;
            $existingById[$cat['id']] = $cat;
        }

        // Load valid attribute & variant IDs from eav_attributes
        $dbAttributes = $this->db->select('id, attr_name, attr_type')
            ->from('eav_attributes')
            ->where('status', 1)
            ->get()
            ->result_array();

        $validAttributeIds = [];
        $validVariantIds = [];
        foreach ($dbAttributes as $attr) {
            if (isset($attr['attr_type']) && (int)$attr['attr_type'] === 1) {
                $validAttributeIds[$attr['id']] = $attr['attr_name'] ?? '';
            } elseif (isset($attr['attr_type']) && (int)$attr['attr_type'] === 2) {
                $validVariantIds[$attr['id']] = $attr['attr_name'] ?? '';
            }
        }

        $validatedRows = [];
        $seenSlugsInFile = [];
        $batchCategoriesByName = [];
        $batchCategoriesBySlug = [];

        $totalRecords = count($rows);
        $validRecords = 0;
        $invalidRecords = 0;
        $duplicateInDbCount = 0;
        $duplicateInFileCount = 0;
        $newCategoriesCount = 0;
        $updateCategoriesCount = 0;
        $skippedCategoriesCount = 0;

        foreach ($rows as $index => $row) {
            $rowNum = $row['_row_number'] ?? ($index + 2);
            $errors = [];
            $warnings = [];

            $catName = trim($row['cat_name'] ?? '');
            $langTitle = trim($row['lang_title'] ?? '');
            $categoryType = trim($row['category_type'] ?? '');
            $parentCategory = trim($row['parent_category'] ?? '');
            $mainParentCategory = trim($row['main_parent_category'] ?? '');
            $userSlug = trim($row['slug'] ?? '');
            $catDesc = trim($row['cat_description'] ?? '');
            $metaTitle = trim($row['meta_title'] ?? '');
            $metaKeyword = trim($row['meta_keyword'] ?? '');
            $metaDesc = trim($row['meta_description'] ?? '');
            $catImage = trim($row['cat_image'] ?? '');
            $selAttributes = trim($row['selected_attributes'] ?? '');
            $selVariants = trim($row['selected_variants'] ?? '');
            $rawStatus = trim($row['status'] ?? '1');
            $position = isset($row['position']) && $row['position'] !== '' ? (int)$row['position'] : 0;

            // 1. Validate Category Name
            if ($catName === '') {
                $errors[] = 'Category Name is required.';
            }

            // 2. Resolve Slug
            $slug = !empty($userSlug) ? $this->generateSlug($userSlug) : $this->generateSlug($catName);
            if ($catName !== '' && empty($slug)) {
                $errors[] = 'Could not generate a valid slug from Category Name.';
            }

            // 3. Duplicate checks
            $isDuplicateInDb = false;
            $isDuplicateInFile = false;
            $existingDbRecord = null;

            if (!empty($slug)) {
                if (isset($existingSlugs[$slug])) {
                    $isDuplicateInDb = true;
                    $existingDbRecord = $existingSlugs[$slug];
                } elseif (isset($existingNames[strtolower($catName)])) {
                    $isDuplicateInDb = true;
                    $existingDbRecord = $existingNames[strtolower($catName)];
                }

                if (isset($seenSlugsInFile[$slug])) {
                    $isDuplicateInFile = true;
                    $errors[] = "Duplicate slug '{$slug}' already defined in Row {$seenSlugsInFile[$slug]}.";
                } else {
                    $seenSlugsInFile[$slug] = $rowNum;
                }
            }

            // 4. Status normalization
            $status = 1;
            $lowerStatus = strtolower($rawStatus);
            if (in_array($lowerStatus, ['0', 'inactive', 'disable', 'disabled', 'no', 'false'], true)) {
                $status = 0;
            } elseif (in_array($lowerStatus, ['1', 'active', 'enable', 'enabled', 'yes', 'true', ''], true)) {
                $status = 1;
            } else {
                $warnings[] = "Unrecognized status '{$rawStatus}', defaulted to Active.";
                $status = 1;
            }

            // 5. Hierarchy Level Resolution
            $catLevel = 0;
            $resolvedParent = null;
            $parentLookup = strtolower($parentCategory);

            $lowerType = strtolower($categoryType);
            $isExplicitParent = in_array($lowerType, ['parent', 'root', 'main', '0', 'level 0', 'level0'], true);
            $isExplicitSub = in_array($lowerType, ['sub-category', 'sub category', 'sub_category', 'subcategory', 'level 1', 'level1', '1'], true);
            $isExplicitChild = in_array($lowerType, ['child sub-category', 'child sub category', 'child_sub_category', 'child subcategory', 'level 2', 'level2', '2'], true);

            if ($isExplicitParent || ($parentCategory === '' || $parentCategory === '0' || $parentCategory === 'Parent')) {
                $catLevel = 0;
            } else {
                // Find parent in DB or earlier in batch
                if (isset($existingNames[$parentLookup])) {
                    $resolvedParent = $existingNames[$parentLookup];
                } elseif (isset($existingSlugs[$parentLookup])) {
                    $resolvedParent = $existingSlugs[$parentLookup];
                } elseif (is_numeric($parentCategory) && isset($existingById[(int)$parentCategory])) {
                    $resolvedParent = $existingById[(int)$parentCategory];
                } elseif (isset($batchCategoriesByName[$parentLookup])) {
                    $resolvedParent = $batchCategoriesByName[$parentLookup];
                } elseif (isset($batchCategoriesBySlug[$parentLookup])) {
                    $resolvedParent = $batchCategoriesBySlug[$parentLookup];
                }

                if ($resolvedParent === null) {
                    $errors[] = "Parent category '{$parentCategory}' not found in database or uploaded file.";
                } else {
                    $parentLevel = (int)$resolvedParent['cat_level'];
                    if ($parentLevel === 0) {
                        $catLevel = 1;
                    } elseif ($parentLevel === 1) {
                        $catLevel = 2;
                    } else {
                        $catLevel = 3;
                        $warnings[] = 'Parent is already Level 2. Category will be set to Level 3.';
                    }
                }
            }

            // Cross-check with explicit Category Type if provided
            if ($isExplicitSub && $catLevel === 0 && !empty($parentCategory)) {
                $catLevel = 1;
            } elseif ($isExplicitChild && $catLevel <= 1 && !empty($parentCategory)) {
                $catLevel = 2;
            }

            // 6. Validate Selected Attributes & Variants
            $formattedAttributes = '';
            if ($selAttributes !== '') {
                $attrList = array_filter(array_map('trim', explode(',', $selAttributes)));
                $invalidAttr = [];
                $validAttrArr = [];
                foreach ($attrList as $aId) {
                    if (isset($validAttributeIds[$aId])) {
                        $validAttrArr[] = $aId;
                    } elseif (is_numeric($aId) && isset($validAttributeIds[(int)$aId])) {
                        $validAttrArr[] = (int)$aId;
                    } else {
                        $invalidAttr[] = $aId;
                    }
                }
                if (!empty($invalidAttr)) {
                    $errors[] = 'Invalid Attribute ID(s): ' . implode(', ', $invalidAttr);
                } else {
                    $formattedAttributes = !empty($validAttrArr) ? ',' . implode(',', $validAttrArr) . ',' : '';
                }
            }

            $formattedVariants = '';
            if ($selVariants !== '') {
                $varList = array_filter(array_map('trim', explode(',', $selVariants)));
                $invalidVar = [];
                $validVarArr = [];
                foreach ($varList as $vId) {
                    if (isset($validVariantIds[$vId])) {
                        $validVarArr[] = $vId;
                    } elseif (is_numeric($vId) && isset($validVariantIds[(int)$vId])) {
                        $validVarArr[] = (int)$vId;
                    } else {
                        $invalidVar[] = $vId;
                    }
                }
                if (!empty($invalidVar)) {
                    $errors[] = 'Invalid Variant ID(s): ' . implode(', ', $invalidVar);
                } else {
                    $formattedVariants = !empty($validVarArr) ? ',' . implode(',', $validVarArr) . ',' : '';
                }
            }

            // 7. Validate Image filename/path if specified
            if ($catImage !== '') {
                if (strpos($catImage, '..') !== false || strpos($catImage, '/') !== false || strpos($catImage, '\\') !== false) {
                    $errors[] = "Image filename '{$catImage}' contains invalid path characters.";
                } else {
                    $imagePath = (defined('SIS_SERVER_PATH') ? SIS_SERVER_PATH : FCPATH) . '/uploads/categories/' . $catImage;
                    if (!file_exists($imagePath)) {
                        $warnings[] = "Image '{$catImage}' was not found in uploads/categories/.";
                    }
                }
            }

            // Determine target action based on duplicate handling
            $action = 'insert';
            if ($isDuplicateInDb) {
                $duplicateInDbCount++;
                if ($duplicateAction === 'skip') {
                    $action = 'skip';
                    $skippedCategoriesCount++;
                } elseif ($duplicateAction === 'update') {
                    $action = 'update';
                    $updateCategoriesCount++;
                } elseif ($duplicateAction === 'stop') {
                    $errors[] = "Duplicate category with slug '{$slug}' already exists in database (Stop on Duplicate mode).";
                }
            } else {
                $newCategoriesCount++;
            }

            if ($isDuplicateInFile) {
                $duplicateInFileCount++;
            }

            $isValid = empty($errors);
            if ($isValid) {
                $validRecords++;
                // Register in temporary batch dictionary so subsequent rows can reference this row as parent
                $batchCategoriesByName[strtolower($catName)] = [
                    'id'             => 'temp_' . $rowNum,
                    'cat_name'       => $catName,
                    'slug'           => $slug,
                    'cat_level'      => $catLevel,
                    'parent_name'    => $parentCategory,
                    'is_temp'        => true
                ];
                $batchCategoriesBySlug[strtolower($slug)] = [
                    'id'             => 'temp_' . $rowNum,
                    'cat_name'       => $catName,
                    'slug'           => $slug,
                    'cat_level'      => $catLevel,
                    'parent_name'    => $parentCategory,
                    'is_temp'        => true
                ];
            } else {
                $invalidRecords++;
            }

            $levelLabel = 'Parent (Level 0)';
            if ($catLevel === 1) {
                $levelLabel = 'Sub-Category (Level 1)';
            } elseif ($catLevel === 2) {
                $levelLabel = 'Child Sub-Category (Level 2)';
            } elseif ($catLevel >= 3) {
                $levelLabel = 'Level ' . $catLevel;
            }

            $validatedRows[] = [
                'row_number'          => $rowNum,
                'cat_name'            => $catName,
                'lang_title'          => $langTitle,
                'category_type'       => $categoryType ?: ($catLevel === 0 ? 'Parent' : ($catLevel === 1 ? 'Sub-Category' : 'Child Sub-Category')),
                'cat_level'           => $catLevel,
                'level_label'         => $levelLabel,
                'slug'                => $slug,
                'parent_category'     => $parentCategory,
                'main_parent_category'=> $mainParentCategory,
                'cat_description'     => $catDesc,
                'meta_title'          => $metaTitle,
                'meta_keyword'        => $metaKeyword,
                'meta_description'    => $metaDesc,
                'cat_image'           => $catImage,
                'selected_attributes' => $formattedAttributes,
                'selected_variants'   => $formattedVariants,
                'raw_attributes'      => $selAttributes,
                'raw_variants'        => $selVariants,
                'status'              => $status,
                'position'            => $position,
                'is_duplicate'        => $isDuplicateInDb || $isDuplicateInFile,
                'is_duplicate_db'     => $isDuplicateInDb,
                'existing_id'         => $existingDbRecord ? (int)$existingDbRecord['id'] : null,
                'action'              => $action,
                'is_valid'            => $isValid,
                'errors'              => $errors,
                'warnings'            => $warnings
            ];
        }

        return [
            'status'   => true,
            'summary'  => [
                'total_records'       => $totalRecords,
                'valid_records'       => $validRecords,
                'invalid_records'     => $invalidRecords,
                'duplicate_db_count'  => $duplicateInDbCount,
                'duplicate_file_count'=> $duplicateInFileCount,
                'new_categories'      => $newCategoriesCount,
                'update_categories'   => $updateCategoriesCount,
                'skipped_categories'  => $skippedCategoriesCount
            ],
            'rows'     => $validatedRows
        ];
    }

    /**
     * Execute Transaction-based Database Import
     */
    public function importRecords($validatedRows, $duplicateAction = 'skip', $fileName = 'category_import.csv', $adminId = 0, $ip = '')
    {
        $this->db->trans_begin();

        $importedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $failedCount = 0;
        $errorLog = [];

        // Build mapping of existing categories
        $dbCategories = $this->db->select('id, cat_name, slug, parent_id, main_parent_id, cat_level')
            ->from('category')
            ->get()
            ->result_array();

        $categoryMapByName = [];
        $categoryMapBySlug = [];
        $categoryMapById = [];

        foreach ($dbCategories as $cat) {
            $categoryMapByName[strtolower($cat['cat_name'])] = $cat;
            $categoryMapBySlug[strtolower($cat['slug'])] = $cat;
            $categoryMapById[(int)$cat['id']] = $cat;
        }

        // Partition rows by cat_level so Level 0 is inserted first, then Level 1, then Level 2
        $levelGroups = [
            0 => [],
            1 => [],
            2 => [],
            3 => []
        ];

        foreach ($validatedRows as $row) {
            if (!$row['is_valid']) {
                $failedCount++;
                $errorLog[] = [
                    'row'      => $row['row_number'],
                    'category' => $row['cat_name'],
                    'slug'     => $row['slug'],
                    'type'     => 'Validation Error',
                    'message'  => implode('; ', $row['errors'])
                ];
                continue;
            }

            if ($row['action'] === 'skip') {
                $skippedCount++;
                continue;
            }

            $lvl = (int)($row['cat_level'] ?? 0);
            if (!isset($levelGroups[$lvl])) {
                $levelGroups[$lvl] = [];
            }
            $levelGroups[$lvl][] = $row;
        }

        $now = time();

        // Process Level 0 -> Level 1 -> Level 2 -> Level 3
        ksort($levelGroups);

        foreach ($levelGroups as $level => $rowsInLevel) {
            foreach ($rowsInLevel as $row) {
                $slug = $row['slug'];
                $catName = $row['cat_name'];
                $existing = $row['existing_id'] ? ($categoryMapById[$row['existing_id']] ?? null) : ($categoryMapBySlug[$slug] ?? null);

                // Handle Update
                if ($row['action'] === 'update' && $existing) {
                    $catId = (int)$existing['id'];
                    $updateData = [
                        'cat_name'            => $catName,
                        'lang_title'          => $row['lang_title'],
                        'cat_description'     => $row['cat_description'],
                        'meta_title'          => $row['meta_title'],
                        'meta_keyword'        => $row['meta_keyword'],
                        'meta_description'    => $row['meta_description'],
                        'status'              => $row['status'],
                        'position'            => $row['position'],
                        'updated_at'          => $now,
                        'ip'                  => $ip
                    ];

                    if (!empty($row['cat_image'])) {
                        $updateData['cat_image'] = $row['cat_image'];
                    }
                    if (!empty($row['selected_attributes'])) {
                        $updateData['selected_attributes'] = $row['selected_attributes'];
                    }
                    if (!empty($row['selected_variants'])) {
                        $updateData['selected_variants'] = $row['selected_variants'];
                    }

                    $this->db->where('id', $catId);
                    $ok = $this->db->update('category', $updateData);

                    if ($ok) {
                        $updatedCount++;
                        // Refresh map entry
                        $merged = array_merge($existing, $updateData);
                        $categoryMapByName[strtolower($catName)] = $merged;
                        $categoryMapBySlug[strtolower($slug)] = $merged;
                        $categoryMapById[$catId] = $merged;
                    } else {
                        $failedCount++;
                        $errorLog[] = [
                            'row'      => $row['row_number'],
                            'category' => $catName,
                            'slug'     => $slug,
                            'type'     => 'Database Error',
                            'message'  => 'Failed to update category record.'
                        ];
                    }
                    continue;
                }

                // Handle Insert
                $parentId = 0;
                $mainParentId = 0;
                $parentLookup = strtolower($row['parent_category']);

                if ($level > 0 && !empty($parentLookup)) {
                    $parentCat = null;
                    if (isset($categoryMapByName[$parentLookup])) {
                        $parentCat = $categoryMapByName[$parentLookup];
                    } elseif (isset($categoryMapBySlug[$parentLookup])) {
                        $parentCat = $categoryMapBySlug[$parentLookup];
                    } elseif (is_numeric($row['parent_category']) && isset($categoryMapById[(int)$row['parent_category']])) {
                        $parentCat = $categoryMapById[(int)$row['parent_category']];
                    }

                    if ($parentCat) {
                        $parentId = (int)$parentCat['id'];
                        $parentLevel = (int)$parentCat['cat_level'];
                        if ($parentLevel === 0) {
                            $mainParentId = $parentId;
                        } else {
                            $mainParentId = (int)$parentCat['main_parent_id'];
                        }
                    }
                }

                $insertData = [
                    'cat_name'            => $catName,
                    'lang_title'          => $row['lang_title'],
                    'slug'                => $slug,
                    'cat_description'     => $row['cat_description'],
                    'meta_title'          => $row['meta_title'],
                    'meta_keyword'        => $row['meta_keyword'],
                    'meta_description'    => $row['meta_description'],
                    'parent_id'           => $parentId,
                    'main_parent_id'      => $mainParentId,
                    'cat_level'           => $level,
                    'cat_image'           => $row['cat_image'],
                    'selected_attributes' => $row['selected_attributes'],
                    'selected_variants'   => $row['selected_variants'],
                    'status'              => $row['status'],
                    'created_by'          => $adminId,
                    'position'            => $row['position'],
                    'created_at'          => $now,
                    'updated_at'          => $now,
                    'ip'                  => $ip
                ];

                $ok = $this->db->insert('category', $insertData);
                if ($ok) {
                    $newId = $this->db->insert_id();
                    $importedCount++;

                    $createdRecord = array_merge($insertData, ['id' => $newId]);
                    $categoryMapByName[strtolower($catName)] = $createdRecord;
                    $categoryMapBySlug[strtolower($slug)] = $createdRecord;
                    $categoryMapById[$newId] = $createdRecord;
                } else {
                    $failedCount++;
                    $errorLog[] = [
                        'row'      => $row['row_number'],
                        'category' => $catName,
                        'slug'     => $slug,
                        'type'     => 'Database Error',
                        'message'  => 'Failed to insert category record.'
                    ];
                }
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return [
                'status'  => false,
                'message' => 'Database transaction failed during bulk import. Changes have been rolled back.'
            ];
        }

        $this->db->trans_commit();

        $overallStatus = 'Completed';
        if ($failedCount > 0 && $importedCount === 0 && $updatedCount === 0) {
            $overallStatus = 'Failed';
        } elseif ($failedCount > 0) {
            $overallStatus = 'Partial';
        }

        // Log into history table
        $historyData = [
            'file_name'        => $fileName,
            'total_records'    => count($validatedRows),
            'imported_count'   => $importedCount,
            'updated_count'    => $updatedCount,
            'skipped_count'    => $skippedCount,
            'failed_count'     => $failedCount,
            'status'           => $overallStatus,
            'duplicate_action' => $duplicateAction,
            'error_log'        => !empty($errorLog) ? json_encode($errorLog) : null,
            'created_by'       => $adminId,
            'created_at'       => $now,
            'ip'               => $ip
        ];

        $this->db->insert('category_bulk_upload_history', $historyData);
        $historyId = $this->db->insert_id();

        return [
            'status'        => true,
            'history_id'    => $historyId,
            'summary'       => [
                'total'    => count($validatedRows),
                'imported' => $importedCount,
                'updated'  => $updatedCount,
                'skipped'  => $skippedCount,
                'failed'   => $failedCount,
                'status'   => $overallStatus
            ],
            'errors'        => $errorLog
        ];
    }

    /**
     * Generate Sample CSV template
     */
    public function generateSampleCsv()
    {
        $headers = [
            'Category Name (English)',
            'Category Name (French)',
            'Category Type',
            'Status',
            'Category Description',
            'Meta Title',
            'Meta Keyword',
            'Meta Description',
            'Parent Category',
            'Main Parent Category',
            'Slug',
            'Category Image',
            'Selected Attributes',
            'Selected Variants',
            'Position'
        ];

        $sampleData = [
            [
                'Apps - Software',
                'Applications - Logiciels',
                'Parent',
                'Active',
                'Shop Apps - Software products on Yellow Markets.',
                'Apps - Software | Yellow Markets',
                'Apps - Software, Apps - Software, Yellow Markets',
                'Browse Apps - Software products on Yellow Markets.',
                '',
                '',
                'apps-software',
                '',
                '',
                '',
                '1'
            ],
            [
                'Apps',
                'Applications',
                'Sub-Category',
                'Active',
                'Browse Apps in Apps - Software on Yellow Markets.',
                'Apps | Apps - Software | Yellow Markets',
                'Apps, Apps - Software, Yellow Markets',
                'Browse Apps products on Yellow Markets.',
                'Apps - Software',
                'Apps - Software',
                'apps',
                '',
                '',
                '',
                '2'
            ],
            [
                'Educational Apps',
                'Applications éducatives',
                'Child Sub-Category',
                'Active',
                'Shop Educational Apps under Apps in Apps - Software on Yellow Markets.',
                'Educational Apps | Apps - Software | Yellow Markets',
                'Educational Apps, Apps, Apps - Software, Yellow Markets',
                'Explore Educational Apps products in the Apps - Software category.',
                'Apps',
                'Apps - Software',
                'educational-apps',
                '',
                '',
                '',
                '3'
            ],
            [
                'Entertainment Apps',
                'Applications de divertissement',
                'Child Sub-Category',
                'Active',
                'Shop Entertainment Apps under Apps in Apps - Software on Yellow Markets.',
                'Entertainment Apps | Apps - Software | Yellow Markets',
                'Entertainment Apps, Apps, Apps - Software, Yellow Markets',
                'Explore Entertainment Apps products in the Apps - Software category.',
                'Apps',
                'Apps - Software',
                'entertainment-apps',
                '',
                '',
                '',
                '4'
            ],
            [
                'Electronics',
                'Électronique',
                'Parent',
                'Active',
                'Shop all electronics devices and gadgets.',
                'Electronics | Yellow Markets',
                'Electronics, Gadgets, Yellow Markets',
                'Best deals on electronics devices and gadgets.',
                '',
                '',
                'electronics',
                '',
                '',
                '',
                '5'
            ],
            [
                'Mobile',
                'Téléphones Portables',
                'Sub-Category',
                'Active',
                'Smartphones and mobile devices.',
                'Mobile Phones | Electronics | Yellow Markets',
                'Mobile, Smartphones, Electronics, Yellow Markets',
                'Explore top brand mobile phones and accessories.',
                'Electronics',
                'Electronics',
                'mobile',
                '',
                '',
                '',
                '6'
            ],
            [
                'Android',
                'Android',
                'Child Sub-Category',
                'Active',
                'Android smartphones and tablets.',
                'Android Phones | Electronics | Yellow Markets',
                'Android, Smartphones, Mobile, Yellow Markets',
                'Discover the latest Android smartphones and tablets.',
                'Mobile',
                'Electronics',
                'android',
                '',
                '',
                '',
                '7'
            ]
        ];

        $output = fopen('php://temp', 'r+');
        // Add UTF-8 BOM for Excel compatibility
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
        $headers = ['Row Number', 'Category Name', 'Slug', 'Error Type', 'Error Message'];
        $output = fopen('php://temp', 'r+');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, $headers);

        foreach ($errors as $err) {
            fputcsv($output, [
                $err['row'] ?? '',
                $err['category'] ?? '',
                $err['slug'] ?? '',
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
