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

		// Styles and Scripts
		self::add_hook( 'admin_init', 'register_assets' );
		self::add_hook( 'admin_enqueue_scripts', 'enqueue_assets' );

		// Interface Additions
		self::add_hook( 'admin_menu', 'add_order_managers' );

		// Order Change Saving
		self::add_hook( 'admin_post_ordermanager_post_order', 'save_post_order' );
		self::add_hook( 'admin_post_ordermanager_term_order', 'save_term_order' );
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
		load_plugin_textdomain( 'order-manager', false, dirname( ORDERMANAGER_PLUGIN_FILE ) . '/languages' );
	}

	// =========================
	// ! Styles and Scripts
	// =========================

	/**
	 * Register styles and scripts.
	 *
	 * @since 1.0.0
	 */
	public static function register_assets(){
		// Admin styling
		wp_register_style( 'ordermanager-admin', plugins_url( 'css/admin.css', ORDERMANAGER_PLUGIN_FILE ), ORDERMANAGER_PLUGIN_VERSION, 'screen' );

		// Admin javascript
		wp_register_script( 'jquery-mjs-nestedsortable', plugins_url( 'js/jquery.mjs.nestedSortable.js', ORDERMANAGER_PLUGIN_FILE ), array( 'jquery-ui-sortable' ), ORDERMANAGER_PLUGIN_VERSION, false );
		wp_register_script( 'ordermanager-admin-js', plugins_url( 'js/admin.js', ORDERMANAGER_PLUGIN_FILE ), array( 'jquery-mjs-nestedsortable' ), ORDERMANAGER_PLUGIN_VERSION, false );

		// Localize the javascript
		wp_localize_script( 'ordermanager-admin-js', 'ordermanagerL10n', array(
			// to be written
		) );
	}

	/**
	 * Enqueue styles and scripts if applicable.
	 *
	 * @since 1.0.0
	 */
	public static function enqueue_assets(){
		global $plugin_page;
		$screen = get_current_screen();

		// Enqueue if on an post/term order page
		$proceed = $plugin_page && strpos( $plugin_page, '-ordermanager' ) > 0;

		// If an edit term page, enqueue if the taxonomy has post order enabled
		if ( $screen->base == 'term' ) {
			$proceed = Registry::is_taxonomy_supported( $screen->taxonomy, 'post_order_manager' );
		}

		if ( $proceed ) {
			wp_enqueue_style( 'ordermanager-admin' );
			wp_enqueue_script( 'ordermanager-admin-js' );
		}
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

			if ( ! post_type_exists( $post_type ) ) {
				continue;
			}

			$post_type_obj = get_post_type_object( $post_type );

			$parent_slug = 'edit.php';
			if ( $post_type != 'post' ) {
				$parent_slug = "edit.php?post_type={$post_type}";
			}

			add_submenu_page(
				$parent_slug, // parent slug
				// translators: %s = post type, singular name
				sprintf( __( 'Manage %s Order', 'order-manager' ), $post_type_obj->labels->singular_name ), // page title
				// translators: %s = post type, singular name
				sprintf( __( '%s Order', 'order-manager' ), $post_type_obj->labels->singular_name ), // menu title
				$post_type_obj->cap->edit_posts, // capability
				"{$post_type}-ordermanager", // menu slug
				array( __CLASS__, 'do_post_order_manager' ) // callback function
			);
		}

		foreach ( $taxonomies as $taxonomy => $options ) {
			if ( ! taxonomy_exists( $taxonomy ) ) {
				continue;
			}

			$taxonomy_obj = get_taxonomy( $taxonomy );

			if ( $options['order_manager'] ) {
				foreach ( $taxonomy_obj->object_type as $post_type ) {
					$parent_slug = 'edit.php';
					if ( $post_type != 'post' ) {
						$parent_slug = "edit.php?post_type={$post_type}";
					}

					add_submenu_page(
						$parent_slug, // parent slug
						// translators: %s = taxonomy, singular name
						sprintf( __( 'Manage %s Order', 'order-manager' ), $taxonomy_obj->labels->singular_name ), // page title
						// translators: %s = taxonomy, singular name
						sprintf( __( '%s Order', 'order-manager' ), $taxonomy_obj->labels->singular_name ), // menu title
						$taxonomy_obj->cap->manage_terms, // capability
						"{$taxonomy}-ordermanager", // menu slug
						array( __CLASS__, 'do_term_order_manager' ) // callback function
					);
				}
			}

			if ( $options['post_order_manager'] ) {
				self::add_hook( "{$taxonomy}_edit_form_fields", 'do_term_post_order_manager', 10, 1 );
				self::add_hook( "edit_{$taxonomy}", 'save_term_post_order', 10, 1 );
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
			'query_context' => 'order-manager',
			'post_type' => $post_type,
			'post_status' => 'any',
			'posts_per_page' => -1,
			'orderby' => 'menu_order',
			'order' => 'asc',
			'suppress_filters' => false,
		) );
		?>
		<div class="wrap">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<?php settings_errors(); ?>
			<form method="post" action="admin-post.php">
				<input type="hidden" name="action" value="ordermanager_post_order" />
				<input type="hidden" name="post_type" value="<?php echo esc_attr( $post_type ); ?>" />
				<?php wp_nonce_field( "ordermanager_post_order:{$post_type}", '_wpnonce' )?>

				<p class="description">
					<?php
					// translators: %s = post type name
					esc_html( sprintf( __( 'Drag to reorder %s', 'order-manager' ), $post_type_obj->labels->name ) );
					?>
					<?php if ( $post_type_obj->hierarchical ) : ?>
						<?php esc_html_e( 'You can also drag child items to assign them to new parents.', 'order-manager' ); ?>
					<?php endif; ?>
				</p>

				<div class="ordermanager-interface <?php echo $post_type_obj->hierarchical ? 'is-nested' : ''; ?>">
					<ol class="ordermanager-items">
						<?php echo $walker->walk( $posts, $post_type_obj->hierarchical ? 0 : -1 ); ?>
					</ol>
					<p>
						<?php esc_html_e( 'Quick Sort:', 'order-manager' ); ?>
						<button type="button" class="button-secondary ordermanager-quicksort" data-sort="title:desc"><?php esc_html_e( 'Title, A-Z', 'order-manager' ); ?></button>
						<button type="button" class="button-secondary ordermanager-quicksort" data-sort="title:asc"><?php esc_html_e( 'Title, Z-A', 'order-manager' ); ?></button>
					</p>
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
			'query_context' => 'order-manager',
			'taxonomy' => $taxonomy,
			'orderby' => 'menu_order',
			'order' => 'asc',
			'hide_empty' => false,
		) );
		?>
		<div class="wrap">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<?php settings_errors(); ?>
			<form method="post" action="admin-post.php">
				<input type="hidden" name="action" value="ordermanager_term_order" />
				<input type="hidden" name="post_type" value="<?php echo esc_attr( get_current_screen()->post_type ); ?>" />
				<input type="hidden" name="taxonomy" value="<?php echo esc_attr( $taxonomy ); ?>" />
				<?php wp_nonce_field( "ordermanager_term_order:{$taxonomy}", '_wpnonce' )?>

				<p class="description">
					<?php
					// translators: %s = taxonomy name
					esc_html( sprintf( __( 'Drag to reorder %s', 'order-manager' ), $taxonomy_obj->labels->name ) );
					?>
					<?php if ( $taxonomy_obj->hierarchical ) : ?>
						<?php esc_html_e( 'You can also drag child items to assign them to new parents.', 'order-manager' ); ?>
					<?php endif; ?>
				</p>

				<div class="ordermanager-interface <?php echo $taxonomy_obj->hierarchical ? 'is-nested' : ''; ?>">
					<ol class="ordermanager-items">
						<?php echo $walker->walk( $terms, $taxonomy_obj->hierarchical ? 0 : -1 ); ?>
					</ol>
					<p>
						<?php esc_html_e( 'Quick Sort:', 'order-manager' ); ?>
						<button type="button" class="button-secondary ordermanager-quicksort" data-sort="title:desc"><?php esc_html_e( 'Title, A-Z', 'order-manager' ); ?></button>
						<button type="button" class="button-secondary ordermanager-quicksort" data-sort="title:asc"><?php esc_html_e( 'Title, Z-A', 'order-manager' ); ?></button>
					</p>
				</div>

				<button type="submit" class="button-primary">Save Order</button>
			</form>
		</div>
		<?php
	}

	/**
	 * Render a post order manager for a specific term.
	 *
	 * @since 1.0.0
	 */
	public static function do_term_post_order_manager( $term ) {
		$taxonomy_obj = get_taxonomy( $term->taxonomy );

		$walker = new Post_Walker;
		$posts = get_posts( array(
			'query_context' => 'order-manager',
			'post_type' => $taxonomy_obj->object_type,
			'post_status' => 'any',
			'posts_per_page' => -1,
			'tax_query' => array(
				array(
					'taxonomy' => $term->taxonomy,
					'terms' => array( $term->term_id ),
				),
			),
			'orderby' => 'term_order',
			'order' => 'asc',
			'suppress_filters' => false,
		) );
		?>
		<tr class="form-field term-order-wrap">
			<th scope="row"><?php esc_html_e( 'Post Order', 'order-manager' ); ?></th>
			<td>
				<p class="description"><?php esc_html_e( 'Drag to reorder entries.', 'order-manager' ); ?></p>

				<div class="ordermanager-interface">
					<ol class="ordermanager-items">
						<?php echo $walker->walk( $posts, -1 ); ?>
					</ol>
					<p>
						<?php esc_html_e( 'Quick Sort:', 'order-manager' ); ?>
						<button type="button" class="button-secondary ordermanager-quicksort" data-sort="title:desc"><?php esc_html_e( 'Title, A-Z', 'order-manager' ); ?></button>
						<button type="button" class="button-secondary ordermanager-quicksort" data-sort="title:asc"><?php esc_html_e( 'Title, Z-A', 'order-manager' ); ?></button>
					</p>
				</div>
			</td>
		</tr>
		<?php
	}

	// =========================
	// ! Order Change Saving
	// =========================

	/**
	 * Update the menu_order and possibly post_parent of the posts.
	 *
	 * @since 1.0.0
	 */
	public static function save_post_order() {
		if ( ! isset( $_POST['post_type'] ) || empty( $_POST['post_type'] ) ) {
			wp_die( esc_html__( 'Post type not specified.', 'order-manager' ) );
			exit;
		}

		$post_type = $_POST['post_type'];
		if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], "ordermanager_post_order:{$post_type}" ) ) {
			cheatin();
		}

		$post_type_obj = get_post_type_object( $post_type );

		if ( ! $post_type_obj ) {
			wp_die( esc_html__( 'Invalid post type.', 'order-manager' ) );
			exit;
		}

		if ( ! isset( $_POST['order'] ) || empty( $_POST['order'] ) ) {
			wp_die( esc_html__( 'No post order provided.', 'order-manager' ) );
			exit;
		}

		$post_order = array_map( 'absint', $_POST['order'] ?: array() );
		$post_parent = array_map( 'absint', $_POST['parents'] ?? array() );
		foreach ( $post_order as $order => $post_id ) {
			$changes = array(
				'ID'         => $post_id,
				'menu_order' => $order,
			);

			if ( $post_type_obj->hierarchical && isset( $post_parent[ $post_id ] ) ) {
				$changes['post_parent'] = $post_parent[ $post_id ];
			}

			wp_update_post( $changes );
		}

		// Add notice about order being updated
		add_settings_error( "{$post_type}-ordermanager", 'settings_updated', __( 'Order saved.', 'order-manager' ), 'updated' );
		set_transient( 'settings_errors', get_settings_errors(), 30 );

		// Return to settings page
		$redirect = add_query_arg( 'settings-updated', 'true',  wp_get_referer() );
		wp_redirect( $redirect );
		exit;
	}

	/**
	 * Update the menu_order and possibly parent of the terms.
	 *
	 * @since 1.0.0
	 */
	public static function save_term_order() {
		if ( ! isset( $_POST['taxonomy'] ) || empty( $_POST['taxonomy'] ) ) {
			wp_die( esc_html__( 'Taxonomy not specified.', 'order-manager' ) );
			exit;
		}

		$taxonomy = $_POST['taxonomy'];
		if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], "ordermanager_term_order:{$taxonomy}" ) ) {
			cheatin();
		}

		$taxonomy_obj = get_taxonomy( $taxonomy );

		if ( ! $taxonomy_obj ) {
			wp_die( esc_html__( 'Invalid taxonomy.', 'order-manager' ) );
			exit;
		}

		if ( ! isset( $_POST['order'] ) || empty( $_POST['order'] ) ) {
			wp_die( esc_html__( 'No term order provided.', 'order-manager' ) );
			exit;
		}

		$term_order = array_map( 'absint', $_POST['order'] ?: array() );
		$term_parent = array_map( 'absint', $_POST['parents'] ?? array() );
		foreach ( $term_order as $order => $term_id ) {
			if ( $taxonomy_obj->hierarchical && isset( $term_parent[ $term_id ] ) ) {
				wp_update_term( $term_id, $taxonomy, array(
					'parent' => $term_parent[ $term_id ],
				) );
			}

			update_term_meta( $term_id, '_ordermanager_menu_order', $order );
		}

		// Add notice about order being updated
		add_settings_error( "{$taxonomy}-ordermanager", 'settings_updated', __( 'Order saved.', 'order-manager' ), 'updated' );
		set_transient( 'settings_errors', get_settings_errors(), 30 );

		// Return to settings page
		$redirect = add_query_arg( 'settings-updated', 'true',  wp_get_referer() );
		wp_redirect( $redirect );
		exit;
	}

	/**
	 * Save the post_order for the term.
	 *
	 * @since 1.0.0
	 *
	 * @param int $term_id Term ID.
	 */
	public static function save_term_post_order( $term_id ) {
		// Don't try if not being updated via edit-tags.php or not allowed to edit
		if ( ! wp_verify_nonce( $_POST['_wpnonce'] ?? '', "update-tag_{$term_id}" ) || ! current_user_can( 'edit_term', $term_id ) ) {
			return;
		}

		$post_order = array_map( 'absint', $_POST['order'] ?? array() );
		update_term_meta( $term_id, '_ordermanager_post_order', $post_order );
	}
}
