<?php

declare(strict_types=1);

function nice_hair_cookie_consent_post_id(): string
{
    return 'nh_cookie_consent';
}

function nice_hair_cookie_consent_cookie_name(): string
{
    return 'nh_cookie_consent';
}

function nice_hair_get_cookie_consent_raw_field(string $field_name, mixed $fallback = null): mixed
{
    if (! function_exists('get_field')) {
        return $fallback;
    }

    $value = get_field($field_name, nice_hair_cookie_consent_post_id());

    return $value === null ? $fallback : $value;
}

function nice_hair_get_cookie_consent_text_field(string $field_name, string $fallback = ''): string
{
    $value = nice_hair_get_cookie_consent_raw_field($field_name, null);

    if (is_string($value) && trim($value) !== '') {
        return trim($value);
    }

    return $fallback;
}

function nice_hair_get_cookie_consent_bool_field(string $field_name, bool $fallback = false): bool
{
    $value = nice_hair_get_cookie_consent_raw_field($field_name, null);

    if ($value === null || $value === '') {
        return $fallback;
    }

    return in_array($value, [true, 1, '1', 'true', 'yes', 'on'], true);
}

function nice_hair_get_cookie_consent_int_field(string $field_name, int $fallback = 1): int
{
    $value = nice_hair_get_cookie_consent_raw_field($field_name, null);

    if (is_numeric($value)) {
        return max(1, (int) $value);
    }

    return max(1, $fallback);
}

function nice_hair_get_cookie_consent_default_policy_url(): string
{
    if (function_exists('nice_hair_get_page_url')) {
        $page_url = nice_hair_get_page_url('privacy-policy');

        if (is_string($page_url) && $page_url !== '' && $page_url !== '#') {
            return $page_url;
        }
    }

    return home_url('/privacy-policy/');
}

function nice_hair_get_cookie_consent_policy_link(): array
{
    $fallback = [
        'url' => nice_hair_get_cookie_consent_default_policy_url(),
        'title' => 'Privacy Policy',
        'target' => '',
    ];

    $value = nice_hair_get_cookie_consent_raw_field('nh_cookie_consent_policy_link', null);

    if (function_exists('nice_hair_normalize_link_array')) {
        return nice_hair_normalize_link_array($value, $fallback);
    }

    if (is_array($value)) {
        return [
            'url' => isset($value['url']) && is_string($value['url']) && trim($value['url']) !== ''
                ? $value['url']
                : $fallback['url'],
            'title' => isset($value['title']) && is_string($value['title']) && trim($value['title']) !== ''
                ? $value['title']
                : $fallback['title'],
            'target' => isset($value['target']) && is_string($value['target'])
                ? $value['target']
                : $fallback['target'],
        ];
    }

    if (is_string($value) && trim($value) !== '') {
        return [
            'url' => trim($value),
            'title' => $fallback['title'],
            'target' => '',
        ];
    }

    return $fallback;
}

