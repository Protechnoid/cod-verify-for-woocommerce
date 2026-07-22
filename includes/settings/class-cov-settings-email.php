<?php
/**
 * Email Settings
 *
 * Handles the Email settings tab.
 *
 * @package COD_Verify_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class COV_Settings_Email implements COV_Settings_Tab_Interface {

	/**
	 * Get the tab slug.
	 *
	 * @return string
	 */
	public function get_slug() {

		return 'email';
	}

	/**
	 * Get the tab title.
	 *
	 * @return string
	 */
	public function get_title() {

		return __( 'Email', 'cod-verify-for-woocommerce' );
	}

	/**
	 * Register settings.
	 */
	public function register_settings() {
		// No settings yet.
	}

	/**
	 * Render the Email tab.
	 */
	public function render() {

		?>

		<h2><?php esc_html_e( 'Email Settings', 'cod-verify-for-woocommerce' ); ?></h2>

		<p>
			<?php esc_html_e( 'Email settings will be available in a future update.', 'cod-verify-for-woocommerce' ); ?>
		</p>

		<?php
	}
}