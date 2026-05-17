<?php

declare(strict_types=1);

/**
 * Central ACF module loader.
 *
 * This file is the single ACF entry point for the theme. It keeps the
 * top-level functions.php include list short while preserving backward
 * compatibility with the incremental refactor steps.
 */

$nice_hair_acf_files = [
    '/blocks/loader.php',
    '/fields.php',
    '/options/loader.php',
    
];

foreach ($nice_hair_acf_files as $nice_hair_acf_file) {
    $nice_hair_acf_path = __DIR__ . $nice_hair_acf_file;

    if (file_exists($nice_hair_acf_path)) {
        require_once $nice_hair_acf_path;
    }
}
