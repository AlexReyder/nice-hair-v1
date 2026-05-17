<?php

declare(strict_types=1);

/**
 * Custom Hair attribute choice helpers.
 *
 * WooCommerce attributes are used as source dictionaries:
 * - Length: pa_length
 * - Hair Quality: pa_hair_quality
 * - Texture: pa_texture
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

function nice_hair_get_custom_hair_attribute_taxonomy_for_field(array $field): string
{
    $field_key = (string) ($field['key'] ?? '');
    $field_name = (string) ($field['name'] ?? '');

    $taxonomy_by_key = [
        'field_nh_custom_hair_available_lengths'       => 'pa_length',
        'field_nh_custom_hair_available_qualities'     => 'pa_hair_quality',
        'field_nh_custom_hair_available_textures'      => 'pa_texture',
        'field_nh_custom_hair_texture_rule_quality'    => 'pa_hair_quality',
        'field_nh_custom_hair_texture_rule_textures'   => 'pa_texture',
    ];

    if (isset($taxonomy_by_key[$field_key])) {
        return $taxonomy_by_key[$field_key];
    }

    $taxonomy_by_name = [
        'nh_custom_hair_available_lengths'   => 'pa_length',
        'nh_custom_hair_available_qualities' => 'pa_hair_quality',
        'nh_custom_hair_available_textures'  => 'pa_texture',
    ];

    return $taxonomy_by_name[$field_name] ?? '';
}

function nice_hair_apply_custom_hair_attribute_choices(array $field): array
{
    $taxonomy = nice_hair_get_custom_hair_attribute_taxonomy_for_field($field);

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

add_filter('acf/load_field/key=field_nh_custom_hair_available_lengths', 'nice_hair_apply_custom_hair_attribute_choices', 50);
add_filter('acf/load_field/key=field_nh_custom_hair_available_qualities', 'nice_hair_apply_custom_hair_attribute_choices', 50);
add_filter('acf/load_field/key=field_nh_custom_hair_available_textures', 'nice_hair_apply_custom_hair_attribute_choices', 50);
add_filter('acf/load_field/key=field_nh_custom_hair_texture_rule_quality', 'nice_hair_apply_custom_hair_attribute_choices', 50);
add_filter('acf/load_field/key=field_nh_custom_hair_texture_rule_textures', 'nice_hair_apply_custom_hair_attribute_choices', 50);

add_filter('acf/prepare_field/key=field_nh_custom_hair_available_lengths', 'nice_hair_apply_custom_hair_attribute_choices', 50);
add_filter('acf/prepare_field/key=field_nh_custom_hair_available_qualities', 'nice_hair_apply_custom_hair_attribute_choices', 50);
add_filter('acf/prepare_field/key=field_nh_custom_hair_available_textures', 'nice_hair_apply_custom_hair_attribute_choices', 50);
add_filter('acf/prepare_field/key=field_nh_custom_hair_texture_rule_quality', 'nice_hair_apply_custom_hair_attribute_choices', 50);
add_filter('acf/prepare_field/key=field_nh_custom_hair_texture_rule_textures', 'nice_hair_apply_custom_hair_attribute_choices', 50);

add_filter('acf/load_field/name=nh_custom_hair_available_lengths', 'nice_hair_apply_custom_hair_attribute_choices', 50);
add_filter('acf/load_field/name=nh_custom_hair_available_qualities', 'nice_hair_apply_custom_hair_attribute_choices', 50);
add_filter('acf/load_field/name=nh_custom_hair_available_textures', 'nice_hair_apply_custom_hair_attribute_choices', 50);

add_filter('acf/prepare_field/name=nh_custom_hair_available_lengths', 'nice_hair_apply_custom_hair_attribute_choices', 50);
add_filter('acf/prepare_field/name=nh_custom_hair_available_qualities', 'nice_hair_apply_custom_hair_attribute_choices', 50);
add_filter('acf/prepare_field/name=nh_custom_hair_available_textures', 'nice_hair_apply_custom_hair_attribute_choices', 50);