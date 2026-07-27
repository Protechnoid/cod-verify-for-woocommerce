<?php
/**
 * Plain Customer Confirmation Email
 *
 * @package COD_Verify_For_WooCommerce
 */

defined( 'ABSPATH' ) || exit;

/**
 * Available template variables.
 *
 * @var WC_Order               $order
 * @var COV_Email_Confirmation $email
 * @var string                 $email_heading
 * @var string                 $confirmation_url
 */

echo wp_strip_all_tags( $email_heading ) . "\n\n";

/* translators: %s: Customer first name. */
printf(
	esc_html__( 'Hi %s,', 'cod-verify-for-woocommerce' ) . "\n\n",
	$order->get_billing_first_name()
);

echo esc_html__(
	'Thank you for your order.',
	'cod-verify-for-woocommerce'
) . "\n\n";

echo esc_html__(
	'Before we can process your Cash on Delivery order, we need to verify it.',
	'cod-verify-for-woocommerce'
) . "\n\n";

echo esc_html__(
	'Please confirm your order using the link below:',
	'cod-verify-for-woocommerce'
) . "\n\n";

echo esc_url( $confirmation_url ) . "\n\n";

/* translators: %s: Verification timeout (e.g. "6 hours" or "30 minutes"). */
printf(
	esc_html__(
		'This verification link expires in %s.',
		'cod-verify-for-woocommerce'
	),
	esc_html( COV_Helper::get_token_lifetime_label() )
);

echo "\n\n";
echo "----------------------------------------\n";
echo esc_html__( 'Order Details', 'cod-verify-for-woocommerce' );
echo "\n";
echo "----------------------------------------\n\n";

do_action(
	'woocommerce_email_order_details',
	$order,
	false,
	true,
	$email
);

echo "\n";
echo "----------------------------------------\n";
echo esc_html__( 'Customer Details', 'cod-verify-for-woocommerce' );
echo "\n";
echo "----------------------------------------\n\n";

do_action(
	'woocommerce_email_customer_details',
	$order,
	false,
	true,
	$email
);

if ( $email->get_additional_content() ) {

	echo "\n";
	echo "----------------------------------------\n";
	echo esc_html__( 'Additional Information', 'cod-verify-for-woocommerce' );
	echo "\n";
	echo "----------------------------------------\n\n";

	echo wp_strip_all_tags( $email->get_additional_content() );
	echo "\n";
}