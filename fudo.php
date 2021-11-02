<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link
 * @since             0.0.1
 * @package           Fudo
 *
 * @wordpress-plugin
 * Plugin Name:       Fudo
 * Plugin URI:
 * Description:       Integra WooCommerce con Fudo
 * Version:           0.0.2
 * Author:            Leo
 * Author URI:
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       fudo
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 0.0.1 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'FUDO_VERSION', '0.0.2' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-fudo-activator.php
 */
function activate_fudo() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-fudo-activator.php';
	Fudo_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-fudo-deactivator.php
 */
function deactivate_fudo() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-fudo-deactivator.php';
	Fudo_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_fudo' );
register_deactivation_hook( __FILE__, 'deactivate_fudo' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-fudo.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.0.1
 */
function run_fudo() {

	$plugin = new Fudo();
	$plugin->run();

}
run_fudo();

function my_plugin_settings_link($links) {
	$settings_link = '<a href="admin.php?page=wc-settings&tab=integration&section=fudo">'.__( 'Settings' ).'</a>';
	array_unshift($links, $settings_link);
	return $links;
}
$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'my_plugin_settings_link' );

function wpdocs_load_textdomain() {
	load_plugin_textdomain( 'wpdocs_textdomain', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'wpdocs_load_textdomain' );