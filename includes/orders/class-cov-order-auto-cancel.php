<?php
/**
 * Handles automatic cancellation of unconfirmed COD orders.
 *
 * @package COD_Verify_For_WooCommerce
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles automatic cancellation scheduling and cleanup.
 */
class COV_Order_Auto_Cancel {

	/**
	 * Token manager instance.
	 *
	 * @var COV_Token_Manager
	 */
	private COV_Token_Manager $token_manager;

	/**
	 * Constructor.
	 *
	 * @param COV_Token_Manager $token_manager Token manager instance.
	 */
	public function __construct( COV_Token_Manager $token_manager ) {

		$this->token_manager = $token_manager;
	}

	/**
	 * Automatically cancel an unconfirmed order.
	 *
	 * @param int $order_id WooCommerce order ID.
	 *
	 * @return void
	 */
	public function auto_cancel_order( int $order_id ): void {

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

		do_action(
			'cov_order_cancelled',
			$order
		);
	}

	/**
	 * Unschedule the pending auto-cancel Action Scheduler job for an
	 * order, if one exists.
	 *
	 * Safe to call even when nothing is scheduled. Shared by
	 * handle_pending_confirmation_exit() (external/manual status
	 * changes) and COV_Confirmation_Handler (the customer's own
	 * successful confirmation) - both need the stale job cleaned up,
	 * but only the former should also invalidate the token.
	 *
	 * @param int $order_id WooCommerce order ID.
	 *
	 * @return void
	 */
	public function unschedule_auto_cancel( int $order_id ): void {

		as_unschedule_all_actions(
			COV_Helper::ACTION_CANCEL_ORDER,
			array( $order_id ),
			COV_Helper::ACTION_GROUP
		);
	}

	/**
	 * Terminate verification when an order leaves the Pending
	 * Confirmation status via an external/manual status change.
	 *
	 * Fires on every status change (manual admin action, bulk action,
	 * another plugin, the REST API - anything that moves the order via
	 * woocommerce_order_status_changed). COV_Confirmation_Handler
	 * temporarily detaches this specific listener around its own
	 * Pending Confirmation -> Processing transition (the customer's own
	 * successful confirmation), so this method only ever runs for
	 * changes this plugin did not itself initiate via a confirmed link.
	 *
	 * Two things happen together, atomically, whenever an order leaves
	 * Pending Confirmation this way:
	 *
	 * 1. The scheduled auto-cancel Action Scheduler job is unscheduled,
	 *    so it can't later cancel an order that has already moved on.
	 * 2. The verification token is invalidated, so any previously
	 *    issued link stops working immediately - even if the order is
	 *    later moved back into Pending Confirmation. Verification is
	 *    never silently restarted on re-entry; only an explicit admin
	 *    "Resend verification link" action can issue a working link
	 *    again.
	 *
	 * @param int      $order_id Order ID.
	 * @param string   $from     Previous status.
	 * @param string   $to       New status.
	 * @param WC_Order $order    Order object.
	 *
	 * @return void
	 */
	public function handle_pending_confirmation_exit(
		int $order_id,
		string $from,
		string $to,
		WC_Order $order
	): void {

		if ( COV_Helper::ORDER_STATUS_PENDING_CONFIRM !== $from ) {
			return;
		}

		if ( COV_Helper::ORDER_STATUS_PENDING_CONFIRM === $to ) {
			return;
		}

		$this->unschedule_auto_cancel( $order_id );

		$this->token_manager->invalidate_token( $order );
	}
}