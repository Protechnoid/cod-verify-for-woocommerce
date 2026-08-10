<?php
/**
 * Customer Order Confirmed Email.
 *
 * @var WC_Order $order
 * @var string   $email_heading
 * @var WC_Email $email
 */

defined( 'ABSPATH' ) || exit;
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p>
	<?php
	printf(
		/* translators: %s: customer first name */
		esc_html__( 'Hello %s,', 'cod-verify-for-woocommerce' ),
		esc_html( $order->get_billing_first_name() )
	);
	?>
</p>

<p>
	<?php esc_html_e( 'Thank you for confirming your Cash on Delivery order.', 'cod-verify-for-woocommerce' ); ?>
</p>

<p>
	<?php
	printf(
		/* translators: %s: order number */
		esc_html__( 'Your order #%s has been successfully confirmed and is now being processed.', 'cod-verify-for-woocommerce' ),
		esc_html( $order->get_order_number() )
	);
	?>
</p>

<?php
do_action( 'woocommerce_email_order_details', $order, false, false, $email );
do_action( 'woocommerce_email_order_meta', $order, false, false, $email );
do_action( 'woocommerce_email_customer_details', $order, false, false, $email );
?>

<p>
	<?php esc_html_e( 'Thank you for shopping with us.', 'cod-verify-for-woocommerce' ); ?>
</p>

<?php do_action( 'woocommerce_email_footer', $email ); ?>