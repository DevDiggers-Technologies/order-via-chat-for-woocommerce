<?php
/**
 * Admin callbacks.
 *
 * @package Order via Chat for WooCommerce
 * @version 1.0.0
 */

namespace DDWCPurchaseViaWhatsApp\Includes\Admin;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCPVW_Admin_Functions' ) ) {
	/**
	 * Admin callbacks.
	 */
	class DDWCPVW_Admin_Functions {
		/**
		 * Plugin configuration.
		 *
		 * @var array
		 */
		protected $ddwcpvw_configuration;

		/**
		 * Store configuration.
		 *
		 * @param array $ddwcpvw_configuration Plugin configuration.
		 */
		public function __construct( $ddwcpvw_configuration ) {
			$this->ddwcpvw_configuration = $ddwcpvw_configuration;
		}

		/**
		 * Register every option with an explicit sanitizer.
		 *
		 * Registered on admin_init, which also runs for the setup wizard's AJAX save, so the
		 * wizard's update_option() calls go through the same sanitizers.
		 *
		 * @return void
		 */
		public function ddwcpvw_register_settings() {
			foreach ( $this->ddwcpvw_get_settings() as $group => $options ) {
				foreach ( $options as $option => $sanitize_callback ) {
					register_setting( $group, $option, [ 'sanitize_callback' => $sanitize_callback ] );
				}
			}
		}

		/**
		 * Every option the plugin saves, by settings group, with its sanitizer.
		 *
		 * The setup wizard saves only options listed here, so a crafted wizard request cannot
		 * write anything else.
		 *
		 * @return array
		 */
		public function ddwcpvw_get_settings() {
			$checkbox = [ $this, 'ddwcpvw_sanitize_checkbox' ];
			$phone    = [ $this, 'ddwcpvw_sanitize_phone' ];

			return [
				'ddwcpvw-general-configuration-fields' => [
					'_ddwcpvw_enabled'                => $checkbox,
					'_ddwcpvw_whatsapp_number' => $phone,
					'_ddwcpvw_allow_guests'           => $checkbox,
					'_ddwcpvw_hide_out_of_stock'      => $checkbox,
					'_ddwcpvw_excluded_products'      => [ $this, 'ddwcpvw_sanitize_ids' ],
					'_ddwcpvw_excluded_categories'    => [ $this, 'ddwcpvw_sanitize_ids' ],
					'_ddwcpvw_thankyou_enabled'       => $checkbox,
					'_ddwcpvw_thankyou_label'         => 'sanitize_text_field',
					'_ddwcpvw_catalog_mode'           => $checkbox,
					'_ddwcpvw_hide_price'             => $checkbox,
					'_ddwcpvw_request_template'       => 'sanitize_textarea_field',
					'_ddwcpvw_thankyou_template'      => 'sanitize_textarea_field',
					'_ddwcpvw_tracking'               => $checkbox,
				],
				'ddwcpvw-display-configuration-fields' => [
					'_ddwcpvw_product_page_position'      => [ $this, 'ddwcpvw_sanitize_product_position' ],
					'_ddwcpvw_cart_page_position'         => [ $this, 'ddwcpvw_sanitize_cart_position' ],
					'_ddwcpvw_shop_page_position'         => [ $this, 'ddwcpvw_sanitize_shop_position' ],
					'_ddwcpvw_purchase_button_label'      => 'sanitize_text_field',
					'_ddwcpvw_button_show_icon'           => $checkbox,
					'_ddwcpvw_purchase_button_background_color' => 'sanitize_hex_color',
					'_ddwcpvw_purchase_button_text_color' => 'sanitize_hex_color',
					'_ddwcpvw_button_radius'              => [ $this, 'ddwcpvw_sanitize_radius' ],
					'_ddwcpvw_button_full_width'          => $checkbox,
					'_ddwcpvw_button_devices'             => [ $this, 'ddwcpvw_sanitize_devices' ],
					'_ddwcpvw_floating_enabled'           => $checkbox,
					'_ddwcpvw_floating_number'            => $phone,
					'_ddwcpvw_floating_message'           => 'sanitize_text_field',
					'_ddwcpvw_floating_label'             => 'sanitize_text_field',
					'_ddwcpvw_floating_position'          => [ $this, 'ddwcpvw_sanitize_floating_position' ],
					'_ddwcpvw_floating_pages'             => [ $this, 'ddwcpvw_sanitize_floating_pages' ],
					'_ddwcpvw_floating_hide_mobile'       => $checkbox,
				],
			];
		}

		/**
		 * WhatsApp box on the order edit screen.
		 *
		 * @return void
		 */
		public function ddwcpvw_add_order_meta_box() {
			$screen = function_exists( 'wc_get_page_screen_id' ) ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order';

			add_meta_box( 'ddwcpvw-order-whatsapp', esc_html__( 'WhatsApp', 'order-via-chat-for-woocommerce' ), [ $this, 'ddwcpvw_render_order_meta_box' ], $screen, 'side', 'default' );
		}

		/**
		 * Open a WhatsApp chat with the customer, the order already written out.
		 *
		 * @param \WP_Post|\WC_Order $post_or_order Post (legacy storage) or order (HPOS).
		 * @return void
		 */
		public function ddwcpvw_render_order_meta_box( $post_or_order ) {
			$order = $post_or_order instanceof \WC_Order ? $post_or_order : wc_get_order( $post_or_order->ID );

			if ( ! $order ) {
				return;
			}

			$phone = ddwcpvw_normalize_phone( $order->get_billing_phone(), $order->get_billing_country() );

			if ( ! $phone ) {
				?>
				<p class="description"><?php esc_html_e( 'This order has no billing phone, so there is no one to message.', 'order-via-chat-for-woocommerce' ); ?></p>
				<?php
				return;
			}

			$message = sprintf(
				/* translators: 1: customer first name, 2: order number, 3: order status. */
				esc_html__( 'Hi %1$s, this is about your order #%2$s, which is now %3$s.', 'order-via-chat-for-woocommerce' ),
				$order->get_billing_first_name(),
				$order->get_order_number(),
				wc_get_order_status_name( $order->get_status() )
			) . "\n\n" . ddwcpvw_get_order_summary( $order );
			?>
			<div class="ddwcpvw-order-box" data-phone="<?php echo esc_attr( preg_replace( '/\D+/', '', $phone ) ); ?>">
				<p class="ddwcpvw-order-box-phone"><?php echo esc_html( $phone ); ?></p>
				<label class="screen-reader-text" for="ddwcpvw-order-box-message"><?php esc_html_e( 'Message', 'order-via-chat-for-woocommerce' ); ?></label>
				<textarea id="ddwcpvw-order-box-message" class="ddwcpvw-order-box-message" rows="7"><?php echo esc_textarea( $message ); ?></textarea>
				<p>
					<a class="button ddwcpvw-order-box-send" href="<?php echo esc_attr( ddwcpvw_get_whatsapp_url( $phone, $message ) ); // esc_url() strips the %0A line breaks; the URL is https://wa.me/ plus digits and rawurlencode() output. ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open in WhatsApp', 'order-via-chat-for-woocommerce' ); ?></a>
				</p>
				<p class="description">
					<?php esc_html_e( 'Opens WhatsApp on this device with the message ready to send.', 'order-via-chat-for-woocommerce' ); ?>
				</p>
			</div>
			<?php
		}

		/**
		 * Load the admin assets for the order box.
		 *
		 * @return void
		 */
		public function ddwcpvw_enqueue_order_screen_scripts() {
			$screen    = get_current_screen();
			$order_ids = [ 'shop_order', function_exists( 'wc_get_page_screen_id' ) ? wc_get_page_screen_id( 'shop-order' ) : '' ];

			if ( ! $screen || ! in_array( $screen->id, $order_ids, true ) ) {
				return;
			}

			wp_enqueue_style( 'ddwcpvw-admin-style', DDWCPVW_PLUGIN_URL . 'assets/css/admin.css', [], filemtime( DDWCPVW_PLUGIN_FILE . 'assets/css/admin.css' ) );
			wp_enqueue_script( 'ddwcpvw-admin-script', DDWCPVW_PLUGIN_URL . 'assets/js/admin.js', [], filemtime( DDWCPVW_PLUGIN_FILE . 'assets/js/admin.js' ), true );
		}

		/**
		 * Sanitize a checkbox.
		 *
		 * @param mixed $value Value.
		 * @return string
		 */
		public function ddwcpvw_sanitize_checkbox( $value ) {
			return 'yes' === $value ? 'yes' : '';
		}

		/**
		 * Devices the order buttons show on.
		 *
		 * @param mixed $value Value.
		 * @return string
		 */
		public function ddwcpvw_sanitize_devices( $value ) {
			return in_array( $value, [ 'mobile', 'desktop' ], true ) ? $value : 'all';
		}

		/**
		 * Sanitize IDs.
		 *
		 * @param mixed $value Value.
		 * @return array
		 */
		public function ddwcpvw_sanitize_ids( $value ) {
			return array_values( array_filter( array_map( 'absint', (array) $value ) ) );
		}

		/**
		 * Sanitize a phone number to E.164.
		 *
		 * @param mixed $value Value.
		 * @return string
		 */
		public function ddwcpvw_sanitize_phone( $value ) {
			return ddwcpvw_normalize_phone( sanitize_text_field( (string) $value ) );
		}

		/**
		 * Floating button corner.
		 *
		 * @param mixed $value Value.
		 * @return string
		 */
		public function ddwcpvw_sanitize_floating_position( $value ) {
			return 'left' === $value ? 'left' : 'right';
		}

		/**
		 * Floating button pages.
		 *
		 * @param mixed $value Value.
		 * @return string
		 */
		public function ddwcpvw_sanitize_floating_pages( $value ) {
			return 'woocommerce' === $value ? 'woocommerce' : 'all';
		}

		/**
		 * Sanitize the button corner radius.
		 *
		 * @param mixed $value Value.
		 * @return int
		 */
		public function ddwcpvw_sanitize_radius( $value ) {
			return min( 50, absint( $value ) );
		}

		/**
		 * Sanitize shop position.
		 *
		 * @param mixed $value Value.
		 * @return string
		 */
		public function ddwcpvw_sanitize_shop_position( $value ) {
			$value = sanitize_key( $value );

			return array_key_exists( $value, ddwcpvw_get_shop_position_options() ) ? $value : '';
		}

		/**
		 * Sanitize product position.
		 *
		 * @param mixed $value Value.
		 * @return string
		 */
		public function ddwcpvw_sanitize_product_position( $value ) {
			$value = sanitize_key( $value );

			return array_key_exists( $value, ddwcpvw_get_product_position_options() ) ? $value : '';
		}

		/**
		 * Sanitize cart position.
		 *
		 * @param mixed $value Value.
		 * @return string
		 */
		public function ddwcpvw_sanitize_cart_position( $value ) {
			$value = sanitize_key( $value );

			return array_key_exists( $value, ddwcpvw_get_cart_position_options() ) ? $value : '';
		}
	}
}
