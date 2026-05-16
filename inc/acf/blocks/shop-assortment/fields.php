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
                'key'           => 'field_nh_shop_assortment_selected_forms',
                'label'         => 'Формы товаров',
                'name'          => 'nh_shop_assortment_selected_forms',
                'type'          => 'checkbox',
                'choices'       => [],
                'layout'        => 'vertical',
                'return_format' => 'value',
                'toggle'        => 1,
                'allow_custom'  => 0,
                'save_custom'   => 0,
                'instructions'  => 'Оставьте пустым, чтобы показать все опубликованные формы товаров Custom Hair.',
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
