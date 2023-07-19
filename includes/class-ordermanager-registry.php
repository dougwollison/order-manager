<?php
/**
 * OrderManager Registry API
 *
 * @package OrderManager
 * @subpackage Tools
 *
 * @since 1.0.0
 */

namespace OrderManager;

/**
 * The Registry
 *
 * Stores all the configuration options for the system.
 *
 * @api
 *
 * @since 1.0.0
 */
final class Registry {
	// =========================
	// ! Properties
	// =========================

	/**
	 * The loaded status flag.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	protected static $__loaded = false;

	/**
	 * The options storage array
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected static $options = array();

	/**
	 * The options whitelist/defaults.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected static $options_whitelist = array(
		// - The list of supported post types
		'post_types' => array(
			/*
			'post' => array(
				'order_manager' => true|false, // show UI for sorting posts
				'get_posts_override' => true|false, // override get_posts order
			),
			*/
		),
		// - The list of supported taxonomies
		'taxonomies' => array(
			/*
			'category' => array(
				'order_manager' => true|false, // show UI for sorting terms
				'get_terms_override' => true|false, // override get_terms order
				'post_order_manager' => true|false, // show UI for sorting posts per term
				'get_posts_override' => true|false, // override get_posts order
			),
			*/
		),
	);

	/**
	 * The deprecated options and their alternatives.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected static $options_deprecated = array();

	/**
	 * The current-state option overrides.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private static $option_overrides = array();

	// =========================
	// ! Property Accessing
	// =========================

	/**
	 * Retrieve the whitelist.
	 *
	 * @internal Used by the Installer.
	 *
	 * @since 1.0.0
	 *
	 * @return array The options whitelist.
	 */
	public static function get_defaults() {
		return self::$options_whitelist;
	}

	/**
	 * Check if an option is supported.
	 *
	 * Will also udpate the option value if it was deprecated
	 * but has a sufficient alternative.
	 *
	 * @since 1.0.0
	 *
	 * @param string &$option The option name.
	 *
	 * @return bool Wether or not the option is supported.
	 */
	public static function has( &$option ) {
		if ( isset( self::$options_deprecated[ $option ] ) ) {
			$option = self::$options_deprecated[ $option ];
		}

		return isset( self::$options_whitelist[ $option ] );
	}

	/**
	 * Retrieve a option value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option       The option name.
	 * @param mixed  $default      Optional. The default value to return.
	 * @param bool   $true_value   Optional. Get the true value, bypassing any overrides.
	 * @param bool   $has_override Optional. By-reference boolean to identify if an override exists.
	 *
	 * @return mixed The property value.
	 */
	public static function get( $option, $default = null, $true_value = false, &$has_override = null ) {
		// Trigger notice error if trying to set an unsupported option
		if ( ! self::has( $option ) ) {
			trigger_error( "[OrderManager] The option '{$option}' is not supported.", E_USER_NOTICE );
		}

		// Check if it's set, return it's value.
		if ( isset( self::$options[ $option ] ) ) {
			// Check if it's been overriden, use that unless otherwise requested
			$has_override = isset( self::$option_overrides[ $option ] );
			if ( $has_override && ! $true_value ) {
				$value = self::$option_overrides[ $option ];
			} else {
				$value = self::$options[ $option ];
			}
		} else {
			$value = $default;
		}

		return $value;
	}

	/**
	 * Update a option value.
	 *
	 * Will not work for $languages, that has it's own method.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option The option name.
	 * @param mixed  $value  The value to assign.
	 */
	public static function set( $option, $value = null ) {
		// Trigger notice error if trying to set an unsupported option
		if ( ! self::has( $option ) ) {
			trigger_error( "[OrderManager] The option '{$option}' is not supported.", E_USER_NOTICE );
		}

		self::$options[ $option ] = $value;
	}

	/**
	 * Temporarily override an option value.
	 *
	 * These options will be retrieved when using get(), but will not be saved.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option The option name.
	 * @param mixed  $value  The value to override with.
	 */
	public static function override( $option, $value ) {
		// Trigger notice error if trying to set an unsupported option
		if ( ! self::has( $option ) ) {
			trigger_error( "[OrderManager] The option '{$option}' is not supported.", E_USER_NOTICE );
		}

		self::$options_override[ $option ] = $value;
	}

	// =========================
	// ! Property Testing
	// =========================

	/**
	 * Test if a post type is supported and with what.
	 *
	 * @since 1.0.0
	 *
	 * @param string $post_type The post type to check support for.
	 * @param string $support   Optional. The support to check, defaults to "order_manager".
	 *
	 * @return bool Wether or not the post type is supported.
	 */
	public static function is_post_type_supported( $post_type, $support = 'order_manager' ) {
		$post_types = self::get( 'post_types' );

		if ( ! isset( $post_types[ $post_type ] ) ) {
			return false;
		}

		return $post_types[ $post_type ][ $support ] ?? false;
	}

	/**
	 * Test if a taxonomy is supported and with what.
	 *
	 * @since 1.0.0
	 *
	 * @param string $taxonomy The taxonomy to check support for.
	 * @param string $support  Optional. The support to check, defaults to "order_manager".
	 *
	 * @return bool Wether or not the post type is supported.
	 */
	public static function is_taxonomy_supported( $taxonomy, $support = 'order_manager' ) {
		$taxonomies = self::get( 'taxonomies' );

		if ( ! isset( $taxonomies[ $taxonomy ] ) ) {
			return false;
		}

		return $taxonomies[ $taxonomy ][ $support ] ?? false;
	}

	// =========================
	// ! Manual Registration
	// =========================

	/**
	 * Register a post type for order management.
	 *
	 * @since 1.1.0
	 *
	 * @param string $post_type The post type to register.
	 * @param array  $supports {
	 *        The supports to register it for.
	 *        @type boolean $order_manager Show UI for sorting posts
	 *        @type boolean $get_posts_override Override get_posts order
	 * }
	 */
	public static function register_post_type( $post_type, array $supports ) {
		$supports = wp_parse_args( $supports, array(
			'order_manager' => false,
			'get_terms_override' => false,
		) );

		$post_types = self::get( 'post_types' );

		$post_types[ $post_type ] = $supports;

		self::set( 'post_types', $post_types );
	}

	/**
	 * Register a taxonomy for order management.
	 *
	 * @since 1.1.0
	 *
	 * @param string $post_type The taxonomy to register.
	 * @param array  $supports {
	 *        The supports to register it for.
	 *        @type boolean $order_manager Show UI for sorting terms
	 *        @type boolean $get_terms_override Override get_terms order
	 *        @type boolean $post_order_manager Show UI for sorting posts per term
	 *        @type boolean $get_posts_override Override get_posts order
	 * }
	 */
	public static function register_taxonomy( $taxonomy, array $supports ) {
		$supports = wp_parse_args( $supports, array(
			'order_manager' => false,
			'get_terms_override' => false,
			'post_order_manager' => false,
			'get_posts_override' => false,
		) );

		$taxonomies = self::get( 'taxonomies' );

		$taxonomies[ $taxonomy ] = $supports;

		self::set( 'taxonomies', $taxonomies );
	}

	/**
	 * Unregister a post type from order management.
	 *
	 * @since 1.1.0
	 *
	 * @param string $post_type The post type to register.
	 */
	public static function unregister_post_type( $post_type ) {
		$post_types = self::get( 'post_types' );

		unset( $taxonomies[ $post_type ] );

		self::set( 'post_types', $post_types );
	}

	/**
	 * Unregister a taxonomy from order management.
	 *
	 * @since 1.1.0
	 *
	 * @param string $post_type The taxonomy to register.
	 */
	public static function unregister_taxonomy( $taxonomy ) {
		$taxonomies = self::get( 'taxonomies' );

		unset( $taxonomies[ $taxonomy ] );

		self::set( 'taxonomies', $taxonomies );
	}

	// =========================
	// ! Setup Method
	// =========================

	/**
	 * Load the relevant options.
	 *
	 * @since 1.0.0
	 *
	 * @see Registry::$__loaded to prevent unnecessary reloading.
	 * @see Registry::$options_whitelist to filter the found options.
	 * @see Registry::set() to actually set the value.
	 *
	 * @param bool $reload Should we reload the options?
	 */
	public static function load( $reload = false ) {
		if ( self::$__loaded && ! $reload ) {
			// Already did this
			return;
		}

		// Load the options
		$options = get_option( 'ordermanager_options', array() );
		foreach ( self::$options_whitelist as $option => $default ) {
			$value = $default;
			if ( isset( $options[ $option ] ) ) {
				$value = $options[ $option ];

				// Ensure the value is the same type as the default
				settype( $value, gettype( $default ) );
			}

			self::set( $option, $value );
		}

		// Flag that we've loaded everything
		self::$__loaded = true;
	}

	/**
	 * Save the options and other settings to the database.
	 *
	 * @since 1.0.0
	 *
	 * @param string $what Optional. What to save if not everything.
	 */
	public static function save( $what = true ) {
		update_option( 'ordermanager_options', self::$options );
	}
}
