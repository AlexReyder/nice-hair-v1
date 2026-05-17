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


}
add_action('acf/init', 'nice_hair_register_acf_options');







