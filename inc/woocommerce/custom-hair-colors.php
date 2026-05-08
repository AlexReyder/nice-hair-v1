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
        return $fallback;
    }

    if (function_exists('nice_hair_get_product_family') && nice_hair_get_product_family($resolved) !== 'custom_hair') {
        return $fallback;
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
        return $fallback;
    }

    return nice_hair_sort_custom_hair_colors(array_values($colors_by_key));
}
