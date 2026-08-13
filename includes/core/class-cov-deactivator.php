<?php
/**
 * Plugin Deactivator.
 *
 * @package COD_Verify_For_WooCommerce
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles plugin deactivation.
 */
class COV_Deactivator {

	/**
	 * Deactivate the plugin.
	 *
	 * Unschedules the auto-cancel action for every order currently in
	 * Pending Confirmation. This prevents orphaned Action Scheduler
	 * actions from firing (as a no-op, since no callback will be
	 * registered) while the plugin is inactive.
	 *
	 * Orders left in Pending Confirmation are NOT touched otherwise —
	 * their stored token and expiration remain intact so activation can
	 * restore the original schedule without resetting the deadline.
	 *
	 * @return void
	 */
	public static function deactivate(): void {

		if ( ! function_exists( 'wc_get_orders' ) || ! function_exists( 'as_unschedule_all_actions' ) ) {
			return;
		}

		$order_ids = wc_get_orders(
			array(
				'status' => COV_Helper::ORDER_STATUS_PENDING_CONFIRM,
				'limit'  => -1,
				'return' => 'ids',
			)
		);

		foreach ( $order_ids as $order_id ) {

			as_unschedule_all_actions(
				COV_Helper::ACTION_CANCEL_ORDER,
				array( (int) $order_id ),
				COV_Helper::ACTION_GROUP
			);
		}
	}
}