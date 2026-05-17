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
                'label'         => 'Available Lengths',
                'name'          => 'nh_custom_hair_available_lengths',
                'type'          => 'checkbox',
                'choices'       => [],
                'return_format' => 'value',
                'layout'        => 'horizontal',
                'toggle'        => 1,
                'instructions'  => 'Values are loaded from WooCommerce attribute: Length.',
            ],
            [
                'key'           => 'field_nh_custom_hair_available_qualities',
                'label'         => 'Available Qualities',
                'name'          => 'nh_custom_hair_available_qualities',
                'type'          => 'checkbox',
                'choices'       => [],
                'return_format' => 'value',
                'layout'        => 'horizontal',
                'toggle'        => 1,
                'instructions'  => 'Values are loaded from WooCommerce attribute: Hair Quality.',
            ],
            [
                'key'           => 'field_nh_custom_hair_available_textures',
                'label'         => 'Available Textures',
                'name'          => 'nh_custom_hair_available_textures',
                'type'          => 'checkbox',
                'choices'       => [],
                'return_format' => 'value',
                'layout'        => 'vertical',
                'toggle'        => 1,
                'instructions'  => 'Values are loaded from WooCommerce attribute: Texture.',
            ],
            [
                'key'           => 'field_nh_custom_hair_min_weight_grams',
                'label'         => 'Min Weight',
                'name'          => 'nh_custom_hair_min_weight_grams',
                'type'          => 'number',
                'default_value' => 30,
                'min'           => 1,
                'step'          => 1,
                'append'        => 'g',
            ],
            [
                'key'           => 'field_nh_custom_hair_weight_step_grams',
                'label'         => 'Weight Step',
                'name'          => 'nh_custom_hair_weight_step_grams',
                'type'          => 'number',
                'default_value' => 10,
                'min'           => 1,
                'step'          => 1,
                'append'        => 'g',
            ],
            [
                'key'           => 'field_nh_custom_hair_default_weight_grams',
                'label'         => 'Default Weight',
                'name'          => 'nh_custom_hair_default_weight_grams',
                'type'          => 'number',
                'default_value' => 30,
                'min'           => 1,
                'step'          => 1,
                'append'        => 'g',
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