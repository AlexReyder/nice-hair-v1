<?php

declare(strict_types=1);

function nice_hair_register_shop_pricing_acf_options(): void
{
    if (! function_exists('acf_add_options_page')) {
        return;
    }

    acf_add_options_page([
        'page_title' => 'Shop Pricing Config',
        'menu_title' => 'Shop Pricing',
        'menu_slug'  => nice_hair_shop_pricing_options_page_slug(),
        'capability' => function_exists('nice_hair_admin_access_acf_capability')
            ? nice_hair_admin_access_acf_capability(nice_hair_shop_pricing_options_page_slug())
            : 'edit_posts',
        'redirect'   => false,
        'icon_url'   => 'dashicons-chart-line',
        'position'   => 33,
        'post_id'    => function_exists('nice_hair_shop_pricing_post_id')
            ? nice_hair_shop_pricing_post_id()
            : 'nh_shop_pricing_config',
    ]);

    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key'    => 'group_nh_shop_pricing_custom_hair',
        'title'  => 'Shop Pricing: Custom Hair',
        'fields' => [
            [
                'key'           => 'field_nh_shop_pricing_custom_note',
                'label'         => 'Комментарий',
                'name'          => 'nh_shop_pricing_custom_note',
                'type'          => 'message',
                'message'       => 'Используем эти таблицы как источник pricing-конфига для Custom Hair и доплаты за форму изделия.',
                'new_lines'     => 'wpautop',
                'esc_html'      => 0,
            ],
            [
                'key'          => 'field_nh_shop_pricing_custom_base_prices',
                'label'        => 'Custom hair - цена волос за 1 грамм',
                'name'         => 'nh_shop_pricing_custom_base_prices',
                'type'         => 'repeater',
                'layout'       => 'table',
                'button_label' => 'Добавить строку',
                'sub_fields'   => [
                    [
                        'key'           => 'field_nh_shop_pricing_custom_base_quality',
                        'label'         => 'Quality',
                        'name'          => 'item_quality',
                        'type'          => 'select',

                        /**
                         * Fallback for early local field registration.
                         * Actual choices are refreshed via acf/load_field in helpers.php.
                         */
                        'choices'       => nice_hair_get_shop_pricing_hair_quality_fallback_choices(),

                        'default_value' => 'Lux',
                        'ui'            => 1,
                    ],
                    [
                        'key'     => 'field_nh_shop_pricing_custom_base_length',
                        'label'   => 'Длина',
                        'name'    => 'item_length',
                        'type'    => 'number',
                        'min'     => 0,
                        'step'    => 1,
                        'append'  => 'cm',
                    ],
                    [
                        'key'     => 'field_nh_shop_pricing_custom_base_price_per_gram',
                        'label'   => 'Цена за 1 грамм',
                        'name'    => 'item_price_per_gram',
                        'type'    => 'number',
                        'min'     => 0,
                        'step'    => 0.01,
                        'prepend' => '$',
                    ],
                ],
            ],
            [
                'key'          => 'field_nh_shop_pricing_form_surcharges',
                'label'        => 'Доплата за форму изделия - цена за 1 грамм',
                'name'         => 'nh_shop_pricing_form_surcharges',
                'type'         => 'repeater',
                'layout'       => 'table',
                'button_label' => 'Добавить строку',
                'sub_fields'   => [
                    [
                        'key'          => 'field_nh_shop_pricing_form_surcharge_extension_type',
                        'label'        => 'Extension type',
                        'name'         => 'item_extension_type',
                        'type'         => 'text',
                        'instructions' => 'Используйте тот же label, что и у Woo attribute Extension Type.',
                    ],
                    [
                        'key'     => 'field_nh_shop_pricing_form_surcharge_price_per_gram',
                        'label'   => 'Цена за 1 грамм',
                        'name'    => 'item_price_per_gram',
                        'type'    => 'number',
                        'min'     => 0,
                        'step'    => 0.01,
                        'prepend' => '$',
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => nice_hair_shop_pricing_options_page_slug(),
                ],
            ],
        ],
        'active' => true,
    ]);
}

add_action('acf/init', 'nice_hair_register_shop_pricing_acf_options', 20);