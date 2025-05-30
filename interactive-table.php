<?php
/**
 * Plugin Name:       Interactive Table
 * Description:       An interactive table block built with the WP Interactivity API
 * Version:           0.1.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Mikey Arce
 * Author URI:        https://mikeyarce.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       interactive-table
 *
 * @package           InteractiveTable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function marce_interactive_table_block_init() {
	register_block_type( __DIR__ . '/build' );
}
// Initialize the block.
add_action( 'init', 'marce_interactive_table_block_init' );
