<?php
/**
 * Shared option lists and small helpers used across admin screens, the wizard and the storefront.
 *
 * @package Order via Chat for WooCommerce
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit();

if ( ! function_exists( 'ddwcpvw_get_brand_mark' ) ) {
	/**
	 * The plugin's mark: a chat bubble carrying a shopping bag.
	 *
	 * One definition for the admin menu header, the setup wizard and the dashboard, so the three
	 * can never drift apart.
	 *
	 * @param int    $size  Pixel size.
	 * @param string $color Any CSS colour. Defaults to the framework's primary.
	 * @return string
	 */
	function ddwcpvw_get_brand_mark( $size = 30, $color = 'var(--ddfw-primary-color, #0256ff)' ) {
		$size  = absint( $size );
		$color = esc_attr( $color );
		// Apple style app icon: a continuous-corner tile (rx is about 22% of the size), one focal glyph, a hairline inner highlight and generous padding.
		// The framework recolours every <path> in the header, so the mark uses rects, circles and polygons only.
		return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 30 30" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">'
			. '<rect width="30" height="30" rx="6.7" fill="' . $color . '"/>'
			. '<rect x=".5" y=".5" width="29" height="29" rx="6.2" stroke="#fff" stroke-width="1" opacity=".22"/>'
			. '<rect x="5.5" y="6" width="19" height="15" rx="6" fill="#fff"/>'
			. '<polygon points="9.2,19.5 8,24.5 14.2,21" fill="#fff" stroke="#fff" stroke-width="1" stroke-linejoin="round"/>'
			. '<rect x="12.5" y="9.4" width="5" height="5.6" rx="2.5" stroke="' . $color . '" stroke-width="1.4" fill="none"/>'
			. '<polygon points="10.3,12.9 19.7,12.9 18.7,18.3 11.3,18.3" fill="' . $color . '" stroke="' . $color . '" stroke-width="1.2" stroke-linejoin="round"/>'
			. '</svg>';
	}
}

if ( ! function_exists( 'ddwcpvw_get_whatsapp_icon' ) ) {
	/**
	 * The WhatsApp glyph used on the storefront button, drawn in the button's own text colour.
	 *
	 * @param int $size Pixel size.
	 * @return string
	 */
	function ddwcpvw_get_whatsapp_icon( $size = 18 ) {
		$size = absint( $size );

		return '<svg class="ddwcpvw-whatsapp-icon" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.64.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.07 2.88 1.21 3.08.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.7.63.71.23 1.36.19 1.87.12.57-.09 1.76-.72 2-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35ZM12.05 21.5h-.01a9.4 9.4 0 0 1-4.8-1.31l-.34-.2-3.57.93.95-3.48-.22-.36a9.43 9.43 0 0 1-1.44-5.02c0-5.2 4.24-9.44 9.45-9.44a9.4 9.4 0 0 1 6.68 2.77 9.38 9.38 0 0 1 2.76 6.68c0 5.21-4.24 9.44-9.45 9.44Zm8.04-17.47A11.3 11.3 0 0 0 12.05.7C5.78.7.68 5.8.68 12.06c0 2 .52 3.96 1.52 5.68L.58 23.7l6.1-1.6a11.33 11.33 0 0 0 5.37 1.37h.01c6.26 0 11.36-5.1 11.37-11.36 0-3.03-1.18-5.89-3.34-8.03Z"/></svg>';
	}
}

if ( ! function_exists( 'ddwcpvw_get_dashboard_url' ) ) {
	/**
	 * URL of a screen inside the plugin dashboard.
	 *
	 * @param array $args Extra query args.
	 * @return string
	 */
	function ddwcpvw_get_dashboard_url( $args = [] ) {
		return add_query_arg( array_merge( [ 'page' => 'ddwcpvw-dashboard' ], $args ), admin_url( 'admin.php' ) );
	}
}

