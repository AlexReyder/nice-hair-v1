<?php

declare(strict_types=1);

function nice_hair_maybe_bootstrap_shop_pricing_config(): void
{
    if (! function_exists('get_field') || ! function_exists('update_field')) {
        return;
    }

    $pricing_post_id = function_exists('nice_hair_shop_pricing_post_id')
        ? nice_hair_shop_pricing_post_id()
        : 'nh_shop_pricing_config';

    $legacy_post_id = function_exists('nice_hair_header_footer_post_id')
        ? nice_hair_header_footer_post_id('shop')
        : 'nh_header_footer_shop';

    $default_values = nice_hair_get_shop_pricing_default_field_values();

    $field_names = [
        'nh_shop_pricing_custom_base_prices',
        'nh_shop_pricing_form_surcharges',
    ];

    foreach ($field_names as $field_name) {
        $current_value = get_field($field_name, $pricing_post_id);

        if (function_exists('nice_hair_acf_has_value') && nice_hair_acf_has_value($current_value)) {
            continue;
        }

        if (
            ! function_exists('nice_hair_acf_has_value')
            && $current_value !== null
            && $current_value !== false
            && $current_value !== ''
            && $current_value !== []
        ) {
            continue;
        }

        $legacy_value = get_field($field_name, $legacy_post_id);
        $value_to_store = null;

        if (function_exists('nice_hair_acf_has_value') && nice_hair_acf_has_value($legacy_value)) {
            $value_to_store = $legacy_value;
        } elseif (
            ! function_exists('nice_hair_acf_has_value')
            && $legacy_value !== null
            && $legacy_value !== false
            && $legacy_value !== ''
            && $legacy_value !== []
        ) {
            $value_to_store = $legacy_value;
        } else {
            $value_to_store = $default_values[$field_name] ?? null;
        }

        if ($value_to_store === null || $value_to_store === []) {
            continue;
        }

        update_field($field_name, $value_to_store, $pricing_post_id);
    }
}

add_action('acf/init', 'nice_hair_maybe_bootstrap_shop_pricing_config', 30);