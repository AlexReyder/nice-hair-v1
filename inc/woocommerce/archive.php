<?php

declare(strict_types=1);

/**
 * Shop archive helpers and product-category filters.
 */

remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);

function nice_hair_get_current_product_category_term(): ?WP_Term
{
    if (! function_exists('is_product_category') || ! is_product_category()) {
        return null;
    }

    $term = get_queried_object();

    return $term instanceof WP_Term && $term->taxonomy === 'product_cat'
        ? $term
        : null;
}

function nice_hair_resolve_product_category_term(WP_Term|int|null $term = null): ?WP_Term
{
    if ($term instanceof WP_Term && $term->taxonomy === 'product_cat') {
        return $term;
    }

    if (is_int($term) && $term > 0) {
        $resolved = get_term($term, 'product_cat');

        return $resolved instanceof WP_Term ? $resolved : null;
    }

    return nice_hair_get_current_product_category_term();
}

function nice_hair_get_product_category_family(WP_Term|int|null $term = null): string
{
    $resolved = nice_hair_resolve_product_category_term($term);

    if (! $resolved instanceof WP_Term) {
        return '';
    }

    $term_ids = array_merge([$resolved->term_id], array_map('intval', get_ancestors($resolved->term_id, 'product_cat', 'taxonomy')));
    $keys = [];

    foreach ($term_ids as $term_id) {
        $current_term = get_term($term_id, 'product_cat');

        if (! $current_term instanceof WP_Term) {
            continue;
        }

        $keys[] = nice_hair_normalize_shop_key($current_term->slug);
        $keys[] = nice_hair_normalize_shop_key($current_term->name);
    }

    $keys = array_values(array_unique(array_filter($keys)));

    foreach (nice_hair_get_shop_family_aliases() as $family => $aliases) {
        foreach ($aliases as $alias) {
            if (in_array($alias, $keys, true)) {
                return $family;
            }
        }
    }

    return '';
}

function nice_hair_get_shop_archive_copy(): array
{
    return [
        'keratin' => [
            'eyebrow'     => 'Keratin collection',
            'description' => '',
        ],
        'tools' => [
            'eyebrow'     => 'Tools & accessories',
            'description' => '',
        ],
        'ready_to_install' => [
            'eyebrow'     => 'Ready to install',
            'description' => '',
        ],
        'exclusive_hair' => [
            'eyebrow'     => 'Exclusive hair',
            'description' => '',
        ],
        'custom_hair' => [
            'eyebrow'     => 'Custom hair',
            'description' => '',
        ],
        'default' => [
            'eyebrow'     => 'Category',
            'description' => '',
        ],
    ];
}

function nice_hair_is_keratin_parent_category(WP_Term|int|null $term = null): bool
{
    $resolved = nice_hair_resolve_product_category_term($term);

    return $resolved instanceof WP_Term
        && nice_hair_get_product_category_family($resolved) === 'keratin'
        && (int) $resolved->parent <= 0;
}

function nice_hair_is_keratin_child_category(WP_Term|int|null $term = null): bool
{
    $resolved = nice_hair_resolve_product_category_term($term);

    return $resolved instanceof WP_Term
        && nice_hair_get_product_category_family($resolved) === 'keratin'
        && (int) $resolved->parent > 0;
}

function nice_hair_get_shop_category_link_map(): array
{
    return [
        'hair_and_custom_hair_extensions'      => 'custom-hair',
        'custom_hair'                          => 'custom-hair',
        'custom_hair_extensions'               => 'custom-hair',
        'tools'                                => 'tools',
        'tools_and_accessories'                => 'tools',
        'keratin'                              => 'keratin',
        'exclusive_hair'                       => 'exclusive-hair',
        'ready_to_install'                     => 'ready-to-install',
        'ready_to_install_hair_extensions'     => 'ready-to-install',
        'ready_to_install_hair_extension'      => 'ready-to-install',
        'ready_to_install_extensions'          => 'ready-to-install',
    ];
}

