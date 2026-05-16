<?php

declare(strict_types=1);

function nice_hair_shop_pricing_options_page_slug(): string
{
    return 'shop-pricing-config';
}

function nice_hair_get_shop_pricing_hair_quality_fallback_choices(): array
{
    return [
        'Lux'       => 'Lux',
        'Premium'   => 'Premium',
        'Exclusive' => 'Exclusive',
    ];
}

function nice_hair_get_shop_pricing_hair_quality_taxonomy_candidates(): array
{
    $candidates = [
        'pa_hair_quality',
        'pa_hair-quality',
    ];

    if (function_exists('wc_attribute_taxonomy_name')) {
        $candidates[] = wc_attribute_taxonomy_name('hair_quality');
        $candidates[] = wc_attribute_taxonomy_name('hair-quality');
        $candidates[] = wc_attribute_taxonomy_name('Hair Quality');
    }

    if (function_exists('wc_get_attribute_taxonomies')) {
        $attribute_taxonomies = wc_get_attribute_taxonomies();

        if (is_array($attribute_taxonomies)) {
            foreach ($attribute_taxonomies as $attribute_taxonomy) {
                if (! is_object($attribute_taxonomy)) {
                    continue;
                }

                $attribute_name = isset($attribute_taxonomy->attribute_name)
                    ? trim((string) $attribute_taxonomy->attribute_name)
                    : '';

                $attribute_label = isset($attribute_taxonomy->attribute_label)
                    ? trim((string) $attribute_taxonomy->attribute_label)
                    : '';

                $normalized_name = function_exists('nice_hair_normalize_shop_key')
                    ? nice_hair_normalize_shop_key($attribute_name)
                    : str_replace('-', '_', sanitize_title($attribute_name));

                $normalized_label = function_exists('nice_hair_normalize_shop_key')
                    ? nice_hair_normalize_shop_key($attribute_label)
                    : str_replace('-', '_', sanitize_title($attribute_label));

                if (
                    $normalized_name === 'hair_quality'
                    || $normalized_label === 'hair_quality'
                    || strtolower($attribute_label) === 'hair quality'
                ) {
                    $candidates[] = function_exists('wc_attribute_taxonomy_name')
                        ? wc_attribute_taxonomy_name($attribute_name)
                        : 'pa_' . $attribute_name;
                }
            }
        }
    }

    $candidates = array_values(array_unique(array_filter(array_map(
        static fn (mixed $candidate): string => trim((string) $candidate),
        $candidates
    ))));

    return $candidates;
}

function nice_hair_get_shop_pricing_hair_quality_choices(): array
{
    $fallback_choices = nice_hair_get_shop_pricing_hair_quality_fallback_choices();

    foreach (nice_hair_get_shop_pricing_hair_quality_taxonomy_candidates() as $taxonomy) {
        if (! taxonomy_exists($taxonomy)) {
            continue;
        }

        $terms = get_terms([
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]);

        if (! is_array($terms) || is_wp_error($terms)) {
            continue;
        }

        $choices = [];

        foreach ($terms as $term) {
            if (! $term instanceof WP_Term) {
                continue;
            }

            $label = trim((string) $term->name);

            if ($label === '') {
                continue;
            }

            /**
             * Keep label as stored value for backward compatibility:
             * Lux => Lux
             * Premium => Premium
             * New Quality => New Quality
             *
             * The pricing data-model normalizes item_quality later.
             */
            $choices[$label] = $label;
        }

        if ($choices !== []) {
            return $choices;
        }
    }

    return $fallback_choices;
}

function nice_hair_load_shop_pricing_hair_quality_field_choices(array $field): array
{
    $field_key = (string) ($field['key'] ?? '');
    $field_name = (string) ($field['name'] ?? '');

    if (
        $field_key !== 'field_nh_shop_pricing_custom_base_quality'
        && $field_name !== 'item_quality'
    ) {
        return $field;
    }

    $field['choices'] = nice_hair_get_shop_pricing_hair_quality_choices();

    return $field;
}

add_filter(
    'acf/load_field/key=field_nh_shop_pricing_custom_base_quality',
    'nice_hair_load_shop_pricing_hair_quality_field_choices'
);

add_filter(
    'acf/load_field/name=item_quality',
    'nice_hair_load_shop_pricing_hair_quality_field_choices'
);

function nice_hair_get_shop_pricing_default_field_values(): array
{
    if (! function_exists('nice_hair_get_shop_pricing_defaults')) {
        return [];
    }

    $defaults = nice_hair_get_shop_pricing_defaults();

    $form_labels = function_exists('nice_hair_get_default_product_form_labels')
        ? nice_hair_get_default_product_form_labels()
        : [];

    $custom_base_rows = [];

    foreach ((array) ($defaults['custom_hair']['base_prices'] ?? []) as $quality => $lengths) {
        foreach ((array) $lengths as $length => $price) {
            $quality_label = function_exists('nice_hair_humanize_shop_key')
                ? nice_hair_humanize_shop_key((string) $quality)
                : ucwords(str_replace('_', ' ', (string) $quality));

            $custom_base_rows[] = [
                'item_quality'        => $quality_label,
                'item_length'         => (float) $length,
                'item_price_per_gram' => (float) $price,
            ];
        }
    }

    $form_surcharge_rows = [];

    foreach ((array) ($defaults['custom_hair']['form_surcharges'] ?? []) as $form_key => $price) {
        $normalized_key = function_exists('nice_hair_normalize_shop_key')
            ? nice_hair_normalize_shop_key((string) $form_key)
            : sanitize_title((string) $form_key);

        $form_surcharge_rows[] = [
            'item_extension_type' => (string) ($form_labels[$normalized_key] ?? ucwords(str_replace('_', ' ', (string) $normalized_key))),
            'item_price_per_gram' => (float) $price,
        ];
    }

    return [
        'nh_shop_pricing_custom_base_prices' => $custom_base_rows,
        'nh_shop_pricing_form_surcharges'    => $form_surcharge_rows,
    ];
}