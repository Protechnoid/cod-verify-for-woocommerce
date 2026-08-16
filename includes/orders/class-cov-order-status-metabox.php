<?php
/**
 * Order Status Metabox.
 *
 * @package COD_Verify_For_WooCommerce
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders a read-only COD verification status meta box on the order
 * edit screen sidebar.
 *
 * Purely informational - does not duplicate the Resend / Confirm
 * actions already available in the native Order Actions box, to
 * avoid two separate controls that could drift out of sync.
 */
class COV_Order_Status_Metabox {

	/**
	 * Token manager instance.
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
	 * Register the meta box.
	 *
	 * Uses WooCommerce's own screen-detection so this works correctly
	 * whether HPOS (custom order tables) is enabled or the legacy
	 * post-based orders screen is in use.
	 *
	 * @return void
	 */
	public function register_metabox(): void {

		$screen = ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' )
			&& \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() )
			? wc_get_page_screen_id( 'shop-order' )
			: 'shop_order';

		add_meta_box(
			'cov-verification-status',
			__( 'COD Verification', 'cod-verify-for-woocommerce' ),
			array( $this, 'render_metabox' ),
			$screen,
			'side',
			'high'
		);
	}

	/**
	 * Render the meta box.
	 *
	 * WooCommerce passes either a WP_Post (legacy) or a WC_Order
	 * (HPOS) depending on storage mode - normalize to WC_Order either
	 * way.
	 *
	 * @param WP_Post|WC_Order $post_or_order Post or order object.
	 *
	 * @return void
	 */
	public function render_metabox( $post_or_order ): void {

		$order = ( $post_or_order instanceof WP_Post )
			? wc_get_order( $post_or_order->ID )
			: $post_or_order;

		if ( ! $order instanceof WC_Order ) {
			return;
		}

		if ( 'cod' !== $order->get_payment_method() ) {
			echo '<p>' . esc_html__( 'Not a Cash on Delivery order.', 'cod-verify-for-woocommerce' ) . '</p>';
			return;
		}

		if ( $this->token_manager->is_token_used( $order ) ) {
			$this->render_confirmed_state( $order );
			return;
		}

		if ( COV_Helper::ORDER_STATUS_PENDING_CONFIRM === $order->get_status() ) {
			$this->render_pending_state( $order );
			return;
		}

		$this->render_not_confirmed_state( $order );
	}

	/**
	 * Render the "confirmed" state.
	 *
	 * @param WC_Order $order Order object.
	 *
	 * @return void
	 */
	private function render_confirmed_state( WC_Order $order ): void {

		$confirmed_via = $order->get_meta( COV_Helper::META_CONFIRMED_VIA );
		$confirmed_at  = (int) $order->get_meta( COV_Helper::META_CONFIRMED_AT );

		$via_label = ( 'admin' === $confirmed_via )
			? __( 'Confirmed by admin (verified outside the plugin)', 'cod-verify-for-woocommerce' )
			: __( 'Confirmed by customer via link', 'cod-verify-for-woocommerce' );

		echo '<p style="margin-top:0;"><strong style="color:#2a8a43;">&#10003; ' . esc_html__( 'Confirmed', 'cod-verify-for-woocommerce' ) . '</strong></p>';
		echo '<p>' . esc_html( $via_label ) . '</p>';

		if ( $confirmed_at ) {

			echo '<p>' . esc_html__( 'Confirmed at:', 'cod-verify-for-woocommerce' ) . '<br>'
				. esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $confirmed_at ) )
				. '</p>';
		}
	}

	/**
	 * Render the "awaiting verification" state.
	 *
	 * @param WC_Order $order Order object.
	 *
	 * @return void
	 */
	private function render_pending_state( WC_Order $order ): void {

		$expires_at = (int) $order->get_meta( COV_Helper::META_TOKEN_EXPIRES );

		echo '<p style="margin-top:0;"><strong style="color:#b45f06;">&#8987; ' . esc_html__( 'Awaiting customer verification', 'cod-verify-for-woocommerce' ) . '</strong></p>';

		if ( $expires_at ) {

			$now = current_time( 'timestamp', true );

			if ( $expires_at > $now ) {

				echo '<p>' . esc_html__( 'Link expires:', 'cod-verify-for-woocommerce' ) . '<br>'
					. esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $expires_at ) )
					. '<br>'
					. esc_html(
						sprintf(
							/* translators: %s: human-readable time difference, e.g. "43 minutes". */
							__( '(in %s)', 'cod-verify-for-woocommerce' ),
							human_time_diff( $now, $expires_at )
						)
					)
					. '</p>';

			} else {

				echo '<p>' . esc_html__( 'Verification link has expired. Awaiting auto-cancel, or use Order actions to resend or confirm manually.', 'cod-verify-for-woocommerce' ) . '</p>';
			}
		}

		echo '<p style="color:#666;">' . esc_html__( 'Use the Order actions box to resend the link or confirm on the customer\'s behalf.', 'cod-verify-for-woocommerce' ) . '</p>';
	}

	/**
	 * Render the "not confirmed via verification" state.
	 *
	 * Distinguishes the plugin's own auto-cancel timeout (a specific,
	 * known outcome) from a genuinely external/manual status change
	 * that bypassed verification (cancelled some other way, or moved
	 * straight to another status like Processing without the customer
	 * ever confirming).
	 *
	 * @param WC_Order $order Order object.
	 *
	 * @return void
	 */
	private function render_not_confirmed_state( WC_Order $order ): void {

		$cancelled_via = $order->get_meta( COV_Helper::META_CANCELLED_VIA );

		if ( 'auto_cancel' === $cancelled_via ) {

			echo '<p style="margin-top:0;"><strong style="color:#999;">&#10007; ' . esc_html__( 'Auto-cancelled (verification timeout)', 'cod-verify-for-woocommerce' ) . '</strong></p>';
			echo '<p style="color:#666;">' . esc_html__( 'The customer did not verify this order before the deadline, so it was automatically cancelled.', 'cod-verify-for-woocommerce' ) . '</p>';
			return;
		}

		echo '<p style="margin-top:0;"><strong>' . esc_html__( 'Not confirmed via verification', 'cod-verify-for-woocommerce' ) . '</strong></p>';
		echo '<p style="color:#666;">' . esc_html__( 'This order left Pending Confirmation without being verified — status changed externally, or verification was never completed.', 'cod-verify-for-woocommerce' ) . '</p>';
	}
}