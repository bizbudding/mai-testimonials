( function() {
	var allSliders    = document.querySelectorAll( '.mait-slider' );
	var sliderButtons = document.querySelectorAll( '.mait-button' );
	var hasFocus      = false;
	var prevPage      = 1;
	var nextPage      = 1;
	var paged         = 1;

	/**
	 * Gets testimonials.
	 */
	var getTestimonials = function( event ) {
		event.preventDefault();

		if ( event.target.getAttribute( 'data-disabled' ) ) {
			return;
		}

		var slider   = event.target.closest( '.mait-slider' );
		var args     = JSON.parse( slider.getAttribute( 'data-args' ) );
		prevPage     = slider.getAttribute( 'data-prev' );
		nextPage     = slider.getAttribute( 'data-next' );
		paged        = event.target.getAttribute( 'data-paged' );
		args.paged   = paged;

		getSlide( slider, args );
	};

	/**
	 * Fetches testimonials via ajax.
	 */
	var getSlide = function( slider, args ) {
		var data  = {
			action: 'mait_ajax_get_testimonials',
			nonce: maiTestimonialsVars.nonce,
			block_args: JSON.stringify( args ),
		};

		var current = slider.querySelector( '.mait-testimonials:not(.mait-hidden)' );

		current.classList.add( 'mait-loading' );

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
		.then( (data) => {
			if ( data.success ) {
				prevPage = data.data.prev;
				nextPage = data.data.next;
				paged    = data.data.paged;

				var slide    = false;
				var existing = slider.querySelector( '.mait-testimonials[data-paged="' + paged + '"]' );

				if ( existing ) {
					// Toggle visiblity classes.
					slide = existing;
					slide.classList.remove( 'mait-hidden' );
					slide.setAttribute( 'aria-hidden', 'false' );
				} else {
					// Build new slide.
					var div       = document.createElement( 'div' );
					div.innerHTML = data.data.html.trim();
					slide         = div.firstChild;
					slide.setAttribute( 'aria-hidden', 'false' );

					// Append to the HTML.
					var last = slider.querySelector( '.mait-testimonials:last-of-type' );
					last.after( slide );
				}

				// Add hidden class.
				slider.querySelectorAll( '.mait-testimonials:not([data-paged="' + paged + '"])' ).forEach( function( toHide ) {
					toHide.classList.add( 'mait-hidden' );
					toHide.setAttribute( 'aria-hidden', 'true' );
				});

				console.log( slide.offsetHeight );

				// Set slider attributes.
				slider.style.setProperty( '--testimonials-slider-min-height', slide.offsetHeight + 'px' );
				slider.setAttribute( 'data-prev', prevPage );
				slider.setAttribute( 'data-next', nextPage );
				slider.setAttribute( 'data-paged', paged );

				// Enable currently disabled dot.
				var disabled = slider.querySelector( '.mait-dot[data-disabled="true"]' );

				if ( disabled ) {
					disabled.removeAttribute( 'data-disabled' );
					disabled.classList.remove( 'mait-current' );
				}

				// Disable current dot.
				var dot = slider.querySelector( '.mait-dot[data-paged="' + paged + '"]' );

				if ( dot ) {
					dot.setAttribute( 'data-disabled', true );
					dot.classList.add( 'mait-current' );
				}

				// Set prev/next attributes.
				var prev = slider.querySelector( '.mait-previous' );
				var next = slider.querySelector( '.mait-next' );

				if ( prev ) {
					prev.setAttribute( 'data-paged', prevPage );
				}

				if ( next ) {
					next.setAttribute( 'data-paged', nextPage );
				}

				// Done loading.
				setTimeout( function() {
					current.classList.remove( 'mait-loading' );
				}, 500 )
			}
		})
		.catch( (error) => {
			console.log( 'Mai Testimonials block:', error );
		});
	};

	/**
	 * Changes slide with arrow keys.
	 * Only works if slider has focus.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	 var handleArrowKeys = function( event ) {
		var focusedSlider = false;

		if ( document.activeElement ) {
			if ( document.activeElement.classList.contains( 'mait-slider' ) ) {
				focusedSlider = document.activeElement;
			} else {
				focusedSlider = document.activeElement.closest( '.mait-slider' );
			}
		}

		if ( ! focusedSlider ) {
			return;
		}

		var args = JSON.parse( focusedSlider.getAttribute( 'data-args' ) );
		var keys = [ 'ArrowLeft', 'ArrowRight' ];

		if ( keys.includes( event.key ) ) {

			switch (event.key) {
				case 'ArrowLeft':
					paged = prevPage;
				break;
				case 'ArrowRight':
					paged = nextPage;
				break;
			}

			args.paged = paged;

			getSlide( focusedSlider, args );
		}
	};

	/**
	 * Adds click event to change slide.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	sliderButtons.forEach( function( sliderButton ) {
		sliderButton.addEventListener( 'click', getTestimonials, false );
	});

	/**
	 * Adds keydown event to change slide with arrow keys.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	document.body.addEventListener( 'keydown', handleArrowKeys );

} )();
