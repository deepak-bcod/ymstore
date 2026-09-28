<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notifications extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Notification_model');
        $this->load->helper(['url','form']);
        $this->load->library('session');
        // load language as needed...
    }

    public function index()
    {
        // detect admin id in session (adjust keys to your app)
        $admin_id = $this->session->userdata('admin_id') ?: $this->session->userdata('LoginID') ?: $this->session->userdata('user_id');

        $data['notifications'] = $this->Notification_model->get_for('admin', $admin_id, 200);
        $data['unread_count'] = $this->Notification_model->unread_count('admin', $admin_id);

        $this->load->view('notifications/index', $data);
    }

    // Mark single notification read/unread via POST (AJAX)
    public function mark()
    {
        $id = $this->input->post('id');
        $is_read = $this->input->post('is_read');
        $is_read = ($is_read === '0' || $is_read === 0 || $is_read === 'false') ? 0 : 1;

        if (empty($id)) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(['status'=>'error','message'=>'Missing id']));
        }

        $ok = $this->Notification_model->mark_read((int)$id, (int)$is_read);
        return $this->output->set_content_type('application/json')->set_output(json_encode(['status'=> $ok ? 'ok' : 'error']));
    }

    // Mark all read for admin
    public function mark_all()
    {
        $admin_id = $this->session->userdata('admin_id') ?: $this->session->userdata('LoginID') ?: $this->session->userdata('user_id');

        if (empty($admin_id)) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(['status'=>'error','message'=>'missing_recipient_id_in_session','session_preview'=>$this->session->userdata()]));
        }

        $affected = $this->Notification_model->mark_all_read('admin', (int)$admin_id);
        $unread = $this->Notification_model->get_unread_count('admin', (int)$admin_id);

        return $this->output->set_content_type('application/json')->set_output(json_encode([
            'status'=>'ok',
            'affected_rows' => (int)$affected,
            'unread_count' => (int)$unread,
            'merchant_id' => (int)$admin_id,
            'last_query' => $this->db->last_query()
        ]));
    }

    // Latest for header popup
    public function latest()
    {
        $admin_id = $this->session->userdata('admin_id') ?: $this->session->userdata('LoginID') ?: $this->session->userdata('user_id');
        $rows = $this->Notification_model->get_for('admin', $admin_id, 10, 0, true); // only_unread true
        echo json_encode($rows);
    }
}
