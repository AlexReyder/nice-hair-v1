<?php

declare(strict_types=1);

/**
 * Custom Hair color library data access.
 *
 * The product template still receives the same color option shape as before:
 * key, label, value, group_key, main_image, preview_image, lightbox_index.
 */

function nice_hair_get_custom_hair_colors_post_id(): string
{
    return function_exists('nice_hair_custom_hair_colors_post_id')
        ? nice_hair_custom_hair_colors_post_id()
        : 'nh_custom_hair_colors';
}

function nice_hair_normalize_custom_hair_color_key(string $key): string
{
    return function_exists('nice_hair_normalize_shop_key')
        ? nice_hair_normalize_shop_key($key)
        : str_replace('-', '_', sanitize_title($key));
}

function nice_hair_normalize_custom_hair_color_image(mixed $image): ?array
{
    if (is_array($image)) {
        $id = isset($image['ID']) && is_numeric($image['ID'])
            ? (int) $image['ID']
            : (isset($image['id']) && is_numeric($image['id']) ? (int) $image['id'] : 0);

        $url = trim((string) ($image['url'] ?? ''));

        if ($id <= 0 && $url === '') {
            return null;
        }

        return [
            'ID'  => $id,
            'url' => $url,
            'alt' => trim((string) ($image['alt'] ?? '')),
        ];
    }

    if (is_numeric($image)) {
        $id = (int) $image;

        if ($id <= 0) {
            return null;
        }

        return [
            'ID'  => $id,
            'url' => (string) wp_get_attachment_image_url($id, 'full'),
            'alt' => (string) get_post_meta($id, '_wp_attachment_image_alt', true),
        ];
    }

    return null;
}

function nice_hair_normalize_custom_hair_color_row(array $row, string $source = 'global'): ?array
{
    $key = nice_hair_normalize_custom_hair_color_key((string) ($row['color_key'] ?? $row['key'] ?? ''));
    $label = trim((string) ($row['color_label'] ?? $row['label'] ?? ''));

    if ($key === '' || $label === '') {
        return null;
    }

    $main_image = nice_hair_normalize_custom_hair_color_image($row['main_image'] ?? null);

    if (! is_array($main_image)) {
        return null;
    }

    $preview_image = nice_hair_normalize_custom_hair_color_image($row['preview_image'] ?? null) ?: $main_image;
    $sort_order = isset($row['sort_order']) && is_numeric($row['sort_order'])
        ? (int) $row['sort_order']
        : 100;

    return [
        'key'           => $key,
        'label'         => $label,
        'value'         => trim((string) ($row['color_value'] ?? $row['value'] ?? '')) ?: $label,
        'group_key'     => nice_hair_normalize_custom_hair_color_key((string) ($row['color_group'] ?? $row['group_key'] ?? '')),
        'preview_image' => $preview_image,
        'main_image'    => $main_image,
        'sort_order'    => $sort_order,
        'source'        => $source,
    ];
}

function nice_hair_sort_custom_hair_colors(array $colors): array
{
    usort($colors, static function (array $left, array $right): int {
        $left_order = isset($left['sort_order']) && is_numeric($left['sort_order']) ? (int) $left['sort_order'] : 100;
        $right_order = isset($right['sort_order']) && is_numeric($right['sort_order']) ? (int) $right['sort_order'] : 100;

        if ($left_order === $right_order) {
            return strcmp((string) ($left['label'] ?? ''), (string) ($right['label'] ?? ''));
        }

        return $left_order <=> $right_order;
    });

    return array_values($colors);
}

function nice_hair_get_custom_hair_global_color_options(bool $active_only = true): array
{
    if (! function_exists('get_field')) {
        return [];
    }

    $rows = get_field('nh_custom_hair_global_colors', nice_hair_get_custom_hair_colors_post_id());

    if (! is_array($rows)) {
        return [];
    }

    $colors = [];

    foreach ($rows as $row) {
        if (! is_array($row)) {
            continue;
        }

        $is_active = array_key_exists('is_active', $row) ? (bool) $row['is_active'] : true;

        if ($active_only && ! $is_active) {
            continue;
        }

        $color = nice_hair_normalize_custom_hair_color_row($row, 'global');

        if (! is_array($color)) {
            continue;
        }

        $colors[$color['key']] = $color;
    }

    return nice_hair_sort_custom_hair_colors(array_values($colors));
}

