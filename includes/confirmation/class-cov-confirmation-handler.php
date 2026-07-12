<?php
/**
 * Confirmation Handler
 * 
 * Handles COD order confirmation requests.
 * 
 * @package COD_Verify_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Confirmation Handler class.
 */
class COV_Confirmation_Handler {

    /**
     * Token Manager instance.
     * 
     * @var COV_Token_Manager
     */
    private $token_manager;

    /**
     * Constructor.
     */
    public function __construct( COV_Token_Manager $token_manager ) {

        $this->token_manager = $token_manager;
    
    }

    /**
     * Handle confirmation request.
     */
    public function handle_confirmation_request() {

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

        if ( ! $order ) {
            $this->render_template( 'invalid_order' );
        }

        if ( COV_Helper::ORDER_STATUS_PENDING_CONFIRM !== $order->get_status() ) {
            $this->render_template( 'invalid_status', $order );
        }

        $stored_token = $this->token_manager->get_token( $order );

        if ( $token !== $stored_token ) {
            $this->render_template( 'invalid_token', $order );
        }

        if ( $this->token_manager->is_token_used( $order ) ) {
            $this->render_template( 'already_confirmed', $order );
        }

        if ( $this->token_manager->is_token_expired( $order ) ) {
            $this->render_template( 'expired', $order );
        }

        $this->token_manager->mark_token_used( $order );

        $order->update_meta_data(
            COV_Helper::META_CONFIRMED_AT,
            current_time( 'timestamp' )
        );

        $order->add_order_note(
            __( 'Customer confirmed the COD order via the verification link.', 'cod-verify-for-woocommerce' )
        );

        $order->update_status(
            'processing',
            __( 'Order confirmed by customer via verification link.', 'cod-verify-for-woocommerce' )
        );

        $this->render_template(
            'success',
            $order
        );
    }

    
    /**
     * Render confirmation status template.
     *
     * @param string         $status Confirmation status.
     * @param WC_Order|null  $order  Order object.
     */
    private function render_template( $status, $order = null ) {

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

        require COV_PLUGIN_PATH . 'templates/confirmation-status.php';

        exit;
    }

}