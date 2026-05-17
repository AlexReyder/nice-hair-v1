<?php

declare(strict_types=1);

/**
 * Shop data-model helpers.
 *
 * This layer keeps product family detection, unique-item semantics,
 * and pricing config access in one place so archive/single templates
 * can rely on structured data instead of parsing product titles.
 */

function nice_hair_resolve_product(WC_Product|int|null $product = null): ?WC_Product
{
    if ($product instanceof WC_Product) {
        return $product;
    }

    if (is_int($product) && $product > 0 && function_exists('wc_get_product')) {
        $resolved = wc_get_product($product);

        return $resolved instanceof WC_Product ? $resolved : null;
    }

    if ($product === null && function_exists('get_the_ID') && function_exists('wc_get_product')) {
        $post_id = (int) get_the_ID();

        if ($post_id > 0) {
            $resolved = wc_get_product($post_id);

            return $resolved instanceof WC_Product ? $resolved : null;
        }
    }

    return null;
}

function nice_hair_normalize_shop_key(string $value): string
{
    return str_replace('-', '_', sanitize_title($value));
}

function nice_hair_get_shop_family_aliases(): array
{
    return [
        'keratin' => [
            'keratin',
            'italian_gel_keratin',
            'pigmented_keratin',
        ],
        'tools' => [
            'tools',
        ],
        'ready_to_install' => [
            'ready_to_install',
            'ready_to_install_hair_extensions',
        ],
        'exclusive_hair' => [
            'exclusive_hair',
        ],
        'custom_hair' => [
            'custom_hair',
            'custom_hair_extensions',
            'hair_and_custom_hair_extensions',
        ],
    ];
}

function nice_hair_get_product_family(WC_Product|int|null $product = null): string
{
    $resolved = nice_hair_resolve_product($product);

    if (! $resolved instanceof WC_Product) {
        return '';
    }

    $terms = wp_get_post_terms($resolved->get_id(), 'product_cat');

    if (! is_array($terms) || $terms === []) {
        return '';
    }

    $term_keys = [];

    foreach ($terms as $term) {
        if (! $term instanceof WP_Term) {
            continue;
        }

        $term_keys[] = nice_hair_normalize_shop_key($term->slug);
        $term_keys[] = nice_hair_normalize_shop_key($term->name);
    }

    $term_keys = array_values(array_unique(array_filter($term_keys)));

    foreach (nice_hair_get_shop_family_aliases() as $family => $aliases) {
        foreach ($aliases as $alias) {
            if (in_array($alias, $term_keys, true)) {
                return $family;
            }
        }
    }

    return '';
}

function nice_hair_product_is_family(string $family, WC_Product|int|null $product = null): bool
{
    return nice_hair_get_product_family($product) === $family;
}

function nice_hair_get_product_archive_sku(WC_Product|int|null $product = null): string
{
    $resolved = nice_hair_resolve_product($product);

    if (! $resolved instanceof WC_Product) {
        return '';
    }

    $sku = trim((string) $resolved->get_sku());

    if ($sku !== '') {
        return $sku;
    }

    if (! $resolved instanceof WC_Product_Variable) {
        return '';
    }

    $variation_ids = method_exists($resolved, 'get_visible_children')
        ? $resolved->get_visible_children()
        : $resolved->get_children();

    foreach ($variation_ids as $variation_id) {
        $variation = wc_get_product((int) $variation_id);

        if (! $variation instanceof WC_Product) {
            continue;
        }

        $variation_sku = trim((string) $variation->get_sku());

        if ($variation_sku !== '') {
            return $variation_sku;
        }
    }

    return '';
}

function nice_hair_get_product_variation_attribute_labels(WC_Product|int|null $product, string $taxonomy): array
{
    $resolved = nice_hair_resolve_product($product);

    if (! $resolved instanceof WC_Product_Variable) {
        return [];
    }

    $taxonomy = wc_attribute_taxonomy_name(str_replace('pa_', '', sanitize_title($taxonomy)));
    $variation_attributes = $resolved->get_variation_attributes();
    $raw_options = $variation_attributes[$taxonomy] ?? $variation_attributes['attribute_' . $taxonomy] ?? [];

    if (! is_array($raw_options) || $raw_options === []) {
        return [];
    }

    $labels = [];

    foreach ($raw_options as $raw_option) {
        $raw_option = trim((string) $raw_option);

        if ($raw_option === '') {
            continue;
        }

        $label = $raw_option;

        if (taxonomy_exists($taxonomy)) {
            $term = get_term_by('slug', $raw_option, $taxonomy);

            if (! $term instanceof WP_Term) {
                $term = get_term_by('name', $raw_option, $taxonomy);
            }

            if ($term instanceof WP_Term) {
                $label = (string) $term->name;
            }
        }

        $labels[] = $label;
    }

    return array_values(array_unique(array_filter($labels)));
}

function nice_hair_get_product_min_price(WC_Product|int|null $product = null): ?float
{
    $resolved = nice_hair_resolve_product($product);

    if (! $resolved instanceof WC_Product) {
        return null;
    }

    $price = $resolved instanceof WC_Product_Variable
        ? $resolved->get_variation_price('min', true)
        : $resolved->get_price();

    if ($price === '' || ! is_numeric($price)) {
        return null;
    }

    return (float) $price;
}

function nice_hair_get_default_unique_families(): array
{
    return [
        'ready_to_install',
        'exclusive_hair',
    ];
}

function nice_hair_is_unique_item_product(WC_Product|int|null $product = null): bool
{
    $resolved = nice_hair_resolve_product($product);

    if (! $resolved instanceof WC_Product) {
        return false;
    }

    $family = nice_hair_get_product_family($resolved);

    if (function_exists('get_field') && get_field('nh_unique_item', $resolved->get_id())) {
        return true;
    }

    return in_array($family, nice_hair_get_default_unique_families(), true);
}

function nice_hair_get_shop_pricing_source_post_id(): string
{
    if (function_exists('nice_hair_shop_pricing_post_id')) {
        return nice_hair_shop_pricing_post_id();
    }

    return 'nh_shop_pricing_config';
}

function nice_hair_get_shop_option_field(string $field_name, mixed $fallback = null): mixed
{
    if (! function_exists('get_field')) {
        return $fallback;
    }

    $value = get_field($field_name, nice_hair_get_shop_pricing_source_post_id());

    if (function_exists('nice_hair_acf_has_value') && nice_hair_acf_has_value($value)) {
        return $value;
    }

    if (! function_exists('nice_hair_acf_has_value') && $value !== null && $value !== false && $value !== '' && $value !== []) {
        return $value;
    }

    $legacy_post_id = function_exists('nice_hair_header_footer_post_id')
        ? nice_hair_header_footer_post_id('shop')
        : 'nh_header_footer_shop';
    $legacy_value = get_field($field_name, $legacy_post_id);

    if (function_exists('nice_hair_acf_has_value') && nice_hair_acf_has_value($legacy_value)) {
        return $legacy_value;
    }

    if (! function_exists('nice_hair_acf_has_value') && $legacy_value !== null && $legacy_value !== false && $legacy_value !== '' && $legacy_value !== []) {
        return $legacy_value;
    }

    return $fallback;
}

function nice_hair_get_shop_pricing_defaults(): array
{
    return [
        'keratin' => [
            'pigmented' => [
                '10g' => 5.0,
                '50g' => 16.0,
                '100g' => 27.0,
            ],
            'italian_standard' => [
                '5g' => 3.0,
                '10g' => 5.0,
                '50g' => 16.0,
                '100g' => 27.0,
                '1000g' => 160.0,
            ],
            'transparent' => [
                '5g' => 3.0,
                '10g' => 4.0,
                '50g' => 13.0,
                '100g' => 21.0,
                '1000g' => 134.0,
            ],
        ],
        'custom_hair' => [
            'base_prices' => [
                'lux' => [
                    '40' => 2.25,
                    '50' => 2.45,
                    '60' => 2.65,
                    '70' => 3.05,
                    '80' => 3.45,
                ],
                'premium' => [
                    '40' => 2.45,
                    '50' => 3.25,
                    '60' => 3.55,
                    '70' => 3.85,
                    '80' => 4.25,
                ],
            ],
            'form_surcharges' => [
                'biotape' => 0.66,
                'bulk' => 0.0,
                'double_layer_hand_tied_weft_with_clip_in' => 1.3,
                'flat_keratin_bonds' => 0.4,
                'flat_weft' => 1.02,
                'genius_weft' => 1.02,
                'hand_tied_weft' => 1.2,
                'i_tip_round_tips' => 0.52,
                'invisible_tapes' => 0.8,
                'machine_weft_clip_in' => 1.0,
                'machine_weft' => 0.86,
                'mini_tapes_butterflies' => 0.66,
                'nano_bonds_with_metal_round_tip' => 0.72,
                'nano_bonds_with_metal_straight_tip' => 0.72,
                'nano_bonds_with_plastic_tip' => 0.62,
                'nano_bonds_with_thread_tip' => 0.72,
                'ponytail_with_ribbon' => 1.0,
                'usual_tapes' => 0.66,
            ],
        ],
    ];
}

function nice_hair_parse_shop_price_table(mixed $rows, array $fallback): array
{
    if (! is_array($rows) || $rows === []) {
        return $fallback;
    }

    $result = [];

    foreach ($rows as $row) {
        if (! is_array($row)) {
            continue;
        }

        $weight = isset($row['item_weight']) ? trim((string) $row['item_weight']) : '';
        $price = $row['item_price'] ?? null;

        if ($weight === '' || ! is_numeric($price)) {
            continue;
        }

        $result[$weight] = (float) $price;
    }

    return $result !== [] ? $result : $fallback;
}

