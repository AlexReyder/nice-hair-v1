<?php
declare(strict_types=1);

function nice_hair_get_hidden_admin_page_ids(): array
{
    $option_names = [
        'woocommerce_cart_page_id',
        'woocommerce_checkout_page_id',
        'woocommerce_myaccount_page_id',
    ];

    $page_ids = [];

    foreach ($option_names as $option_name) {
        $page_id = (int) get_option($option_name);

        if ($page_id > 0) {
            $page_ids[] = $page_id;
        }
    }

    $page_ids = array_values(array_unique(array_filter($page_ids)));

    return apply_filters('nice_hair_hidden_admin_page_ids', $page_ids);
}

function nice_hair_should_show_hidden_admin_pages(): bool
{
    return isset($_GET['nh_show_system_pages'])
        && $_GET['nh_show_system_pages'] === '1'
        && current_user_can('manage_options');
}

function nice_hair_hide_system_pages_from_admin_list(WP_Query $query): void
{
    if (
        ! is_admin()
        || ! $query->is_main_query()
        || nice_hair_should_show_hidden_admin_pages()
    ) {
        return;
    }

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;

    if (! $screen || $screen->id !== 'edit-page') {
        return;
    }

    $hidden_page_ids = nice_hair_get_hidden_admin_page_ids();

    if ($hidden_page_ids === []) {
        return;
    }

    $post__not_in = (array) $query->get('post__not_in');
    $query->set('post__not_in', array_values(array_unique(array_merge($post__not_in, $hidden_page_ids))));
}
add_action('pre_get_posts', 'nice_hair_hide_system_pages_from_admin_list');
