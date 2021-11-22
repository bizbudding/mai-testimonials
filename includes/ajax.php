<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

add_action( 'wp_ajax_mait_load_more_posts', 'mait_load_more_posts' );
add_action( 'wp_ajax_nopriv_mait_load_more_posts', 'mait_load_more_posts' );
function mait_load_more_posts() {
	$security = check_ajax_referer( 'mai_testimonials_slider', 'nonce' );

	if ( false === $security ) {
		return;
	}

	if ( ! isset( $_POST['block_args'] ) ) {
		return;
	}

	$testimonials = new Mai_Testimonials( $_POST['block_args'] );
	$block        = $testimonials->get();

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
	wp_send_json_success( $block );

	// Don't forget to stop execution afterward.
	wp_die();
}
