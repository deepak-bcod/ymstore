<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MerchantFaqs extends CI_Controller {

    public function __construct() {
        parent::__construct();
       
        if(!$this->session->userdata('LoginID')){
            redirect('logout');
        }
        $this->load->model('FaqModel');
 
        $site_lang = $this->session->userdata('site_lang');
 
        if ($site_lang) {
            $this->lang->load('content', $site_lang);
			$this->lang->load('datatables', $site_lang);
        } else {
            $this->lang->load('content', 'english'); // default
        }
    }
    
    public function index() {
        
        $this->load->vars(['side_menu' => 'merchantfaqs']);
        
        $merchant_id = $this->session->userdata('LoginID');
        $this->load->model('UserModel');
        $user = $this->UserModel->getUserByMerchantId($merchant_id);
        $merchant_email = (!empty($user) && !empty($user->email)) ? $user->email : $this->session->userdata('EmailID');

        $data['faq_list'] = $this->FaqModel->get_merchant_own_faqs($merchant_email, 'Merchant');
        //echo "<pre>";print_r($data);die;
        $this->load->view('faqs', $data);
    }

    public function add()
    {
        $this->load->vars(['side_menu' => 'merchantfaqs']);

        $merchant_id = $this->session->userdata('LoginID');

        $this->load->model('UserModel');

        $user = $this->UserModel->getUserByMerchantId($merchant_id);

        $merchant_name = '';
        $merchant_email = '';

        if (!empty($user)) {

            if (!empty($user->vendor_name)) {
                $merchant_name = $user->vendor_name;
            }

            if (!empty($user->email)) {
                $merchant_email = $user->email;
            }
        }

        // Fallback only for email
        if (empty($merchant_email)) {
            $merchant_email = $this->session->userdata('EmailID');
        }

        $data['merchant_name']  = $merchant_name;
        $data['merchant_email'] = $merchant_email;

        $this->load->view('faqsadd', $data);
    }
    
    public function save()
{
    $this->output->set_content_type('application/json');

    $question = $this->input->post('question', TRUE);
    $question_fr = $this->input->post('question_fr', TRUE);

    $merchant_id = $this->session->userdata('LoginID');

    $this->load->model('UserModel');

    $user = $this->UserModel->getUserByMerchantId($merchant_id);

    $merchant_name = (!empty($user) && !empty($user->vendor_name))
        ? $user->vendor_name
        : 'Merchant';

    $merchant_email = (!empty($user) && !empty($user->email))
        ? $user->email
        : $this->session->userdata('EmailID');

    if (empty($merchant_email)) {
        echo json_encode([
            'status'  => 'error',
            'message' => $this->lang->line('merchant_email_not_found')
        ]);
        return;
    }

    $insert_data = [
        'name'        => $merchant_name,
        'email'       => $merchant_email,
        'question'    => $question,
        'question_fr' => $question_fr,
        'answer'      => NULL,
        'faq_type'    => 'Merchant',
        'status'      => 0,
        'created_at'  => time(),
        'ip'          => $this->input->ip_address()
    ];

    if ($this->FaqModel->insert_faq($insert_data)) {

        // Admin notification
        $notification_data = [
            'type'           => 'faq',
            'subtype'        => 'new_faq_request',
            'recipient_type' => 'admin',
            'recipient_id'   => NULL,

            'title'          => 'New FAQ Request',

            'message'        => 'New FAQ added by ' . $merchant_name . ' - Merchant',

            'data'           => [
                'merchant_id'    => $merchant_id,
                'merchant_name'  => $merchant_name,
                'merchant_email' => $merchant_email,
                'faq_type'       => 'Merchant'
            ],

            'is_read' => 0
        ];

        $notification_id = $this->Notification_model->insert($notification_data);

        echo json_encode([
            'status'  => 'success',
            'message' => $this->lang->line('faq_added_success')
        ]);

    } else {

        echo json_encode([
            'status'  => 'error',
            'message' => $this->lang->line('database_error')
        ]);
    }
}
}