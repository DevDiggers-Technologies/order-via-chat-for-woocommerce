<?php
/**
 * This file handles all admin dashboard functionalities.
 *
 * @package DevDiggers Order via Chat for WooCommerce
 * @version 1.0.0
 */

namespace DDWCPurchaseViaWhatsApp\Includes;

use DDWCPurchaseViaWhatsApp\Templates\Admin;
use DevDiggers\Framework\Includes\DDFW_Assets;
use DevDiggers\Framework\Includes\DDFW_Plugin_Dashboard;
use DevDiggers\Framework\Includes\DDFW_SVG;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCPVW_Admin_Dashboard' ) ) {
	/**
	 * Admin Dashboard Class
	 */
	class DDWCPVW_Admin_Dashboard {
		/**
		 * Configuration Variable
		 *
		 * @var array
		 */
		protected $ddwcpvw_configuration;

		/**
		 * Dashboard Variable
		 *
		 * @var DDFW_Plugin_Dashboard
		 */
		protected $dashboard;

		/**
		 * Construct
		 *
		 * @param array $ddwcpvw_configuration Configuration.
		 */
		public function __construct( $ddwcpvw_configuration ) {
			$this->ddwcpvw_configuration = $ddwcpvw_configuration;

			add_action( 'admin_enqueue_scripts', [ $this, 'ddwcpvw_enqueue_admin_scripts' ] );
			add_filter( 'ddfw_modify_svg_icons', [ $this, 'ddwcpvw_add_svg_icons' ], 10, 2 );
			add_filter( 'admin_footer_text', [ $this, 'ddwcpvw_set_admin_footer_text' ], 99 );

			$this->ddwcpvw_add_dashboard_menu();
		}

		/**
		 * Add Admin menu function
		 *
		 * @return void
		 */
		public function ddwcpvw_add_dashboard_menu() {
			ob_start();
			echo wp_kses( ddwcpvw_get_brand_mark(), ddfw_kses_allowed_svg_tags() );
			esc_html_e( 'Order via Chat', 'devdiggers-order-via-chat-for-woocommerce' );
			$plugin_name = ob_get_clean();

			$this->dashboard = new DDFW_Plugin_Dashboard(
				[
					'page_title'              => esc_html__( 'Order via Chat', 'devdiggers-order-via-chat-for-woocommerce' ),
					'menu_title'              => esc_html__( 'Order via Chat', 'devdiggers-order-via-chat-for-woocommerce' ),
					'slug'                    => 'ddwcpvw-dashboard',
					'plugin_name'             => $plugin_name,
					'upgrade_url'             => 'https://devdiggers.com/product/woocommerce-purchase-via-whatsapp/',
					'menus'                   => [
						'dashboard'     => [
							'label'    => esc_html__( 'Dashboard', 'devdiggers-order-via-chat-for-woocommerce' ),
							'layout'   => 'full-width',
							'callback' => [ $this, 'ddwcpvw_get_dashboard_template' ],
						],
						'orders'        => [
							'label'    => esc_html__( 'Orders', 'devdiggers-order-via-chat-for-woocommerce' ),
							'layout'   => 'full-width',
							'callback' => [ $this, 'ddwcpvw_get_orders_template' ],
						],
						'messages'      => [
							'label'    => esc_html__( 'Messages', 'devdiggers-order-via-chat-for-woocommerce' ),
							'layout'   => 'full-width',
							'callback' => [ $this, 'ddwcpvw_get_messages_template' ],
						],
						'broadcast'     => [
							'label'    => esc_html__( 'Broadcast', 'devdiggers-order-via-chat-for-woocommerce' ),
							'layout'   => 'full-width',
							'callback' => [ $this, 'ddwcpvw_get_broadcast_message_template' ],
						],
						'configuration' => [
							'label'  => esc_html__( 'Configuration', 'devdiggers-order-via-chat-for-woocommerce' ),
							'layout' => 'sidebar',
							'tabs'   => [
								'general'       => [
									'label'    => esc_html__( 'General', 'devdiggers-order-via-chat-for-woocommerce' ),
									'icon'     => DDFW_SVG::get_svg_icon( 'general', true, [ 'size' => 18 ] ),
									'callback' => [ $this, 'ddwcpvw_get_general_configuration_template' ],
								],
								'display'       => [
									'label'    => esc_html__( 'Display', 'devdiggers-order-via-chat-for-woocommerce' ),
									'icon'     => DDFW_SVG::get_svg_icon( 'ddwcpvw-display', true, [ 'size' => 18 ] ),
									'callback' => [ $this, 'ddwcpvw_get_display_configuration_template' ],
								],
								'twilio'        => [
									'label'    => esc_html__( 'Twilio', 'devdiggers-order-via-chat-for-woocommerce' ),
									'icon'     => DDFW_SVG::get_svg_icon( 'ddwcpvw-twilio', true, [ 'size' => 18 ] ),
									'callback' => [ $this, 'ddwcpvw_get_twilio_configuration_template' ],
								],
								'chat'          => [
									'label'    => esc_html__( 'Chat Assistant', 'devdiggers-order-via-chat-for-woocommerce' ),
									'icon'     => DDFW_SVG::get_svg_icon( 'ddwcpvw-chat', true, [ 'size' => 18 ] ),
									'callback' => [ $this, 'ddwcpvw_get_chat_configuration_template' ],
								],
								'notifications' => [
									'label'    => esc_html__( 'Notifications', 'devdiggers-order-via-chat-for-woocommerce' ),
									'icon'     => DDFW_SVG::get_svg_icon( 'ddwcpvw-bell', true, [ 'size' => 18 ] ),
									'callback' => [ $this, 'ddwcpvw_get_notifications_configuration_template' ],
								],
								'wallet'        => [
									'label'    => esc_html__( 'Wallet', 'devdiggers-order-via-chat-for-woocommerce' ),
									'icon'     => DDFW_SVG::get_svg_icon( 'ddwcpvw-wallet', true, [ 'size' => 18 ] ),
									'callback' => [ $this, 'ddwcpvw_get_wallet_configuration_template' ],
								],
							],
						],
					],
				]
			);
		}

		/**
		 * Dashboard menu.
		 *
		 * @return void
		 */
		public function ddwcpvw_get_dashboard_template() {
			new Admin\Dashboard\DDWCPVW_Dashboard_Template( $this->ddwcpvw_configuration );
		}

		/**
		 * Orders menu: an introduction to chat-created orders.
		 *
		 * @return void
		 */
		public function ddwcpvw_get_orders_template() {
			$this->ddwcpvw_upgrade_card(
				'orders',
				esc_html__( 'Turn every chat into a real WooCommerce order with Pro', 'devdiggers-order-via-chat-for-woocommerce' ),
				esc_html__( 'In Free the customer sends you their cart and you finish the sale by hand. Pro connects your number through Twilio, so a chat assistant places the order for you while the customer is still in WhatsApp.', 'devdiggers-order-via-chat-for-woocommerce' ),
				[
					esc_html__( 'The customer picks shipping and payment in the chat, and the order is created in WooCommerce on the spot', 'devdiggers-order-via-chat-for-woocommerce' ),
					esc_html__( 'Cash on delivery, bank transfer and cheque confirmed in the chat, a secure payment link for cards', 'devdiggers-order-via-chat-for-woocommerce' ),
					esc_html__( 'Stock, coupons, taxes and shipping rules applied exactly as at your checkout', 'devdiggers-order-via-chat-for-woocommerce' ),
					esc_html__( 'A list of every WhatsApp order with revenue, status and the customer behind it', 'devdiggers-order-via-chat-for-woocommerce' ),
				]
			);
		}

		/**
		 * Messages menu.
		 *
		 * @return void
		 */
		public function ddwcpvw_get_messages_template() {
			$this->ddwcpvw_upgrade_card(
				'messages',
				esc_html__( 'See every WhatsApp message and whether it arrived, with Pro', 'devdiggers-order-via-chat-for-woocommerce' ),
				esc_html__( 'Pro keeps a log of every message your store sends and receives, with the delivery status WhatsApp reports back.', 'devdiggers-order-via-chat-for-woocommerce' ),
				[
					esc_html__( 'Sent, delivered, read and failed, for every message', 'devdiggers-order-via-chat-for-woocommerce' ),
					esc_html__( 'Plain language reasons for a failed message, so you know what to fix', 'devdiggers-order-via-chat-for-woocommerce' ),
					esc_html__( 'Search, filter and export the log to CSV', 'devdiggers-order-via-chat-for-woocommerce' ),
					esc_html__( 'Automatic clean up after the number of days you choose', 'devdiggers-order-via-chat-for-woocommerce' ),
				]
			);
		}

		/**
		 * Broadcast menu.
		 *
		 * @return void
		 */
		public function ddwcpvw_get_broadcast_message_template() {
			$this->ddwcpvw_upgrade_card(
				'broadcast',
				esc_html__( 'Message your customers in bulk on WhatsApp with Pro', 'devdiggers-order-via-chat-for-woocommerce' ),
				esc_html__( 'Announce a sale, a restock or a new arrival to the customers who want to hear it, straight to the app they read first.', 'devdiggers-order-via-chat-for-woocommerce' ),
				[
					esc_html__( 'Send to all customers, recent buyers, buyers of a product, or hand picked people', 'devdiggers-order-via-chat-for-woocommerce' ),
					esc_html__( 'Text, an image, or an approved WhatsApp template, with a live phone preview', 'devdiggers-order-via-chat-for-woocommerce' ),
					esc_html__( 'Customers who opted out are skipped automatically', 'devdiggers-order-via-chat-for-woocommerce' ),
					esc_html__( 'A delivery report when the broadcast is done', 'devdiggers-order-via-chat-for-woocommerce' ),
				]
			);
		}

		/**
		 * Twilio tab.
		 *
		 * @return void
		 */
		public function ddwcpvw_get_twilio_configuration_template() {
			$this->ddwcpvw_upgrade_card(
				'twilio',
				esc_html__( 'Send WhatsApp messages from your store with Pro', 'devdiggers-order-via-chat-for-woocommerce' ),
				esc_html__( 'Free opens WhatsApp on the customer\'s phone. Pro connects your number to the official WhatsApp Business Platform through Twilio, so your store can message customers by itself.', 'devdiggers-order-via-chat-for-woocommerce' ),
				[
					esc_html__( 'Connect with your Account SID and Auth Token, or a revocable API key', 'devdiggers-order-via-chat-for-woocommerce' ),
					esc_html__( 'A one click connection test and a test message to your own phone', 'devdiggers-order-via-chat-for-woocommerce' ),
					esc_html__( 'Every incoming message checked against Twilio\'s signature', 'devdiggers-order-via-chat-for-woocommerce' ),
				]
			);
		}

		/**
		 * Chat Assistant tab.
		 *
		 * @return void
		 */
		public function ddwcpvw_get_chat_configuration_template() {
			$this->ddwcpvw_upgrade_card(
				'chat-assistant',
				esc_html__( 'Let a chat assistant take the order for you, with Pro', 'devdiggers-order-via-chat-for-woocommerce' ),
				esc_html__( 'Pro answers the customer in WhatsApp, walks them through shipping and payment, and places the order without you typing a word.', 'devdiggers-order-via-chat-for-woocommerce' ),
				[
					esc_html__( 'Choose which payment methods the chat offers', 'devdiggers-order-via-chat-for-woocommerce' ),
					esc_html__( 'Customers reply STATUS for their latest order, HELP for options, AGENT for a person', 'devdiggers-order-via-chat-for-woocommerce' ),
					esc_html__( 'Every reply worded your way', 'devdiggers-order-via-chat-for-woocommerce' ),
					esc_html__( 'Unfinished requests expire on their own', 'devdiggers-order-via-chat-for-woocommerce' ),
				]
			);
		}

		/**
		 * Notifications tab.
		 *
		 * @return void
		 */
		public function ddwcpvw_get_notifications_configuration_template() {
			$this->ddwcpvw_upgrade_card(
				'notifications',
				esc_html__( 'Send order updates and win back lost sales on WhatsApp, with Pro', 'devdiggers-order-via-chat-for-woocommerce' ),
				esc_html__( 'Customers read WhatsApp in minutes, not days. Pro keeps them in the loop automatically and nudges the ones who did not finish.', 'devdiggers-order-via-chat-for-woocommerce' ),
				[
					esc_html__( 'Order confirmation and status updates, with your own wording per status', 'devdiggers-order-via-chat-for-woocommerce' ),
					esc_html__( 'A new order alert on your own phone', 'devdiggers-order-via-chat-for-woocommerce' ),
					esc_html__( 'Reminders for unfinished chats and unpaid orders, with a payment link', 'devdiggers-order-via-chat-for-woocommerce' ),
					esc_html__( 'A WhatsApp opt in at checkout, and STOP and START handled for you', 'devdiggers-order-via-chat-for-woocommerce' ),
					esc_html__( 'Approved WhatsApp templates, so updates arrive after the 24 hour window', 'devdiggers-order-via-chat-for-woocommerce' ),
				]
			);
		}

		/**
		 * Wallet tab.
		 *
		 * @return void
		 */
		public function ddwcpvw_get_wallet_configuration_template() {
			$this->ddwcpvw_upgrade_card(
				'wallet',
				esc_html__( 'Let customers pay from their store wallet in the chat, with Pro', 'devdiggers-order-via-chat-for-woocommerce' ),
				esc_html__( 'Pro works with DevDiggers Wallet, so customers can check their balance and pay for an order without leaving WhatsApp.', 'devdiggers-order-via-chat-for-woocommerce' ),
				[
					esc_html__( 'Customers reply WALLET to see their balance', 'devdiggers-order-via-chat-for-woocommerce' ),
					esc_html__( 'Pay for the order from the wallet, right in the chat', 'devdiggers-order-via-chat-for-woocommerce' ),
					esc_html__( 'A WhatsApp message whenever money is added to or taken from the wallet', 'devdiggers-order-via-chat-for-woocommerce' ),
				]
			);
		}

		/**
		 * One full-screen upgrade card.
		 *
		 * @param string $image    Screenshot name.
		 * @param string $heading  Heading.
		 * @param string $text     Description.
		 * @param array  $features Feature bullets.
		 * @return void
		 */
		protected function ddwcpvw_upgrade_card( $image, $heading, $text, $features ) {
			ddfw_upgrade_to_pro_section(
				[
					'image_url'     => ddwcpvw_get_pro_image( $image ),
					'heading'       => $heading,
					'description'   => $text,
					'list_features' => $features,
					'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-purchase-via-whatsapp/',
				]
			);
		}

		/**
		 * Get General Configuration Template
		 *
		 * @return void
		 */
		public function ddwcpvw_get_general_configuration_template() {
			new Admin\Configuration\DDWCPVW_General_Configuration_Template( $this->ddwcpvw_configuration );
		}

		/**
		 * Get Display Configuration Template
		 *
		 * @return void
		 */
		public function ddwcpvw_get_display_configuration_template() {
			new Admin\Configuration\DDWCPVW_Display_Configuration_Template( $this->ddwcpvw_configuration );
		}

		/**
		 * Enqueue admin scripts function
		 *
		 * @return void
		 */
		public function ddwcpvw_enqueue_admin_scripts() {
			if ( ! $this->dashboard->is_a_plugin_page() ) {
				return;
			}

			wp_enqueue_style( 'ddwcpvw-admin-style', DDWCPVW_PLUGIN_URL . 'assets/css/admin.css', [ DDFW_Assets::$framework_css_handle ], filemtime( DDWCPVW_PLUGIN_FILE . 'assets/css/admin.css' ) );
			wp_enqueue_script( 'ddwcpvw-admin-script', DDWCPVW_PLUGIN_URL . 'assets/js/admin.js', [ DDFW_Assets::$framework_js_handle ], filemtime( DDWCPVW_PLUGIN_FILE . 'assets/js/admin.js' ), true );
		}

		/**
		 * Thank-you footer on the plugin's own screens.
		 *
		 * @param string $footer_text Default footer text.
		 * @return string
		 */
		public function ddwcpvw_set_admin_footer_text( $footer_text ) {
			if ( ! $this->dashboard->is_a_plugin_page() ) {
				return $footer_text;
			}

			$review_link = '<a href="' . esc_url( 'https://wordpress.org/support/plugin/devdiggers-order-via-chat-for-woocommerce/reviews/#new-post' ) . '" target="_blank" rel="noopener noreferrer" title="' . esc_attr__( 'Review', 'devdiggers-order-via-chat-for-woocommerce' ) . '" aria-label="' . esc_attr__( 'Review', 'devdiggers-order-via-chat-for-woocommerce' ) . '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 32" height="10"><path d="M16 26.534L6.111 32 8 20.422l-8-8.2 11.056-1.688L16 0l4.944 10.534L32 12.223l-8 8.2L25.889 32zm40 0L46.111 32 48 20.422l-8-8.2 11.056-1.688L56 0l4.944 10.534L72 12.223l-8 8.2L65.889 32zm40 0L86.111 32 88 20.422l-8-8.2 11.056-1.688L96 0l4.944 10.534L112 12.223l-8 8.2L105.889 32zm40 0L126.111 32 128 20.422l-8-8.2 11.056-1.688L136 0l4.944 10.534L152 12.223l-8 8.2L145.889 32zm40 0L166.111 32 168 20.422l-8-8.2 11.056-1.688L176 0l4.944 10.534L192 12.223l-8 8.2L185.889 32z" fill="#F5A623" fill-rule="evenodd"/></svg></a>';

			/* translators: %s: star rating link. */
			return sprintf( esc_html__( 'If DevDiggers Order via Chat for WooCommerce is working well for you, please leave us a %s rating. It really helps.', 'devdiggers-order-via-chat-for-woocommerce' ), $review_link );
		}

		/**
		 * Add SVG icons
		 *
		 * @param array $default_svg_icons Framework icons.
		 * @param array $args              Icon args.
		 * @return array
		 */
		public function ddwcpvw_add_svg_icons( $default_svg_icons, $args ) {
			$size         = ! empty( $args['size'] ) ? $args['size'] : '24';
			$size_attr    = 'width="' . $size . '" height="' . $size . '"';
			$stroke_color = ! empty( $args['stroke_color'] ) ? $args['stroke_color'] : 'currentColor';
			$stroke_width = isset( $args['stroke_width'] ) ? $args['stroke_width'] : '2';
			$fill         = ! empty( $args['fill'] ) ? $args['fill'] : 'none';
			$open         = '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round">';

			$svg_icons = [
				'ddwcpvw-display' => $open . '<rect x="2" y="3" width="20" height="14" rx="2"></rect><path d="M8 21h8"></path><path d="M12 17v4"></path><rect x="6" y="8" width="8" height="4" rx="1.5"></rect></svg>',
				'ddwcpvw-twilio'  => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="none" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '"><circle cx="12" cy="12" r="9.5"></circle><g fill="' . $stroke_color . '" stroke="none"><circle cx="9.2" cy="9.2" r="1.9"></circle><circle cx="14.8" cy="9.2" r="1.9"></circle><circle cx="9.2" cy="14.8" r="1.9"></circle><circle cx="14.8" cy="14.8" r="1.9"></circle></g></svg>',
				'ddwcpvw-chat'    => $open . '<path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 8.7 8.7 0 0 1-3.6-.8L3 21l1.9-5.2A8.4 8.4 0 0 1 12 3a8.4 8.4 0 0 1 9 8.5z"></path><path d="M8.5 11h7"></path><path d="M8.5 14h4"></path></svg>',
				'ddwcpvw-wallet'  => $open . '<path d="M19 7V5a2 2 0 0 0-2-2H5a2 2 0 0 0 0 4h14a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5"></path><path d="M16 14h.01"></path></svg>',
				'ddwcpvw-bell'    => $open . '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path></svg>',
			];

			return array_merge( $default_svg_icons, $svg_icons );
		}
	}
}
