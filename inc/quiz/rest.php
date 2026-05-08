<?php

declare(strict_types=1);

/**
 * REST endpoint for Price Quiz submissions.
 *
 * `POST /wp-json/nice-hair/v1/price-quiz` accepts multipart/form-data:
 *   - goal         JSON-encoded array of goal slugs
 *   - length       one of allowed length slugs
 *   - current_ext  "yes" or "no"
 *   - photos_mode  "uploaded" or "skipped"
 *   - name         client name
 *   - whatsapp     client phone (digits + separators, at least 7 digits)
 *   - hp_field     honeypot — must be empty
 *   - photos[0..N] file uploads (1-3 files, images only, ≤ 10 MB each)
 *
 * Security layers:
 *   1. Nonce check via `X-WP-Nonce` header (`wp_rest` action)
 *   2. Honeypot — non-empty `hp_field` → silent 200 without save
 *   3. Rate limit — 5 submissions per IP per hour via transients
 *   4. Strict whitelist validation of all answer values
 *   5. File validation (count, size, mime, server-side ext check)
 *
 * On success → creates a `nh_price_quiz` post, saves meta, imports files
 * as child attachments, sends notification email, returns thank-you JSON.
 */

const NH_PRICE_QUIZ_REST_NAMESPACE = 'nice-hair/v1';
const NH_PRICE_QUIZ_REST_ROUTE     = '/price-quiz';

const NH_PRICE_QUIZ_MAX_FILES     = 3;
const NH_PRICE_QUIZ_MAX_FILE_SIZE = 10 * 1024 * 1024; // 10 MB
const NH_PRICE_QUIZ_RATE_LIMIT    = 5;                // per hour per IP
const NH_PRICE_QUIZ_RATE_WINDOW   = HOUR_IN_SECONDS;

const NH_PRICE_QUIZ_ALLOWED_GOALS = [
    'add_length',
    'add_volume',
    'fill_sparse',
    'refresh_previous',
    'not_sure',
];

const NH_PRICE_QUIZ_ALLOWED_LENGTHS = [
    'shoulder',
    'below_shoulders',
    'waist',
    'longer_than_waist',
];

const NH_PRICE_QUIZ_ALLOWED_MIMES = [
    'image/jpeg',
    'image/png',
    'image/webp',
];

/**
 * Register the REST route.
 */
function nice_hair_price_quiz_register_rest_route(): void
{
    register_rest_route(NH_PRICE_QUIZ_REST_NAMESPACE, NH_PRICE_QUIZ_REST_ROUTE, [
        'methods'             => 'POST',
        'callback'            => 'nice_hair_price_quiz_handle_submission',
        // Public endpoint — security is enforced inside the handler via
        // nonce, honeypot and rate limit. Do NOT use __return_true blindly
        // in production: this handler explicitly validates X-WP-Nonce.
        'permission_callback' => '__return_true',
    ]);
}
add_action('rest_api_init', 'nice_hair_price_quiz_register_rest_route');

/**
 * Main handler.
 */
