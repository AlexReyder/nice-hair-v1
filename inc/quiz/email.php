<?php

declare(strict_types=1);

/**
 * Assemble and send the admin notification email for a Price Quiz
 * submission. Called from the REST handler after the CPT entry is
 * successfully saved. Failure is non-fatal — the frontend still sees
 * a success response, but the error is logged.
 */

function nice_hair_price_quiz_send_email(int $post_id): bool
{
    if (! function_exists('get_field')) {
        return false;
    }

    $to = (string) get_field('nh_pq_admin_email', 'option');
    if ($to === '' || ! is_email($to)) {
        return false;
    }

    $name      = (string) get_post_meta($post_id, '_nh_pq_name', true);
    $whatsapp  = (string) get_post_meta($post_id, '_nh_pq_whatsapp', true);
    $goal      = get_post_meta($post_id, '_nh_pq_goal', true);
    $length    = (string) get_post_meta($post_id, '_nh_pq_length', true);
    $current   = (string) get_post_meta($post_id, '_nh_pq_current_ext', true);
    $photos_m  = (string) get_post_meta($post_id, '_nh_pq_photos_mode', true);
    $photo_ids = array_filter(array_map(
        'intval',
        explode(',', (string) get_post_meta($post_id, '_nh_pq_photo_ids', true))
    ));

    $goal_str = is_array($goal) ? implode(', ', array_map('strval', $goal)) : '';

    $subject = sprintf('[Nice Hair] New price quiz — %s', $name);

    // Simple HTML table layout. wp_mail handles UTF-8 via the headers below.
    $rows = [
        ['Name',            $name],
        ['WhatsApp',        $whatsapp],
        ['Goal',            $goal_str],
        ['Desired length',  $length],
        ['Current extensions', $current],
        [
            'Photos',
            $photos_m === 'uploaded'
                ? sprintf('%d attached', count($photo_ids))
                : 'Client opted for consultation',
        ],
        [
            'Admin link',
            sprintf(
                '<a href="%s">View in dashboard</a>',
                esc_url((string) get_edit_post_link($post_id, ''))
            ),
        ],
    ];

    $body  = '<h2 style="margin:0 0 16px;">New Price Quiz submission</h2>';
    $body .= '<table cellpadding="8" cellspacing="0" border="1" style="border-collapse:collapse;font-family:Arial,sans-serif;font-size:14px;">';
    foreach ($rows as [$label, $value]) {
        // For the "Admin link" row we intentionally allow HTML, for the
        // rest we escape. Distinguish by key position.
        $is_html = $label === 'Admin link';
        $body   .= sprintf(
            '<tr><td style="background:#f4f4f4;"><strong>%s</strong></td><td>%s</td></tr>',
            esc_html($label),
            $is_html ? $value : esc_html((string) $value)
        );
    }
    $body .= '</table>';

    $headers = [
        'Content-Type: text/html; charset=UTF-8',
    ];

    $attachments = [];
    foreach ($photo_ids as $id) {
        $path = get_attached_file($id);
        if ($path && file_exists($path)) {
            $attachments[] = $path;
        }
    }

    $sent = wp_mail($to, $subject, $body, $headers, $attachments);

    if (! $sent) {
        error_log(sprintf('[nh_price_quiz] wp_mail failed for submission %d', $post_id));
    }

    return (bool) $sent;
}
