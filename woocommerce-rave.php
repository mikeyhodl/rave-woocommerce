<?php

/*
Plugin Name: Flutterwave WooCommerce Payment Gateway
Plugin URI: https://rave.flutterwave.com/
Description: Official WooCommerce payment gateway for Flutterwave.
Version: 2.2.6
Author: Flutterwave Developers
Author URI: http://developer.flutterwave.com
License: MIT License
WC requires at least:   3.0.0
WC tested up to:        4.9.2
Text Domain: flw-payments
*/


if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

define( 'FLW_WC_PLUGIN_FILE', __FILE__ );
define( 'FLW_WC_DIR_PATH', plugin_dir_path( FLW_WC_PLUGIN_FILE ) );
define( 'FLW_WC_ASSET_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );

add_action( 'admin_bar_menu', 'flw_add_admin_bar', 100 );



  function flw_woocommerce_rave_init() {

    if ( !class_exists( 'WC_Payment_Gateway' ) ) return;

    require_once( FLW_WC_DIR_PATH . 'includes/class.flw_wc_payment_gateway.php' );

    // include subscription if exists
    if ( class_exists( 'WC_Subscriptions_Order' ) && class_exists( 'WC_Payment_Gateway_CC' ) ) {

      require_once( FLW_WC_DIR_PATH . 'includes/class.flw_wc_subscription_payment.php' );
      
    }

    add_filter('woocommerce_payment_gateways', 'flw_woocommerce_add_rave_gateway', 99 );
  }
  add_action('plugins_loaded', 'flw_woocommerce_rave_init', 99);

  /**
   * Add the Settings link to the plugin
   *
   * @param  Array $links Existing links on the plugin page
   *
   * @return Array          Existing links with our settings link added
   */
  function flw_plugin_action_links( $links ) {

    $rave_settings_url = esc_url( get_admin_url( null, 'admin.php?page=wc-settings&tab=checkout&section=rave' ) );
    array_unshift( $links, "<a title='Rave Settings Page' href='$rave_settings_url'>Settings</a>" );

    return $links;

  }
  add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'flw_plugin_action_links' );

  /**
   * Add the Gateway to WooCommerce
   *
   * @param  Array $methods Existing gateways in WooCommerce
   *
   * @return Array          Gateway list with our gateway added
   */
  function flw_woocommerce_add_rave_gateway($methods) {

    if ( class_exists( 'WC_Subscriptions_Order' ) && class_exists( 'WC_Payment_Gateway_CC' ) ) {

      $methods[] = 'FLW_WC_Payment_Gateway_Subscriptions';

    } else {

      $methods[] = 'FLW_WC_Payment_Gateway';
    }

    return $methods;

  }

  function flw_add_admin_bar( $admin_bar ){

    $admin_bar->add_menu(
      array(
        'id' => 'flw-settings-page',
        'title' => 'Flutterwave',
        'href' => 'admin.php?page=wc-settings&tab=checkout&section=rave'
      )
      );

  }

  /**
 * Add a widget to the dashboard.
 *
 * This function is hooked into the 'wp_dashboard_setup' action below.
 */
function wc_orders_dashboard_widgets() {

	      wp_add_dashboard_widget(
                 'wc_order_widget_id',         // Widget slug.
                 'WooCommerce Orders',         // Title.
                 'wc_orders_dashboard_widget_function' // Display function.
        );	
}
add_action( 'wp_dashboard_setup', 'wc_orders_dashboard_widgets' );

function wc_orders_dashboard_widget_function($post) {

  echo "id of the page is ";
  print_r($post);
  
}

/**
 * Register meta box(es).
 */
function flutterwave_subaccount_meta_boxes() {
  add_meta_box( 'flutterwave_metabox_1', 'Flutterwave - Select Subaccount', 'flutterwave_add_subaccount_callback', ['product'], 'side');
}
add_action( 'add_meta_boxes', 'flutterwave_subaccount_meta_boxes' );

/**
* Meta box display callback.
*
* @param WP_Post $post Current post object.
*/
function flutterwave_add_subaccount_callback( $post_id ) {

  $flutterwave_data = get_option('woocommerce_rave_settings');

  check_flw_option($flutterwave_data);
  
}



/**
* Save meta box content.
*
* @param int $post_id Post ID
*/
function flutterwave_save_meta_box( $post_id ) {
  // echo '<pre>';
  // print_r($_POST);
  // echo '</pre>';
  // die();

  if(!empty($_POST['flw_subaccount_assign'])){


    update_post_meta( $post_id, 'flw_subaccount_assign', $_POST['flw_subaccount_assign']);
    update_post_meta( $post_id, 'flw_subaccount_ratio', (float)$_POST['flw_subaccount_ratio_'.$_POST['flw_subaccount_assign']]);

  }

}

add_action( 'save_post', 'flutterwave_save_meta_box' );

/**
* check the flutterwave settings.
*
* @param array $flw_option FLUTTERWAVE SETTINGS
*/
function check_flw_option( $flw_option){


   (empty($flw_option))? '<pre>Please Setup your Flutterwave account to assign a subaccount</pre>': get_subaccounts($flw_option) ;

}

/**
*get all existing subaccounts 
*
* @param int $flutterwave_data FLW SETTINGS
*/
function get_subaccounts($flutterwave_data){

  // echo get_post_meta( get_the_ID(), 'flw_subaccount_assign', true );

  $secret_key = (isset($flutterwave_data['go_live']) && $flutterwave_data['go_live'] == 'yes')? $flutterwave_data['live_secret_key']: $flutterwave_data['test_secret_key'] ;

  // echo $auth;

  $args = array(
    'headers' => array(
      'Content-Type'=> 'application/json',
      'Authorization' => 'Bearer '.$secret_key
    ),
  );
  
  //returns all the list of subaccounts created
  $response_flw = wp_remote_get( 'https://api.flutterwave.com/v3/subaccounts', $args );

  // echo "<pre>";
  // print_r(json_decode(wp_remote_retrieve_body( $response_flw), true));
  // echo "</pre>";
  // exit();

  $flw_response_body = json_decode(wp_remote_retrieve_body( $response_flw), true);

  if ( is_wp_error( $response_flw ) ) {
    $error_message = $response_flw->get_error_message();
    echo "Something went wrong: $error_message";
  }else{

    if($flw_response_body['status'] != 'success' && isset($flw_response_body['message'])){

      echo "<p>".$flw_response_body['message']."</p>";
    }else{

      $subaccount_list = $flw_response_body['data'];

      echo '<label for="flw_subaccount_assign"> Assigned Subaccount:   </label>';
      echo '<select name="flw_subaccount_assign">';
      echo '<option value="">--Select Subaccount--</option>';
      foreach ($subaccount_list as $subaccount) {
        
                echo '<option value="'.$subaccount['subaccount_id'].'"'.selected( get_post_meta( get_the_ID(), 'flw_subaccount_assign', true ), $subaccount['subaccount_id'], false ).'>'.$subaccount['business_name'].' </option>';
                
      }
      echo ' </select>';
      foreach ($subaccount_list as $subaccount) {
        echo '<input type="hidden" name="flw_subaccount_ratio_'.$subaccount['subaccount_id'].'" value="'.$subaccount['split_value'].'"/>';
      }

    }

  }


}

?>