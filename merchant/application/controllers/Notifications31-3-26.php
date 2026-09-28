<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notifications extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Notification_model');
        $this->load->model('UserModel');
        $this->load->helper(['url', 'form']);
        $this->load->library('session'); // assuming merchant id stored in session


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
        // Example: your merchant ID from session
        $merchant_id = $this->session->userdata('LoginID');
        $data['notifications'] = $this->Notification_model->get_for('merchant', $merchant_id, 200);
        // echo "<pre>";print_r($data);die;
        $data['unread_count'] = $this->Notification_model->unread_count('merchant', $merchant_id);

       // $this->load->view('common/header'); // optional
        $this->load->view('notifications/index', $data);
       // $this->load->view('common/footer'); // optional
    }

    // Mark notification read/unread via POST (AJAX)
    /*public function mark()
    {
        $id = $this->input->post('id');
        $state = $this->input->post('is_read') ? 1 : 1; // default to mark read
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Missing id']);
            return;
        }

        $ok = $this->Notification_model->mark_read($id, $state);
        echo json_encode(['status' => $ok ? 'ok' : 'error']);
    }*/

    public function mark()
    {
        $this->load->model('Notification_model');

        // Read POST
        $id = $this->input->post('id');
        $is_read = $this->input->post('is_read');

        // Normalize value
        $is_read = ($is_read === '0' || $is_read === 0 || $is_read === 'false') ? 0 : 1;

        if (empty($id)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Missing id']));
        }

        // Attempt to update using model method
        $result = $this->Notification_model->mark_read((int)$id, (int)$is_read);

        // For debugging, return last query and affected rows (remove in production)
        $last_query = $this->db->last_query();
        $affected = $this->db->affected_rows();

        if ($result) {
            $out = ['status' => 'ok', 'affected_rows' => $affected, 'last_query' => $last_query];
        } else {
            // Even if model returned false, still return debug info
            $out = ['status' => 'error', 'message' => 'update_failed', 'affected_rows' => $affected, 'last_query' => $last_query];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($out));
    }

    public function mark_all()
    {
        $this->load->model('Notification_model');

        // get merchant ID from session
        $merchant_id = $this->session->userdata('LoginID');

        if (empty($merchant_id)) {
            echo json_encode([
                "status" => "error",
                "message" => "missing_recipient_id",
                "merchant_id_detected" => null,
                "session_preview" => $this->session->userdata()
            ]);
            return;
        }

        // Use model method — this handles global + merchant notifications
        $affected = $this->Notification_model->mark_all_read('merchant', $merchant_id);

        // Get unread count after update (optional)
        $this->load->model('Notification_model');
        $unread = $this->Notification_model->get_unread_count('merchant', $merchant_id); // you may need to create this method

        echo json_encode([
            "status" => "ok",
            "affected_rows" => $affected,
            "merchant_id" => $merchant_id,
            "unread_count" => $unread,
            "last_query" => $this->db->last_query()
        ]);
    }



    // Optionally: API endpoint to fetch the latest notifications (for header popup)
    public function latest()
    {
        $merchant_id = $this->session->userdata('merchant_id');
        $rows = $this->Notification_model->get_for('merchant', $merchant_id, 10);
        echo json_encode($rows);
    }
}
