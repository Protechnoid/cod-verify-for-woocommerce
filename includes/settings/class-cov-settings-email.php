<?php
/**
 * Email Settings
 *
 * Handles rendering and persistence of the Email settings tab.
 *
 * @package COD_Verify_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email settings tab.
 */
class COV_Settings_Email implements COV_Settings_Tab_Interface {

	/**
	 * Get the tab slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {

		return COV_Helper::SETTINGS_EMAIL;
	}

	/**
	 * Get the tab title.
	 *
	 * @return string
	 */
	public function get_title(): string {

		return __( 'Email', 'cod-verify-for-woocommerce' );
	}

	/**
	 * Render the Email settings tab.
	 *
	 * @return void
	 */
	public function render(): void {

		$this->render_test_email_form();

		echo '<hr>';

		$this->render_template_form();
	}

	/**
	 * Render the Email Testing form.
	 *
	 * @return void
	 */
	private function render_test_email_form(): void {

		?>

		<h2><?php esc_html_e( 'Test Email', 'cod-verify-for-woocommerce' ); ?></h2>

		<p>
			<?php
			esc_html_e(
				'Send a test email to verify that your WordPress site can send emails successfully.',
				'cod-verify-for-woocommerce'
			);
			?>
		</p>

		<form method="post">

			<?php wp_nonce_field( 'cov_email_test', 'cov_email_test_nonce' ); ?>

			<input
				type="hidden"
				name="cov_email_test_submit"
				value="1"
			/>

			<table class="form-table" role="presentation">

				<tr>

					<th scope="row">

						<label for="cov_test_email">

							<?php esc_html_e( 'Email Address', 'cod-verify-for-woocommerce' ); ?>

						</label>

					</th>

					<td>

						<input
							type="email"
							id="cov_test_email"
							name="cov_test_email"
							value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>"
							class="regular-text"
						/>

						<p class="description">

							<?php
							esc_html_e(
								'A test email will be sent immediately to this address.',
								'cod-verify-for-woocommerce'
							);
							?>

						</p>

					</td>

				</tr>

			</table>

			<?php
			submit_button(
				__( 'Send Test Email', 'cod-verify-for-woocommerce' ),
				'secondary',
				'cov_send_test_email',
				false
			);
			?>

		</form>

		<?php
	}

	/**
	 * Processes the Email form submission.
	 *
	 * @return void
	 */
	public function handle_save(): void {

		if ( ! empty( $_POST['cov_email_test_submit'] ) ) {
			$this->process_test_email();
		}

		if ( ! empty( $_POST['cov_email_settings_submit'] ) ) {
			$this->process_template_settings();
		}

	}

