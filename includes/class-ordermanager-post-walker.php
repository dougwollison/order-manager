<?php
/**
 * OrderManager Post Walker
 *
 * @package OrderManager
 * @subpackage Walkers
 *
 * @since 1.0.0
 */

namespace OrderManager;

/**
 * The Post Walker
 *
 * Prints out the hierarchy of the provided posts.
 *
 * @internal Used by Backend.
 *
 * @since 1.0.0
 */
final class Post_Walker extends \Walker {
	// =========================
	// ! Properties
	// =========================

	/**
	 * What the class handles.
	 *
	 * @since 1.0.0
	 * @var string
	 *
	 * @see Walker::$tree_type
	 */
	public $tree_type = 'post';

	/**
	 * Database fields to use.
	 *
	 * @since 1.0.0
	 * @var array
	 *
	 * @see Walker::$db_fields
	 */
	public $db_fields = array(
		'parent' => 'post_parent',
		'id'     => 'ID',
	);

	/**
	 * Outputs the beginning of the current level in the tree before elements are output.
	 *
	 * @since 2.1.0
	 *
	 * @see Walker::start_lvl()
	 *
	 * @param string $output Used to append additional content (passed by reference).
	 * @param int    $depth  Optional. Depth of page. Used for padding. Default 0.
	 * @param array  $args   Optional. Arguments for outputting the next level.
	 *                       Default empty array.
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		if ( isset( $args['item_spacing'] ) && 'preserve' === $args['item_spacing'] ) {
			$t = "\t";
			$n = "\n";
		} else {
			$t = '';
			$n = '';
		}
		$indent  = str_repeat( $t, $depth );
		$output .= "{$n}{$indent}<ol class='children'>{$n}";
	}

	/**
	 * Outputs the end of the current level in the tree after elements are output.
	 *
	 * @since 2.1.0
	 *
	 * @see Walker::end_lvl()
	 *
	 * @param string $output Used to append additional content (passed by reference).
	 * @param int    $depth  Optional. Depth of page. Used for padding. Default 0.
	 * @param array  $args   Optional. Arguments for outputting the end of the current level.
	 *                       Default empty array.
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {
		if ( isset( $args['item_spacing'] ) && 'preserve' === $args['item_spacing'] ) {
			$t = "\t";
			$n = "\n";
		} else {
			$t = '';
			$n = '';
		}
		$indent  = str_repeat( $t, $depth );
		$output .= "{$indent}</ol>{$n}";
	}

	/**
	 * Outputs the beginning of the current element in the tree.
	 *
	 * @see Walker::start_el()
	 * @since 2.1.0
	 *
	 * @param string  $output       Used to append additional content. Passed by reference.
	 * @param WP_Post $post         Post data object.
	 * @param int     $depth        Optional. Depth of page. Used for padding. Default 0.
	 * @param array   $args         Optional. Array of arguments. Default empty array.
	 * @param int     $current_page Optional. Page ID. Default 0.
	 */
	public function start_el( &$output, $post, $depth = 0, $args = array(), $current_page = 0 ) {
		if ( isset( $args['item_spacing'] ) && 'preserve' === $args['item_spacing'] ) {
			$t = "\t";
			$n = "\n";
		} else {
			$t = '';
			$n = '';
		}
		if ( $depth ) {
			$indent = str_repeat( $t, $depth );
		} else {
			$indent = '';
		}

		$output .= $indent . sprintf(
			'<li>%s',
			apply_filters( 'the_title', $post->post_title, $post->ID )
		);
	}

	/**
	 * Outputs the end of the current element in the tree.
	 *
	 * @since 2.1.0
	 *
	 * @see Walker::end_el()
	 *
	 * @param string  $output Used to append additional content. Passed by reference.
	 * @param WP_Post $post   Post data object. Not used.
	 * @param int     $depth  Optional. Depth of page. Default 0 (unused).
	 * @param array   $args   Optional. Array of arguments. Default empty array.
	 */
	public function end_el( &$output, $post, $depth = 0, $args = array() ) {
		if ( isset( $args['item_spacing'] ) && 'preserve' === $args['item_spacing'] ) {
			$t = "\t";
			$n = "\n";
		} else {
			$t = '';
			$n = '';
		}
		$output .= "</li>{$n}";
	}
}