if ( ! function_exists( 'ddwcpvw_normalize_phone' ) ) {
	/**
	 * Turn whatever a customer typed into an E.164 number.
	 *
	 * Customers rarely type the country code, so a local number is completed with the calling
	 * code of their billing country, falling back to the store's own country.
	 *
	 * @param string $phone   Raw phone number.
	 * @param string $country Two letter country code the number belongs to.
	 * @return string E.164 number, or an empty string when nothing usable was given.
	 */
	function ddwcpvw_normalize_phone( $phone, $country = '' ) {
		// E.164 allows at most 15 digits, and nothing shorter than 8 is a real mobile number.
		$valid = function ( $number ) {
			$length = strlen( ltrim( $number, '+' ) );

			return $length >= 8 && $length <= 15 ? $number : '';
		};

		$phone = trim( str_replace( 'whatsapp:', '', (string) $phone ) );

		if ( '' === $phone ) {
			return '';
		}

		$has_plus = 0 === strpos( $phone, '+' );
		$digits   = preg_replace( '/\D+/', '', $phone );

		if ( '' === $digits ) {
			return '';
		}

		if ( $has_plus ) {
			return $valid( '+' . $digits );
		}

		if ( 0 === strpos( $digits, '00' ) ) {
			return $valid( '+' . substr( $digits, 2 ) );
		}

		if ( ! $country && function_exists( 'WC' ) && WC()->countries ) {
			$country = WC()->countries->get_base_country();
		}

		$calling_code = '';

		if ( $country && function_exists( 'WC' ) && WC()->countries ) {
			$calling_code = WC()->countries->get_country_calling_code( $country );
			$calling_code = is_array( $calling_code ) ? reset( $calling_code ) : $calling_code;
			$calling_code = preg_replace( '/\D+/', '', (string) $calling_code );
		}

		if ( ! $calling_code ) {
			return $valid( '+' . $digits );
		}

		// ponytail: a number longer than a national one that already starts with the calling code
		// was typed in international form without the plus. Wrong only for rare 11+ digit national
		// numbers that happen to share the prefix; a libphonenumber port is the upgrade path.
		if ( strlen( $digits ) > 10 && 0 === strpos( $digits, $calling_code ) ) {
			return $valid( '+' . $digits );
		}

		return $valid( '+' . $calling_code . ltrim( $digits, '0' ) );
	}
}

if ( ! function_exists( 'ddwcpvw_get_product_position_options' ) ) {
	/**
	 * Where the purchase button can be rendered on a single product page.
	 *
	 * @return array
	 */
	function ddwcpvw_get_product_position_options() {
		return [
			''   => esc_html__( 'Do not show', 'order-via-chat-for-woocommerce' ),
			'55' => esc_html__( 'Default (end of the summary)', 'order-via-chat-for-woocommerce' ),
			'10' => esc_html__( 'After Product Image', 'order-via-chat-for-woocommerce' ),
			'8'  => esc_html__( 'After Product Title', 'order-via-chat-for-woocommerce' ),
			'3'  => esc_html__( 'Before Product Title', 'order-via-chat-for-woocommerce' ),
			'25' => esc_html__( 'After Short Description', 'order-via-chat-for-woocommerce' ),
			'35' => esc_html__( 'After Add To Cart Button', 'order-via-chat-for-woocommerce' ),
			'5'  => esc_html__( 'Before Tab Information', 'order-via-chat-for-woocommerce' ),
		];
	}
}

if ( ! function_exists( 'ddwcpvw_get_shop_position_options' ) ) {
	/**
	 * Where the purchase button can be rendered on shop and category pages.
	 *
	 * @return array
	 */
	function ddwcpvw_get_shop_position_options() {
		return [
			''       => esc_html__( 'Do not show', 'order-via-chat-for-woocommerce' ),
			'before' => esc_html__( 'Before Add To Cart Button', 'order-via-chat-for-woocommerce' ),
			'after'  => esc_html__( 'After Add To Cart Button', 'order-via-chat-for-woocommerce' ),
		];
	}
}

if ( ! function_exists( 'ddwcpvw_get_cart_position_options' ) ) {
	/**
	 * Where the purchase button can be rendered on the cart page.
	 *
	 * @return array
	 */
	function ddwcpvw_get_cart_position_options() {
		return [
			''   => esc_html__( 'Do not show', 'order-via-chat-for-woocommerce' ),
			'10' => esc_html__( 'Before Proceed To Checkout Button', 'order-via-chat-for-woocommerce' ),
			'20' => esc_html__( 'After Proceed To Checkout Button', 'order-via-chat-for-woocommerce' ),
		];
	}
}

