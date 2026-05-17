<?php

declare(strict_types=1);

function nice_hair_register_shop_assortment_acf_fields(): void
{
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    if (function_exists('acf_get_local_field_group') && acf_get_local_field_group('group_nh_shop_assortment')) {
        return;
    }

    acf_add_local_field_group([
        'key'      => 'group_nh_shop_assortment',
        'title'    => 'Блок: Ассортимент магазина',
        'fields'   => [
            [
                'key'           => 'field_nh_shop_assortment_eyebrow',
                'label'         => 'Надзаголовок',
                'name'          => 'nh_shop_assortment_eyebrow',
                'type'          => 'text',
                'default_value' => '[ ASSORTMENT ]',
                'placeholder'   => '[ ASSORTMENT ]',
            ],
            [
                'key'           => 'field_nh_shop_assortment_title',
                'label'         => 'Заголовок',
                'name'          => 'nh_shop_assortment_title',
                'type'          => 'text',
                'default_value' => 'Our Assortment',
            ],
            [
                'key'           => 'field_nh_shop_assortment_description',
                'label'         => 'Описание',
                'name'          => 'nh_shop_assortment_description',
                'type'          => 'textarea',
                'rows'          => 4,
                'new_lines'     => 'br',
                'default_value' => 'Whether you prefer to work with raw hair or want ready-to-install extensions, we provide both - premium hair in all formats.',
            ],
            [
                'key'           => 'field_nh_shop_assortment_anchor',
                'label'         => 'Якорь',
                'name'          => 'nh_shop_assortment_anchor',
                'type'          => 'text',
                'instructions'  => 'Необязательно. Используется только если стандартный якорь блока пуст.',
                'default_value' => 'shop-assortment',
            ],
            [
                'key'          => 'field_nh_shop_assortment_gallery',
                'label'        => 'Фотографии галереи',
                'name'         => 'nh_shop_assortment_gallery',
                'type'         => 'repeater',
                'layout'       => 'block',
                'collapsed'    => 'field_nh_shop_assortment_gallery_image',
                'button_label' => 'Добавить фотографию',
                'instructions' => 'Загрузите фотографии, которые будут показаны в слайдере блока Our Assortment. При клике фотография откроется через WooCommerce PhotoSwipe.',
                'sub_fields'   => [
                    [
                        'key'           => 'field_nh_shop_assortment_gallery_image',
                        'label'         => 'Фотография',
                        'name'          => 'item_image',
                        'type'          => 'image',
                        'return_format' => 'array',
                        'preview_size'  => 'medium',
                        'library'       => 'all',
                        'required'      => 1,
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'block',
                    'operator' => '==',
                    'value'    => 'acf/nh-shop-assortment',
                ],
            ],
        ],
        'active' => true,
    ]);
}
add_action('acf/init', 'nice_hair_register_shop_assortment_acf_fields', 20);
