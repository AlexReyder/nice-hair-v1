<?php

declare(strict_types=1);

/**
 * Product/category ACF field groups loader.
 *
 * Product modules can be either flat PHP files in this directory
 * or nested modules with their own loader.php.
 */

$nice_hair_product_acf_module_loaders = [
    __DIR__ . '/custom-hair/loader.php',
];

foreach ($nice_hair_product_acf_module_loaders as $nice_hair_product_acf_module_loader) {
    if (file_exists($nice_hair_product_acf_module_loader)) {
        require_once $nice_hair_product_acf_module_loader;
    }
}

$nice_hair_product_acf_field_files = glob(__DIR__ . '/*.php') ?: [];
sort($nice_hair_product_acf_field_files);

foreach ($nice_hair_product_acf_field_files as $nice_hair_product_acf_field_file) {
    if (basename($nice_hair_product_acf_field_file) === 'loader.php') {
        continue;
    }

    require_once $nice_hair_product_acf_field_file;
}