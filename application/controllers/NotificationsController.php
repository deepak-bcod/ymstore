<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class NotificationsController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('NotificationModel');
    }

    /**
     * Full notifications page
     */
    public function index()
    {
        $customer_id = $this->session->userdata('customer_id');

        if (!$customer_id) {
            redirect('customer/login');
            return;
        }

        $data['notifications'] = $this->NotificationModel
            ->get_customer_notifications($customer_id);

        $data['unread_count'] = $this->NotificationModel
            ->get_unread_count($customer_id);

        $this->load->view('notifications/index', $data);
    }


    /**
     * Latest 3 notifications for bell dropdown
     */
    public function latest()
    {
        $customer_id = $this->session->userdata('customer_id');

        if (!$customer_id) {
            echo json_encode([
                'status' => false,
                'message' => 'Please login'
            ]);
            return;
        }

        $notifications = $this->NotificationModel
            ->get_latest_notifications($customer_id, 3);

        $unread_count = $this->NotificationModel
            ->get_unread_count($customer_id);

        echo json_encode([
            'status' => true,
            'notifications' => $notifications,
            'unread_count' => $unread_count
        ]);
    }


    /**
     * Mark notification as read
     */
    public function mark_read($notification_id)
    {
        $customer_id = $this->session->userdata('customer_id');

        if (!$customer_id) {
            echo json_encode([
                'status' => false
            ]);
            return;
        }

        $result = $this->NotificationModel
            ->mark_as_read($notification_id, $customer_id);

        echo json_encode([
            'status' => $result
        ]);
    }
}
