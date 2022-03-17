<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Enqueues testimonials styles.
 *
 * @access private
 *
 * @since TBD
 *
 * @param bool $preview If admin editor preview or not.
 *
 * @return void
 */
function mai_enqueue_testimonials_styles( $preview = false ) {
	$suffix = mai_testimonials_get_suffix();

	// Block.
	wp_enqueue_style( 'mai-testimonials', MAI_TESTIMONIALS_PLUGIN_URL . "assets/css/mai-testimonials{$suffix}.css" );

	// Editor.
	if ( $preview ) {
		wp_enqueue_style( 'mai-testimonials-editor', MAI_TESTIMONIALS_PLUGIN_URL . "assets/css/mai-testimonials-editor{$suffix}.css" );
		wp_enqueue_script( 'mai-testimonials-editor', MAI_TESTIMONIALS_PLUGIN_URL . "assets/js/mai-testimonials-editor{$suffix}.js", [], MAI_TESTIMONIALS_VERSION, true );
	}
	// Front end.
	else {
		// Slider.
		wp_enqueue_script( 'mai-testimonials', MAI_TESTIMONIALS_PLUGIN_URL . "assets/js/mai-testimonials{$suffix}.js", [], MAI_TESTIMONIALS_VERSION, true );
		wp_localize_script( 'mai-testimonials', 'maiTestimonialsVars',
		[
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'mai_testimonials_slider' ),
			]
		);
	}
}

/**
 * Gets the script/style `.min` suffix for minified files.
 *
 * @access private
 *
 * @since TBD
 *
 * @return string
 */
function mai_testimonials_get_suffix() {
	static $suffix = null;

	if ( ! is_null( $suffix ) ) {
		return $suffix;
	}

	$debug  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
	$suffix = $debug ? '' : '.min';

	return $suffix;
}
