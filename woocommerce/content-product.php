<?php
/**
 * WooCommerce — Product card in archive grid.
 *
 * @see https://woocommerce.com/document/template-structure/
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

global $product;

if (empty($product) || ! $product->is_visible()) {
    return;
}

$nh_card_attributes = function_exists('nice_hair_get_product_card_attributes')
    ? nice_hair_get_product_card_attributes($product)
    : [];

$nh_family = function_exists('nice_hair_get_product_family')
    ? nice_hair_get_product_family($product)
    : '';

$nh_is_unique = function_exists('nice_hair_is_unique_item_product')
    ? nice_hair_is_unique_item_product($product)
    : false;

$nh_is_out_of_stock = ! $product->is_in_stock();

$nh_is_keratin = $nh_family === 'keratin';
$nh_is_ready = $nh_family === 'ready_to_install';
$nh_is_exclusive = $nh_family === 'exclusive_hair';
$nh_is_custom = $nh_family === 'custom_hair';
$nh_is_tools = $nh_family === 'tools';

$nh_is_generic = ! $nh_is_keratin
    && ! $nh_is_ready
    && ! $nh_is_exclusive
    && ! $nh_is_custom
    && ! $nh_is_tools;

$nh_permalink = get_the_permalink();

$nh_sku = function_exists('nice_hair_get_product_archive_sku')
    ? nice_hair_get_product_archive_sku($product)
    : trim((string) $product->get_sku());

$nh_status_label = '';

$nh_card_class_names = [
    'nh-product-card',
    $nh_is_out_of_stock ? 'nh-product-card--sold' : '',
    $nh_is_keratin ? 'nh-product-card--keratin' : '',
    $nh_is_ready ? 'nh-product-card--ready-to-install' : '',
    $nh_is_exclusive ? 'nh-product-card--exclusive-hair' : '',
    $nh_is_custom ? 'nh-product-card--custom-hair' : '',
    $nh_is_tools ? 'nh-product-card--tools' : '',
    $nh_is_generic ? 'nh-product-card--generic' : '',
];

$nh_keratin_weight_labels = $nh_is_keratin && function_exists('nice_hair_get_product_variation_attribute_labels')
    ? nice_hair_get_product_variation_attribute_labels($product, 'pa_weight')
    : [];

$nh_keratin_min_price = $nh_is_keratin && function_exists('nice_hair_get_product_min_price')
    ? nice_hair_get_product_min_price($product)
    : null;

$nh_keratin_min_price_html = $nh_keratin_min_price !== null && function_exists('wc_price')
    ? wc_price($nh_keratin_min_price)
    : '';

$nh_card_price = $product->get_price();

/**
 * Exclusive Hair fallback:
 *
 * If WooCommerce price is empty, show ACF "Base lot price" in catalog.
 * This keeps catalog cards filled even when the product is priced only
 * through the Exclusive Hair lot pricing fields.
 */
if (
    ($nh_card_price === '' || ! is_numeric($nh_card_price))
    && $nh_is_exclusive
    && function_exists('nice_hair_get_product_base_lot_price')
) {
    $nh_base_lot_price = nice_hair_get_product_base_lot_price($product);

    if ($nh_base_lot_price !== null) {
        $nh_card_price = (string) $nh_base_lot_price;
    }
}

$nh_card_price_html = '';

if ($nh_card_price !== '' && is_numeric($nh_card_price) && function_exists('wc_price') && function_exists('wc_get_price_to_display')) {
    $nh_card_display_price = wc_get_price_to_display($product, [
        'price' => (float) $nh_card_price,
    ]);

    $nh_card_price_html = wc_price($nh_card_display_price) . $product->get_price_suffix($nh_card_display_price);
}

$nh_discount_icon_url = get_template_directory_uri() . '/assets/images/ecommerce/discount-icon.svg';
$nh_hot_icon_url = get_template_directory_uri() . '/assets/images/ecommerce/hot-icon.svg';

if ($nh_is_out_of_stock) {
    $nh_status_label = $nh_is_unique ? __('Sold', 'nice-hair') : __('Out of stock', 'nice-hair');
}

$nh_show_icon_badges = $nh_status_label === ''
    && ! $nh_is_keratin
    && ! $nh_is_tools
    && ! $nh_is_generic
    && ($product->is_on_sale() || $product->is_featured());
?>

