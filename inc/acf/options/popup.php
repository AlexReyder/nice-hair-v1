<?php

declare(strict_types=1);

function nice_hair_register_popup_acf_options(): void
{
    if (! function_exists('acf_add_options_sub_page')) {
        return;
    }

    acf_add_options_sub_page([
        'page_title' => __('Настройки popup', 'nice-hair'),
        'menu_title' => __('Настройки', 'nice-hair'),
        'menu_slug' => 'popup-settings',
        'parent_slug' => 'edit.php?post_type=nh_popup',
        'capability' => function_exists('nice_hair_admin_access_acf_capability')
            ? nice_hair_admin_access_acf_capability('popup-settings')
            : 'edit_posts',
        'post_id' => 'nh_popup_settings',
    ]);

    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_nh_popup_settings',
        'title' => 'Настройки popup',
        'fields' => [
            [
                'key' => 'field_nh_popup_enabled',
                'label' => 'Включить popup',
                'name' => 'nh_popup_enabled',
                'type' => 'true_false',
                'ui' => 1,
                'default_value' => 0,
            ],
            [
                'key' => 'field_nh_popup_locations',
                'label' => 'Показывать на страницах',
                'name' => 'nh_popup_locations',
                'type' => 'checkbox',
                'layout' => 'vertical',
                'return_format' => 'value',
                'choices' => [
                    'front_page' => 'Главная',
                    'salon_page' => 'Salon',
                    'shop_page' => 'Shop',
                    'shipping_page' => 'Shipping & Payment',
                    'tools_page' => 'Tools',
                    'blog_home' => 'Blog',
                    'blog_single' => 'Записи блога',
                    'default_pages' => 'Другие страницы',
                    'product_archive' => 'Категории товаров',
                    'product_single' => 'Карточки товаров',
                ],
                'instructions' => 'Выберите разделы сайта, где popup должен быть доступен для показа.',
            ],
            [
                'key' => 'field_nh_popup_specific_objects',
                'label' => 'Дополнительно включить на отдельных страницах',
                'name' => 'nh_popup_specific_objects',
                'type' => 'relationship',
                'post_type' => ['page', 'post', 'product'],
                'filters' => ['search', 'post_type'],
                'return_format' => 'id',
                'instructions' => 'Используйте это поле, если popup нужно показывать на конкретной странице, записи блога или карточке товара.',
            ],
            [
                'key' => 'field_nh_popup_delay_seconds',
                'label' => 'Задержка перед открытием, секунд',
                'name' => 'nh_popup_delay_seconds',
                'type' => 'number',
                'default_value' => 10,
                'min' => 0,
                'step' => 1,
                'instructions' => 'Popup всегда открывается автоматически через заданное время после загрузки страницы.',
            ],
            [
                'key' => 'field_nh_popup_display_mode',
                'label' => 'Режим показа popup',
                'name' => 'nh_popup_display_mode',
                'type' => 'select',
                'choices' => [
                    'once' => 'Один раз',
                    'session' => 'Один раз за сессию',
                    'days' => 'Повторно через N дней',
                ],
                'default_value' => 'once',
                'ui' => 1,
            ],
            [
                'key' => 'field_nh_popup_repeat_days',
                'label' => 'Повторно показывать через, дней',
                'name' => 'nh_popup_repeat_days',
                'type' => 'number',
                'default_value' => 7,
                'min' => 1,
                'step' => 1,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_nh_popup_display_mode',
                            'operator' => '==',
                            'value' => 'days',
                        ],
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'popup-settings',
                ],
            ],
        ],
        'active' => true,
    ]);
}
add_action('acf/init', 'nice_hair_register_popup_acf_options', 25);
