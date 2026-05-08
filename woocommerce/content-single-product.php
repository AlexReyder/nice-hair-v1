<?php
/**
 * WooCommerce - Single Product content.
 *
 * Category-routing: detects the product family and delegates
 * to a per-category template part under template-parts/woocommerce/.
 *
 * @see https://woocommerce.com/document/template-structure/
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

global $product;

if (! is_a($product, 'WC_Product')) {
    $product = wc_get_product(get_the_ID());
}

do_action('woocommerce_before_single_product');

if (post_password_required()) {
    echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    return;
}

$nh_product_family = function_exists('nice_hair_get_product_family')
    ? nice_hair_get_product_family($product)
    : '';

if ($nh_product_family === 'tools') {
    get_template_part('template-parts/woocommerce/single-product', 'tools');
} elseif ($nh_product_family === 'keratin') {
    get_template_part('template-parts/woocommerce/single-product', 'keratin');
} elseif ($nh_product_family === 'ready_to_install') {
    get_template_part('template-parts/woocommerce/single-product', 'ready-to-install');
} elseif ($nh_product_family === 'exclusive_hair') {
    get_template_part('template-parts/woocommerce/single-product', 'exclusive-hair');
} elseif ($nh_product_family === 'custom_hair') {
    get_template_part('template-parts/woocommerce/single-product', 'custom-hair');
} else {
    get_template_part('template-parts/woocommerce/single-product', 'generic');
}

do_action('woocommerce_after_single_product');
