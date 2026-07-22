<?php
/**
 * General Settings
 *
 * Registers and manages the General settings tab.
 *
 * @package COD_Verify_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * General Settings.
 */
class COV_Settings_General implements COV_Settings_Tab_Interface {

	/**
	 * Register plugin settings.
	 */
	public function register_settings(): void {

		register_setting(
			COV_Helper::SETTINGS_GROUP,
			COV_Helper::OPTION_SETTINGS,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => array(),
			)
		);

		$this->register_settings_sections();
	}

	/**
	 * Sanitize plugin settings before saving.
	 *
	 * @param array $settings Raw submitted settings.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( $settings ): array {

		if ( ! is_array( $settings ) ) {
			return array();
		}

		$general = $settings[ COV_Helper::SETTINGS_GENERAL ] ?? array();

		$timeout = isset( $general['timeout'] )
			? max( 1, absint( $general['timeout'] ) ) * HOUR_IN_SECONDS
			: 6 * HOUR_IN_SECONDS;

		return array(
			COV_Helper::SETTINGS_GENERAL => array(
				'enabled' => ! empty( $general['enabled'] ) ? 1 : 0,
				'timeout' => $timeout,
			),
		);
	}

	/**
	 * Register settings section and fields.
	 */
	public function register_settings_sections(): void {

		add_settings_section(
			COV_Helper::SECTION_GENERAL,
			__( 'General Settings', 'cod-verify-for-woocommerce' ),
			array( $this, 'render_general_section' ),
			COV_Helper::PAGE_SETTINGS
		);

		add_settings_field(
			'cov_enabled',
			__( 'Enable Plugin', 'cod-verify-for-woocommerce' ),
			array( $this, 'render_enable_plugin_field' ),
			COV_Helper::PAGE_SETTINGS,
			COV_Helper::SECTION_GENERAL
		);

		add_settings_field(
			'cov_timeout',
			__( 'Verification Timeout', 'cod-verify-for-woocommerce' ),
			array( $this, 'render_timeout_field' ),
			COV_Helper::PAGE_SETTINGS,
			COV_Helper::SECTION_GENERAL
		);
	}

	/**
	 * Render the General settings tab.
	 */
	public function render(): void {

		?>

		<form method="post" action="options.php">

			<?php
			settings_fields( COV_Helper::SETTINGS_GROUP );
			do_settings_sections( COV_Helper::PAGE_SETTINGS );
			submit_button();
			?>

		</form>

		<?php
	}

	/**
	 * Render the General Settings section description.
	 */
	public function render_general_section(): void {

		echo '<p>' .
			esc_html__(
				'Configure the general behaviour of COD Verify.',
				'cod-verify-for-woocommerce'
			) .
		'</p>';
	}

	/**
	 * Render the Enable Plugin field.
	 */
	public function render_enable_plugin_field(): void {

		$general = COV_Helper::get_settings(
			COV_Helper::SETTINGS_GENERAL
		);

		$field_name = COV_Helper::OPTION_SETTINGS . '[' . COV_Helper::SETTINGS_GENERAL . '][enabled]';

		?>

		<label>

			<input
				type="hidden"
				name="<?php echo esc_attr( $field_name ); ?>"
				value="0"
			/>

			<input
				type="checkbox"
				name="<?php echo esc_attr( $field_name ); ?>"
				value="1"
				<?php checked( ! empty( $general['enabled'] ) ); ?>
			/>

			<?php esc_html_e( 'Enable COD Verify for WooCommerce', 'cod-verify-for-woocommerce' ); ?>

		</label>

		<?php
	}

	/**
	 * Render the Verification Timeout field.
	 *
	 * The timeout is stored in seconds but displayed in hours.
	 */
	public function render_timeout_field(): void {

		$general = COV_Helper::get_settings(
			COV_Helper::SETTINGS_GENERAL
		);

		$timeout = isset( $general['timeout'] )
			? absint( $general['timeout'] ) / HOUR_IN_SECONDS
			: 6;

		$field_name = COV_Helper::OPTION_SETTINGS . '[' . COV_Helper::SETTINGS_GENERAL . '][timeout]';

		?>

		<input
			type="number"
			name="<?php echo esc_attr( $field_name ); ?>"
			value="<?php echo esc_attr( $timeout ); ?>"
			min="1"
			step="1"
			class="small-text"
		/>

		<p class="description">
			<?php esc_html_e( 'Number of hours before an unconfirmed order expires.', 'cod-verify-for-woocommerce' ); ?>
		</p>

		<?php
	}

	/**
	 * Get the tab slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {

		return COV_Helper::SETTINGS_GENERAL;
	}

	/**
	 * Get the tab title.
	 *
	 * @return string
	 */
	public function get_title(): string {

		return __( 'General', 'cod-verify-for-woocommerce' );
	}
}