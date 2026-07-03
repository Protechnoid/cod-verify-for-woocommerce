<?php
/**
 * Plugin compatibility checks.
 * 
 * @package COD_Verify_For_WooCommerce
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class COV_Compatibility {

    public function is_compatible() {

        return class_exists( 'WooCommerce' );

    }

}