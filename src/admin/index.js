"use strict";

import './admin.less';

const $ = window.jQuery;

const bindOnce = ( element, key ) => {
	if ( ! element || element.dataset[ key ] ) {
		return false;
	}

	element.dataset[ key ] = 'true';
	return true;
};

const initButtonPreview = root => {
	const preview = root.querySelector( '.ddwcpvw-button-preview' );

	if ( ! bindOnce( preview, 'ddwcpvwBound' ) ) {
		return;
	}

	const field = id => root.querySelector( `#${ id }` );
	const update = () => {
		const label = field( 'ddwcpvw-purchase-button-label' );
		const background = field( 'ddwcpvw-purchase-button-background-color' );
		const color = field( 'ddwcpvw-purchase-button-text-color' );
		const radius = field( 'ddwcpvw-button-radius' );
		const icon = field( 'ddwcpvw-button-show-icon' );
		const fullWidth = field( 'ddwcpvw-button-full-width' );

		preview.querySelector( '.ddwcpvw-button-preview-label' ).textContent = label && label.value ? label.value : label.placeholder;
		preview.style.backgroundColor = background ? background.value : '';
		preview.style.color = color ? color.value : '';
		preview.style.borderRadius = radius ? `${ parseInt( radius.value, 10 ) || 0 }px` : '';
		preview.querySelector( '.ddwcpvw-button-preview-icon' ).classList.toggle( 'ddwcpvw-hide', icon && ! icon.checked );
		preview.classList.toggle( 'ddwcpvw-full-width', !! ( fullWidth && fullWidth.checked ) );
	};

	root.addEventListener( 'input', update );
	root.addEventListener( 'change', update );

	if ( $ ) {
		// The WordPress color picker reports changes through Iris, not native events.
		$( root ).on( 'irischange', '.ddfw-color-picker', ( event, ui ) => {
			event.target.value = ui.color.toString();
			update();
		} );
	}
};

/**
 * Keep the order box link in step with the message, so edits are what WhatsApp opens with.
 */
const initOrderBox = root => {
	root.querySelectorAll( '.ddwcpvw-order-box' ).forEach( box => {
		if ( ! bindOnce( box, 'ddwcpvwBound' ) ) {
			return;
		}

		const link = box.querySelector( '.ddwcpvw-order-box-send' );
		const textarea = box.querySelector( '.ddwcpvw-order-box-message' );

		textarea.addEventListener( 'input', () => {
			link.href = `https://wa.me/${ box.dataset.phone }?text=${ encodeURIComponent( textarea.value ) }`;
		} );
	} );
};

const initializeAdmin = ( root = document ) => {
	initButtonPreview( root );
	initOrderBox( root );
};

initializeAdmin();

if ( ! window.ddwcpvwAdminViewListener ) {
	window.ddwcpvwAdminViewListener = true;
	document.addEventListener( 'ddfw:view:load', event => initializeAdmin( event.detail.container ) );
}