function nice_hair_parse_shop_base_price_rows(mixed $rows, array $fallback): array
{
    if (! is_array($rows) || $rows === []) {
        return $fallback;
    }

    $result = [];

    foreach ($rows as $row) {
        if (! is_array($row)) {
            continue;
        }

        $quality = isset($row['item_quality']) ? nice_hair_normalize_shop_key((string) $row['item_quality']) : '';
        $length = isset($row['item_length']) ? trim((string) $row['item_length']) : '';
        $price = $row['item_price_per_gram'] ?? null;

        if ($quality === '' || $length === '' || ! is_numeric($price)) {
            continue;
        }

        $result[$quality][$length] = (float) $price;
    }

    return $result !== [] ? $result : $fallback;
}

function nice_hair_parse_shop_form_surcharge_rows(mixed $rows, array $fallback): array
{
    if (! is_array($rows) || $rows === []) {
        return $fallback;
    }

    $result = [];

    foreach ($rows as $row) {
        if (! is_array($row)) {
            continue;
        }

        $extension_type = isset($row['item_extension_type']) ? trim((string) $row['item_extension_type']) : '';
        $price = $row['item_price_per_gram'] ?? null;

        if ($extension_type === '' || ! is_numeric($price)) {
            continue;
        }

        $result[nice_hair_normalize_shop_key($extension_type)] = (float) $price;
    }

    return $result !== [] ? $result : $fallback;
}

function nice_hair_get_shop_pricing_config(): array
{
    static $config = null;

    if (is_array($config)) {
        return $config;
    }

    $defaults = nice_hair_get_shop_pricing_defaults();

    $config = [
        'keratin' => [
            'pigmented' => nice_hair_parse_shop_price_table(
                nice_hair_get_shop_option_field('nh_shop_pricing_keratin_pigmented', []),
                $defaults['keratin']['pigmented']
            ),
            'italian_standard' => nice_hair_parse_shop_price_table(
                nice_hair_get_shop_option_field('nh_shop_pricing_keratin_italian_standard', []),
                $defaults['keratin']['italian_standard']
            ),
            'transparent' => nice_hair_parse_shop_price_table(
                nice_hair_get_shop_option_field('nh_shop_pricing_keratin_transparent', []),
                $defaults['keratin']['transparent']
            ),
        ],
        'custom_hair' => [
            'base_prices' => nice_hair_parse_shop_base_price_rows(
                nice_hair_get_shop_option_field('nh_shop_pricing_custom_base_prices', []),
                $defaults['custom_hair']['base_prices']
            ),
            'form_surcharges' => nice_hair_parse_shop_form_surcharge_rows(
                nice_hair_get_shop_option_field('nh_shop_pricing_form_surcharges', []),
                $defaults['custom_hair']['form_surcharges']
            ),
        ],
    ];

    return $config;
}

function nice_hair_get_keratin_price_table(string $type): array
{
    $config = nice_hair_get_shop_pricing_config();

    return $config['keratin'][$type] ?? [];
}

function nice_hair_get_custom_hair_base_price_per_gram(string $quality, string|int $length): ?float
{
    $config = nice_hair_get_shop_pricing_config();
    $quality_key = nice_hair_normalize_shop_key($quality);
    $length_key = trim((string) $length);

    return isset($config['custom_hair']['base_prices'][$quality_key][$length_key])
        ? (float) $config['custom_hair']['base_prices'][$quality_key][$length_key]
        : null;
}

function nice_hair_get_product_form_surcharge_per_gram(string $extension_type): ?float
{
    $config = nice_hair_get_shop_pricing_config();
    $type_key = nice_hair_normalize_shop_key($extension_type);

    return isset($config['custom_hair']['form_surcharges'][$type_key])
        ? (float) $config['custom_hair']['form_surcharges'][$type_key]
        : null;
}

function nice_hair_humanize_shop_key(string $key): string
{
    $key = trim($key);

    if ($key === '') {
        return '';
    }

    $label = str_replace('_', ' ', $key);
    $label = preg_replace('/\s+/', ' ', $label);

    return ucwords((string) $label);
}

function nice_hair_get_product_numeric_meta_value(WC_Product|int|null $product = null, string $field_name = ''): ?float
{
    $resolved = nice_hair_resolve_product($product);

    if (! $resolved instanceof WC_Product || $field_name === '') {
        return null;
    }

    $value = function_exists('get_field')
        ? get_field($field_name, $resolved->get_id())
        : get_post_meta($resolved->get_id(), $field_name, true);

    if (! is_numeric($value)) {
        return null;
    }

    $numeric_value = (float) $value;

    return $numeric_value > 0 ? $numeric_value : null;
}

function nice_hair_get_product_base_lot_price(WC_Product|int|null $product = null): ?float
{
    return nice_hair_get_product_numeric_meta_value($product, 'nh_base_lot_price');
}

function nice_hair_get_product_fixed_weight_grams(WC_Product|int|null $product = null): ?float
{
    return nice_hair_get_product_numeric_meta_value($product, 'nh_fixed_weight_grams');
}

function nice_hair_get_default_product_form_labels(): array
{
    return [
        'biotape'                                  => 'Biotape',
        'bulk'                                     => 'Bulk',
        'double_layer_hand_tied_weft_with_clip_in' => 'Double layer hand tied weft with clip in',
        'flat_keratin_bonds'                       => 'Flat keratin bonds',
        'flat_weft'                                => 'Flat weft',
        'genius_weft'                              => 'Genius weft',
        'hand_tied_weft'                           => 'Hand-tied weft',
        'i_tip_round_tips'                         => 'I-tip round tips',
        'invisible_tapes'                          => 'Invisible tapes',
        'machine_weft_clip_in'                     => 'Machine weft clip in',
        'machine_weft'                             => 'Machine weft',
        'mini_tapes_butterflies'                   => 'Mini tapes "butterflies"',
        'nano_bonds_with_metal_round_tip'          => 'Nano bonds with metal round tip',
        'nano_bonds_with_metal_straight_tip'       => 'Nano bonds with metal straight tip',
        'nano_bonds_with_plastic_tip'              => 'Nano bonds with plastic tip',
        'nano_bonds_with_thread_tip'               => 'Nano bonds with thread tip',
        'ponytail_with_ribbon'                     => 'Ponytail with ribbon',
        'usual_tapes'                              => 'Usual tapes',
    ];
}

function nice_hair_get_extension_type_term_for_product_form(string $form_key = '', string $label = ''): ?WP_Term
{
    $normalized_key = nice_hair_normalize_shop_key($form_key);
    $candidate_labels = array_values(array_unique(array_filter([
        trim($label),
        nice_hair_get_default_product_form_labels()[$normalized_key] ?? '',
    ])));
    $candidate_slugs = array_values(array_unique(array_filter([
        sanitize_title($label),
        str_replace('_', '-', $normalized_key),
    ])));

    foreach ($candidate_labels as $candidate_label) {
        $term = get_term_by('name', $candidate_label, 'pa_extension_type');

        if ($term instanceof WP_Term) {
            return $term;
        }
    }

    foreach ($candidate_slugs as $candidate_slug) {
        $term = get_term_by('slug', $candidate_slug, 'pa_extension_type');

        if ($term instanceof WP_Term) {
            return $term;
        }
    }

    if ($normalized_key === '') {
        return null;
    }

    $terms = get_terms([
        'taxonomy'   => 'pa_extension_type',
        'hide_empty' => false,
    ]);

    if (! is_array($terms)) {
        return null;
    }

    foreach ($terms as $term) {
        if (! $term instanceof WP_Term) {
            continue;
        }

        $term_keys = [
            nice_hair_normalize_shop_key((string) $term->slug),
            nice_hair_normalize_shop_key((string) $term->name),
        ];

        if (in_array($normalized_key, $term_keys, true)) {
            return $term;
        }
    }

    return null;
}