function nice_hair_resolve_shop_category_card_link(string $link = '', string $title = ''): string
{
    $link = trim($link);

    if ($link !== '' && $link !== '#') {
        return $link;
    }

    $normalized_title = nice_hair_normalize_shop_key($title);
    $slug = nice_hair_get_shop_category_link_map()[$normalized_title] ?? '';

    if ($slug === '') {
        return $link;
    }

    $term = get_term_by('slug', $slug, 'product_cat');

    if (! $term instanceof WP_Term) {
        return $link;
    }

    $term_link = get_term_link($term);

    if (is_string($term_link) && ! is_wp_error($term_link)) {
        return $term_link;
    }

    return $link !== '' ? $link : '#';
}

function nice_hair_get_shop_catalog_menu_items(): array
{
    $items = [
        ['title' => 'Hair and Custom Hair Extensions', 'url' => ''],
        ['title' => 'Ready-to-Install Hair Extensions', 'url' => ''],
        ['title' => 'Keratin', 'url' => ''],
        ['title' => 'Tools', 'url' => ''],
        ['title' => 'Exclusive Hair', 'url' => ''],
    ];

    $resolved_items = [];

    foreach ($items as $item) {
        $title = trim((string) ($item['title'] ?? ''));

        if ($title === '') {
            continue;
        }

        $url = nice_hair_resolve_shop_category_card_link((string) ($item['url'] ?? ''), $title);

        if ($url === '' || $url === '#') {
            continue;
        }

        $resolved_items[] = [
            'title'  => $title,
            'url'    => $url,
            'target' => '',
        ];
    }

    return $resolved_items;
}

function nice_hair_get_shop_catalog_menu_default_field_rows(): array
{
    $rows = [];

    foreach (nice_hair_get_shop_catalog_menu_items() as $item) {
        $title = trim((string) ($item['title'] ?? ''));
        $url = trim((string) ($item['url'] ?? ''));

        if ($title === '' || $url === '' || $url === '#') {
            continue;
        }

        $rows[] = [
            'item_link' => [
                'title'  => $title,
                'url'    => $url,
                'target' => (string) ($item['target'] ?? ''),
            ],
        ];
    }

    return $rows;
}

function nice_hair_get_product_category_breadcrumbs(WP_Term|int|null $term = null): array
{
    $resolved = nice_hair_resolve_product_category_term($term);
    $crumbs = [];

    if (! $resolved instanceof WP_Term) {
        return $crumbs;
    }

  $shop_url = function_exists('wc_get_page_permalink')
    ? wc_get_page_permalink('shop')
    : nice_hair_get_page_url('shop', 'page-templates/page-shop.php');

if (! is_string($shop_url) || $shop_url === '') {
    $shop_url = home_url('/shop/');
}

$shop_url = trailingslashit($shop_url) . '#catalog';

$crumbs[] = [
    'label' => 'Shop',
    'url'   => $shop_url,
];

    $ancestor_ids = array_reverse(array_map('intval', get_ancestors($resolved->term_id, 'product_cat', 'taxonomy')));

    foreach ($ancestor_ids as $ancestor_id) {
        $ancestor = get_term($ancestor_id, 'product_cat');

        if (! $ancestor instanceof WP_Term) {
            continue;
        }

        $ancestor_link = get_term_link($ancestor);

        if (! is_string($ancestor_link) || $ancestor_link === '' || is_wp_error($ancestor_link)) {
            continue;
        }

        $crumbs[] = [
            'label' => $ancestor->name,
            'url'   => $ancestor_link,
        ];
    }

    $crumbs[] = [
        'label' => $resolved->name,
        'url'   => '',
    ];

    return $crumbs;
}

function nice_hair_get_shop_page_blocks(): array
{
    static $blocks = null;

    if (is_array($blocks)) {
        return $blocks;
    }

    if (! function_exists('wc_get_page_id')) {
        $blocks = [];

        return $blocks;
    }

    $shop_page_id = (int) wc_get_page_id('shop');

    if ($shop_page_id <= 0) {
        $blocks = [];

        return $blocks;
    }

    $content = (string) get_post_field('post_content', $shop_page_id);
    $blocks = $content !== '' && function_exists('parse_blocks')
        ? parse_blocks($content)
        : [];

    return $blocks;
}

