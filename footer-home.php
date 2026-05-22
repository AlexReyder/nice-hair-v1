<?php
declare(strict_types=1);

$default_logo_url = get_template_directory_uri() . '/assets/images/logo.svg';
$logo_url = (string) nice_hair_get_layout_field('nh_footer_logo', 'home', $default_logo_url);
$logo_alt = (string) nice_hair_get_layout_field('nh_footer_logo_alt', 'home', 'Nice Hair');
$description = (string) nice_hair_get_layout_field(
    'nh_footer_description',
    'home',
    '2026 / Premium Hair Extensions in the Heart of Dubai'
);

$default_nav_primary = [
    [
        'item_link' => [
            'url' => '#',
            'title' => 'About',
            'target' => '',
        ],
    ],
    [
        'item_link' => [
            'url' => '#',
            'title' => 'Salon',
            'target' => '',
        ],
    ],
    [
        'item_link' => [
            'url' => '#',
            'title' => 'Shop',
            'target' => '',
        ],
    ],
];

$default_nav_secondary = [
    [
        'item_link' => [
            'url' => '#',
            'title' => 'Contact',
            'target' => '',
        ],
    ],
];

$nav_primary = nice_hair_get_layout_field('nh_footer_nav_primary', 'home', $default_nav_primary);
$nav_secondary = nice_hair_get_layout_field('nh_footer_nav_secondary', 'home', $default_nav_secondary);

if (! is_array($nav_primary) || $nav_primary === []) {
    $nav_primary = $default_nav_primary;
}

if (! is_array($nav_secondary) || $nav_secondary === []) {
    $nav_secondary = $default_nav_secondary;
}

$address = nice_hair_contact_wrap_brackets(nice_hair_get_contact_address_plain('home'));
$hours = nice_hair_get_contact_hours('home');
$telegram_url = nice_hair_get_contact_social_url('telegram', 'home', '#');
$instagram_url = nice_hair_get_contact_social_url('instagram', 'home', '#');
$whatsapp_url = nice_hair_get_contact_social_url('whatsapp', 'home', '#');
$phone_display = nice_hair_get_contact_phone_display('home');
$phone_link = nice_hair_get_contact_phone_link('home');

$cta = nice_hair_get_layout_field('nh_footer_cta', 'home', []);
$cta = is_array($cta) ? $cta : [];
$cta_primary_label = is_string($cta['btn1_label'] ?? null) && trim((string) $cta['btn1_label']) !== ''
    ? (string) $cta['btn1_label']
    : 'VISIT SALON';
$cta_primary_url = is_string($cta['btn1_url'] ?? null) && trim((string) $cta['btn1_url']) !== ''
    ? (string) $cta['btn1_url']
    : '#';
$cta_secondary_label = is_string($cta['btn2_label'] ?? null) && trim((string) $cta['btn2_label']) !== ''
    ? (string) $cta['btn2_label']
    : 'VISIT SHOP';
$cta_secondary_url = is_string($cta['btn2_url'] ?? null) && trim((string) $cta['btn2_url']) !== ''
    ? (string) $cta['btn2_url']
    : '#';

$privacy_url = (string) nice_hair_get_layout_field('nh_footer_privacy_url', 'home', '#');

$credits = nice_hair_get_layout_field('nh_footer_credits', 'home', []);
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
<footer class="nh-site-footer nh-site-footer--home">
    <div class="nh-site-footer__inner">
        <div class="nh-site-footer__main">

            <div class="nh-site-footer__brand">
                <img class="nh-site-footer__logo" src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($logo_alt); ?>" width="210" height="50">
                <p class="nh-site-footer__desc"><?php echo esc_html($description); ?></p>
            </div>

            <nav class="nh-site-footer__nav" aria-label="Footer navigation 1">
                <ul class="nh-site-footer__nav-list">
                    <?php foreach ($nav_primary as $nav_item) :
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
                        ?>
                        <li>
                            <a
                                href="<?php echo esc_url($link['url']); ?>"
                                <?php if ($link['target'] !== '') : ?>target="<?php echo esc_attr($link['target']); ?>" rel="noopener"<?php endif; ?>
                            ><?php echo esc_html($link['title']); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <ul class="nh-site-footer__nav-list">
                    <?php foreach ($nav_secondary as $nav_item) :
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
                        ?>
                        <li>
                            <a
                                href="<?php echo esc_url($link['url']); ?>"
                                <?php if ($link['target'] !== '') : ?>target="<?php echo esc_attr($link['target']); ?>" rel="noopener"<?php endif; ?>
                            ><?php echo esc_html($link['title']); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
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
                <div class="wp-block-button nh-cta-link nh-cta-link"><a class="wp-block-button__link wp-block-button__link__white wp-element-button" href="<?php echo esc_url($cta_primary_url); ?>"><?php echo esc_html($cta_primary_label); ?></a></div>
                <div class="wp-block-button nh-cta-link nh-cta-link"><a class="wp-block-button__link wp-block-button__link__white wp-element-button" href="<?php echo esc_url($cta_secondary_url); ?>"><?php echo esc_html($cta_secondary_label); ?></a></div>
            </div>

        </div>

        <div class="nh-site-footer__bottom">
            <div class="nh-site-footer__bottom-divider"></div>
            <div class="nh-site-footer__bottom-inner">
                <a href="<?php echo esc_url($privacy_url); ?>" class="nh-site-footer__privacy">Privacy Policy</a>
                <?php nice_hair_render_cookie_settings_button(); ?>
                <div class="nh-site-footer__credits">
                    <p>design: <a href="<?php echo esc_url($designer_url); ?>" target="_blank"><?php echo esc_html($designer_name); ?></a></p>
                    <p>development: <a href="<?php echo esc_url($developer_url); ?>" target="_blank"><?php echo esc_html($developer_name); ?></a></p>
                </div>
            </div>
        </div>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