function nice_hair_price_quiz_handle_submission(WP_REST_Request $request): WP_REST_Response
{
    // 1. Nonce check.
    $nonce = (string) $request->get_header('X-WP-Nonce');
    if (! wp_verify_nonce($nonce, 'wp_rest')) {
        return new WP_REST_Response(
            ['success' => false, 'error' => 'invalid_nonce'],
            403
        );
    }

    // 2. Honeypot. If bot filled the hidden field, silently "succeed"
    //    without storing anything — confuses dumb crawlers.
    $honeypot = (string) $request->get_param('hp_field');
    if ($honeypot !== '') {
        return new WP_REST_Response(['success' => true], 200);
    }

    // 3. Rate limit (per IP).
    $ip           = nice_hair_price_quiz_client_ip();
    $skip_rate_limit = nice_hair_price_quiz_should_skip_rate_limit($ip);
    $rate_key     = 'nh_pq_rate_' . md5($ip);
    $rate_current = $skip_rate_limit ? 0 : (int) get_transient($rate_key);

    if (! $skip_rate_limit && $rate_current >= NH_PRICE_QUIZ_RATE_LIMIT) {
        return new WP_REST_Response(
            ['success' => false, 'error' => 'rate_limited'],
            429
        );
    }

    // 4. Parse and validate answer fields.
    $goal_raw = (string) $request->get_param('goal');
    $goal     = json_decode($goal_raw, true);
    if (! is_array($goal)) {
        $goal = [];
    }
    $goal = array_values(array_intersect(NH_PRICE_QUIZ_ALLOWED_GOALS, array_map('strval', $goal)));

    if (count($goal) === 0) {
        return nice_hair_price_quiz_validation_error('goal');
    }

    $length = (string) $request->get_param('length');
    if (! in_array($length, NH_PRICE_QUIZ_ALLOWED_LENGTHS, true)) {
        return nice_hair_price_quiz_validation_error('length');
    }

    $current_ext = (string) $request->get_param('current_ext');
    if (! in_array($current_ext, ['yes', 'no'], true)) {
        return nice_hair_price_quiz_validation_error('current_ext');
    }

    $photos_mode = (string) $request->get_param('photos_mode');
    if (! in_array($photos_mode, ['uploaded', 'skipped'], true)) {
        return nice_hair_price_quiz_validation_error('photos_mode');
    }

    $name = sanitize_text_field((string) $request->get_param('name'));
    if (function_exists('mb_substr')) {
        $name = mb_substr($name, 0, 100);
    } else {
        $name = substr($name, 0, 100);
    }
    if ($name === '') {
        return nice_hair_price_quiz_validation_error('name');
    }

    $whatsapp_raw = (string) $request->get_param('whatsapp');
    $whatsapp     = preg_replace('/\D+/', '', $whatsapp_raw) ?? '';
    if (strlen($whatsapp) < 7 || strlen($whatsapp) > 15) {
        return nice_hair_price_quiz_validation_error('whatsapp');
    }

    // 5. Pre-validate uploaded files before creating the CPT post.
    $files_struct = [];
    if ($photos_mode === 'uploaded') {
        $files_struct = nice_hair_price_quiz_extract_files($request);
        if (empty($files_struct)) {
            return nice_hair_price_quiz_validation_error('photos');
        }
        $file_error = nice_hair_price_quiz_validate_files($files_struct);
        if ($file_error !== null) {
            return new WP_REST_Response(
                ['success' => false, 'error' => 'file_invalid', 'detail' => $file_error],
                400
            );
        }
    }

    // 6. Create the CPT entry. Use wp_insert_post directly — the
    //    `create_posts => do_not_allow` capability only blocks the admin
    //    UI "Add New" button, not programmatic insertions. We also pass
    //    `post_author => 0` (system) so the entry isn't tied to a user.
    $post_id = wp_insert_post([
        'post_type'   => 'nh_price_quiz',
        'post_status' => 'publish',
        'post_title'  => sprintf('%s — %s', $name, wp_date('d M Y H:i')),
        'post_author' => 0,
    ], true);

    if (is_wp_error($post_id) || ! $post_id) {
        return new WP_REST_Response(
            ['success' => false, 'error' => 'insert_failed'],
            500
        );
    }

    // 7. Handle file uploads (create attachments as children of the CPT).
    $photo_ids = [];
    if ($photos_mode === 'uploaded' && ! empty($files_struct)) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        foreach ($files_struct as $index => $file) {
            // media_handle_sideload needs a single-file shape.
            $sideload = [
                'name'     => $file['name'],
                'type'     => $file['type'],
                'tmp_name' => $file['tmp_name'],
                'error'    => $file['error'],
                'size'     => $file['size'],
            ];

            // Temporarily shoehorn into $_FILES for media_handle_sideload.
            $_FILES['nh_pq_photo_' . $index] = $sideload;

            $attachment_id = media_handle_sideload($sideload, $post_id);

            unset($_FILES['nh_pq_photo_' . $index]);

            if (is_wp_error($attachment_id)) {
                // Roll back: delete post + any already-uploaded photos.
                foreach ($photo_ids as $id) {
                    wp_delete_attachment($id, true);
                }
                wp_delete_post($post_id, true);

                return new WP_REST_Response([
                    'success' => false,
                    'error'   => 'upload_failed',
                    'detail'  => $attachment_id->get_error_message(),
                ], 500);
            }

            $photo_ids[] = (int) $attachment_id;
        }
    }

    // 8. Save meta.
    update_post_meta($post_id, '_nh_pq_goal',         $goal);
    update_post_meta($post_id, '_nh_pq_length',       $length);
    update_post_meta($post_id, '_nh_pq_current_ext',  $current_ext);
    update_post_meta($post_id, '_nh_pq_photos_mode',  $photos_mode);
    update_post_meta($post_id, '_nh_pq_photo_ids',    implode(',', $photo_ids));
    update_post_meta($post_id, '_nh_pq_name',         $name);
    update_post_meta($post_id, '_nh_pq_whatsapp',     $whatsapp);
    update_post_meta($post_id, '_nh_pq_submitted_at', current_time('mysql'));
    update_post_meta($post_id, '_nh_pq_user_ip',      $ip);

    // 9. Increment rate-limit counter.
    if (! $skip_rate_limit) {
        set_transient($rate_key, $rate_current + 1, NH_PRICE_QUIZ_RATE_WINDOW);
    }

    // 10. Send admin notification (non-blocking — failure logged, not fatal).
    if (function_exists('nice_hair_price_quiz_send_email')) {
        nice_hair_price_quiz_send_email($post_id);
    }

    // 11. Build success response. Uses ACF options values for message +
    //     WhatsApp link so admin can customise both in Price Quiz settings.
    $success_message = '';
    $whatsapp_option = '';
    if (function_exists('get_field')) {
        $success_message = (string) get_field('nh_pq_success_message', 'option');
        $whatsapp_option = (string) get_field('nh_pq_whatsapp', 'option');
    }
    if ($success_message === '') {
        $success_message = "Thanks! We've got your answers.\nOur stylist will reach out via WhatsApp shortly to confirm your price.";
    }

    return new WP_REST_Response([
        'success'  => true,
        'message'  => $success_message,
        'whatsapp' => $whatsapp_option,
    ], 200);
}

