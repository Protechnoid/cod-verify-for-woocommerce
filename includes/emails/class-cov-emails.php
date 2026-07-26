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

		$emails['COV_Email_Confirmation'] = new COV_Email_Confirmation();

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

		$email = $emails['COV_Email_Confirmation'] ?? null;

		if ( $email instanceof COV_Email_Confirmation ) {
			$email->trigger( $order );
		}
	}
}