<?php
declare(strict_types=1);

status_header(404);
nocache_headers();

get_header('home');

$nh_404_source_page = function_exists('nice_hair_get_404_source_page')
    ? nice_hair_get_404_source_page()
    : null;
?>
<main class="nh-main nh-main--404-page">
    <?php if ($nh_404_source_page instanceof WP_Post && trim((string) $nh_404_source_page->post_content) !== '') : ?>
        <?php
        global $post;
        $post = $nh_404_source_page;
        setup_postdata($post);
        the_content();
        wp_reset_postdata();
        ?>
    <?php else : ?>
        <?php get_template_part('template-parts/sections/error-404/hero'); ?>
    <?php endif; ?>
</main>
<?php
get_footer('home');

