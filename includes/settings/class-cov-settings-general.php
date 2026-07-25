<?php
/**
 * General Settings tab.
 *
 * Handles rendering, validation and persistence of the General settings.
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
	 * Processes the settings form submission.
	 *
	 * @return void
	 */
	public function handle_save(): void {

		// Only process our own form.
		if ( empty( $_POST['cov_general_submit'] ) ) {
			return;
		}

		// Verify nonce.
		if (
			empty( $_POST['cov_general_nonce'] ) ||
			! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['cov_general_nonce'] ) ),
				'cov_general_settings'
			)
		) {
			return;
		}

		// Check user capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Get submitted settings.
		$settings = isset( $_POST[ COV_Helper::OPTION_GENERAL_SETTINGS ] )
			? (array) wp_unslash( $_POST[ COV_Helper::OPTION_GENERAL_SETTINGS ] )
			: array();

		// Sanitize settings.
		$settings = $this->sanitize( $settings );

		// Save settings.
		update_option(
			COV_Helper::OPTION_GENERAL_SETTINGS,
			$settings
		);

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => COV_Helper::PAGE_SETTINGS,
					'tab'     => $this->get_slug(),
					'saved'   => '1',
				),
				admin_url( 'admin.php' )
			)
		);

		exit;
	}

	/**
	 * Sanitize plugin settings before saving.
	 *
	 * @param array $settings Raw submitted settings.
	 * @return array Sanitized settings.
	 */
	public function sanitize( array $settings ): array {

		if ( ! is_array( $settings ) ) {
			return array();
		}

		$timeout = isset( $settings['timeout'] )
			? max( 1, absint( $settings['timeout'] ) ) * HOUR_IN_SECONDS
			: COV_Helper::TOKEN_LIFETIME;

		$return = array(
			'enabled' => ! empty( $settings['enabled'] ) ? 1 : 0,
			'timeout' => $timeout,
		);

		return $return;
	}

	/**
	 * Render the General settings tab.
	 *
	 * @return void
	 */
	public function render(): void {

		$this->render_form();
	}

	/**
	 * Render the General settings form.
	 *
	 * @return void
	 */
	private function render_form(): void {

		?>

		<form method="post">

			<?php wp_nonce_field( 'cov_general_settings', 'cov_general_nonce' ); ?>

			<input type="hidden" name="cov_general_submit" value="1" />

			<h2><?php esc_html_e( 'General Settings', 'cod-verify-for-woocommerce' ); ?></h2>

			<?php $this->render_general_section(); ?>

			<table class="form-table" role="presentation">

				<tr>
					<th scope="row">
						<?php esc_html_e( 'Enable Plugin', 'cod-verify-for-woocommerce' ); ?>
					</th>
					<td>
						<?php $this->render_enable_plugin_field(); ?>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<?php esc_html_e( 'Verification Timeout', 'cod-verify-for-woocommerce' ); ?>
					</th>
					<td>
						<?php $this->render_timeout_field(); ?>
					</td>
				</tr>

			</table>

			<?php submit_button(); ?>

		</form>

		<?php
	}

	/**
	 * Render the General Settings section description.
	 */
	private function render_general_section(): void {

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
	private function render_enable_plugin_field(): void {

		$general = $this->get_settings();

		?>

		<label>

			<input
				type="hidden"
				name="<?php echo esc_attr( COV_Helper::OPTION_GENERAL_SETTINGS ); ?>[enabled]"
				value="0"
			/>

			<input
				type="checkbox"
				name="<?php echo esc_attr( COV_Helper::OPTION_GENERAL_SETTINGS ); ?>[enabled]"
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
	private function render_timeout_field(): void {

		$general = $this->get_settings();

		$timeout = (int) ( absint( $general['timeout'] ) / HOUR_IN_SECONDS );

		?>

		<input
			type="number"
			name="<?php echo esc_attr( COV_Helper::OPTION_GENERAL_SETTINGS ); ?>[timeout]"
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
	 * Get the General settings.
	 *
	 * @return array
	 */
	private function get_settings(): array {

		return get_option(
			COV_Helper::OPTION_GENERAL_SETTINGS,
			array(
				'enabled' => 1,
				'timeout' => COV_Helper::TOKEN_LIFETIME,
			)
		);
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