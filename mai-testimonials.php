<?php

/**
 * Plugin Name:     Mai Testimonials
 * Plugin URI:      https://bizbudding.com/products/mai-testimonials/
 * Description:     Manage and display testimonials on your website.
 * Version:         2.3.0
 *
 * Author:          BizBudding
 * Author URI:      https://bizbudding.com
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main Mai_Testimonials_Plugin Class.
 *
 * @since 0.1.0
 */
final class Mai_Testimonials_Plugin {

	/**
	 * @var    Mai_Testimonials_Plugin The one true Mai_Testimonials_Plugin
	 * @since  0.1.0
	 */
	private static $instance;

	private $post_type_args;

	/**
	 * Main Mai_Testimonials_Plugin Instance.
	 *
	 * Insures that only one instance of Mai_Testimonials_Plugin exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since   0.1.0
	 * @static  var array $instance
	 * @uses    Mai_Testimonials_Plugin::setup_constants() Setup the constants needed.
	 * @uses    Mai_Testimonials_Plugin::setup() Activate, deactivate, etc.
	 * @see     Mai_Testimonials_Plugin()
	 * @return  object | Mai_Testimonials_Plugin The one true Mai_Testimonials_Plugin
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			// Setup the init
			self::$instance = new Mai_Testimonials_Plugin;
			// Methods
			self::$instance->setup_constants();
			self::$instance->includes();
			self::$instance->run();
		}
		return self::$instance;
	}

	/**
	 * Throw error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since   0.1.0
	 * @access  protected
	 * @return  void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'mai-aec' ), '1.0' );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * @since   0.1.0
	 * @access  protected
	 * @return  void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'mai-aec' ), '1.0' );
	}

	/**
	 * Setup plugin constants.
	 *
	 * @access  private
	 * @since   0.1.0
	 * @return  void
	 */
	private function setup_constants() {

		// Plugin version.
		if ( ! defined( 'MAI_TESTIMONIALS_VERSION' ) ) {
			define( 'MAI_TESTIMONIALS_VERSION', '2.3.0' );
		}

		// Plugin Folder Path.
		if ( ! defined( 'MAI_TESTIMONIALS_PLUGIN_DIR' ) ) {
			define( 'MAI_TESTIMONIALS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Classes Path.
		if ( ! defined( 'MAI_TESTIMONIALS_CLASSES_DIR' ) ) {
			define( 'MAI_TESTIMONIALS_CLASSES_DIR', MAI_TESTIMONIALS_PLUGIN_DIR . 'classes/' );
		}

		// Plugin Includes Path.
		if ( ! defined( 'MAI_TESTIMONIALS_INCLUDES_DIR' ) ) {
			define( 'MAI_TESTIMONIALS_INCLUDES_DIR', MAI_TESTIMONIALS_PLUGIN_DIR . 'includes/' );
		}

		// Plugin Folder URL.
		if ( ! defined( 'MAI_TESTIMONIALS_PLUGIN_URL' ) ) {
			define( 'MAI_TESTIMONIALS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Root File.
		if ( ! defined( 'MAI_TESTIMONIALS_PLUGIN_FILE' ) ) {
			define( 'MAI_TESTIMONIALS_PLUGIN_FILE', __FILE__ );
		}

		// Plugin Base Name
		if ( ! defined( 'MAI_TESTIMONIALS_BASENAME' ) ) {
			define( 'MAI_TESTIMONIALS_BASENAME', dirname( plugin_basename( __FILE__ ) ) );
		}
	}

	/**
	 * Include required files.
	 *
	 * @access  private
	 * @since   0.5.3
	 * @return  void
	 */
	private function includes() {
		// Include vendor libraries.
		require_once __DIR__ . '/vendor/autoload.php';
		// Includes.
		foreach ( glob( MAI_TESTIMONIALS_INCLUDES_DIR . '*.php' ) as $file ) { include $file; }
		// Classes.
		foreach ( glob( MAI_TESTIMONIALS_CLASSES_DIR . '*.php' ) as $file ) { include $file; }
	}

	/**
	 * Run hooks.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function run() {
		add_action( 'admin_init',        [ $this, 'updater' ] );
		add_action( 'init',              [ $this, 'init' ] );
		add_action( 'after_setup_theme', [ $this, 'setup' ] ); // plugins_loaded was too early to check for 'mai-engine'.
	}

	/**
	 * Setup the updater.
	 *
	 * composer require yahnis-elsts/plugin-update-checker
	 *
	 * @since 0.1.0
	 *
	 * @uses https://github.com/YahnisElsts/plugin-update-checker/
	 *
	 * @return void
	 */
	public function updater() {

		// Bail if current user cannot manage plugins.
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		// Bail if plugin updater is not loaded.
		if ( ! class_exists( 'Puc_v4_Factory' ) ) {
			return;
		}

		// Setup the updater.
		$updater = Puc_v4_Factory::buildUpdateChecker( 'https://github.com/maithemewp/mai-testimonials/', __FILE__, 'mai-testimonials' );

		// Maybe set github api token.
		if ( defined( 'MAI_GITHUB_API_TOKEN' ) ) {
			$updater->setAuthentication( MAI_GITHUB_API_TOKEN );
		}

		// Add icons for Dashboard > Updates screen.
		if ( function_exists( 'mai_get_updater_icons' ) && $icons = mai_get_updater_icons() ) {
			$updater->addResultFilter(
				function ( $info ) use ( $icons ) {
					$info->icons = $icons;
					return $info;
				}
			);
		}
	}

	/**
	 * Sets args.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function init() {
		$this->post_type_args = [
			'exclude_from_search' => false,
			'has_archive'         => false,
			'hierarchical'        => false,
			'labels'              => [
				'name'                  => _x( 'Testimonials', 'testimonial general name'        , 'mai-testimonials' ),
				'singular_name'         => _x( 'Testimonial' , 'testimonial singular name'       , 'mai-testimonials' ),
				'menu_name'             => _x( 'Testimonials', 'testimonial admin menu'          , 'mai-testimonials' ),
				'name_admin_bar'        => _x( 'Testimonial' , 'testimonial add new on admin bar', 'mai-testimonials' ),
				'add_new'               => _x( 'Add New'  , 'Testimonial'                        , 'mai-testimonials' ),
				'add_new_item'          => __( 'Add New Testimonial'                             , 'mai-testimonials' ),
				'new_item'              => __( 'New Testimonial'                                 , 'mai-testimonials' ),
				'edit_item'             => __( 'Edit Testimonial'                                , 'mai-testimonials' ),
				'view_item'             => __( 'View Testimonial'                                , 'mai-testimonials' ),
				'all_items'             => __( 'All Testimonials'                                , 'mai-testimonials' ),
				'search_items'          => __( 'Search Testimonials'                             , 'mai-testimonials' ),
				'parent_item_colon'     => __( 'Parent Testimonials:'                            , 'mai-testimonials' ),
				'not_found'             => __( 'No Testimonials found.'                          , 'mai-testimonials' ),
				'not_found_in_trash'    => __( 'No Testimonials found in Trash.'                 , 'mai-testimonials' ),
				'featured_image'        => __( 'Testimonial Image'                               , 'mai-testimonials' ),
				'set_featured_image'    => __( 'Set testimonial image'                           , 'mai-testimonials' ),
				'remove_featured_image' => __( 'Remove testimonial image'                        , 'mai-testimonials' ),
				'use_featured_image'    => __( 'Use testimonial image'                           , 'mai-testimonials' ),
			],
			'menu_icon'          => 'dashicons-format-quote',
			'public'             => false,
			'publicly_queryable' => false,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => false,
			'show_in_rest'       => true,
			'show_ui'            => true,
			'rewrite'            => false,
			'supports'           => [ 'title', 'editor', 'thumbnail', 'page-attributes', 'genesis-cpt-archives-settings' ], // 'page-attributes' only here for sort order, especially with Simple Page Ordering plugin.
		];

		$this->post_type_args = apply_filters( 'mai_testimonial_args', $this->post_type_args );
	}

	/**
	 * Sets up the plugin.
	 * Checks for engine plugins.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function setup() {
		// Bail if no engine is anywhere.
		if ( ! ( class_exists( 'Mai_Theme_Engine' ) || class_exists( 'Mai_Engine' ) ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice' ] );
			return;
		}

		// Run.
		$this->hooks();

		// Bail if Genesis is not running.
		if ( ! function_exists( 'genesis' ) ) {
			return;
		}

		// Mai Theme v1.
		if ( class_exists( 'Mai_Theme_Engine' ) ) {
			$grid  = new Mai_Testimonials_Grid_Shortcode;
		}
		// Mai Theme v2.
		elseif ( class_exists( 'Mai_Engine' ) ) {
			$grid  = new Mai_Testimonials_Grid_Block;
			$block = new Mai_Testimonials_Block;
		}
	}

	/**
	 * Displays admin notice.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function admin_notice() {
		printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', __( 'Mai Testimonials requires Mai Theme and it\'s Engine plugin in order to run.', 'mai-testimonials' ) );
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}

	/**
	 * Runs hooks.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function hooks() {

		register_activation_hook(   __FILE__, [ $this, 'activate' ] );
		register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

		add_action( 'init',                                   [ $this, 'register_content_types' ] );
		add_filter( 'pre_get_posts',                          [ $this, 'remove_from_search' ] );
		add_filter( 'manage_testimonial_posts_columns',       [ $this, 'cols' ] );
		add_action( 'manage_testimonial_posts_custom_column', [ $this, 'col' ] );
		add_filter( 'enter_title_here',                       [ $this, 'enter_title_text' ] );
		add_action( 'add_meta_boxes',                         [ $this, 'add_meta_box' ] );
		add_action( 'save_post_testimonial',                  [ $this, 'save_meta_box' ] );
		add_filter( 'mai_display_taxonomy_post_type_choices', [ $this, 'display_taxonomy_post_types' ] );
	}

	/**
	 * Flushes permalinks upon activation.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function activate() {
		$this->register_content_types();
		flush_rewrite_rules();
	}

	/**
	 * Registers post types and taxonomies.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function register_content_types() {

		/***********************
		 *  Custom Post Types  *
		 ***********************/

		register_post_type( 'testimonial', $this->post_type_args );

		/***********************
		 *  Custom Taxonomies  *
		 ***********************/

		register_taxonomy( 'testimonial_cat', 'testimonial',
			apply_filters( 'mai_testimonial_cat_args',
				[
					'exclude_from_search' => true,
					'has_archive'         => false,
					'hierarchical'        => true,
					'labels' =>
					[
						'name'                       => _x( 'Testimonial Categories', 'taxonomy general name' , 'mai-testimonials' ),
						'singular_name'              => _x( 'Testimonial Category' , 'taxonomy singular name' , 'mai-testimonials' ),
						'search_items'               => __( 'Search Testimonial Categories'                   , 'mai-testimonials' ),
						'popular_items'              => __( 'Popular Testimonial Categories'                  , 'mai-testimonials' ),
						'all_items'                  => __( 'All Categories'                                  , 'mai-testimonials' ),
						'edit_item'                  => __( 'Edit Testimonial Category'                       , 'mai-testimonials' ),
						'update_item'                => __( 'Update Testimonial Category'                     , 'mai-testimonials' ),
						'add_new_item'               => __( 'Add New Testimonial Category'                    , 'mai-testimonials' ),
						'new_item_name'              => __( 'New Testimonial Category Name'                   , 'mai-testimonials' ),
						'separate_items_with_commas' => __( 'Separate Testimonial Categories with commas'     , 'mai-testimonials' ),
						'add_or_remove_items'        => __( 'Add or remove Testimonial Categories'            , 'mai-testimonials' ),
						'choose_from_most_used'      => __( 'Choose from the most used Testimonial Categories', 'mai-testimonials' ),
						'not_found'                  => __( 'No Testimonial Categories found.'                , 'mai-testimonials' ),
						'menu_name'                  => __( 'Testimonial Categories'                          , 'mai-testimonials' ),
						'parent_item'                => null,
						'parent_item_colon'          => null,
					],
					'public'            => false,
					'rewrite'           => false,
					'show_admin_column' => true,
					'show_in_menu'      => true,
					'show_in_nav_menus' => false,
					'show_in_rest'      => true,
					'show_tagcloud'     => false,
					'show_ui'           => true,
				]
			)
		);

	}

	/**
	 * Remove testimonials from search results.
	 * We leave 'exclude_from_search' as false when registering the post type
	 * so it can work with FacetWP.
	 *
	 * @since 0.1.0
	 *
	 * @return  void
	 */
	function remove_from_search( $query ) {
		if ( is_admin() || ! $query->is_search ) {
			return;
		}
		// Bail if post_type is public.
		if ( $this->post_type_args['public'] ) {
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

/**
 * The main function for that returns Mai_Testimonials_Plugin
 *
 * The main function responsible for returning the one true Mai_Testimonials_Plugin
 * Instance to functions everywhere.
 *
 * @since 0.1.0
 *
 * @return object|Mai_Testimonials_Plugin The one true Mai_Testimonials_Plugin Instance.
 */
function mai_testimonials() {
	return Mai_Testimonials_Plugin::instance();
}

// Get Mai_Testimonials_Plugin Running.
mai_testimonials();
