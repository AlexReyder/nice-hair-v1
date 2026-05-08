<?php
/**
 * One-shot Exclusive Hair test-product seeder.
 *
 * Run via:
 * wp eval-file wp-content/themes/nice-hair/inc/woocommerce/seed-exclusive-hair.php
 *
 * Safe to re-run:
 * - creates or updates the same TEST-EXH products
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

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

if (! function_exists('nh_seed_exclusive_log')) {
    function nh_seed_exclusive_log(string $message): void
    {
        echo $message . "\n";
    }
}

if (! function_exists('nh_seed_exclusive_get_root_category_id')) {
    function nh_seed_exclusive_get_root_category_id(): int
    {
        $term = term_exists('Exclusive Hair', 'product_cat');

        if (! $term) {
            $term = wp_insert_term('Exclusive Hair', 'product_cat');
            nh_seed_exclusive_log('Created category: Exclusive Hair');
        }

        if (is_wp_error($term)) {
            return 0;
        }

        return is_array($term) ? (int) $term['term_id'] : (int) $term;
    }
}

if (! function_exists('nh_seed_exclusive_find_term_id')) {
    function nh_seed_exclusive_find_term_id(string $taxonomy, string $term_name): int
    {
        $term = term_exists($term_name, $taxonomy);

        if (! $term) {
            $inserted = wp_insert_term($term_name, $taxonomy);

            if (is_wp_error($inserted)) {
                nh_seed_exclusive_log(sprintf('Failed to create term "%s" in %s: %s', $term_name, $taxonomy, $inserted->get_error_message()));

                return 0;
            }

            $term = $inserted;
            nh_seed_exclusive_log(sprintf('Created term "%s" in %s', $term_name, $taxonomy));
        }

        return is_array($term) ? (int) $term['term_id'] : (int) $term;
    }
}

if (! function_exists('nh_seed_exclusive_import_image')) {
    function nh_seed_exclusive_import_image(string $file_path, string $source_meta_key): int
    {
        if (! is_file($file_path)) {
            nh_seed_exclusive_log(sprintf('Image not found: %s', $file_path));

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
            ],
        ]);

        if (is_array($existing) && isset($existing[0])) {
            update_post_meta((int) $existing[0], $source_meta_key, $source_key);

            return (int) $existing[0];
        }

        $contents = file_get_contents($file_path);

        if ($contents === false) {
            nh_seed_exclusive_log(sprintf('Failed to read image contents: %s', $file_path));

            return 0;
        }

        $upload = wp_upload_bits(wp_basename($file_path), null, $contents);

        if (! empty($upload['error'])) {
            nh_seed_exclusive_log(sprintf('Failed to upload image "%s": %s', $file_path, (string) $upload['error']));

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
            nh_seed_exclusive_log(sprintf('Failed to insert attachment for: %s', $file_path));

            return 0;
        }

        $metadata = wp_generate_attachment_metadata((int) $attachment_id, $upload['file']);

        if (! is_wp_error($metadata)) {
            wp_update_attachment_metadata((int) $attachment_id, $metadata);
        }

        update_post_meta((int) $attachment_id, $source_meta_key, $source_key);
        nh_seed_exclusive_log(sprintf('Imported exclusive image: %s', wp_basename($file_path)));

        return (int) $attachment_id;
    }
}

if (! function_exists('nh_seed_exclusive_get_image_ids')) {
    function nh_seed_exclusive_get_image_ids(string $source_meta_key): array
    {
        $directory = trailingslashit(get_template_directory()) . 'assets/images/faker/ready';
        $paths = glob($directory . '/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE);

        if (! is_array($paths) || $paths === []) {
            nh_seed_exclusive_log('No faker images found in assets/images/faker/ready.');

            return [];
        }

        sort($paths, SORT_NATURAL | SORT_FLAG_CASE);

        $image_ids = [];

        foreach ($paths as $path) {
            $attachment_id = nh_seed_exclusive_import_image((string) $path, $source_meta_key);

            if ($attachment_id > 0) {
                $image_ids[] = $attachment_id;
            }
        }

        return array_values(array_unique(array_filter(array_map('intval', $image_ids))));
    }
}

if (! function_exists('nh_seed_exclusive_set_acf_value')) {
    function nh_seed_exclusive_set_acf_value(int $post_id, string $field_key, string $field_name, mixed $value): void
    {
        if (function_exists('update_field')) {
            update_field($field_key, $value, $post_id);
            return;
        }

        if ($value === '' || $value === 0 || $value === null) {
            delete_post_meta($post_id, $field_name);
            delete_post_meta($post_id, '_' . $field_name);
            return;
        }

        update_post_meta($post_id, $field_name, $value);
    }
}

if (! function_exists('nh_seed_exclusive_assign_attributes')) {
    function nh_seed_exclusive_assign_attributes(WC_Product_Simple $product, array $attributes): void
    {
        $product_id = $product->get_id();
        $wc_attributes = [];

        foreach ($attributes as $taxonomy => $term_name) {
            if (! taxonomy_exists($taxonomy)) {
                nh_seed_exclusive_log(sprintf('Taxonomy missing, skipping attribute %s', $taxonomy));
                continue;
            }

            $term_id = nh_seed_exclusive_find_term_id($taxonomy, $term_name);

            if ($term_id <= 0) {
                continue;
            }

            wp_set_object_terms($product_id, [$term_name], $taxonomy, false);

            $attribute_id = wc_attribute_taxonomy_id_by_name(str_replace('pa_', '', $taxonomy));

            if ($attribute_id <= 0) {
                continue;
            }

            $attribute = new WC_Product_Attribute();
            $attribute->set_id($attribute_id);
            $attribute->set_name($taxonomy);
            $attribute->set_options([$term_id]);
            $attribute->set_visible(true);
            $attribute->set_variation(false);
            $wc_attributes[] = $attribute;
        }

        $product->set_attributes($wc_attributes);
    }
}

if (! function_exists('nh_seed_exclusive_get_existing_product')) {
    function nh_seed_exclusive_get_existing_product(string $sku, string $dataset_meta_key, string $dataset_key): ?WC_Product_Simple
    {
        $existing_id = function_exists('wc_get_product_id_by_sku')
            ? (int) wc_get_product_id_by_sku($sku)
            : 0;

        if ($existing_id <= 0) {
            return null;
        }

        $existing_dataset = (string) get_post_meta($existing_id, $dataset_meta_key, true);

        if ($existing_dataset !== '' && $existing_dataset !== $dataset_key) {
            nh_seed_exclusive_log(sprintf('SKU collision detected, skipping foreign product with SKU %s', $sku));
            return null;
        }

        $existing_product = wc_get_product($existing_id);

        if (! $existing_product instanceof WC_Product_Simple) {
            nh_seed_exclusive_log(sprintf('Existing product with SKU %s is not simple, skipping', $sku));
            return null;
        }

        return $existing_product;
    }
}

$dataset_key = 'nh_exclusive_hair_test_v1';
$dataset_meta_key = '_nh_seed_dataset';
$item_meta_key = '_nh_seed_item_key';
$image_source_meta_key = '_nh_seed_exclusive_image_source';
$video_url = 'https://www.youtube.com/watch?v=ysz5S6PUM-U';
$root_category_id = nh_seed_exclusive_get_root_category_id();
$image_ids = nh_seed_exclusive_get_image_ids($image_source_meta_key);

if ($root_category_id <= 0) {
    nh_seed_exclusive_log('Exclusive Hair category is missing and could not be created.');
    return;
}

if (count($image_ids) < 3) {
    nh_seed_exclusive_log('At least 3 images are required for the Exclusive Hair test dataset.');
    return;
}

$items = [
    [
        'key'              => 'exclusive-middle-soft-50-120',
        'sku'              => 'TEST-EXH-001',
        'title'            => 'Exclusive hair, #1700(middle), soft straight, 50 cm, 120 gr',
        'regular_price'    => 170,
        'sale_price'       => null,
        'base_lot_price'   => 170,
        'fixed_weight'     => 120,
        'featured'         => false,
        'sold'             => false,
        'video'            => true,
        'how_to_use_mode'  => 'both',
        'attributes'       => [
            'pa_texture'     => 'Soft straight',
            'pa_color_group' => 'Middle',
            'pa_length'      => '50 cm',
        ],
    ],
    [
        'key'              => 'exclusive-light-wavy-60-130',
        'sku'              => 'TEST-EXH-002',
        'title'            => 'Exclusive hair, #260(light), silky wavy, 60 cm, 130 gr',
        'regular_price'    => 188,
        'sale_price'       => null,
        'base_lot_price'   => 188,
        'fixed_weight'     => 130,
        'featured'         => false,
        'sold'             => false,
        'video'            => false,
        'how_to_use_mode'  => 'text',
        'attributes'       => [
            'pa_texture'     => 'Silky wavy',
            'pa_color_group' => 'Light',
            'pa_length'      => '60 cm',
        ],
    ],
    [
        'key'              => 'exclusive-dark-curly-70-140',
        'sku'              => 'TEST-EXH-003',
        'title'            => 'Exclusive hair, #1b(dark), amazing curly, 70 cm, 140 gr',
        'regular_price'    => 225,
        'sale_price'       => null,
        'base_lot_price'   => 225,
        'fixed_weight'     => 140,
        'featured'         => false,
        'sold'             => false,
        'video'            => true,
        'how_to_use_mode'  => 'none',
        'attributes'       => [
            'pa_texture'     => 'Amazing curly',
            'pa_color_group' => 'Dark',
            'pa_length'      => '70 cm',
        ],
    ],
    [
        'key'              => 'exclusive-light-soft-50-110',
        'sku'              => 'TEST-EXH-004',
        'title'            => 'Exclusive hair, #613(light), soft straight, 50 cm, 110 gr',
        'regular_price'    => 176,
        'sale_price'       => null,
        'base_lot_price'   => 176,
        'fixed_weight'     => 110,
        'featured'         => false,
        'sold'             => false,
        'video'            => false,
        'how_to_use_mode'  => 'none',
        'attributes'       => [
            'pa_texture'     => 'Soft straight',
            'pa_color_group' => 'Light',
            'pa_length'      => '50 cm',
        ],
    ],
    [
        'key'              => 'exclusive-middle-curly-60-150',
        'sku'              => 'TEST-EXH-005',
        'title'            => 'Exclusive hair, #12n(middle), amazing curly, 60 cm, 150 gr',
        'regular_price'    => 245,
        'sale_price'       => null,
        'base_lot_price'   => 245,
        'fixed_weight'     => 150,
        'featured'         => false,
        'sold'             => false,
        'video'            => false,
        'how_to_use_mode'  => 'image',
        'attributes'       => [
            'pa_texture'     => 'Amazing curly',
            'pa_color_group' => 'Middle',
            'pa_length'      => '60 cm',
        ],
    ],
    [
        'key'              => 'exclusive-dark-wavy-70-160-sale',
        'sku'              => 'TEST-EXH-006',
        'title'            => 'Exclusive hair, #2(dark), silky wavy, 70 cm, 160 gr',
        'regular_price'    => 265,
        'sale_price'       => 239,
        'base_lot_price'   => 265,
        'fixed_weight'     => 160,
        'featured'         => false,
        'sold'             => false,
        'video'            => false,
        'how_to_use_mode'  => 'both',
        'attributes'       => [
            'pa_texture'     => 'Silky wavy',
            'pa_color_group' => 'Dark',
            'pa_length'      => '70 cm',
        ],
    ],
    [
        'key'              => 'exclusive-light-curly-50-115-sale',
        'sku'              => 'TEST-EXH-007',
        'title'            => 'Exclusive hair, #18(light), amazing curly, 50 cm, 115 gr',
        'regular_price'    => 182,
        'sale_price'       => 164,
        'base_lot_price'   => 182,
        'fixed_weight'     => 115,
        'featured'         => false,
        'sold'             => false,
        'video'            => true,
        'how_to_use_mode'  => 'none',
        'attributes'       => [
            'pa_texture'     => 'Amazing curly',
            'pa_color_group' => 'Light',
            'pa_length'      => '50 cm',
        ],
    ],
    [
        'key'              => 'exclusive-middle-wavy-60-135-featured',
        'sku'              => 'TEST-EXH-008',
        'title'            => 'Exclusive hair, #8(middle), silky wavy, 60 cm, 135 gr',
        'regular_price'    => 214,
        'sale_price'       => null,
        'base_lot_price'   => 214,
        'fixed_weight'     => 135,
        'featured'         => true,
        'sold'             => false,
        'video'            => false,
        'how_to_use_mode'  => 'both',
        'attributes'       => [
            'pa_texture'     => 'Silky wavy',
            'pa_color_group' => 'Middle',
            'pa_length'      => '60 cm',
        ],
    ],
    [
        'key'              => 'exclusive-dark-soft-70-170-sale-featured',
        'sku'              => 'TEST-EXH-009',
        'title'            => 'Exclusive hair, #3(dark), soft straight, 70 cm, 170 gr',
        'regular_price'    => 285,
        'sale_price'       => 252,
        'base_lot_price'   => 285,
        'fixed_weight'     => 170,
        'featured'         => true,
        'sold'             => false,
        'video'            => true,
        'how_to_use_mode'  => 'text',
        'attributes'       => [
            'pa_texture'     => 'Soft straight',
            'pa_color_group' => 'Dark',
            'pa_length'      => '70 cm',
        ],
    ],
    [
        'key'              => 'exclusive-light-wavy-50-105',
        'sku'              => 'TEST-EXH-010',
        'title'            => 'Exclusive hair, #22(light), silky wavy, 50 cm, 105 gr',
        'regular_price'    => 168,
        'sale_price'       => null,
        'base_lot_price'   => 168,
        'fixed_weight'     => 105,
        'featured'         => false,
        'sold'             => false,
        'video'            => false,
        'how_to_use_mode'  => 'none',
        'attributes'       => [
            'pa_texture'     => 'Silky wavy',
            'pa_color_group' => 'Light',
            'pa_length'      => '50 cm',
        ],
    ],
    [
        'key'              => 'exclusive-middle-soft-60-125',
        'sku'              => 'TEST-EXH-011',
        'title'            => 'Exclusive hair, #7(middle), soft straight, 60 cm, 125 gr',
        'regular_price'    => 205,
        'sale_price'       => null,
        'base_lot_price'   => 205,
        'fixed_weight'     => 125,
        'featured'         => false,
        'sold'             => false,
        'video'            => false,
        'how_to_use_mode'  => 'image',
        'attributes'       => [
            'pa_texture'     => 'Soft straight',
            'pa_color_group' => 'Middle',
            'pa_length'      => '60 cm',
        ],
    ],
    [
        'key'              => 'exclusive-dark-curly-50-145',
        'sku'              => 'TEST-EXH-012',
        'title'            => 'Exclusive hair, #4(dark), amazing curly, 50 cm, 145 gr',
        'regular_price'    => 236,
        'sale_price'       => null,
        'base_lot_price'   => 236,
        'fixed_weight'     => 145,
        'featured'         => false,
        'sold'             => false,
        'video'            => true,
        'how_to_use_mode'  => 'none',
        'attributes'       => [
            'pa_texture'     => 'Amazing curly',
            'pa_color_group' => 'Dark',
            'pa_length'      => '50 cm',
        ],
    ],
    [
        'key'              => 'exclusive-light-soft-70-155-sold',
        'sku'              => 'TEST-EXH-013',
        'title'            => 'Exclusive hair, #1001(light), soft straight, 70 cm, 155 gr',
        'regular_price'    => 258,
        'sale_price'       => null,
        'base_lot_price'   => 258,
        'fixed_weight'     => 155,
        'featured'         => false,
        'sold'             => true,
        'video'            => false,
        'how_to_use_mode'  => 'none',
        'attributes'       => [
            'pa_texture'     => 'Soft straight',
            'pa_color_group' => 'Light',
            'pa_length'      => '70 cm',
        ],
    ],
];

nh_seed_exclusive_log('Seeding Exclusive Hair test products...');

$created = 0;
$updated = 0;

foreach ($items as $index => $item) {
    $existing_product = nh_seed_exclusive_get_existing_product((string) $item['sku'], $dataset_meta_key, $dataset_key);
    $product = $existing_product ?: new WC_Product_Simple();

    $product->set_name((string) $item['title']);
    $product->set_sku((string) $item['sku']);
    $product->set_status('publish');
    $product->set_catalog_visibility('visible');
    $product->set_category_ids([$root_category_id]);
    $product->set_regular_price((string) $item['regular_price']);
    $product->set_sale_price($item['sale_price'] !== null ? (string) $item['sale_price'] : '');
    $product->set_featured((bool) $item['featured']);
    $product->set_manage_stock(true);
    $product->set_sold_individually(true);
    $product->set_stock_quantity((bool) $item['sold'] ? 0 : 1);
    $product->set_stock_status((bool) $item['sold'] ? 'outofstock' : 'instock');
    $product->set_short_description(sprintf(
        'Unique raw hair bundle in %1$s texture, %2$s length and %3$s color group. This TEST item exists to validate Exclusive Hair archive filters, product-form pricing and guest checkout behavior.',
        (string) $item['attributes']['pa_texture'],
        (string) $item['attributes']['pa_length'],
        (string) $item['attributes']['pa_color_group']
    ));
    $product->set_description(sprintf(
        '<p>%1$s</p><p>This TEST Exclusive Hair product is a single unique lot prepared for QA of archive filters, product-form selection, shared sample previews, media states and sold/out-of-stock behavior.</p>',
        esc_html((string) $item['title'])
    ));

    $product_id = $product->save();

    if ($product_id <= 0) {
        nh_seed_exclusive_log(sprintf('Failed to save product: %s', (string) $item['title']));
        continue;
    }

    nh_seed_exclusive_assign_attributes($product, (array) $item['attributes']);

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

    nh_seed_exclusive_set_acf_value($product_id, 'field_nh_unique_item', 'nh_unique_item', 1);
    nh_seed_exclusive_set_acf_value($product_id, 'field_nh_base_lot_price', 'nh_base_lot_price', (float) $item['base_lot_price']);
    nh_seed_exclusive_set_acf_value($product_id, 'field_nh_fixed_weight_grams', 'nh_fixed_weight_grams', (float) $item['fixed_weight']);
    nh_seed_exclusive_set_acf_value(
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
            '1. Evaluate the raw bundle direction and select the final product form before starting.',
            '2. Keep texture and color distribution intact when sectioning the bundle for production.',
            '3. Complete the chosen transformation with consistent tension and check the finish before delivery.',
        ]);
    }

    nh_seed_exclusive_set_acf_value(
        $product_id,
        'field_nh_product_how_to_use_image',
        'nh_product_how_to_use_image',
        $how_to_use_image_id
    );
    nh_seed_exclusive_set_acf_value(
        $product_id,
        'field_nh_product_how_to_use_text',
        'nh_product_how_to_use_text',
        $how_to_use_text
    );

    wc_delete_product_transients($product_id);

    if ($existing_product instanceof WC_Product_Simple) {
        $updated++;
        nh_seed_exclusive_log(sprintf('Updated product: %s (%d)', (string) $item['sku'], $product_id));
    } else {
        $created++;
        nh_seed_exclusive_log(sprintf('Created product: %s (%d)', (string) $item['sku'], $product_id));
    }
}

nh_seed_exclusive_log(sprintf('Exclusive dataset complete. Created: %d, updated: %d, total planned: %d', $created, $updated, count($items)));
