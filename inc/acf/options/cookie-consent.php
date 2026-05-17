<?php

declare(strict_types=1);

function nice_hair_register_cookie_consent_acf_options(): void
{
    if (! function_exists('acf_add_options_page')) {
        return;
    }

    acf_add_options_page([
        'page_title' => 'Настройки cookie',
        'menu_title' => 'Настройки cookie',
        'menu_slug'  => 'cookie-consent-settings',
        'capability' => 'edit_posts',
        'redirect'   => false,
        'icon_url'   => 'dashicons-privacy',
        'position'   => 36,
        'post_id'    => nice_hair_cookie_consent_post_id(),
    ]);

    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key'    => 'group_nh_cookie_consent',
        'title'  => 'Настройки cookie',
        'fields' => [
            [
                'key'           => 'field_nh_cookie_consent_enabled',
                'label'         => 'Включить cookie-плашку',
                'name'          => 'nh_cookie_consent_enabled',
                'type'          => 'true_false',
                'ui'            => 1,
                'default_value' => 1,
            ],
            [
                'key'           => 'field_nh_cookie_consent_version',
                'label'         => 'Версия cookie',
                'name'          => 'nh_cookie_consent_version',
                'type'          => 'number',
                'instructions'  => 'Увеличьте версию, если изменились правила cookie. После этого плашка снова появится у пользователей, которые уже сделали выбор.',
                'default_value' => 1,
                'min'           => 1,
                'step'          => 1,
            ],
            [
                'key'           => 'field_nh_cookie_consent_expiration_days',
                'label'         => 'Срок хранения согласия, дней',
                'name'          => 'nh_cookie_consent_expiration_days',
                'type'          => 'number',
                'instructions'  => 'Через сколько дней сохранённое согласие пользователя истечёт.',
                'default_value' => 180,
                'min'           => 1,
                'max'           => 730,
                'step'          => 1,
            ],
            [
                'key'   => 'field_nh_cookie_consent_content_tab',
                'label' => 'Контент',
                'name'  => '',
                'type'  => 'tab',
            ],
            [
                'key'           => 'field_nh_cookie_consent_title',
                'label'         => 'Заголовок плашки',
                'name'          => 'nh_cookie_consent_title',
                'type'          => 'text',
                'instructions'  => 'Текст выводится на сайте. Для англоязычной версии сайта оставьте значение на английском.',
                'default_value' => 'We use cookies',
            ],
            [
                'key'           => 'field_nh_cookie_consent_text',
                'label'         => 'Текст плашки',
                'name'          => 'nh_cookie_consent_text',
                'type'          => 'textarea',
                'instructions'  => 'Основное описание cookie, которое увидит пользователь на сайте.',
                'rows'          => 4,
                'new_lines'     => 'br',
                'default_value' => 'We use necessary cookies to make the website work. With your consent, we may also use analytics and marketing cookies to improve the website and personalise communication.',
            ],
            [
                'key'           => 'field_nh_cookie_consent_policy_link',
                'label'         => 'Ссылка на политику конфиденциальности / cookie',
                'name'          => 'nh_cookie_consent_policy_link',
                'type'          => 'link',
                'instructions'  => 'Ссылка на страницу Privacy Policy, Cookie Policy или объединённую юридическую страницу.',
                'return_format' => 'array',
            ],
            [
                'key'   => 'field_nh_cookie_consent_buttons_tab',
                'label' => 'Кнопки',
                'name'  => '',
                'type'  => 'tab',
            ],
            [
                'key'           => 'field_nh_cookie_consent_accept_all_label',
                'label'         => 'Текст кнопки принятия всех cookie',
                'name'          => 'nh_cookie_consent_accept_all_label',
                'type'          => 'text',
                'default_value' => 'Accept all',
            ],
            [
                'key'           => 'field_nh_cookie_consent_reject_optional_label',
                'label'         => 'Текст кнопки отказа от необязательных cookie',
                'name'          => 'nh_cookie_consent_reject_optional_label',
                'type'          => 'text',
                'default_value' => 'Reject optional',
            ],
            [
                'key'           => 'field_nh_cookie_consent_settings_label',
                'label'         => 'Текст кнопки настроек cookie',
                'name'          => 'nh_cookie_consent_settings_label',
                'type'          => 'text',
                'default_value' => 'Cookie settings',
            ],
            [
                'key'           => 'field_nh_cookie_consent_save_label',
                'label'         => 'Текст кнопки сохранения выбора',
                'name'          => 'nh_cookie_consent_save_label',
                'type'          => 'text',
                'default_value' => 'Save choices',
            ],
            [
                'key'           => 'field_nh_cookie_consent_back_label',
                'label'         => 'Текст кнопки возврата назад',
                'name'          => 'nh_cookie_consent_back_label',
                'type'          => 'text',
                'default_value' => 'Back',
            ],
            [
                'key'           => 'field_nh_cookie_consent_close_label',
                'label'         => 'Текст кнопки закрытия настроек',
                'name'          => 'nh_cookie_consent_close_label',
                'type'          => 'text',
                'instructions'  => 'Используется как aria-label для кнопки закрытия окна настроек.',
                'default_value' => 'Close cookie settings',
            ],
            [
                'key'   => 'field_nh_cookie_consent_categories_tab',
                'label' => 'Категории',
                'name'  => '',
                'type'  => 'tab',
            ],
            [
                'key'           => 'field_nh_cookie_consent_necessary_title',
                'label'         => 'Заголовок категории «Необходимые cookie»',
                'name'          => 'nh_cookie_consent_necessary_title',
                'type'          => 'text',
                'default_value' => 'Necessary cookies',
            ],
            [
                'key'           => 'field_nh_cookie_consent_necessary_text',
                'label'         => 'Описание категории «Необходимые cookie»',
                'name'          => 'nh_cookie_consent_necessary_text',
                'type'          => 'textarea',
                'instructions'  => 'Эта категория всегда включена и не может быть отключена пользователем.',
                'rows'          => 3,
                'new_lines'     => 'br',
                'default_value' => 'Required for cart, checkout, security and basic website functionality. These cannot be disabled.',
            ],
            [
                'key'           => 'field_nh_cookie_consent_analytics_enabled',
                'label'         => 'Показывать категорию «Аналитические cookie»',
                'name'          => 'nh_cookie_consent_analytics_enabled',
                'type'          => 'true_false',
                'ui'            => 1,
                'default_value' => 1,
            ],
            [
                'key'           => 'field_nh_cookie_consent_analytics_title',
                'label'         => 'Заголовок категории «Аналитические cookie»',
                'name'          => 'nh_cookie_consent_analytics_title',
                'type'          => 'text',
                'default_value' => 'Analytics cookies',
            ],
            [
                'key'           => 'field_nh_cookie_consent_analytics_text',
                'label'         => 'Описание категории «Аналитические cookie»',
                'name'          => 'nh_cookie_consent_analytics_text',
                'type'          => 'textarea',
                'rows'          => 3,
                'new_lines'     => 'br',
                'default_value' => 'Help us understand how visitors use the website so we can improve pages, navigation and content.',
            ],
            [
                'key'           => 'field_nh_cookie_consent_marketing_enabled',
                'label'         => 'Показывать категорию «Маркетинговые cookie»',
                'name'          => 'nh_cookie_consent_marketing_enabled',
                'type'          => 'true_false',
                'ui'            => 1,
                'default_value' => 1,
            ],
            [
                'key'           => 'field_nh_cookie_consent_marketing_title',
                'label'         => 'Заголовок категории «Маркетинговые cookie»',
                'name'          => 'nh_cookie_consent_marketing_title',
                'type'          => 'text',
                'default_value' => 'Marketing cookies',
            ],
            [
                'key'           => 'field_nh_cookie_consent_marketing_text',
                'label'         => 'Описание категории «Маркетинговые cookie»',
                'name'          => 'nh_cookie_consent_marketing_text',
                'type'          => 'textarea',
                'rows'          => 3,
                'new_lines'     => 'br',
                'default_value' => 'May be used to personalise advertising and measure campaign performance.',
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => 'cookie-consent-settings',
                ],
            ],
        ],
        'active' => true,
    ]);
}
add_action('acf/init', 'nice_hair_register_cookie_consent_acf_options', 15);
