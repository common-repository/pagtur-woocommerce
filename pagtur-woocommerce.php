<?php
/**
 * Plugin Name: PagTur WooCommerce Plugin
 * Description: WooCommerce plugin para o gateway de pagamento brasileiro PagTur
 * Author: PagTur
 * Author URI: https://www.pagtur.com.br
 * Version: 1.1
 * License: MIT
 * Text Domain: pagtur-woocommerce-plugin
 * Domain Path: /languages
 * WC requires at least: 3.3
 * WC tested up to: 3.5
 *
 * @package WooCommerce_PagTur
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


define( 'WC_PAGTUR_MIN_PHP_VER', '5.6.0' );
define( 'WC_PAGTUR_MIN_WC_VER', '3.4.0' );
define( 'WC_PAGTUR_MIN_WP_VER', '4.0.0' );
define( 'WC_PAGTUR_DIR', __DIR__ . DIRECTORY_SEPARATOR );
define( 'WC_PAGTUR_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) . DIRECTORY_SEPARATOR );
define( 'WC_PAGTUR_PLUGIN_BASE', dirname(__FILE__) . DIRECTORY_SEPARATOR . basename( __FILE__ ) );
define( 'WC_PAGTUR_PLUGIN_NAME', WC_PAGTUR_PLUGIN_DIR_URL . basename( __FILE__ ) );
define( 'WC_PAGTUR_LANGUAGES_DIR', dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
define( 'WC_PAGTUR_ASSETS_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR );
define( 'WC_PAGTUR_INCLUDES_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR );
define( 'WC_PAGTUR_UTILS_DIR', WC_PAGTUR_INCLUDES_DIR . 'utils' . DIRECTORY_SEPARATOR );
define( 'WC_PAGTUR_CONTROLLERS_DIR', WC_PAGTUR_INCLUDES_DIR . 'controllers' . DIRECTORY_SEPARATOR );
define( 'WC_PAGTUR_MODELS_DIR', WC_PAGTUR_INCLUDES_DIR . 'models' . DIRECTORY_SEPARATOR );
define( 'WC_PAGTUR_VIEWS_DIR', WC_PAGTUR_INCLUDES_DIR . 'views' . DIRECTORY_SEPARATOR );
define( 'WC_PAGTUR_ADMIN_URL', 'admin.php?page=wc-settings&tab=checkout&section=wc_pagtur_payment_gateway');
define ('PAGTUR_SANDBOX_URI','https://test.api.pagtur.com.br');
define ('PAGTUR_PRODUCTION_URI','https://api.pagtur.com.br');

pagtur_load_textdomain();


add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'pagtur_settings_action_links' );

function pagtur_settings_action_links( $links ) {
	$plugin_links = array();

	$plugin_links[] = '<a href="' . esc_url( admin_url( WC_PAGTUR_ADMIN_URL) ) . '">' . __( 'Settings', 'woocommerce-pagtur' ) . '</a>';

	return array_merge( $plugin_links, $links );
}

add_filter (
	'woocommerce_payment_gateways',
	'wc_pagtur_add_gateway_class'
);

add_action( 'admin_init', 'pagtur_wp_sidebar_shortcut' );

function pagtur_wp_sidebar_shortcut() {
	add_menu_page(
		__('PagTur - Settings','woocommerce-pagtur'),
		__('PagTur - Settings','woocommerce-pagtur'),
		'administrator',
		WC_PAGTUR_ADMIN_URL,
		'',
		null,
		25
	);
}

function pagtur_load_textdomain() {
	try{
		load_plugin_textdomain( 'woocommerce-pagtur', false, WC_PAGTUR_LANGUAGES_DIR );
	}
	catch(Exception $ex){
		//Exception To Do
	}
}

function wc_pagtur_add_gateway_class($gateways){
	$gateways[] = 'WC_PagTur_Payment_Gateway';
	return $gateways;
}

add_action(
	'plugins_loaded',
	'WC_PagTur_Gateway_Class'
);
include_once WC_PAGTUR_CONTROLLERS_DIR . 'WC_PAGTUR_GATEWAY.php';



