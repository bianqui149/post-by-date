<?php

/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

$category = isset($attributes['category']) ? intval($attributes['category']) : 0;

if($category){
	$date     = isset($attributes['date']) ? sanitize_text_field($attributes['date']) : '';
	$limit    = isset($attributes['limit']) ? intval($attributes['limit']) : 5;

	$new_date = explode('-', $date);

	$currentDate = date('Y-m-d');

	$dateQuery = '';
	if($date !== $currentDate){
		$dateQuery = array(
			'after' =>
			array(
				'year'  => $new_date[0],
				'month' => $new_date[1],
				'day'   => $date[2],
			)
		);
	}
	// Prepare arguments for WP_Query
	$args = array(
		'post_type'      => 'post',
		'posts_per_page' => $limit,
		'category__in'   => $category ? array($category) : array(), //filter by cat
		'orderby'        => 'date',
		'order'          => 'DESC', // Order by date
		'date_query'     => $dateQuery,
	);

	//new inst WP_Query
	$query = new WP_Query($args);

	// html output

	if ($query->have_posts()) {
		echo '<div class="post-by-date-block">';
		echo '<h2>Posts in Category: ' . esc_html(get_cat_name($category)) . '</h2>';

		// Loop through posts
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

		// Check if there are more posts to load
		if ($query->max_num_pages > 1) {
			?>
			<button class="load-more-posts" data-page="1" data-category="<?php echo esc_attr($category); ?>" data-date="<?php echo esc_attr($date); ?>" data-limit="<?php echo esc_attr($limit); ?>">
			Load More
			</button>
			<?php
		}

		echo '</div>';
	} else {
		echo '<p>No posts found.</p>';
	}

	// Reset query
	wp_reset_postdata();
}
