<?php
/**
 * Order request counters behind the dashboard.
 *
 * @package Order via Chat for WooCommerce
 * @version 1.0.0
 */

namespace DDWCPurchaseViaWhatsApp\Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCPVW_Stats_Helper' ) ) {
	/**
	 * Counts every order request a customer sends to WhatsApp.
	 *
	 * ponytail: one non-autoloaded option, read-modify-write. Two requests in the same instant can
	 * lose one count, which is fine for a trend line; move to a table with atomic increments if a
	 * store ever needs exact figures.
	 */
	class DDWCPVW_Stats_Helper {
		/**
		 * Option holding the counters.
		 */
		const OPTION = 'ddwcpvw_request_stats';

		/**
		 * Days of history kept.
		 */
		const KEEP_DAYS = 400;

		/**
		 * Count one request.
		 *
		 * @param string $type  'product' or 'cart'.
		 * @param array  $items Rows of product_id and quantity.
		 * @param float  $value What the items cost when the request was made.
		 * @return void
		 */
		public static function record( $type, $items, $value ) {
			$stats = self::get();
			$day   = current_time( 'Y-m-d' );

			if ( ! isset( $stats['days'][ $day ] ) ) {
				$stats['days'][ $day ] = [
					'requests' => 0,
					'cart'     => 0,
					'units'    => 0,
					'value'    => 0,
				];
			}

			$units = 0;

			foreach ( $items as $item ) {
				$product_id = absint( $item['product_id'] );
				$quantity   = absint( $item['quantity'] );
				$units     += $quantity;

				if ( ! isset( $stats['products'][ $product_id ] ) ) {
					$stats['products'][ $product_id ] = [
						'requests' => 0,
						'units'    => 0,
					];
				}

				++$stats['products'][ $product_id ]['requests'];
				$stats['products'][ $product_id ]['units'] += $quantity;
			}

			++$stats['days'][ $day ]['requests'];
			$stats['days'][ $day ]['cart']  += 'cart' === $type ? 1 : 0;
			$stats['days'][ $day ]['units'] += $units;
			$stats['days'][ $day ]['value'] += (float) $value;

			// Keys are Y-m-d, so a string sort is a date sort.
			ksort( $stats['days'] );
			$stats['days'] = array_slice( $stats['days'], -self::KEEP_DAYS, null, true );

			update_option( self::OPTION, $stats, false );
		}

		/**
		 * Everything recorded so far.
		 *
		 * @return array
		 */
		public static function get() {
			$stats = get_option( self::OPTION, [] );

			return [
				'days'     => isset( $stats['days'] ) && is_array( $stats['days'] ) ? $stats['days'] : [],
				'products' => isset( $stats['products'] ) && is_array( $stats['products'] ) ? $stats['products'] : [],
			];
		}

		/**
		 * Totals and per day figures between two dates, inclusive.
		 *
		 * @param string $from Y-m-d.
		 * @param string $to   Y-m-d.
		 * @return array
		 */
		public static function get_range( $from, $to ) {
			$totals = [
				'requests' => 0,
				'cart'     => 0,
				'units'    => 0,
				'value'    => 0,
				'by_date'  => [
					'requests' => [],
					'value'    => [],
				],
			];

			foreach ( self::get()['days'] as $day => $row ) {
				if ( $day < $from || $day > $to ) {
					continue;
				}

				foreach ( [ 'requests', 'cart', 'units', 'value' ] as $key ) {
					$totals[ $key ] += isset( $row[ $key ] ) ? $row[ $key ] : 0;
				}

				$totals['by_date']['requests'][ $day ] = (int) $row['requests'];
				$totals['by_date']['value'][ $day ]    = (float) $row['value'];
			}

			return $totals;
		}
	}
}
