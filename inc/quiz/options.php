<?php

declare(strict_types=1);

/**
 * ACF Options page for quiz block settings.
 *
 * Adds a dedicated top-level admin menu "Квиз блок" where the salon
 * manager configures notification email, WhatsApp number for the success
 * state link, and the thank-you message shown after a successful
 * submission.
 *
 * Privacy policy URL is intentionally NOT duplicated here — the block
 * reads `nh_footer_privacy_url` from the existing Footer Settings page.
 */

function nice_hair_register_price_quiz_options(): void
{
    if (! function_exists('acf_add_options_page')) {
        return;
    }

    acf_add_options_page([
        'page_title' => __('Настройки блока квиз', 'nice-hair'),
        'menu_title' => __('Квиз блок', 'nice-hair'),
        'menu_slug'  => 'price-quiz-settings',
        'capability' => 'edit_posts',
        'redirect'   => false,
        'icon_url'   => 'dashicons-forms',
        'position'   => 31,
    ]);

    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key'    => 'group_nh_pq_settings',
        'title'  => 'Настройки блока квиз',
        'fields' => [
            [
                'key'          => 'field_nh_pq_admin_email',
                'label'        => 'Email для уведомлений',
                'name'         => 'nh_pq_admin_email',
                'type'         => 'email',
                'instructions' => 'Куда отправлять уведомления о новых заявках.',
                'required'     => 1,
            ],
            [
                'key'          => 'field_nh_pq_whatsapp',
                'label'        => 'WhatsApp салона (только цифры)',
                'name'         => 'nh_pq_whatsapp',
                'type'         => 'text',
                'instructions' => 'Телефон в международном формате без + и пробелов, например 971501234567. Используется для ссылки wa.me в success-state.',
            ],
            [
                'key'           => 'field_nh_pq_success_message',
                'label'         => 'Текст успешной отправки',
                'name'          => 'nh_pq_success_message',
                'type'          => 'textarea',
                'rows'          => 3,
                'new_lines'     => 'br',
                'default_value' => "Thanks! We've got your answers.\nOur stylist will reach out via WhatsApp shortly to confirm your price.",
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => 'price-quiz-settings',
                ],
            ],
        ],
        'active' => true,
    ]);
}
add_action('acf/init', 'nice_hair_register_price_quiz_options');
