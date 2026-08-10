<?php
/**
 * Merchant Order Cancelled Email - Plain Text.
 *
 * @var WC_Order $order
 * @var string   $email_heading
 * @var WC_Email $email
 */

defined( 'ABSPATH' ) || exit;
?>

<?php echo esc_html( $email_heading ); ?>


<?php esc_html_e( 'A Cash on Delivery order has been automatically cancelled because the customer failed to complete verification.', 'cod-verify-for-woocommerce' ); ?>


<?php
printf(
	/* translators: %s: order number */
	esc_html__(
		'Order #%s was cancelled because the customer did not complete the required verification within the verification timeout.',
		'cod-verify-for-woocommerce'
	),
	esc_html( $order->get_order_number() )
);
?>


<?php esc_html_e( 'No further processing or shipment is required for this order.', 'cod-verify-for-woocommerce' ); ?>


<?php
do_action( 'woocommerce_email_order_details', $order, false, true, $email );
do_action( 'woocommerce_email_order_meta', $order, false, true, $email );
do_action( 'woocommerce_email_customer_details', $order, false, true, $email );
?>