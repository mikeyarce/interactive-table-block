/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the user will see while editing.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 */
export default function Edit() {
	const blockProps = useBlockProps();

	return (
		<div { ...blockProps }>
			<p
				style={ {
					fontStyle: 'italic',
					color: '#555',
					marginBottom: '1em',
				} }
			>
				{ __( 'Interactive Table', 'interactive-table' ) }
			</p>
			<InnerBlocks
				template={ [ [ 'core/table', {} ] ] }
				allowedBlocks={ [ 'core/table' ] }
				templateLock="all"
			/>
		</div>
	);
}
