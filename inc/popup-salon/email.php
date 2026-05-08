<?php

declare(strict_types=1);

function nice_hair_popup_salon_send_email(int $post_id): bool
{
    if (! function_exists('get_field')) {
        return false;
    }

    $to = (string) get_field('nh_popup_salon_admin_email', 'nh_popup_salon_settings');

    if ($to === '' || ! is_email($to)) {
        return false;
    }

    $name = (string) get_post_meta($post_id, '_nh_popup_salon_name', true);
    $phone = (string) get_post_meta($post_id, '_nh_popup_salon_phone', true);
    $popup_label = (string) get_post_meta($post_id, '_nh_popup_salon_label', true);
    $page_url = (string) get_post_meta($post_id, '_nh_popup_salon_page_url', true);
    $submitted_at = (string) get_post_meta($post_id, '_nh_popup_salon_submitted_at', true);

    if ($popup_label === '') {
        $popup_label = 'Book an appointment';
    }

    $subject = sprintf('[Nice Hair] Salon booking — %s', $popup_label);

    $rows = [
        ['Popup label', $popup_label],
        ['Name', $name],
        ['Phone / WhatsApp', $phone],
        ['Page URL', $page_url],
        ['Submitted at', $submitted_at],
        [
            'Admin link',
            sprintf(
                '<a href="%s">View in dashboard</a>',
                esc_url((string) get_edit_post_link($post_id, ''))
            ),
        ],
    ];

    $body = '<h2 style="margin:0 0 16px;">New Popup Salon request</h2>';
    $body .= '<table cellpadding="8" cellspacing="0" border="1" style="border-collapse:collapse;font-family:Arial,sans-serif;font-size:14px;">';

    foreach ($rows as [$label, $value]) {
        $is_html = $label === 'Admin link';
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
        error_log(sprintf('[nh_popup_salon] wp_mail failed for submission %d', $post_id));
    }

    return (bool) $sent;
}
