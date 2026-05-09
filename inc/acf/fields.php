<?php

declare(strict_types=1);

function nice_hair_register_acf_fields(): void
{
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key'      => 'group_nh_faq',
        'title'    => 'Блок: FAQ',
        'fields'   => [
            [
                'key'           => 'field_nh_faq_eyebrow',
                'label'         => 'Надзаголовок',
                'name'          => 'nh_faq_eyebrow',
                'type'          => 'text',
                'default_value' => '[ FAQ ]',
                'placeholder'   => '[ FAQ ]',
            ],
            [
                'key'           => 'field_nh_faq_title',
                'label'         => 'Заголовок',
                'name'          => 'nh_faq_title',
                'type'          => 'text',
                'default_value' => "No stress \u2014 we've got answers",
            ],
            [
                'key'           => 'field_nh_faq_subtitle',
                'label'         => 'Подзаголовок',
                'name'          => 'nh_faq_subtitle',
                'type'          => 'textarea',
                'rows'          => 2,
                'new_lines'     => 'br',
                'default_value' => 'Here are the most common questions clients ask before getting extensions.',
            ],
            [
                'key'        => 'field_nh_faq_items',
                'label'      => 'Элементы FAQ',
                'name'       => 'nh_faq_items',
                'type'       => 'repeater',
                'min'        => 1,
                'layout'     => 'block',
                'sub_fields' => [
                    [
                        'key'      => 'field_nh_faq_question',
                        'label'    => 'Вопрос',
                        'name'     => 'question',
                        'type'     => 'text',
                        'required' => 1,
                    ],
                    [
                        'key'      => 'field_nh_faq_answer',
                        'label'    => 'Ответ',
                        'name'     => 'answer',
                        'type'     => 'textarea',
                        'required' => 1,
                        'rows'     => 3,
                        'new_lines' => 'br',
                    ],
                    [
                        'key'           => 'field_nh_faq_is_open',
                        'label'         => 'Открывать по умолчанию',
                        'name'          => 'is_open',
                        'type'          => 'true_false',
                        'default_value' => 0,
                        'ui'            => 1,
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'block',
                    'operator' => '==',
                    'value'    => 'acf/nh-faq',
                ],
            ],
        ],
        'active' => true,
    ]);

    acf_add_local_field_group([
        'key'      => 'group_nh_stylists',
        'title'    => 'Блок: Слайдер стилистов',
        'fields'   => [
            [
                'key'           => 'field_nh_stylists_eyebrow',
                'label'         => 'Надзаголовок',
                'name'          => 'nh_stylists_eyebrow',
                'type'          => 'text',
                'default_value' => '[ STYLISTS ]',
                'placeholder'   => '[ STYLISTS ]',
            ],
            [
                'key'           => 'field_nh_stylists_value',
                'label'         => 'Число',
                'name'          => 'nh_stylists_value',
                'type'          => 'text',
                'default_value' => '70+',
            ],
            [
                'key'           => 'field_nh_stylists_subtitle',
                'label'         => 'Подпись к числу',
                'name'          => 'nh_stylists_subtitle',
                'type'          => 'text',
                'default_value' => 'team members',
            ],
            [
                'key'           => 'field_nh_stylists_text',
                'label'         => 'Описание',
                'name'          => 'nh_stylists_text',
                'type'          => 'textarea',
                'rows'          => 3,
                'new_lines'     => 'br',
                'default_value' => 'A dedicated team of professionals who truly love what they do — and it shows in every detail.',
            ],
            [
                'key'           => 'field_nh_stylists_link',
                'label'         => 'Ссылка кнопки',
                'name'          => 'nh_stylists_link',
                'type'          => 'link',
                'return_format' => 'array',
            ],
            [
                'key'        => 'field_nh_stylists_items',
                'label'      => 'Стилисты',
                'name'       => 'nh_stylists_items',
                'type'       => 'repeater',
                'min'        => 1,
                'layout'     => 'block',
                'button_label' => 'Добавить стилиста',
                'sub_fields' => [
                    [
                        'key'           => 'field_nh_stylist_image',
                        'label'         => 'Портрет',
                        'name'          => 'item_image',
                        'type'          => 'image',
                        'return_format' => 'array',
                        'preview_size'  => 'medium',
                        'required'      => 1,
                    ],
                    [
                        'key'      => 'field_nh_stylist_name',
                        'label'    => 'Имя',
                        'name'     => 'item_title',
                        'type'     => 'text',
                        'required' => 1,
                    ],
                    [
                        'key'   => 'field_nh_stylist_exp',
                        'label' => 'Опыт',
                        'name'  => 'item_text',
                        'type'  => 'text',
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'block',
                    'operator' => '==',
                    'value'    => 'acf/nh-stylists',
                ],
            ],
        ],
        'active' => true,
    ]);

    acf_add_local_field_group([
        'key'      => 'group_nh_results',
        'title'    => 'Блок: Слайдер результатов',
        'fields'   => [
            [
                'key'           => 'field_nh_results_eyebrow',
                'label'         => 'Надзаголовок',
                'name'          => 'nh_results_eyebrow',
                'type'          => 'text',
                'default_value' => '[ RESULTS ]',
                'placeholder'   => '[ RESULTS ]',
            ],
            [
                'key'           => 'field_nh_results_title',
                'label'         => 'Заголовок',
                'name'          => 'nh_results_title',
                'type'          => 'textarea',
                'rows'          => 2,
                'new_lines'     => 'br',
                'default_value' => 'Results That Speak for Themselves',
            ],
            [
                'key'           => 'field_nh_results_text_1',
                'label'         => 'Абзац 1',
                'name'          => 'nh_results_text_1',
                'type'          => 'textarea',
                'rows'          => 3,
                'new_lines'     => 'br',
                'default_value' => 'Any length. Any volume. No visible extensions. Our techniques ensure a flawless, natural finish — no one will ever guess your hair is extended. Minimum natural hair length required: from 5 cm.',
            ],
            [
                'key'           => 'field_nh_results_text_2',
                'label'         => 'Абзац 2',
                'name'          => 'nh_results_text_2',
                'type'          => 'textarea',
                'rows'          => 3,
                'new_lines'     => 'br',
                'default_value' => 'With a curated stock of over 10,000 hair bundles in all colors and textures, we create custom blends to achieve the perfect shade. The result is a natural, dimensional color effect — without visiting a colorist.',
            ],
            // Result cards repeater moved to Options page "Results Settings"
            // (inc/acf/options.php → group_nh_results_shared).
            // Cards are shared across all pages using this block.
        ],
        'location' => [
            [
                [
                    'param'    => 'block',
                    'operator' => '==',
                    'value'    => 'acf/nh-results',
                ],
            ],
        ],
        'active' => true,
    ]);

    acf_add_local_field_group([
        'key'      => 'group_nh_price_quiz',
        'title'    => 'Блок: Цена',
        'fields'   => [
            [
                'key'           => 'field_nh_pq_eyebrow',
                'label'         => 'Надзаголовок',
                'name'          => 'nh_pq_eyebrow',
                'type'          => 'text',
                'default_value' => '[ PRICE ]',
                'placeholder'   => '[ PRICE ]',
            ],
            [
                'key'           => 'field_nh_pq_title',
                'label'         => 'Заголовок',
                'name'          => 'nh_pq_title',
                'type'          => 'textarea',
                'rows'          => 2,
                'new_lines'     => 'br',
                'default_value' => 'Pricing is personalized — clear and honest',
            ],
            [
                'key'           => 'field_nh_pq_text',
                'label'         => 'Описание',
                'name'          => 'nh_pq_text',
                'type'          => 'textarea',
                'rows'          => 4,
                'new_lines'     => 'br',
                'default_value' => "The final cost depends on the length, volume, color, and structure of your hair.\nWe select the materials specifically for you and provide a transparent quote — no hidden fees or upselling.",
            ],
            [
                'key'           => 'field_nh_pq_bg_image',
                'label'         => 'Фоновое изображение карточки',
                'name'          => 'nh_pq_bg_image',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
            ],
            [
                'key'           => 'field_nh_pq_intro_text',
                'label'         => 'Вводный текст квиза',
                'name'          => 'nh_pq_intro_text',
                'type'          => 'textarea',
                'rows'          => 2,
                'new_lines'     => 'br',
                'default_value' => 'Take a quick quiz to get your personalized price estimate via WhatsApp.',
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'block',
                    'operator' => '==',
                    'value'    => 'acf/nh-price-quiz',
                ],
            ],
        ],
        'active' => true,
    ]);
    acf_add_local_field_group([
        'key'      => 'group_nh_discounts',
        'title'    => 'Блок: Скидки',
        'fields'   => [
            [
                'key'           => 'field_nh_discounts_eyebrow',
                'label'         => 'Надзаголовок',
                'name'          => 'nh_discounts_eyebrow',
                'type'          => 'text',
                'default_value' => '[ DISCOUNTS ]',
                'placeholder'   => '[ DISCOUNTS ]',
            ],
            [
                'key'           => 'field_nh_discounts_title',
                'label'         => 'Заголовок',
                'name'          => 'nh_discounts_title',
                'type'          => 'text',
                'default_value' => 'We love our loyal clients',
            ],
            [
                'key'           => 'field_nh_discounts_text',
                'label'         => 'Описание',
                'name'          => 'nh_discounts_text',
                'type'          => 'textarea',
                'rows'          => 3,
                'new_lines'     => 'br',
                'default_value' => 'Enjoy exclusive bonuses, discounts, and rewards as you continue your journey with us. Every visit or purchase brings you closer to special benefits.',
            ],
            [
                'key'          => 'field_nh_discounts_items',
                'label'        => 'Карточки скидок',
                'name'         => 'nh_discounts_items',
                'type'         => 'repeater',
                'min'          => 1,
                'layout'       => 'block',
                'button_label' => 'Добавить карточку скидки',
                'sub_fields'   => [
                    [
                        'key'      => 'field_nh_discount_item_title',
                        'label'    => 'Заголовок',
                        'name'     => 'item_title',
                        'type'     => 'text',
                        'required' => 1,
                    ],
                    [
                        'key'       => 'field_nh_discount_item_text',
                        'label'     => 'Описание',
                        'name'      => 'item_text',
                        'type'      => 'textarea',
                        'rows'      => 3,
                        'new_lines' => 'br',
                    ],
                    [
                        'key'           => 'field_nh_discount_item_image',
                        'label'         => 'Изображение',
                        'name'          => 'item_image',
                        'type'          => 'image',
                        'return_format' => 'array',
                        'preview_size'  => 'medium',
                        'required'      => 1,
                    ],
                    [
                        'key'           => 'field_nh_discount_item_cta_text',
                        'label'         => 'Текст CTA-кнопки',
                        'name'          => 'item_cta_text',
                        'type'          => 'text',
                        'default_value' => 'BOOK AN APPOINTMENT',
                    ],
                    [
                        'key'          => 'field_nh_discount_item_popup_label',
                        'label'        => 'Метка для popup-заявки',
                        'name'         => 'item_popup_label',
                        'type'         => 'text',
                        'instructions' => 'Служебная метка, которая отправляется вместе с popup-формой и помогает понять, какая скидка вызвала заявку.',
                    ],
                    [
                        'key'   => 'field_nh_discount_item_telegram',
                        'label' => 'Ссылка Telegram',
                        'name'  => 'item_telegram',
                        'type'  => 'url',
                    ],
                    [
                        'key'   => 'field_nh_discount_item_instagram',
                        'label' => 'Ссылка Instagram',
                        'name'  => 'item_instagram',
                        'type'  => 'url',
                    ],
                    [
                        'key'   => 'field_nh_discount_item_whatsapp',
                        'label' => 'Ссылка WhatsApp',
                        'name'  => 'item_whatsapp',
                        'type'  => 'url',
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'block',
                    'operator' => '==',
                    'value'    => 'acf/nh-discounts',
                ],
            ],
        ],
        'active' => true,
    ]);
    acf_add_local_field_group([
        'key'      => 'group_nh_shop_categories',
        'title'    => 'Блок: Категории магазина',
        'fields'   => [
            [
                'key'           => 'field_nh_categories_eyebrow',
                'label'         => 'Надзаголовок',
                'name'          => 'nh_categories_eyebrow',
                'type'          => 'text',
                'default_value' => '[ КАТЕГОРИИ ]',
                'placeholder'   => '[ КАТЕГОРИИ ]',
            ],
            [
                'key'           => 'field_nh_categories_title',
                'label'         => 'Заголовок',
                'name'          => 'nh_categories_title',
                'type'          => 'text',
                'default_value' => 'Все категории в одном месте',
            ],
            [
                'key'           => 'field_nh_categories_text',
                'label'         => 'Описание',
                'name'          => 'nh_categories_text',
                'type'          => 'textarea',
                'rows'          => 4,
                'new_lines'     => 'br',
                'default_value' => 'Выберите то, что нужно сегодня: профессиональные салонные услуги или премиальные продукты. Один бренд, одно производство и единый стандарт качества.',
            ],
            [
                'key'          => 'field_nh_categories_items',
                'label'        => 'Карточки категорий',
                'name'         => 'nh_categories_items',
                'type'         => 'repeater',
                'min'          => 1,
                'layout'       => 'block',
                'button_label' => 'Добавить карточку категории',
                'sub_fields'   => [
                    [
                        'key'      => 'field_nh_category_item_title',
                        'label'    => 'Заголовок',
                        'name'     => 'item_title',
                        'type'     => 'text',
                        'required' => 1,
                    ],
                    [
                        'key'       => 'field_nh_category_item_text',
                        'label'     => 'Описание',
                        'name'      => 'item_text',
                        'type'      => 'textarea',
                        'rows'      => 3,
                        'new_lines' => 'br',
                    ],
                    [
                        'key'           => 'field_nh_category_item_image',
                        'label'         => 'Изображение',
                        'name'          => 'item_image',
                        'type'          => 'image',
                        'return_format' => 'array',
                        'preview_size'  => 'medium',
                        'required'      => 1,
                    ],
                    [
                        'key'   => 'field_nh_category_item_link',
                        'label' => 'Ссылка',
                        'name'  => 'item_link',
                        'type'  => 'url',
                    ],
                    [
                        'key'         => 'field_nh_category_item_cta_text',
                        'label'       => 'Текст кнопки',
                        'name'        => 'item_cta_text',
                        'type'        => 'text',
                        'instructions' => 'Необязательно. Если заполнено, на карточке появится CTA-кнопка.',
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'block',
                    'operator' => '==',
                    'value'    => 'acf/nh-shop-categories',
                ],
            ],
        ],
        'active' => true,
    ]);

    /* ── Product fields ─────────────────────────────────── */

    acf_add_local_field_group([
        'key'      => 'group_nh_shop_assortment',
        'title'    => 'Блок: Ассортимент магазина',
        'fields'   => [
            [
                'key'           => 'field_nh_shop_assortment_eyebrow',
                'label'         => 'Надзаголовок',
                'name'          => 'nh_shop_assortment_eyebrow',
                'type'          => 'text',
                'default_value' => '[ ASSORTMENT ]',
                'placeholder'   => '[ ASSORTMENT ]',
            ],
            [
                'key'           => 'field_nh_shop_assortment_title',
                'label'         => 'Заголовок',
                'name'          => 'nh_shop_assortment_title',
                'type'          => 'text',
                'default_value' => 'Our Assortment',
            ],
            [
                'key'           => 'field_nh_shop_assortment_description',
                'label'         => 'Описание',
                'name'          => 'nh_shop_assortment_description',
                'type'          => 'textarea',
                'rows'          => 4,
                'new_lines'     => 'br',
                'default_value' => 'Whether you prefer to work with raw hair or want ready-to-install extensions, we provide both - premium hair in all formats.',
            ],
            [
                'key'           => 'field_nh_shop_assortment_anchor',
                'label'         => 'Якорь',
                'name'          => 'nh_shop_assortment_anchor',
                'type'          => 'text',
                'instructions'  => 'Необязательно. Используется только если стандартный якорь блока пуст.',
                'default_value' => 'shop-assortment',
            ],
            [
                'key'           => 'field_nh_shop_assortment_selected_forms',
                'label'         => 'Формы товаров',
                'name'          => 'nh_shop_assortment_selected_forms',
                'type'          => 'checkbox',
                'choices'       => [],
                'layout'        => 'vertical',
                'return_format' => 'value',
                'toggle'        => 1,
                'allow_custom'  => 0,
                'save_custom'   => 0,
                'instructions'  => 'Оставьте пустым, чтобы показать все опубликованные формы товаров Custom Hair.',
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'block',
                    'operator' => '==',
                    'value'    => 'acf/nh-shop-assortment',
                ],
            ],
        ],
        'active' => true,
    ]);

    acf_add_local_field_group([
        'key'      => 'group_nh_product_category_filters',
        'title'    => 'Настройки фильтров товаров',
        'fields'   => [
            [
                'key'           => 'field_nh_category_filter_mode',
                'label'         => 'Режим фильтров',
                'name'          => 'nh_category_filter_mode',
                'type'          => 'radio',
                'choices'       => [
                    'inherit'  => 'Наследовать от родительской категории',
                    'custom'   => 'Настроить фильтры',
                    'disabled' => 'Отключить фильтры',
                ],
                'default_value' => 'inherit',
                'layout'        => 'vertical',
                'return_format' => 'value',
                'instructions'  => 'Подкатегории могут наследовать фильтры от ближайшего родителя, переопределить их или отключить полностью.',
            ],
            [
                'key'               => 'field_nh_category_filters',
                'label'             => 'Фильтры',
                'name'              => 'nh_category_filters',
                'type'              => 'repeater',
                'layout'            => 'block',
                'button_label'      => 'Добавить фильтр',
                'instructions'      => 'Добавьте атрибуты WooCommerce, по которым товары этой категории должны фильтроваться.',
                'conditional_logic' => [
                    [
                        [
                            'field'    => 'field_nh_category_filter_mode',
                            'operator' => '==',
                            'value'    => 'custom',
                        ],
                    ],
                ],
                'sub_fields'        => [
                    [
                        'key'          => 'field_nh_category_filter_taxonomy',
                        'label'        => 'Атрибут',
                        'name'         => 'filter_taxonomy',
                        'type'         => 'select',
                        'choices'      => [],
                        'allow_null'   => 0,
                        'ui'           => 1,
                        'required'     => 1,
                        'wrapper'      => [
                            'width' => '45',
                        ],
                    ],
                    [
                        'key'          => 'field_nh_category_filter_label',
                        'label'        => 'Название фильтра',
                        'name'         => 'filter_label',
                        'type'         => 'text',
                        'instructions' => 'Можно оставить пустым, тогда будет использовано название атрибута WooCommerce.',
                        'wrapper'      => [
                            'width' => '55',
                        ],
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'taxonomy',
                    'operator' => '==',
                    'value'    => 'product_cat',
                ],
            ],
        ],
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,
        'menu_order'            => 8,
    ]);

    acf_add_local_field_group([
        'key'      => 'group_nh_product_common',
        'title'    => 'Товар: Общие поля',
        'fields'   => [
            [
                'key'          => 'field_nh_product_video_url',
                'label'        => 'Ссылка на видео',
                'name'         => 'nh_product_video_url',
                'type'         => 'url',
                'instructions' => 'Ссылка YouTube на видеообзор товара.',
                'placeholder'  => 'https://www.youtube.com/watch?v=...',
            ],
            [
                'key'          => 'field_nh_product_how_to_use_image',
                'label'        => 'How to Use: изображение',
                'name'         => 'nh_product_how_to_use_image',
                'type'         => 'image',
                'return_format' => 'array',
                'preview_size' => 'medium',
                'instructions' => 'Изображение для popup-блока "How to use".',
            ],
            [
                'key'          => 'field_nh_product_how_to_use_text',
                'label'        => 'How to Use: описание',
                'name'         => 'nh_product_how_to_use_text',
                'type'         => 'textarea',
                'rows'         => 4,
                'instructions' => 'Короткое описание для popup-блока "How to use".',
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'product',
                ],
            ],
        ],
        'position'           => 'normal',
        'style'              => 'default',
        'label_placement'    => 'top',
        'instruction_placement' => 'label',
        'active'             => true,
        'menu_order'         => 10,
    ]);

    acf_add_local_field_group([
        'key'      => 'group_nh_product_unique',
        'title'    => 'Товар: Уникальный товар',
        'fields'   => [
            [
                'key'           => 'field_nh_unique_item',
                'label'         => 'Уникальный товар',
                'name'          => 'nh_unique_item',
                'type'          => 'true_false',
                'instructions'  => 'Включите для товаров, которые можно купить только в одном экземпляре.',
                'default_value' => 0,
                'ui'            => 1,
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'product',
                ],
            ],
        ],
        'position'           => 'normal',
        'style'              => 'default',
        'label_placement'    => 'top',
        'instruction_placement' => 'label',
        'active'             => true,
        'menu_order'         => 11,
    ]);

    acf_add_local_field_group([
        'key'      => 'group_nh_product_exclusive_hair',
        'title'    => 'Товар: Exclusive Hair',
        'fields'   => [
            [
                'key'               => 'field_nh_base_lot_price',
                'label'             => 'Базовая цена лота',
                'name'              => 'nh_base_lot_price',
                'type'              => 'number',
                'instructions'      => 'Базовая цена лота для уникальных товаров Exclusive Hair до доплаты за выбранную форму товара.',
                'default_value'     => '',
                'min'               => 0,
                'step'              => 0.01,
                'prepend'           => '$',
            ],
            [
                'key'               => 'field_nh_fixed_weight_grams',
                'label'             => 'Фиксированный вес (граммы)',
                'name'              => 'nh_fixed_weight_grams',
                'type'              => 'number',
                'instructions'      => 'Фиксированный вес лота в граммах для расчета цены Exclusive Hair.',
                'default_value'     => '',
                'min'               => 0,
                'step'              => 0.01,
                'append'            => 'г',
            ],
            [
                'key'               => 'field_nh_exclusive_use_custom_product_forms',
                'label'             => 'Exclusive Hair: индивидуальные формы товара',
                'name'              => 'nh_exclusive_use_custom_product_forms',
                'type'              => 'true_false',
                'instructions'      => 'Если включено, товар показывает только Bulk и формы из списка ниже. Применяется только к Exclusive Hair.',
                'default_value'     => 0,
                'ui'                => 1,
            ],
            [
                'key'               => 'field_nh_exclusive_product_form_overrides',
                'label'             => 'Exclusive Hair: настройки форм товара',
                'name'              => 'nh_exclusive_product_form_overrides',
                'type'              => 'repeater',
                'instructions'      => 'Bulk доступен автоматически. Добавьте только дополнительные формы, разрешенные для этого товара. Оставьте цену за грамм пустой, чтобы использовать глобальную цену из Shop Pricing.',
                'layout'            => 'table',
                'button_label'      => 'Добавить форму товара',
                'sub_fields'        => [
                    [
                        'key'           => 'field_nh_exclusive_product_form_key',
                        'label'         => 'Форма товара',
                        'name'          => 'product_form_key',
                        'type'          => 'select',
                        'choices'       => [],
                        'allow_null'    => 0,
                        'ui'            => 1,
                        'required'      => 1,
                        'wrapper'       => [
                            'width' => '55',
                        ],
                    ],
                    [
                        'key'           => 'field_nh_exclusive_product_form_price_override',
                        'label'         => 'Цена за грамм для этого товара',
                        'name'          => 'price_per_gram_override',
                        'type'          => 'number',
                        'instructions'  => 'Необязательная цена только для этого товара.',
                        'default_value' => '',
                        'min'           => 0,
                        'step'          => 0.01,
                        'prepend'       => '$',
                        'wrapper'       => [
                            'width' => '45',
                        ],
                    ],
                ],
                'conditional_logic' => [
                    [
                        [
                            'field'    => 'field_nh_exclusive_use_custom_product_forms',
                            'operator' => '==',
                            'value'    => '1',
                        ],
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'product',
                ],
            ],
        ],
        'position'           => 'normal',
        'style'              => 'default',
        'label_placement'    => 'top',
        'instruction_placement' => 'label',
        'active'             => true,
        'menu_order'         => 12,
    ]);

    acf_add_local_field_group([
        'key'      => 'group_nh_product_custom_hair',
        'title'    => 'Товар: Custom Hair',
        'fields'   => [
            [
                'key'          => 'field_nh_custom_hair_color_options',
                'label'        => 'Custom Hair: цветовые опции',
                'name'         => 'nh_custom_hair_color_options',
                'type'         => 'repeater',
                'instructions' => 'Каждая цветовая опция управляет одним главным изображением в конфигураторе. Остальная галерея остается общей для товара.',
                'layout'       => 'block',
                'button_label' => 'Добавить цвет',
                'sub_fields'   => [
                    [
                        'key'          => 'field_nh_custom_hair_color_label',
                        'label'        => 'Название цвета',
                        'name'         => 'color_label',
                        'type'         => 'text',
                        'instructions' => 'Видимое название, например: #24 или Golden Beige (12).',
                        'required'     => 1,
                        'wrapper'      => [
                            'width' => '34',
                        ],
                    ],
                    [
                        'key'          => 'field_nh_custom_hair_color_value',
                        'label'        => 'Значение цвета',
                        'name'         => 'color_value',
                        'type'         => 'text',
                        'instructions' => 'Стабильное внутреннее значение для корзины. Можно оставить понятным для человека.',
                        'required'     => 1,
                        'wrapper'      => [
                            'width' => '24',
                        ],
                    ],
                    [
                        'key'          => 'field_nh_custom_hair_color_group',
                        'label'        => 'Цветовая группа',
                        'name'         => 'color_group',
                        'type'         => 'select',
                        'choices'      => [],
                        'allow_null'   => 1,
                        'ui'           => 1,
                        'wrapper'      => [
                            'width' => '18',
                        ],
                    ],
                    [
                        'key'           => 'field_nh_custom_hair_color_main_image',
                        'label'         => 'Главное изображение',
                        'name'          => 'main_image',
                        'type'          => 'image',
                        'return_format' => 'array',
                        'preview_size'  => 'medium',
                        'instructions'  => 'Заменяет главное изображение, когда выбран этот цвет.',
                        'required'      => 1,
                        'wrapper'       => [
                            'width' => '24',
                        ],
                    ],
                ],
            ],
            [
                'key'          => 'field_nh_custom_hair_available_lengths',
                'label'        => 'Custom Hair: доступные длины',
                'name'         => 'nh_custom_hair_available_lengths',
                'type'         => 'checkbox',
                'instructions' => 'Выберите длины, доступные для этого конфигуратора.',
                'choices'      => [],
                'layout'       => 'horizontal',
                'toggle'       => 1,
                'return_format' => 'value',
            ],
            [
                'key'          => 'field_nh_custom_hair_available_qualities',
                'label'        => 'Custom Hair: доступные качества волос',
                'name'         => 'nh_custom_hair_available_qualities',
                'type'         => 'checkbox',
                'instructions' => 'Выберите качества волос, доступные для этого конфигуратора.',
                'choices'      => [],
                'layout'       => 'horizontal',
                'toggle'       => 1,
                'return_format' => 'value',
            ],
            [
                'key'          => 'field_nh_custom_hair_available_textures',
                'label'        => 'Custom Hair: доступные текстуры',
                'name'         => 'nh_custom_hair_available_textures',
                'type'         => 'checkbox',
                'instructions' => 'Выберите текстуры, доступные для этого конфигуратора.',
                'choices'      => [],
                'layout'       => 'horizontal',
                'toggle'       => 1,
                'return_format' => 'value',
            ],
            [
                'key'           => 'field_nh_custom_hair_min_weight_grams',
                'label'         => 'Custom Hair: минимальный вес (граммы)',
                'name'          => 'nh_custom_hair_min_weight_grams',
                'type'          => 'number',
                'default_value' => 30,
                'min'           => 0,
                'step'          => 1,
                'append'        => 'г',
            ],
            [
                'key'           => 'field_nh_custom_hair_weight_step_grams',
                'label'         => 'Custom Hair: шаг веса (граммы)',
                'name'          => 'nh_custom_hair_weight_step_grams',
                'type'          => 'number',
                'default_value' => 10,
                'min'           => 1,
                'step'          => 1,
                'append'        => 'г',
            ],
            [
                'key'           => 'field_nh_custom_hair_default_weight_grams',
                'label'         => 'Custom Hair: вес по умолчанию (граммы)',
                'name'          => 'nh_custom_hair_default_weight_grams',
                'type'          => 'number',
                'default_value' => 30,
                'min'           => 0,
                'step'          => 1,
                'append'        => 'г',
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'product',
                ],
            ],
        ],
        'position'           => 'normal',
        'style'              => 'default',
        'label_placement'    => 'top',
        'instruction_placement' => 'label',
        'active'             => true,
        'menu_order'         => 13,
    ]);

    acf_add_local_field_group([
        'key'      => 'group_nh_extension_type_how_to_use',
        'title'    => 'Extension Type: How to Use',
        'fields'   => [
            [
                'key'           => 'field_nh_extension_preview_image',
                'label'         => 'Preview Image',
                'name'          => 'nh_extension_preview_image',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
                'instructions'  => 'Image for product-form preview cards and selector thumbnails of this Extension Type.',
            ],
            [
                'key'           => 'field_nh_extension_how_to_use_image',
                'label'         => 'How to Use - Image',
                'name'          => 'nh_extension_how_to_use_image',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
                'instructions'  => 'Shared image for the "How to use" drawer of this Extension Type.',
            ],
            [
                'key'          => 'field_nh_extension_how_to_use_text',
                'label'        => 'How to Use - Description',
                'name'         => 'nh_extension_how_to_use_text',
                'type'         => 'textarea',
                'rows'         => 6,
                'instructions' => 'Shared description for the "How to use" drawer of this Extension Type.',
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'taxonomy',
                    'operator' => '==',
                    'value'    => 'pa_extension_type',
                ],
            ],
        ],
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,
        'menu_order'            => 15,
    ]);
}

