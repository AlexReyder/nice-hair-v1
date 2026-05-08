<?php
/**
 * One-shot Ready to Install test-product seeder.
 *
 * Run via:
 * wp eval-file wp-content/themes/nice-hair/inc/woocommerce/seed-ready-to-install.php
 *
 * Safe to re-run:
 * - creates or updates the same 13 TEST-RTI products
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

if (! function_exists('nh_seed_ready_log')) {
    function nh_seed_ready_log(string $message): void
    {
        echo $message . "\n";
    }
}

if (! function_exists('nh_seed_ready_get_root_category_id')) {
    function nh_seed_ready_get_root_category_id(): int
    {
        $term = term_exists('Ready to Install', 'product_cat');

        if (! $term) {
            $term = wp_insert_term('Ready to Install', 'product_cat');
            nh_seed_ready_log('Created category: Ready to Install');
        }

        if (is_wp_error($term)) {
            return 0;
        }

        return is_array($term) ? (int) $term['term_id'] : (int) $term;
    }
}

if (! function_exists('nh_seed_ready_find_term_id')) {
    function nh_seed_ready_find_term_id(string $taxonomy, string $term_name): int
    {
        $term = term_exists($term_name, $taxonomy);

        if (! $term) {
            $inserted = wp_insert_term($term_name, $taxonomy);

            if (is_wp_error($inserted)) {
                nh_seed_ready_log(sprintf('Failed to create term "%s" in %s: %s', $term_name, $taxonomy, $inserted->get_error_message()));

                return 0;
            }

            $term = $inserted;
            nh_seed_ready_log(sprintf('Created term "%s" in %s', $term_name, $taxonomy));
        }

        return is_array($term) ? (int) $term['term_id'] : (int) $term;
    }
}

if (! function_exists('nh_seed_ready_import_image')) {
    function nh_seed_ready_import_image(string $file_path, string $source_meta_key): int
    {
        if (! is_file($file_path)) {
            nh_seed_ready_log(sprintf('Image not found: %s', $file_path));

            return 0;
        }

        $source_key = wp_normalize_path(str_replace(wp_normalize_path(get_template_directory()) . '/', '', wp_normalize_path($file_path)));
        $existing = get_posts([
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_key'       => $source_meta_key,
            'meta_value'     => $source_key,
        ]);

        if (is_array($existing) && isset($existing[0])) {
            return (int) $existing[0];
        }

        $contents = file_get_contents($file_path);

        if ($contents === false) {
            nh_seed_ready_log(sprintf('Failed to read image contents: %s', $file_path));

            return 0;
        }

        $upload = wp_upload_bits(wp_basename($file_path), null, $contents);

        if (! empty($upload['error'])) {
            nh_seed_ready_log(sprintf('Failed to upload image "%s": %s', $file_path, (string) $upload['error']));

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
            nh_seed_ready_log(sprintf('Failed to insert attachment for: %s', $file_path));

            return 0;
        }

        $metadata = wp_generate_attachment_metadata((int) $attachment_id, $upload['file']);

        if (! is_wp_error($metadata)) {
            wp_update_attachment_metadata((int) $attachment_id, $metadata);
        }

        update_post_meta((int) $attachment_id, $source_meta_key, $source_key);
        nh_seed_ready_log(sprintf('Imported ready image: %s', wp_basename($file_path)));

        return (int) $attachment_id;
    }
}

if (! function_exists('nh_seed_ready_get_image_ids')) {
    function nh_seed_ready_get_image_ids(string $source_meta_key): array
    {
        $directory = trailingslashit(get_template_directory()) . 'assets/images/faker/ready';
        $paths = glob($directory . '/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE);

        if (! is_array($paths) || $paths === []) {
            nh_seed_ready_log('No faker images found in assets/images/faker/ready.');

            return [];
        }

        sort($paths, SORT_NATURAL | SORT_FLAG_CASE);

        $image_ids = [];

        foreach ($paths as $path) {
            $attachment_id = nh_seed_ready_import_image((string) $path, $source_meta_key);

            if ($attachment_id > 0) {
                $image_ids[] = $attachment_id;
            }
        }

        return array_values(array_unique(array_filter(array_map('intval', $image_ids))));
    }
}

if (! function_exists('nh_seed_ready_set_acf_value')) {
    function nh_seed_ready_set_acf_value(int $post_id, string $field_key, string $field_name, mixed $value): void
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

if (! function_exists('nh_seed_ready_set_term_acf_value')) {
    function nh_seed_ready_set_term_acf_value(int $term_id, string $field_key, string $field_name, mixed $value): void
    {
        if ($term_id <= 0) {
            return;
        }

        $term_object_id = 'term_' . $term_id;

        if (function_exists('update_field')) {
            update_field($field_key, $value, $term_object_id);
            return;
        }

        if ($value === '' || $value === 0 || $value === null) {
            delete_term_meta($term_id, $field_name);
            delete_term_meta($term_id, '_' . $field_name);
            return;
        }

        update_term_meta($term_id, $field_name, $value);
    }
}

if (! function_exists('nh_seed_ready_assign_attributes')) {
    function nh_seed_ready_assign_attributes(WC_Product_Simple $product, array $attributes): void
    {
        $product_id = $product->get_id();
        $wc_attributes = [];

        foreach ($attributes as $taxonomy => $term_name) {
            if (! taxonomy_exists($taxonomy)) {
                nh_seed_ready_log(sprintf('Taxonomy missing, skipping attribute %s', $taxonomy));
                continue;
            }

            $term_id = nh_seed_ready_find_term_id($taxonomy, $term_name);

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

if (! function_exists('nh_seed_ready_get_existing_product')) {
    function nh_seed_ready_get_existing_product(string $sku, string $dataset_meta_key, string $dataset_key): ?WC_Product_Simple
    {
        $existing_id = function_exists('wc_get_product_id_by_sku')
            ? (int) wc_get_product_id_by_sku($sku)
            : 0;

        if ($existing_id <= 0) {
            return null;
        }

        $existing_dataset = (string) get_post_meta($existing_id, $dataset_meta_key, true);

        if ($existing_dataset !== '' && $existing_dataset !== $dataset_key) {
            nh_seed_ready_log(sprintf('SKU collision detected, skipping foreign product with SKU %s', $sku));
            return null;
        }

        $existing_product = wc_get_product($existing_id);

        if (! $existing_product instanceof WC_Product_Simple) {
            nh_seed_ready_log(sprintf('Existing product with SKU %s is not simple, skipping', $sku));
            return null;
        }

        return $existing_product;
    }
}

$dataset_key = 'nh_ready_to_install_test_v1';
$dataset_meta_key = '_nh_seed_dataset';
$item_meta_key = '_nh_seed_item_key';
$image_source_meta_key = '_nh_seed_ready_image_source';
$video_url = 'https://www.youtube.com/watch?v=ysz5S6PUM-U';
$root_category_id = nh_seed_ready_get_root_category_id();
$image_ids = nh_seed_ready_get_image_ids($image_source_meta_key);

if ($root_category_id <= 0) {
    nh_seed_ready_log('Ready to Install category is missing and could not be created.');
    return;
}

if (count($image_ids) < 3) {
    nh_seed_ready_log('At least 3 ready images are required for the test dataset.');
    return;
}

$shared_how_to_use_by_extension_type = [
    'Flat weft' => [
        'image_id' => $image_ids[0],
        'text'     => "1. Section the hair into clean horizontal rows.\n2. Place the flat weft close to the root line and secure the seam evenly.\n3. Check balance across the row before blending and final styling.",
    ],
    'Genius weft' => [
        'image_id' => $image_ids[1],
        'text'     => "1. Create a neat support row for the genius weft placement.\n2. Stitch the row with even tension and keep the seam flat.\n3. Blend the lengths and verify comfort before finishing.",
    ],
    'Invisible tapes' => [
        'image_id' => $image_ids[2],
        'text'     => "1. Prepare thin clean sections for the tape sandwich.\n2. Align both tape pieces symmetrically around the natural strand.\n3. Press the bond firmly and leave enough room for movement near the scalp.",
    ],
    'Mini tapes \"butterflies\"' => [
        'image_id' => $image_ids[0],
        'text'     => "1. Use small precise sections for the mini tape placement.\n2. Center each butterfly tape on the strand and press evenly.\n3. Blend the perimeter carefully so the attachment stays invisible.",
    ],
    'Hand-tied weft' => [
        'image_id' => $image_ids[1],
        'text'     => "1. Build a stable beaded row for the hand-tied weft.\n2. Sew the weft with even spacing to avoid bulk and tension.\n3. Check scalp comfort, balance and movement before styling.",
    ],
];

foreach ($shared_how_to_use_by_extension_type as $extension_type_name => $payload) {
    $extension_type_term_id = nh_seed_ready_find_term_id('pa_extension_type', $extension_type_name);

    if ($extension_type_term_id <= 0) {
        continue;
    }

    nh_seed_ready_set_term_acf_value(
        $extension_type_term_id,
        'field_nh_extension_how_to_use_image',
        'nh_extension_how_to_use_image',
        (int) ($payload['image_id'] ?? 0)
    );
    nh_seed_ready_set_term_acf_value(
        $extension_type_term_id,
        'field_nh_extension_how_to_use_text',
        'nh_extension_how_to_use_text',
        (string) ($payload['text'] ?? '')
    );
}

$items = [
    [
        'key'              => 'test-flat-weft-honey-lux',
        'sku'              => 'TEST-RTI-001',
        'title'            => 'TEST Flat weft, Honey(1B), Soft straight, 50 cm, 10 bundle, Hair Quality: Lux',
        'regular_price'    => 390,
        'sale_price'       => null,
        'featured'         => false,
        'sold'             => false,
        'video'            => true,
        'how_to_use'       => true,
        'attributes'       => [
            'pa_extension_type' => 'Flat weft',
            'pa_hair_quality'   => 'Lux',
            'pa_color_group'    => 'Light',
            'pa_texture'        => 'Soft straight',
            'pa_length'         => '50 cm',
        ],
    ],
    [
        'key'              => 'test-flat-weft-caramel-premium',
        'sku'              => 'TEST-RTI-002',
        'title'            => 'TEST Flat weft, Caramel(6), Silky wavy, 60 cm, 10 bundle, Hair Quality: Premium',
        'regular_price'    => 460,
        'sale_price'       => null,
        'featured'         => false,
        'sold'             => false,
        'video'            => false,
        'how_to_use'       => true,
        'attributes'       => [
            'pa_extension_type' => 'Flat weft',
            'pa_hair_quality'   => 'Premium',
            'pa_color_group'    => 'Middle',
            'pa_texture'        => 'Silky wavy',
            'pa_length'         => '60 cm',
        ],
    ],
    [
        'key'              => 'test-genius-weft-espresso-lux',
        'sku'              => 'TEST-RTI-003',
        'title'            => 'TEST Genius weft, Espresso(2), Soft straight, 70 cm, 1 bundle, Hair Quality: Lux',
        'regular_price'    => 520,
        'sale_price'       => null,
        'featured'         => false,
        'sold'             => false,
        'video'            => true,
        'how_to_use'       => false,
        'attributes'       => [
            'pa_extension_type' => 'Genius weft',
            'pa_hair_quality'   => 'Lux',
            'pa_color_group'    => 'Dark',
            'pa_texture'        => 'Soft straight',
            'pa_length'         => '70 cm',
        ],
    ],
    [
        'key'              => 'test-invisible-tapes-sandy-premium',
        'sku'              => 'TEST-RTI-004',
        'title'            => 'TEST Invisible tapes, Sandy Blonde(18), Silky wavy, 50 cm, 20 pcs, Hair Quality: Premium',
        'regular_price'    => 430,
        'sale_price'       => null,
        'featured'         => false,
        'sold'             => false,
        'video'            => false,
        'how_to_use'       => false,
        'attributes'       => [
            'pa_extension_type' => 'Invisible tapes',
            'pa_hair_quality'   => 'Premium',
            'pa_color_group'    => 'Light',
            'pa_texture'        => 'Silky wavy',
            'pa_length'         => '50 cm',
        ],
    ],
    [
        'key'              => 'test-mini-tapes-chestnut-lux',
        'sku'              => 'TEST-RTI-005',
        'title'            => 'TEST Mini tapes Butterfly, Chestnut(4), Soft straight, 60 cm, 20 pcs, Hair Quality: Lux',
        'regular_price'    => 415,
        'sale_price'       => null,
        'featured'         => false,
        'sold'             => false,
        'video'            => false,
        'how_to_use'       => false,
        'attributes'       => [
            'pa_extension_type' => 'Mini tapes "butterflies"',
            'pa_hair_quality'   => 'Lux',
            'pa_color_group'    => 'Middle',
            'pa_texture'        => 'Soft straight',
            'pa_length'         => '60 cm',
        ],
    ],
    [
        'key'              => 'test-hand-tied-jet-exclusive-sale',
        'sku'              => 'TEST-RTI-006',
        'title'            => 'TEST Hand-tied weft, Jet Black(1), Amazing curly, 60 cm, 1 bundle, Hair Quality: Exclusive',
        'regular_price'    => 610,
        'sale_price'       => 560,
        'featured'         => false,
        'sold'             => false,
        'video'            => false,
        'how_to_use'       => true,
        'attributes'       => [
            'pa_extension_type' => 'Hand-tied weft',
            'pa_hair_quality'   => 'Exclusive',
            'pa_color_group'    => 'Dark',
            'pa_texture'        => 'Amazing curly',
            'pa_length'         => '60 cm',
        ],
    ],
    [
        'key'              => 'test-flat-weft-golden-premium-sale',
        'sku'              => 'TEST-RTI-007',
        'title'            => 'TEST Flat weft, Golden Beige(12), Soft straight, 70 cm, 10 bundle, Hair Quality: Premium',
        'regular_price'    => 480,
        'sale_price'       => 435,
        'featured'         => false,
        'sold'             => false,
        'video'            => true,
        'how_to_use'       => false,
        'attributes'       => [
            'pa_extension_type' => 'Flat weft',
            'pa_hair_quality'   => 'Premium',
            'pa_color_group'    => 'Light',
            'pa_texture'        => 'Soft straight',
            'pa_length'         => '70 cm',
        ],
    ],
    [
        'key'              => 'test-genius-weft-mocha-featured',
        'sku'              => 'TEST-RTI-008',
        'title'            => 'TEST Genius weft, Mocha(5), Silky wavy, 50 cm, 1 bundle, Hair Quality: Exclusive',
        'regular_price'    => 575,
        'sale_price'       => null,
        'featured'         => true,
        'sold'             => false,
        'video'            => false,
        'how_to_use'       => true,
        'attributes'       => [
            'pa_extension_type' => 'Genius weft',
            'pa_hair_quality'   => 'Exclusive',
            'pa_color_group'    => 'Middle',
            'pa_texture'        => 'Silky wavy',
            'pa_length'         => '50 cm',
        ],
    ],
    [
        'key'              => 'test-invisible-tapes-ash-sale-featured',
        'sku'              => 'TEST-RTI-009',
        'title'            => 'TEST Invisible tapes, Ash Brown(7), Soft straight, 60 cm, 20 pcs, Hair Quality: Premium',
        'regular_price'    => 445,
        'sale_price'       => 399,
        'featured'         => true,
        'sold'             => false,
        'video'            => true,
        'how_to_use'       => false,
        'attributes'       => [
            'pa_extension_type' => 'Invisible tapes',
            'pa_hair_quality'   => 'Premium',
            'pa_color_group'    => 'Dark',
            'pa_texture'        => 'Soft straight',
            'pa_length'         => '60 cm',
        ],
    ],
    [
        'key'              => 'test-hand-tied-vanilla-lux',
        'sku'              => 'TEST-RTI-010',
        'title'            => 'TEST Hand-tied weft, Vanilla Blonde(22), Amazing curly, 70 cm, 1 bundle, Hair Quality: Lux',
        'regular_price'    => 540,
        'sale_price'       => null,
        'featured'         => false,
        'sold'             => false,
        'video'            => false,
        'how_to_use'       => false,
        'attributes'       => [
            'pa_extension_type' => 'Hand-tied weft',
            'pa_hair_quality'   => 'Lux',
            'pa_color_group'    => 'Light',
            'pa_texture'        => 'Amazing curly',
            'pa_length'         => '70 cm',
        ],
    ],
    [
        'key'              => 'test-mini-tapes-copper-premium',
        'sku'              => 'TEST-RTI-011',
        'title'            => 'TEST Mini tapes Butterfly, Copper(30), Silky wavy, 50 cm, 20 pcs, Hair Quality: Premium',
        'regular_price'    => 425,
        'sale_price'       => null,
        'featured'         => false,
        'sold'             => false,
        'video'            => false,
        'how_to_use'       => true,
        'attributes'       => [
            'pa_extension_type' => 'Mini tapes "butterflies"',
            'pa_hair_quality'   => 'Premium',
            'pa_color_group'    => 'Middle',
            'pa_texture'        => 'Silky wavy',
            'pa_length'         => '50 cm',
        ],
    ],
    [
        'key'              => 'test-flat-weft-natural-exclusive',
        'sku'              => 'TEST-RTI-012',
        'title'            => 'TEST Flat weft, Natural Brown(6N), Soft straight, 60 cm, 10 bundle, Hair Quality: Exclusive',
        'regular_price'    => 590,
        'sale_price'       => null,
        'featured'         => false,
        'sold'             => false,
        'video'            => false,
        'how_to_use'       => false,
        'attributes'       => [
            'pa_extension_type' => 'Flat weft',
            'pa_hair_quality'   => 'Exclusive',
            'pa_color_group'    => 'Dark',
            'pa_texture'        => 'Soft straight',
            'pa_length'         => '60 cm',
        ],
    ],
    [
        'key'              => 'test-invisible-tapes-pearl-sold',
        'sku'              => 'TEST-RTI-013',
        'title'            => 'TEST Invisible tapes, Pearl Blonde(613), Soft straight, 50 cm, 20 pcs, Hair Quality: Lux',
        'regular_price'    => 410,
        'sale_price'       => null,
        'featured'         => false,
        'sold'             => true,
        'video'            => false,
        'how_to_use'       => false,
        'attributes'       => [
            'pa_extension_type' => 'Invisible tapes',
            'pa_hair_quality'   => 'Lux',
            'pa_color_group'    => 'Light',
            'pa_texture'        => 'Soft straight',
            'pa_length'         => '50 cm',
        ],
    ],
];

nh_seed_ready_log('Seeding Ready to Install test products...');

$created = 0;
$updated = 0;

foreach ($items as $index => $item) {
    $existing_product = nh_seed_ready_get_existing_product((string) $item['sku'], $dataset_meta_key, $dataset_key);
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
        'Salon-ready %1$s in %2$s texture, %3$s length and %4$s quality. Prepared as a single unique item for guest checkout testing.',
        (string) $item['attributes']['pa_extension_type'],
        (string) $item['attributes']['pa_texture'],
        (string) $item['attributes']['pa_length'],
        (string) $item['attributes']['pa_hair_quality']
    ));
    $product->set_description(sprintf(
        '<p>%1$s</p><p>This TEST product exists only to validate archive filters, single-product layout, media states and guest checkout behavior in the Ready to Install flow.</p>',
        esc_html((string) $item['title'])
    ));

    $product_id = $product->save();

    if ($product_id <= 0) {
        nh_seed_ready_log(sprintf('Failed to save product: %s', (string) $item['title']));
        continue;
    }

    nh_seed_ready_assign_attributes($product, (array) $item['attributes']);

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

    nh_seed_ready_set_acf_value($product_id, 'field_nh_unique_item', 'nh_unique_item', 1);
    nh_seed_ready_set_acf_value(
        $product_id,
        'field_nh_product_video_url',
        'nh_product_video_url',
        (bool) $item['video'] ? $video_url : ''
    );
    nh_seed_ready_set_acf_value(
        $product_id,
        'field_nh_product_how_to_use_image',
        'nh_product_how_to_use_image',
        0
    );
    nh_seed_ready_set_acf_value(
        $product_id,
        'field_nh_product_how_to_use_text',
        'nh_product_how_to_use_text',
        ''
    );

    wc_delete_product_transients($product_id);

    if ($existing_product instanceof WC_Product_Simple) {
        $updated++;
        nh_seed_ready_log(sprintf('Updated product: %s (%d)', (string) $item['sku'], $product_id));
    } else {
        $created++;
        nh_seed_ready_log(sprintf('Created product: %s (%d)', (string) $item['sku'], $product_id));
    }
}

nh_seed_ready_log(sprintf('Ready dataset complete. Created: %d, updated: %d, total planned: %d', $created, $updated, count($items)));
