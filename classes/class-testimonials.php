<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

class Mai_Testimonials {
	public $args;
	public $query_args;
	public $prev;
	public $next;
	public $has_image;
	public $has_name;
	public $has_byline;
	public $has_slider;
	public $slider_max;

	function __construct( $args ) {
		$args = wp_parse_args( $args,
			[
				'paged'                  => 1,
				'font_size'              => '',
				'text_align'             => '',
				'image_location'         => '',
				'author_location'        => '',
				'show'                   => [ 'name', 'image', 'byline' ],
				'query_by'               => '',
				'number'                 => 3,
				'include'                => [],
				'taxonomies'             => [],
				'taxonomies_relation'    => 'AND',
				'orderby'                => '',
				'order'                  => 'DESC',
				'exclude'                => [],
				'columns'                => 3,
				'columns_responsive'     => '',
				'columns_md'             => '',
				'columns_sm'             => '',
				'columns_xs'             => '',
				'align_columns'          => '',
				'align_columns_vertical' => '',
				'column_gap'             => 'md',
				'row_gap'                => 'md',
				'boxed'                  => '',
				'slider'                 => false,
				'slider_show'            => [ 'arrows', 'dots' ],
				'slider_max'             => 0,
				'class'                  => '',
			]
		);

		// Sanitize.
		$args = [
			'paged'                  => absint( $args['paged'] ),
			'font_size'              => esc_html( $args['font_size'] ),
			'text_align'             => esc_html( $args['text_align'] ),
			'image_location'         => esc_html( $args['image_location'] ),
			'author_location'        => esc_html( $args['author_location'] ),
			'show'                   => array_map( 'esc_html', (array) $args['show'] ),
			'query_by'               => esc_html( $args['query_by'] ),
			'number'                 => absint( $args['number'] ),
			'include'                => $args['include'] ? array_map( 'absint', (array) $args['include'] ) : [-1], // Empty array returns all posts, [-1] prevents this.
			'taxonomies'             => $this->sanitize_taxonomies( $args['taxonomies'] ),
			'taxonomies_relation'    => esc_html( $args['taxonomies_relation'] ),
			'orderby'                => esc_html( $args['orderby'] ),
			'order'                  => esc_html( $args['order'] ),
			'exclude'                => $args['exclude'] ? array_map( 'absint', (array) $args['exclude'] ) : [],
			'columns'                => absint( $args['columns'] ),
			'columns_responsive'     => mai_sanitize_bool( $args['columns_responsive'] ),
			'columns_md'             => absint( $args['columns_md'] ),
			'columns_sm'             => absint( $args['columns_sm'] ),
			'columns_xs'             => absint( $args['columns_xs'] ),
			'align_columns'          => esc_html( $args['align_columns'] ),
			'align_columns_vertical' => esc_html( $args['align_columns_vertical'] ),
			'column_gap'             => esc_html( $args['column_gap'] ),
			'row_gap'                => esc_html( $args['row_gap'] ),
			'boxed'                  => mai_sanitize_bool( $args['boxed'] ),
			'slider'                 => mai_sanitize_bool( $args['slider'] ),
			'slider_show'            => array_map( 'esc_html', (array) $args['slider_show'] ),
			'slider_max'             => absint( $args['slider_max'] ),
			'class'                  => esc_html( $args['class'] ),
		];

		$this->args       = $args;
		$this->query_args = $this->get_query_args();
		$this->prev       = 1;
		$this->next       = 1;
		$show_keys        = array_flip( $this->args['show'] );
		$this->has_image  = isset( $show_keys['image'] );
		$this->has_name   = isset( $show_keys['name'] );
		$this->has_byline = isset( $show_keys['byline'] );
		$this->has_slider = $this->args['slider'] && count( $this->args['slider_show'] ) >= 1;
	}

