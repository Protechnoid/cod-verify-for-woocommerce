<?php
/**
 * Customer Order Confirmed Email - Plain Text.
 *
 * @var WC_Order $order
 * @var string   $email_heading
 * @var WC_Email   $email
 */

defined( 'ABSPATH' ) || exit;
?>

<?php echo esc_html( $email_heading ); ?>


<?php
printf(
	/* translators: %s: customer first name */
	esc_html__( 'Hello %s,', 'cod-verify-for-woocommerce' ),
	esc_html( $order->get_billing_first_name() )
);
?>


<?php esc_html_e( 'Thank you for confirming your Cash on Delivery order.', 'cod-verify-for-woocommerce' ); ?>


<?php
printf(
	/* translators: %s: order number */
	esc_html__(
		'Your order #%s has been successfully confirmed and is now being processed.',
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


<?php esc_html_e( 'Thank you for shopping with us.', 'cod-verify-for-woocommerce' ); ?>