<?php

declare(strict_types=1);

function nice_hair_register_results_gallery_shop_acf_block(): void
{
    if (! function_exists('acf_register_block_type')) {
        return;
    }

    acf_register_block_type([
        'name'            => 'nh-results-gallery-shop',
        'title'           => __('Results Gallery Shop', 'nice-hair'),
        'description'     => __('Галерея фотографий в стилистике блока Results для страницы Shop.', 'nice-hair'),
        'category'        => 'nice-hair',
        'icon'            => 'format-gallery',
        'keywords'        => ['results', 'gallery', 'shop', 'slider', 'photoswipe', 'результаты', 'галерея', 'магазин'],
        'render_template' => 'template-parts/blocks/results-gallery-shop/results-gallery-shop.php',
        'mode'            => 'auto',
        'supports'        => [
            'align'  => false,
            'anchor' => true,
            'jsx'    => false,
        ],
    ]);
}
add_action('acf/init', 'nice_hair_register_results_gallery_shop_acf_block');
