<?php
declare(strict_types=1);

set_time_limit(0);
ignore_user_abort(true);
error_reporting(E_ALL);

$expectedToken = 'nh-transfer-20260423-catalog-live';

const NH_TRANSFER_ATTACHMENT_MAP_OPTION = 'nh_catalog_transfer_attachment_map';
const NH_TRANSFER_TERM_MAP_OPTION = 'nh_catalog_transfer_term_map';
const NH_TRANSFER_PRODUCT_MAP_OPTION = 'nh_catalog_transfer_product_map';
const NH_TRANSFER_PREPARED_OPTION = 'nh_catalog_transfer_prepared';

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

function nh_json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function nh_fail(string $message, int $status = 500, array $extra = []): never
{
    nh_json_response(array_merge(['ok' => false, 'message' => $message], $extra), $status);
}

function nh_recursive_remap_local_ids(mixed $value, array $attachmentMap): mixed
{
    if (is_array($value)) {
        foreach ($value as $key => $nestedValue) {
            $value[$key] = nh_recursive_remap_local_ids($nestedValue, $attachmentMap);
        }

        return $value;
    }

    if (is_numeric($value)) {
        $intValue = (int) $value;
        if ($intValue > 0 && isset($attachmentMap[$intValue])) {
            return $attachmentMap[$intValue];
        }
    }

    return $value;
}

function nh_get_option_array(string $optionName): array
{
    $value = get_option($optionName, []);
    return is_array($value) ? $value : [];
}

function nh_live_counts(): array
{
    global $wpdb;

    return [
        'products' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish'"),
        'variations' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product_variation' AND post_status = 'publish'"),
        'attachments' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment'"),
    ];
}

function nh_extract_media_zip(string $zipPath, array $uploads): void
{
    if (!is_file($zipPath)) {
        throw new RuntimeException('catalog-transfer-media.zip is missing');
    }

    $zip = new ZipArchive();
    if ($zip->open($zipPath) !== true) {
        throw new RuntimeException('Unable to open media zip');
    }

    $zip->extractTo($uploads['basedir']);
    $zip->close();
}

function nh_delete_existing_catalog(): int
{
    global $wpdb;

    $ids = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type IN ('product', 'product_variation')");
    $deleted = 0;

    foreach (array_map('intval', $ids) as $id) {
        if (wp_delete_post($id, true)) {
            $deleted++;
        }
    }

    return $deleted;
}

function nh_import_attachment(array $attachmentData, array $uploads, array &$log): ?int
{
    global $wpdb;

    if (!empty($attachmentData['missing'])) {
        $log['missing_attachments'][] = [
            'local_id' => $attachmentData['local_id'],
            'relative_path' => $attachmentData['relative_path'] ?? '',
            'title' => $attachmentData['title'] ?? '',
        ];

        return null;
    }

    $relativePath = ltrim((string) ($attachmentData['relative_path'] ?? ''), '/');
    $absolutePath = wp_normalize_path($uploads['basedir'] . '/' . $relativePath);

    if (!is_file($absolutePath)) {
        $log['missing_attachments'][] = [
            'local_id' => $attachmentData['local_id'],
            'relative_path' => $relativePath,
            'title' => $attachmentData['title'] ?? '',
        ];

        return null;
    }

    $existingAttachmentId = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1",
            '_nh_source_relative_file',
            $relativePath
        )
    );

    if ($existingAttachmentId > 0 && get_post_type($existingAttachmentId) === 'attachment') {
        return $existingAttachmentId;
    }

    $fileType = wp_check_filetype(basename($absolutePath), null);

    $attachmentId = wp_insert_attachment(
        [
            'post_title' => $attachmentData['title'] ?? basename($absolutePath),
            'post_name' => $attachmentData['slug'] ?? sanitize_title($attachmentData['title'] ?? basename($absolutePath)),
            'post_excerpt' => $attachmentData['caption'] ?? '',
            'post_content' => $attachmentData['description'] ?? '',
            'post_status' => 'inherit',
            'post_mime_type' => $attachmentData['mime_type'] ?? ($fileType['type'] ?? ''),
        ],
        $absolutePath
    );

    if (is_wp_error($attachmentId) || !$attachmentId) {
        $log['attachment_errors'][] = [
            'local_id' => $attachmentData['local_id'],
            'message' => is_wp_error($attachmentId) ? $attachmentId->get_error_message() : 'Unknown attachment insert error',
        ];

        return null;
    }

    $metadata = wp_generate_attachment_metadata($attachmentId, $absolutePath);
    if (!is_wp_error($metadata)) {
        wp_update_attachment_metadata($attachmentId, $metadata);
    }

    update_post_meta($attachmentId, '_wp_attachment_image_alt', $attachmentData['alt'] ?? '');
    update_post_meta($attachmentId, '_nh_source_local_attachment_id', (string) $attachmentData['local_id']);
    update_post_meta($attachmentId, '_nh_source_relative_file', $relativePath);

    return $attachmentId;
}

