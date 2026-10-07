<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Ensure user/merchant is logged in before viewing notifications
        if (!$this->session->userdata('LoginID')) {
            redirect('customer/login'); // Or your login path
        }
        $this->load->model('Notification_model');
    }

    // View all notifications (table list page)
    public function index() {
        $user_id = $this->session->userdata('LoginID');

        $data['notifications'] = $this->Notification_model->get_notifications($user_id);

        // Load header, view, and footer (adjust paths as per your project layout)
        $this->load->view('frontend/header', [
            'notification_count' => $this->Notification_model->get_unread_count($user_id),
            'top_notifications'  => $this->Notification_model->get_latest_notifications($user_id, 3)
        ]);
        $this->load->view('frontend/notification/index', $data);
        $this->load->view('frontend/footer');
    }

    // Mark single notification as read
    public function mark_read($id) {
        $this->Notification_model->mark_as_read($id);
        redirect('notification');
    }

    // Mark all notifications as read
    public function mark_all_read() {
        $user_id = $this->session->userdata('LoginID');
        $this->Notification_model->mark_all_as_read($user_id);
        redirect('notification');
    }
}