	/**
	 * Displays testimonials.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	function render() {
		echo $this->get();
	}

	/**
	 * Gets testimonials.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	function get() {
		if ( 'id' !== $this->args['query_by'] && ! $this->args['number'] ) {
			return;
		}

		if ( 'id' === $this->args['query_by'] && ! $this->args['include'] ) {
			return;
		}

		$html  = '';
		$query = new WP_Query( $this->query_args );

		if ( $query->have_posts() ) {
			$this->prev = $this->get_prev_page( $query );
			$this->next = $this->get_next_page( $query );

			// If slider.
			if ( $this->has_slider && 1 === $this->args['paged'] ) {
				$attributes = [
					'class'      => 'mait-slider',
					'data-args'  => esc_html( json_encode( $this->args ), ENT_QUOTES, 'UTF-8' ),
					'data-paged' => $this->args['paged'],
					'data-prev'  => $this->prev,
					'data-next'  => $this->next,
				];

				$html .= genesis_markup(
					[
						'open'    => '<div %s>',
						'context' => 'testimonials-slider',
						'echo'    => false,
						'atts'    => $attributes,
						'params'  => [
							'args' => $this->args,
						],
					]
				);
			}

			$html .= $this->get_open();
				$html .= $this->get_inner();

					while ( $query->have_posts() ) : $query->the_post();
						$content  = get_the_content();
						$image_id = $this->has_image ? get_post_thumbnail_id() : '';
						$image    = $this->has_image && $image_id ? wp_get_attachment_image( $image_id, 'tiny' ) : '';
						$image    = $image ? sprintf( '<div class="mait-image">%s</div>', $image ) : '';
						$name     = $this->has_name ? get_the_title() : '';
						$name     = $name ? sprintf( '<span class="mait-name">%s</span>', $name ) : '';
						$byline   = $this->has_byline ? get_post_meta( get_the_ID(), 'byline', true ) : '';
						$byline   = $byline ? sprintf( '<span class="mait-byline">%s</span>', $byline ) : '';
						$details  = $this->get_details( $image, $name, $byline );

						$html .= '<div class="mait-testimonial">';

							if ( 'before' === $this->args['image_location'] ) {
								$html .= $image ?: '';
							}

							if ( 'before' === $this->args['author_location'] ) {
								$html .= $details ?: '';
							}

							$html .= sprintf( '<div class="mait-content">%s</div>', mai_get_processed_content( $content ) );

							if ( 'after' === $this->args['image_location'] ) {
								$html .= $image ?: '';
							}

							if ( 'after' === $this->args['author_location'] ) {
								$html .= $details ?: '';
							}

						$html .= '</div>';

					endwhile;

				$html .= '</div>';

			$html .= '</div>';

			if ( $this->has_slider && 1 === $this->args['paged'] ) {
				// Slider max.
				if ( 'id' === $this->args['query_by'] ) {
					// If by choice, the max slides is the number chosen divided by the number displayed, rounded up.
					$this->slider_max = absint( ceil( count( $this->args['include'] ) / $this->args['number'] ) );
				} else {
					// If slider_max has a value, use the lesser of that or max from the query. Otherwise use max from query.
					$this->slider_max = $this->args['slider_max'] ? min( $this->args['slider_max'], (int) $query->max_num_pages ) : (int) $query->max_num_pages;
				}

				// If more than one page.
				if ( $this->slider_max > 1 ) {
					if ( in_array( 'dots', $this->args['slider_show'] ) ) {
						$html .= $this->get_dots( $query );
					}

					if ( in_array( 'arrows', $this->args['slider_show'] ) ) {
						$html .= $this->get_arrows( $query );
					}
				}

				$html .= genesis_markup(
					[
						'close'   => '</div>',
						'context' => 'testimonials-slider',
						'echo'    => false,
						'params'  => [
							'args' => $this->args,
						],
					]
				);
			}
		}

		wp_reset_postdata();

		return $html;
	}

	/**
	 * Gets query args for WP_Query.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	function get_query_args() {
		$per_page = ( 0 === $this->args['number'] ) ? -1 : $this->args['number'];
		$per_page = ( 'id' === $this->args['query_by'] ) ? count( (array) $this->args['include'] ) : $per_page;

		$query_args = [
			'post_type'              => 'testimonial',
			'posts_per_page'         => $per_page,
			'no_found_rows'          => ! $this->args['paged'],
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'suppress_filters'       => false, // https://github.com/10up/Engineering-Best-Practices/issues/116
		];

		if ( $this->args['paged'] > 1 ) {
			$query_args['paged'] = $this->args['paged'];
		}

		if ( 'id' !== $this->args['query_by'] ) {

			if ( $this->args['orderby'] ) {
				$query_args['orderby'] = $this->args['orderby'];
			}

			if ( $this->args['order'] ) {
				$query_args['order'] = $this->args['order'];
			}

			if ( $this->args['exclude'] ) {
				$query_args['post__not_in'] = $this->args['exclude'];
			}

		} else {

			if ( $this->args['include'] ) {
				$query_args['posts_per_page'] = $this->args['slider'] ? $this->args['number'] : count( $this->args['include'] );
				$query_args['post__in']       = $this->args['include'];
				$query_args['orderby']        = 'post__in';
				$query_args['order']          = 'ASC';
			}
		}

		$tax_query = [];

		if ( 'tax_meta' === $this->args['query_by'] && $this->args['taxonomies'] ) {

			foreach ( $this->args['taxonomies'] as $taxo ) {
				$taxonomy = mai_isset( $taxo, 'taxonomy', '' );
				$terms    = mai_isset( $taxo, 'terms', [] );
				$operator = mai_isset( $taxo, 'operator', '' );

				// Skip if we don't have all the tax query args.
				if ( ! ( $taxonomy && $terms && $operator ) ) {
					continue;
				}

				// Set the value.
				$tax_query[] = [
					'taxonomy' => $taxonomy,
					'field'    => 'id',
					'terms'    => $terms,
					'operator' => $operator,
				];
			}

			// If we have tax query values.
			if ( $tax_query ) {

				$query_args['tax_query'] = $tax_query;

				if ( $this->args['taxonomies_relation'] ) {
					$query_args['tax_query']['relation'] = $this->args['taxonomies_relation'];
				}
			}
		}

		return $query_args;
	}

	/**
	 * Gets openin markup.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	function get_open() {
		$attributes = [
			'class' => mai_add_classes( 'mait-testimonials', $this->args['class'] ),
			'style' => '',
		];

		// Boxed.
		if ( $this->args['boxed'] ) {
			$attributes['class'] .= ' has-boxed';
		}

		// Font size.
		if ( $this->args['font_size'] ) {
			$attributes['style'] .= sprintf( '--testimonial-font-size:var(--font-size-%s);', $this->args['font_size'] );
		}

		// Text align.
		if ( $this->args['text_align'] ) {
			$attributes['style'] .= sprintf( '--testimonial-text-align:%s;', $this->args['text_align'] );
		}

		// Get the columns breakpoint array.
		$columns = mai_get_breakpoint_columns( $this->args );

		$attributes['style'] .= sprintf( '--columns-lg:%s;', $columns['lg'] );
		$attributes['style'] .= sprintf( '--columns-md:%s;', $columns['md'] );
		$attributes['style'] .= sprintf( '--columns-sm:%s;', $columns['sm'] );
		$attributes['style'] .= sprintf( '--columns-xs:%s;', $columns['xs'] );

		// Column/Row gap.
		$column_gap = $this->args['column_gap'] ? sprintf( 'var(--spacing-%s)', $this->args['column_gap'] ) : '0px'; // Needs 0px for calc().
		$row_gap    = $this->args['row_gap'] ? sprintf( 'var(--spacing-%s)', $this->args['row_gap'] ) : '0px'; // Needs 0px for calc().

		$attributes['style'] .= sprintf( '--column-gap:%s;', $column_gap  );
		$attributes['style'] .= sprintf( '--row-gap:%s;', $row_gap );

		// Align columns.
		if ( $this->args['align_columns'] ) {
			$attributes['style'] .= sprintf( '--align-columns:%s;', mai_get_flex_align( $this->args['align_columns'] ) );
		}

		if ( $this->args['align_columns_vertical'] ) {
			$attributes['style'] .= sprintf( '--align-columns-vertical:%s;', mai_get_flex_align( $this->args['align_columns_vertical'] ) );
		}

		if ( 'before' === $this->args['author_location'] ) {
			$attributes['style'] .= '--testimonial-details-margin:0 0 var(--spacing-md);';
		}

		if ( 'inside' === $this->args['image_location'] && ( $this->has_name || $this->has_byline ) ) {
			$attributes['style'] .= '--testimonial-image-margin:0 var(--spacing-md) 0 0;--testimonial-details-text-align:left;';
		}

		// Current page.
		if ( $this->args['paged'] ) {
			$attributes['data-paged'] = $this->args['paged'];
		}

		return genesis_markup(
			[
				'open'    => '<div %s>',
				'context' => 'testimonials',
				'echo'    => false,
				'atts'    => $attributes,
				'params'  => [
					'args' => $this->args,
				],
			]
		);
	}

	/**
	 * Gets inner markup opening.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	function get_inner() {
		return genesis_markup(
			[
				'open'    => '<div %s>',
				'context' => 'testimonials-inner',
				'echo'    => false,
				'atts'    => [ 'class' => 'mait-inner' ],
				'params'  => [
					'args' => $this->args,
				],
			]
		);
	}

	/**
	 * Gets details.
	 *
	 * @since TBD
	 *
	 * @param string $image  Image value.
	 * @param string $name   Name value.
	 * @param string $byline Byline value.
	 *
	 * @return string
	 */
	function get_details( $image = '', $name = '', $byline = '' ) {
		$html = '';

		if ( 'inside' === $this->args['image_location'] ) {
			$html .= $image ?: '';
		}

		if ( $name || $byline ) {
			$html .= '<div class="mait-author">';
				$html .= $name ?: '';
				$html .= $byline ?: '';
			$html .= '</div>';
		}

		if ( ! $html ) {
			return $html;
		}

		return genesis_markup(
			[
				'open'    => '<div %s>',
				'close'   => '</div>',
				'context' => 'testimonial-details',
				'content' => $html,
				'echo'    => false,
				'atts'    => [ 'class' => 'mait-details' ],
				'params'  => [
					'args' => $this->args,
				],
			]
		);
	}

