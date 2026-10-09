<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mydocuments extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Mydocuments_model');
        $this->load->library('session');
        if (!isset($_SESSION['LoginID']) || $_SESSION['LoginID'] == '') {
            redirect(BASE_URL);
        }

        $site_lang = $this->session->userdata('site_lang');

		if ($site_lang) {
			$this->lang->load('content', $site_lang);
		} else {
			$this->lang->load('content', 'english'); // default
		}
    }

    // List page
    public function index()
    {
        $publisher_id = $this->session->userdata('LoginID');
        $mydocument = $this->Mydocuments_model->get_mydocuments($publisher_id);
        // echo "<pre>";print_r($mydocument);die;
        $this->load->view('mydocuments/mydocumentsList', array('mydocument' => $mydocument));
    }

    public function add()
    {
        $publisher_id = $this->session->userdata('LoginID');
        $this->load->view('mydocuments/add_mydocument');
    }

    public function insert()
    {
        $publisher_id = $this->session->userdata('LoginID');
        $document_name = $this->input->post('document_name', true);
        if (empty($document_name)) {
            $arrResponse = [
                'status'  => 400,
                'message' => 'Document name is required.'
            ];
            echo json_encode($arrResponse);
            exit;
        }


        // ===== Handle File Upload =====
        if (!empty($_FILES['document_file']['name'])) {
            $config['upload_path']   = SIS_SERVER_PATH . '/' . 'uploads/documents/';
            $config['allowed_types'] = 'doc|docx|jpg|jpeg|png|pdf|xlsx';

            $config['max_size']      = 5120; // 5 MB

            // create folder if not exists
            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0777, true);
            }

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('document_file')) {
                $uploadData   = $this->upload->data();
                $document_file = $uploadData['file_name'];
            } else {
                $arrResponse = array('status' => 400, 'message' => $this->upload->display_errors());
                echo json_encode($arrResponse);
                exit;
            }
        }

        // print_r($document_file); die;


        // ===== Insert into DB =====
        $insert = [
            'merchant_id'   => $publisher_id,
            'document_name' => $document_name,
            'document_file' => $document_file,
            'created_at'    => time(),
            'ip' => $_SERVER['REMOTE_ADDR']
        ];

        $this->db->insert('mydocuments', $insert);

        $arrResponse = [
            'status'  => 200,
            'message' => 'Document added successfully.',
            'redirect_url' => base_url('mydocuments') // 👈 redirect target
        ];

        echo json_encode($arrResponse);
        exit;
    }

    // Service details page
    public function edit($id)
    {
        $data = $this->Mydocuments_model->get_mydocuments_data($id);
        // echo "<pre>";print_r($data);die;

        $this->load->view('mydocuments/edit_mydocument', array('data' => $data));
    }
    public function update($id)
    {
        $publisher_id  = $this->session->userdata('LoginID');
        $document_name = trim($this->input->post('document_name', true));

        // ✅ Server-side validation: check if name is empty
        if (empty($document_name)) {
            $arrResponse = [
                'status'  => 400,
                'message' => 'Document name is required.'
            ];
            echo json_encode($arrResponse);
            exit;
        }

        // Get the old record first
        $oldDoc = $this->Mydocuments_model->get_mydocuments_data($id);
        $document_file = $oldDoc->document_file; // keep old file by default

        // ===== Handle File Upload (if new file uploaded) =====
        if (!empty($_FILES['document_file']['name'])) {
            $config['upload_path']   = SIS_SERVER_PATH . '/' . 'uploads/documents/';
            $config['allowed_types'] = 'doc|docx|jpg|jpeg|png|pdf|xlsx';
            $config['max_size']      = 5120; // 5 MB

            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0777, true);
            }

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('document_file')) {
                $uploadData   = $this->upload->data();
                $document_file = $uploadData['file_name']; // replace with new file
            } else {
                $arrResponse = [
                    'status' => 400,
                    'message' => strip_tags($this->upload->display_errors())
                ];
                echo json_encode($arrResponse);
                exit;
            }
        }

        // ===== Update DB =====
        $update = [
            'merchant_id'   => $publisher_id,
            'document_name' => $document_name,
            'document_file' => $document_file, // either new file OR old one
            'updated_at'    => time(),
            'ip'            => $_SERVER['REMOTE_ADDR']
        ];

        $this->db->where('id', $id)->update('mydocuments', $update);

        $arrResponse = [
            'status'  => 200,
            'message' => 'Document updated successfully.',
            'redirect_url' => base_url('mydocuments')
        ];

        echo json_encode($arrResponse);
        exit;
    }


    public function messaging()
    {
        $publisher_id = $this->session->userdata('LoginID');
        $data['messaging'] = $this->CommonModel->get_messaging($publisher_id);
        $this->load->view('messaging_list', $data);
    }


    public function messages_view($product_id, $customer_id)
    {
        $publisher_id = $this->session->userdata('LoginID');

        $this->db->from('product_questions');
        $this->db->where('product_id', $product_id);
        $this->db->where('merchant_id', $publisher_id);
        $this->db->where('customer_id', $customer_id);
        $this->db->order_by('id', 'ASC');
        $product_questions_data = $this->db->get()->result();

        $data['product_questions_data'] = $product_questions_data;
        $data['product_id'] = $product_id;
        $data['customer_id'] = $customer_id;

        $this->load->view('messaging_conversation', $data);
    }

    
