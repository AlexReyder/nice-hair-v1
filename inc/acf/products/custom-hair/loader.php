<?php

declare(strict_types=1);

/**
 * Custom Hair ACF module loader.
 */

$nice_hair_custom_hair_acf_files = [
    __DIR__ . '/attribute-choices.php',
    __DIR__ . '/params.php',
    __DIR__ . '/colors.php',
];

foreach ($nice_hair_custom_hair_acf_files as $nice_hair_custom_hair_acf_file) {
    if (file_exists($nice_hair_custom_hair_acf_file)) {
        require_once $nice_hair_custom_hair_acf_file;
    }
}