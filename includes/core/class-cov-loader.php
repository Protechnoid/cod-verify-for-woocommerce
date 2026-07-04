<?php
/**
 * Plugin loader
 * 
 * @package COD_Verify_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class COV_Loader {

    private $actions = array();

    public function run() {

    }

    public function add_action( $hook, $component, $callback ) {

        $this->actions[] = array(
                'hook'      => $hook,
                'component' => $component,
                'callback'  => $callback,
        );
    }

}