<?php

class WC_Gateway_Payfull extends WC_Payment_Gateway
{
    const INSTALLMENTS_TYPE_TABLE = "table";
    const INSTALLMENTS_TYPE_LIST = "list";

    protected static $_instance = null;

    private $_payfull;

    public $username = null;
    public $password = null;
    public $custom_css = null;
    public $endpoint = null;
    public $enable_3dSecure = 1;
    public $force_3dSecure  = 0;
    public $force_3dSecure_debit  = 1;
    public $enable_installment = 1;
    public $enable_extra_installment = 0;
    public $enable_bkm = 0;
    public $currency_class;
    public $total_selector;
    public $options = [];

    public function __construct($register_hooks=false)
    {
        $this->id                   = 'payfull';
        $this->icon                 = plugins_url('assets/img/icon.png', dirname(__FILE__));
        $this->has_fields           = false;
        $this->method_title         = __('Payfull', 'payfull');
        $this->method_description   = __('Process payment via Payfull service.', 'payfull');
        $this->order_button_text    = __('Proceed to Payfull', 'payfull');

        $this->title                    = $this->get_option( 'title' );
        $this->enabled                  = $this->get_option('enabled');
        $this->description              = $this->get_option('description');
        $this->username                 = $this->get_option('username');
        $this->password                 = $this->get_option('password');
        $this->custom_css               = $this->get_option('custom_css');
        $this->endpoint                 = $this->get_option('endpoint');
        $this->currency_class           = $this->get_option('currency_class');
        $this->total_selector           = $this->get_option('total_selector');
        $this->enable_3dSecure          = $this->get_option('enable_3dSecure');
        $this->force_3dSecure           = $this->get_option('force_3dSecure');
        $this->enable_installment       = $this->get_option('enable_installment');
        $this->enable_extra_installment = $this->get_option('enable_extra_installment');
        $this->enable_bkm               = $this->get_option('enable_bkm');

        $this->payfull_init_form_fields();
        $this->payfull_init_settings();

        $this->supports = [
            'products',
            'refunds',
        ];

        if($register_hooks) {
            add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            add_action( 'woocommerce_api_'.strtolower(__CLASS__), array( &$this, 'payfull_check_payment_response' ) );
        }
    }

    public function payfull_version()
    {
        return "v1";
    }

    public function &payfull()
    {
        if(!$this->_payfull) {
            require_once 'PayfullService.php';
            $lang = get_locale();
            $lang = explode('_', $lang);
            $this->_payfull = new PayfullService([
                'username' => $this->username,
                'password' => $this->password,
                'endpoint' => $this->endpoint,
                'language' => $lang[0],
            ]);
        }
        return $this->_payfull;
    }

    public function payfull_initApiService()
    {
        add_rewrite_tag( '%payfull-api%', '([^&]+)' );
        add_action( 'template_redirect', array($this, 'payfull_handleApiRequest'));
    }

    public function payfull_handleApiRequest()
    {
        global $wp_query;
        $payfull = $wp_query->get( 'payfull-api' );

        if ( ! $payfull ) {
            return;
        }

        $params = explode('/', $payfull);
        $version = $params[0];
        $data = $_POST;
        $result = null;

        array_walk_recursive($data, function(&$item) {
            $item  = sanitize_text_field($item);
        });

        if(!isset($data['command'])) {
            throw new Exception("Invalide request.");
        }
        if($version!="v1") {
            throw new Exception("unsupported version.");
        }

        $cmd = $data['command'];
        switch($cmd) {
            case 'bin':
                $result = $this->payfull()->payfull_bin($data['bin']);
                break;
            case 'banks':
                $result = $this->payfull()->payfull_banks($data);
                break;
            case 'extra_ins':
                $result = $this->payfull()->payfull_extraInstallments($data);
                break;
            default:
                $result = ['error' => true, 'message'=>'Unsupported command'];
                break;
        }

        wp_send_json( $result );
    }

