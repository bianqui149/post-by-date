<?php

if ( ! class_exists( 'PostByDateBlock' ) ) {

	class PostByDateBlock {
		private static $instance = null;

		public function __construct() {
			add_action('init', [$this, 'register_block']);
			add_action('wp_enqueue_scripts', [$this, 'enqueue_load_more_script']);
			add_action('wp_ajax_load_more_posts', [$this, 'load_more_posts_ajax_handler']);
			add_action('wp_ajax_nopriv_load_more_posts', [$this, 'load_more_posts_ajax_handler']);
		}

		public static function get_instance() {
			if (self::$instance === null) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Registers the block using the metadata loaded from the `block.json` file.
		 * Behind the scenes, it registers also all assets so they can be enqueued
		 * through the block editor in the corresponding context.
		 *
		 * @see https://developer.wordpress.org/reference/functions/register_block_type/
		 */
		public function register_block() {
			register_block_type(__DIR__ . '/../build');
		}

		// Enqueue the load more script
		public function enqueue_load_more_script() {
			wp_enqueue_script(
				'load-more-posts',
				plugins_url('assets/load-more-button.js', __FILE__),
				array(),
				null,
				true
			);

			wp_localize_script('load-more-posts', 'load_more_posts', array(
				'ajax_url' => admin_url('admin-ajax.php'),
			));
		}

		// Handle the AJAX request for loading more posts
		public function load_more_posts_ajax_handler() {
			// Get the request data
			$page     = isset($_POST['page']) ? intval($_POST['page']) : 1;
			$category = isset($_POST['category']) ? intval($_POST['category']) : 0;
			$date     = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
			$limit    = isset($_POST['limit']) ? intval($_POST['limit']) : 5;

			$new_date = explode('-', $date);

			// arguments
			$args = array(
				'post_type'      => 'post',
				'posts_per_page' => $limit,
				'category__in'   => $category ? array($category) : array(),
				'orderby'        => 'date',
				'order'          => 'DESC',
				'paged'          => $page + 1,
				'date_query'     => array(
					array(
						'after' => array(
							'year'  => $new_date[0],
							'month' => $new_date[1],
							'day'   => $new_date[2],
						),
					),
				),
			);

			// Execute the post query
			$query = new WP_Query($args);

			if ($query->have_posts()) {
				while ($query->have_posts()) {
					$query->the_post();
					?>
					<div class="post-item">
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p><?php the_excerpt(); ?></p>
						<p><small>Published on: <?php echo get_the_date(); ?></small></p>
					</div>
					<?php
				}
			} else {
				echo 'no-more-posts';
			}

			wp_reset_postdata();
			wp_die();
		}
	}

	PostByDateBlock::get_instance();
}
