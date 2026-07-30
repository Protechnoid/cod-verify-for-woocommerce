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
			? max( 1, absint( $settings['timeout'] ) )
			: 6;

		$allowed_units = array(
			COV_Helper::TIMEOUT_MINUTES,
			COV_Helper::TIMEOUT_HOURS,
		);

		$timeout_unit = isset( $settings['timeout_unit'] )
			&& in_array( $settings['timeout_unit'], $allowed_units, true )
				? $settings['timeout_unit']
				: COV_Helper::TIMEOUT_HOURS;

		return array(
			'enabled'               => ! empty( $settings['enabled'] ) ? 1 : 0,
			'timeout'               => $timeout,
			'timeout_unit'          => $timeout_unit,
			'notify_merchant'       => ! empty( $settings['notify_merchant'] ) ? 1 : 0,
			'send_processing_email' => ! empty( $settings['send_processing_email'] ) ? 1 : 0,
		);
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

			<hr>

			<h2><?php esc_html_e( 'Email Notifications', 'cod-verify-for-woocommerce' ); ?></h2>

			<?php $this->render_notification_section(); ?>

			<table class="form-table" role="presentation">

				<tr>
					<th scope="row">
						<?php esc_html_e( 'Merchant (Store Owner)', 'cod-verify-for-woocommerce' ); ?>
					</th>
					<td>
						<?php $this->render_notify_merchant_field(); ?>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<?php esc_html_e( 'Customer', 'cod-verify-for-woocommerce' ); ?>
					</th>
					<td>
						<?php $this->render_processing_email_field(); ?>
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
				'Configure the core settings for COD Verify, including plugin status and the customer verification timeout.',
				'cod-verify-for-woocommerce'
			) .
		'</p>';
	}

	/**
	 * Render the Notification Settings section description.
	 */
	private function render_notification_section(): void {

		echo '<p>' .
			esc_html__(
				'Choose which email notifications are sent after a customer successfully verifies their COD order.',
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

			<?php esc_html_e( 'Enable COD verification', 'cod-verify-for-woocommerce' ); ?>

		</label>

		<?php
	}

	/**
	 * Render the Merchant notification field.
	 */
	private function render_notify_merchant_field(): void {

		$general = $this->get_settings();

		?>

		<label>

			<input
				type="hidden"
				name="<?php echo esc_attr( COV_Helper::OPTION_GENERAL_SETTINGS ); ?>[notify_merchant]"
				value="0"
			/>

			<input
				type="checkbox"
				name="<?php echo esc_attr( COV_Helper::OPTION_GENERAL_SETTINGS ); ?>[notify_merchant]"
				value="1"
				<?php checked( ! empty( $general['notify_merchant'] ) ); ?>
			/>

			<?php esc_html_e( 'Notify the store owner after a customer successfully verifies a COD order.', 'cod-verify-for-woocommerce' ); ?>

		</label>

		<?php
	}

	/**
	 * Render the Customer processing email field.
	 */
	private function render_processing_email_field(): void {

		$general = $this->get_settings();

		?>

		<label>

			<input
				type="hidden"
				name="<?php echo esc_attr( COV_Helper::OPTION_GENERAL_SETTINGS ); ?>[send_processing_email]"
				value="0"
			/>

			<input
				type="checkbox"
				name="<?php echo esc_attr( COV_Helper::OPTION_GENERAL_SETTINGS ); ?>[send_processing_email]"
				value="1"
				<?php checked( ! empty( $general['send_processing_email'] ) ); ?>
			/>

			<?php esc_html_e( 'Send the WooCommerce "Processing Order" email after the customer successfully verifies their COD order.', 'cod-verify-for-woocommerce' ); ?>

		</label>

		<?php
	}

	/**
	 * Render the Verification Timeout field.
	 */
	private function render_timeout_field(): void {

		$general = $this->get_settings();

		?>

		<div class="cov-timeout-field">

			<input
				type="number"
				name="<?php echo esc_attr( COV_Helper::OPTION_GENERAL_SETTINGS ); ?>[timeout]"
				value="<?php echo esc_attr( absint( $general['timeout'] ) ); ?>"
				min="1"
				step="1"
				class="small-text"
			/>

			<select
				name="<?php echo esc_attr( COV_Helper::OPTION_GENERAL_SETTINGS ); ?>[timeout_unit]"
			>

				<option
					value="<?php echo esc_attr( COV_Helper::TIMEOUT_MINUTES ); ?>"
					<?php selected( $general['timeout_unit'], COV_Helper::TIMEOUT_MINUTES ); ?>
				>
					<?php esc_html_e( 'Minutes', 'cod-verify-for-woocommerce' ); ?>
				</option>

				<option
					value="<?php echo esc_attr( COV_Helper::TIMEOUT_HOURS ); ?>"
					<?php selected( $general['timeout_unit'], COV_Helper::TIMEOUT_HOURS ); ?>
				>
					<?php esc_html_e( 'Hours', 'cod-verify-for-woocommerce' ); ?>
				</option>

			</select>

		</div>

		<p class="description">
			<?php
			esc_html_e(
				'Set how long customers have to confirm their Cash on Delivery (COD) order before it is automatically cancelled.',
				'cod-verify-for-woocommerce'
			);
			?>
		</p>

		<?php
	}

	/**
	 * Get the General settings.
	 *
	 * @return array
	 */
	private function get_settings(): array {

		return COV_Helper::get_general_settings();
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