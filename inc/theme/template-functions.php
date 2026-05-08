<?php
declare(strict_types=1);

function nice_hair_home_menu_fallback(): void
{
    echo '<ul class="nh-site-header__menu">';
    echo '<li class="nh-site-header__menu-item"><a href="#home-hero">' . esc_html__('Home', 'nice-hair') . '</a></li>';
    echo '<li class="nh-site-header__menu-item"><a href="#home-faq">' . esc_html__('FAQ', 'nice-hair') . '</a></li>';
    echo '<li class="nh-site-header__menu-item"><a href="#home-cta">' . esc_html__('Contact', 'nice-hair') . '</a></li>';
    echo '</ul>';
}

function nice_hair_header_footer_post_id(string $layout = 'home'): string
{
    return match ($layout) {
        'salon' => 'nh_header_footer_salon',
        'shop' => 'nh_header_footer_shop',
        default => 'nh_header_footer_home',
    };
}

function nice_hair_shop_pricing_post_id(): string
{
    return 'nh_shop_pricing_config';
}

function nice_hair_acf_has_value(mixed $value): bool
{
    if (is_array($value)) {
        return $value !== [];
    }

    if (is_string($value)) {
        return trim($value) !== '';
    }

    return $value !== null && $value !== false && $value !== '';
}

function nice_hair_get_layout_field(string $field_name, string $layout = 'home', mixed $fallback = null): mixed
{
    if (! function_exists('get_field')) {
        return $fallback;
    }

    $value = get_field($field_name, nice_hair_header_footer_post_id($layout));

    if (nice_hair_acf_has_value($value)) {
        return $value;
    }

    if ($layout === 'home') {
        $legacy_value = get_field($field_name, 'option');

        if (nice_hair_acf_has_value($legacy_value)) {
            return $legacy_value;
        }
    }

    return $fallback;
}

function nice_hair_normalize_link_array(mixed $link, array $fallback): array
{
    $fallback_url = (string) ($fallback['url'] ?? '#');
    $fallback_title = (string) ($fallback['title'] ?? '');
    $fallback_target = (string) ($fallback['target'] ?? '');

    if (! is_array($link)) {
        return [
            'url' => $fallback_url,
            'title' => $fallback_title,
            'target' => $fallback_target,
        ];
    }

    $url = isset($link['url']) && is_string($link['url']) && trim($link['url']) !== ''
        ? $link['url']
        : $fallback_url;

    $title = isset($link['title']) && is_string($link['title']) && trim($link['title']) !== ''
        ? $link['title']
        : $fallback_title;

    $target = isset($link['target']) && is_string($link['target'])
        ? $link['target']
        : $fallback_target;

    return [
        'url' => $url,
        'title' => $title,
        'target' => $target,
    ];
}

function nice_hair_get_page_by_template(string $template_slug, string $post_status = 'publish'): ?WP_Post
{
    $pages = get_pages([
        'post_type' => 'page',
        'post_status' => $post_status,
        'meta_key' => '_wp_page_template',
        'meta_value' => $template_slug,
        'number' => 1,
    ]);

    if (is_array($pages) && isset($pages[0]) && $pages[0] instanceof WP_Post) {
        return $pages[0];
    }

    return null;
}

function nice_hair_get_404_source_page(): ?WP_Post
{
    $source_page = nice_hair_get_page_by_template('page-templates/page-404-source.php');

    if ($source_page instanceof WP_Post) {
        return $source_page;
    }

    $fallback_page = get_page_by_path('404-not-found', OBJECT, 'page');

    if ($fallback_page instanceof WP_Post && $fallback_page->post_status === 'publish') {
        return $fallback_page;
    }

    return null;
}

function nice_hair_get_thank_you_source_page(): ?WP_Post
{
    $source_page = nice_hair_get_page_by_template('page-templates/page-thank-you-source.php');

    if ($source_page instanceof WP_Post) {
        return $source_page;
    }

    $fallback_page = get_page_by_path('thank-you', OBJECT, 'page');

    if ($fallback_page instanceof WP_Post && $fallback_page->post_status === 'publish') {
        return $fallback_page;
    }

    return null;
}

function nice_hair_get_404_hero_context(): array
{
    $default_phone_display = '+971 58 598 8409';
    $default_phone_link = '+971585988409';
    $default_address = 'Al Noor st, Al Sufouh, Al Sufouh 1, Dubai';
    $default_hours = "We're open daily: 10 AM - 10 PM";

    $phone_display = trim((string) nice_hair_get_contact_phone_display('home'));
    $phone_link = trim((string) nice_hair_get_contact_phone_link('home'));
    $address = trim((string) nice_hair_get_contact_address_display('home'));
    $hours = trim((string) nice_hair_get_contact_hours('home'));

    if ($phone_display === '') {
        $phone_display = $default_phone_display;
    }

    if ($phone_link === '') {
        $phone_link = $default_phone_link;
    }

    if ($address === '') {
        $address = $default_address;
    }

    if ($hours === '') {
        $hours = $default_hours;
    }

    return [
        'image_url' => get_template_directory_uri() . '/assets/images/404-hero.png',
        'title' => '/ Oops... Page not found',
        'text' => 'It seems this page is no longer in our collection. But beautiful hair is always available.',
        'cta_label' => 'RETURN TO HOME',
        'cta_url' => home_url('/'),
        'phone_display' => $phone_display,
        'phone_link' => $phone_link,
        'address_html' => $address,
        'hours_html' => str_replace(': ', ":<br>", $hours),
        'feature_texts' => [
            "More than 10.000<br>bulks in stock",
            "Invisible keratin<br>bonds",
            "Flawless and<br>natural result",
        ],
        'telegram_url' => nice_hair_get_contact_social_url('telegram', 'home', '#'),
        'instagram_url' => nice_hair_get_contact_social_url('instagram', 'home', '#'),
        'whatsapp_url' => nice_hair_get_contact_social_url('whatsapp', 'home', '#'),
    ];
}

