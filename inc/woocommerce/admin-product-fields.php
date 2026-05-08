<?php

declare(strict_types=1);

function nice_hair_admin_product_field_family_priority(): array
{
    return [
        'custom_hair',
        'exclusive_hair',
        'ready_to_install',
        'keratin',
        'tools',
    ];
}

function nice_hair_admin_product_field_groups(): array
{
    return [
        'group_nh_product_common' => [
            'default',
            'tools',
            'keratin',
            'ready_to_install',
            'exclusive_hair',
            'custom_hair',
        ],
        'group_nh_product_unique' => [
            'ready_to_install',
            'exclusive_hair',
        ],
        'group_nh_product_exclusive_hair' => [
            'exclusive_hair',
        ],
        'group_nh_product_custom_hair' => [
            'custom_hair',
        ],
        'group_nh_product_custom_hair_colors' => [
            'custom_hair',
        ],
    ];
}

function nice_hair_admin_is_product_edit_screen(): bool
{
    if (! is_admin()) {
        return false;
    }

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;

    if ($screen instanceof WP_Screen && $screen->post_type === 'product') {
        return true;
    }

    $post_type = isset($_GET['post_type'])
        ? sanitize_key(wp_unslash((string) $_GET['post_type']))
        : '';

    if ($post_type === 'product') {
        return true;
    }

    $post_id = isset($_GET['post'])
        ? absint(wp_unslash((string) $_GET['post']))
        : 0;

    return $post_id > 0 && get_post_type($post_id) === 'product';
}

function nice_hair_admin_resolve_product_family_from_term_ids(array $term_ids): string
{
    $families = [];

    foreach ($term_ids as $term_id) {
        $term = get_term((int) $term_id, 'product_cat');

        if (! $term instanceof WP_Term) {
            continue;
        }

        $family = function_exists('nice_hair_get_product_category_family')
            ? nice_hair_get_product_category_family($term)
            : '';

        if (is_string($family) && $family !== '') {
            $families[] = $family;
        }
    }

    $families = array_values(array_unique($families));

    foreach (nice_hair_admin_product_field_family_priority() as $family) {
        if (in_array($family, $families, true)) {
            return $family;
        }
    }

    return 'default';
}

function nice_hair_admin_get_current_product_field_family(): string
{
    if (! nice_hair_admin_is_product_edit_screen()) {
        return 'default';
    }

    $post_id = isset($_GET['post'])
        ? absint(wp_unslash((string) $_GET['post']))
        : 0;

    if ($post_id <= 0) {
        return 'default';
    }

    $term_ids = wp_get_post_terms($post_id, 'product_cat', ['fields' => 'ids']);

    return is_array($term_ids)
        ? nice_hair_admin_resolve_product_family_from_term_ids($term_ids)
        : 'default';
}

function nice_hair_admin_get_product_category_family_map(): array
{
    $terms = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
    ]);

    if (is_wp_error($terms) || ! is_array($terms)) {
        return [];
    }

    $map = [];

    foreach ($terms as $term) {
        if (! $term instanceof WP_Term) {
            continue;
        }

        $family = function_exists('nice_hair_get_product_category_family')
            ? nice_hair_get_product_category_family($term)
            : '';

        if (! is_string($family) || $family === '') {
            continue;
        }

        $map[(string) $term->term_id] = $family;
    }

    return $map;
}

function nice_hair_admin_product_field_body_class(string $classes): string
{
    if (! nice_hair_admin_is_product_edit_screen()) {
        return $classes;
    }

    $family = nice_hair_admin_get_current_product_field_family();
    $classes .= ' nh-product-admin-fields nh-product-family--' . sanitize_html_class($family);

    return $classes;
}
add_filter('admin_body_class', 'nice_hair_admin_product_field_body_class');

