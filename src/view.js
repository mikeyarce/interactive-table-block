/**
 * WordPress dependencies
 */
import { store, getContext } from '@wordpress/interactivity';

// Define the store for our interactive table namespace.
store( 'interactiveTable', {
	actions: {
		updateSearchTerm: ( event ) => {
			const context = getContext();
			context.searchTerm = event.target.value.toLowerCase();
		},
		initializeFilteredRows: () => {},
	},
	state: {
		get filteredRows() {
			const context = getContext();
			const { rows, searchTerm } = context;
			if ( ! rows ) {
				return [];
			}
			if ( ! searchTerm ) {
				return rows;
			}
			return rows.filter( ( row ) =>
				row.join( ' ' ).toLowerCase().includes( searchTerm )
			);
		},
	},
} );
