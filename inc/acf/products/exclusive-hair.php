<?php

declare(strict_types=1);

function nice_hair_register_product_exclusive_hair_acf_fields(): void
{
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
            'key'      => 'group_nh_product_exclusive_hair',
            'title'    => 'Товар: Exclusive Hair',
            'fields'   => [
                [
                    'key'               => 'field_nh_base_lot_price',
                    'label'             => 'Базовая цена лота',
                    'name'              => 'nh_base_lot_price',
                    'type'              => 'number',
                    'instructions'      => 'Базовая цена лота для уникальных товаров Exclusive Hair до доплаты за выбранную форму товара.',
                    'default_value'     => '',
                    'min'               => 0,
                    'step'              => 0.01,
                    'prepend'           => '$',
                ],
                [
                    'key'               => 'field_nh_fixed_weight_grams',
                    'label'             => 'Фиксированный вес (граммы)',
                    'name'              => 'nh_fixed_weight_grams',
                    'type'              => 'number',
                    'instructions'      => 'Фиксированный вес лота в граммах для расчета цены Exclusive Hair.',
                    'default_value'     => '',
                    'min'               => 0,
                    'step'              => 0.01,
                    'append'            => 'г',
                ],
                [
                    'key'               => 'field_nh_exclusive_use_custom_product_forms',
                    'label'             => 'Exclusive Hair: индивидуальные формы товара',
                    'name'              => 'nh_exclusive_use_custom_product_forms',
                    'type'              => 'true_false',
                    'instructions'      => 'Если включено, товар показывает только Bulk и формы из списка ниже. Применяется только к Exclusive Hair.',
                    'default_value'     => 0,
                    'ui'                => 1,
                ],
                [
                    'key'               => 'field_nh_exclusive_product_form_overrides',
                    'label'             => 'Exclusive Hair: настройки форм товара',
                    'name'              => 'nh_exclusive_product_form_overrides',
                    'type'              => 'repeater',
                    'instructions'      => 'Bulk доступен автоматически. Добавьте только дополнительные формы, разрешенные для этого товара. Оставьте цену за грамм пустой, чтобы использовать глобальную цену из Shop Pricing.',
                    'layout'            => 'table',
                    'button_label'      => 'Добавить форму товара',
                    'sub_fields'        => [
                        [
                            'key'           => 'field_nh_exclusive_product_form_key',
                            'label'         => 'Форма товара',
                            'name'          => 'product_form_key',
                            'type'          => 'select',
                            'choices'       => [],
                            'allow_null'    => 0,
                            'ui'            => 1,
                            'required'      => 1,
                            'wrapper'       => [
                                'width' => '55',
                            ],
                        ],
                        [
                            'key'           => 'field_nh_exclusive_product_form_price_override',
                            'label'         => 'Цена за грамм для этого товара',
                            'name'          => 'price_per_gram_override',
                            'type'          => 'number',
                            'instructions'  => 'Необязательная цена только для этого товара.',
                            'default_value' => '',
                            'min'           => 0,
                            'step'          => 0.01,
                            'prepend'       => '$',
                            'wrapper'       => [
                                'width' => '45',
                            ],
                        ],
                    ],
                    'conditional_logic' => [
                        [
                            [
                                'field'    => 'field_nh_exclusive_use_custom_product_forms',
                                'operator' => '==',
                                'value'    => '1',
                            ],
                        ],
                    ],
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
            'menu_order'         => 12,
        ]);
}
add_action('acf/init', 'nice_hair_register_product_exclusive_hair_acf_fields');