function nice_hair_get_product_form_sample_image_map(): array
{
    static $map = null;

    if (is_array($map)) {
        return $map;
    }

    $image_files = [
        'warm_bundle'    => 'assets/images/faker/ready/Rectangle 132.jpg',
        'smooth_bundle'  => 'assets/images/faker/ready/Rectangle 16.jpg',
        'studio_bundle'  => 'assets/images/faker/ready/Rectangle 23.jpg',
    ];

    $build_sample = static function (string $image_key, string $alt) use ($image_files): array {
        $relative_path = $image_files[$image_key] ?? '';

        if ($relative_path === '') {
            return [
                'url' => '',
                'alt' => $alt,
            ];
        }

        $absolute_path = function_exists('get_theme_file_path')
            ? (string) get_theme_file_path($relative_path)
            : '';
        $image_url = function_exists('get_theme_file_uri')
            ? (string) get_theme_file_uri($relative_path)
            : '';

        if ($absolute_path === '' || ! file_exists($absolute_path)) {
            return [
                'url' => '',
                'alt' => $alt,
            ];
        }

        return [
            'url' => $image_url,
            'alt' => $alt,
        ];
    };

    $map = [
        'bulk' => $build_sample('warm_bundle', 'Bulk raw hair bundle sample'),
        'biotape' => $build_sample('smooth_bundle', 'Biotape sample'),
        'double_layer_hand_tied_weft_with_clip_in' => $build_sample('studio_bundle', 'Double layer hand-tied weft with clip-in sample'),
        'flat_keratin_bonds' => $build_sample('smooth_bundle', 'Flat keratin bonds sample'),
        'flat_weft' => $build_sample('warm_bundle', 'Flat weft sample'),
        'genius_weft' => $build_sample('studio_bundle', 'Genius weft sample'),
        'hand_tied_weft' => $build_sample('smooth_bundle', 'Hand-tied weft sample'),
        'i_tip_round_tips' => $build_sample('studio_bundle', 'I-tip round tips sample'),
        'invisible_tapes' => $build_sample('warm_bundle', 'Invisible tapes sample'),
        'machine_weft_clip_in' => $build_sample('studio_bundle', 'Machine weft clip-in sample'),
        'machine_weft' => $build_sample('smooth_bundle', 'Machine weft sample'),
        'mini_tapes_butterflies' => $build_sample('warm_bundle', 'Mini tapes butterflies sample'),
        'nano_bonds_with_metal_round_tip' => $build_sample('studio_bundle', 'Nano bonds with metal round tip sample'),
        'nano_bonds_with_metal_straight_tip' => $build_sample('studio_bundle', 'Nano bonds with metal straight tip sample'),
        'nano_bonds_with_plastic_tip' => $build_sample('smooth_bundle', 'Nano bonds with plastic tip sample'),
        'nano_bonds_with_thread_tip' => $build_sample('smooth_bundle', 'Nano bonds with thread tip sample'),
        'ponytail_with_ribbon' => $build_sample('warm_bundle', 'Ponytail with ribbon sample'),
        'usual_tapes' => $build_sample('warm_bundle', 'Usual tapes sample'),
    ];

    return $map;
}

function nice_hair_get_product_form_sample_image(string $form_key = '', string $fallback_label = ''): array
{
    $normalized_key = nice_hair_normalize_shop_key($form_key);
    $extension_type_term = nice_hair_get_extension_type_term_for_product_form($normalized_key, $fallback_label);

    if ($extension_type_term instanceof WP_Term) {
        $term_image = nice_hair_normalize_media_field_value(
            nice_hair_get_term_field_value('nh_extension_preview_image', $extension_type_term)
        );

        if (is_array($term_image) && ! empty($term_image['url'])) {
            $term_image['alt'] = trim((string) ($term_image['alt'] ?? '')) !== ''
                ? (string) $term_image['alt']
                : ((string) $extension_type_term->name . ' sample');

            return $term_image;
        }
    }

    $samples = nice_hair_get_product_form_sample_image_map();
    $sample = $samples[$normalized_key] ?? $samples['bulk'] ?? [
        'url' => '',
        'alt' => '',
    ];

    if (($sample['alt'] ?? '') === '' && $fallback_label !== '') {
        $sample['alt'] = $fallback_label . ' sample';
    }

    return [
        'url' => (string) ($sample['url'] ?? ''),
        'alt' => (string) ($sample['alt'] ?? ''),
    ];
}

function nice_hair_get_product_form_support_copy(string $form_key = '', string $label = ''): string
{
    $normalized_key = nice_hair_normalize_shop_key($form_key);
    $resolved_label = trim($label);

    if ($normalized_key === 'bulk') {
        return __('Price for the bundle (Bulk / Raw hair bundle) only.', 'nice-hair');
    }

    if ($resolved_label === '') {
        $option = nice_hair_get_shop_product_form_option($normalized_key);
        $resolved_label = is_array($option) ? (string) ($option['label'] ?? '') : '';
    }

    if ($resolved_label === '') {
        $resolved_label = nice_hair_humanize_shop_key($normalized_key);
    }

    return sprintf(
        /* translators: %s: selected product form label. */
        __('Final price for the %s transformation of this bundle.', 'nice-hair'),
        $resolved_label
    );
}

function nice_hair_get_shop_product_form_options(): array
{
    static $options = null;

    if (is_array($options)) {
        return array_values($options);
    }

    $options = [];
    $raw_rows = nice_hair_get_shop_option_field('nh_shop_pricing_form_surcharges', []);

    if (is_array($raw_rows)) {
        foreach ($raw_rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $label = isset($row['item_extension_type'])
                ? trim((string) $row['item_extension_type'])
                : '';
            $price = $row['item_price_per_gram'] ?? null;

            if ($label === '' || ! is_numeric($price)) {
                continue;
            }

            $key = nice_hair_normalize_shop_key($label);

            if ($key === '') {
                continue;
            }

            $options[$key] = [
                'key'             => $key,
                'label'           => $label,
                'price_per_gram'  => (float) $price,
                'sample_image'    => nice_hair_get_product_form_sample_image($key, $label),
                'support_copy'    => nice_hair_get_product_form_support_copy($key, $label),
            ];
        }
    }

    if ($options === []) {
        $defaults = nice_hair_get_shop_pricing_defaults();
        $default_labels = nice_hair_get_default_product_form_labels();

        foreach ($defaults['custom_hair']['form_surcharges'] as $key => $price_per_gram) {
            $normalized_key = nice_hair_normalize_shop_key((string) $key);

            $options[$normalized_key] = [
                'key'            => $normalized_key,
                'label'          => $default_labels[$normalized_key] ?? nice_hair_humanize_shop_key($normalized_key),
                'price_per_gram' => (float) $price_per_gram,
                'sample_image'   => nice_hair_get_product_form_sample_image($normalized_key, (string) ($default_labels[$normalized_key] ?? '')),
                'support_copy'   => nice_hair_get_product_form_support_copy($normalized_key, (string) ($default_labels[$normalized_key] ?? '')),
            ];
        }
    }

    if (! isset($options['bulk'])) {
        $options = array_merge(
            [
                'bulk' => [
                    'key'            => 'bulk',
                    'label'          => 'Bulk',
                    'price_per_gram' => 0.0,
                ],
            ],
            $options
        );
    } else {
        $options['bulk']['label'] = trim((string) ($options['bulk']['label'] ?? '')) ?: 'Bulk';
        $options['bulk']['price_per_gram'] = 0.0;
        $options['bulk']['sample_image'] = nice_hair_get_product_form_sample_image('bulk', (string) ($options['bulk']['label'] ?? 'Bulk'));
        $options['bulk']['support_copy'] = nice_hair_get_product_form_support_copy('bulk', (string) ($options['bulk']['label'] ?? 'Bulk'));

        $bulk_option = $options['bulk'];
        unset($options['bulk']);
        $options = array_merge(['bulk' => $bulk_option], $options);
    }

    return array_values($options);
}

function nice_hair_get_shop_product_form_option(string $form_key = ''): ?array
{
    $normalized_key = nice_hair_normalize_shop_key($form_key);

    if ($normalized_key === '') {
        return null;
    }

    foreach (nice_hair_get_shop_product_form_options() as $option) {
        if (($option['key'] ?? '') === $normalized_key) {
            return $option;
        }
    }

    return null;
}

function nice_hair_get_shop_product_form_catalog(): array
{
    static $catalog = null;

    if (is_array($catalog)) {
        return $catalog;
    }

    $catalog = [];

    foreach (nice_hair_get_shop_product_form_options() as $option) {
        if (! is_array($option)) {
            continue;
        }

        $key = isset($option['key']) ? nice_hair_normalize_shop_key((string) $option['key']) : '';

        if ($key === '') {
            continue;
        }

        $label = trim((string) ($option['label'] ?? ''));
        $label = $label !== '' ? $label : nice_hair_humanize_shop_key($key);
        $price_per_gram = isset($option['price_per_gram']) && is_numeric($option['price_per_gram'])
            ? (float) $option['price_per_gram']
            : 0.0;

        if ($key === 'bulk') {
            $price_per_gram = 0.0;
        }

        $catalog[$key] = [
            'key'            => $key,
            'label'          => $label,
            'price_per_gram' => $price_per_gram,
            'sample_image'   => is_array($option['sample_image'] ?? null)
                ? $option['sample_image']
                : nice_hair_get_product_form_sample_image($key, $label),
            'support_copy'   => (string) ($option['support_copy'] ?? nice_hair_get_product_form_support_copy($key, $label)),
        ];
    }

    if (! isset($catalog['bulk'])) {
        $catalog = array_merge([
            'bulk' => [
                'key'            => 'bulk',
                'label'          => 'Bulk',
                'price_per_gram' => 0.0,
                'sample_image'   => nice_hair_get_product_form_sample_image('bulk', 'Bulk'),
                'support_copy'   => nice_hair_get_product_form_support_copy('bulk', 'Bulk'),
            ],
        ], $catalog);
    } else {
        $bulk_option = $catalog['bulk'];
        unset($catalog['bulk']);
        $catalog = array_merge([
            'bulk' => [
                'key'            => 'bulk',
                'label'          => trim((string) ($bulk_option['label'] ?? '')) !== ''
                    ? (string) $bulk_option['label']
                    : 'Bulk',
                'price_per_gram' => 0.0,
                'sample_image'   => is_array($bulk_option['sample_image'] ?? null)
                    ? $bulk_option['sample_image']
                    : nice_hair_get_product_form_sample_image('bulk', 'Bulk'),
                'support_copy'   => (string) ($bulk_option['support_copy'] ?? nice_hair_get_product_form_support_copy('bulk', 'Bulk')),
            ],
        ], $catalog);
    }

    return $catalog;
}

