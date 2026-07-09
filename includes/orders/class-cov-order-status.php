<?php
/**
 * Order Status class
 * 
 * @package COD_Verify_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles custom WooCommerce order statuses.
 */
class COV_Order_Status {

    /**
     * Registers the Pending Confirmation order status.
     */
    public function register_order_status() {

        register_post_status(
            COV_Helper::ORDER_STATUS_PENDING_CONFIRM,
            array(
                'label'                     => __( 'Pending Confirmation', 'cod-verify-for-woocommerce' ),
                'public'                    => false,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count' => _n_noop(
                    'Pending Confirmation <span class="count">(%s)</span>',
                    'Pending Confirmation <span class="count">(%s)</span>',
                    'cod-verify-for-woocommerce'
                ),
            ) 
        );

    }

    /**
     * Adds the custom order status to WooCommerce.
     *
     * @param array $order_statuses Existing WooCommerce order statuses.
     * @return array Modified order statuses.
     */

    public function add_order_status( $order_statuses ) {

        $modified_order_statuses = array();

        foreach ( $order_statuses as $status_key => $status_label ) {
            
            $modified_order_statuses[ $status_key ] = $status_label;
            
            if ( 'wc-pending' === $status_key ) {
                $modified_order_statuses[ COV_Helper::ORDER_STATUS_PENDING_CONFIRM ] = __( 
                    'Pending Confirmation', 
                    'cod-verify-for-woocommerce' 
                );
            }
    
        }

        return $modified_order_statuses;
    }

}