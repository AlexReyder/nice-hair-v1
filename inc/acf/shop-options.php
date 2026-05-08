<?php

declare(strict_types=1);

function nice_hair_shop_pricing_options_page_slug(): string
{
    return 'shop-pricing-config';
}

function nice_hair_get_shop_pricing_default_field_values(): array
{
    if (! function_exists('nice_hair_get_shop_pricing_defaults')) {
        return [];
    }

    $defaults = nice_hair_get_shop_pricing_defaults();
    $form_labels = function_exists('nice_hair_get_default_product_form_labels')
        ? nice_hair_get_default_product_form_labels()
        : [];

    $keratin_table = static function (array $items): array {
        $rows = [];

        foreach ($items as $weight => $price) {
            $rows[] = [
                'item_weight' => (string) $weight,
                'item_price'  => (float) $price,
            ];
        }

        return $rows;
    };

    $custom_base_rows = [];

    foreach ((array) ($defaults['custom_hair']['base_prices'] ?? []) as $quality => $lengths) {
        foreach ((array) $lengths as $length => $price) {
            $custom_base_rows[] = [
                'item_quality'         => ucfirst((string) $quality),
                'item_length'          => (float) $length,
                'item_price_per_gram'  => (float) $price,
            ];
        }
    }

    $form_surcharge_rows = [];

    foreach ((array) ($defaults['custom_hair']['form_surcharges'] ?? []) as $form_key => $price) {
        $normalized_key = function_exists('nice_hair_normalize_shop_key')
            ? nice_hair_normalize_shop_key((string) $form_key)
            : sanitize_title((string) $form_key);
        $form_surcharge_rows[] = [
            'item_extension_type'   => (string) ($form_labels[$normalized_key] ?? ucwords(str_replace('_', ' ', (string) $normalized_key))),
            'item_price_per_gram'   => (float) $price,
        ];
    }

    return [
        'nh_shop_pricing_keratin_pigmented'        => $keratin_table((array) ($defaults['keratin']['pigmented'] ?? [])),
        'nh_shop_pricing_keratin_italian_standard' => $keratin_table((array) ($defaults['keratin']['italian_standard'] ?? [])),
        'nh_shop_pricing_keratin_transparent'      => $keratin_table((array) ($defaults['keratin']['transparent'] ?? [])),
        'nh_shop_pricing_custom_base_prices'       => $custom_base_rows,
        'nh_shop_pricing_form_surcharges'          => $form_surcharge_rows,
    ];
}

function nice_hair_maybe_bootstrap_shop_pricing_config(): void
{
    if (! function_exists('get_field') || ! function_exists('update_field')) {
        return;
    }

    $pricing_post_id = function_exists('nice_hair_shop_pricing_post_id')
        ? nice_hair_shop_pricing_post_id()
        : 'nh_shop_pricing_config';
    $legacy_post_id = function_exists('nice_hair_header_footer_post_id')
        ? nice_hair_header_footer_post_id('shop')
        : 'nh_header_footer_shop';
    $default_values = nice_hair_get_shop_pricing_default_field_values();
    $field_names = [
        'nh_shop_pricing_keratin_pigmented',
        'nh_shop_pricing_keratin_italian_standard',
        'nh_shop_pricing_keratin_transparent',
        'nh_shop_pricing_custom_base_prices',
        'nh_shop_pricing_form_surcharges',
    ];

    foreach ($field_names as $field_name) {
        $current_value = get_field($field_name, $pricing_post_id);

        if (function_exists('nice_hair_acf_has_value') && nice_hair_acf_has_value($current_value)) {
            continue;
        }

        if (! function_exists('nice_hair_acf_has_value') && $current_value !== null && $current_value !== false && $current_value !== '' && $current_value !== []) {
            continue;
        }

        $legacy_value = get_field($field_name, $legacy_post_id);
        $value_to_store = null;

        if (function_exists('nice_hair_acf_has_value') && nice_hair_acf_has_value($legacy_value)) {
            $value_to_store = $legacy_value;
        } elseif (! function_exists('nice_hair_acf_has_value') && $legacy_value !== null && $legacy_value !== false && $legacy_value !== '' && $legacy_value !== []) {
            $value_to_store = $legacy_value;
        } else {
            $value_to_store = $default_values[$field_name] ?? null;
        }

        if ($value_to_store === null || $value_to_store === []) {
            continue;
        }

        update_field($field_name, $value_to_store, $pricing_post_id);
    }
}

