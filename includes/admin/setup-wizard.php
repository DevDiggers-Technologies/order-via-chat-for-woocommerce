<?php
/**
 * Setup wizard integration.
 *
 * @package DevDiggers Order via Chat for WooCommerce
 * @version 1.0.0
 */

namespace DDWCPurchaseViaWhatsApp\Includes\Admin;

use DevDiggers\Framework\Includes\DDFW_Form_Field;
use DevDiggers\Framework\Includes\DDFW_Setup_Wizard;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCPVW_Setup_Wizard' ) ) {
	/**
	 * Setup wizard class
	 */
	class DDWCPVW_Setup_Wizard {
		/**
		 * Construct
		 */
		public function __construct() {
			if ( ! class_exists( '\\DevDiggers\\Framework\\Includes\\DDFW_Setup_Wizard' ) ) {
				return;
			}

			new DDFW_Setup_Wizard( $this->ddwcpvw_get_wizard_config() );
		}

		/**
		 * Wizard configuration.
		 *
		 * @return array
		 */
		public function ddwcpvw_get_wizard_config() {
			return [
				'plugin_slug'    => 'devdiggers-order-via-chat-for-woocommerce',
				'plugin_file'    => 'devdiggers-order-via-chat-for-woocommerce/functions.php',
				'dashboard_page' => 'ddwcpvw-dashboard',
				'redirect_url'   => admin_url( 'admin.php?page=ddwcpvw-dashboard' ),
				'brand'          => [
					'name'        => esc_html__( 'Order via Chat', 'devdiggers-order-via-chat-for-woocommerce' ),
					'description' => esc_html__( 'Welcome to DevDiggers Order via Chat for WooCommerce. Two quick steps and your customers can send you orders on WhatsApp.', 'devdiggers-order-via-chat-for-woocommerce' ),
				],
				'steps'          => [
					'welcome' => [
						'label'         => esc_html__( 'Welcome', 'devdiggers-order-via-chat-for-woocommerce' ),
						'view_callback' => [ $this, 'ddwcpvw_welcome_view' ],
					],
					'general' => [
						'label'         => esc_html__( 'General', 'devdiggers-order-via-chat-for-woocommerce' ),
						'title'         => esc_html__( 'Where should orders go?', 'devdiggers-order-via-chat-for-woocommerce' ),
						'description'   => esc_html__( 'Add the WhatsApp number that receives order requests.', 'devdiggers-order-via-chat-for-woocommerce' ),
						'view_callback' => [ $this, 'ddwcpvw_general_view' ],
						'save_callback' => [ $this, 'ddwcpvw_save_fields' ],
					],
					'display' => [
						'label'         => esc_html__( 'Display', 'devdiggers-order-via-chat-for-woocommerce' ),
						'title'         => esc_html__( 'Where customers can order', 'devdiggers-order-via-chat-for-woocommerce' ),
						'description'   => esc_html__( 'Choose where the WhatsApp button appears. You can fine-tune its look later under Configuration, Display.', 'devdiggers-order-via-chat-for-woocommerce' ),
						'view_callback' => [ $this, 'ddwcpvw_display_view' ],
						'save_callback' => [ $this, 'ddwcpvw_save_fields' ],
					],
					'ready'   => [
						'label'             => esc_html__( 'Ready!', 'devdiggers-order-via-chat-for-woocommerce' ),
						'ready_title'       => esc_html__( 'You are ready to take orders on WhatsApp.', 'devdiggers-order-via-chat-for-woocommerce' ),
						'ready_description' => esc_html__( 'Open one of your products and tap the WhatsApp button to try the whole journey yourself.', 'devdiggers-order-via-chat-for-woocommerce' ),
					],
				],
			];
		}

		/**
		 * Plugin mark shown on the welcome step, the same one as the dashboard header.
		 *
		 * @param int $size Pixel size.
		 * @return string
		 */
		protected function ddwcpvw_get_logo( $size = 100 ) {
			return ddwcpvw_get_brand_mark( $size );
		}

		/**
		 * Welcome step.
		 *
		 * @return void
		 */
		public function ddwcpvw_welcome_view() {
			?>
			<div class="ddfw-setup-wizard-ready ddfw-setup-wizard-onboarding">
				<div class="ddfw-success-icon-wrap">
					<?php echo wp_kses( $this->ddwcpvw_get_logo( 100 ), ddfw_kses_allowed_svg_tags() ); ?>
				</div>
				<h2 class="ddfw-setup-wizard-ready-title"><?php esc_html_e( 'Welcome to Order via Chat!', 'devdiggers-order-via-chat-for-woocommerce' ); ?></h2>
				<p class="ddfw-setup-wizard-ready-desc">
					<?php esc_html_e( 'Let customers order in the app they already use every day. In the next two steps you will add your WhatsApp number and place the button. It takes about a minute.', 'devdiggers-order-via-chat-for-woocommerce' ); ?>
				</p>
			</div>
			<?php
		}

		/**
		 * General step.
		 *
		 * @return void
		 */
		public function ddwcpvw_general_view() {
			$this->ddwcpvw_render_fields(
				[
					[
						'type'        => 'tel',
						'label'       => esc_html__( 'Your WhatsApp Number', 'devdiggers-order-via-chat-for-woocommerce' ),
						'description' => esc_html__( 'With the country code. It can be your personal WhatsApp or a WhatsApp Business app number.', 'devdiggers-order-via-chat-for-woocommerce' ),
						'id'          => 'ddwcpvw-whatsapp-number',
						'name'        => '_ddwcpvw_whatsapp_number',
						'value'       => get_option( '_ddwcpvw_whatsapp_number' ),
						'placeholder' => '+15551234567',
					],
					[
						'type'           => 'checkbox',
						'label'          => esc_html__( 'Status', 'devdiggers-order-via-chat-for-woocommerce' ),
						'checkbox_label' => esc_html__( 'Enable WhatsApp ordering on my store', 'devdiggers-order-via-chat-for-woocommerce' ),
						'description'    => esc_html__( 'You can switch it off at any time without losing your settings.', 'devdiggers-order-via-chat-for-woocommerce' ),
						'id'             => 'ddwcpvw-enabled',
						'name'           => '_ddwcpvw_enabled',
						'value'          => get_option( '_ddwcpvw_enabled', 'yes' ),
					],
					[
						'type'           => 'checkbox',
						'label'          => esc_html__( 'Guest Customers', 'devdiggers-order-via-chat-for-woocommerce' ),
						'checkbox_label' => esc_html__( 'Let visitors order without an account', 'devdiggers-order-via-chat-for-woocommerce' ),
						'description'    => esc_html__( 'Guests add their delivery details in a short popup before WhatsApp opens.', 'devdiggers-order-via-chat-for-woocommerce' ),
						'id'             => 'ddwcpvw-allow-guests',
						'name'           => '_ddwcpvw_allow_guests',
						'value'          => get_option( '_ddwcpvw_allow_guests', 'yes' ),
					],
				]
			);
		}

		/**
		 * Display step.
		 *
		 * @return void
		 */
		public function ddwcpvw_display_view() {
			$this->ddwcpvw_render_fields(
				[
					[
						'type'        => 'select',
						'label'       => esc_html__( 'Product Page', 'devdiggers-order-via-chat-for-woocommerce' ),
						'description' => esc_html__( 'Most orders start here. The default spot sits at the end of the product summary, below the add to cart area.', 'devdiggers-order-via-chat-for-woocommerce' ),
						'id'          => 'ddwcpvw-product-page-position',
						'name'        => '_ddwcpvw_product_page_position',
						'value'       => get_option( '_ddwcpvw_product_page_position', '55' ),
						'options'     => ddwcpvw_get_product_position_options(),
					],
					[
						'type'        => 'select',
						'label'       => esc_html__( 'Cart Page', 'devdiggers-order-via-chat-for-woocommerce' ),
						'description' => esc_html__( 'Lets customers send their whole cart to you in one message.', 'devdiggers-order-via-chat-for-woocommerce' ),
						'id'          => 'ddwcpvw-cart-page-position',
						'name'        => '_ddwcpvw_cart_page_position',
						'value'       => get_option( '_ddwcpvw_cart_page_position', '20' ),
						'options'     => ddwcpvw_get_cart_position_options(),
					],
					[
						'type'           => 'checkbox',
						'label'          => esc_html__( 'Floating Chat Button', 'devdiggers-order-via-chat-for-woocommerce' ),
						'checkbox_label' => esc_html__( 'Show a WhatsApp chat bubble in the corner of every page', 'devdiggers-order-via-chat-for-woocommerce' ),
						'description'    => esc_html__( 'Shoppers can ask a question before they buy.', 'devdiggers-order-via-chat-for-woocommerce' ),
						'id'             => 'ddwcpvw-floating-enabled',
						'name'           => '_ddwcpvw_floating_enabled',
						'value'          => get_option( '_ddwcpvw_floating_enabled' ),
					],
				]
			);
		}

		/**
		 * Save every posted option that belongs to this plugin.
		 *
		 * Options go through the sanitizers registered in DDWCPVW_Admin_Functions.
		 *
		 * @param array $form_data Form data.
		 * @return true|\WP_Error
		 */
		public function ddwcpvw_save_fields( $form_data ) {
			$values  = $this->ddwcpvw_flatten( $form_data );
			$allowed = [];

			foreach ( ( new DDWCPVW_Admin_Functions( [] ) )->ddwcpvw_get_settings() as $options ) {
				$allowed = array_merge( $allowed, array_keys( $options ) );
			}

			// Only the plugin's own settings, so a crafted request cannot write any other option.
			foreach ( array_intersect_key( $values, array_flip( $allowed ) ) as $name => $value ) {
				update_option( $name, sanitize_text_field( $value ) );
			}

			if ( isset( $values['_ddwcpvw_whatsapp_number'] ) && ! get_option( '_ddwcpvw_whatsapp_number' ) ) {
				return new \WP_Error( 'ddwcpvw_number', esc_html__( 'Please enter a valid WhatsApp number with the country code.', 'devdiggers-order-via-chat-for-woocommerce' ) );
			}

			return true;
		}

		/**
		 * Turn the wizard's list of name/value pairs into a map.
		 *
		 * The last value wins, so a ticked checkbox overrides its hidden empty twin.
		 *
		 * @param array $form_data Form data.
		 * @return array
		 */
		protected function ddwcpvw_flatten( $form_data ) {
			$values = [];

			foreach ( (array) $form_data as $field ) {
				if ( ! empty( $field['name'] ) ) {
					$values[ $field['name'] ] = isset( $field['value'] ) ? $field['value'] : '';
				}
			}

			return $values;
		}

		/**
		 * Render a wizard step's fields inside the framework section chrome.
		 *
		 * @param array  $fields Fields.
		 * @param string $note   Optional note below the table.
		 * @return void
		 */
		protected function ddwcpvw_render_fields( $fields, $note = '' ) {
			?>
			<div class="ddfw-fields-section">
				<table class="form-table">
					<tbody>
						<?php
						foreach ( $fields as $field ) {
							DDFW_Form_Field::display_form_field( $field );
						}
						?>
					</tbody>
				</table>
				<?php if ( $note ) : ?>
					<p class="description"><i><?php echo esc_html( $note ); ?></i></p>
				<?php endif; ?>
			</div>
			<?php
		}
	}
}
