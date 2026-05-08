<?php
/**
 * Tools product category archive layout.
 */

declare(strict_types=1);

defined('ABSPATH') || exit;
?>

<div class="nh-tools-archive">
    <?php if (woocommerce_product_loop()) : ?>
        <?php do_action('woocommerce_before_shop_loop'); ?>

        <section class="nh-tools-grid-section" aria-label="<?php esc_attr_e('Tools products', 'nice-hair'); ?>">
            <div class="nh-tools-grid nh-shop-archive__product-grid nh-shop-archive__product-grid--tools">
                <?php woocommerce_product_loop_start(); ?>

                <?php while (have_posts()) : ?>
                    <?php the_post(); ?>
                    <?php wc_get_template_part('content', 'product'); ?>
                <?php endwhile; ?>

                <?php woocommerce_product_loop_end(); ?>
            </div>
        </section>

        <?php do_action('woocommerce_after_shop_loop'); ?>
    <?php else : ?>
        <section class="nh-tools-grid-section">
            <div class="nh-tools-grid nh-tools-grid--empty">
                <div class="nh-tools-empty">
                    <h2 class="nh-tools-empty__title"><?php esc_html_e('No tools found', 'nice-hair'); ?></h2>
                    <p class="nh-tools-empty__text"><?php esc_html_e('This category is ready, but there are no visible products in it yet.', 'nice-hair'); ?></p>
                </div>
            </div>
        </section>
    <?php endif; ?>
</div>
