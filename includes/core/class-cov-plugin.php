<?php
/**
 * Main plugin class
 * 
 * @package COD_Verify_For_WooCommerce
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class COV_Plugin {

    public function run() {

        $compatibility = new COV_Compatibility();

        if( !$compatibility->is_compatible() ) {
            return;
        }

        // Plugin initialization will continue here.
    }


}