function nice_hair_get_exclusive_product_form_override_choices(): array
{
    $choices = [];

    if (! function_exists('nice_hair_get_shop_product_form_options')) {
        return $choices;
    }

    foreach (nice_hair_get_shop_product_form_options() as $option) {
        if (! is_array($option)) {
            continue;
        }

        $key = isset($option['key']) ? nice_hair_normalize_shop_key((string) $option['key']) : '';

        if ($key === '' || $key === 'bulk') {
            continue;
        }

        $choices[$key] = (string) ($option['label'] ?? nice_hair_humanize_shop_key($key));
    }

    return $choices;
}

function nice_hair_load_exclusive_product_form_override_field(array $field): array
{
    $field['choices'] = nice_hair_get_exclusive_product_form_override_choices();

    return $field;
}

function nice_hair_load_shop_assortment_selected_forms_field(array $field): array
{
    $field['choices'] = function_exists('nice_hair_get_shop_assortment_form_filter_choices')
        ? nice_hair_get_shop_assortment_form_filter_choices()
        : [];

    return $field;
}

function nice_hair_get_custom_hair_field_choices(string $type): array
{
    return match ($type) {
        'lengths' => function_exists('nice_hair_get_custom_hair_length_choice_map')
            ? nice_hair_get_custom_hair_length_choice_map()
            : [
                '40' => '40 cm',
                '50' => '50 cm',
                '60' => '60 cm',
                '70' => '70 cm',
                '80' => '80 cm',
                '90' => '90 cm',
            ],
        'qualities' => function_exists('nice_hair_get_custom_hair_quality_choice_map')
            ? nice_hair_get_custom_hair_quality_choice_map()
            : [
                'lux' => 'Lux',
                'premium' => 'Premium',
            ],
        'textures' => function_exists('nice_hair_get_custom_hair_texture_choice_map')
            ? nice_hair_get_custom_hair_texture_choice_map()
            : [
                'soft_straight' => 'Soft straight',
                'silky_wavy' => 'Silky wavy',
                'amazing_curly' => 'Amazing curly',
            ],
        'color_groups' => function_exists('nice_hair_get_custom_hair_color_group_choice_map')
            ? nice_hair_get_custom_hair_color_group_choice_map()
            : [
                'light' => 'Light',
                'middle' => 'Middle',
                'dark' => 'Dark',
            ],
        default => [],
    };
}

