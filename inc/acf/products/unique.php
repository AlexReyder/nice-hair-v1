<?php

declare(strict_types=1);

function nice_hair_register_product_unique_acf_fields(): void
{
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
            'key'      => 'group_nh_product_unique',
            'title'    => 'Товар: Уникальный товар',
            'fields'   => [
                [
                    'key'           => 'field_nh_unique_item',
                    'label'         => 'Уникальный товар',
                    'name'          => 'nh_unique_item',
                    'type'          => 'true_false',
                    'instructions'  => 'Включите для товаров, которые можно купить только в одном экземпляре.',
                    'default_value' => 0,
                    'ui'            => 1,
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
            'menu_order'         => 11,
        ]);
}
add_action('acf/init', 'nice_hair_register_product_unique_acf_fields');