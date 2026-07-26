<?php
/**
 * Placeholder Manager
 *
 * Handles placeholder replacement for plugin notifications.
 *
 * @package COD_Verify_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'COV_Placeholder_Manager' ) ) {

	/**
	 * Placeholder Manager.
	 */
	class COV_Placeholder_Manager {

		/**
		 * Get supported placeholders.
		 *
		 * @return array
		 */
		public static function get_supported_placeholders(): array {

			return array(
				'{customer_name}'    => __( 'Customer first name', 'cod-verify-for-woocommerce' ),
				'{store_name}'       => __( 'Store name', 'cod-verify-for-woocommerce' ),
				'{confirmation_url}' => __( 'Confirmation URL', 'cod-verify-for-woocommerce' ),
			);

		}

		/**
		 * Replace placeholders in a template.
		 *
		 * @param string   $template Template containing placeholders.
		 * @param WC_Order $order    WooCommerce order object.
		 *
		 * @return string
		 */
		public static function replace( string $template, WC_Order $order ): string {

			$placeholders = array(
				'{customer_name}'    => $order->get_billing_first_name(),
				'{store_name}'       => get_bloginfo( 'name' ),
				'{confirmation_url}' => COV_Link_Manager::get_confirmation_url( $order ),
			);

			return strtr( $template, $placeholders );
		}
	}
}