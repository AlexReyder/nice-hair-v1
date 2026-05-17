<?php

declare(strict_types=1);

/**
 * Shop Pricing ACF options module loader.
 */

$nice_hair_shop_pricing_files = [
    __DIR__ . '/helpers.php',
    __DIR__ . '/fields.php',
    __DIR__ . '/bootstrap.php',
];

foreach ($nice_hair_shop_pricing_files as $nice_hair_shop_pricing_file) {
    if (file_exists($nice_hair_shop_pricing_file)) {
        require_once $nice_hair_shop_pricing_file;
    }
}