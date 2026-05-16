<?php

declare(strict_types=1);

function nice_hair_register_results_acf_block(): void
{
    if (! function_exists('acf_register_block_type')) {
        return;
    }

    acf_register_block_type([
        'name'            => 'nh-results',
        'title'           => __('Слайдер результатов', 'nice-hair'),
        'description'     => __('Слайдер результатов до/после с переключателем внутри карточки.', 'nice-hair'),
        'category'        => 'nice-hair',
        'icon'            => 'format-image',
        'keywords'        => ['results', 'before', 'after', 'slider', 'swiper'],
        'render_template' => 'template-parts/blocks/results/results.php',
        'mode'            => 'auto',
        'supports'        => [
            'align'  => false,
            'anchor' => true,
            'jsx'    => false,
        ],
    ]);
}
add_action('acf/init', 'nice_hair_register_results_acf_block');
