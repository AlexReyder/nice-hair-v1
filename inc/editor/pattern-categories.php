<?php
declare(strict_types=1);

function nice_hair_register_pattern_categories(): void
{
    if (! function_exists('register_block_pattern_category')) {
        return;
    }

    register_block_pattern_category('nice-hair-home', [
        'label' => __('Nice Hair — Home', 'nice-hair'),
    ]);

    register_block_pattern_category('nice-hair-salon', [
        'label' => __('Nice Hair — Salon', 'nice-hair'),
    ]);

    register_block_pattern_category('nice-hair-shop', [
        'label' => __('Nice Hair — Shop', 'nice-hair'),
    ]);

    register_block_pattern_category('nice-hair-shipping', [
        'label' => __('Nice Hair — Shipping', 'nice-hair'),
    ]);

    register_block_pattern_category('nice-hair-blog', [
        'label' => __('Nice Hair — Blog', 'nice-hair'),
    ]);

    register_block_pattern_category('nice-hair-shared', [
        'label' => __('Nice Hair — Shared', 'nice-hair'),
    ]);
}
add_action('init', 'nice_hair_register_pattern_categories');
