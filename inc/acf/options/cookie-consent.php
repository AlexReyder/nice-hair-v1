<?php

declare(strict_types=1);

function nice_hair_register_cookie_consent_acf_options(): void
{
    if (! function_exists('acf_add_options_page')) {
        return;
    }

    acf_add_options_page([
        'page_title' => 'Cookie Consent',
        'menu_title' => 'Cookie Consent',
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
        'title'  => 'Cookie Consent',
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
                'label'         => 'Версия согласия',
                'name'          => 'nh_cookie_consent_version',
                'type'          => 'number',
                'instructions'  => 'Увеличьте версию, если изменились правила cookie. Баннер появится у пользователей заново.',
                'default_value' => 1,
                'min'           => 1,
                'step'          => 1,
            ],
            [
                'key'           => 'field_nh_cookie_consent_expiration_days',
                'label'         => 'Срок хранения согласия, дней',
                'name'          => 'nh_cookie_consent_expiration_days',
                'type'          => 'number',
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
                'label'         => 'Заголовок',
                'name'          => 'nh_cookie_consent_title',
                'type'          => 'text',
                'default_value' => 'We use cookies',
            ],
            [
                'key'           => 'field_nh_cookie_consent_text',
                'label'         => 'Текст',
                'name'          => 'nh_cookie_consent_text',
                'type'          => 'textarea',
                'rows'          => 4,
                'new_lines'     => 'br',
                'default_value' => 'We use necessary cookies to make the website work. With your consent, we may also use analytics and marketing cookies to improve the website and personalise communication.',
            ],
            [
                'key'          => 'field_nh_cookie_consent_policy_link',
                'label'        => 'Ссылка на Privacy / Cookie Policy',
                'name'         => 'nh_cookie_consent_policy_link',
                'type'         => 'link',
                'return_format'=> 'array',
            ],
            [
                'key'   => 'field_nh_cookie_consent_buttons_tab',
                'label' => 'Кнопки',
                'name'  => '',
                'type'  => 'tab',
            ],
            [
                'key'           => 'field_nh_cookie_consent_accept_all_label',
                'label'         => 'Accept all label',
                'name'          => 'nh_cookie_consent_accept_all_label',
                'type'          => 'text',
                'default_value' => 'Accept all',
            ],
            [
                'key'           => 'field_nh_cookie_consent_reject_optional_label',
                'label'         => 'Reject optional label',
                'name'          => 'nh_cookie_consent_reject_optional_label',
                'type'          => 'text',
                'default_value' => 'Reject optional',
            ],
            [
                'key'           => 'field_nh_cookie_consent_settings_label',
                'label'         => 'Cookie settings label',
                'name'          => 'nh_cookie_consent_settings_label',
                'type'          => 'text',
                'default_value' => 'Cookie settings',
            ],
            [
                'key'           => 'field_nh_cookie_consent_save_label',
                'label'         => 'Save choices label',
                'name'          => 'nh_cookie_consent_save_label',
                'type'          => 'text',
                'default_value' => 'Save choices',
            ],
            [
                'key'           => 'field_nh_cookie_consent_back_label',
                'label'         => 'Back label',
                'name'          => 'nh_cookie_consent_back_label',
                'type'          => 'text',
                'default_value' => 'Back',
            ],
            [
                'key'           => 'field_nh_cookie_consent_close_label',
                'label'         => 'Close label',
                'name'          => 'nh_cookie_consent_close_label',
                'type'          => 'text',
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
                'label'         => 'Necessary title',
                'name'          => 'nh_cookie_consent_necessary_title',
                'type'          => 'text',
                'default_value' => 'Necessary cookies',
            ],
            [
                'key'           => 'field_nh_cookie_consent_necessary_text',
                'label'         => 'Necessary description',
                'name'          => 'nh_cookie_consent_necessary_text',
                'type'          => 'textarea',
                'rows'          => 3,
                'new_lines'     => 'br',
                'default_value' => 'Required for cart, checkout, security and basic website functionality. These cannot be disabled.',
            ],
            [
                'key'           => 'field_nh_cookie_consent_analytics_enabled',
                'label'         => 'Показывать Analytics cookies',
                'name'          => 'nh_cookie_consent_analytics_enabled',
                'type'          => 'true_false',
                'ui'            => 1,
                'default_value' => 1,
            ],
            [
                'key'           => 'field_nh_cookie_consent_analytics_title',
                'label'         => 'Analytics title',
                'name'          => 'nh_cookie_consent_analytics_title',
                'type'          => 'text',
                'default_value' => 'Analytics cookies',
            ],
            [
                'key'           => 'field_nh_cookie_consent_analytics_text',
                'label'         => 'Analytics description',
                'name'          => 'nh_cookie_consent_analytics_text',
                'type'          => 'textarea',
                'rows'          => 3,
                'new_lines'     => 'br',
                'default_value' => 'Help us understand how visitors use the website so we can improve pages, navigation and content.',
            ],
            [
                'key'           => 'field_nh_cookie_consent_marketing_enabled',
                'label'         => 'Показывать Marketing cookies',
                'name'          => 'nh_cookie_consent_marketing_enabled',
                'type'          => 'true_false',
                'ui'            => 1,
                'default_value' => 1,
            ],
            [
                'key'           => 'field_nh_cookie_consent_marketing_title',
                'label'         => 'Marketing title',
                'name'          => 'nh_cookie_consent_marketing_title',
                'type'          => 'text',
                'default_value' => 'Marketing cookies',
            ],
            [
                'key'           => 'field_nh_cookie_consent_marketing_text',
                'label'         => 'Marketing description',
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
