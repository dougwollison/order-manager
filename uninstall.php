<?php
/**
 * OrderManager Uninstall Logic
 *
 * @package OrderManager
 *
 * @since 1.0.0
 */

namespace OrderManager;

/**
 * The Plugin Uninstaller
 *
 * Handles removal of tables and options created by the plugin.
 *
 * @internal Automatically called within this file.
 *
 * @since 1.0.0
 */
final class Uninstaller {
	/**
	 * Run the uninstallation.
	 *
	 * Will check for Multisite and run uninstall() for each blog.
	 *
	 * @since 1.0.0
	 */
	public static function run() {
		// Abort if not running in the WordPress context.
		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			die( 'Must be run within WordPress context.' );
		}

		// Also abort if (somehow) it's some other plugin being uninstalled
		if ( WP_UNINSTALL_PLUGIN != basename( __DIR__ ) . '/order-manager.php' ) {
			die( sprintf( 'Illegal attempt to uninstall [Plugin Name] while uninstalling %s.', esc_html( WP_UNINSTALL_PLUGIN ) ) );
		}

		// Check if this site is a Multisite installation
		if ( is_multisite() ) {
			// Run through each blog and perform the uninstall on each one
			$sites = get_sites( array(
				'fields' => 'ids',
			) );

			foreach ( $sites as $site ) {
				switch_to_blog( $site );

				self::uninstall();

				restore_current_blog();
			}
		} else {
			self::uninstall();
		}
	}

	/**
	 * Perform the actual uninstallation.
	 *
	 * Delete all data added by OrderManager.
	 *
	 * @since 1.0.0
	 */
	public static function uninstall() {
		delete_option( 'ordermanager_options' );
		delete_metadata( 'term', 0, '_ordermanager_menu_order', null, 'delete all' );
		delete_metadata( 'term', 0, '_ordermanager_post_order', null, 'delete all' );
	}
}

Uninstaller::run();
