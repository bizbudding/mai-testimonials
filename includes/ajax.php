<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

add_action( 'wp_ajax_mait_load_more_posts', 'mait_load_more_posts' );
add_action( 'wp_ajax_nopriv_mait_load_more_posts', 'mait_load_more_posts' );
function mait_load_more_posts() {
	$security = check_ajax_referer( 'mai_testimonials_slider', 'nonce' );

	if ( false === $security ) {
		// return;
		// wp_die();
		return wp_send_json_error();
	}

	if ( ! isset( $_POST['block_args'] ) ) {
		// return;
		// wp_die();
		return wp_send_json_error();
	}

	$args         = wp_unslash( $_POST['block_args'] );
	$args         = json_decode( $args, true );
	$testimonials = new Mai_Testimonials( $args );
	$html         = $testimonials->get(); // Get first so prev/next properties are set.
	$data         = [
		'html'  => $html,
		'paged' => $args['paged'],
		'prev'  => $testimonials->prev,
		'next'  => $testimonials->next,
	];

	// ray( $_POST );
	// $query_args = json_decode( $_POST['query_args'] );

	// ray( $query_args );
	// ray( $_POST['posts_per_page'] );
	// $result = [
	// 	'success' => true,
	// 	'message' => 'Sweeeeet!',
	// ];

	// $result = '<div class="mai-testimonials">';
	// 	$result .= '<h2>This is working?!?!?</h2>';
	// $result .= '</div>';

	// Make your array as json
	wp_send_json_success( $data );

	// Don't forget to stop execution afterward.
	wp_die();
}
