<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Load phpseclib for RSA encryption
$phpseclib_base = rtrim(APPPATH, '/') . '/third_party/phpseclib/';
spl_autoload_register(function ($class) use ($phpseclib_base) {
    $class = ltrim($class, '\\');
    $prefixes = ['phpseclib\\', 'phpseclib3\\'];
    foreach ($prefixes as $prefix) {
        if (strncmp($prefix, $class, strlen($prefix)) !== 0) continue;
        $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
        $paths = [
            $phpseclib_base . $relative . '.php',
            $phpseclib_base . 'src/' . $relative . '.php'
        ];
        foreach ($paths as $file) {
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

// Detect RSA class
$PHPSECLIB_RSA_CLASS = null;
if (class_exists('\\phpseclib\\Crypt\\RSA')) {
    $PHPSECLIB_RSA_CLASS = '\\phpseclib\\Crypt\\RSA';
} elseif (class_exists('\\phpseclib3\\Crypt\\RSA')) {
    $PHPSECLIB_RSA_CLASS = '\\phpseclib3\\Crypt\\RSA';
}
if ($PHPSECLIB_RSA_CLASS === null) {
    log_message('error', 'phpseclib RSA class not found.');
    show_error('phpseclib RSA class not found. Please install phpseclib.');
}

class Addons extends CI_Controller
{
    private $rsaClass;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Addon_model');
        $this->load->model('UserModel');
        $this->load->library('session');
        $this->load->helper(['url', 'form']);
        $this->config->load('payment');

        global $PHPSECLIB_RSA_CLASS;
        $this->rsaClass = $PHPSECLIB_RSA_CLASS;

        // Only skip login check for these methods
        $public_methods = ['mytNotify', 'mytCallback', 'status'];

        if (!in_array($this->router->fetch_method(), $public_methods)) {
            if (!$this->session->userdata('LoginID')) {
                redirect(BASE_URL);
            }

            // Disable Add-on services if merchant subscription is expired
            $this->load->model('Subscription_model');
            if ($this->Subscription_model->is_subscription_expired($this->session->userdata('LoginID'))) {
                $site_lang = $this->session->userdata('site_lang');
                $error_msg = ($site_lang === 'french')
                    ? "Votre abonnement a expiré et votre boutique est actuellement désactivée. Veuillez renouveler votre abonnement afin d'accéder aux services Add-on."
                    : "Your subscription has expired and your shop is currently disabled. Please renew your subscription below to access Add-on services.";
                $this->session->set_flashdata('error', $error_msg);
                redirect('subscription');
                return;
            }
        }

        $site_lang = $this->session->userdata('site_lang');

        if ($site_lang) {
            $this->lang->load('content', $site_lang);
        } else {
            $this->lang->load('content', 'english'); // default
        }
    }

    public function index()
    {
        $addons = $this->Addon_model->get_addons_with_services();
        $data['addon_data'] = [];
        $site_lang = $this->session->userdata('site_lang');

        foreach ($addons as $row) {
           
            if ($site_lang == 'french' && !empty($row['category_name_fr'])) {
                $category_key = $row['category_name_fr'];
            } else {
                $category_key = $row['category_name'];
            }

           
            if ($site_lang == 'french') {
                if (!empty($row['title_fr'])) {
                    $row['title'] = $row['title_fr'];
                }
                if (!empty($row['description_fr'])) {
                    $row['description'] = $row['description_fr'];
                }
            }

            $data['addon_data'][$category_key][] = $row;
        }

        $this->load->view('addons/index', $data);
    }

    public function buy($service_id)
    {
        $publisher_id = $this->session->userdata('LoginID');
        $qty = $this->input->post('qty');
        $qty = (!empty($qty) && $qty > 0) ? $qty : 1;

        $service = $this->Addon_model->get_service_by_id($service_id);
        if (!$service) {
            show_error('Invalid service.');
        }

        $amount = 1;
        // Save purchase
        $purchase = [
            'merchant_id' => $publisher_id,
            'service_id'  => $service_id,
            'qty'         => $qty,
            'status'      => 'pending',
            'created_at'  => date('Y-m-d H:i:s'),
            'ip'          => $this->input->ip_address()
        ];
        $this->db->insert('merchant_addon_purchases', $purchase);
        $purchase_id = $this->db->insert_id();

        // Create a transaction
        $site_lang = $this->session->userdata('site_lang');
        $payment_lang = ($site_lang === 'french') ? 'french' : 'english';

        $transaction = [
            'purchase_id' => $purchase_id,
            'merchant_id' => $publisher_id,
            'amount'      => $amount,
            'status'      => 'initiated',
            'lang'        => $payment_lang,
            'created_at'  => date('Y-m-d H:i:s')
        ];
        $this->db->insert('merchant_addon_transactions', $transaction);
        $transaction_id = $this->db->insert_id();
        log_message('info', "Addon payment created | Transaction ID: {$transaction_id} | Language: {$payment_lang}");

        // Start secure payment
        $this->initiate_payment($transaction_id, $amount);
    }

    /**
     * Dedicated checkout endpoint for Order Resolution replacement delivery AddOn
     */
    public function buy_resolution_delivery($ticket_number = '')
    {
        $publisher_id = $this->session->userdata('LoginID');
        $this->load->model('OrderResolutionModel');
        $res = $this->OrderResolutionModel->get_resolution_by_ticket_number($ticket_number);

        if (!$res || (int)$res->merchant_id !== (int)$publisher_id) {
            show_error('Invalid resolution ticket or unauthorized access.');
            return;
        }

        // Prevent duplicate charge if AddOn payment is already verified as paid
        if (!empty($res->addon_purchase_id)) {
            $existing_purchase = $this->db->get_where('merchant_addon_purchases', ['id' => (int)$res->addon_purchase_id])->row();
            if ($existing_purchase && $existing_purchase->status === 'paid') {
                $this->session->set_flashdata('info', 'AddOn Service payment for this replacement has already been confirmed.');
                redirect('order_resolution/view/' . $res->ticket_number);
                return;
            }
        }

        // Resolve dynamic Admin-configured service (ID 61, YM Delivery, MUR 1.00)
        $service = $this->OrderResolutionModel->get_delivery_addon_service($res->addon_service_id ?? null);
        $service_id = $service ? (int)$service->id : 61;
        $amount = $service ? (float)$service->final_price : 1.00;

        // Save purchase
        $purchase = [
            'merchant_id' => $publisher_id,
            'service_id'  => $service_id,
            'qty'         => 1,
            'status'      => 'pending',
            'created_at'  => date('Y-m-d H:i:s'),
            'ip'          => $this->input->ip_address()
        ];
        $this->db->insert('merchant_addon_purchases', $purchase);
        $purchase_id = $this->db->insert_id();

        // Immediately link addon_purchase_id and addon_service_id to order resolution ticket
        $update_res = [
            'addon_purchase_id' => $purchase_id,
            'updated_at'        => time()
        ];
        if ($this->db->field_exists('addon_service_id', 'order_resolutions')) {
            $update_res['addon_service_id'] = $service_id;
        }
        $this->db->where('id', $res->id)->update('order_resolutions', $update_res);

        $site_lang = $this->session->userdata('site_lang');
        $payment_lang = ($site_lang === 'french') ? 'french' : 'english';

        $transaction = [
            'purchase_id' => $purchase_id,
            'merchant_id' => $publisher_id,
            'amount'      => $amount,
            'status'      => 'initiated',
            'lang'        => $payment_lang,
            'created_at'  => date('Y-m-d H:i:s')
        ];
        $this->db->insert('merchant_addon_transactions', $transaction);
        $transaction_id = $this->db->insert_id();

        log_message('info', "Addon delivery payment created for Ticket {$ticket_number} | Service ID: {$service_id} | Purchase ID: {$purchase_id} | Txn ID: {$transaction_id}");

        $this->initiate_payment($transaction_id, $amount);
    }

    /**
     * Initiate secure my.t money payment (RSA + HMAC)
     */
    private function initiate_payment($transaction_id, $amount)
    {
        $cfg = $this->config->item('myt_money');

        $merchantAppId = $cfg['app_id'];
        $apiKey        = $cfg['api_key'];
        $publicKey     = $cfg['public_key'];
        $notifyUrl     = base_url('addons/mytNotify');
        $returnUrl     = base_url('addons/mytCallback');
        $mode          = $cfg['mode'] ?? 'production';

        // Get saved payment language
        $transaction = $this->db
            ->get_where(
                'merchant_addon_transactions',
                ['id' => $transaction_id]
            )
            ->row_array();

        $payment_lang = !empty($transaction['lang'])
            ? $transaction['lang']
            : 'english';

        // Convert to MyT language
        $gateway_lang = ($payment_lang === 'french')
            ? 'fr'
            : 'en';

        // Create unique trade number
        $merTradeNo = (string)(microtime(true) * 1000);

        $this->db
            ->where('id', $transaction_id)
            ->update(
                'merchant_addon_transactions',
                [
                    'mer_trade_no' => $merTradeNo,
                    'updated_at'   => date('Y-m-d H:i:s')
                ]
            );

        // Prepare payload
        $payload = [
            "totalPrice" => $amount,
            "currency"   => "MUR",
            "merTradeNo" => $merTradeNo,
            "notifyUrl"  => $notifyUrl,
            "returnUrl"  => $returnUrl,
            "remark"     => "Addon Purchase",
            "lang"       => $gateway_lang
        ];

        $payloadJson = json_encode($payload);

        // Encrypt payload using RSA public key
        $rsa = new $this->rsaClass();
        if (defined($this->rsaClass . '::ENCRYPTION_OAEP')) {
            $rsa->setEncryptionMode($this->rsaClass::ENCRYPTION_OAEP);
        }
        $rsa->loadKey($publicKey);
        $encryptedPayload = base64_encode($rsa->encrypt($payloadJson));

        // Create HMAC signature
        $paymentType = "S";
        $signatureData = "appId={$merchantAppId}&merTradeNo={$merTradeNo}&payload={$encryptedPayload}&paymentType={$paymentType}";
        $sign = base64_encode(hash_hmac('sha512', $signatureData, $apiKey, true));

        // Gateway URL (sandbox or production)
        $gateway_url = ($mode === 'sandbox')
            ? "https://pay.sandbox.mytmoney.mu/Mt/web/payments"
            : "https://pay.mytmoney.mu/Mt/web/payments";

        log_message('info', "Addon Payment Payload: {$payloadJson}");
        log_message('info', "Addon Encrypted Payload: {$encryptedPayload}");
        log_message('info', "Addon Payment Signature: {$sign}");
        log_message('info', "Addon Gateway URL: {$gateway_url}");

        // Auto-submit secure payment form
        echo '<html><body>';
        echo '<form id="mytForm" method="POST" action="' . $gateway_url . '">';
        echo '<input type="hidden" name="appId" value="' . htmlspecialchars($merchantAppId) . '">';
        echo '<input type="hidden" name="merTradeNo" value="' . htmlspecialchars($merTradeNo) . '">';
        echo '<input type="hidden" name="payload" value="' . htmlspecialchars($encryptedPayload) . '">';
        echo '<input type="hidden" name="paymentType" value="' . htmlspecialchars($paymentType) . '">';
        echo '<input type="hidden" name="sign" value="' . htmlspecialchars($sign) . '">';
        echo '<p>Redirecting to payment gateway...</p>';
        echo '<script>document.getElementById("mytForm").submit();</script>';
        echo '</body></html>';
    }

    public function payment_callback($transaction_id)
    {
        $response = $this->input->post();

        // Log response
        $this->db->where('id', $transaction_id)
                 ->update('merchant_addon_transactions', [
                     'gateway_response' => json_encode($response),
                     'updated_at' => date('Y-m-d H:i:s')
                 ]);

        $status = (!empty($response['status']) && strtolower($response['status']) == 'success') ? 'success' : 'failed';
        $this->db->where('id', $transaction_id)->update('merchant_addon_transactions', ['status' => $status]);

        // Update purchase status
        $transaction = $this->db->get_where('merchant_addon_transactions', ['id' => $transaction_id])->row();
        if ($transaction && $status == 'success') {
            $this->db->where('id', $transaction->purchase_id)
                     ->update('merchant_addon_purchases', ['status' => 'paid']);
            $this->_processAddonPurchaseSuccess($transaction_id);
            $this->session->set_flashdata('success', 'Payment successful! Add-on activated.');
        } else {
            $this->db->where('id', $transaction->purchase_id)
                     ->update('merchant_addon_purchases', ['status' => 'cancelled']);
            $this->session->set_flashdata('error', 'Payment failed or cancelled.');
        }

        redirect('addons');
    }

    public function mytCallback()
    {
        log_message('info', '=== MyT Addon Callback Called ===');

        $merTradeNo = $this->input->get_post('merTradeNo') ?? $this->input->get('merTradeNo') ?? $this->input->post('merTradeNo');

        if (!$merTradeNo) {
            log_message('error', 'Missing merTradeNo in Addon callback.');
            show_error('Invalid payment reference. Missing merTradeNo.', 400);
            return;
        }

        // Fetch transaction
        $transaction = $this->db->get_where('merchant_addon_transactions', ['mer_trade_no' => $merTradeNo])->row_array();
        if (!$transaction) {
            log_message('error', 'Addon transaction not found for merTradeNo: ' . $merTradeNo);
            show_error('Transaction not found.', 404);
            return;
        }

        // If transaction is still initiated/pending, process callback data
        if ($transaction['status'] === 'initiated' || $transaction['status'] === 'pending') {
            $callbackData = array_merge($this->input->get() ?: [], $this->input->post() ?: []);
            if (!empty($callbackData['tradeStatus']) || isset($callbackData['resultCode'])) {
                $this->_handleMytAddonResponse(false, $callbackData);
                $transaction = $this->db->get_where('merchant_addon_transactions', ['id' => $transaction['id']])->row_array();
            }
        }

        // Redirect to Addon payment status page using encrypted token
        $this->load->library('encryption');
        $encrypted = $this->encryption->encrypt($transaction['id']);
        $token = rtrim(strtr(base64_encode($encrypted), '+/', '-_'), '=');
        $frontendUrl = base_url("addons/status/{$token}");
        redirect($frontendUrl);
    }

    public function mytNotify()
    {
        log_message('info', '=== MyT Addon Notify Called ===');

        $data = $this->input->get();
        log_message('info', 'MyT Addon Notify Data: ' . print_r($data, true));

        $this->_handleMytAddonResponse(true, $data);
    }

    private function _handleMytAddonResponse($isServerCall = true, $overrideData = null)
    {
        $data = $overrideData ?? array_merge($this->input->get(), $this->input->post());
        log_message('info', '=== MyT Addon Response Data Processed === ' . print_r($data, true));

        if (empty($data)) {
            log_message('error', 'MyT Addon Notify: Empty payload');
            if ($isServerCall) return $this->_sendJson(['status' => 'fail', 'message' => 'Empty data']);
            return;
        }

        $merTradeNo = $data['merTradeNo'] ?? null;
        if (!$merTradeNo) {
            log_message('error', 'MyT Addon Notify: Missing merTradeNo');
            if ($isServerCall) return $this->_sendJson(['status' => 'fail', 'message' => 'Missing merTradeNo']);
            return;
        }

        $transaction = $this->db->get_where('merchant_addon_transactions', ['mer_trade_no' => $merTradeNo])->row_array();
        if (!$transaction) {
            log_message('error', "Addon Transaction not found for merTradeNo: $merTradeNo");
            if ($isServerCall) return $this->_sendJson(['status' => 'fail', 'message' => 'Transaction not found']);
            return;
        }

        $tradeStatus = strtoupper($data['tradeStatus'] ?? '');

        if ($tradeStatus === 'TRADE_FINISHED' || ($data['resultCode'] ?? '') === '0') {
            $status = 'success';
        } else {
            $status = 'failed';
        }

        $updateData = [
            'status'            => $status,
            'transaction_ref'   => $data['tradeNo'] ?? null,
            'gateway_response'  => json_encode($data),
            'updated_at'        => date('Y-m-d H:i:s')
        ];
        $this->db->where('id', $transaction['id'])->update('merchant_addon_transactions', $updateData);

        $purchaseStatus = ($status === 'success') ? 'paid' : 'cancelled';
        $this->db->where('id', $transaction['purchase_id'])->update('merchant_addon_purchases', ['status' => $purchaseStatus]);

        if ($status === 'success') {
            log_message('info', "✅ MyT Addon Payment SUCCESS → Txn ID: {$transaction['id']} | merTradeNo: {$merTradeNo}");

            $res = $this->db->get_where('order_resolutions', ['addon_purchase_id' => $transaction['purchase_id']])->row();
            if ($res) {
                $this->load->model('OrderResolutionModel');
                $this->OrderResolutionModel->confirm_addon_payment($res->ticket_number, $transaction['purchase_id']);
            }

            // ==========================================
            // PROCESS NOTIFICATIONS & EMAILS (BELL ICON & EMAIL)
            // ==========================================
            $this->_processAddonPurchaseSuccess($transaction['id']);

        } else {
            log_message('error', "❌ MyT Addon Payment FAILED → Txn ID: {$transaction['id']} | tradeStatus: {$tradeStatus}");
        }  

        if ($isServerCall) return $this->_sendJson(['status' => $status, 'message' => 'Addon payment processed']);
    }

    public function status($token = null)
    {
        if (!$token) {
            show_error('Invalid request.', 400);
        }

        $this->load->library('encryption');

        $decoded = base64_decode(
            strtr($token, '-_', '+/')
        );

        if ($decoded === false) {
            show_error('Invalid token.', 400);
            return;
        }

        $transaction_id = $this->encryption->decrypt($decoded);

        if (!$transaction_id) {
            show_error('Invalid token or expired.', 400);
            return;
        }

        $txn = $this->db
            ->get_where(
                'merchant_addon_transactions',
                ['id' => $transaction_id]
            )
            ->row_array();

        if (!$txn) {
            show_error('Transaction not found.');
            return;
        }

        $purchase = $this->db
            ->get_where(
                'merchant_addon_purchases',
                ['id' => $txn['purchase_id']]
            )
            ->row_array();

        $payment_lang = !empty($txn['lang'])
            ? strtolower(trim($txn['lang']))
            : 'english';

        if ($payment_lang === 'french' || $payment_lang === 'fr') {
            $payment_lang = 'french';
            $this->session->set_userdata([
                'site_lang'            => 'french',
                'lcode'                => 'fr',
                'ldisplay_name'        => 'French',
                'lis_default_language' => 0
            ]);
        } else {
            $payment_lang = 'english';
            $this->session->set_userdata([
                'site_lang'            => 'english',
                'lcode'                => 'en',
                'ldisplay_name'        => 'English',
                'lis_default_language' => 0
            ]);
        }

        $this->lang->load('content', $payment_lang);

        log_message(
            'info',
            "Addon payment language restored: {$payment_lang} | Transaction ID: {$transaction_id}"
        );

        $merchant_id = $txn['merchant_id'] ?? null;

        if ($merchant_id && !$this->session->userdata('LoginID')) {
            $publisher = $this->db->get_where(
                'publisher',
                [
                    'id' => $merchant_id,
                    'status' => 1
                ]
            )->row();

            if ($publisher) {
                $LoginToken = bin2hex(random_bytes(16));

                $this->session->set_userdata([
                    'LoginID'    => $publisher->id,
                    'LoginToken' => $LoginToken
                ]);

                if (method_exists($this->UserModel, 'insertIntoLoginSession')) {
                    $this->UserModel->insertIntoLoginSession(
                        $LoginToken,
                        $publisher->id
                    );
                }

                $this->db
                    ->where('id', $publisher->id)
                    ->update(
                        'publisher',
                        [
                            'last_login_at' => time()
                        ]
                    );
            }
        }

        $res_ticket = $this->db->get_where('order_resolutions', ['addon_purchase_id' => $txn['purchase_id']])->row();
        if ($res_ticket) {
            if ($txn['status'] === 'success') {
                $service = $this->db->where('id', $purchase['service_id'])->get('addon_services')->row();
                $service_title = $service ? $service->title : 'YM Delivery';
                $txn_ref = !empty($txn['transaction_ref']) ? $txn['transaction_ref'] : (!empty($txn['mer_trade_no']) ? $txn['mer_trade_no'] : ('Purchase #' . $purchase['id']));

                $this->_processAddonPurchaseSuccess($txn['id']);

                $this->session->set_flashdata('addon_payment_success', [
                    'order_number'    => $res_ticket->order_number,
                    'ticket_number'   => $res_ticket->ticket_number,
                    'service_name'    => $service_title,
                    'amount'          => $txn['amount'],
                    'transaction_ref' => $txn_ref
                ]);
                $this->session->set_flashdata('success', 'Your YM Delivery AddOn Service payment has been confirmed. You can now proceed with completing the replacement.');
                redirect('order_resolution/view/' . $res_ticket->ticket_number . '?payment=success');
                return;
            } else {
                $this->db->where('id', $txn['id'])->update('merchant_addon_transactions', ['status' => 'failed', 'updated_at' => date('Y-m-d H:i:s')]);
                $this->db->where('id', $txn['purchase_id'])->update('merchant_addon_purchases', ['status' => 'cancelled']);

                $this->session->set_flashdata('error', 'AddOn Service payment was not completed or failed. Please click "Purchase AddOn Service" to try again.');
                redirect('order_resolution/view/' . $res_ticket->ticket_number . '?payment=failed');
                return;
            }
        }

        // Process notification and email for regular add-on purchases if payment succeeded
        if ($txn['status'] === 'success') {
            $this->_processAddonPurchaseSuccess($txn['id']);
        }

        $data['transaction']  = $txn;
        $data['purchase']     = $purchase;
        $data['payment_lang'] = $payment_lang;

        $this->load->view(
            'addons/payment_status',
            $data
        );
    }

    private function _sendJson($response)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function details($service_id)
    {
        $service = $this->Addon_model->get_service_by_id($service_id);
        if (!$service) show_404();
        $data['service'] = $service;
        $this->load->view('addons/service_details', $data);
    }

    public function manage() {
        $data['side_menu'] = 'manage_addons';
        $this->load->model('Addon_model');
        $data['manage_addons'] = $this->Addon_model->get_merchant_addons(); 

        $this->load->view('common/fbc-user/header', $data); 
        $this->load->view('addons/manage_addons_view', $data); 
        $this->load->view('common/fbc-user/footer');
    }

    public function download_invoice($purchase_id = NULL) 
    {
        if (empty($purchase_id)) {
            show_404();
        }

        $this->load->model('Addon_model');
        $data['addon_purchase'] = $this->Addon_model->get_purchase_by_id($purchase_id);

        if (empty($data['addon_purchase'])) {
            show_404();
        }

        // Try direct PDF generation via Dompdf so user can download PDF
        if (!class_exists('Dompdf\\Dompdf') && !class_exists('\\Dompdf\\Dompdf')) {
            $autoload_candidates = [
                APPPATH . '../vendor/autoload.php',
                APPPATH . '../../vendor/autoload.php',
                rtrim(FCPATH, '/\\') . '/vendor/autoload.php',
                rtrim(FCPATH, '/\\') . '/../vendor/autoload.php',
                rtrim(FCPATH, '/\\') . '/../merchant/vendor/autoload.php'
            ];
            foreach ($autoload_candidates as $cand) {
                if (file_exists($cand)) {
                    require_once $cand;
                    if (class_exists('Dompdf\\Dompdf') || class_exists('\\Dompdf\\Dompdf')) {
                        break;
                    }
                }
            }
        }

        if (empty($_GET['html']) && (class_exists('Dompdf\\Dompdf') || class_exists('\\Dompdf\\Dompdf'))) {
            try {
                $data['is_pdf'] = true;
                $htmldata = $this->load->view('addons/invoice_print_view', $data, true);

                $options = new \Dompdf\Options();
                $options->set('isRemoteEnabled', TRUE);
                $options->set('isHtml5ParserEnabled', TRUE);
                $options->set('isFontSubsettingEnabled', TRUE);
                $options->set('defaultFont', 'DejaVu Sans');

                $dompdf = new \Dompdf\Dompdf($options);
                $context = stream_context_create([
                    'ssl' => [
                        'verify_peer' => FALSE,
                        'verify_peer_name' => FALSE,
                        'allow_self_signed' => TRUE
                    ]
                ]);
                $dompdf->setHttpContext($context);
                $dompdf->loadHtml($htmldata);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $output = $dompdf->output();

                $filename = 'addon_invoice_' . $data['addon_purchase']->purchase_id . '.pdf';
                if (ob_get_length()) {
                    ob_end_clean();
                }
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . strlen($output));
                header('Cache-Control: private, max-age=0, must-revalidate');
                header('Pragma: public');
                echo $output;
                exit;
            } catch (\Throwable $e) {
                log_message('error', "Merchant Addon Dompdf error: " . $e->getMessage());
            }
        }

        $this->load->view('addons/invoice_print_view', $data);
    }

    /**
     * Process notifications and emails for a successful add-on service purchase
     * - In-app notification to Merchant (appears in bell icon)
     * - In-app notification to Admin (appears in bell icon & admin panel)
     * - Email to login merchant email, CC admin
     * - Fully idempotent (will not duplicate notifications or emails)
     */
    private function _processAddonPurchaseSuccess($transaction_id)
    {
        $transaction = $this->db
            ->get_where('merchant_addon_transactions', ['id' => $transaction_id])
            ->row_array();

        if (!$transaction || empty($transaction['purchase_id'])) {
            return;
        }

        $purchase = $this->db
            ->get_where('merchant_addon_purchases', ['id' => $transaction['purchase_id']])
            ->row_array();

        if (!$purchase) {
            return;
        }

        // Idempotency check: prevent duplicate notifications/emails for the same purchase
        $already_notified = $this->db
            ->where('recipient_type', 'merchant')
            ->where('type', 'addon_purchase')
            ->where('recipient_id', (int)$purchase['merchant_id'])
            ->group_start()
                ->like('data', '"purchase_id":' . (int)$purchase['id'])
                ->or_like('data', '"purchase_id":"' . (int)$purchase['id'] . '"')
            ->group_end()
            ->get('notifications')
            ->row();

        if ($already_notified) {
            log_message('info', "Addon purchase ID {$purchase['id']} has already been processed for notifications/emails. Skipping.");
            return;
        }

        $this->load->model('Notification_model');
        $this->load->model('CommonModel');

        // Fetch Service and Category details
        $service = $this->db->get_where('addon_services', ['id' => $purchase['service_id']])->row_array();
        $category = null;
        if (!empty($service['category_id'])) {
            $category = $this->db->get_where('addon_categories', ['id' => $service['category_id']])->row_array();
        }

        // Fetch Merchant details
        $merchant = $this->db->get_where('publisher', ['id' => $purchase['merchant_id']])->row_array();
        $merchant_email = !empty($merchant['email']) ? trim($merchant['email']) : '';
        if (empty($merchant_email) && $this->session->userdata('LoginID') == $purchase['merchant_id']) {
            $merchant_email = trim($this->session->userdata('email') ?? '');
        }

        $merchant_name = !empty($merchant['publication_name'])
            ? $merchant['publication_name']
            : (!empty($merchant['name']) ? $merchant['name'] : (!empty($merchant['company_name']) ? $merchant['company_name'] : ('Merchant #' . $purchase['merchant_id'])));

        // Language resolution
        $site_lang = !empty($transaction['lang']) ? $transaction['lang'] : ($this->session->userdata('site_lang') ?: ($merchant['language'] ?? 'english'));
        $is_french = (strtolower($site_lang) === 'french' || strtolower($site_lang) === 'fr');
        $lang_code = $is_french ? 'fr' : 'en';

        // Names & formatting
        $service_title = $service ? ($is_french && !empty($service['title_fr']) ? $service['title_fr'] : $service['title']) : 'Add-On Service';
        $category_name = $category ? ($is_french && !empty($category['name_fr']) ? $category['name_fr'] : $category['name']) : 'Add-On Services';

        $amount = !empty($transaction['amount']) ? $transaction['amount'] : ($service['final_price'] ?? 0);
        $amount_formatted = number_format((float)$amount, 2);
        $txn_ref = !empty($transaction['transaction_ref']) ? $transaction['transaction_ref'] : (!empty($transaction['mer_trade_no']) ? $transaction['mer_trade_no'] : ('Purchase #' . $purchase['id']));
        $date_formatted = !empty($purchase['created_at']) ? date('d-M-Y H:i', strtotime($purchase['created_at'])) : date('d-M-Y H:i');

        // ==========================================
        // 1. IN-APP NOTIFICATION FOR MERCHANT (BELL ICON)
        // ==========================================
        $merchant_notif_title = $is_french ? "Service Add-on Activé" : "Add-On Service Activated";
        $merchant_notif_msg = $is_french
            ? "Votre service add-on '{$service_title}' a été activé avec succès."
            : "Your add-on service '{$service_title}' has been successfully activated.";

        $merchant_notification = [
            'recipient_type' => 'merchant',
            'recipient_id'   => (int)$purchase['merchant_id'],
            'title'          => $merchant_notif_title,
            'message'        => $merchant_notif_msg,
            'type'           => 'addon_purchase',
            'subtype'        => 'success',
            'data'           => [
                'purchase_id'    => (int)$purchase['id'],
                'service_id'     => (int)$purchase['service_id'],
                'service_title'  => $service_title,
                'category_id'    => (int)($service['category_id'] ?? 0),
                'category_name'  => $category_name,
                'qty'            => (int)$purchase['qty'],
                'amount'         => $amount,
                'transaction_ref'=> $txn_ref,
                'type'           => 'addon_purchase'
            ],
            'is_read'        => 0
        ];
        $this->Notification_model->insert($merchant_notification);
        log_message('info', "Addon bell notification created for Merchant ID {$purchase['merchant_id']} | Purchase ID {$purchase['id']}");

        // ==========================================
        // 2. IN-APP NOTIFICATION FOR ADMIN (BELL ICON & ADMIN PANEL)
        // ==========================================
        $admin_notif_title = 'New Add-On Purchase';
        $admin_notif_msg = "Merchant {$merchant_name} successfully purchased '{$service_title}' for MUR {$amount_formatted}.";

        $admin_notification = [
            'recipient_type' => 'admin',
            'recipient_id'   => 1,
            'title'          => $admin_notif_title,
            'message'        => $admin_notif_msg,
            'type'           => 'addon_purchase',
            'subtype'        => 'success',
            'data'           => [
                'purchase_id'    => (int)$purchase['id'],
                'merchant_id'    => (int)$purchase['merchant_id'],
                'merchant_name'  => $merchant_name,
                'service_id'     => (int)$purchase['service_id'],
                'service_title'  => $service_title,
                'category_id'    => (int)($service['category_id'] ?? 0),
                'category_name'  => $category_name,
                'qty'            => (int)$purchase['qty'],
                'amount'         => $amount,
                'transaction_ref'=> $txn_ref,
                'type'           => 'addon_purchase'
            ],
            'is_read'        => 0
        ];
        $this->Notification_model->insert($admin_notification);
        log_message('info', "Addon bell notification created for Admin (ID 1) | Purchase ID {$purchase['id']}");

        if ($this->db->table_exists('admin_notifications')) {
            $exists = $this->db->get_where('admin_notifications', [
                'type'         => 'addon_purchase',
                'reference_id' => $purchase['id']
            ])->row();

            if (!$exists) {
                $this->db->insert('admin_notifications', [
                    'title'        => $admin_notif_title,
                    'message'      => $admin_notif_msg,
                    'type'         => 'addon_purchase',
                    'reference_id' => $purchase['id'],
                    'is_read'      => 0,
                    'created_at'   => date('Y-m-d H:i:s')
                ]);
            }
        }

        // ==========================================
        // 3. EMAIL TO LOGIN MERCHANT EMAIL (CC ADMIN)
        // ==========================================
        $admin_email = '';
        if (method_exists($this->CommonModel, 'get_custom_variable')) {
            $admin_email = $this->CommonModel->get_custom_variable('admin_email');
            if (empty($admin_email)) {
                $admin_email = $this->CommonModel->get_custom_variable('contact_us_email');
            }
        }
        if (empty($admin_email)) {
            $admin_email = $this->config->item('admin_email');
        }
        if (empty($admin_email)) {
            $admin_email = 'admin@yellowmarkets.com';
        }
        $admin_email = trim($admin_email);

        if (!empty($merchant_email) && filter_var($merchant_email, FILTER_VALIDATE_EMAIL)) {
            $manage_url = base_url('addons/manage');
            $invoice_url = base_url('addons/download_invoice/' . $purchase['id']);

            // Ensure template in email_template table if table exists
            $this->_ensureAddonEmailTemplate();

            if ($is_french) {
                $email_subject = "Confirmation d'achat du service Add-on : {$service_title} - Facture #{$purchase['id']}";
                $email_content = '<div style="font-family: Arial, Helvetica, sans-serif; color: #333333; line-height: 1.6; max-width: 600px; margin: 0 auto;">' .
                    '<h2 style="color: #222222; margin-top: 0;">Confirmation d\'achat de service Add-on</h2>' .
                    "<p>Bonjour <strong>{$merchant_name}</strong>,</p>" .
                    "<p>Merci pour votre paiement ! Nous vous confirmons que votre service add-on <strong>{$service_title}</strong> a été payé et activé avec succès sur votre compte marchand.</p>" .
                    '<div style="background-color: #fdfdfd; border: 1px solid #e0e0e0; border-left: 4px solid #f7a800; border-radius: 4px; padding: 18px 20px; margin: 25px 0;">' .
                    '<h3 style="margin: 0 0 15px 0; font-size: 16px; color: #111;">Détails de la commande</h3>' .
                    '<table style="width: 100%; border-collapse: collapse; font-size: 14px;">' .
                    '<tr><td style="padding: 6px 0; color: #666; width: 40%;"><strong>N° de commande :</strong></td><td style="padding: 6px 0; color: #222;">#' . $purchase['id'] . '</td></tr>' .
                    '<tr><td style="padding: 6px 0; color: #666;"><strong>Nom du service :</strong></td><td style="padding: 6px 0; color: #222;"><strong>' . htmlspecialchars($service_title) . '</strong></td></tr>' .
                    '<tr><td style="padding: 6px 0; color: #666;"><strong>Catégorie :</strong></td><td style="padding: 6px 0; color: #222;">' . htmlspecialchars($category_name) . '</td></tr>' .
                    '<tr><td style="padding: 6px 0; color: #666;"><strong>Quantité :</strong></td><td style="padding: 6px 0; color: #222;">' . (int)$purchase['qty'] . '</td></tr>' .
                    '<tr><td style="padding: 6px 0; color: #666;"><strong>Montant payé :</strong></td><td style="padding: 6px 0; color: #28a745; font-size: 16px;"><strong>MUR ' . $amount_formatted . '</strong></td></tr>' .
                    '<tr><td style="padding: 6px 0; color: #666;"><strong>Réf. transaction :</strong></td><td style="padding: 6px 0; color: #222;">' . htmlspecialchars($txn_ref) . '</td></tr>' .
                    '<tr><td style="padding: 6px 0; color: #666;"><strong>Date :</strong></td><td style="padding: 6px 0; color: #222;">' . $date_formatted . '</td></tr>' .
                    '</table>' .
                    '</div>' .
                    '<div style="text-align: left; margin: 25px 0;">' .
                    '<a href="' . $manage_url . '" style="background-color: #f7a800; color: #000000; padding: 12px 25px; text-decoration: none; font-weight: bold; border-radius: 4px; display: inline-block; margin-right: 10px;">Gérer mes Add-ons</a>' .
                    '<a href="' . $invoice_url . '" style="background-color: #f0f0f0; color: #333333; border: 1px solid #ccc; padding: 12px 22px; text-decoration: none; font-weight: bold; border-radius: 4px; display: inline-block;">Télécharger la facture</a>' .
                    '</div>' .
                    '<p style="margin-top: 30px;">Merci pour votre partenariat avec <strong>Yellow Markets</strong>.</p>' .
                    '<p style="margin-bottom: 0;">Cordialement,<br><strong>L\'équipe support Yellow Markets</strong></p>' .
                    '</div>';
            } else {
                $email_subject = "Add-On Service Purchase Confirmation: {$service_title} - Invoice #{$purchase['id']}";
                $email_content = '<div style="font-family: Arial, Helvetica, sans-serif; color: #333333; line-height: 1.6; max-width: 600px; margin: 0 auto;">' .
                    '<h2 style="color: #222222; margin-top: 0;">Add-On Service Purchase Confirmation</h2>' .
                    "<p>Dear <strong>{$merchant_name}</strong>,</p>" .
                    "<p>Thank you for your payment! We are pleased to confirm that your add-on service <strong>{$service_title}</strong> has been successfully paid and activated for your merchant account.</p>" .
                    '<div style="background-color: #fdfdfd; border: 1px solid #e0e0e0; border-left: 4px solid #f7a800; border-radius: 4px; padding: 18px 20px; margin: 25px 0;">' .
                    '<h3 style="margin: 0 0 15px 0; font-size: 16px; color: #111;">Purchase Summary</h3>' .
                    '<table style="width: 100%; border-collapse: collapse; font-size: 14px;">' .
                    '<tr><td style="padding: 6px 0; color: #666; width: 40%;"><strong>Order ID:</strong></td><td style="padding: 6px 0; color: #222;">#' . $purchase['id'] . '</td></tr>' .
                    '<tr><td style="padding: 6px 0; color: #666;"><strong>Service Name:</strong></td><td style="padding: 6px 0; color: #222;"><strong>' . htmlspecialchars($service_title) . '</strong></td></tr>' .
                    '<tr><td style="padding: 6px 0; color: #666;"><strong>Category:</strong></td><td style="padding: 6px 0; color: #222;">' . htmlspecialchars($category_name) . '</td></tr>' .
                    '<tr><td style="padding: 6px 0; color: #666;"><strong>Quantity:</strong></td><td style="padding: 6px 0; color: #222;">' . (int)$purchase['qty'] . '</td></tr>' .
                    '<tr><td style="padding: 6px 0; color: #666;"><strong>Amount Paid:</strong></td><td style="padding: 6px 0; color: #28a745; font-size: 16px;"><strong>MUR ' . $amount_formatted . '</strong></td></tr>' .
                    '<tr><td style="padding: 6px 0; color: #666;"><strong>Transaction Reference:</strong></td><td style="padding: 6px 0; color: #222;">' . htmlspecialchars($txn_ref) . '</td></tr>' .
                    '<tr><td style="padding: 6px 0; color: #666;"><strong>Payment Date:</strong></td><td style="padding: 6px 0; color: #222;">' . $date_formatted . '</td></tr>' .
                    '</table>' .
                    '</div>' .
                    '<div style="text-align: left; margin: 25px 0;">' .
                    '<a href="' . $manage_url . '" style="background-color: #f7a800; color: #000000; padding: 12px 25px; text-decoration: none; font-weight: bold; border-radius: 4px; display: inline-block; margin-right: 10px;">Manage Add-Ons</a>' .
                    '<a href="' . $invoice_url . '" style="background-color: #f0f0f0; color: #333333; border: 1px solid #ccc; padding: 12px 22px; text-decoration: none; font-weight: bold; border-radius: 4px; display: inline-block;">Download Invoice</a>' .
                    '</div>' .
                    '<p style="margin-top: 30px;">Thank you for partnering with <strong>Yellow Markets</strong>.</p>' .
                    '<p style="margin-bottom: 0;">Best regards,<br><strong>Yellow Markets Support Team</strong></p>' .
                    '</div>';
            }

            $tempVars = [
                '##MERCHANT_NAME##', '{merchant_name}',
                '##INVOICE_ID##', '{invoice_id}',
                '##SERVICE_TITLE##', '{service_title}',
                '##CATEGORY_NAME##', '{category_name}',
                '##QTY##', '{qty}',
                '##AMOUNT##', '{amount}',
                '##TRANSACTION_REF##', '{transaction_ref}',
                '##DATE##', '{date}',
                '##MANAGE_URL##', '{manage_url}',
                '##INVOICE_URL##', '{invoice_url}'
            ];

            $dynamicVars = [
                $merchant_name, $merchant_name,
                $purchase['id'], $purchase['id'],
                $service_title, $service_title,
                $category_name, $category_name,
                $purchase['qty'], $purchase['qty'],
                $amount_formatted, $amount_formatted,
                $txn_ref, $txn_ref,
                $date_formatted, $date_formatted,
                $manage_url, $manage_url,
                $invoice_url, $invoice_url
            ];

            $mail_sent = false;
            try {
                $mail_sent = $this->CommonModel->sendCommonHTMLEmail(
                    $merchant_email,
                    'merchant-addon-purchase-confirmation',
                    $tempVars,
                    $dynamicVars,
                    $lang_code,
                    $email_subject,
                    $email_content,
                    '',
                    $admin_email
                );
            } catch (\Throwable $e) {
                log_message('error', "sendCommonHTMLEmail error for Addon purchase {$purchase['id']}: " . $e->getMessage());
            }

            if (!$mail_sent) {
                try {
                    $this->load->library('email');
                    $this->email->clear(TRUE);
                    $this->email->from('no-reply@yellowmarkets.com', 'Yellow Markets');
                    $this->email->to($merchant_email);
                    if (!empty($admin_email)) {
                        $this->email->cc($admin_email);
                    }
                    $this->email->subject($email_subject);
                    $this->email->message($email_content);
                    $this->email->set_mailtype('html');
                    @$this->email->send();
                    log_message('info', "Sent Addon purchase confirmation email via direct CI email library to {$merchant_email} (CC: {$admin_email})");
                } catch (\Throwable $e) {
                    log_message('error', "Direct CI email send failed for Addon purchase {$purchase['id']}: " . $e->getMessage());
                }
            } else {
                log_message('info', "Sent Addon purchase confirmation email via sendCommonHTMLEmail to {$merchant_email} (CC: {$admin_email})");
            }
        } else {
            log_message('error', "Invalid or missing merchant email for Addon purchase ID {$purchase['id']} (Merchant ID: {$purchase['merchant_id']})");
        }

        // ==========================================
        // 4. DEDICATED NOTIFICATION EMAIL TO ADMIN
        // ==========================================
        if (!empty($admin_email) && filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
            $admin_manage_url = 'https://mu.yellowmarkets.com/admin/addons/manage';
            $admin_invoice_url = 'https://mu.yellowmarkets.com/admin/addons/download_invoice/' . $purchase['id'];
            if (defined('BASE_URL')) {
                $admin_manage_url = str_replace('/merchant/', '/admin/', BASE_URL) . 'addons/manage';
                $admin_invoice_url = str_replace('/merchant/', '/admin/', BASE_URL) . 'addons/download_invoice/' . $purchase['id'];
            }

            $admin_email_subject = "New Add-On Purchased: {$service_title} by {$merchant_name} (Order #{$purchase['id']})";
            $admin_email_content = '<div style="font-family: Arial, Helvetica, sans-serif; color: #333333; line-height: 1.6; max-width: 600px; margin: 0 auto;">' .
                '<div style="background-color: #222222; color: #ffffff; padding: 16px 20px; border-radius: 4px 4px 0 0;">' .
                '<h2 style="color: #f7a800; margin: 0; font-size: 20px;">New Add-On Service Purchased</h2>' .
                '</div>' .
                '<div style="padding: 20px; border: 1px solid #e0e0e0; border-top: none; border-radius: 0 0 4px 4px; background-color: #ffffff;">' .
                '<p>Hello Admin,</p>' .
                "<p>A merchant has successfully purchased and paid for an add-on service on <strong>Yellow Markets</strong>.</p>" .
                '<div style="background-color: #fdfdfd; border: 1px solid #e0e0e0; border-left: 4px solid #f7a800; border-radius: 4px; padding: 15px 18px; margin: 20px 0;">' .
                '<h3 style="margin: 0 0 12px 0; font-size: 15px; color: #111;">Purchase & Merchant Details</h3>' .
                '<table style="width: 100%; border-collapse: collapse; font-size: 14px;">' .
                '<tr><td style="padding: 6px 0; color: #666; width: 40%;"><strong>Order ID:</strong></td><td style="padding: 6px 0; color: #222;">#' . $purchase['id'] . '</td></tr>' .
                '<tr><td style="padding: 6px 0; color: #666;"><strong>Merchant Name:</strong></td><td style="padding: 6px 0; color: #222;"><strong>' . htmlspecialchars($merchant_name) . '</strong> (ID: #' . (int)$purchase['merchant_id'] . ')</td></tr>' .
                '<tr><td style="padding: 6px 0; color: #666;"><strong>Merchant Email:</strong></td><td style="padding: 6px 0; color: #222;">' . htmlspecialchars($merchant_email) . '</td></tr>' .
                '<tr><td style="padding: 6px 0; color: #666;"><strong>Service Name:</strong></td><td style="padding: 6px 0; color: #222;"><strong>' . htmlspecialchars($service_title) . '</strong></td></tr>' .
                '<tr><td style="padding: 6px 0; color: #666;"><strong>Category:</strong></td><td style="padding: 6px 0; color: #222;">' . htmlspecialchars($category_name) . '</td></tr>' .
                '<tr><td style="padding: 6px 0; color: #666;"><strong>Quantity:</strong></td><td style="padding: 6px 0; color: #222;">' . (int)$purchase['qty'] . '</td></tr>' .
                '<tr><td style="padding: 6px 0; color: #666;"><strong>Amount Paid:</strong></td><td style="padding: 6px 0; color: #28a745; font-size: 16px;"><strong>MUR ' . $amount_formatted . '</strong></td></tr>' .
                '<tr><td style="padding: 6px 0; color: #666;"><strong>Transaction Ref:</strong></td><td style="padding: 6px 0; color: #222;">' . htmlspecialchars($txn_ref) . '</td></tr>' .
                '<tr><td style="padding: 6px 0; color: #666;"><strong>Payment Date:</strong></td><td style="padding: 6px 0; color: #222;">' . $date_formatted . '</td></tr>' .
                '</table>' .
                '</div>' .
                '<div style="text-align: left; margin: 25px 0;">' .
                '<a href="' . $admin_manage_url . '" style="background-color: #f7a800; color: #000000; padding: 12px 24px; text-decoration: none; font-weight: bold; border-radius: 4px; display: inline-block; margin-right: 10px;">Manage Add-Ons (Admin)</a>' .
                '<a href="' . $admin_invoice_url . '" style="background-color: #f0f0f0; color: #333333; border: 1px solid #ccc; padding: 12px 20px; text-decoration: none; font-weight: bold; border-radius: 4px; display: inline-block;">Download Invoice</a>' .
                '</div>' .
                '<p style="margin-top: 25px; font-size: 13px; color: #777;">This is an automated notification from Yellow Markets System.</p>' .
                '</div>' .
                '</div>';

            try {
                $this->load->library('email');
                $this->email->clear(TRUE);
                $this->email->from('no-reply@yellowmarkets.com', 'Yellow Markets');
                $this->email->to($admin_email);
                $this->email->subject($admin_email_subject);
                $this->email->message($admin_email_content);
                $this->email->set_mailtype('html');
                @$this->email->send();
                log_message('info', "Sent Addon purchase alert email to Admin ({$admin_email}) for Purchase ID {$purchase['id']}");
            } catch (\Throwable $e) {
                log_message('error', "Failed to send Addon purchase alert email to Admin ({$admin_email}): " . $e->getMessage());
            }
        }
    }

    /**
     * Ensure email template for merchant add-on purchase confirmation exists
     */
    private function _ensureAddonEmailTemplate()
    {
        if (!$this->db->table_exists('email_template')) {
            return;
        }

        $tpl = $this->db->get_where('email_template', ['email_code' => 'merchant-addon-purchase-confirmation'])->row_array();
        if (!$tpl) {
            $has_fr = $this->db->field_exists('subject_fr', 'email_template');
            $tpl_data = [
                'email_code' => 'merchant-addon-purchase-confirmation',
                'title'      => 'Merchant Add-on Purchase Confirmation',
                'subject'    => 'Add-On Service Purchase Confirmation: ##SERVICE_TITLE## - Invoice ###INVOICE_ID##',
                'content'    => '<div style="font-family: Arial, Helvetica, sans-serif; color: #333333; line-height: 1.6; max-width: 600px; margin: 0 auto;">' .
                    '<h2 style="color: #222222; margin-top: 0;">Add-On Service Purchase Confirmation</h2>' .
                    '<p>Dear <strong>##MERCHANT_NAME##</strong>,</p>' .
                    '<p>Thank you for your payment! We are pleased to confirm that your add-on service <strong>##SERVICE_TITLE##</strong> has been successfully paid and activated for your merchant account.</p>' .
                    '<div style="background-color: #fdfdfd; border: 1px solid #e0e0e0; border-left: 4px solid #f7a800; border-radius: 4px; padding: 18px 20px; margin: 25px 0;">' .
                    '<h3 style="margin: 0 0 15px 0; font-size: 16px; color: #111;">Purchase Summary</h3>' .
                    '<table style="width: 100%; border-collapse: collapse; font-size: 14px;">' .
                    '<tr><td style="padding: 6px 0; color: #666; width: 40%;"><strong>Order ID:</strong></td><td style="padding: 6px 0; color: #222;">###INVOICE_ID##</td></tr>' .
                    '<tr><td style="padding: 6px 0; color: #666;"><strong>Service Name:</strong></td><td style="padding: 6px 0; color: #222;"><strong>##SERVICE_TITLE##</strong></td></tr>' .
                    '<tr><td style="padding: 6px 0; color: #666;"><strong>Category:</strong></td><td style="padding: 6px 0; color: #222;">##CATEGORY_NAME##</td></tr>' .
                    '<tr><td style="padding: 6px 0; color: #666;"><strong>Quantity:</strong></td><td style="padding: 6px 0; color: #222;">##QTY##</td></tr>' .
                    '<tr><td style="padding: 6px 0; color: #666;"><strong>Amount Paid:</strong></td><td style="padding: 6px 0; color: #28a745; font-size: 16px;"><strong>MUR ##AMOUNT##</strong></td></tr>' .
                    '<tr><td style="padding: 6px 0; color: #666;"><strong>Transaction Reference:</strong></td><td style="padding: 6px 0; color: #222;">##TRANSACTION_REF##</td></tr>' .
                    '<tr><td style="padding: 6px 0; color: #666;"><strong>Payment Date:</strong></td><td style="padding: 6px 0; color: #222;">##DATE##</td></tr>' .
                    '</table>' .
                    '</div>' .
                    '<div style="text-align: left; margin: 25px 0;">' .
                    '<a href="##MANAGE_URL##" style="background-color: #f7a800; color: #000000; padding: 12px 25px; text-decoration: none; font-weight: bold; border-radius: 4px; display: inline-block; margin-right: 10px;">Manage Add-Ons</a>' .
                    '<a href="##INVOICE_URL##" style="background-color: #f0f0f0; color: #333333; border: 1px solid #ccc; padding: 12px 22px; text-decoration: none; font-weight: bold; border-radius: 4px; display: inline-block;">Download Invoice</a>' .
                    '</div>' .
                    '<p style="margin-top: 30px;">Thank you for partnering with <strong>Yellow Markets</strong>.</p>' .
                    '<p style="margin-bottom: 0;">Best regards,<br><strong>Yellow Markets Support Team</strong></p>' .
                    '</div>',
                'status'     => 1,
                'created_by' => 1,
                'created_at' => time()
            ];
            if ($has_fr) {
                if ($this->db->field_exists('title_fr', 'email_template')) {
                    $tpl_data['title_fr'] = "Confirmation d'achat du service Add-on";
                }
                $tpl_data['subject_fr'] = "Confirmation d'achat du service Add-on : ##SERVICE_TITLE## - Facture ###INVOICE_ID##";
                $tpl_data['content_fr'] = '<div style="font-family: Arial, Helvetica, sans-serif; color: #333333; line-height: 1.6; max-width: 600px; margin: 0 auto;">' .
                    '<h2 style="color: #222222; margin-top: 0;">Confirmation d\'achat de service Add-on</h2>' .
                    '<p>Bonjour <strong>##MERCHANT_NAME##</strong>,</p>' .
                    '<p>Merci pour votre paiement ! Nous vous confirmons que votre service add-on <strong>##SERVICE_TITLE##</strong> a été payé et activé avec succès sur votre compte marchand.</p>' .
                    '<div style="background-color: #fdfdfd; border: 1px solid #e0e0e0; border-left: 4px solid #f7a800; border-radius: 4px; padding: 18px 20px; margin: 25px 0;">' .
                    '<h3 style="margin: 0 0 15px 0; font-size: 16px; color: #111;">Détails de la commande</h3>' .
                    '<table style="width: 100%; border-collapse: collapse; font-size: 14px;">' .
                    '<tr><td style="padding: 6px 0; color: #666; width: 40%;"><strong>N° de commande :</strong></td><td style="padding: 6px 0; color: #222;">###INVOICE_ID##</td></tr>' .
                    '<tr><td style="padding: 6px 0; color: #666;"><strong>Nom du service :</strong></td><td style="padding: 6px 0; color: #222;"><strong>##SERVICE_TITLE##</strong></td></tr>' .
                    '<tr><td style="padding: 6px 0; color: #666;"><strong>Catégorie :</strong></td><td style="padding: 6px 0; color: #222;">##CATEGORY_NAME##</td></tr>' .
                    '<tr><td style="padding: 6px 0; color: #666;"><strong>Quantité :</strong></td><td style="padding: 6px 0; color: #222;">##QTY##</td></tr>' .
                    '<tr><td style="padding: 6px 0; color: #666;"><strong>Montant payé :</strong></td><td style="padding: 6px 0; color: #28a745; font-size: 16px;"><strong>MUR ##AMOUNT##</strong></td></tr>' .
                    '<tr><td style="padding: 6px 0; color: #666;"><strong>Réf. transaction :</strong></td><td style="padding: 6px 0; color: #222;">##TRANSACTION_REF##</td></tr>' .
                    '<tr><td style="padding: 6px 0; color: #666;"><strong>Date :</strong></td><td style="padding: 6px 0; color: #222;">##DATE##</td></tr>' .
                    '</table>' .
                    '</div>' .
                    '<div style="text-align: left; margin: 25px 0;">' .
                    '<a href="##MANAGE_URL##" style="background-color: #f7a800; color: #000000; padding: 12px 25px; text-decoration: none; font-weight: bold; border-radius: 4px; display: inline-block; margin-right: 10px;">Gérer mes Add-ons</a>' .
                    '<a href="##INVOICE_URL##" style="background-color: #f0f0f0; color: #333333; border: 1px solid #ccc; padding: 12px 22px; text-decoration: none; font-weight: bold; border-radius: 4px; display: inline-block;">Télécharger la facture</a>' .
                    '</div>' .
                    '<p style="margin-top: 30px;">Merci pour votre partenariat avec <strong>Yellow Markets</strong>.</p>' .
                    '<p style="margin-bottom: 0;">Cordialement,<br><strong>L\'équipe support Yellow Markets</strong></p>' .
                    '</div>';
            }
            $this->db->insert('email_template', $tpl_data);
        }
    }
}