<?php

declare(strict_types=1);

function nice_hair_register_results_acf_fields(): void
{
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    if (function_exists('acf_get_local_field_group') && acf_get_local_field_group('group_nh_results')) {
        return;
    }

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
}
add_action('acf/init', 'nice_hair_register_results_acf_fields', 20);
