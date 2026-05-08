<?php
/**
 * Template Name: Источник страницы Thank You
 */
declare(strict_types=1);

get_header('home');
?>
<main class="nh-main nh-main--thank-you-source-page">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <?php if (trim((string) get_the_content()) !== '') : ?>
                <?php the_content(); ?>
            <?php else : ?>
                <?php get_template_part('template-parts/sections/thank-you/hero'); ?>
            <?php endif; ?>
        <?php endwhile; ?>
    <?php endif; ?>
</main>
<?php
get_footer('home');

