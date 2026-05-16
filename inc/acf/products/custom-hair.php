<?php

declare(strict_types=1);

function nice_hair_register_product_custom_hair_acf_fields(): void
{
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
            'key'      => 'group_nh_product_custom_hair',
            'title'    => 'Товар: Custom Hair',
            'fields'   => [
                [
                    'key'          => 'field_nh_custom_hair_color_options',
                    'label'        => 'Custom Hair: цветовые опции',
                    'name'         => 'nh_custom_hair_color_options',
                    'type'         => 'repeater',
                    'instructions' => 'Каждая цветовая опция управляет одним главным изображением в конфигураторе. Остальная галерея остается общей для товара.',
                    'layout'       => 'block',
                    'button_label' => 'Добавить цвет',
                    'sub_fields'   => [
                        [
                            'key'          => 'field_nh_custom_hair_color_label',
                            'label'        => 'Название цвета',
                            'name'         => 'color_label',
                            'type'         => 'text',
                            'instructions' => 'Видимое название, например: #24 или Golden Beige (12).',
                            'required'     => 1,
                            'wrapper'      => [
                                'width' => '34',
                            ],
                        ],
                        [
                            'key'          => 'field_nh_custom_hair_color_value',
                            'label'        => 'Значение цвета',
                            'name'         => 'color_value',
                            'type'         => 'text',
                            'instructions' => 'Стабильное внутреннее значение для корзины. Можно оставить понятным для человека.',
                            'required'     => 1,
                            'wrapper'      => [
                                'width' => '24',
                            ],
                        ],
                        [
                            'key'          => 'field_nh_custom_hair_color_group',
                            'label'        => 'Цветовая группа',
                            'name'         => 'color_group',
                            'type'         => 'select',
                            'choices'      => [],
                            'allow_null'   => 1,
                            'ui'           => 1,
                            'wrapper'      => [
                                'width' => '18',
                            ],
                        ],
                        [
                            'key'           => 'field_nh_custom_hair_color_main_image',
                            'label'         => 'Главное изображение',
                            'name'          => 'main_image',
                            'type'          => 'image',
                            'return_format' => 'array',
                            'preview_size'  => 'medium',
                            'instructions'  => 'Заменяет главное изображение, когда выбран этот цвет.',
                            'required'      => 1,
                            'wrapper'       => [
                                'width' => '24',
                            ],
                        ],
                    ],
                ],
                [
                    'key'          => 'field_nh_custom_hair_available_lengths',
                    'label'        => 'Custom Hair: доступные длины',
                    'name'         => 'nh_custom_hair_available_lengths',
                    'type'         => 'checkbox',
                    'instructions' => 'Выберите длины, доступные для этого конфигуратора.',
                    'choices'      => [],
                    'layout'       => 'horizontal',
                    'toggle'       => 1,
                    'return_format' => 'value',
                ],
                [
                    'key'          => 'field_nh_custom_hair_available_qualities',
                    'label'        => 'Custom Hair: доступные качества волос',
                    'name'         => 'nh_custom_hair_available_qualities',
                    'type'         => 'checkbox',
                    'instructions' => 'Выберите качества волос, доступные для этого конфигуратора.',
                    'choices'      => [],
                    'layout'       => 'horizontal',
                    'toggle'       => 1,
                    'return_format' => 'value',
                ],
                [
                    'key'          => 'field_nh_custom_hair_available_textures',
                    'label'        => 'Custom Hair: доступные текстуры',
                    'name'         => 'nh_custom_hair_available_textures',
                    'type'         => 'checkbox',
                    'instructions' => 'Выберите текстуры, доступные для этого конфигуратора.',
                    'choices'      => [],
                    'layout'       => 'horizontal',
                    'toggle'       => 1,
                    'return_format' => 'value',
                ],
                [
                    'key'           => 'field_nh_custom_hair_min_weight_grams',
                    'label'         => 'Custom Hair: минимальный вес (граммы)',
                    'name'          => 'nh_custom_hair_min_weight_grams',
                    'type'          => 'number',
                    'default_value' => 30,
                    'min'           => 0,
                    'step'          => 1,
                    'append'        => 'г',
                ],
                [
                    'key'           => 'field_nh_custom_hair_weight_step_grams',
                    'label'         => 'Custom Hair: шаг веса (граммы)',
                    'name'          => 'nh_custom_hair_weight_step_grams',
                    'type'          => 'number',
                    'default_value' => 10,
                    'min'           => 1,
                    'step'          => 1,
                    'append'        => 'г',
                ],
                [
                    'key'           => 'field_nh_custom_hair_default_weight_grams',
                    'label'         => 'Custom Hair: вес по умолчанию (граммы)',
                    'name'          => 'nh_custom_hair_default_weight_grams',
                    'type'          => 'number',
                    'default_value' => 30,
                    'min'           => 0,
                    'step'          => 1,
                    'append'        => 'г',
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
            'position'           => 'normal',
            'style'              => 'default',
            'label_placement'    => 'top',
            'instruction_placement' => 'label',
            'active'             => true,
            'menu_order'         => 13,
        ]);
}
add_action('acf/init', 'nice_hair_register_product_custom_hair_acf_fields');