<?php
/**
 * Template Name: Tools Category
 */
declare(strict_types=1);

get_header('shop');
?>
<main class="nh-main nh-main--tools">
    <?php /* ── Hero (from Gutenberg content) ──────────── */ ?>
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <?php the_content(); ?>
        <?php endwhile; ?>
    <?php endif; ?>

    <?php /* ── Product grid (Tools category) ─────────── */ ?>
    <?php
    $tools_cat = get_term_by('name', 'Tools', 'product_cat');

    if ($tools_cat) :
        $tools_query = new WP_Query([
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'tax_query'      => [
                [
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $tools_cat->term_id,
                ],
            ],
            'orderby' => 'menu_order date',
            'order'   => 'ASC',
        ]);

        if ($tools_query->have_posts()) :
    ?>
    <section class="nh-tools-grid-section">
        <div class="nh-tools-grid nh-shop-archive__product-grid nh-shop-archive__product-grid--tools">
            <?php woocommerce_product_loop_start(); ?>

            <?php while ($tools_query->have_posts()) : $tools_query->the_post(); ?>
                <?php
                global $product;
                if (empty($product) || ! $product instanceof WC_Product) {
                    $product = wc_get_product(get_the_ID());
                }
                if (! $product) {
                    continue;
                }
                ?>
                <?php wc_get_template_part('content', 'product'); ?>
            <?php endwhile; ?>

            <?php woocommerce_product_loop_end(); ?>
        </div>
    </section>
    <?php
        endif;
        wp_reset_postdata();
    endif;
    ?>
</main>
<?php
get_footer('shop');
