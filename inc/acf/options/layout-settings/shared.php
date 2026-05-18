<?php

declare(strict_types=1);

function nice_hair_layout_settings_parent_slug(): string
{
    return 'header-footer-settings';
}

function nice_hair_register_layout_settings_parent_page(): void
{
    if (! function_exists('acf_add_options_page')) {
        return;
    }

    acf_add_options_page([
        'page_title' => __('Шапка и подвал', 'nice-hair'),
        'menu_title' => __('Шапка и подвал', 'nice-hair'),
        'menu_slug'  => nice_hair_layout_settings_parent_slug(),
        'capability' => function_exists('nice_hair_admin_access_acf_capability')
            ? nice_hair_admin_access_acf_capability('header-footer-settings')
            : 'edit_posts',
        'redirect'   => true,
        'icon_url'   => 'dashicons-screenoptions',
        'position'   => 30,
    ]);
}

add_action('acf/init', 'nice_hair_register_layout_settings_parent_page', 5);