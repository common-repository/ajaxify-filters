<?php
/**
* Plugin Name: Ajaxify filters
* Plugin URI: https://cedcommerce.com
* Description: WooCommerce Extension lets you user to apply the filters you need to display the correct WooCommerce variations of the products you are looking for. 
* Version: 1.0.5
* Author: CedCommerce <plugins@cedcommerce.com>
* Author URI: https://cedcommerce.com
* Requires at least: 4.0
* Tested up to: 5.4
* Text Domain: ajaxify-filters
* Domain Path: /language/
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Check for multisite support
 **/
$activated = true;
if (function_exists('is_multisite') && is_multisite())
{
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) )
	{
		$activated = false;
	}
}
else
{
	/**
	 * Check if WooCommerce is active
	 **/
	if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))))
	{
		$activated = false;
	}
}

if($activated)
{
	//defining prefix for the plugin-environment
	define( 'CED_CAF_PREFIX', 'ced_caf_' );
	define( 'CED_CAF_TXTDOMAIN', 'ajaxify-filters' );
	define( 'CED_CAF_VERSION', '1.0.5' );
	define( 'CED_CAF_PLUGIN_NAME', trim( dirname( plugin_basename( __FILE__ ) ), '/' ) );
	define( 'CED_CAF_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ));
	define( 'CED_CAF_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ));
	define( 'CED_CAF_PLUGIN_BASE_FILE', plugin_basename( __FILE__ ) );
	
	//including the core file resonsible for executing plugin	
	require_once 'core/class-caf-core.php';
}