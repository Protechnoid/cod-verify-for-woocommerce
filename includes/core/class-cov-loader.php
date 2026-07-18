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

    private array $actions = array();
    private array $filters = array();

    public function run() {

        foreach ( $this->actions as $action ) {
            add_action(
                $action['hook'], 
                array( $action['component'], $action['callback'] ),
                $action['priority'],
                $action['accepted_args']
            );
        }

        foreach ( $this->filters as $filter ) {
            add_filter(
                $filter['hook'], 
                array( $filter['component'], $filter['callback'] ),
                $filter['priority'],
                $filter['accepted_args']
            );
        }

    }

    /**
     * Registers an action hook.
     *
     * @param string $hook          Hook name.
     * @param object $component     Class instance.
     * @param string $callback      Method name.
     * @param int    $priority      Hook priority.
     * @param int    $accepted_args Number of accepted arguments.
     */
    public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {

        $this->actions[] = array(
                'hook'          => $hook,
                'component'     => $component,
                'callback'      => $callback,
                'priority'      => $priority,
                'accepted_args' => $accepted_args,
        );
    } 


    /**
     * Registers a filter hook.
     *
     * @param string $hook          Hook name.
     * @param object $component     Class instance.
     * @param string $callback      Method name.
     * @param int    $priority      Hook priority.
     * @param int    $accepted_args Number of accepted arguments.
     */
    public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {

    $this->filters[] = array(
                'hook'          => $hook,
                'component'     => $component,
                'callback'      => $callback,
                'priority'      => $priority,
                'accepted_args' => $accepted_args,
    );

    }

}