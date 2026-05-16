<?php

declare(strict_types=1);

function nice_hair_register_shop_categories_acf_block(): void
{
    if (! function_exists('acf_register_block_type')) {
        return;
    }

    acf_register_block_type([
        'name'            => 'nh-shop-categories',
        'title'           => __('Категории магазина', 'nice-hair'),
        'description'     => __('Карточки категорий в bento-сетке с фоновыми изображениями и ссылками.', 'nice-hair'),
        'category'        => 'nice-hair',
        'icon'            => 'category',
        'keywords'        => ['categories', 'shop', 'cards', 'grid', 'категории', 'магазин', 'карточки', 'сетка'],
        'render_template' => 'template-parts/blocks/shop-categories/shop-categories.php',
        'mode'            => 'auto',
        'supports'        => [
            'align'  => false,
            'anchor' => true,
            'jsx'    => false,
        ],
    ]);
}
add_action('acf/init', 'nice_hair_register_shop_categories_acf_block');
