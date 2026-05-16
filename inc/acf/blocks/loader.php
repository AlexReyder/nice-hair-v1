<?php

declare(strict_types=1);

$nice_hair_acf_block_files = [
    __DIR__ . '/category.php',

    __DIR__ . '/faq/register.php',
    __DIR__ . '/faq/fields.php',

    __DIR__ . '/stylists/register.php',
    __DIR__ . '/stylists/fields.php',

    __DIR__ . '/results/register.php',
    __DIR__ . '/results/fields.php',

    __DIR__ . '/price-quiz/register.php',
    __DIR__ . '/price-quiz/fields.php',

    __DIR__ . '/discounts/register.php',
    __DIR__ . '/discounts/fields.php',

    __DIR__ . '/shop-categories/register.php',
    __DIR__ . '/shop-categories/fields.php',

    __DIR__ . '/shop-assortment/register.php',
    __DIR__ . '/shop-assortment/fields.php',
];

foreach ($nice_hair_acf_block_files as $nice_hair_acf_block_file) {
    if (file_exists($nice_hair_acf_block_file)) {
        require_once $nice_hair_acf_block_file;
    }
}
