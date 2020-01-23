<?php

use GlobalPayments\Api\Entities\EncryptionData;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\CreditTrackData;
use GlobalPayments\Api\Services\CreditService;
use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Entities\TransactionSummary;

class securesubmit extends base {

    public $code;
    public $title;
    public $description;
    public $enabled;
    public $auth_code;
    public $transaction_id;
    public $avs_code;
    public $invoice_number;
    private $enableCryptoUrl;

    public function securesubmit() {
        global $order, $messageStack;
        $this->code = 'securesubmit';
        $this->codeVersion = '1.5.2';
        $this->title = MODULE_PAYMENT_SECURESUBMIT_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_SECURESUBMIT_TEXT_DESCRIPTION;
        $this->sort_order = MODULE_PAYMENT_SECURESUBMIT_SORT_ORDER;
        $this->enabled = ((MODULE_PAYMENT_SECURESUBMIT_STATUS == 'True') ? true : false);
        $this->form_action_url = '';

        if (IS_ADMIN_FLAG === true) {
            if (MODULE_PAYMENT_SECURESUBMIT_SECRET_KEY == '' || MODULE_PAYMENT_SECURESUBMIT_PUBLIC_KEY == '') {
                $this->title .= '<strong><span class="alert">One of your SecureSubmit API keys is missing.</span></strong>';
            }
        }

        if ((int) MODULE_PAYMENT_SECURESUBMIT_ORDER_STATUS_ID > 0) {
            $this->order_status = MODULE_PAYMENT_SECURESUBMIT_ORDER_STATUS_ID;
        }

        if (is_object($order)) {
            $this->update_status();
        }
    }

    public function update_status() {
        global $order, $db;
        if (($this->enabled == true) && ((int) MODULE_PAYMENT_SECURESUBMIT_ZONE > 0)) {
            $check_flag = false;
            $check = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_COD_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");

            while (!$check->EOF) {
                if ($check->fields['zone_id'] < 1) {
                    $check_flag = true;
                    break;
                } elseif ($check->fields['zone_id'] == $order->delivery['zone_id']) {
                    $check_flag = true;
                    break;
                }

                $check->MoveNext();
            }

            if ($check_flag == false) {
                $this->enabled = false;
            }
        }
    }

    public function javascript_validation() {
        return false;
    }

    public function selection() {
        return array(
            'id' => $this->code,
            'module' => $this->title
        );
    }

    public function pre_confirmation_check() {
        return false;
    }