public function update_messaging()
{
    $publisher_id   = (int) $this->session->userdata('LoginID');
    $product_id     = (int) $this->input->post('product_id');
    $customer_id    = (int) $this->input->post('customer_id');
    $merchant_reply = trim((string) $this->input->post('merchant_reply'));

    // STEP 1: Validate request
    if (
        $publisher_id <= 0 ||
        $product_id <= 0 ||
        $customer_id <= 0 ||
        $merchant_reply === ''
    ) {
        $this->session->set_flashdata(
            'error',
            'Invalid request or reply cannot be empty.'
        );

        redirect(
            'Mydocuments/messages_view/' .
            $product_id . '/' . $customer_id
        );
        return;
    }

    // STEP 2: Get the shopper's original question
    $this->db->from('product_questions');
    $this->db->where('product_id', $product_id);
    $this->db->where('merchant_id', $publisher_id);
    $this->db->where('customer_id', $customer_id);
    $this->db->where('message !=', '');
    $this->db->order_by('id', 'ASC');
    $this->db->limit(1);

    $question = $this->db->get()->row();

    if (!$question) {
        $this->session->set_flashdata(
            'error',
            'Question not found.'
        );

        redirect(
            'Mydocuments/messages_view/' .
            $product_id . '/' . $customer_id
        );
        return;
    }

    $now = date('Y-m-d H:i:s');

    // STEP 3: Save the merchant's reply
    if (empty($question->merchant_reply)) {

        // Update the original question if it has no reply yet.
        $this->db->where('id', (int) $question->id);
        $this->db->where('product_id', $product_id);
        $this->db->where('merchant_id', $publisher_id);
        $this->db->where('customer_id', $customer_id);

        $this->db->update('product_questions', [
            'merchant_reply' => $merchant_reply,
            'updated_at'     => $now
        ]);

        $saved = ($this->db->affected_rows() > 0);

    } else {

        // Insert a new reply if the original question
        // already has a merchant reply.
        $this->db->insert('product_questions', [
            'product_id'     => $product_id,
            'merchant_id'    => $publisher_id,
            'customer_id'    => $customer_id,
            'name'           => $question->name,
            'category'       => $question->category,
            'message'        => '',
            'merchant_reply' => $merchant_reply,
            'created_at'     => $now,
            'updated_at'     => $now
        ]);

        $saved = ($this->db->affected_rows() > 0);
    }

    // STEP 4: Stop if the reply was not saved
    if (!$saved) {
        $this->session->set_flashdata(
            'error',
            'Unable to save the reply.'
        );

        redirect(
            'Mydocuments/messages_view/' .
            $product_id . '/' . $customer_id
        );
        return;
    }

    // STEP 5: Create shopper notification
    $notification_data = [
        'question_id' => (int) $question->id,
        'product_id'  => $product_id,
        'customer_id' => $customer_id,
        'merchant_id' => $publisher_id
    ];

    $notification = [
        'type'           => 'product_question',
        'subtype'        => 'merchant_reply',
        'recipient_type' => 'shopper',
        'recipient_id'   => $customer_id,
        'title'          => 'Ask a Question',
        'message'        => 'You got a reply to your Ask a Question message.',
        'data'           => json_encode($notification_data),
        'is_read'        => 0,
        'created_at'     => $now,
        'updated_at'     => $now
    ];

    $this->db->insert('notifications', $notification);

    // STEP 6: Show result and return to conversation
    $this->session->set_flashdata(
        'success',
        'Reply sent successfully!'
    );

    redirect(
        'Mydocuments/messages_view/' .
        $product_id . '/' . $customer_id
    );
}


}