/**
 * Build a short 400 response for a failed field validation.
 */
function nice_hair_price_quiz_validation_error(string $field): WP_REST_Response
{
    return new WP_REST_Response(
        ['success' => false, 'error' => 'validation', 'field' => $field],
        400
    );
}

/**
 * Extract uploaded photo files from the REST request.
 *
 * `FormData.append('photos[0]', file)` on the JS side makes PHP populate
 * `$_FILES['photos']` with array-per-attribute shape:
 *   $_FILES['photos']['name']     = ['f1.jpg', 'f2.jpg', ...]
 *   $_FILES['photos']['tmp_name'] = ['/tmp/phpA', '/tmp/phpB', ...]
 *   etc.
 *
 * We also support individual keys like $_FILES['photos[0]'] that some
 * SAPIs produce. Returns a flat list of single-file arrays suitable for
 * media_handle_sideload.
 */
function nice_hair_price_quiz_extract_files(WP_REST_Request $request): array
{
    $files = $request->get_file_params();
    $out   = [];

    if (isset($files['photos']) && is_array($files['photos']['name'] ?? null)) {
        $count = count($files['photos']['name']);
        for ($i = 0; $i < $count; $i++) {
            $name = $files['photos']['name'][$i] ?? '';
            if ($name === '') {
                continue;
            }
            $out[] = [
                'name'     => $name,
                'type'     => $files['photos']['type'][$i]     ?? '',
                'tmp_name' => $files['photos']['tmp_name'][$i] ?? '',
                'error'    => (int) ($files['photos']['error'][$i] ?? UPLOAD_ERR_NO_FILE),
                'size'     => (int) ($files['photos']['size'][$i]  ?? 0),
            ];
        }
    } else {
        // Fallback: photos[0], photos[1], ... as separate top-level keys.
        foreach ($files as $key => $file) {
            if (strpos($key, 'photos[') !== 0 || ! is_array($file)) {
                continue;
            }
            $out[] = [
                'name'     => $file['name']     ?? '',
                'type'     => $file['type']     ?? '',
                'tmp_name' => $file['tmp_name'] ?? '',
                'error'    => (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE),
                'size'     => (int) ($file['size']  ?? 0),
            ];
        }
    }

    return $out;
}

/**
 * Validate uploaded files: count ≤ 3, size ≤ 10 MB each, mime allowlist,
 * extension matches content. Returns null on success or an error string.
 */
function nice_hair_price_quiz_validate_files(array $files): ?string
{
    if (count($files) === 0) {
        return 'No files uploaded';
    }
    if (count($files) > NH_PRICE_QUIZ_MAX_FILES) {
        return sprintf('Too many files (max %d)', NH_PRICE_QUIZ_MAX_FILES);
    }

    foreach ($files as $file) {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return sprintf('Upload error for %s', $file['name'] ?? '?');
        }
        if (($file['size'] ?? 0) > NH_PRICE_QUIZ_MAX_FILE_SIZE) {
            return sprintf('%s is larger than 10 MB', $file['name'] ?? '?');
        }

        // Trust only the real file type, not the client-sent MIME.
        $check = wp_check_filetype_and_ext(
            $file['tmp_name'],
            $file['name'],
            [
                'jpg|jpeg' => 'image/jpeg',
                'png'      => 'image/png',
                'webp'     => 'image/webp',
            ]
        );

        $real_type = $check['type'] ?? '';
        if (! in_array($real_type, NH_PRICE_QUIZ_ALLOWED_MIMES, true)) {
            return sprintf('%s has an unsupported file type', $file['name'] ?? '?');
        }
    }

    return null;
}

/**
 * Best-effort client IP for rate limiting. Doesn't trust X-Forwarded-For
 * because this is a local project on XAMPP — can be extended later if
 * deployed behind a CDN / reverse proxy.
 */
function nice_hair_price_quiz_client_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if (! is_string($ip) || $ip === '') {
        return 'unknown';
    }
    return $ip;
}

function nice_hair_price_quiz_should_skip_rate_limit(string $ip): bool
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
