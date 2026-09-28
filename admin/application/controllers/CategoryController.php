<?php 



class CategoryController extends CI_Controller

{

    public function __construct()

    {

        parent::__construct();

		$this->load->model('CategoryNewModel');

	

    }

	

	public function categoryList()

	{

		if($_SESSION['UserRole'] !== 'Super Admin') {

            if(!empty($this->session->userdata('userPermission')) && !in_array('database/category',$this->session->userdata('userPermission'))){ 

                redirect('dashboard');

            }

        }



		$SISA_ID=$this->session->userdata('LoginID');

		if($SISA_ID){

			$data['categories'] = $this->CategoryNewModel->getSubCategoryList();
			
			$data['browse_category'] = $this->CategoryNewModel->getAllCategories();

			$data['PageTitle']='Category';

			$data['side_menu']='category';

			$this->load->view('category/category_list',$data);

		}else{

			return redirect('/'); 

		}

	}



	public function addCategory()

	{

		if($_SESSION['UserRole'] !== 'Super Admin') {

            if(!empty($this->session->userdata('userPermission')) && !in_array('database/category',$this->session->userdata('userPermission'))){ 

                //redirect('dashboard');

				redirect('/admin/category');

            }

        }

		

		$LoginID=$this->session->userdata('LoginID');

		if($LoginID){

			$data['category_id']=$category_id = $this->uri->segment(3);

			if(isset($category_id)){

				$cat_ID_DATA= $this->CategoryNewModel->getSingleDataByID('category',array('id'=>$category_id),'*');

				if($cat_ID_DATA == ''){

					redirect('dashboard');

				}

				$url = base_url();

				$data['url'] =  rtrim($url,"/admin");

				$data['categoryData'] =$this->CategoryNewModel->getSingleDataByID('category',array('id'=>$category_id),'*');

				

			}

			$data['PageTitle']='Add Category';

			$data['side_menu']='category';

			$data['browse_category'] = $this->CategoryNewModel->getAllCategories();



			$this->load->view('category/category_add',$data);

		}else{

			return redirect('/'); 

		}

	}



	public function submitCategory()

