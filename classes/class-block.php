<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

class Mai_Testimonials_Block {

	function __construct() {
		$this->hooks();
	}

	/**
	 * Runs hooks.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	function hooks() {
		add_action( 'acf/init', [ $this, 'register_block' ] );
		add_action( 'acf/load_field/key=mai_testimonials_taxonomy', [ $this, 'load_taxonomies' ] );
		add_filter( 'acf/load_field/key=mai_testimonials_terms', [ $this, 'load_terms' ] );
		add_filter( 'acf/prepare_field/key=mai_testimonials_terms', [ $this, 'prepare_terms' ] );
		add_action( 'acf/render_field/key=mai_testimonials_display_tab', [ $this, 'admin_css' ] );
	}

	/**
	 * Register Mai Testimonials block.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	function register_block() {
		if ( ! ( function_exists( 'acf_register_block_type' ) && function_exists( 'acf_add_local_field_group' ) ) ) {
			return;
		}

		acf_register_block_type(
			[
				'name'            => 'mai-testimonials',
				'title'           => __( 'Mai Testimonials', 'mai-testimonials' ),
				'description'     => __( 'Display testimonials in various layouts and configurations.', 'mai-testimonials' ),
				'render_callback' => [ $this, 'do_testimonial' ],
				'category'        => 'widgets',
				'keywords'        => [ 'testimonial' ],
				'icon'            => 'format-quote',
				'mode'            => 'preview',
				'enqueue_assets' => function() {
					$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

					// Block.
					wp_enqueue_style( 'mai-testimonials', MAI_TESTIMONIALS_PLUGIN_URL . "assets/css/mai-testimonials{$suffix}.css" );

					// Editor.
					if ( is_admin() ) {
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
				},
				'supports'        => [
					'align' => false,
				],
			]
		);

		$this->register_fields();
	}

	/**
	 * Callback function to render the testimonials block.
	 *
	 * @since TBD
	 *
	 * @param array  $block      The block settings and attributes.
	 * @param string $content    The block inner HTML (empty).
	 * @param bool   $is_preview True during AJAX preview.
	 * @param int    $post_id    The post ID this block is saved to.
	 *
	 * @return void
	 */
	function do_testimonial( $block, $content = '', $is_preview = false, $post_id = 0 ) {
		$args = [
			'font_size'              => get_field( 'font_size' ),
			'text_align'             => get_field( 'text_align' ),
			'image_location'         => get_field( 'image_location' ),
			'author_location'        => get_field( 'author_location' ),
			'show'                   => get_field( 'show' ),
			'query_by'               => get_field( 'query_by' ),
			'number'                 => get_field( 'number' ),
			'include'                => get_field( 'include' ),
			'taxonomies'             => get_field( 'taxonomies' ), // Repeater.
			'taxonomies_relation'    => get_field( 'taxonomies_relation' ),
			'orderby'                => get_field( 'orderby' ),
			'order'                  => get_field( 'order' ),
			'exclude'                => get_field( 'exclude' ),
			'columns'                => get_field( 'columns' ),
			'columns_responsive'     => get_field( 'columns_responsive' ),
			'columns_md'             => get_field( 'columns_md' ),
			'columns_sm'             => get_field( 'columns_sm' ),
			'columns_xs'             => get_field( 'columns_xs' ),
			'align_columns'          => get_field( 'align_columns' ),
			'align_columns_vertical' => get_field( 'align_columns_vertical' ),
			'column_gap'             => get_field( 'column_gap' ),
			'row_gap'                => get_field( 'row_gap' ),
			'boxed'                  => get_field( 'boxed' ),
			'slider'                 => get_field( 'slider' ),
			'slider_show'            => get_field( 'slider_show' ),
			'slider_max'             => get_field( 'slider_max' ),
			'class'                  => isset( $block['className'] ) ? mai_add_classes( $block['className'] ) : '',
		];

		$testimonials = new Mai_Testimonials( $args );
		$testimonials->render();
	}

