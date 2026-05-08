<?php
/**
 * Single Product — Generic fallback.
 *
 * Wraps standard WC hooks in basic BEM markup.
 * Used for products without a custom category layout.
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

global $product;
?>

<section id="product-<?php the_ID(); ?>" <?php wc_product_class('nh-single-product nh-single-product--generic'); ?>>
    <div class="nh-single-product__container">
        <div class="nh-single-product__columns">

            <div class="nh-single-product__gallery">
                <?php do_action('woocommerce_before_single_product_summary'); ?>
            </div>

            <div class="nh-single-product__info">
                <?php do_action('woocommerce_single_product_summary'); ?>
            </div>

        </div>

        <?php do_action('woocommerce_after_single_product_summary'); ?>
    </div>
</section>
