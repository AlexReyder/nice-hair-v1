<?php

declare(strict_types=1);

function nice_hair_results_settings_page_slug(): string
{
    return 'results-settings';
}

function nice_hair_results_salon_settings_page_slug(): string
{
    return 'results-settings-salon';
}

function nice_hair_results_shop_settings_page_slug(): string
{
    return 'results-settings-shop';
}

function nice_hair_results_post_id(string $context = ''): string
{
    return $context === 'shop'
        ? 'nh_results_shop'
        : 'nh_results_salon';
}

function nice_hair_is_results_settings_admin_page(): bool
{
    if (! is_admin()) {
        return false;
    }

    $page = isset($_GET['page']) && is_string($_GET['page'])
        ? sanitize_key(wp_unslash($_GET['page']))
        : '';

    return in_array($page, [
        nice_hair_results_settings_page_slug(),
        nice_hair_results_salon_settings_page_slug(),
        nice_hair_results_shop_settings_page_slug(),
    ], true);
}

function nice_hair_results_acf_capability(string $page_slug): string
{
    return function_exists('nice_hair_admin_access_acf_capability')
        ? nice_hair_admin_access_acf_capability($page_slug)
        : 'edit_posts';
}

function nice_hair_results_field_key(string $context, string $suffix): string
{
    $context = in_array($context, ['salon', 'shop'], true) ? $context : 'salon';

    return sprintf('field_nh_results_%s_%s', $context, $suffix);
}

function nice_hair_results_options_fields(string $context): array
{
    $context = in_array($context, ['salon', 'shop'], true) ? $context : 'salon';

    return [
        [
            'key'           => nice_hair_results_field_key($context, 'hide_nav_desktop'),
            'label'         => 'Скрыть навигацию на Desktop',
            'name'          => 'nh_results_hide_nav_desktop',
            'type'          => 'true_false',
            'ui'            => 0,
            'default_value' => 0,
            'instructions'  => 'Скрывает стрелки слайдера только на ширине Desktop.',
        ],
        [
            'key'           => nice_hair_results_field_key($context, 'hide_nav_tablet'),
            'label'         => 'Скрыть навигацию на Tablet',
            'name'          => 'nh_results_hide_nav_tablet',
            'type'          => 'true_false',
            'ui'            => 0,
            'default_value' => 0,
            'instructions'  => 'Скрывает стрелки слайдера только на ширине Tablet.',
        ],
        [
            'key'           => nice_hair_results_field_key($context, 'hide_nav_mobile'),
            'label'         => 'Скрыть навигацию на Mobile',
            'name'          => 'nh_results_hide_nav_mobile',
            'type'          => 'true_false',
            'ui'            => 0,
            'default_value' => 0,
            'instructions'  => 'Скрывает стрелки слайдера только на ширине Mobile.',
        ],
        [
            'key'          => nice_hair_results_field_key($context, 'items'),
            'label'        => 'Карточки результатов',
            'name'         => 'nh_results_items',
            'type'         => 'repeater',
            'min'          => 1,
            'layout'       => 'block',
            'collapsed'    => nice_hair_results_field_key($context, 'length'),
            'button_label' => 'Добавить карточку результата',
            'instructions' => $context === 'shop'
                ? 'Эти карточки используются для блока Results на странице Shop.'
                : 'Эти карточки используются для блока Results на странице Salon.',
            'sub_fields'   => [
                [
                    'key'           => nice_hair_results_field_key($context, 'before_image'),
                    'label'         => 'Изображение до',
                    'name'          => 'item_before_image',
                    'type'          => 'image',
                    'return_format' => 'array',
                    'preview_size'  => 'medium',
                    'required'      => 1,
                ],
                [
                    'key'           => nice_hair_results_field_key($context, 'after_image'),
                    'label'         => 'Изображение после',
                    'name'          => 'item_after_image',
                    'type'          => 'image',
                    'return_format' => 'array',
                    'preview_size'  => 'medium',
                    'required'      => 1,
                ],
                [
                    'key'         => nice_hair_results_field_key($context, 'length'),
                    'label'       => 'Длина',
                    'name'        => 'item_length',
                    'type'        => 'text',
                    'placeholder' => '65 cm',
                ],
                [
                    'key'         => nice_hair_results_field_key($context, 'hair'),
                    'label'       => 'Волосы',
                    'name'        => 'item_hair',
                    'type'        => 'text',
                    'placeholder' => 'exclusive 3226',
                ],
                [
                    'key'         => nice_hair_results_field_key($context, 'capsules'),
                    'label'       => 'Капсулы',
                    'name'        => 'item_capsules',
                    'type'        => 'text',
                    'placeholder' => '288 pcs',
                ],
                [
                    'key'       => nice_hair_results_field_key($context, 'comment'),
                    'label'     => 'Комментарий стилиста',
                    'name'      => 'item_comment',
                    'type'      => 'textarea',
                    'rows'      => 4,
                    'new_lines' => 'br',
                ],
            ],
        ],
    ];
}

