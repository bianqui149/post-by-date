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


require_once plugin_dir_path( __FILE__ ) . 'inc/class-post-by-date-block.php';

require_once plugin_dir_path( __FILE__ ) . 'inc/class-category-options-page.php';
