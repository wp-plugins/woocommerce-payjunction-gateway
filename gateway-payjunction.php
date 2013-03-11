<?php
/*
Plugin Name: WooCommerce PayJunction Gateway
Plugin URI: https://company.payjunction.com/support/WooCommerce
Description: With PayJunction, Accept all major credit cards and debit cards directly on your WooCommerce website with a seamless and secure checkout experience.. <a href="https://pricing.payjunction.com/woothemes" target="_blank">Click Here</a> to get a PayJunction account.
Version: 2.0
Author: PayJunction
Author URI: http://www.PayJunction.com
*/

add_action('plugins_loaded', 'woocommerce_payjunction_init', 0);

function woocommerce_payjunction_init() {

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) { return; }

	require_once(WP_PLUGIN_DIR . "/" . plugin_basename( dirname(__FILE__)) . '/classes/payjunction-request.php');
	require_once(WP_PLUGIN_DIR . "/" . plugin_basename( dirname(__FILE__)) . '/classes/payjunction-response.php');
	
	/**
 	* Gateway class
 	**/
	class WC_Gateway_PayJunction extends WC_Payment_Gateway {
	
		var $avaiable_countries = array(
			'GB' => array(
				'Visa',
				'MasterCard',
				'Discover',
				'American Express'
			),
			'US' => array(
				'Visa',
				'MasterCard',
				'Discover',
				'American Express'
			),
			'CA' => array(
				'Visa',
				'MasterCard',
				'Discover',
				'American Express'
			)
		);
		var $api_username;
		var $api_password;
		var $liveurl = 'https://www.payjunction.com/quick_link';
		var $testurl = 'https://www.payjunctionlabs.com/quick_link';
		var $testmode;
	
		function __construct() { 
			
			$this->id				= 'payjunction';
			$this->method_title 	= __('PayJunction', 'woothemes');
			//$this->icon 			= WP_PLUGIN_URL . "/" . plugin_basename( dirname(__FILE__)) . '/images/CardLogo.png';
			$this->has_fields 		= true;
			
			// Load the form fields
			$this->init_form_fields();
			
			// Load the settings.
			$this->init_settings();
			
			// Get setting values
			$this->title 			= $this->settings['title'];
			$this->description 		= $this->settings['description'];
			$this->enabled 			= $this->settings['enabled'];
			$this->api_username 	= $this->settings['api_username'];
			$this->api_password 	= $this->settings['api_password'];
			$this->testmode 		= $this->settings['testmode'];

			// Hooks
			add_action( 'admin_notices', array( &$this, 'ssl_check') );
			//add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );  // Version 1 Hook
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) ); // Version 2.0 Hook
		}
		
		/**
	 	* Check if SSL is enabled and notify the user if SSL is not enabled
	 	**/
		function ssl_check() {
	     
		if (get_option('woocommerce_force_ssl_checkout')=='no' && $this->enabled=='yes') :
		
			echo '<div class="error"><p>'.sprintf(__('PayJunction is enabled, but the <a href="%s">force SSL option</a> is disabled; your checkout is not secure! Please enable SSL and ensure your server has a valid SSL certificate - PayJunction will only work in test mode.', 'woothemes'), admin_url('admin.php?page=woocommerce')).'</p></div>';
		
		endif;
		}
		
		/**
	     * Initialize Gateway Settings Form Fields
	     */
	    function init_form_fields() {
	    
	    	$this->form_fields = array(
				'title' => array(
								'title' => __( 'Title', 'woothemes' ), 
								'type' => 'text', 
								'description' => __( 'This controls the title which the user sees during checkout.', 'woothemes' ), 
								'default' => __( 'Credit Card / Debit Card', 'woothemes' )
							), 
				'enabled' => array(
								'title' => __( 'Enable/Disable', 'woothemes' ), 
								'label' => __( 'Enable PayJunction', 'woothemes' ), 
								'type' => 'checkbox', 
								'description' => '', 
								'default' => 'no'
							), 
				'description' => array(
								'title' => __( 'Description', 'woothemes' ), 
								'type' => 'text', 
								'description' => __( 'This controls the description which the user sees during checkout.', 'woothemes' ), 
								'default' => 'Pay with your Credit Card or debit card.'
							),  
				'testmode' => array(
								'title' => __( 'PayJunction Test', 'woothemes' ), 
								'label' => __( 'Enable PayJunction Test', 'woothemes' ), 
								'type' => 'checkbox', 
								'description' => __( 'Process transactions in Test Mode via the PayJunction Test account (www.payjunctionlabs.com).', 'woothemes' ), 
								'default' => 'no'
							), 
				'api_username' => array(
								'title' => __( 'API Username', 'woothemes' ), 
								'type' => 'text', 
								'description' => __( 'Get your QuickLink API Login from PayJunction.', 'woothemes' ), 
								'default' => ''
							), 
				'api_password' => array(
								'title' => __( 'API Password', 'woothemes' ), 
								'type' => 'text', 
								'description' => __( 'Get your QuickLink API Password from PayJunction.', 'woothemes' ), 
								'default' => ''
							),
				);
	    }
	    
	    /**
		 * Admin Panel Options 
		 * - Options for bits like 'title' and availability on a country-by-country basis
		 */
		function admin_options() {
	    	?>
	    	<h3><?php _e( 'PayJunction', 'woothemes' ); ?></h3>
	    	<p><?php _e( 'PayJunction works by adding credit card fields on the checkout and then sending the details to PayJunction for verification.', 'woothemes' ); ?></p>
	    	<table class="form-table">
	    		<?php $this->generate_settings_html(); ?>
			</table><!--/.form-table-->
	    	<?php
	    }
		
		/**
	     * Check if this gateway is enabled and available in the user's country
	     */
		function is_available() {
		
			if ($this->enabled=="yes") :
			
				if (get_option('woocommerce_force_ssl_checkout')=='no' && $this->testmode == 'no') return false;
				
				$user_country = $this->get_country_code();
				if(empty($user_country)) {
					return false;
				}
			
				return isset($this->avaiable_countries[$user_country]);
				
			endif;	
			
			return false;
		}
		
		/**
	     * Get the users country either from their order, or from their customer data
	     */
		function get_country_code() {
			global $woocommerce;
			
			if(isset($_GET['order_id'])) {
			
				$order = new WC_Order($_GET['order_id']);
	
				return $order->billing_country;
				
			} elseif ($woocommerce->customer->get_country()) {
				
				return $woocommerce->customer->get_country();
			
			}
			
			return NULL;
		}
	
		/**
	     * Payment form on checkout page
	     */
		function payment_fields() {
			$user_country = $this->get_country_code();
			
			if(empty($user_country)) :
				echo __('Select a country to see the payment form', 'woothemes');
				return;
			endif;
			
			if (!isset($this->avaiable_countries[$user_country])) :
				echo __('PayJunction is not available in your country.', 'woothemes');
				return;
			endif;
			
			$available_cards = $this->avaiable_countries[$user_country];
			
			?>
			<?php if ($this->testmode=='yes') : ?><p><?php _e('TEST MODE/SANDBOX ENABLED', 'woothemes'); ?></p><?php endif; ?>
			<?php if ($this->description) : ?><p><?php echo $this->description; ?></p><?php endif; ?>
			<fieldset>
				<p class="form-row form-row-first">
					<label for="payjunction_cart_number"><?php echo __("Credit Card number", 'woocommerce') ?> <span class="required">*</span></label>
					<input type="text" class="input-text" name="payjunction_card_number" />
				</p>
				<p class="form-row form-row-last">
					<label for="payjunction_cart_type"><?php echo __("Card type", 'woocommerce') ?> <span class="required">*</span></label>
					<select id="payjunction_card_type" name="payjunction_card_type">
						<?php foreach ($available_cards as $card) : ?>
									<option value="<?php echo $card ?>"><?php echo $card; ?></options>
						<?php endforeach; ?>
					</select>
				</p>
				<div class="clear"></div>
				<p class="form-row form-row-first">
					<label for="cc-expire-month"><?php echo __("Expiration date", 'woocommerce') ?> <span class="required">*</span></label>
					<select name="payjunction_card_expiration_month" id="cc-expire-month">
						<option value=""><?php _e('Month', 'woocommerce') ?></option>
						<?php
							$months = array();
							for ($i = 1; $i <= 12; $i++) {
							    $timestamp = mktime(0, 0, 0, $i, 1);
							    $months[date('m', $timestamp)] = date('F', $timestamp);
							}
							foreach ($months as $num => $name) {
					            printf('<option value="%s">%s</option>', $num, $name);
					        }
					        
						?>
					</select>
					<select name="payjunction_card_expiration_year" id="cc-expire-year">
						<option value=""><?php _e('Year', 'woocommerce') ?></option>
						<?php
							$years = array();
							for ($i = date('Y'); $i <= date('Y') + 15; $i++) {
							    printf('<option value="%u">%u</option>', $i, $i);
							}
						?>
					</select>
				</p>
				<p class="form-row form-row-last">
					<label for="payjunction_card_csc"><?php _e("Card security code", 'woocommerce') ?> <span class="required">*</span></label>
					<input type="text" class="input-text" id="payjunction_card_csc" name="payjunction_card_csc" maxlength="4" style="width:45px" />
					<span class="help payjunction_card_csc_description"></span>
				</p>
				<div class="clear"></div>
			</fieldset>
			<script type="text/javascript">
			
				function toggle_csc() {
					var card_type = jQuery("#payjunction_card_type").val();
					var csc = jQuery("#payjunction_card_csc").parent();
			
					if(card_type == "Visa" || card_type == "MasterCard" || card_type == "Discover" || card_type == "American Express" ) {
						csc.fadeIn("fast");
					} else {
						csc.fadeOut("fast");
					}
					
					if(card_type == "Visa" || card_type == "MasterCard" || card_type == "Discover") {
						jQuery('.payjunction_card_csc_description').text("<?php _e('3 digits usually found on the back of the card.', 'woocommerce'); ?>");
					} else if ( cardType == "American Express" ) {
						jQuery('.payjunction_card_csc_description').text("<?php _e('4 digits usually found on the front of the card.', 'woocommerce'); ?>");
					} else {
						jQuery('.payjunction_card_csc_description').text('');
					}
				}
			
				jQuery("#payjunction_card_type").change(function(){
					toggle_csc();
				}).change();
			
			</script>
			<?php
		}
		
		/**
	     * Process the payment
	     */
		function process_payment($order_id) {
			global $woocommerce;
			
			$order = new WC_Order( $order_id );
			
			$billing_country 	= isset($_POST['billing-country']) ? $_POST['billing-country'] : '';
			$card_type 			= isset($_POST['payjunction_card_type']) ? $_POST['payjunction_card_type'] : '';
			$card_number 		= isset($_POST['payjunction_card_number']) ? $_POST['payjunction_card_number'] : '';
			$card_csc 			= isset($_POST['payjunction_card_csc']) ? $_POST['payjunction_card_csc'] : '';
			$card_exp_month		= isset($_POST['payjunction_card_expiration_month']) ? $_POST['payjunction_card_expiration_month'] : '';
			$card_exp_year 		= isset($_POST['payjunction_card_expiration_year']) ? $_POST['payjunction_card_expiration_year'] : '';
	
			// Format Expiration Year			
			$expirationYear = substr($card_exp_year, -2);
			
			// Format card number
			$card_number = str_replace(array(' ', '-'), '', $card_number);
	
			// Validate plugin settings
			if (!$this->validate_settings()) :
				$cancelNote = __('Order was cancelled due to invalid settings (check your API credentials and make sure your currency is supported).', 'woothemes');
				$order->add_order_note( $cancelNote );
		
				$woocommerce->add_error(__('Payment was rejected due to configuration error.', 'woothemes'));
				return false;
			endif;
	
			// Send request to payjunction
			try {
				$url = $this->liveurl;
				if ($this->testmode == 'yes') :
					$url = $this->testurl;
				endif;
	
				$request = new payjunction_request($url);
				
				$response = $request->send(array(
					"dc_logon" => $this->api_username,
					"dc_password" => $this->api_password,
					"dc_transaction_amount" => $order->order_total,
					"dc_number" => $card_number,
					"dc_verification_number" => $card_csc,
					"dc_expiration_month" => $card_exp_month,
					"dc_expiration_year" => $expirationYear,
					"dc_transaction_type" => "AUTHORIZATION_CAPTURE",					
					// "dc_transaction_type" => $this->salemethod, --> ADD THIS FEATURE
					"dc_first_name" => $order->billing_first_name,
					"dc_last_name" => $order->billing_last_name,
					"dc_address" => $order->billing_address_1 . ' ' . $order->billing_address_2,
					"dc_city" => $order->billing_city,
					"dc_state" => $order->billing_state,
					"dc_zipcode" => $order->billing_postcode,
					"dc_country" => $order->billing_country,
					"dc_notes" => "Customer ID: ".$order->user_id,
					"dc_invoice" => $order_id,
					"dc_version" => "1.2",
				));
				
			} catch(Exception $e) {
				$woocommerce->add_error(__('There was a connection error', 'woothemes') . ': "' . $e->getMessage() . '"');
				return;
			}
	
			if ($response->success()) :
				$order->add_order_note( __('PayJunction payment completed', 'woothemes') . ' (Transaction ID: ' . $response->get_transaction_id() . ')' );
				$order->payment_complete();
	
				$woocommerce->cart->empty_cart();
					
				// Return thank you page redirect
				return array(
					'result' 	=> 'success',
					'redirect'	=> add_query_arg('key', $order->order_key, add_query_arg('order', $order_id, get_permalink(get_option('woocommerce_thanks_page_id'))))
				);
			else :
				$cancelNote = __('PayJunction payment failed', 'woothemes') . ' (Transaction ID: ' . $response->get_transaction_id() . '). ' . __('Payment was rejected due to an error', 'woothemes') . ': "' . $response->get_error() . '". ';
	
				$order->add_order_note( $cancelNote );
				
				$woocommerce->add_error(__('Payment error', 'woothemes') . ': ' . $response->get_error() . '');
			endif;
		}
	
		/**
	     * Validate the payment form
	     */
		function validate_fields() {
			global $woocommerce;
												
			$billing_country 	= isset($_POST['billing_country']) ? $_POST['billing_country'] : '';
			$card_type 			= isset($_POST['payjunction_card_type']) ? $_POST['payjunction_card_type'] : '';
			$card_number 		= isset($_POST['payjunction_card_number']) ? $_POST['payjunction_card_number'] : '';
			$card_csc 			= isset($_POST['payjunction_card_csc']) ? $_POST['payjunction_card_csc'] : '';
			$card_exp_month		= isset($_POST['payjunction_card_expiration_month']) ? $_POST['payjunction_card_expiration_month'] : '';
			$card_exp_year 		= isset($_POST['payjunction_card_expiration_year']) ? $_POST['payjunction_card_expiration_year'] : '';
	
			// Check if payment is avaiable for given country and card
			if (!isset($this->avaiable_countries[$billing_country])) {
				$woocommerce->add_error(__('Payment method is not available for your billing country', 'woothemes'));
				return false;
			}
			
			// Check card type is available
			$available_cards = $this->avaiable_countries[$billing_country];
			if (!in_array($card_type, $available_cards)) {
				$woocommerce->add_error(__('The selected credit card type is not available for your billing country', 'woothemes'));
				return false;
			}
	
			// Check card security code
			if(!ctype_digit($card_csc)) {
				$woocommerce->add_error(__('Card security code is invalid (only digits are allowed)', 'woothemes'));
				return false;
			}
	
			if((strlen($card_csc) != 3 && in_array($card_type, array('Visa', 'MasterCard', 'Discover'))) || (strlen($card_csc) != 4 && $card_type == 'American Express')) {
				$woocommerce->add_error(__('Card security code is invalid (wrong length)', 'woothemes'));
				return false;
			}
	
			// Check card expiration data
			if(!ctype_digit($card_exp_month) || !ctype_digit($card_exp_year) ||
				 $card_exp_month > 12 ||
				 $card_exp_month < 1 ||
				 $card_exp_year < date('Y') ||
				 $card_exp_year > date('Y') + 20
			) {
				$woocommerce->add_error(__('Card expiration date is invalid', 'woothemes'));
				return false;
			}
	
			// Check card number
			$card_number = str_replace(array(' ', '-'), '', $card_number);
	
			if(empty($card_number) || !ctype_digit($card_number)) {
				$woocommerce->add_error(__('Card number is invalid', 'woothemes'));
				return false;
			}
	
			return true;
		}
		
		/**
	     * Validate plugin settings
	     */
		function validate_settings() {
			$currency = get_option('woocommerce_currency');
	
			if (!in_array($currency, array('USD'))) {
				return false;
			}
	
			if (!$this->api_username || !$this->api_password) {
				return false;
			}
	
			return true;
		}
		
		/**
	     * Get user's IP address
	     */
		function get_user_ip() {
			if (!empty($_SERVER['HTTP_X_FORWARD_FOR'])) {
				return $_SERVER['HTTP_X_FORWARD_FOR'];
			} else {
				return $_SERVER['REMOTE_ADDR'];
			}
		}

	} // end woocommerce_payjunction
	
	/**
 	* Add the Gateway to WooCommerce
 	**/
	function add_payjunction_gateway($methods) {
		$methods[] = 'WC_Gateway_PayJunction';
		return $methods;
	}
	
	add_filter('woocommerce_payment_gateways', 'add_payjunction_gateway' );
} 
