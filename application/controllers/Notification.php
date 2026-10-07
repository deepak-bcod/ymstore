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
    // View all notifications (table list page)
    public function index() {
        $user_id = $this->session->userdata('LoginID');

        // Combine all view data into a single array
        $data['notifications'] = $this->Notification_model->get_notifications($user_id);
        $data['notification_count'] = $this->Notification_model->get_unread_count($user_id);
        $data['top_notifications'] = $this->Notification_model->get_latest_notifications($user_id, 3);

        // Load views with their correct folder paths based on your sidebar structure
        $this->load->view('common/header', $data); // Points to application/views/common/header.php
        $this->load->view('notification/index', $data); // Points to application/views/notification/index.php
        $this->load->view('common/footer'); // Points to application/views/common/footer.php
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