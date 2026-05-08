<?php
/**
 * Title: Nice Hair / Ассортимент магазина
 * Slug: nice-hair/shop-assortment
 * Categories: nice-hair-shop
 * Inserter: yes
 */

$data = [
    'nh_shop_assortment_eyebrow' => '[ ASSORTMENT ]',
    '_nh_shop_assortment_eyebrow' => 'field_nh_shop_assortment_eyebrow',
    'nh_shop_assortment_title' => 'Our Assortment',
    '_nh_shop_assortment_title' => 'field_nh_shop_assortment_title',
    'nh_shop_assortment_description' => 'Whether you prefer to work with raw hair or want ready-to-install extensions, we provide both - premium hair in all formats.',
    '_nh_shop_assortment_description' => 'field_nh_shop_assortment_description',
    'nh_shop_assortment_anchor' => 'shop-assortment',
    '_nh_shop_assortment_anchor' => 'field_nh_shop_assortment_anchor',
];

$block_json = wp_json_encode([
    'name' => 'acf/nh-shop-assortment',
    'data' => $data,
    'mode' => 'preview',
]);
?>
<!-- wp:acf/nh-shop-assortment <?php echo $block_json; ?> /-->
