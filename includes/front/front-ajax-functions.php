<?php
/**
 * Front AJAX callbacks: turn the customer's product or cart into a WhatsApp purchase request.
 *
 * @package DevDiggers Order via Chat for WooCommerce
 * @version 1.0.0
 */

namespace DDWCPurchaseViaWhatsApp\Includes\Front;

use DDWCPurchaseViaWhatsApp\Helper\DDWCPVW_Stats_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCPVW_Front_Ajax_Functions' ) ) {
	/**
	 * Front ajax functions class
	 */
	class DDWCPVW_Front_Ajax_Functions {
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
		 * Build the WhatsApp link for a purchase request, or ask for an address first.
		 *
		 * @return void
		 */
		public function ddwcpvw_prepare_whatsapp_url() {
			if ( ! check_ajax_referer( 'ddwcpvw-nonce', 'nonce', false ) ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Your session expired. Please refresh the page and try again.', 'devdiggers-order-via-chat-for-woocommerce' ) ], 403 );
			}

			if ( 'yes' !== $this->ddwcpvw_configuration['enabled'] || empty( $this->ddwcpvw_configuration['whatsapp_number'] ) ) {
				wp_send_json_error( [ 'message' => esc_html__( 'WhatsApp ordering is not available right now.', 'devdiggers-order-via-chat-for-woocommerce' ) ] );
			}

			// phpcs:disable WordPress.Security.NonceVerification.Missing -- Verified above.
			$type       = isset( $_POST['type'] ) && 'cart' === $_POST['type'] ? 'cart' : 'product';
			$product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
			$quantity   = isset( $_POST['quantity'] ) ? min( 9999, max( 1, absint( wp_unslash( $_POST['quantity'] ) ) ) ) : 1;
			$guest      = isset( $_POST['guest'] ) && 'yes' === $_POST['guest'];
			$attributes = isset( $_POST['attributes'] ) ? $this->ddwcpvw_sanitize_attributes( sanitize_text_field( wp_unslash( $_POST['attributes'] ) ) ) : [];
			// phpcs:enable WordPress.Security.NonceVerification.Missing

			// Check the items first, so nobody fills an address form for a product they cannot buy.
			$items = 'cart' === $type ? $this->ddwcpvw_get_cart_items() : $this->ddwcpvw_get_product_item( $product_id, $quantity, $attributes );

			if ( is_wp_error( $items ) ) {
				wp_send_json_error( [ 'message' => $items->get_error_message() ] );
			}

			$user_id = get_current_user_id();
			$address = $guest ? $this->ddwcpvw_get_posted_address() : ( $user_id ? $this->ddwcpvw_get_customer_address( $user_id ) : [] );

			if ( ! $user_id && ! $guest && 'yes' !== $this->ddwcpvw_configuration['allow_guests'] ) {
				wp_send_json_error(
					[
						'message'  => esc_html__( 'Please log in to order on WhatsApp.', 'devdiggers-order-via-chat-for-woocommerce' ),
						'redirect' => wc_get_page_permalink( 'myaccount' ),
					]
				);
			}

			$error = $address ? $this->ddwcpvw_validate_address( $address ) : '';

			// No address yet, or an incomplete one: ask for it, prefilled with what we know.
			if ( ! $address || ( $error && ! $guest ) ) {
				wp_send_json_success( [ 'html' => $this->ddwcpvw_get_address_form( $address, $error ) ] );
			}

			if ( $error ) {
				wp_send_json_error( [ 'message' => $error ] );
			}

			$message = $this->ddwcpvw_get_request_message( $items, $address );

			DDWCPVW_Stats_Helper::record( $type, wp_list_pluck( $items, 'data' ), $this->ddwcpvw_get_items_value( $items ) );

			wp_send_json_success(
				[
					'url' => apply_filters( 'ddwcpvw_whatsapp_url', ddwcpvw_get_whatsapp_url( $this->ddwcpvw_configuration['whatsapp_number'], $message ), $message, $items ),
				]
			);
		}

		/**
		 * The single product the customer wants.
		 *
		 * @param int   $product_id Product or variation ID.
		 * @param int   $quantity   Quantity.
		 * @param array $attributes Chosen variation attributes.
		 * @return array|\WP_Error
		 */
		protected function ddwcpvw_get_product_item( $product_id, $quantity, $attributes ) {
			$product = wc_get_product( $product_id );

			if ( ! $product ) {
				return new \WP_Error( 'ddwcpvw_no_product', esc_html__( 'This product could not be found.', 'devdiggers-order-via-chat-for-woocommerce' ) );
			}

			if ( $product->is_type( 'variable' ) ) {
				return new \WP_Error( 'ddwcpvw_choose_options', esc_html__( 'Please choose product options first.', 'devdiggers-order-via-chat-for-woocommerce' ) );
			}

			if ( ! ddwcpvw_is_product_available( $product, $this->ddwcpvw_configuration ) || ! $product->is_in_stock() ) {
				return new \WP_Error( 'ddwcpvw_unavailable', esc_html__( 'Sorry, this product cannot be ordered on WhatsApp right now.', 'devdiggers-order-via-chat-for-woocommerce' ) );
			}

			if ( ! $product->has_enough_stock( $quantity ) ) {
				/* translators: %d: units in stock. */
				return new \WP_Error( 'ddwcpvw_stock', sprintf( esc_html__( 'Only %d left in stock. Please lower the quantity.', 'devdiggers-order-via-chat-for-woocommerce' ), $product->get_stock_quantity() ) );
			}

			return [ $this->ddwcpvw_make_item( $product, $quantity, $attributes ) ];
		}

		/**
		 * Everything in the cart that may be ordered on WhatsApp.
		 *
		 * @return array|\WP_Error
		 */
		protected function ddwcpvw_get_cart_items() {
			$items = [];

			if ( WC()->cart ) {
				foreach ( WC()->cart->get_cart() as $cart_item ) {
					$product = $cart_item['data'];

					if ( ddwcpvw_is_product_available( $product, $this->ddwcpvw_configuration ) ) {
						$items[] = $this->ddwcpvw_make_item( $product, $cart_item['quantity'], (array) $cart_item['variation'] );
					}
				}
			}

			if ( ! $items ) {
				return new \WP_Error( 'ddwcpvw_empty_cart', esc_html__( 'Your cart has no products that can be ordered on WhatsApp.', 'devdiggers-order-via-chat-for-woocommerce' ) );
			}

			return $items;
		}

		/**
		 * One line of the request.
		 *
		 * @param \WC_Product $product    Product.
		 * @param int         $quantity   Quantity.
		 * @param array       $attributes Variation attributes.
		 * @return array
		 */
		protected function ddwcpvw_make_item( $product, $quantity, $attributes ) {
			return [
				'product' => $product,
				'data'    => [
					'product_id' => $product->get_id(),
					'quantity'   => absint( $quantity ),
					'attributes' => $attributes,
				],
			];
		}

		/**
		 * The message the customer sends to start the conversation.
		 *
		 * @param array $items   Items.
		 * @param array $address Customer address.
		 * @return string
		 */
		protected function ddwcpvw_get_request_message( $items, $address ) {
			$show_price = 'yes' !== $this->ddwcpvw_configuration['hide_price'];
			$lines      = [];

			foreach ( $items as $item ) {
				$product  = $item['product'];
				$quantity = $item['data']['quantity'];
				$line     = '*' . wp_strip_all_tags( $product->get_name() ) . '*';

				if ( $product->is_type( 'variation' ) ) {
					$options = wc_get_formatted_variation( $product, true, false, false );

					// WooCommerce already puts the options in the variation's name unless it has a custom title.
					if ( $options && false === strpos( $product->get_name(), wp_strip_all_tags( $options ) ) ) {
						$line .= ' (' . wp_strip_all_tags( $options ) . ')';
					}
				}

				if ( $show_price ) {
					/* translators: 1: quantity, 2: unit price. */
					$line .= "\n" . sprintf( esc_html__( '%1$d x %2$s', 'devdiggers-order-via-chat-for-woocommerce' ), $quantity, ddwcpvw_format_price( (float) wc_get_price_to_display( $product ) ) );
				} else {
					/* translators: %d: quantity. */
					$line .= "\n" . sprintf( esc_html__( 'Quantity: %d', 'devdiggers-order-via-chat-for-woocommerce' ), $quantity );
				}

				$lines[] = $line . "\n" . $product->get_permalink();
			}

			$billing   = $address['billing'];
			$formatted = WC()->countries->get_formatted_address( $address['shipping'] ? $address['shipping'] : $billing );

			$message = ddwcpvw_replace_tags(
				$this->ddwcpvw_configuration['request_template'],
				[
					'{items}'          => implode( "\n\n", $lines ),
					'{subtotal}'       => $show_price ? ddwcpvw_format_price( $this->ddwcpvw_get_items_value( $items ) ) : '',
					'{customer_name}'  => trim( $billing['first_name'] . ' ' . $billing['last_name'] ),
					'{customer_phone}' => $billing['phone'],
					'{customer_email}' => $billing['email'],
					'{address}'        => wp_strip_all_tags( str_replace( [ '<br/>', '<br />', '<br>' ], ', ', $formatted ) ),
					'{site_name}'      => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
				]
			);

			return apply_filters( 'ddwcpvw_request_message', $message, $items, $address );
		}

		/**
		 * The logged-in customer's saved address.
		 *
		 * @param int $user_id User ID.
		 * @return array
		 */
		protected function ddwcpvw_get_customer_address( $user_id ) {
			$customer = new \WC_Customer( $user_id );
			$billing  = $customer->get_billing();
			$shipping = $customer->get_shipping();

			if ( empty( $billing['email'] ) ) {
				$billing['email'] = $customer->get_email();
			}

			unset( $shipping['phone'] );

			return [
				'billing'  => $billing,
				'shipping' => array_filter( $shipping ) && ! empty( $shipping['address_1'] ) ? $shipping : [],
			];
		}

		/**
		 * The address a customer entered in the popup.
		 *
		 * @return array
		 */
		protected function ddwcpvw_get_posted_address() {
			$billing = [];

			foreach ( [ 'first_name', 'last_name', 'company', 'country', 'state', 'postcode', 'city', 'address_1', 'address_2', 'phone', 'email' ] as $field ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified by the caller.
				$billing[ $field ] = isset( $_POST[ 'billing_' . $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'billing_' . $field ] ) ) : '';
			}

			$billing['email'] = sanitize_email( $billing['email'] );

			return [
				'billing'  => $billing,
				'shipping' => [],
			];
		}

		/**
		 * Check the address against the store's own required checkout fields.
		 *
		 * @param array $address Address.
		 * @return string Error message, empty when valid.
		 */
		protected function ddwcpvw_validate_address( $address ) {
			$fields = WC()->checkout()->get_checkout_fields( 'billing' );

			foreach ( $address['billing'] as $key => $value ) {
				if ( ! empty( $fields[ 'billing_' . $key ]['required'] ) && '' === (string) $value ) {
					/* translators: %s: field label. */
					return sprintf( esc_html__( '%s is required.', 'devdiggers-order-via-chat-for-woocommerce' ), wp_strip_all_tags( $fields[ 'billing_' . $key ]['label'] ) );
				}
			}

			if ( empty( $address['billing']['phone'] ) ) {
				return esc_html__( 'Please add the phone number you use on WhatsApp.', 'devdiggers-order-via-chat-for-woocommerce' );
			}

			if ( ! empty( $address['billing']['email'] ) && ! is_email( $address['billing']['email'] ) ) {
				return esc_html__( 'Please enter a valid email address.', 'devdiggers-order-via-chat-for-woocommerce' );
			}

			$digits = preg_replace( '/\D+/', '', ddwcpvw_normalize_phone( $address['billing']['phone'], $address['billing']['country'] ) );

			if ( strlen( $digits ) < 8 || strlen( $digits ) > 15 ) {
				return esc_html__( 'That phone number does not look right. Please include the full number you use on WhatsApp.', 'devdiggers-order-via-chat-for-woocommerce' );
			}

			return '';
		}

		/**
		 * Address popup.
		 *
		 * @param array  $address Known address to prefill.
		 * @param string $error   Why the saved address could not be used.
		 * @return string
		 */
		protected function ddwcpvw_get_address_form( $address, $error ) {
			$fields = WC()->countries->get_address_fields( ! empty( $address['billing']['country'] ) ? $address['billing']['country'] : WC()->countries->get_base_country(), 'billing_' );

			if ( isset( $fields['billing_phone'] ) ) {
				$fields['billing_phone']['required']    = true;
				$fields['billing_phone']['description'] = esc_html__( 'Use the number you have WhatsApp on. We continue your order there.', 'devdiggers-order-via-chat-for-woocommerce' );
			}

			ob_start();
			?>
			<div class="ddwcpvw-popup" role="dialog" aria-modal="true" aria-labelledby="ddwcpvw-popup-title">
				<div class="ddwcpvw-popup-content">
					<button type="button" class="ddwcpvw-close-popup" aria-label="<?php esc_attr_e( 'Close', 'devdiggers-order-via-chat-for-woocommerce' ); ?>">&times;</button>
					<form method="post" id="ddwcpvw-guest-address-form" class="woocommerce">
						<h3 id="ddwcpvw-popup-title"><?php esc_html_e( 'Where should we deliver?', 'devdiggers-order-via-chat-for-woocommerce' ); ?></h3>
						<p class="ddwcpvw-popup-intro"><?php esc_html_e( 'Add your details once and we will finish your order on WhatsApp.', 'devdiggers-order-via-chat-for-woocommerce' ); ?></p>
						<?php if ( $error ) : ?>
							<p class="ddwcpvw-popup-error"><?php echo esc_html( $error ); ?></p>
						<?php endif; ?>
						<div class="ddwcpvw-popup-fields">
							<?php
							foreach ( $fields as $key => $field ) {
								$short = substr( $key, 8 );

								woocommerce_form_field( $key, $field, isset( $address['billing'][ $short ] ) ? $address['billing'][ $short ] : '' );
							}
							?>
						</div>
						<p class="ddwcpvw-popup-error ddwcpvw-popup-ajax-error" role="alert" hidden></p>
						<button type="submit" class="button alt ddwcpvw-popup-submit">
							<?php
							echo ddwcpvw_get_whatsapp_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG markup.
							esc_html_e( 'Continue on WhatsApp', 'devdiggers-order-via-chat-for-woocommerce' );
							?>
						</button>
					</form>
				</div>
			</div>
			<?php
			return ob_get_clean();
		}

		/**
		 * Keep only real variation attributes from the posted JSON.
		 *
		 * @param string $json Posted attributes.
		 * @return array
		 */
		protected function ddwcpvw_sanitize_attributes( $json ) {
			$decoded    = json_decode( $json, true );
			$attributes = [];

			foreach ( is_array( $decoded ) ? $decoded : [] as $key => $value ) {
				$key = sanitize_title( $key );

				if ( 0 === strpos( $key, 'attribute_' ) ) {
					$attributes[ $key ] = sanitize_text_field( (string) $value );
				}
			}

			return $attributes;
		}

		/**
		 * What the requested items cost at today's displayed prices.
		 *
		 * @param array $items Items.
		 * @return float
		 */
		protected function ddwcpvw_get_items_value( $items ) {
			$value = 0;

			foreach ( $items as $item ) {
				$value += (float) wc_get_price_to_display( $item['product'] ) * $item['data']['quantity'];
			}

			return $value;
		}
	}
}
