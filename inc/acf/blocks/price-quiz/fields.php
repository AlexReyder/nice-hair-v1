<?php

declare(strict_types=1);

function nice_hair_register_price_quiz_acf_fields(): void
{
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    if (function_exists('acf_get_local_field_group') && acf_get_local_field_group('group_nh_price_quiz')) {
        return;
    }

    acf_add_local_field_group([
        'key'      => 'group_nh_price_quiz',
        'title'    => 'Блок: Цена',
        'fields'   => [
            [
                'key'           => 'field_nh_pq_eyebrow',
                'label'         => 'Надзаголовок',
                'name'          => 'nh_pq_eyebrow',
                'type'          => 'text',
                'default_value' => '[ PRICE ]',
                'placeholder'   => '[ PRICE ]',
            ],
            [
                'key'           => 'field_nh_pq_title',
                'label'         => 'Заголовок',
                'name'          => 'nh_pq_title',
                'type'          => 'textarea',
                'rows'          => 2,
                'new_lines'     => 'br',
                'default_value' => 'Pricing is personalized — clear and honest',
            ],
            [
                'key'           => 'field_nh_pq_text',
                'label'         => 'Описание',
                'name'          => 'nh_pq_text',
                'type'          => 'textarea',
                'rows'          => 4,
                'new_lines'     => 'br',
                'default_value' => "The final cost depends on the length, volume, color, and structure of your hair.\nWe select the materials specifically for you and provide a transparent quote — no hidden fees or upselling.",
            ],
            [
                'key'           => 'field_nh_pq_bg_image',
                'label'         => 'Фоновое изображение карточки',
                'name'          => 'nh_pq_bg_image',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
            ],
            [
                'key'           => 'field_nh_pq_intro_text',
                'label'         => 'Вводный текст квиза',
                'name'          => 'nh_pq_intro_text',
                'type'          => 'textarea',
                'rows'          => 2,
                'new_lines'     => 'br',
                'default_value' => 'Take a quick quiz to get your personalized price estimate via WhatsApp.',
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'block',
                    'operator' => '==',
                    'value'    => 'acf/nh-price-quiz',
                ],
            ],
        ],
        'active' => true,
    ]);
}
add_action('acf/init', 'nice_hair_register_price_quiz_acf_fields', 20);
