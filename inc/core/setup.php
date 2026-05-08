<?php
declare(strict_types=1);

function nice_hair_setup(): void
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('responsive-embeds');
    add_theme_support('wp-block-styles');
    add_theme_support('align-wide');
    add_theme_support('editor-styles');
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ]);

    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');

    register_nav_menus([
        'home_primary'  => __('Home Primary Menu', 'nice-hair'),
        'salon_primary' => __('Salon Primary Menu', 'nice-hair'),
        'shop_primary'  => __('Shop Primary Menu', 'nice-hair'),
        'footer_menu'   => __('Footer Menu', 'nice-hair'),
        'footer_nav_1'  => __('Footer Navigation 1', 'nice-hair'),
        'footer_nav_2'  => __('Footer Navigation 2', 'nice-hair'),
    ]);
}
add_action('after_setup_theme', 'nice_hair_setup');
