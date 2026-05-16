<?php

declare(strict_types=1);

/**
 * Product/category ACF field groups loader.
 *
 * Generated product field group files live in this directory and are loaded
 * automatically. Keep this loader small: each field group should remain in its
 * own file to make product ACF changes isolated and reviewable.
 */

$nice_hair_product_acf_field_files = glob(__DIR__ . '/*.php') ?: [];
sort($nice_hair_product_acf_field_files);

foreach ($nice_hair_product_acf_field_files as $nice_hair_product_acf_field_file) {
    if (basename($nice_hair_product_acf_field_file) === 'loader.php') {
        continue;
    }

    require_once $nice_hair_product_acf_field_file;
}