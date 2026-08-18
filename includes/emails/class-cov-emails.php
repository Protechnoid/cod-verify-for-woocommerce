<?php
/**
 * Email module.
 *
 * @package COD_Verify_For_WooCommerce
 */

defined( 'ABSPATH' ) || exit;

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
		require_once COV_PLUGIN_PATH . 'includes/emails/class-cov-email-merchant-awaiting-confirmation.php';
		require_once COV_PLUGIN_PATH . 'includes/emails/class-cov-email-customer-order-cancelled.php';
		require_once COV_PLUGIN_PATH . 'includes/emails/class-cov-email-merchant-order-cancelled.php';

		$emails['COV_Email_Confirmation'] = new COV_Email_Confirmation();
		$emails['COV_Email_Customer_Order_Confirmed'] = new COV_Email_Customer_Order_Confirmed();
		$emails['COV_Email_Merchant_Order_Confirmed'] = new COV_Email_Merchant_Order_Confirmed();
		$emails['COV_Email_Merchant_Awaiting_Confirmation'] = new COV_Email_Merchant_Awaiting_Confirmation();
		$emails['COV_Email_Customer_Order_Cancelled'] = new COV_Email_Customer_Order_Cancelled();
		$emails['COV_Email_Merchant_Order_Cancelled'] = new COV_Email_Merchant_Order_Cancelled();

		return $emails;
	}

	/**
	 * Trigger the customer confirmation email.
	 *
	 * Always fires unconditionally - this is the mandatory email
	 * carrying the verification link, with no plugin setting to
	 * disable it. See COV_Email_Confirmation::is_enabled(), which is
	 * also hardcoded to true so WooCommerce's native per-email toggle
	 * can't disable it either.
	 *
	 * @param WC_Order $order WooCommerce order object.
	 *
	 * @return void
	 */
	public function trigger_customer_confirmation_email( WC_Order $order ): void {

		$mailer = WC()->mailer();

		$emails = $mailer->get_emails();

		$customer_confirmation_email = $emails['COV_Email_Confirmation'] ?? null;

		if ( $customer_confirmation_email instanceof COV_Email_Confirmation ) {
			$customer_confirmation_email->trigger( $order );
		}
	}

	/**
	 * Trigger the merchant awaiting confirmation email.
	 *
	 * @param WC_Order $order WooCommerce order object.
	 *
	 * @return void
	 */
	public function trigger_merchant_awaiting_confirmation_email( WC_Order $order ): void {

		$settings = COV_Helper::get_general_settings();

		if ( empty( $settings['notify_merchant'] ) ) {
			return;
		}

		$mailer = WC()->mailer();

		$emails = $mailer->get_emails();

		$merchant_awaiting_confirmation_email = $emails['COV_Email_Merchant_Awaiting_Confirmation'] ?? null;

		if ( $merchant_awaiting_confirmation_email instanceof COV_Email_Merchant_Awaiting_Confirmation ) {
			$merchant_awaiting_confirmation_email->trigger( $order );
		}
	}

	/**
	 * Trigger order confirmed emails.
	 *
	 * Customer and merchant recipients are gated independently, so a
	 * merchant can keep their own confirmation email on while turning
	 * off the customer's, or vice versa.
	 *
	 * @param WC_Order $order WooCommerce order object.
	 *
	 * @return void
	 */
	public function trigger_order_confirmed_emails( WC_Order $order ): void {

		$settings = COV_Helper::get_general_settings();

		$mailer = WC()->mailer();

		$emails = $mailer->get_emails();

		if ( ! empty( $settings['customer_order_confirmed'] ) ) {

			$customer_email = $emails['COV_Email_Customer_Order_Confirmed'] ?? null;

			if ( $customer_email instanceof COV_Email_Customer_Order_Confirmed ) {
				$customer_email->trigger( $order );
			}
		}

		if ( ! empty( $settings['merchant_order_confirmed'] ) ) {

			$merchant_email = $emails['COV_Email_Merchant_Order_Confirmed'] ?? null;

			if ( $merchant_email instanceof COV_Email_Merchant_Order_Confirmed ) {
				$merchant_email->trigger( $order );
			}
		}
	}

	/**
	 * Trigger order cancelled emails.
	 *
	 * Customer and merchant recipients are gated independently, same
	 * reasoning as trigger_order_confirmed_emails().
	 *
	 * @param WC_Order $order WooCommerce order object.
	 *
	 * @return void
	 */
	public function trigger_order_cancelled_emails( WC_Order $order ): void {

		$settings = COV_Helper::get_general_settings();

		$mailer = WC()->mailer();

		$emails = $mailer->get_emails();

		if ( ! empty( $settings['customer_order_cancelled'] ) ) {

			$customer_email = $emails['COV_Email_Customer_Order_Cancelled'] ?? null;

			if ( $customer_email instanceof COV_Email_Customer_Order_Cancelled ) {
				$customer_email->trigger( $order );
			}
		}

		if ( ! empty( $settings['merchant_order_cancelled'] ) ) {

			$merchant_email = $emails['COV_Email_Merchant_Order_Cancelled'] ?? null;

			if ( $merchant_email instanceof COV_Email_Merchant_Order_Cancelled ) {
				$merchant_email->trigger( $order );
			}
		}
	}
}