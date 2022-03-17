<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

class Mai_Testimonials {
	public $args;
	public $query_args;
	public $has_image;
	public $has_name;
	public $has_byline;
	public $has_slider;
	public $slider_max;

	function __construct( $args ) {
		$args = wp_parse_args( $args,
			[
				'preview'                => false,
				'paged'                  => 1,
				'font_size'              => '',
				'text_align'             => '',
				'details_align'          => '',
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
				'margin_top'             => '',
				'margin_bottom'          => '',
				'boxed'                  => '',
				'slider'                 => false,
				'slider_show'            => [ 'arrows', 'dots' ],
				'slider_max'             => 0,
				'class'                  => '',
			]
		);

		// Sanitize.
		$args = [
			'preview'                => mai_sanitize_bool( $args['preview'] ),
			'paged'                  => absint( $args['paged'] ),
			'font_size'              => esc_html( $args['font_size'] ),
			'text_align'             => esc_html( $args['text_align'] ),
			'details_align'          => esc_html( $args['details_align'] ),
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
			'margin_top'             => esc_html( $args['margin_top'] ),
			'margin_bottom'          => esc_html( $args['margin_bottom'] ),
			'boxed'                  => mai_sanitize_bool( $args['boxed'] ),
			'slider'                 => mai_sanitize_bool( $args['slider'] ),
			'slider_show'            => array_map( 'esc_html', (array) $args['slider_show'] ),
			'slider_max'             => absint( $args['slider_max'] ),
			'class'                  => esc_html( $args['class'] ),
		];

		$this->args        = $args;
		$this->query_args  = $this->get_query_args();
		$show_keys         = array_flip( $this->args['show'] );
		$this->has_image   = isset( $show_keys['image'] );
		$this->has_name    = isset( $show_keys['name'] );
		$this->has_byline  = isset( $show_keys['byline'] );
		$this->has_url     = isset( $show_keys['url'] );
		$this->has_slider  = $this->args['slider'] && count( $this->args['slider_show'] ) >= 1;
	}

	/**
	 * Displays testimonials.
	 *
	 * @since 2.3.0
	 *
	 * @return void
	 */
	function render() {
		echo $this->get();
	}

	/**
	 * Gets testimonials.
	 *
	 * @since 2.3.0
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
			// If slider.
			if ( $this->has_slider && 1 === $this->args['paged'] ) {
				// Slider max.
				if ( 'id' === $this->args['query_by'] ) {
					// If by choice, the max slides is the number chosen divided by the number displayed, rounded up.
					$this->slider_max = absint( ceil( count( $this->args['include'] ) / $this->args['number'] ) );
				} else {
					// If slider_max has a value, use the lesser of that or max from the query. Otherwise use max from query.
					$this->slider_max = $this->args['slider_max'] ? min( $this->args['slider_max'], (int) $query->max_num_pages ) : (int) $query->max_num_pages;
				}

				$atts = [
					'class'        => 'mait-slider',
					'data-args'    => esc_html( json_encode( $this->args ), ENT_QUOTES, 'UTF-8' ),
					'data-current' => $this->args['paged'],
					'data-prev'    => $this->get_prev_page( $query ),
					'data-next'    => $this->get_next_page( $query ),
					'data-max'     => $this->slider_max,
				];

				// Margin. If not a slider this is on the testimonials container.
				if ( $this->args['margin_top'] ) {
					$atts['class'] = mai_add_classes( sprintf( 'has-%s-margin-top', $this->args['margin_top'] ), $atts['class'] );
				}

				if ( $this->args['margin_bottom'] ) {
					$atts['class'] = mai_add_classes( sprintf( 'has-%s-margin-bottom', $this->args['margin_bottom'] ), $atts['class'] );
				}

				$html .= genesis_markup(
					[
						'open'    => '<div %s>',
						'context' => 'testimonials-slider',
						'echo'    => false,
						'atts'    => $atts,
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
						$url      = $this->has_url ? get_post_meta( get_the_ID(), 'url', true ) : '';

						if ( $url ) {
							$parsed = wp_parse_url( $url );
							$url    = sprintf( '<span class="mait-url"><a target="_blank" rel="nofollow noopener" href="%s">%s</a></span>', esc_url( $url ), $parsed['host'] );
						}

						// Build details.
						$details  = $this->get_details( $image, $name, $byline, $url );

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
	 * @since 2.3.0
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
	 * @since 2.3.0
	 *
	 * @return string
	 */
	function get_open() {
		$atts = [
			'class' => mai_add_classes( 'mait-testimonials', $this->args['class'] ),
		];

		$atts             = mai_get_columns_atts( $atts, $this->args );
		$has_image_inside = $this->has_image && 'inside' === $this->args['image_location'];
		$has_details      = $has_image_inside || $this->has_name || $this->has_byline || $this->has_url;

		// Boxed.
		if ( $this->args['boxed'] ) {
			$atts['class'] .= ' has-boxed';
		}

		// Margin. If slider this is on the slider container.
		if ( ! ( $this->has_slider && 1 === $this->args['paged'] ) ) {
			if ( $this->args['margin_top'] ) {
				$atts['class'] = mai_add_classes( sprintf( 'has-%s-margin-top', $this->args['margin_top'] ), $atts['class'] );
			}

			if ( $this->args['margin_bottom'] ) {
				$atts['class'] = mai_add_classes( sprintf( 'has-%s-margin-bottom', $this->args['margin_bottom'] ), $atts['class'] );
			}
		}

		// Font size.
		if ( $this->args['font_size'] ) {
			$atts['style'] .= sprintf( '--testimonial-font-size:var(--font-size-%s);', $this->args['font_size'] );
		}

		// Text align.
		if ( $this->args['text_align'] ) {
			$atts['style'] .= sprintf( '--testimonial-text-align:%s;', $this->args['text_align'] );
		}

		// Details.
		if ( $has_details ) {
			if ( 'before' === $this->args['author_location'] ) {
				$atts['style'] .= '--testimonial-details-margin:0 0 var(--spacing-md);';
			}

			// Details align.
			if ( $this->args['details_align'] ) {
				$atts['style'] .= sprintf( '--testimonial-details-justify-content:%s;', $this->args['details_align'] );

				// No image, text can align.
				if ( ! $has_image_inside ) {
					$atts['style'] .= sprintf( '--testimonial-details-text-align:%s;', $this->args['details_align'] );
				}
				/**
				 * Has image.
				 * Start or center align with an image should both align text to start.
				 * End is the only setting with text aligned end.
				 */
				else {
					if ( 'end' === $this->args['details_align'] ) {
						$atts['style'] .= '--testimonial-details-text-align:end;';
					} else {
						$atts['style'] .= '--testimonial-details-text-align:start;';
					}
				}
			}
		}

		if ( $has_image_inside ) {
			$atts['style'] .= '--testimonial-image-margin:0;';
		}

		// if ( ! $this->args['details_align'] || ( $this->args['details_align'] && 'inside' === $this->args['image_location'] && ( $this->has_name || $this->has_byline || $this->has_url ) ) ) {
		// 	if ( ! $this->args['details_align'] ) {
		// 		$atts['style'] .= '--testimonial-image-margin:0 var(--spacing-md) 0 0;--testimonial-details-text-align:start;';
		// 	} else {
		// 		if ( in_array( $this->args['details_align'], [ 'start, center' ] ) ) {
		// 			$atts['style'] .= '--testimonial-image-margin:0 var(--spacing-md) 0 0;--testimonial-details-text-align:start;';
		// 		} else {
		// 			$atts['style'] .= '--testimonial-image-margin:0 0 0 var(--spacing-md);--testimonial-details-text-align:end;';
		// 		}
		// 	}
		// }

		// Current page.
		if ( $this->args['paged'] ) {
			$atts['data-slide'] = $this->args['paged'];
		}

		return genesis_markup(
			[
				'open'    => '<div %s>',
				'context' => 'testimonials',
				'echo'    => false,
				'atts'    => $atts,
				'params'  => [
					'args' => $this->args,
				],
			]
		);
	}