    /**
     * override
     */
    public function payfull_init_settings()
    {
        parent::init_settings();
    }

    public function payfull_init_form_fields()
    {
        $this->form_fields = [
            'enabled' => [
                'title' => __('Enabled', 'payfull'),
                'type' => 'checkbox',
                'default' => 'yes',
            ],
            'title' => [
                'title' => __('Title', 'payfull'),
                'type' => 'text',
                'description' => __('The title which the user will see in the checkout page.', 'payfull'),
                'default' => __('Payfull Checkout', 'payfull'),
            ],
            'description' => [
                'title' => __('Description', 'payfull'),
                'type' => 'textarea',
                'description' => __('The message to display during checkout.', 'payfull'),
                'default' => __('Pay via Payfull, pay safely with your credit card.', 'payfull'),
            ],
            'endpoint' => [
                'title' => __('Endpoint', 'payfull'),
                'type' => 'text',
                'description' => __('The api url to "Payfull" service.', 'payfull'),
                'default' => '',
            ],
            'username' => [
                'title' => __('Api Username', 'payfull'),
                'type' => 'text',
                'default' => '',
            ],
            'password' => [
                'title' => __('Api Password', 'payfull'),
                'type' => 'password',
                'default' => '',
            ],
            'enable_3dSecure' => [
                'title' => __('Enable 3D secure', 'payfull'),
                'type' => 'select',
                'options'     => [__( 'No', 'payfull' ),__( 'Yes', 'payfull' )],
                'description' => __('Choose whether to enable 3D secure payment option.', 'payfull'),
            ],
            'force_3dSecure' => [
                'title' => __('Force 3D Secure', 'payfull'),
                'type' => 'select',
                'options'     => [__( 'No', 'payfull' ),__( 'Yes', 'payfull' )],
                'description' => __('If 3D secure option is mandatory in Payfull side, this option must be enable. Otherwise your transactions will fail.', 'payfull'),
            ],
            'enable_installment' => [
                'title' => __('Enable Installment', 'payfull'),
                'type' => 'select',
                'options'     => [__( 'No', 'payfull' ),__( 'Yes', 'payfull' )],
                'description' => __('Choose whether to enable installment option.', 'payfull'),
            ],
            'enable_extra_installment' => [
                'title' => __('Enable Extra Installment', 'payfull'),
                'type' => 'select',
                'options'     => [__( 'No', 'payfull' ),__( 'Yes', 'payfull' )],
                'description' => __('Choose whether to enable extra installment option.', 'payfull'),
            ],
            'enable_bkm' => [
                'title' => __('Enable BKM Express', 'payfull'),
                'type' => 'select',
                'options'     => [__( 'No', 'payfull' ),__( 'Yes', 'payfull' )],
                'description' => __('Choose whether to enable BKM Express gateway.', 'payfull'),
            ],
            'total_selector' => [
                'title' => __('Total Selector', 'payfull'),
                'type' => 'text',
                'default' => '.order_details .amount',
                'description' => __('A jQuery selector of the HTML element that contains the total amount in checkout page.', 'payfull'),
            ],
            'currency_class' => [
                'title' => __('Currency Class', 'payfull'),
                'type' => 'text',
                'default' => 'woocommerce-Price-currencySymbol',
                'description' => __('The CSS class(es) to be applied to the curreny on checkout page', 'payfull'),
            ],
            'custom_css' => [
                'title' => __('Custom Css', 'payfull'),
                'type' => 'textarea',
                'default' => file_get_contents (WP_PLUGIN_DIR. '/payfull/assets/custom.css'),
            ],
        ];
    }

    public function receipt_page($order_id)
    {
        $order = new WC_Order(isset($order_id) ? $order_id : false);

        if($order===false) {
            throw new \Exception('Invalid request, the order is not recognized.');
        }

        $data = [];
        do_action( 'woocommerce_credit_card_form_start', $this->id );

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;

            array_walk_recursive($data, function(&$item) {
                $item = sanitize_text_field($item);
            });

            $errors = $this->payfull_validatePaymentForm($data);

            if($errors !== true) {
                foreach ($errors as $err) {
                    wc_add_notice($err, 'error');
                }
            } else {
                $this->payfull_sendPayment($order, $data);
            }
        }

