<?php

declare(strict_types=1);

function nice_hair_register_acf_options(): void
{
    if (! function_exists('acf_add_options_page')) {
        return;
    }

    acf_add_options_page([
        'page_title' => __('Шапка и подвал', 'nice-hair'),
        'menu_title' => __('Шапка и подвал', 'nice-hair'),
        'menu_slug'  => 'header-footer-settings',
        'capability' => 'edit_posts',
        'redirect'   => true,
        'icon_url'   => 'dashicons-screenoptions',
        'position'   => 30,
    ]);

    acf_add_options_sub_page([
        'page_title'  => __('Шапка и подвал главной страницы', 'nice-hair'),
        'menu_title'  => __('Главная', 'nice-hair'),
        'menu_slug'   => 'header-footer-default',
        'parent_slug' => 'header-footer-settings',
        'capability'  => 'edit_posts',
        'post_id'     => nice_hair_header_footer_post_id('home'),
    ]);

    acf_add_options_sub_page([
        'page_title'  => __('Шапка и подвал Salon', 'nice-hair'),
        'menu_title'  => __('Salon', 'nice-hair'),
        'menu_slug'   => 'header-footer-salon',
        'parent_slug' => 'header-footer-settings',
        'capability'  => 'edit_posts',
        'post_id'     => nice_hair_header_footer_post_id('salon'),
    ]);

    acf_add_options_page([
        'page_title' => __('Настройки результатов', 'nice-hair'),
        'menu_title' => __('Результаты', 'nice-hair'),
        'menu_slug'  => 'results-settings',
        'capability' => 'edit_posts',
        'redirect'   => false,
        'icon_url'   => 'dashicons-images-alt2',
        'position'   => 32,
    ]);

    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key'    => 'group_nh_default_header',
        'title'  => 'Шапка главной страницы',
        'fields' => [
            [
                'key'           => 'field_nh_header_logo',
                'label'         => 'Логотип',
                'name'          => 'nh_header_logo',
                'type'          => 'image',
                'return_format' => 'url',
                'preview_size'  => 'medium',
            ],
            [
                'key'           => 'field_nh_header_logo_alt',
                'label'         => 'Alt-текст логотипа',
                'name'          => 'nh_header_logo_alt',
                'type'          => 'text',
                'default_value' => 'Nice Hair',
            ],
            [
                'key'          => 'field_nh_header_nav_items',
                'label'        => 'Основная навигация',
                'name'         => 'nh_header_nav_items',
                'type'         => 'repeater',
                'layout'       => 'block',
                'min'          => 1,
                'button_label' => 'Добавить пункт навигации',
                'sub_fields'   => [
                    [
                        'key'           => 'field_nh_header_nav_item_link',
                        'label'         => 'Ссылка',
                        'name'          => 'item_link',
                        'type'          => 'link',
                        'return_format' => 'array',
                        'required'      => 1,
                    ],
                ],
            ],
            [
                'key'           => 'field_nh_header_cta_primary',
                'label'         => 'Быстрая ссылка 1',
                'name'          => 'nh_header_cta_primary',
                'type'          => 'link',
                'return_format' => 'array',
            ],
            [
                'key'           => 'field_nh_header_cta_secondary',
                'label'         => 'Быстрая ссылка 2',
                'name'          => 'nh_header_cta_secondary',
                'type'          => 'link',
                'return_format' => 'array',
            ],
            [
                'key'           => 'field_nh_header_mobile_phone',
                'label'         => 'Телефон в мобильном меню (отображение)',
                'name'          => 'nh_header_mobile_phone',
                'type'          => 'text',
                'default_value' => '+971 58 598 8409',
            ],
            [
                'key'           => 'field_nh_header_mobile_phone_link',
                'label'         => 'Телефон в мобильном меню (ссылка)',
                'name'          => 'nh_header_mobile_phone_link',
                'type'          => 'text',
                'default_value' => '+971585988409',
            ],
            [
                'key'           => 'field_nh_header_mobile_address',
                'label'         => 'Адрес в мобильном меню',
                'name'          => 'nh_header_mobile_address',
                'type'          => 'textarea',
                'rows'          => 3,
                'new_lines'     => 'br',
                'default_value' => "Al Noor st, Al Sufouh,\nAl Sufouh 1, Dubai",
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => 'header-footer-default',
                ],
            ],
        ],
        'active' => true,
    ]);

    acf_add_local_field_group([
        'key'    => 'group_nh_footer',
        'title'  => 'Подвал главной страницы',
        'fields' => [
            [
                'key'           => 'field_nh_footer_logo',
                'label'         => 'Логотип',
                'name'          => 'nh_footer_logo',
                'type'          => 'image',
                'return_format' => 'url',
                'preview_size'  => 'medium',
            ],
            [
                'key'           => 'field_nh_footer_logo_alt',
                'label'         => 'Alt-текст логотипа',
                'name'          => 'nh_footer_logo_alt',
                'type'          => 'text',
                'default_value' => 'Nice Hair',
            ],
            [
                'key'           => 'field_nh_footer_description',
                'label'         => 'Описание бренда',
                'name'          => 'nh_footer_description',
                'type'          => 'text',
                'default_value' => '2026 / Premium Hair Extensions in the Heart of Dubai',
            ],
            [
                'key'          => 'field_nh_footer_nav_primary',
                'label'        => 'Навигация, колонка 1',
                'name'         => 'nh_footer_nav_primary',
                'type'         => 'repeater',
                'layout'       => 'block',
                'button_label' => 'Добавить пункт навигации',
                'sub_fields'   => [
                    [
                        'key'           => 'field_nh_footer_nav_primary_link',
                        'label'         => 'Ссылка',
                        'name'          => 'item_link',
                        'type'          => 'link',
                        'return_format' => 'array',
                        'required'      => 1,
                    ],
                ],
            ],
            [
                'key'          => 'field_nh_footer_nav_secondary',
                'label'        => 'Навигация, колонка 2',
                'name'         => 'nh_footer_nav_secondary',
                'type'         => 'repeater',
                'layout'       => 'block',
                'button_label' => 'Добавить пункт навигации',
                'sub_fields'   => [
                    [
                        'key'           => 'field_nh_footer_nav_secondary_link',
                        'label'         => 'Ссылка',
                        'name'          => 'item_link',
                        'type'          => 'link',
                        'return_format' => 'array',
                        'required'      => 1,
                    ],
                ],
            ],
            [
                'key'           => 'field_nh_footer_address',
                'label'         => 'Адрес',
                'name'          => 'nh_footer_address',
                'type'          => 'text',
                'default_value' => '[Al Noor st, Al Sufouh, Al Sufouh 1, Dubai]',
            ],
            [
                'key'           => 'field_nh_footer_phone',
                'label'         => 'Телефон (отображение)',
                'name'          => 'nh_footer_phone',
                'type'          => 'text',
                'default_value' => '+971 58 598 8409',
            ],
            [
                'key'           => 'field_nh_footer_phone_link',
                'label'         => 'Телефон (ссылка)',
                'name'          => 'nh_footer_phone_link',
                'type'          => 'text',
                'default_value' => '+971585988409',
            ],
            [
                'key'           => 'field_nh_footer_hours',
                'label'         => 'Время работы',
                'name'          => 'nh_footer_hours',
                'type'          => 'text',
                'default_value' => "We're open daily: 10 AM - 10 PM",
            ],
            [
                'key'        => 'field_nh_footer_socials',
                'label'      => 'Соцсети',
                'name'       => 'nh_footer_socials',
                'type'       => 'group',
                'layout'     => 'row',
                'sub_fields' => [
                    [
                        'key'   => 'field_nh_footer_telegram',
                        'label' => 'Телеграм',
                        'name'  => 'telegram',
                        'type'  => 'url',
                    ],
                    [
                        'key'   => 'field_nh_footer_instagram',
                        'label' => 'Инстаграм',
                        'name'  => 'instagram',
                        'type'  => 'url',
                    ],
                    [
                        'key'   => 'field_nh_footer_whatsapp',
                        'label' => 'WhatsApp',
                        'name'  => 'whatsapp',
                        'type'  => 'url',
                    ],
                ],
            ],
            [
                'key'        => 'field_nh_footer_cta',
                'label'      => 'CTA-кнопки',
                'name'       => 'nh_footer_cta',
                'type'       => 'group',
                'layout'     => 'block',
                'sub_fields' => [
                    [
                        'key'           => 'field_nh_footer_cta1_label',
                        'label'         => 'Текст кнопки 1',
                        'name'          => 'btn1_label',
                        'type'          => 'text',
                        'default_value' => 'VISIT SALON',
                    ],
                    [
                        'key'   => 'field_nh_footer_cta1_url',
                        'label' => 'Ссылка кнопки 1',
                        'name'  => 'btn1_url',
                        'type'  => 'url',
                    ],
                    [
                        'key'           => 'field_nh_footer_cta2_label',
                        'label'         => 'Текст кнопки 2',
                        'name'          => 'btn2_label',
                        'type'          => 'text',
                        'default_value' => 'VISIT SHOP',
                    ],
                    [
                        'key'   => 'field_nh_footer_cta2_url',
                        'label' => 'Ссылка кнопки 2',
                        'name'  => 'btn2_url',
                        'type'  => 'url',
                    ],
                ],
            ],
            [
                'key'   => 'field_nh_footer_privacy_url',
                'label' => 'Ссылка на Privacy Policy',
                'name'  => 'nh_footer_privacy_url',
                'type'  => 'url',
            ],
            [
                'key'        => 'field_nh_footer_credits',
                'label'      => 'Кредиты',
                'name'       => 'nh_footer_credits',
                'type'       => 'group',
                'layout'     => 'block',
                'sub_fields' => [
                    [
                        'key'           => 'field_nh_footer_designer_name',
                        'label'         => 'Имя дизайнера',
                        'name'          => 'designer_name',
                        'type'          => 'text',
                        'default_value' => 'umapalata.space',
                    ],
                    [
                        'key'   => 'field_nh_footer_designer_url',
                        'label' => 'Ссылка дизайнера',
                        'name'  => 'designer_url',
                        'type'  => 'url',
                    ],
                    [
                        'key'           => 'field_nh_footer_developer_name',
                        'label'         => 'Имя разработчика',
                        'name'          => 'developer_name',
                        'type'          => 'text',
                        'default_value' => 'username',
                    ],
                    [
                        'key'   => 'field_nh_footer_developer_url',
                        'label' => 'Ссылка разработчика',
                        'name'  => 'developer_url',
                        'type'  => 'url',
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => 'header-footer-default',
                ],
            ],
        ],
        'active' => true,
    ]);

    acf_add_local_field_group([
        'key'    => 'group_nh_salon_header',
        'title'  => 'Шапка Salon',
        'fields' => [
            [
                'key'           => 'field_nh_salon_header_logo',
                'label'         => 'Логотип',
                'name'          => 'nh_header_logo',
                'type'          => 'image',
                'return_format' => 'url',
                'preview_size'  => 'medium',
            ],
            [
                'key'           => 'field_nh_salon_header_logo_alt',
                'label'         => 'Alt-текст логотипа',
                'name'          => 'nh_header_logo_alt',
                'type'          => 'text',
                'default_value' => 'Nice Hair',
            ],
            [
                'key'          => 'field_nh_salon_header_nav_items',
                'label'        => 'Пункты меню',
                'name'         => 'nh_header_nav_items',
                'type'         => 'repeater',
                'layout'       => 'block',
                'min'          => 1,
                'button_label' => 'Добавить пункт меню',
                'sub_fields'   => [
                    [
                        'key'           => 'field_nh_salon_header_nav_item_link',
                        'label'         => 'Ссылка',
                        'name'          => 'item_link',
                        'type'          => 'link',
                        'return_format' => 'array',
                        'required'      => 1,
                    ],
                ],
            ],
            [
                'key'           => 'field_nh_salon_header_contact_phone',
                'label'         => 'Телефон',
                'name'          => 'nh_header_contact_phone',
                'type'          => 'text',
                'default_value' => '+971 58 598 8409',
            ],
            [
                'key'           => 'field_nh_salon_header_contact_phone_link',
                'label'         => 'Телефон (ссылка)',
                'name'          => 'nh_header_contact_phone_link',
                'type'          => 'text',
                'default_value' => '+971585988409',
            ],
            [
                'key'           => 'field_nh_salon_header_contact_address',
                'label'         => 'Адрес',
                'name'          => 'nh_header_contact_address',
                'type'          => 'textarea',
                'rows'          => 3,
                'new_lines'     => 'br',
                'default_value' => 'Al Noor st, Al Sufouh, Al Sufouh 1, Dubai',
            ],
            [
                'key'           => 'field_nh_salon_header_contact_hours',
                'label'         => 'Время работы',
                'name'          => 'nh_header_contact_hours',
                'type'          => 'text',
                'default_value' => "We're open daily: 10 AM - 10 PM",
            ],
            [
                'key'        => 'field_nh_salon_header_socials',
                'label'      => 'Соцсети',
                'name'       => 'nh_header_contact_socials',
                'type'       => 'group',
                'layout'     => 'row',
                'sub_fields' => [
                    [
                        'key'   => 'field_nh_salon_header_socials_telegram',
                        'label' => 'Телеграм',
                        'name'  => 'telegram',
                        'type'  => 'url',
                    ],
                    [
                        'key'   => 'field_nh_salon_header_socials_instagram',
                        'label' => 'Инстаграм',
                        'name'  => 'instagram',
                        'type'  => 'url',
                    ],
                    [
                        'key'   => 'field_nh_salon_header_socials_whatsapp',
                        'label' => 'WhatsApp',
                        'name'  => 'whatsapp',
                        'type'  => 'url',
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => 'header-footer-salon',
                ],
            ],
        ],
        'active' => true,
    ]);

    acf_add_local_field_group([
        'key'    => 'group_nh_salon_footer',
        'title'  => 'Подвал Salon',
        'fields' => [
            [
                'key'           => 'field_nh_salon_footer_logo',
                'label'         => 'Логотип',
                'name'          => 'nh_footer_logo',
                'type'          => 'image',
                'return_format' => 'url',
                'preview_size'  => 'medium',
            ],
            [
                'key'           => 'field_nh_salon_footer_logo_alt',
                'label'         => 'Alt-текст логотипа',
                'name'          => 'nh_footer_logo_alt',
                'type'          => 'text',
                'default_value' => 'Nice Hair',
            ],
            [
                'key'           => 'field_nh_salon_footer_description',
                'label'         => 'Описание бренда',
                'name'          => 'nh_footer_description',
                'type'          => 'text',
                'default_value' => '2026 / Premium Hair Extensions in the Heart of Dubai',
            ],
            [
                'key'          => 'field_nh_salon_footer_nav_column_1',
                'label'        => 'РЎСЃС‹Р»РєРё, РєРѕР»РѕРЅРєР° 1',
                'name'         => 'nh_footer_nav_column_1',
                'label'        => "\xD0\x9D\xD0\xB0\xD0\xB2\xD0\xB8\xD0\xB3\xD0\xB0\xD1\x86\xD0\xB8\xD1\x8F\x2C\x20\xD0\xBA\xD0\xBE\xD0\xBB\xD0\xBE\xD0\xBD\xD0\xBA\xD0\xB0\x20\x31",
                'type'         => 'repeater',
                'layout'       => 'block',
                'button_label' => 'Р”РѕР±Р°РІРёС‚СЊ СЃСЃС‹Р»РєСѓ РІ РєРѕР»РѕРЅРєСѓ 1',
                'sub_fields'   => [
                    [
                        'key'           => 'field_nh_salon_footer_nav_column_1_link',
                        'label'         => 'Ссылка',
                        'name'          => 'item_link',
                        'type'          => 'link',
                        'return_format' => 'array',
                        'required'      => 1,
                    ],
                ],
                'button_label' => "\xD0\x94\xD0\xBE\xD0\xB1\xD0\xB0\xD0\xB2\xD0\xB8\xD1\x82\xD1\x8C\x20\xD1\x81\xD1\x81\xD1\x8B\xD0\xBB\xD0\xBA\xD1\x83\x20\xD0\xB2\x20\xD0\xBA\xD0\xBE\xD0\xBB\xD0\xBE\xD0\xBD\xD0\xBA\xD1\x83\x20\x31",
            ],
            [
                'key'          => 'field_nh_salon_footer_nav_column_2',
                'label'        => 'РЎСЃС‹Р»РєРё, РєРѕР»РѕРЅРєР° 2',
                'name'         => 'nh_footer_nav_column_2',
                'label'        => "\xD0\x9D\xD0\xB0\xD0\xB2\xD0\xB8\xD0\xB3\xD0\xB0\xD1\x86\xD0\xB8\xD1\x8F\x2C\x20\xD0\xBA\xD0\xBE\xD0\xBB\xD0\xBE\xD0\xBD\xD0\xBA\xD0\xB0\x20\x32",
                'type'         => 'repeater',
                'layout'       => 'block',
                'button_label' => 'Р”РѕР±Р°РІРёС‚СЊ СЃСЃС‹Р»РєСѓ РІ РєРѕР»РѕРЅРєСѓ 2',
                'sub_fields'   => [
                    [
                        'key'           => 'field_nh_salon_footer_nav_column_2_link',
                        'label'         => 'Ссылка',
                        'name'          => 'item_link',
                        'type'          => 'link',
                        'return_format' => 'array',
                        'required'      => 1,
                    ],
                ],
                'button_label' => "\xD0\x94\xD0\xBE\xD0\xB1\xD0\xB0\xD0\xB2\xD0\xB8\xD1\x82\xD1\x8C\x20\xD1\x81\xD1\x81\xD1\x8B\xD0\xBB\xD0\xBA\xD1\x83\x20\xD0\xB2\x20\xD0\xBA\xD0\xBE\xD0\xBB\xD0\xBE\xD0\xBD\xD0\xBA\xD1\x83\x20\x32",
            ],
            [
                'key'          => 'field_nh_salon_footer_nav_column_3',
                'label'        => 'РЎСЃС‹Р»РєРё, РєРѕР»РѕРЅРєР° 3',
                'name'         => 'nh_footer_nav_column_3',
                'label'        => "\xD0\x9D\xD0\xB0\xD0\xB2\xD0\xB8\xD0\xB3\xD0\xB0\xD1\x86\xD0\xB8\xD1\x8F\x2C\x20\xD0\xBA\xD0\xBE\xD0\xBB\xD0\xBE\xD0\xBD\xD0\xBA\xD0\xB0\x20\x33",
                'type'         => 'repeater',
                'layout'       => 'block',
                'button_label' => 'Р”РѕР±Р°РІРёС‚СЊ СЃСЃС‹Р»РєСѓ РІ РєРѕР»РѕРЅРєСѓ 3',
                'sub_fields'   => [
                    [
                        'key'           => 'field_nh_salon_footer_nav_column_3_link',
                        'label'         => 'Ссылка',
                        'name'          => 'item_link',
                        'type'          => 'link',
                        'return_format' => 'array',
                        'required'      => 1,
                    ],
                ],
                'button_label' => "\xD0\x94\xD0\xBE\xD0\xB1\xD0\xB0\xD0\xB2\xD0\xB8\xD1\x82\xD1\x8C\x20\xD1\x81\xD1\x81\xD1\x8B\xD0\xBB\xD0\xBA\xD1\x83\x20\xD0\xB2\x20\xD0\xBA\xD0\xBE\xD0\xBB\xD0\xBE\xD0\xBD\xD0\xBA\xD1\x83\x20\x33",
            ],
            [
                'key'          => 'field_nh_salon_footer_nav_items',
                'label'        => 'Пункты навигации',
                'name'         => 'nh_footer_nav_items',
                'type'         => 'repeater',
                'layout'       => 'block',
                'min'          => 1,
                'button_label' => 'Добавить пункт навигации',
                'instructions' => 'Пункты выводятся по порядку и распределяются по колонкам в шаблоне.',
                'sub_fields'   => [
                    [
                        'key'           => 'field_nh_salon_footer_nav_item_link',
                        'label'         => 'Ссылка',
                        'name'          => 'item_link',
                        'type'          => 'link',
                        'return_format' => 'array',
                        'required'      => 1,
                    ],
                ],
            ],
            [
                'key'           => 'field_nh_salon_footer_address',
                'label'         => 'Адрес',
                'name'          => 'nh_footer_address',
                'type'          => 'text',
                'default_value' => '[Al Noor st, Al Sufouh, Al Sufouh 1, Dubai]',
            ],
            [
                'key'           => 'field_nh_salon_footer_hours',
                'label'         => 'Время работы',
                'name'          => 'nh_footer_hours',
                'type'          => 'text',
                'default_value' => "We're open daily: 10 AM - 10 PM",
            ],
            [
                'key'        => 'field_nh_salon_footer_socials',
                'label'      => 'Соцсети',
                'name'       => 'nh_footer_socials',
                'type'       => 'group',
                'layout'     => 'row',
                'sub_fields' => [
                    [
                        'key'   => 'field_nh_salon_footer_socials_telegram',
                        'label' => 'Телеграм',
                        'name'  => 'telegram',
                        'type'  => 'url',
                    ],
                    [
                        'key'   => 'field_nh_salon_footer_socials_instagram',
                        'label' => 'Инстаграм',
                        'name'  => 'instagram',
                        'type'  => 'url',
                    ],
                    [
                        'key'   => 'field_nh_salon_footer_socials_whatsapp',
                        'label' => 'WhatsApp',
                        'name'  => 'whatsapp',
                        'type'  => 'url',
                    ],
                ],
            ],
            [
                'key'           => 'field_nh_salon_footer_phone',
                'label'         => 'Телефон (отображение)',
                'name'          => 'nh_footer_phone',
                'type'          => 'text',
                'default_value' => '+971 58 598 8409',
            ],
            [
                'key'           => 'field_nh_salon_footer_phone_link',
                'label'         => 'Телефон (ссылка)',
                'name'          => 'nh_footer_phone_link',
                'type'          => 'text',
                'default_value' => '+971585988409',
            ],
            [
                'key'           => 'field_nh_salon_footer_booking_link',
                'label'         => 'Кнопка записи',
                'name'          => 'nh_footer_booking_link',
                'type'          => 'link',
                'return_format' => 'array',
            ],
            [
                'key'           => 'field_nh_salon_footer_subscribe_title',
                'label'         => 'Заголовок блока подписки',
                'name'          => 'nh_footer_subscribe_title',
                'type'          => 'text',
                'default_value' => 'SUBSCRIBE TO NEWS:',
            ],
            [
                'key'           => 'field_nh_salon_footer_subscribe_placeholder',
                'label'         => 'Placeholder поля подписки',
                'name'          => 'nh_footer_subscribe_placeholder',
                'type'          => 'text',
                'default_value' => 'Email',
            ],
            [
                'key'   => 'field_nh_salon_footer_privacy_url',
                'label' => 'Ссылка на Privacy Policy',
                'name'  => 'nh_footer_privacy_url',
                'type'  => 'url',
            ],
            [
                'key'        => 'field_nh_salon_footer_credits',
                'label'      => 'Кредиты',
                'name'       => 'nh_footer_credits',
                'type'       => 'group',
                'layout'     => 'block',
                'sub_fields' => [
                    [
                        'key'           => 'field_nh_salon_footer_designer_name',
                        'label'         => 'Имя дизайнера',
                        'name'          => 'designer_name',
                        'type'          => 'text',
                        'default_value' => 'umapalata.space',
                    ],
                    [
                        'key'   => 'field_nh_salon_footer_designer_url',
                        'label' => 'Ссылка дизайнера',
                        'name'  => 'designer_url',
                        'type'  => 'url',
                    ],
                    [
                        'key'           => 'field_nh_salon_footer_developer_name',
                        'label'         => 'Имя разработчика',
                        'name'          => 'developer_name',
                        'type'          => 'text',
                        'default_value' => 'username',
                    ],
                    [
                        'key'   => 'field_nh_salon_footer_developer_url',
                        'label' => 'Ссылка разработчика',
                        'name'  => 'developer_url',
                        'type'  => 'url',
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => 'header-footer-salon',
                ],
            ],
        ],
        'active' => true,
    ]);

    acf_add_local_field_group([
        'key'    => 'group_nh_results_shared',
        'title'  => 'Результаты - общие карточки',
        'fields' => [
            [
                'key'           => 'field_nh_results_global_hide_nav_desktop',
                'label'         => 'Скрыть навигацию на Desktop',
                'name'          => 'nh_results_hide_nav_desktop',
                'type'          => 'true_false',
                'ui'            => 0,
                'default_value' => 0,
                'instructions'  => 'Скрывает стрелки слайдера только на ширине Desktop.',
            ],
            [
                'key'           => 'field_nh_results_global_hide_nav_tablet',
                'label'         => 'Скрыть навигацию на Tablet',
                'name'          => 'nh_results_hide_nav_tablet',
                'type'          => 'true_false',
                'ui'            => 0,
                'default_value' => 0,
                'instructions'  => 'Скрывает стрелки слайдера только на ширине Tablet.',
            ],
            [
                'key'           => 'field_nh_results_global_hide_nav_mobile',
                'label'         => 'Скрыть навигацию на Mobile',
                'name'          => 'nh_results_hide_nav_mobile',
                'type'          => 'true_false',
                'ui'            => 0,
                'default_value' => 0,
                'instructions'  => 'Скрывает стрелки слайдера только на ширине Mobile.',
            ],
            [
                'key'          => 'field_nh_results_items',
                'label'        => 'Карточки результатов',
                'name'         => 'nh_results_items',
                'type'         => 'repeater',
                'min'          => 1,
                'layout'       => 'block',
                'collapsed'    => 'field_nh_results_length',
                'button_label' => 'Добавить карточку результата',
                'instructions' => 'Эти карточки используются на всех страницах с блоком Results (Salon, Shop и т.д.).',
                'sub_fields'   => [
                    [
                        'key'           => 'field_nh_results_before_image',
                        'label'         => 'Изображение до',
                        'name'          => 'item_before_image',
                        'type'          => 'image',
                        'return_format' => 'array',
                        'preview_size'  => 'medium',
                        'required'      => 1,
                    ],
                    [
                        'key'           => 'field_nh_results_after_image',
                        'label'         => 'Изображение после',
                        'name'          => 'item_after_image',
                        'type'          => 'image',
                        'return_format' => 'array',
                        'preview_size'  => 'medium',
                        'required'      => 1,
                    ],
                    [
                        'key'         => 'field_nh_results_length',
                        'label'       => 'Длина',
                        'name'        => 'item_length',
                        'type'        => 'text',
                        'placeholder' => '65 cm',
                    ],
                    [
                        'key'         => 'field_nh_results_hair',
                        'label'       => 'Волосы',
                        'name'        => 'item_hair',
                        'type'        => 'text',
                        'placeholder' => 'exclusive 3226',
                    ],
                    [
                        'key'         => 'field_nh_results_capsules',
                        'label'       => 'Капсулы',
                        'name'        => 'item_capsules',
                        'type'        => 'text',
                        'placeholder' => '288 pcs',
                    ],
                    [
                        'key'       => 'field_nh_results_comment',
                        'label'     => 'Комментарий стилиста',
                        'name'      => 'item_comment',
                        'type'      => 'textarea',
                        'rows'      => 4,
                        'new_lines' => 'br',
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => 'results-settings',
                ],
            ],
        ],
        'active' => true,
    ]);
}
add_action('acf/init', 'nice_hair_register_acf_options');

function nice_hair_hide_legacy_salon_footer_nav_field(array $field): array|false
{
    $current_page = isset($_GET['page']) && is_string($_GET['page'])
        ? sanitize_key(wp_unslash($_GET['page']))
        : '';

    if ($current_page === 'header-footer-salon') {
        return false;
    }

    return $field;
}
add_filter('acf/prepare_field/name=nh_footer_nav_items', 'nice_hair_hide_legacy_salon_footer_nav_field');

function nice_hair_results_settings_page_slug(): string
{
    return 'results-settings';
}

function nice_hair_is_results_settings_admin_page(): bool
{
    if (! is_admin()) {
        return false;
    }

    $page = isset($_GET['page']) && is_string($_GET['page'])
        ? sanitize_key(wp_unslash($_GET['page']))
        : '';

    return $page === nice_hair_results_settings_page_slug();
}

function nice_hair_rename_results_admin_menu(): void
{
    global $menu;

    if (! is_array($menu)) {
        return;
    }

    foreach ($menu as &$menu_item) {
        if (! is_array($menu_item) || ($menu_item[2] ?? '') !== nice_hair_results_settings_page_slug()) {
            continue;
        }

        $menu_item[0] = __('Блок результаты', 'nice-hair');
        break;
    }
}
add_action('admin_menu', 'nice_hair_rename_results_admin_menu', 999);

function nice_hair_results_settings_admin_title(string $admin_title, string $title): string
{
    if (! nice_hair_is_results_settings_admin_page()) {
        return $admin_title;
    }

    return str_replace($title, __('Настройки блока результаты', 'nice-hair'), $admin_title);
}
add_filter('admin_title', 'nice_hair_results_settings_admin_title', 10, 2);

function nice_hair_results_settings_admin_head(): void
{
    if (! nice_hair_is_results_settings_admin_page()) {
        return;
    }
    ?>
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        const pageTitle = document.querySelector(".wrap > h1");

        if (pageTitle) {
          pageTitle.textContent = "Настройки блока результаты";
        }
      });
    </script>
    <?php
}
add_action('admin_head', 'nice_hair_results_settings_admin_head');

function nice_hair_rename_results_field_group(array $field_group): array
{
    $field_group['title'] = 'Блок результатов - общие настройки и карточки';

    return $field_group;
}
add_filter('acf/load_field_group/key=group_nh_results_shared', 'nice_hair_rename_results_field_group');
