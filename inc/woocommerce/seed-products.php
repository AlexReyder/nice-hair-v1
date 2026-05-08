<?php
/**
 * One-shot WooCommerce product seeder.
 *
 * Run via: wp eval-file wp-content/themes/nice-hair/inc/woocommerce/seed-products.php
 *
 * Seeds:
 *  - Product categories (Keratin, Tools, Ready to Install, Exclusive Hair, Custom Hair)
 *  - Global attribute: Weight (pa_weight)
 *  - Keratin products with weight variations
 *
 * Safe to re-run — skips existing items.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('WooCommerce')) {
    echo "WooCommerce is not active.\n";
    return;
}

/* ======================================================================
 * 1. Product Categories
 * ==================================================================== */

$categories = [
    'Keratin' => [
        'Italian Gel Keratin',
        'Pigmented Keratin',
    ],
    'Tools'            => [],
    'Ready to Install' => [],
    'Exclusive Hair'   => [],
    'Custom Hair'      => [],
];

foreach ($categories as $parent_name => $children) {
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

/* ======================================================================
 * 2. Global Attribute: Weight
 * ==================================================================== */

$weight_attr_id = wc_attribute_taxonomy_id_by_name('weight');

if (! $weight_attr_id) {
    $weight_attr_id = wc_create_attribute([
        'name'         => 'Weight',
        'slug'         => 'weight',
        'type'         => 'select',
        'order_by'     => 'menu_order',
        'has_archives' => false,
    ]);

    // Register the taxonomy so terms can be added in this request.
    register_taxonomy('pa_weight', 'product', [
        'label'        => 'Weight',
        'hierarchical' => false,
    ]);

    echo "Created attribute: Weight (pa_weight)\n";
} else {
    echo "Attribute exists: Weight (pa_weight)\n";
}

/* ======================================================================
 * 3. Keratin Products
 * ==================================================================== */

/**
 * Italian Gel Keratin pricing.
 *
 * Transparent has its own pricing; all others share the standard pricing.
 */
$italian_gel_standard = [
    '5g'    => 3,
    '10g'   => 5,
    '50g'   => 16,
    '100g'  => 27,
    '1000g' => 160,
];

$italian_gel_transparent = [
    '5g'    => 3,
    '10g'   => 4,
    '50g'   => 13,
    '100g'  => 21,
    '1000g' => 134,
];

$italian_gel_colors = [
    'Transparent' => $italian_gel_transparent,
    'Perl'        => $italian_gel_standard,
    'Middle'      => $italian_gel_standard,
    'Honey'       => $italian_gel_standard,
    'Dark'        => $italian_gel_standard,
    'Brown'       => $italian_gel_standard,
    'Blond'       => $italian_gel_standard,
];

/**
 * Pigmented Keratin pricing.
 */
$pigmented_pricing = [
    '10g'  => 5,
    '50g'  => 16,
    '100g' => 27,
];

$pigmented_colors = [
    'Brown'      => $pigmented_pricing,
    'Dark brown' => $pigmented_pricing,
    'Black'      => $pigmented_pricing,
];

// Get category IDs.
$cat_italian   = get_term_by('name', 'Italian Gel Keratin', 'product_cat');
$cat_pigmented = get_term_by('name', 'Pigmented Keratin', 'product_cat');
$cat_keratin   = get_term_by('name', 'Keratin', 'product_cat');

if (! $cat_italian || ! $cat_pigmented || ! $cat_keratin) {
    echo "ERROR: Keratin categories not found.\n";
    return;
}

/**
 * Create a variable product with weight variations.
 */
function nh_seed_variable_product(
    string $title,
    array $category_ids,
    array $weight_prices
): void {
    // Check if product already exists.
    $existing = get_page_by_title($title, OBJECT, 'product');
    if ($existing) {
        echo "  Product exists: {$title}\n";
        return;
    }

    // Ensure weight terms exist.
    $weight_terms = [];
    foreach (array_keys($weight_prices) as $weight_label) {
        if (! term_exists($weight_label, 'pa_weight')) {
            wp_insert_term($weight_label, 'pa_weight');
        }
        $weight_terms[] = $weight_label;
    }

    // Create the product.
    $product = new WC_Product_Variable();
    $product->set_name($title);
    $product->set_status('publish');
    $product->set_catalog_visibility('visible');
    $product->set_category_ids($category_ids);

    // Set the weight attribute.
    $attribute = new WC_Product_Attribute();
    $attribute->set_name('pa_weight');
    $attribute->set_options($weight_terms);
    $attribute->set_visible(true);
    $attribute->set_variation(true);

    $product->set_attributes([$attribute]);
    $product_id = $product->save();

    // Create variations.
    $min_price = PHP_INT_MAX;
    foreach ($weight_prices as $weight_label => $price) {
        $variation = new WC_Product_Variation();
        $variation->set_parent_id($product_id);
        $variation->set_attributes(['pa_weight' => $weight_label]);
        $variation->set_regular_price((string) $price);
        $variation->set_manage_stock(false);
        $variation->set_status('publish');
        $variation->save();

        $min_price = min($min_price, $price);
    }

    // Sync variable product data.
    WC_Product_Variable::sync($product_id);

    echo "  Created product: {$title} ({$product_id}) with " . count($weight_prices) . " variations\n";
}

echo "\nSeeding Italian Gel Keratin products...\n";
foreach ($italian_gel_colors as $color => $prices) {
    nh_seed_variable_product(
        "Italian gel keratin - {$color}",
        [$cat_keratin->term_id, $cat_italian->term_id],
        $prices
    );
}

echo "\nSeeding Pigmented Keratin products...\n";
foreach ($pigmented_colors as $color => $prices) {
    nh_seed_variable_product(
        "Pigmented keratin - {$color}",
        [$cat_keratin->term_id, $cat_pigmented->term_id],
        $prices
    );
}

/* ======================================================================
 * 4. Tools Products (simple products)
 * ==================================================================== */

$cat_tools = get_term_by('name', 'Tools', 'product_cat');

if (! $cat_tools) {
    echo "ERROR: Tools category not found.\n";
} else {

    /**
     * Create a simple product.
     */
    function nh_seed_simple_product(
        string $title,
        string $sku,
        array $category_ids,
        float $price = 0,
        string $short_description = '',
        string $description = ''
    ): void {
        $existing = get_page_by_title($title, OBJECT, 'product');
        if ($existing) {
            echo "  Product exists: {$title}\n";
            return;
        }

        $product = new WC_Product_Simple();
        $product->set_name($title);
        $product->set_sku($sku);
        $product->set_status('publish');
        $product->set_catalog_visibility('visible');
        $product->set_category_ids($category_ids);
        $product->set_manage_stock(false);

        if ($price > 0) {
            $product->set_regular_price((string) $price);
        }

        if ($short_description) {
            $product->set_short_description($short_description);
        }

        if ($description) {
            $product->set_description($description);
        }

        $product_id = $product->save();
        echo "  Created product: {$title} ({$product_id})\n";
    }

    $tools_items = [
        [
            'title' => 'Heat Fusion Connector',
            'sku'   => 'NH-TOOL-001',
            'price' => 89.00,
            'short' => 'Professional heat connector for keratin bond application. Adjustable temperature control with LED indicator.',
            'desc'  => '<h3>Features</h3><p>Designed for salon-grade keratin bond application. Features adjustable temperature from 100°C to 230°C, ergonomic grip, and LED temperature display. Compatible with all keratin types including Italian Gel and Pigmented.</p><h3>Specifications</h3><p>Voltage: 110-240V. Heat-up time: 30 seconds. Weight: 180g. Cable length: 2.5m.</p>',
        ],
        [
            'title' => 'Micro Ring Pliers',
            'sku'   => 'NH-TOOL-002',
            'price' => 35.00,
            'short' => 'Precision pliers for micro ring and nano ring hair extension installation and removal.',
            'desc'  => '<h3>Features</h3><p>Stainless steel construction with cushioned grip handles. Designed specifically for opening and clamping micro rings and nano rings without damaging the hair or the ring.</p>',
        ],
        [
            'title' => 'Sectioning Clips Set',
            'sku'   => 'NH-TOOL-003',
            'price' => 12.00,
            'short' => 'Set of 12 professional sectioning clips. Non-slip grip, gentle on hair.',
            'desc'  => '',
        ],
        [
            'title' => 'Extension Detangling Brush',
            'sku'   => 'NH-TOOL-004',
            'price' => 24.00,
            'short' => 'Loop bristle brush designed for hair extensions. Prevents snagging and bond damage.',
            'desc'  => '<h3>Why this brush?</h3><p>Standard brushes can catch on extension bonds causing breakage. This brush features looped bristles that glide through extensions safely, detangling without pulling on attachment points.</p>',
        ],
        [
            'title' => 'Tape Remover Solution',
            'sku'   => 'NH-TOOL-005',
            'price' => 18.00,
            'short' => 'Professional-grade adhesive remover for tape-in hair extensions. 120ml bottle.',
            'desc'  => '<h3>Usage</h3><p>Apply a small amount to the tape bond area. Allow 2-3 minutes for the solution to dissolve the adhesive. Gently slide the tape extensions apart. Safe for natural hair and reusable tape tabs.</p>',
        ],
        [
            'title' => 'Hair Extension Storage Hanger',
            'sku'   => 'NH-TOOL-006',
            'price' => 0,
            'short' => 'Portable hanger for storing and organizing clip-in and tape-in hair extensions between uses.',
            'desc'  => '',
        ],
    ];

    echo "\nSeeding Tools products...\n";
    foreach ($tools_items as $item) {
        nh_seed_simple_product(
            $item['title'],
            $item['sku'],
            [$cat_tools->term_id],
            $item['price'],
            $item['short'],
            $item['desc']
        );
    }
}

echo "\nSeeding complete.\n";