function nice_hair_get_thank_you_page_url(WC_Order|int|null $order = null): string
{
    $source_page = nice_hair_get_thank_you_source_page();
    $url = $source_page instanceof WP_Post
        ? (string) get_permalink($source_page)
        : home_url('/thank-you/');

    if ($url === '') {
        $url = home_url('/thank-you/');
    }

    if ($order === null || ! function_exists('wc_get_order')) {
        return $url;
    }

    $resolved_order = $order instanceof WC_Order ? $order : wc_get_order($order);

    if (! $resolved_order instanceof WC_Order) {
        return $url;
    }

    return (string) add_query_arg([
        'order' => $resolved_order->get_id(),
        'key' => $resolved_order->get_order_key(),
    ], $url);
}

function nice_hair_get_thank_you_hero_context(): array
{
    $hero = nice_hair_get_404_hero_context();

    $shop_url = function_exists('wc_get_page_permalink')
        ? wc_get_page_permalink('shop')
        : nice_hair_get_page_url('shop', 'page-templates/page-shop.php');

    if (! is_string($shop_url) || $shop_url === '') {
        $shop_url = home_url('/shop/');
    }

    $hero['image_url'] = get_template_directory_uri() . '/assets/images/thank-you-hero.png';
    $hero['title'] = '/ Thank you for your order';
    $hero['text'] = 'Your request has been successfully received. We will contact you shortly to confirm the details.';
    $hero['cta_label'] = 'START SHOPPING';
    $hero['cta_url'] = $shop_url;

    return $hero;
}

function nice_hair_get_page_url(string $slug, string $template_slug = ''): string
{
    if ($template_slug !== '') {
        $page = nice_hair_get_page_by_template($template_slug);

        if ($page instanceof WP_Post) {
            $url = get_permalink($page);

            if (is_string($url) && $url !== '') {
                return $url;
            }
        }
    }

    $page = get_page_by_path($slug);

    if ($page instanceof WP_Post) {
        $url = get_permalink($page);

        if (is_string($url) && $url !== '') {
            return $url;
        }
    }

    return home_url('/' . trim($slug, '/') . '/');
}

function nice_hair_get_current_section(string $fallback = 'home'): string
{
    if (function_exists('is_shop') && is_shop()) {
        return 'shop';
    }

    if (function_exists('is_product') && is_product()) {
        return 'shop';
    }

    if (function_exists('is_product_taxonomy') && is_product_taxonomy()) {
        return 'shop';
    }

    if (function_exists('is_cart') && is_cart()) {
        return 'shop';
    }

    if (function_exists('is_checkout') && is_checkout()) {
        return 'shop';
    }

    $object_id = get_queried_object_id();

    if ($object_id > 0) {
        $template_slug = get_page_template_slug($object_id);

        if ($template_slug === 'page-templates/page-salon.php') {
            return 'salon';
        }

        if ($template_slug === 'page-templates/page-shop.php') {
            return 'shop';
        }

        $post = get_post($object_id);

        if ($post instanceof WP_Post && $post->post_type === 'page') {
            if ($post->post_name === 'salon') {
                return 'salon';
            }

            if ($post->post_name === 'shop') {
                return 'shop';
            }
        }
    }

    return $fallback;
}

function nice_hair_is_shop_utility_page(): bool
{
    return (function_exists('is_cart') && is_cart())
        || (function_exists('is_checkout') && is_checkout());
}

function nice_hair_get_section_switch_items(string $active_section = ''): array
{
    $active_section = in_array($active_section, ['salon', 'shop'], true)
        ? $active_section
        : nice_hair_get_current_section('salon');

    $shop_url = function_exists('wc_get_page_id') && wc_get_page_id('shop') > 0
        ? get_permalink((int) wc_get_page_id('shop'))
        : nice_hair_get_page_url('shop', 'page-templates/page-shop.php');

    if (! is_string($shop_url) || $shop_url === '') {
        $shop_url = home_url('/shop/');
    }

    return [
        [
            'slug' => 'salon',
            'label' => 'Salon',
            'url' => nice_hair_get_page_url('salon', 'page-templates/page-salon.php'),
            'is_active' => $active_section === 'salon',
        ],
        [
            'slug' => 'shop',
            'label' => 'Shop',
            'url' => $shop_url,
            'is_active' => $active_section === 'shop',
        ],
    ];
}
