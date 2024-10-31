<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.rapidmail.de/
 * @since             1.0.0
 * @package           Rapidmail_Connector
 *
 * @wordpress-plugin
 * Plugin Name:       rapidmail: Newsletter & E-Mail Marketing for WooCommerce
 * Description:       Create and send newsletters for customers of your WooCommerce online store with ease - and boost sales! Contact data is automatically synchronized.
 * Version:           1.0.1
 * Author:            rapidmail GmbH
 * Author URI:        https://www.rapidmail.de/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       rapidmail-connector
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'RAPIDMAIL_CONNECTOR_VERSION', '1.0.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/Activator.php
 */
function activate_rapidmail_connector() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/Activator.php';
	\Rapidmail\Connector\Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/Deactivator.php
 */
function deactivate_rapidmail_connector() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/Deactivator.php';
	\Rapidmail\Connector\Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_rapidmail_connector' );
register_deactivation_hook( __FILE__, 'deactivate_rapidmail_connector' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/Connector.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_rapidmail_connector() {

	$plugin = new \Rapidmail\Connector\Connector();
	$plugin->run();

}

run_rapidmail_connector();
