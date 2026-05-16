<?php

declare(strict_types=1);

function nice_hair_register_faq_acf_block(): void
{
    if (! function_exists('acf_register_block_type')) {
        return;
    }

    acf_register_block_type([
        'name'            => 'nh-faq',
        'title'           => __('FAQ блок', 'nice-hair'),
        'description'     => __('FAQ блок-аккордеон с двухколоночной сеткой.', 'nice-hair'),
        'category'        => 'nice-hair',
        'icon'            => 'editor-help',
        'keywords'        => ['faq', 'вопросы', 'аккордеон'],
        'render_template' => 'template-parts/blocks/faq/faq.php',
        'mode'            => 'auto',
        'supports'        => [
            'align'  => false,
            'anchor' => true,
            'jsx'    => false,
        ],
    ]);
}
add_action('acf/init', 'nice_hair_register_faq_acf_block');
