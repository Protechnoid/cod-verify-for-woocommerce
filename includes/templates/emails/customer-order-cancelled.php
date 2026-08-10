<?php
/**
 * Customer Order Cancelled Email.
 *
 * @var WC_Order $order
 * @var string   $email_heading
 * @var WC_Email $email
 */

defined( 'ABSPATH' ) || exit;
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p>
	<?php esc_html_e( 'Your Cash on Delivery order has been cancelled.', 'cod-verify-for-woocommerce' ); ?>
</p>

<p>
	<?php
	printf(
		/* translators: %s: order number */
		esc_html__(
			'Your Cash on Delivery order #%s could not be confirmed because the verification was not completed within the required time.',
			'cod-verify-for-woocommerce'
		),
		esc_html( $order->get_order_number() )
	);
	?>
</p>

<p>
	<?php esc_html_e( 'The order has therefore been cancelled. If you still wish to purchase the items, please place a new order.', 'cod-verify-for-woocommerce' ); ?>
</p>

<?php
do_action( 'woocommerce_email_order_details', $order, false, false, $email );
do_action( 'woocommerce_email_order_meta', $order, false, false, $email );
do_action( 'woocommerce_email_customer_details', $order, false, false, $email );
?>

<?php do_action( 'woocommerce_email_footer', $email ); ?>