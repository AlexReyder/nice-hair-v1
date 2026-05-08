<?php
declare(strict_types=1);

set_time_limit(0);
error_reporting(E_ALL);

function nh_find_wp_load(string $startDir): string
{
    $dir = $startDir;

    while ($dir !== dirname($dir)) {
        $candidate = $dir . DIRECTORY_SEPARATOR . 'wp-load.php';
        if (is_file($candidate)) {
            return $candidate;
        }

        $dir = dirname($dir);
    }

    throw new RuntimeException('wp-load.php not found');
}

function nh_rrmdir(string $path): void
{
    if (!is_dir($path)) {
        return;
    }

    $items = scandir($path);
    if ($items === false) {
        return;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $child = $path . DIRECTORY_SEPARATOR . $item;
        if (is_dir($child)) {
            nh_rrmdir($child);
        } else {
            @unlink($child);
        }
    }

    @rmdir($path);
}

function nh_post_meta_should_export(string $metaKey): bool
{
    if (str_starts_with($metaKey, 'nh_') || str_starts_with($metaKey, '_nh_')) {
        return true;
    }

    return false;
}

function nh_recursive_collect_attachment_ids(mixed $value, array &$attachmentIds): void
{
    if (is_array($value)) {
        foreach ($value as $nestedValue) {
            nh_recursive_collect_attachment_ids($nestedValue, $attachmentIds);
        }

        return;
    }

    if (is_numeric($value)) {
        $attachmentId = (int) $value;
        if ($attachmentId > 0 && get_post_type($attachmentId) === 'attachment') {
            $attachmentIds[$attachmentId] = true;
        }
    }
}

function nh_export_post_meta(int $postId, array &$attachmentIds): array
{
    $result = [];
    $meta = get_post_meta($postId);

    foreach ($meta as $metaKey => $metaValues) {
        if (!nh_post_meta_should_export($metaKey)) {
            continue;
        }

        $normalizedValues = [];

        foreach ((array) $metaValues as $metaValue) {
            $unserialized = maybe_unserialize($metaValue);
            nh_recursive_collect_attachment_ids($unserialized, $attachmentIds);
            $normalizedValues[] = $unserialized;
        }

        $result[$metaKey] = count($normalizedValues) === 1 ? $normalizedValues[0] : $normalizedValues;
    }

    ksort($result);

    return $result;
}

function nh_queue_term_with_parents(WP_Term $term, array &$termIds): void
{
    $termIds[$term->term_id] = true;

    if ($term->parent > 0) {
        $parent = get_term($term->parent, $term->taxonomy);
        if ($parent instanceof WP_Term) {
            nh_queue_term_with_parents($parent, $termIds);
        }
    }
}

function nh_export_attachment(int $attachmentId): ?array
{
    $attachment = get_post($attachmentId);

    if (!$attachment instanceof WP_Post || $attachment->post_type !== 'attachment') {
        return null;
    }

    $relativePath = (string) get_post_meta($attachmentId, '_wp_attached_file', true);
    $absolutePath = get_attached_file($attachmentId);

    if (!$relativePath || !$absolutePath || !is_file($absolutePath)) {
        return [
            'local_id' => $attachmentId,
            'missing' => true,
            'relative_path' => $relativePath,
            'title' => $attachment->post_title,
        ];
    }

    return [
        'local_id' => $attachmentId,
        'relative_path' => wp_normalize_path($relativePath),
        'absolute_path' => wp_normalize_path($absolutePath),
        'title' => $attachment->post_title,
        'slug' => $attachment->post_name,
        'mime_type' => $attachment->post_mime_type,
        'caption' => $attachment->post_excerpt,
        'description' => $attachment->post_content,
        'alt' => (string) get_post_meta($attachmentId, '_wp_attachment_image_alt', true),
    ];
}

function nh_export_product_attributes(WC_Product $product, array &$termIds): array
{
    $result = [];

    foreach ($product->get_attributes() as $attribute) {
        $item = [
            'name' => $attribute->get_name(),
            'position' => $attribute->get_position(),
            'visible' => $attribute->get_visible(),
            'variation' => $attribute->get_variation(),
            'taxonomy' => $attribute->is_taxonomy(),
        ];

        if ($attribute->is_taxonomy()) {
            $terms = wc_get_product_terms($product->get_id(), $attribute->get_name(), ['fields' => 'all']);
            $item['options'] = [];

            foreach ($terms as $term) {
                if (!$term instanceof WP_Term) {
                    continue;
                }

                nh_queue_term_with_parents($term, $termIds);
                $item['options'][] = $term->slug;
            }
        } else {
            $item['options'] = array_values(array_map('strval', $attribute->get_options()));
        }

        $result[] = $item;
    }

    return $result;
}

