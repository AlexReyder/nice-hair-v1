<?php
declare(strict_types=1);

$default_logo_url = get_template_directory_uri() . '/assets/images/logo.svg';
$default_cart_icon_url = get_template_directory_uri() . '/assets/images/cart-icon.svg';

$logo_url = (string) nice_hair_get_layout_field('nh_header_logo', 'shop', $default_logo_url);
$logo_alt = (string) nice_hair_get_layout_field('nh_header_logo_alt', 'shop', 'Nice Hair');

$default_nav_items = [
    ['item_link' => ['url' => nice_hair_get_page_url('shop', 'page-templates/page-shop.php'), 'title' => 'Catalog', 'target' => '']],
    ['item_link' => ['url' => nice_hair_get_page_url('shipping-payment', 'page-templates/page-shipping-payment.php'), 'title' => 'Shipping & Payment', 'target' => '']],
    ['item_link' => ['url' => nice_hair_get_page_url('about'), 'title' => 'About', 'target' => '']],
    ['item_link' => ['url' => nice_hair_get_page_url('faq'), 'title' => 'FAQ', 'target' => '']],
    ['item_link' => ['url' => nice_hair_get_page_url('blog'), 'title' => 'Blog', 'target' => '']],
    ['item_link' => ['url' => nice_hair_get_page_url('contact'), 'title' => 'Contact', 'target' => '']],
];

$nav_items = nice_hair_get_layout_field('nh_header_nav_items', 'shop', $default_nav_items);
if (! is_array($nav_items) || $nav_items === []) {
    $nav_items = $default_nav_items;
}

$default_nav_links = array_map(
    static fn (array $item): array => is_array($item['item_link'] ?? null)
        ? $item['item_link']
        : ['url' => '#', 'title' => '', 'target' => ''],
    $default_nav_items
);

$nav_links = [];

foreach ($nav_items as $index => $nav_item) {
    $fallback_link = $default_nav_links[$index] ?? [
        'url' => '#',
        'title' => '',
        'target' => '',
    ];

    $link = nice_hair_normalize_link_array(
        is_array($nav_item) ? ($nav_item['item_link'] ?? null) : null,
        $fallback_link
    );

    if ($link['title'] === '') {
        continue;
    }

    $nav_links[] = $link;
}

$contact_phone = nice_hair_get_contact_phone_display('shop');
$contact_phone_link = nice_hair_get_contact_phone_link('shop');
$contact_address = nice_hair_get_contact_address_plain('shop');
$contact_hours = nice_hair_get_contact_hours('shop');
$telegram_url = nice_hair_get_contact_social_url('telegram', 'shop', '#');
$instagram_url = nice_hair_get_contact_social_url('instagram', 'shop', '#');
$whatsapp_url = nice_hair_get_contact_social_url('whatsapp', 'shop', '#');
$default_catalog_menu_rows = function_exists('nice_hair_get_shop_catalog_menu_default_field_rows')
    ? nice_hair_get_shop_catalog_menu_default_field_rows()
    : [];
$catalog_menu_rows = function_exists('get_field') && function_exists('nice_hair_header_footer_post_id')
    ? get_field('nh_header_catalog_dropdown_items', nice_hair_header_footer_post_id('shop'))
    : null;

if (! is_array($catalog_menu_rows)) {
    $catalog_menu_rows = nice_hair_get_layout_field('nh_header_catalog_dropdown_items', 'shop', $default_catalog_menu_rows);
}

$catalog_menu_items = [];

if (is_array($catalog_menu_rows)) {
    foreach ($catalog_menu_rows as $catalog_menu_row) {
        $catalog_link = is_array($catalog_menu_row) ? ($catalog_menu_row['item_link'] ?? null) : null;

        if (! is_array($catalog_link)) {
            continue;
        }

        $catalog_title = isset($catalog_link['title']) ? trim((string) $catalog_link['title']) : '';
        $catalog_url = isset($catalog_link['url']) ? trim((string) $catalog_link['url']) : '';
        $catalog_target = isset($catalog_link['target']) ? trim((string) $catalog_link['target']) : '';

        if ($catalog_title === '' || $catalog_url === '' || $catalog_url === '#') {
            continue;
        }

        $catalog_menu_items[] = [
            'title'  => $catalog_title,
            'url'    => $catalog_url,
            'target' => $catalog_target,
        ];
    }
}

$current_section = nice_hair_get_current_section('shop');
$section_switch_items = nice_hair_get_section_switch_items($current_section);
$brand_aria_label = sprintf('%s - Shop', $logo_alt);
$desktop_navigation_aria_label = 'Shop navigation';
$drawer_navigation_aria_label = 'Shop mobile navigation';
$drawer_dialog_aria_label = 'Shop menu';
$drawer_id = 'nh-site-header-drawer-shop';
$cart_count = function_exists('WC') && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
$use_cart_link = function_exists('nice_hair_is_shop_utility_page') && nice_hair_is_shop_utility_page();
$cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart/');

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class('nh-body nh-body--shop'); ?>>
<?php wp_body_open(); ?>

