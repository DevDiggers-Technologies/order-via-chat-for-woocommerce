<?php
/**
 * Frontend callbacks.
 *
 * @package Order via Chat for WooCommerce
 * @version 1.0.0
 */

namespace DDWCPurchaseViaWhatsApp\Includes\Front;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCPVW_Front_Functions' ) ) {
	/**
	 * Front functions class
	 */
	class DDWCPVW_Front_Functions {
		/**
		 * Configuration Variable
		 *
		 * @var array
		 */
		protected $ddwcpvw_configuration;

		/**
		 * Construct
		 *
		 * @param array $ddwcpvw_configuration Plugin configuration.
		 */
		public function __construct( $ddwcpvw_configuration ) {
			$this->ddwcpvw_configuration = $ddwcpvw_configuration;
		}

		/**
		 * Button on the single product page.
		 *
		 * @return void
		 */
		public function ddwcpvw_add_content_in_single_product_page() {
			global $product;

			if ( ! $product instanceof \WC_Product || ! ddwcpvw_is_product_available( $product, $this->ddwcpvw_configuration ) ) {
				return;
			}

			echo wp_kses( $this->ddwcpvw_get_whatsapp_button( $product->get_id() ), $this->ddwcpvw_get_allowed_html() );
		}

		/**
		 * Button beside the add to cart button on shop and category pages.
		 *
		 * Only simple products: variable products need an option picked first.
		 *
		 * @param string      $button  Existing button markup.
		 * @param \WC_Product $product Product.
		 * @return string
		 */
		public function ddwcpvw_modify_woocommerce_loop_add_to_cart_link( $button, $product ) {
			if ( ! $product->is_type( 'simple' ) || ! ddwcpvw_is_product_available( $product, $this->ddwcpvw_configuration ) ) {
				return $button;
			}

			$whatsapp_button = $this->ddwcpvw_get_whatsapp_button( $product->get_id(), 'product', 'no', 'ddwcpvw-loop-button' );

			// Catalog mode: the WhatsApp button takes the add to cart button's place.
			if ( 'yes' === $this->ddwcpvw_configuration['catalog_mode'] ) {
				return $whatsapp_button;
			}

			if ( empty( $this->ddwcpvw_configuration['shop_page_position'] ) ) {
				return $button;
			}

			return 'before' === $this->ddwcpvw_configuration['shop_page_position'] ? $whatsapp_button . $button : $button . $whatsapp_button;
		}

		/**
		 * Same as the loop filter above, for the Product Button block.
		 *
		 * @param string    $content Rendered block.
		 * @param array     $parsed  Parsed block.
		 * @param \WP_Block $block   Block instance.
		 * @return string
		 */
		public function ddwcpvw_modify_product_button_block( $content, $parsed, $block ) {
			$product = isset( $block->context['postId'] ) ? wc_get_product( $block->context['postId'] ) : null;

			return $product ? $this->ddwcpvw_modify_woocommerce_loop_add_to_cart_link( $content, $product ) : $content;
		}

		/**
		 * Lets the stylesheet hide add to cart on a product page in catalog mode. Quantity and
		 * variation pickers stay, the WhatsApp button reads them.
		 *
		 * @param array $classes Body classes.
		 * @return array
		 */
		public function ddwcpvw_add_catalog_body_class( $classes ) {
			if ( is_product() && ddwcpvw_is_product_available( wc_get_product( get_queried_object_id() ), $this->ddwcpvw_configuration ) ) {
				$classes[] = 'ddwcpvw-catalog-mode';
			}

			return $classes;
		}

		/**
		 * Catalog mode is enforced on the server too, so a hidden button cannot be bypassed.
		 *
		 * @param bool $passed     Validation result.
		 * @param int  $product_id Product ID.
		 * @return bool
		 */
		public function ddwcpvw_block_add_to_cart( $passed, $product_id ) {
			if ( $passed && ddwcpvw_is_product_available( wc_get_product( $product_id ), $this->ddwcpvw_configuration ) ) {
				wc_add_notice( esc_html__( 'This product is ordered on WhatsApp. Please use the WhatsApp button on the product page.', 'order-via-chat-for-woocommerce' ), 'error' );
				return false;
			}

			return $passed;
		}

		/**
		 * Hide the price of products ordered on WhatsApp ("ask for price" stores).
		 *
		 * @param string      $price   Price HTML.
		 * @param \WC_Product $product Product.
		 * @return string
		 */
		public function ddwcpvw_hide_price_html( $price, $product ) {
			return ddwcpvw_is_product_available( $product, $this->ddwcpvw_configuration ) ? '' : $price;
		}

		/**
		 * Hide the sale badge where the price is hidden.
		 *
		 * @param string      $html    Badge HTML.
		 * @param \WP_Post    $post    Post.
		 * @param \WC_Product $product Product.
		 * @return string
		 */
		public function ddwcpvw_hide_sale_flash( $html, $post, $product ) {
			return $this->ddwcpvw_hide_price_html( $html, $product );
		}

		/**
		 * [ddwcpvw_button] for page builders and custom templates.
		 *
		 * Attributes: id (product ID, defaults to the current product), type (product or cart).
		 *
		 * @param array $atts Shortcode attributes.
		 * @return string
		 */
		public function ddwcpvw_button_shortcode( $atts ) {
			$atts = shortcode_atts(
				[
					'id'   => 0,
					'type' => 'product',
				],
				$atts,
				'ddwcpvw_button'
			);

			if ( 'cart' === $atts['type'] ) {
				return WC()->cart && ! WC()->cart->is_empty() ? $this->ddwcpvw_get_whatsapp_button( '', 'cart' ) : '';
			}

			$product = wc_get_product( absint( $atts['id'] ) ? absint( $atts['id'] ) : get_the_ID() );

			if ( ! $product || ! ddwcpvw_is_product_available( $product, $this->ddwcpvw_configuration ) ) {
				return '';
			}

			return $this->ddwcpvw_get_whatsapp_button( $product->get_id() );
		}

		/**
		 * Button on the cart page.
		 *
		 * @return void
		 */
		public function ddwcpvw_add_content_after_cart_totals() {
			if ( WC()->cart && ! WC()->cart->is_empty() ) {
				echo wp_kses( $this->ddwcpvw_get_whatsapp_button( '', 'cart', 'no', 'checkout-button' ), $this->ddwcpvw_get_allowed_html() );
			}
		}

		/**
		 * Button under the Cart block, which has no classic proceed to checkout hook.
		 *
		 * @param string $content Rendered Cart block.
		 * @return string
		 */
		public function ddwcpvw_add_button_after_cart_block( $content ) {
			if ( ! WC()->cart || WC()->cart->is_empty() ) {
				return $content;
			}

			return $content . '<div class="ddwcpvw-cart-block">' . $this->ddwcpvw_get_whatsapp_button( '', 'cart', 'no', 'checkout-button' ) . '</div>';
		}

		/**
		 * Purchase button markup.
		 *
		 * @param int|string $product_id Product ID.
		 * @param string     $action     Purchase source, product or cart.
		 * @param string     $guest      Whether the guest address form was filled.
		 * @param string     $css_class  Extra CSS class.
		 * @return string
		 */
		public function ddwcpvw_get_whatsapp_button( $product_id = '', $action = 'product', $guest = 'no', $css_class = '' ) {
			wp_enqueue_style( 'ddwcpvw-front-style' );
			wp_enqueue_script( 'ddwcpvw-front-script' );

			$label   = $this->ddwcpvw_configuration['purchase_button_label'];
			$classes = [ 'ddwcpvw-purchase-button', 'button', $css_class ];

			if ( 'yes' === $this->ddwcpvw_configuration['button_full_width'] ) {
				$classes[] = 'ddwcpvw-full-width';
			}

			if ( 'all' !== $this->ddwcpvw_configuration['button_devices'] ) {
				// CSS, not wp_is_mobile(), so page caches serve one copy to every device.
				$classes[] = 'ddwcpvw-' . $this->ddwcpvw_configuration['button_devices'] . '-only';
			}

			ob_start();
			?>
			<button type="button" data-action="<?php echo esc_attr( $action ); ?>" data-guest="<?php echo esc_attr( $guest ); ?>" data-product-id="<?php echo esc_attr( $product_id ); ?>" data-attributes="" class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>" aria-label="<?php echo esc_attr( $label ); ?>">
				<?php
				if ( 'yes' === $this->ddwcpvw_configuration['button_show_icon'] ) {
					echo ddwcpvw_get_whatsapp_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG markup.
				}
				?>
				<span class="ddwcpvw-button-label"><?php echo esc_html( $label ); ?></span>
			</button>
			<?php
			return apply_filters( 'ddwcpvw_purchase_button_html', ob_get_clean(), $product_id, $action );
		}

		/**
		 * Floating "chat with us" bubble. A plain wa.me link: no script, works with every cache.
		 *
		 * @return void
		 */
		public function ddwcpvw_render_floating_button() {
			$number = $this->ddwcpvw_configuration['floating_number'] ? $this->ddwcpvw_configuration['floating_number'] : $this->ddwcpvw_configuration['whatsapp_number'];
			$url    = ddwcpvw_get_whatsapp_url( $number, '' );

			if ( ! $url || ( 'woocommerce' === $this->ddwcpvw_configuration['floating_pages'] && ! ( is_woocommerce() || is_cart() || is_checkout() ) ) ) {
				return;
			}

			$message = $this->ddwcpvw_configuration['floating_message'];

			if ( is_product() ) {
				$message .= "\n\n" . wp_specialchars_decode( get_the_title(), ENT_QUOTES ) . "\n" . get_permalink();
			}

			$label   = $this->ddwcpvw_configuration['floating_label'];
			$classes = [ 'ddwcpvw-floating', 'ddwcpvw-floating-' . $this->ddwcpvw_configuration['floating_position'] ];

			if ( 'yes' === $this->ddwcpvw_configuration['floating_hide_mobile'] ) {
				$classes[] = 'ddwcpvw-floating-hide-mobile';
			}

			if ( $label ) {
				$classes[] = 'ddwcpvw-floating-has-label';
			}

			wp_enqueue_style( 'ddwcpvw-front-style' );

			$url = ddwcpvw_get_whatsapp_url( $number, apply_filters( 'ddwcpvw_floating_message', $message ) );
			?>
			<a class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" href="<?php echo esc_attr( $url ); // esc_url() strips the %0A line breaks; the URL is https://wa.me/ plus digits and rawurlencode() output. ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $label ? $label : __( 'Chat with us on WhatsApp', 'order-via-chat-for-woocommerce' ) ); ?>">
				<span class="ddwcpvw-floating-icon"><?php echo ddwcpvw_get_whatsapp_icon( 28 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG markup. ?></span>
				<?php if ( $label ) : ?>
					<span class="ddwcpvw-floating-label"><?php echo esc_html( $label ); ?></span>
				<?php endif; ?>
			</a>
			<?php
		}

		/**
		 * "Send my order on WhatsApp" on the order received page, after a normal checkout.
		 *
		 * WooCommerce has already checked the order key before this page renders, so only the
		 * customer who placed the order sees it.
		 *
		 * @param int $order_id Order ID.
		 * @return void
		 */
		public function ddwcpvw_render_thankyou_button( $order_id ) {
			$order = wc_get_order( $order_id );

			if ( ! $order || $order->has_status( 'failed' ) ) {
				return;
			}

			$message = ddwcpvw_replace_tags(
				$this->ddwcpvw_configuration['thankyou_template'],
				[
					'{order_number}'  => $order->get_order_number(),
					'{order_total}'   => ddwcpvw_format_price( $order->get_total(), $order->get_currency() ),
					'{order_summary}' => ddwcpvw_get_order_summary( $order ),
					'{customer_name}' => trim( $order->get_formatted_billing_full_name() ),
					'{site_name}'     => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
				]
			);

			$url = ddwcpvw_get_whatsapp_url( $this->ddwcpvw_configuration['whatsapp_number'], apply_filters( 'ddwcpvw_thankyou_message', $message, $order ) );

			wp_enqueue_style( 'ddwcpvw-front-style' );
			?>
			<p class="ddwcpvw-thankyou">
				<a class="ddwcpvw-purchase-button button" href="<?php echo esc_attr( $url ); // esc_url() strips the %0A line breaks; the URL is https://wa.me/ plus digits and rawurlencode() output. ?>" target="_blank" rel="noopener noreferrer">
					<?php
					if ( 'yes' === $this->ddwcpvw_configuration['button_show_icon'] ) {
						echo ddwcpvw_get_whatsapp_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG markup.
					}
					?>
					<span class="ddwcpvw-button-label"><?php echo esc_html( $this->ddwcpvw_configuration['thankyou_label'] ); ?></span>
				</a>
			</p>
			<?php
		}

		/**
		 * Register storefront assets. They are enqueued only where a button renders.
		 *
		 * @return void
		 */
		public function ddwcpvw_front_scripts() {
			wp_register_style( 'ddwcpvw-front-style', DDWCPVW_PLUGIN_URL . 'assets/css/front.css', [], filemtime( DDWCPVW_PLUGIN_FILE . 'assets/css/front.css' ) );
			wp_register_script( 'ddwcpvw-front-script', DDWCPVW_PLUGIN_URL . 'assets/js/front.js', [ 'jquery', 'wc-country-select' ], filemtime( DDWCPVW_PLUGIN_FILE . 'assets/js/front.js' ), true );

			wp_localize_script(
				'ddwcpvw-front-script',
				'ddwcpvwFrontObj',
				[
					'ajax'     => [
						'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
						'ajaxNonce' => wp_create_nonce( 'ddwcpvw-nonce' ),
					],
					'tracking' => 'yes' === $this->ddwcpvw_configuration['tracking'],
					'i18n'     => [
						'error'         => esc_html__( 'Something went wrong. Please try again.', 'order-via-chat-for-woocommerce' ),
						'chooseOptions' => esc_html__( 'Please choose product options first.', 'order-via-chat-for-woocommerce' ),
						'opening'       => esc_html__( 'Opening WhatsApp…', 'order-via-chat-for-woocommerce' ),
					],
				]
			);

			wp_add_inline_style(
				'ddwcpvw-front-style',
				sprintf(
					':root{--ddwcpvw-font-color:%1$s;--ddwcpvw-background-color:%2$s;--ddwcpvw-radius:%3$dpx;}',
					esc_attr( $this->ddwcpvw_configuration['purchase_button_text_color'] ),
					esc_attr( $this->ddwcpvw_configuration['purchase_button_background_color'] ),
					absint( $this->ddwcpvw_configuration['button_radius'] )
				)
			);
		}

		/**
		 * Markup allowed in the purchase button.
		 *
		 * @return array
		 */
		protected function ddwcpvw_get_allowed_html() {
			return array_merge(
				ddfw_kses_allowed_svg_tags(),
				[
					'button' => [
						'type'            => true,
						'class'           => true,
						'aria-label'      => true,
						'disabled'        => true,
						'data-action'     => true,
						'data-guest'      => true,
						'data-product-id' => true,
						'data-attributes' => true,
					],
					'span'   => [ 'class' => true ],
				]
			);
		}
	}
}
