<?php

declare(strict_types=1);

const NH_SHOP_CONSULTATION_REST_NAMESPACE = 'nice-hair/v1';
const NH_SHOP_CONSULTATION_REST_ROUTE = '/shop-consultation';
const NH_SHOP_CONSULTATION_RATE_LIMIT = 5;
const NH_SHOP_CONSULTATION_RATE_WINDOW = HOUR_IN_SECONDS;

function nice_hair_shop_consultation_product(mixed $product): ?WC_Product
{
    if ($product instanceof WC_Product) {
        return $product;
    }

    if (is_numeric($product) && function_exists('wc_get_product')) {
        $resolved = wc_get_product((int) $product);

        return $resolved instanceof WC_Product ? $resolved : null;
    }

    return null;
}

function nice_hair_shop_consultation_drawer_id(mixed $product): string
{
    $resolved = nice_hair_shop_consultation_product($product);
    $product_id = $resolved instanceof WC_Product ? $resolved->get_id() : 0;

    return 'shop-consultation-' . (string) max(0, (int) $product_id);
}

function nice_hair_render_shop_consultation_cta(mixed $product): void
{
    $drawer_id = nice_hair_shop_consultation_drawer_id($product);

    if ($drawer_id === 'shop-consultation-0') {
        return;
    }
    ?>
    <span class="nh-single-product__cta-control nh-cta-link nh-cta-link--dark">
        <button type="button"
                class="nh-single-product__cta-btn nh-single-product__cta-btn--secondary wp-block-button__link"
                data-nh-content-drawer-target="<?php echo esc_attr($drawer_id); ?>"
                aria-controls="<?php echo esc_attr($drawer_id); ?>">
            <?php esc_html_e('Consultation', 'nice-hair'); ?>
        </button>
    </span>
    <?php
}

function nice_hair_shop_consultation_privacy_url(): string
{
    $privacy_url = '';

    if (function_exists('nice_hair_get_layout_field')) {
        $privacy_url = (string) nice_hair_get_layout_field('nh_footer_privacy_url', 'shop', '');
    }

    if ($privacy_url === '' && function_exists('get_privacy_policy_url')) {
        $privacy_url = (string) get_privacy_policy_url();
    }

    return $privacy_url !== '' ? $privacy_url : '#';
}

