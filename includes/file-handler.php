<?php
/**
 * File handler
 *
 * @author DevDiggers
 * @package DevDiggers Order via Chat for WooCommerce
 * @version 1.0.0
 */

namespace DDWCPurchaseViaWhatsApp\Includes;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCPVW_File_Handler' ) ) {
	/**
	 * File handler class
	 */
	class DDWCPVW_File_Handler {
		/**
		 * Constructor
		 */
		public function __construct() {
			$ddwcpvw_configuration = $this->ddwcpvw_set_globals();

			// Order requests reach admin-ajax.php, which counts as admin.
			new Front\DDWCPVW_Front_Ajax_Hooks( $ddwcpvw_configuration );

			// Outside the front end check below: the editor previews the block over the REST API.
			// This class is built on init, so register now rather than hooking init again.
			$this->ddwcpvw_register_block();

			if ( is_admin() ) {
				new DDWCPVW_Admin_Dashboard( $ddwcpvw_configuration );
				new Admin\DDWCPVW_Admin_Hooks( $ddwcpvw_configuration );
				new Admin\DDWCPVW_Setup_Wizard();
			} elseif ( 'yes' === $ddwcpvw_configuration['enabled'] && $ddwcpvw_configuration['whatsapp_number'] ) {
				// No number means no chat to open, so the buttons would only lead to an error.
				new Front\DDWCPVW_Front_Hooks( $ddwcpvw_configuration );
			}
		}

		/**
		 * "Order on WhatsApp" block. It renders the shortcode, so both always match.
		 *
		 * @return void
		 */
		public function ddwcpvw_register_block() {
			wp_register_script( 'ddwcpvw-block-script', DDWCPVW_PLUGIN_URL . 'assets/js/block.js', [ 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-server-side-render', 'wp-i18n' ], filemtime( DDWCPVW_PLUGIN_FILE . 'assets/js/block.js' ), true );
			wp_register_style( 'ddwcpvw-block-style', DDWCPVW_PLUGIN_URL . 'assets/css/front.css', [], filemtime( DDWCPVW_PLUGIN_FILE . 'assets/css/front.css' ) );
			wp_set_script_translations( 'ddwcpvw-block-script', 'devdiggers-order-via-chat-for-woocommerce' );

			register_block_type(
				'ddwcpvw/order-button',
				[
					'api_version'     => 3,
					'title'           => __( 'Order on WhatsApp', 'devdiggers-order-via-chat-for-woocommerce' ),
					'category'        => 'woocommerce',
					'editor_script'   => 'ddwcpvw-block-script',
					'editor_style'    => 'ddwcpvw-block-style',
					'attributes'      => [
						'productId' => [
							'type'    => 'number',
							'default' => 0,
						],
						'type'      => [
							'type'    => 'string',
							'default' => 'product',
						],
					],
					'render_callback' => function ( $attributes ) {
						if ( ! shortcode_exists( 'ddwcpvw_button' ) ) {
							return '';
						}

						return do_shortcode( sprintf( '[ddwcpvw_button id="%d" type="%s"]', absint( $attributes['productId'] ), 'cart' === $attributes['type'] ? 'cart' : 'product' ) );
					},
				]
			);
		}

		/**
		 * Set globals function
		 *
		 * @return array
		 */
		public function ddwcpvw_set_globals() {
			global $ddwcpvw_configuration;

			$button_label     = get_option( '_ddwcpvw_purchase_button_label' );
			$text_color       = get_option( '_ddwcpvw_purchase_button_text_color' );
			$background       = get_option( '_ddwcpvw_purchase_button_background_color' );
			$floating_message = get_option( '_ddwcpvw_floating_message' );
			$thankyou_label   = get_option( '_ddwcpvw_thankyou_label' );
			$request_template = get_option( '_ddwcpvw_request_template' );
			$thankyou_message = get_option( '_ddwcpvw_thankyou_template' );
			$devices          = get_option( '_ddwcpvw_button_devices' );

			// Every setting carries the value the plugin needs to work out of the box, so adding
			// a WhatsApp number is all a store has to do.
			$ddwcpvw_configuration = [
				'enabled'                          => get_option( '_ddwcpvw_enabled', 'yes' ),
				'whatsapp_number'                  => ddwcpvw_normalize_phone( get_option( '_ddwcpvw_whatsapp_number' ) ),

				// Who can order.
				'excluded_products'                => array_map( 'absint', (array) get_option( '_ddwcpvw_excluded_products', [] ) ),
				'excluded_categories'              => array_map( 'absint', (array) get_option( '_ddwcpvw_excluded_categories', [] ) ),
				'allow_guests'                     => get_option( '_ddwcpvw_allow_guests', 'yes' ),
				'hide_out_of_stock'                => get_option( '_ddwcpvw_hide_out_of_stock', 'yes' ),

				// Catalog mode: WhatsApp becomes the only way to buy the products above.
				'catalog_mode'                     => get_option( '_ddwcpvw_catalog_mode' ),
				'hide_price'                       => get_option( '_ddwcpvw_hide_price' ),

				// Messages.
				'request_template'                 => $request_template ? $request_template : ddwcpvw_get_default_template( 'request' ),
				'thankyou_template'                => $thankyou_message ? $thankyou_message : ddwcpvw_get_default_template( 'thankyou' ),
				'tracking'                         => get_option( '_ddwcpvw_tracking' ),

				// Storefront.
				'purchase_button_label'            => $button_label ? $button_label : esc_html__( 'Order on WhatsApp', 'devdiggers-order-via-chat-for-woocommerce' ),
				'purchase_button_text_color'       => $text_color ? $text_color : '#ffffff',
				'purchase_button_background_color' => $background ? $background : '#25d366',
				'button_show_icon'                 => get_option( '_ddwcpvw_button_show_icon', 'yes' ),
				'button_radius'                    => absint( get_option( '_ddwcpvw_button_radius', 6 ) ),
				'button_full_width'                => get_option( '_ddwcpvw_button_full_width' ),
				'button_devices'                   => in_array( $devices, [ 'mobile', 'desktop' ], true ) ? $devices : 'all',
				'shop_page_position'               => get_option( '_ddwcpvw_shop_page_position', '' ),
				'product_page_position'            => get_option( '_ddwcpvw_product_page_position', '55' ),
				'cart_page_position'               => get_option( '_ddwcpvw_cart_page_position', '20' ),

				// Order received page.
				'thankyou_enabled'                 => get_option( '_ddwcpvw_thankyou_enabled', 'yes' ),
				'thankyou_label'                   => $thankyou_label ? $thankyou_label : esc_html__( 'Send my order on WhatsApp', 'devdiggers-order-via-chat-for-woocommerce' ),

				// Floating chat button.
				'floating_enabled'                 => get_option( '_ddwcpvw_floating_enabled' ),
				'floating_number'                  => get_option( '_ddwcpvw_floating_number' ),
				'floating_message'                 => $floating_message ? $floating_message : esc_html__( 'Hi! I have a question about your store.', 'devdiggers-order-via-chat-for-woocommerce' ),
				'floating_label'                   => get_option( '_ddwcpvw_floating_label' ),
				'floating_position'                => 'left' === get_option( '_ddwcpvw_floating_position' ) ? 'left' : 'right',
				'floating_pages'                   => 'woocommerce' === get_option( '_ddwcpvw_floating_pages' ) ? 'woocommerce' : 'all',
				'floating_hide_mobile'             => get_option( '_ddwcpvw_floating_hide_mobile' ),
			];

			return $ddwcpvw_configuration;
		}
	}
}
