<?php
/**
 * Confirmation Handler
 *
 * Handles COD order confirmation requests.
 *
 * @package COD_Verify_For_WooCommerce
 */

defined( 'ABSPATH' ) || exit;

/**
 * Confirmation Handler class.
 */
class COV_Confirmation_Handler {

	/**
	 * Token Manager instance.
	 *
	 * @var COV_Token_Manager
	 */
	private COV_Token_Manager $token_manager;

	/**
	 * Order Auto Cancel instance.
	 *
	 * Used to temporarily detach the generic
	 * woocommerce_order_status_changed exit-listener around this
	 * class's own Pending Confirmation -> Processing transition, so a
	 * genuine customer confirmation never has its token metadata wiped
	 * by the listener meant for external/manual status changes.
	 *
	 * @var COV_Order_Auto_Cancel
	 */
	private COV_Order_Auto_Cancel $order_auto_cancel;

	/**
	 * Constructor.
	 *
	 * @param COV_Token_Manager    $token_manager     Token manager instance.
	 * @param COV_Order_Auto_Cancel $order_auto_cancel Order auto cancel instance.
	 */
	public function __construct(
		COV_Token_Manager $token_manager,
		COV_Order_Auto_Cancel $order_auto_cancel
	) {

		$this->token_manager     = $token_manager;
		$this->order_auto_cancel = $order_auto_cancel;
	}

	/**
	 * Handle confirmation request.
	 *
	 * @return void
	 */
	public function handle_confirmation_request(): void {

		if ( ! COV_Helper::is_plugin_enabled() ) {
			return;
		}

		// Only handle frontend requests.
		if ( is_admin() ) {
			return;
		}

		// Only process verification URLs.
		if ( ! isset( $_GET['cov_order_id'], $_GET['cov_token'] ) ) {
			return;
		}

		$order_id = absint( $_GET['cov_order_id'] );

		$token = sanitize_text_field(
			wp_unslash( $_GET['cov_token'] )
		);

		$order = wc_get_order( $order_id );

		// Ensure the order exists.
		if ( ! $order ) {
			$this->render_template( 'invalid_order' );
		}

		// Ensure the order is still waiting for confirmation. Checked
		// first, ahead of token mechanics: this is the authoritative
		// signal for whether verification still applies at all, and
		// covers a status change invalidating the token (see
		// COV_Order_Auto_Cancel::handle_pending_confirmation_exit())
		// with an accurate, status-aware message rather than a generic
		// "invalid token" result.
		if ( COV_Helper::ORDER_STATUS_PENDING_CONFIRM !== $order->get_status() ) {
			$this->log_stale_link_note( $order );
			$this->render_template( 'invalid_status', $order );
		}

		// Validate the verification token.
		$stored_token = $this->token_manager->get_token( $order );

		if ( ! hash_equals( $stored_token, $token ) ) {
			$this->render_template( 'invalid_token', $order );
		}

		// Prevent the same verification link from being used twice.
		if ( $this->token_manager->is_token_used( $order ) ) {
			$this->render_template( 'already_confirmed', $order );
		}

		// Ensure the verification link has not expired.
		if ( $this->token_manager->is_token_expired( $order ) ) {
			$this->render_template( 'expired', $order );
		}

		// Mark the verification token as used.
		$this->token_manager->mark_token_used( $order );

		// Store the confirmation timestamp.
		$order->update_meta_data(
			COV_Helper::META_CONFIRMED_AT,
			current_time( 'timestamp', true )
		);

		// Add an internal order note.
		$order->add_order_note(
			__(
				'Customer confirmed the COD order via the verification link.',
				'cod-verify-for-woocommerce'
			)
		);

		// Move the order to Processing.
		//
		// This transition is the customer's own successful verification,
		// not an external/manual status change - so the generic exit
		// listener (which invalidates the token for external changes)
		// is temporarily detached around this specific call. This keeps
		// _cov_token and _cov_token_expires intact as a record of what
		// was actually used to confirm, while _cov_token_used remains
		// the sole, truthful signal that the customer confirmed.
		remove_action(
			'woocommerce_order_status_changed',
			array( $this->order_auto_cancel, 'handle_pending_confirmation_exit' ),
			10
		);

		try {

			$order->update_status(
				'processing',
				__(
					'Order confirmed by customer via verification link.',
					'cod-verify-for-woocommerce'
				)
			);

		} finally {

			add_action(
				'woocommerce_order_status_changed',
				array( $this->order_auto_cancel, 'handle_pending_confirmation_exit' ),
				10,
				4
			);
		}

		// Clean up the now-stale scheduled auto-cancel job. This is
		// always safe and always correct to do here - the order is
		// confirmed, so it should never be auto-cancelled. This is
		// deliberately separate from token invalidation: the listener
		// above was detached specifically to preserve the token as a
		// record of what confirmed the order, but the scheduled job
		// itself has no such reason to be kept around.
		$this->order_auto_cancel->unschedule_auto_cancel( $order->get_id() );

		// Notify other plugin components that the order has been confirmed.
		do_action(
			'cov_order_confirmed',
			$order
		);

		$this->render_template(
			'success',
			$order
		);
	}

