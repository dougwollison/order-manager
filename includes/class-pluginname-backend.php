<?php
/**
 * PluginName Backend Functionality
 *
 * @package PluginName
 * @subpackage Handlers
 *
 * @since 1.0.0
 */

namespace PluginName;

/**
 * The Backend Functionality
 *
 * Hooks into various backend systems to load
 * custom assets and add the editor interface.
 *
 * @internal Used by the System.
 *
 * @since 1.0.0
 */
final class Backend extends Handler {
	// =========================
	// ! Properties
	// =========================

	/**
	 * Record of added hooks.
	 *
	 * @internal Used by the Handler enable/disable methods.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected static $implemented_hooks = array();

	// =========================
	// ! Hook Registration
	// =========================

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	public static function register_hooks() {
		// Don't do anything if not in the backend
		if ( ! is_backend() ) {
			return;
		}

		// Setup stuff
		self::add_hook( 'plugins_loaded', 'load_textdomain', 10, 0 );

		// Plugin information
		self::add_hook( 'in_plugin_update_message-' . plugin_basename( PLUGINNAME_PLUGIN_FILE ), 'update_notice' );

		// Script/Style Enqueues
		self::add_hook( 'admin_enqueue_scripts', 'enqueue_assets' );
	}

	// =========================
	// ! Setup Stuff
	// =========================

	/**
	 * Load the text domain.
	 *
	 * @since 1.0.0
	 */
	public static function load_textdomain() {
		// Load the textdomain
		load_plugin_textdomain( 'pluginname', false, dirname( PLUGINNAME_PLUGIN_FILE ) . '/languages' );
	}

	// =========================
	// ! Plugin Information
	// =========================

	/**
	 * In case of update, check for notice about the update.
	 *
	 * @since 1.0.0
	 *
	 * @param array $plugin The information about the plugin and the update.
	 */
	public static function update_notice( $plugin ) {
		// Get the version number that the update is for
		$version = $plugin['new_version'];

		// Check if there's a notice about the update
		$transient = "pluginname-update-notice-{$version}";
		$notice = get_transient( $transient );
		if ( $notice === false ) {
			// Hasn't been saved, fetch it from the SVN repo
			$notice = @file_get_contents( "http://plugins.svn.wordpress.org/pluginname/assets/notice-{$version}.txt" ) ?: '';

			// Save the notice
			set_transient( $transient, $notice, YEAR_IN_SECONDS );
		}

		// Print out the notice if there is one
		if ( $notice ) {
			// Since the notice is normally contained within a single div/p combo,
			// we need to close it before printing the update notice
			?>
			</p></div>
			<div class="notice inline notice-warning notice-alt">
				<?php echo apply_filters( 'the_content', $notice ); ?>
			</div>
			<div><p>
			<?php
			// Now that we've re-opened it, there will be
			// an empty div/p combo after our notice
		}
	}

	// =========================
	// ! Script/Style Enqueues
	// =========================

	/**
	 * Enqueue necessary styles and scripts.
	 *
	 * @since 1.0.0
	 */
	public static function enqueue_assets(){
		// Admin styling
		wp_enqueue_style( 'pluginname-admin', plugins_url( 'css/admin.css', PLUGINNAME_PLUGIN_FILE ), '1.0.0', 'screen' );

		// Admin javascript
		wp_enqueue_script( 'pluginname-admin-js', plugins_url( 'js/admin.js', PLUGINNAME_PLUGIN_FILE ), array(), '1.0.0' );

		// Localize the javascript
		wp_localize_script( 'pluginname-admin-js', 'pluginnameL10n', array(
			// to be written
		) );
	}
}
