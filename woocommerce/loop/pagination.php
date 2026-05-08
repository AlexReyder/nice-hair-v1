<?php
/**
 * WooCommerce pagination template.
 *
 * Mirrors the Blog pagination markup while keeping WooCommerce loop data.
 *
 * @see https://woocommerce.com/document/template-structure/
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$total = isset($total) ? (int) $total : (int) wc_get_loop_prop('total_pages');
$current = isset($current) ? (int) $current : (int) wc_get_loop_prop('current_page');
$base = isset($base) ? (string) $base : esc_url_raw(str_replace(999999999, '%#%', remove_query_arg('add-to-cart', get_pagenum_link(999999999, false))));
$format = isset($format) ? (string) $format : '';

if ($total <= 1) {
    return;
}

$current = max(1, $current);
$filter_args = function_exists('is_product_category') && is_product_category() && function_exists('nice_hair_get_archive_filter_query_args')
    ? nice_hair_get_archive_filter_query_args()
    : [];
$links = paginate_links(apply_filters('woocommerce_pagination_args', [
    'base'      => $base,
    'format'    => $format,
    'add_args'  => $filter_args,
    'current'   => $current,
    'total'     => $total,
    'mid_size'  => 3,
    'prev_text' => '&lsaquo;',
    'next_text' => '&rsaquo;',
    'type'      => 'array',
    'end_size'  => 1,
]));

$links = is_array($links) ? $links : [];
?>

<nav class="woocommerce-pagination navigation pagination nh-shop-pagination" aria-label="<?php esc_attr_e('Product pagination', 'nice-hair'); ?>">
    <div class="nav-links">
        <?php if ($current > 1) : ?>
            <a class="page-numbers first" href="<?php echo esc_url(add_query_arg($filter_args, get_pagenum_link(1))); ?>">&laquo;</a>
        <?php else : ?>
            <span class="page-numbers first">&laquo;</span>
        <?php endif; ?>

        <?php if ($links !== []) : ?>
            <?php foreach ($links as $link) : ?>
                <?php echo wp_kses_post($link); ?>
            <?php endforeach; ?>
        <?php else : ?>
            <span class="page-numbers current">1</span>
        <?php endif; ?>

        <?php if ($current < $total) : ?>
            <a class="page-numbers last" href="<?php echo esc_url(add_query_arg($filter_args, get_pagenum_link($total))); ?>">&raquo;</a>
        <?php else : ?>
            <span class="page-numbers last">&raquo;</span>
        <?php endif; ?>
    </div>
</nav>
