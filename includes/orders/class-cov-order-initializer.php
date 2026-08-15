<?php
/**
 * Order Initializer.
 *
 * @package COD_Verify_For_WooCommerce
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles COD order initialization.
 */
class COV_Order_Initializer {

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
	 * Initialize a newly created WooCommerce order.
	 *
	 * @param int      $order_id    Order ID.
	 * @param array    $posted_data Checkout posted data.
	 * @param WC_Order $order       WooCommerce order object.
	 *
	 * @return void
	 */
	public function initialize_order( int $order_id, array $posted_data, WC_Order $order ) {

		if ( ! COV_Helper::is_plugin_enabled() ) {
			return;
		}

		if ( 'cod' !== $order->get_payment_method() ) {
			return;
		}

		if ( $this->token_manager->get_token( $order ) ) {
			return;
		}

		$this->setup_verification_token( $order );

		$order->update_status(
			COV_Helper::ORDER_STATUS_PENDING_CONFIRM,
			__( 'Order awaiting customer verification.', 'cod-verify-for-woocommerce' )
		);

		/**
		 * Fires after a COD order has been initialized for customer confirmation.
		 *
		 * Allows notification channels and future integrations (email, SMS,
		 * WhatsApp, etc.) to notify the customer.
		 *
		 * @param WC_Order $order WooCommerce order object.
		 */
		do_action(
			'cov_customer_confirmation_ready',
			$order
		);
	}

	/**
	 * Resend the verification link for an order stuck in Pending
	 * Confirmation - e.g. one whose token was invalidated by an
	 * external/manual status change and later moved back into Pending
	 * Confirmation (which never auto-restarts verification), or one
	 * where the merchant simply wants to extend the deadline.
	 *
	 * Regenerates the token and expiry via the same setup logic used
	 * at checkout, reschedules the auto-cancel job against the new
	 * expiry, and fires a dedicated action so only the customer
	 * confirmation email goes out - this is a merchant-initiated
	 * resend, so the merchant does not need their own "awaiting
	 * confirmation" email sent again.
	 *
	 * @param WC_Order $order WooCommerce order object.
	 *
	 * @return bool True if the link was resent, false if the order
	 *              wasn't eligible (plugin disabled or order not in
	 *              Pending Confirmation).
	 */
	public function resend_verification_link( WC_Order $order ): bool {

		if ( ! COV_Helper::is_plugin_enabled() ) {
			return false;
		}

		if ( COV_Helper::ORDER_STATUS_PENDING_CONFIRM !== $order->get_status() ) {
			return false;
		}

		$this->setup_verification_token( $order );

		$order->add_order_note(
			__(
				'Verification link resent to customer by admin.',
				'cod-verify-for-woocommerce'
			)
		);

		/**
		 * Fires after an admin resends a COD order's verification link.
		 *
		 * Deliberately separate from cov_customer_confirmation_ready so
		 * a resend only ever notifies the customer, never the merchant
		 * "awaiting confirmation" listeners.
		 *
		 * @param WC_Order $order WooCommerce order object.
		 */
		do_action(
			'cov_verification_link_resent',
			$order
		);

		return true;
	}

	/**
	 * Generate and store a fresh verification token and expiry for an
	 * order, and (re)schedule its auto-cancel Action Scheduler job
	 * against the new expiry.
	 *
	 * Shared by initialize_order() (first-time setup at checkout) and
	 * resend_verification_link() (admin resend), so both paths always
	 * stay in sync.
	 *
	 * Any existing scheduled job for this order is unscheduled first -
	 * safe/idempotent when nothing is scheduled (e.g. checkout, or a
	 * resend on an order that re-entered Pending Confirmation without
	 * a job) and correct when a job is still pending (e.g. resend
	 * before the original deadline), since the old job's timestamp is
	 * no longer accurate once the token/expiry are regenerated.
	 *
	 * @param WC_Order $order WooCommerce order object.
	 *
	 * @return void
	 */
	private function setup_verification_token( WC_Order $order ): void {

		$token = $this->token_manager->generate_token();

		$this->token_manager->store_token( $order, $token );

		$expires_at = current_time( 'timestamp', true ) + COV_Helper::get_token_lifetime();

		$this->token_manager->store_token_expiration( $order, $expires_at );

		$hook  = COV_Helper::ACTION_CANCEL_ORDER;
		$args  = array( $order->get_id() );
		$group = COV_Helper::ACTION_GROUP;

		as_unschedule_all_actions( $hook, $args, $group );

		as_schedule_single_action(
			$expires_at,
			$hook,
			$args,
			$group
		);
	}
}