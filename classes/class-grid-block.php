<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

class Mai_Testimonials_Grid_Block {

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
		add_filter( 'mai_grid_post_types',                [ $this, 'post_types' ] );
		add_filter( 'mai_link_entry',                     [ $this, 'disable_entry_link' ], 10, 3 );
		add_filter( 'mai_entry_content',                  [ $this, 'entry_content' ], 10, 3 );
		add_filter( 'genesis_markup_entry_close',         [ $this, 'entry_schema' ], 10, 2 );
		add_filter( 'genesis_markup_entry-title_content', [ $this, 'do_author_info' ], 10, 2 );
		add_filter( 'genesis_attr_entry',                 [ $this, 'remove_schema' ], 12, 3 );
		add_filter( 'genesis_attr_entry-image',           [ $this, 'remove_schema' ], 12, 3 );
		add_filter( 'genesis_attr_entry-title',           [ $this, 'remove_schema' ], 12, 3 );
		add_filter( 'genesis_attr_entry-content',         [ $this, 'remove_schema' ], 12, 3 );
	}

	/**
	 * Adds testimonial to the available grid post types.
	 *
	 * @since 2.0.0
	 *
	 * @param array $post_types The post types.
	 *
	 * @return array
	 */
	function post_types( $post_types ) {
		$post_types[] = 'testimonial';

		return array_unique( $post_types );
	}

	/**
	 * Disables entry link if post type is testimonial.
	 *
	 * @since TBD
	 *
	 * @param bool            $link  If linking the entry.
	 * @param array           $args  The grid block args.
	 * @param WP_Post|WP_Term $entry The entry.
	 *
	 * @return bool
	 */
	function disable_entry_link( $link, $args, $entry ) {
		if ( 'WP_Post' !== get_class( $entry ) ) {
			return $link;
		}

		if ( 'testimonial' !== $entry->post_type ) {
			return $link;
		}

		return false;
	}

	/**
	 * Show full block content on testimonials.
	 *
	 * @since 2.1.0
	 *
	 * @param bool            $link  If linking the entry.
	 * @param array           $args  The grid block args.
	 * @param WP_Post|WP_Term $entry The entry.
	 *
	 * @return bool
	 */
	function entry_content( $entry_content, $args, $entry ) {
		if ( 'WP_Post' !== get_class( $entry ) ) {
			return $entry_content;
		}

		if ( 'testimonial' !== $entry->post_type ) {
			return $entry_content;
		}

		return function_exists( 'mai_get_processed_content' ) ? mai_get_processed_content( $entry_content ) : $entry_content;
	}

	/**
	 * Adds entry schema via JSON in the entry.
	 *
	 * @since 2.0.0
	 *
	 * @param string $close The closing markup.
	 * @param array  $args  The entry args.
	 *
	 * @return string
	 */
	function entry_schema( $close, $args ) {
		if ( ! $this->is_testimonial( $args ) ) {
			return $close;
		}

		if ( ! $close ) {
			return $close;
		}

		$post   = $args['params']['entry'];
		$schema = [
			'@context'     => 'https://schema.org/',
			'@type'        => 'Review',
			'itemReviewed' => [
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
			],
			'author'       => [
				'@type' => 'Person',
				'name'  => get_the_title( $post ),
			],
			'reviewBody'   => get_the_content( $post ),
		];

		$schema = apply_filters( 'mai_testimonials_schema', $schema, $post );
		$schema = $schema ? sprintf( '<script type="application/ld+json">%s</script>', json_encode( $schema ) ) : '';

		return $schema . $close;
	}

	/**
	 * Renders the author info in the title.
	 *
	 * @since 2.0.0
	 *
	 * @param string $content The content.
	 * @param array  $args    The entry args.
	 *
	 * @return string
	 */
	function do_author_info( $content, $args ) {
		if ( ! $this->is_testimonial( $args ) ) {
			return $content;
		}

		$post    = $args['params']['entry'];
		$post_id = $post->ID;

		// Byline.
		$byline = get_post_meta( $post_id, 'byline', true );
		if ( $byline ) {
			$content .= sprintf( '<span class="entry-byline">%s</span>', sanitize_text_field( $byline ) );
		}

		// Website URL.
		$url = get_post_meta( $post_id, 'url', true );
		if ( $url ) {
			$url      = esc_url( $url );
			$content .= sprintf( '<span class="entry-website"><a class="entry-website-link" href="%s" target="_blank" rel="noopener" itemprop="url">%s</a></span>', $url, $url );
		}

		return $content;
	}

	/**
	 * Converts itemprop and itemptype to review schema.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $attributes The entry attributes.
	 * @param string $context    The entry context.
	 * @param array  $args       The entry args.
	 *
	 * @return array
	 */
	function remove_schema( $attributes, $context, $args ) {
		if ( ! $this->is_testimonial( $args ) ) {
			return $attributes;
		}
		$attributes['itemprop']  = false;
		$attributes['itemtype']  = false;
		$attributes['itemscope'] = false;
		return $attributes;
	}

	/**
	 * Checks whether the entry is a grid block testimonial entry.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args The entry args.
	 *
	 * @return array
	 */
	function is_testimonial( $args ) {
		if ( ! $args ) {
			return;
		}
		if ( ! isset( $args['params']['args']['context'] ) || 'block' !== $args['params']['args']['context'] ) {
			return false;
		}
		if ( ! ( isset( $args['params']['entry'] ) && is_object( $args['params']['entry'] ) && 'WP_Post' === get_class( $args['params']['entry'] ) ) ) {
			return false;
		}

		return 'testimonial' === $args['params']['entry']->post_type;
	}
}
