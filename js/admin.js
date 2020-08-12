/* globals jQuery */
jQuery( function( $ ) {
	// Create the sortable options
	var sortableOptions = {
		cursor           : 'move',
		handle           : '.ordermanager-item-label',
		helper           : 'clone',
		items            : 'li',
		opacity          : 0.6,
		placeholder      : 'ordermanager-placeholder',
		revert           : true,
		tolerance        : 'pointer',
		toleranceElement : '> .ordermanager-item-label',
	};

	// Create the nestedSortable options
	// a copy of the sortable options + an update event for the parent value
	var nestedSortableOptions = $.extend( {}, sortableOptions, {
		update: function( event, ui ) {
			var $parent = ui.item.parents( '.ordermanager-item' ).first();

			let parent_id = 0;
			if ( $parent.length > 0 ) {
				parent_id = $parent.find( '.ordermanager-item-id' ).val();
			}

			ui.item.find( '> .ordermanager-item-parent' ).val( parent_id );
		},
	} );

	// Apply the sortable options
	// to order managers NOT using the is-nested class
	$( '.ordermanager-interface:not(.is-nested)' )
		.children( '.ordermanager-items' )
		.sortable( sortableOptions );

	// Apply the nestedSotrable options
	// ONLY to order managers using the is-nested class
	$( '.ordermanager-interface.is-nested' )
		.children( '.ordermanager-items' )
		.nestedSortable( nestedSortableOptions );
} );
