<?php
declare(strict_types=1);

get_header();
?>
<main class="nh-main nh-main--fallback">
    <div class="nh-dev-fallback">
        <h1><?php bloginfo('name'); ?></h1>
        <p><?php esc_html_e('Theme fallback template.', 'nice-hair'); ?></p>
    </div>
</main>
<?php
get_footer();
