<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

add_action( 'wp_ajax_mait_ajax_get_testimonials', 'mait_ajax_get_testimonials' );
add_action( 'wp_ajax_nopriv_mait_ajax_get_testimonials', 'mait_ajax_get_testimonials' );
/**
 * Gets testimonials via ajax.
 *
 * @access private
 *
 * @since TBD
 *
 * @return void
 */
function mait_ajax_get_testimonials() {
	$security = check_ajax_referer( 'mai_testimonials_slider', 'nonce' );

	if ( false === $security ) {
		wp_send_json_error();
		wp_die();
	}

	if ( ! isset( $_POST['block_args'] ) ) {
		wp_send_json_error();
		wp_die();
	}

	$args         = wp_unslash( $_POST['block_args'] );
	$args         = json_decode( $args, true );
	$testimonials = new Mai_Testimonials( $args );
	$data         = [
		'html'  => $testimonials->get(),
		'paged' => $args['paged'],
	];

	// Make your array as json.
	wp_send_json_success( $data );

	// Die.
	wp_die();
}
