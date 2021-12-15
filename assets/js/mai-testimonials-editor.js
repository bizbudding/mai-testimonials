( function( $ ) {

	if ( 'object' !== typeof acf ) {
		return;
	}

	var taxoKeys = [ 'mai_testimonials_terms' ];

	/**
	 * Uses current post types or taxonomy for use in other field queries.
	 *
	 * @since 0.1.0
	 *
	 * @return object
	 */
	acf.addFilter( 'select2_ajax_data', function( data, args, $input, field, instance ) {
		if ( field && taxoKeys.includes( data.field_key ) ) {

			var taxoField = acf.getFields(
				{
					key: 'mai_testimonials_taxonomy',
					sibling: field.$el,
				}
			);

			if ( taxoField ) {
				var first = taxoField.shift();
				var value = first ? first.val() : '';
				data.taxonomy = value;
			}
		}

		return data;
	} );

} )( jQuery );
