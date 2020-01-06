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
		self::add_hook( 'parse_query', 'maybe_set_post_menu_order', 10, 1 );
		self::add_hook( 'parse_query', 'maybe_set_post_term_order', 10, 1 );
		self::add_hook( 'get_terms_defaults', 'maybe_set_term_menu_order', 10, 2 );
		self::add_hook( 'parse_term_query', 'handle_term_order', 10, 1 );
		self::add_hook( 'posts_orderby', 'handle_term_post_order', 10, 2 );
	}

	// =========================
	// ! Query Manipulation
	// =========================

	/**
	 * Set the orderby arg to menu_order if not explicitly set.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Query $query Current instance of WP_Query.
	 */
	public static function maybe_set_post_menu_order( $query ) {
		// Skip if orderby is already specified
		if ( isset( $query->query['orderby'] ) ) {
			return;
		}

		// Get the specified post type
		$post_type = $query->get( 'post_type' ) ?: array();

		// Skip if none or more than one is specified
		if ( ! $post_type || $post_type == 'any' || is_array( $post_type ) && count( $post_type ) > 1 ) {
			return;
		}

		// Convert to string if needed
		if ( is_array( $post_type ) ) {
			$post_type = $post_type[0];
		}

		// Skip if post type does not support the override
		if ( ! Registry::is_post_type_supported( $post_type, 'get_posts_override' ) ) {
			return;
		}

		// Set orderby to menu_order, asc if not explicitly set
		$query->set( 'orderby', 'menu_order' );
		$query->set( 'order', $query->query['order'] ?? 'asc' );
	}

	/**
	 * Set the orderby arg to term_order if applicable and not explicitly set.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Query $query Current instance of WP_Query.
	 */
	public static function maybe_set_post_term_order( $query ) {
		// Skip if orderby is already specified
		if ( isset( $query->query['orderby'] ) ) {
			return;
		}

		// Skip if none or multipe tax queries specified
		if ( ! $query->tax_query || count( $query->tax_query->queries ) != 1 ) {
			return;
		}

		// Skip if taxonomy is not supporter or multiple terms are requested
		$tax_query = $query->tax_query->queries[0];
		if ( ! Registry::is_taxonomy_supported( $tax_query['taxonomy'], 'get_posts_override' ) || count( $tax_query['terms'] ) != 1 ) {
			return;
		}

		// Set orderby to menu_order, asc if not explicitly set
		$query->set( 'orderby', 'term_order' );
		$query->set( 'order', $query->query['order'] ?? 'asc' );
	}

	/**
	 * Set the orderby arg to menu_order if not explicitly set.
	 *
	 * @since 1.0.0
	 *
	 * @param array    $defaults   An array of default get_terms() arguments.
	 * @param string[] $taxonomies An array of taxonomy names.
	 */
	public static function maybe_set_term_menu_order( $defaults, $taxonomies ) {
		// Only use menu_order if for a single, supported taxonomy
		if ( count( $taxonomies ) == 1 && Registry::is_taxonomy_supported( $taxonomies[0], 'get_terms_override' ) ) {
			// Set orderby to menu_order, asc
			$defaults['orderby'] = 'menu_order';
			$defaults['order'] = 'asc';
		}

		return $defaults;
	}

	/**
	 * Rewrite query to handle "menu_order" orderby argument.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Term_Query $query Current instance of WP_Term_Query.
	 */
	public static function handle_term_order( $query ) {
		$vars = &$query->query_vars;

		if ( isset( $vars['orderby'] ) && $vars['orderby'] == 'menu_order' ) {
			$vars['orderby'] = 'meta_value_num';
			$vars['meta_query'] = $vars['meta_query'] ?: array();

			$vars['meta_query']['relation'] = 'OR';
			$vars['meta_query'][] = array(
				'key' => '_ordermanager_menu_order',
				'compare' => 'EXISTS',
			);
			$vars['meta_query'][] = array(
				'key' => '_ordermanager_menu_order',
				'compare' => 'NOT EXISTS',
			);

			if ( ! isset( $vars['order'] ) ) {
				$vars['orderby'] = 'asc';
			}
		}
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

		// Only attempt if term_order is requested and there's only a single tax query
		if ( $query->get( 'orderby' ) == 'term_order' && count( $query->tax_query->queries ) == 1 ) {
			$tax_query = $query->tax_query->queries[0];
			$terms = $tax_query['terms'];
			$taxonomy = $tax_query['taxonomy'];

			// Only proceed if for a single term and a supported taxonomy
			if ( count( $terms ) == 1 && Registry::is_taxonomy_supported( $taxonomy, 'post_order_manager' ) ) {
				// Fetch the term
				$term = get_term_by( $tax_query['field'], $terms[0], $taxonomy );

				// Fetch the post order
				$post_order = get_term_meta( $term->term_id, '_ordermanager_post_order', true ) ?: array();

				// Only proceed if there is a post order
				if ( $post_order ) {
					$post_order = implode( ',', $post_order );
					$order = $query->get( 'order' );

					$orderby = "FIELD({$wpdb->posts}.ID,{$post_order}) {$order}";
				}
			}
		}

		return $orderby;
	}
}