<header class="nh-site-header nh-site-header--shop">
    <div class="nh-site-header__inner">
        <a class="nh-site-header__brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr($brand_aria_label); ?>">
            <img class="nh-site-header__logo" src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($logo_alt); ?>" width="132" height="32">
            <span class="nh-site-header__brand-accent" aria-hidden="true"></span>
        </a>

        <nav class="nh-site-header__nav" aria-label="<?php echo esc_attr($desktop_navigation_aria_label); ?>">
            <ul class="nh-site-header__nav-list">
                <?php foreach ($nav_links as $link) : ?>
                    <?php $is_catalog_link = strcasecmp(trim($link['title']), 'Catalog') === 0; ?>
                    <li class="nh-site-header__nav-item<?php echo $is_catalog_link && $catalog_menu_items !== [] ? ' nh-site-header__nav-item--catalog' : ''; ?>"<?php if ($is_catalog_link && $catalog_menu_items !== []) : ?> data-nh-header-desktop-catalog<?php endif; ?>>
                        <a
                            class="nh-site-header__nav-link"
                            href="<?php echo esc_url($link['url']); ?>"
                            <?php if ($is_catalog_link && $catalog_menu_items !== []) : ?>aria-haspopup="true" aria-expanded="false"<?php endif; ?>
                            <?php if ($link['target'] !== '') : ?>target="<?php echo esc_attr($link['target']); ?>" rel="noopener"<?php endif; ?>
                        ><span class="nh-site-header__nav-link-label"><?php echo esc_html($link['title']); ?></span><?php if ($is_catalog_link) : ?><span class="nh-site-header__nav-link-plus" aria-hidden="true"> +</span><?php endif; ?></a>
                        <?php if ($is_catalog_link && $catalog_menu_items !== []) : ?>
                            <div class="nh-site-header__nav-submenu" aria-label="Catalog categories">
                                <ul class="nh-site-header__nav-submenu-list">
                                    <?php foreach ($catalog_menu_items as $catalog_item) : ?>
                                        <li>
                                            <a class="nh-site-header__nav-submenu-link" href="<?php echo esc_url((string) $catalog_item['url']); ?>"<?php if (((string) ($catalog_item['target'] ?? '')) !== '') : ?> target="<?php echo esc_attr((string) $catalog_item['target']); ?>" rel="noopener"<?php endif; ?>>
                                                <?php echo esc_html((string) $catalog_item['title']); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div class="nh-site-header__actions">
            <?php if ($use_cart_link) : ?>
                <a href="<?php echo esc_url($cart_url); ?>" class="nh-site-header__cart-button" aria-label="View cart">
                    <span class="nh-site-header__cart-button-inner" aria-hidden="true">
                        <img class="nh-site-header__cart-icon" src="<?php echo esc_url($default_cart_icon_url); ?>" alt="" width="18" height="18">
                    </span>
                    <span class="nh-cart-count"><?php echo esc_html((string) $cart_count); ?></span>
                </a>
            <?php else : ?>
                <button type="button" class="nh-site-header__cart-button" data-nh-cart-open aria-label="Open cart">
                    <span class="nh-site-header__cart-button-inner" aria-hidden="true">
                        <img class="nh-site-header__cart-icon" src="<?php echo esc_url($default_cart_icon_url); ?>" alt="" width="18" height="18">
                    </span>
                    <span class="nh-cart-count"><?php echo esc_html((string) $cart_count); ?></span>
                </button>
            <?php endif; ?>

            <div class="nh-site-header__section-switch" role="group" aria-label="Section switch">
                <?php foreach ($section_switch_items as $section_switch_item) : ?>
                    <a
                        class="nh-site-header__section-switch-link<?php echo $section_switch_item['is_active'] ? ' is-active' : ''; ?>"
                        href="<?php echo esc_url($section_switch_item['url']); ?>"
                        <?php if ($section_switch_item['is_active']) : ?>aria-current="page"<?php endif; ?>
                    ><?php echo esc_html($section_switch_item['label']); ?></a>
                <?php endforeach; ?>
            </div>

            <button
                type="button"
                class="nh-site-header__toggle"
                aria-expanded="false"
                aria-controls="<?php echo esc_attr($drawer_id); ?>"
                aria-label="Open menu"
            >
                <span class="nh-site-header__toggle-bar" aria-hidden="true"></span>
                <span class="nh-site-header__toggle-bar" aria-hidden="true"></span>
                <span class="nh-site-header__toggle-bar" aria-hidden="true"></span>
            </button>
        </div>
    </div>

    <div class="nh-site-header__divider" aria-hidden="true"></div>

    <div class="nh-site-header__drawer" id="<?php echo esc_attr($drawer_id); ?>" hidden>
        <div class="nh-site-header__drawer-backdrop" data-nh-drawer-close></div>

        <div class="nh-site-header__drawer-panel" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr($drawer_dialog_aria_label); ?>">
            <div class="nh-site-header__drawer-top">
                <div class="nh-site-header__section-switch" role="group" aria-label="Section switch">
                    <?php foreach ($section_switch_items as $section_switch_item) : ?>
                        <a
                            class="nh-site-header__section-switch-link<?php echo $section_switch_item['is_active'] ? ' is-active' : ''; ?>"
                            href="<?php echo esc_url($section_switch_item['url']); ?>"
                            <?php if ($section_switch_item['is_active']) : ?>aria-current="page"<?php endif; ?>
                        ><?php echo esc_html($section_switch_item['label']); ?></a>
                    <?php endforeach; ?>
                </div>

                <?php if ($use_cart_link) : ?>
                    <a href="<?php echo esc_url($cart_url); ?>" class="nh-site-header__cart-button nh-site-header__cart-button--drawer" aria-label="View cart">
                        <span class="nh-site-header__cart-button-inner" aria-hidden="true">
                            <img class="nh-site-header__cart-icon" src="<?php echo esc_url($default_cart_icon_url); ?>" alt="" width="18" height="18">
                        </span>
                        <span class="nh-cart-count"><?php echo esc_html((string) $cart_count); ?></span>
                    </a>
                <?php else : ?>
                    <button type="button" class="nh-site-header__cart-button nh-site-header__cart-button--drawer" data-nh-cart-open aria-label="Open cart">
                        <span class="nh-site-header__cart-button-inner" aria-hidden="true">
                            <img class="nh-site-header__cart-icon" src="<?php echo esc_url($default_cart_icon_url); ?>" alt="" width="18" height="18">
                        </span>
                        <span class="nh-cart-count"><?php echo esc_html((string) $cart_count); ?></span>
                    </button>
                <?php endif; ?>

                <button type="button" class="nh-site-header__drawer-close" aria-label="Close menu" data-nh-drawer-close>
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="nh-site-header__drawer-contact">
                <a class="nh-site-header__drawer-phone" href="tel:<?php echo esc_attr($contact_phone_link); ?>"><?php echo esc_html($contact_phone); ?></a>
                <p class="nh-site-header__drawer-address"><?php echo wp_kses_post($contact_address); ?></p>
            </div>

            <nav class="nh-site-header__drawer-nav" aria-label="<?php echo esc_attr($drawer_navigation_aria_label); ?>">
                <ul class="nh-site-header__drawer-list">
                    <?php foreach ($nav_links as $link) : ?>
                        <?php $is_catalog_link = strcasecmp(trim($link['title']), 'Catalog') === 0; ?>
                        <?php if ($is_catalog_link && $catalog_menu_items !== []) : ?>
                            <li class="nh-site-header__drawer-item nh-site-header__drawer-item--catalog" data-nh-header-accordion>
                                <div class="nh-site-header__drawer-item-row">
                                    <button
                                        type="button"
                                        class="nh-site-header__drawer-accordion-trigger"
                                        aria-expanded="false"
                                        aria-controls="<?php echo esc_attr($drawer_id); ?>-catalog-panel"
                                        aria-label="Toggle catalog categories"
                                        data-nh-header-accordion-toggle
                                    >
                                        <span class="nh-site-header__drawer-accordion-label"><?php echo esc_html($link['title']); ?></span>
                                        <span class="nh-site-header__drawer-accordion-icon" aria-hidden="true"></span>
                                    </button>
                                </div>

                                <div
                                    class="nh-site-header__drawer-accordion-panel"
                                    id="<?php echo esc_attr($drawer_id); ?>-catalog-panel"
                                    hidden
                                    data-nh-header-accordion-panel
                                >
                                    <ul class="nh-site-header__drawer-submenu-list">
                                        <?php foreach ($catalog_menu_items as $catalog_item) : ?>
                                            <li>
                                                <a class="nh-site-header__drawer-submenu-link" href="<?php echo esc_url((string) $catalog_item['url']); ?>"<?php if (((string) ($catalog_item['target'] ?? '')) !== '') : ?> target="<?php echo esc_attr((string) $catalog_item['target']); ?>" rel="noopener"<?php endif; ?>>
                                                    <?php echo esc_html((string) $catalog_item['title']); ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </li>
                        <?php else : ?>
                            <li class="nh-site-header__drawer-item">
                                <a
                                    class="nh-site-header__drawer-link"
                                    href="<?php echo esc_url($link['url']); ?>"
                                    <?php if ($link['target'] !== '') : ?>target="<?php echo esc_attr($link['target']); ?>" rel="noopener"<?php endif; ?>
                                ><?php echo esc_html($link['title']); ?></a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <div class="nh-site-header__drawer-bottom">
                <p class="nh-site-header__drawer-bottom-address"><?php echo wp_kses_post($contact_address); ?></p>
                <p class="nh-site-header__drawer-hours"><?php echo esc_html($contact_hours); ?></p>

                <div class="nh-site-header__drawer-socials">
                    <a href="<?php echo esc_url($telegram_url); ?>">Telegram</a>
                    <a href="<?php echo esc_url($instagram_url); ?>">Instagram</a>
                    <a href="<?php echo esc_url($whatsapp_url); ?>">WhatsApp</a>
                </div>
            </div>
        </div>
    </div>
</header>
