<?php
/**
 * WooCommerce — Single Product page.
 *
 * Minimal shell — real design will be added with Figma mockups.
 *
 * @see https://woocommerce.com/document/template-structure/
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

get_header('shop');

// Detect product family for wrapper modifier.
$nh_sp_modifiers = [];
$nh_sp_product = function_exists('wc_get_product')
    ? wc_get_product(get_the_ID())
    : null;
$nh_sp_family = $nh_sp_product instanceof WC_Product && function_exists('nice_hair_get_product_family')
    ? nice_hair_get_product_family($nh_sp_product)
    : '';

if ($nh_sp_family === 'tools') {
    $nh_sp_modifiers[] = 'nh-main--single-product--tools';
}

if ($nh_sp_family === 'keratin') {
    $nh_sp_modifiers[] = 'nh-main--single-product--keratin';
}

if ($nh_sp_family === 'ready_to_install') {
    $nh_sp_modifiers[] = 'nh-main--single-product--ready-to-install';
}

if ($nh_sp_family === 'exclusive_hair') {
    $nh_sp_modifiers[] = 'nh-main--single-product--exclusive-hair';
}

if ($nh_sp_family === 'custom_hair') {
    $nh_sp_modifiers[] = 'nh-main--single-product--custom-hair';
}
?>

<main class="nh-main nh-main--single-product<?php echo $nh_sp_modifiers !== [] ? ' ' . esc_attr(implode(' ', $nh_sp_modifiers)) : ''; ?>">
    <?php while (have_posts()) : ?>
        <?php the_post(); ?>
        <?php wc_get_template_part('content', 'single-product'); ?>
    <?php endwhile; ?>
</main>

<?php
get_footer('shop');
