<?php
/**
 * Link Manager.
 *
 * @package COD_Verify_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'COV_Link_Manager' ) ) {

	/**
	 * Handles plugin links.
	 */
	class COV_Link_Manager {

        /**
         * Get the confirmation URL for an order.
         *
         * @param WC_Order $order WooCommerce order object.
         *
         * @return string
         */
        public static function get_confirmation_url( WC_Order $order ): string {

            $token_manager = new COV_Token_Manager();

            $token = $token_manager->get_token( $order );

            if ( empty( $token ) ) {
                return '';
            }

            return add_query_arg(
                array(
                    'cov_order_id' => $order->get_id(),
                    'cov_token'    => $token,
                ),
                home_url( '/' )
            );
        }

	}
}