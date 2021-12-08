/* globals jQuery */
jQuery( function( $ ) {
	// Create the sortable options
	const sortableOptions = {
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
	const nestedSortableOptions = $.extend( {}, sortableOptions, {
		update( event, ui ) {
			const $parent = ui.item.parents( '.ordermanager-item' ).first();

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

	// Handle quick sorting of items by a specified field
	$( '.ordermanager-quicksort' ).click( function() {
		const [ field, order ] = $( this ).data( 'sort' ).split( ':' );

		const $list = $( '.ordermanager-interface > ol' );
		const $items = $list.children();

		$items.sort( function( a, b ) {
			const aValue = $( a ).data( `sort-${ field }` );
			const bValue = $( b ).data( `sort-${ field }` );

			if ( aValue === bValue ) {
				return 0;
			}

			if ( order === 'desc' ) {
				return aValue > bValue ? 1 : -1;
			}

			return aValue < bValue ? 1 : -1;
		} );

		$items.detach().appendTo( $list );

		$list.sortable( 'refresh' );
	} );
} );
