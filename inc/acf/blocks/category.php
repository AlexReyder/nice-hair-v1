<?php

declare(strict_types=1);

function nice_hair_register_block_category(array $categories): array
{
    array_unshift($categories, [
        'slug'  => 'nice-hair',
        'title' => __('Nice Hair', 'nice-hair'),
    ]);

    return $categories;
}
add_filter('block_categories_all', 'nice_hair_register_block_category');