	/**
	 * Registers field group.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	function register_fields() {
		acf_add_local_field_group(
			[
				'key'    => 'mai_testimonials_block',
				'title'  => __( 'Mai Testimonials Block', 'mai-testimonials' ),
				'fields' => [
					[
						'key'       => 'mai_testimonials_display_tab',__(  'mai-testimonials' ),
						'label'     => __( 'Display', 'mai-testimonials' ),
						'type'      => 'tab',
						'placement' => 'top',
					],
					[
						'key'           => 'mai_testimonials_font_size',
						'label'         => __( 'Text Size', 'mai-testimonials' ),
						'name'          => 'font_size',
						'type'          => 'button_group',
						'default_value' => 'md',
						'choices'       => [
							'sm' => 'S',
							'md' => 'M',
							'lg' => 'L',
							'xl' => 'XL',
						],
						'wrapper'       => [
							'class' => 'mai-acf-button-group',
						],
					],
					[
						'key'           => 'mai_testimonials_text_align',
						'label'         => __( 'Text Align', 'mai-testimonials' ),
						'name'          => 'text_align',
						'type'          => 'button_group',
						'wrapper'       => [
							'class'        => 'mai-acf-button-group',
						],
						'choices'       => [
							'start'        => __( 'Start', 'mai-testimonials' ),
							'center'       => __( 'Center', 'mai-testimonials' ),
							'end'          => __( 'End', 'mai-testimonials' ),
						],
						'allow_null'    => 0,
						'default_value' => 'start',
						'layout'        => 'horizontal',
						'return_format' => 'value',
					],
					[
						'key'           => 'mai_testimonials_show',
						'label'         => 'Show',
						'name'          => 'show',
						'type'          => 'checkbox',
						'default_value' => [
							'image',
							'name',
							'byline',
						],
						'choices'       => [
							'image'  => __( 'Image', 'mai-testimonials' ),
							'name'   => __( 'Name', 'mai-testimonials' ),
							'byline' => __( 'Byline', 'mai-testimonials' ),
						],
					],
					[
						'key'               => 'mai_testimonials_image_location',
						'label'             => __( 'Image location', 'mai-testimonials' ),
						'name'              => 'image_location',
						'type'              => 'select',
						'default_value'     => 'inside',
						'choices'           => [
							'before' => __( 'Above content', 'mai-testimonials' ),
							'after'  => __( 'Below content', 'mai-testimonials' ),
							'inside' => __( 'Next to name/byline', 'mai-testimonials' ),
						],
						'conditional_logic' => [
							[
								[
									'field'    => 'mai_testimonials_show',
									'operator' => '==',
									'value'    => 'image',
								],
							],
						],
					],
					[
						'key'           => 'mai_testimonials_author_location',
						'label'         => __( 'Name/byline location', 'mai-testimonials' ),
						'name'          => 'author_location',
						'type'          => 'select',
						'default_value' => 'after',
						'choices'       => [
							'before' => __( 'Above content', 'mai-testimonials' ),
							'after'  => __( 'Below content', 'mai-testimonials' ),
						],
					],
					[
						'key'     => 'mai_testimonials_boxed',
						'label'   => __( 'Boxed', 'mai-testimonials' ),
						'name'    => 'boxed',
						'type'    => 'true_false',
						'message' => __( 'Display boxed styling', 'mai-testimonials' ),
					],
					[
						'key'   => 'mai_testimonials_layout_tab',
						'label' => __( 'Layout', 'mai-testimonials' ),
						'type'  => 'tab',
					],
					[
						'key'           => 'mai_testimonials_columns',
						'label'         => 'Columns',
						'name'          => 'columns',
						'type'          => 'button_group',
						'default_value' => 3,
						'choices'       => [
							1 => 1,
							2 => 2,
							3 => 3,
							4 => 4,
							5 => 5,
							6 => 6,
						],
						'wrapper'       => [
							'class' => 'mai-acf-button-group',
						]
					],
					[
						'key'     => 'mai_testimonials_columns_responsive',
						'name'    => 'columns_responsive',
						'type'    => 'true_false',
						'message' => __( 'Custom responsive columns', 'mai-testimonials' ),
					],
					[
						'key'               => 'mai_testimonials_columns_md',
						'label'             => __( 'Columns (lg tablets)', 'mai-testimonials' ),
						'name'              => 'columns_md',
						'type'              => 'button_group',
						'choices'           => [
							1 => 1,
							2 => 2,
							3 => 3,
							4 => 4,
							5 => 5,
							6 => 6,
						],
						'wrapper'           => [
							'class' => 'mai-acf-button-group mai-grid-nested-columns mai-grid-nested-columns-first',
						],
						'conditional_logic' => [
							[
								[
									'field'    => 'mai_testimonials_columns_responsive',
									'operator' => '==',
									'value'    => 1,
								],
							],
						],
					],
					[
						'key'               => 'mai_testimonials_columns_sm',
						'label'             => __( 'Columns (md tablets)', 'mai-testimonials' ),
						'name'              => 'columns_sm',
						'type'              => 'button_group',
						'choices'           => [
							1 => 1,
							2 => 2,
							3 => 3,
							4 => 4,
							5 => 5,
							6 => 6,
						],
						'wrapper'           => [
							'class' => 'mai-acf-button-group mai-grid-nested-columns',
						],
						'conditional_logic' => [
							[
								[
									'field'    => 'mai_testimonials_columns_responsive',
									'operator' => '==',
									'value'    => 1,
								],
							],
						],
					],
					[
						'key'               => 'mai_testimonials_columns_xs',
						'label'             => __( 'Columns (mobile)', 'mai-testimonials' ),
						'name'              => 'columns_xs',
						'type'              => 'button_group',
						'choices'           => [
							1 => 1,
							2 => 2,
							3 => 3,
							4 => 4,
							5 => 5,
							6 => 6,
						],
						'wrapper'           => [
							'class' => 'mai-acf-button-group mai-grid-nested-columns mai-grid-nested-columns-last',
						],
						'conditional_logic' => [
							[
								[
									'field'    => 'mai_testimonials_columns_responsive',
									'operator' => '==',
									'value'    => 1,
								],
							],
						],
					],
					[
						'key'               => 'mai_testimonials_align_columns',
						'label'             => __( 'Align Columns', 'mai-testimonials' ),
						'name'              => 'align_columns',
						'type'              => 'button_group',
						'default_value'     => 'center',
						'choices'           => [
							'start'  => 'Start',
							'center' => 'Center',
							'end'    => 'End',
						],
						'wrapper'           => [
							'class' => 'mai-acf-button-group',
						],
						'conditional_logic' => [
							[
								[
									'field'    => 'mai_testimonials_columns',
									'operator' => '!=',
									'value'    => '1',
								],
							],
						],
					],
					[
						'key'               => 'mai_testimonials_align_columns_vertical',
						'label'             => __( 'Align Columns (vertical)', 'mai-testimonials' ),
						'name'              => 'align_columns_vertical',
						'type'              => 'button_group',
						'choices'           => [
							'full'   => __( 'Full', 'mai-testimonials' ),
							'top'    => __( 'Top', 'mai-testimonials' ),
							'middle' => __( 'Middle', 'mai-testimonials' ),
							'bottom' => __( 'Bottom', 'mai-testimonials' ),
						],
						'wrapper'           => [
							'class' => 'mai-acf-button-group',
						],
						'conditional_logic' => [
							[
								[
									'field'    => 'mai_testimonials_columns',
									'operator' => '!=',
									'value'    => '1',
								],
							],
						],
					],
					[
						'key'           => 'mai_testimonials_column_gap',
						'label'         => __( 'Column Gap', 'mai-testimonials' ),
						'name'          => 'column_gap',
						'type'          => 'button_group',
						'default_value' => 'md',
						'choices'       => [
							''     => __( 'None', 'mai-testimonials' ),
							'md'   => __( 'XS', 'mai-testimonials' ),
							'lg'   => __( 'S', 'mai-testimonials' ),
							'xl'   => __( 'M', 'mai-testimonials' ),
							'xxl'  => __( 'L', 'mai-testimonials' ),
							'xxxl' => __( 'XL', 'mai-testimonials' ),
						],
						'wrapper'       => [
							'class' => 'mai-acf-button-group',
						],
					],
					[
						'key'           => 'mai_testimonials_row_gap',
						'label'         => __( 'Row Gap', 'mai-testimonials' ),
						'name'          => 'row_gap',
						'type'          => 'button_group',
						'default_value' => 'md',
						'choices'       => [
							''     => __( 'None', 'mai-testimonials' ),
							'md'   => __( 'XS', 'mai-testimonials' ),
							'lg'   => __( 'S', 'mai-testimonials' ),
							'xl'   => __( 'M', 'mai-testimonials' ),
							'xxl'  => __( 'L', 'mai-testimonials' ),
							'xxxl' => __( 'XL', 'mai-testimonials' ),
						],
						'wrapper'       => [
							'class' => 'mai-acf-button-group',
						],
					],
					[
						'key'   => 'mai_testimonials_entries_tab',
						'label' => __( 'Entries', 'mai-testimonials' ),
						'type'  => 'tab',
					],
					[
						'key'     => 'mai_testimonials_query_by',
						'label'   => __( 'Get testimonials by', 'mai-testimonials' ),
						'name'    => 'query_by',
						'type'    => 'select',
						'choices' => [
							''         => __( 'Date', 'mai-testimonials' ),
							'id'       => __( 'Choice', 'mai-testimonials' ),
							'tax_meta' => __( 'Taxonomy', 'mai-testimonials' ),
						],
					],
					[
						'key'               => 'mai_testimonials_number',
						'label'             => __( 'Number to display', 'mai-testimonials' ),
						'name'              => 'number',
						'type'              => 'number',
						'default_value'     => 3,
						'min'               => 1,
						'conditional_logic' => [
							[
								[
									'field'    => 'mai_testimonials_query_by',
									'operator' => '!=',
									'value'    => 'id',
								],
							],
							[
								[
									'field'    => 'mai_testimonials_slider',
									'operator' => '==',
									'value'    => '1',
								],
							],
						],
					],
					[
						'key'               => 'mai_testimonials_include',
						'label'             => __( 'Include', 'mai-testimonials' ),
						'name'              => 'include',
						'type'              => 'post_object',
						'instructions'      => __( 'Show specific testimonials.', 'mai-testimonials' ),
						'multiple'          => 1,
						'return_format'     => 'id',
						'ui'                => 1,
						'post_type'         => [
							'testimonial',
						],
						'conditional_logic' => [
							[
								[
									'field'    => 'mai_testimonials_query_by',
									'operator' => '==',
									'value'    => 'id',
								],
							],
						],
					],
					[
						'key'               => 'mai_testimonials_taxonomies',
						'label'             => __( 'Taxonomies', 'mai-testimonials' ),
						'name'              => 'taxonomies',
						'type'              => 'repeater',
						'instructions'      => __( 'Limit to testimonials in these taxonomies.', 'mai-testimonials' ),
						'collapsed'         => 'mai_testimonials_terms',
						'layout'            => 'block',
						'button_label'      => __( 'Add Taxonomy Condition', 'mai-testimonials' ),
						'sub_fields'        => [
							// TODO: Including ajax load taxonomy name.
							[
								'key'           => 'mai_testimonials_taxonomy',
								'label'         => __( 'Taxonomy', 'mai-testimonials' ),
								'name'          => 'taxonomy',
								'type'          => 'select',
								'default_value' => 'testimonial_cat',
								'choices'       => [],
								'ui'            => 0,
								'ajax'          => 1,
							],
							[
								'key'      => 'mai_testimonials_terms',
								'label'    => __( 'Terms', 'mai-testimonials' ),
								'name'     => 'terms',
								'type'     => 'select',
								'choices'  => [],
								'ui'       => 1,
								'ajax'     => 1,
								'multiple' => 1,
							],
							[
								'key'        => 'mai_testimonials_operator',
								'label'      => __( 'Operator', 'mai-testimonials' ),
								'name'       => 'operator',
								'type'       => 'select',
								'choices'    => [
									'IN'     => __( 'In', 'mai-testimonials' ),
									'NOT IN' => __( 'Not In', 'mai-testimonials' ),
								],
							],
						],
						'conditional_logic' => [
							[
								[
									'field'    => 'mai_testimonials_query_by',
									'operator' => '==',
									'value'    => 'tax_meta',
								],
							],
						],
					],
					[
						'key'               => 'mai_testimonials_taxonomies_relation',
						'label'             => __( 'Taxonomies Relation', 'mai-testimonials' ),
						'name'              => 'taxonomies_relation',
						'type'              => 'select',
						'instructions'      => '',
						'required'          => 0,
						'default_value'     => 'AND',
						'choices'           => [
							'AND' => __( 'AND', 'mai-testimonials' ),
							'OR'  => __( 'OR', 'mai-testimonials' ),
						],__(  'mai-testimonials' ),
						'conditional_logic' => [
							[
								[
									'field'    => 'mai_testimonials_query_by',
									'operator' => '==',
									'value'    => 'tax_meta',
								],
								[
									'field'    => 'mai_testimonials_taxonomies',
									'operator' => '>',
									'value'    => '1',
								],
							],
						],
					],
					[
						'key'               => 'mai_testimonials_orderby',
						'label'             => __( 'Order by', 'mai-testimonials' ),
						'name'              => 'orderby',
						'type'              => 'select',
						'default_value'     => 'date',
						'choices'           => [
							'title'      => __( 'Title', 'mai-testimonials' ),
							'date'       => __( 'Date', 'mai-testimonials' ),
							'modified'   => __( 'Modified', 'mai-testimonials' ),
							'rand'       => __( 'Random', 'mai-testimonials' ),
							'menu_order' => __( 'Menu Order', 'mai-testimonials' ),
						],
						'conditional_logic' => [
							[
								[
									'field'    => 'mai_testimonials_query_by',
									'operator' => '!=',
									'value'    => 'id',
								],
							],
						],
					],
					[
						'key'               => 'mai_testimonials_order',
						'label'             => __( 'Order', 'mai-testimonials' ),
						'name'              => 'order',
						'type'              => 'select',
						'default_value'     => 'ASC',
						'choices'           => [
							'ASC'  => __( 'ASC', 'mai-testimonials' ),
							'DESC' => __( 'DESC', 'mai-testimonials' ),
						],
						'conditional_logic' => [
							[
								[
									'field'    => 'mai_testimonials_query_by',
									'operator' => '!=',
									'value'    => 'id',
								],
							],
						],
					],
					[
						'key'               => 'mai_testimonials_exclude',
						'label'             => __( 'Exclude', 'mai-testimonials' ),
						'name'              => 'exclude',
						'type'              => 'post_object',
						'instructions'      => __( 'Exclude specific testimonials.', 'mai-testimonials' ),
						'multiple'          => 1,
						'return_format'     => 'id',
						'ui'                => 1,
						'post_type'         => [
							'testimonial',
						],
						'conditional_logic' => [
							[
								[
									'field'    => 'mai_testimonials_query_by',
									'operator' => '!=',
									'value'    => 'id',
								],
							],
						],
					],
					[
						'key'     => 'mai_testimonials_slider',
						'label'   => __( 'Slider', 'mai-testimonials' ),
						'name'    => 'slider',
						'type'    => 'true_false',
						'message' => __( 'Enable slider', 'mai-testimonials' ),
					],
					[
						'key'           => 'mai_testimonials_slider_show',
						'label'         => __( 'Slider navigation', 'mai-testimonials' ),
						'name'          => 'slider_show',
						'type'          => 'checkbox',
						'default_value' => [ 'arrows', 'dots' ],
						'choices'       => [
							'dots'   => __( 'Dots', 'mai-testimonials' ),
							'arrows' => __( 'Arrows', 'mai-testimonials' ),
						],
						'conditional_logic' => [
							[
								[
									'field'    => 'mai_testimonials_slider',
									'operator' => '==',
									'value'    => '1',
								],
							],
						],
					],
					[
						'key'               => 'mai_testimonials_slider_show_notice',
						'label'             => '',
						'name'              => 'slider_show_notice',
						'type'              => 'message',
						'message'           => sprintf( '<p style="display:block;padding:4px 8px;color:white;background:red;border-left:4px solid darkred;">%s</p>', __( 'You must show at least one slider navigation element.', 'mai-testimonials' ) ),
						'new_lines'         => '',
						'esc_html'          => 0,
						'conditional_logic' => [
							[
								[
									'field'    => 'mai_testimonials_slider',
									'operator' => '==',
									'value'    => '1',
								],
								[
									'field'    => 'mai_testimonials_slider_show',
									'operator' => '==empty',
								],
							],
						],
					],
					[
						'key'               => 'mai_testimonials_slider_max',
						'label'             => __( 'Max number of slides', 'mai-testimonials' ),
						'instructions'      => __( 'Use 0 to show all', 'mai-testimonials' ),
						'name'              => 'slider_max',
						'type'              => 'number',
						'default_value'     => 0,
						'conditional_logic' => [
							[
								[
									'field'    => 'mai_testimonials_slider',
									'operator' => '==',
									'value'    => '1',
								],
							],
						],
					],
				],
				'location' => [
					[
						[
							'param'    => 'block',
							'operator' => '==',
							'value'    => 'acf/mai-testimonials',
						],
					],
				],
			]
		);
	}

	/**
	 * Loads taxonomy choices.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	function load_taxonomies( $field ) {
		$field['choices'] = wp_list_pluck( get_object_taxonomies( 'testimonial', 'objects' ), 'label', 'name' );

		return $field;
	}

	/**
	 * Get terms from an ajax query.
	 * The taxonomy is passed via JS on select2_query_args filter.
	 *
	 * @since TBD
	 *
	 * @param array $field The ACF field array.
	 *
	 * @return mixed
	 */
	function load_terms( $field ) {
		if ( function_exists( 'mai_acf_load_terms' ) ) {
			$field = mai_acf_load_terms( $field );
		}

		return $field;
	}

	/**
	 * Get terms from an ajax query.
	 * The taxonomy is passed via JS on select2_query_args filter.
	 *
	 * @since TBD
	 *
	 * @param array $field The ACF field array.
	 *
	 * @return mixed
	 */
	function prepare_terms( $field ) {
		if ( function_exists( 'mai_acf_prepare_terms' ) ) {
			$field = mai_acf_prepare_terms( $field );
		}

		return $field;
	}

	/**
	 * Adds custom CSS in the first field.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	function admin_css( $field ) {
		echo '<style>
			.acf-field-mai-testimonials-taxonomies > .acf-input > .acf-repeater > .acf-actions > .acf-button {
				display: block;
				width: 100%;
				text-align: center;
			}
			.acf-field select[name*=mai_testimonials_taxonomy] {
				padding-right: 16px !important;
				white-space: pre;
				text-overflow: ellipsis;
				-webkit-appearance: none;
			}
		</style>';
	}
}
