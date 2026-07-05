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

    private COV_Loader $loader;

    public function run() {

        $this->loader = new COV_Loader();

        $this->define_hooks();
        
        $this->loader->run();

    }

    private function define_hooks() {

    }  

}
