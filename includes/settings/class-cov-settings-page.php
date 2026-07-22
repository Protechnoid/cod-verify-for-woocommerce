<?php
/**
 * Settings Page
 *
 * Handles the plugin settings page.
 *
 * @package COD_Verify_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings Page.
 */
class COV_Settings_Page {

    /**
     * Settings tabs.
     *
     * Array of tab instances keyed by tab slug.
     *
     * @var array<string, object>
     */
    private array $tabs;

    /**
     * Constructor.
     *
     * @param array<string, object> $tabs Settings tab instances.
     */
    public function __construct( array $tabs ) {

        $this->tabs = $tabs;
    }

	/**
	 * Register the plugin settings page.
	 */
	public function register_admin_menu() {

		add_menu_page(
			__( 'COD Verify Settings', 'cod-verify-for-woocommerce' ),
			__( 'COD Verify', 'cod-verify-for-woocommerce' ),
			'manage_woocommerce',
			COV_Helper::PAGE_SETTINGS,
			array( $this, 'render_settings_page' ),
			'dashicons-shield-alt',
			56
		);
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page() {
		?>

		<div class="wrap">

			<h1>
				<?php esc_html_e( 'COD Verify Settings', 'cod-verify-for-woocommerce' ); ?>
			</h1>
            
            <?php
                $this->render_tabs();
                $this->render_active_tab();
            ?>

		</div>

		<?php
	}

    /**
     * Render the active settings tab.
     */
    private function render_active_tab() {

        $active_tab = $this->get_active_tab();

        if ( isset( $this->tabs[ $active_tab ] ) ) {
            $this->tabs[ $active_tab ]->render();
            return;
        }

        $first_tab = reset( $this->tabs );

        if ( $first_tab ) {
            $first_tab->render();
        }
    }

    /**
     * Get the active settings tab.
     *
     * @return string
     */
    private function get_active_tab() {

        return isset( $_GET['tab'] )
            ? sanitize_key( wp_unslash( $_GET['tab'] ) )
            : 'general';
    }


    /**
     * Render the settings navigation tabs.
     */
    private function render_tabs() {

        $active_tab = $this->get_active_tab();

        echo '<h2 class="nav-tab-wrapper">';

        foreach ( $this->tabs as $tab ) {

            $url = add_query_arg(
                array(
                    'page' => COV_Helper::PAGE_SETTINGS,
                    'tab'  => $tab->get_slug(),
                ),
                admin_url( 'admin.php' )
            );

            printf(
                '<a href="%1$s" class="nav-tab %2$s">%3$s</a>',
                esc_url( $url ),
                $active_tab === $tab->get_slug() ? 'nav-tab-active' : '',
                esc_html( $tab->get_title() )
            );
        }

        echo '</h2>';
    }


}