<?php
declare(strict_types=1);

$nh_use_shop_layout = function_exists('nice_hair_is_shop_utility_page') && nice_hair_is_shop_utility_page();

if ($nh_use_shop_layout) {
    get_header('shop');
} else {
    get_header();
}
?>
<main class="nh-main nh-main--page<?php echo $nh_use_shop_layout ? ' nh-main--shop-utility' : ''; ?>">
    <?php while (have_posts()) : the_post(); ?>
        <?php if ($nh_use_shop_layout) : ?>
            <article <?php post_class('nh-page nh-page--woocommerce'); ?>>
                <?php the_content(); ?>
            </article>
        <?php else : ?>
            <article <?php post_class('nh-page'); ?>>
                <div class="nh-dev-fallback">
                    <header class="nh-page__header">
                        <h1 class="nh-page__title"><?php the_title(); ?></h1>
                    </header>
                    <div class="nh-page__content">
                        <?php the_content(); ?>
                    </div>
                </div>
            </article>
        <?php endif; ?>
    <?php endwhile; ?>
</main>
<?php
if ($nh_use_shop_layout) {
    get_footer('shop');
} else {
    get_footer();
}
