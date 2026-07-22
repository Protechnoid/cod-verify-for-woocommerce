<?php
/**
 * Helper class
 *
 * Provides shared constants and helper methods used throughout the plugin.
 *
 * @package COD_Verify_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shared plugin constants and helper methods.
 */
class COV_Helper {

	/**
	 * Order statuses.
	 */
	const ORDER_STATUS_PENDING_CONFIRM = 'pending-confirm';

	/**
	 * Order meta keys.
	 */
	const META_TOKEN           = '_cov_token';
	const META_TOKEN_EXPIRES   = '_cov_token_expires';
	const META_TOKEN_USED      = '_cov_token_used';
	const META_CONFIRMED_AT    = '_cov_confirmed_at';

	/**
	 * Token lifetime in seconds.
	 */
	const TOKEN_LIFETIME = 6 * HOUR_IN_SECONDS;

	/**
	 * Cron hook names.
	 */
	const CRON_CANCEL_ORDER  = 'cov_cancel_unconfirmed_order';
	const CRON_SEND_REMINDER = 'cov_send_reminder_email';

	/**
	 * Settings.
	 */
	const OPTION_SETTINGS = 'cov_settings';
	const SETTINGS_GROUP  = 'cov_settings_group';

	/**
	 * Settings pages.
	 */
	const PAGE_SETTINGS   = 'cov-settings';

	/**
	 * Settings sections.
	 */
	const SECTION_GENERAL = 'cov_general_section';

	/**
	 * Settings tabs.
	 */
	const SETTINGS_GENERAL = 'general';
	const SETTINGS_EMAIL   = 'email';
	const SETTINGS_DEBUG   = 'debug';

	/**
	 * Get plugin settings.
	 *
	 * Returns all plugin settings or a specific settings section.
	 *
	 * @param string $section Optional settings section.
	 * @return array
	 */
	public static function get_settings( string $section = '' ): array {

		$settings = get_option(
			self::OPTION_SETTINGS,
			array()
		);

		if ( ! is_array( $settings ) ) {
			return array();
		}

		if ( '' === $section ) {
			return $settings;
		}

		return $settings[ $section ] ?? array();
	}
}