<?php
/**
 * Token manager.
 *
 * @package COD_Verify_For_WooCommerce
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles verification token operations.
 */
class COV_Token_Manager {

	/**
	 * Generate a secure verification token.
	 *
	 * @return string
	 */
	public function generate_token(): string {

		return bin2hex( random_bytes( 32 ) );
	}

	/**
	 * Stores the verification token for an order.
	 *
	 * @param WC_Order $order WooCommerce order object.
	 * @param string   $token Verification token.
	 *
	 * @return void
	 */
	public function store_token( WC_Order $order, string $token ): void {

		$order->update_meta_data(
			COV_Helper::META_TOKEN,
			$token
		);

		$order->save();
	}

	/**
	 * Retrieves the verification token for an order.
	 *
	 * @param WC_Order $order WooCommerce order object.
	 *
	 * @return string
	 */
	public function get_token( WC_Order $order ): string {

		return (string) $order->get_meta( COV_Helper::META_TOKEN );
	}

	/**
	 * Stores the token expiration time for an order.
	 *
	 * @param WC_Order $order     WooCommerce order object.
	 * @param int      $expires_at Expiration timestamp.
	 *
	 * @return void
	 */
	public function store_token_expiration( WC_Order $order, int $expires_at ): void {

		$order->update_meta_data(
			COV_Helper::META_TOKEN_EXPIRES,
			$expires_at
		);

		$order->save();
	}

	/**
	 * Retrieves the token expiration time for an order.
	 *
	 * @param WC_Order $order WooCommerce order object.
	 *
	 * @return int
	 */
	public function get_token_expiration( WC_Order $order ): int {

		return (int) $order->get_meta( COV_Helper::META_TOKEN_EXPIRES );
	}

	/**
	 * Checks whether the token has expired.
	 *
	 * @param WC_Order $order WooCommerce order object.
	 *
	 * @return bool
	 */
	public function is_token_expired( WC_Order $order ): bool {

		return current_time( 'timestamp', true ) >= $this->get_token_expiration( $order );
	}

	/**
	 * Marks the token as used.
	 *
	 * @param WC_Order $order WooCommerce order object.
	 *
	 * @return void
	 */
	public function mark_token_used( WC_Order $order ): void {

		$order->update_meta_data(
			COV_Helper::META_TOKEN_USED,
			1
		);

		$order->save();
	}

	/**
	 * Resets the token-used flag for an order.
	 *
	 * Called whenever a fresh token is issued (checkout, resend), so a
	 * newly issued token is never blocked by a stale "already used"
	 * flag left over from a prior confirmation on the same order - e.g.
	 * an order manually moved back into Pending Confirmation after
	 * already being confirmed once (via link or admin override), then
	 * resent. Without this, the customer's new link would incorrectly
	 * show "This order has already been confirmed" instead of actually
	 * working.
	 *
	 * @param WC_Order $order WooCommerce order object.
	 *
	 * @return void
	 */
	public function reset_token_used( WC_Order $order ): void {

		$order->update_meta_data(
			COV_Helper::META_TOKEN_USED,
			0
		);

		$order->save();
	}

	/**
	 * Checks whether the token has already been used.
	 *
	 * @param WC_Order $order WooCommerce order object.
	 *
	 * @return bool
	 */
	public function is_token_used( WC_Order $order ): bool {

		return (bool) $order->get_meta( COV_Helper::META_TOKEN_USED );
	}

	/**
	 * Invalidates the verification token for an order.
	 *
	 * Called when an order leaves Pending Confirmation via a status
	 * change (merchant action, another plugin, the REST API, etc.) so
	 * that any previously issued verification link stops working -
	 * even if the order is later moved back into Pending Confirmation.
	 *
	 * This clears the token and its expiration only. It deliberately
	 * does NOT touch META_TOKEN_USED, since that flag means the customer
	 * actually confirmed the order - it must stay truthful and is never
	 * set as a side effect of invalidation.
	 *
	 * @param WC_Order $order WooCommerce order object.
	 *
	 * @return void
	 */
	public function invalidate_token( WC_Order $order ): void {

		$order->update_meta_data(
			COV_Helper::META_TOKEN,
			''
		);

		$order->update_meta_data(
			COV_Helper::META_TOKEN_EXPIRES,
			0
		);

		$order->save();
	}
}