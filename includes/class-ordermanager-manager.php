<?php
/**
 * OrderManager Manager Funtionality
 *
 * @package OrderManager
 * @subpackage Handlers
 *
 * @since 1.0.0
 */

namespace OrderManager;

/**
 * The Management System
 *
 * Hooks into the backend to add the interfaces for
 * managing the configuration of OrderManager.
 *
 * @internal Used by the System.
 *
 * @since 1.0.0
 */
final class Manager extends Handler {
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

		// Settings & Pages
		self::add_hook( 'admin_menu', 'add_menu_pages' );
		self::add_hook( 'admin_init', 'register_settings' );
	}

	// =========================
	// ! Utilities
	// =========================

	// to be written

	// =========================
	// ! Settings Page Setup
	// =========================

	/**
	 * Register admin pages.
	 *
	 * @since 1.0.0
	 *
	 * @uses Manager::settings_page() for general options page output.
	 * @uses Documenter::register_help_tabs() to register help tabs for all screens.
	 */
	public static function add_menu_pages() {
		// Main Options page
		$options_page_hook = add_options_page(
			__( 'Order Manager Options', 'order-manager' ), // page title
			_x( 'Order Manager', 'menu title', 'order-manager' ), // menu title
			'manage_options', // capability
			'ordermanager-options', // slug
			array( __CLASS__, 'settings_page' ) // callback
		);

		// Setup the help tabs for each page
		Documenter::register_help_tabs( array(
			$options_page_hook => 'options',
		) );
	}

	// =========================
	// ! Settings Registration
	// =========================

	/**
	 * Register the settings/fields for the admin pages.
	 *
	 * @since 1.0.0
	 *
	 * @uses Manager::setup_options_fields() to add fields to the main options fields.
	 */
	public static function register_settings() {
		register_setting( 'ordermanager-options', 'ordermanager_options', array( __CLASS__, 'update_options' ) );
		self::setup_options_fields();
	}

	// =========================
	// ! Settings Saving
	// =========================

	/**
	 * Merge the updated options with the rest before saving.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value The options being updated.
	 *
	 * @return mixed The merged/sanitized options.
	 */
	public static function update_options( $updated_options ) {
		$all_options = get_option( 'ordermanager_options', array() );

		return array_merge( $all_options, $updated_options );
	}

	// =========================
	// ! Settings Fields Setup
	// =========================

	/**
	 * Fields for the options page.
	 *
	 * @since 1.0.0
	 */
	protected static function setup_options_fields() {
		/**
		 * Post Types
		 */

		add_settings_section( 'post_types', __( 'Post Types', 'ordermanager' ), null, 'ordermanager-options' );

		// Build the list
		$post_types_settings = array();
		foreach ( get_post_types( array(
			'show_ui' => true,
		), 'objects' ) as $post_type ) {
			// Automatically skip attachments
			if ( $post_type->name == 'attachment' ) {
				continue;
			}

			$post_types_settings[ "post_types[{$post_type->name}]" ] = array(
				'title' => $post_type->labels->name,
				'type' => 'checklist',
				'data' => array(
					'order_manager' => __( 'Enable order manager', 'ordermanager' ),
					'get_posts_override' => __( 'Override order on get_posts()', 'ordermanager' ),
				),
			);
		}

		Settings::add_fields( $post_types_settings, 'options', 'post_types' );

		/**
		 * Taxonomies
		 */

		add_settings_section( 'taxonomies', __( 'Taxonomies', 'ordermanager' ), null, 'ordermanager-options' );

		// Build the list
		$taxonomies_settings = array();
		foreach ( get_taxonomies( array(
			'show_ui' => true,
		), 'objects' ) as $taxonomy ) {

			$taxonomies_settings[ "taxonomies[{$taxonomy->name}]" ] = array(
				'title' => $taxonomy->labels->name,
				'type' => 'checklist',
				'data' => array(
					'order_manager' => __( 'Enable order manager', 'ordermanager' ),
					'get_terms_override' => __( 'Override order on get_terms()', 'ordermanager' ),
				),
			);
		}

		Settings::add_fields( $taxonomies_settings, 'options', 'taxonomies' );
	}

	// =========================
	// ! Settings Page Output
	// =========================

	/**
	 * Output for generic settings page.
	 *
	 * @since 1.0.0
	 *
	 * @global $plugin_page The slug of the current admin page.
	 */
	public static function settings_page() {
		global $plugin_page;
?>
		<div class="wrap">
			<h2><?php echo get_admin_page_title(); ?></h2>
			<?php settings_errors(); ?>
			<form method="post" action="options.php" id="<?php echo $plugin_page; ?>-form">
				<?php settings_fields( $plugin_page ); ?>
				<?php do_settings_sections( $plugin_page ); ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
