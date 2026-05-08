<?php
declare(strict_types=1);

/**
 * Custom taxonomy "nh_post_type" for Salon / Shop post classification.
 */

add_action('init', 'nice_hair_register_post_type_taxonomy');

function nice_hair_register_post_type_taxonomy(): void {
	register_taxonomy('nh_post_type', 'post', [
		'labels' => [
			'name'          => 'Post Types',
			'singular_name' => 'Post Type',
			'add_new_item'  => 'Add Post Type',
			'edit_item'     => 'Edit Post Type',
			'all_items'     => 'All Post Types',
			'search_items'  => 'Search Post Types',
			'not_found'     => 'No post types found',
		],
		'hierarchical'      => true,
		'public'            => false,
		'show_ui'           => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => false,
	]);

	// Seed default terms on every init (wp_insert_term is a no-op if term exists).
	if (!term_exists('salon', 'nh_post_type')) {
		wp_insert_term('Salon', 'nh_post_type', ['slug' => 'salon']);
	}
	if (!term_exists('shop', 'nh_post_type')) {
		wp_insert_term('Shop', 'nh_post_type', ['slug' => 'shop']);
	}
}
