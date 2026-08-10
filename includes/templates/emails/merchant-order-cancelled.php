<?php
/**
 * Merchant Order Cancelled Email.
 *
 * @var WC_Order $order
 * @var string   $email_heading
 * @var WC_Email $email
 */

defined( 'ABSPATH' ) || exit;
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p>
	<?php esc_html_e( 'A Cash on Delivery order has been automatically cancelled because the customer failed to complete verification.', 'cod-verify-for-woocommerce' ); ?>
</p>

<p>
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
</p>

<p>
	<?php esc_html_e( 'No further processing or shipment is required for this order.', 'cod-verify-for-woocommerce' ); ?>
</p>

<?php
do_action( 'woocommerce_email_order_details', $order, false, false, $email );
do_action( 'woocommerce_email_order_meta', $order, false, false, $email );
do_action( 'woocommerce_email_customer_details', $order, false, false, $email );
?>

<?php do_action( 'woocommerce_email_footer', $email ); ?>