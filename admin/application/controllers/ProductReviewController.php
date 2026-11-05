<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ProductReviewController extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

        if ($this->session->userdata('LoginID') == '') {
            redirect(base_url());
        }

        $this->load->model('UserModel');
        $this->load->model('ProductReviewModel');
        $this->load->model('CommonModel');
        $this->load->library('encryption');
    }

    public function reviewlist()
    {
        if (isset($_SESSION['UserRole']) && $_SESSION['UserRole'] !== 'Super Admin') {
            if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/product_reviews', $this->session->userdata('userPermission'))) { 
                redirect('dashboard');
            }
        }

        if (isset($_SESSION['LoginID'])) {
            $data['PageTitle'] = 'Review Management';
            $data['side_menu'] = 'webshop';
            $data['current_tab'] = 'product_reviews';
            $data['status_counts'] = $this->ProductReviewModel->getStatusCounts();

            $this->load->view('product_review/product_review_main', $data);
        } else {
            redirect(base_url('customer/login'));
        }
    }

    public function ajaxreviewlist()
    {
        $draw = isset($_POST['draw']) ? (int)$_POST['draw'] : 1;
        $row = isset($_POST['start']) ? (int)$_POST['start'] : 0;
        $rowperpage = isset($_POST['length']) ? (int)$_POST['length'] : 50;

        $search_param = array();
        if (!empty($_POST['search']['value'])) {
            $search_param['keyword'] = trim($_POST['search']['value']);
        }

        $status_filter = isset($_POST['status_filter']) ? trim($_POST['status_filter']) : 'all';
        $type_filter = isset($_POST['type_filter']) && in_array($_POST['type_filter'], array('merchant', 'product', 'all')) ? trim($_POST['type_filter']) : 'merchant';

        $reviewList = $this->ProductReviewModel->getReviewsList($row, $rowperpage, $search_param, $status_filter, $type_filter);
        $totalRecords = $this->ProductReviewModel->getReviewsCount(array(), '', '');
        $totalRecordwithFilter = $this->ProductReviewModel->getReviewsCount($search_param, $status_filter, $type_filter);

        $data = array();

        if (!empty($reviewList)) {
            foreach ($reviewList as $r) {
                $type = $r['review_type'];
                $id = (int)$r['review_id'];
                $parent_id = (int)$r['parent_review_id'];
                $current_status = strtolower($r['status']);

                // Status Badge
                if ($current_status === 'published') {
                    $status_badge = '<span class="badge badge-success" style="background:#28a745;color:#fff;padding:6px 10px;font-size:12px;border-radius:4px;font-weight:600;"><i class="fa fa-check-circle"></i> Published</span>';
                } elseif ($current_status === 'rejected') {
                    $status_badge = '<span class="badge badge-danger" style="background:#dc3545;color:#fff;padding:6px 10px;font-size:12px;border-radius:4px;font-weight:600;"><i class="fa fa-times-circle"></i> Rejected</span>';
                } else {
                    $status_badge = '<span class="badge badge-warning" style="background:#ffc107;color:#212529;padding:6px 10px;font-size:12px;border-radius:4px;font-weight:600;"><i class="fa fa-clock"></i> Pending</span>';
                }

                // Type Badge
                if ($type === 'merchant') {
                    $type_badge = '<span class="badge" style="background:#17a2b8;color:#fff;padding:4px 8px;font-size:11px;border-radius:3px;">Merchant Service</span>';
                } else {
                    $type_badge = '<span class="badge" style="background:#6f42c1;color:#fff;padding:4px 8px;font-size:11px;border-radius:3px;">Product</span>';
                }

                // Stars display
                $rating_num = (int)$r['rating'];
                $stars_html = '<span style="color:#f59e0b;font-size:14px;white-space:nowrap;">';
                for ($s = 1; $s <= 5; $s++) {
                    $stars_html .= ($s <= $rating_num) ? '★' : '<span style="color:#d1d5db;">★</span>';
                }
                $stars_html .= '</span> <small style="font-weight:600;color:#333;">(' . $rating_num . '/5)</small>';

                // Review comments with ellipsis
                $comment_text = !empty($r['comments']) ? htmlspecialchars($r['comments']) : '<em style="color:#9ca3af;">No comments</em>';
                if (mb_strlen($r['comments'] ?? '') > 70) {
                    $comment_preview = '<span title="' . htmlspecialchars($r['comments']) . '">' . htmlspecialchars(mb_substr($r['comments'], 0, 70)) . '...</span>';
                } else {
                    $comment_preview = $comment_text;
                }

                // Created date
                $created_date = !empty($r['created_at']) ? date("d M Y, h:i A", strtotime($r['created_at'])) : '-';

                // Order link / display
                $order_display = !empty($r['B2B_order_id']) ? htmlspecialchars($r['B2B_order_id']) : ('#' . $r['order_id']);

                // Action buttons (Independent Publish / Reject / Pending)
                $action_btns = '<div class="btn-group btn-group-sm" role="group" style="gap:4px;">';
                if ($current_status !== 'published') {
                    $action_btns .= '<button type="button" class="btn btn-sm btn-outline-success" onclick="changeReviewStatus(\'' . $type . '\', ' . $id . ', \'published\')" title="Publish Review" style="padding:3px 8px;font-size:11px;font-weight:600;"><i class="fa fa-check"></i> Publish</button>';
                }
                if ($current_status !== 'rejected') {
                    $action_btns .= '<button type="button" class="btn btn-sm btn-outline-danger" onclick="changeReviewStatus(\'' . $type . '\', ' . $id . ', \'rejected\')" title="Reject Review" style="padding:3px 8px;font-size:11px;font-weight:600;"><i class="fa fa-times"></i> Reject</button>';
                }
                if ($current_status !== 'pending') {
                    $action_btns .= '<button type="button" class="btn btn-sm btn-outline-warning text-dark" onclick="changeReviewStatus(\'' . $type . '\', ' . $id . ', \'pending\')" title="Mark as Pending" style="padding:3px 8px;font-size:11px;font-weight:600;"><i class="fa fa-clock"></i> Pending</button>';
                }
                $action_btns .= '</div>';

                // View Details Button
                $view_btn = '<button type="button" class="btn btn-sm btn-info text-white" onclick="viewOrderReviews(' . $parent_id . ')" title="View Order Reviews" style="padding:4px 10px;font-size:12px;"><i class="fa fa-eye"></i> View</button>';

                // Delete button
                if (empty($this->session->userdata('userPermission')) || in_array('webshop/product_reviews/write', $this->session->userdata('userPermission'))) {
                    $delete_btn = '<a href="javascript:void(0)" onclick="deleteReviewItem(\'' . $type . '\', ' . $id . ')" class="text-danger" title="Delete Review"><i class="fa fa-trash-alt"></i></a>';
                } else {
                    $delete_btn = '-';
                }

                $data[] = array(
                    "id"            => '<input type="checkbox" name="chk_review[]" data-type="' . $type . '" value="' . $id . '">',
                    "order_id"      => '<strong style="color:#007bff;">' . $order_display . '</strong>',
                    "type"          => $type_badge,
                    "target_name"   => '<strong>' . htmlspecialchars($r['target_name']) . '</strong>',
                    "customer_name" => htmlspecialchars($r['customer_name']),
                    "email_id"      => htmlspecialchars($r['customer_email']),
                    "rating"        => $stars_html,
                    "review"        => $comment_preview,
                    "created_at"    => '<small>' . $created_date . '</small>',
                    "status"        => $status_badge,
                    "action"        => $action_btns,
                    "view_url"      => $view_btn,
                    "delete"        => $delete_btn
                );
            }
        }

        $response = array(
            "draw"            => intval($draw),
            "iTotalRecords"   => intval($totalRecords),
            "iTotalDisplayRecords" => intval($totalRecordwithFilter),
            "aaData"          => $data
        );

        echo json_encode($response);
        exit;
    }

    public function change_reviews_status()
    {
        $type = $this->input->post('type');
        $id = (int)$this->input->post('id');
        $status = strtolower(trim((string)$this->input->post('status')));

        // Support legacy 1/0 status conversion if received
        if ($status === '1') {
            $status = 'published';
        } elseif ($status === '0') {
            $status = 'rejected';
        }

        if (empty($type)) {
            $type = 'product';
        }

        if (empty($id) || !in_array($status, array('pending', 'published', 'rejected'))) {
            echo json_encode(array("flag" => 0, "msg" => "Invalid parameters provided."));
            exit;
        }

        $updated = $this->ProductReviewModel->updateReviewStatus($type, $id, $status);

        if ($updated) {
            echo json_encode(array(
                "flag"   => 1,
                "msg"    => ucfirst($type) . " review status updated to " . ucfirst($status) . " successfully.",
                "status" => $status,
                "counts" => $this->ProductReviewModel->getStatusCounts()
            ));
        } else {
            echo json_encode(array("flag" => 0, "msg" => "Failed to update review status."));
        }
        exit;
    }

    public function viewOrderReviews()
    {
        $review_id = (int)$this->input->post('review_id');
        if (empty($review_id)) {
            echo json_encode(array("flag" => 0, "msg" => "Invalid review ID."));
            exit;
        }

        $details = $this->ProductReviewModel->getOrderReviewDetails($review_id);
        if (empty($details)) {
            echo json_encode(array("flag" => 0, "msg" => "Review details not found."));
            exit;
        }

        $data['details'] = $details;
        $html = $this->load->view('product_review/popup-data', $data, true);

        echo json_encode(array("flag" => 1, "data" => $html));
        exit;
    }

    public function delete_review()
    {
        $type = $this->input->post('type');
        $id = (int)$this->input->post('id');

        if (empty($id) || empty($type)) {
            echo json_encode(array("flag" => 0, "msg" => "Invalid parameters."));
            exit;
        }

        $deleted = $this->ProductReviewModel->deleteReview($type, $id);
        if ($deleted) {
            echo json_encode(array(
                "flag"   => 1,
                "msg"    => ucfirst($type) . " review deleted successfully.",
                "counts" => $this->ProductReviewModel->getStatusCounts()
            ));
        } else {
            echo json_encode(array("flag" => 0, "msg" => "Failed to delete review."));
        }
        exit;
    }

    public function bulk_update_status()
    {
        $reviews = $this->input->post('reviews'); // array of ['id' => X, 'type' => Y]
        $status = strtolower(trim((string)$this->input->post('status')));

        if (empty($reviews) || !in_array($status, array('pending', 'published', 'rejected'))) {
            echo json_encode(array("flag" => 0, "msg" => "Please select reviews and a valid status."));
            exit;
        }

        $count = 0;
        foreach ($reviews as $rev) {
            if (!empty($rev['id']) && !empty($rev['type'])) {
                if ($this->ProductReviewModel->updateReviewStatus($rev['type'], $rev['id'], $status)) {
                    $count++;
                }
            }
        }

        echo json_encode(array(
            "flag"   => 1,
            "msg"    => "Updated " . $count . " review(s) to " . ucfirst($status) . ".",
            "counts" => $this->ProductReviewModel->getStatusCounts()
        ));
        exit;
    }
}
