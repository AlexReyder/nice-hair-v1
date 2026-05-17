<?php
declare(strict_types=1);

$default_logo_url = get_template_directory_uri() . '/assets/images/logo.svg';
$logo_url = (string) nice_hair_get_layout_field('nh_footer_logo', 'shop', $default_logo_url);
$logo_alt = (string) nice_hair_get_layout_field('nh_footer_logo_alt', 'shop', 'Nice Hair');
$description = (string) nice_hair_get_layout_field(
    'nh_footer_description',
    'shop',
    '2026 / Premium Hair Extensions in the Heart of Dubai'
);

$get_product_category_url = static function (string $category_name): string {
    if (! taxonomy_exists('product_cat')) {
        return '#';
    }

    $term = get_term_by('name', $category_name, 'product_cat');

    if (! $term instanceof WP_Term) {
        return '#';
    }

    $url = get_term_link($term);

    return is_wp_error($url) ? '#' : (string) $url;
};

$default_nav_groups = [
    [
        'title' => 'Catalog',
        'items' => [
            ['item_link' => ['url' => $get_product_category_url('Hair and Custom Hair Extensions'), 'title' => 'Hair and Custom Hair Extensions', 'target' => '']],
            ['item_link' => ['url' => $get_product_category_url('Ready-to-Install Hair Extensions'), 'title' => 'Ready-to-Install Hair Extensions', 'target' => '']],
            ['item_link' => ['url' => $get_product_category_url('Keratin'), 'title' => 'Keratin', 'target' => '']],
            ['item_link' => ['url' => $get_product_category_url('Tools'), 'title' => 'Tools', 'target' => '']],
            ['item_link' => ['url' => $get_product_category_url('Exclusive Hair'), 'title' => 'Exclusive Hair', 'target' => '']],
        ],
    ],
    [
        'title' => 'Site map',
        'items' => [
            ['item_link' => ['url' => nice_hair_get_page_url('shop', 'page-templates/page-shop.php'), 'title' => 'Catalog', 'target' => '']],
            ['item_link' => ['url' => nice_hair_get_page_url('shipping-payment', 'page-templates/page-shipping-payment.php'), 'title' => 'Shipping & Payment', 'target' => '']],
            ['item_link' => ['url' => nice_hair_get_page_url('about'), 'title' => 'About', 'target' => '']],
            ['item_link' => ['url' => nice_hair_get_page_url('faq'), 'title' => 'FAQ', 'target' => '']],
            ['item_link' => ['url' => nice_hair_get_page_url('blog'), 'title' => 'Blog', 'target' => '']],
            ['item_link' => ['url' => nice_hair_get_page_url('contact'), 'title' => 'Contact', 'target' => '']],
        ],
    ],
];

$nav_group_titles = [
    (string) nice_hair_get_layout_field('nh_footer_nav_column_1_title', 'shop', $default_nav_groups[0]['title']),
    (string) nice_hair_get_layout_field('nh_footer_nav_column_2_title', 'shop', $default_nav_groups[1]['title']),
];

$nav_group_items = [
    nice_hair_get_layout_field('nh_footer_nav_column_1', 'shop', $default_nav_groups[0]['items']),
    nice_hair_get_layout_field('nh_footer_nav_column_2', 'shop', $default_nav_groups[1]['items']),
];

$has_custom_nav_groups = false;

foreach ($nav_group_items as $group_items) {
    if (is_array($group_items) && $group_items !== []) {
        $has_custom_nav_groups = true;
        break;
    }
}

if (! $has_custom_nav_groups) {
    $nav_group_items = [
        $default_nav_groups[0]['items'],
        $default_nav_groups[1]['items'],
    ];
}

$nav_groups = [];

foreach ($nav_group_items as $index => $group_items) {
    $group_items = is_array($group_items) ? $group_items : [];
    $group_links = [];

    foreach ($group_items as $nav_item) {
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

        $group_links[] = $link;
    }

    if ($group_links === []) {
        $default_group_items = $default_nav_groups[$index]['items'] ?? [];

        foreach ($default_group_items as $nav_item) {
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

            $group_links[] = $link;
        }
    }

    $title = trim($nav_group_titles[$index] ?? '');

    $nav_groups[] = [
        'title' => $title !== '' ? $title : $default_nav_groups[$index]['title'],
        'links' => $group_links,
    ];
}

$address = nice_hair_contact_wrap_brackets(nice_hair_get_contact_address_plain('shop'));
$hours = nice_hair_get_contact_hours('shop');
$telegram_url = nice_hair_get_contact_social_url('telegram', 'shop', '#');
$instagram_url = nice_hair_get_contact_social_url('instagram', 'shop', '#');
$whatsapp_url = nice_hair_get_contact_social_url('whatsapp', 'shop', '#');
$phone_display = nice_hair_get_contact_phone_display('shop');
$phone_link = nice_hair_get_contact_phone_link('shop');

$shopping_link = nice_hair_normalize_link_array(
    nice_hair_get_layout_field('nh_footer_booking_link', 'shop'),
    [
        'url' => nice_hair_get_page_url('shop', 'page-templates/page-shop.php'),
        'title' => 'START SHOPPING',
        'target' => '',
    ]
);

$subscribe_title = (string) nice_hair_get_layout_field('nh_footer_subscribe_title', 'shop', 'SUBSCRIBE TO NEWS:');
$subscribe_placeholder = (string) nice_hair_get_layout_field('nh_footer_subscribe_placeholder', 'shop', 'Email');
$privacy_url = (string) nice_hair_get_layout_field('nh_footer_privacy_url', 'shop', '#');

