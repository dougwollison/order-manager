<?php
/**
 * OrderManager Internal Functions
 *
 * @package OrderManager
 * @subpackage Utilities
 *
 * @internal
 *
 * @since 1.0.0
 */

namespace OrderManager;

// =========================
// ! Conditional Tags
// =========================

/**
 * Check if we're in the backend of the site (excluding frontend AJAX requests)
 *
 * @internal
 *
 * @since 1.0.0
 */
function is_backend() {
	if ( defined( 'WP_INSTALLING' ) && WP_INSTALLING ) {
		// "Install" process, count as backend
		return true;
	}

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		// AJAX request, check if the referrer is from wp-admin
		return isset( $_SERVER['HTTP_REFERER'] ) && strpos( $_SERVER['HTTP_REFERER'], admin_url() ) === 0;
	}

	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		// REST request, check if the referrer is from wp-admin
		return isset( $_SERVER['HTTP_REFERER'] ) && strpos( $_SERVER['HTTP_REFERER'], admin_url() ) === 0;
	}

	// Check if in the admin or otherwise the login/register page
	return is_admin() || in_array( basename( $_SERVER['SCRIPT_NAME'] ), array( 'wp-login.php', 'wp-register.php' ) );
}

// =========================
// ! Misc. Utilities
// =========================

/**
 * Triggers the standard "Cheatin’ uh?" wp_die message.
 *
 * @internal
 *
 * @since 1.0.0
 */
function cheatin() {
	wp_die( 'Cheatin&#8217; uh?', 403 );
}
