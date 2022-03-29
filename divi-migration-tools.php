<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @since             1.0.0
 * @package           divi-migration-tools
 *
 * @wordpress-plugin
 * Plugin Name:       Divi migration tools
 * Description:       This plugin is being used for Divi editor's content migrations.
 * Version:           1.0.0
 * Author:            Automattic
 * Author URI:        https://automattic.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       divi-migration-tools
 * Domain Path:       /languages
 */

/**
 * Currently plugin version.
 */
define( 'DIVI_MIGRATION_TOOLS_VERSION', '1.0.0' );

define( 'DIVI_MIGRATION_TOOLS_DIR', plugin_dir_path( __FILE__ ) );

// add WP-CLI command support
if ( defined( 'WP_CLI' ) && WP_CLI ) {

	require_once DIVI_MIGRATION_TOOLS_DIR . '/cli/class-divi-shortcode-migration.php';
}
