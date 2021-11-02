<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

class Mai_Testimonials {

	public $args;
	public $has_image;
	public $has_name;
	public $has_byline;
	public $has_author;

	function __construct( $args ) {
		$args = wp_parse_args( $args,
			[
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

		$html       = '';
		$query_args = [
			'post_type'              => 'testimonial',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'suppress_filters'       => false, // https://github.com/10up/Engineering-Best-Practices/issues/116
		];

		if ( 'id' !== $this->args['query_by'] ) {

			if ( $this->args['number'] ) {
				$query_args['posts_per_page'] = $this->args['number'];
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

		$query = new WP_Query( $query_args );

		if ( $query->have_posts() ) {

			$html .= $this->get_open();
				$html .= $this->get_inner();

					while ( $query->have_posts() ) : $query->the_post();
						$content  = get_the_content();
						$image_id = $this->has_image ? get_post_thumbnail_id() : '';
						$image    = $this->has_image && $image_id ? wp_get_attachment_image( $image_id, 'tiny' ) : '';
						$image    = $image ? sprintf( '<div class="mai-testimonial-image">%s</div>', $image ) : '';
						$name     = $this->has_name ? get_the_title() : '';
						$name     = $name ? sprintf( '<span class="mai-testimonial-name">%s</span>', $name ) : '';
						$byline   = $this->has_byline ? get_post_meta( get_the_ID(), 'byline', true ) : '';
						$byline   = $byline ? sprintf( '<span class="mai-testimonial-byline">%s</span>', $byline ) : '';
						$details  = $this->get_details( $image, $name, $byline );

						$html .= '<div class="mai-testimonial">';

							if ( 'before' === $this->args['image_location'] ) {
								$html .= $image ?: '';
							}

							if ( 'before' === $this->args['author_location'] ) {
								$html .= $details ?: '';
							}

							$html .= sprintf( '<div class="mai-testimonial-content">%s</div>', mai_get_processed_content( $content ) );

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

		}

		wp_reset_postdata();

		return $html;
	}

	function get_open() {
		$attributes = [
			'class' => mai_add_classes( 'mai-testimonials', $this->args['class'] ),
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
				'atts'    => [ 'class' => 'mai-testimonials-inner' ],
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
			$html .= '<div class="mai-testimonial-author">';
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
				'atts'    => [ 'class' => 'mai-testimonial-details' ],
				'params'  => [
					'args' => $this->args,
				],
			]
		);
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