function nh_ensure_attribute_taxonomies(array $attributes): void
{
    $existing = [];
    foreach ((array) wc_get_attribute_taxonomies() as $attributeTaxonomy) {
        $existing[(string) $attributeTaxonomy->attribute_name] = $attributeTaxonomy;
    }

    $createdAny = false;

    foreach ($attributes as $attribute) {
        $attributeName = (string) ($attribute['name'] ?? '');
        if ($attributeName === '' || isset($existing[$attributeName])) {
            continue;
        }

        $result = wc_create_attribute([
            'name' => $attribute['label'] ?? $attributeName,
            'slug' => $attributeName,
            'type' => $attribute['type'] ?? 'select',
            'order_by' => $attribute['orderby'] ?? 'menu_order',
            'has_archives' => !empty($attribute['public']),
        ]);

        if (!is_wp_error($result)) {
            $createdAny = true;
        }
    }

    if ($createdAny) {
        delete_transient('wc_attribute_taxonomies');
        if (class_exists('WC_Cache_Helper')) {
            WC_Cache_Helper::invalidate_cache_group('woocommerce-attributes');
        }
    }

    if (class_exists('WC_Post_Types')) {
        WC_Post_Types::register_taxonomies();
    }
}

function nh_import_term(array $termData, array &$termMap): int
{
    $taxonomy = (string) $termData['taxonomy'];
    $slug = (string) $termData['slug'];
    $name = (string) $termData['name'];

    $existing = get_term_by('slug', $slug, $taxonomy);
    if ($existing instanceof WP_Term) {
        $termId = (int) $existing->term_id;
        wp_update_term($termId, $taxonomy, [
            'name' => $name,
            'description' => $termData['description'] ?? '',
            'slug' => $slug,
        ]);
    } else {
        $result = wp_insert_term($name, $taxonomy, [
            'slug' => $slug,
            'description' => $termData['description'] ?? '',
        ]);

        if (is_wp_error($result)) {
            throw new RuntimeException(sprintf('Failed to import term %s (%s): %s', $name, $taxonomy, $result->get_error_message()));
        }

        $termId = (int) $result['term_id'];
    }

    $termMap[$taxonomy . '::' . $slug] = $termId;
    $termMap['local::' . (int) $termData['local_id']] = $termId;

    return $termId;
}

function nh_build_product_object(string $type): WC_Product
{
    return match ($type) {
        'variable' => new WC_Product_Variable(),
        default => new WC_Product_Simple(),
    };
}

function nh_find_existing_product_id(int $localId): int
{
    global $wpdb;

    return (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1",
            '_nh_source_local_product_id',
            (string) $localId
        )
    );
}

