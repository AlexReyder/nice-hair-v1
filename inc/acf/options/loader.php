<?php

declare(strict_types=1);

/**
 * ACF Options module loader.
 *
 * New structure:
 * - inc/acf/options/layout.php
 * - inc/acf/options/contact.php
 * - inc/acf/options/popup.php
 * - inc/acf/options/popup-salon.php
 * - inc/acf/options/running-line.php
 * - inc/acf/options/shop.php
 *
 * The legacy fallback paths keep the site working even before the migration
 * script moves the flat option files into this directory.
 */

$nice_hair_acf_option_files = [
    [
        'new'    => __DIR__ . '/layout.php',
        'legacy' => dirname(__DIR__) . '/options.php',
    ],
    [
        'new'    => __DIR__ . '/contact.php',
        'legacy' => dirname(__DIR__) . '/contact-options.php',
    ],
    [
        'new'    => __DIR__ . '/popup.php',
        'legacy' => dirname(__DIR__) . '/popup-options.php',
    ],
    [
        'new'    => __DIR__ . '/popup-salon.php',
        'legacy' => dirname(__DIR__) . '/popup-salon-options.php',
    ],
    [
        'new'    => __DIR__ . '/running-line.php',
        'legacy' => dirname(__DIR__) . '/running-line-options.php',
    ],
    [
        'new'    => __DIR__ . '/shop.php',
        'legacy' => dirname(__DIR__) . '/shop-options.php',
    ],
    [
    'new'    => __DIR__ . '/shop-pricing/loader.php',
    'legacy' => '',
],
];

foreach ($nice_hair_acf_option_files as $nice_hair_acf_option_file) {
    $nice_hair_acf_new_path = (string) $nice_hair_acf_option_file['new'];
    $nice_hair_acf_legacy_path = (string) $nice_hair_acf_option_file['legacy'];

    if (file_exists($nice_hair_acf_new_path)) {
        require_once $nice_hair_acf_new_path;
        continue;
    }

    if (file_exists($nice_hair_acf_legacy_path)) {
        require_once $nice_hair_acf_legacy_path;
    }
}
