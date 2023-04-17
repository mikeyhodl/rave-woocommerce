<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://flutterwave.com
 * @since      2.3.2
 *
 * @package    Flutterwave\WooCommerce
 * @subpackage FLW_WC_Payment_Gateway/includes
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'FLUTTERWAVEACCESS' ) ) {
	exit;
}

require_once __DIR__ . '/class-flw-wc-payment-gateway-event-handler.php';
require_once __DIR__ . '/client/class-flw-wc-payment-gateway-request.php';
require_once __DIR__ . '/client/class-flw-wc-payment-gateway-sdk.php';

use Flutterwave\WooCommerce\Client\Flw_WC_Payment_Gateway_Request;
use Flutterwave\WooCommerce\Client\FLW_WC_Payment_Gateway_Sdk as FlwSdk;
use FLW_WC_Payment_Gateway_Event_Handler as FlwEventHandler;

/**
 * Main Flutterwave Gateway Class
 */
class FLW_WC_Payment_Gateway extends \WC_Payment_Gateway {

	/**
	 * @var bool
	 */
	public static bool $log_enabled = false;
	/**
	 * @var string
	 */
	protected string $public_key;
	/**
	 * @var string
	 */
	protected string $secret_key;
	/**
	 * @var string
	 */
	private string $test_public_key;
	/**
	 * @var string
	 */
	private string $test_secret_key;
	/**
	 * @var string
	 */
	private string $live_public_key;
	/**
	 * @var string
	 */
	private string $go_live;
	/**
	 * @var string
	 */
	private string $live_secret_key;
	/**
	 * @var false|mixed|null
	 */
	private $auto_complete_order;
	/**
	 * @var WC_Logger
	 */
	private WC_Logger $logger;
	/**
	 * @var FlwSdk
	 */
	private FlwSdk $sdk;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
		$this->base_url           = 'https://api.ravepay.co';
		$this->id                 = 'rave';
		$this->icon               = plugins_url( 'assets/img/rave.png', FLW_WC_PLUGIN_FILE );
		$this->has_fields         = false;
		$this->method_title       = __( 'Rave', 'flw-payments' );
		$this->method_description = __( 'Rave allows you to accept payment from cards and bank accounts in multiple currencies. You can also accept payment offline via USSD and POS.', 'flw-payments' );
		$this->supports           = array(
			'products',
		);

		$this->init_form_fields();
		$this->init_settings();

		$this->title               = $this->get_option( 'title' );
		$this->description         = $this->get_option( 'description' );
		$this->enabled             = $this->get_option( 'enabled' );
		$this->test_public_key     = $this->get_option( 'test_public_key' );
		$this->test_secret_key     = $this->get_option( 'test_secret_key' );
		$this->live_public_key     = $this->get_option( 'live_public_key' );
		$this->live_secret_key     = $this->get_option( 'live_secret_key' );
		$this->auto_complete_order = get_option( 'autocomplete_order' );
		$this->go_live             = $this->get_option( 'go_live' );
		$this->payment_options     = $this->get_option( 'payment_options' );
		$this->payment_style       = $this->get_option( 'payment_style' );
		$this->barter              = $this->get_option( 'barter' );
		$this->logging_option      = 'yes' === $this->get_option( 'logging_option', 'no' );
		$this->country             = '';
		self::$log_enabled         = $this->logging_option;
		$this->supports            = array(
			'products',
			'tokenization',
			'subscriptions',
			'subscription_cancellation',
			'subscription_suspension',
			'subscription_reactivation',
			'subscription_amount_changes',
			'subscription_date_changes',
			'subscription_payment_method_change',
			'subscription_payment_method_change_customer',
			'subscription_payment_method_change_admin',
			'multiple_subscriptions',
		);

		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
		add_action( 'woocommerce_api_flw_wc_payment_gateway', array( $this, 'flw_verify_payment' ) );

		// Webhook listener/API hook.
		add_action( 'woocommerce_api_flw_wc_payment_webhook', array( $this, 'flutterwave_webhooks' ) );

