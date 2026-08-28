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
	 *
	 * @return void
	 */
	public function enqueue_frontend_assets(): void {

		// These public query parameters identify the verification page.
		// No nonce is applicable because this is a customer-facing email link.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public verification URLs use the secure order token instead of a WordPress nonce.
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
	public function enqueue_admin_assets(): void {

		$screen = get_current_screen();

		if ( ! $screen || 'toplevel_page_' . COV_Helper::PAGE_SETTINGS !== $screen->id ) {
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