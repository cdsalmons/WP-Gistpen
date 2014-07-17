<?php
/**
 * @package   WP_Gistpen_Admin
 * @author    James DiGioia <jamesorodig@gmail.com>
 * @license   GPL-2.0+
 * @link      http://jamesdigioia.com/wp-gistpen/
 * @copyright 2014 James DiGioia
 */

/**
 * Plugin class. This class works with the
 * admin-facing side of the WordPress site.
 *
 * @package WP_Gistpen_Admin
 * @author  James DiGioia <email@example.com>
 */
class WP_Gistpen_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    0.1.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    0.1.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     0.1.0
	 */
	private function __construct() {

		/**
		 * Call $plugin_slug from public plugin class.
		 */
		$plugin = WP_Gistpen::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		 // Add metaboxes
		add_action( 'init', array( $this, 'initialize_meta_boxes' ), 9999 );
		add_filter( 'cmb_meta_boxes', array( $this, 'add_metaboxes' ) );

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     0.1.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;

	}

	/**
	 * Register and enqueue admin-specific styles.
	 * @since     0.1.0
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/wp-gistpen-admin.min.css', __FILE__ ), array(), WP_Gistpen::VERSION );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @return    null    Return early if no settings page is registered.
	 * @since     0.1.0
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.min.js', __FILE__ ), array( 'jquery' ), WP_Gistpen::VERSION );
		}

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    0.1.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 *
		 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
		 *
		 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
		 *
		 * @TODO:
		 *
		 * - Change 'Page Title' to the title of your plugin admin page
		 * - Change 'Menu Text' to the text for menu item for the plugin settings page
		 * - Change 'manage_options' to the capability you see fit
		 *   For reference: http://codex.wordpress.org/Roles_and_Capabilities
		 */
		// $this->plugin_screen_hook_suffix = add_options_page(
		// 	__( 'Page Title', $this->plugin_slug ),
		// 	__( 'Menu Text', $this->plugin_slug ),
		// 	'manage_options',
		// 	$this->plugin_slug,
		// 	array( $this, 'display_plugin_admin_page' )
		// );

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    0.1.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    0.1.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);

	}

	/**
	 * Initialize the metabox class.
	 *
	 * @since    0.1.0
	 */
	public function initialize_meta_boxes() {

		if ( ! class_exists( 'cmb_Meta_Box' ) )
			require_once( plugin_dir_path( __DIR__ ) . 'includes/webdevstudios/custom-metaboxes-and-fields-for-wordpress/init.php' );

	}

	/**
	 * Register the metaboxes
	 *
	 * @since    0.1.0
	 */
	public function add_metaboxes() {

		// Start with an underscore to hide fields from custom fields list
		$prefix = '_wpgp_';

		/**
		 * Sample metabox to demonstrate each field type included
		 */
		$meta_boxes['gistpen_description'] = array(
			'id'         => 'gistpen_description',
			'title'      => __( 'Gistpen Description', 'wp-gistpen' ),
			'pages'      => array( 'gistpens' ), // Post type
			'context'    => 'normal',
			'priority'   => 'high',
			'show_names' => false, // Show field names on the left
			'fields'     => array(
				array(
					'desc'       => __( 'Write a short description of this Gistpen.', 'wp-gistpen' ),
					'id'         => $prefix . 'gistpen_description',
					'type'       => 'textarea',
					// 'show_on_cb' => 'cmb_test_text_show_on_cb', // function should return a bool value
					// 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
					// 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
					'on_front'        => false, // Optionally designate a field to wp-admin only
				),
			)
		);

		return $meta_boxes;
	}

}