function nice_hair_find_block_by_name(array $blocks, string $block_name): ?array
{
    foreach ($blocks as $block) {
        if (! is_array($block)) {
            continue;
        }

        if (($block['blockName'] ?? '') === $block_name) {
            return $block;
        }

        $inner_blocks = is_array($block['innerBlocks'] ?? null)
            ? $block['innerBlocks']
            : [];
        $found = nice_hair_find_block_by_name($inner_blocks, $block_name);

        if (is_array($found)) {
            return $found;
        }
    }

    return null;
}

function nice_hair_render_shop_page_block(string $block_name): string
{
    if (! function_exists('render_block')) {
        return '';
    }

    $block = nice_hair_find_block_by_name(nice_hair_get_shop_page_blocks(), $block_name);

    return is_array($block) ? (string) render_block($block) : '';
}

function nice_hair_render_block_patterns(array $pattern_slugs): string
{
    if (! function_exists('do_blocks')) {
        return '';
    }

    $markup = '';

    foreach ($pattern_slugs as $pattern_slug) {
        $pattern_slug = trim((string) $pattern_slug);

        if ($pattern_slug === '') {
            continue;
        }

        $markup .= sprintf('<!-- wp:pattern {"slug":"%s"} /-->', esc_attr($pattern_slug));
    }

    return $markup !== '' ? (string) do_blocks($markup) : '';
}

function nice_hair_get_archive_child_categories(WP_Term|int|null $term = null): array
{
    $resolved = nice_hair_resolve_product_category_term($term);

    if (! $resolved instanceof WP_Term) {
        return [];
    }

    $hide_empty = nice_hair_get_product_category_family($resolved) !== 'keratin';

    $terms = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => $hide_empty,
        'parent'     => $resolved->term_id,
        'orderby'    => 'menu_order',
        'order'      => 'ASC',
    ]);

    return is_array($terms) ? array_values(array_filter($terms, static fn (mixed $item): bool => $item instanceof WP_Term)) : [];
}

function nice_hair_get_archive_sibling_categories(WP_Term|int|null $term = null): array
{
    $resolved = nice_hair_resolve_product_category_term($term);

    if (! $resolved instanceof WP_Term || $resolved->parent <= 0) {
        return [];
    }

    $terms = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => true,
        'parent'     => (int) $resolved->parent,
        'orderby'    => 'menu_order',
        'order'      => 'ASC',
    ]);

    return is_array($terms) ? array_values(array_filter($terms, static fn (mixed $item): bool => $item instanceof WP_Term)) : [];
}

function nice_hair_get_legacy_archive_filter_definitions(WP_Term|int|null $term = null): array
{
    $family = nice_hair_get_product_category_family($term);

    $definitions = [
        'ready_to_install' => [
            [
                'param'    => 'filter_extension_type',
                'taxonomy' => 'pa_extension_type',
                'label'    => 'Hair extensions type',
            ],
            [
                'param'    => 'filter_hair_quality',
                'taxonomy' => 'pa_hair_quality',
                'label'    => 'Hair quality',
            ],
            [
                'param'    => 'filter_color_group',
                'taxonomy' => 'pa_color_group',
                'label'    => 'Color',
            ],
        ],
        'exclusive_hair' => [
            [
                'param'    => 'filter_texture',
                'taxonomy' => 'pa_texture',
                'label'    => 'Texture',
            ],
            [
                'param'    => 'filter_color_group',
                'taxonomy' => 'pa_color_group',
                'label'    => 'Color',
            ],
        ],
    ];

    $family_definitions = $definitions[$family] ?? [];

    return array_values(array_filter($family_definitions, static function (array $definition): bool {
        return taxonomy_exists($definition['taxonomy']);
    }));
}

function nice_hair_get_archive_filter_param_for_taxonomy(string $taxonomy): string
{
    $taxonomy = sanitize_key($taxonomy);
    $base = str_starts_with($taxonomy, 'pa_') ? substr($taxonomy, 3) : $taxonomy;

    return 'filter_' . sanitize_key($base);
}

function nice_hair_get_archive_filter_label_for_taxonomy(string $taxonomy): string
{
    $taxonomy = sanitize_key($taxonomy);
    $label = function_exists('wc_attribute_label') ? trim((string) wc_attribute_label($taxonomy)) : '';

    if ($label !== '' && $label !== $taxonomy) {
        return $label;
    }

    return ucwords(str_replace(['_', '-'], ' ', preg_replace('/^pa_/', '', $taxonomy)));
}

