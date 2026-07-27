<?php
/**
 * Order Initializer.
 *
 * @package COD_Verify_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

		$token = $this->token_manager->generate_token();

		$this->token_manager->store_token( $order, $token );

		$expires_at = current_time( 'timestamp', true ) + COV_Helper::get_token_lifetime();

		$this->token_manager->store_token_expiration( $order, $expires_at );

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
}