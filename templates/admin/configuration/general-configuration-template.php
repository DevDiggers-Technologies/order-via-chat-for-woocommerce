<?php
/**
 * General configuration.
 *
 * @package Order via Chat for WooCommerce
 * @version 1.0.0
 */

namespace DDWCPurchaseViaWhatsApp\Templates\Admin\Configuration;

use DevDiggers\Framework\Includes\DDFW_Layout;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCPVW_General_Configuration_Template' ) ) {
	/**
	 * General configuration template.
	 */
	class DDWCPVW_General_Configuration_Template {
		/**
		 * Render settings.
		 *
		 * @param array $configuration Plugin configuration.
		 */
		public function __construct( $configuration ) {
			$sections = [
				[
					'header' => [
						'heading'     => esc_html__( 'Plugin Status', 'order-via-chat-for-woocommerce' ),
						'description' => esc_html__( 'Switch WhatsApp ordering on or off for your whole store.', 'order-via-chat-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Status', 'order-via-chat-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Enable Order via Chat for WooCommerce', 'order-via-chat-for-woocommerce' ),
							'description'    => esc_html__( 'When this is off, the WhatsApp buttons disappear from your store. Your settings stay exactly as they are.', 'order-via-chat-for-woocommerce' ),
							'id'             => 'ddwcpvw-enabled',
							'name'           => '_ddwcpvw_enabled',
							'value'          => $configuration['enabled'],
						],
						[
							'type'        => 'tel',
							'label'       => esc_html__( 'Your WhatsApp Number', 'order-via-chat-for-woocommerce' ),
							'description' => esc_html__( 'Where order requests are sent, with the country code. It can be your personal WhatsApp or a WhatsApp Business app number. The buttons stay hidden until this is filled in.', 'order-via-chat-for-woocommerce' ),
							'id'          => 'ddwcpvw-whatsapp-number',
							'name'        => '_ddwcpvw_whatsapp_number',
							'value'       => $configuration['whatsapp_number'],
							'placeholder' => '+15551234567',
						],
					],
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Who Can Order', 'order-via-chat-for-woocommerce' ),
						'description' => esc_html__( 'Decide which products and customers can use WhatsApp ordering.', 'order-via-chat-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Guest Customers', 'order-via-chat-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Let visitors order without an account', 'order-via-chat-for-woocommerce' ),
							'description'    => esc_html__( 'Guests fill in their delivery details in a short popup before WhatsApp opens. Turn this off to ask them to log in first.', 'order-via-chat-for-woocommerce' ),
							'id'             => 'ddwcpvw-allow-guests',
							'name'           => '_ddwcpvw_allow_guests',
							'value'          => $configuration['allow_guests'],
						],
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Out of Stock Products', 'order-via-chat-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Hide the button when a product is out of stock', 'order-via-chat-for-woocommerce' ),
							'description'    => esc_html__( 'Saves customers from starting a chat for something you cannot send them.', 'order-via-chat-for-woocommerce' ),
							'id'             => 'ddwcpvw-hide-out-of-stock',
							'name'           => '_ddwcpvw_hide_out_of_stock',
							'value'          => $configuration['hide_out_of_stock'],
						],
						[
							'type'              => 'products',
							'label'             => esc_html__( 'Excluded Products', 'order-via-chat-for-woocommerce' ),
							'description'       => esc_html__( 'These products keep their normal checkout only. Handy for items that need a custom quote or special handling.', 'order-via-chat-for-woocommerce' ),
							'id'                => 'ddwcpvw-excluded-products',
							'name'              => '_ddwcpvw_excluded_products[]',
							'value'             => $configuration['excluded_products'],
							'custom_attributes' => [
								'multiple' => 'multiple',
							],
						],
						[
							'type'              => 'categories',
							'label'             => esc_html__( 'Excluded Categories', 'order-via-chat-for-woocommerce' ),
							'description'       => esc_html__( 'Every product in these categories keeps its normal checkout only.', 'order-via-chat-for-woocommerce' ),
							'id'                => 'ddwcpvw-excluded-categories',
							'name'              => '_ddwcpvw_excluded_categories[]',
							'value'             => $configuration['excluded_categories'],
							'custom_attributes' => [
								'multiple' => 'multiple',
							],
						],
					],
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Catalog Mode', 'order-via-chat-for-woocommerce' ),
						'description' => esc_html__( 'Take orders on WhatsApp only. Products you excluded above keep their normal checkout.', 'order-via-chat-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'WhatsApp Only', 'order-via-chat-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Replace Add to Cart with the WhatsApp button', 'order-via-chat-for-woocommerce' ),
							'description'    => esc_html__( 'Customers still pick options and quantity on the product page, then send the order on WhatsApp. Adding these products to the cart is blocked.', 'order-via-chat-for-woocommerce' ),
							'id'             => 'ddwcpvw-catalog-mode',
							'name'           => '_ddwcpvw_catalog_mode',
							'value'          => $configuration['catalog_mode'],
						],
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Prices', 'order-via-chat-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Hide prices and quote them in the chat', 'order-via-chat-for-woocommerce' ),
							'description'    => esc_html__( 'For wholesale, custom or "ask for price" products. Prices are left out of the WhatsApp message too.', 'order-via-chat-for-woocommerce' ),
							'id'             => 'ddwcpvw-hide-price',
							'name'           => '_ddwcpvw_hide_price',
							'value'          => $configuration['hide_price'],
						],
					],
				],
				[
					'header' => [
						'heading'     => esc_html__( 'WhatsApp Messages', 'order-via-chat-for-woocommerce' ),
						'description' => esc_html__( 'The text customers send you. Lines whose tag is empty are left out on their own.', 'order-via-chat-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'              => 'textarea',
							'label'             => esc_html__( 'Order Request', 'order-via-chat-for-woocommerce' ),
							'description'       => ddwcpvw_get_template_tags_help( [ '{items}', '{subtotal}', '{customer_name}', '{customer_phone}', '{customer_email}', '{address}', '{site_name}' ] ),
							'id'                => 'ddwcpvw-request-template',
							'name'              => '_ddwcpvw_request_template',
							'value'             => $configuration['request_template'],
							'custom_attributes' => [
								'rows' => 7,
							],
						],
						[
							'type'              => 'textarea',
							'label'             => esc_html__( 'Order Received Page', 'order-via-chat-for-woocommerce' ),
							'description'       => ddwcpvw_get_template_tags_help( [ '{order_number}', '{order_total}', '{order_summary}', '{customer_name}', '{site_name}' ] ),
							'id'                => 'ddwcpvw-thankyou-template',
							'name'              => '_ddwcpvw_thankyou_template',
							'value'             => $configuration['thankyou_template'],
							'custom_attributes' => [
								'rows' => 5,
							],
						],
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Analytics Events', 'order-via-chat-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Report WhatsApp orders to Google Analytics, Tag Manager and Meta Pixel', 'order-via-chat-for-woocommerce' ),
							'description'    => esc_html__( 'Sends a generate_lead event (and a Pixel Contact event) when a customer opens WhatsApp with an order. Uses the tracking code already on your site; nothing is sent when there is none.', 'order-via-chat-for-woocommerce' ),
							'id'             => 'ddwcpvw-tracking',
							'name'           => '_ddwcpvw_tracking',
							'value'          => $configuration['tracking'],
						],
					],
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Order Received Page', 'order-via-chat-for-woocommerce' ),
						'description' => esc_html__( 'For customers who check out the normal way. They can send you the finished order on WhatsApp in one tap.', 'order-via-chat-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Send Order Button', 'order-via-chat-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Show a WhatsApp button on the order received page', 'order-via-chat-for-woocommerce' ),
							'description'    => esc_html__( 'The message holds the order number, items, shipping, payment method and total, so you can confirm it in the chat.', 'order-via-chat-for-woocommerce' ),
							'id'             => 'ddwcpvw-thankyou-enabled',
							'name'           => '_ddwcpvw_thankyou_enabled',
							'value'          => $configuration['thankyou_enabled'],
						],
						[
							'type'  => 'text',
							'label' => esc_html__( 'Button Text', 'order-via-chat-for-woocommerce' ),
							'id'    => 'ddwcpvw-thankyou-label',
							'name'  => '_ddwcpvw_thankyou_label',
							'value' => $configuration['thankyou_label'],
						],
					],
				],
			];

			$layout = new DDFW_Layout();
			$layout->get_form_section_layout( $sections, 'ddwcpvw-general-configuration-fields' );
		}
	}
}