function nice_hair_get_product_category_ids_by_family(string $family): array
{
    static $cache = [];

    $normalized_family = nice_hair_normalize_shop_key($family);

    if ($normalized_family === '') {
        return [];
    }

    if (isset($cache[$normalized_family])) {
        return $cache[$normalized_family];
    }

    $aliases = nice_hair_get_shop_family_aliases()[$normalized_family] ?? [];

    if ($aliases === []) {
        $cache[$normalized_family] = [];

        return $cache[$normalized_family];
    }

    $terms = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
    ]);

    if (! is_array($terms)) {
        $cache[$normalized_family] = [];

        return $cache[$normalized_family];
    }

    $ids = [];

    foreach ($terms as $term) {
        if (! $term instanceof WP_Term) {
            continue;
        }

        $term_keys = [
            nice_hair_normalize_shop_key((string) $term->slug),
            nice_hair_normalize_shop_key((string) $term->name),
        ];

        if (array_intersect($aliases, $term_keys) !== []) {
            $ids[] = (int) $term->term_id;
        }
    }

    $cache[$normalized_family] = array_values(array_unique($ids));

    return $cache[$normalized_family];
}

function nice_hair_get_shop_assortment_form_filter_choices(): array
{
    $choices = [];

    foreach (nice_hair_get_shop_product_form_catalog() as $key => $item) {
        if (! is_string($key) || $key === '') {
            continue;
        }

        $label = trim((string) ($item['label'] ?? ''));
        $choices[$key] = $label !== '' ? $label : nice_hair_humanize_shop_key($key);
    }

    return $choices;
}

function nice_hair_get_shop_assortment_candidate_product_ids(): array
{
    static $cache = null;

    if (is_array($cache)) {
        return $cache;
    }

    $category_ids = nice_hair_get_product_category_ids_by_family('custom_hair');

    if ($category_ids === []) {
        $cache = [];

        return $cache;
    }

    $query = new WP_Query([
        'post_type'              => 'product',
        'post_status'            => 'publish',
        'fields'                 => 'ids',
        'posts_per_page'         => -1,
        'no_found_rows'          => true,
        'ignore_sticky_posts'    => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'orderby'                => [
            'menu_order' => 'ASC',
            'title'      => 'ASC',
            'ID'         => 'ASC',
        ],
        'tax_query'              => [
            [
                'taxonomy'         => 'product_cat',
                'field'            => 'term_id',
                'terms'            => $category_ids,
                'include_children' => true,
                'operator'         => 'IN',
            ],
        ],
    ]);

    $cache = array_map('intval', is_array($query->posts) ? $query->posts : []);

    return $cache;
}

function nice_hair_get_shop_assortment_card_image_payload(
    WC_Product|int|null $product = null,
    array $product_form = [],
    array $catalog_entry = []
): array {
    $resolved = nice_hair_resolve_product($product);
    $catalog_image = nice_hair_normalize_media_field_value($catalog_entry['sample_image'] ?? null);
    $fallback_label = trim((string) ($catalog_entry['label'] ?? ($product_form['label'] ?? '')));

    if (is_array($catalog_image) && ! empty($catalog_image['url'])) {
        return [
            'url' => (string) $catalog_image['url'],
            'alt' => trim((string) ($catalog_image['alt'] ?? '')) !== ''
                ? (string) $catalog_image['alt']
                : ($fallback_label !== '' ? $fallback_label . ' preview' : 'Product preview'),
        ];
    }

    if ($resolved instanceof WC_Product) {
        $image_id = (int) $resolved->get_image_id();

        if ($image_id > 0) {
            $image_url = wp_get_attachment_image_url($image_id, 'large');

            if (! is_string($image_url) || $image_url === '') {
                $image_url = wp_get_attachment_image_url($image_id, 'full');
            }

            if (is_string($image_url) && $image_url !== '') {
                $image_alt = trim((string) get_post_meta($image_id, '_wp_attachment_image_alt', true));

                return [
                    'url' => $image_url,
                    'alt' => $image_alt !== ''
                        ? $image_alt
                        : ($fallback_label !== '' ? $fallback_label . ' preview' : $resolved->get_name()),
                ];
            }
        }
    }

    return [
        'url' => function_exists('wc_placeholder_img_src')
            ? (string) wc_placeholder_img_src('woocommerce_single')
            : '',
        'alt' => $fallback_label !== '' ? $fallback_label . ' preview' : 'Product preview',
    ];
}

function nice_hair_get_shop_assortment_cards(array $selected_form_keys = []): array
{
    static $cache = [];

    $catalog = nice_hair_get_shop_product_form_catalog();

    if ($catalog === []) {
        return [];
    }

    $requested_lookup = [];

    foreach ($selected_form_keys as $selected_form_key) {
        $normalized_key = nice_hair_normalize_shop_key((string) $selected_form_key);

        if ($normalized_key === '' || ! isset($catalog[$normalized_key])) {
            continue;
        }

        $requested_lookup[$normalized_key] = true;
    }

    $requested_keys = $requested_lookup !== []
        ? array_keys($requested_lookup)
        : array_keys($catalog);
    $cache_key = md5((string) wp_json_encode($requested_keys));

    if (isset($cache[$cache_key])) {
        return $cache[$cache_key];
    }

    $requested_key_lookup = array_fill_keys($requested_keys, true);
    $cards_by_key = [];

    foreach (nice_hair_get_shop_assortment_candidate_product_ids() as $product_id) {
        $product = wc_get_product($product_id);

        if (! $product instanceof WC_Product || ! $product->is_visible() || ! nice_hair_product_is_family('custom_hair', $product)) {
            continue;
        }

        $product_form = nice_hair_get_custom_hair_product_form_data($product);
        $form_key = nice_hair_normalize_shop_key((string) ($product_form['key'] ?? ''));

        if (
            $form_key === ''
            || empty($product_form['is_valid'])
            || ! isset($requested_key_lookup[$form_key])
            || isset($cards_by_key[$form_key])
        ) {
            continue;
        }

        $catalog_entry = $catalog[$form_key] ?? [];
        $label = trim((string) ($catalog_entry['label'] ?? ($product_form['label'] ?? $product->get_name())));

        if ($label === '') {
            $label = $product->get_name();
        }

        $permalink = get_permalink($product->get_id());

        if (! is_string($permalink) || $permalink === '') {
            continue;
        }

        $cards_by_key[$form_key] = [
            'key'        => $form_key,
            'label'      => $label,
            'product_id' => $product->get_id(),
            'url'        => $permalink,
            'image'      => nice_hair_get_shop_assortment_card_image_payload($product, $product_form, $catalog_entry),
        ];
    }

    $cards = [];

    foreach ($requested_keys as $requested_key) {
        if (isset($cards_by_key[$requested_key])) {
            $cards[] = $cards_by_key[$requested_key];
        }
    }

    $cache[$cache_key] = $cards;

    return $cache[$cache_key];
}

function nice_hair_product_uses_custom_exclusive_product_forms(WC_Product|int|null $product = null): bool
{
    $resolved = nice_hair_resolve_product($product);

    if (! $resolved instanceof WC_Product || ! nice_hair_product_is_family('exclusive_hair', $resolved)) {
        return false;
    }

    $raw_value = function_exists('get_field')
        ? get_field('nh_exclusive_use_custom_product_forms', $resolved->get_id())
        : get_post_meta($resolved->get_id(), 'nh_exclusive_use_custom_product_forms', true);

    if (is_bool($raw_value)) {
        return $raw_value;
    }

    if (is_numeric($raw_value)) {
        return (int) $raw_value === 1;
    }

    $normalized_value = strtolower(trim((string) $raw_value));

    return in_array($normalized_value, ['1', 'true', 'yes', 'on'], true);
}

function nice_hair_get_exclusive_product_form_overrides(WC_Product|int|null $product = null): array
{
    $resolved = nice_hair_resolve_product($product);

    if (! $resolved instanceof WC_Product || ! nice_hair_product_uses_custom_exclusive_product_forms($resolved)) {
        return [];
    }

    $rows = function_exists('get_field')
        ? get_field('nh_exclusive_product_form_overrides', $resolved->get_id())
        : [];

    if (! is_array($rows) || $rows === []) {
        return [];
    }

    $catalog = nice_hair_get_shop_product_form_catalog();
    $overrides = [];

    foreach ($rows as $row) {
        if (! is_array($row)) {
            continue;
        }

        $form_key = isset($row['product_form_key'])
            ? nice_hair_normalize_shop_key((string) $row['product_form_key'])
            : '';

        if ($form_key === '' || $form_key === 'bulk' || isset($overrides[$form_key]) || ! isset($catalog[$form_key])) {
            continue;
        }

        $raw_override = $row['price_per_gram_override'] ?? null;
        $override_price_per_gram = null;

        if ($raw_override !== '' && $raw_override !== null && is_numeric($raw_override)) {
            $override_price_per_gram = max(0, (float) $raw_override);
        }

        $overrides[$form_key] = [
            'key'                     => $form_key,
            'price_per_gram_override' => $override_price_per_gram,
        ];
    }

    return $overrides;
}

function nice_hair_get_exclusive_product_form_options(WC_Product|int|null $product = null): array
{
    $catalog = nice_hair_get_shop_product_form_catalog();

    if ($catalog === []) {
        return [];
    }

    if (! nice_hair_product_uses_custom_exclusive_product_forms($product)) {
        return array_values($catalog);
    }

    $options = [];

    if (isset($catalog['bulk'])) {
        $options['bulk'] = $catalog['bulk'];
    }

    foreach (nice_hair_get_exclusive_product_form_overrides($product) as $form_key => $override) {
        if (! isset($catalog[$form_key])) {
            continue;
        }

        $option = $catalog[$form_key];

        if (isset($override['price_per_gram_override']) && is_numeric($override['price_per_gram_override'])) {
            $option['price_per_gram'] = max(0, (float) $override['price_per_gram_override']);
        }

        $options[$form_key] = $option;
    }

    return array_values($options);
}

