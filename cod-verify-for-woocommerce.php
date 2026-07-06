<?php
/*
Plugin Name: COD Verify for WooCommerce
Plugin URI: https://protechnoid.com
Description: Verify Cash on Delivery orders before processing to reduce fake and abandoned COD orders.
Version: 1.0.0
Requires at least: 6.8
Requires PHP: 8.0
Requires Plugins: woocommerce
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

// Constants

define( 'COV_VERSION', '1.0.0' ); 
define( 'COV_PLUGIN_FILE', __FILE__ );
define( 'COV_PLUGIN_PATH', plugin_dir_path( COV_PLUGIN_FILE ) ); //Filesystem path
define( 'COV_PLUGIN_URL', plugin_dir_url( COV_PLUGIN_FILE ) ); //Browser URL
define( 'COV_PLUGIN_BASENAME', plugin_basename( COV_PLUGIN_FILE ) ); //Plugin identifier
 

require_once COV_PLUGIN_PATH . '/includes/core/class-cov-plugin.php';
require_once COV_PLUGIN_PATH . '/includes/core/class-cov-loader.php';
require_once COV_PLUGIN_PATH . '/includes/core/class-cov-activator.php';
require_once COV_PLUGIN_PATH . '/includes/core/class-cov-deactivator.php';


register_activation_hook( COV_PLUGIN_FILE, array( 'COV_Activator', 'activate' ) );
register_deactivation_hook( COV_PLUGIN_FILE, array( 'COV_Deactivator', 'deactivate' ) );


function cov_run_plugin() {
	
	$plugin = new COV_Plugin();
	$plugin->run();
}

cov_run_plugin();



