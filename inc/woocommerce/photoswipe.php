<?php

declare(strict_types=1);

/**
 * WooCommerce PhotoSwipe integration for custom theme galleries.
 */

function nice_hair_custom_photoswipe_gallery_block_names(): array
{
    return [
        'acf/nh-shop-assortment',
        'acf/nh-results-gallery-shop',
    ];
}

function nice_hair_post_has_custom_photoswipe_gallery_block(?WP_Post $post): bool
{
    if (! $post instanceof WP_Post || ! function_exists('has_block')) {
        return false;
    }

    if (trim((string) $post->post_content) === '') {
        return false;
    }

    foreach (nice_hair_custom_photoswipe_gallery_block_names() as $block_name) {
        if (has_block($block_name, $post)) {
            return true;
        }
    }

    return false;
}

function nice_hair_should_enqueue_custom_photoswipe_gallery_assets(): bool
{
    if (is_admin()) {
        return false;
    }

    $queried_id = function_exists('get_queried_object_id') ? (int) get_queried_object_id() : 0;

    if ($queried_id > 0 && nice_hair_post_has_custom_photoswipe_gallery_block(get_post($queried_id))) {
        return true;
    }

    if (! function_exists('is_shop') || ! is_shop() || ! function_exists('wc_get_page_id')) {
        return false;
    }

    $shop_page_id = (int) wc_get_page_id('shop');

    if ($shop_page_id <= 0) {
        return false;
    }

    return nice_hair_post_has_custom_photoswipe_gallery_block(get_post($shop_page_id));
}

function nice_hair_render_woocommerce_photoswipe_template(): void
{
    static $did_render = false;

    if ($did_render) {
        return;
    }

    $did_render = true;

    if (function_exists('woocommerce_photoswipe')) {
        woocommerce_photoswipe();
        return;
    }

    if (function_exists('wc_get_template')) {
        wc_get_template('single-product/photoswipe.php');
    }
}

function nice_hair_enqueue_custom_photoswipe_gallery_assets(): void
{
    if (! nice_hair_should_enqueue_custom_photoswipe_gallery_assets()) {
        return;
    }

    wp_enqueue_script('photoswipe');
    wp_enqueue_script('photoswipe-ui-default');
    wp_enqueue_style('photoswipe');
    wp_enqueue_style('photoswipe-default-skin');

    if (function_exists('woocommerce_photoswipe')) {
        if (has_action('wp_footer', 'woocommerce_photoswipe') === false) {
            add_action('wp_footer', 'woocommerce_photoswipe', 20);
        }

        return;
    }

    if (function_exists('wc_get_template')) {
        add_action('wp_footer', 'nice_hair_render_woocommerce_photoswipe_template', 20);
    }
}
add_action('wp_enqueue_scripts', 'nice_hair_enqueue_custom_photoswipe_gallery_assets', 20);
