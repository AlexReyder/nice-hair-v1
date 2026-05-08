<?php

declare(strict_types=1);

function nice_hair_register_block_category(array $categories): array
{
    array_unshift($categories, [
        'slug'  => 'nice-hair',
        'title' => __('Nice Hair', 'nice-hair'),
    ]);

    return $categories;
}
add_filter('block_categories_all', 'nice_hair_register_block_category');

function nice_hair_register_acf_blocks(): void
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
add_action('acf/init', 'nice_hair_register_acf_blocks');