function nice_hair_normalize_archive_filter_definition(array $definition): ?array
{
    $taxonomy = sanitize_key((string) ($definition['taxonomy'] ?? $definition['filter_taxonomy'] ?? ''));

    if ($taxonomy === '' || ! str_starts_with($taxonomy, 'pa_') || ! taxonomy_exists($taxonomy)) {
        return null;
    }

    $label = trim((string) ($definition['label'] ?? $definition['filter_label'] ?? ''));

    if ($label === '') {
        $label = nice_hair_get_archive_filter_label_for_taxonomy($taxonomy);
    }

    return [
        'param'    => nice_hair_get_archive_filter_param_for_taxonomy($taxonomy),
        'taxonomy' => $taxonomy,
        'label'    => $label,
    ];
}

function nice_hair_get_product_category_filter_post_id(WP_Term $term): string
{
    return 'term_' . (int) $term->term_id;
}

function nice_hair_get_product_category_filter_mode(WP_Term $term): string
{
    if (! function_exists('get_field')) {
        return 'inherit';
    }

    $mode = sanitize_key((string) get_field('nh_category_filter_mode', nice_hair_get_product_category_filter_post_id($term)));

    return in_array($mode, ['inherit', 'custom', 'disabled'], true) ? $mode : 'inherit';
}

function nice_hair_get_product_category_custom_filter_definitions(WP_Term $term): array
{
    if (! function_exists('get_field')) {
        return [];
    }

    $rows = get_field('nh_category_filters', nice_hair_get_product_category_filter_post_id($term));

    if (! is_array($rows)) {
        return [];
    }

    $definitions = [];
    $seen_taxonomies = [];

    foreach ($rows as $row) {
        if (! is_array($row)) {
            continue;
        }

        $definition = nice_hair_normalize_archive_filter_definition($row);

        if (! is_array($definition) || in_array($definition['taxonomy'], $seen_taxonomies, true)) {
            continue;
        }

        $seen_taxonomies[] = $definition['taxonomy'];
        $definitions[] = $definition;
    }

    return $definitions;
}

function nice_hair_resolve_archive_filter_config(WP_Term|int|null $term = null): array
{
    static $cache = [];

    $resolved = nice_hair_resolve_product_category_term($term);

    if (! $resolved instanceof WP_Term) {
        return [
            'source'      => 'none',
            'definitions' => [],
        ];
    }

    $cache_key = (int) $resolved->term_id;

    if (isset($cache[$cache_key])) {
        return $cache[$cache_key];
    }

    if (function_exists('get_field')) {
        $current = $resolved;

        while ($current instanceof WP_Term) {
            $mode = nice_hair_get_product_category_filter_mode($current);

            if ($mode === 'disabled') {
                $cache[$cache_key] = [
                    'source'      => 'disabled',
                    'definitions' => [],
                ];

                return $cache[$cache_key];
            }

            if ($mode === 'custom') {
                $cache[$cache_key] = [
                    'source'      => 'custom',
                    'definitions' => nice_hair_get_product_category_custom_filter_definitions($current),
                ];

                return $cache[$cache_key];
            }

            if ((int) $current->parent <= 0) {
                break;
            }

            $parent = get_term((int) $current->parent, 'product_cat');
            $current = $parent instanceof WP_Term ? $parent : null;
        }
    }

    $cache[$cache_key] = [
        'source'      => 'legacy',
        'definitions' => nice_hair_get_legacy_archive_filter_definitions($resolved),
    ];

    return $cache[$cache_key];
}

function nice_hair_get_archive_filter_definitions(WP_Term|int|null $term = null): array
{
    $config = nice_hair_resolve_archive_filter_config($term);

    return is_array($config['definitions'] ?? null) ? $config['definitions'] : [];
}

function nice_hair_archive_filter_definitions_are_configured(WP_Term|int|null $term = null): bool
{
    $config = nice_hair_resolve_archive_filter_config($term);

    return ($config['source'] ?? '') === 'custom';
}

