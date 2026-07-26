<?php
/**
 * Helper class
 *
 * Provides shared constants used throughout the plugin.
 *
 * @package COD_Verify_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shared plugin constants.
 */
class COV_Helper {

	/**
	 * Order statuses.
	 */
	const ORDER_STATUS_PENDING_CONFIRM = 'pending-confirm';

	/**
	 * Order meta keys.
	 */
	const META_TOKEN         = '_cov_token';
	const META_TOKEN_EXPIRES = '_cov_token_expires';
	const META_TOKEN_USED    = '_cov_token_used';
	const META_CONFIRMED_AT  = '_cov_confirmed_at';

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
	 * Settings option names.
	 */
	const OPTION_GENERAL_SETTINGS = 'cov_settings_general';
	const OPTION_EMAIL_SETTINGS   = 'cov_settings_email';

	/**
	 * Admin page slug.
	 */
	const PAGE_SETTINGS = 'cov-settings';

	/**
	 * Settings tabs.
	 */
	const SETTINGS_GENERAL = 'general';
	const SETTINGS_EMAIL   = 'email';


	/**
	 * Check whether the plugin is enabled.
	 *
	 * @return bool True if the plugin is enabled, otherwise false.
	 */
	public static function is_plugin_enabled(): bool {

		$settings = get_option(
			self::OPTION_GENERAL_SETTINGS,
			array()
		);

		$settings = wp_parse_args(
			$settings,
			array(
				'enabled' => true,
			)
		);

		return (bool) $settings['enabled'];
	}

}