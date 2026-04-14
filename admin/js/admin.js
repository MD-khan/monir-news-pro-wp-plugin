/**
 * MonirNews Pro — Admin JavaScript v2.0.0
 *
 * Handles:
 *  1. Ad-type radio → show/hide media vs HTML sections
 *  2. WordPress media library upload via wp.media()
 *  3. Ad zone change → auto-fill width/height
 *  4. Delete-form confirmation dialog
 *  5. Status toggle via AJAX (active ↔ paused)
 */

/* global wp, mnpAdmin */

( function () {
	'use strict';

	// -------------------------------------------------------------------------
	// Zone → dimension defaults
	// -------------------------------------------------------------------------
	var zoneDimensions = {
		'header':          { w: 728, h: 90  },
		'sidebar-top':     { w: 300, h: 250 },
		'sidebar-middle':  { w: 300, h: 250 },
		'in-content':      { w: 468, h: 60  },
		'footer':          { w: 728, h: 90  },
		'mobile-top':      { w: 320, h: 50  },
	};

	// -------------------------------------------------------------------------
	// 1. Ad-type toggle
	// -------------------------------------------------------------------------
	var typeRadios    = document.querySelectorAll( 'input[name="type"]' );
	var mediaSection  = document.getElementById( 'mnp-media-section' );
	var htmlSection   = document.getElementById( 'mnp-html-section' );

	function applyTypeVisibility( type ) {
		if ( ! mediaSection || ! htmlSection ) { return; }
		if ( 'html' === type ) {
			mediaSection.style.display = 'none';
			htmlSection.style.display  = '';
		} else {
			mediaSection.style.display = '';
			htmlSection.style.display  = 'none';
		}
	}

	typeRadios.forEach( function ( radio ) {
		radio.addEventListener( 'change', function () {
			applyTypeVisibility( this.value );
		} );
	} );

	// Apply on page load (handles pre-selected value on edit screen).
	var checkedType = document.querySelector( 'input[name="type"]:checked' );
	if ( checkedType ) {
		applyTypeVisibility( checkedType.value );
	}

	// -------------------------------------------------------------------------
	// 2. WordPress media library upload
	// -------------------------------------------------------------------------
	var uploadBtn    = document.getElementById( 'mnp-upload-btn' );
	var mediaIdInput = document.getElementById( 'mnp-media-id' );
	var mediaUrlInput = document.getElementById( 'mnp-media-url' );
	var mediaPreview = document.getElementById( 'mnp-media-preview' );

	if ( uploadBtn && typeof wp !== 'undefined' && wp.media ) {
		uploadBtn.addEventListener( 'click', function ( e ) {
			e.preventDefault();

			var frame = wp.media( {
				title:    mnpAdmin.mediaTitle,
				button:   { text: mnpAdmin.mediaButton },
				multiple: false,
			} );

			frame.on( 'select', function () {
				var att = frame.state().get( 'selection' ).first().toJSON();

				if ( mediaIdInput )  { mediaIdInput.value  = att.id;  }
				if ( mediaUrlInput ) { mediaUrlInput.value = att.url; }

				if ( mediaPreview ) {
					var currentType = document.querySelector( 'input[name="type"]:checked' );
					var adType = currentType ? currentType.value : 'image';

					if ( 'video' === adType ) {
						mediaPreview.innerHTML =
							'<video src="' + att.url + '" controls style="max-width:300px;max-height:200px;border-radius:4px;"></video>';
					} else {
						mediaPreview.innerHTML =
							'<img src="' + att.url + '" style="max-width:300px;max-height:200px;border-radius:4px;border:1px solid #eee;" alt="">';
					}
					mediaPreview.style.display = 'block';
				}
			} );

			frame.open();
		} );
	}

	// -------------------------------------------------------------------------
	// 3. Zone change → auto-fill dimensions
	// -------------------------------------------------------------------------
	var zoneSelect  = document.getElementById( 'mnp-zone' );
	var widthInput  = document.getElementById( 'mnp-width' );
	var heightInput = document.getElementById( 'mnp-height' );

	if ( zoneSelect ) {
		zoneSelect.addEventListener( 'change', function () {
			var dims = zoneDimensions[ this.value ];
			if ( dims ) {
				if ( widthInput )  { widthInput.value  = dims.w; }
				if ( heightInput ) { heightInput.value = dims.h; }
			}
		} );
	}

	// -------------------------------------------------------------------------
	// 4. Delete confirmation
	// -------------------------------------------------------------------------
	var deleteForms = document.querySelectorAll( '.mnp-delete-form' );

	deleteForms.forEach( function ( form ) {
		form.addEventListener( 'submit', function ( e ) {
			if ( ! window.confirm( mnpAdmin.confirmDelete ) ) {
				e.preventDefault();
			}
		} );
	} );

	// -------------------------------------------------------------------------
	// 5. Status toggle via AJAX
	// -------------------------------------------------------------------------
	var toggleBtns = document.querySelectorAll( '.mnp-toggle-status' );

	toggleBtns.forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			var self  = this;
			var adId  = self.getAttribute( 'data-id' );
			var badge = document.getElementById( 'mnp-status-' + adId );

			var body = 'action=mnp_toggle_ad&id=' + encodeURIComponent( adId )
				+ '&nonce=' + encodeURIComponent( mnpAdmin.toggleNonce );

			self.disabled = true;

			fetch( mnpAdmin.ajaxUrl, {
				method:  'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body:    body,
			} )
				.then( function ( r ) { return r.json(); } )
				.then( function ( data ) {
					if ( data.success && badge ) {
						var newStatus = data.data.status;
						badge.className   = 'mnp-status ' + newStatus;
						badge.textContent = newStatus;
						self.textContent  = 'active' === newStatus ? 'Pause' : 'Activate';
					}
				} )
				.catch( function () {
					// Silently fail — page reload will show the correct state.
				} )
				.finally( function () {
					self.disabled = false;
				} );
		} );
	} );

} )();
