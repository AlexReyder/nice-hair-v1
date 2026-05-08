<?php
declare(strict_types=1);

$default_logo_url = get_template_directory_uri() . '/assets/images/logo.svg';
$logo_url = (string) nice_hair_get_layout_field('nh_header_logo', 'home', $default_logo_url);
$logo_alt = (string) nice_hair_get_layout_field('nh_header_logo_alt', 'home', 'Nice Hair');

$default_nav_items = [
    [
        'item_link' => [
            'url' => '#home-about',
            'title' => 'About',
            'target' => '',
        ],
    ],
    [
        'item_link' => [
            'url' => '#home-salon',
            'title' => 'Salon',
            'target' => '',
        ],
    ],
    [
        'item_link' => [
            'url' => '#home-shop',
            'title' => 'Shop',
            'target' => '',
        ],
    ],
    [
        'item_link' => [
            'url' => '#home-contact',
            'title' => 'Contact',
            'target' => '',
        ],
    ],
];

$nav_items = nice_hair_get_layout_field('nh_header_nav_items', 'home', $default_nav_items);
if (! is_array($nav_items) || $nav_items === []) {
    $nav_items = $default_nav_items;
}

$nav_links = [];

foreach ($nav_items as $nav_item) {
    $link = nice_hair_normalize_link_array(
        is_array($nav_item) ? ($nav_item['item_link'] ?? null) : null,
        [
            'url' => '#',
            'title' => '',
            'target' => '',
        ]
    );

    if ($link['title'] === '') {
        continue;
    }

    $nav_links[] = $link;
}

$cta_primary = nice_hair_normalize_link_array(
    nice_hair_get_layout_field('nh_header_cta_primary', 'home'),
    [
        'url' => '#home-salon',
        'title' => 'Salon',
        'target' => '',
    ]
);

$cta_secondary = nice_hair_normalize_link_array(
    nice_hair_get_layout_field('nh_header_cta_secondary', 'home'),
    [
        'url' => '#home-shop',
        'title' => 'Shop',
        'target' => '',
    ]
);

$contact_phone = nice_hair_get_contact_phone_display('home');
$contact_phone_link = nice_hair_get_contact_phone_link('home');
$contact_address = nice_hair_get_contact_address_plain('home');
$contact_hours = nice_hair_get_contact_hours('home');
$telegram_url = nice_hair_get_contact_social_url('telegram', 'home', '#');
$instagram_url = nice_hair_get_contact_social_url('instagram', 'home', '#');
$whatsapp_url = nice_hair_get_contact_social_url('whatsapp', 'home', '#');

$brand_aria_label = sprintf('%s - %s', $logo_alt, __('Home', 'nice-hair'));
$desktop_navigation_aria_label = __('Home navigation', 'nice-hair');
$drawer_navigation_aria_label = __('Home mobile navigation', 'nice-hair');
$drawer_dialog_aria_label = __('Home menu', 'nice-hair');
$drawer_id = 'nh-site-header-drawer-home';
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class('nh-body nh-body--home'); ?>>
<?php wp_body_open(); ?>

<header class="nh-site-header nh-site-header--home">
    <div class="nh-site-header__inner">

        <a class="nh-site-header__brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr($brand_aria_label); ?>">
            <img class="nh-site-header__logo" src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($logo_alt); ?>" width="210" height="50">
        </a>

        <nav class="nh-site-header__nav" aria-label="<?php echo esc_attr($desktop_navigation_aria_label); ?>">
            <ul class="nh-site-header__nav-list">
                <?php foreach ($nav_links as $link) : ?>
                    <li>
                        <a
                            href="<?php echo esc_url($link['url']); ?>"
                            <?php if ($link['target'] !== '') : ?>target="<?php echo esc_attr($link['target']); ?>" rel="noopener"<?php endif; ?>
                        ><?php echo esc_html($link['title']); ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div class="nh-site-header__actions">
            <div class="nh-site-header__section-switch" role="group" aria-label="Section switch">
                <a
                    class="nh-site-header__section-switch-link"
                    href="<?php echo esc_url($cta_primary['url']); ?>"
                    <?php if ($cta_primary['target'] !== '') : ?>target="<?php echo esc_attr($cta_primary['target']); ?>" rel="noopener"<?php endif; ?>
                ><?php echo esc_html($cta_primary['title']); ?></a>
                <a
                    class="nh-site-header__section-switch-link"
                    href="<?php echo esc_url($cta_secondary['url']); ?>"
                    <?php if ($cta_secondary['target'] !== '') : ?>target="<?php echo esc_attr($cta_secondary['target']); ?>" rel="noopener"<?php endif; ?>
                ><?php echo esc_html($cta_secondary['title']); ?></a>
            </div>

            <button type="button" class="nh-site-header__toggle"
                    aria-expanded="false"
                    aria-controls="<?php echo esc_attr($drawer_id); ?>"
                    aria-label="Open menu">
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
                    <a
                        class="nh-site-header__section-switch-link"
                        href="<?php echo esc_url($cta_primary['url']); ?>"
                        <?php if ($cta_primary['target'] !== '') : ?>target="<?php echo esc_attr($cta_primary['target']); ?>" rel="noopener"<?php endif; ?>
                    ><?php echo esc_html($cta_primary['title']); ?></a>
                    <a
                        class="nh-site-header__section-switch-link"
                        href="<?php echo esc_url($cta_secondary['url']); ?>"
                        <?php if ($cta_secondary['target'] !== '') : ?>target="<?php echo esc_attr($cta_secondary['target']); ?>" rel="noopener"<?php endif; ?>
                    ><?php echo esc_html($cta_secondary['title']); ?></a>
                </div>

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
                        <li>
                            <a
                                href="<?php echo esc_url($link['url']); ?>"
                                <?php if ($link['target'] !== '') : ?>target="<?php echo esc_attr($link['target']); ?>" rel="noopener"<?php endif; ?>
                            ><?php echo esc_html($link['title']); ?></a>
                        </li>
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
