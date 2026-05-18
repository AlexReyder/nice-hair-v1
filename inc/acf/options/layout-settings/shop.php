<?php

declare(strict_types=1);

function nice_hair_get_shop_header_catalog_default_field_values(): array
{
    return function_exists('nice_hair_get_shop_catalog_menu_default_field_rows')
        ? nice_hair_get_shop_catalog_menu_default_field_rows()
        : [];
}

function nice_hair_maybe_bootstrap_shop_header_catalog_dropdown(): void
{
    if (! function_exists('get_field') || ! function_exists('update_field')) {
        return;
    }

    $bootstrap_flag_option = 'nh_shop_header_catalog_dropdown_bootstrapped';

    if (get_option($bootstrap_flag_option) === '1') {
        return;
    }

    $post_id = function_exists('nice_hair_header_footer_post_id')
        ? nice_hair_header_footer_post_id('shop')
        : 'nh_header_footer_shop';

    $field_name = 'nh_header_catalog_dropdown_items';
    $current_value = get_field($field_name, $post_id);

    if (function_exists('nice_hair_acf_has_value') && nice_hair_acf_has_value($current_value)) {
        update_option($bootstrap_flag_option, '1', false);
        return;
    }

    if (
        ! function_exists('nice_hair_acf_has_value')
        && $current_value !== null
        && $current_value !== false
        && $current_value !== ''
        && $current_value !== []
    ) {
        update_option($bootstrap_flag_option, '1', false);
        return;
    }

    $default_rows = nice_hair_get_shop_header_catalog_default_field_values();

    if ($default_rows === []) {
        return;
    }

    update_field($field_name, $default_rows, $post_id);
    update_option($bootstrap_flag_option, '1', false);
}

