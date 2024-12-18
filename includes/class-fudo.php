<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      0.0.1
 *
 * @package    Fudo
 * @subpackage Fudo/includes
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
 * @since      0.0.1
 * @package    Fudo
 * @subpackage Fudo/includes
 * @author     Your Name <email@example.com>
 */
class Fudo {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      Fudo_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $fudo    The string used to uniquely identify this plugin.
	 */
	protected $fudo;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    0.0.1
	 */
	public function __construct() {
		if ( defined( 'FUDO_VERSION' ) ) {
			$this->version = FUDO_VERSION;
		} else {
			$this->version = '0.0.1';
		}
		$this->plugin_name = 'fudo';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		add_action( 'plugins_loaded', array( $this, 'define_integration' ) );
		add_action( 'init', [$this, 'schedule_fudo_product_importation'] );
		add_action( 'fudo_products_importation', [$this, 'import_fudo_products'] );
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Fudo_Loader. Orchestrates the hooks of the plugin.
	 * - Fudo_i18n. Defines internationalization functionality.
	 * - Fudo_Admin. Defines all hooks for the admin area.
	 * - Fudo_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fudo-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fudo-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-fudo-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-fudo-public.php';

		/**
		 * The class responsible for defining api client functionality of the plugin
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fudo-client.php';

		/**
		 * The class responsible for defining products importer functionality of the plugin
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-fudo-importer.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . '../woocommerce/packages/action-scheduler/action-scheduler.php';

		$this->loader = new Fudo_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Fudo_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Fudo_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_admin_hooks() {

		/*$plugin_admin = new Fudo_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Hooks into admin_menu hook to add custom page
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_page' );*/

	}

	/**
	 * Register all of the hooks related to the integration functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	public function define_integration() {

		if ( class_exists( 'WC_Integration' ) ){
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fudo-integration.php';
			$plugin_integration = new Fudo_Integration();
			add_filter( 'woocommerce_integrations', array( $plugin_integration, 'add_integration' ), 10, 1 );
		}

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Fudo_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    0.0.1
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     0.0.1
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     0.0.1
	 * @return    Fudo_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     0.0.1
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * A callback to run when the 'fudo_products_importation' scheduled action is run.
	 */
	public function import_fudo_products() {
		$fudoImporter = new Fudo_Importer;
        error_log(json_encode($fudoImporter->import()));
        error_log(json_encode($fudoImporter->remove_old(24)));
	}

	/**
	 * Schedule an action with the hook 'fudo_products_importation' to run at midnight each day
	 * so that our callback is run then.
	 */
	public function schedule_fudo_product_importation() {
		$plugin_integration = new Fudo_Integration();
		$importation_minutes_interval = intval($plugin_integration->get_option( 'fudo_import_interval_minutes' ));
		if ( false === as_has_scheduled_action( 'fudo_products_importation' ) ) {
			as_schedule_recurring_action( strtotime( 'now' ), $importation_minutes_interval*60, 'fudo_products_importation' );
		}
	}

}
