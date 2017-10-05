<?php

/**
 * Plugin Name:     Mai - Testimonials
 * Plugin URI:      https://maipro.io
 * Description:     Manage and display testimonials on your website.
 * Version:         0.1.0
 *
 * Author:          MaiPro.io
 * Author URI:      https://maipro.io
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Mai_Testimonials_Setup' ) ) :

/**
 * Main Mai_Testimonials_Setup Class.
 *
 * @since 0.1.0
 */
final class Mai_Testimonials_Setup {

	/**
	 * @var    Mai_Testimonials_Setup The one true Mai_Testimonials_Setup
	 * @since  0.1.0
	 */
	private static $instance;

	/**
	 * Main Mai_Testimonials_Setup Instance.
	 *
	 * Insures that only one instance of Mai_Testimonials_Setup exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since   0.1.0
	 * @static  var array $instance
	 * @uses    Mai_Testimonials_Setup::setup_constants() Setup the constants needed.
	 * @uses    Mai_Testimonials_Setup::setup() Activate, deactivate, etc.
	 * @see     Mai_Testimonials()
	 * @return  object | Mai_Testimonials_Setup The one true Mai_Testimonials_Setup
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			// Setup the init
			self::$instance = new Mai_Testimonials_Setup;
			// Methods
			self::$instance->setup_constants();
			self::$instance->setup();
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
			define( 'MAI_TESTIMONIALS_VERSION', '0.1.0' );
		}

		// Plugin Folder Path.
		if ( ! defined( 'MAI_TESTIMONIALS_PLUGIN_DIR' ) ) {
			define( 'MAI_TESTIMONIALS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Includes Path
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

	public function setup() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	public function init() {
		// Bail if CMB2 is not running anywhere
		if ( ! defined( 'CMB2_LOADED' ) ) {
			add_action( 'admin_init',    array( $this, 'deactivate_plugin' ) );
			add_action( 'admin_notices', array( $this, 'admin_notice' ) );
			return;
		}
		/**
		 * Setup the updater.
		 *
		 * @uses    https://github.com/YahnisElsts/plugin-update-checker/
		 *
		 * @return  void
		 */
		if ( ! class_exists( 'Puc_v4_Factory' ) ) {
			require_once MAI_FAVORITES_PLUGIN_DIR . 'plugin-update-checker/plugin-update-checker.php';
		}
		$updater = Puc_v4_Factory::buildUpdateChecker( 'https://github.com/maiprowp/mai-testimonials/', __FILE__, 'mai-testimonials' );