function nice_hair_render_shop_consultation_drawer(mixed $product): void
{
    static $rendered = [];

    $resolved = nice_hair_shop_consultation_product($product);

    if (! $resolved instanceof WC_Product) {
        return;
    }

    $product_id = (int) $resolved->get_id();
    $drawer_id = nice_hair_shop_consultation_drawer_id($resolved);

    if ($product_id <= 0 || isset($rendered[$drawer_id])) {
        return;
    }

    $rendered[$drawer_id] = true;
    $product_name = $resolved->get_name();
    $product_url = (string) get_permalink($product_id);
    $privacy_url = nice_hair_shop_consultation_privacy_url();
    ?>
    <div class="nh-content-drawer nh-content-drawer--product-guide nh-content-drawer--shop-consultation"
         id="<?php echo esc_attr($drawer_id); ?>"
         data-nh-content-drawer
         data-nh-content-drawer-id="<?php echo esc_attr($drawer_id); ?>"
         data-nh-shop-consultation
         data-endpoint="<?php echo esc_url(rest_url(NH_SHOP_CONSULTATION_REST_NAMESPACE . NH_SHOP_CONSULTATION_REST_ROUTE)); ?>"
         data-nonce="<?php echo esc_attr(wp_create_nonce('wp_rest')); ?>"
         hidden>
        <div class="nh-content-drawer__backdrop" data-nh-content-drawer-close></div>

        <div class="nh-content-drawer__panel"
             role="dialog"
             aria-modal="true"
             aria-labelledby="<?php echo esc_attr($drawer_id . '-title'); ?>">
            <button type="button"
                    class="nh-content-drawer__close"
                    data-nh-content-drawer-close
                    data-nh-content-drawer-close-button
                    aria-label="<?php esc_attr_e('Close drawer', 'nice-hair'); ?>">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none" aria-hidden="true">
                    <circle cx="16" cy="16" r="15.5" stroke="currentColor"/>
                    <path d="M11 11L21 21M21 11L11 21" stroke="currentColor" stroke-width="1.5"/>
                </svg>
            </button>

            <div class="nh-content-drawer__header">
                <span class="nh-content-drawer__eyebrow"><?php esc_html_e('[ CONSULTATION ]', 'nice-hair'); ?></span>
                <div class="nh-content-drawer__rule" aria-hidden="true"></div>
                <h2 class="nh-content-drawer__title" id="<?php echo esc_attr($drawer_id . '-title'); ?>">
                    <?php esc_html_e('Get a consultation', 'nice-hair'); ?>
                </h2>
            </div>

            <div class="nh-content-drawer__body">
                <div class="nh-single-product__consultation" data-nh-shop-consultation-panel>
                    <form class="nh-single-product__consultation-form" data-nh-shop-consultation-form novalidate>
                        <input type="hidden" name="product_id" value="<?php echo esc_attr((string) $product_id); ?>">
                        <input type="hidden" name="product_name" value="<?php echo esc_attr($product_name); ?>">
                        <input type="hidden" name="product_url" value="<?php echo esc_url($product_url); ?>">
                        <input type="hidden" name="page_url" value="<?php echo esc_url($product_url); ?>" data-nh-shop-consultation-page-url>
                        <input type="text" name="hp_field" class="nh-single-product__consultation-hp" autocomplete="off" tabindex="-1" aria-hidden="true">

                        <p class="nh-single-product__consultation-intro">
                            <?php esc_html_e('LEAVE YOUR CONTACT DETAILS AND WE WILL CONTACT YOU TO DISCUSS YOUR REQUEST AND OFFER THE BEST SOLUTION.', 'nice-hair'); ?>
                        </p>

                        <label class="nh-single-product__consultation-field">
                            <span class="nh-single-product__consultation-label"><?php esc_html_e('NAME', 'nice-hair'); ?></span>
                            <input class="nh-single-product__consultation-input" type="text" name="name" autocomplete="name" required>
                        </label>

                        <label class="nh-single-product__consultation-field">
                            <span class="nh-single-product__consultation-label"><?php esc_html_e('PHONE', 'nice-hair'); ?></span>
                            <input class="nh-single-product__consultation-input" type="tel" name="phone" autocomplete="tel" required>
                        </label>

                        <label class="nh-single-product__consultation-field">
                            <span class="nh-single-product__consultation-label"><?php esc_html_e('WHATSAPP NUMBER', 'nice-hair'); ?></span>
                            <input class="nh-single-product__consultation-input" type="tel" name="whatsapp" autocomplete="tel" required>
                        </label>

                        <div class="nh-single-product__consultation-actions">
                            <span class="nh-single-product__consultation-cta nh-cta-link">
                                <button type="submit"
                                        class="nh-single-product__consultation-submit wp-block-button__link"
                                        data-nh-shop-consultation-submit>
                                    <span class="nh-single-product__consultation-submit-label"><?php esc_html_e('GET A CONSULTATION', 'nice-hair'); ?></span>
                                </button>
                            </span>

                            <p class="nh-single-product__consultation-privacy">
                                <?php
                                echo wp_kses_post(
                                    sprintf(
                                        /* translators: %s: privacy policy URL */
                                        __('By clicking the button, you agree to the <a href="%s">privacy policy</a>.', 'nice-hair'),
                                        esc_url($privacy_url)
                                    )
                                );
                                ?>
                            </p>
                        </div>

                        <p class="nh-single-product__consultation-hint" data-nh-shop-consultation-hint hidden></p>
                    </form>

                    <div class="nh-single-product__consultation-success" data-nh-shop-consultation-success hidden>
                        <p data-nh-shop-consultation-success-message>
                            <?php esc_html_e('Thanks! Your request has been sent. We will contact you shortly.', 'nice-hair'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function nice_hair_shop_consultation_register_rest_route(): void
{
    register_rest_route(NH_SHOP_CONSULTATION_REST_NAMESPACE, NH_SHOP_CONSULTATION_REST_ROUTE, [
        'methods' => 'POST',
        'callback' => 'nice_hair_shop_consultation_handle_submission',
        'permission_callback' => '__return_true',
    ]);
}
add_action('rest_api_init', 'nice_hair_shop_consultation_register_rest_route');

function nice_hair_shop_consultation_handle_submission(WP_REST_Request $request): WP_REST_Response
{
    $nonce = (string) $request->get_header('X-WP-Nonce');

    if (! wp_verify_nonce($nonce, 'wp_rest')) {
        return new WP_REST_Response([
            'success' => false,
            'error' => 'invalid_nonce',
        ], 403);
    }

    $honeypot = (string) $request->get_param('hp_field');

    if ($honeypot !== '') {
        return new WP_REST_Response(['success' => true], 200);
    }

    $ip = nice_hair_shop_consultation_client_ip();
    $skip_rate_limit = nice_hair_shop_consultation_should_skip_rate_limit($ip);
    $rate_key = 'nh_shop_consultation_rate_' . md5($ip);
    $rate_current = $skip_rate_limit ? 0 : (int) get_transient($rate_key);

    if (! $skip_rate_limit && $rate_current >= NH_SHOP_CONSULTATION_RATE_LIMIT) {
        return new WP_REST_Response([
            'success' => false,
            'error' => 'rate_limited',
        ], 429);
    }

    $name = sanitize_text_field((string) $request->get_param('name'));
    $name = mb_substr($name, 0, 100);

    if ($name === '') {
        return nice_hair_shop_consultation_validation_error('name');
    }

    $phone = preg_replace('/\D+/', '', (string) $request->get_param('phone')) ?? '';

    if (strlen($phone) < 7 || strlen($phone) > 15) {
        return nice_hair_shop_consultation_validation_error('phone');
    }

    $whatsapp = preg_replace('/\D+/', '', (string) $request->get_param('whatsapp')) ?? '';

    if (strlen($whatsapp) < 7 || strlen($whatsapp) > 15) {
        return nice_hair_shop_consultation_validation_error('whatsapp');
    }

    $product_id = absint($request->get_param('product_id'));
    $product = $product_id > 0 && function_exists('wc_get_product') ? wc_get_product($product_id) : null;
    $product_name = $product instanceof WC_Product
        ? $product->get_name()
        : sanitize_text_field((string) $request->get_param('product_name'));
    $product_name = mb_substr($product_name, 0, 160);
    $product_url = $product instanceof WC_Product
        ? (string) get_permalink($product_id)
        : esc_url_raw((string) $request->get_param('product_url'));
    $page_url = esc_url_raw((string) $request->get_param('page_url'));
    $submitted_at = current_time('mysql');
    $label = $product_name !== ''
        ? sprintf('Shop consultation: %s', $product_name)
        : 'Shop consultation';

    $post_id = wp_insert_post([
        'post_type' => 'nh_salon_request',
        'post_status' => 'publish',
        'post_title' => sprintf('%s - Shop consultation - %s', $name, wp_date('d M Y H:i')),
        'post_author' => 0,
    ], true);

    if (is_wp_error($post_id) || ! $post_id) {
        error_log(sprintf(
            '[nh_shop_consultation] Request insert failed: %s',
            is_wp_error($post_id) ? $post_id->get_error_message() : 'unknown error'
        ));

        return new WP_REST_Response([
            'success' => false,
            'error' => 'insert_failed',
        ], 500);
    }

    update_post_meta((int) $post_id, '_nh_salon_request_source', 'shop_consultation');
    update_post_meta((int) $post_id, '_nh_popup_salon_name', $name);
    update_post_meta((int) $post_id, '_nh_popup_salon_phone', $phone);
    update_post_meta((int) $post_id, '_nh_popup_salon_label', $label);
    update_post_meta((int) $post_id, '_nh_popup_salon_page_url', $page_url);
    update_post_meta((int) $post_id, '_nh_popup_salon_submitted_at', $submitted_at);
    update_post_meta((int) $post_id, '_nh_popup_salon_user_ip', $ip);
    update_post_meta((int) $post_id, '_nh_shop_consultation_whatsapp', $whatsapp);
    update_post_meta((int) $post_id, '_nh_shop_consultation_product_id', $product_id);
    update_post_meta((int) $post_id, '_nh_shop_consultation_product_name', $product_name);
    update_post_meta((int) $post_id, '_nh_shop_consultation_product_url', $product_url);

    if (! $skip_rate_limit) {
        set_transient($rate_key, $rate_current + 1, NH_SHOP_CONSULTATION_RATE_WINDOW);
    }

    nice_hair_shop_consultation_send_email((int) $post_id);

    return new WP_REST_Response([
        'success' => true,
        'message' => __('Thanks! Your request has been sent. We will contact you shortly.', 'nice-hair'),
    ], 200);
}

function nice_hair_shop_consultation_validation_error(string $field): WP_REST_Response
{
    return new WP_REST_Response([
        'success' => false,
        'error' => 'validation',
        'field' => $field,
    ], 400);
}

function nice_hair_shop_consultation_notification_email(): string
{
    $candidates = [];

    if (function_exists('get_field')) {
        $candidates[] = (string) get_field('nh_pq_admin_email', 'option');
        $candidates[] = (string) get_field('nh_popup_salon_admin_email', 'nh_popup_salon_settings');
    }

    $candidates[] = (string) get_option('admin_email');

    foreach ($candidates as $candidate) {
        if ($candidate !== '' && is_email($candidate)) {
            return $candidate;
        }
    }

    return '';
}

function nice_hair_shop_consultation_send_email(int $post_id): bool
{
    $to = nice_hair_shop_consultation_notification_email();

    if ($to === '') {
        return false;
    }

    $name = (string) get_post_meta($post_id, '_nh_popup_salon_name', true);
    $phone = (string) get_post_meta($post_id, '_nh_popup_salon_phone', true);
    $whatsapp = (string) get_post_meta($post_id, '_nh_shop_consultation_whatsapp', true);
    $product_name = (string) get_post_meta($post_id, '_nh_shop_consultation_product_name', true);
    $product_url = (string) get_post_meta($post_id, '_nh_shop_consultation_product_url', true);
    $page_url = (string) get_post_meta($post_id, '_nh_popup_salon_page_url', true);
    $submitted_at = (string) get_post_meta($post_id, '_nh_popup_salon_submitted_at', true);

    $subject = sprintf('[Nice Hair] Shop consultation - %s', $name);

    $rows = [
        ['Name', $name],
        ['Phone', $phone],
        ['WhatsApp', $whatsapp],
        ['Product', $product_name],
        [
            'Product URL',
            $product_url !== ''
                ? sprintf('<a href="%1$s">%1$s</a>', esc_url($product_url))
                : '',
        ],
        [
            'Page URL',
            $page_url !== ''
                ? sprintf('<a href="%1$s">%1$s</a>', esc_url($page_url))
                : '',
        ],
        ['Submitted at', $submitted_at],
        [
            'Admin link',
            sprintf(
                '<a href="%s">View in dashboard</a>',
                esc_url((string) get_edit_post_link($post_id, ''))
            ),
        ],
    ];

    $body = '<h2 style="margin:0 0 16px;">New Shop consultation request</h2>';
    $body .= '<table cellpadding="8" cellspacing="0" border="1" style="border-collapse:collapse;font-family:Arial,sans-serif;font-size:14px;">';

    foreach ($rows as [$label, $value]) {
        $is_html = in_array($label, ['Product URL', 'Page URL', 'Admin link'], true);
        $body .= sprintf(
            '<tr><td style="background:#f4f4f4;"><strong>%s</strong></td><td>%s</td></tr>',
            esc_html($label),
            $is_html ? $value : esc_html((string) $value)
        );
    }

    $body .= '</table>';

    $sent = wp_mail($to, $subject, $body, [
        'Content-Type: text/html; charset=UTF-8',
    ]);

    if (! $sent) {
        error_log(sprintf('[nh_shop_consultation] wp_mail failed for submission %d', $post_id));
    }

    return (bool) $sent;
}

function nice_hair_shop_consultation_client_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    if (! is_string($ip) || $ip === '') {
        return 'unknown';
    }

    return $ip;
}

function nice_hair_shop_consultation_should_skip_rate_limit(string $ip): bool
{
    if (function_exists('wp_get_environment_type') && wp_get_environment_type() === 'local') {
        return true;
    }

    $normalized_ip = strtolower(trim($ip));

    if (in_array($normalized_ip, ['127.0.0.1', '::1', '::ffff:127.0.0.1'], true)) {
        return true;
    }

    $home_host = strtolower((string) wp_parse_url(home_url('/'), PHP_URL_HOST));

    return in_array($home_host, ['localhost', '127.0.0.1', '::1'], true);
}
