<?php
/**
 * Plugin Activator.
 *
 * @package COD_Verify_For_WooCommerce
 */

defined( 'ABSPATH' ) || exit;

require_once COV_PLUGIN_PATH . 'includes/tokens/class-cov-token-manager.php';

/**
 * Handles plugin activation.
 */
class COV_Activator {

	/**
	 * Activate the plugin.
	 *
	 * Registering activation flags rather than calling wc_get_orders()/
	 * Action Scheduler functions directly here, because WooCommerce
	 * (and Action Scheduler) are not guaranteed to be fully loaded yet
	 * at the point register_activation_hook runs. The actual reschedule
	 * work happens in maybe_reschedule_pending_orders(), hooked on
	 * wp_loaded.
	 *
	 * @return void
	 */
	public static function activate(): void {

		update_option( COV_Helper::OPTION_RESCHEDULE_FLAG, 1 );
	}

	/**
	 * Restore auto-cancel scheduling for orders left in Pending
	 * Confirmation while the plugin was inactive.
	 *
	 * Runs once, on the first wp_loaded after activation. Reuses
	 * each order's existing stored token expiration rather than resetting
	 * the deadline - consistent with how resend/reminders never extend
	 * the original deadline. If the stored deadline has already passed
	 * (plugin was off past the timeout), the order is scheduled for
	 * cancellation right away instead of being left to linger.
	 *
	 * @return void
	 */
	public static function maybe_reschedule_pending_orders(): void {

		if ( ! get_option( COV_Helper::OPTION_RESCHEDULE_FLAG ) ) {
			return;
		}

		if ( ! function_exists( 'wc_get_orders' ) || ! function_exists( 'as_schedule_single_action' ) || ! function_exists( 'as_has_scheduled_action' ) ) {
			return;
		}

		delete_option( COV_Helper::OPTION_RESCHEDULE_FLAG );

		$orders = wc_get_orders(
			array(
				'status' => COV_Helper::ORDER_STATUS_PENDING_CONFIRM,
				'limit'  => -1,
			)
		);

		$token_manager = new COV_Token_Manager();

		foreach ( $orders as $order ) {

			if ( ! $order instanceof WC_Order ) {
				continue;
			}

			$order_id = $order->get_id();

			if ( as_has_scheduled_action( COV_Helper::ACTION_CANCEL_ORDER, array( $order_id ), COV_Helper::ACTION_GROUP ) ) {
				continue;
			}

			$expires_at = $token_manager->get_token_expiration( $order );

			// No expiration on record (shouldn't normally happen) - fall
			// back to scheduling an immediate check rather than skipping
			// the order entirely.
			$timestamp = $expires_at > 0 ? $expires_at : time();

			// Deadline already passed while the plugin was inactive -
			// don't silently extend it, cancel as soon as possible.
			$timestamp = max( $timestamp, time() );

			as_schedule_single_action(
				$timestamp,
				COV_Helper::ACTION_CANCEL_ORDER,
				array( $order_id ),
				COV_Helper::ACTION_GROUP,
			);
		}
	}
}