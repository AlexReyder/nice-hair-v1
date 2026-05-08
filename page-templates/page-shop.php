<?php
/**
 * Template Name: Shop Page
 *
 * WP treats the WC shop page as a product archive, so the main query
 * contains products, not the page itself.  We load the page content
 * directly by ID to render the Gutenberg hero and sections.
 */
declare(strict_types=1);

get_header('shop');

$nh_shop_id   = function_exists('wc_get_page_id') ? wc_get_page_id('shop') : get_the_ID();
$nh_shop_page = get_post($nh_shop_id);
?>
<main class="nh-main nh-main--shop-page">
    <?php if ($nh_shop_page && trim((string) $nh_shop_page->post_content) !== '') : ?>
        <?php
        // Set up global $post so block rendering context is correct.
        global $post;
        $post = $nh_shop_page;
        setup_postdata($post);
        the_content();
        wp_reset_postdata();
        ?>
    <?php else : ?>
        <section class="nh-dev-fallback">
            <div class="nh-dev-fallback__card">
                <h1><?php echo esc_html(get_the_title($nh_shop_id) ?: __('Shop', 'nice-hair')); ?></h1>
                <p><?php esc_html_e('The shop page exists, but there is no seeded block content yet.', 'nice-hair'); ?></p>
                <p><?php esc_html_e('Insert the "Nice Hair / Shop Hero" pattern from the editor to get started.', 'nice-hair'); ?></p>
            </div>
        </section>
    <?php endif; ?>
</main>
<?php
get_footer('shop');
