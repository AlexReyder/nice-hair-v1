<?php

declare(strict_types=1);

function nice_hair_register_discounts_acf_fields(): void
{
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    if (function_exists('acf_get_local_field_group') && acf_get_local_field_group('group_nh_discounts')) {
        return;
    }

    acf_add_local_field_group([
        'key'      => 'group_nh_discounts',
        'title'    => 'Блок: Скидки',
        'fields'   => [
            [
                'key'           => 'field_nh_discounts_eyebrow',
                'label'         => 'Надзаголовок',
                'name'          => 'nh_discounts_eyebrow',
                'type'          => 'text',
                'default_value' => '[ DISCOUNTS ]',
                'placeholder'   => '[ DISCOUNTS ]',
            ],
            [
                'key'           => 'field_nh_discounts_title',
                'label'         => 'Заголовок',
                'name'          => 'nh_discounts_title',
                'type'          => 'text',
                'default_value' => 'We love our loyal clients',
            ],
            [
                'key'           => 'field_nh_discounts_text',
                'label'         => 'Описание',
                'name'          => 'nh_discounts_text',
                'type'          => 'textarea',
                'rows'          => 3,
                'new_lines'     => 'br',
                'default_value' => 'Enjoy exclusive bonuses, discounts, and rewards as you continue your journey with us. Every visit or purchase brings you closer to special benefits.',
            ],
            [
                'key'          => 'field_nh_discounts_items',
                'label'        => 'Карточки скидок',
                'name'         => 'nh_discounts_items',
                'type'         => 'repeater',
                'min'          => 1,
                'layout'       => 'block',
                'button_label' => 'Добавить карточку скидки',
                'sub_fields'   => [
                    [
                        'key'      => 'field_nh_discount_item_title',
                        'label'    => 'Заголовок',
                        'name'     => 'item_title',
                        'type'     => 'text',
                        'required' => 1,
                    ],
                    [
                        'key'       => 'field_nh_discount_item_text',
                        'label'     => 'Описание',
                        'name'      => 'item_text',
                        'type'      => 'textarea',
                        'rows'      => 3,
                        'new_lines' => 'br',
                    ],
                    [
                        'key'           => 'field_nh_discount_item_image',
                        'label'         => 'Изображение',
                        'name'          => 'item_image',
                        'type'          => 'image',
                        'return_format' => 'array',
                        'preview_size'  => 'medium',
                        'required'      => 1,
                    ],
                    [
                        'key'           => 'field_nh_discount_item_cta_text',
                        'label'         => 'Текст CTA-кнопки',
                        'name'          => 'item_cta_text',
                        'type'          => 'text',
                        'default_value' => 'BOOK AN APPOINTMENT',
                    ],
                    [
                        'key'          => 'field_nh_discount_item_popup_label',
                        'label'        => 'Метка для popup-заявки',
                        'name'         => 'item_popup_label',
                        'type'         => 'text',
                        'instructions' => 'Служебная метка, которая отправляется вместе с popup-формой и помогает понять, какая скидка вызвала заявку.',
                    ],
                    [
                        'key'   => 'field_nh_discount_item_telegram',
                        'label' => 'Ссылка Telegram',
                        'name'  => 'item_telegram',
                        'type'  => 'url',
                    ],
                    [
                        'key'   => 'field_nh_discount_item_instagram',
                        'label' => 'Ссылка Instagram',
                        'name'  => 'item_instagram',
                        'type'  => 'url',
                    ],
                    [
                        'key'   => 'field_nh_discount_item_whatsapp',
                        'label' => 'Ссылка WhatsApp',
                        'name'  => 'item_whatsapp',
                        'type'  => 'url',
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'block',
                    'operator' => '==',
                    'value'    => 'acf/nh-discounts',
                ],
            ],
        ],
        'active' => true,
    ]);
}
add_action('acf/init', 'nice_hair_register_discounts_acf_fields', 20);