	/**
	 * Gets pagination dots.
	 *
	 * @since TBD
	 *
	 * @param WP_Query $query Query object.
	 *
	 * @return string
	 */
	function get_dots( $query ) {
		$html = '';

		for( $i = 0; $i < $this->slider_max; $i++ ) {
			$paged    = $i + 1;
			$text     = sprintf( '<span class="screen-reader-text">%s %s</span>', __( 'Go to page', 'genesis' ), $i );
			$current  = (int) $paged === (int) $this->args['paged'];
			$current  = $current ? ' mait-current' : '';
			$disabled = $current ? ' data-disabled="true"' : '';
			$html    .= sprintf( '<li><button class="mait-dot mait-button%s" data-paged="%s"%s>%s</button>', $current, $paged, $disabled, $text );
		}

		return genesis_markup(
			[
				'open'    => '<ul %s>',
				'close'   => '</ul>',
				'context' => 'testimonials-dots',
				'content' => $html,
				'echo'    => false,
				'atts'    => [
					'class'      => 'mait-dots',
					'role'       => 'navigation',
					'aria-label' => esc_attr__( 'Pagination', 'genesis' ),
				],
			]
		);
	}

	/**
	 * Gets prev/next slide arrows.
	 *
	 * @since TBD
	 *
	 * @param WP_Query $query Query object.
	 *
	 * @return string
	 */
	function get_arrows( $query ) {
		$html      = '';
		$prev_link = $this->get_previous_arrow( $query );
		$next_link = $this->get_next_arrow( $query );

		if ( $prev_link || $next_link ) {
			$html .= '<ul class="mait-arrows">';
				$html .= $prev_link ? sprintf( '<li class="mait-arrow mait-arrow-previous">%s</li>', $prev_link ) : '';
				$html .= $next_link ? sprintf( '<li class="mait-arrow mait-arrow-next">%s</li>', $next_link ) : '';
			$html .= '</ul>';
		}

		return $html;
	}

