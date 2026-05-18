<?php

declare(strict_types=1);

function nice_hair_register_contact_acf_options(): void
{
    if (! function_exists('acf_add_options_page')) {
        return;
    }

    acf_add_options_page([
        'page_title' => 'Контакты',
        'menu_title' => 'Контакты',
        'menu_slug'  => 'contact-settings',
        'capability' => function_exists('nice_hair_admin_access_acf_capability')
            ? nice_hair_admin_access_acf_capability('contact-settings')
            : 'edit_posts',
        'redirect'   => false,
        'icon_url'   => 'dashicons-location-alt',
        'position'   => 31,
        'post_id'    => nice_hair_contact_post_id(),
    ]);

    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key'    => 'group_nh_contacts',
        'title'  => 'Контактная информация',
        'fields' => [
            [
                'key'           => 'field_nh_contact_phone_display',
                'label'         => 'Телефон (отображение)',
                'name'          => 'nh_contact_phone_display',
                'type'          => 'text',
                'default_value' => '+971 58 598 8409',
            ],
            [
                'key'           => 'field_nh_contact_phone_link',
                'label'         => 'Телефон (ссылка)',
                'name'          => 'nh_contact_phone_link',
                'type'          => 'text',
                'default_value' => '+971585988409',
            ],
            [
                'key'           => 'field_nh_contact_address_display',
                'label'         => 'Адрес (отображение)',
                'name'          => 'nh_contact_address_display',
                'type'          => 'textarea',
                'rows'          => 3,
                'new_lines'     => 'br',
                'default_value' => "Al Noor st, Al Sufouh,\nAl Sufouh 1, Dubai",
            ],
            [
                'key'           => 'field_nh_contact_address_plain',
                'label'         => 'Адрес (обычный текст)',
                'name'          => 'nh_contact_address_plain',
                'type'          => 'text',
                'default_value' => 'Al Noor st, Al Sufouh, Al Sufouh 1, Dubai',
            ],
            [
                'key'           => 'field_nh_contact_working_hours',
                'label'         => 'Часы работы',
                'name'          => 'nh_contact_working_hours',
                'type'          => 'text',
                'default_value' => "We're open daily: 10 AM - 10 PM",
            ],
            [
                'key'   => 'field_nh_contact_whatsapp_url',
                'label' => 'WhatsApp URL',
                'name'  => 'nh_contact_whatsapp_url',
                'type'  => 'url',
            ],
            [
                'key'   => 'field_nh_contact_instagram_url',
                'label' => 'Instagram URL',
                'name'  => 'nh_contact_instagram_url',
                'type'  => 'url',
            ],
            [
                'key'   => 'field_nh_contact_telegram_url',
                'label' => 'Telegram URL',
                'name'  => 'nh_contact_telegram_url',
                'type'  => 'url',
            ],
            [
                'key'   => 'field_nh_contact_map_url',
                'label' => 'Ссылка на карту',
                'name'  => 'nh_contact_map_url',
                'type'  => 'url',
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => 'contact-settings',
                ],
            ],
        ],
        'active' => true,
    ]);
}
add_action('acf/init', 'nice_hair_register_contact_acf_options', 15);


