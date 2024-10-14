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

		$all_options['post_types'] = array();
		foreach ( $updated_options['post_types'] as $post_type => $options ) {
			$all_options['post_types'][ $post_type ] = array_map( 'boolval', $options );
		}

		$all_options['taxonomies'] = array();
		foreach ( $updated_options['taxonomies'] as $taxonomy => $options ) {
			$all_options['taxonomies'][ $taxonomy ] = array_map( 'boolval', $options );
		}

		return $all_options;
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

		add_settings_section( 'post_types', __( 'Post Types', 'order-manager' ), null, 'ordermanager-options' );

		// Build the list
		foreach ( get_post_types( array(
			'show_ui' => true,
		), 'objects' ) as $post_type ) {
			// Automatically skip attachments
			if ( $post_type->name == 'attachment' ) {
				continue;
			}

			// Add the settings field
			add_settings_field(
				"ordermanager_post_types_{$post_type->name}", // id
				sprintf( '%s <code>%s</code>', $post_type->labels->name, $post_type->name ), // title
				array( __CLASS__, 'print_options_field' ), // callback
				'ordermanager-options', // page
				'post_types', // section
				array(
					'name' => 'post_types',
					'section' => $post_type->name,
					'options' => array(
						'order_manager' => __( 'Enable order manager for all posts', 'order-manager' ),
						'get_posts_override' => __( 'Override order on get_posts()', 'order-manager' ),
					),
				) // arguments
			);
		}

		/**
		 * Taxonomies
		 */

		add_settings_section( 'taxonomies', __( 'Taxonomies', 'order-manager' ), null, 'ordermanager-options' );

		// Build the list
		foreach ( get_taxonomies( array(
			'show_ui' => true,
		), 'objects' ) as $taxonomy ) {
			// Add the settings field
			add_settings_field(
				"ordermanager_taxonomies_{$taxonomy->name}", // id
				sprintf( '%s <code>%s</code>', $taxonomy->labels->name, $taxonomy->name ), // title
				array( __CLASS__, 'print_options_field' ), // callback
				'ordermanager-options', // page
				'taxonomies', // section
				array(
					'title' => $taxonomy->labels->name,
					'name' => 'taxonomies',
					'section' => $taxonomy->name,
					'options' => array(
						'order_manager' => __( 'Enable order manager for all terms', 'order-manager' ),
						'get_terms_override' => __( 'Override order on get_terms()', 'order-manager' ),
						'post_order_manager' => __( 'Enable post order manager for each term', 'order-manager' ),
						'get_posts_override' => __( 'Override order on get_posts() for each term', 'order-manager' ),
					),
				) // arguments
			);
		}
	}

	/**
	 * Print the checkboxes for the options.
	 *
	 * @since 1.0.0
	 */
	public static function print_options_field( $settings ) {
		$name = $settings['name'];
		$section = $settings['section'];

		$list = Registry::get( $name );
		$data = $list[ $section ] ?? array();
		?>
		<fieldset>
			<legend class="screen-reader-text"><?php echo esc_html( $settings['section'] ); ?></legend>
			<?php foreach ( $settings['options'] as $field => $label ) :
				$field_id = "ordermanager_{$name}_{$section}_{$field}";
				$field_name = "ordermanager_options[{$name}][{$section}][{$field}]";
				?>
				<label for="<?php echo esc_attr( $field_id ); ?>">
					<input type="hidden" name="<?php echo esc_attr( $field_name ); ?>" value="0" />
					<input type="checkbox" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $field_name ); ?>" value="1" <?php checked( $data[ $field ] ?? false ); ?> />
					<?php echo esc_html( $label ); ?>
				</label>
				<br>
			<?php endforeach; ?>
		</fieldset>
		<?php
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
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<form method="post" action="options.php" id="<?php echo esc_attr( $plugin_page ); ?>-form">
				<?php settings_fields( $plugin_page ); ?>
				<?php do_settings_sections( $plugin_page ); ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
