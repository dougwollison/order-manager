=== Order Manager ===
Contributors: dougwollison
Tags: order manager, post order, term order, sort posts, sort terms
Requires at least: 5.2
Tested up to: 6.6.2
Requires PHP: 7.1.0
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds order controls for posts and terms

== Description ==

Order Manager allows you sort posts and terms belonging to any post type or any taxonomy,
as well as posts tagged with a specific term.

= Options =

**Enable order manager for all posts/terms**

This will add a new page to the WordPress menu under the associated post type.
It provides you with a straightforward drag-and-drop interface to organize your
posts or terms. If the post type or taxonomy supports a hierarchy, it will also
let you easily reassign items to different parents by dragging them just below
the intended parent.

This adds a new `'orderby'` value when using `get_terms()`: `'menu_order'`.

**Enable post order manager for each term**

Taxonomies with this option enabled will now offer a post order interface on
each term's edit screen, allowing you to set a unique order for posts in that
term.

This adds a new `'orderby'` value when using `get_posts()`: `'term_order'`.

*Note: this unique order will only take effect when listing posts belonging to
a specific term that uses this.*

**Override order on `get_posts()/get_terms()`**

This will cause all queries for posts/terms of that type to use the custom
order by default, rather than by date/name. In the case of a query for posts
belonging to a term with post-sorting enabled, it will use that order by default.

== Installation ==

1. Upload the contents of `order-manager.tar.gz` to your `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Find the new 'Order Manager' page under 'Settings' in WordPress.
4. Select which features you want enabled on each post type/taxonomy (any that have a UI will be available).
5. The individual order managers will appear in the menu under it's respective post type
   (e.g. 'Page Order' under 'Pages' or 'Category Order' under 'Posts'). If post order is enabled for terms,
   the interface will appear on the edit screen of each term.

== Changelog ==

**Details on each release can be found [on the GitHub releases page](https://github.com/dougwollison/order-manager/releases) for this project.**

= 1.1.0 =
Quick Sort, REST support, public API, code cleanup

= 1.0.0 =
Initial public release.
