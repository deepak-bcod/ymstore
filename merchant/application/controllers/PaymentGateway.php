<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Path where phpseclib is placed
$phpseclib_base = rtrim(APPPATH, '/') . '/third_party/phpseclib/';

// Autoloader for phpseclib (v2 or v3)
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
            if (file_exists($file)) { require_once $file; return; }
        }
    }
});

// Detect RSA class
$PHPSECLIB_RSA_CLASS = null;
if (class_exists('\\phpseclib\\Crypt\\RSA')) $PHPSECLIB_RSA_CLASS = '\\phpseclib\\Crypt\\RSA';
elseif (class_exists('\\phpseclib3\\Crypt\\RSA')) $PHPSECLIB_RSA_CLASS = '\\phpseclib3\\Crypt\\RSA';
if ($PHPSECLIB_RSA_CLASS === null) {
    log_message('error', 'phpseclib RSA class not found.');
    show_error('phpseclib RSA class not found. Please install phpseclib.');
}

class PaymentGateway extends CI_Controller {

    private $rsaClass;

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->model('Subscription_model');
        $this->load->model('UserModel');
        $this->load->model('CommonModel');

        global $PHPSECLIB_RSA_CLASS;
        $this->rsaClass = $PHPSECLIB_RSA_CLASS;

        if (!$this->rsaClass) {
            show_error('phpseclib RSA class not available');
        }

