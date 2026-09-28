<?php 

class AttributeController extends CI_Controller
{
    public function __construct()
    {
         parent::__construct();
		 $this->load->model('EavAttributesModel');
    }
	
	public function attributeList()
	{
		if($_SESSION['UserRole'] !== 'Super Admin') {
            if(!empty($this->session->userdata('userPermission')) && !in_array('database/attributes',$this->session->userdata('userPermission'))){ 
                redirect('dashboard');
            }
        }

		$SISA_ID=$this->session->userdata('LoginID');
		if($SISA_ID){			
			$data['getAttribute'] = $this->EavAttributesModel->get_attributes_masters();
			$data['PageTitle']='Attribute';
			$data['side_menu']='attribute';
			$this->load->view('attribute/attribute_list',$data);  
		}else{
			return redirect('/'); 
		}
	}

	public function addAttribute()
	{
		if($_SESSION['UserRole'] !== 'Super Admin') {
            if(!empty($this->session->userdata('userPermission')) && !in_array('database/attributes',$this->session->userdata('userPermission'))){ 
                redirect('dashboard');
            }
        }
		
		$SISA_ID=$this->session->userdata('LoginID');
		if($SISA_ID){		
			$data['PageTitle']='Attribute Add';
			$data['side_menu']='attribute';
			$this->load->view('attribute/attribute_add');  
		}else{
			return redirect('/'); 
		}
	}

	public function editAttribute($attributeId)
	{
		$SISA_ID=$this->session->userdata('LoginID');
		if($SISA_ID){	
			if($attributeId){
				$Att_ID_DATA= $this->EavAttributesModel->getSingleDataByID('eav_attributes',array('id'=>$attributeId),'*');
				$data['attribute'] = $this->EavAttributesModel->get_attribute_detail($attributeId);
				$valuesArray = $this->EavAttributesModel->get_attribute_option_values($attributeId);	
				if($Att_ID_DATA == '' || $valuesArray == ''){
					redirect('dashboard');

				}			
				$strValues = implode(",",array_map(function($a) {return implode("~",$a);},$valuesArray));
				$data['attributevalues']=$strValues;
				$data['PageTitle']='Attribute Edit';
				$data['side_menu']='attribute';
				$this->load->view('attribute/attribute_edit',$data);  
			}else{
				return redirect('/'); 
			}
		}else{
			return redirect('/');
		}
	}

	public function getAttribute()
	{	
		$attribute_id = $_POST['id'];	
		if($attribute_id){
			$attributevalues = $this->AttributeModel->getAttributeValues($attribute_id);
			echo json_encode(array('flag'=>1, 'data'=>$attributevalues));
			exit;
		}else{
			echo json_encode(array('flag'=>0));
			exit;
		}
	}