function nice_hair_get_default_product_category_filter_rows(string $family): array
{
    $definitions = nice_hair_get_legacy_archive_filter_definitions(get_term_by('slug', $family === 'ready_to_install' ? 'ready-to-install' : 'exclusive-hair', 'product_cat') ?: null);

    if ($definitions === []) {
        $definitions = match ($family) {
            'ready_to_install' => [
                [
                    'taxonomy' => 'pa_extension_type',
                    'label'    => 'Hair extensions type',
                ],
                [
                    'taxonomy' => 'pa_hair_quality',
                    'label'    => 'Hair quality',
                ],
                [
                    'taxonomy' => 'pa_color_group',
                    'label'    => 'Color',
                ],
            ],
            'exclusive_hair' => [
                [
                    'taxonomy' => 'pa_texture',
                    'label'    => 'Texture',
                ],
                [
                    'taxonomy' => 'pa_color_group',
                    'label'    => 'Color',
                ],
            ],
            default => [],
        };
    }

    $rows = [];

    foreach ($definitions as $definition) {
        $taxonomy = sanitize_key((string) ($definition['taxonomy'] ?? ''));

        if ($taxonomy === '') {
            continue;
        }

        $rows[] = [
            'field_nh_category_filter_taxonomy' => $taxonomy,
            'field_nh_category_filter_label'    => (string) ($definition['label'] ?? ''),
        ];
    }

    return $rows;
}

function nice_hair_find_product_category_term_by_family(string $family): ?WP_Term
{
    $category_ids = function_exists('nice_hair_get_product_category_ids_by_family')
        ? nice_hair_get_product_category_ids_by_family($family)
        : [];

    foreach ($category_ids as $category_id) {
        $term = get_term((int) $category_id, 'product_cat');

        if ($term instanceof WP_Term && (int) $term->parent <= 0) {
            return $term;
        }
    }

    foreach ($category_ids as $category_id) {
        $term = get_term((int) $category_id, 'product_cat');

        if ($term instanceof WP_Term) {
            return $term;
        }
    }

    return null;
}

function nice_hair_maybe_bootstrap_product_category_filters(): void
{
    if (! is_admin() || ! function_exists('get_field') || ! function_exists('update_field')) {
        return;
    }

    $bootstrap_flag_option = 'nh_product_category_filters_bootstrapped';

    if (get_option($bootstrap_flag_option) === '1') {
        return;
    }

    $families = ['ready_to_install', 'exclusive_hair'];
    $found_category = false;

    foreach ($families as $family) {
        $term = nice_hair_find_product_category_term_by_family($family);

        if (! $term instanceof WP_Term) {
            continue;
        }

        $found_category = true;
        $stored_mode = get_term_meta((int) $term->term_id, 'nh_category_filter_mode', true);
        $stored_filters = get_term_meta((int) $term->term_id, 'nh_category_filters', true);

        if ($stored_mode !== '' || $stored_filters !== '') {
            continue;
        }

        $post_id = nice_hair_get_product_category_filter_post_id($term);
        $rows = nice_hair_get_default_product_category_filter_rows($family);

        if ($rows === []) {
            continue;
        }

        update_field('field_nh_category_filter_mode', 'custom', $post_id);
        update_field('field_nh_category_filters', $rows, $post_id);
    }

    if ($found_category) {
        update_option($bootstrap_flag_option, '1', false);
    }
}

add_action('acf/init', 'nice_hair_maybe_bootstrap_product_category_filters', 30);

function nice_hair_get_archive_filter_values(array $definition): array
{
    $raw_value = $_GET[$definition['param']] ?? [];
    $values = is_array($raw_value) ? $raw_value : [$raw_value];

    $normalized = [];

    foreach ($values as $value) {
        $value = sanitize_title(wp_unslash((string) $value));

        if ($value !== '') {
            $normalized[] = $value;
        }
    }

    return array_values(array_unique($normalized));
}

function nice_hair_get_archive_filter_query_args(WP_Term|int|null $term = null): array
{
    $args = [];

    foreach (nice_hair_get_archive_filter_definitions($term) as $definition) {
        $values = nice_hair_get_archive_filter_values($definition);

        if ($values !== []) {
            $args[$definition['param']] = $values;
        }
    }

    return $args;
}

