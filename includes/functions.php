<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Enqueues testimonials styles.
 *
 * @access private
 *
 * @since 2.4.0
 *
 * @param bool $preview If admin editor preview or not.
 *
 * @return void
 */
function mai_enqueue_testimonials_styles( $preview = false ) {
	$suffix = mai_testimonials_get_suffix();

	// Block.
	wp_enqueue_style( 'mai-testimonials', MAI_TESTIMONIALS_PLUGIN_URL . "assets/css/mai-testimonials{$suffix}.css", [], MAI_TESTIMONIALS_VERSION . '.' . date( 'njYHi', filemtime( MAI_TESTIMONIALS_PLUGIN_DIR . "assets/css/mai-testimonials{$suffix}.css" ) ) );

	// Editor.
	if ( $preview ) {
		wp_enqueue_style( 'mai-testimonials-editor', MAI_TESTIMONIALS_PLUGIN_URL . "assets/css/mai-testimonials-editor{$suffix}.css", [], MAI_TESTIMONIALS_VERSION . '.' . date( 'njYHi', filemtime( MAI_TESTIMONIALS_PLUGIN_DIR . "assets/css/mai-testimonials-editor{$suffix}.css" ) ) );
		wp_enqueue_script( 'mai-testimonials-editor', MAI_TESTIMONIALS_PLUGIN_URL . "assets/js/mai-testimonials-editor{$suffix}.js", [], MAI_TESTIMONIALS_VERSION . '.' . date( 'njYHi', filemtime( MAI_TESTIMONIALS_PLUGIN_DIR . "assets/js/mai-testimonials-editor{$suffix}.js" ) ), [ 'strategy' => 'defer' ] );
	}
	// Front end.
	else {
		// Slider.
		wp_enqueue_script( 'mai-testimonials', MAI_TESTIMONIALS_PLUGIN_URL . "assets/js/mai-testimonials{$suffix}.js", [], MAI_TESTIMONIALS_VERSION . '.' . date( 'njYHi', filemtime( MAI_TESTIMONIALS_PLUGIN_DIR . "assets/js/mai-testimonials{$suffix}.js" ) ), [ 'strategy' => 'defer' ] );
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
 * @since 2.4.0
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

/**
 * Gets all schemas.
 *
 * @access private
 *
 * @since TBD
 *
 * @param  array $schemas
 *
 * @return array
 */
function mai_testimonials_get_schemas( $schemas = [] ) {
	static $cache = [];

	if ( $schemas ) {
		$cache[] = $schemas;
	}

	return $cache;
}

/**
 * Gets Review schema.
 * Optionally add new schema to the static variable.
 *
 * @access private
 *
 * @since TBD
 *
 * @param array $review Array of schema data.
 * @param bool  $clear  If we should clear cache after storing values.
 *
 * @return array
 */
function mai_testimonials_get_schema( $review = [], $clear = false ) {
	static $cache = [];

	if ( $review ) {
		$cache[] = $review;
	}

	$return = $cache;

	if ( $clear ) {
		$cache = [];
	}

	return $return;
}

/**
 * Gets sanitized schema content from post.
 *
 * @access private
 *
 * @since TBD
 *
 * @param WP_post $post The post object.
 *
 * @return string
 */
function mai_testimonials_get_schema_content( $post ) {
	$content = get_the_content( $post );
	$content = do_blocks( $content );
	$content = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $content ); // Strip script and style tags.
	$content = strip_tags( $content, [ 'a' ] ); // Strip tags, leave links.
	$content = trim( $content );

	return wpautop( $content );
}