function nice_hair_get_product_custom_hair_selected_color_keys(WC_Product|int|null $product = null): array
{
    $resolved = function_exists('nice_hair_resolve_product')
        ? nice_hair_resolve_product($product)
        : null;

    if (! $resolved instanceof WC_Product || ! function_exists('get_field')) {
        return [];
    }

    $values = get_field('nh_custom_hair_selected_global_colors', $resolved->get_id());
    $values = is_array($values) ? $values : [];
    $keys = [];

    foreach ($values as $value) {
        $key = nice_hair_normalize_custom_hair_color_key((string) $value);

        if ($key !== '') {
            $keys[] = $key;
        }
    }

    return array_values(array_unique($keys));
}

function nice_hair_get_product_custom_hair_local_color_options(WC_Product|int|null $product = null): array
{
    $resolved = function_exists('nice_hair_resolve_product')
        ? nice_hair_resolve_product($product)
        : null;

    if (! $resolved instanceof WC_Product || ! function_exists('get_field')) {
        return [];
    }

    $rows = get_field('nh_custom_hair_local_colors', $resolved->get_id());

    if (! is_array($rows)) {
        return [];
    }

    $colors = [];

    foreach ($rows as $row) {
        if (! is_array($row)) {
            continue;
        }

        $color = nice_hair_normalize_custom_hair_color_row($row, 'local');

        if (! is_array($color)) {
            continue;
        }

        $colors[$color['key']] = $color;
    }

    return nice_hair_sort_custom_hair_colors(array_values($colors));
}

function nice_hair_get_product_custom_hair_color_options(WC_Product|int|null $product = null, array $fallback = []): array
{
    $resolved = function_exists('nice_hair_resolve_product')
        ? nice_hair_resolve_product($product)
        : null;

    if (! $resolved instanceof WC_Product) {
        return [];
    }

    if (function_exists('nice_hair_get_product_family') && nice_hair_get_product_family($resolved) !== 'custom_hair') {
        return [];
    }

    $global_colors = nice_hair_get_custom_hair_global_color_options(true);
    $selected_keys = nice_hair_get_product_custom_hair_selected_color_keys($resolved);
    $local_colors = nice_hair_get_product_custom_hair_local_color_options($resolved);

    $colors_by_key = [];

    if ($selected_keys !== []) {
        $selected_lookup = array_flip($selected_keys);

        foreach ($global_colors as $color) {
            $key = (string) ($color['key'] ?? '');

            if ($key !== '' && isset($selected_lookup[$key])) {
                $colors_by_key[$key] = $color;
            }
        }
    }

    foreach ($local_colors as $color) {
        $key = (string) ($color['key'] ?? '');

        if ($key !== '') {
            $colors_by_key[$key] = $color;
        }
    }

    if ($colors_by_key === []) {
        return [];
    }

    return nice_hair_sort_custom_hair_colors(array_values($colors_by_key));
}

function nice_hair_remove_legacy_custom_hair_color_options_acf_group(): void
{
    if (! function_exists('acf_get_local_field_groups') || ! function_exists('acf_remove_local_field_group')) {
        return;
    }

    foreach (acf_get_local_field_groups() as $group) {
        if (! is_array($group)) {
            continue;
        }

        $group_key = (string) ($group['key'] ?? '');
        $group_title = (string) ($group['title'] ?? '');

        if ($group_key === '') {
            continue;
        }

        $is_legacy_custom_hair_colors_group = str_contains($group_title, 'Custom Hair')
            && (
                str_contains($group_title, 'цветовые опции')
                || str_contains($group_title, 'Color options')
                || str_contains($group_title, 'color options')
            );

        if (! $is_legacy_custom_hair_colors_group && function_exists('acf_get_local_fields')) {
            $fields = acf_get_local_fields($group_key);

            if (is_array($fields)) {
                foreach ($fields as $field) {
                    if (! is_array($field)) {
                        continue;
                    }

                    $field_name = (string) ($field['name'] ?? '');

                    if (in_array($field_name, [
                        'nh_custom_hair_color_options',
                        'nh_custom_hair_colors',
                        'nh_custom_hair_product_colors',
                    ], true)) {
                        $is_legacy_custom_hair_colors_group = true;
                        break;
                    }
                }
            }
        }

        if ($is_legacy_custom_hair_colors_group) {
            acf_remove_local_field_group($group_key);
        }
    }
}
add_action('acf/init', 'nice_hair_remove_legacy_custom_hair_color_options_acf_group', 1000);

