<?php
/**
 * Main plugin class
 * 
 * @package COD_Verify_For_WooCommerce
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once COV_PLUGIN_PATH . 'includes/orders/class-cov-order-status.php';

class COV_Plugin {

    private COV_Loader $loader;

    /**
     * Runs the plugin.
     */
    public function run() {

        $this->loader = new COV_Loader();

        $this->define_hooks();
        
        $this->loader->run();

    }

    /**
     * Registers all plugin hooks.
     */
    private function define_hooks() {

        $order_status = new COV_Order_Status();
        
        $this->loader->add_action( 
            'init', 
            $order_status, 
            'register_order_status' 
        );

        $this->loader->add_filter(
            'wc_order_statuses',
            $order_status,
            'add_order_status'
        );

    }  

}