function nh_apply_common_product_fields(WC_Product $product, array $data, array $attachmentMap, array $termMap): void
{
    $product->set_name((string) ($data['name'] ?? ''));
    $product->set_slug((string) ($data['slug'] ?? ''));
    $product->set_status((string) ($data['status'] ?? 'publish'));
    $product->set_menu_order((int) ($data['menu_order'] ?? 0));
    $product->set_featured(!empty($data['featured']));
    $product->set_catalog_visibility((string) ($data['catalog_visibility'] ?? 'visible'));
    $product->set_description((string) ($data['description'] ?? ''));
    $product->set_short_description((string) ($data['short_description'] ?? ''));

    if (!empty($data['sku'])) {
        $product->set_sku((string) $data['sku']);
    }

    if (array_key_exists('regular_price', $data) && $data['regular_price'] !== '') {
        $product->set_regular_price((string) $data['regular_price']);
    }

    if (array_key_exists('sale_price', $data) && $data['sale_price'] !== '') {
        $product->set_sale_price((string) $data['sale_price']);
    }

    $product->set_manage_stock((bool) ($data['manage_stock'] ?? false));
    $product->set_stock_quantity($data['stock_quantity'] === null ? null : (int) $data['stock_quantity']);
    $product->set_stock_status((string) ($data['stock_status'] ?? 'instock'));
    $product->set_backorders((string) ($data['backorders'] ?? 'no'));
    $product->set_sold_individually(!empty($data['sold_individually']));
    $product->set_virtual(!empty($data['virtual']));
    $product->set_downloadable(!empty($data['downloadable']));
    $product->set_weight((string) ($data['weight'] ?? ''));
    $product->set_length((string) ($data['length'] ?? ''));
    $product->set_width((string) ($data['width'] ?? ''));
    $product->set_height((string) ($data['height'] ?? ''));
    $product->set_tax_status((string) ($data['tax_status'] ?? 'taxable'));
    $product->set_tax_class((string) ($data['tax_class'] ?? ''));

    $categoryIds = [];
    foreach ((array) ($data['category_slugs'] ?? []) as $slug) {
        $termId = $termMap['product_cat::' . $slug] ?? null;
        if ($termId) {
            $categoryIds[] = (int) $termId;
        }
    }
    if ($categoryIds) {
        $product->set_category_ids(array_values(array_unique($categoryIds)));
    }

    $tagIds = [];
    foreach ((array) ($data['tag_slugs'] ?? []) as $slug) {
        $termId = $termMap['product_tag::' . $slug] ?? null;
        if ($termId) {
            $tagIds[] = (int) $termId;
        }
    }
    if ($tagIds) {
        $product->set_tag_ids(array_values(array_unique($tagIds)));
    }

    $attributes = [];
    foreach ((array) ($data['attributes'] ?? []) as $attributeData) {
        $attribute = new WC_Product_Attribute();
        $attribute->set_name((string) $attributeData['name']);
        $attribute->set_position((int) ($attributeData['position'] ?? 0));
        $attribute->set_visible(!empty($attributeData['visible']));
        $attribute->set_variation(!empty($attributeData['variation']));

        if (!empty($attributeData['taxonomy'])) {
            $taxonomy = (string) $attributeData['name'];
            $attribute->set_id((int) wc_attribute_taxonomy_id_by_name($taxonomy));

            $termIds = [];
            foreach ((array) ($attributeData['options'] ?? []) as $termSlug) {
                $termId = $termMap[$taxonomy . '::' . $termSlug] ?? null;
                if ($termId) {
                    $termIds[] = (int) $termId;
                }
            }
            $attribute->set_options($termIds);
        } else {
            $attribute->set_options(array_values(array_map('strval', (array) ($attributeData['options'] ?? []))));
        }

        $attributes[] = $attribute;
    }

    if ($attributes) {
        $product->set_attributes($attributes);
    }

    if (!empty($data['default_attributes'])) {
        $product->set_default_attributes((array) $data['default_attributes']);
    }

    $imageLocalId = (int) ($data['image_local_id'] ?? 0);
    if ($imageLocalId > 0 && isset($attachmentMap[$imageLocalId])) {
        $product->set_image_id((int) $attachmentMap[$imageLocalId]);
    }

    $galleryIds = [];
    foreach ((array) ($data['gallery_local_ids'] ?? []) as $galleryLocalId) {
        $galleryLocalId = (int) $galleryLocalId;
        if ($galleryLocalId > 0 && isset($attachmentMap[$galleryLocalId])) {
            $galleryIds[] = (int) $attachmentMap[$galleryLocalId];
        }
    }
    if ($galleryIds) {
        $product->set_gallery_image_ids(array_values(array_unique($galleryIds)));
    }
}

