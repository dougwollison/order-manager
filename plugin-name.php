<?php
/*
Plugin Name: [plugin name]
Plugin URI: https://github.com/dougwollison/plugin-name
Description: [plugin description]
Version: 1.0.0
Author: Doug Wollison
Author URI: http://dougw.me
Tags: [plugin tags]
License: GPL2
Text Domain: plugin-name
Domain Path: /languages
*/

// =========================
// ! Constants
// =========================

/**
 * Reference to the plugin file.
 *
 * @since 1.0.0
 *
 * @var string
 */
define( 'PLUGINNAME_PLUGIN_FILE', __FILE__ );

/**
 * Reference to the plugin directory.
 *
 * @since 1.0.0
 *
 * @var string
 */
define( 'PLUGINNAME_PLUGIN_DIR', dirname( PLUGINNAME_PLUGIN_FILE ) );

/**
 * Reference to the plugin slug.
 *
 * @since 1.0.0
 *
 * @var string
 */
define( 'PLUGINNAME_PLUGIN_SLUG', basename( PLUGINNAME_PLUGIN_DIR ) . '/' . basename( PLUGINNAME_PLUGIN_FILE ) );

/**
 * Identifies the current plugin version.
 *
 * @since 1.0.0
 *
 * @var string
 */
define( 'PLUGINNAME_PLUGIN_VERSION', '1.0.0' );

/**
 * Identifies the current database version.
 *
 * @since 1.0.0
 *
 * @var string
 */
define( 'PLUGINNAME_DB_VERSION', '1.0.0' );

// =========================
// ! Includes
// =========================

require PLUGINNAME_PLUGIN_DIR . '/includes/autoloader.php';
require PLUGINNAME_PLUGIN_DIR . '/includes/functions-pluginname.php';
require PLUGINNAME_PLUGIN_DIR . '/includes/functions-gettext.php';

// =========================
// ! Setup
// =========================

PluginName\System::setup();