function nice_hair_get_exclusive_product_form_option_for_product(WC_Product|int|null $product = null, string $form_key = ''): ?array
{
    $normalized_key = nice_hair_normalize_shop_key($form_key);

    if ($normalized_key === '') {
        return null;
    }

    foreach (nice_hair_get_exclusive_product_form_options($product) as $option) {
        if (($option['key'] ?? '') === $normalized_key) {
            return $option;
        }
    }

    return null;
}

function nice_hair_get_exclusive_default_product_form_key(WC_Product|int|null $product = null): string
{
    if (nice_hair_product_uses_custom_exclusive_product_forms($product)) {
        return 'bulk';
    }

    foreach (nice_hair_get_exclusive_product_form_options($product) as $option) {
        if (($option['key'] ?? '') === 'bulk') {
            return 'bulk';
        }
    }

    $first_option = nice_hair_get_exclusive_product_form_options($product)[0] ?? null;

    return is_array($first_option) ? (string) ($first_option['key'] ?? '') : '';
}

function nice_hair_get_exclusive_base_pricing(WC_Product|int|null $product = null): array
{
    $resolved = nice_hair_resolve_product($product);
    $base_lot_price = nice_hair_get_product_base_lot_price($resolved);
    $effective_base_lot_price = $base_lot_price;
    $discount_amount = 0.0;
    $is_on_sale = false;

    if ($resolved instanceof WC_Product && $base_lot_price !== null) {
        $regular_price = $resolved->get_regular_price();
        $sale_price = $resolved->get_sale_price();

        if (is_numeric($regular_price) && is_numeric($sale_price)) {
            $regular_numeric = (float) $regular_price;
            $sale_numeric = (float) $sale_price;

            if ($regular_numeric > $sale_numeric && $sale_numeric >= 0) {
                $discount_amount = max(0, $regular_numeric - $sale_numeric);
                $effective_base_lot_price = max(0, $base_lot_price - $discount_amount);
                $is_on_sale = $discount_amount > 0;
            }
        }
    }

    return [
        'base_lot_price'           => $base_lot_price,
        'effective_base_lot_price' => $effective_base_lot_price,
        'discount_amount'          => $discount_amount,
        'is_on_sale'               => $is_on_sale,
    ];
}

function nice_hair_get_exclusive_product_form_totals(WC_Product|int|null $product = null, string $form_key = ''): array
{
    $fixed_weight = nice_hair_get_product_fixed_weight_grams($product);
    $form_option = nice_hair_get_exclusive_product_form_option_for_product($product, $form_key);
    $base_pricing = nice_hair_get_exclusive_base_pricing($product);
    $base_lot_price = $base_pricing['base_lot_price'] ?? null;
    $effective_base_lot_price = $base_pricing['effective_base_lot_price'] ?? null;
    $is_on_sale = (bool) ($base_pricing['is_on_sale'] ?? false);

    if ($base_lot_price === null || $effective_base_lot_price === null || $fixed_weight === null || ! is_array($form_option)) {
        return [
            'regular_total_price' => null,
            'current_total_price' => null,
            'price_html'          => '',
        ];
    }

    $price_per_gram = (float) ($form_option['price_per_gram'] ?? 0);
    $surcharge_total = $fixed_weight * $price_per_gram;
    $regular_total_price = round($base_lot_price + $surcharge_total, 2);
    $current_total_price = round($effective_base_lot_price + $surcharge_total, 2);

    if ($is_on_sale && $regular_total_price > $current_total_price) {
        return [
            'regular_total_price' => $regular_total_price,
            'current_total_price' => $current_total_price,
            'price_html'          => sprintf(
                '<del>%1$s</del> <ins>%2$s</ins>',
                wc_price($regular_total_price),
                wc_price($current_total_price)
            ),
        ];
    }

    return [
        'regular_total_price' => $regular_total_price,
        'current_total_price' => $current_total_price,
        'price_html'          => wc_price($current_total_price),
    ];
}

function nice_hair_calculate_exclusive_product_form_total(WC_Product|int|null $product = null, string $form_key = ''): ?float
{
    $totals = nice_hair_get_exclusive_product_form_totals($product, $form_key);

    return isset($totals['current_total_price']) && is_numeric($totals['current_total_price'])
        ? (float) $totals['current_total_price']
        : null;
}

function nice_hair_get_exclusive_product_form_pricing(WC_Product|int|null $product = null): array
{
    $resolved = nice_hair_resolve_product($product);

    $empty_result = [
        'fixed_weight'    => null,
        'options'         => [],
        'default_key'     => '',
        'selected_option' => null,
        'is_complete'     => false,
    ];

    if (! $resolved instanceof WC_Product) {
        return $empty_result;
    }

    $acf_base_lot_price = nice_hair_get_product_base_lot_price($resolved);
    $fixed_weight = nice_hair_get_product_fixed_weight_grams($resolved);
    $form_catalog = nice_hair_get_shop_product_form_catalog();

    if ($fixed_weight === null || $fixed_weight <= 0 || $form_catalog === []) {
        return array_replace($empty_result, [
            'fixed_weight' => $fixed_weight,
        ]);
    }

    /**
     * Exclusive Hair price source rules:
     *
     * 1. ACF base lot price is the business fallback/base lot price.
     * 2. If WooCommerce sale is active, WooCommerce regular/sale prices define
     *    regular/current base prices for frontend display and cart price.
     * 3. Do not subtract the WooCommerce discount from ACF base price.
     *
     * Bug example before this fix:
     * ACF base = 130, WC regular = 175, WC sale = 130
     * Previous result: 130 - (175 - 130) = 85
     * Correct result: current base = 130, regular base = 175
     */
    $wc_regular_price = $resolved->get_regular_price();
    $wc_sale_price = $resolved->get_sale_price();
    $wc_current_price = $resolved->get_price();

    $wc_regular_price = is_numeric($wc_regular_price) ? (float) $wc_regular_price : null;
    $wc_sale_price = is_numeric($wc_sale_price) ? (float) $wc_sale_price : null;
    $wc_current_price = is_numeric($wc_current_price) ? (float) $wc_current_price : null;

    $has_wc_sale = $wc_regular_price !== null
        && $wc_sale_price !== null
        && $wc_regular_price > 0
        && $wc_sale_price > 0
        && $wc_sale_price < $wc_regular_price
        && $resolved->is_on_sale();

    if ($has_wc_sale) {
        $regular_base_price = $wc_regular_price;
        $current_base_price = $wc_sale_price;
    } else {
        $fallback_base_price = $acf_base_lot_price;

        if ($fallback_base_price === null && $wc_current_price !== null && $wc_current_price > 0) {
            $fallback_base_price = $wc_current_price;
        }

        if ($fallback_base_price === null || $fallback_base_price <= 0) {
            return array_replace($empty_result, [
                'fixed_weight' => $fixed_weight,
            ]);
        }

        $regular_base_price = $fallback_base_price;
        $current_base_price = $fallback_base_price;
    }

    $options = [];

    foreach ($form_catalog as $form_option) {
        if (! is_array($form_option)) {
            continue;
        }

        $form_key = isset($form_option['key'])
            ? nice_hair_normalize_shop_key((string) $form_option['key'])
            : '';

        if ($form_key === '') {
            continue;
        }

        $form_label = trim((string) ($form_option['label'] ?? ''));

        if ($form_label === '') {
            $form_label = nice_hair_humanize_shop_key($form_key);
        }

        $price_per_gram = isset($form_option['price_per_gram']) && is_numeric($form_option['price_per_gram'])
            ? (float) $form_option['price_per_gram']
            : null;

        if ($price_per_gram === null) {
            continue;
        }

        if ($form_key === 'bulk') {
            $price_per_gram = 0.0;
        }

        $form_surcharge_total = $fixed_weight * $price_per_gram;

        $regular_price = $regular_base_price + $form_surcharge_total;
        $current_price = $current_base_price + $form_surcharge_total;

        $has_discount = $regular_price > $current_price;

        $regular_display_price = function_exists('wc_get_price_to_display')
            ? wc_get_price_to_display($resolved, ['price' => $regular_price])
            : $regular_price;

        $current_display_price = function_exists('wc_get_price_to_display')
            ? wc_get_price_to_display($resolved, ['price' => $current_price])
            : $current_price;

        if ($has_discount && function_exists('wc_format_sale_price')) {
            $price_html = wc_format_sale_price($regular_display_price, $current_display_price);
        } elseif (function_exists('wc_price')) {
            $price_html = wc_price($current_display_price);
        } else {
            $price_html = number_format($current_display_price, 2, '.', '');
        }

        $sample_image = is_array($form_option['sample_image'] ?? null)
            ? $form_option['sample_image']
            : nice_hair_get_product_form_sample_image($form_key, $form_label);

        $support_copy = trim((string) ($form_option['support_copy'] ?? ''));

        if ($support_copy === '') {
            $support_copy = nice_hair_get_product_form_support_copy($form_key, $form_label);
        }

        $options[$form_key] = [
            'key'                     => $form_key,
            'label'                   => $form_label,
            'price_per_gram'          => $price_per_gram,
            'base_lot_price'          => $current_base_price,
            'regular_base_lot_price'  => $regular_base_price,
            'form_surcharge_total'    => $form_surcharge_total,

            /**
             * Keep several names for compatibility with cart/order code.
             */
            'price'                   => $current_price,
            'regular_price'           => $regular_price,
            'unit_price'              => $current_price,
            'regular_unit_price'      => $regular_price,

            'price_html'              => $price_html,
            'has_discount'            => $has_discount,
            'is_available'            => true,
            'sample_image'            => $sample_image,
            'support_copy'            => $support_copy,
        ];
    }

    if ($options === []) {
        return array_replace($empty_result, [
            'fixed_weight' => $fixed_weight,
        ]);
    }

    $default_key = isset($options['bulk'])
        ? 'bulk'
        : (string) array_key_first($options);

    return [
        'fixed_weight'    => $fixed_weight,
        'options'         => array_values($options),
        'default_key'     => $default_key,
        'selected_option' => $options[$default_key] ?? null,
        'is_complete'     => true,
    ];
}