function nice_hair_get_custom_hair_request_value(string $key): string
{
    if (! isset($_REQUEST[$key])) {
        return '';
    }

    $value = wp_unslash($_REQUEST[$key]);

    if (is_array($value)) {
        return '';
    }

    return wc_clean((string) $value);
}

function nice_hair_find_custom_hair_config_option_by_key(array $options, string $key): ?array
{
    $normalized_key = nice_hair_normalize_custom_hair_color_key($key);

    if ($normalized_key === '') {
        return null;
    }

    foreach ($options as $option) {
        if (! is_array($option)) {
            continue;
        }

        $option_key = nice_hair_normalize_custom_hair_color_key((string) ($option['key'] ?? ''));

        if ($option_key === $normalized_key) {
            return $option;
        }
    }

    return null;
}

function nice_hair_find_custom_hair_config_option_or_first(array $options, string $key): ?array
{
    $option = nice_hair_find_custom_hair_config_option_by_key($options, $key);

    if (is_array($option)) {
        return $option;
    }

    foreach ($options as $fallback_option) {
        if (is_array($fallback_option) && trim((string) ($fallback_option['key'] ?? '')) !== '') {
            return $fallback_option;
        }
    }

    return null;
}

function nice_hair_get_custom_hair_config_option_label(?array $option): string
{
    if (! is_array($option)) {
        return '';
    }

    $value = trim((string) ($option['value'] ?? ''));

    if ($value !== '') {
        return $value;
    }

    return trim((string) ($option['label'] ?? ''));
}

function nice_hair_normalize_custom_hair_weight_value(mixed $raw_weight, array $weight_config): int
{
    $min = isset($weight_config['min']) && is_numeric($weight_config['min'])
        ? (int) $weight_config['min']
        : 30;

    $step = isset($weight_config['step']) && is_numeric($weight_config['step'])
        ? (int) $weight_config['step']
        : 10;

    $fallback = isset($weight_config['default']) && is_numeric($weight_config['default'])
        ? (int) $weight_config['default']
        : $min;

    if ($step <= 0) {
        $step = 10;
    }

    $weight = is_numeric($raw_weight) ? (int) $raw_weight : $fallback;
    $weight = max($weight, $min);

    return max((int) (round(($weight - $min) / $step) * $step + $min), $min);
}

function nice_hair_get_custom_hair_selection_values_from_configurator(array $configurator): array
{
    $selection = is_array($configurator['selection'] ?? null) ? $configurator['selection'] : [];
    $values = is_array($selection['selection'] ?? null) ? $selection['selection'] : [];
    $defaults = is_array($configurator['default_selection'] ?? null) ? $configurator['default_selection'] : [];

    return array_replace($defaults, $values);
}

