<?php
/**
 * Single Product — Generic fallback.
 *
 * Used for products without a predefined shop family.
 * Visually follows the Tools single product layout.
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

global $product;

if (! $product instanceof WC_Product) {
    return;
}

$nh_sku = $product->get_sku();
$nh_price = $product->get_price();
$nh_video_url = function_exists('get_field') ? get_field('nh_product_video_url') : '';

$nh_shop_url = function_exists('wc_get_page_id')
    ? get_permalink(wc_get_page_id('shop'))
    : home_url('/shop/');

$nh_product_terms = get_the_terms($product->get_id(), 'product_cat');
$nh_primary_term = null;

if (is_array($nh_product_terms) && $nh_product_terms !== []) {
    $nh_valid_terms = array_values(array_filter(
        $nh_product_terms,
        static fn (mixed $term): bool => $term instanceof WP_Term
    ));

    usort($nh_valid_terms, static function (WP_Term $left, WP_Term $right): int {
        return (int) $right->parent <=> (int) $left->parent;
    });

    $nh_primary_term = $nh_valid_terms[0] ?? null;
}

$nh_category_url = '';

if ($nh_primary_term instanceof WP_Term) {
    $nh_category_link = get_term_link($nh_primary_term);

    if (is_string($nh_category_link) && ! is_wp_error($nh_category_link)) {
        $nh_category_url = $nh_category_link;
    }
}

$nh_gallery_ids = $product->get_gallery_image_ids();
$nh_featured_id = (int) get_post_thumbnail_id();

$nh_lightbox_image_ids = array_values(array_unique(array_filter(array_map(
    'intval',
    array_merge($nh_featured_id ? [$nh_featured_id] : [], $nh_gallery_ids)
))));

$nh_main_image_id = $nh_featured_id ?: ($nh_lightbox_image_ids[0] ?? 0);
$nh_has_lightbox_gallery = ! empty($nh_lightbox_image_ids) && function_exists('wc_get_gallery_image_html');

$nh_contact_phone = function_exists('nice_hair_get_contact_phone_display')
    ? nice_hair_get_contact_phone_display('shop')
    : '';

$nh_contact_phone_link = function_exists('nice_hair_get_contact_phone_link')
    ? nice_hair_get_contact_phone_link('shop')
    : '';

$nh_contact_address = function_exists('nice_hair_get_contact_address_plain')
    ? nice_hair_get_contact_address_plain('shop')
    : '';
?>

<section id="product-<?php the_ID(); ?>" <?php wc_product_class('nh-single-product nh-single-product--generic'); ?>>
    <div class="nh-single-product__container">

        <div class="nh-single-product__top">
            <nav class="nh-single-product__breadcrumb" aria-label="<?php esc_attr_e('Breadcrumb', 'nice-hair'); ?>">
                <a href="<?php echo esc_url(home_url('/')); ?>">[Home]</a> -
                <a href="<?php echo esc_url((string) $nh_shop_url); ?>">[Shop]</a> -

                <?php if ($nh_primary_term instanceof WP_Term && $nh_category_url !== '') : ?>
                    <a href="<?php echo esc_url($nh_category_url); ?>">[<?php echo esc_html($nh_primary_term->name); ?>]</a> -
                <?php endif; ?>

                <span>[<?php the_title(); ?>]</span>
            </nav>

            <?php if ($nh_contact_phone !== '' || $nh_contact_address !== '') : ?>
                <div class="nh-single-product__contact">
                    <?php if ($nh_contact_phone !== '') : ?>
                        <p class="nh-single-product__phone">
                            <a href="tel:<?php echo esc_attr($nh_contact_phone_link); ?>">
                                <?php echo esc_html($nh_contact_phone); ?>
                            </a>
                        </p>
                    <?php endif; ?>

                    <?php if ($nh_contact_address !== '') : ?>
                        <p class="nh-single-product__address"><?php echo esc_html($nh_contact_address); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="nh-single-product__columns">
            <div class="nh-single-product__gallery">
                <?php if ($nh_has_lightbox_gallery) : ?>
                    <button
                        type="button"
                        class="nh-single-product__gallery-main"
                        data-nh-sp-gallery-trigger
                        data-active-index="0"
                        aria-controls="photoswipe-fullscreen-dialog"
                        aria-label="<?php esc_attr_e('Open product gallery', 'nice-hair'); ?>"
                    >
                        <?php
                        echo wp_get_attachment_image($nh_main_image_id, 'woocommerce_single', false, [
                            'class'         => 'nh-single-product__main-img',
                            'data-full-src' => wp_get_attachment_image_url($nh_main_image_id, 'full'),
                        ]);
                        ?>
                    </button>
                <?php else : ?>
                    <div class="nh-single-product__gallery-main">
                        <?php
                        if ($nh_main_image_id) {
                            echo wp_get_attachment_image($nh_main_image_id, 'woocommerce_single', false, [
                                'class'         => 'nh-single-product__main-img',
                                'data-full-src' => wp_get_attachment_image_url($nh_main_image_id, 'full'),
                            ]);
                        } else {
                            echo wc_placeholder_img('woocommerce_single'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <?php if ($nh_lightbox_image_ids) : ?>
                    <div class="nh-single-product__gallery-thumbs">
                        <?php
                        foreach ($nh_lightbox_image_ids as $index => $img_id) {
                            echo wp_get_attachment_image((int) $img_id, 'thumbnail', false, [
                                'class'              => 'nh-single-product__thumb' . ($index === 0 ? ' is-active' : ''),
                                'data-full-src'      => wp_get_attachment_image_url((int) $img_id, 'woocommerce_single'),
                                'data-gallery-index' => (string) $index,
                            ]);
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <?php if ($nh_has_lightbox_gallery) : ?>
                    <div class="nh-single-product__lightbox-source" data-nh-sp-lightbox-source aria-hidden="true">
                        <?php
                        foreach ($nh_lightbox_image_ids as $index => $img_id) {
                            echo wc_get_gallery_image_html((int) $img_id, $index === 0, $index); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <?php if ($nh_video_url) : ?>
                    <div class="nh-single-product__gallery-links">
                        <a
                            href="<?php echo esc_url((string) $nh_video_url); ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="nh-single-product__action-link"
                        >
                            [ VIDEO ] <span class="nh-single-product__link-arrow">&rarr;</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="nh-single-product__info">
                <h1 class="nh-single-product__title"><?php the_title(); ?></h1>

                <?php if ($nh_sku) : ?>
                    <span class="nh-single-product__sku">SKU: <?php echo esc_html($nh_sku); ?></span>
                <?php endif; ?>

                <?php if ($product->get_short_description()) : ?>
                    <div class="nh-single-product__excerpt">
                        <?php echo wp_kses_post($product->get_short_description()); ?>
                    </div>
                <?php endif; ?>

                <div class="nh-single-product__purchase">
                    <?php if ($nh_price !== '') : ?>
                        <div class="nh-single-product__price">
                            <?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </div>
                    <?php else : ?>
                        <p class="nh-single-product__price-request">
                            <?php esc_html_e('Price on request', 'nice-hair'); ?>
                        </p>
                    <?php endif; ?>

                    <?php if ($product->is_purchasable() && $product->is_in_stock() && ! $product->is_type('variable')) : ?>
                        <form
                            class="nh-single-product__cart-form"
                            action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>"
                            method="post"
                            enctype="multipart/form-data"
                            data-product-id="<?php echo esc_attr((string) $product->get_id()); ?>"
                        >
                            <input type="hidden" name="add-to-cart" value="<?php echo esc_attr((string) $product->get_id()); ?>">
                            <input type="hidden" name="product_id" value="<?php echo esc_attr((string) $product->get_id()); ?>">

                            <div class="nh-single-product__actions">
                                <div class="nh-single-product__qty">
                                    <button type="button" class="nh-single-product__qty-btn" data-nh-sp-qty="minus" aria-label="<?php esc_attr_e('Decrease quantity', 'nice-hair'); ?>">&minus;</button>

                                    <input
                                        type="number"
                                        class="nh-single-product__qty-input"
                                        name="quantity"
                                        value="1"
                                        min="1"
                                        max="<?php echo esc_attr($product->get_max_purchase_quantity() > 0 ? (string) $product->get_max_purchase_quantity() : ''); ?>"
                                        step="1"
                                        inputmode="numeric"
                                        aria-label="<?php esc_attr_e('Quantity', 'nice-hair'); ?>"
                                    >

                                    <button type="button" class="nh-single-product__qty-btn" data-nh-sp-qty="plus" aria-label="<?php esc_attr_e('Increase quantity', 'nice-hair'); ?>">+</button>
                                </div>

                                <div class="nh-single-product__cta nh-single-product__cta--primary">
                                    <span class="nh-single-product__cta-control nh-cta-link">
                                        <button
                                            type="submit"
                                            name="add-to-cart"
                                            value="<?php echo esc_attr((string) $product->get_id()); ?>"
                                            class="nh-single-product__cta-btn nh-single-product__cta-btn--primary wp-block-button__link"
                                            data-nh-atc-submit
                                        >
                                            <?php esc_html_e('Add to cart', 'nice-hair'); ?>
                                        </button>
                                    </span>
                                </div>

                                <?php if (function_exists('nice_hair_render_shop_consultation_cta')) : ?>
                                    <div class="nh-single-product__cta nh-single-product__cta--secondary">
                                        <?php nice_hair_render_shop_consultation_cta($product); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </form>
                    <?php else : ?>
                        <div class="nh-single-product__actions nh-single-product__actions--stockless">
                            <?php if (! $product->is_in_stock()) : ?>
                                <p class="nh-single-product__out-of-stock">
                                    <?php esc_html_e('Out of stock', 'nice-hair'); ?>
                                </p>
                            <?php endif; ?>

                            <?php if ($product->is_type('variable')) : ?>
                                <p class="nh-single-product__out-of-stock">
                                    <?php esc_html_e('Please configure this variable product in one of the predefined product families or use a simple product type.', 'nice-hair'); ?>
                                </p>
                            <?php endif; ?>

                            <?php if (function_exists('nice_hair_render_shop_consultation_cta')) : ?>
                                <div class="nh-single-product__cta nh-single-product__cta--secondary">
                                    <?php nice_hair_render_shop_consultation_cta($product); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php
    if (function_exists('nice_hair_render_shop_consultation_drawer')) {
        nice_hair_render_shop_consultation_drawer($product);
    }
    ?>
</section>