<?php
declare(strict_types=1);

function nice_hair_allowed_blocks(array|bool $allowed_block_types, WP_Block_Editor_Context $editor_context): array|bool
{
    $post = $editor_context->post ?? null;

    if (! $post instanceof WP_Post) {
        return $allowed_block_types;
    }

    if ($post->post_type === 'nh_running_line') {
        return [
            'core/paragraph',
        ];
    }

    if ($post->post_type === 'nh_popup') {
        return [
            'core/group',
            'core/columns',
            'core/column',
            'core/heading',
            'core/paragraph',
            'core/image',
            'core/buttons',
            'core/button',
            'core/spacer',
        ];
    }

    if ($post->post_type === 'nh_popup_salon') {
        return [
            'core/group',
            'core/heading',
            'core/paragraph',
            'core/image',
            'core/buttons',
            'core/button',
            'core/spacer',
        ];
    }

    $is_front_page = (int) get_option('page_on_front') === (int) $post->ID;
    $is_home_slug  = $post->post_type === 'page' && $post->post_name === 'home';

    $page_template = $post->post_type === 'page'
        ? (string) get_page_template_slug($post->ID)
        : '';
    $is_salon_page = $post->post_type === 'page'
        && ($post->post_name === 'salon' || $page_template === 'page-templates/page-salon.php');
    $is_shop_page = $post->post_type === 'page'
        && ($post->post_name === 'shop' || $page_template === 'page-templates/page-shop.php');
    $is_shipping_page = $post->post_type === 'page'
        && ($post->post_name === 'shipping-payment' || $page_template === 'page-templates/page-shipping-payment.php');
    $is_404_source_page = $post->post_type === 'page'
        && ($post->post_name === '404-not-found' || $page_template === 'page-templates/page-404-source.php');
    $is_thank_you_source_page = $post->post_type === 'page'
        && ($post->post_name === 'thank-you' || $page_template === 'page-templates/page-thank-you-source.php');
    $is_privacy_policy_page = $post->post_type === 'page'
        && ($post->post_name === 'privacy-policy' || $page_template === 'page-templates/page-privacy-policy.php');
    $is_blog_page = (int) get_option('page_for_posts') === (int) $post->ID;

    if (! $is_front_page && ! $is_home_slug && ! $is_salon_page && ! $is_shop_page && ! $is_shipping_page && ! $is_404_source_page && ! $is_thank_you_source_page && ! $is_privacy_policy_page && ! $is_blog_page) {
        return $allowed_block_types;
    }

    return [
        'core/group',
        'core/columns',
        'core/column',
        'core/heading',
        'core/paragraph',
        'core/image',
        'core/gallery',
        'core/buttons',
        'core/button',
        'core/list',
        'core/list-item',
        'core/spacer',
        'core/separator',
        'core/details',
        'core/quote',
        'core/cover',
        'core/media-text',
        'acf/nh-faq',
        'acf/nh-stylists',
        'acf/nh-results',
        'acf/nh-results-gallery-shop',
        'acf/nh-price-quiz',
        'acf/nh-discounts',
        'acf/nh-shop-categories',
        'acf/nh-shop-assortment',
    ];
}
add_filter('allowed_block_types_all', 'nice_hair_allowed_blocks', 10, 2);
