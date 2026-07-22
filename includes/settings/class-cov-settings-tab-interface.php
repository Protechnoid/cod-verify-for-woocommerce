<?php
/**
 * Settings Tab Interface
 *
 * Defines the contract for all settings tabs.
 *
 * @package COD_Verify_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings tab interface.
 */
interface COV_Settings_Tab_Interface {

	/**
	 * Get the tab slug.
	 *
	 * @return string
	 */
	public function get_slug();

	/**
	 * Get the tab title.
	 *
	 * @return string
	 */
	public function get_title();

	/**
	 * Render the tab.
	 *
	 * @return void
	 */
	public function render();

	/**
	 * Register settings for the tab.
	 *
	 * @return void
	 */
	public function register_settings();

}