function nice_hair_get_shop_header_catalog_default_field_values(): array
{
    return function_exists('nice_hair_get_shop_catalog_menu_default_field_rows')
        ? nice_hair_get_shop_catalog_menu_default_field_rows()
        : [];
}

function nice_hair_maybe_bootstrap_shop_header_catalog_dropdown(): void
{
    if (! function_exists('get_field') || ! function_exists('update_field')) {
        return;
    }

    $bootstrap_flag_option = 'nh_shop_header_catalog_dropdown_bootstrapped';

    if (get_option($bootstrap_flag_option) === '1') {
        return;
    }

    $post_id = function_exists('nice_hair_header_footer_post_id')
        ? nice_hair_header_footer_post_id('shop')
        : 'nh_header_footer_shop';
    $field_name = 'nh_header_catalog_dropdown_items';
    $current_value = get_field($field_name, $post_id);

    if (function_exists('nice_hair_acf_has_value') && nice_hair_acf_has_value($current_value)) {
        update_option($bootstrap_flag_option, '1', false);
        return;
    }

    if (! function_exists('nice_hair_acf_has_value') && $current_value !== null && $current_value !== false && $current_value !== '' && $current_value !== []) {
        update_option($bootstrap_flag_option, '1', false);
        return;
    }

    $default_rows = nice_hair_get_shop_header_catalog_default_field_values();

    if ($default_rows === []) {
        return;
    }

    update_field($field_name, $default_rows, $post_id);
    update_option($bootstrap_flag_option, '1', false);
}

