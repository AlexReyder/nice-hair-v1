<?php

declare(strict_types=1);

/**
 * Admin visibility rules for product ACF field groups.
 *
 * Product family is resolved from selected WooCommerce product categories.
 * JS receives the same fieldGroups map and updates visibility live when
 * product categories are changed in the editor.
 */

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
        /**
         * Common fields must be visible for every product category.
         */
        'group_nh_product_common' => [
            '*',
        ],

        /**
         * Unique product toggle is relevant only for unique-item product families.
         */
        'group_nh_product_unique' => [
            'ready_to_install',
            'exclusive_hair',
        ],

        /**
         * Exclusive Hair fields.
         */
        'group_nh_product_exclusive_hair' => [
            'exclusive_hair',
        ],

     /**
 * Custom Hair fields.
 *
 * group_nh_product_custom_hair_params:
 *   Length / Hair Quality / Texture / Weight settings from
 *   inc/acf/products/custom-hair/params.php.
 *
 * group_nh_product_custom_hair_colors:
 *   Product-level Custom Hair color selector from
 *   inc/acf/products/custom-hair/colors.php.
 */
       
        'group_nh_product_custom_hair_params' => [
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

function nice_hair_admin_build_acf_group_selectors(array $group_keys, string $body_selector = 'body.nh-product-admin-fields'): string
{
    $selectors = [];

    foreach ($group_keys as $group_key) {
        $group_key = sanitize_key((string) $group_key);

        if ($group_key === '') {
            continue;
        }

        $selectors[] = sprintf(
            '%s .acf-postbox[data-key="%s"]',
            $body_selector,
            esc_attr($group_key)
        );

        $selectors[] = sprintf(
            '%s #acf-%s',
            $body_selector,
            esc_attr($group_key)
        );
    }

    return implode(",\n        ", $selectors);
}

function nice_hair_admin_get_category_specific_group_keys(): array
{
    $group_keys = [];

    foreach (nice_hair_admin_product_field_groups() as $group_key => $allowed_families) {
        if (in_array('*', $allowed_families, true)) {
            continue;
        }

        $group_keys[] = (string) $group_key;
    }

    return array_values(array_unique(array_filter($group_keys)));
}

function nice_hair_admin_build_visible_group_selectors(): string
{
    $selectors = [];

    foreach (nice_hair_admin_product_field_groups() as $group_key => $allowed_families) {
        if (in_array('*', $allowed_families, true)) {
            continue;
        }

        foreach ($allowed_families as $family) {
            $family = sanitize_key((string) $family);

            if ($family === '') {
                continue;
            }

            $selector = nice_hair_admin_build_acf_group_selectors(
                [(string) $group_key],
                'body.nh-product-admin-fields.nh-product-family--' . sanitize_html_class($family)
            );

            if ($selector !== '') {
                $selectors[] = $selector;
            }
        }
    }

    return implode(",\n        ", array_values(array_unique($selectors)));
}

function nice_hair_admin_product_field_visibility_css(): void
{
    if (! nice_hair_admin_is_product_edit_screen()) {
        return;
    }

    $hidden_selectors = nice_hair_admin_build_acf_group_selectors(
        nice_hair_admin_get_category_specific_group_keys()
    );

    $visible_selectors = nice_hair_admin_build_visible_group_selectors();

    if ($hidden_selectors === '' && $visible_selectors === '') {
        return;
    }
    ?>
    <style id="nice-hair-admin-product-fields-css">
        <?php if ($hidden_selectors !== '') : ?>
        <?php echo $hidden_selectors; ?> {
            display: none !important;
        }
        <?php endif; ?>

        <?php if ($visible_selectors !== '') : ?>
        <?php echo $visible_selectors; ?> {
            display: block !important;
        }
        <?php endif; ?>
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