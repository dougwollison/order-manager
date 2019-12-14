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
 * The Term Walker
 *
 * Prints out the hierarchy of the provided terms.
 *
 * @internal Used by Backend.
 *
 * @since 1.0.0
 */
final class Term_Walker extends Walker {
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
	public $tree_type = 'term';

	/**
	 * Database fields to use.
	 *
	 * @since 1.0.0
	 * @var array
	 *
	 * @see Walker::$db_fields
	 */
	public $db_fields = array(
		'id'     => 'term_id',
		'name'   => 'name',
		'parent' => 'parent',
	);
}