function nice_hair_resolve_custom_hair_posted_configuration(WC_Product $product): array|WP_Error
{
    if (! function_exists('nice_hair_get_custom_hair_configurator')) {
        return new WP_Error(
            'nh_custom_hair_configurator_missing',
            __('Custom Hair configuration is unavailable.', 'nice-hair')
        );
    }

    $configurator = nice_hair_get_custom_hair_configurator($product);

    if (! is_array($configurator)) {
        return new WP_Error(
            'nh_custom_hair_configurator_invalid',
            __('Custom Hair configuration is unavailable.', 'nice-hair')
        );
    }

    $selection_defaults = nice_hair_get_custom_hair_selection_values_from_configurator($configurator);

    $color_options = nice_hair_get_product_custom_hair_color_options($product, []);
    $length_options = is_array($configurator['length_options'] ?? null) ? $configurator['length_options'] : [];
    $quality_options = is_array($configurator['quality_options'] ?? null) ? $configurator['quality_options'] : [];
    $texture_options = is_array($configurator['texture_options'] ?? null) ? $configurator['texture_options'] : [];
    $weight_config = is_array($configurator['weight_config'] ?? null) ? $configurator['weight_config'] : [];

    $posted_color = nice_hair_get_custom_hair_request_value('nh_custom_hair_color');
    $posted_length = nice_hair_get_custom_hair_request_value('nh_custom_hair_length');
    $posted_quality = nice_hair_get_custom_hair_request_value('nh_custom_hair_quality');
    $posted_texture = nice_hair_get_custom_hair_request_value('nh_custom_hair_texture');
    $posted_weight = nice_hair_get_custom_hair_request_value('nh_custom_hair_weight');

    $color_option = null;

    if ($color_options !== []) {
        $color_option = $posted_color !== ''
            ? nice_hair_find_custom_hair_config_option_by_key($color_options, $posted_color)
            : nice_hair_find_custom_hair_config_option_or_first($color_options, (string) ($selection_defaults['color'] ?? ''));

        if (! is_array($color_option)) {
            return new WP_Error(
                'nh_custom_hair_color_unavailable',
                __('The selected Custom Hair color is no longer available for this product.', 'nice-hair')
            );
        }
    }

    $length_option = nice_hair_find_custom_hair_config_option_or_first(
        $length_options,
        $posted_length !== '' ? $posted_length : (string) ($selection_defaults['length'] ?? '')
    );

    $quality_option = nice_hair_find_custom_hair_config_option_or_first(
        $quality_options,
        $posted_quality !== '' ? $posted_quality : (string) ($selection_defaults['quality'] ?? '')
    );

    $texture_option = nice_hair_find_custom_hair_config_option_or_first(
        $texture_options,
        $posted_texture !== '' ? $posted_texture : (string) ($selection_defaults['texture'] ?? '')
    );

    if (! is_array($length_option) || ! is_array($quality_option)) {
        return new WP_Error(
            'nh_custom_hair_required_options_missing',
            __('Custom Hair pricing is incomplete for this item. Please contact us for assistance.', 'nice-hair')
        );
    }

    $length_key = (string) ($length_option['key'] ?? '');
    $quality_key = (string) ($quality_option['key'] ?? '');
    $texture_key = is_array($texture_option) ? (string) ($texture_option['key'] ?? '') : '';
    $weight = nice_hair_normalize_custom_hair_weight_value($posted_weight, $weight_config);

    $base_price_map = is_array($configurator['base_price_map'] ?? null) ? $configurator['base_price_map'] : [];
    $base_price = $base_price_map[$quality_key][$length_key] ?? null;

    if (! is_numeric($base_price) && function_exists('nice_hair_get_custom_hair_base_price_per_gram')) {
        $base_price = nice_hair_get_custom_hair_base_price_per_gram($quality_key, $length_key);
    }

    $product_form = is_array($configurator['product_form'] ?? null) ? $configurator['product_form'] : [];
    $product_form_key = (string) ($product_form['key'] ?? '');
    $product_form_label = trim((string) ($product_form['label'] ?? ''));
    $surcharge = $product_form['surcharge_per_gram'] ?? null;

    if (! is_numeric($surcharge) && function_exists('nice_hair_get_product_form_surcharge_per_gram')) {
        $surcharge = nice_hair_get_product_form_surcharge_per_gram($product_form_key);
    }

    if (! is_numeric($base_price) || ! is_numeric($surcharge)) {
        return new WP_Error(
            'nh_custom_hair_price_unavailable',
            __('Custom Hair pricing is incomplete for this item. Please contact us for assistance.', 'nice-hair')
        );
    }

    $total_price = round(((float) $base_price + (float) $surcharge) * $weight, 2);

    if ($total_price <= 0) {
        return new WP_Error(
            'nh_custom_hair_price_invalid',
            __('Custom Hair pricing is incomplete for this item. Please contact us for assistance.', 'nice-hair')
        );
    }

    return [
        'color_key'          => is_array($color_option) ? (string) ($color_option['key'] ?? '') : '',
        'color_label'        => nice_hair_get_custom_hair_config_option_label($color_option),
        'length_key'         => $length_key,
        'length_label'       => nice_hair_get_custom_hair_config_option_label($length_option),
        'quality_key'        => $quality_key,
        'quality_label'      => nice_hair_get_custom_hair_config_option_label($quality_option),
        'texture_key'        => $texture_key,
        'texture_label'      => nice_hair_get_custom_hair_config_option_label($texture_option),
        'weight_grams'       => $weight,
        'product_form_key'   => $product_form_key,
        'product_form_label' => $product_form_label,
        'total_price'        => $total_price,
    ];
}