function nice_hair_get_archive_base_url(WP_Term|int|null $term = null): string
{
    $resolved = nice_hair_resolve_product_category_term($term);

    if ($resolved instanceof WP_Term) {
        $term_link = get_term_link($resolved);

        if (is_string($term_link) && $term_link !== '' && ! is_wp_error($term_link)) {
            return $term_link;
        }
    }

    $shop_url = function_exists('wc_get_page_permalink')
        ? wc_get_page_permalink('shop')
        : nice_hair_get_page_url('shop', 'page-templates/page-shop.php');

    return is_string($shop_url) ? $shop_url : home_url('/shop/');
}

function nice_hair_build_archive_url(array $query_args = [], WP_Term|int|null $term = null): string
{
    $base_url = nice_hair_get_archive_base_url($term);
    $normalized_args = [];

    foreach ($query_args as $key => $value) {
        if ($value === '' || $value === null || $value === [] || $value === false) {
            continue;
        }

        $normalized_args[$key] = $value;
    }

    return $normalized_args === []
        ? $base_url
        : add_query_arg($normalized_args, $base_url);
}

function nice_hair_get_archive_reset_url(WP_Term|int|null $term = null): string
{
    $query_args = nice_hair_get_archive_preserved_query_args(array_merge(
        array_map(static fn (array $definition): string => $definition['param'], nice_hair_get_archive_filter_definitions($term)),
        ['paged']
    ));

    return nice_hair_build_archive_url($query_args, $term);
}

function nice_hair_get_archive_preserved_query_args(array $exclude = []): array
{
    $preserved = [];

    foreach ($_GET as $key => $value) {
        if (in_array($key, $exclude, true)) {
            continue;
        }

        if (is_array($value)) {
            $items = [];

            foreach ($value as $item) {
                $item = sanitize_text_field(wp_unslash((string) $item));

                if ($item !== '') {
                    $items[] = $item;
                }
            }

            if ($items !== []) {
                $preserved[sanitize_key((string) $key)] = $items;
            }

            continue;
        }

        $normalized_value = sanitize_text_field(wp_unslash((string) $value));

        if ($normalized_value !== '') {
            $preserved[sanitize_key((string) $key)] = $normalized_value;
        }
    }

    return $preserved;
}

function nice_hair_get_archive_product_ids(WP_Term|int|null $term = null): array
{
    static $cache = [];

    $resolved = nice_hair_resolve_product_category_term($term);

    if (! $resolved instanceof WP_Term) {
        return [];
    }

    if (isset($cache[$resolved->term_id])) {
        return $cache[$resolved->term_id];
    }

    $query = new WP_Query([
        'post_type'              => 'product',
        'post_status'            => 'publish',
        'fields'                 => 'ids',
        'posts_per_page'         => -1,
        'no_found_rows'          => true,
        'ignore_sticky_posts'    => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'tax_query'              => [
            [
                'taxonomy'         => 'product_cat',
                'field'            => 'term_id',
                'terms'            => [$resolved->term_id],
                'include_children' => true,
            ],
        ],
    ]);

    $cache[$resolved->term_id] = array_map('intval', is_array($query->posts) ? $query->posts : []);

    return $cache[$resolved->term_id];
}

