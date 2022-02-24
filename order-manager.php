<?php
/*
Plugin Name: Order Manager
Plugin URI: https://github.com/dougwollison/order-manager
Description: Adds order controls for posts and terms
Version: 1.1.0
Author: Doug Wollison
Author URI: http://dougw.me
Tags: order, sort, post order, term order
License: GPL2
Text Domain: order-manager
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
define( 'ORDERMANAGER_PLUGIN_FILE', __FILE__ );

/**
 * Reference to the plugin directory.
 *
 * @since 1.0.0
 *
 * @var string
 */
define( 'ORDERMANAGER_PLUGIN_DIR', dirname( ORDERMANAGER_PLUGIN_FILE ) );

/**
 * Reference to the plugin slug.
 *
 * @since 1.0.0
 *
 * @var string
 */
define( 'ORDERMANAGER_PLUGIN_SLUG', basename( ORDERMANAGER_PLUGIN_DIR ) . '/' . basename( ORDERMANAGER_PLUGIN_FILE ) );

/**
 * Identifies the current plugin version.
 *
 * @since 1.0.0
 *
 * @var string
 */
define( 'ORDERMANAGER_PLUGIN_VERSION', '1.1.0' );

/**
 * Identifies the current database version.
 *
 * @since 1.0.0
 *
 * @var string
 */
define( 'ORDERMANAGER_DB_VERSION', '1.1.0' );

// =========================
// ! Includes
// =========================

require ORDERMANAGER_PLUGIN_DIR . '/includes/autoloader.php';
require ORDERMANAGER_PLUGIN_DIR . '/includes/functions-ordermanager.php';

// =========================
// ! Setup
// =========================

OrderManager\System::setup();
