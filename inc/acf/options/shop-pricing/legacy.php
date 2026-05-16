<?php

declare(strict_types=1);

function nice_hair_hide_legacy_shop_pricing_field(array $field): array|false
{
    $current_page = isset($_GET['page']) && is_string($_GET['page'])
        ? sanitize_key(wp_unslash($_GET['page']))
        : '';

    if ($current_page !== nice_hair_shop_pricing_options_page_slug()) {
        return $field;
    }

    return false;
}

add_filter('acf/prepare_field/name=nh_shop_pricing_keratin_transparent', 'nice_hair_hide_legacy_shop_pricing_field');