function nice_hair_register_shop_acf_options(): void
{
    if (! function_exists('acf_add_options_sub_page') || ! function_exists('acf_add_options_page')) {
        return;
    }

    acf_add_options_sub_page([
        'page_title'  => "\u{0425}\u{0435}\u{0434}\u{0435}\u{0440} \u{0438} \u{0444}\u{0443}\u{0442}\u{0435}\u{0440} Shop",
        'menu_title'  => 'Shop',
        'menu_slug'   => 'header-footer-shop',
        'parent_slug' => 'header-footer-settings',
        'capability'  => 'edit_posts',
        'post_id'     => nice_hair_header_footer_post_id('shop'),
    ]);

    acf_add_options_page([
        'page_title' => 'Shop Pricing Config',
        'menu_title' => 'Shop Pricing',
        'menu_slug'  => nice_hair_shop_pricing_options_page_slug(),
        'capability' => 'edit_posts',
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
        'key'    => 'group_nh_shop_header',
        'title'  => "\u{0425}\u{0435}\u{0434}\u{0435}\u{0440} Shop",
        'fields' => [
            [
                'key'           => 'field_nh_shop_header_logo',
                'label'         => "\u{041B}\u{043E}\u{0433}\u{043E}\u{0442}\u{0438}\u{043F}",
                'name'          => 'nh_header_logo',
                'type'          => 'image',
                'return_format' => 'url',
                'preview_size'  => 'medium',
            ],
            [
                'key'           => 'field_nh_shop_header_logo_alt',
                'label'         => "Alt-\u{0442}\u{0435}\u{043A}\u{0441}\u{0442} \u{043B}\u{043E}\u{0433}\u{043E}\u{0442}\u{0438}\u{043F}\u{0430}",
                'name'          => 'nh_header_logo_alt',
                'type'          => 'text',
                'default_value' => 'Nice Hair',
            ],
            [
                'key'          => 'field_nh_shop_header_nav_items',
                'label'        => "\u{041F}\u{0443}\u{043D}\u{043A}\u{0442}\u{044B} \u{043C}\u{0435}\u{043D}\u{044E}",
                'name'         => 'nh_header_nav_items',
                'type'         => 'repeater',
                'layout'       => 'block',
                'min'          => 1,
                'button_label' => "\u{0414}\u{043E}\u{0431}\u{0430}\u{0432}\u{0438}\u{0442}\u{044C} \u{043F}\u{0443}\u{043D}\u{043A}\u{0442} \u{043C}\u{0435}\u{043D}\u{044E}",
                'sub_fields'   => [
                    [
                        'key'           => 'field_nh_shop_header_nav_item_link',
                        'label'         => "\u{0421}\u{0441}\u{044B}\u{043B}\u{043A}\u{0430}",
                        'name'          => 'item_link',
                        'type'          => 'link',
                        'return_format' => 'array',
                        'required'      => 1,
                    ],
                ],
            ],
            [
                'key'          => 'field_nh_shop_header_catalog_dropdown_items',
                'label'        => 'Выпадающее меню Catalog',
                'name'         => 'nh_header_catalog_dropdown_items',
                'type'         => 'repeater',
                'layout'       => 'block',
                'button_label' => "\u{0414}\u{043E}\u{0431}\u{0430}\u{0432}\u{0438}\u{0442}\u{044C} \u{043F}\u{0443}\u{043D}\u{043A}\u{0442} dropdown",
                'instructions' => 'Desktop dropdown и mobile accordion для пункта Catalog в Header Shop.',
                'sub_fields'   => [
                    [
                        'key'           => 'field_nh_shop_header_catalog_dropdown_item_link',
                        'label'         => "\u{0421}\u{0441}\u{044B}\u{043B}\u{043A}\u{0430}",
                        'name'          => 'item_link',
                        'type'          => 'link',
                        'return_format' => 'array',
                        'required'      => 1,
                    ],
                ],
            ],
            [
                'key'           => 'field_nh_shop_header_contact_phone',
                'label'         => "\u{0422}\u{0435}\u{043B}\u{0435}\u{0444}\u{043E}\u{043D}",
                'name'          => 'nh_header_contact_phone',
                'type'          => 'text',
                'default_value' => '+971 58 598 8409',
            ],
            [
                'key'           => 'field_nh_shop_header_contact_phone_link',
                'label'         => "\u{0422}\u{0435}\u{043B}\u{0435}\u{0444}\u{043E}\u{043D} (\u{0441}\u{0441}\u{044B}\u{043B}\u{043A}\u{0430})",
                'name'          => 'nh_header_contact_phone_link',
                'type'          => 'text',
                'default_value' => '+971585988409',
            ],
            [
                'key'           => 'field_nh_shop_header_contact_address',
                'label'         => "\u{0410}\u{0434}\u{0440}\u{0435}\u{0441}",
                'name'          => 'nh_header_contact_address',
                'type'          => 'textarea',
                'rows'          => 3,
                'new_lines'     => 'br',
                'default_value' => 'Al Noor st, Al Sufouh, Al Sufouh 1, Dubai',
            ],
            [
                'key'           => 'field_nh_shop_header_contact_hours',
                'label'         => "\u{0412}\u{0440}\u{0435}\u{043C}\u{044F} \u{0440}\u{0430}\u{0431}\u{043E}\u{0442}\u{044B}",
                'name'          => 'nh_header_contact_hours',
                'type'          => 'text',
                'default_value' => "We're open daily: 10 AM - 10 PM",
            ],
            [
                'key'        => 'field_nh_shop_header_socials',
                'label'      => "\u{0421}\u{043E}\u{0446}\u{0441}\u{0435}\u{0442}\u{0438}",
                'name'       => 'nh_header_contact_socials',
                'type'       => 'group',
                'layout'     => 'row',
                'sub_fields' => [
                    [
                        'key'   => 'field_nh_shop_header_socials_telegram',
                        'label' => "\u{0422}\u{0435}\u{043B}\u{0435}\u{0433}\u{0440}\u{0430}\u{043C}",
                        'name'  => 'telegram',
                        'type'  => 'url',
                    ],
                    [
                        'key'   => 'field_nh_shop_header_socials_instagram',
                        'label' => "\u{0418}\u{043D}\u{0441}\u{0442}\u{0430}\u{0433}\u{0440}\u{0430}\u{043C}",
                        'name'  => 'instagram',
                        'type'  => 'url',
                    ],
                    [
                        'key'   => 'field_nh_shop_header_socials_whatsapp',
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
                    'value'    => 'header-footer-shop',
                ],
            ],
        ],
        'active' => true,
    ]);

    acf_add_local_field_group([
        'key'    => 'group_nh_shop_footer',
        'title'  => "\u{0424}\u{0443}\u{0442}\u{0435}\u{0440} Shop",
        'fields' => [
            [
                'key'           => 'field_nh_shop_footer_logo',
                'label'         => "\u{041B}\u{043E}\u{0433}\u{043E}\u{0442}\u{0438}\u{043F}",
                'name'          => 'nh_footer_logo',
                'type'          => 'image',
                'return_format' => 'url',
                'preview_size'  => 'medium',
            ],
            [
                'key'           => 'field_nh_shop_footer_logo_alt',
                'label'         => "Alt-\u{0442}\u{0435}\u{043A}\u{0441}\u{0442} \u{043B}\u{043E}\u{0433}\u{043E}\u{0442}\u{0438}\u{043F}\u{0430}",
                'name'          => 'nh_footer_logo_alt',
                'type'          => 'text',
                'default_value' => 'Nice Hair',
            ],
            [
                'key'           => 'field_nh_shop_footer_description',
                'label'         => "\u{041E}\u{043F}\u{0438}\u{0441}\u{0430}\u{043D}\u{0438}\u{0435} \u{0431}\u{0440}\u{0435}\u{043D}\u{0434}\u{0430}",
                'name'          => 'nh_footer_description',
                'type'          => 'text',
                'default_value' => '2026 / Premium Hair Extensions in the Heart of Dubai',
            ],
            [
                'key'           => 'field_nh_shop_footer_nav_column_1_title',
                'label'         => "\u{0417}\u{0430}\u{0433}\u{043E}\u{043B}\u{043E}\u{0432}\u{043E}\u{043A} \u{043A}\u{043E}\u{043B}\u{043E}\u{043D}\u{043A}\u{0438} 1",
                'name'          => 'nh_footer_nav_column_1_title',
                'type'          => 'text',
                'default_value' => 'Catalog',
            ],
            [
                'key'          => 'field_nh_shop_footer_nav_column_1',
                'label'        => "\u{041D}\u{0430}\u{0432}\u{0438}\u{0433}\u{0430}\u{0446}\u{0438}\u{044F}, \u{043A}\u{043E}\u{043B}\u{043E}\u{043D}\u{043A}\u{0430} 1",
                'name'         => 'nh_footer_nav_column_1',
                'type'         => 'repeater',
                'layout'       => 'block',
                'button_label' => "\u{0414}\u{043E}\u{0431}\u{0430}\u{0432}\u{0438}\u{0442}\u{044C} \u{043F}\u{0443}\u{043D}\u{043A}\u{0442} \u{043C}\u{0435}\u{043D}\u{044E}",
                'sub_fields'   => [
                    [
                        'key'           => 'field_nh_shop_footer_nav_column_1_link',
                        'label'         => "\u{0421}\u{0441}\u{044B}\u{043B}\u{043A}\u{0430}",
                        'name'          => 'item_link',
                        'type'          => 'link',
                        'return_format' => 'array',
                        'required'      => 1,
                    ],
                ],
            ],
            [
                'key'           => 'field_nh_shop_footer_nav_column_2_title',
                'label'         => "\u{0417}\u{0430}\u{0433}\u{043E}\u{043B}\u{043E}\u{0432}\u{043E}\u{043A} \u{043A}\u{043E}\u{043B}\u{043E}\u{043D}\u{043A}\u{0438} 2",
                'name'          => 'nh_footer_nav_column_2_title',
                'type'          => 'text',
                'default_value' => 'Site map',
            ],
            [
                'key'          => 'field_nh_shop_footer_nav_column_2',
                'label'        => "\u{041D}\u{0430}\u{0432}\u{0438}\u{0433}\u{0430}\u{0446}\u{0438}\u{044F}, \u{043A}\u{043E}\u{043B}\u{043E}\u{043D}\u{043A}\u{0430} 2",
                'name'         => 'nh_footer_nav_column_2',
                'type'         => 'repeater',
                'layout'       => 'block',
                'button_label' => "\u{0414}\u{043E}\u{0431}\u{0430}\u{0432}\u{0438}\u{0442}\u{044C} \u{043F}\u{0443}\u{043D}\u{043A}\u{0442} \u{043C}\u{0435}\u{043D}\u{044E}",
                'sub_fields'   => [
                    [
                        'key'           => 'field_nh_shop_footer_nav_column_2_link',
                        'label'         => "\u{0421}\u{0441}\u{044B}\u{043B}\u{043A}\u{0430}",
                        'name'          => 'item_link',
                        'type'          => 'link',
                        'return_format' => 'array',
                        'required'      => 1,
                    ],
                ],
            ],
            [
                'key'           => 'field_nh_shop_footer_address',
                'label'         => "\u{0410}\u{0434}\u{0440}\u{0435}\u{0441}",
                'name'          => 'nh_footer_address',
                'type'          => 'text',
                'default_value' => '[Al Noor st, Al Sufouh, Al Sufouh 1, Dubai]',
            ],
            [
                'key'           => 'field_nh_shop_footer_hours',
                'label'         => "\u{0412}\u{0440}\u{0435}\u{043C}\u{044F} \u{0440}\u{0430}\u{0431}\u{043E}\u{0442}\u{044B}",
                'name'          => 'nh_footer_hours',
                'type'          => 'text',
                'default_value' => "We're open daily: 10 AM - 10 PM",
            ],
            [
                'key'        => 'field_nh_shop_footer_socials',
                'label'      => "\u{0421}\u{043E}\u{0446}\u{0441}\u{0435}\u{0442}\u{0438}",
                'name'       => 'nh_footer_socials',
                'type'       => 'group',
                'layout'     => 'row',
                'sub_fields' => [
                    [
                        'key'   => 'field_nh_shop_footer_socials_telegram',
                        'label' => "\u{0422}\u{0435}\u{043B}\u{0435}\u{0433}\u{0440}\u{0430}\u{043C}",
                        'name'  => 'telegram',
                        'type'  => 'url',
                    ],
                    [
                        'key'   => 'field_nh_shop_footer_socials_instagram',
                        'label' => "\u{0418}\u{043D}\u{0441}\u{0442}\u{0430}\u{0433}\u{0440}\u{0430}\u{043C}",
                        'name'  => 'instagram',
                        'type'  => 'url',
                    ],
                    [
                        'key'   => 'field_nh_shop_footer_socials_whatsapp',
                        'label' => 'WhatsApp',
                        'name'  => 'whatsapp',
                        'type'  => 'url',
                    ],
                ],
            ],
            [
                'key'           => 'field_nh_shop_footer_phone',
                'label'         => "\u{0422}\u{0435}\u{043B}\u{0435}\u{0444}\u{043E}\u{043D} (\u{043E}\u{0442}\u{043E}\u{0431}\u{0440}\u{0430}\u{0436}\u{0435}\u{043D}\u{0438}\u{0435})",
                'name'          => 'nh_footer_phone',
                'type'          => 'text',
                'default_value' => '+971 58 598 8409',
            ],
            [
                'key'           => 'field_nh_shop_footer_phone_link',
                'label'         => "\u{0422}\u{0435}\u{043B}\u{0435}\u{0444}\u{043E}\u{043D} (\u{0441}\u{0441}\u{044B}\u{043B}\u{043A}\u{0430})",
                'name'          => 'nh_footer_phone_link',
                'type'          => 'text',
                'default_value' => '+971585988409',
            ],
            [
                'key'           => 'field_nh_shop_footer_booking_link',
                'label'         => "\u{041A}\u{043D}\u{043E}\u{043F}\u{043A}\u{0430} CTA",
                'name'          => 'nh_footer_booking_link',
                'type'          => 'link',
                'return_format' => 'array',
            ],
            [
                'key'           => 'field_nh_shop_footer_subscribe_title',
                'label'         => "\u{0417}\u{0430}\u{0433}\u{043E}\u{043B}\u{043E}\u{0432}\u{043E}\u{043A} \u{043F}\u{043E}\u{0434}\u{043F}\u{0438}\u{0441}\u{043A}\u{0438}",
                'name'          => 'nh_footer_subscribe_title',
                'type'          => 'text',
                'default_value' => 'SUBSCRIBE TO NEWS:',
            ],
            [
                'key'           => 'field_nh_shop_footer_subscribe_placeholder',
                'label'         => "\u{041F}\u{043B}\u{0435}\u{0439}\u{0441}\u{0445}\u{043E}\u{043B}\u{0434}\u{0435}\u{0440} \u{043F}\u{043E}\u{043B}\u{044F} email",
                'name'          => 'nh_footer_subscribe_placeholder',
                'type'          => 'text',
                'default_value' => 'Email',
            ],
            [
                'key'   => 'field_nh_shop_footer_privacy_url',
                'label' => "\u{0421}\u{0441}\u{044B}\u{043B}\u{043A}\u{0430} \u{043D}\u{0430} Privacy Policy",
                'name'  => 'nh_footer_privacy_url',
                'type'  => 'url',
            ],
            [
                'key'        => 'field_nh_shop_footer_credits',
                'label'      => "\u{041A}\u{0440}\u{0435}\u{0434}\u{0438}\u{0442}\u{044B}",
                'name'       => 'nh_footer_credits',
                'type'       => 'group',
                'layout'     => 'block',
                'sub_fields' => [
                    [
                        'key'           => 'field_nh_shop_footer_designer_name',
                        'label'         => "\u{0418}\u{043C}\u{044F} \u{0434}\u{0438}\u{0437}\u{0430}\u{0439}\u{043D}\u{0435}\u{0440}\u{0430}",
                        'name'          => 'designer_name',
                        'type'          => 'text',
                        'default_value' => 'umapalata.space',
                    ],
                    [
                        'key'   => 'field_nh_shop_footer_designer_url',
                        'label' => "\u{0421}\u{0441}\u{044B}\u{043B}\u{043A}\u{0430} \u{0434}\u{0438}\u{0437}\u{0430}\u{0439}\u{043D}\u{0435}\u{0440}\u{0430}",
                        'name'  => 'designer_url',
                        'type'  => 'url',
                    ],
                    [
                        'key'           => 'field_nh_shop_footer_developer_name',
                        'label'         => "\u{0418}\u{043C}\u{044F} \u{0440}\u{0430}\u{0437}\u{0440}\u{0430}\u{0431}\u{043E}\u{0442}\u{0447}\u{0438}\u{043A}\u{0430}",
                        'name'          => 'developer_name',
                        'type'          => 'text',
                        'default_value' => 'username',
                    ],
                    [
                        'key'   => 'field_nh_shop_footer_developer_url',
                        'label' => "\u{0421}\u{0441}\u{044B}\u{043B}\u{043A}\u{0430} \u{0440}\u{0430}\u{0437}\u{0440}\u{0430}\u{0431}\u{043E}\u{0442}\u{0447}\u{0438}\u{043A}\u{0430}",
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
                    'value'    => 'header-footer-shop',
                ],
            ],
        ],
        'active' => true,
    ]);

    acf_add_local_field_group([
        'key'    => 'group_nh_shop_pricing_keratin',
        'title'  => 'Shop Pricing: Keratin',
        'fields' => [
            [
                'key'           => 'field_nh_shop_pricing_keratin_note',
                'label'         => 'Комментарий',
                'name'          => 'nh_shop_pricing_keratin_note',
                'type'          => 'message',
                'message'       => 'Если таблицы пустые, тема использует встроенные fallback-значения из текущего shop data-model слоя.',
                'new_lines'     => 'wpautop',
                'esc_html'      => 0,
            ],
            [
                'key'          => 'field_nh_shop_pricing_keratin_pigmented',
                'label'        => 'Pigmented keratin - цены по весу',
                'name'         => 'nh_shop_pricing_keratin_pigmented',
                'type'         => 'repeater',
                'layout'       => 'table',
                'button_label' => 'Добавить строку',
                'sub_fields'   => [
                    [
                        'key'   => 'field_nh_shop_pricing_keratin_pigmented_weight',
                        'label' => 'Вес',
                        'name'  => 'item_weight',
                        'type'  => 'text',
                    ],
                    [
                        'key'   => 'field_nh_shop_pricing_keratin_pigmented_price',
                        'label' => 'Цена',
                        'name'  => 'item_price',
                        'type'  => 'number',
                        'min'   => 0,
                        'step'  => 0.01,
                        'prepend' => '$',
                    ],
                ],
            ],
            [
                'key'          => 'field_nh_shop_pricing_keratin_italian_standard',
                'label'        => 'Italian gel keratin - standard цены по весу',
                'name'         => 'nh_shop_pricing_keratin_italian_standard',
                'type'         => 'repeater',
                'layout'       => 'table',
                'button_label' => 'Добавить строку',
                'sub_fields'   => [
                    [
                        'key'   => 'field_nh_shop_pricing_keratin_italian_standard_weight',
                        'label' => 'Вес',
                        'name'  => 'item_weight',
                        'type'  => 'text',
                    ],
                    [
                        'key'     => 'field_nh_shop_pricing_keratin_italian_standard_price',
                        'label'   => 'Цена',
                        'name'    => 'item_price',
                        'type'    => 'number',
                        'min'     => 0,
                        'step'    => 0.01,
                        'prepend' => '$',
                    ],
                ],
            ],
            [
                'key'          => 'field_nh_shop_pricing_keratin_transparent',
                'label'        => 'Transparent keratin - цены по весу',
                'name'         => 'nh_shop_pricing_keratin_transparent',
                'type'         => 'repeater',
                'layout'       => 'table',
                'button_label' => 'Добавить строку',
                'sub_fields'   => [
                    [
                        'key'   => 'field_nh_shop_pricing_keratin_transparent_weight',
                        'label' => 'Вес',
                        'name'  => 'item_weight',
                        'type'  => 'text',
                    ],
                    [
                        'key'     => 'field_nh_shop_pricing_keratin_transparent_price',
                        'label'   => 'Цена',
                        'name'    => 'item_price',
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
                        'choices'       => [
                            'Lux'       => 'Lux',
                            'Premium'   => 'Premium',
                            'Exclusive' => 'Exclusive',
                        ],
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
add_action('acf/init', 'nice_hair_register_shop_acf_options', 20);
add_action('acf/init', 'nice_hair_maybe_bootstrap_shop_header_catalog_dropdown', 25);
add_action('acf/init', 'nice_hair_maybe_bootstrap_shop_pricing_config', 30);

function nice_hair_hide_legacy_shop_pricing_field(array $field): array|false
{
    $current_page = isset($_GET['page']) && is_string($_GET['page'])
        ? sanitize_key(wp_unslash($_GET['page']))
        : '';

    if ($current_page !== nice_hair_shop_pricing_options_page_slug()) {
        return $field;
    }

    return false;
}

add_filter('acf/prepare_field/name=nh_shop_pricing_keratin_transparent', 'nice_hair_hide_legacy_shop_pricing_field');
