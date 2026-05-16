<?php

declare(strict_types=1);

/**
 * Shop Pricing ACF options module loader.
 */

$nice_hair_shop_pricing_acf_files = [
    '/helpers.php',
    '/fields.php',
    '/bootstrap.php',
    '/legacy.php',
];

foreach ($nice_hair_shop_pricing_acf_files as $nice_hair_shop_pricing_acf_file) {
    $nice_hair_shop_pricing_acf_path = __DIR__ . $nice_hair_shop_pricing_acf_file;

    if (file_exists($nice_hair_shop_pricing_acf_path)) {
        require_once $nice_hair_shop_pricing_acf_path;
    }
}