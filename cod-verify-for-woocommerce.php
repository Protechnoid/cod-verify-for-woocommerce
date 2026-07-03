<?php
/*
Plugin Name: COD Verify for WooCommerce
Plugin URI: https://protechnoid.com
Description: Verify Cash on Delivery orders before processing to reduce fake and abandoned COD orders.
Version: 1.0.0
Requires at least: 6.8
Requires PHP: 8.0
Author: Protechnoid
Author URI: https://protechnoid.com
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: cod-verify-for-woocommerce
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} 

//Constants

define( 'COV_VERSION', '1.0.0' ); 
define( 'COV_PLUGIN_PATH', plugin_dir_path( __FILE__ ) ); //Filesystem path
define( 'COV_PLUGIN_URL', plugin_dir_url( __FILE__ ) ); //Browser URL
define( 'COV_PLUGIN_BASENAME', plugin_basename( __FILE__ ) ); //Plugin identifier

 
require_once COV_PLUGIN_PATH . '/includes/core/class-cov-plugin.php';
require_once COV_PLUGIN_PATH . '/includes/compatibility/class-cov-compatibility.php';


function cov_run_plugin() {
	
	$plugin = new COV_Plugin();
	$plugin->run();
}

cov_run_plugin();



