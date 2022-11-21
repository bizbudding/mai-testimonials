( function() {
	let sliders       = document.querySelectorAll( '.mait-slider' );
	let sliderButtons = document.querySelectorAll( '.mait-button' );

	/**
	 * Gets testimonials.
	 */
	 var getTestimonials = function( event ) {
		event.preventDefault();

		var el = event.target.classList.contains( 'mait-button' ) ? event.target : event.target.closest( '.mait-button' );

		if ( el.dataset.disabled ) {
			return;
		}

		slider = el.closest( '.mait-slider' );
		paged  = el.dataset.slide;

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
					paged = slider.dataset.prev;
				break;
				case 'ArrowRight':
					paged = slider.dataset.next;
				break;
			}

			getSlide( slider, paged );
		}
	};

	/**
	 * Gets testimonials slide.
	 */
	var getSlide = function( slider, paged ) {
		current  = slider.querySelector( '.mait-testimonials[data-slide="' + slider.dataset.current + '"]' );
		existing = slider.querySelector( '.mait-testimonials[data-slide="' + paged + '"]' );

		current.innerHTML += '<span class="mai-testimonials-loading__overlay"><svg class="mai-testimonials-loading__ring" viewBox="25 25 50 50" stroke-width="5"><circle cx="50" cy="50" r="20" /></svg>';
		current.classList.add( 'mait-loading' );

		if ( existing ) {
			doNextSlide( slider, paged );

		} else {
			args       = JSON.parse( slider.dataset.args );
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

		var max      = parseInt( slider.dataset.max );
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
		slider.setAttribute( 'data-current', paged );

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
		slider.querySelector( '.mai-testimonials-loading__overlay' ).remove();
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

	/**
	 * Handles side swipe for sliders.
	 *
	 * @since TBD
	 * @link  https://gist.github.com/SleepWalker/da5636b1abcbaff48c4d#gistcomment-3753498
	 */
	sliders.forEach( function( slider ) {
		let touchstartX = 0;
		let touchstartY = 0;
		let touchendX   = 0;
		let touchendY   = 0;

		// Set start values.
		slider.addEventListener( 'touchstart', function( event ) {
			touchstartX = event.changedTouches[0].screenX;
			touchstartY = event.changedTouches[0].screenY;
		}, false );

		// Set end values and compare.
		slider.addEventListener( 'touchend', function( event ) {
			touchendX = event.changedTouches[0].screenX;
			touchendY = event.changedTouches[0].screenY;

			const delx = touchendX - touchstartX;
			const dely = touchendY - touchstartY;

			if ( Math.abs( delx ) > Math.abs( dely ) ) {
				// Right.
				if ( delx > 0 ) {
					getSlide( slider, slider.dataset.next );
				}
				// Left.
				else {
					getSlide( slider, slider.dataset.prev );
				}
			}
		}, false );
	});
} )();
