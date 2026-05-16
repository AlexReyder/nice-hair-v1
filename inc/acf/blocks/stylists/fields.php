<?php

declare(strict_types=1);

function nice_hair_register_stylists_acf_fields(): void
{
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    if (function_exists('acf_get_local_field_group') && acf_get_local_field_group('group_nh_stylists')) {
        return;
    }

    acf_add_local_field_group([
        'key'      => 'group_nh_stylists',
        'title'    => 'Блок: Слайдер стилистов',
        'fields'   => [
            [
                'key'           => 'field_nh_stylists_eyebrow',
                'label'         => 'Надзаголовок',
                'name'          => 'nh_stylists_eyebrow',
                'type'          => 'text',
                'default_value' => '[ STYLISTS ]',
                'placeholder'   => '[ STYLISTS ]',
            ],
            [
                'key'           => 'field_nh_stylists_value',
                'label'         => 'Число',
                'name'          => 'nh_stylists_value',
                'type'          => 'text',
                'default_value' => '70+',
            ],
            [
                'key'           => 'field_nh_stylists_subtitle',
                'label'         => 'Подпись к числу',
                'name'          => 'nh_stylists_subtitle',
                'type'          => 'text',
                'default_value' => 'team members',
            ],
            [
                'key'           => 'field_nh_stylists_text',
                'label'         => 'Описание',
                'name'          => 'nh_stylists_text',
                'type'          => 'textarea',
                'rows'          => 3,
                'new_lines'     => 'br',
                'default_value' => 'A dedicated team of professionals who truly love what they do — and it shows in every detail.',
            ],
            [
                'key'           => 'field_nh_stylists_link',
                'label'         => 'Ссылка кнопки',
                'name'          => 'nh_stylists_link',
                'type'          => 'link',
                'return_format' => 'array',
            ],
            [
                'key'          => 'field_nh_stylists_items',
                'label'        => 'Стилисты',
                'name'         => 'nh_stylists_items',
                'type'         => 'repeater',
                'min'          => 1,
                'layout'       => 'block',
                'button_label' => 'Добавить стилиста',
                'sub_fields'   => [
                    [
                        'key'           => 'field_nh_stylist_image',
                        'label'         => 'Портрет',
                        'name'          => 'item_image',
                        'type'          => 'image',
                        'return_format' => 'array',
                        'preview_size'  => 'medium',
                        'required'      => 1,
                    ],
                    [
                        'key'      => 'field_nh_stylist_name',
                        'label'    => 'Имя',
                        'name'     => 'item_title',
                        'type'     => 'text',
                        'required' => 1,
                    ],
                    [
                        'key'   => 'field_nh_stylist_exp',
                        'label' => 'Опыт',
                        'name'  => 'item_text',
                        'type'  => 'text',
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'block',
                    'operator' => '==',
                    'value'    => 'acf/nh-stylists',
                ],
            ],
        ],
        'active' => true,
    ]);
}
add_action('acf/init', 'nice_hair_register_stylists_acf_fields', 20);
