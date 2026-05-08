<?php

declare(strict_types=1);

const NH_POPUP_SALON_REST_NAMESPACE = 'nice-hair/v1';
const NH_POPUP_SALON_REST_ROUTE = '/popup-salon';
const NH_POPUP_SALON_RATE_LIMIT = 5;
const NH_POPUP_SALON_RATE_WINDOW = HOUR_IN_SECONDS;

function nice_hair_popup_salon_register_rest_route(): void
{
    register_rest_route(NH_POPUP_SALON_REST_NAMESPACE, NH_POPUP_SALON_REST_ROUTE, [
        'methods' => 'POST',
        'callback' => 'nice_hair_popup_salon_handle_submission',
        'permission_callback' => '__return_true',
    ]);
}
add_action('rest_api_init', 'nice_hair_popup_salon_register_rest_route');

function nice_hair_popup_salon_handle_submission(WP_REST_Request $request): WP_REST_Response
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

    $ip = nice_hair_popup_salon_client_ip();
    $skip_rate_limit = nice_hair_popup_salon_should_skip_rate_limit($ip);
    $rate_key = 'nh_popup_salon_rate_' . md5($ip);
    $rate_current = $skip_rate_limit ? 0 : (int) get_transient($rate_key);

    if (! $skip_rate_limit && $rate_current >= NH_POPUP_SALON_RATE_LIMIT) {
        return new WP_REST_Response([
            'success' => false,
            'error' => 'rate_limited',
        ], 429);
    }

    $name = sanitize_text_field((string) $request->get_param('name'));
    $name = mb_substr($name, 0, 100);

    if ($name === '') {
        return nice_hair_popup_salon_validation_error('name');
    }

    $phone_raw = (string) $request->get_param('phone');
    $phone = preg_replace('/\D+/', '', $phone_raw) ?? '';

    if (strlen($phone) < 7 || strlen($phone) > 15) {
        return nice_hair_popup_salon_validation_error('phone');
    }

    $popup_label = sanitize_text_field((string) $request->get_param('popup_label'));

    if ($popup_label === '') {
        $popup_label = 'Book an appointment';
    }

    $page_url = esc_url_raw((string) $request->get_param('page_url'));
    $submitted_at = current_time('mysql');

    $post_id = wp_insert_post([
        'post_type' => 'nh_salon_request',
        'post_status' => 'publish',
        'post_title' => sprintf('%s - %s', $name, wp_date('d M Y H:i')),
        'post_author' => 0,
    ], true);

    if (is_wp_error($post_id) || ! $post_id) {
        error_log(sprintf(
            '[nh_popup_salon] Request insert failed: %s',
            is_wp_error($post_id) ? $post_id->get_error_message() : 'unknown error'
        ));

        return new WP_REST_Response([
            'success' => false,
            'error' => 'insert_failed',
        ], 500);
    }

    update_post_meta($post_id, '_nh_popup_salon_name', $name);
    update_post_meta($post_id, '_nh_popup_salon_phone', $phone);
    update_post_meta($post_id, '_nh_popup_salon_label', $popup_label);
    update_post_meta($post_id, '_nh_popup_salon_page_url', $page_url);
    update_post_meta($post_id, '_nh_popup_salon_submitted_at', $submitted_at);
    update_post_meta($post_id, '_nh_popup_salon_user_ip', $ip);

    if (! $skip_rate_limit) {
        set_transient($rate_key, $rate_current + 1, NH_POPUP_SALON_RATE_WINDOW);
    }

    if (function_exists('nice_hair_popup_salon_send_email')) {
        nice_hair_popup_salon_send_email((int) $post_id);
    }

    $success_message = '';
    $whatsapp = '';

    if (function_exists('get_field')) {
        $success_message = (string) get_field('nh_popup_salon_success_message', 'nh_popup_salon_settings');
        $whatsapp = (string) get_field('nh_popup_salon_whatsapp', 'nh_popup_salon_settings');
    }

    if ($success_message === '') {
        $success_message = "Thanks! Your request has been sent.\nOur stylist will contact you shortly via WhatsApp.";
    }

    return new WP_REST_Response([
        'success' => true,
        'message' => $success_message,
        'whatsapp' => $whatsapp,
    ], 200);
}

function nice_hair_popup_salon_validation_error(string $field): WP_REST_Response
{
    return new WP_REST_Response([
        'success' => false,
        'error' => 'validation',
        'field' => $field,
    ], 400);
}

function nice_hair_popup_salon_client_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    if (! is_string($ip) || $ip === '') {
        return 'unknown';
    }

    return $ip;
}

function nice_hair_popup_salon_should_skip_rate_limit(string $ip): bool
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
