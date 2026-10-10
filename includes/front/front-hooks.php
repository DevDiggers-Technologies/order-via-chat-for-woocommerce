<?php
/**
 * Frontend hooks.
 *
 * @package DevDiggers Order via Chat for WooCommerce
 * @version 1.0.0
 */

namespace DDWCPurchaseViaWhatsApp\Includes\Front;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCPVW_Front_Hooks' ) ) {
	/**
	 * Front end hooks class
	 */
	class DDWCPVW_Front_Hooks extends DDWCPVW_Front_Functions {
		/**
		 * Construct
		 *
		 * @param array $ddwcpvw_configuration Plugin configuration.
		 */
		public function __construct( $ddwcpvw_configuration ) {
			parent::__construct( $ddwcpvw_configuration );

			$product_position = (string) $ddwcpvw_configuration['product_page_position'];

			// Catalog mode removes Add to cart, so the WhatsApp button is the only way to buy and cannot be hidden.
			if ( '' === $product_position && 'yes' === $ddwcpvw_configuration['catalog_mode'] ) {
				$product_position = '55';
			}

			if ( '' !== $product_position ) {
				if ( '5' === $product_position ) {
					add_action( 'woocommerce_after_single_product_summary', [ $this, 'ddwcpvw_add_content_in_single_product_page' ], 5 );
				} elseif ( '10' === $product_position ) {
					add_action( 'woocommerce_product_thumbnails', [ $this, 'ddwcpvw_add_content_in_single_product_page' ], 50 );
				} else {
					add_action( 'woocommerce_single_product_summary', [ $this, 'ddwcpvw_add_content_in_single_product_page' ], absint( $product_position ) );
				}
			}

			if ( ! empty( $ddwcpvw_configuration['shop_page_position'] ) || 'yes' === $ddwcpvw_configuration['catalog_mode'] ) {
				add_filter( 'woocommerce_loop_add_to_cart_link', [ $this, 'ddwcpvw_modify_woocommerce_loop_add_to_cart_link' ], 10, 2 );
				// Product Collection and other block grids skip the classic loop filter.
				add_filter( 'render_block_woocommerce/product-button', [ $this, 'ddwcpvw_modify_product_button_block' ], 10, 3 );
			}

			if ( 'yes' === $ddwcpvw_configuration['catalog_mode'] ) {
				add_filter( 'body_class', [ $this, 'ddwcpvw_add_catalog_body_class' ] );
				add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'ddwcpvw_block_add_to_cart' ], 10, 2 );
			}

			if ( 'yes' === $ddwcpvw_configuration['hide_price'] ) {
				add_filter( 'woocommerce_get_price_html', [ $this, 'ddwcpvw_hide_price_html' ], 99, 2 );
				// A "Sale!" badge with no price next to it only confuses.
				add_filter( 'woocommerce_sale_flash', [ $this, 'ddwcpvw_hide_sale_flash' ], 99, 3 );
			}

			add_shortcode( 'ddwcpvw_button', [ $this, 'ddwcpvw_button_shortcode' ] );

			if ( ! empty( $ddwcpvw_configuration['cart_page_position'] ) ) {
				add_action( 'woocommerce_proceed_to_checkout', [ $this, 'ddwcpvw_add_content_after_cart_totals' ], absint( $ddwcpvw_configuration['cart_page_position'] ) );
				// The Cart block does not run the classic hook above.
				add_filter( 'render_block_woocommerce/cart', [ $this, 'ddwcpvw_add_button_after_cart_block' ] );
			}

			add_action( 'wp_enqueue_scripts', [ $this, 'ddwcpvw_front_scripts' ] );

			if ( 'yes' === $ddwcpvw_configuration['thankyou_enabled'] ) {
				add_action( 'woocommerce_thankyou', [ $this, 'ddwcpvw_render_thankyou_button' ], 5 );
			}

			if ( 'yes' === $ddwcpvw_configuration['floating_enabled'] ) {
				add_action( 'wp_footer', [ $this, 'ddwcpvw_render_floating_button' ] );
			}
		}
	}
}
