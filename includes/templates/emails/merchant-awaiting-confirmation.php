<?php
/**
 * Merchant Awaiting Confirmation Email.
 *
 * @var WC_Order $order
 * @var string   $email_heading
 * @var WC_Email $email
 */

defined( 'ABSPATH' ) || exit;
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p>
	<?php esc_html_e( 'A new Cash on Delivery order is awaiting customer verification.', 'cod-verify-for-woocommerce' ); ?>
</p>

<p>
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
</p>

<p>
	<?php esc_html_e( 'Please do not process or ship this order until it has been verified by the customer.', 'cod-verify-for-woocommerce' ); ?>
</p>

<p>
	<?php esc_html_e( 'You can track the order status from the Orders page in WooCommerce.', 'cod-verify-for-woocommerce' ); ?>
</p>

<?php
do_action( 'woocommerce_email_order_details', $order, false, false, $email );
do_action( 'woocommerce_email_order_meta', $order, false, false, $email );
do_action( 'woocommerce_email_customer_details', $order, false, false, $email );
?>

<?php do_action( 'woocommerce_email_footer', $email ); ?>