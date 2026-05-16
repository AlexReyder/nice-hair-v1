<?php

declare(strict_types=1);

function nice_hair_register_stylists_acf_block(): void
{
    if (! function_exists('acf_register_block_type')) {
        return;
    }

    acf_register_block_type([
        'name'            => 'nh-stylists',
        'title'           => __('Слайдер стилистов', 'nice-hair'),
        'description'     => __('Слайдер стилистов со стрелками, вводной статистикой и CTA-кнопкой.', 'nice-hair'),
        'category'        => 'nice-hair',
        'icon'            => 'groups',
        'keywords'        => ['stylists', 'team', 'slider', 'swiper', 'стилисты', 'команда', 'слайдер'],
        'render_template' => 'template-parts/blocks/stylists/stylists.php',
        'mode'            => 'auto',
        'supports'        => [
            'align'  => false,
            'anchor' => true,
            'jsx'    => false,
        ],
    ]);
}
add_action('acf/init', 'nice_hair_register_stylists_acf_block');
