<?php
/**
 * Render the Interactive Table Wrapper block on the front end.
 *
 * This file handles the server-side rendering of the Interactive Table block.
 *
 * @package InteractiveTable
 */

$namespace         = 'interactiveTable';
$initial_rows_data = array();
$table_html_open   = '';
$table_html_close  = '';
$figure_html_open  = '';

if ( empty( $content ) || ! is_string( $content ) ) {
	return;
}

$doc = new DOMDocument();
libxml_use_internal_errors( true );
$doc->loadHTML( mb_convert_encoding( wp_kses_post( $content ), 'HTML-ENTITIES', 'UTF-8' ), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
libxml_clear_errors();

// Check if DOM parsing was successful.
if ( ! $doc ) {
	// @todo: Add error handling.
	return;
}

$figure_node = $doc->getElementsByTagName( 'figure' )->item( 0 );
$table_node  = $doc->getElementsByTagName( 'table' )->item( 0 );
$tbody_node  = $doc->getElementsByTagName( 'tbody' )->item( 0 );

// If no table or tbody is found, display a message and return.
if ( ! $table_node || ! $tbody_node ) {
	return;
}

// Extract the table opening tag with its attributes.
$table_html_open = '<table';
if ( $table_node->hasAttributes() ) {
	foreach ( iterator_to_array( $table_node->attributes ) as $attr ) {
		// Only include safe attributes.
		if ( in_array( $attr->name, array( 'class', 'id', 'style' ), true ) ) {
			$table_html_open .= ' ' . esc_attr( $attr->name ) . '="' . esc_attr( $attr->value ) . '"';
		}
	}
}
$table_html_open .= '>';

// Check for and include the table header (thead) if it exists.
$thead_node = $table_node->getElementsByTagName( 'thead' )->item( 0 );
if ( $thead_node ) {
	$thead_html = '<thead>';
	$th_rows    = $thead_node->getElementsByTagName( 'tr' );
	foreach ( $th_rows as $th_row ) {
		$thead_html .= '<tr>';
		$th_cells    = $th_row->getElementsByTagName( 'th' );
		foreach ( $th_cells as $th_cell ) {
			// Get and sanitize header cell content.
			$th_content  = wp_strip_all_tags( (string) $th_cell->textContent ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$thead_html .= '<th>' . esc_html( $th_content ) . '</th>';
		}
		$thead_html .= '</tr>';
	}
	$thead_html      .= '</thead>';
	$table_html_open .= $thead_html;
}

$table_html_open .= '<tbody>';
$table_html_close = '</tbody></table>';

// Handle figure if it exists.
if ( $figure_node ) {
	$figure_html_open = '<figure';
	if ( $figure_node->hasAttributes() ) {
		foreach ( iterator_to_array( $figure_node->attributes ) as $attr ) {
			// Only include safe attributes.
			if ( in_array( $attr->name, array( 'class', 'id', 'style' ), true ) ) {
				$figure_html_open .= ' ' . esc_attr( $attr->name ) . '="' . esc_attr( $attr->value ) . '"';
			}
		}
	}
	$figure_html_open .= '>';
	$table_html_close .= '</figure>';
}

$rows = $tbody_node->getElementsByTagName( 'tr' );

// Process table rows.
foreach ( $rows as $row ) {

	$cells = $row->getElementsByTagName( 'td' );

	// Skip rows with no cells.
	if ( 0 === $cells->length ) {
		continue;
	}

	$cells_array = array();
	foreach ( $cells as $cell ) {
		$cell_content  = (string) $cell->textContent; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$cells_array[] = wp_strip_all_tags( $cell_content );
	}

	// Only add non-empty rows.
	if ( ! empty( $cells_array ) ) {
		$initial_rows_data[] = $cells_array;
	}
}

$column_count = ! empty( $initial_rows_data[0] ) ? count( $initial_rows_data[0] ) : 0;
$context      = array(
	'searchTerm' => '',
	'rows'       => $initial_rows_data,
);

$filter_input_html = sprintf(
	'<input
		type="text"
		placeholder="%1$s"
		data-wp-bind--value="context.searchTerm"
		data-wp-on--input="actions.updateSearchTerm"
		aria-label="%1$s"
		aria-controls="interactive-table-content"
		role="searchbox"
		class="interactive-table-filter"
	/>',
	esc_attr__( 'Filter table...', 'interactive-table' )
);

// Generate unique IDs for accessibility.
$table_id             = 'interactive-table-' . uniqid();
$table_description_id = 'interactive-table-desc-' . uniqid();

$block_wrapper_attributes = get_block_wrapper_attributes();
?>

<?php
if ( ! isset( $context['rows'] ) || ! is_array( $context['rows'] ) ) {
	$context['rows'] = array();
}
if ( ! isset( $context['searchTerm'] ) || ! is_string( $context['searchTerm'] ) ) {
	$context['searchTerm'] = '';
}

$json_context = wp_json_encode( $context );
if ( false === $json_context ) {
	$json_context = '{"rows":[],"searchTerm":""}';
}
?>

<div
	<?php echo $block_wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	data-wp-interactive="<?php echo esc_attr( $namespace ); ?>"
	data-wp-context="<?php echo esc_attr( $json_context ); ?>"
	data-wp-init="actions.initializeFilteredRows"
	id="<?php echo esc_attr( $table_id ); ?>"
	class="interactive-table-wrapper"
	role="region"
	aria-labelledby="<?php echo esc_attr( $table_description_id ); ?>"
>
	<div id="<?php echo esc_attr( $table_description_id ); ?>" class="screen-reader-text">
		<?php esc_html_e( 'Interactive table with filtering capability', 'interactive-table' ); ?>
	</div>

	<?php if ( ! empty( $initial_rows_data ) ) : ?>
		<?php
		// Define allowed HTML elements and attributes for filter input.
		$input_allowed_html = array(
			'input' => array(
				'type'                => true,
				'placeholder'         => true,
				'data-wp-bind--value' => true,
				'data-wp-on--input'   => true,
				'aria-label'          => true,
				'aria-controls'       => true,
				'role'                => true,
				'class'               => true,
			),
		);
		echo wp_kses( $filter_input_html, $input_allowed_html );

		$table_html_open = str_replace( '<table', '<table aria-describedby="' . esc_attr( $table_description_id ) . '" role="grid"', $table_html_open );

		$figure_allowed_html = array(
			'figure' => array(
				'class' => true,
				'id'    => true,
				'style' => true,
			),
		);
		echo wp_kses( $figure_html_open, $figure_allowed_html );

		$table_allowed_html = array(
			'table' => array(
				'class'            => true,
				'id'               => true,
				'style'            => true,
				'aria-describedby' => true,
				'role'             => true,
			),
			'thead' => array(),
			'tbody' => array(),
			'tr'    => array(),
			'th'    => array(),
			'td'    => array(),
		);
		echo wp_kses( $table_html_open, $table_allowed_html );
		?>
	<?php else : ?>
		<div class="interactive-table-no-data">
			<?php esc_html_e( 'No table data available.', 'interactive-table' ); ?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $initial_rows_data ) ) : ?>
			<!-- Template for dynamic row rendering -->
			<template data-wp-each--row="state.filteredRows">
				<tr>
					<?php for ( $i = 0; $i < $column_count; $i++ ) : ?>
						<td data-wp-text="context.row.<?php echo (int) $i; ?>"></td>
					<?php endfor; ?>
				</tr>
			</template>

			<?php
			$closing_allowed_html = array(
				'tbody'  => array(),
				'table'  => array(),
				'figure' => array(),
			);
			echo wp_kses( $table_html_close, $closing_allowed_html );
			?>
	<?php endif; ?>
</div>