function nh_update_custom_meta(int $postId, array $meta, array $attachmentMap): void
{
    foreach ($meta as $metaKey => $metaValue) {
        delete_post_meta($postId, $metaKey);
        $remappedValue = nh_recursive_remap_local_ids($metaValue, $attachmentMap);

        if (is_array($remappedValue)) {
            foreach ($remappedValue as $singleValue) {
                add_post_meta($postId, $metaKey, $singleValue);
            }

            continue;
        }

        update_post_meta($postId, $metaKey, $remappedValue);
    }
}

$providedToken = $_GET['token'] ?? '';
if ($providedToken !== $expectedToken) {
    nh_fail('Forbidden', 403);
}

$wpLoad = nh_find_wp_load(__DIR__);
require_once $wpLoad;

if (!function_exists('wc_get_products')) {
    nh_fail('WooCommerce is not available');
}

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$manifestPath = __DIR__ . DIRECTORY_SEPARATOR . 'catalog-transfer.json';
$zipPath = __DIR__ . DIRECTORY_SEPARATOR . 'catalog-transfer-media.zip';
$manifest = json_decode((string) file_get_contents($manifestPath), true);

if (!is_array($manifest)) {
    nh_fail('catalog-transfer.json is invalid', 400);
}

$uploads = wp_get_upload_dir();
$action = (string) ($_GET['action'] ?? 'status');

