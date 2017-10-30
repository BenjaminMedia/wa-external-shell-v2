<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       bonnier.dk
 * @since      1.0.0
 *
 * @package    Wa_External_Header_V2
 * @subpackage Wa_External_Header_V2/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wa_External_Header_V2
 * @subpackage Wa_External_Header_V2/includes
 * @author     Bonnier Interactive <interactive@bonnier.dk>
 */
class Wa_External_Header_V2 {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wa_External_Header_V2_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The key used to save and load required options from the WordPress database.
	 * These options are used to configure business-critical details we need from the WhiteAlbum API.
	 *
	 * These are:
	 *   - Domain (eg. costume.no), which determines the brand the blog is co-branding with.
	 *   - Emediate content unit category (or "shortname"), which enables tracking ad impressions correctly.
	 *   - TNS tracking "path"; a string required to differentiate pageviews, for blogs where TNS/Gallup tracking is used.
	 * 	 - WhiteAlbum API Token for API v2 access
	 * 	 - WhiteAlbum API Secret for API v2 access
	 *
	 * The settings are handled in the class White_Album_External_Header_Admin.
	 *
	 * @since    1.1.0
	 * @access   protected
	 * @var      string    $version    The key used for the array that serializes the options in the database.
	 */
	protected $options_group_name;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'wa-external-header-v2';
		$this->version = '1.0.0';
		$this->options_group_name = $this->plugin_name . '_settings';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wa_External_Header_V2_Loader. Orchestrates the hooks of the plugin.
	 * - Wa_External_Header_V2_i18n. Defines internationalization functionality.
	 * - Wa_External_Header_V2_Admin. Defines all hooks for the admin area.
	 * - Wa_External_Header_V2_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wa-external-header-v2-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wa-external-header-v2-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wa-external-header-v2-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wa-external-header-v2-public.php';

		$this->loader = new Wa_External_Header_V2_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wa_External_Header_V2_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wa_External_Header_V2_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the dashboard functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Wa_External_Header_V2_Admin( $this->get_plugin_name(), $this->get_version(), $this->get_options_group_name() );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'init_admin_settings' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = Wa_External_Header_V2_Public::getInstance( $this->get_plugin_name(), $this->get_version(), $this->get_options_group_name() );
		$headHook = (!empty($hook = $plugin_public->getOption('head_hook')) ? $hook : "wp_head");
		$this->loader->add_action( 'wp_enqueue_style', $plugin_public, 'enqueue_fontawesome_styles' );
		$this->loader->add_action( $headHook, $plugin_public, 'wp_head', 40);
		$this->loader->add_action( 'wp_footer', $plugin_public, 'wp_footer' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wa_External_Header_V2_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	public function get_options_group_name() {
		return $this->options_group_name;
	}

}