function nice_hair_load_custom_hair_length_field(array $field): array
{
    $field['choices'] = nice_hair_get_custom_hair_field_choices('lengths');

    return $field;
}

function nice_hair_load_custom_hair_quality_field(array $field): array
{
    $field['choices'] = nice_hair_get_custom_hair_field_choices('qualities');

    return $field;
}

function nice_hair_load_custom_hair_texture_field(array $field): array
{
    $field['choices'] = nice_hair_get_custom_hair_field_choices('textures');

    return $field;
}

function nice_hair_load_custom_hair_color_group_field(array $field): array
{
    $field['choices'] = nice_hair_get_custom_hair_field_choices('color_groups');

    return $field;
}

function nice_hair_get_product_attribute_filter_choices(): array
{
    $choices = [];

    if (function_exists('wc_get_attribute_taxonomies')) {
        foreach ((array) wc_get_attribute_taxonomies() as $attribute) {
            $attribute_name = trim((string) ($attribute->attribute_name ?? ''));

            if ($attribute_name === '') {
                continue;
            }

            $taxonomy = function_exists('wc_attribute_taxonomy_name')
                ? wc_attribute_taxonomy_name($attribute_name)
                : 'pa_' . sanitize_title($attribute_name);
            $taxonomy = sanitize_key($taxonomy);

            if ($taxonomy === '' || ! str_starts_with($taxonomy, 'pa_')) {
                continue;
            }

            $label = trim((string) ($attribute->attribute_label ?? ''));

            if ($label === '' && function_exists('wc_attribute_label')) {
                $label = trim((string) wc_attribute_label($taxonomy));
            }

            if ($label === '') {
                $label = ucwords(str_replace(['_', '-'], ' ', preg_replace('/^pa_/', '', $taxonomy)));
            }

            $choices[$taxonomy] = sprintf('%s (%s)', $label, $taxonomy);
        }
    }

    if ($choices === []) {
        foreach (get_taxonomies([], 'objects') as $taxonomy => $taxonomy_object) {
            $taxonomy = sanitize_key((string) $taxonomy);

            if (! str_starts_with($taxonomy, 'pa_')) {
                continue;
            }

            $label = trim((string) ($taxonomy_object->label ?? ''));
            $choices[$taxonomy] = sprintf('%s (%s)', $label !== '' ? $label : $taxonomy, $taxonomy);
        }
    }

    asort($choices, SORT_NATURAL | SORT_FLAG_CASE);

    return $choices;
}

