<?php
/**
 * Helper class
 * 
 * Provides shared constants used throughout the plugin.
 * 
 * @package COD_Verify_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Shared plugin constants.
 */
class COV_Helper {

    /**
     * Pending confirmation order status.
     */

    const ORDER_STATUS_PENDING_CONFIRM = 'wc-pending-confirm';

    /**
	 * Order meta keys.
	 */
    const META_TOKEN = '_cov_token';
    const META_TOKEN_EXPIRES = '_cov_token_expires';
    const META_TOKEN_USED = '_cov_token_used';
    const META_CONFIRMED_AT = '_cov_confirmed_at';

    /**
	 * Cron hook names.
	 */
    const CRON_CANCEL_ORDER = 'cov_cancel_unconfirmed_order';
    const CRON_SEND_REMINDER = 'cov_send_reminder_email';

}