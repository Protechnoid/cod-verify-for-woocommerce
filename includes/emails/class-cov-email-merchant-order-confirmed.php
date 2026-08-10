<?php
/**
 * Merchant Order Confirmed Email.
 *
 * @package COD_Verify_For_WooCommerce
 */

defined( 'ABSPATH' ) || exit;

/**
 * Merchant order confirmed email.
 */
class COV_Email_Merchant_Order_Confirmed extends WC_Email {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id          = 'cov_merchant_order_confirmed';
		$this->title       = __( 'Merchant Order Confirmed', 'cod-verify-for-woocommerce' );
		$this->description = __(
			'Email sent to the store owner after a customer successfully confirms a COD order.',
			'cod-verify-for-woocommerce'
		);

		$this->customer_email = false;

		$this->template_html  = 'emails/merchant-order-confirmed.php';
		$this->template_plain = 'emails/plain/merchant-order-confirmed.php';
		$this->template_base  = COV_PLUGIN_PATH . 'includes/templates/';

		$this->subject = __(
			'New COD Order Confirmed — Customer Verification Completed',
			'cod-verify-for-woocommerce'
		);

		$this->heading = __(
			'New COD Order Confirmed',
			'cod-verify-for-woocommerce'
		);

		parent::__construct();
	}

	/**
	 * Trigger the email.
	 *
	 * @param int|WC_Order $order Order object or order ID.
	 *
	 * @return void
	 */
	public function trigger( $order ): void {

		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		if ( ! $order instanceof WC_Order ) {
			return;
		}

		$this->object    = $order;

        $mailer = WC()->mailer();

        $emails = $mailer->get_emails();

        $new_order_email = $emails['WC_Email_New_Order'] ?? null;

        if ( $new_order_email instanceof WC_Email_New_Order ) {
            $this->recipient = $new_order_email->get_recipient();
        } else {
            $this->recipient = get_option( 'admin_email' );
        }

		$this->placeholders = array(
			'{customer_name}' => $order->get_billing_first_name(),
			'{store_name}'    => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
			'{order_number}'  => $order->get_order_number(),
		);

		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return;
		}

		$this->send(
			$this->get_recipient(),
			$this->get_subject(),
			$this->get_content(),
			$this->get_headers(),
			$this->get_attachments()
		);
	}

	/**
	 * Get template arguments.
	 *
	 * @return array
	 */
	protected function get_template_args(): array {

		return array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'email'         => $this,
		);
	}

	/**
	 * Get HTML content.
	 *
	 * @return string
	 */
	public function get_content_html(): string {

		return wc_get_template_html(
			$this->template_html,
			$this->get_template_args(),
			'',
			$this->template_base
		);
	}

	/**
	 * Get plain text content.
	 *
	 * @return string
	 */
	public function get_content_plain(): string {

		return wc_get_template_html(
			$this->template_plain,
			$this->get_template_args(),
			'',
			$this->template_base
		);
	}
}