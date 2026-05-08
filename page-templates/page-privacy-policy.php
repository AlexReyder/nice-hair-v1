<?php
/**
 * Template Name: Privacy Policy Page
 */
declare(strict_types=1);

get_header('home');
?>
<main class="nh-main nh-main--privacy-policy-page">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <?php if (trim((string) get_the_content()) !== '') : ?>
                <?php the_content(); ?>
            <?php else : ?>
                <section class="nh-dev-fallback">
                    <div class="nh-dev-fallback__card">
                        <h1><?php echo esc_html(get_the_title() ?: __('Privacy Policy', 'nice-hair')); ?></h1>
                        <p><?php esc_html_e('The privacy policy page exists, but there is no seeded block content yet.', 'nice-hair'); ?></p>
                        <p><?php esc_html_e('Insert the "Nice Hair / Privacy Policy" pattern from the editor to get started.', 'nice-hair'); ?></p>
                    </div>
                </section>
            <?php endif; ?>
        <?php endwhile; ?>
    <?php endif; ?>
</main>
<?php
get_footer('home');