function nice_hair_register_shop_layout_settings_acf_options(): void
{
    if (! function_exists('acf_add_options_sub_page')) {
        return;
    }

    $parent_slug = function_exists('nice_hair_layout_settings_parent_slug')
        ? nice_hair_layout_settings_parent_slug()
        : 'header-footer-settings';

    acf_add_options_sub_page([
        'page_title'  => __('Хедер и футер Shop', 'nice-hair'),
        'menu_title'  => 'Shop',
        'menu_slug'   => 'header-footer-shop',
        'parent_slug' => $parent_slug,
        'capability'  => function_exists('nice_hair_admin_access_acf_capability')
            ? nice_hair_admin_access_acf_capability('header-footer-shop')
            : 'edit_posts',
        'post_id'     => nice_hair_header_footer_post_id('shop'),
    ]);

    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key'    => 'group_nh_shop_header',
        'title'  => __('Хедер Shop', 'nice-hair'),
        'fields' => [
            [
                'key'           => 'field_nh_shop_header_logo',
                'label'         => __('Логотип', 'nice-hair'),
                'name'          => 'nh_header_logo',
                'type'          => 'image',
                'return_format' => 'url',
                'preview_size'  => 'medium',
            ],
            [
                'key'           => 'field_nh_shop_header_logo_alt',
                'label'         => __('Alt-текст логотипа', 'nice-hair'),
                'name'          => 'nh_header_logo_alt',
                'type'          => 'text',
                'default_value' => 'Nice Hair',
            ],
            [
                'key'          => 'field_nh_shop_header_nav_items',
                'label'        => __('Пункты меню', 'nice-hair'),
                'name'         => 'nh_header_nav_items',
                'type'         => 'repeater',
                'layout'       => 'block',
                'min'          => 1,
                'button_label' => __('Добавить пункт меню', 'nice-hair'),
                'sub_fields'   => [
                    [
                        'key'           => 'field_nh_shop_header_nav_item_link',
                        'label'         => __('Ссылка', 'nice-hair'),
                        'name'          => 'item_link',
                        'type'          => 'link',
                        'return_format' => 'array',
                        'required'      => 1,
                    ],
                ],
            ],
            [
                'key'          => 'field_nh_shop_header_catalog_dropdown_items',
                'label'        => __('Выпадающее меню Catalog', 'nice-hair'),
                'name'         => 'nh_header_catalog_dropdown_items',
                'type'         => 'repeater',
                'layout'       => 'block',
                'button_label' => __('Добавить пункт dropdown', 'nice-hair'),
                'instructions' => 'Desktop dropdown и mobile accordion для пункта Catalog в Header Shop.',
                'sub_fields'   => [
                    [
                        'key'           => 'field_nh_shop_header_catalog_dropdown_item_link',
                        'label'         => __('Ссылка', 'nice-hair'),
                        'name'          => 'item_link',
                        'type'          => 'link',
                        'return_format' => 'array',
                        'required'      => 1,
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => 'header-footer-shop',
                ],
            ],
        ],
        'active' => true,
    ]);

    acf_add_local_field_group([
        'key'    => 'group_nh_shop_footer',
        'title'  => __('Футер Shop', 'nice-hair'),
        'fields' => [
            [
                'key'           => 'field_nh_shop_footer_logo',
                'label'         => __('Логотип', 'nice-hair'),
                'name'          => 'nh_footer_logo',
                'type'          => 'image',
                'return_format' => 'url',
                'preview_size'  => 'medium',
            ],
            [
                'key'           => 'field_nh_shop_footer_logo_alt',
                'label'         => __('Alt-текст логотипа', 'nice-hair'),
                'name'          => 'nh_footer_logo_alt',
                'type'          => 'text',
                'default_value' => 'Nice Hair',
            ],
            [
                'key'           => 'field_nh_shop_footer_description',
                'label'         => __('Описание бренда', 'nice-hair'),
                'name'          => 'nh_footer_description',
                'type'          => 'text',
                'default_value' => '2026 / Premium Hair Extensions in the Heart of Dubai',
            ],
            [
                'key'           => 'field_nh_shop_footer_nav_column_1_title',
                'label'         => __('Заголовок колонки 1', 'nice-hair'),
                'name'          => 'nh_footer_nav_column_1_title',
                'type'          => 'text',
                'default_value' => 'Catalog',
            ],
            [
                'key'          => 'field_nh_shop_footer_nav_column_1',
                'label'        => __('Навигация, колонка 1', 'nice-hair'),
                'name'         => 'nh_footer_nav_column_1',
                'type'         => 'repeater',
                'layout'       => 'block',
                'button_label' => __('Добавить пункт меню', 'nice-hair'),
                'sub_fields'   => [
                    [
                        'key'           => 'field_nh_shop_footer_nav_column_1_link',
                        'label'         => __('Ссылка', 'nice-hair'),
                        'name'          => 'item_link',
                        'type'          => 'link',
                        'return_format' => 'array',
                        'required'      => 1,
                    ],
                ],
            ],
            [
                'key'           => 'field_nh_shop_footer_nav_column_2_title',
                'label'         => __('Заголовок колонки 2', 'nice-hair'),
                'name'          => 'nh_footer_nav_column_2_title',
                'type'          => 'text',
                'default_value' => 'Site map',
            ],
            [
                'key'          => 'field_nh_shop_footer_nav_column_2',
                'label'        => __('Навигация, колонка 2', 'nice-hair'),
                'name'         => 'nh_footer_nav_column_2',
                'type'         => 'repeater',
                'layout'       => 'block',
                'button_label' => __('Добавить пункт меню', 'nice-hair'),
                'sub_fields'   => [
                    [
                        'key'           => 'field_nh_shop_footer_nav_column_2_link',
                        'label'         => __('Ссылка', 'nice-hair'),
                        'name'          => 'item_link',
                        'type'          => 'link',
                        'return_format' => 'array',
                        'required'      => 1,
                    ],
                ],
            ],
            [
                'key'           => 'field_nh_shop_footer_booking_link',
                'label'         => __('Кнопка CTA', 'nice-hair'),
                'name'          => 'nh_footer_booking_link',
                'type'          => 'link',
                'return_format' => 'array',
            ],
            [
                'key'           => 'field_nh_shop_footer_subscribe_title',
                'label'         => __('Заголовок подписки', 'nice-hair'),
                'name'          => 'nh_footer_subscribe_title',
                'type'          => 'text',
                'default_value' => 'SUBSCRIBE TO NEWS:',
            ],
            [
                'key'           => 'field_nh_shop_footer_subscribe_placeholder',
                'label'         => __('Placeholder поля email', 'nice-hair'),
                'name'          => 'nh_footer_subscribe_placeholder',
                'type'          => 'text',
                'default_value' => 'Email',
            ],
            [
                'key'   => 'field_nh_shop_footer_privacy_url',
                'label' => __('Ссылка на Privacy Policy', 'nice-hair'),
                'name'  => 'nh_footer_privacy_url',
                'type'  => 'url',
            ],
            [
                'key'        => 'field_nh_shop_footer_credits',
                'label'      => __('Кредиты', 'nice-hair'),
                'name'       => 'nh_footer_credits',
                'type'       => 'group',
                'layout'     => 'block',
                'sub_fields' => [
                    [
                        'key'           => 'field_nh_shop_footer_designer_name',
                        'label'         => __('Имя дизайнера', 'nice-hair'),
                        'name'          => 'designer_name',
                        'type'          => 'text',
                        'default_value' => 'umapalata.space',
                    ],
                    [
                        'key'   => 'field_nh_shop_footer_designer_url',
                        'label' => __('Ссылка дизайнера', 'nice-hair'),
                        'name'  => 'designer_url',
                        'type'  => 'url',
                    ],
                    [
                        'key'           => 'field_nh_shop_footer_developer_name',
                        'label'         => __('Имя разработчика', 'nice-hair'),
                        'name'          => 'developer_name',
                        'type'          => 'text',
                        'default_value' => 'username',
                    ],
                    [
                        'key'   => 'field_nh_shop_footer_developer_url',
                        'label' => __('Ссылка разработчика', 'nice-hair'),
                        'name'  => 'developer_url',
                        'type'  => 'url',
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => 'header-footer-shop',
                ],
            ],
        ],
        'active' => true,
    ]);
}

add_action('acf/init', 'nice_hair_register_shop_layout_settings_acf_options', 20);
add_action('acf/init', 'nice_hair_maybe_bootstrap_shop_header_catalog_dropdown', 25);