	{

		

		$LoginID=$this->session->userdata('LoginID');

		if($LoginID){

			if(empty($_POST))

			{

				echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));

				exit;

			}else{

				$categoryName = $this->CommonModel->custom_filter_input($_POST['categoryName']);

				

				$category_id = $_POST['category_id'];

				if($category_id != '')

				{

					$categoryName = $this->CommonModel->custom_filter_input($_POST['categoryName']);

					$categorySlug = $categoryName;

					$categorySlug = str_replace(" ", "-", trim($categorySlug));

					$categorySlug = preg_replace('/[^A-Za-z0-9-]/', '', $categorySlug);

					$categorySlug = strtolower($categorySlug);

					$category_exist_count = $this->CategoryNewModel->checkSlugExistItself($categorySlug,$category_id);

					if (sizeof($category_exist_count) > 0) {

						echo json_encode(array('flag' => 0, 'msg' => "Category Name Already Exist"));

						exit;

					}

					$catImage =$this->CategoryNewModel->getSingleDataByID('category',array('id'=>$category_id),'cat_image');

					$imageName=$catImage->cat_image;

					if(isset($_FILES['customFil']['name']) && !empty($_FILES['customFil']['name']))

					{	

						$config['upload_path'] = SIS_SERVER_PATH.'/'.'uploads/categories/';

						$config['allowed_types'] = 'jpg|jpeg|png';

						$config['encrypt_name'] = true;

						$config['detect_mime'] = true;

						$this->load->library('upload', $config);

						$this->upload->initialize($config);



						if (!$this->upload->do_upload('customFil')) {

							$imageName='';

							



						} else {

						

							$cat_img = $this->upload->data();

							$imageName = $cat_img['file_name'];

						}

					}



					$updateData=array(  

						'cat_name'    		=> $categoryName,

						'lang_title' => isset($_POST['langTitle']) ? $this->CommonModel->custom_filter_input($_POST['langTitle']) : '',

						'cat_description'	=> $_POST['categoryDesc'],

						'meta_title'	=> $_POST['meta_title'],

						'meta_keyword'	=> $_POST['meta_keyword'],

						'meta_description'	=> $_POST['meta_description'],

						'cat_image'			=> $imageName,

						'status'			=> $_POST['status'],

						'updated_at'		=> strtotime(date('Y-m-d H:i:s')),

						'ip'				=> $_SERVER['REMOTE_ADDR'],

					);



					$this->db->where(array('id' => $category_id));

					$afftedRow = $this->db->update('category', $updateData);

					if($afftedRow){

						echo json_encode("update");

                            exit;

						// $url = base_url().'category';

						// echo json_encode(array('flag' => 1, 'msg' => "Updated successfully",'url'=>$url));

						// exit();

					}else{

						echo json_encode(array('flag' => 0, 'msg' => "went something wrong!"));

						exit;

					}

				}else{

					

					//insert

					if(empty($_POST['categoryName'])){

						echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));

						exit;

					}else if(empty($_POST['imageName'])){

						echo json_encode(array('flag'=>0, 'msg'=>"Please upload image / compulsory fields."));

						exit;



					}else{

						$categoryName = $this->CommonModel->custom_filter_input($_POST['categoryName']);

						$categorySlug = $categoryName;

						$categorySlug = str_replace(" ", "-", trim($categorySlug));

						$categorySlug = preg_replace('/[^A-Za-z0-9-]/', '', $categorySlug);

						$categorySlug = strtolower($categorySlug);

						$category_exist_count = $this->CategoryNewModel->checkSlugExist($categorySlug);

						if (sizeof($category_exist_count) > 0) {

							echo json_encode(array('flag' => 0, 'msg' => "Category Name Already Exist"));

							exit;

						}



						$category_type = $_POST['category_type'];

						$parent_id = 0;

						$main_parent_id = 0;

						$cat_level = 0;



						if(isset($category_type) && $category_type != 0){

							$parent_id = $category_type;

							$main_parent_id = $category_type;

							$check_cat_level = $this->CategoryNewModel->check_cat_level($category_type);

							if($check_cat_level->cat_level == 0){

								$cat_level = 1;

								$parent_id = $check_cat_level->id;

								$main_parent_id = $check_cat_level->id;

							}elseif($check_cat_level->cat_level == 1){

								$cat_level = 2;

								$parent_id = $check_cat_level->id;

								$main_parent_id = $check_cat_level->main_parent_id;

							}else{

								$cat_level = 3;

								$parent_id = $check_cat_level->id;

								$main_parent_id = $check_cat_level->main_parent_id;

							}

						}else{

							$parent_id = 0;	

							$main_parent_id = 0;

						}



					$imageName='';

					if(isset($_FILES['customFil']['name']) && !empty($_FILES['customFil']['name']))

					{	

						$config['upload_path'] = SIS_SERVER_PATH.'/'.'uploads/categories/';

						$config['allowed_types'] = 'jpg|jpeg|png';

						$config['encrypt_name'] = true;

						$config['detect_mime'] = true;

						$this->load->library('upload', $config);

						$this->upload->initialize($config);

						if (!$this->upload->do_upload('customFil')) {

							$imageName='';

						} else {

							$logo_imgdata = $this->upload->data();

							$imageName = $logo_imgdata['file_name'];

						}

					}

						$insertData=array(  

							'cat_name'    		=> $categoryName,
							
							'lang_title' => isset($_POST['langTitle']) ? $this->CommonModel->custom_filter_input($_POST['langTitle']) : '',

							'slug'				=> $categorySlug,

							'cat_description'	=> $_POST['categoryDesc'],

							'meta_title'	=> $_POST['meta_title'],

							'meta_keyword'	=> $_POST['meta_keyword'],

							'meta_description'	=> $_POST['meta_description'],

							'parent_id'	=> $parent_id,

							'main_parent_id'	=> $main_parent_id,

							'cat_level'	=> $cat_level,

							'cat_image'	=> $imageName,

							'status'	=> $_POST['status'],

							// 'created_by_type' 	=> 0,

							'created_at'		=> strtotime(date('Y-m-d H:i:s')),

							'ip'				=> $_SERVER['REMOTE_ADDR'],

						);

						$this->db->insert('category', $insertData);

						$insert_id = $this->db->insert_id();

						if($insert_id){

							echo json_encode("success");

                            exit;

							// $url = base_url().'category';

							// echo json_encode(array('flag' => 1, 'msg' => "Successfully","url"=>$url));

							// exit;

						}else{

							echo json_encode(array('flag' => 0, 'msg' => "went something wrong!"));

							exit;

						}

					}

					

				}

			}	

			echo json_encode(array('flag' => 1,'msg' => "Update Successfully!!"));

			exit();

		}else{

			return redirect('/'); 

		}

	}





	



	// function getAjaxAttr()

	// {

	// 	$result = $this->CategoryModel->getAttribute();

	// 	echo json_encode($result);

	// 	exit();

	// }



	// function getAjaxAttrData()

	// {

	// 	$attr= $_GET['attr_id'];

	// 	$result = $this->AttributeModel->getAttributeById($attr);

	// 	$attributevalues = $this->AttributeModel->getAttributeValues($attr);

	// 	echo json_encode(array('result'=>$result, 'attributevalues'=>$attributevalues));

	// 	exit();

	// }



	// function getAjaxVarint()

	// {

	// 	$result = $this->CategoryModel->getVariant();

	// 	echo json_encode($result);

	// 	exit();

	// }



	// function getAjaxVarintData()

	// {

	// 	$variant= $_GET['variant_id'];

	// 	$result = $this->VariantModel->getVariantById($variant);

	// 	$variantvalues = $this->VariantModel->getVariantValues($variant);

	// 	echo json_encode(array('result'=>$result, 'variantvalues'=>$variantvalues));

	// 	exit();

	// }





	// function getSubCatAjaxCalog()

	// {

	// 	$subCategoryId= $_GET['subCategoryId'];

	// 	$result = $this->CategoryModel->geSubCategoryTagData($subCategoryId);

	// 	echo json_encode(array('data'=>$result));

	// 	exit();

	// }







	public function bulkUpload()
	{
		if (isset($_SESSION['UserRole']) && $_SESSION['UserRole'] !== 'Super Admin') {
			if (!empty($this->session->userdata('userPermission')) && !in_array('database/category', $this->session->userdata('userPermission'))) {
				redirect('/admin/category');
			}
		}

		$LoginID = $this->session->userdata('LoginID');
		if ($LoginID) {
			$data['PageTitle'] = 'Category - Bulk Upload';
			$data['side_menu'] = 'category';
			$data['sub_menu'] = 'category_bulk_upload';

			// Fetch upload history
			$this->db->select('h.*, CONCAT(u.first_name, " ", u.last_name) as admin_name, u.username as admin_username');
			$this->db->from('category_bulk_upload_history h');
			$this->db->join('adminusers u', 'u.id = h.created_by', 'left');
			$this->db->order_by('h.id', 'DESC');
			$this->db->limit(50);
			$data['history'] = $this->db->get()->result_array();

			$this->load->view('category/category_bulk_upload', $data);
		} else {
			return redirect('/');
		}
	}

	public function downloadSampleTemplate($format = 'csv')
	{
		$this->load->library('CategoryBulkImporter');
		$csvContent = $this->categorybulkimporter->generateSampleCsv();

		$filename = 'category_bulk_upload_sample.csv';
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
			if (!empty($this->session->userdata('userPermission')) && !in_array('database/category', $this->session->userdata('userPermission'))) {
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

		$duplicateAction = $this->input->post('duplicate_action') ?: 'skip';
		if (!in_array($duplicateAction, ['skip', 'update', 'stop'], true)) {
			$duplicateAction = 'skip';
		}

		$tempPath = $_FILES['bulk_file']['tmp_name'];
		$this->load->library('CategoryBulkImporter');

		$parseResult = $this->categorybulkimporter->parseFile($tempPath, $originalName);
		if (!$parseResult['status']) {
			echo json_encode($parseResult);
			exit;
		}

		$validationResult = $this->categorybulkimporter->validateRecords($parseResult['rows'], $duplicateAction);

		// Store validated rows and error reports temporarily in session
		$this->session->set_userdata('category_bulk_rows', $validationResult['rows']);
		$this->session->set_userdata('category_bulk_duplicate_action', $duplicateAction);
		$this->session->set_userdata('category_bulk_filename', $originalName);

		echo json_encode([
			'status'           => true,
			'duplicate_action' => $duplicateAction,
			'filename'         => $originalName,
			'summary'          => $validationResult['summary'],
			'rows'             => $validationResult['rows']
		]);
		exit;
	}

	public function importBulkCategories()
	{
		header('Content-Type: application/json');

		if (isset($_SESSION['UserRole']) && $_SESSION['UserRole'] !== 'Super Admin') {
			if (!empty($this->session->userdata('userPermission')) && !in_array('database/category', $this->session->userdata('userPermission'))) {
				echo json_encode(['status' => false, 'message' => 'Permission denied.']);
				exit;
			}
		}

		$validatedRows = $this->session->userdata('category_bulk_rows');
		$duplicateAction = $this->input->post('duplicate_action') ?: $this->session->userdata('category_bulk_duplicate_action') ?: 'skip';
		$fileName = $this->session->userdata('category_bulk_filename') ?: 'category_import.csv';

		if (empty($validatedRows) || !is_array($validatedRows)) {
			echo json_encode([
				'status'  => false,
				'message' => 'No validated category data found to import. Please upload and validate a file first.'
			]);
			exit;
		}

		$adminId = (int)$this->session->userdata('LoginID');
		$ip = $this->input->ip_address();

		$this->load->library('CategoryBulkImporter');
		$importResult = $this->categorybulkimporter->importRecords($validatedRows, $duplicateAction, $fileName, $adminId, $ip);

		// Clear temporary session data
		$this->session->unset_userdata('category_bulk_rows');
		$this->session->unset_userdata('category_bulk_duplicate_action');
		$this->session->unset_userdata('category_bulk_filename');

		if ($importResult['status']) {
			// Save last errors in session for quick download if needed
			$this->session->set_userdata('last_import_errors', $importResult['errors'] ?? []);
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
				->from('category_bulk_upload_history')
				->where('id', $historyId)
				->get()
				->row_array();

			if ($record && !empty($record['error_log'])) {
				$errors = json_decode($record['error_log'], true) ?: [];
			}
		}

		if (empty($errors)) {
			$errors = $this->session->userdata('last_import_errors') ?: [];
		}

		if (empty($errors)) {
			$errors = [
				[
					'row'      => '-',
					'category' => '-',
					'slug'     => '-',
					'type'     => 'Info',
					'message'  => 'No errors logged for this import session.'
				]
			];
		}

		$this->load->library('CategoryBulkImporter');
		$csvContent = $this->categorybulkimporter->generateErrorReportCsv($errors);

		$filename = 'category_import_error_report_' . date('Y-m-d_His') . '.csv';
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
			$this->db->where('id', $historyId)->delete('category_bulk_upload_history');
			echo json_encode(['status' => true, 'message' => 'History record deleted successfully.']);
			exit;
		}
		echo json_encode(['status' => false, 'message' => 'Invalid history ID.']);
		exit;
	}

}

