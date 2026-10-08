<?php
/**
 * Plugin Name: Order via Chat for WooCommerce
 * Description: Let customers send their product or whole cart to your WhatsApp in one tap, with delivery details, a floating chat button and order request analytics.
 * Plugin URI: https://devdiggers.com/product/woocommerce-purchase-via-whatsapp/
 * Author: DevDiggers
 * Author URI: https://devdiggers.com/
 * Version: 1.0.0
 * Text Domain: order-via-chat-for-woocommerce
 * Domain Path: /i18n
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * WC requires at least: 9.0
 * WC tested up to: 11.2
 * DevDiggersPrefix: ddwcpvw
 * Requires Plugins: woocommerce
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package Order via Chat for WooCommerce
 */

// ddwcpvw: Order via Chat for WooCommerce.

use DDWCPurchaseViaWhatsApp\Includes\DDWCPVW_File_Handler;

defined( 'ABSPATH' ) || exit();

/**
 * Point the plugin constants at this directory.
 *
 * @return void
 */
function ddwcpvw_free_define_constants() {
	defined( 'DDWCPVW_PLUGIN_FILE' ) || define( 'DDWCPVW_PLUGIN_FILE', plugin_dir_path( __FILE__ ) );
	defined( 'DDWCPVW_PLUGIN_URL' ) || define( 'DDWCPVW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! class_exists( 'DDWCPVW_Free_Init' ) ) {
	/**
	 * Free Init class.
	 */
	final class DDWCPVW_Free_Init {
		/**
		 * The single instance of this class.
		 *
		 * @var DDWCPVW_Free_Init|null
		 */
		private static $instance = null;

		/**
		 * Class constructor.
		 */
		public function __construct() {
			add_action( 'init', [ $this, 'ddwcpvw_init' ] );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'ddwcpvw_plugin_settings_link' ] );
			add_filter( 'plugin_row_meta', [ $this, 'ddwcpvw_plugin_row_meta' ], 10, 2 );
		}

		/**
		 * Create a plugin instance.
		 *
		 * @return static
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();

				/**
				 * Fires when the main plugin instance is loaded.
				 *
				 * @since 1.0.0
				 */
				do_action( 'ddwcpvw_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Init function.
		 *
		 * @return void
		 */
		public function ddwcpvw_init() {
			// WordPress.org loads plugin translations automatically.
			if ( ! class_exists( 'WooCommerce' ) ) {
				add_action(
					'admin_notices',
					function () {
						?>
						<div class="notice notice-error">
							<p>
								<?php
								/* translators: %1$s: opening link tag, %2$s: closing link tag */
								printf( esc_html__( 'Order via Chat for WooCommerce is activated but not effective. It requires %1$sWooCommerce%2$s in order to work.', 'order-via-chat-for-woocommerce' ), '<a href="' . esc_url( 'https://wordpress.org/plugins/woocommerce/' ) . '" target="_blank">', '</a>' );
								?>
							</p>
						</div>
						<?php
					}
				);

				return;
			}

			require_once DDWCPVW_PLUGIN_FILE . 'includes/global-functions.php';
			require_once DDWCPVW_PLUGIN_FILE . 'autoload/autoload.php';
			new DDWCPVW_File_Handler();

			// Only on the plugin's own screens (plus AJAX so the dismiss button works).
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing input.
			$on_plugin_page = is_admin() && isset( $_GET['page'] ) && 'ddwcpvw-dashboard' === sanitize_key( wp_unslash( $_GET['page'] ) );

			if ( ( $on_plugin_page || wp_doing_ajax() ) && class_exists( '\DevDiggers\Framework\Includes\DDFW_Review_Notice' ) ) {
				new \DevDiggers\Framework\Includes\DDFW_Review_Notice(
					[
						'plugin_name'   => esc_html__( 'Order via Chat for WooCommerce', 'order-via-chat-for-woocommerce' ),
						'plugin_prefix' => 'ddwcpvw',
						'review_url'    => 'https://wordpress.org/support/plugin/order-via-chat-for-woocommerce/reviews/#new-post',
					]
				);
			}
		}

		/**
		 * Plugin settings link.
		 *
		 * @param array $links Links array.
		 * @return array
		 */
		public function ddwcpvw_plugin_settings_link( $links ) {
			ob_start();
			?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ddwcpvw-dashboard' ) ); ?>"><?php esc_html_e( 'Dashboard', 'order-via-chat-for-woocommerce' ); ?></a>
			|
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ddwcpvw-dashboard&menu=configuration' ) ); ?>"><?php esc_html_e( 'Configuration', 'order-via-chat-for-woocommerce' ); ?></a>
			|
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ddwcpvw-dashboard&setup-wizard=true' ) ); ?>"><?php esc_html_e( 'Setup Wizard', 'order-via-chat-for-woocommerce' ); ?></a>
			|
			<a href="<?php echo esc_url( 'https://devdiggers.com/product/woocommerce-purchase-via-whatsapp/' ); ?>" style="color: #0256ff; font-weight: bold;" target="_blank"><?php esc_html_e( 'Upgrade to Pro', 'order-via-chat-for-woocommerce' ); ?></a>
			<?php
			array_unshift( $links, ob_get_clean() );

			return $links;
		}

		/**
		 * Plugin row meta links.
		 *
		 * @param array  $links Links.
		 * @param string $file  Plugin file.
		 * @return array
		 */
		public function ddwcpvw_plugin_row_meta( $links, $file ) {
			if ( plugin_basename( __FILE__ ) === $file ) {
				$row_meta = [
					'support'       => '<a href="https://devdiggers.com/contact/" aria-label="' . esc_attr__( 'Support', 'order-via-chat-for-woocommerce' ) . '">' . esc_html__( 'Support', 'order-via-chat-for-woocommerce' ) . '</a>',
					'documentation' => '<a href="https://docs.devdiggers.com/woocommerce-purchase-via-whatsapp/" aria-label="' . esc_attr__( 'Documentation', 'order-via-chat-for-woocommerce' ) . '">' . esc_html__( 'Documentation', 'order-via-chat-for-woocommerce' ) . '</a>',
					'review'        => '<a href="https://wordpress.org/support/plugin/order-via-chat-for-woocommerce/reviews/#new-post" target="_blank" title="' . esc_attr__( 'Review', 'order-via-chat-for-woocommerce' ) . '" aria-label="' . esc_attr__( 'Review', 'order-via-chat-for-woocommerce' ) . '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 32" height="10"><path d="M16 26.534L6.111 32 8 20.422l-8-8.2 11.056-1.688L16 0l4.944 10.534L32 12.223l-8 8.2L25.889 32zm40 0L46.111 32 48 20.422l-8-8.2 11.056-1.688L56 0l4.944 10.534L72 12.223l-8 8.2L65.889 32zm40 0L86.111 32 88 20.422l-8-8.2 11.056-1.688L96 0l4.944 10.534L112 12.223l-8 8.2L105.889 32zm40 0L126.111 32 128 20.422l-8-8.2 11.056-1.688L136 0l4.944 10.534L152 12.223l-8 8.2L145.889 32zm40 0L166.111 32 168 20.422l-8-8.2 11.056-1.688L176 0l4.944 10.534L192 12.223l-8 8.2L185.889 32z" fill="#F5A623" fill-rule="evenodd"/></svg></a>',
				];

				$links = array_merge( $links, $row_meta );
			}

			return $links;
		}
	}
}