		if ( is_admin() ) {
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		$this->public_key = $this->test_public_key;
		$this->secret_key = $this->test_secret_key;

		if ( 'yes' === $this->go_live ) {
			$this->public_key = $this->live_public_key;
			$this->secret_key = $this->live_secret_key;
		}

		$this->sdk = new FlwSdk( $this->secret_key, self::$log_enabled );

		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );

	}

	/**
	 * Initial gateway settings form fields
	 *
	 * @return void
	 */
	public function init_form_fields() {

		$this->form_fields = array(

			'enabled'            => array(
				'title'       => __( 'Enable/Disable', 'flw-payments' ),
				'label'       => __( 'Enable Rave Payment Gateway', 'flw-payments' ),
				'type'        => 'checkbox',
				'description' => __( 'Enable Rave Payment Gateway as a payment option on the checkout page', 'flw-payments' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'go_live'            => array(
				'title'       => __( 'Mode', 'flw-payments' ),
				'label'       => __( 'Live mode', 'flw-payments' ),
				'type'        => 'checkbox',
				'description' => __( 'Check this box if you\'re using your live keys.', 'flw-payments' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'logging_option'     => array(
				'title'       => __( 'Disable Logging', 'flw-payments' ),
				'label'       => __( 'Disable Logging', 'flw-payments' ),
				'type'        => 'checkbox',
				'description' => __( 'Check this box if you\'re disabling logging.', 'flw-payments' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'barter'             => array(
				'title'       => __( 'Disable Barter', 'flw-payments' ),
				'label'       => __( 'Disable Barter', 'flw-payments' ),
				'type'        => 'checkbox',
				'description' => __( 'Check the box if you want to disable barter.', 'flw-payments' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'webhook'            => array(
				'title'       => __( 'Webhook Instruction', 'flw-payments' ),
				'type'        => 'hidden',
				'description' => __( 'Please copy this webhook URL and paste on the webhook section on your dashboard <strong style="color: red"><pre><code>' . WC()->api_request_url( 'Flw_WC_Payment_Webhook' ) . '</code></pre></strong> (<a href="https://rave.flutterwave.com/dashboard/settings/webhooks" target="_blank">Rave Account</a>)', 'flw-payments' ),
			),
			'secret_hash'        => array(
				'title'       => __( 'Enter Secret Hash', 'flw-payments' ),
				'type'        => 'text',
				'description' => __( 'Ensure that <b>SECRET HASH</b> is the same with the one on your Rave dashboard', 'flw-payments' ),
				'default'     => 'Rave-Secret-Hash',
			),
			'title'              => array(
				'title'       => __( 'Payment method title', 'flw-payments' ),
				'type'        => 'text',
				'description' => __( 'Optional', 'flw-payments' ),
				'default'     => 'Rave',
			),
			'description'        => array(
				'title'       => __( 'Payment method description', 'flw-payments' ),
				'type'        => 'text',
				'description' => __( 'Optional', 'flw-payments' ),
				'default'     => 'Powered by Flutterwave: Accepts Mastercard, Visa, Verve, Discover, AMEX, Diners Club and Union Pay.',
			),
			'test_public_key'    => array(
				'title'   => __( 'Rave Test Public Key', 'flw-payments' ),
				'type'    => 'text',
				// 'description' => __( 'Required! Enter your Rave test public key here', 'flw-payments' ),
				'default' => '',
			),
			'test_secret_key'    => array(
				'title'   => __( 'Rave Test Secret Key', 'flw-payments' ),
				'type'    => 'text',
				// 'description' => __( 'Required! Enter your Rave test secret key here', 'flw-payments' ),
				'default' => '',
			),
			'live_public_key'    => array(
				'title'   => __( 'Rave Live Public Key', 'flw-payments' ),
				'type'    => 'text',
				// 'description' => __( 'Required! Enter your Rave live public key here', 'flw-payments' ),
				'default' => '',
			),
			'live_secret_key'    => array(
				'title'   => __( 'Rave Live Secret Key', 'flw-payments' ),
				'type'    => 'text',
				// 'description' => __( 'Required! Enter your Rave live secret key here', 'flw-payments' ),
				'default' => '',
			),
			'payment_style'      => array(
				'title'       => __( 'Payment Style on checkout', 'flw-payments' ),
				'type'        => 'select',
				'description' => __( 'Optional - Choice of payment style to use. Either inline or redirect. (Default: inline)', 'flw-payments' ),
				'options'     => array(
					'inline'   => esc_html_x( 'Popup(Keep payment experience on the website)', 'payment_style', 'flw-payments' ),
					'redirect' => esc_html_x( 'Redirect', 'payment_style', 'flw-payments' ),
				),
				'default'     => 'inline',
			),
			'autocomplete_order' => array(
				'title'       => __( 'Autocomplete Order After Payment', 'flw-payments' ),
				'label'       => __( 'Autocomplete Order', 'flw-payments' ),
				'type'        => 'checkbox',
				'class'       => 'wc-flw-autocomplete-order',
				'description' => __( 'If enabled, the order will be marked as complete after successful payment', 'flw-payments' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'payment_options'    => array(
				'title'       => __( 'Payment Options', 'flw-payments' ),
				'type'        => 'select',
				'description' => __( 'Optional - Choice of payment method to use. Card, Account etc.', 'flw-payments' ),
				'options'     => array(
					''                    => esc_html_x( 'Default', 'payment_options', 'flw-payments' ),
					'card'                => esc_html_x( 'Card Only', 'payment_options', 'flw-payments' ),
					'account'             => esc_html_x( 'Account Only', 'payment_options', 'flw-payments' ),
					'ussd'                => esc_html_x( 'USSD Only', 'payment_options', 'flw-payments' ),
					'qr'                  => esc_html_x( 'QR Only', 'payment_options', 'flw-payments' ),
					'mpesa'               => esc_html_x( 'Mpesa Only', 'payment_options', 'flw-payments' ),
					'mobilemoneyghana'    => esc_html_x( 'Ghana MM Only', 'payment_options', 'flw-payments' ),
					'mobilemoneyrwanda'   => esc_html_x( 'Rwanda MM Only', 'payment_options', 'flw-payments' ),
					'mobilemoneyzambia'   => esc_html_x( 'Zambia MM Only', 'payment_options', 'flw-payments' ),
					'mobilemoneytanzania' => esc_html_x( 'Tanzania MM Only', 'payment_options', 'flw-payments' ),
				),
				'default'     => '',
			),

		);

	}

	/**
	 * Order id
	 *
	 * @param $order_id
	 *
	 * @return array
	 */
	public function process_payment( $order_id ) {
		include_once dirname( __FILE__ ) . '/client/class-flw-wc-payment-gateway-request.php';

		$order               = wc_get_order( $order_id );
		$flutterwave_request = ( new FLW_WC_Payment_Gateway_Request() )->get_prepared_payload( $order );
		$sdk                 = $this->sdk->set_event_handler( new FlwEventHandler( $order ) );

		$response = $sdk->get_client()->request( $this->sdk::$standard_inline_endpoint, 'POST', $flutterwave_request );
		//phpcs:ignore 'redirect' => $order->get_checkout_payment_url( true )
		if ( ! is_wp_error( $response ) ) {
			$response = json_decode( $response['body'] );
			return array(
				'result'   => 'success',
				'redirect' => $response->data->link,
			);
		} else {
			wc_add_notice( 'Unable to Connect to Flutterwave.', 'error' );
			return;
		}

	}

	/**
	 * Handles admin notices
	 *
	 * @return void
	 */
	public function admin_notices(): void {

		if ( 'yes' === $this->enabled ) {

			if ( empty( $this->public_key ) || empty( $this->secret_key ) ) {
				/* translators: %s: url */
				echo sprintf( '<div class="error"><p>%s</p></div>', sprintf( (string) __( 'Flutterwave is enabled, but the <strong>Public Key</strong> and <strong>Secret Key</strong> are not configured. Please <a href="%s">click here</a> to configure it.', 'flw-payments' ), esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=rave' ) ), 'flw-payments' ) );
			}
		}

	}

	/**
	 * Checkout receipt page
	 *
	 * @return void
	 */
	public function receipt_page( $order ) {

		$order = wc_get_order( $order );

		echo '<p>' . __( 'Thank you for your order, please click the <b>Make Payment</b> button below to make payment. You will be redirected to a secure page where you can enter you card details or bank account details. <b>Please, do not close your browser at any point in this process.</b>', 'flw-payments' ) . '</p>';
		echo '<a class="button cancel" href="' . esc_url( $order->get_cancel_order_url() ) . '">';
		echo __( 'Cancel order &amp; restore cart', 'flw-payments' ) . '</a> ';
		echo '<button class="button alt  wc-forward" id="flw-pay-now-button">Make Payment</button> ';

	}

	/**
	 * Loads (enqueue) static files (js & css) for the checkout page
	 *
	 * @return void
	 */
	public function load_scripts() {

		if ( ! is_checkout_pay_page() ) {
			return;
		}
		$p_key           = $this->public_key;
		$payment_options = $this->payment_options;

		if ( $this->payment_style === 'inline' ) {
			wp_enqueue_script( 'flwpbf_inline_js', $this->base_url . '/flwv3-pug/getpaidx/api/flwpbf-inline.js', array(), FLW_WC_VERSION, true );
		}

		wp_enqueue_script( 'flw_js', plugins_url( 'assets/build/js/checkout.js', FLW_WC_PLUGIN_FILE ), array( 'jquery' ), FLW_WC_VERSION, true );

		$payment_args = [];

		if ( get_query_var( 'order-pay' ) ) {

			$order_key = urldecode( sanitize_text_field( $_REQUEST['key'] ) );
			$order_id  = absint( get_query_var( 'order-pay' ) );
			$cb_url    = WC()->api_request_url( 'FLW_WC_Payment_Gateway' ) . '?rave_id=' . $order_id;

			if ( $this->payment_style === 'inline' ) { // phpcs:ignore
				wp_enqueue_script( 'flwpbf_inline_js', $this->base_url . '/flwv3-pug/getpaidx/api/flwpbf-inline.js', array(), '1.0.0', true );
				$cb_url = WC()->api_request_url( 'FLW_WC_Payment_Gateway' );
			}

			$order = wc_get_order( $order_id );

			$txnref = 'WOOC_' . $order_id . '_' . time();
			$txnref = sanitize_text_field( $txnref );

			$amount         = $order->get_total();
			$email          = $order->get_billing_email();
			$currency       = $order->get_currency();
			$main_order_key = $order->get_order_key();

			// set the currency to route to their countries.
			switch ( $currency ) {
				case 'KES':
					$this->country = 'KE';
					break;
				case 'GHS':
					$this->country = 'GH';
					break;
				case 'ZAR':
					$this->country = 'ZA';
					break;
				case 'TZS':
					$this->country = 'TZ';
					break;

				default:
					$this->country = 'NG';
					break;
			}

			$country       = $this->country;
			$payment_style = $this->payment_style;

			$payment_args = compact( 'amount', 'email', 'txnref', 'p_key', 'currency', 'country', 'payment_options', 'cb_url', 'payment_style' );

			if ( $main_order_key === $order_key ) {
				$payment_args['desc']      = filter_var( $this->description, FILTER_SANITIZE_STRING );
				$payment_args['title']     = filter_var( $this->title, FILTER_SANITIZE_STRING );
				$payment_args['firstname'] = $order->get_billing_first_name();
				$payment_args['lastname']  = $order->get_billing_last_name();
			}

			update_post_meta( $order_id, '_flw_payment_txn_ref', $txnref );

		}

		wp_localize_script( 'flw_js', 'flw_payment_args', $payment_args );
	}

	/**
	 * Verify payment made on the checkout page
	 *
	 * @return void
	 */
	public function flw_verify_payment() {
		$public_key     = $this->public_key;
		$secret_key     = $this->secret_key;
		$logging_option = $this->logging_option;
		$sdk            = $this->sdk;

		if ( isset( $_GET['cancelled'] ) && isset( $_GET['order_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$order_id     = urldecode( sanitize_text_field( wp_unslash( $_GET['order_id'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
			$order        = wc_get_order( $order_id );
			$redirect_url = $order->get_checkout_payment_url( true );
			header( 'Location: ' . $redirect_url );
			die();
		}

		if ( isset( $_POST['tx_ref'] ) || isset( $_GET['tx_ref'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$txn_ref  = urldecode( sanitize_text_field( wp_unslash( $_GET['tx_ref'] ) ) ) ?? sanitize_text_field( wp_unslash( $_POST['tx_ref'] ) );// phpcs:ignore WordPress.Security.NonceVerification
			$o        = explode( '_', sanitize_text_field( $txn_ref ) );
			$order_id = intval( $o[1] );
			$order    = wc_get_order( $order_id );

			$sdk->set_event_handler( new FlwEventHandler( $order ) )->requery_transaction( $txn_ref );

			$redirect_url = $this->get_return_url( $order );
			header( 'Location: ' . $redirect_url );
			die();
		}
	}

	/**
	 * Process Webhook
	 */
	public function flutterwave_webhooks() {
		$public_key     = $this->public_key;
		$secret_key     = $this->secret_key;
		$logging_option = $this->logging_option;
		$sdk            = $this->sdk;

		$event = file_get_contents( 'php://input' );

		// retrieve the signature sent in the request header's.
		$signature = ( $_SERVER['HTTP_VERIF_HASH'] ?? '' ); // phpcs:ignore

		if ( ! $signature ) {
			// redirect to the home page.
			wp_safe_redirect( home_url() );
			exit();
		}

		$local_signature = $this->get_option( 'secret_hash' );

		if ( $signature !== $local_signature ) {
			echo 'Access Denied Hash does not match';
			exit();
		}

		http_response_code( 200 );
		$event = json_decode( $event );

		if ( 'charge.completed' === $event->event ) {
			sleep( 10 );

			$event_type = $event->event;
			$event_data = $event->data;

			$txn_ref  = sanitize_text_field( $event_data->tx_ref );
			$o        = explode( '_', sanitize_text_field( $txn_ref ) );
			$order_id = intval( $o[1] );
			$order    = wc_get_order( $order_id );
			// get order status.
			$current_order_status = $order->get_status();

			/**
			 * Fires after the webhook has been processed.
			 *
			 * @param string $event The webhook event.
			 * @since 2.3.0
			 */
			do_action( 'flw_webhook_after_action', wp_json_encode( $event, true ) );
			// TODO: Handle Checkout draft status for WooCommerce Blocks users.
			$statuses_in_question = array( 'pending', 'on-hold' );
			if ( ! in_array( $current_order_status, $statuses_in_question, true ) ) {
				$msg = wp_json_encode(
					array(
						'status'  => 'error',
						'message' => 'Order already processed',
					)
				);
				die( $msg ); //phpcs:ignore
			}

			$sdk->set_event_handler( new FlwEventHandler( $order ) )->webhook_verify( $event_type, $event_data );
		}
	}

	/**
	 * Save Customer Card Details
	 */
	public static function save_card_details( $rave_response, $user_id, $order_id ) {

		$token_code = $rave_response->card->card_tokens[0]->embedtoken ?? '';

		// save payment token to the order.
		self::save_subscription_payment_token( $order_id, $token_code );
		//phpcs:ignore $save_card = get_post_meta( $order_id, '_wc_rave_save_card', true );
	}

	/**
	 * Save payment token to the order for automatic renewal for further subscription payment
	 */
	public static function save_subscription_payment_token( $order_id, $payment_token ) {

		if ( ! function_exists( 'wcs_order_contains_subscription' ) ) {
			return;
		}

		if ( WC_Subscriptions_Order::order_contains_subscription( $order_id ) && ! empty( $payment_token ) ) {

			// Also store it on the subscriptions being purchased or paid for in the order
			if ( function_exists( 'wcs_order_contains_subscription' ) && wcs_order_contains_subscription( $order_id ) ) {

				$subscriptions = wcs_get_subscriptions_for_order( $order_id );

			} elseif ( function_exists( 'wcs_order_contains_renewal' ) && wcs_order_contains_renewal( $order_id ) ) {

				$subscriptions = wcs_get_subscriptions_for_renewal_order( $order_id );

			} else {

				$subscriptions = array();

			}

			foreach ( $subscriptions as $subscription ) {

				$subscription_id = $subscription->get_id();

				update_post_meta( $subscription_id, '_rave_wc_token', $payment_token );

			}
		}
	}
}