function nice_hair_get_product_attribute_terms(WC_Product|int|null $product = null, string $taxonomy = ''): array
{
    $resolved = nice_hair_resolve_product($product);

    if (! $resolved instanceof WC_Product || $taxonomy === '' || ! taxonomy_exists($taxonomy)) {
        return [];
    }

    $terms = wp_get_post_terms($resolved->get_id(), $taxonomy, [
        'orderby' => 'term_order',
        'order'   => 'ASC',
    ]);

    if (! is_array($terms) || $terms === []) {
        return [];
    }

    return array_values(array_filter($terms, static fn (mixed $term): bool => $term instanceof WP_Term));
}

function nice_hair_get_primary_product_attribute_term(WC_Product|int|null $product = null, string $taxonomy = ''): ?WP_Term
{
    $terms = nice_hair_get_product_attribute_terms($product, $taxonomy);

    return $terms[0] ?? null;
}

function nice_hair_get_custom_hair_length_choice_map(): array
{
    static $choices = null;

    if (is_array($choices)) {
        return $choices;
    }

    $lengths = ['40', '50', '60', '70', '80', '90'];
    $config = nice_hair_get_shop_pricing_config();

    foreach ((array) ($config['custom_hair']['base_prices'] ?? []) as $quality_rows) {
        if (! is_array($quality_rows)) {
            continue;
        }

        foreach (array_keys($quality_rows) as $length_key) {
            $length_key = trim((string) $length_key);

            if ($length_key !== '') {
                $lengths[] = $length_key;
            }
        }
    }

    $lengths = array_values(array_unique($lengths));
    usort($lengths, static fn (string $left, string $right): int => (int) $left <=> (int) $right);

    $choices = [];

    foreach ($lengths as $length) {
        $choices[$length] = $length . ' cm';
    }

    return $choices;
}

function nice_hair_get_custom_hair_quality_choice_map(): array
{
    static $choices = null;

    if (is_array($choices)) {
        return $choices;
    }

    $defaults = [
        'lux' => 'Lux',
        'premium' => 'Premium',
        'exclusive' => 'Exclusive',
    ];
    $choices = [];
    $config = nice_hair_get_shop_pricing_config();

    foreach (array_keys((array) ($config['custom_hair']['base_prices'] ?? [])) as $quality_key) {
        $normalized_key = nice_hair_normalize_shop_key((string) $quality_key);

        if ($normalized_key === '') {
            continue;
        }

        $choices[$normalized_key] = $defaults[$normalized_key] ?? nice_hair_humanize_shop_key($normalized_key);
    }

    if ($choices === []) {
        $choices = $defaults;
    }

    return $choices;
}

function nice_hair_get_custom_hair_texture_choice_map(): array
{
    return [
        'soft_straight' => 'Soft straight',
        'silky_wavy' => 'Silky wavy',
        'amazing_curly' => 'Amazing curly',
    ];
}

function nice_hair_get_custom_hair_color_group_choice_map(): array
{
    return [
        'light' => 'Light',
        'middle' => 'Middle',
        'dark' => 'Dark',
    ];
}

function nice_hair_get_custom_hair_allowed_choice_keys(
    WC_Product|int|null $product = null,
    string $field_name = '',
    array $choice_map = [],
    bool $normalize_keys = true
): array {
    $resolved = nice_hair_resolve_product($product);

    if (! $resolved instanceof WC_Product || $field_name === '') {
        return array_keys($choice_map);
    }

    $raw_value = function_exists('get_field')
        ? get_field($field_name, $resolved->get_id())
        : get_post_meta($resolved->get_id(), $field_name, true);
    $values = is_array($raw_value) ? $raw_value : (is_string($raw_value) && $raw_value !== '' ? [$raw_value] : []);
    $allowed = [];

    foreach ($values as $value) {
        $normalized_value = $normalize_keys
            ? nice_hair_normalize_shop_key((string) $value)
            : trim((string) $value);

        if ($normalized_value === '' || ! isset($choice_map[$normalized_value])) {
            continue;
        }

        $allowed[] = $normalized_value;
    }

    $allowed = array_values(array_unique($allowed));

    return $allowed !== [] ? $allowed : array_keys($choice_map);
}

function nice_hair_get_custom_hair_allowed_lengths(WC_Product|int|null $product = null): array
{
    return nice_hair_get_custom_hair_allowed_choice_keys(
        $product,
        'nh_custom_hair_available_lengths',
        nice_hair_get_custom_hair_length_choice_map(),
        false
    );
}

function nice_hair_get_custom_hair_allowed_qualities(WC_Product|int|null $product = null): array
{
    return nice_hair_get_custom_hair_allowed_choice_keys(
        $product,
        'nh_custom_hair_available_qualities',
        nice_hair_get_custom_hair_quality_choice_map()
    );
}

function nice_hair_get_custom_hair_allowed_textures(WC_Product|int|null $product = null): array
{
    return nice_hair_get_custom_hair_allowed_choice_keys(
        $product,
        'nh_custom_hair_available_textures',
        nice_hair_get_custom_hair_texture_choice_map()
    );
}

function nice_hair_get_custom_hair_product_form_data(WC_Product|int|null $product = null): array
{
    $terms = nice_hair_get_product_attribute_terms($product, 'pa_extension_type');
    $term_count = count($terms);
    $primary_term = $terms[0] ?? null;
    $form_key = $primary_term instanceof WP_Term
        ? nice_hair_normalize_shop_key((string) $primary_term->slug)
        : '';
    $form_label = $primary_term instanceof WP_Term
        ? trim((string) $primary_term->name)
        : '';
    $form_option = $form_key !== ''
        ? nice_hair_get_shop_product_form_option($form_key)
        : null;

    if ($form_label === '' && is_array($form_option)) {
        $form_label = (string) ($form_option['label'] ?? '');
    }

    if ($form_label === '' && $form_key !== '') {
        $form_label = nice_hair_humanize_shop_key($form_key);
    }

    $surcharge_per_gram = $form_key !== ''
        ? nice_hair_get_product_form_surcharge_per_gram($form_key)
        : null;

    return [
        'term' => $primary_term,
        'term_id' => $primary_term instanceof WP_Term ? (int) $primary_term->term_id : 0,
        'term_count' => $term_count,
        'key' => $form_key,
        'label' => $form_label,
        'surcharge_per_gram' => $surcharge_per_gram,
        'sample_image' => $form_key !== ''
            ? nice_hair_get_product_form_sample_image($form_key, $form_label)
            : ['url' => '', 'alt' => ''],
        'support_copy' => $form_key !== ''
            ? nice_hair_get_product_form_support_copy($form_key, $form_label)
            : '',
        'is_valid' => $primary_term instanceof WP_Term && $term_count === 1 && $form_key !== '',
    ];
}

function nice_hair_get_custom_hair_color_options(WC_Product|int|null $product = null): array
{
    if (! function_exists('nice_hair_get_product_custom_hair_color_options')) {
        return [];
    }

    $resolved = nice_hair_resolve_product($product);

    if (! $resolved instanceof WC_Product) {
        return [];
    }

    $rows = nice_hair_get_product_custom_hair_color_options($resolved);

    if (! is_array($rows) || $rows === []) {
        return [];
    }

    $options = [];

    foreach ($rows as $row) {
        if (! is_array($row)) {
            continue;
        }

        $key = nice_hair_normalize_shop_key((string) ($row['key'] ?? ''));

        if ($key === '' || isset($options[$key])) {
            continue;
        }

        $label = trim((string) ($row['label'] ?? ''));
        $value = trim((string) ($row['value'] ?? ''));

        if ($label === '' && $value === '') {
            continue;
        }

        if ($label === '') {
            $label = nice_hair_humanize_shop_key($key);
        }

        if ($value === '') {
            $value = $label;
        }

        $main_image = nice_hair_normalize_media_field_value($row['main_image'] ?? null);
        $preview_image = nice_hair_normalize_media_field_value($row['preview_image'] ?? null);

        if (! is_array($preview_image)) {
            $preview_image = $main_image;
        }

        $group_key = nice_hair_normalize_shop_key((string) ($row['group_key'] ?? ''));

        $options[$key] = [
            'key'           => $key,
            'value'         => $value,
            'label'         => $label,
            'group_key'     => $group_key,
            'group_label'   => $group_key !== '' ? nice_hair_humanize_shop_key($group_key) : '',
            'preview_image' => $preview_image,
            'main_image'    => $main_image,
            'has_image'     => is_array($main_image) && ! empty($main_image['url']),
            'sort_order'    => isset($row['sort_order']) && is_numeric($row['sort_order'])
                ? (int) $row['sort_order']
                : 100,
            'source'        => (string) ($row['source'] ?? ''),
        ];
    }

    return array_values($options);
}

