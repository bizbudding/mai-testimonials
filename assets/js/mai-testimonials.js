( function() {

	var getTestimonials = function( event ) {
		event.preventDefault();

		var slider    = event.target.closest( '.mai-testimonials-slider' );
		var block     = slider.querySelector( '.mai-testimonials' );
		var blockArgs = JSON.parse( block.getAttribute( 'data-args' ) );
		// const queryArgs = block.getAttribute( 'data-query' );

		blockArgs.paged = event.target.getAttribute( 'data-paged' );

		console.log( blockArgs.paged );
		// console.log( event.target.getAttribute( 'data-paged' ) );
		// console.log( JSON.stringify( queryArgs ) );

		// const urlArgs   = {
		// 	action: 'mait_load_more_posts',
		// 	nonce: maiTestimonialsVars.nonce,
		// 	query_args: JSON.stringify( queryArgs ),
		// 	query_args: queryArgs,
		// 	posts_per_page: 8,
		// 	something: 'else',
		// };

		var data = {
			action: 'mait_load_more_posts',
			nonce: maiTestimonialsVars.nonce,
			// query_args: JSON.stringify( queryArgs ),
			block_args: JSON.stringify( blockArgs ),
			// query_args: queryArgs,
		};

		// var data = new FormData();
		// data.append( 'query_args', JSON.stringify( queryArgs ) );

		fetch( maiTestimonialsVars.ajaxurl, {
			method: "POST",
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
				// 'Content-Type': 'application/json',
				'Cache-Control': 'no-cache',
			},
			// headers: {
			// 	'Accept': 'application/json'
			// },
			// body: new URLSearchParams({
			// 	action: 'mait_load_more_posts',
			// 	nonce: maiTestimonialsVars.nonce,
			// 	query_args: queryArgs,
			// })
			// body: new URLSearchParams( urlArgs ),
			body: new URLSearchParams( data ),
			// body: JSON.stringify( queryArgs )
			// body: JSON.stringify( data )
			// body: data,
		})
		.then( (response) => response.json() )
		// .then( (response) => response.text() )
		.then( (data) => {
			console.log( data );

			if ( data.success ) {
				var existing = slider.querySelector( '.mai-testimonials[data-paged="' + data.data.paged + '"]' );

				if ( existing ) {
					existing.classList.remove( 'mai-testimonials-hidden' );
				} else {
					var div = document.createElement( 'div' );
					div.innerHTML = data.data.block.trim();
					slider.append( div.firstChild );
				}

				var sliders = slider.querySelectorAll( '.mai-testimonials:not([data-paged="' + data.data.paged + '"])' );

				sliders.forEach( function( toHide ) {
					toHide.classList.add( 'mai-testimonials-hidden' )
				});

				// slider.querySelector( '.mai-testimonials[data-paged="' + data.data.paged + '"]' ).classList.remove( 'mai-testimonials-hidden' );

				// Add event listener for new sliders.
				var sliderButtons = document.querySelectorAll( '.testimonials-pagination-button' );

				sliderButtons.forEach( function( sliderButton ) {
					sliderButton.addEventListener( 'click', getTestimonials, false );
				});
			}

			// Initialize the DOM parser
			// var parser = new DOMParser();

			// Parse the text
			// var doc = parser.parseFromString( data, 'text/html' );

			// You can now even select part of that html as you would in the regular DOM
			// Example:
			// var docArticle = doc.querySelector('article').innerHTML;

			// console.log( decodeURI( doc.body.innerHTML ) );
		})
		.catch( (error) => {
			console.log( 'Mai Testimonials block:' );
			// console.log( 'catch error' );
			console.error( error );
		});
	};

	var sliderButtons = document.querySelectorAll( '.testimonials-pagination-button' );

	sliderButtons.forEach( function( sliderButton ) {
		sliderButton.addEventListener( 'click', getTestimonials, false );
	});

} )();
