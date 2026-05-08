<?php
/**
 * One-shot Custom Hair test-product seeder.
 *
 * Run via:
 * wp eval-file wp-content/themes/nice-hair/inc/woocommerce/seed-custom-hair.php
 *
 * Safe to re-run:
 * - creates or updates the same TEST-CH products
 * - reuses imported faker images from Media Library
 * - does not touch products outside the TEST dataset
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('WooCommerce')) {
    echo "WooCommerce is not active.\n";
    return;
}

if (! function_exists('update_field')) {
    echo "ACF is required for the Custom Hair seeder.\n";
    return;
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

if (! function_exists('nh_seed_custom_log')) {
    function nh_seed_custom_log(string $message): void
    {
        echo $message . "\n";
    }
}

if (! function_exists('nh_seed_custom_get_root_category_id')) {
    function nh_seed_custom_get_root_category_id(): int
    {
        $term = term_exists('Custom Hair', 'product_cat');

        if (! $term) {
            $term = wp_insert_term('Custom Hair', 'product_cat');
            nh_seed_custom_log('Created category: Custom Hair');
        }

        if (is_wp_error($term)) {
            return 0;
        }

        return is_array($term) ? (int) $term['term_id'] : (int) $term;
    }
}

if (! function_exists('nh_seed_custom_find_term_id')) {
    function nh_seed_custom_find_term_id(string $taxonomy, string $term_name): int
    {
        $term = term_exists($term_name, $taxonomy);

        if (! $term) {
            $inserted = wp_insert_term($term_name, $taxonomy);

            if (is_wp_error($inserted)) {
                nh_seed_custom_log(sprintf('Failed to create term "%s" in %s: %s', $term_name, $taxonomy, $inserted->get_error_message()));

                return 0;
            }

            $term = $inserted;
            nh_seed_custom_log(sprintf('Created term "%s" in %s', $term_name, $taxonomy));
        }

        return is_array($term) ? (int) $term['term_id'] : (int) $term;
    }
}

if (! function_exists('nh_seed_custom_import_image')) {
    function nh_seed_custom_import_image(string $file_path, string $source_meta_key): int
    {
        if (! is_file($file_path)) {
            nh_seed_custom_log(sprintf('Image not found: %s', $file_path));

            return 0;
        }

        $source_key = wp_normalize_path(str_replace(wp_normalize_path(get_template_directory()) . '/', '', wp_normalize_path($file_path)));
        $existing = get_posts([
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_query'     => [
                'relation' => 'OR',
                [
                    'key'   => $source_meta_key,
                    'value' => $source_key,
                ],
                [
                    'key'   => '_nh_seed_ready_image_source',
                    'value' => $source_key,
                ],
                [
                    'key'   => '_nh_seed_exclusive_image_source',
                    'value' => $source_key,
                ],
            ],
        ]);

        if (is_array($existing) && isset($existing[0])) {
            update_post_meta((int) $existing[0], $source_meta_key, $source_key);

            return (int) $existing[0];
        }

        $contents = file_get_contents($file_path);

        if ($contents === false) {
            nh_seed_custom_log(sprintf('Failed to read image contents: %s', $file_path));

            return 0;
        }

        $upload = wp_upload_bits(wp_basename($file_path), null, $contents);

        if (! empty($upload['error'])) {
            nh_seed_custom_log(sprintf('Failed to upload image "%s": %s', $file_path, (string) $upload['error']));

            return 0;
        }

        $filetype = wp_check_filetype($upload['file'], null);
        $attachment_id = wp_insert_attachment([
            'post_mime_type' => $filetype['type'] ?? 'image/jpeg',
            'post_title'     => sanitize_text_field(pathinfo($file_path, PATHINFO_FILENAME)),
            'post_content'   => '',
            'post_status'    => 'inherit',
        ], $upload['file']);

        if (! $attachment_id || is_wp_error($attachment_id)) {
            nh_seed_custom_log(sprintf('Failed to insert attachment for: %s', $file_path));

            return 0;
        }

        $metadata = wp_generate_attachment_metadata((int) $attachment_id, $upload['file']);

        if (! is_wp_error($metadata)) {
            wp_update_attachment_metadata((int) $attachment_id, $metadata);
        }

        update_post_meta((int) $attachment_id, $source_meta_key, $source_key);
        nh_seed_custom_log(sprintf('Imported custom image: %s', wp_basename($file_path)));

        return (int) $attachment_id;
    }
}

if (! function_exists('nh_seed_custom_get_image_ids')) {
    function nh_seed_custom_get_image_ids(string $source_meta_key): array
    {
        $directory = trailingslashit(get_template_directory()) . 'assets/images/faker/ready';
        $paths = glob($directory . '/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE);

        if (! is_array($paths) || $paths === []) {
            nh_seed_custom_log('No faker images found in assets/images/faker/ready.');

            return [];
        }

        sort($paths, SORT_NATURAL | SORT_FLAG_CASE);

        $image_ids = [];

        foreach ($paths as $path) {
            $attachment_id = nh_seed_custom_import_image((string) $path, $source_meta_key);

            if ($attachment_id > 0) {
                $image_ids[] = $attachment_id;
            }
        }

        return array_values(array_unique(array_filter(array_map('intval', $image_ids))));
    }
}

if (! function_exists('nh_seed_custom_set_acf_value')) {
    function nh_seed_custom_set_acf_value(int $post_id, string $field_key, string $field_name, mixed $value): void
    {
        if (function_exists('update_field')) {
            update_field($field_key, $value, $post_id);
            return;
        }

        if ($value === '' || $value === 0 || $value === null || $value === []) {
            delete_post_meta($post_id, $field_name);
            delete_post_meta($post_id, '_' . $field_name);
            return;
        }

        update_post_meta($post_id, $field_name, $value);
    }
}

if (! function_exists('nh_seed_custom_set_term_acf_value')) {
    function nh_seed_custom_set_term_acf_value(int $term_id, string $field_key, string $field_name, mixed $value): void
    {
        if ($term_id <= 0) {
            return;
        }

        $term_object_id = 'term_' . $term_id;

        if (function_exists('update_field')) {
            update_field($field_key, $value, $term_object_id);
            return;
        }

        if ($value === '' || $value === 0 || $value === null || $value === []) {
            delete_term_meta($term_id, $field_name);
            delete_term_meta($term_id, '_' . $field_name);
            return;
        }

        update_term_meta($term_id, $field_name, $value);
    }
}

if (! function_exists('nh_seed_custom_assign_extension_type')) {
    function nh_seed_custom_assign_extension_type(WC_Product_Simple $product, string $term_name): int
    {
        $product_id = $product->get_id();

        if (! taxonomy_exists('pa_extension_type')) {
            nh_seed_custom_log('Taxonomy pa_extension_type is missing.');
            return 0;
        }

        $term_id = nh_seed_custom_find_term_id('pa_extension_type', $term_name);

        if ($term_id <= 0) {
            return 0;
        }

        wp_set_object_terms($product_id, [$term_name], 'pa_extension_type', false);

        $attribute_id = wc_attribute_taxonomy_id_by_name('extension_type');

        if ($attribute_id > 0) {
            $attribute = new WC_Product_Attribute();
            $attribute->set_id($attribute_id);
            $attribute->set_name('pa_extension_type');
            $attribute->set_options([$term_id]);
            $attribute->set_visible(true);
            $attribute->set_variation(false);
            $product->set_attributes([$attribute]);
            $product->save();
        }

        return $term_id;
    }
}

if (! function_exists('nh_seed_custom_get_existing_product')) {
    function nh_seed_custom_get_existing_product(string $sku, string $dataset_meta_key, string $dataset_key): ?WC_Product_Simple
    {
        $existing_id = function_exists('wc_get_product_id_by_sku')
            ? (int) wc_get_product_id_by_sku($sku)
            : 0;

        if ($existing_id <= 0) {
            return null;
        }

        $existing_dataset = (string) get_post_meta($existing_id, $dataset_meta_key, true);

        if ($existing_dataset !== '' && $existing_dataset !== $dataset_key) {
            nh_seed_custom_log(sprintf('SKU collision detected, skipping foreign product with SKU %s', $sku));
            return null;
        }

        $existing_product = wc_get_product($existing_id);

        if (! $existing_product instanceof WC_Product_Simple) {
            nh_seed_custom_log(sprintf('Existing product with SKU %s is not simple, skipping', $sku));
            return null;
        }

        return $existing_product;
    }
}

if (! function_exists('nh_seed_custom_build_color_options')) {
    function nh_seed_custom_build_color_options(array $palette, array $image_ids, int $seed = 0): array
    {
        $rows = [];
        $image_count = count($image_ids);

        foreach ($palette as $index => $color) {
            if (! is_array($color)) {
                continue;
            }

            $rows[] = [
                'color_label' => (string) ($color['label'] ?? ''),
                'color_value' => (string) ($color['value'] ?? ''),
                'color_group' => (string) ($color['group'] ?? ''),
                'main_image'  => $image_count > 0 ? (int) $image_ids[($seed + $index) % $image_count] : 0,
            ];
        }

        return $rows;
    }
}

if (! function_exists('nh_seed_custom_calculate_default_price')) {
    function nh_seed_custom_calculate_default_price(string $form_key, string $quality_key = 'lux', string $length_key = '40', int $weight = 30): float
    {
        $base_price = function_exists('nice_hair_get_custom_hair_base_price_per_gram')
            ? nice_hair_get_custom_hair_base_price_per_gram($quality_key, $length_key)
            : null;
        $surcharge = function_exists('nice_hair_get_product_form_surcharge_per_gram')
            ? nice_hair_get_product_form_surcharge_per_gram($form_key)
            : null;

        if ($base_price === null || $surcharge === null) {
            return 0.0;
        }

        return round(($base_price + $surcharge) * $weight, 2);
    }
}

$dataset_key = 'nh_custom_hair_test_v1';
$dataset_meta_key = '_nh_seed_dataset';
$item_meta_key = '_nh_seed_item_key';
$image_source_meta_key = '_nh_seed_custom_image_source';
$video_url = 'https://www.youtube.com/watch?v=ysz5S6PUM-U';
$root_category_id = nh_seed_custom_get_root_category_id();
$image_ids = nh_seed_custom_get_image_ids($image_source_meta_key);

if ($root_category_id <= 0) {
    nh_seed_custom_log('Custom Hair category is missing and could not be created.');
    return;
}

if (count($image_ids) < 3) {
    nh_seed_custom_log('At least 3 images are required for the Custom Hair test dataset.');
    return;
}

$default_lengths = ['40', '50', '60', '70', '80'];
$default_qualities = ['lux', 'premium'];
$default_textures = ['soft_straight', 'silky_wavy', 'amazing_curly'];
$color_palette = [
    ['label' => '#1', 'value' => '1', 'group' => 'dark'],
    ['label' => '#1B', 'value' => '1b', 'group' => 'dark'],
    ['label' => '#2', 'value' => '2', 'group' => 'dark'],
    ['label' => '#4', 'value' => '4', 'group' => 'middle'],
    ['label' => '#6', 'value' => '6', 'group' => 'middle'],
    ['label' => '#8', 'value' => '8', 'group' => 'middle'],
    ['label' => '#10', 'value' => '10', 'group' => 'middle'],
    ['label' => '#18', 'value' => '18', 'group' => 'light'],
    ['label' => '#24', 'value' => '24', 'group' => 'light'],
    ['label' => '#60', 'value' => '60', 'group' => 'light'],
    ['label' => '#613', 'value' => '613', 'group' => 'light'],
    ['label' => '#18/60', 'value' => '18-60', 'group' => 'light'],
];
$default_labels = function_exists('nice_hair_get_default_product_form_labels')
    ? nice_hair_get_default_product_form_labels()
    : [];

$items = [
    ['key' => 'bulk', 'sku' => 'TEST-CH-001', 'title' => 'Bulk', 'term_name' => $default_labels['bulk'] ?? 'Bulk', 'video' => false, 'how_to_use_mode' => 'none'],
    ['key' => 'biotape', 'sku' => 'TEST-CH-002', 'title' => 'Biotape', 'term_name' => $default_labels['biotape'] ?? 'Biotape', 'video' => true, 'how_to_use_mode' => 'both'],
    ['key' => 'hand_tied_weft', 'sku' => 'TEST-CH-003', 'title' => 'Hand-tied weft', 'term_name' => $default_labels['hand_tied_weft'] ?? 'Hand-tied weft', 'video' => false, 'how_to_use_mode' => 'text'],
    ['key' => 'machine_weft', 'sku' => 'TEST-CH-004', 'title' => 'Machine weft', 'term_name' => $default_labels['machine_weft'] ?? 'Machine weft', 'video' => true, 'how_to_use_mode' => 'none'],
    ['key' => 'machine_weft_clip_in', 'sku' => 'TEST-CH-005', 'title' => 'Clip-in', 'term_name' => $default_labels['machine_weft_clip_in'] ?? 'Machine weft clip in', 'video' => false, 'how_to_use_mode' => 'image'],
    ['key' => 'genius_weft', 'sku' => 'TEST-CH-006', 'title' => 'Genius weft', 'term_name' => $default_labels['genius_weft'] ?? 'Genius weft', 'video' => true, 'how_to_use_mode' => 'both'],
    ['key' => 'flat_weft', 'sku' => 'TEST-CH-007', 'title' => 'Flat weft', 'term_name' => $default_labels['flat_weft'] ?? 'Flat weft', 'video' => false, 'how_to_use_mode' => 'none'],
    ['key' => 'flat_keratin_bonds', 'sku' => 'TEST-CH-008', 'title' => 'Flat keratin bonds', 'term_name' => $default_labels['flat_keratin_bonds'] ?? 'Flat keratin bonds', 'video' => true, 'how_to_use_mode' => 'text'],
    ['key' => 'nano_bonds_with_metal_round_tip', 'sku' => 'TEST-CH-009', 'title' => 'Nano bonds with metal round tip', 'term_name' => $default_labels['nano_bonds_with_metal_round_tip'] ?? 'Nano bonds with metal round tip', 'video' => false, 'how_to_use_mode' => 'none'],
    ['key' => 'nano_bonds_with_metal_straight_tip', 'sku' => 'TEST-CH-010', 'title' => 'Nano bonds with metal straight tip', 'term_name' => $default_labels['nano_bonds_with_metal_straight_tip'] ?? 'Nano bonds with metal straight tip', 'video' => false, 'how_to_use_mode' => 'image'],
    ['key' => 'nano_bonds_with_plastic_tip', 'sku' => 'TEST-CH-011', 'title' => 'Nano bonds with plastic tip', 'term_name' => $default_labels['nano_bonds_with_plastic_tip'] ?? 'Nano bonds with plastic tip', 'video' => true, 'how_to_use_mode' => 'none'],
    ['key' => 'nano_bonds_with_thread_tip', 'sku' => 'TEST-CH-012', 'title' => 'Nano bonds with thread tip', 'term_name' => $default_labels['nano_bonds_with_thread_tip'] ?? 'Nano bonds with thread tip', 'video' => false, 'how_to_use_mode' => 'text'],
    ['key' => 'i_tip_round_tips', 'sku' => 'TEST-CH-013', 'title' => 'I-tip round tips', 'term_name' => $default_labels['i_tip_round_tips'] ?? 'I-tip round tips', 'video' => true, 'how_to_use_mode' => 'both'],
    ['key' => 'invisible_tapes', 'sku' => 'TEST-CH-014', 'title' => 'Invisible tapes', 'term_name' => $default_labels['invisible_tapes'] ?? 'Invisible tapes', 'video' => false, 'how_to_use_mode' => 'none'],
    ['key' => 'usual_tapes', 'sku' => 'TEST-CH-015', 'title' => 'Usual tapes', 'term_name' => $default_labels['usual_tapes'] ?? 'Usual tapes', 'video' => false, 'how_to_use_mode' => 'image'],
    ['key' => 'mini_tapes_butterflies', 'sku' => 'TEST-CH-016', 'title' => 'Mini tapes "butterflies"', 'term_name' => $default_labels['mini_tapes_butterflies'] ?? 'Mini tapes "butterflies"', 'video' => true, 'how_to_use_mode' => 'none'],
    ['key' => 'ponytail_with_ribbon', 'sku' => 'TEST-CH-017', 'title' => 'Ponytail with ribbon', 'term_name' => $default_labels['ponytail_with_ribbon'] ?? 'Ponytail with ribbon', 'video' => true, 'how_to_use_mode' => 'both'],
];

nh_seed_custom_log('Seeding Custom Hair test products...');

$created = 0;
$updated = 0;

foreach ($items as $index => $item) {
    $existing_product = nh_seed_custom_get_existing_product((string) $item['sku'], $dataset_meta_key, $dataset_key);
    $product = $existing_product ?: new WC_Product_Simple();
    $default_price = nh_seed_custom_calculate_default_price((string) $item['key'], 'lux', '40', 30);

    $product->set_name((string) $item['title']);
    $product->set_sku((string) $item['sku']);
    $product->set_status('publish');
    $product->set_catalog_visibility('visible');
    $product->set_category_ids([$root_category_id]);
    $product->set_regular_price((string) $default_price);
    $product->set_price((string) $default_price);
    $product->set_manage_stock(false);
    $product->set_stock_status('instock');
    $product->set_sold_individually(true);
    $product->set_short_description('Raw, unprocessed hair bundle — perfect for custom work or any extension method. Soft, strong, and natural.');
    $product->set_description(sprintf(
        '<p>%1$s</p><p>This TEST Custom Hair product exists to validate the configurator flow: color image switching, dynamic pricing by length / quality / texture / weight, and AJAX add-to-cart.</p>',
        esc_html((string) $item['title'])
    ));

    $product_id = $product->save();

    if ($product_id <= 0) {
        nh_seed_custom_log(sprintf('Failed to save product: %s', (string) $item['title']));
        continue;
    }

    $extension_term_id = nh_seed_custom_assign_extension_type($product, (string) $item['term_name']);

    $featured_image_id = $image_ids[$index % count($image_ids)];
    $gallery_image_ids = [
        $image_ids[($index + 1) % count($image_ids)],
        $image_ids[($index + 2) % count($image_ids)],
    ];

    $product->set_image_id($featured_image_id);
    $product->set_gallery_image_ids($gallery_image_ids);
    $product->save();

    update_post_meta($product_id, $dataset_meta_key, $dataset_key);
    update_post_meta($product_id, $item_meta_key, (string) $item['key']);

    nh_seed_custom_set_acf_value(
        $product_id,
        'field_nh_custom_hair_color_options',
        'nh_custom_hair_color_options',
        nh_seed_custom_build_color_options($color_palette, $image_ids, $index)
    );
    nh_seed_custom_set_acf_value(
        $product_id,
        'field_nh_custom_hair_available_lengths',
        'nh_custom_hair_available_lengths',
        $default_lengths
    );
    nh_seed_custom_set_acf_value(
        $product_id,
        'field_nh_custom_hair_available_qualities',
        'nh_custom_hair_available_qualities',
        $default_qualities
    );
    nh_seed_custom_set_acf_value(
        $product_id,
        'field_nh_custom_hair_available_textures',
        'nh_custom_hair_available_textures',
        $default_textures
    );
    nh_seed_custom_set_acf_value(
        $product_id,
        'field_nh_custom_hair_min_weight_grams',
        'nh_custom_hair_min_weight_grams',
        30
    );
    nh_seed_custom_set_acf_value(
        $product_id,
        'field_nh_custom_hair_weight_step_grams',
        'nh_custom_hair_weight_step_grams',
        10
    );
    nh_seed_custom_set_acf_value(
        $product_id,
        'field_nh_custom_hair_default_weight_grams',
        'nh_custom_hair_default_weight_grams',
        30
    );
    nh_seed_custom_set_acf_value(
        $product_id,
        'field_nh_product_video_url',
        'nh_product_video_url',
        (bool) $item['video'] ? $video_url : ''
    );

    $how_to_use_mode = (string) ($item['how_to_use_mode'] ?? 'none');
    $how_to_use_image_id = 0;
    $how_to_use_text = '';

    if (in_array($how_to_use_mode, ['image', 'both'], true)) {
        $how_to_use_image_id = $image_ids[($index + 2) % count($image_ids)];
    }

    if (in_array($how_to_use_mode, ['text', 'both'], true)) {
        $how_to_use_text = implode("\n", [
            sprintf('1. Choose the final %s form before calculating the order weight.', (string) $item['title']),
            '2. Match the selected color, length, quality and texture before confirming production.',
            '3. Use the final calculated weight as the source of truth for the order and quality check the finished bundle before delivery.',
        ]);
    }

    nh_seed_custom_set_acf_value(
        $product_id,
        'field_nh_product_how_to_use_image',
        'nh_product_how_to_use_image',
        $how_to_use_image_id
    );
    nh_seed_custom_set_acf_value(
        $product_id,
        'field_nh_product_how_to_use_text',
        'nh_product_how_to_use_text',
        $how_to_use_text
    );

    if ($extension_term_id > 0) {
        $term_preview = function_exists('get_field')
            ? get_field('nh_extension_preview_image', 'term_' . $extension_term_id)
            : get_term_meta($extension_term_id, 'nh_extension_preview_image', true);

        if (empty($term_preview)) {
            nh_seed_custom_set_term_acf_value(
                $extension_term_id,
                'field_nh_extension_preview_image',
                'nh_extension_preview_image',
                $featured_image_id
            );
        }
    }

    wc_delete_product_transients($product_id);

    if ($existing_product instanceof WC_Product_Simple) {
        $updated++;
        nh_seed_custom_log(sprintf('Updated product: %s (%d)', (string) $item['sku'], $product_id));
    } else {
        $created++;
        nh_seed_custom_log(sprintf('Created product: %s (%d)', (string) $item['sku'], $product_id));
    }
}

nh_seed_custom_log(sprintf('Custom Hair dataset complete. Created: %d, updated: %d, total planned: %d', $created, $updated, count($items)));