	/**
	 * Log an order note when a customer clicks a verification link
	 * for an order that is no longer in Pending Confirmation.
	 *
	 * Gives the merchant an audit trail if a customer reports a
	 * "broken" or "expired" link after the order's status changed.
	 *
	 * @param WC_Order $order Order object.
	 *
	 * @return void
	 */
	private function log_stale_link_note( WC_Order $order ): void {

		$order->add_order_note(
			sprintf(
				/* translators: %s: current order status label. */
				__(
					'Customer clicked the verification link, but the order status had already changed to "%s". No action was taken.',
					'cod-verify-for-woocommerce'
				),
				wc_get_order_status_name( $order->get_status() )
			)
		);
	}

	/**
	 * Build a status-aware message/icon pair for a stale verification
	 * link, based on what the order's current status actually is.
	 *
	 * @param WC_Order $order Order object.
	 *
	 * @return array{message: string, icon: string}
	 */
	private function get_status_aware_result( WC_Order $order ): array {

		switch ( $order->get_status() ) {

			case 'cancelled':
				return array(
					'message' => __( 'This order has been cancelled and no longer needs confirmation.', 'cod-verify-for-woocommerce' ),
					'icon'    => 'info',
				);

			case 'processing':
			case 'completed':
				return array(
					'message' => __( 'Good news — this order is already being processed. No action needed.', 'cod-verify-for-woocommerce' ),
					'icon'    => 'success',
				);

			default:
				return array(
					'message' => __( 'This link is no longer valid for this order.', 'cod-verify-for-woocommerce' ),
					'icon'    => 'error',
				);
		}
	}

	/**
	 * Render confirmation status template.
	 *
	 * @param string       $status Confirmation status.
	 * @param WC_Order|null $order  Order object.
	 *
	 * @return void
	 */
	private function render_template(
		string $status,
		?WC_Order $order = null
	): void {

		$page_title = '';
		$message    = '';
		$icon       = '';

		switch ( $status ) {

			case 'success':
				$page_title = __( 'Order Confirmed', 'cod-verify-for-woocommerce' );
				$message    = __( 'Thank you! Your Cash on Delivery order has been confirmed successfully.', 'cod-verify-for-woocommerce' );
				$icon       = 'success';
				break;

			case 'invalid_order':
				$page_title = __( 'Invalid Order', 'cod-verify-for-woocommerce' );
				$message    = __( 'The requested order could not be found.', 'cod-verify-for-woocommerce' );
				$icon       = 'error';
				break;

			case 'invalid_status':
				$page_title = __( 'Invalid Order Status', 'cod-verify-for-woocommerce' );
				$status_result = $this->get_status_aware_result( $order );
				$message    = $status_result['message'];
				$icon       = $status_result['icon'];
				break;

			case 'invalid_token':
				$page_title = __( 'Invalid Verification Link', 'cod-verify-for-woocommerce' );
				$message    = __( 'The verification link is invalid.', 'cod-verify-for-woocommerce' );
				$icon       = 'error';
				break;

			case 'already_confirmed':
				$page_title = __( 'Already Confirmed', 'cod-verify-for-woocommerce' );
				$message    = __( 'This order has already been confirmed.', 'cod-verify-for-woocommerce' );
				$icon       = 'info';
				break;

			case 'expired':
				$page_title = __( 'Verification Link Expired', 'cod-verify-for-woocommerce' );
				$message    = __( 'This verification link has expired.', 'cod-verify-for-woocommerce' );
				$icon       = 'error';
				break;
		}

		nocache_headers();

		// Rank Math and Yoast render their own robots meta tag independently
		// of WordPress core, so we redirect their output to noindex/nofollow
		// instead of adding a second, competing meta tag alongside theirs.
		add_filter( 'rank_math/frontend/robots', array( $this, 'force_noindex_robots_array' ), 999 );
		add_filter( 'wpseo_robots_array', array( $this, 'force_noindex_robots_array' ), 999 );

		// Only output our own tag when no SEO plugin is present to render one
		// via the filters above.
		if ( ! defined( 'RANK_MATH_VERSION' ) && ! defined( 'WPSEO_VERSION' ) ) {
			add_action( 'wp_head', array( $this, 'output_noindex_meta' ), 1 );
		}

		require COV_PLUGIN_PATH . 'templates/confirmation-status.php';

		exit;
	}

	/**
	 * Force a robots directives array to noindex/nofollow.
	 *
	 * Used with Rank Math's `rank_math/frontend/robots` filter and Yoast's
	 * `wpseo_robots_array` filter, both of which expect an associative
	 * array such as array( 'index' => 'index', 'follow' => 'follow', ... ).
	 *
	 * @param mixed $robots Existing robots directives array from the SEO plugin.
	 *
	 * @return array Modified robots directives array.
	 */
	public function force_noindex_robots_array( $robots ): array {

		if ( ! is_array( $robots ) ) {
			$robots = array();
		}

		$robots['index']  = 'noindex';
		$robots['follow'] = 'nofollow';

		return $robots;
	}

	/**
	 * Output a noindex/nofollow meta tag on the verification status page.
	 *
	 * Fallback for sites with no SEO plugin active. Prevents the
	 * confirm/cancel URL (home_url('/') plus query args) from being
	 * indexed, and signals crawlers/caches not to treat it as cacheable
	 * content.
	 *
	 * @return void
	 */
	public function output_noindex_meta(): void {

		echo '<meta name="robots" content="noindex, nofollow" />' . "\n";
	}
}