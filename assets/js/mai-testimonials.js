( function() {

	var getTestimonials = function( event ) {
		// event.target

		const block     = event.target.closest( '.mai-testimonials' );
		const blockArgs = block.getAttribute( 'data-args' );
		const queryArgs = block.getAttribute( 'data-query' );

		// console.log( queryArgs );
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
			block_args: blockArgs,
			query_args: queryArgs,
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
				var div = document.createElement( 'div' );
				div.innerHTML = data.data.trim();

				// Change this to div.childNodes to support multiple top-level nodes
				block.parentNode.append( div.firstChild );
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

	var sliderButtons = document.querySelectorAll( '.mai-testimonials-button' );

	sliderButtons.forEach( function( sliderButton ) {
		sliderButton.addEventListener( 'click', getTestimonials, false );
	});

} )();
