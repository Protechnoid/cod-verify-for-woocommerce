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
	 * Constructor.
	 *
	 * @param COV_Token_Manager $token_manager Token manager instance.
	 */
	public function __construct( COV_Token_Manager $token_manager ) {

		$this->token_manager = $token_manager;
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

		// Ensure the order is still waiting for confirmation.
		if ( COV_Helper::ORDER_STATUS_PENDING_CONFIRM !== $order->get_status() ) {
			$this->render_template( 'invalid_status', $order );
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
		$order->update_status(
			'processing',
			__(
				'Order confirmed by customer via verification link.',
				'cod-verify-for-woocommerce'
			)
		);

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
				$message    = __( 'This order can no longer be confirmed.', 'cod-verify-for-woocommerce' );
				$icon       = 'error';
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