<?php
/**
 * Merchant Order Confirmed Email - Plain Text.
 *
 * @var WC_Order $order
 * @var string   $email_heading
 * @var WC_Email $email
 */

defined( 'ABSPATH' ) || exit;
?>

<?php echo esc_html( $email_heading ); ?>


<?php esc_html_e( 'A new Cash on Delivery order has been successfully confirmed by the customer.', 'cod-verify-for-woocommerce' ); ?>


<?php
printf(
	/* translators: %s: order number */
	esc_html__(
		'Order #%s has completed customer verification and is now being processed.',
		'cod-verify-for-woocommerce'
	),
	esc_html( $order->get_order_number() )
);
?>


<?php esc_html_e( 'Order details:', 'cod-verify-for-woocommerce' ); ?>


<?php
do_action( 'woocommerce_email_order_details', $order, false, true, $email );
do_action( 'woocommerce_email_order_meta', $order, false, true, $email );
do_action( 'woocommerce_email_customer_details', $order, false, true, $email );
?>