function nice_hair_admin_product_field_visibility_css(): void
{
    if (! nice_hair_admin_is_product_edit_screen()) {
        return;
    }
    ?>
    <style id="nice-hair-admin-product-fields-css">
        body.nh-product-admin-fields .acf-postbox[data-key="group_nh_product_unique"],
        body.nh-product-admin-fields #acf-group_nh_product_unique,
        body.nh-product-admin-fields .acf-postbox[data-key="group_nh_product_exclusive_hair"],
        body.nh-product-admin-fields #acf-group_nh_product_exclusive_hair,
        body.nh-product-admin-fields .acf-postbox[data-key="group_nh_product_custom_hair"],
        body.nh-product-admin-fields #acf-group_nh_product_custom_hair,
        body.nh-product-admin-fields .acf-postbox[data-key="group_nh_product_custom_hair_colors"],
        body.nh-product-admin-fields #acf-group_nh_product_custom_hair_colors {
            display: none !important;
        }

        body.nh-product-admin-fields.nh-product-family--ready_to_install .acf-postbox[data-key="group_nh_product_unique"],
        body.nh-product-admin-fields.nh-product-family--ready_to_install #acf-group_nh_product_unique,
        body.nh-product-admin-fields.nh-product-family--exclusive_hair .acf-postbox[data-key="group_nh_product_unique"],
        body.nh-product-admin-fields.nh-product-family--exclusive_hair #acf-group_nh_product_unique,
        body.nh-product-admin-fields.nh-product-family--exclusive_hair .acf-postbox[data-key="group_nh_product_exclusive_hair"],
        body.nh-product-admin-fields.nh-product-family--exclusive_hair #acf-group_nh_product_exclusive_hair,
        body.nh-product-admin-fields.nh-product-family--custom_hair .acf-postbox[data-key="group_nh_product_custom_hair"],
        body.nh-product-admin-fields.nh-product-family--custom_hair #acf-group_nh_product_custom_hair,
        body.nh-product-admin-fields.nh-product-family--custom_hair .acf-postbox[data-key="group_nh_product_custom_hair_colors"],
        body.nh-product-admin-fields.nh-product-family--custom_hair #acf-group_nh_product_custom_hair_colors {
            display: block !important;
        }
    </style>
    <?php
}
add_action('admin_head-post.php', 'nice_hair_admin_product_field_visibility_css');
add_action('admin_head-post-new.php', 'nice_hair_admin_product_field_visibility_css');

function nice_hair_admin_enqueue_product_field_script(): void
{
    if (! nice_hair_admin_is_product_edit_screen()) {
        return;
    }

    $script_path = get_theme_file_path('/assets/js/admin-product-fields.js');
    $script_uri  = get_theme_file_uri('/assets/js/admin-product-fields.js');

    wp_enqueue_script(
        'nice-hair-admin-product-fields',
        $script_uri,
        [],
        file_exists($script_path) ? (string) filemtime($script_path) : null,
        true
    );

    wp_localize_script(
        'nice-hair-admin-product-fields',
        'nhProductAdminFields',
        [
            'defaultFamily'  => 'default',
            'currentFamily'  => nice_hair_admin_get_current_product_field_family(),
            'familyByTermId' => nice_hair_admin_get_product_category_family_map(),
            'familyPriority' => nice_hair_admin_product_field_family_priority(),
            'fieldGroups'    => nice_hair_admin_product_field_groups(),
        ]
    );
}
add_action('admin_enqueue_scripts', 'nice_hair_admin_enqueue_product_field_script');

function nice_hair_admin_render_product_field_hint(WP_Post $post): void
{
    if ($post->post_type !== 'product') {
        return;
    }
    ?>
    <div class="notice notice-info inline">
        <p>
            <strong>Поля товара зависят от категории.</strong>
            Сначала выберите категорию товара, затем заполните поля. Нерелевантные поля будут скрыты автоматически.
        </p>
    </div>
    <?php
}
add_action('edit_form_after_title', 'nice_hair_admin_render_product_field_hint');
