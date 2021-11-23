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
	public $has_author;

	function __construct( $args ) {
		$args = wp_parse_args( $args,
			[
				'slider'                 => true,
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
				'class'                  => '',
			]
		);

		// Sanitize.
		$args = [
			'slider'                 => mai_sanitize_bool( $args['slider'] ),
			'paged'                  => absint( $args['paged'] ),
			'font_size'              => esc_html( $args['font_size'] ),
			'text_align'             => esc_html( $args['text_align'] ),
			'image_location'         => esc_html( $args['image_location'] ),
			'author_location'        => esc_html( $args['author_location'] ),
			'show'                   => array_map( 'esc_html', (array) $args['show'] ),
			'query_by'               => esc_html( $args['query_by'] ),
			'number'                 => absint( $args['number'] ),
			'include'                => $args['include'] ? array_map( 'absint', (array) $args['include'] ) : [],
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
	}

	function render() {
		echo $this->get();
	}

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
			// if ( $this->args['slider'] ) {
			if ( $this->args['slider'] && 1 === $this->args['paged'] ) {
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

			if ( $this->args['slider'] && 1 === $this->args['paged'] ) {
				if ( $this->args['slider'] ) {
					$html .= $this->get_prev_next_posts_nav( $query );

					// TODO: Dots can be mait-button with data-paged so all JS just works.

					// $html .= $this->get_numeric_posts_nav( $query );
				}

			// if ( $this->args['slider'] ) {
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

	function get_query_args() {
		$query_args = [
			'post_type'              => 'testimonial',
			'no_found_rows'          => ! $this->args['paged'],
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'suppress_filters'       => false, // https://github.com/10up/Engineering-Best-Practices/issues/116
		];

		if ( $this->args['paged'] > 1 ) {
			$query_args['paged'] = $this->args['paged'];
		}

		if ( 'id' !== $this->args['query_by'] ) {

			if ( $this->args['number'] ) {
				$query_args['posts_per_page'] = $this->args['number'];

				// Offset for slider.
				// if ( $this->args['paged'] ) {
				// 	$query_args['offset'] = (int) $query_args['posts_per_page'] * (int) $this->args['paged'];
				// }
			}

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
				$query_args['posts_per_page'] = count( $this->args['include'] );
				$query_args['post__in']       = $this->args['include'];
				$query_args['order']          = 'post__in';
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

		// Orderby.
		if ( 'id' !== $this->args['query_by'] && $this->args['orderby'] ) {
			$query_args['orderby'] = $this->args['orderby'];
		}

		// Order.
		if ( $this->args['order'] ) {
			$query_args['order'] = $this->args['order'];
		}

		return $query_args;
	}

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
	 * Gets prev/next slide arrows.
	 *
	 * @since TBD
	 */
	function get_prev_next_posts_nav( $query ) {
		$html      = '';
		$prev_link = $this->get_previous_posts_link( $query );
		$next_link = $this->get_next_posts_link( $query );

		if ( $prev_link || $next_link ) {
			$html .= '<ul class="mait-arrows">';
				$html .= $prev_link ? sprintf( '<li class="mait-arrow mait-arrow-previous">%s</li>', $prev_link ) : '';
				$html .= $next_link ? sprintf( '<li class="mait-arrow mait-arrow-next">%s</li>', $next_link ) : '';
			$html .= '</ul>';
		}

		return $html;
	}

	function get_previous_posts_link( $query ) {
		return sprintf( '<button class="mait-button mait-previous" data-paged="%s">%s</button>', $this->get_prev_page( $query ), '<' );
	}

	function get_next_posts_link( $query ) {
		return sprintf( '<button class="mait-button mait-next" data-paged="%s">%s</button>', $this->get_next_page( $query ), '>' );
	}

	function get_prev_page( $query ) {
		$page = (int) $this->args['paged'] - 1;

		if ( $page < 1 ) {
			$page = (int) $query->max_num_pages;
		}

		return $page;
	}

	function get_next_page( $query ) {
		$page = (int) $this->args['paged'] + 1;

		if ( $page > (int) $query->max_num_pages ) {
			$page = 1;
		}

		return $page;
	}

	/**
	 * Gets pagination dots.
	 *
	 * The links, if needed, are ordered as:
	 *
	 *  * previous page arrow,
	 *  * first page,
	 *  * up to two pages before current page,
	 *  * current page,
	 *  * up to two pages after the current page,
	 *  * last page,
	 *  * next page arrow.
	 *
	 * @since TBD
	 *
	 * @param WP_Query $query Query object.
	 *
	 * @return void Return early if on a single post or page, or only one page exists.
	 */
	function get_numeric_posts_nav( $query ) {
		// Stop execution if there's only one page.
		if ( $query->max_num_pages <= 1 ) {
			return;
		}

		$html  = '';
		$paged = $this->args['paged'];
		$max   = (int) $query->max_num_pages;

		// Add current page to the array.
		if ( $paged >= 1 ) {
			$links[] = $paged;
		}

		// Add the pages around the current page to the array.
		if ( $paged >= 3 ) {
			$links[] = $paged - 1;
			$links[] = $paged - 2;
		}

		if ( ( $paged + 2 ) <= $max ) {
			$links[] = $paged + 2;
			$links[] = $paged + 1;
		}

		$atts = [
			'role'       => 'navigation',
			'aria-label' => esc_attr__( 'Pagination', 'genesis' ),
		];

		$html .= genesis_markup(
			[
				'open'    => '<div %s>',
				'context' => 'archive-pagination',
				'echo'    => false,
				'atts'    => $atts,
			]
		);

		$before_number = sprintf( '<span class="screen-reader-text">%s</span>', __( 'Go to page', 'genesis' ) );

		$html .= '<ul>';

		// Previous Post Link.
		if ( get_previous_posts_link() ) {
			$ally_label = __( '<span class="screen-reader-text">Go to</span> Previous Page', 'genesis' );
			$label      = genesis_a11y() ? $ally_label : __( 'Previous Page', 'genesis' );
			$link       = get_previous_posts_link( apply_filters( 'genesis_prev_link_text', '&#x000AB; ' . $label ) );
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Value is hardcoded and safe, not set via input.
			$html .= sprintf( '<li class="pagination-previous">%s</li>' . "\n", $link );
		}

		// Link to first page, plus ellipses if necessary.
		if ( ! in_array( 1, $links, true ) ) {
			$class = 1 === $paged ? ' class="active"' : '';

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Value is known to be safe, not set via input.
			$html .= sprintf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, get_pagenum_link( 1 ), trim( $before_number . ' 1' ) );

			if ( ! in_array( 2, $links, true ) ) {
				$a11y_label = sprintf( '<span class="screen-reader-text">%s</span> &#x02026;', __( 'Interim pages omitted', 'genesis' ) );
				$label      = genesis_a11y() ? $a11y_label : '&#x02026;';
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Value is known to be safe, not set via input.
				printf( '<li class="pagination-omission">%s</li> ' . "\n", $label );
			}
		}

		// Link to current page, plus 2 pages in either direction if necessary.
		sort( $links );
		foreach ( (array) $links as $link ) {
			$class = '';
			$aria  = '';
			if ( $paged === $link ) {
				$class = ' class="active" ';
				$aria  = ' aria-label="' . esc_attr__( 'Current page', 'genesis' ) . '" aria-current="page"';
			}

			$html .= sprintf(
				'<li%s><a href="%s"%s>%s</a></li>' . "\n",
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Value is safe, not set via input.
				$class,
				esc_url( get_pagenum_link( $link ) ),
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Value is safe, not set via input.
				$aria,
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Value is safe, not set via input.
				trim( $before_number . ' ' . $link )
			);
		}

		// Link to last page, plus ellipses if necessary.
		if ( ! in_array( $max, $links, true ) ) {

			if ( ! in_array( $max - 1, $links, true ) ) {
				$a11y_label = sprintf( '<span class="screen-reader-text">%s</span> &#x02026;', __( 'Interim pages omitted', 'genesis' ) );
				$label      = genesis_a11y() ? $a11y_label : '&#x02026;';
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Value is known to be safe, not set via input.
				$html .= sprintf( '<li class="pagination-omission">%s</li> ' . "\n", $label );
			}

			$class = $paged === $max ? ' class="active"' : '';
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Value is safe, not set via input.
			$html .= sprintf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, get_pagenum_link( $max ), trim( $before_number . ' ' . $max ) );
		}

		// Next Post Link.
		if ( get_next_posts_link() ) {
			$ally_label = __( '<span class="screen-reader-text">Go to</span> Next Page', 'genesis' );
			$label      = genesis_a11y() ? $ally_label : __( 'Next Page', 'genesis' );
			$link       = get_next_posts_link( apply_filters( 'genesis_next_link_text', $label . ' &#x000BB;' ) );
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Value is hardcoded and safe, not set via input.
			$html .= sprintf( '<li class="pagination-next">%s</li>' . "\n", $link );
		}

		$html .= '</ul>';
		$html .= genesis_markup(
			[
				'close'   => '</div>',
				'context' => 'archive-pagination',
				'echo'    => false,
			]
		);

		$html .= "\n";

		return $html;
	}

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