function nice_hair_load_product_category_filter_taxonomy_field(array $field): array
{
    $field['choices'] = nice_hair_get_product_attribute_filter_choices();

    return $field;
}

add_action('acf/init', 'nice_hair_register_acf_fields');
add_filter(
    'acf/load_field/key=field_nh_exclusive_product_form_key',
    'nice_hair_load_exclusive_product_form_override_field'
);
add_filter(
    'acf/load_field/key=field_nh_shop_assortment_selected_forms',
    'nice_hair_load_shop_assortment_selected_forms_field'
);
add_filter(
    'acf/load_field/key=field_nh_custom_hair_available_lengths',
    'nice_hair_load_custom_hair_length_field'
);
add_filter(
    'acf/load_field/key=field_nh_custom_hair_available_qualities',
    'nice_hair_load_custom_hair_quality_field'
);
add_filter(
    'acf/load_field/key=field_nh_custom_hair_available_textures',
    'nice_hair_load_custom_hair_texture_field'
);
add_filter(
    'acf/load_field/key=field_nh_custom_hair_color_group',
    'nice_hair_load_custom_hair_color_group_field'
);
add_filter(
    'acf/load_field/key=field_nh_category_filter_taxonomy',
    'nice_hair_load_product_category_filter_taxonomy_field'
);
