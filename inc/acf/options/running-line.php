<?php

declare(strict_types=1);

function nice_hair_register_running_line_acf_options(): void
{
    if (! function_exists('acf_add_options_sub_page')) {
        return;
    }

    acf_add_options_sub_page([
        'page_title' => __('Настройки бегущей строки', 'nice-hair'),
        'menu_title' => __('Настройки', 'nice-hair'),
        'menu_slug' => 'running-line-settings',
        'parent_slug' => 'edit.php?post_type=nh_running_line',
        'capability' => 'edit_posts',
        'post_id' => 'nh_running_line_settings',
    ]);

    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_nh_running_line_settings',
        'title' => 'Настройки бегущей строки',
        'fields' => [
            [
                'key' => 'field_nh_running_line_enabled',
                'label' => 'Включить бегущую строку',
                'name' => 'nh_running_line_enabled',
                'type' => 'true_false',
                'ui' => 1,
                'default_value' => 0,
            ],
            [
                'key' => 'field_nh_running_line_locations',
                'label' => 'Показывать на страницах',
                'name' => 'nh_running_line_locations',
                'type' => 'checkbox',
                'layout' => 'vertical',
                'return_format' => 'value',
                'choices' => [
                    'front_page' => 'Главная',
                    'salon_page' => 'Salon',
                    'shop_page' => 'Shop',
                    'shipping_page' => 'Shipping & Payment',
                    'tools_page' => 'Tools',
                    'blog_home' => 'Blog',
                    'blog_single' => 'Записи блога',
                    'default_pages' => 'Другие страницы',
                    'product_archive' => 'Категории товаров',
                    'product_single' => 'Карточки товаров',
                ],
                'instructions' => 'Выберите основные страницы и разделы, где нужно показывать бегущую строку.',
            ],
            [
                'key' => 'field_nh_running_line_specific_objects',
                'label' => 'Дополнительно включить на отдельных страницах',
                'name' => 'nh_running_line_specific_objects',
                'type' => 'relationship',
                'post_type' => ['page', 'post', 'product'],
                'filters' => ['search', 'post_type'],
                'return_format' => 'id',
                'instructions' => 'Используйте это поле, если строку нужно показать на конкретной странице, записи блога или карточке товара.',
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'running-line-settings',
                ],
            ],
        ],
        'active' => true,
    ]);
}
add_action('acf/init', 'nice_hair_register_running_line_acf_options', 25);
