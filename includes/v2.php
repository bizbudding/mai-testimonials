<?php

class Mai_Testimonials_v2 {

	function __construct() {
		// add_filter( 'genesis_attr_entry', [ $this, 'entry_atts' ], 12, 3 );
		add_filter( 'mai_grid_post_types', [ $this, 'post_types' ], 12, 3 );
	}

	function post_types( $post_types ) {
		$post_types[] = 'testimonial';
		return array_unique( $post_types );
	}
}
