<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

class Mai_Testimonials_Grid_Shortcode {

	function __construct() {
		$this->hooks();
	}

	/**
	 * Runs hooks.
	 *
	 * @since 2.3.0
	 *
	 * @return void
	 */
	function hooks() {
		add_action( 'wp_enqueue_scripts',         [ $this, 'css' ], 1000 ); // Way late cause Engine changes stylesheet to 999.
		add_filter( 'shortcode_atts_grid',        [ $this, 'grid_atts' ], 8, 3 );
		add_filter( 'genesis_attr_flex-entry',    [ $this, 'flex_entry_atts' ], 12, 3 );
		add_filter( 'genesis_attr_entry-content', [ $this, 'entry_content_atts' ], 12, 3 );
		add_filter( 'genesis_attr_entry-header',  [ $this, 'entry_header_atts' ], 12, 3 );
		add_filter( 'genesis_attr_entry-title',   [ $this, 'entry_title_atts' ], 12, 3 );
		add_filter( 'mai_flex_entry_header',      [ $this, 'add_author_details' ], 10, 2 );
	}

	/**
	 * Add inline CSS.
	 *
	 * @since 0.5.0
	 *
	 * @link  http://www.billerickson.net/code/enqueue-inline-styles/
	 * @link  https://sridharkatakam.com/chevron-shaped-featured-parallax-section-in-genesis-using-clip-path/
	 */
	function css() {
		$css = '
			.flex-entry.testimonial {
				background-color: transparent;
				border-radius: 5px;
			}
			.mai-slider[data-slidestoshow="1"] .flex-entry.testimonial.slick-slide {
				border: none;
				-webkit-box-shadow: none;
				box-shadow: none;
			}
			.flex-entry.testimonial .entry-header {
				-webkit-box-ordinal-group: 3;-ms-flex-order: 2;order: 2;
				padding-top: 12px;
			}
			.flex-entry.testimonial .entry-header span {
				display: inline-block;
			}
			.flex-entry.testimonial .entry-header .entry-title,
			.flex-entry.testimonial .entry-header .title {
				font-size: 1.2rem;
			}
			.flex-entry.testimonial .entry-header .entry-title {
				font-weight: bold;
			}
			.flex-entry.testimonial .entry-header .title {
				font-size: 1rem;
			}
			.flex-entry.testimonial .entry-header .title::before {
				display: inline-block;
				content: "-";
				margin: 0 6px;
			}
			.flex-entry.testimonial .entry-header .url {
				display: block;
				font-size: 1rem;
			}
			.flex-entry.testimonial .entry-content {
				font-style: italic;
				letter-spacing: 1px;
			}
			.flex-entry.testimonial .entry-image-link {
				max-width: 120px;
				border-radius: 50%;
				overflow: hidden;
			}
			/* offset negative margin */
			.flex-entry.testimonial .entry-image-link.entry-image-before-entry.alignnone {
				width: auto;
				margin-left: auto;
				margin-right: auto;
			}
		';
		$handle = ( defined( 'CHILD_THEME_NAME' ) && CHILD_THEME_NAME ) ? sanitize_title_with_dashes( CHILD_THEME_NAME ) : 'child-theme';
		wp_add_inline_style( $handle, $css );
	}


	/**
	 * Filter the default args for [grid] shortcode when displaying testimonials.
	 *
	 * @param   array  $out    The modified attributes.
	 * @param   array  $pairs  Entire list of supported attributes and their defaults.
	 * @param   array  $atts   User defined attributes in shortcode tag.
	 *
	 * @return  array  The modified attributes.
	 */
	function grid_atts( $out, $pairs, $atts ) {

		// Bail if not a testimonial.
		if ( ! isset( $atts['content'] ) || 'testimonial' !== $atts['content'] ) {
			return $out;
		}

		if ( ! isset( $atts['align'] ) ) {
			$out['align'] = 'center, middle';
		}

		if ( ! isset( $atts['boxed'] ) ) {
			$out['boxed'] = false;
		}

		if ( ! isset( $atts['columns'] ) ) {
			$out['columns'] = 2;
		}

		if ( ! isset( $atts['image_size'] ) ) {
			$out['image_size'] = 'thumbnail';
		}

		if ( ! isset( $atts['link'] ) ) {
			$out['link'] = false;
		}

		if ( ! isset( $atts['show'] ) ) {
			$out['show'] = 'image, title, content';
		}

		if ( ! isset( $atts['title_wrap'] ) ) {
			$out['title_wrap'] = 'span';
		}

		return $out;
	}

	function flex_entry_atts( $attributes, $context, $atts ) {
		// Bail if not a testimonial.
		if ( ! $this->is_testimonial( $atts ) ) {
			return $attributes;
		}
		$attributes['itemprop'] = 'review';
		$attributes['itemtype'] = 'http://schema.org/Review';
		return $attributes;
	}

	function entry_content_atts( $attributes, $context, $atts ) {
		// Bail if not a testimonial.
		if ( ! $this->is_testimonial( $atts ) ) {
			return $attributes;
		}
		$attributes['class']   .= ' text-lg';
		$attributes['itemprop'] = 'reviewBody';
		return $attributes;
	}

	function entry_header_atts( $attributes, $context, $atts ) {
		// Bail if not a testimonial.
		if ( ! $this->is_testimonial( $atts ) ) {
			return $attributes;
		}
		$attributes['itemprop'] = 'author';
		$attributes['itemtype'] = 'http://schema.org/Person';
		return $attributes;
	}

	function entry_title_atts( $attributes, $context, $atts ) {
		// Bail if not a testimonial.
		if ( ! $this->is_testimonial( $atts ) ) {
			return $attributes;
		}
		$attributes['itemprop'] = 'name';
		return $attributes;
	}

	function add_author_details( $entry_header, $atts ) {
		// Bail if not a testimonial.
		if ( ! $this->is_testimonial( $atts ) ) {
			return $entry_header;
		}
		// Byline
		$byline = get_post_meta( get_the_ID(), 'byline', true );
		if ( $byline ) {
			$entry_header .= sprintf( '<span class="title" itemprop="jobTitle">%s</span>', sanitize_text_field( $byline ) );
		}
		// URL
		$url = get_post_meta( get_the_ID(), 'url', true );
		if ( $url ) {
			$url = esc_url( $url );
			$entry_header .= sprintf( '<span class="url"><a href="%s" target="_blank" rel="noopener" itemprop="url">%s</a></span>', $url, $url );
		}
		return $entry_header;
	}

	function is_testimonial( $atts ) {
		// Bail if we have no atts.
		if ( ! isset( $atts ) || ! is_array( $atts ) ) {
			return false;
		}
		// Bail if not a testimonial.
		if ( ! isset( $atts['content'] ) || ! in_array( 'testimonial', (array) $atts['content'] ) ) {
			return false;
		}
		// Yay, a testimonial.
		return true;
	}
}
