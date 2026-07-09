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
    private $filters = array();

    public function run() {

        foreach ( $this->actions as $action ) {
            add_action(
                $action['hook'], 
                array( $action['component'], $action['callback'] ) 
            );
        }

        foreach ( $this->filters as $filter ) {
            add_filter(
                $filter['hook'], 
                array( $filter['component'], $filter['callback'] ) 
            );
        }

    }

    /**
     * Registers an action hook.
     *
     * @param string $hook      Hook name.
     * @param object $component Class instance.
     * @param string $callback  Method name.
     */
    public function add_action( $hook, $component, $callback ) {

        $this->actions[] = array(
                'hook'      => $hook,
                'component' => $component,
                'callback'  => $callback,
        );
    } 


    /**
     * Registers a filter hook.
     *
     * @param string $hook      Hook name.
     * @param object $component Class instance.
     * @param string $callback  Method name.
     */
    public function add_filter( $hook, $component, $callback ) {

    $this->filters[] = array(
                'hook'      => $hook,
                'component' => $component,
                'callback'  => $callback,
    );

    }

}