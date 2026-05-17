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

    echo '<div class="notice notice-info"><pre>';

    foreach (['nh_price_quiz', 'nh_salon_request'] as $post_type) {
        $object = get_post_type_object($post_type);

        if (! $object) {
            echo esc_html($post_type . ': post type object not found') . "\n\n";
            continue;
        }

        echo esc_html($post_type) . "\n";
        echo 'edit_posts cap: ' . esc_html($object->cap->edit_posts) . "\n";
        echo 'can edit_posts cap: ' . (current_user_can($object->cap->edit_posts) ? 'yes' : 'no') . "\n";
        echo 'read_private_posts cap: ' . esc_html($object->cap->read_private_posts) . "\n";
        echo 'can read_private_posts cap: ' . (current_user_can($object->cap->read_private_posts) ? 'yes' : 'no') . "\n";
        echo "\n";
    }

    echo '</pre></div>';
});