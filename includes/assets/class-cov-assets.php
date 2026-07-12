<?php
/**
 * Assets.
 *
 * Registers and enqueues plugin assets.
 *
 * @package COD_Verify_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Assets class.
 */
class COV_Assets {

    /**
     * Enqueue frontend assets.
     */
    public function enqueue_frontend_assets() {

        if ( ! isset( $_GET['cov_order_id'], $_GET['cov_token'] ) ) {
            return;
        }

        wp_enqueue_style(
            'cov-confirmation-status',
            COV_PLUGIN_URL . 'assets/css/confirmation-status.css',
            array(),
            COV_VERSION
        );

    }

}