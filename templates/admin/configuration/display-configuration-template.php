<?php
/**
 * Display configuration: how the WhatsApp button looks and where it appears.
 *
 * @package Order via Chat for WooCommerce
 * @version 1.0.0
 */

namespace DDWCPurchaseViaWhatsApp\Templates\Admin\Configuration;

use DevDiggers\Framework\Includes\DDFW_Layout;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCPVW_Display_Configuration_Template' ) ) {
	/**
	 * Display configuration template.
	 */
	class DDWCPVW_Display_Configuration_Template {
		/**
		 * Render settings.
		 *
		 * @param array $configuration Plugin configuration.
		 */
		public function __construct( $configuration ) {
			$sections = [
				[
					'header' => [
						'heading'     => esc_html__( 'Button Placement', 'order-via-chat-for-woocommerce' ),
						'description' => esc_html__( 'Choose the pages where customers see the WhatsApp button.', 'order-via-chat-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'        => 'select',
							'label'       => esc_html__( 'Product Page', 'order-via-chat-for-woocommerce' ),
							'description' => esc_html__( 'Most orders start here. The default spot sits at the end of the product summary, below the add to cart area.', 'order-via-chat-for-woocommerce' ),
							'id'          => 'ddwcpvw-product-page-position',
							'name'        => '_ddwcpvw_product_page_position',
							'value'       => $configuration['product_page_position'],
							'options'     => ddwcpvw_get_product_position_options(),
						],
						[
							'type'        => 'select',
							'label'       => esc_html__( 'Cart Page', 'order-via-chat-for-woocommerce' ),
							'description' => esc_html__( 'Lets customers send their whole cart to you in one message.', 'order-via-chat-for-woocommerce' ),
							'id'          => 'ddwcpvw-cart-page-position',
							'name'        => '_ddwcpvw_cart_page_position',
							'value'       => $configuration['cart_page_position'],
							'options'     => ddwcpvw_get_cart_position_options(),
						],
						[
							'type'        => 'select',
							'label'       => esc_html__( 'Shop and Category Pages', 'order-via-chat-for-woocommerce' ),
							'description' => esc_html__( 'Shown on simple products only, because variable products need options picked on the product page first.', 'order-via-chat-for-woocommerce' ),
							'id'          => 'ddwcpvw-shop-page-position',
							'name'        => '_ddwcpvw_shop_page_position',
							'value'       => $configuration['shop_page_position'],
							'options'     => ddwcpvw_get_shop_position_options(),
						],
						[
							'type'  => 'field_html',
							'label' => esc_html__( 'Anywhere Else', 'order-via-chat-for-woocommerce' ),
							'html'  => '<p class="description">' . wp_kses_post( __( 'Add the <strong>Order on WhatsApp</strong> block in the editor, or use the shortcode <code>[ddwcpvw_button]</code> in any page builder. Pass <code>id="123"</code> for a specific product or <code>type="cart"</code> to send the whole cart.', 'order-via-chat-for-woocommerce' ) ) . '</p>',
						],
					],
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Button Style', 'order-via-chat-for-woocommerce' ),
						'description' => esc_html__( 'Match the button to your theme. The preview updates as you change the settings.', 'order-via-chat-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'  => 'field_html',
							'label' => esc_html__( 'Preview', 'order-via-chat-for-woocommerce' ),
							'html'  => $this->ddwcpvw_get_preview( $configuration ),
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Button Text', 'order-via-chat-for-woocommerce' ),
							'description' => esc_html__( 'Short and clear works best, for example "Order on WhatsApp".', 'order-via-chat-for-woocommerce' ),
							'id'          => 'ddwcpvw-purchase-button-label',
							'name'        => '_ddwcpvw_purchase_button_label',
							'value'       => $configuration['purchase_button_label'],
						],
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'WhatsApp Icon', 'order-via-chat-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Show the WhatsApp logo before the text', 'order-via-chat-for-woocommerce' ),
							'description'    => esc_html__( 'The familiar logo tells shoppers at a glance what the button does.', 'order-via-chat-for-woocommerce' ),
							'id'             => 'ddwcpvw-button-show-icon',
							'name'           => '_ddwcpvw_button_show_icon',
							'value'          => $configuration['button_show_icon'],
						],
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Background Color', 'order-via-chat-for-woocommerce' ),
							'description' => esc_html__( 'WhatsApp green is #25D366 if you want the button to feel instantly recognisable.', 'order-via-chat-for-woocommerce' ),
							'id'          => 'ddwcpvw-purchase-button-background-color',
							'name'        => '_ddwcpvw_purchase_button_background_color',
							'value'       => $configuration['purchase_button_background_color'],
						],
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Text Color', 'order-via-chat-for-woocommerce' ),
							'description' => esc_html__( 'Pick a color with strong contrast against the background so the text stays easy to read.', 'order-via-chat-for-woocommerce' ),
							'id'          => 'ddwcpvw-purchase-button-text-color',
							'name'        => '_ddwcpvw_purchase_button_text_color',
							'value'       => $configuration['purchase_button_text_color'],
						],
						[
							'type'              => 'number',
							'label'             => esc_html__( 'Corner Roundness', 'order-via-chat-for-woocommerce' ),
							'description'       => esc_html__( 'In pixels. Use 0 for square corners or 50 for a pill shape.', 'order-via-chat-for-woocommerce' ),
							'id'                => 'ddwcpvw-button-radius',
							'name'              => '_ddwcpvw_button_radius',
							'value'             => $configuration['button_radius'],
							'custom_attributes' => [
								'min'  => 0,
								'max'  => 50,
								'step' => 1,
							],
						],
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Full Width', 'order-via-chat-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Stretch the button across the product summary', 'order-via-chat-for-woocommerce' ),
							'description'    => esc_html__( 'A bigger tap target that stands out on phones. The cart page button is always full width.', 'order-via-chat-for-woocommerce' ),
							'id'             => 'ddwcpvw-button-full-width',
							'name'           => '_ddwcpvw_button_full_width',
							'value'          => $configuration['button_full_width'],
						],
						[
							'type'        => 'select',
							'label'       => esc_html__( 'Devices', 'order-via-chat-for-woocommerce' ),
							'description' => esc_html__( 'Most WhatsApp orders come from phones. Choose "Phones and tablets only" to keep the desktop layout unchanged.', 'order-via-chat-for-woocommerce' ),
							'id'          => 'ddwcpvw-button-devices',
							'name'        => '_ddwcpvw_button_devices',
							'value'       => $configuration['button_devices'],
							'options'     => [
								'all'     => esc_html__( 'All devices', 'order-via-chat-for-woocommerce' ),
								'mobile'  => esc_html__( 'Phones and tablets only', 'order-via-chat-for-woocommerce' ),
								'desktop' => esc_html__( 'Desktop only', 'order-via-chat-for-woocommerce' ),
							],
						],
					],
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Floating Chat Button', 'order-via-chat-for-woocommerce' ),
						'description' => esc_html__( 'A round WhatsApp bubble in the corner of every page, so shoppers can ask a question before they buy. Questions answered quickly turn into orders.', 'order-via-chat-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Chat Bubble', 'order-via-chat-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Show a floating WhatsApp chat button', 'order-via-chat-for-woocommerce' ),
							'description'    => esc_html__( 'It opens a normal WhatsApp chat with your team. It uses the button colors above, so it always matches.', 'order-via-chat-for-woocommerce' ),
							'id'             => 'ddwcpvw-floating-enabled',
							'name'           => '_ddwcpvw_floating_enabled',
							'value'          => $configuration['floating_enabled'],
						],
						[
							'type'        => 'tel',
							'label'       => esc_html__( 'Chat Number', 'order-via-chat-for-woocommerce' ),
							'description' => esc_html__( 'Where questions go. Leave empty to use your WhatsApp number from the General tab, or add the number your support team uses.', 'order-via-chat-for-woocommerce' ),
							'id'          => 'ddwcpvw-floating-number',
							'name'        => '_ddwcpvw_floating_number',
							'value'       => $configuration['floating_number'],
							'placeholder' => $configuration['whatsapp_number'],
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Prefilled Message', 'order-via-chat-for-woocommerce' ),
							'description' => esc_html__( 'Already typed in when the chat opens, so the shopper only taps send. On a product page the product name and link are added for you.', 'order-via-chat-for-woocommerce' ),
							'id'          => 'ddwcpvw-floating-message',
							'name'        => '_ddwcpvw_floating_message',
							'value'       => $configuration['floating_message'],
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Bubble Text', 'order-via-chat-for-woocommerce' ),
							'description' => esc_html__( 'Optional. A short line next to the icon, like "Need help? Chat with us". Leave empty for an icon only bubble.', 'order-via-chat-for-woocommerce' ),
							'id'          => 'ddwcpvw-floating-label',
							'name'        => '_ddwcpvw_floating_label',
							'value'       => $configuration['floating_label'],
						],
						[
							'type'        => 'select',
							'label'       => esc_html__( 'Position', 'order-via-chat-for-woocommerce' ),
							'description' => esc_html__( 'Pick the corner that does not clash with your theme, a cookie banner or a live chat widget.', 'order-via-chat-for-woocommerce' ),
							'id'          => 'ddwcpvw-floating-position',
							'name'        => '_ddwcpvw_floating_position',
							'value'       => $configuration['floating_position'],
							'options'     => [
								'right' => esc_html__( 'Bottom right', 'order-via-chat-for-woocommerce' ),
								'left'  => esc_html__( 'Bottom left', 'order-via-chat-for-woocommerce' ),
							],
						],
						[
							'type'        => 'select',
							'label'       => esc_html__( 'Show On', 'order-via-chat-for-woocommerce' ),
							'description' => esc_html__( 'Every page, or only the shop, product, cart and checkout pages.', 'order-via-chat-for-woocommerce' ),
							'id'          => 'ddwcpvw-floating-pages',
							'name'        => '_ddwcpvw_floating_pages',
							'value'       => $configuration['floating_pages'],
							'options'     => [
								'all'         => esc_html__( 'All pages', 'order-via-chat-for-woocommerce' ),
								'woocommerce' => esc_html__( 'Store pages only', 'order-via-chat-for-woocommerce' ),
							],
						],
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Phones', 'order-via-chat-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Hide the bubble on small screens', 'order-via-chat-for-woocommerce' ),
							'description'    => esc_html__( 'Handy if your mobile theme already has a sticky bar at the bottom.', 'order-via-chat-for-woocommerce' ),
							'id'             => 'ddwcpvw-floating-hide-mobile',
							'name'           => '_ddwcpvw_floating_hide_mobile',
							'value'          => $configuration['floating_hide_mobile'],
						],
					],
				],
			];

			$layout = new DDFW_Layout();
			$layout->get_form_section_layout( $sections, 'ddwcpvw-display-configuration-fields' );
		}

		/**
		 * Live preview of the storefront button.
		 *
		 * @param array $configuration Plugin configuration.
		 * @return string
		 */
		protected function ddwcpvw_get_preview( $configuration ) {
			$style = sprintf(
				'background-color:%1$s;color:%2$s;border-radius:%3$dpx;',
				esc_attr( $configuration['purchase_button_background_color'] ),
				esc_attr( $configuration['purchase_button_text_color'] ),
				absint( $configuration['button_radius'] )
			);

			return '<div class="ddwcpvw-button-preview-wrap"><span class="ddwcpvw-button-preview' . ( 'yes' === $configuration['button_full_width'] ? ' ddwcpvw-full-width' : '' ) . '" style="' . $style . '">'
				. '<span class="ddwcpvw-button-preview-icon' . ( 'yes' === $configuration['button_show_icon'] ? '' : ' ddwcpvw-hide' ) . '">' . ddwcpvw_get_whatsapp_icon() . '</span>'
				. '<span class="ddwcpvw-button-preview-label">' . esc_html( $configuration['purchase_button_label'] ) . '</span>'
				. '</span></div>';
		}
	}
}
