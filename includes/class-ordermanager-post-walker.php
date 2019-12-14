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
final class Post_Walker extends Walker {
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
		'id'     => 'ID',
		'name'   => 'post_title',
		'parent' => 'post_parent',
	);
}