try {
    switch ($action) {
        case 'status':
            nh_json_response([
                'ok' => true,
                'prepared' => (bool) get_option(NH_TRANSFER_PREPARED_OPTION, false),
                'manifest_summary' => $manifest['summary'] ?? [],
                'attachment_map_count' => count(nh_get_option_array(NH_TRANSFER_ATTACHMENT_MAP_OPTION)),
                'term_map_count' => count(nh_get_option_array(NH_TRANSFER_TERM_MAP_OPTION)),
                'product_map_count' => count(nh_get_option_array(NH_TRANSFER_PRODUCT_MAP_OPTION)),
                'live_counts' => nh_live_counts(),
            ]);

        case 'prepare':
            nh_extract_media_zip($zipPath, $uploads);
            nh_ensure_attribute_taxonomies((array) ($manifest['attributes'] ?? []));

            $deleted = nh_delete_existing_catalog();

            update_option(NH_TRANSFER_ATTACHMENT_MAP_OPTION, [], false);
            update_option(NH_TRANSFER_TERM_MAP_OPTION, [], false);
            update_option(NH_TRANSFER_PRODUCT_MAP_OPTION, [], false);
            update_option(NH_TRANSFER_PREPARED_OPTION, 1, false);

            nh_json_response([
                'ok' => true,
                'deleted_catalog_posts' => $deleted,
                'live_counts' => nh_live_counts(),
            ]);

        case 'import_attachments':
            if (!get_option(NH_TRANSFER_PREPARED_OPTION, false)) {
                nh_fail('Run action=prepare first', 400);
            }

            $offset = max(0, (int) ($_GET['offset'] ?? 0));
            $limit = max(1, min(20, (int) ($_GET['limit'] ?? 10)));
            $batch = array_slice((array) ($manifest['attachments'] ?? []), $offset, $limit);

            $attachmentMap = nh_get_option_array(NH_TRANSFER_ATTACHMENT_MAP_OPTION);
            $log = ['missing_attachments' => [], 'attachment_errors' => []];

            foreach ($batch as $attachmentData) {
                $liveAttachmentId = nh_import_attachment($attachmentData, $uploads, $log);
                if ($liveAttachmentId) {
                    $attachmentMap[(int) $attachmentData['local_id']] = (int) $liveAttachmentId;
                }
            }

            update_option(NH_TRANSFER_ATTACHMENT_MAP_OPTION, $attachmentMap, false);

            $nextOffset = $offset + count($batch);
            nh_json_response([
                'ok' => true,
                'batch_size' => count($batch),
                'next_offset' => $nextOffset,
                'done' => $nextOffset >= count((array) ($manifest['attachments'] ?? [])),
                'attachment_map_count' => count($attachmentMap),
                'missing_attachments' => $log['missing_attachments'],
                'attachment_errors' => $log['attachment_errors'],
            ]);

        case 'import_terms':
            if (!get_option(NH_TRANSFER_PREPARED_OPTION, false)) {
                nh_fail('Run action=prepare first', 400);
            }

            nh_ensure_attribute_taxonomies((array) ($manifest['attributes'] ?? []));

            $attachmentMap = nh_get_option_array(NH_TRANSFER_ATTACHMENT_MAP_OPTION);
            $termMap = [];

            foreach ((array) ($manifest['terms'] ?? []) as $termData) {
                nh_import_term($termData, $termMap);
            }

            foreach ((array) ($manifest['terms'] ?? []) as $termData) {
                $taxonomy = (string) $termData['taxonomy'];
                $termId = (int) ($termMap['local::' . (int) $termData['local_id']] ?? 0);
                if ($termId <= 0) {
                    continue;
                }

                $parentLocalId = (int) ($termData['parent_local_id'] ?? 0);
                $parentTermId = $parentLocalId > 0 ? (int) ($termMap['local::' . $parentLocalId] ?? 0) : 0;

                wp_update_term($termId, $taxonomy, [
                    'parent' => $parentTermId,
                    'description' => $termData['description'] ?? '',
                    'name' => $termData['name'] ?? '',
                    'slug' => $termData['slug'] ?? '',
                ]);

                foreach ((array) ($termData['meta'] ?? []) as $metaKey => $metaValue) {
                    delete_term_meta($termId, $metaKey);
                    update_term_meta($termId, $metaKey, nh_recursive_remap_local_ids($metaValue, $attachmentMap));
                }
            }

            update_option(NH_TRANSFER_TERM_MAP_OPTION, $termMap, false);

            nh_json_response([
                'ok' => true,
                'term_map_count' => count($termMap),
            ]);

        case 'import_products':
            if (!get_option(NH_TRANSFER_PREPARED_OPTION, false)) {
                nh_fail('Run action=prepare first', 400);
            }

            $offset = max(0, (int) ($_GET['offset'] ?? 0));
            $limit = max(1, min(10, (int) ($_GET['limit'] ?? 5)));
            $batch = array_slice((array) ($manifest['products'] ?? []), $offset, $limit);

            $attachmentMap = nh_get_option_array(NH_TRANSFER_ATTACHMENT_MAP_OPTION);
            $termMap = nh_get_option_array(NH_TRANSFER_TERM_MAP_OPTION);
            $productMap = nh_get_option_array(NH_TRANSFER_PRODUCT_MAP_OPTION);

            $importedProducts = 0;
            $importedVariations = 0;

            foreach ($batch as $productData) {
                $existingProductId = nh_find_existing_product_id((int) ($productData['local_id'] ?? 0));
                if ($existingProductId > 0 && get_post_type($existingProductId) === 'product') {
                    $product = wc_get_product($existingProductId);
                    if (!$product instanceof WC_Product || $product->get_type() !== ($productData['type'] ?? 'simple')) {
                        wp_delete_post($existingProductId, true);
                        $product = nh_build_product_object((string) ($productData['type'] ?? 'simple'));
                    }
                } else {
                    $product = nh_build_product_object((string) ($productData['type'] ?? 'simple'));
                }

                nh_apply_common_product_fields($product, $productData, $attachmentMap, $termMap);
                $liveProductId = $product->save();
                nh_update_custom_meta($liveProductId, (array) ($productData['meta'] ?? []), $attachmentMap);
                update_post_meta($liveProductId, '_nh_source_local_product_id', (string) ($productData['local_id'] ?? 0));

                $productMap[(int) $productData['local_id']] = (int) $liveProductId;
                $importedProducts++;

                foreach ((array) ($productData['variations'] ?? []) as $variationData) {
                    $existingVariationId = nh_find_existing_product_id((int) ($variationData['local_id'] ?? 0));
                    if ($existingVariationId > 0 && get_post_type($existingVariationId) === 'product_variation') {
                        $variation = new WC_Product_Variation($existingVariationId);
                    } else {
                        $variation = new WC_Product_Variation();
                    }

                    $variation->set_parent_id($liveProductId);
                    $variation->set_slug((string) ($variationData['slug'] ?? ''));
                    $variation->set_status((string) ($variationData['status'] ?? 'publish'));
                    $variation->set_menu_order((int) ($variationData['menu_order'] ?? 0));

                    if (!empty($variationData['sku'])) {
                        $variation->set_sku((string) $variationData['sku']);
                    }

                    if (array_key_exists('regular_price', $variationData) && $variationData['regular_price'] !== '') {
                        $variation->set_regular_price((string) $variationData['regular_price']);
                    }

                    if (array_key_exists('sale_price', $variationData) && $variationData['sale_price'] !== '') {
                        $variation->set_sale_price((string) $variationData['sale_price']);
                    }

                    $variation->set_manage_stock((bool) ($variationData['manage_stock'] ?? false));
                    $variation->set_stock_quantity($variationData['stock_quantity'] === null ? null : (int) $variationData['stock_quantity']);
                    $variation->set_stock_status((string) ($variationData['stock_status'] ?? 'instock'));
                    $variation->set_backorders((string) ($variationData['backorders'] ?? 'no'));
                    $variation->set_virtual(!empty($variationData['virtual']));
                    $variation->set_downloadable(!empty($variationData['downloadable']));
                    $variation->set_description((string) ($variationData['description'] ?? ''));
                    $variation->set_attributes((array) ($variationData['attributes'] ?? []));

                    $variationImageLocalId = (int) ($variationData['image_local_id'] ?? 0);
                    if ($variationImageLocalId > 0 && isset($attachmentMap[$variationImageLocalId])) {
                        $variation->set_image_id((int) $attachmentMap[$variationImageLocalId]);
                    }

                    $liveVariationId = $variation->save();
                    nh_update_custom_meta($liveVariationId, (array) ($variationData['meta'] ?? []), $attachmentMap);
                    update_post_meta($liveVariationId, '_nh_source_local_product_id', (string) ($variationData['local_id'] ?? 0));
                    $importedVariations++;
                }

                if (($productData['type'] ?? 'simple') === 'variable') {
                    WC_Product_Variable::sync($liveProductId);
                }
            }

            update_option(NH_TRANSFER_PRODUCT_MAP_OPTION, $productMap, false);

            $nextOffset = $offset + count($batch);
            nh_json_response([
                'ok' => true,
                'batch_size' => count($batch),
                'imported_products' => $importedProducts,
                'imported_variations' => $importedVariations,
                'next_offset' => $nextOffset,
                'done' => $nextOffset >= count((array) ($manifest['products'] ?? [])),
                'product_map_count' => count($productMap),
                'live_counts' => nh_live_counts(),
            ]);

        case 'finalize':
            if (function_exists('wc_delete_product_transients')) {
                wc_delete_product_transients();
            }
            if (function_exists('wc_recount_all_terms')) {
                wc_recount_all_terms();
            }

            nh_json_response([
                'ok' => true,
                'live_counts' => nh_live_counts(),
                'attachment_map_count' => count(nh_get_option_array(NH_TRANSFER_ATTACHMENT_MAP_OPTION)),
                'term_map_count' => count(nh_get_option_array(NH_TRANSFER_TERM_MAP_OPTION)),
                'product_map_count' => count(nh_get_option_array(NH_TRANSFER_PRODUCT_MAP_OPTION)),
            ]);

        default:
            nh_fail('Unknown action', 400, ['action' => $action]);
    }
} catch (Throwable $e) {
    nh_fail($e->getMessage(), 500, ['trace' => [$e->getFile() . ':' . $e->getLine()]]);
}
