<?php

declare(strict_types=1);

$nice_hair_files = [
    '/inc/core/setup.php',
    '/inc/core/admin.php',
    '/inc/core/vite.php',
    '/inc/core/enqueue.php',
    '/inc/editor/pattern-categories.php',
    '/inc/editor/pattern-registration.php',
    '/inc/editor/editor-assets.php',
    '/inc/editor/allowed-blocks.php',
    '/inc/acf/loader.php',
    '/inc/admin/capabilities.php',
    '/inc/quiz/options.php',
    '/inc/quiz/cpt.php',
    '/inc/quiz/email.php',
    '/inc/quiz/rest.php',
    '/inc/subscribe/cpt.php',
    '/inc/subscribe/rest.php',
    '/inc/blog/taxonomy.php',
    '/inc/blog/query.php',
    '/inc/popup/cpt.php',
    '/inc/popup-salon/cpt.php',
    '/inc/popup-salon/email.php',
    '/inc/popup-salon/rest.php',
    '/inc/running-line/cpt.php',
    '/inc/theme/template-functions.php',
    '/inc/theme/contact.php',
    '/inc/theme/popup.php',
    '/inc/theme/popup-salon.php',
    '/inc/theme/running-line.php',
    '/inc/theme/cookie-consent.php',
   
    '/inc/woocommerce/data-model.php',
    '/inc/woocommerce/exclusive-product-forms.php',
    '/inc/woocommerce/custom-hair-colors.php',
    '/inc/woocommerce/archive.php',
    '/inc/woocommerce/setup.php',
    '/inc/woocommerce/admin-product-fields.php',
    '/inc/woocommerce/consultation.php',
    '/inc/woocommerce/photoswipe.php',
];

foreach ($nice_hair_files as $nice_hair_file) {
    $nice_hair_path = get_theme_file_path($nice_hair_file);

    if (file_exists($nice_hair_path)) {
        require_once $nice_hair_path;
    }
}

add_filter('script_loader_tag', function (string $tag, string $handle, string $src): string {
    if ('module' === wp_scripts()->get_data($handle, 'type')) {
        $tag = str_replace('<script ', '<script type="module" ', $tag);
    }

    return $tag;
}, 10, 3);


add_action('admin_notices', function (): void {
    if (! is_admin() || ! current_user_can('read')) {
        return;
    }

    $caps = [
        'read',
        'read_nh_submission',
        'read_nh_submissions',
        'edit_nh_submission',
        'edit_nh_submissions',
        'edit_others_nh_submissions',
        'edit_private_nh_submissions',
        'edit_published_nh_submissions',
        'read_private_nh_submissions',
        'manage_nh_submission_statuses',
    ];

    echo '<div class="notice notice-info"><pre>';

    foreach ($caps as $cap) {
        printf(
            "%s: %s\n",
            esc_html($cap),
            current_user_can($cap) ? 'yes' : 'no'
        );
    }

    echo '</pre></div>';
});