<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Giftcards extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Giftcard_model');
        $this->load->model('CommonModel');
        $this->load->helper('string');
        date_default_timezone_set('Indian/Mauritius');
        $this->config->load('payment');

        
        $site_lang = $this->session->userdata('site_lang');
        if ($site_lang) {
            $this->lang->load('content', $site_lang);
        } else {
            $this->lang->load('content', 'english');
        }
    }

    // List gift card values
    public function index()
    {
        $data['values'] = $this->Giftcard_model->get_values();
        $this->load->view('giftcards/index', $data);
    }

    // Show purchase form for a value id
    public function purchase($value_id = null)
    {
        $user_id = $this->session->userdata('LoginID');
        if (!$user_id) redirect('customer/login');

        $value = $this->Giftcard_model->get_value((int)$value_id);
        if (!$value) show_404();

        $data['value'] = $value;
        $this->load->view('giftcards/purchase', $data);
    }

    // Process purchase request -> create pending order & redirect to payment
    public function processPurchase()
    {
        $user_id = $this->session->userdata('LoginID');
        if (!$user_id) {
            echo json_encode(['status' => 0, 'msg' => 'Please login first.']);
            return;
        }

        $value_id = (int)$this->input->post('value_id');
        $receiver_name = trim($this->input->post('receiver_name'));
        $receiver_email = trim($this->input->post('receiver_email'));
        $message = trim($this->input->post('message'));

        if (!$value_id || !$receiver_name || !$receiver_email) {
            echo json_encode(['status' => 0, 'msg' => 'Please fill required fields.']);
            return;
        }

        $value = $this->Giftcard_model->get_value($value_id);
        if (!$value) {
            echo json_encode(['status' => 0, 'msg' => 'Invalid gift card value.']);
            return;
        }

        // Create order_number
        $order_number = 'GCORD-' . time() . '-' . strtoupper(random_string('alnum', 6));

        $order_data = [
            'order_number'   => $order_number,
            'user_id'        => $user_id, // Store purchaser's ID so session and cart are preserved
            'value_id'       => $value->id,
            'amount'         => $value->amount,
            'receiver_name'  => $receiver_name,
            'receiver_email' => $receiver_email,
            'message'        => $message,
            'status'         => 0, // 0 = pending/unpaid
            'created_at'     => date('Y-m-d H:i:s')
        ];

        $order_id = $this->Giftcard_model->create_order($order_data);

        if ($order_id) {
            $order_response = $this->Giftcard_model->get_order($order_id);
            $this->initMytTransaction($order_response);
        } else {
            echo json_encode(['status' => 0, 'msg' => 'Failed to create order. Try again.']);
        }
    }

    // Initialize MyT Money transaction
    public function initMytTransaction($order_response)
    {
        log_message('info', '=== MyT Money Transaction Init ===');

        // phpseclib RSA init
        $phpseclib_base = rtrim(APPPATH, '/') . '/third_party/phpseclib/';
        static $rsaClass = null;
        if ($rsaClass === null) {
            spl_autoload_register(function ($class) use ($phpseclib_base) {
                $class = ltrim($class, '\\');
                foreach (['phpseclib\\', 'phpseclib3\\'] as $prefix) {
                    if (strncmp($prefix, $class, strlen($prefix)) !== 0) continue;
                    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
                    foreach ([$phpseclib_base . $relative . '.php', $phpseclib_base . 'src/' . $relative . '.php'] as $file) {
                        if (file_exists($file)) { require_once $file; return; }
                    }
                }
            });

            if (class_exists('\\phpseclib3\\Crypt\\RSA')) {
                $rsaClass = '\\phpseclib3\\Crypt\\RSA';
            } elseif (class_exists('\\phpseclib\\Crypt\\RSA')) {
                $rsaClass = '\\phpseclib\\Crypt\\RSA';
            } else {
                show_error('phpseclib RSA class not found.');
            }
        }

        $rsa = new $rsaClass();
        $rsa->setEncryptionMode($rsaClass::ENCRYPTION_OAEP);

        $order_id       = $order_response->id;
        $user_id        = $this->session->userdata('LoginID');
        $lang_id        = $this->session->userdata('lid') ?: 1;
        $increment_id   = $order_response->order_number;
        $amount         = 1; // or $order_response->amount
        $quote_id       = $this->session->userdata('QuoteId');
        $sis_session_id = $this->session->userdata('sis_session_id') ?: $this->session->userdata('LoginToken');

        // Look up active quote with items from database if not in session or current quote has no items
        $has_items = false;
        if (!empty($quote_id)) {
            $item_count = $this->db->from('sales_quote_items')
                ->where('quote_id', $quote_id)
                ->count_all_results();
            $has_items = ($item_count > 0);
        }
        if (!$has_items && !empty($user_id)) {
            $active_quote = $this->db->select('SQ.quote_id, SQ.session_id')
                ->from('sales_quote SQ')
                ->join('sales_quote_items SQI', 'SQI.quote_id = SQ.quote_id')
                ->where('SQ.customer_id', $user_id)
                ->order_by('SQ.quote_id', 'DESC')
                ->limit(1)
                ->get()
                ->row();
            if (!$active_quote) {
                $active_quote = $this->db->select('quote_id, session_id')
                    ->from('sales_quote')
                    ->where('customer_id', $user_id)
                    ->order_by('quote_id', 'DESC')
                    ->limit(1)
                    ->get()
                    ->row();
            }
            if ($active_quote) {
                $quote_id = $active_quote->quote_id;
                $this->session->set_userdata('QuoteId', $quote_id);
                if (!empty($active_quote->session_id)) {
                    $sis_session_id = $active_quote->session_id;
                    $this->session->set_userdata('sis_session_id', $sis_session_id);
                    $this->session->set_userdata('LoginToken', $sis_session_id);
                }
            }
        }
        if (empty($sis_session_id)) {
            $sis_session_id = function_exists('generateToken') ? generateToken('50') : md5(uniqid((string)mt_rand(), true));
            $this->session->set_userdata('sis_session_id', $sis_session_id);
            if (!empty($user_id)) {
                $this->session->set_userdata('LoginToken', $sis_session_id);
            }
        }

        $callbackParams = [
            'customer_id' => base64_encode($user_id),
            'lang_id'     => base64_encode($lang_id),
            'key'         => base64_encode($increment_id)
        ];
        if (!empty($quote_id)) {
            $callbackParams['quote_id'] = base64_encode($quote_id);
        }
        if (!empty($sis_session_id)) {
            $callbackParams['session_id'] = base64_encode($sis_session_id);
        }
        $callbackUrl    = base_url('Giftcards/success/?' . http_build_query($callbackParams));
        $notifyUrl      = base_url('Giftcards/mytNotify');

        // Insert transaction into new table
        $insertData = [
            'order_id'        => $order_id,
            'increment_id'    => $increment_id,
            'transaction_ref' => null,
            'amount'          => $amount,
            'currency'        => 'MUR',
            'status'          => 'initiated',
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('giftcard_order_mytmoney_transactions', $insertData);
        $transaction_id = $this->db->insert_id();

        $cfg           = $this->config->item('myt_money');
        $merchantAppId = $cfg['app_id'];
        $apiKey        = $cfg['api_key'];
        $publicKey     = $cfg['public_key'];

        $merTradeNo = (string)(microtime(true) * 1000);
        $payload = [
            "totalPrice" => $amount,
            "currency"   => "MUR",
            "merTradeNo" => $merTradeNo,
            "notifyUrl"  => $notifyUrl,
            "returnUrl"  => $callbackUrl,
            "remark"     => "Gift Card #$increment_id",
            "lang"       => "en"
        ];
        $payloadJson = json_encode($payload);
        $rsa->loadKey($publicKey);
        $encryptedPayload = base64_encode($rsa->encrypt($payloadJson));

        $paymentType   = "S";
        $signatureData = "appId={$merchantAppId}&merTradeNo={$merTradeNo}&payload={$encryptedPayload}&paymentType={$paymentType}";
        $sign          = base64_encode(hash_hmac('sha512', $signatureData, $apiKey, true));

        // Update transaction
        $this->db->where('id', $transaction_id)->update('giftcard_order_mytmoney_transactions', [
            'mer_trade_no' => $merTradeNo,
            'payload'      => $payloadJson
        ]);

        // Redirect to gateway
        $gatewayUrl = "https://pay.mytmoney.mu/Mt/web/payments";
        echo '<html><body>';
        echo '<form id="mytForm" action="' . $gatewayUrl . '" method="POST">';
        echo '<input type="hidden" name="appId" value="' . $merchantAppId . '">';
        echo '<input type="hidden" name="merTradeNo" value="' . $merTradeNo . '">';
        echo '<input type="hidden" name="payload" value="' . $encryptedPayload . '">';
        echo '<input type="hidden" name="paymentType" value="' . $paymentType . '">';
        echo '<input type="hidden" name="sign" value="' . $sign . '">';
        echo '</form>';
        echo '<script>document.getElementById("mytForm").submit();</script>';
        echo '</body></html>';
    }

    // MyT Money notify callback
 // MyT Money notify callback (server-to-server)
public function mytNotify()
{
    // Gather notify data from GET or POST
    $notifyData = [
        'merTradeNo'   => $this->input->get('merTradeNo'),
        'msg'          => $this->input->get('msg'),
        'errorCode'    => $this->input->get('errorCode'),
        'tradeNo'      => $this->input->get('tradeNo'),
        'tradeStatus'  => $this->input->get('tradeStatus'),
        'timestamp'    => $this->input->get('timestamp'),
        'sign'         => $this->input->get('sign')
    ];

    log_message('info', 'Giftcard Notify GET data: ' . json_encode($notifyData));

    // Validate required fields
    if (empty($notifyData['merTradeNo']) || empty($notifyData['tradeStatus'])) {
        log_message('error', 'Invalid notify data received.');
        show_error('Invalid data', 400);
        return;
    }

    $order_number   = $notifyData['merTradeNo'];
    $payment_ref    = $notifyData['tradeNo'] ?? null;
    $status         = ($notifyData['tradeStatus'] === 'TRADE_FINISHED' && $notifyData['errorCode'] === '000')
                      ? 'success'
                      : 'failed';
    $payload        = json_encode($notifyData);

    // Update transaction record in giftcard_order_mytmoney_transactions
    $this->db->where('mer_trade_no', $order_number)
             ->update('giftcard_order_mytmoney_transactions', [
                 'response_payload' => $payload,
                 'status'           => $status,
                 'transaction_ref'  => $payment_ref,
                 'updated_at'       => date('Y-m-d H:i:s')
             ]);

    // Process successful payment
    if ($status === 'success') {
        $order = $this->Giftcard_model->get_order_by_number($order_number);

        if ($order && $order->status == 0) {
            $this->Giftcard_model->mark_order_paid($order->id, $payment_ref);
            $this->Giftcard_model->issue_giftcard($order->id);
            log_message('info', "Giftcard order marked as paid: OrderID {$order->id}, PaymentRef {$payment_ref}");
        } else {
            log_message('info', "Order not found or already processed: {$order_number}");
        }
    } else {
        $order = $this->Giftcard_model->get_order_by_number($order_number);
        if ($order) {
            $this->Giftcard_model->mark_order_failed($order->id);
        }
        log_message('error', "Giftcard payment failed: Order {$order_number}, Status {$notifyData['tradeStatus']}, ErrorCode {$notifyData['errorCode']}");
    }

    echo 'OK';
}

// Payment success page
public function success($order_id = null)
{
    if (!empty($order_id)) {
        $order_id = rtrim($order_id, '/');
    }

    $key              = $this->input->get('key');
    $customer_id      = $this->input->get('customer_id');
    $lang_id          = $this->input->get('lang_id');
    $quote_id_param   = $this->input->get('quote_id');
    $session_id_param = $this->input->get('session_id');

    // Retrieve order by increment_id (from key) or by order_id segment
    $order = null;
    $increment_id = null;
    if (!empty($key)) {
        $increment_id = base64_decode($key);
        if (!empty($increment_id)) {
            $order = $this->Giftcard_model->get_order_by_number($increment_id);
        }
    }
    if (!$order && !empty($order_id)) {
        if (is_numeric($order_id)) {
            $order = $this->Giftcard_model->get_order((int)$order_id);
        } else {
            $order = $this->Giftcard_model->get_order_by_number($order_id);
        }
    }
    if (!$order) show_404();

    if (empty($increment_id)) {
        $increment_id = $order->order_number;
    }

    // Determine customer_id (decode if base64)
    if (!empty($customer_id)) {
        $decoded_customer_id = base64_decode($customer_id);
        if ($decoded_customer_id !== false && is_numeric($decoded_customer_id)) {
            $customer_id = $decoded_customer_id;
        }
    }
    if (empty($customer_id)) {
        $customer_id = $this->session->userdata('LoginID') ?: $order->user_id;
    }

    if (!empty($lang_id)) {
        $language_id = base64_decode($lang_id);
    } elseif ($this->session->userdata('lid')) {
        $language_id = $this->session->userdata('lid');
    } else {
        $language_id = 1;
    }

    $languages = [
        1 => [
            'id' => 1,
            'name' => 'english',
            'display_name' => 'English',
            'code' => 'en',
            'is_default_language' => 1
        ],
        2 => [
            'id' => 2,
            'name' => 'french',
            'display_name' => 'Français',
            'code' => 'fr',
            'is_default_language' => 0
        ]
    ];

    if (!isset($languages[$language_id])) {
        $language_id = 1;
    }

    // Auto-login customer if not already logged in
    $customer = null;
    $target_customer_id = $this->session->userdata('LoginID') ?: $customer_id;
    if (!$this->session->userdata('LoginID') && !empty($target_customer_id)) {
        $customer = $this->db
            ->select('id, first_name, last_name, email_id, customer_type_id, access_prelanch_product, allow_catlog_builder')
            ->from('customers')
            ->where('id', $target_customer_id)
            ->get()
            ->row();

        if ($customer) {
            $active_token = !empty($session_id_param) ? base64_decode($session_id_param) : ($this->session->userdata('LoginToken') ?: $this->session->userdata('sis_session_id'));
            if (empty($active_token)) {
                $active_token = function_exists('generateToken') ? generateToken('50') : md5(uniqid((string)mt_rand(), true));
            }

            $sessionArr = array(
                'LoginID'        => $customer->id,
                'LoginToken'     => $active_token,
                'sis_session_id' => $active_token,
                'FirstName'      => $customer->first_name,
                'LastName'       => $customer->last_name,
                'EmailID'        => $customer->email_id,
                'LoginRole'      => 'customer',
                'CustomerTypeID' => $customer->customer_type_id,
                'is_logged_in'   => true
            );

            $this->session->set_userdata($sessionArr);

            if (
                (isset($customer->access_prelanch_product) && $customer->access_prelanch_product == 1) ||
                (isset($customer->allow_catlog_builder) && $customer->allow_catlog_builder == 1)
            ) {
                $this->session->set_userdata('special_features', 1);
            }

            log_message('info', "Giftcard success(): Customer auto-logged in (ID: {$customer->id})");
        } else {
            log_message('error', "Giftcard success(): Customer not found for order user_id {$order->user_id}");
        }
    } elseif (!empty($target_customer_id)) {
        $customer = $this->db
            ->select('id, first_name, last_name, email_id, customer_type_id, access_prelanch_product, allow_catlog_builder')
            ->from('customers')
            ->where('id', $target_customer_id)
            ->get()
            ->row();
    }

    // Restore Shopping Cart / QuoteId and session_id so existing products remain in cart
    if (!empty($quote_id_param)) {
        $restored_quote_id = base64_decode($quote_id_param);
        if (!empty($restored_quote_id)) {
            $this->session->set_userdata('QuoteId', $restored_quote_id);
        }
    }
    if (!empty($session_id_param)) {
        $restored_session_id = base64_decode($session_id_param);
        if (!empty($restored_session_id)) {
            $this->session->set_userdata('sis_session_id', $restored_session_id);
            $this->session->set_userdata('LoginToken', $restored_session_id);
        }
    }

    // Fallback: If QuoteId is missing or has no items, recover customer's active quote with items from DB
    $current_user_id = $this->session->userdata('LoginID') ?: $customer_id;
    if (!empty($current_user_id)) {
        $has_items = false;
        $current_quote_id = $this->session->userdata('QuoteId');
        if (!empty($current_quote_id)) {
            $item_count = $this->db->from('sales_quote_items')
                ->where('quote_id', $current_quote_id)
                ->count_all_results();
            $has_items = ($item_count > 0);
        }
        if (!$has_items) {
            $active_quote = $this->db->select('SQ.quote_id, SQ.session_id')
                ->from('sales_quote SQ')
                ->join('sales_quote_items SQI', 'SQI.quote_id = SQ.quote_id')
                ->where('SQ.customer_id', $current_user_id)
                ->order_by('SQ.quote_id', 'DESC')
                ->limit(1)
                ->get()
                ->row();
            if (!$active_quote) {
                $active_quote = $this->db->select('quote_id, session_id')
                    ->from('sales_quote')
                    ->where('customer_id', $current_user_id)
                    ->order_by('quote_id', 'DESC')
                    ->limit(1)
                    ->get()
                    ->row();
            }
            if ($active_quote) {
                $this->session->set_userdata('QuoteId', $active_quote->quote_id);
                if (!empty($active_quote->session_id)) {
                    $this->session->set_userdata('sis_session_id', $active_quote->session_id);
                    $this->session->set_userdata('LoginToken', $active_quote->session_id);
                }
            }
        }
    }

    // Refresh order details from DB
    $order = $this->Giftcard_model->get_order($order->id);

    // Check if payment transaction is successful in DB
    $txn = $this->db
        ->select('status, transaction_ref')
        ->from('giftcard_order_mytmoney_transactions')
        ->where('order_id', $order->id)
        ->order_by('id', 'DESC')
        ->limit(1)
        ->get()
        ->row();

    $getTradeNo = $this->input->get('tradeNo') ?: ($txn->transaction_ref ?? ('GC-' . time()));
    $getTradeStatus = $this->input->get('tradeStatus') ?: ($txn->status ?? '');
    $getErrorCode = $this->input->get('errorCode') ?: '';

    $is_payment_success = ($order && $order->status == 1) || ($txn && strtolower($txn->status) === 'success');

    if (!$gift_card = $this->db->get_where('gift_cards', ['order_id' => $order->id])->row()) {

        if ($is_payment_success) {
            $this->Giftcard_model->mark_order_paid($order->id, $getTradeNo);
            $this->db->where('order_id', $order->id)->update('giftcard_order_mytmoney_transactions', [
                'status'          => 'success',
                'transaction_ref' => $getTradeNo,
                'updated_at'      => date('Y-m-d H:i:s')
            ]);
            $gift_card = $this->Giftcard_model->issue_giftcard($order->id);
            log_message('info', "Giftcard issued for OrderID {$order->id}");
        } else {
            // Payment failed, canceled, or expired on gateway level
            $this->Giftcard_model->mark_order_failed($order->id);
            $this->db->where('order_id', $order->id)->update('giftcard_order_mytmoney_transactions', [
                'status'     => 'failed',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            log_message('warn', "Giftcard payment failed/expired via gateway return params: OrderID {$order->id}, tradeStatus {$getTradeStatus}, errorCode {$getErrorCode}");
            $failParams = [
                'customer_id' => base64_encode($customer_id),
                'lang_id'     => base64_encode($language_id),
                'key'         => base64_encode($increment_id)
            ];
            $saved_quote_id = $this->session->userdata('QuoteId');
            if (!empty($saved_quote_id)) {
                $failParams['quote_id'] = base64_encode($saved_quote_id);
            }
            $saved_session_id = $this->session->userdata('sis_session_id') ?: $this->session->userdata('LoginToken');
            if (!empty($saved_session_id)) {
                $failParams['session_id'] = base64_encode($saved_session_id);
            }
            redirect('Giftcards/failed/' . $order->id . '/?' . http_build_query($failParams));
            return;
        }
    }

    // Issue gift card only if payment success and not already issued
    if (!$gift_card = $this->db->get_where('gift_cards', ['order_id' => $order->id])->row()) {
        $gift_card = $this->Giftcard_model->issue_giftcard($order->id);
        if ($gift_card) {
            log_message('info', "Giftcard issued for OrderID {$order->id}");
        }
    }

    if (!$gift_card) {
        log_message('error', "Giftcard success(): Failed to issue giftcard for OrderID {$order->id}");
        $failParams = [
            'customer_id' => base64_encode($customer_id),
            'lang_id'     => base64_encode($language_id),
            'key'         => base64_encode($increment_id)
        ];
        if ($this->session->userdata('QuoteId')) {
            $failParams['quote_id'] = base64_encode($this->session->userdata('QuoteId'));
        }
        if ($this->session->userdata('sis_session_id')) {
            $failParams['session_id'] = base64_encode($this->session->userdata('sis_session_id'));
        }
        redirect('Giftcards/failed/' . $order->id . '/?' . http_build_query($failParams));
        return;
    }

    // ✅ Add credit transaction if missing
    if ($gift_card) {
        $exists = $this->db
            ->where('gift_card_id', $gift_card->id)
            ->where('type', 'credit')
            ->get('gift_card_transactions')
            ->row();

        if (!$exists) {
            $receiver_id = $this->Giftcard_model->get_user_id($order->receiver_email);
            $this->Giftcard_model->add_transaction(
                $receiver_id,
                $gift_card->id,
                'credit',
                $gift_card->balance,
                1
            );
        }
    }

    $languageData = $languages[$language_id];

    $languageArr = [
        'lid' => $languageData['id'],
        'site_lang' => strtolower($languageData['name']),
        'ldisplay_name' => $languageData['display_name'],
        'lcode' => $languageData['code'],
        'lis_default_language' => $languageData['is_default_language']
    ];

    // Store in session
    $this->session->set_userdata($languageArr);

    // Also store cookie
    $this->load->helper('cookie');
    $this->input->set_cookie('site_language', $languageData['code'], time() + 60 * 60 * 24 * 365, '', '/', '', ENVIRONMENT === 'production', true);

    $site_lang = $this->session->userdata('site_lang');
    if ($site_lang) {
        $this->lang->load('content', $site_lang);
    } else {
        $this->lang->load('content', 'english');
    }

    // ✅ Send email to recipient ONLY if gift card exists, has a code, and email was not sent already
    if ($gift_card && !empty($gift_card->code) && !$this->Giftcard_model->is_email_sent($order->id)) {
        $order_number   = $order->order_number;
        $gift_amount    = $order->amount;
        $receiver_name  = $order->receiver_name;
        $receiver_email = $order->receiver_email;
        $message        = $order->message;
        $created_at     = $order->created_at;
        $buyer_name     = (isset($customer) && !empty($customer->first_name)) ? trim($customer->first_name . " " . ($customer->last_name ?? '')) : "Customer";
        $lang_code      = $languageArr['lcode'];
        $gift_code      = $gift_card->code;

        $recipient_identifier = 'gift-card-recipient';

        $RecipientTempVars = array(
            '##RECIPIENT_NAME##',
            '##SENDER_NAME##',
            '##GIFTCARD_CODE##',
            '##GIFTCARD_AMOUNT##',
            '##PERSONAL_MESSAGE##',
            '##PURCHASE_DATE##'
        );

        $RecipientDynamicVars = array(
            $receiver_name,
            $buyer_name,
            $gift_code,
            'MUR ' . $gift_amount,
            nl2br($message),
            date('d-M-Y', strtotime($created_at))
        );

        $mailSent = $this->CommonModel->sendCommonHTMLEmail(
            $receiver_email,
            $recipient_identifier,
            $RecipientTempVars,
            $RecipientDynamicVars,
            $lang_code
        );

        if ($mailSent !== false) {
            $this->Giftcard_model->mark_email_sent($order->id);
            log_message('info', "Giftcard email sent to recipient {$receiver_email} for OrderID {$order->id}");
        }
    }

    $data['order'] = $order;
    $data['gift_card'] = $gift_card;
    $this->load->view('giftcards/success', $data);
}


// Payment failed page
public function failed($order_id = null)
{
    // Clean trailing slashes from order_id segment if present
    if (!empty($order_id)) {
        $order_id = rtrim($order_id, '/');
    }

    $key              = $this->input->get('key');
    $customer_id      = $this->input->get('customer_id');
    $lang_id          = $this->input->get('lang_id');
    $quote_id_param   = $this->input->get('quote_id');
    $session_id_param = $this->input->get('session_id');

    // Decode language if provided
    if ($lang_id != "") {
        $language_id = base64_decode($lang_id);
    } elseif ($this->session->userdata('lid')) {
        $language_id = $this->session->userdata('lid');
    } else {
        $language_id = 1;
    }

    $languages = [
        1 => [
            'id' => 1,
            'name' => 'english',
            'display_name' => 'English',
            'code' => 'en',
            'is_default_language' => 1
        ],
        2 => [
            'id' => 2,
            'name' => 'french',
            'display_name' => 'Français',
            'code' => 'fr',
            'is_default_language' => 0
        ]
    ];

    if (!isset($languages[$language_id])) {
        $language_id = 1;
    }

    $languageData = $languages[$language_id];

    $languageArr = [
        'lid'                  => $languageData['id'],
        'site_lang'            => strtolower($languageData['name']),
        'ldisplay_name'        => $languageData['display_name'],
        'lcode'                => $languageData['code'],
        'lis_default_language' => $languageData['is_default_language']
    ];

    // Store in session and set language cookie
    $this->session->set_userdata($languageArr);
    $this->load->helper('cookie');
    $this->input->set_cookie('site_language', $languageData['code'], time() + 60 * 60 * 24 * 365, '', '/', '', ENVIRONMENT === 'production', true);

    $site_lang = $this->session->userdata('site_lang');
    if ($site_lang) {
        $this->lang->load('content', $site_lang);
    } else {
        $this->lang->load('content', 'english');
    }

    // Try finding order from key or order_id
    $order = null;
    if (!empty($key)) {
        $increment_id = base64_decode($key);
        if (!empty($increment_id)) {
            $order = $this->Giftcard_model->get_order_by_number($increment_id);
        }
    }

    if (!$order && !empty($order_id)) {
        if (is_numeric($order_id)) {
            $order = $this->Giftcard_model->get_order((int)$order_id);
        } else {
            $order = $this->Giftcard_model->get_order_by_number($order_id);
        }
    }

    $order_number = null;
    if ($order) {
        $order_number = $order->order_number;
        $this->Giftcard_model->mark_order_failed($order->id);
    } elseif (!empty($order_id) && !is_numeric($order_id)) {
        $order_number = $order_id;
    }

    // Determine target customer_id
    $target_customer_id = null;
    if (!empty($customer_id)) {
        $decoded = base64_decode($customer_id);
        $target_customer_id = ($decoded !== false && is_numeric($decoded)) ? $decoded : $customer_id;
    }
    if (empty($target_customer_id) && $order) {
        $target_customer_id = $order->user_id;
    }
    if (empty($target_customer_id)) {
        $target_customer_id = $this->session->userdata('LoginID');
    }

    // Auto-login customer if not already logged in
    if (!$this->session->userdata('LoginID') && !empty($target_customer_id)) {
        $customer = $this->db
            ->select('id, first_name, last_name, email_id, customer_type_id, access_prelanch_product, allow_catlog_builder')
            ->from('customers')
            ->where('id', $target_customer_id)
            ->get()
            ->row();

        if ($customer) {
            $active_token = !empty($session_id_param) ? base64_decode($session_id_param) : ($this->session->userdata('LoginToken') ?: $this->session->userdata('sis_session_id'));
            if (empty($active_token)) {
                $active_token = function_exists('generateToken') ? generateToken('50') : md5(uniqid((string)mt_rand(), true));
            }

            $sessionArr = array(
                'LoginID'        => $customer->id,
                'LoginToken'     => $active_token,
                'sis_session_id' => $active_token,
                'FirstName'      => $customer->first_name,
                'LastName'       => $customer->last_name,
                'EmailID'        => $customer->email_id,
                'LoginRole'      => 'customer',
                'CustomerTypeID' => $customer->customer_type_id,
                'is_logged_in'   => true
            );

            $this->session->set_userdata($sessionArr);
            log_message('info', "Giftcard failed(): Customer auto-logged in (ID: {$customer->id})");
        }
    }

    // Restore Shopping Cart / QuoteId and session_id
    if (!empty($quote_id_param)) {
        $restored_quote_id = base64_decode($quote_id_param);
        if (!empty($restored_quote_id)) {
            $this->session->set_userdata('QuoteId', $restored_quote_id);
        }
    }
    if (!empty($session_id_param)) {
        $restored_session_id = base64_decode($session_id_param);
        if (!empty($restored_session_id)) {
            $this->session->set_userdata('sis_session_id', $restored_session_id);
            $this->session->set_userdata('LoginToken', $restored_session_id);
        }
    }

    // Fallback: If QuoteId is missing or has no items, recover customer's active quote with items from DB
    $current_user_id = $this->session->userdata('LoginID') ?: $target_customer_id;
    if (!empty($current_user_id)) {
        $has_items = false;
        $current_quote_id = $this->session->userdata('QuoteId');
        if (!empty($current_quote_id)) {
            $item_count = $this->db->from('sales_quote_items')
                ->where('quote_id', $current_quote_id)
                ->count_all_results();
            $has_items = ($item_count > 0);
        }
        if (!$has_items) {
            $active_quote = $this->db->select('SQ.quote_id, SQ.session_id')
                ->from('sales_quote SQ')
                ->join('sales_quote_items SQI', 'SQI.quote_id = SQ.quote_id')
                ->where('SQ.customer_id', $current_user_id)
                ->order_by('SQ.quote_id', 'DESC')
                ->limit(1)
                ->get()
                ->row();
            if (!$active_quote) {
                $active_quote = $this->db->select('quote_id, session_id')
                    ->from('sales_quote')
                    ->where('customer_id', $current_user_id)
                    ->order_by('quote_id', 'DESC')
                    ->limit(1)
                    ->get()
                    ->row();
            }
            if ($active_quote) {
                $this->session->set_userdata('QuoteId', $active_quote->quote_id);
                if (!empty($active_quote->session_id)) {
                    $this->session->set_userdata('sis_session_id', $active_quote->session_id);
                    $this->session->set_userdata('LoginToken', $active_quote->session_id);
                }
            }
        }
    }

    // Ensure session_id and LoginToken are never null
    if (!$this->session->userdata('sis_session_id')) {
        $token = $this->session->userdata('LoginToken') ?: (function_exists('generateToken') ? generateToken('50') : md5(uniqid((string)mt_rand(), true)));
        $this->session->set_userdata('sis_session_id', $token);
        if ($this->session->userdata('LoginID')) {
            $this->session->set_userdata('LoginToken', $token);
        }
    }

    $data['order_number'] = $order_number;
    $this->load->view('giftcards/giftcard_failed', $data);
}

    // Customer gift card balance & transactions
    public function mycards()
    {
        $user_id = $this->session->userdata('LoginID');
        if (!$user_id) redirect('login');

        $balance = $this->Giftcard_model->get_user_balance($user_id);
        $transactions = $this->Giftcard_model->get_user_transactions($user_id);
        $gift_cards = $this->Giftcard_model->get_user_giftcards($user_id);

        $gift_cards_map = [];
        foreach ($gift_cards as $gcard) {
            $gift_cards_map[$gcard->id] = $gcard;
        }

        $data['balance'] = $balance;
        $data['transactions'] = $transactions;
        $data['gift_cards_map'] = $gift_cards_map;

        $this->load->view('myprofile/my_cards', $data);
    }

    // Apply gift card to cart
    public function applyGiftCard()
    {
        $this->output->set_content_type('application/json');

        $gift_code  = trim($this->input->post('gift_code'));
        $session_id = trim($this->input->post('session_id'));
        $user_id = $this->session->userdata('LoginID') ?? 0;

        if (empty($user_id) || $user_id == 0) {
            echo json_encode(['status'=>'error','message'=>'Please log in to your account to apply the gift card.']);
            return;
        }

        if (empty($gift_code) || empty($session_id)) {
            echo json_encode(['status'=>'error','message'=>'Gift code or session ID missing.']);
            return;
        }

        $result = $this->Giftcard_model->applyGiftCardToCart($gift_code, $session_id, $user_id);
        echo json_encode($result);
    }

    // Remove gift card from cart
    public function removeGiftCard()
    {
        $this->output->set_content_type('application/json');

        $gift_code  = $this->input->post('gift_code');
        $session_id = $this->input->post('session_id');
        $user_id    = $this->session->userdata('LoginID') ?? 0;

        if (empty($user_id) || $user_id == 0) {
            echo json_encode(['status'=>'error','message'=>'Please log in to your account to apply the gift card.']);
            return;
        }

        if (empty($gift_code) || empty($session_id)) {
            echo json_encode(['status'=>'error','message'=>'Missing parameters']);
            return;
        }

        $result = $this->Giftcard_model->removeGiftCardFromCart($gift_code, $session_id, $user_id);
        echo json_encode($result);
    }
}
