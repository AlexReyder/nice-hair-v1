<?php

declare(strict_types=1);

function nice_hair_register_popup_salon_options(): void
{
    if (! function_exists('acf_add_options_sub_page')) {
        return;
    }

    acf_add_options_sub_page([
        'page_title' => __('Настройки Popup Salon', 'nice-hair'),
        'menu_title' => __('Настройки', 'nice-hair'),
        'menu_slug' => 'popup-salon-settings',
        'parent_slug' => 'edit.php?post_type=nh_popup_salon',
        'capability' => function_exists('nice_hair_admin_access_acf_capability')
            ? nice_hair_admin_access_acf_capability('popup-salon-settings')
            : 'edit_posts',
        'post_id' => 'nh_popup_salon_settings',
    ]);

    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_nh_popup_salon_settings',
        'title' => 'Настройки Popup Salon',
        'fields' => [
            [
                'key' => 'field_nh_popup_salon_admin_email',
                'label' => 'Email для заявок',
                'name' => 'nh_popup_salon_admin_email',
                'type' => 'email',
                'required' => 1,
                'instructions' => 'На этот email будут отправляться новые заявки из Popup Salon.',
            ],
            [
                'key' => 'field_nh_popup_salon_whatsapp',
                'label' => 'WhatsApp салона (только цифры)',
                'name' => 'nh_popup_salon_whatsapp',
                'type' => 'text',
                'instructions' => 'Номер в международном формате без + и пробелов, например 971501234567.',
            ],
            [
                'key' => 'field_nh_popup_salon_success_message',
                'label' => 'Текст после успешной отправки',
                'name' => 'nh_popup_salon_success_message',
                'type' => 'textarea',
                'rows' => 3,
                'new_lines' => 'br',
                'default_value' => "Thanks! Your request has been sent.\nOur stylist will contact you shortly via WhatsApp.",
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'popup-salon-settings',
                ],
            ],
        ],
        'active' => true,
    ]);
}
add_action('acf/init', 'nice_hair_register_popup_salon_options', 30);
