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
            $data['merchant_count'] = $this->ProductReviewModel->getMerchantBadgeCount();

            $this->load->view('product_review/product_review_main', $data);
        } else {
            redirect(base_url('customer/login'));
        }
    }

    public function ajaxreviewlist()
    {
        try {
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
            $totalRecords = $this->ProductReviewModel->getReviewsCount(array(), '', $type_filter);
            $totalRecordwithFilter = $this->ProductReviewModel->getReviewsCount($search_param, $status_filter, $type_filter);

            $data = array();

            if (!empty($reviewList)) {
                foreach ($reviewList as $r) {
                    $type = $r['review_type'] ?? 'product';
                    $id = (int)($r['review_id'] ?? 0);
                    $parent_id = (int)($r['parent_review_id'] ?? 0);
                    $current_status = strtolower($r['status'] ?? 'pending');

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
                    $rating_num = (int)($r['rating'] ?? 5);
                    $stars_html = '<span style="color:#f59e0b;font-size:14px;white-space:nowrap;">';
                    for ($s = 1; $s <= 5; $s++) {
                        $stars_html .= ($s <= $rating_num) ? '★' : '<span style="color:#d1d5db;">★</span>';
                    }
                    $stars_html .= '</span> <small style="font-weight:600;color:#333;">(' . $rating_num . '/5)</small>';

                    // Review comments with ellipsis
                    $raw_comments = trim((string)($r['comments'] ?? ''));
                    if (!empty($raw_comments)) {
                        if (mb_strlen($raw_comments) > 70) {
                            $comment_preview = '<span title="' . htmlspecialchars($raw_comments) . '">' . htmlspecialchars(mb_substr($raw_comments, 0, 70)) . '...</span>';
                        } else {
                            $comment_preview = htmlspecialchars($raw_comments);
                        }
                    } else {
                        $comment_preview = '<em style="color:#9ca3af;">No comments</em>';
                    }

                    // Created date
                    $created_date = !empty($r['created_at']) ? date("d M Y, h:i A", strtotime($r['created_at'])) : '-';

                    // Order link / display (with click to view review details)
                    $order_display = !empty($r['B2B_order_id']) ? htmlspecialchars($r['B2B_order_id']) : ('#' . ($r['order_id'] ?? ''));
                    $order_link = '<a href="javascript:void(0)" onclick="viewReviewDetails(\'' . $type . '\', ' . $id . ')" title="Click to view review details" style="color:#007bff; font-weight:700; text-decoration:underline; cursor:pointer;">' . $order_display . '</a>';

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

                    // Delete button
                    if (empty($this->session->userdata('userPermission')) || in_array('webshop/product_reviews/write', $this->session->userdata('userPermission'))) {
                        $delete_btn = '<a href="javascript:void(0)" onclick="deleteReviewItem(\'' . $type . '\', ' . $id . ')" class="text-danger" title="Delete Review"><i class="fa fa-trash-alt"></i></a>';
                    } else {
                        $delete_btn = '-';
                    }

                    $data[] = array(
                        "id"            => '<input type="checkbox" name="chk_review[]" data-type="' . $type . '" value="' . $id . '">',
                        "order_id"      => $order_link,
                        "rating"        => $stars_html,
                        "status"        => $status_badge,
                        "action"        => $action_btns,
                        "delete"        => $delete_btn
                    );
                }
            }

            $response = array(
                "draw"                 => intval($draw),
                "recordsTotal"         => intval($totalRecords),
                "recordsFiltered"      => intval($totalRecordwithFilter),
                "iTotalRecords"        => intval($totalRecords),
                "iTotalDisplayRecords" => intval($totalRecordwithFilter),
                "data"                 => $data,
                "aaData"               => $data
            );

            echo json_encode($response);
            exit;
        } catch (Throwable $e) {
            echo json_encode(array(
                "draw"                 => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
                "recordsTotal"         => 0,
                "recordsFiltered"      => 0,
                "iTotalRecords"        => 0,
                "iTotalDisplayRecords" => 0,
                "data"                 => array(),
                "aaData"               => array(),
                "error"                => $e->getMessage()
            ));
            exit;
        }
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
        $type = trim((string)$this->input->post('type'));
        if (empty($type)) {
            $type = 'merchant';
        }

        if (empty($review_id)) {
            echo json_encode(array("flag" => 0, "msg" => "Invalid review ID."));
            exit;
        }

        $review = $this->ProductReviewModel->getSingleReviewDetails($type, $review_id);
        if (empty($review)) {
            // Fallback for parent order reviews
            $details = $this->ProductReviewModel->getOrderReviewDetails($review_id);
            if (empty($details)) {
                echo json_encode(array("flag" => 0, "msg" => "Review details not found."));
                exit;
            }
            $data['details'] = $details;
            $data['review_type'] = 'all';
            $html = $this->load->view('product_review/popup-data', $data, true);
            echo json_encode(array(
                "flag"        => 1, 
                "data"        => $html,
                "modal_title" => '<i class="fa fa-star text-warning mr-2"></i> Order Reviews & Moderation'
            ));
            exit;
        }

        $data['review'] = $review;
        $data['review_type'] = $type;
        $html = $this->load->view('product_review/popup-data', $data, true);

        $modal_title = ($type === 'merchant') 
            ? '<i class="fa fa-store text-info mr-2"></i> Merchant Service Review' 
            : '<i class="fa fa-box mr-2" style="color: #6f42c1;"></i> Product Review';

        echo json_encode(array(
            "flag"        => 1, 
            "data"        => $html,
            "modal_title" => $modal_title
        ));
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

    public function ajaxmerchantbadgelist()
    {
        try {
            $draw = isset($_POST['draw']) ? (int)$_POST['draw'] : 1;
            $row = isset($_POST['start']) ? (int)$_POST['start'] : 0;
            $rowperpage = isset($_POST['length']) ? (int)$_POST['length'] : 50;

            $search_param = array();
            if (!empty($_POST['search']['value'])) {
                $search_param['keyword'] = trim($_POST['search']['value']);
            }

            $merchantList = $this->ProductReviewModel->getMerchantBadgeList($row, $rowperpage, $search_param);
            $totalRecords = $this->ProductReviewModel->getMerchantBadgeCount(array());
            $totalRecordwithFilter = $this->ProductReviewModel->getMerchantBadgeCount($search_param);

            $data = array();

            if (!empty($merchantList)) {
                foreach ($merchantList as $m) {
                    $m_id = (int)$m['merchant_id'];
                    $trade_name = htmlspecialchars($m['publication_name'] ?? 'N/A');
                    $vendor_name = htmlspecialchars($m['vendor_name'] ?? 'N/A');

                    $pub_reviews = (int)($m['total_published_reviews'] ?? 0);
                    $reviews_badge = '<span class="badge badge-light border font-weight-bold" style="font-size:12px; padding: 6px 12px; border-radius: 12px;">' . $pub_reviews . '</span>';

                    $avg = round((float)($m['avg_rating'] ?? 0), 1);
                    if ($pub_reviews > 0 && $avg > 0) {
                        $rating_html = '<div style="font-weight:700; color:#111; font-size:14px;"><span style="color:#f59e0b;">★</span> ' . number_format($avg, 1) . ' / 5</div>';
                    } else {
                        $rating_html = '<span class="text-muted" style="font-size:12px;">Unrated</span>';
                    }

                    $is_enabled = (int)($m['is_badge_enabled'] ?? 1);
                    $checked = ($is_enabled === 1) ? 'checked' : '';
                    $status_text = ($is_enabled === 1) ? 'Enabled' : 'Disabled';
                    $status_color = ($is_enabled === 1) ? '#28a745' : '#dc3545';

                    $toggle_html = '
                        <div class="d-flex align-items-center justify-content-center" style="gap: 8px;">
                            <label class="switch-toggle" style="margin-bottom:0;">
                                <input type="checkbox" id="switch_merchant_' . $m_id . '" data-id="' . $m_id . '" ' . $checked . ' onchange="toggleMerchantBadge(' . $m_id . ', this.checked)">
                                <span class="slider round"></span>
                            </label>
                            <span id="label_merchant_' . $m_id . '" style="font-weight:700; font-size:12px; min-width:60px; color:' . $status_color . ';">
                                ' . $status_text . '
                            </span>
                        </div>
                    ';

                    $data[] = array(
                        "trade_name"        => '<strong>' . $trade_name . '</strong>',
                        "vendor_name"       => $vendor_name,
                        "published_reviews" => $reviews_badge,
                        "avg_rating"        => $rating_html,
                        "toggle_badge"      => $toggle_html
                    );
                }
            }

            echo json_encode(array(
                "draw"            => $draw,
                "recordsTotal"    => $totalRecords,
                "recordsFiltered" => $totalRecordwithFilter,
                "data"            => $data,
                "aaData"          => $data
            ));
            exit;
        } catch (Exception $e) {
            echo json_encode(array(
                "draw"            => 1,
                "recordsTotal"    => 0,
                "recordsFiltered" => 0,
                "data"            => array(),
                "aaData"          => array(),
                "error"           => $e->getMessage()
            ));
            exit;
        }
    }

    public function toggle_merchant_badge()
    {
        $merchant_id = (int)$this->input->post('merchant_id');
        $status = (int)$this->input->post('is_badge_enabled');

        if (empty($merchant_id)) {
            echo json_encode(array("flag" => 0, "msg" => "Invalid merchant ID provided."));
            exit;
        }

        $updated = $this->ProductReviewModel->toggleMerchantBadge($merchant_id, $status);

        if ($updated) {
            echo json_encode(array(
                "flag"   => 1,
                "msg"    => "Front-end merchant trust badge " . ($status ? "enabled" : "disabled") . " successfully.",
                "status" => $status
            ));
        } else {
            echo json_encode(array("flag" => 0, "msg" => "Failed to update merchant badge status."));
        }
        exit;
    }
}
