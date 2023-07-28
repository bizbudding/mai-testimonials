<?php

add_action( 'wp_footer', 'mai_render_testimonials_schema' );
/**
 * Renders schema from testimonial data.
 *
 * @since TBD
 *
 * @return void
 */
function mai_render_testimonials_schema() {
	$schemas = mai_testimonials_get_schemas();

	if ( ! $schemas ) {
		return;
	}

	printf( '<script type="application/ld+json">%s</script>', wp_json_encode( $schemas ) );
}