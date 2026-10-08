"use strict";

import { registerBlockType } from '@wordpress/blocks';
import { createElement as el } from '@wordpress/element';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

// Rendered by the [ddwcpvw_button] shortcode on the server, so the editor shows the real button.
registerBlockType( 'ddwcpvw/order-button', {
	apiVersion: 3,
	title: __( 'Order on WhatsApp', 'order-via-chat-for-woocommerce' ),
	description: __( 'A button that sends a product or the cart to your WhatsApp.', 'order-via-chat-for-woocommerce' ),
	category: 'woocommerce',
	icon: 'whatsapp',
	keywords: [ 'whatsapp', 'order', 'chat' ],
	usesContext: [ 'postId' ],
	edit: ( { attributes, setAttributes } ) => el(
		'div',
		useBlockProps(),
		el(
			InspectorControls,
			null,
			el(
				PanelBody,
				{ title: __( 'Settings', 'order-via-chat-for-woocommerce' ) },
				el( SelectControl, {
					label: __( 'Send', 'order-via-chat-for-woocommerce' ),
					value: attributes.type,
					options: [
						{ value: 'product', label: __( 'A product', 'order-via-chat-for-woocommerce' ) },
						{ value: 'cart', label: __( 'The whole cart', 'order-via-chat-for-woocommerce' ) },
					],
					onChange: type => setAttributes( { type } ),
				} ),
				'product' === attributes.type && el( TextControl, {
					label: __( 'Product ID', 'order-via-chat-for-woocommerce' ),
					help: __( 'Leave empty to use the product of the current page.', 'order-via-chat-for-woocommerce' ),
					type: 'number',
					value: attributes.productId || '',
					onChange: value => setAttributes( { productId: parseInt( value, 10 ) || 0 } ),
				} )
			)
		),
		el( ServerSideRender, {
			block: 'ddwcpvw/order-button',
			attributes,
			EmptyResponsePlaceholder: () => el( 'p', null, __( 'The WhatsApp button shows here when this product can be ordered on WhatsApp and the plugin has a number set.', 'order-via-chat-for-woocommerce' ) ),
		} )
	),
	save: () => null,
} );
