<?php

declare(strict_types=1);

/**
 * ACF Options module loader.
 *
 * This file is the single entry point for all ACF option-page modules.
 */

$nice_hair_acf_option_files = [
    __DIR__ . '/layout.php',
     __DIR__ . '/results.php',
    __DIR__ . '/contact.php',
    __DIR__ . '/popup.php',
    __DIR__ . '/popup-salon.php',
    __DIR__ . '/running-line.php',
    __DIR__ . '/shop.php',
    __DIR__ . '/shop-pricing/helpers.php',
    __DIR__ . '/shop-pricing/fields.php',
    __DIR__ . '/shop-pricing/bootstrap.php',
];

foreach ($nice_hair_acf_option_files as $nice_hair_acf_option_file) {
    if (file_exists($nice_hair_acf_option_file)) {
        require_once $nice_hair_acf_option_file;
    }
}