<li <?php wc_product_class(implode(' ', array_filter($nh_card_class_names)), $product); ?>>
    <a href="<?php echo esc_url($nh_permalink); ?>" class="nh-product-card__link">
        <div class="nh-product-card__image">
            <?php echo $product->get_image('woocommerce_thumbnail'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

            <?php if ($nh_show_icon_badges) : ?>
                <div class="nh-product-card__badge-stack">
                    <?php if ($product->is_on_sale()) : ?>
                        <span class="nh-product-card__icon-badge nh-product-card__icon-badge--sale" aria-label="<?php esc_attr_e('Sale', 'nice-hair'); ?>">
                            <img class="nh-product-card__icon-badge-image" src="<?php echo esc_url($nh_discount_icon_url); ?>" alt="" loading="lazy" decoding="async">
                        </span>
                    <?php endif; ?>

                    <?php if ($product->is_featured()) : ?>
                        <span class="nh-product-card__icon-badge nh-product-card__icon-badge--best" aria-label="<?php esc_attr_e('Best Seller', 'nice-hair'); ?>">
                            <img class="nh-product-card__icon-badge-image" src="<?php echo esc_url($nh_hot_icon_url); ?>" alt="" loading="lazy" decoding="async">
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($nh_status_label !== '') : ?>
                <span class="nh-product-card__badge nh-product-card__badge--sold">
                    <?php echo esc_html($nh_status_label); ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="nh-product-card__info">
            <h3 class="nh-product-card__title"><?php the_title(); ?></h3>

            <?php if ($nh_is_keratin) : ?>
                <?php if ($nh_keratin_weight_labels !== []) : ?>
                    <ul class="nh-product-card__weights" aria-label="<?php esc_attr_e('Available weights', 'nice-hair'); ?>">
                        <?php foreach ($nh_keratin_weight_labels as $nh_weight_label) : ?>
                            <li class="nh-product-card__weight"><?php echo esc_html($nh_weight_label); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <div class="nh-product-card__footer nh-product-card__keratin-footer">
                    <div class="nh-product-card__meta-stack nh-product-card__keratin-meta">
                        <?php if ($nh_sku !== '') : ?>
                            <span class="nh-product-card__sku"><?php echo esc_html($nh_sku); ?></span>
                        <?php endif; ?>

                        <?php if ($nh_keratin_min_price_html !== '') : ?>
                            <div class="nh-product-card__price nh-product-card__price--from">
                                <span class="nh-product-card__price-prefix"><?php esc_html_e('FROM', 'nice-hair'); ?></span>
                                <?php echo $nh_keratin_min_price_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <span class="nh-product-card__more">
                        [ MORE ] <span class="nh-product-card__more-arrow">&rarr;</span>
                    </span>
                </div>

            <?php elseif ($nh_is_custom) : ?>
                <span class="nh-product-card__more">
                    [MORE] <span class="nh-product-card__more-arrow">&rarr;</span>
                </span>

            <?php elseif ($nh_is_ready || $nh_is_exclusive || $nh_is_tools || $nh_is_generic) : ?>
                <div class="nh-product-card__footer nh-product-card__ready-footer">
                    <div class="nh-product-card__meta-stack nh-product-card__ready-meta">
                        <?php if ($nh_sku !== '') : ?>
                            <span class="nh-product-card__sku"><?php echo esc_html($nh_sku); ?></span>
                        <?php endif; ?>

                        <?php if ($nh_card_price_html !== '') : ?>
                            <div class="nh-product-card__price">
                                <?php echo $nh_card_price_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <span class="nh-product-card__more">
                        <?php echo esc_html(($nh_is_tools || $nh_is_generic) ? '[ MORE ]' : '[MORE]'); ?>
                        <span class="nh-product-card__more-arrow">&rarr;</span>
                    </span>
                </div>

            <?php else : ?>
                <?php if ($nh_card_attributes !== []) : ?>
                    <ul class="nh-product-card__meta" aria-label="<?php esc_attr_e('Product attributes', 'nice-hair'); ?>">
                        <?php foreach ($nh_card_attributes as $nh_card_attribute) : ?>
                            <li class="nh-product-card__meta-item"><?php echo esc_html($nh_card_attribute); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if ($nh_card_price_html !== '') : ?>
                    <div class="nh-product-card__price">
                        <?php echo $nh_card_price_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </a>

    <?php if (! $nh_is_keratin && ! $nh_is_ready && ! $nh_is_exclusive && ! $nh_is_custom && ! $nh_is_tools && ! $nh_is_generic && ! $product->is_type('variable') && $product->is_in_stock() && $product->is_purchasable()) : ?>
        <?php woocommerce_template_loop_add_to_cart(); ?>
    <?php elseif ($nh_status_label !== '' && ! $nh_is_ready && ! $nh_is_exclusive && ! $nh_is_custom && ! $nh_is_tools && ! $nh_is_generic) : ?>
        <span class="nh-product-card__stock-state"><?php echo esc_html($nh_status_label); ?></span>
    <?php endif; ?>
</li>