	public function submitAttribute()
	{
		$SISA_ID=$this->session->userdata('LoginID');
		if($SISA_ID){
			if(empty($_POST['attribute_name']) || empty($_POST['attribute_code'])|| empty($_POST['attribute_properties'] ) )
			{
				echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));
				exit;
			}
			elseif(($_POST['attribute_properties']== '6' || $_POST['attribute_properties']=='5') && (empty($_POST['tagsValues']) && empty($_POST['tagsnewValues'])) ){
				echo json_encode(array('flag'=>0, 'msg'=>"Please enter attribute values"));
				exit;
			}
			else{
				$array_attributes = $_POST;
				$attribute_id = $_POST['attribute_id'];
				$attribute_code = $_POST['attribute_code'];

		    	if($attribute_id!= ''){

					$is_success = $this->EavAttributesModel->insert_update_attributes($array_attributes,$SISA_ID,$attribute_id,1);
					if($is_success){
						if($_POST['attribute_properties'] == 5 || $_POST['attribute_properties'] == 6){
							$strTags='';
							if($array_attributes['tagsnewValues'] !='' && $array_attributes['tagsValues'] !=''){
								$strTags=$array_attributes['tagsValues'].",".$array_attributes['tagsnewValues'];
							}
							elseif($array_attributes['tagsnewValues'] !=''){
								$strTags = $array_attributes['tagsnewValues'];
							}
							elseif($array_attributes['tagsValues'] !=''){
								$strTags = $array_attributes['tagsValues'];
			
							}
							 $this->db->delete('eav_attributes_options',['attr_id'=>$attribute_id]);
							if($strTags != '')
							{
								$tagsValues = explode(',', $strTags);
								foreach ($tagsValues as $key => $value) {
									if($value != ''){
										$this->EavAttributesModel->insert_attributes_option_value($value,$attribute_id,$SISA_ID);
									}
								}
							}
						}
						echo json_encode("update");
                            exit;
						// $url = base_url().'attribute';
						// echo json_encode(array('flag' => 1, 'msg' => "Successfully Updated","url"=>$url));
						// exit;		
					}
					else{
						echo json_encode(array('flag' => 0, 'msg' => "Something went wrong. Please try again"));
						exit;
					}
		    	}else{
					//Insert attribute
					$codeFund = $this->EavAttributesModel->getAttributeCode($attribute_code,'1');
					if($codeFund){
						echo json_encode(array('flag' => 0, 'msg' => "Attribute Code Exist!"));
						exit;
					}
					elseif($this->EavAttributesModel->getAttributeName($_POST["attribute_name"]) != 0){
						echo json_encode(array('flag' => 0, 'msg' => "Attribute Name Exist!"));
						exit;
					}
					$is_success = $this->EavAttributesModel->insert_update_attributes($array_attributes,$SISA_ID,$attribute_id,1);
					if($is_success){
						$insert_id = $this->db->insert_id();
						if($_POST['attribute_properties'] == 5 || $_POST['attribute_properties'] == 6){
	
							$tagsValues = explode(',', $_POST['tagsValues']);
							foreach ($tagsValues as $key => $value) {
								if($value != ''){
	
									$this->EavAttributesModel->insert_attributes_option_value($value,$insert_id,$SISA_ID);
								}
							}
						}
						echo json_encode("insert");
                            exit;
						// $url = base_url().'attribute';
						// echo json_encode(array('flag' => 1, 'msg' => "Successfully Added","url"=>$url));
						// exit;				
					}
					else{
						echo json_encode(array('flag' => 0, 'msg' => "Something went wrong. Please try again"));
						exit;
					}
				}
			}
		}else{
			return redirect('/'); 
		}
	}

	public function bulkUpload()
	{
		if (isset($_SESSION['UserRole']) && $_SESSION['UserRole'] !== 'Super Admin') {
			if (!empty($this->session->userdata('userPermission')) && !in_array('database/attributes', $this->session->userdata('userPermission'))) {
				redirect('/admin/attribute');
			}
		}

		$SISA_ID = $this->session->userdata('LoginID');
		if ($SISA_ID) {
			$data['PageTitle'] = 'Attribute - Bulk Upload';
			$data['side_menu'] = 'attribute';
			$data['sub_menu'] = 'attribute_bulk_upload';

			// Fetch upload history
			$this->db->select('h.*, CONCAT(u.first_name, " ", u.last_name) as admin_name, u.username as admin_username');
			$this->db->from('attribute_bulk_upload_history h');
			$this->db->join('adminusers u', 'u.id = h.created_by', 'left');
			$this->db->order_by('h.id', 'DESC');
			$this->db->limit(50);
			$data['history'] = $this->db->get()->result_array();

			$this->load->view('attribute/attribute_bulk_upload', $data);
		} else {
			return redirect('/');
		}
	}

	public function downloadSampleTemplate()
	{
		$this->load->library('AttributeBulkImporter');
		$csvContent = $this->attributebulkimporter->generateSampleCsv();

		$filename = 'category_attribute_bulk_upload_sample.csv';
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Pragma: no-cache');
		header('Expires: 0');

		echo $csvContent;
		exit;
	}

	public function validateBulkUpload()
	{
		header('Content-Type: application/json');

		if (isset($_SESSION['UserRole']) && $_SESSION['UserRole'] !== 'Super Admin') {
			if (!empty($this->session->userdata('userPermission')) && !in_array('database/attributes', $this->session->userdata('userPermission'))) {
				echo json_encode(['status' => false, 'message' => 'Permission denied.']);
				exit;
			}
		}

		if (empty($_FILES['bulk_file']['name'])) {
			echo json_encode(['status' => false, 'message' => 'Please select an Excel or CSV file to upload.']);
			exit;
		}

		$allowedExtensions = ['csv', 'xlsx', 'xls', 'txt'];
		$originalName = $_FILES['bulk_file']['name'];
		$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

		if (!in_array($ext, $allowedExtensions, true)) {
			echo json_encode(['status' => false, 'message' => 'Invalid file format. Only .csv and .xlsx files are supported.']);
			exit;
		}

		$tempPath = $_FILES['bulk_file']['tmp_name'];
		$this->load->library('AttributeBulkImporter');

		$parseResult = $this->attributebulkimporter->parseFile($tempPath, $originalName);
		if (!$parseResult['status']) {
			echo json_encode($parseResult);
			exit;
		}

		$validationResult = $this->attributebulkimporter->validateRecords($parseResult['rows']);

		// Cache validated rows in session
		$this->session->set_userdata('attribute_bulk_rows', $validationResult['rows']);
		$this->session->set_userdata('attribute_bulk_filename', $originalName);

		echo json_encode([
			'status'   => true,
			'filename' => $originalName,
			'summary'  => $validationResult['summary'],
			'rows'     => $validationResult['rows']
		]);
		exit;
	}

	public function importBulkAttributes()
	{
		header('Content-Type: application/json');

		if (isset($_SESSION['UserRole']) && $_SESSION['UserRole'] !== 'Super Admin') {
			if (!empty($this->session->userdata('userPermission')) && !in_array('database/attributes', $this->session->userdata('userPermission'))) {
				echo json_encode(['status' => false, 'message' => 'Permission denied.']);
				exit;
			}
		}

		$validatedRows = $this->session->userdata('attribute_bulk_rows');
		$fileName = $this->session->userdata('attribute_bulk_filename') ?: 'attribute_import.csv';

		if (empty($validatedRows) || !is_array($validatedRows)) {
			echo json_encode([
				'status'  => false,
				'message' => 'No validated attribute data found to import. Please upload and validate a file first.'
			]);
			exit;
		}

		$adminId = (int)$this->session->userdata('LoginID');
		$ip = $this->input->ip_address();

		$this->load->library('AttributeBulkImporter');
		$importResult = $this->attributebulkimporter->importRecords($validatedRows, $fileName, $adminId, $ip);

		// Clear temporary session data
		$this->session->unset_userdata('attribute_bulk_rows');
		$this->session->unset_userdata('attribute_bulk_filename');

		if ($importResult['status']) {
			$this->session->set_userdata('last_attr_import_errors', $importResult['errors'] ?? []);
		}

		echo json_encode($importResult);
		exit;
	}

	public function downloadErrorReport($historyId = 0)
	{
		$historyId = (int)$historyId;
		$errors = [];

		if ($historyId > 0) {
			$record = $this->db->select('error_log, file_name')
				->from('attribute_bulk_upload_history')
				->where('id', $historyId)
				->get()
				->row_array();

			if ($record && !empty($record['error_log'])) {
				$errors = json_decode($record['error_log'], true) ?: [];
			}
		}

		if (empty($errors)) {
			$errors = $this->session->userdata('last_attr_import_errors') ?: [];
		}

		if (empty($errors)) {
			$errors = [
				[
					'row'           => '-',
					'category_path' => '-',
					'attr_code'     => '-',
					'attr_name'     => '-',
					'type'          => 'Info',
					'message'       => 'No errors logged for this import session.'
				]
			];
		}

		$this->load->library('AttributeBulkImporter');
		$csvContent = $this->attributebulkimporter->generateErrorReportCsv($errors);

		$filename = 'attribute_import_error_report_' . date('Y-m-d_His') . '.csv';
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Pragma: no-cache');
		header('Expires: 0');

		echo $csvContent;
		exit;
	}

	public function deleteBulkUploadHistory()
	{
		header('Content-Type: application/json');
		$historyId = (int)$this->input->post('history_id');
		if ($historyId > 0) {
			$this->db->where('id', $historyId)->delete('attribute_bulk_upload_history');
			echo json_encode(['status' => true, 'message' => 'History record deleted successfully.']);
			exit;
		}
		echo json_encode(['status' => false, 'message' => 'Invalid history ID.']);
		exit;
	}
}
