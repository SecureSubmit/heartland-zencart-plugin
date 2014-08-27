<?php
class securesubmit extends base
{
    var $code, $title, $description, $enabled, $auth_code, $transaction_id;

    function securesubmit()
    {
        global $order,$messageStack;
        $this->code            = 'securesubmit';
        $this->codeVersion 	   = '1.5.1';
        $this->title           = MODULE_PAYMENT_SECURESUBMIT_TEXT_TITLE;
        $this->description     = MODULE_PAYMENT_SECURESUBMIT_TEXT_DESCRIPTION;
        $this->sort_order      = MODULE_PAYMENT_SECURESUBMIT_SORT_ORDER;
        $this->enabled         = ((MODULE_PAYMENT_SECURESUBMIT_STATUS == 'True') ? true : false);
        $this->form_action_url = '';

		if (IS_ADMIN_FLAG === true) {
			if ( MODULE_PAYMENT_SECURESUBMIT_SECRET_KEY == '' || MODULE_PAYMENT_SECURESUBMIT_PUBLIC_KEY == ''){
				 $this->title .= '<strong><span class="alert">One of your SecureSubmit API keys is missing.</span></strong>';
			}
		}

        if ((int) MODULE_PAYMENT_SECURESUBMIT_ORDER_STATUS_ID > 0) {
            $this->order_status = MODULE_PAYMENT_SECURESUBMIT_ORDER_STATUS_ID;
        }

        if (is_object($order))
		{
            $this->update_status();
		}
    }

