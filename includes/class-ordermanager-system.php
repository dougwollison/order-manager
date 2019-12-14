<?php
/**
 * OrderManager System
 *
 * @package OrderManager
 * @subpackage Handlers
 *
 * @since 1.0.0
 */

namespace OrderManager;

/**
 * The Main System
 *
 * Sets up the database table aliases, the Registry,
 * and all the Handler classes.
 *
 * @api
 *
 * @since 1.0.0
 */
final class System extends Handler {
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
	// ! Master Setup Method
	// =========================

	/**
	 * Register hooks and load options.
	 *
	 * @since 1.0.0
	 *
	 * @uses Registry::load() to load the options.
	 * @uses Loader::register_hooks() to setup plugin management.
	 * @uses System::register_hooks() to setup global functionality.
	 * @uses Backend::register_hooks() to setup backend functionality.
	 * @uses AJAX::register_hooks() to setup AJAX functionality.
	 * @uses Manager::register_hooks() to setup admin screens.
	 * @uses Documenter::register_hooks() to setup admin documentation.
	 */
	public static function setup() {
		// Setup the registry
		Registry::load();

		// Register the Installer stuff
		Installer::register_hooks();

		// Register global hooks
		self::register_hooks();

		// Register the hooks of the subsystems
		Backend::register_hooks();
		AJAX::register_hooks();
		Manager::register_hooks();
		Documenter::register_hooks();
	}

	// =========================
	// ! Setup Utilities
	// =========================

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	public static function register_hooks() {
		// Query Manipulation
		self::add_hook( 'get_terms_orderby', 'handle_term_order', 10, 3 );
		self::add_hook( 'posts_orderby', 'handle_term_post_order', 10, 2 );

		// Insert Manipulation
		self::add_hook( 'create_term', 'default_term_menu_order', 10, 3 );
	}

	// =========================
	// ! Query Manipulation
	// =========================

	/**
	 * Filters the ORDERBY clause of the terms query.
	 *
	 * Adds handling of "menu_order" option.
	 *
	 * @since 1.0.0
	 *
	 * @param string   $orderby    `ORDERBY` clause of the terms query.
	 * @param array    $args       An array of term query arguments.
	 * @param string[] $taxonomies An array of taxonomy names.
	 *
	 * @return string The filtered `ORDERBY` clause.
	 */
	public static function handle_term_order( $orderby, $args, $taxonomy ) {
		return $orderby;
	}

	/**
	 * Filters the ORDER BY clause of the query.
	 *
	 * Adds handling of "term_order" option.
	 *
	 * @since 1.0.0
	 *
	 * @param string   $orderby The ORDER BY clause of the query.
	 * @param WP_Query $query   The WP_Query instance (passed by reference).
	 *
	 * @return string The filtered `ORDERBY` clause.
	 */
	public static function handle_term_post_order( $orderby, $query ) {
		global $wpdb;

		if ( $query->get( 'orderby' ) == 'term_order' ) {
			$tax_query = $query->tax_query;
			// If for a single term in a single taxonomy, use post order
			if ( count( $tax_query->queries ) == 1 && count( $tax_query->queries[0]['terms'] ) == 1
			&& Registry::is_taxonomy_supported( $tax_query->queries[0]['taxonomy'], 'post_order_manager' )
			&& ( $post_order = get_term_meta( $tax_query->queries[0]['terms'][0], 'post_order', true ) ) ) {
				$post_order = array_map( 'absint', $post_order ); // ensure a list of numbers

				$post_order = implode( ',', $post_order );
				$order = $query->get( 'order' );

				$orderby = "FIELD({$wpdb->posts}.ID,{$post_order}) {$order}";
			}
		}

		return $orderby;
	}

	// =========================
	// ! Insert Manupulation
	// =========================

	/**
	 * Add default value for menu_order term meta value.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 */
	function term_order_default_value( $term_id, $tt_id, $taxonomy ) {
		// If term is of supported taxonomy, add default menu_order value
		if ( Registry::is_taxonomy_supported( $taxonomy, 'order_manager' ) ) {
			add_term_meta( $term_id, 'menu_order', $term_id, true );
		}
	}
}
