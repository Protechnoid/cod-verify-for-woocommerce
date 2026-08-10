<?php
/**
 * Plugin loader.
 *
 * @package COD_Verify_For_WooCommerce
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers and runs plugin hooks.
 */
class COV_Loader {

	/**
	 * Registered action hooks.
	 *
	 * @var array<int, array<string, mixed>>
	 */
	private array $actions = array();

	/**
	 * Registered filter hooks.
	 *
	 * @var array<int, array<string, mixed>>
	 */
	private array $filters = array();

	/**
	 * Register all stored hooks with WordPress.
	 *
	 * @return void
	 */
	public function run(): void {

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
	 * @param string   $hook          Hook name.
	 * @param object   $component     Class instance.
	 * @param string   $callback      Method name.
	 * @param int      $priority      Hook priority.
	 * @param int      $accepted_args Number of accepted arguments.
	 *
	 * @return void
	 */
	public function add_action(
		string $hook,
		object $component,
		string $callback,
		int $priority = 10,
		int $accepted_args = 1
	): void {

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
	 * @param string   $hook          Hook name.
	 * @param object   $component     Class instance.
	 * @param string   $callback      Method name.
	 * @param int      $priority      Hook priority.
	 * @param int      $accepted_args Number of accepted arguments.
	 *
	 * @return void
	 */
	public function add_filter(
		string $hook,
		object $component,
		string $callback,
		int $priority = 10,
		int $accepted_args = 1
	): void {

		$this->filters[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);
	}
}