function nice_hair_get_cookie_consent_config(): array
{
    return [
        'enabled' => nice_hair_get_cookie_consent_bool_field('nh_cookie_consent_enabled', true),
        'version' => nice_hair_get_cookie_consent_int_field('nh_cookie_consent_version', 1),
        'expiration_days' => nice_hair_get_cookie_consent_int_field('nh_cookie_consent_expiration_days', 180),
        'title' => nice_hair_get_cookie_consent_text_field('nh_cookie_consent_title', 'We use cookies'),
        'text' => nice_hair_get_cookie_consent_text_field(
            'nh_cookie_consent_text',
            'We use necessary cookies to make the website work. With your consent, we may also use analytics and marketing cookies to improve the website and personalise communication.'
        ),
        'policy_link' => nice_hair_get_cookie_consent_policy_link(),
        'accept_all_label' => nice_hair_get_cookie_consent_text_field('nh_cookie_consent_accept_all_label', 'Accept all'),
        'reject_optional_label' => nice_hair_get_cookie_consent_text_field('nh_cookie_consent_reject_optional_label', 'Reject optional'),
        'settings_label' => nice_hair_get_cookie_consent_text_field('nh_cookie_consent_settings_label', 'Cookie settings'),
        'save_label' => nice_hair_get_cookie_consent_text_field('nh_cookie_consent_save_label', 'Save choices'),
        'back_label' => nice_hair_get_cookie_consent_text_field('nh_cookie_consent_back_label', 'Back'),
        'close_label' => nice_hair_get_cookie_consent_text_field('nh_cookie_consent_close_label', 'Close cookie settings'),
        'categories' => [
            'necessary' => [
                'enabled' => true,
                'title' => nice_hair_get_cookie_consent_text_field('nh_cookie_consent_necessary_title', 'Necessary cookies'),
                'description' => nice_hair_get_cookie_consent_text_field(
                    'nh_cookie_consent_necessary_text',
                    'Required for cart, checkout, security and basic website functionality. These cannot be disabled.'
                ),
            ],
            'analytics' => [
                'enabled' => nice_hair_get_cookie_consent_bool_field('nh_cookie_consent_analytics_enabled', true),
                'title' => nice_hair_get_cookie_consent_text_field('nh_cookie_consent_analytics_title', 'Analytics cookies'),
                'description' => nice_hair_get_cookie_consent_text_field(
                    'nh_cookie_consent_analytics_text',
                    'Help us understand how visitors use the website so we can improve pages, navigation and content.'
                ),
            ],
            'marketing' => [
                'enabled' => nice_hair_get_cookie_consent_bool_field('nh_cookie_consent_marketing_enabled', true),
                'title' => nice_hair_get_cookie_consent_text_field('nh_cookie_consent_marketing_title', 'Marketing cookies'),
                'description' => nice_hair_get_cookie_consent_text_field(
                    'nh_cookie_consent_marketing_text',
                    'May be used to personalise advertising and measure campaign performance.'
                ),
            ],
        ],
    ];
}

function nice_hair_get_cookie_consent_from_request(): array
{
    $cookie_name = nice_hair_cookie_consent_cookie_name();
    $raw_value = $_COOKIE[$cookie_name] ?? '';

    if (! is_string($raw_value) || $raw_value === '') {
        return [];
    }

    if (function_exists('wp_unslash')) {
        $raw_value = wp_unslash($raw_value);
    } else {
        $raw_value = stripslashes($raw_value);
    }

    $decoded = json_decode($raw_value, true);

    if (! is_array($decoded)) {
        $decoded = json_decode(rawurldecode($raw_value), true);
    }

    return is_array($decoded) ? $decoded : [];
}

function nice_hair_user_has_cookie_consent(string $category): bool
{
    $category = trim($category);

    if ($category === '') {
        return false;
    }

    $consent = nice_hair_get_cookie_consent_from_request();

    if ($consent === []) {
        return false;
    }

    $config = nice_hair_get_cookie_consent_config();
    $version = (int) ($config['version'] ?? 1);

    if ((int) ($consent['version'] ?? 0) !== $version) {
        return false;
    }

    if (($consent['necessary'] ?? false) !== true) {
        return false;
    }

    if ($category === 'necessary') {
        return true;
    }

    return ($consent[$category] ?? false) === true;
}

function nice_hair_is_cookie_consent_enabled(): bool
{
    if (is_admin() || wp_doing_ajax()) {
        return false;
    }

    $config = nice_hair_get_cookie_consent_config();

    return (bool) ($config['enabled'] ?? true);
}

function nice_hair_render_cookie_consent(): void
{
    if (! nice_hair_is_cookie_consent_enabled()) {
        return;
    }

    get_template_part(
        'template-parts/cookie-consent/banner',
        null,
        [
            'config' => nice_hair_get_cookie_consent_config(),
        ]
    );
}
add_action('wp_footer', 'nice_hair_render_cookie_consent', 30);

function nice_hair_render_cookie_settings_button(
    string $class = 'nh-site-footer__cookie-settings',
    string $label = 'Cookie settings'
): void {
    if (! nice_hair_is_cookie_consent_enabled()) {
        return;
    }

    $label = trim($label) !== '' ? $label : 'Cookie settings';
    ?>
    <button type="button" class="<?php echo esc_attr($class); ?>" data-nh-cookie-settings-trigger>
        <?php echo esc_html($label); ?>
    </button>
    <?php
}
