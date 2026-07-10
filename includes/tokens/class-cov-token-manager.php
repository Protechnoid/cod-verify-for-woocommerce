<?php
/**
 * Token manager.
 * 
 * @package COD_Verify_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles verification token operations.
 */
class COV_Token_Manager {
    
    /**
     * Constructor.
     */
    public function __construct() {

    }

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

        return $order->get_meta( COV_Helper::META_TOKEN );
    
    }


    /**
     * Stores the token expiration time for an order.
     *
     * @param WC_Order $order WooCommerce order object.
     * @param int      $expires_at Expiration timestamp.
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

        return current_time( 'timestamp', true ) > $this->get_token_expiration( $order );

    }

    /**
     * Marks the token as used.
     *
     * @param WC_Order $order WooCommerce order object.
     */
    public function mark_token_used( WC_Order $order ): void {

        $order->update_meta_data(
            COV_Helper::META_TOKEN_USED,
            1
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


}