<?php
/**
 * General Settings tab.
 *
 * Handles rendering, validation and persistence of the General settings.
 *
 * @package COD_Verify_For_WooCommerce
 */

defined( 'ABSPATH' ) || exit;

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
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		// Get and sanitize submitted settings.
		$settings = isset( $_POST[ COV_Helper::OPTION_GENERAL_SETTINGS ] )
			? map_deep(
				(array) wp_unslash( $_POST[ COV_Helper::OPTION_GENERAL_SETTINGS ] ),
				'sanitize_text_field'
			)
			: array();

		// Validate and normalize settings.
		$settings = $this->sanitize( $settings );

		// Save settings.
		update_option(
			COV_Helper::OPTION_GENERAL_SETTINGS,
			$settings
		);

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'  => COV_Helper::PAGE_SETTINGS,
					'tab'   => $this->get_slug(),
					'saved' => '1',
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

		$timeout = isset( $settings['timeout'] )
			? max( 1, (int) $settings['timeout'] )
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
			'enabled'         => ! empty( $settings['enabled'] ) ? 1 : 0,
			'timeout'         => $timeout,
			'timeout_unit'    => $timeout_unit,
			'notify_merchant' => ! empty( $settings['notify_merchant'] ) ? 1 : 0,
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

			<div class="cov-settings-page">

				<!-- General Settings Card -->

				<div class="cov-settings-card cov-general-settings-card">

					<div class="cov-settings-card-header">

						<div class="cov-settings-card-icon cov-settings-card-icon-blue">
							<span class="dashicons dashicons-admin-generic"></span>
						</div>

						<div>
							<h2>
								<?php esc_html_e( 'General Settings', 'cod-verify-for-woocommerce' ); ?>
							</h2>
						</div>

					</div>

					<div class="cov-settings-card-body">

						<div class="cov-setting-row">

							<div class="cov-setting-label">
								<?php esc_html_e( 'Enable COD verification', 'cod-verify-for-woocommerce' ); ?>
							</div>

							<div class="cov-setting-control">
								<?php $this->render_enable_plugin_field(); ?>
							</div>

						</div>

						<div class="cov-setting-row">

							<div class="cov-setting-label">
								<?php esc_html_e( 'Verification Timeout', 'cod-verify-for-woocommerce' ); ?>
							</div>

							<div class="cov-setting-control">
								<?php $this->render_timeout_field(); ?>
							</div>

						</div>

					</div>

				</div>

				<!-- Email Notifications Card -->

				<div class="cov-settings-card cov-email-settings-card">

					<div class="cov-settings-card-header">

						<div class="cov-settings-card-icon cov-settings-card-icon-blue">
							<span class="dashicons dashicons-email-alt"></span>
						</div>

						<div>

							<h2>
								<?php esc_html_e( 'Email Notifications', 'cod-verify-for-woocommerce' ); ?>
							</h2>

							<p>
								<?php
								esc_html_e(
									'Manage email notifications for the COD verification process.',
									'cod-verify-for-woocommerce'
								);
								?>
							</p>

						</div>

					</div>

					<div class="cov-notification-columns">

						<!-- Merchant Notifications -->

						<div class="cov-notification-column cov-merchant-column">

							<div class="cov-notification-column-header">

								<div class="cov-notification-column-icon cov-merchant-icon">
									<span class="dashicons dashicons-store"></span>
								</div>

								<div>

									<h3>
										<?php esc_html_e( 'Merchant (Store Owner) Notifications', 'cod-verify-for-woocommerce' ); ?>
									</h3>

									<p>
										<?php
										esc_html_e(
											'Configure email notifications sent to the store owner during the COD verification process.',
											'cod-verify-for-woocommerce'
										);
										?>
									</p>

								</div>

							</div>

							<?php $this->render_notify_merchant_field(); ?>

							<?php $this->render_merchant_notifications(); ?>

						</div>

						<!-- Customer Notifications -->

						<div class="cov-notification-column cov-customer-column">

							<div class="cov-notification-column-header">

								<div class="cov-notification-column-icon cov-customer-icon">
									<span class="dashicons dashicons-admin-users"></span>
								</div>

								<div>

									<h3>
										<?php esc_html_e( 'Customer Notifications', 'cod-verify-for-woocommerce' ); ?>
									</h3>

									<p>
										<?php
										esc_html_e(
											'Customer email notifications are automatically sent during the COD verification process.',
											'cod-verify-for-woocommerce'
										);
										?>
									</p>

								</div>

							</div>

							<?php $this->render_customer_notifications(); ?>

						</div>

					</div>

				</div>

			</div>

			<?php submit_button( __( 'Save Changes', 'cod-verify-for-woocommerce' ) ); ?>

		</form>

		<?php
	}

	/**
	 * Render the Enable Plugin field.
	 *
	 * @return void
	 */
	private function render_enable_plugin_field(): void {

		$general = $this->get_settings();

		?>

		<label class="cov-checkbox-label">

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

			<span>
				<?php esc_html_e( 'Enable COD verification', 'cod-verify-for-woocommerce' ); ?>
			</span>

		</label>

		<?php
	}

	/**
	 * Render the Verification Timeout field.
	 *
	 * @return void
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
	 * Render the optional merchant awaiting verification notification.
	 *
	 * @return void
	 */
	private function render_notify_merchant_field(): void {

		$general = $this->get_settings();

		?>

		<div class="cov-merchant-optional-setting">

			<label class="cov-checkbox-label cov-merchant-checkbox">

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

				<span class="cov-checkbox-content">

					<strong>
						<?php esc_html_e( 'Notify the store owner when a new COD order is awaiting customer verification.', 'cod-verify-for-woocommerce' ); ?>
					</strong>

					<span class="cov-checkbox-description">
						<?php
						esc_html_e(
							'Receive an email when a new Cash on Delivery order is placed and is awaiting customer verification.',
							'cod-verify-for-woocommerce'
						);
						?>
					</span>

				</span>

			</label>

		</div>

		<?php
	}

	/**
	 * Render mandatory merchant notifications.
	 *
	 * @return void
	 */
	private function render_merchant_notifications(): void {

		?>

		<div class="cov-mandatory-notifications cov-merchant-mandatory">

			<div class="cov-mandatory-header">

				<span class="dashicons dashicons-info"></span>

				<strong>
					<?php esc_html_e( 'The following emails are always sent to the store owner and cannot be disabled.', 'cod-verify-for-woocommerce' ); ?>
				</strong>

			</div>

			<ul>

				<li>
					<span class="dashicons dashicons-yes"></span>

					<span>
						<strong>
							<?php esc_html_e( 'New COD Order Confirmed', 'cod-verify-for-woocommerce' ); ?>
						</strong>

						<?php
						esc_html_e(
							' – Sent when the customer successfully verifies their COD order.',
							'cod-verify-for-woocommerce'
						);
						?>
					</span>
				</li>

				<li>
					<span class="dashicons dashicons-yes"></span>

					<span>
						<strong>
							<?php esc_html_e( 'COD Order Cancelled', 'cod-verify-for-woocommerce' ); ?>
						</strong>

						<?php
						esc_html_e(
							' – Sent when a COD order is automatically cancelled due to non-verification.',
							'cod-verify-for-woocommerce'
						);
						?>
					</span>
				</li>

			</ul>

		</div>

		<?php
	}

	/**
	 * Render mandatory customer notifications.
	 *
	 * @return void
	 */
	private function render_customer_notifications(): void {

		?>

		<div class="cov-customer-notification-list">

			<div class="cov-customer-notification-item">

				<span class="dashicons dashicons-yes"></span>

				<div>
					<strong>
						<?php esc_html_e( 'Customer Verification Email', 'cod-verify-for-woocommerce' ); ?>
					</strong>

					<p>
						<?php
						esc_html_e(
							'Sent immediately after a Cash on Delivery order is placed. Contains the secure verification link.',
							'cod-verify-for-woocommerce'
						);
						?>
					</p>
				</div>

			</div>

			<div class="cov-customer-notification-item">

				<span class="dashicons dashicons-yes"></span>

				<div>
					<strong>
						<?php esc_html_e( 'Customer Order Confirmed Email', 'cod-verify-for-woocommerce' ); ?>
					</strong>

					<p>
						<?php
						esc_html_e(
							'Sent after the customer successfully verifies their COD order.',
							'cod-verify-for-woocommerce'
						);
						?>
					</p>
				</div>

			</div>

			<div class="cov-customer-notification-item">

				<span class="dashicons dashicons-yes"></span>

				<div>
					<strong>
						<?php esc_html_e( 'Customer Order Cancelled Email', 'cod-verify-for-woocommerce' ); ?>
					</strong>

					<p>
						<?php
						esc_html_e(
							'Sent if the customer does not verify the order before the verification timeout.',
							'cod-verify-for-woocommerce'
						);
						?>
					</p>
				</div>

			</div>

		</div>

		<div class="cov-customer-mandatory-note">

			<span class="dashicons dashicons-info"></span>

			<strong>
				<?php
				esc_html_e(
					'These customer emails are mandatory and cannot be disabled to ensure a smooth verification experience.',
					'cod-verify-for-woocommerce'
				);
				?>
			</strong>

		</div>

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