function nice_hair_get_archive_filter_terms(array $definition, WP_Term|int|null $term = null): array
{
    $resolved_term = nice_hair_resolve_product_category_term($term);
    $family = $resolved_term instanceof WP_Term
        ? nice_hair_get_product_category_family($resolved_term)
        : '';
    $fixed_term_order = [];

    if ($family === 'exclusive_hair') {
        $fixed_term_order = match ((string) ($definition['taxonomy'] ?? '')) {
            'pa_texture' => ['amazing_curly', 'silky_wavy', 'soft_straight'],
            'pa_color_group' => ['dark', 'middle', 'light'],
            default => [],
        };
    }

    if ($fixed_term_order !== []) {
        $terms = get_terms([
            'taxonomy'   => (string) ($definition['taxonomy'] ?? ''),
            'hide_empty' => false,
            'slug'       => array_map(
                static fn (string $key): string => str_replace('_', '-', $key),
                $fixed_term_order
            ),
        ]);

        if (is_array($terms) && $terms !== []) {
            $term_order_lookup = array_flip($fixed_term_order);
            $terms = array_values(array_filter($terms, static fn (mixed $item): bool => $item instanceof WP_Term));

            usort($terms, static function (WP_Term $left, WP_Term $right) use ($term_order_lookup): int {
                $left_key = nice_hair_normalize_shop_key($left->slug);
                $right_key = nice_hair_normalize_shop_key($right->slug);
                $left_order = $term_order_lookup[$left_key] ?? PHP_INT_MAX;
                $right_order = $term_order_lookup[$right_key] ?? PHP_INT_MAX;

                return $left_order <=> $right_order;
            });

            return $terms;
        }
    }

    $product_ids = nice_hair_get_archive_product_ids($term);

    if ($product_ids === []) {
        return [];
    }

    $terms = wp_get_object_terms($product_ids, $definition['taxonomy'], [
        'orderby' => 'name',
        'order'   => 'ASC',
    ]);

    return is_array($terms) ? array_values(array_filter($terms, static fn (mixed $item): bool => $item instanceof WP_Term)) : [];
}

function nice_hair_get_active_archive_filters(WP_Term|int|null $term = null): array
{
    $active = [];
    $base_query_args = array_merge(
        nice_hair_get_archive_preserved_query_args(),
        nice_hair_get_archive_filter_query_args($term)
    );

    foreach (nice_hair_get_archive_filter_definitions($term) as $definition) {
        $selected_values = nice_hair_get_archive_filter_values($definition);

        if ($selected_values === []) {
            continue;
        }

        $selected_terms = get_terms([
            'taxonomy'   => $definition['taxonomy'],
            'hide_empty' => false,
            'slug'       => $selected_values,
        ]);

        if (! is_array($selected_terms)) {
            continue;
        }

        foreach ($selected_terms as $selected_term) {
            if (! $selected_term instanceof WP_Term) {
                continue;
            }

            $updated_values = array_values(array_diff($selected_values, [$selected_term->slug]));
            $remove_args = $base_query_args;

            if ($updated_values === []) {
                unset($remove_args[$definition['param']]);
            } else {
                $remove_args[$definition['param']] = $updated_values;
            }

            unset($remove_args['paged']);

            $active[] = [
                'group_label' => $definition['label'],
                'label'       => $selected_term->name,
                'remove_url'  => nice_hair_build_archive_url($remove_args, $term),
            ];
        }
    }

    return $active;
}

function nice_hair_get_product_card_attributes(WC_Product|int|null $product = null): array
{
    $resolved = nice_hair_resolve_product($product);

    if (! $resolved instanceof WC_Product) {
        return [];
    }

    $family = nice_hair_get_product_family($resolved);

    $map = [
        'ready_to_install' => ['pa_extension_type', 'pa_hair_quality', 'pa_color_group'],
        'exclusive_hair'   => ['pa_texture', 'pa_color_group', 'pa_length', 'pa_weight'],
        'custom_hair'      => ['pa_extension_type'],
    ];

    $taxonomies = $map[$family] ?? [];
    $labels = [];

    foreach ($taxonomies as $taxonomy) {
        if (! taxonomy_exists($taxonomy)) {
            continue;
        }

        $names = wp_get_post_terms($resolved->get_id(), $taxonomy, ['fields' => 'names']);

        if (! is_array($names) || $names === []) {
            continue;
        }

        foreach ($names as $name) {
            $name = trim((string) $name);

            if ($name !== '') {
                $labels[] = $name;
            }
        }
    }

    return array_slice(array_values(array_unique($labels)), 0, 4);
}