$credits = nice_hair_get_layout_field('nh_footer_credits', 'shop', []);
$credits = is_array($credits) ? $credits : [];

$designer_name = is_string($credits['designer_name'] ?? null) && trim((string) $credits['designer_name']) !== ''
    ? (string) $credits['designer_name']
    : 'umapalata.space';
$designer_url = is_string($credits['designer_url'] ?? null) && trim((string) $credits['designer_url']) !== ''
    ? (string) $credits['designer_url']
    : '#';
$developer_name = is_string($credits['developer_name'] ?? null) && trim((string) $credits['developer_name']) !== ''
    ? (string) $credits['developer_name']
    : 'username';
$developer_url = is_string($credits['developer_url'] ?? null) && trim((string) $credits['developer_url']) !== ''
    ? (string) $credits['developer_url']
    : '#';
?>
<footer class="nh-site-footer nh-site-footer--shop">
    <div class="nh-site-footer__inner">
        <div class="nh-site-footer__main">
            <div class="nh-site-footer__brand">
                <img class="nh-site-footer__logo" src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($logo_alt); ?>" width="338" height="73">
                <p class="nh-site-footer__desc"><?php echo esc_html($description); ?></p>
            </div>

            <nav class="nh-site-footer__nav nh-site-footer__nav--shop" aria-label="Shop footer navigation">
                <?php foreach ($nav_groups as $group) : ?>
                    <div class="nh-site-footer__nav-group">
                        <p class="nh-site-footer__nav-heading"><?php echo esc_html($group['title']); ?></p>

                        <ul class="nh-site-footer__nav-list nh-site-footer__nav-list--shop">
                            <?php foreach ($group['links'] as $link) : ?>
                                <li>
                                    <a
                                        href="<?php echo esc_url($link['url']); ?>"
                                        <?php if ($link['target'] !== '') : ?>target="<?php echo esc_attr($link['target']); ?>" rel="noopener"<?php endif; ?>
                                    ><?php echo esc_html($link['title']); ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </nav>

            <div class="nh-site-footer__info">
                <p class="nh-site-footer__address"><?php echo esc_html($address); ?></p>
                <p class="nh-site-footer__hours"><?php echo esc_html($hours); ?></p>

                <div class="nh-site-footer__socials">
                    <a href="<?php echo esc_url($telegram_url); ?>" class="nh-site-footer__social">Telegram</a>
                    <a href="<?php echo esc_url($instagram_url); ?>" class="nh-site-footer__social">Instagram</a>
                    <a href="<?php echo esc_url($whatsapp_url); ?>" class="nh-site-footer__social">WhatsApp</a>
                </div>
            </div>

            <div class="nh-site-footer__cta">
                <a href="tel:<?php echo esc_attr($phone_link); ?>" class="nh-site-footer__phone"><?php echo esc_html($phone_display); ?></a>

                <a
                    class="nh-site-footer__shop-button"
                    href="<?php echo esc_url($shopping_link['url']); ?>"
                    <?php if ($shopping_link['target'] !== '') : ?>target="<?php echo esc_attr($shopping_link['target']); ?>" rel="noopener"<?php endif; ?>
                ><?php echo esc_html($shopping_link['title']); ?></a>

                <div class="nh-site-footer__subscribe">
                    <p class="nh-site-footer__subscribe-title"><?php echo esc_html($subscribe_title); ?></p>

                    <form class="nh-site-footer__subscribe-form" data-subscribe-form>
                        <input
                            type="text"
                            name="hp_field"
                            class="nh-site-footer__subscribe-hp"
                            autocomplete="off"
                            tabindex="-1"
                            aria-hidden="true"
                        >
                        <input type="hidden" name="source" value="footer_shop">

                        <div class="nh-site-footer__subscribe-field">
                            <input
                                type="email"
                                name="email"
                                class="nh-site-footer__subscribe-input"
                                placeholder="<?php echo esc_attr($subscribe_placeholder); ?>"
                                required
                                autocomplete="email"
                                aria-label="<?php echo esc_attr($subscribe_title); ?>"
                            >
                            <button type="submit" class="nh-site-footer__subscribe-submit" aria-label="Subscribe">
                                <svg width="20" height="14" viewBox="0 0 20 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M0 7H18" stroke="currentColor"/>
                                    <path d="M13 1L19 7L13 13" stroke="currentColor"/>
                                </svg>
                            </button>
                        </div>

                        <p class="nh-subscribe__status nh-site-footer__subscribe-status" data-subscribe-status aria-live="polite"></p>
                    </form>
                </div>
            </div>
        </div>

        <div class="nh-site-footer__bottom">
            <div class="nh-site-footer__bottom-divider"></div>

            <div class="nh-site-footer__bottom-inner">
                <a href="<?php echo esc_url($privacy_url); ?>" class="nh-site-footer__privacy">Privacy Policy</a>
                <?php nice_hair_render_cookie_settings_button(); ?>
                <div class="nh-site-footer__credits">
                    <p>design: <a href="<?php echo esc_url($designer_url); ?>"><?php echo esc_html($designer_name); ?></a></p>
                    <p>development: <a href="<?php echo esc_url($developer_url); ?>"><?php echo esc_html($developer_name); ?></a></p>
                </div>
            </div>
        </div>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
