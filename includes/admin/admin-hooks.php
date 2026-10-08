<?php
/**
 * Admin hooks.
 *
 * @package Order via Chat for WooCommerce
 * @version 1.0.0
 */

namespace DDWCPurchaseViaWhatsApp\Includes\Admin;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCPVW_Admin_Hooks' ) ) {
	/**
	 * Admin hook handler.
	 */
	class DDWCPVW_Admin_Hooks extends DDWCPVW_Admin_Functions {
		/**
		 * Register hooks.
		 *
		 * @param array $ddwcpvw_configuration Plugin configuration.
		 */
		public function __construct( $ddwcpvw_configuration ) {
			parent::__construct( $ddwcpvw_configuration );

			add_action( 'admin_init', [ $this, 'ddwcpvw_register_settings' ] );

			add_action( 'add_meta_boxes', [ $this, 'ddwcpvw_add_order_meta_box' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'ddwcpvw_enqueue_order_screen_scripts' ] );
		}
	}
}
