<?php

declare(strict_types=1);

function nice_hair_register_price_quiz_acf_block(): void
{
    if (! function_exists('acf_register_block_type')) {
        return;
    }

    acf_register_block_type([
        'name'            => 'nh-price-quiz',
        'title'           => __('Блок цены', 'nice-hair'),
        'description'     => __('Блок цены с квизом, загрузкой фото и отправкой заявки через WhatsApp.', 'nice-hair'),
        'category'        => 'nice-hair',
        'icon'            => 'forms',
        'keywords'        => ['price', 'quiz', 'form', 'pricing', 'calculator', 'цена', 'квиз', 'форма'],
        'render_template' => 'template-parts/blocks/price-quiz/price-quiz.php',
        'mode'            => 'auto',
        'supports'        => [
            'align'  => false,
            'anchor' => true,
            'jsx'    => false,
        ],
    ]);
}
add_action('acf/init', 'nice_hair_register_price_quiz_acf_block');
