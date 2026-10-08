<?php
/**
 * Dashboard data.
 *
 * @package Order via Chat for WooCommerce
 * @version 1.0.0
 */

namespace DDWCPurchaseViaWhatsApp\Helper\Dashboard;

use DDWCPurchaseViaWhatsApp\Helper\DDWCPVW_Stats_Helper;
use DevDiggers\Framework\Includes\DDFW_Dashboard_Data;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCPVW_Dashboard_Helper' ) ) {
	/**
	 * Dashboard helper class
	 */
	class DDWCPVW_Dashboard_Helper {
		/**
		 * Configuration Variable
		 *
		 * @var array
		 */
		protected $ddwcpvw_configuration;

		/**
		 * Construct
		 *
		 * @param array $ddwcpvw_configuration Configuration.
		 */
		public function __construct( $ddwcpvw_configuration ) {
			$this->ddwcpvw_configuration = $ddwcpvw_configuration;
		}

		/**
		 * Everything the dashboard template renders.
		 *
		 * @return array
		 */
		public function ddwcpvw_get_dashboard_data() {
			$range    = DDFW_Dashboard_Data::get_date_range();
			$previous = $this->ddwcpvw_get_previous_range( $range );

			$current = DDWCPVW_Stats_Helper::get_range( $range['from'], $range['to'] );
			$before  = DDWCPVW_Stats_Helper::get_range( $previous['from'], $previous['to'] );

			return [
				'date_range'   => $range,
				'summary'      => [
					'requests' => $this->ddwcpvw_make_summary( $current['requests'], $before['requests'] ),
					'value'    => $this->ddwcpvw_make_summary( $current['value'], $before['value'] ),
					'units'    => $this->ddwcpvw_make_summary( $current['units'], $before['units'] ),
					'cart'     => $this->ddwcpvw_make_summary( $current['cart'], $before['cart'] ),
				],
				'chart'        => DDFW_Dashboard_Data::build_time_series( $range['from'], $range['to'], $current['by_date'] ),
				'top_products' => $this->ddwcpvw_get_top_products(),
				'checklist'    => $this->ddwcpvw_get_checklist(),
			];
		}

		/**
		 * The products customers ask for most, all time.
		 *
		 * @return array
		 */
		protected function ddwcpvw_get_top_products() {
			$map = DDWCPVW_Stats_Helper::get()['products'];

			uasort(
				$map,
				function ( $first, $second ) {
					return $second['requests'] <=> $first['requests'];
				}
			);

			$products = [];

			foreach ( $map as $product_id => $totals ) {
				$product = wc_get_product( $product_id );

				if ( ! $product ) {
					continue;
				}

				$products[] = [
					'name'      => wp_strip_all_tags( $product->get_name() ),
					'requests'  => $totals['requests'],
					'units'     => $totals['units'],
					'thumbnail' => $product->get_image( 'thumbnail', [ 'class' => 'ddwcpvw-dash-thumb-img' ], true ),
					'edit_url'  => get_edit_post_link( $product->get_parent_id() ? $product->get_parent_id() : $product_id, '' ),
				];

				if ( 5 === count( $products ) ) {
					break;
				}
			}

			return $products;
		}

		/**
		 * What is left before WhatsApp ordering works.
		 *
		 * @return array
		 */
		protected function ddwcpvw_get_checklist() {
			$config  = $this->ddwcpvw_configuration;
			$general = ddwcpvw_get_dashboard_url(
				[
					'menu' => 'configuration',
					'tab'  => 'general',
				]
			);
			$display = ddwcpvw_get_dashboard_url(
				[
					'menu' => 'configuration',
					'tab'  => 'display',
				]
			);

			return [
				[
					'done'  => (bool) $config['whatsapp_number'],
					'title' => esc_html__( 'Add your WhatsApp number', 'order-via-chat-for-woocommerce' ),
					'desc'  => esc_html__( 'The buttons stay hidden until there is a number to send orders to.', 'order-via-chat-for-woocommerce' ),
					'url'   => $general,
				],
				[
					'done'  => 'yes' === $config['enabled'],
					'title' => esc_html__( 'Switch WhatsApp ordering on', 'order-via-chat-for-woocommerce' ),
					'desc'  => esc_html__( 'Turn on the plugin status in the General tab.', 'order-via-chat-for-woocommerce' ),
					'url'   => $general,
				],
				[
					'done'  => '' !== (string) $config['product_page_position'] || '' !== (string) $config['cart_page_position'] || '' !== (string) $config['shop_page_position'],
					'title' => esc_html__( 'Place the button', 'order-via-chat-for-woocommerce' ),
					'desc'  => esc_html__( 'Show it on product pages, the cart, or shop pages.', 'order-via-chat-for-woocommerce' ),
					'url'   => $display,
				],
				[
					'done'  => ! empty( DDWCPVW_Stats_Helper::get()['days'] ),
					'title' => esc_html__( 'Send your first order request', 'order-via-chat-for-woocommerce' ),
					'desc'  => esc_html__( 'Tap the WhatsApp button on one of your products to try the whole journey.', 'order-via-chat-for-woocommerce' ),
					'url'   => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ),
				],
			];
		}

		/**
		 * Wrap a value with its period-over-period change.
		 *
		 * @param float $value    Current value.
		 * @param float $previous Previous value.
		 * @return array
		 */
		protected function ddwcpvw_make_summary( $value, $previous ) {
			$value    = (float) $value;
			$previous = (float) $previous;

			if ( $previous <= 0 ) {
				$change = $value > 0 ? 100 : 0;
			} else {
				$change = ( ( $value - $previous ) / $previous ) * 100;
			}

			return [
				'value'       => $value,
				'change'      => round( $change, 1 ),
				'is_positive' => $value >= $previous,
			];
		}

		/**
		 * The period immediately before the selected one, of the same length.
		 *
		 * @param array $range Selected range.
		 * @return array
		 */
		protected function ddwcpvw_get_previous_range( $range ) {
			$from = strtotime( $range['from'] );
			$to   = strtotime( $range['to'] );
			$days = max( 1, (int) round( ( $to - $from ) / DAY_IN_SECONDS ) + 1 );

			return [
				'from' => gmdate( 'Y-m-d', strtotime( '-' . $days . ' days', $from ) ),
				'to'   => gmdate( 'Y-m-d', strtotime( '-1 day', $from ) ),
			];
		}
	}
}
