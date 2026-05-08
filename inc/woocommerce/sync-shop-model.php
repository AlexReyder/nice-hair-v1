<?php
/**
 * One-shot WooCommerce shop model sync.
 *
 * Run via:
 * wp eval-file wp-content/themes/nice-hair/inc/woocommerce/sync-shop-model.php
 *
 * Safe to re-run:
 * - creates missing product categories
 * - creates missing global attributes
 * - creates missing base terms
 *
 * Does not seed demo products.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('WooCommerce')) {
    echo "WooCommerce is not active.\n";
    return;
}

function nh_sync_shop_category_tree(array $tree): void
{
    foreach ($tree as $parent_name => $children) {
        $parent = term_exists($parent_name, 'product_cat');

        if (! $parent) {
            $parent = wp_insert_term($parent_name, 'product_cat');
            echo "Created category: {$parent_name}\n";
        } else {
            echo "Category exists: {$parent_name}\n";
        }

        $parent_id = is_array($parent) ? (int) $parent['term_id'] : (int) $parent;

        foreach ($children as $child_name) {
            if (! term_exists($child_name, 'product_cat')) {
                wp_insert_term($child_name, 'product_cat', ['parent' => $parent_id]);
                echo "  Created sub-category: {$child_name}\n";
            } else {
                echo "  Sub-category exists: {$child_name}\n";
            }
        }
    }
}

function nh_sync_shop_attribute(string $slug, string $label, array $terms): void
{
    $attribute_id = wc_attribute_taxonomy_id_by_name($slug);

    if (! $attribute_id) {
        $attribute_id = wc_create_attribute([
            'name'         => $label,
            'slug'         => $slug,
            'type'         => 'select',
            'order_by'     => 'menu_order',
            'has_archives' => false,
        ]);

        echo "Created attribute: {$label} (pa_{$slug})\n";
    } else {
        echo "Attribute exists: {$label} (pa_{$slug})\n";
    }

    $taxonomy = 'pa_' . $slug;

    if (! taxonomy_exists($taxonomy)) {
        register_taxonomy($taxonomy, 'product', [
            'label'        => $label,
            'hierarchical' => false,
            'show_ui'      => false,
            'query_var'    => true,
            'rewrite'      => false,
        ]);
    }

    foreach ($terms as $term_name) {
        if (! term_exists($term_name, $taxonomy)) {
            wp_insert_term($term_name, $taxonomy);
            echo "  Created term: {$term_name}\n";
        } else {
            echo "  Term exists: {$term_name}\n";
        }
    }
}

$category_tree = [
    'Keratin' => [
        'Italian Gel Keratin',
        'Pigmented Keratin',
    ],
    'Tools' => [],
    'Ready to Install' => [],
    'Exclusive Hair' => [],
    'Custom Hair' => [],
];

$attribute_terms = [
    'weight' => [
        'label' => 'Weight',
        'terms' => ['5g', '10g', '30g', '40g', '50g', '60g', '70g', '80g', '90g', '97g', '100g', '120g', '1000g'],
    ],
    'extension_type' => [
        'label' => 'Extension Type',
        'terms' => [
            'Biotape',
            'Bulk',
            'Double layer hand-tied weft with clip-in',
            'Flat keratin bonds',
            'Flat weft',
            'Genius weft',
            'Hand-tied weft',
            'I-tip (round tips)',
            'Invisible tapes',
            'Machine weft clip-in',
            'Machine weft',
            'Mini tapes "butterflies"',
            'Nano bonds with metal round tip',
            'Nano bonds with metal straight tip',
            'Nano bonds with plastic tip',
            'Nano bonds with thread tip',
            'Ponytail with ribbon',
            'Usual tapes',
        ],
    ],
    'hair_quality' => [
        'label' => 'Hair Quality',
        'terms' => ['Lux', 'Premium', 'Exclusive'],
    ],
    'color_group' => [
        'label' => 'Color Group',
        'terms' => ['Dark', 'Middle', 'Light'],
    ],
    'texture' => [
        'label' => 'Texture',
        'terms' => ['Amazing curly', 'Silky wavy', 'Soft straight'],
    ],
    'length' => [
        'label' => 'Length',
        'terms' => ['40 cm', '50 cm', '60 cm', '63 cm', '70 cm', '80 cm'],
    ],
];

echo "Syncing product categories...\n";
nh_sync_shop_category_tree($category_tree);

echo "\nSyncing global attributes and terms...\n";

foreach ($attribute_terms as $slug => $config) {
    nh_sync_shop_attribute($slug, $config['label'], $config['terms']);
}

echo "\nShop model sync complete.\n";
