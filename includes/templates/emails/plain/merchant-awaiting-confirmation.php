<?php
/**
 * Merchant Awaiting Confirmation Email - Plain Text.
 *
 * @var WC_Order $order
 * @var string   $email_heading
 * @var WC_Email $email
 */

defined( 'ABSPATH' ) || exit;
?>

<?php echo esc_html( $email_heading ); ?>


<?php esc_html_e( 'A new Cash on Delivery order is awaiting customer verification.', 'cod-verify-for-woocommerce' ); ?>


<?php
printf(
	/* translators: %s: order number */
	esc_html__(
		'Order #%s is currently awaiting customer verification.',
		'cod-verify-for-woocommerce'
	),
	esc_html( $order->get_order_number() )
);
?>


<?php esc_html_e( 'Please do not process or ship this order until it has been verified by the customer.', 'cod-verify-for-woocommerce' ); ?>


<?php esc_html_e( 'You can track the order status from the Orders page in WooCommerce.', 'cod-verify-for-woocommerce' ); ?>


<?php
do_action( 'woocommerce_email_order_details', $order, false, true, $email );
do_action( 'woocommerce_email_order_meta', $order, false, true, $email );
do_action( 'woocommerce_email_customer_details', $order, false, true, $email );
?>