function nice_hair_results_acf_option_exists(string $post_id, string $field_name): bool
{
    $option_keys = $post_id === 'option'
        ? [
            'options_' . $field_name,
            'option_' . $field_name,
        ]
        : [
            $post_id . '_' . $field_name,
        ];

    foreach ($option_keys as $option_key) {
        if (get_option($option_key, null) !== null) {
            return true;
        }

        if (get_option('_' . $option_key, null) !== null) {
            return true;
        }
    }

    return false;
}

function nice_hair_get_results_context(array $block = []): string
{
    $class_name = isset($block['className']) && is_string($block['className'])
        ? strtolower($block['className'])
        : '';

    if ($class_name !== '') {
        if (
            str_contains($class_name, 'shop-results')
            || str_contains($class_name, 'results-shop')
            || str_contains($class_name, 'is-shop-results')
        ) {
            return 'shop';
        }

        if (
            str_contains($class_name, 'salon-results')
            || str_contains($class_name, 'results-salon')
            || str_contains($class_name, 'is-salon-results')
        ) {
            return 'salon';
        }
    }

    $section = function_exists('nice_hair_get_current_section')
        ? nice_hair_get_current_section('salon')
        : 'salon';

    return $section === 'shop' ? 'shop' : 'salon';
}

function nice_hair_get_results_option_field(
    string $field_name,
    string $context = '',
    mixed $fallback = null,
    bool $legacy_salon_fallback = true
): mixed {
    if (! function_exists('get_field')) {
        return $fallback;
    }

    $context = $context === 'shop' ? 'shop' : 'salon';
    $post_id = nice_hair_results_post_id($context);

    $value = get_field($field_name, $post_id);

    if (nice_hair_results_acf_option_exists($post_id, $field_name)) {
        return $value;
    }

    if ($legacy_salon_fallback && $context === 'salon') {
        $legacy_value = get_field($field_name, 'option');

        if (nice_hair_results_acf_option_exists('option', $field_name)) {
            return $legacy_value;
        }
    }

    return $fallback;
}

function nice_hair_register_results_acf_options(): void
{
    if (! function_exists('acf_add_options_page')) {
        return;
    }

    acf_add_options_page([
        'page_title' => __('Блок результатов', 'nice-hair'),
        'menu_title' => __('Блок результатов', 'nice-hair'),
        'menu_slug'  => nice_hair_results_settings_page_slug(),
        'capability' => nice_hair_results_acf_capability(nice_hair_results_settings_page_slug()),
        'redirect'   => true,
        'icon_url'   => 'dashicons-images-alt2',
        'position'   => 32,
    ]);

    if (function_exists('acf_add_options_sub_page')) {
        acf_add_options_sub_page([
            'page_title'  => __('Блок результатов — Salon', 'nice-hair'),
            'menu_title'  => __('Salon', 'nice-hair'),
            'parent_slug' => nice_hair_results_settings_page_slug(),
            'menu_slug'   => nice_hair_results_salon_settings_page_slug(),
            'capability'  => nice_hair_results_acf_capability(nice_hair_results_salon_settings_page_slug()),
            'post_id'     => nice_hair_results_post_id('salon'),
            'redirect'    => false,
        ]);

        acf_add_options_sub_page([
            'page_title'  => __('Блок результатов — Shop', 'nice-hair'),
            'menu_title'  => __('Shop', 'nice-hair'),
            'parent_slug' => nice_hair_results_settings_page_slug(),
            'menu_slug'   => nice_hair_results_shop_settings_page_slug(),
            'capability'  => nice_hair_results_acf_capability(nice_hair_results_shop_settings_page_slug()),
            'post_id'     => nice_hair_results_post_id('shop'),
            'redirect'    => false,
        ]);
    }

    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key'    => 'group_nh_results_salon',
        'title'  => 'Блок результатов — Salon',
        'fields' => nice_hair_results_options_fields('salon'),
        'location' => [
            [
                [
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => nice_hair_results_salon_settings_page_slug(),
                ],
            ],
        ],
        'active' => true,
    ]);

    acf_add_local_field_group([
        'key'    => 'group_nh_results_shop',
        'title'  => 'Блок результатов — Shop',
        'fields' => nice_hair_results_options_fields('shop'),
        'location' => [
            [
                [
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => nice_hair_results_shop_settings_page_slug(),
                ],
            ],
        ],
        'active' => true,
    ]);
}

add_action('acf/init', 'nice_hair_register_results_acf_options');