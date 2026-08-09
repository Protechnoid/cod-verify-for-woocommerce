<?php
/**
 * Merchant Order Confirmed Email.
 *
 * @var WC_Order $order
 * @var string   $email_heading
 * @var WC_Email $email
 */

defined( 'ABSPATH' ) || exit;
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p>
	<?php esc_html_e( 'A new Cash on Delivery order has been successfully confirmed by the customer.', 'cod-verify-for-woocommerce' ); ?>
</p>

<p>
	<?php
	printf(
		/* translators: %s: order number */
		esc_html__( 'Order #%s has completed customer verification and is now being processed.', 'cod-verify-for-woocommerce' ),
		esc_html( $order->get_order_number() )
	);
	?>
</p>

<?php
do_action( 'woocommerce_email_order_details', $order, false, false, $email );
do_action( 'woocommerce_email_order_meta', $order, false, false, $email );
do_action( 'woocommerce_email_customer_details', $order, false, false, $email );
?>

<?php do_action( 'woocommerce_email_footer', $email ); ?>