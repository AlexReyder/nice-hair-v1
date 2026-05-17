<?php

declare(strict_types=1);

function nice_hair_register_results_gallery_shop_acf_fields(): void
{
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    if (function_exists('acf_get_local_field_group') && acf_get_local_field_group('group_nh_results_gallery_shop')) {
        return;
    }

    acf_add_local_field_group([
        'key'      => 'group_nh_results_gallery_shop',
        'title'    => 'Блок: Results Gallery Shop',
        'fields'   => [
            [
                'key'           => 'field_nh_results_gallery_shop_eyebrow',
                'label'         => 'Надзаголовок',
                'name'          => 'nh_results_gallery_shop_eyebrow',
                'type'          => 'text',
                'default_value' => '[ RESULTS ]',
                'placeholder'   => '[ RESULTS ]',
            ],
            [
                'key'           => 'field_nh_results_gallery_shop_title',
                'label'         => 'Заголовок',
                'name'          => 'nh_results_gallery_shop_title',
                'type'          => 'textarea',
                'rows'          => 2,
                'new_lines'     => 'br',
                'default_value' => 'Results That Speak for Themselves',
            ],
            [
                'key'           => 'field_nh_results_gallery_shop_text_1',
                'label'         => 'Абзац 1',
                'name'          => 'nh_results_gallery_shop_text_1',
                'type'          => 'textarea',
                'rows'          => 3,
                'new_lines'     => 'br',
                'default_value' => 'Any length. Any volume. No visible extensions. Our techniques ensure a flawless, natural finish — no one will ever guess your hair is extended. Minimum natural hair length required: from 5 cm.',
            ],
            [
                'key'           => 'field_nh_results_gallery_shop_text_2',
                'label'         => 'Абзац 2',
                'name'          => 'nh_results_gallery_shop_text_2',
                'type'          => 'textarea',
                'rows'          => 3,
                'new_lines'     => 'br',
                'default_value' => 'With a curated stock of over 10,000 hair bundles in all colors and textures, we create custom blends to achieve the perfect shade. The result is a natural, dimensional color effect — without visiting a colorist.',
            ],
            [
                'key'           => 'field_nh_results_gallery_shop_anchor',
                'label'         => 'Якорь',
                'name'          => 'nh_results_gallery_shop_anchor',
                'type'          => 'text',
                'instructions'  => 'Необязательно. Используется только если стандартный якорь блока пуст.',
                'default_value' => 'shop-results-gallery',
            ],
            [
                'key'          => 'field_nh_results_gallery_shop_items',
                'label'        => 'Фотографии галереи',
                'name'         => 'nh_results_gallery_shop_items',
                'type'         => 'repeater',
                'layout'       => 'block',
                'collapsed'    => 'field_nh_results_gallery_shop_item_image',
                'button_label' => 'Добавить фотографию',
                'instructions' => 'Загрузите фотографии, которые будут показаны в слайдере. При клике фотография откроется на весь экран через WooCommerce PhotoSwipe.',
                'sub_fields'   => [
                    [
                        'key'           => 'field_nh_results_gallery_shop_item_image',
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
                    'value'    => 'acf/nh-results-gallery-shop',
                ],
            ],
        ],
        'active' => true,
    ]);
}
add_action('acf/init', 'nice_hair_register_results_gallery_shop_acf_fields', 20);
