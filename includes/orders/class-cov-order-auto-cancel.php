<?php
/**
 * Handles automatic cancellation of unconfirmed COD orders.
 *
 * @package COD_Verify_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles automatic cancellation scheduling and cleanup.
 */
class COV_Order_Auto_Cancel {

	/**
	 * Automatically cancel an unconfirmed order.
	 *
	 * @param int $order_id WooCommerce order ID.
	 *
	 * @return void
	 */
	public function auto_cancel_order( int $order_id ) {

		if ( ! COV_Helper::is_plugin_enabled() ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		if ( COV_Helper::ORDER_STATUS_PENDING_CONFIRM !== $order->get_status() ) {
			return;
		}

		$order->update_status(
			'cancelled',
			__(
				'Order automatically cancelled because the customer did not verify the COD order before the verification timeout.',
				'cod-verify-for-woocommerce'
			)
		);

		$order->add_order_note(
			__(
				'COD verification timeout reached. Order was automatically cancelled.',
				'cod-verify-for-woocommerce'
			)
		);
	}

	/**
	 * Unschedule the auto-cancel event when an order leaves
	 * the pending confirmation status.
	 *
	 * @param int      $order_id   Order ID.
	 * @param string   $from       Previous status.
	 * @param string   $to         New status.
	 * @param WC_Order $order      Order object.
	 *
	 * @return void
	 */
	public function maybe_unschedule_auto_cancel( $order_id, $from, $to, $order ) {

		if ( COV_Helper::ORDER_STATUS_PENDING_CONFIRM !== $from ) {
			return;
		}

		if ( COV_Helper::ORDER_STATUS_PENDING_CONFIRM === $to ) {
			return;
		}

		$hook = COV_Helper::CRON_CANCEL_ORDER;
		$args = array( $order_id );

		$timestamp = wp_next_scheduled( $hook, $args );

		if ( false !== $timestamp ) {
			wp_unschedule_event( $timestamp, $hook, $args );
		}
	}
}