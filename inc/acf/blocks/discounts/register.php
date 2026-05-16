<?php

declare(strict_types=1);

function nice_hair_register_discounts_acf_block(): void
{
    if (! function_exists('acf_register_block_type')) {
        return;
    }

    acf_register_block_type([
        'name'            => 'nh-discounts',
        'title'           => __('Скидки', 'nice-hair'),
        'description'     => __('Карточки скидок с CTA-кнопкой и ссылками на соцсети.', 'nice-hair'),
        'category'        => 'nice-hair',
        'icon'            => 'tag',
        'keywords'        => ['discounts', 'offers', 'promo', 'cards', 'скидки', 'акции', 'карточки'],
        'render_template' => 'template-parts/blocks/discounts/discounts.php',
        'mode'            => 'auto',
        'supports'        => [
            'align'  => false,
            'anchor' => true,
            'jsx'    => false,
        ],
    ]);
}
add_action('acf/init', 'nice_hair_register_discounts_acf_block');
