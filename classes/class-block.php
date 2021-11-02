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

					wp_enqueue_style( 'mai-testimonials', MAI_TESTIMONIALS_PLUGIN_URL . "assets/css/mai-testimonials{$suffix}.css" );

					if ( is_admin() ) {
						wp_enqueue_style( 'mai-testimonials-editor', MAI_TESTIMONIALS_PLUGIN_URL . "assets/css/mai-testimonials-editor{$suffix}.css" );
						wp_enqueue_script( 'mai-testimonials-block', MAI_TESTIMONIALS_PLUGIN_URL . "assets/js/mai-testimonials{$suffix}.js", [ 'jquery' ], MAI_TESTIMONIALS_VERSION, true );
					}
				},
				'supports'        => [
					'align' => [ 'wide', 'full' ],
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
				'title'  => 'Mai Testimonials Block',
				'fields' => [
					[
						'key'       => 'mai_testimonials_display_tab',
						'label'     => 'Display',
						'type'      => 'tab',
						'placement' => 'top',
					],
					[
						'key'           => 'mai_testimonials_font_size',
						'label'         => 'Text Size',
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
						'label'         => 'Text Align',
						'name'          => 'text_align',
						'type'          => 'button_group',
						'wrapper'       => [
							'class'        => 'mai-acf-button-group',
						],
						'choices'       => [
							'start'        => 'Start',
							'center'       => 'Center',
							'end'          => 'End',
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
							'image'  => 'Image',
							'name'   => 'Name',
							'byline' => 'Byline',
						],
					],
					[
						'key'               => 'mai_testimonials_image_location',
						'label'             => 'Image location',
						'name'              => 'image_location',
						'type'              => 'select',
						'default_value'     => 'inside',
						'choices'           => [
							'before' => 'Above content',
							'after'  => 'Below content',
							'inside' => 'Next to name/byline',
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
						'label'         => 'Name/byline location',
						'name'          => 'author_location',
						'type'          => 'select',
						'default_value' => 'after',
						'choices'       => [
							'before' => 'Above content',
							'after'  => 'Below content',
						],
					],
					[
						'key'     => 'mai_testimonials_boxed',
						'label'   => 'Boxed',
						'name'    => 'boxed',
						'type'    => 'true_false',
						'message' => 'Display boxed styling',
					],
					[
						'key'   => 'mai_testimonials_layout_tab',
						'label' => 'Layout',
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
						],
					],
					[
						'key'     => 'mai_testimonials_columns_responsive',
						'name'    => 'columns_responsive',
						'type'    => 'true_false',
						'message' => 'Custom responsive columns',
					],
					[
						'key'               => 'mai_testimonials_columns_md',
						'label'             => 'Columns (lg tablets)',
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
						'label'             => 'Columns (md tablets)',
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
						'label'             => 'Columns (mobile)',
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
						'key'     => 'mai_testimonials_align_columns',
						'label'   => 'Align Columns',
						'name'    => 'align_columns',
						'type'    => 'button_group',
						'choices' => [
							'start'  => 'Start',
							'center' => 'Center',
							'end'    => 'End',
						],
						'wrapper' => [
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
						'label'             => 'Align Columns (vertical)',
						'name'              => 'align_columns_vertical',
						'type'              => 'button_group',
						'choices'           => [
							'full'   => 'Full',
							'top'    => 'Top',
							'middle' => 'Middle',
							'bottom' => 'Bottom',
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
						'label'         => 'Column Gap',
						'name'          => 'column_gap',
						'type'          => 'button_group',
						'default_value' => 'md',
						'choices'       => [
							''     => 'None',
							'md'   => 'XS',
							'lg'   => 'S',
							'xl'   => 'M',
							'xxl'  => 'L',
							'xxxl' => 'XL',
						],
						'wrapper'       => [
							'class' => 'mai-acf-button-group',
						],
					],
					[
						'key'           => 'mai_testimonials_row_gap',
						'label'         => 'Row Gap',
						'name'          => 'row_gap',
						'type'          => 'button_group',
						'default_value' => 'md',
						'choices'       => [
							''     => 'None',
							'md'   => 'XS',
							'lg'   => 'S',
							'xl'   => 'M',
							'xxl'  => 'L',
							'xxxl' => 'XL',
						],
						'wrapper'       => [
							'class' => 'mai-acf-button-group',
						],
					],
					[
						'key'   => 'mai_testimonials_entries_tab',
						'label' => 'Entries',
						'type'  => 'tab',
					],
					[
						'key'     => 'mai_testimonials_query_by',
						'label'   => 'Get testimonials by',
						'name'    => 'query_by',
						'type'    => 'select',
						'choices' => [
							''         => 'Date',
							'id'       => 'Choice',
							'tax_meta' => 'Taxonomy',
						],
					],
					[
						'key'               => 'mai_testimonials_number',
						'label'             => 'Number',
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
						],
					],
					[
						'key'               => 'mai_testimonials_include',
						'label'             => 'Include',
						'name'              => 'include',
						'type'              => 'post_object',
						'instructions'      => 'Show specific testimonials.',
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
						'label'             => 'Taxonomies',
						'name'              => 'taxonomies',
						'type'              => 'repeater',
						'instructions'      => 'Limit to testimonials in these taxonomies.',
						'collapsed'         => 'mai_testimonials_terms',
						'layout'            => 'block',
						'button_label'      => 'Add Taxonomy Condition',
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
								'label'    => 'Terms',
								'name'     => 'terms',
								'type'     => 'select',
								'choices'  => [],
								'ui'       => 1,
								'ajax'     => 1,
								'multiple' => 1,
							],
							[
								'key'        => 'mai_testimonials_operator',
								'label'      => 'Operator',
								'name'       => 'operator',
								'type'       => 'select',
								'choices'    => [
									'IN'     => 'In',
									'NOT IN' => 'Not In',
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
						'label'             => 'Taxonomies Relation',
						'name'              => 'taxonomies_relation',
						'type'              => 'select',
						'instructions'      => '',
						'required'          => 0,
						'default_value'     => 'AND',
						'choices'           => [
							'AND' => 'AND',
							'OR'  => 'OR',
						],
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
						'label'             => 'Order by',
						'name'              => 'orderby',
						'type'              => 'select',
						'default_value'     => 'date',
						'choices'           => [
							'title'      => 'Title',
							'date'       => 'Date',
							'modified'   => 'Modified',
							'rand'       => 'Random',
							'menu_order' => 'Menu Order',
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
						'label'             => 'Order',
						'name'              => 'order',
						'type'              => 'select',
						'default_value'     => 'ASC',
						'choices'           => [
							'ASC'  => 'ASC',
							'DESC' => 'DESC',
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
						'label'             => 'Exclude',
						'name'              => 'exclude',
						'type'              => 'post_object',
						'instructions'      => 'Exclude specific testimonials.',
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
