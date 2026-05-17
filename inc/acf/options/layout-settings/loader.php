<?php

declare(strict_types=1);

/**
 * Layout Settings ACF options module loader.
 */

$nice_hair_layout_settings_files = [
    __DIR__ . '/shared.php',
    __DIR__ . '/home-salon.php',
    __DIR__ . '/shop.php',
];

foreach ($nice_hair_layout_settings_files as $nice_hair_layout_settings_file) {
    if (file_exists($nice_hair_layout_settings_file)) {
        require_once $nice_hair_layout_settings_file;
    }
}