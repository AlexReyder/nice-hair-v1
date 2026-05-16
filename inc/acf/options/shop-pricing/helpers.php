<?php

declare(strict_types=1);

function nice_hair_shop_pricing_options_page_slug(): string
{
    return 'shop-pricing-config';
}

function nice_hair_get_shop_pricing_hair_quality_choices(): array
{
    $default_choices = [
        'Lux'       => 'Lux',
        'Premium'   => 'Premium',
        'Exclusive' => 'Exclusive',
    ];

    if (! taxonomy_exists('pa_hair_quality')) {
        return $default_choices;
    }

    $terms = get_terms([
        'taxonomy'   => 'pa_hair_quality',
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ]);

    if (! is_array($terms) || is_wp_error($terms)) {
        return $default_choices;
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

    return $choices !== [] ? $choices : $default_choices;
}

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