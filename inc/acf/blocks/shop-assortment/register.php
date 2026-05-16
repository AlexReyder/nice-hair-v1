<?php

declare(strict_types=1);

function nice_hair_register_shop_assortment_acf_block(): void
{
    if (! function_exists('acf_register_block_type')) {
        return;
    }

    acf_register_block_type([
        'name'            => 'nh-shop-assortment',
        'title'           => __('Ассортимент магазина', 'nice-hair'),
        'description'     => __('Слайдер форм товаров Custom Hair для страницы Shop.', 'nice-hair'),
        'category'        => 'nice-hair',
        'icon'            => 'images-alt2',
        'keywords'        => ['assortment', 'shop', 'custom hair', 'slider', 'forms', 'ассортимент', 'магазин', 'слайдер'],
        'render_template' => 'template-parts/blocks/shop-assortment/shop-assortment.php',
        'mode'            => 'auto',
        'supports'        => [
            'align'  => false,
            'anchor' => true,
            'jsx'    => false,
        ],
    ]);
}
add_action('acf/init', 'nice_hair_register_shop_assortment_acf_block');
