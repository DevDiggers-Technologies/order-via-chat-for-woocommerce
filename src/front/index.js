"use strict";

import './front.less';

const { ajax, i18n, tracking } = window.ddwcpvwFrontObj || { ajax: {}, i18n: {} };

/**
 * Report the order request to whatever analytics the store already runs. No tags, no calls.
 */
const track = source => {
	if ( ! tracking ) {
		return;
	}

	const params = { method: 'whatsapp', ddwcpvw_source: source.type, item_id: source.productId || undefined };

	if ( 'function' === typeof window.gtag ) {
		window.gtag( 'event', 'generate_lead', params );
	} else if ( Array.isArray( window.dataLayer ) ) {
		window.dataLayer.push( { event: 'generate_lead', ...params } );
	}

	if ( 'function' === typeof window.fbq ) {
		window.fbq( 'track', 'Contact', { content_category: 'whatsapp_order' } );
	}
};

/**
 * The product container a button belongs to, so pages with several products (quick views,
 * related products) never read another product's quantity or options.
 */
const getProductScope = button => button.closest( '.product' ) || document;

/**
 * Keep a single product button in step with the chosen variation.
 */
const bindVariationForms = () => {
	if ( ! window.jQuery ) {
		return;
	}

	window.jQuery( '.variations_form' ).each( ( index, form ) => {
		const scope = form.closest( '.product' ) || document;
		const button = scope.querySelector( '.ddwcpvw-purchase-button[data-action="product"]' );

		if ( ! button || form.dataset.ddwcpvwBound ) {
			return;
		}

		form.dataset.ddwcpvwBound = 'true';
		button.dataset.variable = 'yes';
		button.setAttribute( 'aria-disabled', 'true' );

		window.jQuery( form )
			.on( 'found_variation', ( event, variation ) => {
				const attributes = {};

				form.querySelectorAll( '[name^="attribute_"]' ).forEach( field => {
					attributes[ field.name ] = field.value;
				} );

				button.dataset.productId = variation.variation_id;
				button.dataset.attributes = JSON.stringify( attributes );
				button.setAttribute( 'aria-disabled', variation.is_purchasable && variation.is_in_stock ? 'false' : 'true' );
			} )
			.on( 'reset_data', () => {
				button.dataset.attributes = '';
				button.setAttribute( 'aria-disabled', 'true' );
			} );

		// Default attributes can resolve a variation before this script binds.
		const variationField = form.querySelector( '.variation_id' );

		if ( variationField && parseInt( variationField.value, 10 ) > 0 ) {
			const attributes = {};

			form.querySelectorAll( '[name^="attribute_"]' ).forEach( field => {
				attributes[ field.name ] = field.value;
			} );

			button.dataset.productId = variationField.value;
			button.dataset.attributes = JSON.stringify( attributes );
			button.setAttribute( 'aria-disabled', 'false' );
		}
	} );
};

const closePopup = () => {
	document.querySelectorAll( '.ddwcpvw-popup' ).forEach( popup => popup.remove() );
	document.body.classList.remove( 'ddwcpvw-popup-active' );
};

/**
 * Open WhatsApp. window.open() after an AJAX call is blocked by some browsers, so fall back to
 * navigating the current tab, which on phones hands straight over to the WhatsApp app.
 */
const openWhatsApp = url => {
	const isMobile = /Android|iPhone|iPad|iPod/i.test( navigator.userAgent );
	const opened = isMobile ? null : window.open( url, '_blank', 'noopener' );

	if ( ! opened ) {
		window.location.href = url;
	}
};

const showPopup = ( html, source ) => {
	closePopup();
	document.body.insertAdjacentHTML( 'beforeend', html );
	document.body.classList.add( 'ddwcpvw-popup-active' );

	const popup = document.querySelector( '.ddwcpvw-popup' );
	const form = popup.querySelector( 'form' );

	// Remember what the customer was buying, the popup has no product form of its own.
	Object.entries( source ).forEach( ( [ key, value ] ) => {
		form.dataset[ key ] = value;
	} );

	if ( window.jQuery ) {
		window.jQuery( document.body ).trigger( 'country_to_state_changed' );
		window.jQuery( form ).find( ':input.country_to_state' ).trigger( 'change' );
	}

	const firstField = form.querySelector( 'input:not([type="hidden"]), select' );

	if ( firstField ) {
		firstField.focus();
	}
};

