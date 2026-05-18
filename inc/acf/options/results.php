<?php

declare(strict_types=1);

function nice_hair_results_settings_page_slug(): string
{
    return 'results-settings';
}

function nice_hair_is_results_settings_admin_page(): bool
{
    if (! is_admin()) {
        return false;
    }

    $page = isset($_GET['page']) && is_string($_GET['page'])
        ? sanitize_key(wp_unslash($_GET['page']))
        : '';

    return $page === nice_hair_results_settings_page_slug();
}

function nice_hair_register_results_acf_options(): void
{
    if (! function_exists('acf_add_options_page')) {
        return;
    }

    acf_add_options_page([
        'page_title' => __('Настройки блока результаты', 'nice-hair'),
        'menu_title' => __('Блок результаты', 'nice-hair'),
        'menu_slug'  => nice_hair_results_settings_page_slug(),
        'capability' => function_exists('nice_hair_admin_access_acf_capability')
            ? nice_hair_admin_access_acf_capability(nice_hair_results_settings_page_slug())
            : 'edit_posts',
        'redirect'   => false,
        'icon_url'   => 'dashicons-images-alt2',
        'position'   => 32,
    ]);

    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key'    => 'group_nh_results_shared',
        'title'  => 'Блок результатов - общие настройки и карточки',
        'fields' => [
            [
                'key'           => 'field_nh_results_global_hide_nav_desktop',
                'label'         => 'Скрыть навигацию на Desktop',
                'name'          => 'nh_results_hide_nav_desktop',
                'type'          => 'true_false',
                'ui'            => 0,
                'default_value' => 0,
                'instructions'  => 'Скрывает стрелки слайдера только на ширине Desktop.',
            ],
            [
                'key'           => 'field_nh_results_global_hide_nav_tablet',
                'label'         => 'Скрыть навигацию на Tablet',
                'name'          => 'nh_results_hide_nav_tablet',
                'type'          => 'true_false',
                'ui'            => 0,
                'default_value' => 0,
                'instructions'  => 'Скрывает стрелки слайдера только на ширине Tablet.',
            ],
            [
                'key'           => 'field_nh_results_global_hide_nav_mobile',
                'label'         => 'Скрыть навигацию на Mobile',
                'name'          => 'nh_results_hide_nav_mobile',
                'type'          => 'true_false',
                'ui'            => 0,
                'default_value' => 0,
                'instructions'  => 'Скрывает стрелки слайдера только на ширине Mobile.',
            ],
            [
                'key'          => 'field_nh_results_items',
                'label'        => 'Карточки результатов',
                'name'         => 'nh_results_items',
                'type'         => 'repeater',
                'min'          => 1,
                'layout'       => 'block',
                'collapsed'    => 'field_nh_results_length',
                'button_label' => 'Добавить карточку результата',
                'instructions' => 'Эти карточки используются на всех страницах с блоком Results (Salon, Shop и т.д.).',
                'sub_fields'   => [
                    [
                        'key'           => 'field_nh_results_before_image',
                        'label'         => 'Изображение до',
                        'name'          => 'item_before_image',
                        'type'          => 'image',
                        'return_format' => 'array',
                        'preview_size'  => 'medium',
                        'required'      => 1,
                    ],
                    [
                        'key'           => 'field_nh_results_after_image',
                        'label'         => 'Изображение после',
                        'name'          => 'item_after_image',
                        'type'          => 'image',
                        'return_format' => 'array',
                        'preview_size'  => 'medium',
                        'required'      => 1,
                    ],
                    [
                        'key'         => 'field_nh_results_length',
                        'label'       => 'Длина',
                        'name'        => 'item_length',
                        'type'        => 'text',
                        'placeholder' => '65 cm',
                    ],
                    [
                        'key'         => 'field_nh_results_hair',
                        'label'       => 'Волосы',
                        'name'        => 'item_hair',
                        'type'        => 'text',
                        'placeholder' => 'exclusive 3226',
                    ],
                    [
                        'key'         => 'field_nh_results_capsules',
                        'label'       => 'Капсулы',
                        'name'        => 'item_capsules',
                        'type'        => 'text',
                        'placeholder' => '288 pcs',
                    ],
                    [
                        'key'       => 'field_nh_results_comment',
                        'label'     => 'Комментарий стилиста',
                        'name'      => 'item_comment',
                        'type'      => 'textarea',
                        'rows'      => 4,
                        'new_lines' => 'br',
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => nice_hair_results_settings_page_slug(),
                ],
            ],
        ],
        'active' => true,
    ]);
}

add_action('acf/init', 'nice_hair_register_results_acf_options');