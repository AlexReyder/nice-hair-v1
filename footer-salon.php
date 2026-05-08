<?php
declare(strict_types=1);

$default_logo_url = get_template_directory_uri() . '/assets/images/logo.svg';
$logo_url = (string) nice_hair_get_layout_field('nh_footer_logo', 'salon', $default_logo_url);
$logo_alt = (string) nice_hair_get_layout_field('nh_footer_logo_alt', 'salon', 'Nice Hair');
$description = (string) nice_hair_get_layout_field(
    'nh_footer_description',
    'salon',
    '2026 / Premium Hair Extensions in the Heart of Dubai'
);

$default_nav_columns = [
    [
        ['item_link' => ['url' => '#', 'title' => 'About', 'target' => '']],
        ['item_link' => ['url' => '#', 'title' => 'Technology', 'target' => '']],
        ['item_link' => ['url' => '#', 'title' => 'Results', 'target' => '']],
    ],
    [
        ['item_link' => ['url' => '#', 'title' => 'Our Approach', 'target' => '']],
        ['item_link' => ['url' => '#', 'title' => 'Prices', 'target' => '']],
        ['item_link' => ['url' => '#', 'title' => 'Discounts', 'target' => '']],
    ],
    [
        ['item_link' => ['url' => '#', 'title' => 'FAQ', 'target' => '']],
        ['item_link' => ['url' => '#', 'title' => 'Blog', 'target' => '']],
        ['item_link' => ['url' => '#', 'title' => 'Contact', 'target' => '']],
    ],
];

$nav_columns = [
    nice_hair_get_layout_field('nh_footer_nav_column_1', 'salon', $default_nav_columns[0]),
    nice_hair_get_layout_field('nh_footer_nav_column_2', 'salon', $default_nav_columns[1]),
    nice_hair_get_layout_field('nh_footer_nav_column_3', 'salon', $default_nav_columns[2]),
];

$has_custom_nav_columns = false;

foreach ($nav_columns as $column_items) {
    if (is_array($column_items) && $column_items !== []) {
        $has_custom_nav_columns = true;
        break;
    }
}

if (! $has_custom_nav_columns) {
    $nav_columns = $default_nav_columns;
}

$nav_links = [];

foreach ($nav_columns as $column_items) {
    $column_items = is_array($column_items) ? $column_items : [];

    foreach ($column_items as $nav_item) {
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
}

$address = nice_hair_contact_wrap_brackets(nice_hair_get_contact_address_plain('salon'));
$hours = nice_hair_get_contact_hours('salon');
$telegram_url = nice_hair_get_contact_social_url('telegram', 'salon', '#');
$instagram_url = nice_hair_get_contact_social_url('instagram', 'salon', '#');
$whatsapp_url = nice_hair_get_contact_social_url('whatsapp', 'salon', '#');
$phone_display = nice_hair_get_contact_phone_display('salon');
$phone_link = nice_hair_get_contact_phone_link('salon');

$booking_link = nice_hair_normalize_link_array(
    nice_hair_get_layout_field('nh_footer_booking_link', 'salon'),
    [
        'url' => '#',
        'title' => 'BOOK AN APPOINTMENT',
        'target' => '',
    ]
);

$subscribe_title = (string) nice_hair_get_layout_field('nh_footer_subscribe_title', 'salon', 'SUBSCRIBE TO NEWS:');
$subscribe_placeholder = (string) nice_hair_get_layout_field('nh_footer_subscribe_placeholder', 'salon', 'Email');
$privacy_url = (string) nice_hair_get_layout_field('nh_footer_privacy_url', 'salon', '#');

$credits = nice_hair_get_layout_field('nh_footer_credits', 'salon', []);
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
<footer class="nh-site-footer nh-site-footer--salon">
    <div class="nh-site-footer__inner">
        <div class="nh-site-footer__main">
            <div class="nh-site-footer__brand">
                <img class="nh-site-footer__logo" src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($logo_alt); ?>" width="338" height="73">
                <p class="nh-site-footer__desc"><?php echo esc_html($description); ?></p>
            </div>

            <nav class="nh-site-footer__nav" aria-label="Salon footer navigation">
                <ul class="nh-site-footer__nav-list nh-site-footer__nav-list--salon">
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

                <div class="nh-site-footer__booking">
                    <a
                        class="nh-site-footer__btn"
                        href="#book"
                        data-popup-salon-trigger
                        data-popup-label="Book an appointment"
                    ><?php echo esc_html($booking_link['title']); ?></a>
                </div>

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
                        <input type="hidden" name="source" value="footer_salon">

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