        $this->config->load('payment');
    }

    /**
     * Step 1: Initiate My.T Money payment
     */
    public function mytMoney($order_id = null) {
        $order = $this->db->get_where('subscription_orders', ['id' => $order_id])->row_array();
        if (!$order) show_404();

        $cfg = $this->config->item('myt_money');

        $merchantAppId = $cfg['app_id'];
        $apiKey        = $cfg['api_key'];
        $publicKey     = $cfg['public_key'];
        $notifyUrl     = $cfg['notify_url'];
        $returnUrl     = $cfg['return_url'];
        $mode          = $cfg['mode'];

        $merTradeNo = (int)(microtime(true) * 1000);
        $this->db->where('id', $order_id)->update('subscription_orders', ['mer_trade_no' => $merTradeNo]);

        $payload = [
            "totalPrice" => $order['amount'],
            "currency"   => "MUR",
            "merTradeNo" => $merTradeNo,
            "notifyUrl"  => $notifyUrl,
            "returnUrl"  => $returnUrl,
            "remark"     => "Subscription Plan Payment",
            "lang"       => "en"
        ];

        $payloadJson = json_encode($payload);

        // Encrypt payload with RSA
        $rsa = new $this->rsaClass();
        $rsa->setEncryptionMode($this->rsaClass::ENCRYPTION_OAEP);
        $rsa->loadKey($publicKey);
        $encryptedPayload = base64_encode($rsa->encrypt($payloadJson));

        // Create HMAC signature
        $paymentType = "S";
        $signatureData = "appId={$merchantAppId}&merTradeNo={$merTradeNo}&payload={$encryptedPayload}&paymentType={$paymentType}";
        $sign = base64_encode(hash_hmac('sha512', $signatureData, $apiKey, true));

        log_message('info', "MyT Payment Initiation → Order ID: {$order_id} | Publisher ID: {$order['publisher_id']} | merTradeNo: {$merTradeNo}");

        $gatewayUrl = $mode === 'sandbox'
            ? "https://pay.mytmoney.mu/Mt/web/payments"
            : "https://pay.mytmoney.mu/Mt/web/payments";

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

    /**
     * Step 2A: My.T Money Server-to-Server Notify (GET/POST)
     */
    public function mytNotify() {
        log_message('info', '=== MyT Notify Called ===');

        $data = array_merge($this->input->get() ?: [], $this->input->post() ?: []);
        log_message('info', 'MyT Notify Data: ' . print_r($data, true));

        $this->_handleMytResponse(true, $data);
    }

    /**
     * Step 2B: My.T Money Return (Browser Redirect)
     * Verifies order, safely restores merchant session if lost, and redirects to subscription page.
     */
    public function mytCallback()
    {
        log_message('info', '=== MyT Callback Called ===');

        $merTradeNo = $this->input->get_post('merTradeNo') ?? $this->input->get('merTradeNo') ?? $this->input->post('merTradeNo');

        log_message('info', "MyT Callback merTradeNo: " . ($merTradeNo ? $merTradeNo : 'none') . " | Method: " . $this->input->method(TRUE));

        if (!$merTradeNo) {
            log_message('error', 'Missing merTradeNo in callback.');
            $this->session->set_flashdata('error', 'Missing payment reference.');
            redirect(base_url('subscription?payment=error'));
            return;
        }

        // Fetch subscription order
        $order = $this->db->get_where('subscription_orders', ['mer_trade_no' => $merTradeNo])->row_array();

        if (!$order) {
            log_message('error', 'Order not found for merTradeNo: ' . $merTradeNo);
            $this->session->set_flashdata('error', 'Order not found.');
            redirect(base_url('subscription?payment=error'));
            return;
        }

        // If order is still pending, process callback status data
        if ($order['status'] === 'pending') {
            $callbackData = array_merge($this->input->get() ?: [], $this->input->post() ?: []);
            $this->_handleMytResponse(false, $callbackData);
            // Reload updated order record
            $order = $this->db->get_where('subscription_orders', ['id' => $order['id']])->row_array();
        }

        // ============================================
        // SECURE MERCHANT SESSION VERIFICATION & RESTORATION
        // ============================================
        $currentLoginId = $this->session->userdata('LoginID');
        $orderPublisherId = (int)$order['publisher_id'];

        if (empty($currentLoginId) || (int)$currentLoginId !== $orderPublisherId) {
            log_message('info', "Merchant session missing or mismatched in callback (Session LoginID: " . ($currentLoginId ?: 'null') . ", Order PublisherID: {$orderPublisherId}). Restoring session...");

            $publisher = $this->db->get_where('publisher', [
                'id'          => $orderPublisherId,
                'remove_flag' => 0,
                'status'      => 1
            ])->row();

            if ($publisher) {
                $LoginToken = bin2hex(random_bytes(16));

                // Find shop details if any
                $shop_id = $publisher->id;
                $ShopOwnerId = $publisher->id;
                if (isset($this->UserModel) && method_exists($this->UserModel, 'getShopDetailsByShopId')) {
                    $ShopDetails = $this->UserModel->getShopDetailsByShopId($shop_id);
                    if ($ShopDetails && !empty($ShopDetails->fbc_user_id)) {
                        $ShopOwnerId = $ShopDetails->fbc_user_id;
                    }
                }

                $sessionArr = [
                    'LoginID'     => $publisher->id,
                    'LoginToken'  => $LoginToken,
                    'ShopID'      => $shop_id,
                    'ShopOwnerId' => $ShopOwnerId,
                    'UserRole'    => ''
                ];

                $this->session->set_userdata($sessionArr);
                $_SESSION['LoginID'] = $publisher->id;
                $_SESSION['LoginToken'] = $LoginToken;

                if (isset($this->UserModel) && method_exists($this->UserModel, 'insertIntoLoginSession')) {
                    $this->UserModel->insertIntoLoginSession($LoginToken, $publisher->id);
                }

                $this->db->where('id', $publisher->id)->update('publisher', [
                    'last_login_at' => time()
                ]);

                log_message('info', "Merchant session successfully restored for Publisher ID: {$publisher->id}");
            } else {
                log_message('error', "Publisher not found or inactive for ID: {$orderPublisherId}. Cannot restore session.");
            }
        } else {
            log_message('info', "Existing merchant session verified for Publisher ID: {$currentLoginId}");
        }

        // Redirect to merchant subscription page with appropriate status
        if ($order['status'] === 'paid') {
            redirect(base_url('subscription?payment=success'));
        } elseif ($order['status'] === 'failed') {
            redirect(base_url('subscription?payment=error'));
        } else {
            redirect(base_url('subscription?payment=pending'));
        }
    }

    /**
     * Handle My.T Money response (Server-to-Server Notify & Callback fallback)
     */
    private function _handleMytResponse($isServerCall = true, $overrideData = null) {
        $data = $overrideData ?? array_merge($this->input->get() ?: [], $this->input->post() ?: []);
        log_message('info', '=== MyT Response Data Processed === ' . print_r($data, true));

        if (empty($data)) {
            log_message('error', 'MyT Response: Empty payload');
            if ($isServerCall) return $this->_sendJson(['status' => 'fail', 'message' => 'Empty data']);
            return;
        }

        $merTradeNo = $data['merTradeNo'] ?? null;
        if (!$merTradeNo) {
            log_message('error', 'MyT Response: Missing merTradeNo');
            if ($isServerCall) return $this->_sendJson(['status' => 'fail', 'message' => 'Missing merTradeNo']);
            return;
        }

        $order = $this->db->get_where('subscription_orders', ['mer_trade_no' => $merTradeNo])->row_array();
        if (!$order) {
            log_message('error', "Order not found for merTradeNo: $merTradeNo");
            if ($isServerCall) return $this->_sendJson(['status' => 'fail', 'message' => 'Order not found']);
            return;
        }

        $tradeStatus = strtoupper($data['tradeStatus'] ?? '');
        $errorCode   = $data['errorCode'] ?? '';
        $timestamp   = $data['timestamp'] ?? '';

        if ($tradeStatus === 'TRADE_FINISHED' || ($data['resultCode'] ?? '') === '0' || $tradeStatus === 'TRADE_SUCCESS' || $tradeStatus === 'SUCCESS') {
            $status = 'paid';
        } else {
            $status = ($tradeStatus !== '' || $errorCode !== '') ? 'failed' : $order['status'];
        }

        $alreadyPaid = ($order['status'] === 'paid');

        $updateData = [
            'status'            => $status,
            'transaction_id'    => $data['tradeNo'] ?? $order['transaction_id'] ?? null,
            'error_code'        => $errorCode ?: ($order['error_code'] ?? null),
            'payment_timestamp' => $timestamp ?: ($order['payment_timestamp'] ?? null),
            'updated_at'        => date('Y-m-d H:i:s')
        ];

        $this->db->where('id', $order['id'])->update('subscription_orders', $updateData); 

        if ($status === 'paid') {
            if (!$alreadyPaid) {
                $this->Subscription_model->subscribe_plan($order['publisher_id'], $order['plan_id'], $this->input->ip_address());
                log_message('info', "MyT Payment SUCCESS → Activated Plan for Order ID: {$order['id']} | Publisher: {$order['publisher_id']} | merTradeNo: {$merTradeNo}");
            } else {
                log_message('info', "MyT Payment already marked paid for Order ID: {$order['id']}. Skipping duplicate activation.");
            }
        } else {
            log_message('error', "MyT Payment status: {$status} for Order ID: {$order['id']} | tradeStatus: {$tradeStatus}");
        }

        if ($isServerCall) return $this->_sendJson(['status' => $status, 'message' => 'Payment processed']);
    }

    /**
     * Send JSON response for server-to-server notify
     */
    private function _sendJson($data) {
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }



}
