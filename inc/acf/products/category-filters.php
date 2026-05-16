<?php

declare(strict_types=1);

function nice_hair_register_product_category_filters_acf_fields(): void
{
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
            'key'      => 'group_nh_product_category_filters',
            'title'    => 'Настройки фильтров товаров',
            'fields'   => [
                [
                    'key'           => 'field_nh_category_filter_mode',
                    'label'         => 'Режим фильтров',
                    'name'          => 'nh_category_filter_mode',
                    'type'          => 'radio',
                    'choices'       => [
                        'inherit'  => 'Наследовать от родительской категории',
                        'custom'   => 'Настроить фильтры',
                        'disabled' => 'Отключить фильтры',
                    ],
                    'default_value' => 'inherit',
                    'layout'        => 'vertical',
                    'return_format' => 'value',
                    'instructions'  => 'Подкатегории могут наследовать фильтры от ближайшего родителя, переопределить их или отключить полностью.',
                ],
                [
                    'key'               => 'field_nh_category_filters',
                    'label'             => 'Фильтры',
                    'name'              => 'nh_category_filters',
                    'type'              => 'repeater',
                    'layout'            => 'block',
                    'button_label'      => 'Добавить фильтр',
                    'instructions'      => 'Добавьте атрибуты WooCommerce, по которым товары этой категории должны фильтроваться.',
                    'conditional_logic' => [
                        [
                            [
                                'field'    => 'field_nh_category_filter_mode',
                                'operator' => '==',
                                'value'    => 'custom',
                            ],
                        ],
                    ],
                    'sub_fields'        => [
                        [
                            'key'          => 'field_nh_category_filter_taxonomy',
                            'label'        => 'Атрибут',
                            'name'         => 'filter_taxonomy',
                            'type'         => 'select',
                            'choices'      => [],
                            'allow_null'   => 0,
                            'ui'           => 1,
                            'required'     => 1,
                            'wrapper'      => [
                                'width' => '45',
                            ],
                        ],
                        [
                            'key'          => 'field_nh_category_filter_label',
                            'label'        => 'Название фильтра',
                            'name'         => 'filter_label',
                            'type'         => 'text',
                            'instructions' => 'Можно оставить пустым, тогда будет использовано название атрибута WooCommerce.',
                            'wrapper'      => [
                                'width' => '55',
                            ],
                        ],
                    ],
                ],
            ],
            'location' => [
                [
                    [
                        'param'    => 'taxonomy',
                        'operator' => '==',
                        'value'    => 'product_cat',
                    ],
                ],
            ],
            'position'              => 'normal',
            'style'                 => 'default',
            'label_placement'       => 'top',
            'instruction_placement' => 'label',
            'active'                => true,
            'menu_order'            => 8,
        ]);
}
add_action('acf/init', 'nice_hair_register_product_category_filters_acf_fields');
