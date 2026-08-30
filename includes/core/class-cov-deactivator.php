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
	 * Moves every order currently in Pending Confirmation to On hold
	 * and unschedules its auto-cancel action. The existing order status
	 * transition logic handles the associated COD Verify cleanup.
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

			$order = wc_get_order( $order_id );

			if ( ! $order instanceof WC_Order ) {
				continue;
			}

			$order->update_status(
				'on-hold',
				__( 'COD Verify was deactivated. The order was moved from Pending Confirmation to On hold.', 'cod-verify-for-woocommerce' )
			);
		}
	}
}