function nice_hair_get_custom_hair_color_option_map(WC_Product|int|null $product = null): array
{
    $map = [];

    foreach (nice_hair_get_custom_hair_color_options($product) as $option) {
        if (! is_array($option)) {
            continue;
        }

        $key = (string) ($option['key'] ?? '');

        if ($key !== '') {
            $map[$key] = $option;
        }
    }

    return $map;
}

function nice_hair_get_custom_hair_weight_config(WC_Product|int|null $product = null): array
{
    $resolved = nice_hair_resolve_product($product);
    $default_config = [
        'min' => 30,
        'step' => 10,
        'default' => 30,
    ];

    if (! $resolved instanceof WC_Product) {
        return $default_config;
    }

    $min = function_exists('get_field')
        ? get_field('nh_custom_hair_min_weight_grams', $resolved->get_id())
        : get_post_meta($resolved->get_id(), 'nh_custom_hair_min_weight_grams', true);
    $step = function_exists('get_field')
        ? get_field('nh_custom_hair_weight_step_grams', $resolved->get_id())
        : get_post_meta($resolved->get_id(), 'nh_custom_hair_weight_step_grams', true);
    $default_weight = function_exists('get_field')
        ? get_field('nh_custom_hair_default_weight_grams', $resolved->get_id())
        : get_post_meta($resolved->get_id(), 'nh_custom_hair_default_weight_grams', true);

    $min = is_numeric($min) ? max(0, (int) round((float) $min)) : $default_config['min'];
    $step = is_numeric($step) ? max(1, (int) round((float) $step)) : $default_config['step'];
    $default_weight = is_numeric($default_weight) ? max($min, (int) round((float) $default_weight)) : $default_config['default'];

    if ($default_weight < $min) {
        $default_weight = $min;
    }

    $remainder = ($default_weight - $min) % $step;

    if ($remainder !== 0) {
        $default_weight += $step - $remainder;
    }

    return [
        'min' => $min,
        'step' => $step,
        'default' => $default_weight,
    ];
}

function nice_hair_is_valid_custom_hair_weight(int|float|string|null $weight, array $weight_config = []): bool
{
    if (! is_numeric($weight)) {
        return false;
    }

    $numeric_weight = (int) round((float) $weight);
    $min = isset($weight_config['min']) ? max(0, (int) $weight_config['min']) : 30;
    $step = isset($weight_config['step']) ? max(1, (int) $weight_config['step']) : 10;

    if ($numeric_weight < $min) {
        return false;
    }

    return (($numeric_weight - $min) % $step) === 0;
}

function nice_hair_get_custom_hair_default_selection(
    array $color_options = [],
    array $length_keys = [],
    array $quality_keys = [],
    array $texture_keys = [],
    array $weight_config = [],
    string $form_key = ''
): array {
    $default_color = is_array($color_options[0] ?? null) ? (string) ($color_options[0]['key'] ?? '') : '';
    $default_texture = (string) ($texture_keys[0] ?? '');
    $default_quality = (string) ($quality_keys[0] ?? '');
    $default_length = (string) ($length_keys[0] ?? '');

    foreach ($quality_keys as $quality_key) {
        foreach ($length_keys as $length_key) {
            $base_price = nice_hair_get_custom_hair_base_price_per_gram((string) $quality_key, (string) $length_key);

            if ($base_price !== null && ($form_key === '' || nice_hair_get_product_form_surcharge_per_gram($form_key) !== null)) {
                $default_quality = (string) $quality_key;
                $default_length = (string) $length_key;
                break 2;
            }
        }
    }

    return [
        'color' => $default_color,
        'length' => $default_length,
        'quality' => $default_quality,
        'texture' => $default_texture,
        'weight' => isset($weight_config['default']) ? (int) $weight_config['default'] : 30,
    ];
}

function nice_hair_resolve_custom_hair_selection(WC_Product|int|null $product = null, array $request = []): array
{
    $resolved = nice_hair_resolve_product($product);
    $color_options = nice_hair_get_custom_hair_color_options($resolved);
    $color_map = nice_hair_get_custom_hair_color_option_map($resolved);
    $length_choice_map = nice_hair_get_custom_hair_length_choice_map();
    $quality_choice_map = nice_hair_get_custom_hair_quality_choice_map();
    $texture_choice_map = nice_hair_get_custom_hair_texture_choice_map();
    $length_keys = nice_hair_get_custom_hair_allowed_lengths($resolved);
    $quality_keys = nice_hair_get_custom_hair_allowed_qualities($resolved);
    $texture_keys = nice_hair_get_custom_hair_allowed_textures($resolved);
    $weight_config = nice_hair_get_custom_hair_weight_config($resolved);
    $product_form = nice_hair_get_custom_hair_product_form_data($resolved);
    $defaults = nice_hair_get_custom_hair_default_selection(
        $color_options,
        $length_keys,
        $quality_keys,
        $texture_keys,
        $weight_config,
        (string) ($product_form['key'] ?? '')
    );
    $request_valid = true;

    $requested_color = isset($request['color']) ? nice_hair_normalize_shop_key((string) $request['color']) : '';
    $requested_length = isset($request['length']) ? trim((string) $request['length']) : '';
    $requested_quality = isset($request['quality']) ? nice_hair_normalize_shop_key((string) $request['quality']) : '';
    $requested_texture = isset($request['texture']) ? nice_hair_normalize_shop_key((string) $request['texture']) : '';
    $requested_weight = $request['weight'] ?? null;

    if ($requested_color !== '' && ! isset($color_map[$requested_color])) {
        $request_valid = false;
    }

    if ($requested_length !== '' && ! in_array($requested_length, $length_keys, true)) {
        $request_valid = false;
    }

    if ($requested_quality !== '' && ! in_array($requested_quality, $quality_keys, true)) {
        $request_valid = false;
    }

    if ($requested_texture !== '' && ! in_array($requested_texture, $texture_keys, true)) {
        $request_valid = false;
    }

    if ($requested_weight !== null && $requested_weight !== '' && ! nice_hair_is_valid_custom_hair_weight($requested_weight, $weight_config)) {
        $request_valid = false;
    }

    $selected_color_key = $requested_color !== '' && isset($color_map[$requested_color])
        ? $requested_color
        : (string) ($defaults['color'] ?? '');
    $selected_length = $requested_length !== '' && in_array($requested_length, $length_keys, true)
        ? $requested_length
        : (string) ($defaults['length'] ?? '');
    $selected_quality = $requested_quality !== '' && in_array($requested_quality, $quality_keys, true)
        ? $requested_quality
        : (string) ($defaults['quality'] ?? '');
    $selected_texture = $requested_texture !== '' && in_array($requested_texture, $texture_keys, true)
        ? $requested_texture
        : (string) ($defaults['texture'] ?? '');
    $selected_weight = ($requested_weight !== null && $requested_weight !== '' && nice_hair_is_valid_custom_hair_weight($requested_weight, $weight_config))
        ? (int) round((float) $requested_weight)
        : (int) ($defaults['weight'] ?? 30);

    $selected_color = $selected_color_key !== '' && isset($color_map[$selected_color_key])
        ? $color_map[$selected_color_key]
        : null;
    $selected_form_key = (string) ($product_form['key'] ?? '');
    $selected_form_label = (string) ($product_form['label'] ?? '');
    $base_price_per_gram = ($selected_quality !== '' && $selected_length !== '')
        ? nice_hair_get_custom_hair_base_price_per_gram($selected_quality, $selected_length)
        : null;
    $surcharge_per_gram = $selected_form_key !== ''
        ? nice_hair_get_product_form_surcharge_per_gram($selected_form_key)
        : null;
    $has_complete_config = $resolved instanceof WC_Product
        && $selected_color !== null
        && $selected_length !== ''
        && $selected_quality !== ''
        && $selected_texture !== ''
        && $selected_form_key !== ''
        && ! empty($product_form['is_valid'])
        && $base_price_per_gram !== null
        && $surcharge_per_gram !== null
        && nice_hair_is_valid_custom_hair_weight($selected_weight, $weight_config);
    $total_price = $has_complete_config
        ? round(($base_price_per_gram + $surcharge_per_gram) * $selected_weight, 2)
        : null;

    return [
        'is_valid' => $has_complete_config && $request_valid,
        'is_complete' => $has_complete_config,
        'request_is_valid' => $request_valid,
        'product_form' => $product_form,
        'base_price_per_gram' => $base_price_per_gram,
        'surcharge_per_gram' => $surcharge_per_gram,
        'total_price' => $total_price,
        'price_html' => $total_price !== null ? wc_price($total_price) : '',
        'selection' => [
            'color' => $selected_color_key,
            'length' => $selected_length,
            'quality' => $selected_quality,
            'texture' => $selected_texture,
            'weight' => $selected_weight,
        ],
        'labels' => [
            'color' => (string) ($selected_color['label'] ?? ''),
            'length' => (string) ($length_choice_map[$selected_length] ?? $selected_length),
            'quality' => (string) ($quality_choice_map[$selected_quality] ?? $selected_quality),
            'texture' => (string) ($texture_choice_map[$selected_texture] ?? $selected_texture),
            'weight' => nice_hair_format_weight_grams($selected_weight),
            'product_form' => $selected_form_label,
        ],
        'selected_color' => $selected_color,
        'weight_config' => $weight_config,
        'allowed' => [
            'lengths' => $length_keys,
            'qualities' => $quality_keys,
            'textures' => $texture_keys,
        ],
    ];
}