// A short inline notice under the button, instead of a blocking browser alert.
const notify = ( button, message ) => {
	const previous = button.parentNode.querySelector( '.ddwcpvw-inline-notice' );

	if ( previous ) {
		previous.remove();
	}

	const notice = document.createElement( 'div' );

	notice.className = 'ddwcpvw-inline-notice';
	notice.setAttribute( 'role', 'alert' );
	notice.textContent = message;
	button.insertAdjacentElement( 'afterend', notice );
	setTimeout( () => notice.remove(), 6000 );
};

const request = ( source, button, form = null ) => {
	const formData = form ? new FormData( form ) : new FormData();

	formData.append( 'action', 'ddwcpvw_prepare_whatsapp_url' );
	formData.append( 'nonce', ajax.ajaxNonce );
	formData.append( 'type', source.type );
	formData.append( 'product_id', source.productId );
	formData.append( 'quantity', source.quantity );
	formData.append( 'attributes', source.attributes );
	formData.append( 'guest', form ? 'yes' : 'no' );

	button.classList.add( 'ddwcpvw-loading' );
	button.disabled = true;

	return fetch( ajax.ajaxUrl, {
		method: 'POST',
		credentials: 'same-origin',
		body: formData,
	} )
		.then( response => response.json() )
		.then( response => {
			const data = response.data || {};

			if ( response.success && data.html ) {
				showPopup( data.html, source );
				return;
			}

			if ( response.success && data.url ) {
				closePopup();
				track( source );
				openWhatsApp( data.url );
				return;
			}

			if ( data.redirect ) {
				// Show why before leaving, or the customer lands on the login page with no explanation.
				if ( data.message ) {
					notify( button, data.message );
				}
				setTimeout( () => {
					window.location.href = data.redirect;
				}, data.message ? 1500 : 0 );
				return;
			}

			const message = data.message || i18n.error;
			const inlineError = form ? form.querySelector( '.ddwcpvw-popup-ajax-error' ) : null;

			if ( inlineError ) {
				inlineError.textContent = message;
				inlineError.hidden = false;
			} else {
				notify( button, message );
			}
		} )
		.catch( () => notify( button, i18n.error ) )
		.finally( () => {
			button.classList.remove( 'ddwcpvw-loading' );
			button.disabled = false;
		} );
};

document.addEventListener( 'click', event => {
	const button = event.target.closest( '.ddwcpvw-purchase-button[data-action]' );

	if ( event.target.classList.contains( 'ddwcpvw-popup' ) || event.target.closest( '.ddwcpvw-close-popup' ) ) {
		closePopup();
		return;
	}

	if ( ! button ) {
		return;
	}

	event.preventDefault();

	if ( 'yes' === button.dataset.variable && 'true' === button.getAttribute( 'aria-disabled' ) ) {
		notify( button, i18n.chooseOptions );
		return;
	}

	const scope = getProductScope( button );
	const quantityField = 'product' === button.dataset.action && ! button.classList.contains( 'ddwcpvw-loop-button' ) ? scope.querySelector( 'form.cart .qty' ) : null;

	request(
		{
			type: button.dataset.action,
			productId: button.dataset.productId || '',
			quantity: quantityField ? quantityField.value : 1,
			attributes: button.dataset.attributes || '',
		},
		button
	);
} );

document.addEventListener( 'submit', event => {
	const form = event.target.closest( '#ddwcpvw-guest-address-form' );

	if ( ! form ) {
		return;
	}

	event.preventDefault();

	const error = form.querySelector( '.ddwcpvw-popup-ajax-error' );

	if ( error ) {
		error.hidden = true;
	}

	request(
		{
			type: form.dataset.type,
			productId: form.dataset.productId,
			quantity: form.dataset.quantity,
			attributes: form.dataset.attributes,
		},
		form.querySelector( '.ddwcpvw-popup-submit' ),
		form
	);
} );

document.addEventListener( 'keydown', event => {
	if ( 'Escape' === event.key ) {
		closePopup();
	}
} );

if ( 'loading' === document.readyState ) {
	document.addEventListener( 'DOMContentLoaded', bindVariationForms );
} else {
	bindVariationForms();
}

if ( window.jQuery ) {
	// Quick view plugins load product forms after the page.
	window.jQuery( document.body ).on( 'wc_variation_form', bindVariationForms );
}
