<?php

declare(strict_types=1);

/**
 * REST endpoint for blog email subscriptions.
 *
 * `POST /wp-json/nice-hair/v1/subscribe` accepts JSON:
 *   - email     valid email address
 *   - hp_field  honeypot — must be empty
 *
 * Security: nonce, honeypot, rate limit (10/hour/IP), duplicate check.
 */

const NH_SUBSCRIBE_RATE_LIMIT  = 10;
const NH_SUBSCRIBE_RATE_WINDOW = HOUR_IN_SECONDS;

function nice_hair_subscribe_register_rest_route(): void
{
    register_rest_route('nice-hair/v1', '/subscribe', [
        'methods'             => 'POST',
        'callback'            => 'nice_hair_subscribe_handle',
        'permission_callback' => '__return_true',
    ]);
}
add_action('rest_api_init', 'nice_hair_subscribe_register_rest_route');

function nice_hair_subscribe_handle(WP_REST_Request $request): WP_REST_Response
{
    // 1. Nonce.
    $nonce = (string) $request->get_header('X-WP-Nonce');
    if (! wp_verify_nonce($nonce, 'wp_rest')) {
        return new WP_REST_Response(['success' => false, 'error' => 'invalid_nonce'], 403);
    }

    // 2. Honeypot.
    $hp = (string) $request->get_param('hp_field');
    if ($hp !== '') {
        return new WP_REST_Response(['success' => true], 200);
    }

    // 3. Rate limit.
    $ip       = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $rate_key = 'nh_sub_rate_' . md5((string) $ip);
    $rate_cur = (int) get_transient($rate_key);

    if ($rate_cur >= NH_SUBSCRIBE_RATE_LIMIT) {
        return new WP_REST_Response(['success' => false, 'error' => 'rate_limited'], 429);
    }

    // 4. Validate email.
    $email = sanitize_email((string) $request->get_param('email'));
    if (! is_email($email)) {
        return new WP_REST_Response(['success' => false, 'error' => 'invalid_email'], 400);
    }

    $allowed_sources = ['blog', 'footer_salon', 'footer_shop'];
    $source = sanitize_key((string) $request->get_param('source'));

    if (! in_array($source, $allowed_sources, true)) {
        $source = 'blog';
    }

    // 5. Duplicate check.
    $existing = get_posts([
        'post_type'  => 'nh_subscriber',
        'meta_key'   => '_nh_sub_email',
        'meta_value' => $email,
        'numberposts' => 1,
        'fields'      => 'ids',
    ]);

    if (! empty($existing)) {
        return new WP_REST_Response([
            'success' => true,
            'message' => 'You are already subscribed!',
        ], 200);
    }

    // 6. Create entry.
    $post_id = wp_insert_post([
        'post_type'   => 'nh_subscriber',
        'post_status' => 'publish',
        'post_title'  => $email,
        'post_author' => 0,
    ], true);

    if (is_wp_error($post_id)) {
        return new WP_REST_Response(['success' => false, 'error' => 'insert_failed'], 500);
    }

    update_post_meta($post_id, '_nh_sub_email', $email);
    update_post_meta($post_id, '_nh_sub_ip', (string) $ip);
    update_post_meta($post_id, '_nh_sub_date', current_time('mysql'));
    update_post_meta($post_id, '_nh_sub_source', $source);

    // 7. Rate limit increment.
    set_transient($rate_key, $rate_cur + 1, NH_SUBSCRIBE_RATE_WINDOW);

    return new WP_REST_Response([
        'success' => true,
        'message' => 'Thank you for subscribing!',
    ], 200);
}
