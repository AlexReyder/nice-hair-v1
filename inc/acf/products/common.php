<?php

declare(strict_types=1);

function nice_hair_register_product_common_acf_fields(): void
{
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
            'key'      => 'group_nh_product_common',
            'title'    => 'Товар: Общие поля',
            'fields'   => [
                [
                    'key'          => 'field_nh_product_video_url',
                    'label'        => 'Ссылка на видео',
                    'name'         => 'nh_product_video_url',
                    'type'         => 'url',
                    'instructions' => 'Ссылка YouTube на видеообзор товара.',
                    'placeholder'  => 'https://www.youtube.com/watch?v=...',
                ],
                [
                    'key'          => 'field_nh_product_how_to_use_image',
                    'label'        => 'How to Use: изображение',
                    'name'         => 'nh_product_how_to_use_image',
                    'type'         => 'image',
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                    'instructions' => 'Изображение для popup-блока "How to use".',
                ],
                [
                    'key'          => 'field_nh_product_how_to_use_text',
                    'label'        => 'How to Use: описание',
                    'name'         => 'nh_product_how_to_use_text',
                    'type'         => 'textarea',
                    'rows'         => 4,
                    'instructions' => 'Короткое описание для popup-блока "How to use".',
                ],
            ],
            'location' => [
                [
                    [
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'product',
                    ],
                ],
            ],
            'position'           => 'normal',
            'style'              => 'default',
            'label_placement'    => 'top',
            'instruction_placement' => 'label',
            'active'             => true,
            'menu_order'         => 10,
        ]);
}
add_action('acf/init', 'nice_hair_register_product_common_acf_fields');