    function update_status()
    {
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

    function javascript_validation()
    {
        return false;
    }

    function selection()
    {
        return array(
            'id' => $this->code,
            'module' => $this->title
        );
    }

    function pre_confirmation_check()
    {
        return false;
    }

    function confirmation()
    {
        global $order,  $db;

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

        $confirmation['fields'][] = array(
            'title' => '<span class="card_hide" style="margin-right:10px">' . MODULE_PAYMENT_SECURESUBMIT_CREDIT_CARD_OWNER . '</span>' . zen_draw_input_field('', $order->billing['firstname'] . ' ' . $order->billing['lastname'], 'class="card-name card_hide"'),
            'field' => ''
        );
        $confirmation['fields'][] = array(
            'title' => '<span class="card_hide" style="margin-right:10px">' . MODULE_PAYMENT_SECURESUBMIT_CREDIT_CARD_NUMBER . '</span>'. zen_draw_input_field('', '', 'style="display:inline-block; padding-right:10px" class="card_number card_hide"'),
            'field' => ''
        );
        $confirmation['fields'][] = array(
            'title' => '<span class="card_hide" style="margin-right:10px">' . MODULE_PAYMENT_SECURESUBMIT_CREDIT_CARD_EXPIRES . '</span>'.zen_draw_pull_down_menu('', $expires_month, '', 'class="card_expiry_month card_hide"') . '&nbsp;' . zen_draw_pull_down_menu('', $expires_year, '', 'class="card-expiry-year  card_hide"'),
            'field' => ''
        );
		$confirmation['fields'][] = array(
			'title' => '<span class="card_hide" style="margin-right:10px">' . MODULE_PAYMENT_SECURESUBMIT_CREDIT_CARD_CVC . '</span>'.zen_draw_input_field('', '', 'size="5" maxlength="4" class="card_cvc card_hide"'),
			'field' => ''
		);

		$confirmation['title'] .= '<script type="text/javascript" src="includes/jquery.js"></script>';
		$confirmation['title'] .= '<script type="text/javascript" src="includes/secure.submit-1.0.2.js"></script>';
        $confirmation['title'] .= '<script type="text/javascript">
			jQuery(document).ready(function($) {
				$("form[name=checkout_confirmation]").submit(function(event) {
					hps.tokenize({
						data: {
							public_key: \'' . $public_key . '\',
							number: $(\'.card_number\').val().replace(/\D/g, \'\'),
							cvc: $(\'.card_cvc\').val(),
							exp_month: $(\'.card_expiry_month\').val(),
							exp_year: $(\'.card-expiry-year\').val()
						},
						success: function(response) {
							secureSubmitResponseHandler(response);
						},
						error: function(response) {
							secureSubmitResponseHandler(response);
						}
					});

                    $("#btn_submit").hide();

					return false; // stop the form submission
				});

				function secureSubmitResponseHandler(response) {
					if ( response.message ) {
                        $("#btn_submit").show();
						alert(response.message);
					} else {
						var form$ = $("form[name=checkout_confirmation]");
						var token = response.token_value;

						form$.append("<input type=\'hidden\' name=\'securesubmit_token\' value=\'" + token + "\'/>");
						form$.attr(\'action\', \'index.php?main_page=checkout_process\');

						$(\'.card_number\').val(\'\');
						$(\'.card_cvc\').val(\'\');
						$(\'.card_expiry_month\').val(\'\');
						$(\'.card-expiry-year\').val(\'\');

						$("#tdb5").hide();
						form$.get(0).submit();
					}
				}
			});
			</script>';
        return $confirmation;
    }

    function process_button()
    {
        return false;
    }

    function before_process()
    {
        global $_POST,  $order, $sendto, $currency, $charge, $db, $messageStack;
        require_once(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/securesubmit/Hps.php');
        $error = '';
        
        $config = new HpsConfiguration();
        $config->secretApiKey = MODULE_PAYMENT_SECURESUBMIT_SECRET_KEY;
        $config->versionNumber = '1512';
        $config->developerId = '002914';
        
        $chargeService = new HpsChargeService($config);

        $hpsaddress = new HpsAddress();
        $hpsaddress->address = $order->billing['street_address'];
        $hpsaddress->city = $order->billing['city'];
        $hpsaddress->state = $order->billing['state'];
        $hpsaddress->zip = preg_replace('/[^0-9]/', '', $order->billing['postcode']);
        $hpsaddress->country = $order->billing['country']['title'];

        $cardHolder = new HpsCardHolder();
        $cardHolder->firstName = $order->billing['firstname'];
        $cardHolder->lastName = $order->billing['lastname'];
        $cardHolder->emailAddress = $order->customer['email_address'];
        $cardHolder->address = $hpsaddress;

        $hpstoken = new HpsTokenData();
        $hpstoken->tokenValue = $_POST['securesubmit_token'];

		try {
			if (MODULE_PAYMENT_SECURESUBMIT_AUTHCAPTURE == 'Authorize')
			{
                $response = $chargeService->authorize(
                    round($order->info['total'], 2),
                    MODULE_PAYMENT_SECURESUBMIT_CURRENCY,
                    $hpstoken,
                    $cardHolder,
                    false,
                    null);
			}
			else
			{
                $response = $chargeService->charge(
                    round($order->info['total'], 2),
                    MODULE_PAYMENT_SECURESUBMIT_CURRENCY,
                    $hpstoken,
                    $cardHolder,
                    false,
                    null);
			}

            $this->transaction_id = $response->transactionId;
            $this->auth_code = $response->authorizationCode;
		}
		catch (Exception $e) {
			$error = $e->getMessage();
			$messageStack->add_session('checkout_confirmation', $error . '<!-- [' . $this->code . '] -->', 'error');
			zen_redirect(zen_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL', true, false));
		}

        return false;
    }

    function after_process()
    {
        global $insert_id,  $db;

        $comments .= " AUTH: " . $this->auth_code . ". TransID: " . $this->transaction_id;

        $sql = "insert into " . TABLE_ORDERS_STATUS_HISTORY . " (comments, orders_id, orders_status_id, customer_notified, date_added) values (:orderComments, :orderID, :orderStatus, -1, now() )";
        $sql = $db->bindVars($sql, ':orderComments', 'Credit Card payment. ' . $comments, 'string');
        $sql = $db->bindVars($sql, ':orderID', $insert_id, 'integer');
        $sql = $db->bindVars($sql, ':orderStatus', $this->order_status, 'integer');
        $db->Execute($sql);

        return false;
    }

    function get_error()
    {
        global $_GET;
        $error = array(
            'title' => MODULE_PAYMENT_SECURESUBMIT_ERROR_TITLE,
            'error' => stripslashes($_GET['error'])
        );
        return $error;
    }

    function check()
    {
        global $db;

        if (!isset($this->_check)) {
            $check_query  = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_SECURESUBMIT_STATUS'");
            $this->_check = $check_query->RecordCount();
        }

        return $this->_check;
    }

    function install()
    {
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
    function remove()
    {
        global $db;
        $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
    function keys()
    {
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
    function format_raw($number, $currency_code = '', $currency_value = '')
    {
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

function table_exists($tablename, $database = false)
{
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
?>
