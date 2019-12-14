<?php
/**
 * OrderManager Backend Functionality
 *
 * @package OrderManager
 * @subpackage Handlers
 *
 * @since 1.0.0
 */

namespace OrderManager;

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
		self::add_hook( 'in_plugin_update_message-' . plugin_basename( ORDERMANAGER_PLUGIN_FILE ), 'update_notice' );

		// Script/Style Enqueues
		self::add_hook( 'admin_enqueue_scripts', 'enqueue_assets' );

		// Interface Additions
		self::add_hook( 'admin_menu', 'add_order_managers' );
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
		load_plugin_textdomain( 'ordermanager', false, dirname( ORDERMANAGER_PLUGIN_FILE ) . '/languages' );
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
		$transient = "ordermanager-update-notice-{$version}";
		$notice = get_transient( $transient );
		if ( $notice === false ) {
			// Hasn't been saved, fetch it from the SVN repo
			$notice = @file_get_contents( "http://plugins.svn.wordpress.org/ordermanager/assets/notice-{$version}.txt" ) ?: '';

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
		wp_enqueue_style( 'ordermanager-admin', plugins_url( 'css/admin.css', ORDERMANAGER_PLUGIN_FILE ), '1.0.0', 'screen' );

		// Admin javascript
		wp_enqueue_script( 'ordermanager-admin-js', plugins_url( 'js/admin.js', ORDERMANAGER_PLUGIN_FILE ), array(), '1.0.0' );

		// Localize the javascript
		wp_localize_script( 'ordermanager-admin-js', 'ordermanagerL10n', array(
			// to be written
		) );
	}

	// =========================
	// ! Interface Additions
	// =========================

	/**
	 * Register order manager pages for enabled post types/taxonomies.
	 *
	 * @since 1.0.0
	 */
	public static function add_order_managers() {
		$post_types = Registry::get( 'post_types' );
		$taxonomies = Registry::get( 'taxonomies' );

		foreach ( $post_types as $post_type => $options ) {
			if ( ! $options['order_manager'] ) {
				continue;
			}

			$post_type_obj = get_post_type_object( $post_type );

			$parent_slug = 'edit.php';
			if ( $post_type != 'post' ) {
				$parent_slug = "edit.php?post_type={$post_type}";
			}

			add_submenu_page(
				$parent_slug, // parent slug
				sprintf( __( 'Manage %s Order', 'ordermanager' ), $post_type_obj->labels->singular_name ), // page title
				sprintf( __( '%s Order', 'ordermanager' ), $post_type_obj->labels->singular_name ), // menu title
				$post_type_obj->cap->edit_posts, // capability
				"{$post_type}-ordermanager", // menu slug
				array( __CLASS__, 'do_post_order_manager' ) // callback function
			);
		}

		foreach ( $taxonomies as $taxonomy => $options ) {
			if ( ! $options['order_manager'] ) {
				continue;
			}

			$taxonomy_obj = get_taxonomy( $taxonomy );

			foreach ( $taxonomy_obj->object_type as $post_type ) {
				$parent_slug = 'edit.php';
				if ( $post_type != 'post' ) {
					$parent_slug = "edit.php?post_type={$post_type}";
				}

				add_submenu_page(
					$parent_slug, // parent slug
					sprintf( __( 'Manage %s Order', 'ordermanager' ), $taxonomy_obj->labels->singular_name ), // page title
					sprintf( __( '%s Order', 'ordermanager' ), $taxonomy_obj->labels->singular_name ), // menu title
					$taxonomy_obj->cap->manage_terms, // capability
					"{$taxonomy}-ordermanager", // menu slug
					array( __CLASS__, 'do_term_order_manager' ) // callback function
				);
			}
		}
	}

	/**
	 * Render a post order manager page.
	 *
	 * @since 1.0.0
	 */
	public static function do_post_order_manager() {
		global $plugin_page;
		$post_type = str_replace( '-ordermanager', '', $plugin_page );
		$post_type_obj = get_post_type_object( $post_type );

		$walker = new Post_Walker;
		$posts = get_posts( array(
			'query_context' => 'ordermanager',
			'post_type' => $post_type,
			'post_status' => 'any',
			'posts_per_page' => -1,
			'orderby' => 'menu_order',
			'order' => 'asc',
			'suppress_filters' => false,
		) );
		?>
		<div class="wrap">
			<h1><?php echo get_admin_page_title()?></h1>

			<br>

			<form method="post" action="admin-post.php">
				<input type="hidden" name="action" value="ordermanager_post_order" />
				<input type="hidden" name="post_type" value="<?php echo $post_type ?>" />
				<?php wp_nonce_field( "ordermanager:{$post_type}", '_wpnonce' )?>

				<p class="description">
					Drag to reorder <?php echo $post_type_obj->labels->name; ?>.
					<?php if ( $post_type_obj->hierarchical ) : ?>
						You can also drag child items to assign them to new parents.
					<?php endif; ?>
				</p>

				<div class="ordermanager-interface <?= $taxonomy->hierarchical ? 'is-nested' : '' ?>">
					<ol class="ordermanager-items">
						<?php echo $walker->walk( $posts, 0 ); ?>
					</ol>
				</div>

				<button type="submit" class="button-primary">Save Order</button>
			</form>
		</div>
		<?php
	}

	/**
	 * Render a term order manager page.
	 *
	 * @since 1.0.0
	 */
	public static function do_term_order_manager() {
		global $plugin_page;
		$taxonomy = str_replace( '-ordermanager', '', $plugin_page );
		$taxonomy_obj = get_taxonomy( $taxonomy );

		$walker = new Term_Walker;
		$terms = get_terms( array(
			'query_context' => 'ordermanager',
			'taxonomy' => $taxonomy,
			'orderby' => 'term_order',
			'order' => 'asc',
			'hide_empty' => false,
		) );
		?>
		<div class="wrap">
			<h1><?php echo get_admin_page_title()?></h1>

			<br>

			<form method="post" action="admin-post.php">
				<input type="hidden" name="action" value="ordermanager_term_order" />
				<input type="hidden" name="taxonomy" value="<?php echo $taxonomy ?>" />
				<?php wp_nonce_field( "ordermanager:{$taxonomy}", '_wpnonce' )?>

				<p class="description">
					Drag to reorder <?php echo $taxonomy_obj->labels->name; ?>.
					<?php if ( $taxonomy_obj->hierarchical ) : ?>
						You can also drag child items to assign them to new parents.
					<?php endif; ?>
				</p>

				<div class="ordermanager-interface <?= $taxonomy->hierarchical ? 'is-nested' : '' ?>">
					<ol class="ordermanager-items">
						<?php echo $walker->walk( $terms, 0 ); ?>
					</ol>
				</div>

				<button type="submit" class="button-primary">Save Order</button>
			</form>
		</div>
		<?php
	}
}
