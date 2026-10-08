<?php
/**
 * Dashboard template.
 *
 * Supplies data and configuration to the shared devdiggers-framework dashboard builder.
 *
 * @package Order via Chat for WooCommerce
 * @version 1.0.0
 */

namespace DDWCPurchaseViaWhatsApp\Templates\Admin\Dashboard;

use DDWCPurchaseViaWhatsApp\Helper\Dashboard\DDWCPVW_Dashboard_Helper;
use DevDiggers\Framework\Includes\DDFW_Dashboard;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCPVW_Dashboard_Template' ) ) {
	/**
	 * Dashboard template class
	 */
	class DDWCPVW_Dashboard_Template {
		/**
		 * Dashboard data.
		 *
		 * @var array
		 */
		protected $dashboard_data;

		/**
		 * Construct
		 *
		 * @param array $ddwcpvw_configuration Configuration.
		 */
		public function __construct( $ddwcpvw_configuration ) {
			if ( ! class_exists( '\\DevDiggers\\Framework\\Includes\\DDFW_Dashboard' ) ) {
				return;
			}

			$this->dashboard_data = ( new DDWCPVW_Dashboard_Helper( $ddwcpvw_configuration ) )->ddwcpvw_get_dashboard_data();

			$this->ddwcpvw_render();
		}

		/**
		 * Render the dashboard.
		 *
		 * @return void
		 */
		protected function ddwcpvw_render() {
			$summary    = $this->dashboard_data['summary'];
			$date_label = $this->dashboard_data['date_range']['label'];
			$pending    = array_filter(
				$this->dashboard_data['checklist'],
				function ( $step ) {
					return ! $step['done'];
				}
			);

			new DDFW_Dashboard(
				[
					'columns'       => 4,
					'header'        => [
						/* translators: %s: admin display name. */
						'welcome'  => esc_html__( 'Welcome back, %s! 👋🏻', 'order-via-chat-for-woocommerce' ),
						'subtitle' => esc_html__( 'How many customers are sending you orders on WhatsApp, and what they ask for.', 'order-via-chat-for-woocommerce' ),
					],
					'summary_cards' => [
						[
							'title'       => esc_html__( 'Order Requests', 'order-via-chat-for-woocommerce' ),
							'value'       => $summary['requests']['value'],
							'change'      => $summary['requests']['change'],
							'is_positive' => $summary['requests']['is_positive'],
							'icon'        => $this->ddwcpvw_get_stat_icon( '<path d="M21 11.5a8.4 8.4 0 0 1-12.3 7.4L3 20.5l1.6-5.4A8.4 8.4 0 1 1 21 11.5Z"/>' ),
						],
						[
							'title'       => esc_html__( 'Requested Value', 'order-via-chat-for-woocommerce' ),
							'value'       => wc_price( $summary['value']['value'] ),
							'value_type'  => 'html',
							'change'      => $summary['value']['change'],
							'is_positive' => $summary['value']['is_positive'],
							'icon'        => $this->ddwcpvw_get_stat_icon( '<path d="M3.5 3.5v14.2a2.8 2.8 0 0 0 2.8 2.8h14.2"/><path d="M8 17.2v-3.6M12.6 17.2v-7M17.2 17.2v-4.8"/>' ),
						],
						[
							'title'       => esc_html__( 'Units Requested', 'order-via-chat-for-woocommerce' ),
							'value'       => $summary['units']['value'],
							'change'      => $summary['units']['change'],
							'is_positive' => $summary['units']['is_positive'],
							'icon'        => $this->ddwcpvw_get_stat_icon( '<path d="M4 7.5h16V19a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7.5Z"/><path d="M8.5 7.5V6a3.5 3.5 0 0 1 7 0v1.5"/>' ),
						],
						[
							'title'       => esc_html__( 'Whole Cart Requests', 'order-via-chat-for-woocommerce' ),
							'value'       => $summary['cart']['value'],
							'change'      => $summary['cart']['change'],
							'is_positive' => $summary['cart']['is_positive'],
							'icon'        => $this->ddwcpvw_get_stat_icon( '<circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M2.5 3.5h3l2.4 11.6h11.6l2-8.1H6.6"/>' ),
						],
					],
					'charts'        => [
						[
							'id'           => 'ddwcpvw-value-trend',
							'title'        => esc_html__( 'Requested Value', 'order-via-chat-for-woocommerce' ),
							'date_label'   => $date_label,
							'type'         => 'line',
							'data'         => $this->dashboard_data['chart'],
							'x_key'        => 'date',
							'value_format' => 'currency',
							'series'       => [
								[
									'key'   => 'value',
									'label' => esc_html__( 'Value', 'order-via-chat-for-woocommerce' ),
									'color' => '#0256ff',
								],
							],
							'empty'        => [
								'title' => esc_html__( 'No order requests yet', 'order-via-chat-for-woocommerce' ),
								'desc'  => esc_html__( 'Each time a customer sends you a product or cart on WhatsApp, its value shows up here.', 'order-via-chat-for-woocommerce' ),
							],
						],
						[
							'id'         => 'ddwcpvw-requests-trend',
							'title'      => esc_html__( 'Order Requests', 'order-via-chat-for-woocommerce' ),
							'date_label' => $date_label,
							'type'       => 'bar',
							'data'       => $this->dashboard_data['chart'],
							'x_key'      => 'date',
							'series'     => [
								[
									'key'   => 'requests',
									'label' => esc_html__( 'Requests', 'order-via-chat-for-woocommerce' ),
									'color' => '#25d366',
								],
							],
							'empty'      => [
								'title' => esc_html__( 'No requests in this period', 'order-via-chat-for-woocommerce' ),
								'desc'  => esc_html__( 'Try a wider date range, or tap the WhatsApp button on one of your products.', 'order-via-chat-for-woocommerce' ),
							],
						],
					],
					'widgets'       => [
						$pending ? [
							'title'  => esc_html__( 'Finish Setting Up', 'order-via-chat-for-woocommerce' ),
							'width'  => 'half',
							'render' => [ $this, 'ddwcpvw_render_checklist' ],
						] : [
							'title'  => esc_html__( 'Most Requested Products', 'order-via-chat-for-woocommerce' ),
							'width'  => 'half',
							'render' => [ $this, 'ddwcpvw_render_top_products' ],
						],
						[
							'width'  => 'half',
							'render' => [ $this, 'ddwcpvw_render_pro_card' ],
						],
					],
				]
			);
		}

		/**
		 * Upgrade card under the figures.
		 *
		 * @return void
		 */
		public function ddwcpvw_render_pro_card() {
			ddfw_upgrade_to_pro_section(
				[
					'heading'       => esc_html__( 'Count real orders and revenue, not just requests', 'order-via-chat-for-woocommerce' ),
					'description'   => esc_html__( 'With Pro the chat places the order itself, so this dashboard shows what WhatsApp actually sold.', 'order-via-chat-for-woocommerce' ),
					'list_features' => [
						esc_html__( 'WhatsApp orders and revenue, day by day', 'order-via-chat-for-woocommerce' ),
						esc_html__( 'Messages sent and the share that were delivered', 'order-via-chat-for-woocommerce' ),
						esc_html__( 'Failed messages with the reason, so nothing slips by', 'order-via-chat-for-woocommerce' ),
					],
					'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-purchase-via-whatsapp/',
				]
			);
		}

		/**
		 * Setup checklist widget body.
		 *
		 * @return void
		 */
		public function ddwcpvw_render_checklist() {
			$steps = $this->dashboard_data['checklist'];
			$done  = count( array_filter( wp_list_pluck( $steps, 'done' ) ) );
			?>
			<div class="ddwcpvw-checklist">
				<div class="ddwcpvw-checklist-progress" role="progressbar" aria-valuemin="0" aria-valuemax="<?php echo esc_attr( count( $steps ) ); ?>" aria-valuenow="<?php echo esc_attr( $done ); ?>">
					<span style="width:<?php echo esc_attr( round( $done / max( 1, count( $steps ) ) * 100 ) ); ?>%"></span>
				</div>
				<p class="ddwcpvw-checklist-count">
					<?php
					/* translators: 1: completed steps, 2: total steps. */
					printf( esc_html__( '%1$d of %2$d steps done', 'order-via-chat-for-woocommerce' ), absint( $done ), count( $steps ) );
					?>
				</p>
				<?php foreach ( $steps as $step ) : ?>
					<a class="ddwcpvw-checklist-item<?php echo esc_attr( $step['done'] ? ' ddwcpvw-checklist-item-done' : '' ); ?>" href="<?php echo esc_url( $step['url'] ); ?>">
						<span class="ddwcpvw-checklist-mark" aria-hidden="true"><?php echo esc_html( $step['done'] ? '✓' : '' ); ?></span>
						<span class="ddwcpvw-dash-list-info">
							<strong><?php echo esc_html( $step['title'] ); ?></strong>
							<?php if ( ! $step['done'] ) : ?>
								<span><?php echo esc_html( $step['desc'] ); ?></span>
							<?php endif; ?>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
			<?php
		}

		/**
		 * Top products widget body.
		 *
		 * @return void
		 */
		public function ddwcpvw_render_top_products() {
			$products = $this->dashboard_data['top_products'];

			if ( empty( $products ) ) {
				$this->ddwcpvw_render_no_data(
					esc_html__( 'No products requested yet', 'order-via-chat-for-woocommerce' ),
					esc_html__( 'The products customers ask for most on WhatsApp will be listed here.', 'order-via-chat-for-woocommerce' )
				);
				return;
			}
			?>
			<div class="ddwcpvw-dash-list">
				<?php foreach ( $products as $product ) : ?>
					<a class="ddwcpvw-dash-list-item" href="<?php echo esc_url( $product['edit_url'] ); ?>">
						<?php if ( ! empty( $product['thumbnail'] ) ) : ?>
							<span class="ddwcpvw-dash-thumb" aria-hidden="true"><?php echo wp_kses_post( $product['thumbnail'] ); ?></span>
						<?php endif; ?>
						<div class="ddwcpvw-dash-list-info">
							<strong><?php echo esc_html( $product['name'] ); ?></strong>
							<span>
								<?php
								/* translators: %d: number of units requested. */
								printf( esc_html( _n( '%d unit requested', '%d units requested', $product['units'], 'order-via-chat-for-woocommerce' ) ), absint( $product['units'] ) );
								?>
							</span>
						</div>
						<div class="ddwcpvw-dash-list-value">
							<?php
							/* translators: %d: number of requests. */
							printf( esc_html( _n( '%d request', '%d requests', $product['requests'], 'order-via-chat-for-woocommerce' ) ), absint( $product['requests'] ) );
							?>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
			<?php
		}

		/**
		 * Shared empty state for the list widgets.
		 *
		 * @param string $title Title.
		 * @param string $desc  Description.
		 * @return void
		 */
		protected function ddwcpvw_render_no_data( $title, $desc ) {
			?>
			<div class="ddwcpvw-dash-empty">
				<strong><?php echo esc_html( $title ); ?></strong>
				<p><?php echo esc_html( $desc ); ?></p>
			</div>
			<?php
		}

		/**
		 * One stat icon: 24px, 1.8 stroke, round caps, no fills, so the five cards read as a set.
		 *
		 * @param string $paths Inner SVG markup.
		 * @return string
		 */
		protected function ddwcpvw_get_stat_icon( $paths ) {
			return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths . '</svg>';
		}
	}
}
