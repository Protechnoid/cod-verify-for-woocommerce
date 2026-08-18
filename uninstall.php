<?php
/**
 * Uninstall handler.
 *
 * Runs when the plugin is deleted from the Plugins screen (after
 * being deactivated). Not loaded through the plugin's normal
 * bootstrap, so nothing here can rely on COV_Helper or any other
 * plugin class being available - constants and hook/option names are
 * intentionally hardcoded to match includes/helper/class-cov-helper.php.
 * Keep them in sync if those values ever change there.
 *
 * @package COD_Verify_For_WooCommerce
 */

// If uninstall is not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// If WooCommerce's order functions aren't available (e.g. WooCommerce
// was deactivated/removed first), there's nothing safe to migrate -
// just remove this plugin's own options and stop.
if ( ! function_exists( 'wc_get_orders' ) ) {

	delete_option( 'cov_settings_general' );
	delete_option( 'cov_reschedule_pending_orders' );

	return;
}

/**
 * Move any order still sitting in Pending Confirmation to On-hold
 * before this plugin's custom order status registration disappears.
 *
 * Without this, those orders would be left pointing at a post_status
 * ('wc-pending-confirm') that WooCommerce no longer recognizes once
 * this plugin is gone, showing as blank/invalid in the order list.
 *
 * On-hold (a standard WooCommerce status) is used rather than
 * Cancelled or Processing - it signals "needs manual review" without
 * this migration silently deciding whether the order succeeded or
 * failed. That decision is left to the store owner.
 *
 * The literal 'wc-pending-confirm' post_status is used directly
 * (rather than the unprefixed 'pending-confirm' slug used elsewhere
 * in the plugin) since this plugin's status registration is not
 * active during uninstall - querying the raw DB value is the only
 * way to reliably find these orders in this context.
 *
 * Paginated in batches of 50 to avoid memory/timeout issues on
 * stores with many affected orders.
 */
$page = 1;

do {

	$orders = wc_get_orders(
		array(
			'status'   => 'wc-pending-confirm',
			'limit'    => 50,
			'page'     => $page,
			'paginate' => false,
			'return'   => 'objects',
		)
	);

	if ( empty( $orders ) ) {
		break;
	}

    foreach ( $orders as $order ) {

        // set_status() + save() is used instead of update_status()
        // here deliberately - update_status() auto-generates its own
        // "Order status changed from X to Y" note text using
        // wc_get_order_status_name(), which can't find a label for
        // Pending Confirmation in this context (this plugin's status
        // registration doesn't run during uninstall.php), producing a
        // misleading fallback label. Setting the status directly and
        // adding our own note avoids that lookup entirely.
        $order->set_status( 'on-hold' );
        $order->save();

        $order->add_order_note(
            __(
                'Order automatically moved out of Pending Confirmation to On-hold because the COD Verify for WooCommerce plugin was uninstalled. Please review this order manually.',
                'cod-verify-for-woocommerce'
            )
        );
    }

	++$page;

} while ( count( $orders ) === 50 );

/**
 * Remove any remaining scheduled Action Scheduler jobs for the
 * auto-cancel hook, across every order - otherwise Action Scheduler
 * would keep trying to fire a hook whose callback no longer exists.
 */
if ( function_exists( 'as_unschedule_all_actions' ) ) {

	as_unschedule_all_actions( 'cov_cancel_unconfirmed_order' );
}

/**
 * Remove plugin options only. Order-level meta (_cov_token,
 * _cov_confirmed_via, _cov_confirmed_at, etc.) is intentionally left
 * in place on existing orders - it's historical business data about
 * those specific orders, not plugin configuration, and isn't
 * something a store owner would expect to lose when removing a
 * plugin. It's also inert once the plugin is gone - no code reads it.
 */
delete_option( 'cov_settings_general' );
delete_option( 'cov_reschedule_pending_orders' );