<?php
/**
 * Order Manual Confirm Handler.
 *
 * @package COD_Verify_For_WooCommerce
 */

defined( 'ABSPATH' ) || exit;

/**
 * Adds and handles the admin "Confirm order (verified by admin)" order
 * action, for cases where the customer confirms outside the plugin
 * (phone, WhatsApp, etc.) instead of clicking the verification link.
 *
 * Runs the same success path as a customer's own link confirmation
 * (Processing status, token marked used, auto-cancel job cleaned up,
 * cov_order_confirmed fired so the existing customer confirmation
 * email goes out unchanged) but records _cov_confirmed_via as 'admin'
 * instead of 'link', and logs a distinct order note, so the two paths
 * stay tellable apart in order history and any future reporting.
 */
class COV_Order_Manual_Confirm_Handler {

	/**
	 * Token manager instance.
	 *
	 * @var COV_Token_Manager
	 */
	private COV_Token_Manager $token_manager;

	/**
	 * Order auto cancel instance.
	 *
	 * Used to temporarily detach the generic
	 * woocommerce_order_status_changed exit-listener around this
	 * class's own Pending Confirmation -> Processing transition, for
	 * the same reason COV_Confirmation_Handler does - so this
	 * deliberate confirmation isn't treated as an external/manual
	 * status change and doesn't get its token invalidated by the
	 * listener meant for that case.
	 *
	 * @var COV_Order_Auto_Cancel
	 */
	private COV_Order_Auto_Cancel $order_auto_cancel;

	/**
	 * Constructor.
	 *
	 * @param COV_Token_Manager     $token_manager     Token manager instance.
	 * @param COV_Order_Auto_Cancel $order_auto_cancel Order auto cancel instance.
	 */
	public function __construct(
		COV_Token_Manager $token_manager,
		COV_Order_Auto_Cancel $order_auto_cancel
	) {

		$this->token_manager     = $token_manager;
		$this->order_auto_cancel = $order_auto_cancel;
	}

	/**
	 * Add the manual confirm action to the order actions dropdown.
	 *
	 * Only offered while the order is in Pending Confirmation, same
	 * scoping as the resend action - this only makes sense for an
	 * order still waiting on verification.
	 *
	 * @param array $actions Existing order actions.
	 *
	 * @return array Modified order actions.
	 */
	public function add_order_action( array $actions ): array {

		global $theorder;

		if ( ! $theorder instanceof WC_Order ) {
			return $actions;
		}

		if ( COV_Helper::ORDER_STATUS_PENDING_CONFIRM !== $theorder->get_status() ) {
			return $actions;
		}

		$actions['cov_manual_confirm'] = __(
			'Confirm order (verified by admin)',
			'cod-verify-for-woocommerce'
		);

		return $actions;
	}

	/**
	 * Handle the manual confirm order action.
	 *
	 * @param WC_Order $order WooCommerce order object.
	 *
	 * @return void
	 */
	public function handle_order_action( WC_Order $order ): void {

		if ( ! COV_Helper::is_plugin_enabled() ) {
			return;
		}

		if ( COV_Helper::ORDER_STATUS_PENDING_CONFIRM !== $order->get_status() ) {
			return;
		}

		// Mark the token used, same as a genuine link confirmation -
		// this order is done waiting for verification either way, and
		// keeps is_token_used() as a single reliable "no longer
		// awaiting confirmation" signal regardless of which path got
		// it there.
		$this->token_manager->mark_token_used( $order );

		$order->update_meta_data(
			COV_Helper::META_CONFIRMED_AT,
			current_time( 'timestamp', true )
		);

		$order->update_meta_data(
			COV_Helper::META_CONFIRMED_VIA,
			'admin'
		);

		$order->add_order_note(
			__(
				'Order manually confirmed by admin — customer verification received outside the plugin (phone/WhatsApp/etc.).',
				'cod-verify-for-woocommerce'
			)
		);

		// Detach the generic exit-listener around this transition, for
		// the same reason COV_Confirmation_Handler does: this is a
		// deliberate confirmation, not an external/manual status
		// change, so it should not have its token metadata wiped by
		// the listener meant for that case.
		remove_action(
			'woocommerce_order_status_changed',
			array( $this->order_auto_cancel, 'handle_pending_confirmation_exit' ),
			10
		);

		try {

			$order->update_status(
				'processing',
				__(
					'Order confirmed by admin on customer\'s behalf.',
					'cod-verify-for-woocommerce'
				)
			);

		} finally {

			add_action(
				'woocommerce_order_status_changed',
				array( $this->order_auto_cancel, 'handle_pending_confirmation_exit' ),
				10,
				4
			);
		}

		$this->order_auto_cancel->unschedule_auto_cancel( $order->get_id() );

		/**
		 * Fires after an order has been confirmed, whether by the
		 * customer via the verification link or by an admin manually.
		 * Check COV_Helper::META_CONFIRMED_VIA to tell which.
		 *
		 * @param WC_Order $order WooCommerce order object.
		 */
		do_action(
			'cov_order_confirmed',
			$order
		);
	}
}