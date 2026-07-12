<?php
/**
 * Confirmation Status Template.
 *
 * @package COD_Verify_For_WooCommerce
 * 
 * @var string $page_title
 * @var string $message
 * @var string $icon
 * @var WC_Order|null $order 
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


get_header(); ?>

<main class="cov-wrapper">

	<div class="cov-card cov-card-<?php echo esc_attr( $icon ); ?>">

		<div class="cov-icon">

			<?php if ( 'success' === $icon ) : ?>

				<svg
                    width="72"
                    height="72"
                    viewBox="0 0 24 24"
                    fill="none"
                    aria-hidden="true"
                >
                    <circle
                        cx="12"
                        cy="12"
                        r="11"
                        fill="#22c55e"
                    />

                    <path
                        d="M7 12.5L10.5 16L17 9"
                        stroke="#fff"
                        stroke-width="2.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>

			<?php elseif ( 'info' === $icon ) : ?>

				<svg
                    width="72"
                    height="72"
                    viewBox="0 0 24 24"
                    fill="none"
                    aria-hidden="true"
                >

                    <circle
                        cx="12"
                        cy="12"
                        r="11"
                        fill="#3b82f6"
                    />

                    <path
                        d="M12 10V16"
                        stroke="#fff"
                        stroke-width="2.5"
                        stroke-linecap="round"
                    />

                    <circle
                        cx="12"
                        cy="7"
                        r="1"
                        fill="#fff"
                    />

                </svg>
                
			<?php else : ?>

				<svg
                    width="72"
                    height="72"
                    viewBox="0 0 24 24"
                    fill="none"
                    aria-hidden="true"
                >

                    <circle
                        cx="12"
                        cy="12"
                        r="11"
                        fill="#ef4444"
                    />

                    <path
                        d="M8 8L16 16"
                        stroke="#fff"
                        stroke-width="2.5"
                        stroke-linecap="round"
                    />

                    <path
                        d="M16 8L8 16"
                        stroke="#fff"
                        stroke-width="2.5"
                        stroke-linecap="round"
                    />

                </svg>


			<?php endif; ?>

		</div>

		<h1>

			<?php echo esc_html( $page_title ); ?>

		</h1>

		<p>

			<?php echo esc_html( $message ); ?>

		</p>

		<?php if ( $order ) : ?>

			<div class="cov-order">

                <div class="cov-order-row">

                    <span class="cov-label">
                        <?php esc_html_e( 'Order Number', 'cod-verify-for-woocommerce' ); ?>
                    </span>

                    <span class="cov-value">
                        #<?php echo esc_html( $order->get_order_number() ); ?>
                    </span>

                </div>

                <?php if ( 'success' === $icon ) : ?>

                    <div class="cov-order-row">

                        <span class="cov-label">
                            <?php esc_html_e( 'Status', 'cod-verify-for-woocommerce' ); ?>
                        </span>

                        <span class="cov-value">
                            <?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>
                        </span>

                    </div>

                <?php endif; ?>

            </div>

		<?php endif; ?>

		<a
			class="cov-button"
			href="<?php echo esc_url( home_url() ); ?>"
		>

			<?php esc_html_e( 'Continue Shopping', 'cod-verify-for-woocommerce' ); ?>

		</a>

	</div>

</main>

<?php get_footer(); ?>