// Step aside when the premium edition of this plugin is active.
add_action(
	'plugins_loaded',
	function () {
		if ( class_exists( 'DDWCPVW_Init' ) ) {
			return;
		}

		ddwcpvw_free_define_constants();

		DDWCPVW_Free_Init::get_instance();

		// Load DevDiggers Framework if not loaded already.
		if ( ! defined( 'DDFW_LOADED' ) && file_exists( DDWCPVW_PLUGIN_FILE . 'devdiggers-framework/init.php' ) ) {
			$should_load = true;

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing input.
			if ( ! empty( $_GET['page'] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing input.
				$current_page = sanitize_text_field( wp_unslash( $_GET['page'] ) );
				$prefix       = explode( '-', $current_page )[0];

				if ( 0 === strpos( $prefix, 'ddwc' ) || 0 === strpos( $prefix, 'ddwp' ) ) {
					$pro_class  = strtoupper( $prefix ) . '_Init';
					$free_class = strtoupper( $prefix ) . '_Free_Init';

					// Yield to a sibling DevDiggers plugin except on this plugin's own pages.
					if ( class_exists( $free_class ) && ! class_exists( $pro_class ) && 'ddwcpvw' !== $prefix ) {
						$should_load = false;
					}
				}
			}

			if ( $should_load ) {
				require DDWCPVW_PLUGIN_FILE . 'devdiggers-framework/init.php';
			}
		}
	},
	10
);

register_activation_hook(
	__FILE__,
	function () {
		// The review notice counts its delay from here.
		add_option( 'ddwcpvw_installed_at', time() );

		// A fresh store goes through the setup wizard. One that already has a WhatsApp number
		// (an earlier install) is configured, so it is not sent there again.
		if ( get_option( '_ddwcpvw_whatsapp_number' ) ) {
			update_option( 'ddfw_setup_wizard_completed_order-via-chat-for-woocommerce', true );
		} else {
			set_transient( 'ddfw_activation_redirect_order-via-chat-for-woocommerce', true, 30 );
		}
	}
);

// HPOS and Cart/Checkout Blocks compatibility.
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
		}
	}
);
