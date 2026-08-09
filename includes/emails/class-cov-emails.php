<?php
/**
 * Email module.
 *
 * @package COD_Verify_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class COV_Emails {

	/**
	 * Register plugin emails with WooCommerce.
	 *
	 * @param array $emails WooCommerce email classes.
	 * @return array
	 */
	public function register_email_classes( $emails ) {

		require_once COV_PLUGIN_PATH . 'includes/emails/class-cov-email-confirmation.php';
		require_once COV_PLUGIN_PATH . 'includes/emails/class-cov-email-customer-order-confirmed.php';
		require_once COV_PLUGIN_PATH . 'includes/emails/class-cov-email-merchant-order-confirmed.php';

		$emails['COV_Email_Confirmation'] = new COV_Email_Confirmation();
		$emails['COV_Email_Customer_Order_Confirmed'] = new COV_Email_Customer_Order_Confirmed();
		$emails['COV_Email_Merchant_Order_Confirmed'] = new COV_Email_Merchant_Order_Confirmed();

		return $emails;
	}

	/**
	 * Trigger the customer confirmation email.
	 *
	 * @param WC_Order $order WooCommerce order object.
	 *
	 * @return void
	 */
	public function trigger_customer_confirmation_email( WC_Order $order ): void {

		$mailer = WC()->mailer();

		$emails = $mailer->get_emails();

		$customer_confirmation_email  = $emails['COV_Email_Confirmation'] ?? null;

		if ( $customer_confirmation_email instanceof COV_Email_Confirmation ) {
			$customer_confirmation_email->trigger( $order );
		}
	}

	/**
	 * Trigger order confirmed emails.
	 *
	 * @param WC_Order $order WooCommerce order object.
	 *
	 * @return void
	 */
	public function trigger_order_confirmed_emails( WC_Order $order ): void {

		$mailer = WC()->mailer();

		$emails = $mailer->get_emails();

		$customer_email = $emails['COV_Email_Customer_Order_Confirmed'] ?? null;

		if ( $customer_email instanceof COV_Email_Customer_Order_Confirmed ) {
			$customer_email->trigger( $order );
		}

		$merchant_email = $emails['COV_Email_Merchant_Order_Confirmed'] ?? null;

		if ( $merchant_email instanceof COV_Email_Merchant_Order_Confirmed ) {
			$merchant_email->trigger( $order );
		}
	}
}