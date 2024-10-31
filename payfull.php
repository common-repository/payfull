<?php
/*
 *  Plugin Name: Payfull
 *  Plugin URI: https://www.payfull.com
 *  Description: Integrate PayFull payment service with WooCommerce checkout
 *  Text Domain: payfull
 *  Domain Path: /i18n/languages/
 *  Version: 1.1.0
 *  Author: Muhammed Alabed
 *  Author URI: https://payfull.com
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function payfull_load_textdomain() {
    load_plugin_textdomain( 'payfull', false, dirname( plugin_basename(__FILE__) ) . '/i18n/languages/' );
}

function payfull_init() {
    if(!defined('WOOCOMMERCE_VERSION')) {
        throw new \Exception('The WooCommerce is not activated.');
    }
    
    require_once dirname(__FILE__).'/src/WC_Gateway_Payfull.php';
    $instance = new WC_Gateway_Payfull(true);
    $instance->payfull_initApiService();
}

function payfull_add_class( $methods ) {
	$methods[] = 'WC_Gateway_Payfull';
    if (!class_exists('WC_Gateway_Payfull')) {
		die("not class_exists('WC_Gateway_Payfull')");
        return [];
    }
	return $methods;
}

function payfull_activate() {
	global $user_ID;
    $new_post = array(
		'post_title' => 'Payfull Payment Result',
		'post_content' => '[payfull_payment_result]',
		'post_status' => 'publish',
		'post_date' => date('Y-m-d H:i:s'),
		'post_author' => $user_ID,
		'post_type' => 'page',
		'post_category' => array(0)
	);
	$post_id = wp_insert_post($new_post);
	update_option('woo_payfull_payment_result_page_id', $post_id);
}

function payfull_deactivate() {
	$pid = get_option('woo_payfull_payment_result_page_id', null);
	if($pid) {
		wp_trash_post( $pid );
	}
}

function payfull_payment_result_shortcode( $atts ) {
	$html[] =  "payfull_payment_result_shortcode";
    $html[] = "<pre>";
    $html[] = print_r($_GET, 1);
    $html[] = "</pre>";
    return implode('', $html);
}


add_action('init', 'payfull_init', 0);
add_filter( 'woocommerce_payment_gateways', 'payfull_add_class' );
register_activation_hook( __FILE__, 'payfull_activate' );
register_deactivation_hook( __FILE__, 'payfull_deactivate' );
add_shortcode( 'payfull_payment_result', 'payfull_payment_result_shortcode' );
add_action('plugins_loaded', 'payfull_load_textdomain');