function nh_export_product(WC_Product $product, array &$attachmentIds, array &$termIds): array
{
    $productId = $product->get_id();

    $categories = wp_get_post_terms($productId, 'product_cat', ['fields' => 'all']);
    foreach ($categories as $term) {
        if ($term instanceof WP_Term) {
            nh_queue_term_with_parents($term, $termIds);
        }
    }

    $tags = wp_get_post_terms($productId, 'product_tag', ['fields' => 'all']);
    foreach ($tags as $term) {
        if ($term instanceof WP_Term) {
            nh_queue_term_with_parents($term, $termIds);
        }
    }

    $imageId = $product->get_image_id();
    if ($imageId > 0) {
        $attachmentIds[$imageId] = true;
    }

    $galleryImageIds = array_map('intval', $product->get_gallery_image_ids());
    foreach ($galleryImageIds as $galleryImageId) {
        if ($galleryImageId > 0) {
            $attachmentIds[$galleryImageId] = true;
        }
    }

    $meta = nh_export_post_meta($productId, $attachmentIds);

    $result = [
        'local_id' => $productId,
        'type' => $product->get_type(),
        'name' => $product->get_name(),
        'slug' => $product->get_slug(),
        'status' => $product->get_status(),
        'menu_order' => $product->get_menu_order(),
        'featured' => $product->is_featured(),
        'catalog_visibility' => $product->get_catalog_visibility(),
        'description' => $product->get_description(),
        'short_description' => $product->get_short_description(),
        'sku' => $product->get_sku(),
        'regular_price' => $product->get_regular_price(),
        'sale_price' => $product->get_sale_price(),
        'manage_stock' => $product->get_manage_stock(),
        'stock_quantity' => $product->get_stock_quantity(),
        'stock_status' => $product->get_stock_status(),
        'backorders' => $product->get_backorders(),
        'sold_individually' => $product->get_sold_individually(),
        'virtual' => $product->is_virtual(),
        'downloadable' => $product->is_downloadable(),
        'weight' => $product->get_weight(),
        'length' => $product->get_length(),
        'width' => $product->get_width(),
        'height' => $product->get_height(),
        'tax_status' => $product->get_tax_status(),
        'tax_class' => $product->get_tax_class(),
        'image_local_id' => $imageId > 0 ? $imageId : 0,
        'gallery_local_ids' => $galleryImageIds,
        'category_slugs' => array_values(array_map(static fn (WP_Term $term): string => $term->slug, $categories)),
        'tag_slugs' => array_values(array_map(static fn (WP_Term $term): string => $term->slug, $tags)),
        'attributes' => nh_export_product_attributes($product, $termIds),
        'default_attributes' => $product->get_default_attributes(),
        'meta' => $meta,
        'variations' => [],
    ];

    if ($product->is_type('variable')) {
        foreach ($product->get_children() as $variationId) {
            $variation = wc_get_product($variationId);
            if (!$variation instanceof WC_Product_Variation) {
                continue;
            }

            $variationImageId = $variation->get_image_id();
            if ($variationImageId > 0) {
                $attachmentIds[$variationImageId] = true;
            }

            $variationMeta = nh_export_post_meta($variationId, $attachmentIds);

            $result['variations'][] = [
                'local_id' => $variationId,
                'slug' => $variation->get_slug(),
                'status' => $variation->get_status(),
                'menu_order' => $variation->get_menu_order(),
                'sku' => $variation->get_sku(),
                'regular_price' => $variation->get_regular_price(),
                'sale_price' => $variation->get_sale_price(),
                'manage_stock' => $variation->get_manage_stock(),
                'stock_quantity' => $variation->get_stock_quantity(),
                'stock_status' => $variation->get_stock_status(),
                'backorders' => $variation->get_backorders(),
                'virtual' => $variation->is_virtual(),
                'downloadable' => $variation->is_downloadable(),
                'description' => $variation->get_description(),
                'image_local_id' => $variationImageId > 0 ? $variationImageId : 0,
                'attributes' => $variation->get_attributes(),
                'meta' => $variationMeta,
            ];
        }
    }

    return $result;
}

$outputDir = $argv[1] ?? (__DIR__ . DIRECTORY_SEPARATOR . 'dist');
$outputDir = rtrim($outputDir, "\\/");