		// Run
		$this->hooks();
	}

	function admin_notice() {
		printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', __( 'Mai - Testimonials requires the Mai Pro Engine plugin or CMB2 plugin in order to run. As a result, this plugin has been deactivated.', 'mai-testimonials' ) );
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}

	public function hooks() {

		register_activation_hook(   __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

		add_action( 'init',                                   array( $this, 'register_content_types' ) );
		add_action( 'template_redirect',                      array( $this, 'redirect' ) );
		add_action( 'cmb2_admin_init',                        array( $this, 'metabox' ) );
		add_action( 'wp_enqueue_scripts',                     array( $this, 'enqueue_scripts' ) );

		add_filter( 'manage_testimonial_posts_columns',       array( $this, 'cols' ) );
		add_action( 'manage_testimonial_posts_custom_column', array( $this, 'col' ) );

		add_filter( 'shortcode_atts_grid',                    array( $this, 'grid_atts' ), 8, 3 );
		add_filter( 'genesis_attr_flex-entry',                array( $this, 'flex_entry_atts'), 12, 3 );
		add_filter( 'genesis_attr_entry-content',             array( $this, 'entry_content_atts'), 12, 3 );
		add_filter( 'genesis_attr_entry-header',              array( $this, 'entry_header_atts'), 12, 3 );
		add_filter( 'genesis_attr_entry-title',               array( $this, 'entry_title_atts'), 12, 3 );
		add_filter( 'mai_flex_entry_header',                  array( $this, 'add_author_details' ), 10, 2 );
	}

	public function activate() {
		$this->register_content_types();
		flush_rewrite_rules();
	}

	function deactivate_plugin() {
		deactivate_plugins( plugin_basename( __FILE__ ) );
	}

	function cols( $cols ) {
		$date = $cols['date'];
		unset( $cols['date'] );
		$cols['testimonial_excerpt'] = 'Excerpt';
		$cols['date']                = $date;
		return $cols;
	}

	function col( $col ) {
		if ( 'testimonial_excerpt' === $col ) {
			echo esc_html( get_the_excerpt() );
		}
	}

	public function register_content_types() {

		/***********************
		 *  Custom Post Types  *
		 ***********************/

		register_post_type( 'testimonial',
			apply_filters( 'mai_testimonial_args', array(
				'exclude_from_search' => false,
				'has_archive'         => false,
				'hierarchical'        => true,
				'labels'              => array(
					'name'               => _x( 'Testimonials', 'testimonial general name'        , 'mai-testimonials' ),
					'singular_name'      => _x( 'Testimonial' , 'testimonial singular name'       , 'mai-testimonials' ),
					'menu_name'          => _x( 'Testimonials', 'testimonial admin menu'          , 'mai-testimonials' ),
					'name_admin_bar'     => _x( 'Testimonial' , 'testimonial add new on admin bar', 'mai-testimonials' ),
					'add_new'            => _x( 'Add New'  , 'Testimonial'                        , 'mai-testimonials' ),
					'add_new_item'       => __( 'Add New Testimonial'                             , 'mai-testimonials' ),
					'new_item'           => __( 'New Testimonial'                                 , 'mai-testimonials' ),
					'edit_item'          => __( 'Edit Testimonial'                                , 'mai-testimonials' ),
					'view_item'          => __( 'View Testimonial'                                , 'mai-testimonials' ),
					'all_items'          => __( 'All Testimonials'                                , 'mai-testimonials' ),
					'search_items'       => __( 'Search Testimonials'                             , 'mai-testimonials' ),
					'parent_item_colon'  => __( 'Parent Testimonials:'                            , 'mai-testimonials' ),
					'not_found'          => __( 'No Testimonials found.'                          , 'mai-testimonials' ),
					'not_found_in_trash' => __( 'No Testimonials found in Trash.'                 , 'mai-testimonials' )
				),
				'menu_icon'          => 'dashicons-format-quote',
				'public'             => false,
				'publicly_queryable' => false,
				'show_in_menu'       => true,
				'show_in_nav_menus'  => false,
				'show_ui'            => true,
				'rewrite'            => false,
				'supports'           => array( 'title', 'editor', 'thumbnail' ),
			)
		));

	}

	// Redirect if trying to view a single testimonial
	public function redirect() {
		if ( ! is_singular( 'testimonial' ) ) {
			return;
		}
		wp_redirect( home_url() );
		exit();
	}

	/**
	 * Define the metabox and field configurations.
	 */
	function metabox() {

		// Initiate the metabox
		$cmb = new_cmb2_box( array(
			'id'           => 'mai_testimonials',
			'title'        => __( 'Testimonial Details', 'mai-testimonials' ),
			'object_types' => array( 'testimonial' ),
			'context'      => 'normal',
			'priority'     => 'high',
			'show_names'   => true, // Show field names on the left
		) );

		// Regular text field
		$cmb->add_field( array(
			'name' => __( 'Byline', 'mai-testimonials' ),
			'desc' => __( 'Enter a byline for the customer giving this testimonial (for example: "CEO of MaiPro").', 'mai-testimonials' ),
			'id'   => 'byline',
			'type' => 'text',
		) );

		// URL text field
		$cmb->add_field( array(
			'name' => __( 'Website URL', 'mai-testimonials' ),
			'desc' => __( 'Enter a URL that applies to this customer (for example: https://maipro.io).', 'mai-testimonials' ),
			'id'   => 'url',
			'type' => 'text_url',
		) );
	}

	function enqueue_scripts() {
		// Register CSS file for later
		wp_register_style( 'mai-testimonials', MAI_TESTIMONIALS_PLUGIN_URL . 'assets/mai-testimonials.css', array(), MAI_TESTIMONIALS_VERSION );
	}

	/**
	 * Filter the default args for [grid] shortcode when displaying testimonials.
	 *
	 * @param   array  $out    The modified attributes.
	 * @param   array  $pairs  Entire list of supported attributes and their defaults.
	 * @param   array  $atts   User defined attributes in shortcode tag.
	 *
	 * @return  array  The modified attributes.
	 */
	function grid_atts( $out, $pairs, $atts ) {

		// Bail if not a testimonial
		if ( 'testimonial' !== $atts['content'] ) {
			return $out;
		}

		// Enqueue CSS file
		wp_enqueue_style( 'mai-testimonials' );

		if ( ! isset( $atts['align'] ) ) {
			$out['align'] = 'center, middle';
		}

		if ( ! isset( $atts['columns'] ) ) {
			$out['columns'] = 2;
		}

		if ( ! isset( $atts['image_size'] ) ) {
			$out['image_size'] = 'thumbnail';
		}

		if ( ! isset( $atts['link'] ) ) {
			$out['link'] = false;
		}

		if ( ! isset( $atts['show'] ) ) {
			$out['show'] = 'image, title, content';
		}

		if ( ! isset( $atts['title_wrap'] ) ) {
			$out['title_wrap'] = 'span';
		}

		return $out;
	}

	function flex_entry_atts( $attributes, $context, $atts ) {
		// Bail if not a testimonial.
		if ( ! $this->is_testimonial( $atts ) ) {
			return $attributes;
		}
		$attributes['itemprop'] = 'review';
		$attributes['itemtype'] = 'http://schema.org/Review';
		return $attributes;
	}

	function entry_content_atts( $attributes, $context, $atts ) {
		// Bail if not a testimonial.
		if ( ! $this->is_testimonial( $atts ) ) {
			return $attributes;
		}
		$attributes['class']   .= ' text-lg';
		$attributes['itemprop'] = 'reviewBody';
		return $attributes;
	}

	function entry_header_atts( $attributes, $context, $atts ) {
		// Bail if not a testimonial.
		if ( ! $this->is_testimonial( $atts ) ) {
			return $attributes;
		}
		$attributes['itemprop'] = 'author';
		$attributes['itemtype'] = 'http://schema.org/Person';
		return $attributes;
	}

	function entry_title_atts( $attributes, $context, $atts ) {
		// Bail if not a testimonial.
		if ( ! $this->is_testimonial( $atts ) ) {
			return $attributes;
		}
		$attributes['itemprop'] = 'name';
		return $attributes;
	}

	function add_author_details( $entry_header, $atts ) {
		// Bail if not a testimonial.
		if ( ! $this->is_testimonial( $atts ) ) {
			return $entry_header;
		}
		// Byline
		$byline = get_post_meta( get_the_ID(), 'byline', true );
		if ( $byline ) {
			$entry_header .= sprintf( '<span class="title" itemprop="jobTitle">%s</span>', sanitize_text_field( $byline ) );
		}
		// URL
		$url = get_post_meta( get_the_ID(), 'url', true );
		if ( $url ) {
			$url = esc_url( $url );
			$entry_header .= sprintf( '<span class="url"><a href="%s" itemprop="url">%s</a></span>', $url, $url );
		}
		return $entry_header;
	}

	function is_testimonial( $atts ) {
		// Bail if we have no atts.
		if ( ! isset( $atts ) || ! is_array( $atts ) ) {
			return false;
		}
		// Bail if not a testimonial.
		if ( ! isset( $atts['content'] ) || ! in_array( 'testimonial', (array) $atts['content'] ) ) {
			return false;
		}
		// Yay, a testimonial.
		return true;
	}

}
endif; // End if class_exists check.

/**
 * The main function for that returns Mai_Testimonials_Setup
 *
 * The main function responsible for returning the one true Mai_Testimonials_Setup
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $plugin = Mai_Testimonials(); ?>
 *
 * @since 0.1.0
 *
 * @return object|Mai_Testimonials_Setup The one true Mai_Testimonials_Setup Instance.
 */
function Mai_Testimonials() {
	return Mai_Testimonials_Setup::instance();
}

// Get Mai_Testimonials Running.
Mai_Testimonials();
