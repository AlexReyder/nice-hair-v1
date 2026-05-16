<?php

declare(strict_types=1);

function nice_hair_register_shop_categories_acf_fields(): void
{
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    if (function_exists('acf_get_local_field_group') && acf_get_local_field_group('group_nh_shop_categories')) {
        return;
    }

    acf_add_local_field_group([
        'key'      => 'group_nh_shop_categories',
        'title'    => 'Блок: Категории магазина',
        'fields'   => [
            [
                'key'           => 'field_nh_categories_eyebrow',
                'label'         => 'Надзаголовок',
                'name'          => 'nh_categories_eyebrow',
                'type'          => 'text',
                'default_value' => '[ КАТЕГОРИИ ]',
                'placeholder'   => '[ КАТЕГОРИИ ]',
            ],
            [
                'key'           => 'field_nh_categories_title',
                'label'         => 'Заголовок',
                'name'          => 'nh_categories_title',
                'type'          => 'text',
                'default_value' => 'Все категории в одном месте',
            ],
            [
                'key'           => 'field_nh_categories_text',
                'label'         => 'Описание',
                'name'          => 'nh_categories_text',
                'type'          => 'textarea',
                'rows'          => 4,
                'new_lines'     => 'br',
                'default_value' => 'Выберите то, что нужно сегодня: профессиональные салонные услуги или премиальные продукты. Один бренд, одно производство и единый стандарт качества.',
            ],
            [
                'key'          => 'field_nh_categories_items',
                'label'        => 'Карточки категорий',
                'name'         => 'nh_categories_items',
                'type'         => 'repeater',
                'min'          => 1,
                'layout'       => 'block',
                'button_label' => 'Добавить карточку категории',
                'sub_fields'   => [
                    [
                        'key'      => 'field_nh_category_item_title',
                        'label'    => 'Заголовок',
                        'name'     => 'item_title',
                        'type'     => 'text',
                        'required' => 1,
                    ],
                    [
                        'key'       => 'field_nh_category_item_text',
                        'label'     => 'Описание',
                        'name'      => 'item_text',
                        'type'      => 'textarea',
                        'rows'      => 3,
                        'new_lines' => 'br',
                    ],
                    [
                        'key'           => 'field_nh_category_item_image',
                        'label'         => 'Изображение',
                        'name'          => 'item_image',
                        'type'          => 'image',
                        'return_format' => 'array',
                        'preview_size'  => 'medium',
                        'required'      => 1,
                    ],
                    [
                        'key'   => 'field_nh_category_item_link',
                        'label' => 'Ссылка',
                        'name'  => 'item_link',
                        'type'  => 'url',
                    ],
                    [
                        'key'          => 'field_nh_category_item_cta_text',
                        'label'        => 'Текст кнопки',
                        'name'         => 'item_cta_text',
                        'type'         => 'text',
                        'instructions' => 'Необязательно. Если заполнено, на карточке появится CTA-кнопка.',
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'block',
                    'operator' => '==',
                    'value'    => 'acf/nh-shop-categories',
                ],
            ],
        ],
        'active' => true,
    ]);
}
add_action('acf/init', 'nice_hair_register_shop_categories_acf_fields', 20);
