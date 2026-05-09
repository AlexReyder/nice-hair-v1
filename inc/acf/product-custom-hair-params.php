<?php

declare(strict_types=1);

/**
 * Custom Hair product configurator fields.
 *
 * WooCommerce attributes are used as source dictionaries:
 * - Length: pa_length
 * - Hair Quality: pa_hair_quality
 * - Texture: pa_texture
 *
 * Product ACF fields still store normalized string keys so existing
 * Custom Hair frontend/configurator logic remains compatible.
 */

function nice_hair_custom_hair_length_option_key(WP_Term $term): string
{
    foreach ([(string) $term->name, (string) $term->slug] as $candidate) {
        if (preg_match('/\d+(?:[\.,]\d+)?/', $candidate, $matches)) {
            return str_replace(',', '.', (string) $matches[0]);
        }
    }

    return nice_hair_normalize_shop_key((string) $term->slug);
}

function nice_hair_custom_hair_attribute_option_key(WP_Term $term, string $taxonomy): string
{
    if ($taxonomy === 'pa_length') {
        return nice_hair_custom_hair_length_option_key($term);
    }

    return nice_hair_normalize_shop_key((string) $term->slug);
}

function nice_hair_get_custom_hair_attribute_choices(string $taxonomy): array
{
    if (! taxonomy_exists($taxonomy)) {
        return [];
    }

    $terms = get_terms([
        'taxonomy'   => $taxonomy,
        'hide_empty' => false,
        'orderby'    => $taxonomy === 'pa_length' ? 'name_num' : 'name',
        'order'      => 'ASC',
    ]);

    if (! is_array($terms) || is_wp_error($terms)) {
        return [];
    }

    $choices = [];

    foreach ($terms as $term) {
        if (! $term instanceof WP_Term) {
            continue;
        }

        $key = nice_hair_custom_hair_attribute_option_key($term, $taxonomy);

        if ($key === '') {
            continue;
        }

        $choices[$key] = (string) $term->name;
    }

    return $choices;
}

function nice_hair_load_custom_hair_attribute_choices(array $field): array
{
    $field_name = (string) ($field['name'] ?? '');

    $taxonomy_by_field = [
        'nh_custom_hair_available_lengths'   => 'pa_length',
        'nh_custom_hair_available_qualities' => 'pa_hair_quality',
        'nh_custom_hair_available_textures'  => 'pa_texture',
    ];

    $taxonomy = $taxonomy_by_field[$field_name] ?? '';

    if ($taxonomy === '') {
        return $field;
    }

    $field['choices'] = nice_hair_get_custom_hair_attribute_choices($taxonomy);

    if ($field['choices'] === []) {
        $field['instructions'] = trim((string) ($field['instructions'] ?? ''));
        $field['instructions'] .= ($field['instructions'] !== '' ? '<br>' : '')
            . sprintf(
                'No values found. Add values in Products → Attributes → %s.',
                esc_html(function_exists('wc_attribute_label') ? wc_attribute_label($taxonomy) : $taxonomy)
            );
    }

    return $field;
}

add_filter('acf/load_field/name=nh_custom_hair_available_lengths', 'nice_hair_load_custom_hair_attribute_choices');
add_filter('acf/load_field/name=nh_custom_hair_available_qualities', 'nice_hair_load_custom_hair_attribute_choices');
add_filter('acf/load_field/name=nh_custom_hair_available_textures', 'nice_hair_load_custom_hair_attribute_choices');

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