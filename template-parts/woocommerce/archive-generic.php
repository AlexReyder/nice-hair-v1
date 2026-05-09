<?php
/**
 * Generic product category archive layout.
 *
 * Used for product categories that do not belong to predefined shop families.
 * Product grid follows Tools layout.
 * Filters reuse the same layout/classes as collection categories.
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$nh_archive_term = function_exists('nice_hair_get_current_product_category_term')
    ? nice_hair_get_current_product_category_term()
    : null;

$nh_archive_title = function_exists('woocommerce_page_title')
    ? trim((string) woocommerce_page_title(false))
    : '';

if ($nh_archive_title === '' && $nh_archive_term instanceof WP_Term) {
    $nh_archive_title = trim((string) $nh_archive_term->name);
}

if ($nh_archive_title === '') {
    $nh_archive_title = __('Products', 'nice-hair');
}

$nh_filter_definitions = $nh_archive_term instanceof WP_Term && function_exists('nice_hair_get_archive_filter_definitions')
    ? nice_hair_get_archive_filter_definitions($nh_archive_term)
    : [];

$nh_filter_groups = [];

foreach ($nh_filter_definitions as $nh_definition) {
    $nh_options = function_exists('nice_hair_get_archive_filter_terms') && $nh_archive_term instanceof WP_Term
        ? nice_hair_get_archive_filter_terms($nh_definition, $nh_archive_term)
        : [];

    if ($nh_options === []) {
        continue;
    }

    $nh_filter_groups[] = [
        'definition' => $nh_definition,
        'options'    => $nh_options,
    ];
}

$nh_has_filters = $nh_filter_groups !== [];

$nh_active_filters = $nh_archive_term instanceof WP_Term && function_exists('nice_hair_get_active_archive_filters')
    ? nice_hair_get_active_archive_filters($nh_archive_term)
    : [];

$nh_reset_url = $nh_archive_term instanceof WP_Term && function_exists('nice_hair_get_archive_reset_url')
    ? nice_hair_get_archive_reset_url($nh_archive_term)
    : '';

$nh_archive_action_url = $nh_archive_term instanceof WP_Term
    ? get_term_link($nh_archive_term)
    : home_url('/shop/');

if (is_wp_error($nh_archive_action_url) || ! is_string($nh_archive_action_url)) {
    $nh_archive_action_url = home_url('/shop/');
}

$nh_preserved_args = function_exists('nice_hair_get_archive_preserved_query_args')
    ? nice_hair_get_archive_preserved_query_args(array_merge(
        array_map(static fn (array $definition): string => (string) $definition['param'], $nh_filter_definitions),
        ['paged']
    ))
    : [];

$nh_filter_drawer_id = $nh_archive_term instanceof WP_Term
    ? 'nh-shop-filters-' . (int) $nh_archive_term->term_id
    : 'nh-shop-filters-generic';

$nh_total = (int) wc_get_loop_prop('total');

$nh_render_collection_filters_form = static function (
    array $filter_groups,
    array $preserved_args,
    string $action_url,
    string $submit_mode = 'auto',
    string $reset_url = '',
    bool $has_active_filters = false
): void {
    $submit_mode = $submit_mode === 'manual' ? 'manual' : 'auto';
    ?>
    <form
        class="nh-shop-ready__filters-form nh-shop-ready__filters-form--<?php echo esc_attr($submit_mode); ?>"
        method="get"
        action="<?php echo esc_url($action_url); ?>"
        data-nh-ready-filters-form
        data-nh-ready-filters-submit-mode="<?php echo esc_attr($submit_mode); ?>"
    >
        <?php foreach ($preserved_args as $nh_arg_key => $nh_arg_value) : ?>
            <?php if (is_array($nh_arg_value)) : ?>
                <?php foreach ($nh_arg_value as $nh_arg_item) : ?>
                    <input type="hidden" name="<?php echo esc_attr((string) $nh_arg_key); ?>[]" value="<?php echo esc_attr((string) $nh_arg_item); ?>">
                <?php endforeach; ?>
            <?php else : ?>
                <input type="hidden" name="<?php echo esc_attr((string) $nh_arg_key); ?>" value="<?php echo esc_attr((string) $nh_arg_value); ?>">
            <?php endif; ?>
        <?php endforeach; ?>

        <?php foreach ($filter_groups as $nh_filter_group) : ?>
            <?php
            $nh_definition = $nh_filter_group['definition'];
            $nh_options = $nh_filter_group['options'];

            $nh_selected_values = function_exists('nice_hair_get_archive_filter_values')
                ? nice_hair_get_archive_filter_values($nh_definition)
                : [];

            $nh_selected_count = count($nh_selected_values);
            ?>

            <details class="nh-shop-ready__filter-group nh-shop-ready__filter-group--accordion<?php echo $nh_selected_count > 0 ? ' is-active' : ''; ?>" data-nh-ready-filter-group open>
                <summary class="nh-shop-ready__filter-summary">
                    <span class="nh-shop-ready__filter-summary-label">
                        <?php echo esc_html((string) $nh_definition['label']); ?>
                    </span>

                    <span class="nh-shop-ready__filter-summary-meta">
                        <span class="nh-shop-ready__filter-summary-icon" aria-hidden="true"></span>
                    </span>
                </summary>

                <div class="nh-shop-ready__filter-panel">
                    <div class="nh-shop-ready__filter-list">
                        <?php foreach ($nh_options as $nh_option) : ?>
                            <?php
                            if (! $nh_option instanceof WP_Term) {
                                continue;
                            }

                            $nh_is_selected = in_array($nh_option->slug, $nh_selected_values, true);
                            ?>

                            <label class="nh-shop-ready__filter-option<?php echo $nh_is_selected ? ' is-active' : ''; ?>" data-nh-ready-filter-option>
                                <input
                                    class="nh-shop-ready__filter-input"
                                    type="checkbox"
                                    name="<?php echo esc_attr((string) $nh_definition['param']); ?>[]"
                                    value="<?php echo esc_attr($nh_option->slug); ?>"
                                    <?php checked($nh_is_selected); ?>
                                    data-nh-ready-filter-input
                                >

                                <span class="nh-shop-ready__filter-text">
                                    <?php echo esc_html($nh_option->name); ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </details>
        <?php endforeach; ?>

        <div class="nh-shop-ready__filters-actions">
            <?php if ($submit_mode === 'manual' && $has_active_filters && $reset_url !== '') : ?>
                <a class="nh-shop-ready__filters-reset-link" href="<?php echo esc_url($reset_url); ?>">
                    <?php esc_html_e('Clear filters', 'nice-hair'); ?>
                </a>
            <?php endif; ?>

            <button type="submit" class="nh-shop-ready__filters-submit">
                <?php esc_html_e('Apply filters', 'nice-hair'); ?>
            </button>
        </div>
    </form>
    <?php
};

$nh_shop_url = function_exists('wc_get_page_permalink')
    ? wc_get_page_permalink('shop')
    : home_url('/shop/');
?>

<div class="nh-shop-archive nh-shop-archive--wide nh-shop-archive--ready nh-shop-archive--generic">
    <div class="nh-shop-ready<?php echo $nh_has_filters ? '' : ' nh-shop-ready--no-sidebar'; ?> nh-shop-ready--generic">
        <?php if ($nh_has_filters) : ?>
            <aside class="nh-shop-ready__sidebar" aria-label="<?php esc_attr_e('Product filters', 'nice-hair'); ?>">
                <section class="nh-shop-ready__filters">
                    <div class="nh-shop-ready__filters-header">
                        <h2 class="nh-shop-ready__filters-title">
                            <?php esc_html_e('Filters', 'nice-hair'); ?>
                        </h2>
                    </div>

                    <?php
                    $nh_render_collection_filters_form(
                        $nh_filter_groups,
                        $nh_preserved_args,
                        (string) $nh_archive_action_url,
                        'auto',
                        $nh_reset_url,
                        $nh_active_filters !== []
                    );
                    ?>
                </section>
            </aside>

            <div
                class="nh-content-drawer nh-content-drawer--shop-filters"
                id="<?php echo esc_attr($nh_filter_drawer_id); ?>"
                data-nh-content-drawer
                data-nh-content-drawer-id="<?php echo esc_attr($nh_filter_drawer_id); ?>"
                hidden
            >
                <div class="nh-content-drawer__backdrop" data-nh-content-drawer-close></div>

                <div
                    class="nh-content-drawer__panel"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="<?php echo esc_attr($nh_filter_drawer_id . '-title'); ?>"
                >
                    <button
                        type="button"
                        class="nh-content-drawer__close"
                        data-nh-content-drawer-close
                        data-nh-content-drawer-close-button
                        aria-label="<?php esc_attr_e('Close filters', 'nice-hair'); ?>"
                    >
                        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" aria-hidden="true">
                            <circle cx="16" cy="16" r="15.5" stroke="currentColor"/>
                            <path d="M11 11L21 21M21 11L11 21" stroke="currentColor" stroke-width="1.5"/>
                        </svg>
                    </button>

                    <div class="nh-content-drawer__header">
                        <span class="nh-content-drawer__eyebrow">
                            <?php esc_html_e('[ FILTERS ]', 'nice-hair'); ?>
                        </span>

                        <div class="nh-content-drawer__rule" aria-hidden="true"></div>

                        <h2 class="nh-content-drawer__title" id="<?php echo esc_attr($nh_filter_drawer_id . '-title'); ?>">
                            <?php esc_html_e('Filters', 'nice-hair'); ?>
                        </h2>
                    </div>

                    <div class="nh-content-drawer__body nh-shop-ready__filter-drawer-body">
                        <?php
                        $nh_render_collection_filters_form(
                            $nh_filter_groups,
                            $nh_preserved_args,
                            (string) $nh_archive_action_url,
                            'manual',
                            $nh_reset_url,
                            $nh_active_filters !== []
                        );
                        ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="nh-shop-ready__content">
            <?php if ($nh_has_filters) : ?>
                <div class="nh-shop-ready__mobile-filter-bar">
                    <button
                        type="button"
                        class="nh-shop-ready__filter-trigger"
                        data-nh-content-drawer-target="<?php echo esc_attr($nh_filter_drawer_id); ?>"
                        aria-haspopup="dialog"
                        aria-controls="<?php echo esc_attr($nh_filter_drawer_id); ?>"
                    >
                        <span class="nh-shop-ready__filter-trigger-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                                <path d="M3 4.5H15M5.25 9H12.75M7.5 13.5H10.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                            </svg>
                        </span>

                        <span><?php esc_html_e('Filters', 'nice-hair'); ?></span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if ($nh_active_filters !== [] || $nh_total > 0) : ?>
                <div class="nh-shop-ready__toolbar">
                    <div class="nh-shop-ready__active-filters" aria-label="<?php esc_attr_e('Active filters', 'nice-hair'); ?>">
                        <?php foreach ($nh_active_filters as $nh_active_filter) : ?>
                            <a class="nh-shop-ready__active-filter" href="<?php echo esc_url((string) $nh_active_filter['remove_url']); ?>">
                                <span class="nh-shop-ready__active-filter-group">
                                    <?php echo esc_html((string) $nh_active_filter['group_label']); ?>
                                </span>

                                <span class="nh-shop-ready__active-filter-label">
                                    <?php echo esc_html((string) $nh_active_filter['label']); ?>
                                </span>
                            </a>
                        <?php endforeach; ?>

                        <?php if ($nh_active_filters !== [] && $nh_reset_url !== '') : ?>
                            <a class="nh-shop-ready__clear-filters" href="<?php echo esc_url($nh_reset_url); ?>">
                                <?php esc_html_e('Clear filters', 'nice-hair'); ?>
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if ($nh_total > 0) : ?>
                        <p class="nh-shop-ready__results-count">
                            <?php
                            echo esc_html(
                                sprintf(
                                    _n('%d product', '%d products', $nh_total, 'nice-hair'),
                                    $nh_total
                                )
                            );
                            ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (woocommerce_product_loop()) : ?>
                <div class="nh-shop-archive__product-grid nh-shop-archive__product-grid--tools nh-shop-archive__product-grid--generic">
                    <?php woocommerce_product_loop_start(); ?>

                    <?php while (have_posts()) : ?>
                        <?php the_post(); ?>
                        <?php wc_get_template_part('content', 'product'); ?>
                    <?php endwhile; ?>

                    <?php woocommerce_product_loop_end(); ?>
                </div>

                <?php woocommerce_pagination(); ?>
            <?php else : ?>
                <section class="nh-shop-archive__empty nh-shop-archive__empty--ready nh-shop-archive__empty--generic">
                    <h2 class="nh-shop-archive__empty-title">
                        <?php esc_html_e('No products found', 'nice-hair'); ?>
                    </h2>

                    <p class="nh-shop-archive__empty-text">
                        <?php esc_html_e('Try adjusting the selected filters or return to the category overview.', 'nice-hair'); ?>
                    </p>

                    <div class="nh-shop-archive__empty-actions">
                        <?php if ($nh_active_filters !== [] && $nh_reset_url !== '') : ?>
                            <a class="nh-shop-ready__clear-filters" href="<?php echo esc_url($nh_reset_url); ?>">
                                <?php esc_html_e('Clear filters', 'nice-hair'); ?>
                            </a>
                        <?php endif; ?>

                        <a class="nh-shop-archive__filters-secondary" href="<?php echo esc_url((string) $nh_shop_url); ?>">
                            <?php esc_html_e('Back to Shop', 'nice-hair'); ?>
                        </a>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </div>
</div>