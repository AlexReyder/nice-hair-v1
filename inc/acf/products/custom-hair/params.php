<?php

declare(strict_types=1);

/**
 * Custom Hair product configurator params.
 */

function nice_hair_register_custom_hair_params_fields(): void
{
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key'    => 'group_nh_product_custom_hair_params',
        'title'  => 'Товар: параметры Custom Hair',
        'fields' => [
            [
                'key'           => 'field_nh_custom_hair_available_lengths',
                'label'         => 'Доступные длины',
                'name'          => 'nh_custom_hair_available_lengths',
                'type'          => 'checkbox',
                'choices'       => [],
                'return_format' => 'value',
                'layout'        => 'horizontal',
                'toggle'        => 1,
                'instructions'  => 'Значения загружаются динамически из WooCommerce-атрибута: Length.',
            ],
            [
                'key'           => 'field_nh_custom_hair_available_qualities',
                'label'         => 'Доступные качества волос',
                'name'          => 'nh_custom_hair_available_qualities',
                'type'          => 'checkbox',
                'choices'       => [],
                'return_format' => 'value',
                'layout'        => 'horizontal',
                'toggle'        => 1,
                'instructions'  => 'Значения загружаются динамически из WooCommerce-атрибута: Hair Quality.',
            ],
            [
                'key'           => 'field_nh_custom_hair_available_textures',
                'label'         => 'Доступные текстуры',
                'name'          => 'nh_custom_hair_available_textures',
                'type'          => 'checkbox',
                'choices'       => [],
                'return_format' => 'value',
                'layout'        => 'vertical',
                'toggle'        => 1,
                'instructions'  => 'Резервный список. Если правила текстур по качеству волос не заполнены, все выбранные текстуры будут доступны для каждого качества волос.',
            ],
            [
                'key'          => 'field_nh_custom_hair_texture_rules',
                'label'        => 'Правила текстур по качеству волос',
                'name'         => 'nh_custom_hair_texture_rules',
                'type'         => 'repeater',
                'layout'       => 'block',
                'button_label' => 'Добавить правило для качества волос',
                'instructions' => 'Укажите, какие текстуры доступны для каждого качества волос. Если таблица пустая, используется поле «Доступные текстуры» для всех качеств волос.',
                'sub_fields'   => [
                    [
                        'key'           => 'field_nh_custom_hair_texture_rule_quality',
                        'label'         => 'Качество волос',
                        'name'          => 'item_quality',
                        'type'          => 'select',
                        'choices'       => [],
                        'return_format' => 'value',
                        'ui'            => 1,
                        'required'      => 1,
                    ],
                    [
                        'key'           => 'field_nh_custom_hair_texture_rule_textures',
                        'label'         => 'Доступные текстуры',
                        'name'          => 'item_textures',
                        'type'          => 'checkbox',
                        'choices'       => [],
                        'return_format' => 'value',
                        'layout'        => 'vertical',
                        'toggle'        => 1,
                        'required'      => 1,
                    ],
                ],
            ],
            [
                'key'           => 'field_nh_custom_hair_min_weight_grams',
                'label'         => 'Минимальный вес',
                'name'          => 'nh_custom_hair_min_weight_grams',
                'type'          => 'number',
                'default_value' => 30,
                'min'           => 1,
                'step'          => 1,
                'append'        => 'г',
            ],
            [
                'key'           => 'field_nh_custom_hair_weight_step_grams',
                'label'         => 'Шаг веса',
                'name'          => 'nh_custom_hair_weight_step_grams',
                'type'          => 'number',
                'default_value' => 10,
                'min'           => 1,
                'step'          => 1,
                'append'        => 'г',
            ],
            [
                'key'           => 'field_nh_custom_hair_default_weight_grams',
                'label'         => 'Вес по умолчанию',
                'name'          => 'nh_custom_hair_default_weight_grams',
                'type'          => 'number',
                'default_value' => 30,
                'min'           => 1,
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
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,
        'menu_order'            => 12,
    ]);
}

add_action('acf/init', 'nice_hair_register_custom_hair_params_fields');