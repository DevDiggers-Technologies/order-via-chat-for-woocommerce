<?php
/**
 * Front AJAX hooks.
 *
 * @package Order via Chat for WooCommerce
 * @version 1.0.0
 */

namespace DDWCPurchaseViaWhatsApp\Includes\Front;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCPVW_Front_Ajax_Hooks' ) ) {
	/**
	 * Front ajax end hooks class
	 */
	class DDWCPVW_Front_Ajax_Hooks extends DDWCPVW_Front_Ajax_Functions {
		/**
		 * Construct
		 *
		 * @param array $ddwcpvw_configuration Configuration.
		 */
		public function __construct( $ddwcpvw_configuration ) {
			parent::__construct( $ddwcpvw_configuration );

			add_action( 'wp_ajax_nopriv_ddwcpvw_prepare_whatsapp_url', [ $this, 'ddwcpvw_prepare_whatsapp_url' ] );
			add_action( 'wp_ajax_ddwcpvw_prepare_whatsapp_url', [ $this, 'ddwcpvw_prepare_whatsapp_url' ] );
		}
	}
}
