<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      0.0.1
 *
 * @package    Fudo
 * @subpackage Fudo/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Fudo
 * @subpackage Fudo/admin
 * @author     Your Name <email@example.com>
 */
class Fudo_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.0.1
	 * @param      string    $fudo       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $fudo, $version ) {

		$this->plugin_name = $fudo;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Fudo_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Fudo_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/fudo-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Fudo_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Fudo_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/fudo-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function add_admin_page() {
		add_submenu_page(
			'edit.php?post_type=product',
			'Integración con Fudo',
			'Fudo',
			'manage_options',
			$this->plugin_name,
			array( $this, 'load_admin_page_content' )
		);
	}
	// Load the plugin admin page partial.
	public function load_admin_page_content() {
		require_once plugin_dir_path( __FILE__ ). 'partials/fudo-admin-display.php';
	}

}