if ( ! function_exists( 'ddwcpvw_is_product_available' ) ) {
	/**
	 * Whether a product can be bought through WhatsApp under the merchant's rules.
	 *
	 * @param \WC_Product $product       Product or variation.
	 * @param array       $configuration Plugin configuration.
	 * @return bool
	 */
	function ddwcpvw_is_product_available( $product, $configuration ) {
		if ( ! $product instanceof \WC_Product ) {
			return false;
		}

		$ids = array_filter( [ $product->get_id(), $product->get_parent_id() ] );

		if ( array_intersect( $ids, (array) $configuration['excluded_products'] ) ) {
			return false;
		}

		if ( ! empty( $configuration['excluded_categories'] ) ) {
			$category_ids = wc_get_product_cat_ids( $product->get_parent_id() ? $product->get_parent_id() : $product->get_id() );

			if ( array_intersect( $category_ids, (array) $configuration['excluded_categories'] ) ) {
				return false;
			}
		}

		if ( ! $product->is_purchasable() ) {
			return false;
		}

		// A variable product is "in stock" when any variation is, which is what the button needs.
		if ( 'yes' === $configuration['hide_out_of_stock'] && ! $product->is_in_stock() ) {
			return false;
		}

		return (bool) apply_filters( 'ddwcpvw_is_product_available', true, $product, $configuration );
	}
}

if ( ! function_exists( 'ddwcpvw_get_whatsapp_url' ) ) {
	/**
	 * A wa.me link that opens a chat with a number, the message already typed in.
	 *
	 * Only the digits of the number are used, and the message is rawurlencode()d, so the
	 * result is safe to print with esc_attr(). esc_url() would strip the %0A line breaks.
	 *
	 * @param string $number  Phone number in any format.
	 * @param string $message Prefilled message.
	 * @return string Empty when the number is not usable.
	 */
	function ddwcpvw_get_whatsapp_url( $number, $message = '' ) {
		$digits = preg_replace( '/\D+/', '', ddwcpvw_normalize_phone( $number ) );

		if ( ! $digits ) {
			return '';
		}

		return 'https://wa.me/' . $digits . ( '' !== $message ? '?text=' . rawurlencode( $message ) : '' );
	}
}

if ( ! function_exists( 'ddwcpvw_format_price' ) ) {
	/**
	 * Price with currency symbol and without HTML, for a WhatsApp message.
	 *
	 * @param float|string $amount   Amount.
	 * @param string       $currency Currency code.
	 * @return string
	 */
	function ddwcpvw_format_price( $amount, $currency = '' ) {
		return html_entity_decode( wp_strip_all_tags( wc_price( $amount, [ 'currency' => $currency ? $currency : get_woocommerce_currency() ] ) ) );
	}
}

if ( ! function_exists( 'ddwcpvw_get_order_summary' ) ) {
	/**
	 * A WooCommerce order written out for a WhatsApp message: items, totals and payment.
	 *
	 * WhatsApp renders *text* in bold, so the labels use it.
	 *
	 * @param \WC_Order $order Order.
	 * @return string
	 */
	function ddwcpvw_get_order_summary( $order ) {
		$currency = $order->get_currency();
		$incl_tax = 'incl' === get_option( 'woocommerce_tax_display_cart' );
		$summary  = '';
		$i        = 1;

		foreach ( $order->get_items() as $item ) {
			$quantity = max( 1, $item->get_quantity() );
			$subtotal = $order->get_line_subtotal( $item, $incl_tax );

			$summary .= sprintf( "%d. %s (%s x %d) = %s\n", $i, wp_strip_all_tags( $item->get_name() ), ddwcpvw_format_price( $subtotal / $quantity, $currency ), $quantity, ddwcpvw_format_price( $subtotal, $currency ) );
			++$i;
		}

		if ( $order->get_shipping_method() ) {
			/* translators: %s: shipping method. */
			$summary .= "\n" . sprintf( esc_html__( '*Shipping:* %s', 'order-via-chat-for-woocommerce' ), wp_strip_all_tags( $order->get_shipping_method() ) );
		}

		if ( $order->get_total_discount() > 0 ) {
			/* translators: %s: discount amount. */
			$summary .= "\n" . sprintf( esc_html__( '*Discount:* %s', 'order-via-chat-for-woocommerce' ), ddwcpvw_format_price( $order->get_total_discount(), $currency ) );
		}

		// Lines shown without tax: list it, so the lines add up to the total.
		if ( ! $incl_tax && $order->get_total_tax() > 0 ) {
			/* translators: 1: tax label, 2: tax amount. */
			$summary .= "\n" . sprintf( esc_html__( '*%1$s:* %2$s', 'order-via-chat-for-woocommerce' ), WC()->countries->tax_or_vat(), ddwcpvw_format_price( $order->get_total_tax(), $currency ) );
		}

		if ( $order->get_payment_method_title() ) {
			/* translators: %s: payment method title. */
			$summary .= "\n" . sprintf( esc_html__( '*Payment:* %s', 'order-via-chat-for-woocommerce' ), wp_strip_all_tags( $order->get_payment_method_title() ) );
		}

		/* translators: %s: order total. */
		$summary .= "\n" . sprintf( esc_html__( '*Total:* %s', 'order-via-chat-for-woocommerce' ), ddwcpvw_format_price( $order->get_total(), $currency ) );

		return trim( $summary );
	}
}

