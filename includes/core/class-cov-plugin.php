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

require_once COV_PLUGIN_PATH . 'includes/helper/class-cov-helper.php';
require_once COV_PLUGIN_PATH . 'includes/orders/class-cov-order-status.php';
require_once COV_PLUGIN_PATH . 'includes/tokens/class-cov-token-manager.php';
require_once COV_PLUGIN_PATH . 'includes/confirmation/class-cov-confirmation-handler.php';
require_once COV_PLUGIN_PATH . 'includes/assets/class-cov-assets.php';
require_once COV_PLUGIN_PATH . 'includes/orders/class-cov-order-initializer.php';
require_once COV_PLUGIN_PATH . 'includes/settings/class-cov-settings.php';

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

        $token_manager = new COV_Token_Manager();

        $confirmation_handler = new COV_Confirmation_Handler( $token_manager );

        $assets = new COV_Assets();

        /**
         * Handle customer confirmation requests before the theme template loads.
         */
        $this->loader->add_action(
            'template_redirect',
            $confirmation_handler,
            'handle_confirmation_request'
        );

        $this->loader->add_action(
            'wp_enqueue_scripts',
            $assets,
            'enqueue_frontend_assets'
        );

        $order_initializer = new COV_Order_Initializer( $token_manager );

        $this->loader->add_action(
            'woocommerce_checkout_order_processed',
            $order_initializer,
            'initialize_order',
            10,
            3
        );

        $settings = new COV_Settings();

        $this->loader->add_action(
            'admin_menu',
            $settings,
            'register_admin_menu'
        );

        $this->loader->add_action(
            'admin_init',
            $settings,
            'register_settings'
        );
    }  

}
