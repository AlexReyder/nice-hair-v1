<?php

declare(strict_types=1);

function nice_hair_vite_dev_server_url(): string
{
    $origin = getenv('VITE_DEV_ORIGIN');

    if (is_string($origin) && $origin !== '') {
        return rtrim($origin, '/');
    }

    return 'http://127.0.0.1:5173';
}

function nice_hair_vite_manifest_path(): string
{
    return get_theme_file_path('/assets/dist/manifest.json');
}

function nice_hair_vite_manifest(): array
{
    static $manifest = null;

    if ($manifest !== null) {
        return $manifest;
    }

    $path = nice_hair_vite_manifest_path();

    if (! file_exists($path)) {
        $manifest = [];
        return $manifest;
    }

    $contents = file_get_contents($path);
    $decoded  = is_string($contents) ? json_decode($contents, true) : [];

    $manifest = is_array($decoded) ? $decoded : [];

    return $manifest;
}

function nice_hair_vite_manifest_entry(string $entry): ?array
{
    $manifest = nice_hair_vite_manifest();

    if (! isset($manifest[$entry]) || ! is_array($manifest[$entry])) {
        return null;
    }

    return $manifest[$entry];
}

function nice_hair_is_vite_dev_server_running(): bool
{
    static $available = null;

    if ($available !== null) {
        return $available;
    }

    $response = wp_remote_get(
        nice_hair_vite_dev_server_url() . '/@vite/client',
        [
            'timeout'   => 0.35,
            'sslverify' => false,
        ]
    );

    $available = ! is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;

    return $available;
}

function nice_hair_vite_dev_asset_url(string $asset_path): string
{
    return trailingslashit(nice_hair_vite_dev_server_url()) . ltrim($asset_path, '/');
}

function nice_hair_enqueue_vite_entry(string $handle, string $entry, array $deps = []): void
{
    $asset = nice_hair_vite_manifest_entry($entry);

    if ($asset === null || ! isset($asset['file'])) {
        return;
    }

    $base = get_theme_file_uri('/assets/dist/');

    if (! empty($asset['css']) && is_array($asset['css'])) {
        foreach ($asset['css'] as $index => $css_file) {
            wp_enqueue_style(
                sprintf('%s-css-%d', $handle, $index),
                trailingslashit($base) . ltrim((string) $css_file, '/'),
                [],
                null
            );
        }
    }

    wp_enqueue_script(
        $handle,
        trailingslashit($base) . ltrim((string) $asset['file'], '/'),
        $deps,
        null,
        true
    );

    wp_script_add_data($handle, 'type', 'module');
}

function nice_hair_enqueue_vite_dev_entry(string $handle, string $entry): void
{
    $base = nice_hair_vite_dev_server_url();

    wp_enqueue_script(
        'nice-hair-vite-client',
        $base . '/@vite/client',
        [],
        null,
        false
    );

    wp_script_add_data('nice-hair-vite-client', 'type', 'module');

    wp_enqueue_script(
        $handle,
        $base . '/' . ltrim($entry, '/'),
        [],
        null,
        false
    );

    wp_script_add_data($handle, 'type', 'module');
}

function nice_hair_enqueue_vite_style_entry(string $handle, string $entry): void
{
    $asset = nice_hair_vite_manifest_entry($entry);

    if ($asset === null || ! isset($asset['file'])) {
        return;
    }

    $base      = trailingslashit(get_theme_file_uri('/assets/dist/'));
    $css_files = [];

    if (str_ends_with((string) $asset['file'], '.css')) {
        $css_files[] = (string) $asset['file'];
    }

    if (! empty($asset['css']) && is_array($asset['css'])) {
        foreach ($asset['css'] as $css_file) {
            $css_files[] = (string) $css_file;
        }
    }

    $css_files = array_values(array_unique($css_files));

    foreach ($css_files as $index => $css_file) {
        wp_enqueue_style(
            sprintf('%s-%d', $handle, $index),
            $base . ltrim($css_file, '/'),
            [],
            null
        );
    }
}

function nice_hair_enqueue_vite_dev_style(string $handle, string $asset_path): void
{
    wp_enqueue_style(
        $handle,
        nice_hair_vite_dev_asset_url($asset_path),
        [],
        null
    );
}