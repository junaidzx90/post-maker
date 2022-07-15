<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.fiverr.com/junaidzx90
 * @since             1.0.0
 * @package           Post_Maker
 *
 * @wordpress-plugin
 * Plugin Name:       Post maker
 * Plugin URI:        https://www.fiverr.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.7
 * Author:            Developer Junayed
 * Author URI:        https://www.fiverr.com/junaidzx90
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       post-maker
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
define( 'POST_MAKER_VERSION', '1.0.7' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-post-maker-activator.php
 */
function activate_post_maker() {
	global $wpdb;
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	$postmaker_keywords = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}postmaker_keywords` (
		`ID` INT NOT NULL AUTO_INCREMENT,
		`shortcode` INT NOT NULL,
		`post_id` INT NOT NULL,
		`keyword` VARCHAR(555) NOT NULL,
		`created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`ID`)) ENGINE = InnoDB";
	dbDelta($postmaker_keywords);
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-post-maker-deactivator.php
 */
function deactivate_post_maker() {
	
}

register_activation_hook( __FILE__, 'activate_post_maker' );
register_deactivation_hook( __FILE__, 'deactivate_post_maker' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'admin/class-post-maker.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_post_maker() {

	$plugin = new Post_Maker();
	$plugin->run();

}
run_post_maker();
