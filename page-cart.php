<?php
declare(strict_types=1);

get_header('shop');
?>
<main class="nh-main nh-main--page nh-main--shop-utility nh-main--cart-page">
    <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class('nh-page nh-page--woocommerce nh-page--cart'); ?>>
            <?php echo do_shortcode('[woocommerce_cart]'); ?>
        </article>
    <?php endwhile; ?>
</main>
<?php
get_footer('shop');