function nice_hair_get_product_category_thumbnail_url(WP_Term|int|null $term = null, string $size = 'woocommerce_thumbnail'): string
{
    $resolved = nice_hair_resolve_product_category_term($term);

    if (! $resolved instanceof WP_Term) {
        return '';
    }

    $thumbnail_id = (int) get_term_meta($resolved->term_id, 'thumbnail_id', true);

    if ($thumbnail_id > 0) {
        $image_url = wp_get_attachment_image_url($thumbnail_id, $size);

        if (is_string($image_url) && $image_url !== '') {
            return $image_url;
        }
    }

    $products = get_posts([
        'post_type'              => 'product',
        'post_status'            => 'publish',
        'posts_per_page'         => 1,
        'fields'                 => 'ids',
        'no_found_rows'          => true,
        'ignore_sticky_posts'    => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'tax_query'              => [
            [
                'taxonomy'         => 'product_cat',
                'field'            => 'term_id',
                'terms'            => [$resolved->term_id],
                'include_children' => true,
            ],
        ],
        'meta_query'             => [
            [
                'key'     => '_thumbnail_id',
                'compare' => 'EXISTS',
            ],
        ],
    ]);

    $product_id = isset($products[0]) ? (int) $products[0] : 0;

    if ($product_id <= 0) {
        return '';
    }

    $image_url = get_the_post_thumbnail_url($product_id, $size);

    return is_string($image_url) ? $image_url : '';
}

add_action('woocommerce_product_query', function (WP_Query $query): void {
    if (is_admin() || ! $query->is_main_query() || ! function_exists('is_product_category') || ! is_product_category()) {
        return;
    }

    $term = nice_hair_get_current_product_category_term();

    if (! $term instanceof WP_Term) {
        return;
    }

    $filter_definitions = nice_hair_get_archive_filter_definitions($term);

    if ($filter_definitions === []) {
        return;
    }

    $tax_query = $query->get('tax_query');
    $tax_query = is_array($tax_query) ? $tax_query : [];

    foreach ($filter_definitions as $definition) {
        $values = nice_hair_get_archive_filter_values($definition);

        if ($values === []) {
            continue;
        }

        $tax_query[] = [
            'taxonomy' => $definition['taxonomy'],
            'field'    => 'slug',
            'terms'    => $values,
            'operator' => 'IN',
        ];
    }

    if (count($tax_query) > 1) {
        $tax_query['relation'] = 'AND';
    }

    $query->set('tax_query', $tax_query);
}, 20);

add_filter('woocommerce_product_query_tax_query', function (array $tax_query, mixed $query): array {
    if (is_admin() || ! function_exists('is_product_category') || ! is_product_category()) {
        return $tax_query;
    }

    $term = nice_hair_get_current_product_category_term();

    $family = $term instanceof WP_Term ? nice_hair_get_product_category_family($term) : '';

    if (! $term instanceof WP_Term || ! in_array($family, ['ready_to_install', 'exclusive_hair'], true)) {
        return $tax_query;
    }

    if (! function_exists('wc_get_product_visibility_term_ids')) {
        return $tax_query;
    }

    $visibility_term_ids = wc_get_product_visibility_term_ids();
    $out_of_stock_term_id = (int) ($visibility_term_ids['outofstock'] ?? 0);

    if ($out_of_stock_term_id <= 0) {
        return $tax_query;
    }

    $filtered_tax_query = [];

    foreach ($tax_query as $key => $clause) {
        if ($key === 'relation') {
            $filtered_tax_query[$key] = $clause;
            continue;
        }

        if (
            is_array($clause)
            && ($clause['taxonomy'] ?? '') === 'product_visibility'
            && strtoupper((string) ($clause['operator'] ?? '')) === 'NOT IN'
        ) {
            $terms = array_map('intval', (array) ($clause['terms'] ?? []));

            if (in_array($out_of_stock_term_id, $terms, true)) {
                $terms = array_values(array_diff($terms, [$out_of_stock_term_id]));

                if ($terms === []) {
                    continue;
                }

                $clause['terms'] = $terms;
            }
        }

        $filtered_tax_query[] = $clause;
    }

    if (! isset($filtered_tax_query['relation']) && isset($tax_query['relation'])) {
        $filtered_tax_query['relation'] = $tax_query['relation'];
    }

    return $filtered_tax_query;
}, 20, 2);

add_filter('woocommerce_pagination_args', function (array $args): array {
    if (! function_exists('is_product_category') || ! is_product_category()) {
        return $args;
    }

    $args['mid_size'] = 3;
    $args['prev_text'] = '&lsaquo;';
    $args['next_text'] = '&rsaquo;';
    $args['add_args'] = array_merge(
        is_array($args['add_args'] ?? null) ? $args['add_args'] : [],
        nice_hair_get_archive_filter_query_args()
    );

    return $args;
});
