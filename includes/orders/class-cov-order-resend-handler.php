<?php
/**
 * Order Resend Handler.
 *
 * @package COD_Verify_For_WooCommerce
 */

defined( 'ABSPATH' ) || exit;

/**
 * Adds and handles the admin "Resend COD verification link" order action.
 *
 * Surfaces the resend as a native WooCommerce order action (the
 * "Order actions" dropdown on the order edit screen) rather than a
 * custom meta box or AJAX endpoint, so it lives where merchants
 * already look for actions like "Resend order emails" and reuses
 * WooCommerce's own save/redirect handling for free.
 */
class COV_Order_Resend_Handler {

	/**
	 * Order initializer instance.
	 *
	 * @var COV_Order_Initializer
	 */
	private COV_Order_Initializer $order_initializer;

	/**
	 * Constructor.
	 *
	 * @param COV_Order_Initializer $order_initializer Order initializer instance.
	 */
	public function __construct( COV_Order_Initializer $order_initializer ) {

		$this->order_initializer = $order_initializer;
	}

	/**
	 * Add the resend action to the order actions dropdown.
	 *
	 * Only offered while the order is in Pending Confirmation - this
	 * is the only status resend_verification_link() will act on, and
	 * WooCommerce populates $theorder as a global before applying this
	 * filter from the core "Order actions" meta box.
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

		$actions['cov_resend_verification'] = __(
			'Resend COD verification link',
			'cod-verify-for-woocommerce'
		);

		return $actions;
	}

	/**
	 * Handle the resend order action.
	 *
	 * Fired by WooCommerce's core order-actions save handler when the
	 * merchant selects "Resend COD verification link" and clicks
	 * Update. Feedback is via the order note added inside
	 * resend_verification_link() - it appears in the Order notes
	 * panel as soon as the page reloads.
	 *
	 * @param WC_Order $order WooCommerce order object.
	 *
	 * @return void
	 */
	public function handle_order_action( WC_Order $order ): void {

		$this->order_initializer->resend_verification_link( $order );
	}
}