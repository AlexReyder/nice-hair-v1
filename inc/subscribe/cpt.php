<?php

declare(strict_types=1);

/**
 * CPT `nh_subscriber` for site-wide newsletter subscriptions.
 */

function nice_hair_register_subscriber_cpt(): void
{
    register_post_type('nh_subscriber', [
        'labels'          => [
            'name'          => __('Подписчики', 'nice-hair'),
            'singular_name' => __('Подписчик', 'nice-hair'),
            'menu_name'     => __('Подписчики рассылки', 'nice-hair'),
            'all_items'     => __('Все подписчики', 'nice-hair'),
            'search_items'  => __('Искать подписчиков', 'nice-hair'),
            'not_found'     => __('Подписчиков пока нет', 'nice-hair'),
        ],
        'public'          => false,
        'show_ui'         => true,
        'show_in_menu'    => true,
        'show_in_rest'    => false,
        'menu_position'   => 33,
        'menu_icon'       => 'dashicons-email',
        'capability_type' => 'post',
        'capabilities'    => [
            'create_posts' => 'do_not_allow',
        ],
        'map_meta_cap'    => true,
        'hierarchical'    => false,
        'supports'        => ['title'],
        'has_archive'     => false,
        'rewrite'         => false,
        'query_var'       => false,
    ]);
}
add_action('init', 'nice_hair_register_subscriber_cpt');

/**
 * Admin list columns.
 */
function nice_hair_subscriber_admin_columns(array $columns): array
{
    $new = [];
    foreach ($columns as $key => $label) {
        $new[$key] = $label;
        if ($key === 'title') {
            $new['nh_sub_email'] = __('Email', 'nice-hair');
            $new['nh_sub_source'] = __('Источник', 'nice-hair');
        }
    }
    return $new;
}
add_filter('manage_nh_subscriber_posts_columns', 'nice_hair_subscriber_admin_columns');

function nice_hair_subscriber_admin_column_value(string $column, int $post_id): void
{
    if ($column === 'nh_sub_email') {
        echo esc_html((string) get_post_meta($post_id, '_nh_sub_email', true));
        return;
    }

    if ($column === 'nh_sub_source') {
        $source = (string) get_post_meta($post_id, '_nh_sub_source', true);
        $source_labels = [
            'blog' => __('Блог', 'nice-hair'),
            'footer_salon' => __('Футер Salon', 'nice-hair'),
            'footer_shop' => __('Футер Shop', 'nice-hair'),
        ];

        echo esc_html($source_labels[$source] ?? __('Не указан', 'nice-hair'));
    }
}
add_action('manage_nh_subscriber_posts_custom_column', 'nice_hair_subscriber_admin_column_value', 10, 2);
