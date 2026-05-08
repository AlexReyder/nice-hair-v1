<?php
declare(strict_types=1);

/**
 * Filter the main blog query by nh_post_type taxonomy.
 * Default type: salon. Accepted values: salon, shop.
 */

add_action('pre_get_posts', 'nice_hair_blog_type_filter');

function nice_hair_blog_type_filter(WP_Query $query): void {
	if (is_admin() || !$query->is_main_query() || !$query->is_home()) {
		return;
	}

	$type = nice_hair_get_blog_type();

	$query->set('tax_query', [
		[
			'taxonomy' => 'nh_post_type',
			'field'    => 'slug',
			'terms'    => $type,
		],
	]);
}

/**
 * Get the current blog filter type from query string.
 */
function nice_hair_get_blog_type(): string {
	$allowed = ['salon', 'shop'];
	$type    = isset($_GET['type']) ? sanitize_key($_GET['type']) : 'salon';

	return in_array($type, $allowed, true) ? $type : 'salon';
}