        $this->renderView('views/payment-form.php', [
            'id'                        => esc_attr($this->id),
            'order'                     => $order,
            'args'                      => isset($args)?$args:[],
            'form'                      => $data,
            'total_selector'            => $this->get_option('total_selector'),
            'currency_class'            => $this->get_option('currency_class'),
            'currency_symbol'           => get_woocommerce_currency_symbol($order->get_currency()),
            'custom_css'                => $this->get_option('custom_css'),
            'enable_3dSecure'           => intval($this->enable_3dSecure) === 1,
            'force_3dSecure'            => intval($this->force_3dSecure) === 1,
            'enable_installment'        => intval($this->enable_installment)===1,
            'enable_extra_installment'  => intval($this->enable_extra_installment)===1,
            'enable_bkm'                => intval($this->enable_bkm)===1,
        ]);
        do_action( 'woocommerce_credit_card_form_end', $this->id );
    }

    public function process_payment($order_id )
    {
        global $woocommerce;
        $order      = wc_get_order( $order_id );

        if(!$order) {
            wc_add_notice( __('Failed to process the payment because of invalid order', 'payfull'), 'error' );
            return array(
                'result' => 'error'
            );
        }

        if($order) {
            $checkout_payment_url = $order->get_checkout_payment_url(true);
            return array(
                'result' => 'success',
                'redirect' => add_query_arg(
                    array(
                        'order-pay' => $order->get_id(),
                        'key' => $order->get_order_key(),
                    ),
                    $checkout_payment_url
                ),
            );
        }
        wc_add_notice( __('Failed to process the payment because of invalid order', 'payfull'), 'error' );
        return null;
    }

    protected function payfull_sendPayment($order, $data)
    {
        $use3d               = 0;
        $installments        = 1;
        $card                = isset($data['card']) ? $data['card'] : null;
        $extraInsCampaignId  = isset($data['campaign_id']) ? $data['campaign_id'] : null;

        if($this->enable_3dSecure && isset($data['use3d'])) {
            $use3d = ($data['use3d']=="true");
        }

        if($this->force_3dSecure) {
            $use3d = true;
        }

        if($this->force_3dSecure_debit) {
            $bin = str_replace(' ', '', $card['pan']);
            $bin = substr($bin, 0, 6);
            $cardInfo = $this->payfull()->payfull_bin($bin);
            if($cardInfo['status']) {
                $cardInfo = $cardInfo['data'];
                if( !isset($cardInfo['type']) OR $cardInfo['type'] != 'CREDIT' OR $cardInfo['type'] == null )
                    $use3d = true;
            } else {
                $use3d = true;
            }
        }

        if($this->enable_installment && isset($data['installment'])) {
            $installments = intval($data['installment']);
            $installments = $installments <=0 ? 1 : $installments;
        }

        $firstName  = @$order->get_billing_first_name();
        $lastName   = @$order->get_billing_last_name();
        $oId        = @$order->get_id();
        $oEmail     = @$order->get_billing_email();
        $oPhone     = @$order->get_billing_phone();

        $order->update_status('wc-pending', 'Process payment by Payfull');

        $request = [
            'total'                 => $order->get_total(),
            'currency'              => $order->get_currency(),
            'installments'          => $installments,
            'passive_data'          => $oId,
            'cc_name'               => $card['holder'],
            'cc_number'             => str_replace(' ', '', $card['pan']),
            'cc_month'              => $card['month'],
            'cc_year'               => $card['year'],
            'cc_cvc'                => $card['cvc'],
            'customer_firstname'    => $firstName,
            'customer_lastname'     => $lastName,
            'customer_email'        => $oEmail,
            'customer_phone'        => $oPhone,
            'payment_title'         => "{$firstName} {$lastName} | order $oId | ".$order->get_total().$order->get_currency(),
        ];

        $bank_id = isset($data['bank']) ? $data['bank'] : null;
        $gateway = isset($data['gateway']) ? $data['gateway'] : null;

        if(!isset($gateway, $bank_id) AND $installments > 1) {
            wc_add_notice( __('Invalid installment information.', 'payfull'), 'error' );
            return;
        }

        $total = $order->get_total();
        $fee = $this->payfull()->payfull_getCommission($total, $bank_id, $installments);
        WC()->session->set( 'installment_fee',    $fee );

        if($bank_id != '')              $request['bank_id']     = $bank_id;
        if($gateway != '')              $request['gateway']     = $gateway;
        if(isset($extraInsCampaignId))  $request['campaign_id'] = $extraInsCampaignId;

        if($use3d) {
            $checkout_url = $order->get_checkout_payment_url(true);
            $return_url = add_query_arg(['order-id'=>$oId, 'wc-api'=>'WC_Gateway_Payfull'], $checkout_url);
            $request['use3d'] = 1;
            $request['return_url'] = $return_url;
        }

        $data["useBKM"] = isset($data["useBKM"])?$data["useBKM"]:0;
        if($data["useBKM"]){
            unset($request['cc_name']);
            unset($request['cc_number']);
            unset($request['cc_month']);
            unset($request['cc_year']);
            unset($request['cc_cvc']);
            $request['installments'] = $this->enable_installment;
            $request['bank_id']      = 'BKMExpress';
            $checkout_url            = $order->get_checkout_payment_url(true);
            $return_url              = add_query_arg(['order-id'=>$order->get_id(), 'wc-api'=>'WC_Gateway_Payfull'], $checkout_url);
            $request['return_url']   = $return_url;
        }

        $return_json = !($use3d OR $data["useBKM"]);

        $response = $this->payfull()->payfull_send('Sale', $request, $return_json);

        if($response == null){
            $response = ['ErrorMSG' => $this->payfull_getErrorMessage($response,__('Invalid response received.', 'hepsipay'))];
            $message = $response['ErrorMSG'];
            wc_add_notice($message, 'error');
            return;
        }

        if($use3d or $data["useBKM"]) {
            if(strpos($response, '<html')===false AND strpos($response, '<form')===false AND json_decode($response) == null) {
                $error = $this->payfull_getErrorMessage($response,__('Invalid response received.', 'payfull'));
                wc_add_notice( $error, 'error' );
                $order->add_order_note('Could not complete the transaction.' . $error);
                return;
            }elseif(strpos($response, '<html')!==false OR strpos($response, '<form')!==false){
                echo $response;
                exit;
            }
        }

        $response = (@json_decode($response) == null)?$response:json_decode($response,true);

        if($this->payfull_processPaymentResponse($order, $response)) {
            $message = __('Thank you for shopping with us. Your transaction is succeeded.', 'payfull');
            wc_add_notice($message);
            $thank_url = $order->get_checkout_order_received_url();
            wp_redirect($thank_url);
            exit;
        } else {
            wc_add_notice($response['ErrorMSG'], 'error');
        }
    }

    protected static function payfull_sanitize_post_data($data)
    {
        array_walk_recursive($data, function(&$item) {
            $item  = sanitize_text_field($item);
        });
        return $data;
    }

    public function payfull_check_payment_response()
    {
        global $woocommerce;

        if(!defined( 'ABSPATH' )) {
            throw new \Exception('Wordpress is not running.');
        }

        if(!defined('WOOCOMMERCE_VERSION')) {
            throw new \Exception('WooCommerce is not running.');
        }

        $data           = self::payfull_sanitize_post_data($_POST);
        $tx             = isset($data['transaction_id']) ? $data['transaction_id'] : false;
        $order_id       = isset($data['passive_data']) ? $data['passive_data'] : (isset($_GET['order-id']) ? $_GET['order-id'] : null);
        $order          = wc_get_order($order_id);
        $hash           = $this->payfull_generateHash($data);
        $redirect_url   = $woocommerce->cart->get_checkout_url();

        if(!isset($order)) {
            $message = __('Order not found.', 'payfull');
            if($tx) {
                $message = printf(__('The payment is done but your order not found. Your transaction id is "%1$s"', 'payfull'), $tx);
            }
        } else if($hash != $data['hash']) {
            $message = __('Invalid hash code', 'payfull').' '.$order_id;
        } else {
            if($this->payfull_processPaymentResponse($order, $data)) {
                $message = __('Thank you for shopping with us. Your transaction is succeeded.', 'payfull');
                wc_add_notice($message);
                $redirect_url = $order->get_checkout_order_received_url();
                wp_redirect($redirect_url);
                exit;
            }
            else {
                $order->update_status('wc-failed', '3D Payment failed');
                $message = $this->payfull_getErrorMessage($data,__('Unexpected error occurred while processing your request.', 'payfull'));
                $order->add_order_note($message);
            }
        }
        // error happened:
        wc_add_notice($message, 'error');
        wp_redirect($redirect_url);
    }

    protected function payfull_generateHash($params)
    {
        $arr = [];
        unset($params['hash']);

        foreach($params as $param_key => $param_val) {
            $arr[strtolower($param_key)] = $param_val;

        }
        ksort($arr);
        $hashString_char_count = "";

        foreach ($arr as $key => $val) {
            $l =  mb_strlen($val);
            $hashString_char_count .= $l . $val;
        }

        $hashString_char_count      = strtolower(hash_hmac("sha1", $hashString_char_count, $this->password));

        return $hashString_char_count;
    }

    protected function payfull_processPaymentResponse($order, $response)
    {
        $hash = $this->payfull_generateHash($response);

        if(isset($response['status']) && $response['status']) {
            $xid = $response['transaction_id'];
            if(empty($xid)) {
                $order->add_order_note("Invalid response: Transaction id is missing.");
                return false;
            }

            if($hash != $response['hash'] AND !isset($response['html']) AND $response['use3d'] == 1){
                $order->add_order_note("Invalid hash code.");
                return false;
            }

            $order->add_order_note("Payment Via Payfull, Transaction ID: {$xid}");

            $installments      = isset($response['installments'])?$response['installments']:1;
            $extraInstallments = isset($response['extra_installments'])?$response['extra_installments']:'';

            $this->payfull_saveOrderCommission($order, WC()->session->get('installment_fee'), $installments, $extraInstallments);
            unset(WC()->session->installment_fee); // there is no need any more

            $order->update_status('wc-processing', "Payment succeeded. Transaction ID: {$xid}");
            $order->reduce_order_stock();
            $order->payment_complete($xid);
            WC()->cart->empty_cart();
            update_post_meta( $order->get_id(), '_payfull_transaction_id', $xid );
            return true;
        } else {
            return false;
        }
    }

    protected function payfull_getErrorMessage($response, $default)
    {
        if(isset($response['ErrorMSG']) && strlen($response['ErrorMSG']))
            return $response['ErrorMSG'];
        return $default;
    }

    /**
     * @return boolean|array true on success otherwise it returns array of errors
     */
    protected function payfull_validatePaymentForm($form)
    {
        $errors = [];
        if(!isset($form['card']['holder']) || empty($form['card']['holder'])) {
            $errors[] = __('Holder name cannot be empty.', 'payfull');
        }

        if(!isset($form['card']['pan']) || empty($form['card']['pan'])) {
            $errors[] = __('Card number cannot be empty.', 'payfull');
        } elseif(!$this->payfull_checkCCNumber($form['card']['pan'])){
            $errors[] = __('Please enter a valid credit card number.', 'payfull');
        }

        if(!isset($form['card']['year']) || empty($form['card']['year'])) {
            $errors[] = __('Card expiration year cannot be empty.', 'payfull');
        } else {
            $y = intval($form['card']['year']);
            $y += ($y>0 && $y < 99) ? 2000 : 0;
            if($y < date('Y')) {
                $errors[] = __('The expiration year is invalid', 'payfull');
            }
        }

        if(!isset($form['card']['month']) || empty($form['card']['month'])) {
            $errors[] = __('Card expiration month cannot be empty.', 'payfull');
        } else {
            $m = intval($form['card']['month']);
            if($m<1 || $m > 12) {
                $errors[] = __('The expiration month is invalid: '.var_export($form['card']['month'], 1), 'payfull');
            }
        }

        if(!$this->payfull_checkCCEXPDate($form['card']['month'], $form['card']['year'])){
            $errors[] = __('The expiration month is invalid: '.var_export($form['card']['month'], 1), 'payfull');
        }

        if(!isset($form['card']['cvc']) || empty($form['card']['cvc'])) {
            $errors[] = __('Card CVC cannot be empty.', 'payfull');
        }elseif(isset($form['card']['pan']) AND !$this->payfull_checkCCCVC($form['card']['pan'], $form['card']['cvc'])){
            $errors[] = __('Please enter a valid credit card verification number.', 'payfull');
        }

        if($this->enable_installment && (!isset($form['installment']) || intval($form['installment'])<1)) {
            $errors[] = __('The installment value must be a positive integer.', 'payfull');
        }

        if(!$this->enable_bkm AND isset($form['useBKM']) AND $form['useBKM']) {
            $errors[] = __('BKM Express is inactive.', 'payfull');
        }

        if($this->enable_bkm AND isset($form['useBKM']) AND $form['useBKM']) {
            $errors = [];
        }

        return count($errors) ? $errors : true;
    }

    protected function payfull_saveOrderCommission($order, $amount, $installments, $extraInstallments)
    {
        if($extraInstallments != '' AND $extraInstallments != 0) {
            $installments .= ' +'.$extraInstallments;
            $installments  = __('Installment Commission'.' ('.$installments.')', 'payfull');
        }

        if($installments == 1) {
            $oneShotCommission = $this->payfull()->payfull_oneShotCommission();
            $total             = $order->get_total();
            $amount            = ($total*$oneShotCommission/100);
            $installments      = __('Commission', 'payfull');
        }

        $fee            = new stdClass();
        $fee->tax       = 0;
        $fee->amount    = $amount;
        $fee->taxable   = false;
        $fee->name      = $installments;
        $order->add_item($fee);
        $order->calculate_totals();
    }

    protected function payfull_checkCCEXPDate($month, $year)
    {
        if(strtotime('01-'.$month.'-'.$year) <= time()){
            return false;
        }
        return true;
    }

    protected function payfull_checkCCNumber($cardNumber)
    {
        $cardNumber = preg_replace('/\D/', '', $cardNumber);
        $len = strlen($cardNumber);
        if ($len < 15 || $len > 16) {
            return false;
        } else {
            switch($cardNumber) {
                case(preg_match ('/^4/', $cardNumber) >= 1):
                    return true;
                    break;
                case(preg_match ('/^5[1-5]/', $cardNumber) >= 1):
                    return true;
                    break;
                default:
                    return false;
                    break;
            }
        }
    }

    protected function payfull_checkCCCVC($cardNumber, $cvc)
    {
        $firstNumber = (int) substr($cardNumber, 0, 1);
        if ($firstNumber === 3) {
            if (!preg_match("/^\d{4}$/", $cvc)){
                return false;
            }
        } else if (!preg_match("/^\d{3}$/", $cvc)) {
            return false;
        }

        return true;
    }

    protected  function renderView($_viewFile_,$_data_=null,$_return_=false)
    {
        if(is_array($_data_)) {
            extract($_data_,EXTR_PREFIX_SAME,'data');
        } else {
            $data=$_data_;
        }
        if($_return_) {
            ob_start();
            ob_implicit_flush(false);
            require($_viewFile_);
            return ob_get_clean();
        }
        else {
            require($_viewFile_);
        }
    }

}
