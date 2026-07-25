<?php
/**
 * Email Settings
 *
 * Handles rendering and persistence of the Email settings tab.
 *
 * @package COD_Verify_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email settings tab.
 */
class COV_Settings_Email implements COV_Settings_Tab_Interface {

	/**
	 * Get the tab slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {

		return COV_Helper::SETTINGS_EMAIL;
	}

	/**
	 * Get the tab title.
	 *
	 * @return string
	 */
	public function get_title(): string {

		return __( 'Email', 'cod-verify-for-woocommerce' );
	}

	/**
	 * Render the Email tab.
	 */
	public function render(): void {

		?>

		<h2><?php esc_html_e( 'Email Settings', 'cod-verify-for-woocommerce' ); ?></h2>

		<p>
			<?php esc_html_e( 'Email settings will be available in a future update.', 'cod-verify-for-woocommerce' ); ?>
		</p>

		<?php
	}

	/**
	 * Processes the settings form submission.
	 *
	 * @return void
	 */
	public function handle_save(): void {

		// Email settings are not implemented yet.
	}

	/**
	 * Sanitizes the submitted settings.
	 *
	 * @param array $settings Raw settings.
	 * @return array
	 */
	public function sanitize( array $settings ): array {

		// No email settings to sanitize yet.
		return $settings;
	}
}