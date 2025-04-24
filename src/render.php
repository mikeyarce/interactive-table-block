<?php
/**
 * Render the Interactive Table Wrapper block on the front end.
 *
 * @package CreateBlock
 */

$namespace         = 'interactiveTable';
$initial_rows_data = array();
$table_html_open   = '';
$table_html_close  = '';
$figure_html_open  = '';

if ( ! empty( $content ) ) {
	$doc = new DOMDocument();
	libxml_use_internal_errors( true );
	$doc->loadHTML( mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' ), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();

	$figure_node = $doc->getElementsByTagName( 'figure' )->item( 0 );
	$table_node  = $doc->getElementsByTagName( 'table' )->item( 0 );
	$tbody_node  = $doc->getElementsByTagName( 'tbody' )->item( 0 );

	if ( $table_node && $tbody_node ) {
		$table_html_open = $doc->saveHTML( $table_node );
		$tbody_open_pos  = stripos( $table_html_open, '<tbody' );
		if ( false !== $tbody_open_pos ) {
			$tbody_tag_end_pos = strpos( $table_html_open, '>', $tbody_open_pos );
			if ( false !== $tbody_tag_end_pos ) {
				$table_html_open = substr( $table_html_open, 0, $tbody_tag_end_pos + 1 );
			}
		}
		$table_html_close = '</tbody></table>';
		if ( $figure_node ) {
			$figure_html_open  = $doc->saveHTML( $figure_node );
			$figure_html_open  = substr( $figure_html_open, 0, strpos( $figure_html_open, '>' ) + 1 );
			$table_html_close .= '</figure>';
		}

		$rows = $tbody_node->getElementsByTagName( 'tr' );
		foreach ( $rows as $row ) {
			$cells       = $row->getElementsByTagName( 'td' );
			$cells_array = array();
			foreach ( $cells as $cell ) {
				$cells_array[] = (string) $cell->textContent; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			}
			$initial_rows_data[] = $cells_array;
		}
	}
}

$column_count = ! empty( $initial_rows_data[0] ) ? count( $initial_rows_data[0] ) : 0;
$context      = array(
	'searchTerm' => '',
	'rows'       => $initial_rows_data,
);

$filter_input_html  = sprintf(
	'<input
		type="text"
		placeholder="%1$s"
		data-wp-bind--value="context.searchTerm"
		data-wp-on--input="actions.updateSearchTerm"
		aria-label="%1$s"
		style="margin-bottom: 1em; width: 100%%; max-width: 320px; padding: 0.5em 1em; border: 1px solid #ccc; border-radius: 6px; font-size: 1.1em; transition: border-color 0.2s, box-shadow 0.2s; box-sizing: border-box; outline: none;"
	/>',
	esc_attr__( 'Filter table...', 'interactive-table' )
);
$filter_input_html .= '<style>.wp-block-interactive-table-wrapper input[type="text"]:focus { border-color: #0073aa; box-shadow: 0 0 0 2px #cce6f6; }</style>';

$block_wrapper_attributes = get_block_wrapper_attributes();
?>
<div
	<?php echo $block_wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	data-wp-interactive="<?php echo esc_attr( $namespace ); ?>"
	data-wp-context="<?php echo esc_attr( wp_json_encode( $context ) ); ?>"
	data-wp-init="actions.initializeFilteredRows"
>
	<?php echo $filter_input_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php echo $figure_html_open; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php echo $table_html_open; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

	<template data-wp-each--row="state.filteredRows">
		<tr>
			<?php for ( $i = 0; $i < $column_count; $i++ ) : ?>
				<td data-wp-text="context.row.<?php echo (int) $i; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"></td>
			<?php endfor; ?>
		</tr>
	</template>

	<?php echo $table_html_close; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>