	/**
	 * Gets previous arrow.
	 *
	 * @since TBD
	 *
	 * @param WP_Query $query Query object.
	 *
	 * @return string
	 */
	function get_previous_arrow( $query ) {
		$icon     = apply_filters( 'mai_testimonials_previous_arrow', '←' );
		$classes  = 'mait-button mait-previous';
		$classes .= is_admin() ? ' button' : '';

		return sprintf( '<button class="%s" data-paged="%s">%s</button>', $classes, $this->get_prev_page( $query ), wp_kses_post( $icon ) );
	}

	/**
	 * Gets next arrow.
	 *
	 * @since TBD
	 *
	 * @param WP_Query $query Query object.
	 *
	 * @return string
	 */
	function get_next_arrow( $query ) {
		$icon     = apply_filters( 'mai_testimonials_previous_arrow', '→' );
		$classes  = 'mait-button mait-next';
		$classes .= is_admin() ? ' button' : '';

		return sprintf( '<button class="%s" data-paged="%s">%s</button>', $classes, $this->get_next_page( $query ), wp_kses_post( $icon ) );
	}

	/**
	 * Gets previous page number.
	 *
	 * @since TBD
	 *
	 * @param WP_Query $query Query object.
	 *
	 * @return int
	 */
	function get_prev_page( $query ) {
		$page = (int) $this->args['paged'] - 1;

		if ( $page < 1 ) {
			$page = (int) $query->max_num_pages; // Don't use slider_max here.
		}

		return $page;
	}

	/**
	 * Gets next page number.
	 *
	 * @since TBD
	 *
	 * @param WP_Query $query Query object.
	 *
	 * @return int
	 */
	function get_next_page( $query ) {
		$page = (int) $this->args['paged'] + 1;

		if ( $page > (int) $query->max_num_pages ) { // Don't use slider_max here.
			$page = 1;
		}

		return $page;
	}

	/**
	 * Sanitizes taxonomies.
	 *
	 * @since TBD
	 *
	 * @param array
	 *
	 * @return array
	 */
	function sanitize_taxonomies( $taxonomies ) {
		if ( ! $taxonomies ) {
			return $taxonomies;
		}

		$sanitized = [];

		foreach ( $taxonomies as $data ) {
			$args = wp_parse_args( $data,
				[
					'taxonomy' => '',
					'terms'    => [],
					'operator' => 'IN',
				]
			);

			// Skip if we don't have all of the data.
			if ( ! ( $args['taxonomy'] && $args['terms'] && $args['operator'] ) ) {
				continue;
			}

			$sanitized[] = [
				'taxonomy' => esc_html( $args['taxonomy'] ),
				'terms'    => array_map( 'absint', (array) $args['terms'] ),
				'operator' => esc_html( $args['operator'] ),
			];
		}

		return $sanitized;
	}
}