nh_rrmdir($outputDir);
if (!is_dir($outputDir) && !mkdir($outputDir, 0777, true) && !is_dir($outputDir)) {
    throw new RuntimeException('Unable to create export directory');
}

$wpLoad = nh_find_wp_load(__DIR__);
require_once $wpLoad;

if (!function_exists('wc_get_products')) {
    throw new RuntimeException('WooCommerce is not available');
}

$products = wc_get_products([
    'status' => ['publish'],
    'limit' => -1,
    'orderby' => 'ID',
    'order' => 'ASC',
    'return' => 'objects',
]);

$attachmentIds = [];
$termIds = [];
$exportedProducts = [];

foreach ($products as $product) {
    if (!$product instanceof WC_Product) {
        continue;
    }

    $exportedProducts[] = nh_export_product($product, $attachmentIds, $termIds);
}

$attributeTaxonomies = [];
foreach ((array) wc_get_attribute_taxonomies() as $attributeTaxonomy) {
    $attributeTaxonomies[] = [
        'name' => (string) $attributeTaxonomy->attribute_name,
        'label' => (string) $attributeTaxonomy->attribute_label,
        'type' => (string) $attributeTaxonomy->attribute_type,
        'orderby' => (string) $attributeTaxonomy->attribute_orderby,
        'public' => (int) $attributeTaxonomy->attribute_public,
    ];
}

$exportedTerms = [];
foreach (array_keys($termIds) as $termId) {
    $term = get_term($termId);
    if (!$term instanceof WP_Term) {
        continue;
    }

    $termMeta = [];
    foreach (get_term_meta($termId) as $metaKey => $metaValues) {
        if ($metaKey === 'product_count_product_cat') {
            continue;
        }

        $normalizedValues = [];
        foreach ((array) $metaValues as $metaValue) {
            $unserialized = maybe_unserialize($metaValue);
            nh_recursive_collect_attachment_ids($unserialized, $attachmentIds);
            $normalizedValues[] = $unserialized;
        }

        $termMeta[$metaKey] = count($normalizedValues) === 1 ? $normalizedValues[0] : $normalizedValues;
    }

    ksort($termMeta);

    $exportedTerms[] = [
        'local_id' => $term->term_id,
        'taxonomy' => $term->taxonomy,
        'name' => $term->name,
        'slug' => $term->slug,
        'description' => $term->description,
        'parent_local_id' => (int) $term->parent,
        'meta' => $termMeta,
    ];
}

usort(
    $exportedTerms,
    static fn (array $left, array $right): int => [$left['taxonomy'], $left['local_id']] <=> [$right['taxonomy'], $right['local_id']]
);

$attachments = [];
$uploads = wp_get_upload_dir();
$mediaZipPath = $outputDir . DIRECTORY_SEPARATOR . 'catalog-transfer-media.zip';
$zip = new ZipArchive();

if ($zip->open($mediaZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    throw new RuntimeException('Unable to create media zip');
}

foreach (array_keys($attachmentIds) as $attachmentId) {
    $attachmentData = nh_export_attachment($attachmentId);
    if ($attachmentData === null) {
        continue;
    }

    $attachments[] = $attachmentData;

    if (!empty($attachmentData['missing'])) {
        continue;
    }

    $zip->addFile($attachmentData['absolute_path'], $attachmentData['relative_path']);
}

$zip->close();

usort(
    $attachments,
    static fn (array $left, array $right): int => $left['local_id'] <=> $right['local_id']
);

$manifest = [
    'generated_at' => gmdate('c'),
    'source' => [
        'site_url' => home_url('/'),
        'uploads_base_url' => $uploads['baseurl'] ?? '',
        'uploads_base_dir' => wp_normalize_path($uploads['basedir'] ?? ''),
    ],
    'summary' => [
        'product_count' => count($exportedProducts),
        'term_count' => count($exportedTerms),
        'attachment_count' => count($attachments),
    ],
    'attributes' => $attributeTaxonomies,
    'terms' => $exportedTerms,
    'attachments' => $attachments,
    'products' => $exportedProducts,
];

$manifestPath = $outputDir . DIRECTORY_SEPARATOR . 'catalog-transfer.json';
file_put_contents(
    $manifestPath,
    wp_json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
);

fwrite(
    STDOUT,
    wp_json_encode(
        [
            'ok' => true,
            'manifest' => wp_normalize_path($manifestPath),
            'media_zip' => wp_normalize_path($mediaZipPath),
            'summary' => $manifest['summary'],
        ],
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    ) . PHP_EOL
);