	/**
	 * Process the Test Email form submission.
	 *
	 * @return void
	 */
	private function process_test_email(): void {

		// Verify nonce.
		if (
			empty( $_POST['cov_email_test_nonce'] ) ||
			! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['cov_email_test_nonce'] ) ),
				'cov_email_test'
			)
		) {
			return;
		}

		// Check user capability.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		// Get the email address.
		$email = isset( $_POST['cov_test_email'] )
			? sanitize_email( wp_unslash( $_POST['cov_test_email'] ) )
			: '';

		// Validate the email address.
		if ( empty( $email ) || ! is_email( $email ) ) {
			return;
		}

		//Send the test email.
		$subject = __( 'COD Verify – Test Email', 'cod-verify-for-woocommerce' );

		$message = sprintf(
			/* translators: %s: Site name. */
			__(
				"Hello,\n\n" .
				"This is a test email from COD Verify for WooCommerce.\n\n" .
				"If you received this email, your WordPress site is able to send emails successfully.\n\n" .
				"Store: %s\n\n" .
				"You can now continue configuring your COD Verify email settings.\n\n" .
				"Thank you,\n" .
				"COD Verify for WooCommerce",
				'cod-verify-for-woocommerce'
			),
			get_bloginfo( 'name' )
		);

		$sent = wp_mail(
			$email,
			$subject,
			$message
		);

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'       => COV_Helper::PAGE_SETTINGS,
					'tab'        => $this->get_slug(),
					'email_test' => $sent ? 'success' : 'failed',
				),
				admin_url( 'admin.php' )
			)
		);

		exit;

	}
	
	/**
	 * Process the Email Template form submission.
	 *
	 * @return void
	 */
	private function process_template_settings(): void {

		// Verify nonce.
		if (
			empty( $_POST['cov_email_settings_nonce'] ) ||
			! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['cov_email_settings_nonce'] ) ),
				'cov_email_settings'
			)
		) {
			return;
		}

		// Check user capability.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		// Get the submitted settings.
		$settings = isset( $_POST['cov_email_settings'] )
			? (array) wp_unslash( $_POST['cov_email_settings'] )
			: array();

		// Sanitize the settings.
		$settings = $this->sanitize( $settings );

		// Save the settings.
		update_option(
			COV_Helper::OPTION_EMAIL_SETTINGS,
			$settings
		);
	}

	/**
	 * Render the Email Template form.
	 *
	 * @return void
	 */
	private function render_template_form(): void {

		?>

		<h2><?php esc_html_e( 'Email Template', 'cod-verify-for-woocommerce' ); ?></h2>

		<?php $email_settings = $this->get_settings(); ?>

		<form method="post">

			<?php wp_nonce_field( 'cov_email_settings', 'cov_email_settings_nonce' ); ?>

			<input
				type="hidden"
				name="cov_email_settings_submit"
				value="1"
			/>

			<table class="form-table" role="presentation">

				<tr>

					<th scope="row">

						<label for="cov_email_subject">

							<?php esc_html_e( 'Subject', 'cod-verify-for-woocommerce' ); ?>

						</label>

					</th>

					<td>

						<input
							type="text"
							id="cov_email_subject"
							name="cov_email_settings[subject]"
							value="<?php echo esc_attr( $email_settings['subject'] ); ?>"
							class="regular-text"
						/>

						<p class="description">

							<?php
							esc_html_e(
								'Subject line used for the customer confirmation email.',
								'cod-verify-for-woocommerce'
							);
							?>

						</p>

					</td>

				</tr>

				<tr>

					<th scope="row">

						<label for="cov_email_heading">

							<?php esc_html_e( 'Heading', 'cod-verify-for-woocommerce' ); ?>

						</label>

					</th>

					<td>

						<input
							type="text"
							id="cov_email_heading"
							name="cov_email_settings[heading]"
							value="<?php echo esc_attr( $email_settings['heading'] ); ?>"
							class="regular-text"
						/>

						<p class="description">

							<?php
							esc_html_e(
								'Main heading displayed at the top of the customer confirmation email.',
								'cod-verify-for-woocommerce'
							);
							?>

						</p>

					</td>

				</tr>

				<tr>

					<th scope="row">

						<label for="cov_email_message">

							<?php esc_html_e( 'Message', 'cod-verify-for-woocommerce' ); ?>

						</label>

					</th>

					<td>

						<textarea
							id="cov_email_message"
							name="cov_email_settings[message]"
							rows="8"
							class="large-text"
						><?php echo esc_textarea( $email_settings['message'] ); ?></textarea>

						<p class="description">

							<?php
							esc_html_e(
								'Message displayed above the confirmation button in the customer email.',
								'cod-verify-for-woocommerce'
							);
							?>

						</p>

					</td>

				</tr>

			</table>

			<?php submit_button(); ?>

		</form>

		<?php
	}

	/**
	 * Sanitizes the submitted settings.
	 *
	 * @param array $settings Raw settings.
	 * @return array
	 */
	public function sanitize( array $settings ): array {

		// No email settings to sanitize yet.
		return $settings;
	}

	/**
	 * Get the Email settings.
	 *
	 * @return array
	 */
	private function get_settings(): array {

		$defaults = array(
			'subject' => __( 'Please confirm your Cash on Delivery order', 'cod-verify-for-woocommerce' ),
			'heading' => __( 'Confirm Your Cash on Delivery Order', 'cod-verify-for-woocommerce' ),
			'message' => __(
								"Hello,\n\n" .
								"Thank you for your order.\n\n" .
								"Please click the confirmation button below to verify your Cash on Delivery order.\n\n" .
								"Your order will be processed after confirmation.\n\n" .
								"Thank you for shopping with us.",
								'cod-verify-for-woocommerce'
							),
		);

		$settings = get_option(
			COV_Helper::OPTION_EMAIL_SETTINGS,
			array()
		);

		return wp_parse_args( $settings, $defaults );
	}
}