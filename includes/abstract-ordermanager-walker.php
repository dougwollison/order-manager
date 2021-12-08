<?php
/**
 * OrderManager Term Walker
 *
 * @package OrderManager
 * @subpackage Walkers
 *
 * @since 1.0.0
 */

namespace OrderManager;

/**
 * The Base Walker
 *
 * A basis for the Post and Term Walkers.
 *
 * @internal Used by Post_Walker and Term_Walker.
 *
 * @since 1.0.0
 */
abstract class Walker extends \Walker {
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
		$output .= "{$n}{$indent}<ol class='ordermanager-items'>{$n}";
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
	 * @param string $output       Used to append additional content. Passed by reference.
	 * @param object $object         The data object.
	 * @param int    $depth        Optional. Depth of page. Used for padding. Default 0.
	 * @param array  $args         Optional. Array of arguments. Default empty array.
	 * @param int    $current_page Optional. Page ID. Default 0.
	 */
	public function start_el( &$output, $object, $depth = 0, $args = array(), $current_page = 0 ) {
		if ( isset( $args['item_spacing'] ) && 'preserve' === $args['item_spacing'] ) {
			$t = "\t";
		} else {
			$t = '';
		}
		if ( $depth ) {
			$indent = str_repeat( $t, $depth );
		} else {
			$indent = '';
		}

		$output .= $indent . sprintf(
			'<li class="ordermanager-item" data-sort-title="%3$s">
				<input type="hidden" name="order[]" value="%1$d" class="ordermanager-item-id" />
				<input type="hidden" name="parents[%1$d]" value="%4$d" class="ordermanager-item-parent" />
				<div class="ordermanager-item-label">
					%2$s
				</div>',
			$object->{$this->db_fields['id']},
			$object->{$this->db_fields['name']},
			esc_attr( $object->{$this->db_fields['name']} ),
			$object->{$this->db_fields['parent']}
		);
	}

	/**
	 * Outputs the end of the current element in the tree.
	 *
	 * @since 2.1.0
	 *
	 * @see Walker::end_el()
	 *
	 * @param string $output Used to append additional content. Passed by reference.
	 * @param object $object Data object. Not used.
	 * @param int    $depth  Optional. Depth of page. Default 0 (unused).
	 * @param array  $args   Optional. Array of arguments. Default empty array.
	 */
	public function end_el( &$output, $object, $depth = 0, $args = array() ) {
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
