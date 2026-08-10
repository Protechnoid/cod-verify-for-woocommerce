<?php
/**
 * Assets.
 *
 * Registers and enqueues plugin assets.
 *
 * @package COD_Verify_For_WooCommerce
 */

defined( 'ABSPATH' ) || exit;

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

	/**
	 * Enqueue admin assets.
	 *
	 * @return void
	 */
	public function enqueue_admin_assets() {

		if ( ! isset( $_GET['page'] ) || COV_Helper::PAGE_SETTINGS !== sanitize_key( $_GET['page'] ) ) {
			return;
		}

		wp_enqueue_style(
			'cov-admin',
			COV_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			COV_VERSION
		);
	}
}