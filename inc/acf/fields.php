<?php

declare(strict_types=1);

require_once __DIR__ . '/products/loader.php';

function nice_hair_register_acf_fields(): void
{
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
            'key'      => 'group_nh_extension_type_how_to_use',
            'title'    => 'Extension Type: How to Use',
            'fields'   => [
                [
                    'key'           => 'field_nh_extension_preview_image',
                    'label'         => 'Preview Image',
                    'name'          => 'nh_extension_preview_image',
                    'type'          => 'image',
                    'return_format' => 'array',
                    'preview_size'  => 'medium',
                    'instructions'  => 'Image for product-form preview cards and selector thumbnails of this Extension Type.',
                ],
                [
                    'key'           => 'field_nh_extension_how_to_use_image',
                    'label'         => 'How to Use - Image',
                    'name'          => 'nh_extension_how_to_use_image',
                    'type'          => 'image',
                    'return_format' => 'array',
                    'preview_size'  => 'medium',
                    'instructions'  => 'Shared image for the "How to use" drawer of this Extension Type.',
                ],
                [
                    'key'          => 'field_nh_extension_how_to_use_text',
                    'label'        => 'How to Use - Description',
                    'name'         => 'nh_extension_how_to_use_text',
                    'type'         => 'textarea',
                    'rows'         => 6,
                    'instructions' => 'Shared description for the "How to use" drawer of this Extension Type.',
                ],
            ],
            'location' => [
                [
                    [
                        'param'    => 'taxonomy',
                        'operator' => '==',
                        'value'    => 'pa_extension_type',
                    ],
                ],
            ],
            'position'              => 'normal',
            'style'                 => 'default',
            'label_placement'       => 'top',
            'instruction_placement' => 'label',
            'active'                => true,
            'menu_order'            => 15,
        ]);
}

function nice_hair_get_exclusive_product_form_override_choices(): array
{
    $choices = [];

    if (! function_exists('nice_hair_get_shop_product_form_options')) {
        return $choices;
    }

    foreach (nice_hair_get_shop_product_form_options() as $option) {
        if (! is_array($option)) {
            continue;
        }

        $key = isset($option['key']) ? nice_hair_normalize_shop_key((string) $option['key']) : '';

        if ($key === '' || $key === 'bulk') {
            continue;
        }

        $choices[$key] = (string) ($option['label'] ?? nice_hair_humanize_shop_key($key));
    }

    return $choices;
}

function nice_hair_load_exclusive_product_form_override_field(array $field): array
{
    $field['choices'] = nice_hair_get_exclusive_product_form_override_choices();

    return $field;
}

function nice_hair_load_shop_assortment_selected_forms_field(array $field): array
{
    $field['choices'] = function_exists('nice_hair_get_shop_assortment_form_filter_choices')
        ? nice_hair_get_shop_assortment_form_filter_choices()
        : [];

    return $field;
}


function nice_hair_get_product_attribute_filter_choices(): array
{
    $choices = [];

    if (function_exists('wc_get_attribute_taxonomies')) {
        foreach ((array) wc_get_attribute_taxonomies() as $attribute) {
            $attribute_name = trim((string) ($attribute->attribute_name ?? ''));

            if ($attribute_name === '') {
                continue;
            }

            $taxonomy = function_exists('wc_attribute_taxonomy_name')
                ? wc_attribute_taxonomy_name($attribute_name)
                : 'pa_' . sanitize_title($attribute_name);
            $taxonomy = sanitize_key($taxonomy);

            if ($taxonomy === '' || ! str_starts_with($taxonomy, 'pa_')) {
                continue;
            }

            $label = trim((string) ($attribute->attribute_label ?? ''));

            if ($label === '' && function_exists('wc_attribute_label')) {
                $label = trim((string) wc_attribute_label($taxonomy));
            }

            if ($label === '') {
                $label = ucwords(str_replace(['_', '-'], ' ', preg_replace('/^pa_/', '', $taxonomy)));
            }

            $choices[$taxonomy] = sprintf('%s (%s)', $label, $taxonomy);
        }
    }

    if ($choices === []) {
        foreach (get_taxonomies([], 'objects') as $taxonomy => $taxonomy_object) {
            $taxonomy = sanitize_key((string) $taxonomy);

            if (! str_starts_with($taxonomy, 'pa_')) {
                continue;
            }

            $label = trim((string) ($taxonomy_object->label ?? ''));
            $choices[$taxonomy] = sprintf('%s (%s)', $label !== '' ? $label : $taxonomy, $taxonomy);
        }
    }

    asort($choices, SORT_NATURAL | SORT_FLAG_CASE);

    return $choices;
}

function nice_hair_load_product_category_filter_taxonomy_field(array $field): array
{
    $field['choices'] = nice_hair_get_product_attribute_filter_choices();

    return $field;
}

add_action('acf/init', 'nice_hair_register_acf_fields');
add_filter(
    'acf/load_field/key=field_nh_exclusive_product_form_key',
    'nice_hair_load_exclusive_product_form_override_field'
);
add_filter(
    'acf/load_field/key=field_nh_shop_assortment_selected_forms',
    'nice_hair_load_shop_assortment_selected_forms_field'
);
add_filter(
    'acf/load_field/key=field_nh_custom_hair_available_lengths',
    'nice_hair_load_custom_hair_length_field'
);
add_filter(
    'acf/load_field/key=field_nh_custom_hair_available_qualities',
    'nice_hair_load_custom_hair_quality_field'
);
add_filter(
    'acf/load_field/key=field_nh_custom_hair_available_textures',
    'nice_hair_load_custom_hair_texture_field'
);
add_filter(
    'acf/load_field/key=field_nh_custom_hair_color_group',
    'nice_hair_load_custom_hair_color_group_field'
);
add_filter(
    'acf/load_field/key=field_nh_category_filter_taxonomy',
    'nice_hair_load_product_category_filter_taxonomy_field'
);
