<?php
/**
 * Settings Tab Interface
 *
 * Defines the contract for all settings tabs.
 *
 * @package COD_Verify_For_WooCommerce
 */

defined( 'ABSPATH' ) || exit;

/**
 * Interface for settings tabs.
 */
interface COV_Settings_Tab_Interface {

	/**
	 * Returns the tab slug.
	 *
	 * @return string
	 */
	public function get_slug(): string;

	/**
	 * Returns the tab title.
	 *
	 * @return string
	 */
	public function get_title(): string;

	/**
	 * Processes the settings form submission.
	 *
	 * @return void
	 */
	public function handle_save(): void;

	/**
	 * Sanitizes the submitted settings.
	 *
	 * @param array $settings Raw settings.
	 * @return array
	 */
	public function sanitize( array $settings ): array;

	/**
	 * Renders the settings tab.
	 *
	 * @return void
	 */
	public function render(): void;
}