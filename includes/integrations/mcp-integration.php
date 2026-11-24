<?php
/**
 * MCP Integration
 *
 * Puts Order via Chat in front of AI assistants (Claude, Cursor, VS Code and any MCP client). Every
 * feature is registered as a WordPress Ability (WordPress 6.9+) and announced to the DevDiggers AI &
 * MCP hub (DevDiggers Plugins > AI & MCP), which lists the tools on the shared DevDiggers MCP server,
 * turns them on or off, logs calls and offers a playground.
 *
 * Text here goes to AI clients as JSON, so it is translated but not HTML-escaped.
 *
 * Read abilities are for store managers, like the plugin's dashboard. The write ability (exclude a
 * product) changes a setting, so it needs manage_options and stays off until the owner turns it on.
 *
 * @package DevDiggers Order via Chat for WooCommerce
 * @version 1.1.0
 */

namespace DDWCPurchaseViaWhatsApp\Includes\Integrations;

use DDWCPurchaseViaWhatsApp\Helper\DDWCPVW_Stats_Helper;
use DDWCPurchaseViaWhatsApp\Helper\Dashboard\DDWCPVW_Dashboard_Helper;
use DDWCPurchaseViaWhatsApp\Includes\Admin\DDWCPVW_Admin_Functions;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCPVW_MCP_Integration' ) ) {
	/**
	 * MCP Integration class
	 */
	class DDWCPVW_MCP_Integration {
		/**
		 * Ability category and name prefix. The Pro edition uses the same one, and only one of the
		 * two ever runs (Free steps aside when Pro is active), so the hub shows one entry.
		 */
		const CATEGORY = 'order-via-chat';

		/**
		 * Configuration.
		 *
		 * @var array
		 */
		protected $ddwcpvw_configuration;

		/**
		 * Construct.
		 *
		 * @param array $ddwcpvw_configuration Configuration.
		 */
		public function __construct( $ddwcpvw_configuration ) {
			$this->ddwcpvw_configuration = $ddwcpvw_configuration;

			// The Abilities API arrived in WordPress 6.9.
			if ( ! function_exists( 'wp_register_ability' ) ) {
				return;
			}

			add_action( 'wp_abilities_api_categories_init', [ $this, 'ddwcpvw_register_category' ] );
			add_action( 'wp_abilities_api_init', [ $this, 'ddwcpvw_register_abilities' ] );
			add_filter( 'ddfw_mcp_plugins', [ $this, 'ddwcpvw_add_to_hub' ] );
		}

		/**
		 * Announce the plugin to the DevDiggers AI & MCP hub.
		 *
		 * @param array $plugins Hub plugins.
		 * @return array
		 */
		public function ddwcpvw_add_to_hub( $plugins ) {
			$plugins[ self::CATEGORY ] = [
				'name'        => __( 'Order via Chat', 'devdiggers-order-via-chat-for-woocommerce' ),
				'description' => __( 'Ask how many order requests customers sent to your WhatsApp, which products they ask for most, why a product has no WhatsApp button and how ordering on WhatsApp is set up.', 'devdiggers-order-via-chat-for-woocommerce' ),
				'category'    => self::CATEGORY,
				'icon'        => ddwcpvw_get_brand_mark(),
				'url'         => ddwcpvw_get_dashboard_url(),
			];

			return $plugins;
		}

		/**
		 * Category for every ability.
		 *
		 * @return void
		 */
		public function ddwcpvw_register_category() {
			if ( wp_has_ability_category( self::CATEGORY ) ) {
				return;
			}

			wp_register_ability_category(
				self::CATEGORY,
				[
					'label'       => __( 'Order via Chat', 'devdiggers-order-via-chat-for-woocommerce' ),
					'description' => __( 'Ordering on WhatsApp: order requests, most requested products, button availability and settings.', 'devdiggers-order-via-chat-for-woocommerce' ),
				]
			);
		}

		/**
		 * Register every ability.
		 *
		 * @return void
		 */
		public function ddwcpvw_register_abilities() {
			foreach ( $this->ddwcpvw_get_abilities() as $name => $args ) {
				$name = self::CATEGORY . '/' . $name;

				if ( wp_has_ability( $name ) ) {
					continue;
				}

				$readonly = ! empty( $args['readonly'] );

				wp_register_ability(
					$name,
					[
						'label'               => $args['label'],
						'description'         => $args['description'],
						'category'            => self::CATEGORY,
						'input_schema'        => $args['input'],
						'output_schema'       => $args['output'],
						'execute_callback'    => $args['callback'],
						'permission_callback' => isset( $args['permission'] ) ? $args['permission'] : [ $this, 'ddwcpvw_can_read' ],
						'meta'                => [
							'show_in_rest' => true,
							'mcp'          => [
								'public' => true,
								'type'   => isset( $args['type'] ) ? $args['type'] : 'tool',
							],
							'annotations'  => [
								'readonly'        => $readonly,
								'destructive'     => ! $readonly,
								'idempotent'      => $readonly,
								'title'           => $args['label'],
								'readOnlyHint'    => $readonly,
								'destructiveHint' => ! $readonly,
								'idempotentHint'  => $readonly,
								'openWorldHint'   => false,
							],
						],
					]
				);
			}
		}

		/**
		 * Who may read: store managers, like the plugin's dashboard.
		 *
		 * @return bool
		 */
		public function ddwcpvw_can_read() {
			return is_user_logged_in() && current_user_can( 'manage_woocommerce' );
		}

		/**
		 * Who may change settings: the people who can save the plugin's settings (options.php).
		 *
		 * @return bool
		 */
		public function ddwcpvw_can_write() {
			return is_user_logged_in() && current_user_can( 'manage_options' );
		}

		/**
		 * Ability definitions.
		 *
		 * @return array
		 */
		protected function ddwcpvw_get_abilities() {
			$date    = [
				'type'    => 'string',
				'pattern' => '^\d{4}-\d{2}-\d{2}$',
			];
			$product = [
				'product_id' => [
					'type'        => 'integer',
					'minimum'     => 1,
					'description' => __( 'Product or variation ID.', 'devdiggers-order-via-chat-for-woocommerce' ),
				],
				'sku'        => [
					'type'        => 'string',
					'description' => __( 'Product SKU, instead of the ID.', 'devdiggers-order-via-chat-for-woocommerce' ),
				],
			];

			$abilities = [
				'requests-report' => [
					'label'       => __( 'Order requests report', 'devdiggers-order-via-chat-for-woocommerce' ),
					'description' => __( 'How many order requests customers sent to the store\'s WhatsApp in a date range, compared with the period before: requests, how many were whole carts vs single products, units and the value of the requested items at the prices of the moment, plus a per-day list. A request is counted when the customer taps the button and WhatsApp opens with the order message, so it is a lead, not a paid order. Individual requests and customer details are not stored, only these totals (kept 400 days).', 'devdiggers-order-via-chat-for-woocommerce' ),
					'readonly'    => true,
					'input'       => $this->ddwcpvw_input_schema(
						[
							'after'  => $date + [ 'description' => __( 'First day, Y-m-d in the store timezone. Defaults to 29 days before "before".', 'devdiggers-order-via-chat-for-woocommerce' ) ],
							'before' => $date + [ 'description' => __( 'Last day, Y-m-d. Defaults to today.', 'devdiggers-order-via-chat-for-woocommerce' ) ],
						]
					),
					'output'      => $this->ddwcpvw_object_schema(
						[
							'period'          => 'object',
							'currency'        => 'string',
							'totals'          => 'object',
							'previous_period' => 'object',
							'change_percent'  => 'object',
							'by_day'          => 'array',
							'tracked_since'   => 'string',
						]
					),
					'callback'    => [ $this, 'ddwcpvw_requests_report' ],
				],
				'top-products'    => [
					'label'       => __( 'Most requested products', 'devdiggers-order-via-chat-for-woocommerce' ),
					'description' => __( 'The products customers request most on WhatsApp since tracking began (all time, the same list as the dashboard), with request and unit counts, stock and whether the product can still be ordered on WhatsApp today.', 'devdiggers-order-via-chat-for-woocommerce' ),
					'readonly'    => true,
					'input'       => $this->ddwcpvw_input_schema(
						[
							'limit' => [
								'type'    => 'integer',
								'minimum' => 1,
								'maximum' => 50,
								'default' => 10,
							],
						]
					),
					'output'      => $this->ddwcpvw_object_schema(
						[
							'products'      => 'array',
							'tracked_since' => 'string',
						]
					),
					'callback'    => [ $this, 'ddwcpvw_top_products' ],
				],
				'check-product'   => [
					'label'       => __( 'Check a product\'s WhatsApp button', 'devdiggers-order-via-chat-for-woocommerce' ),
					'description' => __( 'Whether one product can be ordered on WhatsApp right now and, if not, every reason why (plugin off, no WhatsApp number, product or category excluded, not purchasable, out of stock). Use it for "why is there no WhatsApp button on this product". Give product_id or sku.', 'devdiggers-order-via-chat-for-woocommerce' ),
					'readonly'    => true,
					'input'       => $this->ddwcpvw_input_schema( $product ),
					'output'      => $this->ddwcpvw_object_schema(
						[
							'product'   => 'object',
							'can_order' => 'boolean',
							'reasons'   => 'array',
							'button_on' => 'array',
						]
					),
					'callback'    => [ $this, 'ddwcpvw_check_product' ],
				],
				'get-settings'    => [
					'label'       => __( 'WhatsApp ordering settings', 'devdiggers-order-via-chat-for-woocommerce' ),
					'description' => __( 'How ordering on WhatsApp is set up: on or off, the store\'s WhatsApp number, guests, catalog mode, hidden prices, excluded products and categories, where the button shows, the floating chat button, the message templates and the setup checklist with what is still missing.', 'devdiggers-order-via-chat-for-woocommerce' ),
					'readonly'    => true,
					'input'       => $this->ddwcpvw_input_schema( [] ),
					'output'      => $this->ddwcpvw_object_schema(
						[
							'ordering'  => 'object',
							'buttons'   => 'object',
							'floating'  => 'object',
							'messages'  => 'object',
							'checklist' => 'array',
						]
					),
					'callback'    => [ $this, 'ddwcpvw_get_settings' ],
				],
				'whatsapp-review' => [
					'type'        => 'prompt',
					'label'       => __( 'WhatsApp ordering review', 'devdiggers-order-via-chat-for-woocommerce' ),
					'description' => __( 'A short review of ordering on WhatsApp for a period: request trend against the period before, the products people ask for, products that can no longer be ordered, and setup gaps, ending with what to do next.', 'devdiggers-order-via-chat-for-woocommerce' ),
					'readonly'    => true,
					'input'       => $this->ddwcpvw_input_schema(
						[
							'after'  => [
								'type'        => 'string',
								'description' => __( 'First day, Y-m-d. Defaults to 29 days before "before".', 'devdiggers-order-via-chat-for-woocommerce' ),
							],
							'before' => [
								'type'        => 'string',
								'description' => __( 'Last day, Y-m-d. Defaults to today.', 'devdiggers-order-via-chat-for-woocommerce' ),
							],
						]
					),
					'output'      => $this->ddwcpvw_object_schema( [ 'messages' => 'array' ] ),
					'callback'    => [ $this, 'ddwcpvw_prompt_review' ],
				],
			];

			// The hub keeps write tools off until the owner turns them on; without the hub there is no switch, so no write tool.
			if ( class_exists( 'DDFW_MCP' ) ) {
				$abilities['set-product-exclusion'] = [
					'label'       => __( 'Exclude or include a product', 'devdiggers-order-via-chat-for-woocommerce' ),
					'description' => __( 'Take a product off WhatsApp ordering (excluded: true) or allow it again (excluded: false), the same as the Excluded products field under Configuration > General. Variations follow their parent, so pass the parent product ID.', 'devdiggers-order-via-chat-for-woocommerce' ),
					'readonly'    => false,
					'input'       => $this->ddwcpvw_input_schema(
						$product + [
							'excluded' => [
								'type'        => 'boolean',
								'description' => __( 'True to exclude the product, false to allow it again.', 'devdiggers-order-via-chat-for-woocommerce' ),
							],
						],
						[ 'excluded' ]
					),
					'output'      => $this->ddwcpvw_object_schema(
						[
							'product'           => 'object',
							'excluded'          => 'boolean',
							'changed'           => 'boolean',
							'excluded_products' => 'array',
						]
					),
					'callback'    => [ $this, 'ddwcpvw_set_product_exclusion' ],
					'permission'  => [ $this, 'ddwcpvw_can_write' ],
				];
			}

			/**
			 * Order via Chat abilities for AI assistants (MCP). Add, change or remove one; names get the "order-via-chat/" prefix.
			 *
			 * @param array $abilities Ability definitions keyed by name.
			 */
			return apply_filters( 'ddwcpvw_mcp_abilities', $abilities );
		}

		/**
		 * Input schema of an object; an empty call is allowed.
		 *
		 * @param array $properties Properties.
		 * @param array $required   Required properties.
		 * @return array
		 */
		protected function ddwcpvw_input_schema( $properties, $required = [] ) {
			$schema = [
				'type'                 => 'object',
				'additionalProperties' => false,
				// An object in JSON ( {} ), so MCP clients read it as "no arguments needed".
				'default'              => (object) [],
			];

			if ( $properties ) {
				$schema['properties'] = $properties;
			}

			if ( $required ) {
				$schema['required'] = $required;
				unset( $schema['default'] );
			}

			return $schema;
		}

		/**
		 * Output schema: an object with the given top-level property types.
		 *
		 * @param array $types Property => JSON type.
		 * @return array
		 */
		protected function ddwcpvw_object_schema( $types ) {
			$properties = [];

			foreach ( $types as $key => $type ) {
				$properties[ $key ] = [ 'type' => $type ];
			}

			return [
				'type'       => 'object',
				'properties' => $properties,
			];
		}

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Shared helpers.
		 * ---------------------------------------------------------------------------------------------
		 */

		/**
		 * The dashboard helper, opened up so the tools compare periods exactly as the dashboard does.
		 *
		 * @return DDWCPVW_Dashboard_Helper
		 */
		protected function ddwcpvw_dashboard() {
			return new class( $this->ddwcpvw_configuration ) extends DDWCPVW_Dashboard_Helper {
				/**
				 * Value with its change against the previous period.
				 *
				 * @param float $value    Value.
				 * @param float $previous Previous value.
				 * @return array
				 */
				public function summary( $value, $previous ) {
					return $this->ddwcpvw_make_summary( $value, $previous );
				}

				/**
				 * The period right before a range.
				 *
				 * @param string $from Y-m-d.
				 * @param string $to   Y-m-d.
				 * @return array
				 */
				public function previous( $from, $to ) {
					return $this->ddwcpvw_get_previous_range(
						[
							'from' => $from,
							'to'   => $to,
						]
					);
				}

				/**
				 * The setup checklist.
				 *
				 * @return array
				 */
				public function checklist() {
					return $this->ddwcpvw_get_checklist();
				}
			};
		}

		/**
		 * A Y-m-d date from input, or the fallback.
		 *
		 * @param array  $input    Input.
		 * @param string $key      Key.
		 * @param string $fallback Fallback.
		 * @return string
		 */
		protected function ddwcpvw_date( $input, $key, $fallback = '' ) {
			$value = isset( $input[ $key ] ) ? (string) $input[ $key ] : '';
			$date  = \DateTime::createFromFormat( '!Y-m-d', $value );

			return $date && $date->format( 'Y-m-d' ) === $value ? $value : $fallback;
		}

		/**
		 * A date range from input: [ after, before ], before defaulting to today.
		 *
		 * @param array $input Input.
		 * @param int   $days  Days before "before" when "after" is missing.
		 * @return array|\WP_Error
		 */
		protected function ddwcpvw_range( $input, $days ) {
			$before = $this->ddwcpvw_date( $input, 'before', current_time( 'Y-m-d' ) );
			$after  = $this->ddwcpvw_date( $input, 'after', gmdate( 'Y-m-d', strtotime( $before . ' -' . $days . ' days' ) ) );

			if ( $after > $before ) {
				return new \WP_Error( 'ddwcpvw_invalid_range', __( 'The start date ("after") must be on or before the end date ("before").', 'devdiggers-order-via-chat-for-woocommerce' ), [ 'status' => 400 ] );
			}

			return [ $after, $before ];
		}

		/**
		 * Price rounded to the store decimals.
		 *
		 * @param mixed $amount Amount.
		 * @return float
		 */
		protected function ddwcpvw_round( $amount ) {
			return round( (float) $amount, wc_get_price_decimals() );
		}

		/**
		 * First day with recorded requests, or an empty string.
		 *
		 * @return string
		 */
		protected function ddwcpvw_tracked_since() {
			$days = array_keys( DDWCPVW_Stats_Helper::get()['days'] );

			return $days ? (string) min( $days ) : '';
		}

		/**
		 * The product a tool was given, by ID or SKU.
		 *
		 * @param array $input Input.
		 * @return \WC_Product|\WP_Error
		 */
		protected function ddwcpvw_find_product( $input ) {
			$product_id = isset( $input['product_id'] ) ? absint( $input['product_id'] ) : 0;

			if ( ! $product_id && ! empty( $input['sku'] ) ) {
				$product_id = wc_get_product_id_by_sku( sanitize_text_field( $input['sku'] ) );
			}

			$product = $product_id ? wc_get_product( $product_id ) : false;

			if ( ! $product ) {
				return new \WP_Error( 'ddwcpvw_product_not_found', __( 'Product not found. Pass a valid product_id or sku.', 'devdiggers-order-via-chat-for-woocommerce' ), [ 'status' => 404 ] );
			}

			return $product;
		}

		/**
		 * A product in a few fields.
		 *
		 * @param \WC_Product $product Product.
		 * @return array
		 */
		protected function ddwcpvw_product_row( $product ) {
			$parent_id = $product->get_parent_id();

			return [
				'id'        => $product->get_id(),
				'parent_id' => $parent_id,
				'name'      => wp_strip_all_tags( $product->get_name() ),
				'sku'       => $product->get_sku(),
				'type'      => $product->get_type(),
				'edit_url'  => admin_url( 'post.php?post=' . ( $parent_id ? $parent_id : $product->get_id() ) . '&action=edit' ),
			];
		}

		/**
		 * Term ID => name for product categories.
		 *
		 * @param int[] $ids Term IDs.
		 * @return array
		 */
		protected function ddwcpvw_category_rows( $ids ) {
			$rows = [];

			foreach ( (array) $ids as $id ) {
				$term   = get_term( $id, 'product_cat' );
				$rows[] = [
					'id'   => (int) $id,
					'name' => $term && ! is_wp_error( $term ) ? $term->name : '',
				];
			}

			return $rows;
		}

		/**
		 * Where the order button shows, as plain labels.
		 *
		 * @return array
		 */
		protected function ddwcpvw_placements() {
			$config = $this->ddwcpvw_configuration;
			$where  = [];
			$map    = [
				'product_page' => [ $config['product_page_position'], ddwcpvw_get_product_position_options() ],
				'shop_pages'   => [ $config['shop_page_position'], ddwcpvw_get_shop_position_options() ],
				'cart'         => [ $config['cart_page_position'], ddwcpvw_get_cart_position_options() ],
			];

			foreach ( $map as $page => $setting ) {
				list( $value, $options ) = $setting;

				$where[ $page ] = '' === (string) $value || ! isset( $options[ $value ] ) ? '' : html_entity_decode( $options[ $value ], ENT_QUOTES );
			}

			return $where;
		}

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Abilities.
		 * ---------------------------------------------------------------------------------------------
		 */

		/**
		 * Order requests in a date range against the period before.
		 *
		 * @param array $input Input.
		 * @return array|\WP_Error
		 */
		public function ddwcpvw_requests_report( $input = [] ) {
			$input = (array) $input;
			$range = $this->ddwcpvw_range( $input, 29 );

			if ( is_wp_error( $range ) ) {
				return $range;
			}

			list( $after, $before ) = $range;

			$dashboard = $this->ddwcpvw_dashboard();
			$previous  = $dashboard->previous( $after, $before );
			$current   = DDWCPVW_Stats_Helper::get_range( $after, $before );
			$earlier   = DDWCPVW_Stats_Helper::get_range( $previous['from'], $previous['to'] );
			$change    = [];

			foreach ( [ 'requests', 'cart', 'units', 'value' ] as $key ) {
				$label            = 'cart' === $key ? 'cart_requests' : $key;
				$change[ $label ] = $dashboard->summary( $current[ $key ], $earlier[ $key ] )['change'];
			}

			$by_day = [];

			foreach ( $current['by_date']['requests'] as $day => $requests ) {
				$by_day[] = [
					'date'     => $day,
					'requests' => (int) $requests,
					'value'    => $this->ddwcpvw_round( $current['by_date']['value'][ $day ] ),
				];
			}

			return [
				'period'          => [
					'after'  => $after,
					'before' => $before,
				],
				'currency'        => get_woocommerce_currency(),
				'totals'          => [
					'requests'         => (int) $current['requests'],
					'cart_requests'    => (int) $current['cart'],
					'product_requests' => (int) $current['requests'] - (int) $current['cart'],
					'units'            => (int) $current['units'],
					'value'            => $this->ddwcpvw_round( $current['value'] ),
				],
				'previous_period' => [
					'after'         => $previous['from'],
					'before'        => $previous['to'],
					'requests'      => (int) $earlier['requests'],
					'cart_requests' => (int) $earlier['cart'],
					'units'         => (int) $earlier['units'],
					'value'         => $this->ddwcpvw_round( $earlier['value'] ),
				],
				// Null: nothing in the previous period, so there is no percentage to give.
				'change_percent'  => $change,
				'by_day'          => $by_day,
				'tracked_since'   => $this->ddwcpvw_tracked_since(),
			];
		}

		/**
		 * Most requested products, all time.
		 *
		 * @param array $input Input.
		 * @return array
		 */
		public function ddwcpvw_top_products( $input = [] ) {
			$input = (array) $input;
			$limit = isset( $input['limit'] ) ? min( 50, max( 1, absint( $input['limit'] ) ) ) : 10;
			$map   = DDWCPVW_Stats_Helper::get()['products'];
			$rows  = [];

			// Same order as the dashboard: most requests first.
			uasort(
				$map,
				function ( $first, $second ) {
					return $second['requests'] <=> $first['requests'];
				}
			);

			foreach ( $map as $product_id => $totals ) {
				$product = wc_get_product( $product_id );

				if ( ! $product ) {
					continue;
				}

				$rows[] = $this->ddwcpvw_product_row( $product ) + [
					'requests'     => (int) $totals['requests'],
					'units'        => (int) $totals['units'],
					'stock_status' => $product->get_stock_status(),
					'can_order'    => ddwcpvw_is_product_available( $product, $this->ddwcpvw_configuration ),
				];

				if ( count( $rows ) === $limit ) {
					break;
				}
			}

			return [
				'products'      => $rows,
				'tracked_since' => $this->ddwcpvw_tracked_since(),
			];
		}

		/**
		 * Whether a product can be ordered on WhatsApp, and why not.
		 *
		 * @param array $input Input.
		 * @return array|\WP_Error
		 */
		public function ddwcpvw_check_product( $input = [] ) {
			$product = $this->ddwcpvw_find_product( (array) $input );

			if ( is_wp_error( $product ) ) {
				return $product;
			}

			$config    = $this->ddwcpvw_configuration;
			$parent_id = $product->get_parent_id();
			$reasons   = [];

			if ( 'yes' !== $config['enabled'] ) {
				$reasons[] = __( 'Ordering on WhatsApp is switched off (Configuration > General).', 'devdiggers-order-via-chat-for-woocommerce' );
			}

			if ( ! $config['whatsapp_number'] ) {
				$reasons[] = __( 'No WhatsApp number is saved, so every button is hidden (Configuration > General).', 'devdiggers-order-via-chat-for-woocommerce' );
			}

			if ( array_intersect( array_filter( [ $product->get_id(), $parent_id ] ), $config['excluded_products'] ) ) {
				$reasons[] = __( 'The product is in Excluded products.', 'devdiggers-order-via-chat-for-woocommerce' );
			}

			$excluded_categories = array_intersect( wc_get_product_cat_ids( $parent_id ? $parent_id : $product->get_id() ), $config['excluded_categories'] );

			if ( $excluded_categories ) {
				/* translators: %s: category names. */
				$reasons[] = sprintf( __( 'Its category is excluded: %s.', 'devdiggers-order-via-chat-for-woocommerce' ), implode( ', ', wp_list_pluck( $this->ddwcpvw_category_rows( $excluded_categories ), 'name' ) ) );
			}

			if ( ! $product->is_purchasable() ) {
				$reasons[] = __( 'The product cannot be bought (not published or no price).', 'devdiggers-order-via-chat-for-woocommerce' );
			}

			if ( 'yes' === $config['hide_out_of_stock'] && ! $product->is_in_stock() ) {
				$reasons[] = __( 'It is out of stock and out of stock products are hidden.', 'devdiggers-order-via-chat-for-woocommerce' );
			}

			$available = ddwcpvw_is_product_available( $product, $config );

			if ( ! $available && ! $reasons ) {
				$reasons[] = __( 'Another plugin or custom code hides it (the ddwcpvw_is_product_available filter).', 'devdiggers-order-via-chat-for-woocommerce' );
			}

			$button_on = array_keys( array_filter( $this->ddwcpvw_placements() ) );

			return [
				'product'   => $this->ddwcpvw_product_row( $product ) + [ 'stock_status' => $product->get_stock_status() ],
				'can_order' => $available && 'yes' === $config['enabled'] && (bool) $config['whatsapp_number'],
				'reasons'   => $reasons,
				// Pages that show the button (the shortcode and block work anywhere).
				'button_on' => $button_on,
			];
		}

		/**
		 * Safe settings summary. An allow-list: nothing is dumped.
		 *
		 * @return array
		 */
		public function ddwcpvw_get_settings() {
			$config            = $this->ddwcpvw_configuration;
			$excluded_products = [];

			foreach ( $config['excluded_products'] as $product_id ) {
				$product             = wc_get_product( $product_id );
				$excluded_products[] = [
					'id'   => (int) $product_id,
					'name' => $product ? wp_strip_all_tags( $product->get_name() ) : '',
				];
			}

			$checklist = [];

			foreach ( $this->ddwcpvw_dashboard()->checklist() as $step ) {
				$checklist[] = [
					'done'  => (bool) $step['done'],
					'title' => html_entity_decode( $step['title'], ENT_QUOTES ),
				];
			}

			return [
				'ordering'  => [
					'enabled'             => 'yes' === $config['enabled'],
					'whatsapp_number'     => (string) $config['whatsapp_number'],
					'ready'               => 'yes' === $config['enabled'] && (bool) $config['whatsapp_number'],
					'allow_guests'        => 'yes' === $config['allow_guests'],
					'hide_out_of_stock'   => 'yes' === $config['hide_out_of_stock'],
					'catalog_mode'        => 'yes' === $config['catalog_mode'],
					'hide_price'          => 'yes' === $config['hide_price'],
					'excluded_products'   => $excluded_products,
					'excluded_categories' => $this->ddwcpvw_category_rows( $config['excluded_categories'] ),
					'tracking_events'     => 'yes' === $config['tracking'],
				],
				'buttons'   => [
					'label'          => html_entity_decode( $config['purchase_button_label'], ENT_QUOTES ),
					'placements'     => $this->ddwcpvw_placements(),
					// all, mobile (phones and tablets) or desktop.
					'devices'        => $config['button_devices'],
					'order_received' => 'yes' === $config['thankyou_enabled'],
				],
				'floating'  => [
					'enabled'     => 'yes' === $config['floating_enabled'],
					// Falls back to the main number when empty, as on the storefront.
					'number'      => (string) ( $config['floating_number'] ? $config['floating_number'] : $config['whatsapp_number'] ),
					'position'    => $config['floating_position'],
					'pages'       => $config['floating_pages'],
					'hide_mobile' => 'yes' === $config['floating_hide_mobile'],
				],
				'messages'  => [
					'request_template'  => $config['request_template'],
					'thankyou_template' => $config['thankyou_template'],
				],
				'checklist' => $checklist,
				'edit_url'  => ddwcpvw_get_dashboard_url( [ 'menu' => 'configuration' ] ),
			];
		}

		/**
		 * Exclude a product from WhatsApp ordering, or allow it again.
		 *
		 * @param array $input Input.
		 * @return array|\WP_Error
		 */
		public function ddwcpvw_set_product_exclusion( $input = [] ) {
			$input   = (array) $input;
			$product = $this->ddwcpvw_find_product( $input );

			if ( is_wp_error( $product ) ) {
				return $product;
			}

			if ( ! isset( $input['excluded'] ) ) {
				return new \WP_Error( 'ddwcpvw_missing_excluded', __( 'Pass "excluded": true to exclude the product or false to allow it again.', 'devdiggers-order-via-chat-for-woocommerce' ), [ 'status' => 400 ] );
			}

			if ( $product->get_parent_id() ) {
				return new \WP_Error(
					'ddwcpvw_variation',
					/* translators: %d: parent product ID. */
					sprintf( __( 'Exclusions apply to whole products. Use the parent product ID %d.', 'devdiggers-order-via-chat-for-woocommerce' ), $product->get_parent_id() ),
					[ 'status' => 400 ]
				);
			}

			$excluded = (bool) $input['excluded'];
			$current  = array_map( 'absint', (array) get_option( '_ddwcpvw_excluded_products', [] ) );
			$changed  = in_array( $product->get_id(), $current, true ) !== $excluded;
			$ids      = $excluded ? array_merge( $current, [ $product->get_id() ] ) : array_diff( $current, [ $product->get_id() ] );
			// The same sanitizer the settings screen saves this option with.
			$ids = array_values( array_unique( ( new DDWCPVW_Admin_Functions( $this->ddwcpvw_configuration ) )->ddwcpvw_sanitize_ids( $ids ) ) );

			if ( $changed ) {
				update_option( '_ddwcpvw_excluded_products', $ids );

				// AI made changes leave a trail under WooCommerce > Status > Logs.
				wc_get_logger()->info(
					sprintf( 'Product #%1$d %2$s WhatsApp ordering by user #%3$d through MCP.', $product->get_id(), $excluded ? 'excluded from' : 'allowed again for', get_current_user_id() ),
					[ 'source' => 'ddwcpvw-mcp' ]
				);
			}

			return [
				'product'           => $this->ddwcpvw_product_row( $product ),
				'excluded'          => $excluded,
				'changed'           => $changed,
				'excluded_products' => $ids,
			];
		}

		/**
		 * Review prompt.
		 *
		 * @param array $input Input.
		 * @return array
		 */
		public function ddwcpvw_prompt_review( $input = [] ) {
			$input  = (array) $input;
			$before = $this->ddwcpvw_date( $input, 'before', current_time( 'Y-m-d' ) );
			$after  = $this->ddwcpvw_date( $input, 'after', gmdate( 'Y-m-d', strtotime( $before . ' -29 days' ) ) );

			$text = sprintf(
				/* translators: 1: first day, 2: last day. */
				__(
					'Review ordering on WhatsApp from %1$s to %2$s.

Use the Order via Chat tools:
1. order-via-chat-requests-report with after and before set to those days.
2. order-via-chat-top-products with limit 10.
3. order-via-chat-get-settings for the setup and its checklist.
4. order-via-chat-check-product for any top product whose can_order is false.

Then write a short review for the store owner:
- Requests, cart vs single product requests and requested value, with the change against the previous period in percent (say "new" when the previous period had none).
- The busiest days, and the products people ask for most.
- Popular products that can no longer be ordered on WhatsApp, and why.
- Setup gaps from the checklist and settings (no number, button not placed, ordering off).
- Up to three next steps.
Remember a request is a lead (WhatsApp opened with the order), not a paid order. Use the store currency, round money to 2 decimals and keep it under 250 words. Say plainly when there were no requests.',
					'devdiggers-order-via-chat-for-woocommerce'
				),
				$after,
				$before
			);

			return [
				'description' => __( 'WhatsApp ordering review', 'devdiggers-order-via-chat-for-woocommerce' ),
				'messages'    => [
					[
						'role'    => 'user',
						'content' => [
							'type' => 'text',
							'text' => $text,
						],
					],
				],
			];
		}
	}
}
