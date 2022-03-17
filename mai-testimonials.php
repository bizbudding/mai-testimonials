<?php

/**
 * Plugin Name:     Mai Testimonials
 * Plugin URI:      https://bizbudding.com/products/mai-testimonials/
 * Description:     Manage and display testimonials on your website.
 * Version:         2.3.1
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
	static function instance() {
		if ( ! isset( self::$instance ) ) {
			// Setup the init
			self::$instance = new Mai_Testimonials_Plugin;
			// Methods
			self::$instance->setup_constants();
			self::$instance->includes();
			self::$instance->hooks();
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
	function __clone() {
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
	function __wakeup() {
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
			define( 'MAI_TESTIMONIALS_VERSION', '2.3.1' );
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
	function hooks() {
		register_activation_hook(   __FILE__, [ $this, 'activate' ] );
		register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

		add_action( 'admin_init',        [ $this, 'updater' ] );
		add_action( 'init',              [ $this, 'register_content_types' ] );
		add_action( 'after_setup_theme', [ $this, 'run' ] ); // Plugins loaded is too early to check for engine version.
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
	function updater() {
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
	 * Sets up the plugin.
	 * Checks for engine plugins.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	// function setup() {
	// 	// Bail if no engine is anywhere.
	// 	if ( ! ( class_exists( 'Mai_Theme_Engine' ) || class_exists( 'Mai_Engine' ) ) ) {
	// 		add_action( 'admin_notices', [ $this, 'admin_notice' ] );
	// 		return;
	// 	}

	// 	// Run.
	// 	$this->hooks();

	// 	// Bail if Genesis is not running.
	// 	if ( ! function_exists( 'genesis' ) ) {
	// 		return;
	// 	}

	// 	// Mai Theme v1.
	// 	if ( class_exists( 'Mai_Theme_Engine' ) ) {
	// 		$grid  = new Mai_Testimonials_Grid_Shortcode;
	// 	}
	// 	// Mai Theme v2.
	// 	elseif ( class_exists( 'Mai_Engine' ) ) {
	// 		$grid  = new Mai_Testimonials_Grid_Block;
	// 		$block = new Mai_Testimonials_Block;
	// 	}
	// }

	// /**
	//  * Displays admin notice.
	//  *
	//  * @since 0.1.0
	//  *
	//  * @return void
	//  */
	// function admin_notice() {
	// 	printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', __( 'Mai Testimonials requires Mai Theme and it\'s Engine plugin in order to run.', 'mai-testimonials' ) );
	// 	if ( isset( $_GET['activate'] ) ) {
	// 		unset( $_GET['activate'] );
	// 	}
	// }

	// /**
	//  * Flushes permalinks upon activation.
	//  *
	//  * @since 0.1.0
	//  *
	//  * @return void
	//  */
	function activate() {
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
	function register_content_types() {

		/***********************
		 *  Custom Post Types  *
		 ***********************/

		register_post_type( 'testimonial',
			apply_filters( 'mai_testimonial_args',
				[
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
				]
			)
		);

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
	 * Runs plugin if Mai Engine is active.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	function run() {
		// Bail if Genesis is not running.
		if ( ! function_exists( 'genesis' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_genesis' ] );
			return;
		}

		// Bail if no engine is anywhere.
		if ( ! ( class_exists( 'Mai_Theme_Engine' ) || class_exists( 'Mai_Engine' ) ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_engine' ] );
			return;
		}

		// Content type hooks and filters.
		$types = new Mai_Testimonials_Content_Types;

		// Bail if v2 engine is too old.
		if ( class_exists( 'Mai_Engine' ) && ! version_compare( mai_get_version(), '2.21', '>' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_version' ] );
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
	 * Displays admin notice if no genesis.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	function admin_notice_genesis() {
		printf( '<div class="notice notice-warning"><p>%s</p></div>', __( 'Mai Theme and Mai Testimonials requires Genesis as the parent theme.', 'mai-testimonials' ) );
	}

	/**
	 * Displays admin notice if no engine.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function admin_notice_engine() {
		printf( '<div class="notice notice-warning"><p>%s</p></div>', __( 'Mai Testimonials requires Mai Theme and it\'s Engine plugin in order to run.', 'mai-testimonials' ) );
	}

	/**
	 * Displays admin notice if engine version is too old.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	function admin_notice_version() {
		printf( '<div class="notice notice-warning"><p>%s%s%s</p></div>', __( 'Mai Testimonials requires Mai Engine plugin version 2.21.0 or later. Please install/upgrade now to use the Mai Testimonials.', 'mai-testimonials' ) );
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
