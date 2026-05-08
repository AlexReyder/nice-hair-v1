<?php
declare(strict_types=1);

function nice_hair_enqueue_theme_assets(): void
{
    if (nice_hair_is_vite_dev_server_running()) {
        nice_hair_enqueue_vite_dev_entry('nice-hair-app', 'assets/js/app.js');
        return;
    }

    if (file_exists(nice_hair_vite_manifest_path())) {
        nice_hair_enqueue_vite_entry('nice-hair-app', 'assets/js/app.js');
        return;
    }

    wp_enqueue_style('nice-hair-fallback-style', get_stylesheet_uri(), [], wp_get_theme()->get('Version'));
}
add_action('wp_enqueue_scripts', 'nice_hair_enqueue_theme_assets');

/**
 * Inline script data for subscribe form nonce.
 */
function nice_hair_subscribe_inline_data(): void
{
    if (! wp_script_is('nice-hair-app', 'registered') && ! wp_script_is('nice-hair-app', 'enqueued')) {
        return;
    }

    wp_add_inline_script(
        'nice-hair-app',
        'window.nhSubscribe = ' . wp_json_encode([
            'nonce' => wp_create_nonce('wp_rest'),
            'restUrl' => esc_url_raw(rest_url('nice-hair/v1/subscribe')),
        ]) . ';',
        'before'
    );
}
add_action('wp_enqueue_scripts', 'nice_hair_subscribe_inline_data', 20);
