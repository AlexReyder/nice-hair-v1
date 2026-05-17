<?php

declare(strict_types=1);

/**
 * Custom Hair color library and product color selector.
 */

function nice_hair_custom_hair_colors_post_id(): string
{
    return 'nh_custom_hair_colors';
}

function nice_hair_register_custom_hair_colors_acf(): void
{
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    if (function_exists('acf_add_options_page')) {
        acf_add_options_page([
            'page_title' => 'Цвета Custom Hair',
            'menu_title' => 'Цвета Custom Hair',
            'menu_slug'  => 'custom-hair-colors',
            'capability' => 'edit_posts',
            'redirect'   => false,
            'icon_url'   => 'dashicons-art',
            'position'   => 34,
            'post_id'    => nice_hair_custom_hair_colors_post_id(),
        ]);
    }

    acf_add_local_field_group([
        'key'    => 'group_nh_custom_hair_colors_library',
        'title'  => 'Custom Hair: глобальная библиотека цветов',
        'fields' => [
            [
                'key'          => 'field_nh_custom_hair_global_colors',
                'label'        => 'Цвета',
                'name'         => 'nh_custom_hair_global_colors',
                'type'         => 'repeater',
                'layout'       => 'block',
                'button_label' => 'Добавить цвет',
                'instructions' => 'Глобальная библиотека цветов Custom Hair. В товарах можно выбирать цвета из этого списка.',
                'sub_fields'   => nice_hair_custom_hair_color_acf_sub_fields('global'),
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => 'custom-hair-colors',
                ],
            ],
        ],
        'active' => true,
    ]);

    acf_add_local_field_group([
        'key'    => 'group_nh_product_custom_hair_colors',
        'title'  => 'Товар: цвета Custom Hair',
        'fields' => [
            [
                'key'           => 'field_nh_product_custom_hair_selected_global_colors',
                'label'         => 'Выбранные глобальные цвета',
                'name'          => 'nh_custom_hair_selected_global_colors',
                'type'          => 'checkbox',
                'choices'       => [],
                'layout'        => 'vertical',
                'return_format' => 'value',
                'toggle'        => 1,
                'allow_custom'  => 0,
                'save_custom'   => 0,
                'instructions'  => 'Выберите цвета из раздела «Цвета Custom Hair». В карточке товара будут показаны только выбранные цвета.',
            ],
            [
                'key'          => 'field_nh_product_custom_hair_local_colors',
                'label'        => 'Локальные дополнительные цвета',
                'name'         => 'nh_custom_hair_local_colors',
                'type'         => 'repeater',
                'layout'       => 'block',
                'button_label' => 'Добавить локальный цвет',
                'instructions' => 'Используйте только для цветов, которые нужны конкретно этому товару. Если локальный цвет имеет тот же ключ, что и глобальный, он переопределит глобальный цвет для этого товара.',
                'sub_fields'   => nice_hair_custom_hair_color_acf_sub_fields('local'),
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'product',
                ],
            ],
        ],
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,
        'menu_order'            => 13,
    ]);
}

add_action('acf/init', 'nice_hair_register_custom_hair_colors_acf', 35);

function nice_hair_custom_hair_color_acf_sub_fields(string $context): array
{
    $prefix = sanitize_key($context);

    $fields = [
        [
            'key'          => 'field_nh_' . $prefix . '_custom_hair_color_key',
            'label'        => 'Ключ цвета',
            'name'         => 'color_key',
            'type'         => 'text',
            'required'     => 1,
            'instructions' => 'Стабильный ключ, например: dark_brown, ash_blonde, copper_27. Не меняйте его без необходимости.',
        ],
        [
            'key'      => 'field_nh_' . $prefix . '_custom_hair_color_label',
            'label'    => 'Название цвета',
            'name'     => 'color_label',
            'type'     => 'text',
            'required' => 1,
        ],
        [
            'key'          => 'field_nh_' . $prefix . '_custom_hair_color_value',
            'label'        => 'Название на сайте',
            'name'         => 'color_value',
            'type'         => 'text',
            'instructions' => 'Необязательно. Если оставить пустым, будет использовано «Название цвета».',
        ],
        [
            'key'          => 'field_nh_' . $prefix . '_custom_hair_color_group',
            'label'        => 'Группа цвета',
            'name'         => 'color_group',
            'type'         => 'text',
            'instructions' => 'Необязательная группа, например: dark, middle, light.',
        ],
        [
            'key'           => 'field_nh_' . $prefix . '_custom_hair_preview_image',
            'label'         => 'Превью / образец цвета',
            'name'          => 'preview_image',
            'type'          => 'image',
            'return_format' => 'array',
            'preview_size'  => 'thumbnail',
            'instructions'  => 'Маленькое изображение для селектора Color. Если не заполнено, будет использовано основное изображение.',
        ],
        [
            'key'           => 'field_nh_' . $prefix . '_custom_hair_main_image',
            'label'         => 'Основное изображение',
            'name'          => 'main_image',
            'type'          => 'image',
            'return_format' => 'array',
            'preview_size'  => 'medium',
            'required'      => 1,
            'instructions'  => 'Изображение, которое появится в основной галерее товара после клика по этому цвету.',
        ],
        [
            'key'           => 'field_nh_' . $prefix . '_custom_hair_sort_order',
            'label'         => 'Порядок сортировки',
            'name'          => 'sort_order',
            'type'          => 'number',
            'default_value' => 100,
            'step'          => 1,
        ],
    ];

    if ($context === 'global') {
        $fields[] = [
            'key'           => 'field_nh_global_custom_hair_color_is_active',
            'label'         => 'Активен',
            'name'          => 'is_active',
            'type'          => 'true_false',
            'default_value' => 1,
            'ui'            => 1,
        ];
    }

    return $fields;
}

function nice_hair_load_custom_hair_selected_global_color_choices(array $field): array
{
    $field['choices'] = [];

    if (! function_exists('get_field')) {
        return $field;
    }

    $rows = get_field('nh_custom_hair_global_colors', nice_hair_custom_hair_colors_post_id());

    if (! is_array($rows)) {
        return $field;
    }

    foreach ($rows as $row) {
        if (! is_array($row)) {
            continue;
        }

        $is_active = array_key_exists('is_active', $row) ? (bool) $row['is_active'] : true;

        if (! $is_active) {
            continue;
        }

        $key = function_exists('nice_hair_normalize_shop_key')
            ? nice_hair_normalize_shop_key((string) ($row['color_key'] ?? ''))
            : sanitize_title((string) ($row['color_key'] ?? ''));

        $label = trim((string) ($row['color_label'] ?? ''));

        if ($key === '' || $label === '') {
            continue;
        }

        $field['choices'][$key] = $label;
    }

    return $field;
}

add_filter(
    'acf/load_field/key=field_nh_product_custom_hair_selected_global_colors',
    'nice_hair_load_custom_hair_selected_global_color_choices'
);

add_filter(
    'acf/load_field/name=nh_custom_hair_selected_global_colors',
    'nice_hair_load_custom_hair_selected_global_color_choices'
);