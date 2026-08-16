<?php
/**
 * Helper class
 *
 * Provides shared constants used throughout the plugin.
 *
 * @package COD_Verify_For_WooCommerce
 */

defined( 'ABSPATH' ) || exit;

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
	const META_CONFIRMED_VIA = '_cov_confirmed_via';

	/**
	 * Action Scheduler hook names.
	 */
	const ACTION_CANCEL_ORDER = 'cov_cancel_unconfirmed_order';

	/**
	 * Action Scheduler group.
	 */
	const ACTION_GROUP = 'cod-verify';

	/**
	 * Settings option names.
	 */
	const OPTION_GENERAL_SETTINGS = 'cov_settings_general';

	/**
	 * Transient/option flag: set on activation, tells the plugin to
	 * reschedule auto-cancel actions for orders left in Pending
	 * Confirmation once WooCommerce (and Action Scheduler) are ready.
	 */
	const OPTION_RESCHEDULE_FLAG = 'cov_reschedule_pending_orders';

	/**
	 * Verification timeout units.
	 */
	const TIMEOUT_MINUTES = 'minutes';
	const TIMEOUT_HOURS   = 'hours';

	/**
	 * Admin page slug.
	 */
	const PAGE_SETTINGS = 'cov-settings';

	/**
	 * Settings tabs.
	 */
	const SETTINGS_GENERAL = 'general';

	/**
	 * Get the General plugin settings.
	 *
	 * @return array
	 */
	public static function get_general_settings(): array {

		$settings = get_option(
			self::OPTION_GENERAL_SETTINGS,
			array()
		);

		return wp_parse_args(
			$settings,
			array(
				'enabled'         => 1,
				'timeout'         => 6,
				'timeout_unit'    => self::TIMEOUT_HOURS,
				'notify_merchant' => 0,
			)
		);
	}

	/**
	 * Check whether the plugin is enabled.
	 *
	 * @return bool
	 */
	public static function is_plugin_enabled(): bool {

		$settings = self::get_general_settings();

		return (bool) $settings['enabled'];
	}

	/**
	 * Get the token lifetime in seconds.
	 *
	 * @return int
	 */
	public static function get_token_lifetime(): int {

		$settings = self::get_general_settings();

		$value = max( 1, absint( $settings['timeout'] ) );
		$unit  = sanitize_key( $settings['timeout_unit'] );

		switch ( $unit ) {

			case self::TIMEOUT_MINUTES:
				return $value * MINUTE_IN_SECONDS;

			case self::TIMEOUT_HOURS:
			default:
				// Default to hours for unknown or invalid values.
				return $value * HOUR_IN_SECONDS;
		}
	}

	/**
	 * Get the token lifetime label.
	 *
	 * @return string
	 */
	public static function get_token_lifetime_label(): string {

		$settings = self::get_general_settings();

		$value = max( 1, absint( $settings['timeout'] ) );
		$unit  = sanitize_key( $settings['timeout_unit'] );

		$labels = array(
			self::TIMEOUT_MINUTES => _n(
				'minute',
				'minutes',
				$value,
				'cod-verify-for-woocommerce'
			),
			self::TIMEOUT_HOURS => _n(
				'hour',
				'hours',
				$value,
				'cod-verify-for-woocommerce'
			),
		);

		/* translators: 1: Timeout value, 2: Timeout unit. */
		return sprintf(
			'%1$d %2$s',
			$value,
			// Default to hours for unknown or invalid values.
			$labels[ $unit ] ?? $labels[ self::TIMEOUT_HOURS ]
		);
	}
}