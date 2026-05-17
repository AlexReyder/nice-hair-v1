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
        'description'     => __('Галерея фотографий для блока Our Assortment на странице Shop.', 'nice-hair'),
        'category'        => 'nice-hair',
        'icon'            => 'images-alt2',
        'keywords'        => ['assortment', 'shop', 'gallery', 'slider', 'ассортимент', 'магазин', 'галерея', 'слайдер'],
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
