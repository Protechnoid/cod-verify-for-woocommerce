<?php
/**
 * Settings Module
 *
 * Registers and manages plugin settings.
 *
 * @package COD_Verify_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once COV_PLUGIN_PATH . 'includes/settings/class-cov-settings-tab-interface.php';

require_once COV_PLUGIN_PATH . 'includes/settings/class-cov-settings-general.php';
require_once COV_PLUGIN_PATH . 'includes/settings/class-cov-settings-email.php';
require_once COV_PLUGIN_PATH . 'includes/settings/class-cov-settings-debug.php';

require_once COV_PLUGIN_PATH . 'includes/settings/class-cov-settings-page.php';

/**
 * Settings Module.
 */
class COV_Settings {

	/**
	 * Settings page.
	 *
	 * @var COV_Settings_Page
	 */
	private COV_Settings_Page $page;

	/**
	 * Settings tabs.
	 *
	 * @var array<string, COV_Settings_Tab_Interface>
	 */
	private array $tabs = array();

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->tabs = array(
			'general' => new COV_Settings_General(),
			'email'   => new COV_Settings_Email(),
			'debug'   => new COV_Settings_Debug(),
		);

		$this->page = new COV_Settings_Page(
			$this->tabs
		);
	}

	/**
	 * Register the settings admin menu.
	 */
	public function register_admin_menu() {

		$this->page->register_admin_menu();
	}

	/**
	 * Register settings for all tabs.
	 */
	public function register_settings() {

		foreach ( $this->tabs as $tab ) {
			$tab->register_settings();
		}
	}

}