function nice_hair_custom_hair_add_to_cart_validation(bool $passed, int $product_id, int $quantity = 1): bool
{
    if (! function_exists('wc_get_product')) {
        return $passed;
    }

    $product = wc_get_product($product_id);

    if (
        ! $product instanceof WC_Product
        || ! function_exists('nice_hair_get_product_family')
        || nice_hair_get_product_family($product) !== 'custom_hair'
    ) {
        return $passed;
    }

    if (! $product->is_purchasable() || ! $product->is_in_stock()) {
        return $passed;
    }

    $configuration = nice_hair_resolve_custom_hair_posted_configuration($product);

    if ($configuration instanceof WP_Error) {
        wc_add_notice($configuration->get_error_message(), 'error');
        return false;
    }

    if (! $passed && function_exists('wc_clear_notices')) {
        wc_clear_notices();
    }

    return true;
}
add_filter('woocommerce_add_to_cart_validation', 'nice_hair_custom_hair_add_to_cart_validation', 9999, 3);

function nice_hair_custom_hair_add_cart_item_data(array $cart_item_data, int $product_id): array
{
    if (! function_exists('wc_get_product')) {
        return $cart_item_data;
    }

    $product = wc_get_product($product_id);

    if (
        ! $product instanceof WC_Product
        || ! function_exists('nice_hair_get_product_family')
        || nice_hair_get_product_family($product) !== 'custom_hair'
    ) {
        return $cart_item_data;
    }

    $configuration = nice_hair_resolve_custom_hair_posted_configuration($product);

    if ($configuration instanceof WP_Error) {
        return $cart_item_data;
    }

    $cart_item_data['nh_custom_hair_color'] = $configuration['color_key'];
    $cart_item_data['nh_custom_hair_color_label'] = $configuration['color_label'];
    $cart_item_data['nh_custom_hair_length'] = $configuration['length_key'];
    $cart_item_data['nh_custom_hair_length_label'] = $configuration['length_label'];
    $cart_item_data['nh_custom_hair_quality'] = $configuration['quality_key'];
    $cart_item_data['nh_custom_hair_quality_label'] = $configuration['quality_label'];
    $cart_item_data['nh_custom_hair_texture'] = $configuration['texture_key'];
    $cart_item_data['nh_custom_hair_texture_label'] = $configuration['texture_label'];
    $cart_item_data['nh_custom_hair_weight_grams'] = $configuration['weight_grams'];
    $cart_item_data['nh_custom_hair_product_form'] = $configuration['product_form_key'];
    $cart_item_data['nh_custom_hair_product_form_label'] = $configuration['product_form_label'];
    $cart_item_data['nh_custom_hair_total_price'] = $configuration['total_price'];
    $cart_item_data['nh_custom_hair_price'] = $configuration['total_price'];

    $cart_item_data['nh_custom_hair_config_hash'] = md5(wp_json_encode([
        $configuration['color_key'],
        $configuration['length_key'],
        $configuration['quality_key'],
        $configuration['texture_key'],
        $configuration['weight_grams'],
        $configuration['product_form_key'],
        $configuration['total_price'],
    ]));

    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'nice_hair_custom_hair_add_cart_item_data', 9999, 2);

function nice_hair_apply_custom_hair_cart_item_prices(WC_Cart $cart): void
{
    if (is_admin() && ! wp_doing_ajax()) {
        return;
    }

    foreach ($cart->get_cart() as $cart_item) {
        $product = $cart_item['data'] ?? null;
        $price = $cart_item['nh_custom_hair_total_price'] ?? $cart_item['nh_custom_hair_price'] ?? null;

        if (! $product instanceof WC_Product || ! is_numeric($price)) {
            continue;
        }

        $product->set_price((float) $price);
    }
}
add_action('woocommerce_before_calculate_totals', 'nice_hair_apply_custom_hair_cart_item_prices', 9999);