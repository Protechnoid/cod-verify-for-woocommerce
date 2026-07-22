<?php
/**
 * Debug Settings
 * 
 * Handles Debug settings tab.
 * 
 * @package COD_Verify_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class COV_Settings_Debug implements COV_Settings_Tab_Interface {


	/**
	 * Get the tab slug.
	 *
	 * @return string
	 */
	public function get_slug() {

		return 'debug';
	}

    /**
	 * Get the tab title.
	 *
	 * @return string
	 */
	public function get_title() {

		return __( 'Debug', 'cod-verify-for-woocommerce' );
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

		<h2><?php esc_html_e( 'Debug Settings', 'cod-verify-for-woocommerce' ); ?></h2>

		<p>
			<?php esc_html_e( 'Debug settings will be available in a future update.', 'cod-verify-for-woocommerce' ); ?>
		</p>

		<?php
	}

}