/* globals jQuery */
jQuery( function( $ ) {
	$( '.ordermanager-items' ).sortable( {
		axis                 : false,
		items                : 'li',
		containment          : '.ordermanager-items',
		cursor               : 'move',
		forcePlaceholderSize : true,
		revert               : true,
	} );
} );
