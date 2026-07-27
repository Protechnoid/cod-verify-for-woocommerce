<?php
/**
 * Customer Confirmation Email
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

/**
 * Email Header.
 */
do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<p>
	<?php
	printf(
		/* translators: %s: Customer first name. */
		esc_html__( 'Hi %s,', 'cod-verify-for-woocommerce' ),
		esc_html( $order->get_billing_first_name() )
	);
	?>
</p>

<p>
	<?php esc_html_e(
		'Thank you for your order.',
		'cod-verify-for-woocommerce'
	); ?>
</p>

<p>
	<?php esc_html_e(
		'Before we can process your Cash on Delivery order, we need to verify it.',
		'cod-verify-for-woocommerce'
	); ?>
</p>

<p>
	<?php esc_html_e(
		'Please click the button below to confirm your order.',
		'cod-verify-for-woocommerce'
	); ?>
</p>

<?php
$button_text = apply_filters(
	'cov_confirmation_button_text',
	__( 'Verify My Order', 'cod-verify-for-woocommerce' ),
	$order
);
?>

<p style="text-align: center; margin: 30px 0;">

	<a
		href="<?php echo esc_url( $confirmation_url ); ?>"
		style="
			background: #2271b1;
			color: #ffffff;
			text-decoration: none;
			padding: 14px 28px;
			border-radius: 4px;
			display: inline-block;
			font-weight: 600;
		"
	>
		<?php echo esc_html( $button_text ); ?>
	</a>

</p>

<p style="text-align: center;">

	<?php
		printf(
			/* translators: %s: Verification timeout (e.g. "6 hours" or "30 minutes"). */
			esc_html__(
				'This verification link expires in %s.',
				'cod-verify-for-woocommerce'
			),
			esc_html( COV_Helper::get_token_lifetime_label() )
		);
	?>

</p>

<?php
/**
 * Order Details.
 */
do_action(
	'woocommerce_email_order_details',
	$order,
	false,
	false,
	$email
);

/**
 * Customer Details.
 */
do_action(
	'woocommerce_email_customer_details',
	$order,
	false,
	false,
	$email
);

/**
 * Additional Content.
 */
if ( $email->get_additional_content() ) :
	?>

	<p>
		<?php echo wp_kses_post( wpautop( wptexturize( $email->get_additional_content() ) ) ); ?>
	</p>

	<?php
endif;

/**
 * Email Footer.
 */
do_action( 'woocommerce_email_footer', $email );