if ( ! function_exists( 'ddwcpvw_get_pro_image' ) ) {
	/**
	 * Screenshot for an upgrade card, or an empty string when none is bundled.
	 *
	 * An empty string makes the upgrade card render on its own instead of over a broken image,
	 * so a screenshot can be dropped into assets/images/pro/ later without a code change.
	 *
	 * @param string $name File name without the extension.
	 * @return string
	 */
	function ddwcpvw_get_pro_image( $name ) {
		$file = 'assets/images/pro/' . sanitize_file_name( $name ) . '.webp';

		return file_exists( DDWCPVW_PLUGIN_FILE . $file ) ? DDWCPVW_PLUGIN_URL . $file : '';
	}
}

if ( ! function_exists( 'ddwcpvw_get_default_template' ) ) {
	/**
	 * Default WhatsApp messages.
	 *
	 * @param string $name request or thankyou.
	 * @return string
	 */
	function ddwcpvw_get_default_template( $name ) {
		if ( 'thankyou' === $name ) {
			/* translators: Keep the {placeholders} as they are. */
			return __( "Hello! I just placed order #{order_number} on {site_name}.\n\n{order_summary}\n\n*Name:* {customer_name}", 'order-via-chat-for-woocommerce' );
		}

		/* translators: Keep the {placeholders} as they are. */
		return __( "Hello! I would like to order:\n\n{items}\n\n*Estimated subtotal:* {subtotal}\n*Name:* {customer_name}\n*Deliver to:* {address}", 'order-via-chat-for-woocommerce' );
	}
}

if ( ! function_exists( 'ddwcpvw_replace_tags' ) ) {
	/**
	 * Fill a message template. Lines whose only tag came out empty are dropped, so a hidden
	 * price never leaves a dangling "Estimated subtotal:" label.
	 *
	 * @param string $template Template with {tags}.
	 * @param array  $tags     Tag => value.
	 * @return string
	 */
	function ddwcpvw_replace_tags( $template, $tags ) {
		$lines = [];

		foreach ( explode( "\n", str_replace( "\r", '', (string) $template ) ) as $line ) {
			$empty = false;

			foreach ( $tags as $tag => $value ) {
				if ( false !== strpos( $line, $tag ) && '' === trim( (string) $value ) ) {
					$empty = true;
				}
			}

			if ( ! $empty ) {
				$lines[] = strtr( $line, $tags );
			}
		}

		return trim( preg_replace( "/\n{3,}/", "\n\n", implode( "\n", $lines ) ) );
	}
}

if ( ! function_exists( 'ddwcpvw_get_template_tags_help' ) ) {
	/**
	 * Tag list shown under a template field.
	 *
	 * @param array $tags Tags.
	 * @return string
	 */
	function ddwcpvw_get_template_tags_help( $tags ) {
		/* translators: %s: list of tags. */
		return sprintf( esc_html__( 'Available tags: %s. Wrap words in *stars* for bold.', 'order-via-chat-for-woocommerce' ), '<code>' . implode( '</code> <code>', array_map( 'esc_html', $tags ) ) . '</code>' );
	}
}