	/**
	 * Gets inner markup opening.
	 *
	 * @since 2.3.0
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
	 * @since 2.3.0
	 *
	 * @param string $image  Image value.
	 * @param string $name   Name value.
	 * @param string $byline Byline value.
	 * @param string $url    Url value.
	 *
	 * @return string
	 */
	function get_details( $image = '', $name = '', $byline = '', $url = '' ) {
		$html = '';

		if ( $this->has_image && 'inside' === $this->args['image_location'] && 'end' !== $this->args['details_align'] ) {
			$html .= $image ?: '';
		}

		if ( $name || $byline || $url ) {
			$html .= '<div class="mait-author">';
				$html .= $name ?: '';
				$html .= $byline ?: '';
				$html .= $url ?: '';
			$html .= '</div>';
		}

		if ( $this->has_image && 'inside' === $this->args['image_location'] && 'end' === $this->args['details_align'] ) {
			$html .= $image ?: '';
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
	 * @since 2.3.0
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
			$html    .= sprintf( '<li><button class="mait-dot mait-button%s" data-slide="%s"%s>%s</button>', $current, $paged, $disabled, $text );
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
	 * @since 2.3.0
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
	 * @since 2.3.0
	 *
	 * @param WP_Query $query Query object.
	 *
	 * @return string
	 */
	function get_previous_arrow( $query ) {
		$icon     = apply_filters( 'mai_testimonials_previous_arrow', '←' );
		$classes  = 'mait-button mait-previous';
		$classes .= is_admin() ? ' button' : '';

		return sprintf( '<button class="%s" data-slide="%s">%s</button>', $classes, $this->get_prev_page( $query ), wp_kses_post( $icon ) );
	}

	/**
	 * Gets next arrow.
	 *
	 * @since 2.3.0
	 *
	 * @param WP_Query $query Query object.
	 *
	 * @return string
	 */
	function get_next_arrow( $query ) {
		$icon     = apply_filters( 'mai_testimonials_previous_arrow', '→' );
		$classes  = 'mait-button mait-next';
		$classes .= is_admin() ? ' button' : '';

		return sprintf( '<button class="%s" data-slide="%s">%s</button>', $classes, $this->get_next_page( $query ), wp_kses_post( $icon ) );
	}

	/**
	 * Gets previous page number.
	 *
	 * @since 2.3.0
	 *
	 * @param WP_Query $query Query object.
	 *
	 * @return int
	 */
	function get_prev_page( $query ) {
		$page = (int) $this->args['paged'] - 1;

		if ( $page < 1 ) {
			$page = (int) $query->max_num_pages; // Can't use slider_max here?
		}

		return $page;
	}

	/**
	 * Gets next page number.
	 *
	 * @since 2.3.0
	 *
	 * @param WP_Query $query Query object.
	 *
	 * @return int
	 */
	function get_next_page( $query ) {
		$page = (int) $this->args['paged'] + 1;

		if ( $page > (int) $query->max_num_pages ) { // Can't use slider_max here?
			$page = 1;
		}

		return $page;
	}

	/**
	 * Sanitizes taxonomies.
	 *
	 * @since 2.3.0
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
