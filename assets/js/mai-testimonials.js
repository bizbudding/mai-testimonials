( function() {
	// var allSliders    = document.querySelectorAll( '.mait-slider' );
	var sliderButtons = document.querySelectorAll( '.mait-button' );
	// var hasFocus      = false;
	// var prevPage      = 1;
	// var nextPage      = 1;
	// var paged         = 1;
	// var slider        = false;
	// var slide         = false;
	// var current       = false;
	// var existing      = false;
	// var args          = false;

	/**
	 * Gets testimonials.
	 */
	 var getTestimonials = function( event ) {
		event.preventDefault();

		if ( event.target.getAttribute( 'data-disabled' ) ) {
			return;
		}

		slider = event.target.closest( '.mait-slider' );
		paged  = event.target.getAttribute( 'data-slide' );

		getSlide( slider, paged );
	};

	/**
	 * Changes slide with arrow keys.
	 * Only works if slider has focus.
	 *
	 * @since 2.3.0
	 *
	 * @return void
	 */
	 var handleArrowKeys = function( event ) {
		var slider = false;

		if ( document.activeElement ) {
			if ( document.activeElement.classList.contains( 'mait-slider' ) ) {
				slider = document.activeElement;
			} else {
				slider = document.activeElement.closest( '.mait-slider' );
			}
		}

		if ( ! slider ) {
			return;
		}

		var keys = [ 'ArrowLeft', 'ArrowRight' ];

		if ( keys.includes( event.key ) ) {
			switch (event.key) {
				case 'ArrowLeft':
					paged = slider.getAttribute( 'data-prev' );
				break;
				case 'ArrowRight':
					paged = slider.getAttribute( 'data-next' );
				break;
			}

			getSlide( slider, paged );
		}
	};

	/**
	 * Gets testimonials slide.
	 */
	var getSlide = function( slider, paged ) {
		current  = slider.querySelector( '.mait-testimonials[data-slide="' + slider.getAttribute( 'data-current' ) + '"]' );
		existing = slider.querySelector( '.mait-testimonials[data-slide="' + paged + '"]' );

		current.classList.add( 'mait-loading' );

		if ( existing ) {
			doNextSlide( slider, paged );

		} else {
			args       = JSON.parse( slider.getAttribute( 'data-args' ) );
			args.paged = paged;

			var data = {
				action: 'mait_ajax_get_testimonials',
				nonce: maiTestimonialsVars.nonce,
				block_args: JSON.stringify( args ),
			};

			/**
			 * Runs ajax.
			 */
			fetch( maiTestimonialsVars.ajaxurl, {
				method: "POST",
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
					'Cache-Control': 'no-cache',
				},
				body: new URLSearchParams( data ),
			})
			.then( (response) => response.json() )
			.then( (response) => {
				if ( response.success ) {
					// Build new slide.
					var div       = document.createElement( 'div' );
					div.innerHTML = response.data.html.trim();

					// Append to the HTML.
					var last = slider.querySelector( '.mait-testimonials:last-of-type' );
					last.after( div.firstChild );

					doNextSlide( slider, response.data.paged );
				}
			})
			.catch( (error) => {
				console.log( 'Mai Testimonials block:', error );
			});
		}
	};

	var doNextSlide = function( slider, paged ) {
		paged = parseInt( paged );

		// Hide others.
		slider.querySelectorAll( '.mait-testimonials:not([data-slide="' + paged + '"])' ).forEach( function( slide ) {
			hideSlide( slide );
		});

		// Show new slide.
		showSlide( slider.querySelector( '.mait-testimonials[data-slide="' + paged + '"]' ) );

		var max      = parseInt( slider.getAttribute( 'data-max' ) );
		var prevPage = paged - 1;
		var nextPage = paged + 1;

		if ( prevPage < 1 ) {
			prevPage = max;
		}

		if ( nextPage > max ) {
			nextPage = 1;
		}

		// Set slider attributes.
		slider.setAttribute( 'data-prev', prevPage );
		slider.setAttribute( 'data-next', nextPage );
		slider.setAttribute( 'data-slide', paged );

		// Enable currently disabled dot.
		var disabled = slider.querySelector( '.mait-dot[data-disabled="true"]' );

		if ( disabled ) {
			disabled.removeAttribute( 'data-disabled', 'false' );
			disabled.classList.remove( 'mait-current' );
		}

		// Disable current dot.
		var dot = slider.querySelector( '.mait-dot[data-slide="' + paged + '"]' );

		if ( dot ) {
			dot.setAttribute( 'data-disabled', 'true' );
			dot.classList.add( 'mait-current' );
		}

		// Set prev/next attributes.
		var prev = slider.querySelector( '.mait-previous' );
		var next = slider.querySelector( '.mait-next' );

		if ( prev ) {
			prev.setAttribute( 'data-slide', prevPage );
		}

		if ( next ) {
			next.setAttribute( 'data-slide', nextPage );
		}

		// Done loading.
		current.classList.remove( 'mait-loading' );
	};

	/**
	 * Hide slide.
	 *
	 * @since 2.3.0
	 *
	 * @return void
	 */
	var hideSlide = function( slide ) {
		slide.classList.add( 'mait-hidden' );
		slide.setAttribute( 'aria-hidden', 'true' );
	}

	/**
	 * Show slide.
	 *
	 * @since 2.3.0
	 *
	 * @return void
	 */
	var showSlide = function( slide ) {
		slide.classList.remove( 'mait-hidden' );
		slide.setAttribute( 'aria-hidden', 'false' );
	}

	/**
	 * Adds click event to change slide.
	 *
	 * @since 2.3.0
	 *
	 * @return void
	 */
	sliderButtons.forEach( function( sliderButton ) {
		sliderButton.addEventListener( 'click', getTestimonials, false );
	});

	/**
	 * Adds keydown event to change slide with arrow keys.
	 *
	 * @since 2.3.0
	 *
	 * @return void
	 */
	document.body.addEventListener( 'keydown', handleArrowKeys );

} )();
