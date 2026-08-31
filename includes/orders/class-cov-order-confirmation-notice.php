<?php
/**
 * Order Confirmation Notice.
 *
 * @package COD_Verify_For_WooCommerce
 */

defined( 'ABSPATH' ) || exit;

/**
 * Displays a COD verification notice on the WooCommerce
 * order confirmation page.
 */
class COV_Order_Confirmation_Notice {

	/**
	 * Render the verification notice.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return void
	 */
	public function render_notice( $order_id ): void {

		$order = wc_get_order( $order_id );

		if ( ! $order instanceof WC_Order ) {
			return;
		}

		if ( 'cod' !== $order->get_payment_method() ) {
			return;
		}

		$settings = COV_Helper::get_general_settings();

		if ( empty( $settings['enabled'] ) ) {
			return;
		}

		if ( COV_Helper::ORDER_STATUS_PENDING_CONFIRM !== $order->get_status() ) {
			return;
		}

		echo '<div class="woocommerce-info cov-order-confirmation-notice">';
		echo '<strong>' . esc_html__( 'Action required: Verify your Cash on Delivery order', 'cod-verify-for-woocommerce' ) . '</strong>';
		echo '<p>' . esc_html__( 'Please check your email and click the verification link to confirm your order. Your order will be processed after verification.', 'cod-verify-for-woocommerce' ) . '</p>';
		echo '</div>';
	}
}
