( function() {

	var allSliders    = document.querySelectorAll( '.mait-slider' );
	var sliderButtons = document.querySelectorAll( '.mait-button' );
	var hasFocus      = false;
	var prevPage      = 1;
	var nextPage      = 1;
	var paged         = 1;

	var getTestimonials = function( event ) {
		event.preventDefault();

		var slider   = event.target.closest( '.mait-slider' );
		var args     = JSON.parse( slider.getAttribute( 'data-args' ) );
		prevPage     = slider.getAttribute( 'data-prev' );
		nextPage     = slider.getAttribute( 'data-next' );
		paged        = event.target.getAttribute( 'data-paged' );
		args.paged   = paged;

		getSlide( slider, args );
	};

	var getSlide = function( slider, args ) {
		var data  = {
			action: 'mait_load_more_posts',
			nonce: maiTestimonialsVars.nonce,
			block_args: JSON.stringify( args ),
		};

		var current = slider.querySelector( '.mait-visible' );

		current.classList.add( 'mait-loading' );

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

				var existing = slider.querySelector( '.mait-testimonials[data-paged="' + paged + '"]' );

				if ( existing ) {
					// Toggle visiblity classes.
					existing.classList.add( 'mait-visible' );
					existing.classList.remove( 'mait-hidden' );
				} else {
					// Build new slider.
					var div = document.createElement( 'div' );
					div.innerHTML = data.data.html.trim();
					var newSlider = div.firstChild;

					// Add visible class.
					newSlider.classList.add( 'mait-visible' );

					// Append to the HTML.
					slider.append( newSlider );

					// Add arrow keys listener for the new slider.
					newSlider.addEventListener( 'focusin', handleArrowKeys );
				}

				slider.querySelectorAll( '.mait-testimonials:not([data-paged="' + paged + '"])' ).forEach( function( toHide ) {
					toHide.classList.add( 'mait-hidden' );
					toHide.classList.remove( 'mait-visible' );
				});

				slider.setAttribute( 'data-prev', prevPage );
				slider.setAttribute( 'data-next', nextPage );
				slider.setAttribute( 'data-paged', paged );
				slider.querySelector( '.mait-previous' ).setAttribute( 'data-paged', prevPage );
				slider.querySelector( '.mait-next' ).setAttribute( 'data-paged', nextPage );

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
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	var handleArrowKeys = function( event ) {
		document.onkeydown = function(e) {
			e = e || window.event;

			var slider = event.target.closest( '.mait-slider' );

			// console.log( slider === document.activeElement );

			// if ( slider === document.activeElement ) {
				var args   = JSON.parse( slider.getAttribute( 'data-args' ) );
				var keys   = [ 'ArrowLeft', 'ArrowRight' ];

				if ( keys.includes( e.key ) ) {

					switch (e.key) {
						case 'ArrowLeft':
							paged = prevPage;
						break;
						case 'ArrowRight':
							paged = nextPage;
						break;
					}

					args.paged = paged;

					getSlide( slider, args );
				}
			// }
		};
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
	 * Adds focusin event to change slide with arrow keys.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	allSliders.forEach( function( slider ) {
		// console.log( slider === document.activeElement );
		// if ( slider === document.activeElement ) {

		slider.addEventListener( 'focusin', handleArrowKeys );

		// TODO: When focus out of slider we need to remove focusin listener somehow.

		// slider.addEventListener( 'focusout', function( event ) {
		// 	hasFocus = false;
		// 	alert( hasFocus );
		// 	// slider.removeEventListener(	'focusin', handleArrowKeys );
		// });
	});

} )();
