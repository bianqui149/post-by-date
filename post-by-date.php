<?php
/**
 * Plugin Name:       Post By Date
 * Description:       Display a list of posts from a category
 * Requires at least: 6.6
 * Requires PHP:      7.4.0
 * Version:           0.1.0
 * Author:            Bianqui Julian
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       post-by-date
 *
 * @package CreateBlock
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
function create_block_post_by_date_block_init() {
	register_block_type( __DIR__ . '/build' );
}
add_action( 'init', 'create_block_post_by_date_block_init' );



require_once plugin_dir_path( __FILE__ ) . 'inc/class-category-options-page.php';
