<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

class Mai_Testimonials_Content_Types {
	function __construct() {
		$this->hooks();
	}

	/**
	 * Runs hooks.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	function hooks() {
		add_filter( 'pre_get_posts',                          [ $this, 'remove_from_search' ] );
		add_filter( 'manage_testimonial_posts_columns',       [ $this, 'cols' ] );
		add_action( 'manage_testimonial_posts_custom_column', [ $this, 'col' ] );
		add_filter( 'enter_title_here',                       [ $this, 'enter_title_text' ] );
		add_action( 'add_meta_boxes',                         [ $this, 'add_meta_box' ] );
		add_action( 'save_post_testimonial',                  [ $this, 'save_meta_box' ] );
		add_filter( 'mai_display_taxonomy_post_type_choices', [ $this, 'display_taxonomy_post_types' ] );
	}

	/**
	 * Remove testimonials from search results.
	 * We leave 'exclude_from_search' as false when registering the post type
	 * so it can work with SearchWP/FacetWP.
	 *
	 * @since 0.1.0
	 *
	 * @return  void
	 */
	function remove_from_search( $query ) {
		if ( is_admin() || ! $query->is_search ) {
			return;
		}

		global $wp_post_types;

		if ( isset( $wp_post_types['testimonial'] ) ) {
			$wp_post_types['testimonial']->exclude_from_search = true;
		}
	}

	function cols( $cols ) {
		$date = $cols['date'];
		$cats = $cols['taxonomy-testimonial_cat'];
		unset( $cols['date'] );
		unset( $cols['taxonomy-testimonial_cat'] );
		$cols['testimonial_excerpt']      = __( 'Excerpt', 'mai-testimonials' );
		$cols['taxonomy-testimonial_cat'] = $cats;
		$cols['date']                     = $date;
		return $cols;
	}

	function col( $col ) {
		if ( 'testimonial_excerpt' === $col ) {
			echo esc_html( get_the_excerpt() );
		}
	}

	/**
	 * Change the enter title here text.
	 *
	 * @since 0.1.0
	 *
	 * @param  string  $title  The existing title placeholder.
	 *
	 * @return string  The modified title placeholder.
	 */
	function enter_title_text( $title ){
		$screen = get_current_screen();

		if ( 'testimonial' !== $screen->post_type ) {
			return $title;
		}

		return __( 'Enter person\'s name here', 'mai-testimonials' );
	}

	/**
	 * Render Meta Box content.
	 *
	 * @since 2.0.0
	 *
	 * @param string $post_type The post type.
	 *
	 * @return void
	 */
	function add_meta_box( $post_type ) {
		if ( 'testimonial' !== $post_type ) {
			return;
		}

		add_meta_box(
			'maitestimonials_meta_box',
			esc_html__( 'Testimonial Info', 'mai-testimonials' ),
			[ $this, 'render_meta_box' ],
			$post_type,
			'normal',
			'high'
		);
	}


	/**
	 * Render Meta Box content.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post The post object.
	 *
	 * @return void
	 */
	function render_meta_box( $post ) {
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'maitestimonials_meta_box', 'maitestimonials_meta_box_nonce' );

		// Use get_post_meta to retrieve an existing value from the database.
		$byline = get_post_meta( $post->ID, 'byline', true );
		$url    = get_post_meta( $post->ID, 'url', true );

		// Display the form, using the current value.
		printf( '<p style="margin-bottom:4px;"><label for="maitestimonials_byline">%s</label></p>', esc_html__( 'Byline', 'mai-testimonials' ) );
		printf( '<input style="display:block;width:100%%;margin-bottom:1em;" type="text" id="maitestimonials_byline" name="maitestimonials_byline" value="%s" placeholder="%s" />', esc_attr( $byline ), esc_html__( 'CEO of Mai Theme', 'mai-testimonials' ) );
		printf( '<p style="margin-bottom:4px;"><label for="maitestimonials_url">%s</label></p>', esc_html__( 'Website URL', 'mai-testimonials' ) );
		printf( '<input style="display:block;width:100%%;" type="url" id="maitestimonials_url" name="maitestimonials_url" value="%s" placeholder="%s"/>', esc_attr( $url ), __( 'Enter URL here', 'mai-testimonials' ) );
	}

	/**
	 * Save the meta when the post is saved.
	 * We need to verify this came from the our screen and with proper authorization,
	 * because save_post can be triggered at other times.*
	 *
	 * @since 2.0.0
	 *
	 * @param int $post_id The ID of the post being saved.
	 *
	 * @return int
	 */
	function save_meta_box( $post_id ) {
		// Check if our nonce is set.
		if ( ! isset( $_POST['maitestimonials_meta_box_nonce'] ) ) {
			return $post_id;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['maitestimonials_meta_box_nonce'], 'maitestimonials_meta_box' ) ) {
			return $post_id;
		}

		// Bail if an autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// Check if there was a multisite switch before.
		if ( is_multisite() && ms_is_switched() ) {
			return $post_id;
		}

		// Update the meta fields.
		update_post_meta( $post_id, 'url', esc_url( $_POST['maitestimonials_url'] ) );
		update_post_meta( $post_id, 'byline', sanitize_text_field( $_POST['maitestimonials_byline'] ) );
	}

	/**
	 * Adds testimonial post type to Mai Display Taxonomy settings.
	 *
	 * @since 2.3.0
	 *
	 * @param array $post_types The existing post type choices.
	 *
	 * @return array
	 */
	function display_taxonomy_post_types( $post_types ) {
		$post_types[] = 'testimonial';

		return array_unique( $post_types );
	}
}