    public function confirmation() {
        global $order, $db;

        $public_key = MODULE_PAYMENT_SECURESUBMIT_PUBLIC_KEY;
        if ($public_key == '') {
            ?>
            <script type="text/javascript">alert('No Public Key found - unable to procede.');</script>
            <?php

        }
        ?>
        <?php

        for ($i = 1; $i < 13; $i++) {
            $expires_month[] = array(
                'id' => sprintf('%02d', $i),
                'text' => strftime('%B', mktime(0, 0, 0, $i, 1, 2000))
            );
        }

        $today = getdate();

        for ($i = $today['year']; $i < $today['year'] + 10; $i++) {
            $expires_year[] = array(
                'id' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)),
                'text' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i))
            );
        }

        $confirmation = array();
        $confirmation['fields'] = array();

        $confirmation['title'] .= 
             '<!-- make iframes styled like other form -->
                    <style type="text/css">
                            #iframes iframe{
                                    float:left;
                                    width:100%;
                            }
                            .iframeholder:after,
                            .iframeholder::after{
                                    content:;
                                    display:block;
                                    width:100%;
                                    height:0px;
                                    clear:both;
                                    position:relative;
                            }
                            #iframes label {
                                    text-transform:uppercase !important;
                                    font-weight:500;
                                    font-family:sans-serif !important;
                                    color:#777777;
                            }
                            .iframeholder {
                              margin-bottom:50px;
                            }
                    </style>

                    <!-- The Payment Form -->
                    <form id="iframes" action="" method="GET" name="card_details">
                        <div class="form-group">
                                <label for="iframesCardNumber">Card Number:</label>
                                <div class="iframeholder" id="iframesCardNumber"></div>
                        </div>
                        <div class="form-group">
                                <label for="iframesCardExpiration">Card Expiration:</label>
                                <div class="iframeholder" id="iframesCardExpiration"></div>
                        </div>
                        <div class="form-group">
                                <label for="iframesCardCvv">Card CVV:</label>
                                <div class="iframeholder" id="iframesCardCvv"></div>
                        </div>                                        
                    </form>';
        
        $confirmation['title'] .= '<script type="text/javascript" src="includes/jquery.js"></script>';
        $confirmation['title'] .= '<script type="text/javascript" src="https://api2.heartlandportico.com/SecureSubmit.v1/token/2.1/securesubmit.js"></script>';
        $confirmation['title'] .= '<script type="text/javascript">var public_key = \'' . $public_key . '\'</script>';
        $confirmation['title'] .= '<script type="text/javascript" src="includes/secure.submit-1.0.2.js"></script>';

        return $confirmation;
    }

    public function process_button() {
        return false;
    }

    public function before_process() {
        global $_POST, $order, $sendto, $currency, $charge, $db, $messageStack; 
        require_once(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/securesubmit/autoload.php');
        $error = '';
        
        $chargeservice = $this->setConfig();
        $hpsaddress = new Address();
        $hpsaddress->address = $order->billing['street_address'];
        $hpsaddress->city = $order->billing['city'];
        $hpsaddress->state = $order->billing['state'];
        $hpsaddress->zip = preg_replace('/[^0-9]/', '', $order->billing['postcode']);
        $hpsaddress->country = $order->billing['country']['title'];

        $hpstoken = new CreditCardData();
        $hpstoken->token = $_POST['securesubmit_token']; 
        $hpstoken->cardHolderName = $order->billing['firstname'];

        $last_order = $db->Execute("select orders_id from " . TABLE_ORDERS . " order by orders_id desc limit 1");
        $this->invoice_number = ($last_order->fields['orders_id']) + 1;

        $details = new TransactionSummary();
        $details->invoiceNumber = $this->invoice_number;
        try {
            if (MODULE_PAYMENT_SECURESUBMIT_AUTHCAPTURE == 'Authorize') {
                $response = $hpstoken->authorize(round($order->info['total'], 2))
                ->withCurrency(MODULE_PAYMENT_SECURESUBMIT_CURRENCY)
                ->withAddress($hpsaddress)
                ->withInvoiceNumber($this->invoice_number)
                ->withAmountEstimated(round($order->info['total'], 2))
                ->withAllowDuplicates(true)
                ->execute();
            } else {
                $response = $hpstoken->charge(round($order->info['total'], 2))
                ->withCurrency(MODULE_PAYMENT_SECURESUBMIT_CURRENCY)
                ->withAddress($hpsaddress)
                ->withAllowDuplicates(true)
                ->execute();
            }
            $this->transaction_id = $response->transactionId;
            $this->auth_code = $response->authorizationCode;
            $this->avs_code = $response->avsResponseCode;
        } catch (Exception $e) {
            $error = $e->getMessage();
            $messageStack->add_session('checkout_confirmation', $error . '<!-- [' . $this->code . '] -->', 'error');
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL', true, false));
        }

        return false;
    }
    
    public function setConfig()
    {
        $config = new ServicesConfig();
        $config->secretApiKey = MODULE_PAYMENT_SECURESUBMIT_SECRET_KEY;
        $config->serviceUrl = "https://cert.api2.heartlandportico.com"; 
        $service =  ServicesContainer::configure($config);
        return $service;    
    }
	
    public function after_process() {
        global $insert_id, $db;

        try {
            $comments .= " AUTH: " . $this->auth_code;
            $comments .= ". TransID: " . $this->transaction_id;
            $comments .= ". AVS Code: " . $this->avs_code;
            $comments .= ". Invoice Number: " . $this->invoice_number;

            $sql = "insert into " . TABLE_ORDERS_STATUS_HISTORY . " (comments, orders_id, orders_status_id, customer_notified, date_added) values (:orderComments, :orderID, :orderStatus, -1, now() )";
            $sql = $db->bindVars($sql, ':orderComments', 'Credit Card payment. ' . $comments, 'string');
            $sql = $db->bindVars($sql, ':orderID', $insert_id, 'integer');
            $sql = $db->bindVars($sql, ':orderStatus', $this->order_status, 'integer');
            $db->Execute($sql);
        } catch (Exception $e) {
            $comments = " ";
            $sql = "insert into " . TABLE_ORDERS_STATUS_HISTORY . " (comments, orders_id, orders_status_id, customer_notified, date_added) values (:orderComments, :orderID, :orderStatus, -1, now() )";
            $sql = $db->bindVars($sql, ':orderComments', 'Credit Card payment. ' . $comments, 'string');
            $sql = $db->bindVars($sql, ':orderID', $insert_id, 'integer');
            $sql = $db->bindVars($sql, ':orderStatus', $this->order_status, 'integer');
            $db->Execute($sql);
        }

        return false;
    }

    public function get_error() {
        global $_GET;
        $error = array(
            'title' => MODULE_PAYMENT_SECURESUBMIT_ERROR_TITLE,
            'error' => stripslashes($_GET['error'])
        );
        return $error;
    }

    public function check() {
        global $db;

        if (!isset($this->_check)) {
            $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_SECURESUBMIT_STATUS'");
            $this->_check = $check_query->RecordCount();
        }

        return $this->_check;
    }

    public function install() {
        global $db, $messageStack;

        if (defined('MODULE_PAYMENT_SECURESUBMIT_STATUS')) {
            $messageStack->add_session('SecureSubmit module already installed.', 'error');
            zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=securesubmit', 'NONSSL'));
            return 'failed';
        }

        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable SecureSubmit', 'MODULE_PAYMENT_SECURESUBMIT_STATUS', 'True', 'Do you want to accept SecureSubmit payments?', '6', '10', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_SECURESUBMIT_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '20', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_SECURESUBMIT_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '30', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_SECURESUBMIT_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '40', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('SecureSubmit Currency', 'MODULE_PAYMENT_SECURESUBMIT_CURRENCY', 'USD', 'The currency that your SecureSubmit account is setup to handle - currently only USD - <b>make sure that your store is operating in the same currency!</b>', '6', '50', 'zen_cfg_select_option(array(\'USD\'), ', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Payment Type', 'MODULE_PAYMENT_SECURESUBMIT_AUTHCAPTURE', 'USD', 'Authorize or Authorize and Capture', '6', '51', 'zen_cfg_select_option(array(\'Authorize and Capture\', \'Authorize\'), ', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Secret key', 'MODULE_PAYMENT_SECURESUBMIT_SECRET_KEY', '', 'Secret key  - available in your SecureSubmit Account Tab.', '6', '64', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Public key', 'MODULE_PAYMENT_SECURESUBMIT_PUBLIC_KEY', '', 'Public key  - available in your SecureSubmit Account Tab.', '6', '66', now())");
    }

    public function remove() {
        global $db;
        $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    public function keys() {
        return array(
            'MODULE_PAYMENT_SECURESUBMIT_STATUS',
            'MODULE_PAYMENT_SECURESUBMIT_ZONE',
            'MODULE_PAYMENT_SECURESUBMIT_ORDER_STATUS_ID',
            'MODULE_PAYMENT_SECURESUBMIT_SORT_ORDER',
            'MODULE_PAYMENT_SECURESUBMIT_CURRENCY',
            'MODULE_PAYMENT_SECURESUBMIT_SECRET_KEY',
            'MODULE_PAYMENT_SECURESUBMIT_PUBLIC_KEY',
            'MODULE_PAYMENT_SECURESUBMIT_AUTHCAPTURE'
        );
    }

    // for now, we only accept usd, but let's leave our options option with some validations.
    public function format_raw($number, $currency_code = '', $currency_value = '') {
        global $currencies, $currency;

        if (empty($currency_code) || !$this->is_set($currency_code)) {
            $currency_code = $currency;
        }

        if (empty($currency_value) || !is_numeric($currency_value)) {
            $currency_value = $currencies->currencies[$currency_code]['value'];
        }

        return number_format(zen_round($number * $currency_value, $currencies->currencies[$currency_code]['decimal_places']), $currencies->currencies[$currency_code]['decimal_places'], '.', '');
    }

}

function table_exists($tablename, $database = false) {
    global $db;
    $res = $db->Execute("
        SELECT COUNT(*) AS count
        FROM information_schema.tables
        WHERE table_schema = '" . DB_DATABASE . "'
        AND table_name = '$tablename'
    ");
    if ($res->fields['count'] > 0) {
        return true;
    } else {
        return false;
    }
}