function nice_hair_get_custom_hair_configurator(WC_Product|int|null $product = null): array
{
    $resolved = nice_hair_resolve_product($product);
    $color_options = nice_hair_get_custom_hair_color_options($resolved);
    $product_form = nice_hair_get_custom_hair_product_form_data($resolved);
    $length_choice_map = nice_hair_get_custom_hair_length_choice_map();
    $quality_choice_map = nice_hair_get_custom_hair_quality_choice_map();
    $texture_choice_map = nice_hair_get_custom_hair_texture_choice_map();
    $length_keys = nice_hair_get_custom_hair_allowed_lengths($resolved);
    $quality_keys = nice_hair_get_custom_hair_allowed_qualities($resolved);
    $texture_keys = nice_hair_get_custom_hair_allowed_textures($resolved);
    $weight_config = nice_hair_get_custom_hair_weight_config($resolved);
    $selection = nice_hair_resolve_custom_hair_selection($resolved);

    $length_options = [];
    $quality_options = [];
    $texture_options = [];
    $color_gallery_index = 0;

    foreach ($color_options as $index => $color_option) {
        if (! is_array($color_option)) {
            continue;
        }

        $main_image = is_array($color_option['main_image'] ?? null) ? $color_option['main_image'] : null;
        $color_options[$index]['gallery_index'] = $main_image !== null && ! empty($main_image['ID'])
            ? $color_gallery_index++
            : null;
    }

    foreach ($length_keys as $length_key) {
        $length_options[] = [
            'key' => $length_key,
            'label' => (string) ($length_choice_map[$length_key] ?? $length_key),
        ];
    }

    foreach ($quality_keys as $quality_key) {
        $quality_options[] = [
            'key' => $quality_key,
            'label' => (string) ($quality_choice_map[$quality_key] ?? $quality_key),
        ];
    }

    foreach ($texture_keys as $texture_key) {
        $texture_options[] = [
            'key' => $texture_key,
            'label' => (string) ($texture_choice_map[$texture_key] ?? $texture_key),
        ];
    }

    $base_price_map = [];

    foreach ($quality_keys as $quality_key) {
        foreach ($length_keys as $length_key) {
            $base_price = nice_hair_get_custom_hair_base_price_per_gram($quality_key, $length_key);

            if ($base_price !== null) {
                $base_price_map[$quality_key][$length_key] = $base_price;
            }
        }
    }

    return [
        'product_form' => $product_form,
        'color_options' => $color_options,
        'length_options' => $length_options,
        'quality_options' => $quality_options,
        'texture_options' => $texture_options,
        'weight_config' => $weight_config,
        'base_price_map' => $base_price_map,
        'default_selection' => $selection['selection'] ?? [],
        'selected_color' => $selection['selected_color'] ?? null,
        'selected_price_html' => (string) ($selection['price_html'] ?? ''),
        'selected_total_price' => isset($selection['total_price']) && is_numeric($selection['total_price'])
            ? (float) $selection['total_price']
            : null,
        'price_note' => (string) ($product_form['support_copy'] ?? ''),
        'is_complete' => (bool) ($selection['is_complete'] ?? false),
        'selection' => $selection,
    ];
}

function nice_hair_get_term_field_value(string $field_name, WP_Term|int|null $term = null, mixed $fallback = null): mixed
{
    $resolved_term = null;

    if ($term instanceof WP_Term) {
        $resolved_term = $term;
    } elseif (is_int($term) && $term > 0) {
        $candidate = get_term($term);
        $resolved_term = $candidate instanceof WP_Term ? $candidate : null;
    }

    if (! $resolved_term instanceof WP_Term) {
        return $fallback;
    }

    if (function_exists('get_field')) {
        $value = get_field($field_name, 'term_' . $resolved_term->term_id);

        if (function_exists('nice_hair_acf_has_value') ? nice_hair_acf_has_value($value) : ($value !== null && $value !== false && $value !== '' && $value !== [])) {
            return $value;
        }
    }

    $meta_value = get_term_meta($resolved_term->term_id, $field_name, true);

    if (function_exists('nice_hair_acf_has_value') ? nice_hair_acf_has_value($meta_value) : ($meta_value !== null && $meta_value !== false && $meta_value !== '' && $meta_value !== [])) {
        return $meta_value;
    }

    return $fallback;
}

function nice_hair_normalize_media_field_value(mixed $value): ?array
{
    if (is_array($value) && ! empty($value['url'])) {
        return $value;
    }

    if (! is_numeric($value)) {
        return null;
    }

    $attachment_id = (int) $value;

    if ($attachment_id <= 0) {
        return null;
    }

    $url = wp_get_attachment_image_url($attachment_id, 'full');

    if (! is_string($url) || $url === '') {
        return null;
    }

    return [
        'ID'    => $attachment_id,
        'id'    => $attachment_id,
        'url'   => $url,
        'alt'   => (string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true),
        'title' => get_the_title($attachment_id),
    ];
}

function nice_hair_get_product_how_to_use_fallback_data(WC_Product|int|null $product = null): array
{
    $resolved = nice_hair_resolve_product($product);

    if (! $resolved instanceof WC_Product) {
        return [
            'image'       => null,
            'text'        => '',
            'has_image'   => false,
            'has_text'    => false,
            'has_content' => false,
            'source'      => '',
            'term_id'     => 0,
            'term_name'   => '',
        ];
    }

    $image_value = function_exists('get_field')
        ? get_field('nh_product_how_to_use_image', $resolved->get_id())
        : get_post_meta($resolved->get_id(), 'nh_product_how_to_use_image', true);
    $text_value = function_exists('get_field')
        ? get_field('nh_product_how_to_use_text', $resolved->get_id())
        : get_post_meta($resolved->get_id(), 'nh_product_how_to_use_text', true);

    $image = nice_hair_normalize_media_field_value($image_value);
    $text = is_string($text_value) ? trim($text_value) : trim((string) $text_value);
    $has_image = $image !== null;
    $has_text = $text !== '';

    return [
        'image'       => $image,
        'text'        => $text,
        'has_image'   => $has_image,
        'has_text'    => $has_text,
        'has_content' => $has_image || $has_text,
        'source'      => ($has_image || $has_text) ? 'product_fallback' : '',
        'term_id'     => 0,
        'term_name'   => '',
    ];
}

function nice_hair_get_product_how_to_use_data(WC_Product|int|null $product = null): array
{
    $resolved = nice_hair_resolve_product($product);

    if (! $resolved instanceof WC_Product) {
        return nice_hair_get_product_how_to_use_fallback_data(null);
    }

    $fallback = nice_hair_get_product_how_to_use_fallback_data($resolved);

    if (nice_hair_product_is_family('ready_to_install', $resolved)) {
        $extension_type_term = nice_hair_get_primary_product_attribute_term($resolved, 'pa_extension_type');

        if ($extension_type_term instanceof WP_Term) {
            $image = nice_hair_normalize_media_field_value(
                nice_hair_get_term_field_value('nh_extension_how_to_use_image', $extension_type_term)
            );
            $text = trim((string) nice_hair_get_term_field_value('nh_extension_how_to_use_text', $extension_type_term, ''));
            $has_image = $image !== null;
            $has_text = $text !== '';

            if ($has_image || $has_text) {
                return [
                    'image'       => $image,
                    'text'        => $text,
                    'has_image'   => $has_image,
                    'has_text'    => $has_text,
                    'has_content' => true,
                    'source'      => 'term',
                    'term_id'     => (int) $extension_type_term->term_id,
                    'term_name'   => (string) $extension_type_term->name,
                ];
            }

            $fallback['term_id'] = (int) $extension_type_term->term_id;
            $fallback['term_name'] = (string) $extension_type_term->name;
        }
    }

    return $fallback;
}

function nice_hair_normalize_unique_product_inventory(int $post_id): void
{
    if (! function_exists('wc_get_product')) {
        return;
    }

    $product = wc_get_product($post_id);

    if (! $product instanceof WC_Product || ! nice_hair_is_unique_item_product($product)) {
        return;
    }

    $product->set_sold_individually(true);
    $product->set_manage_stock(true);

    $stock_quantity = $product->get_stock_quantity();
    $stock_status = $product->get_stock_status();

    if ($stock_quantity === null) {
        $stock_quantity = $stock_status === 'outofstock' ? 0 : 1;
    }

    $stock_quantity = (int) $stock_quantity;

    if ($stock_quantity <= 0) {
        $product->set_stock_quantity(0);
        $product->set_stock_status('outofstock');
    } else {
        $product->set_stock_quantity(1);
        $product->set_stock_status('instock');
    }

    $product->save();
}

add_action('acf/save_post', function (mixed $post_id): void {
    static $is_syncing = false;

    if ($is_syncing || ! is_numeric($post_id)) {
        return;
    }

    $post_id = (int) $post_id;

    if ($post_id <= 0 || get_post_type($post_id) !== 'product') {
        return;
    }

    $is_syncing = true;

    try {
        nice_hair_normalize_unique_product_inventory($post_id);
    } finally {
        $is_syncing = false;
    }
}, 30);
