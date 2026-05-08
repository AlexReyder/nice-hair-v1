<?php
/**
 * Single Product - Keratin family layout.
 *
 * Uses Woo variations on pa_weight with a custom chip UI.
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

global $product;

$nh_sku       = $product->get_sku();
$nh_video_url = function_exists('get_field') ? get_field('nh_product_video_url') : '';
$nh_shop_url  = get_permalink(wc_get_page_id('shop'));

$nh_product_terms = wp_get_post_terms($product->get_id(), 'product_cat');
$nh_product_terms = is_array($nh_product_terms) ? $nh_product_terms : [];
$nh_keratin_term = null;
$nh_keratin_child_term = null;

foreach ($nh_product_terms as $nh_term) {
    if ($nh_term instanceof WP_Term && $nh_term->slug === 'keratin') {
        $nh_keratin_term = $nh_term;
        break;
    }
}

if (! $nh_keratin_term instanceof WP_Term) {
    $nh_resolved_keratin_term = get_term_by('slug', 'keratin', 'product_cat');
    $nh_keratin_term = $nh_resolved_keratin_term instanceof WP_Term ? $nh_resolved_keratin_term : null;
}

foreach ($nh_product_terms as $nh_term) {
    if (! $nh_term instanceof WP_Term || ! $nh_keratin_term instanceof WP_Term) {
        continue;
    }

    if ((int) $nh_term->parent === (int) $nh_keratin_term->term_id) {
        $nh_keratin_child_term = $nh_term;
        break;
    }

    $nh_ancestors = array_map('intval', get_ancestors($nh_term->term_id, 'product_cat', 'taxonomy'));

    if (in_array((int) $nh_keratin_term->term_id, $nh_ancestors, true)) {
        $nh_keratin_child_term = $nh_term;
        break;
    }
}

$nh_keratin_url = $nh_keratin_term instanceof WP_Term ? get_term_link($nh_keratin_term) : '';
$nh_keratin_child_url = $nh_keratin_child_term instanceof WP_Term ? get_term_link($nh_keratin_child_term) : '';

if (is_wp_error($nh_keratin_url)) {
    $nh_keratin_url = '';
}

if (is_wp_error($nh_keratin_child_url)) {
    $nh_keratin_child_url = '';
}

$nh_gallery_ids = $product->get_gallery_image_ids();
$nh_featured_id = (int) get_post_thumbnail_id();
$nh_lightbox_image_ids = array_values(array_unique(array_filter(array_map(
    'intval',
    array_merge($nh_featured_id ? [$nh_featured_id] : [], $nh_gallery_ids)
))));
$nh_main_image_id = $nh_featured_id ?: ($nh_lightbox_image_ids[0] ?? 0);
$nh_has_lightbox_gallery = ! empty($nh_lightbox_image_ids) && function_exists('wc_get_gallery_image_html');

$nh_contact_phone = nice_hair_get_contact_phone_display('shop');
$nh_contact_phone_link = nice_hair_get_contact_phone_link('shop');
$nh_contact_address = nice_hair_get_contact_address_plain('shop');

$nh_weight_order = [
    '5g'    => 10,
    '10g'   => 20,
    '50g'   => 30,
    '100g'  => 40,
    '1000g' => 50,
];
$nh_variation_options = [];

if ($product->is_type('variable')) {
    $nh_available_variations = $product->get_available_variations();

    foreach ($nh_available_variations as $nh_variation_data) {
        $nh_variation_id = (int) ($nh_variation_data['variation_id'] ?? 0);
        $nh_variation = wc_get_product($nh_variation_id);

        if (! $nh_variation instanceof WC_Product_Variation) {
            continue;
        }

        $nh_weight_value = (string) ($nh_variation_data['attributes']['attribute_pa_weight'] ?? '');

        if ($nh_weight_value === '') {
            $nh_weight_value = (string) $nh_variation->get_attribute('pa_weight');
        }

        if ($nh_weight_value === '') {
            continue;
        }

        $nh_weight_term = taxonomy_exists('pa_weight')
            ? get_term_by('slug', $nh_weight_value, 'pa_weight')
            : null;
        $nh_weight_label = $nh_weight_term instanceof WP_Term
            ? $nh_weight_term->name
            : $nh_weight_value;
        $nh_max_qty = $nh_variation->get_max_purchase_quantity();
        $nh_is_purchasable = $nh_variation->is_purchasable() && $nh_variation->is_in_stock();

        $nh_variation_unit_price = wc_get_price_to_display($nh_variation);

        $nh_variation_regular_unit_price = null;

        if ($nh_variation->is_on_sale() && $nh_variation->get_regular_price() !== '') {
            $nh_variation_regular_unit_price = wc_get_price_to_display($nh_variation, [
                'price' => (float) $nh_variation->get_regular_price(),
            ]);
        }

        $nh_variation_options[] = [
            'variation_id'    => $nh_variation_id,
            'sku'             => (string) $nh_variation->get_sku(),
            'weight_value'    => $nh_weight_value,
            'weight_label'    => $nh_weight_label,
            'price_html'      => $nh_variation->get_price_html(),
            'is_purchasable'  => $nh_is_purchasable,
            'max_qty'         => $nh_max_qty > 0 ? (int) $nh_max_qty : 0,
            'sort_order'      => $nh_weight_order[$nh_weight_value] ?? 999,
            'unit_price'         => $nh_variation_unit_price,
            'regular_unit_price' => $nh_variation_regular_unit_price,
        ];
    }

    usort($nh_variation_options, static function (array $left, array $right): int {
        if ($left['sort_order'] === $right['sort_order']) {
            return strcmp((string) $left['weight_label'], (string) $right['weight_label']);
        }

        return $left['sort_order'] <=> $right['sort_order'];
    });
}

$nh_default_attributes = $product->is_type('variable') ? $product->get_default_attributes() : [];
$nh_selected_option = null;
$nh_default_weight_value = (string) ($nh_default_attributes['pa_weight'] ?? $nh_default_attributes['attribute_pa_weight'] ?? '');

foreach ($nh_variation_options as $nh_option) {
    if ($nh_default_weight_value !== '' && $nh_option['weight_value'] === $nh_default_weight_value) {
        $nh_selected_option = $nh_option;
        break;
    }
}

if (! is_array($nh_selected_option)) {
    foreach ($nh_variation_options as $nh_option) {
        if ($nh_option['is_purchasable']) {
            $nh_selected_option = $nh_option;
            break;
        }
    }
}

if (! is_array($nh_selected_option) && $nh_variation_options !== []) {
    $nh_selected_option = $nh_variation_options[0];
}

$nh_selected_price_html = is_array($nh_selected_option)
    ? (string) ($nh_selected_option['price_html'] ?? '')
    : $product->get_price_html();
$nh_selected_variation_id = is_array($nh_selected_option)
    ? (int) ($nh_selected_option['variation_id'] ?? 0)
    : 0;
$nh_selected_sku = is_array($nh_selected_option)
    ? (string) ($nh_selected_option['sku'] ?? '')
    : $nh_sku;
$nh_selected_weight_value = is_array($nh_selected_option)
    ? (string) ($nh_selected_option['weight_value'] ?? '')
    : '';
$nh_selected_max_qty = is_array($nh_selected_option)
    ? (int) ($nh_selected_option['max_qty'] ?? 0)
    : 0;
$nh_can_purchase = $product->is_type('variable')
    ? (is_array($nh_selected_option) && ! empty($nh_selected_option['is_purchasable']))
    : ($product->is_purchasable() && $product->is_in_stock());
?>

<section id="product-<?php the_ID(); ?>" <?php wc_product_class('nh-single-product nh-single-product--keratin'); ?>>
    <div class="nh-single-product__container">
        <div class="nh-single-product__top">
            <nav class="nh-single-product__breadcrumb" aria-label="Breadcrumb">
                <a href="<?php echo esc_url(home_url('/')); ?>">[Home]</a> -
                <a href="<?php echo esc_url($nh_shop_url); ?>">[Shop]</a> -
                <?php if ($nh_keratin_url !== '') : ?>
                    <a href="<?php echo esc_url($nh_keratin_url); ?>">[Keratin]</a> -
                <?php endif; ?>
                <?php if ($nh_keratin_child_term instanceof WP_Term && $nh_keratin_child_url !== '') : ?>
                    <a href="<?php echo esc_url($nh_keratin_child_url); ?>">[<?php echo esc_html($nh_keratin_child_term->name); ?>]</a> -
                <?php endif; ?>
                <span>[<?php the_title(); ?>]</span>
            </nav>

            <div class="nh-single-product__contact">
                <p class="nh-single-product__phone"><a href="tel:<?php echo esc_attr($nh_contact_phone_link); ?>"><?php echo esc_html($nh_contact_phone); ?></a></p>
                <p class="nh-single-product__address"><?php echo esc_html($nh_contact_address); ?></p>
            </div>
        </div>

        <div class="nh-single-product__columns">
            <div class="nh-single-product__gallery">
                <?php if ($nh_has_lightbox_gallery) : ?>
                    <button type="button"
                            class="nh-single-product__gallery-main"
                            data-nh-sp-gallery-trigger
                            data-active-index="0"
                            aria-controls="photoswipe-fullscreen-dialog"
                            aria-label="<?php esc_attr_e('Open product gallery', 'nice-hair'); ?>">
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

                <?php if ($nh_lightbox_image_ids !== []) : ?>
                    <div class="nh-single-product__gallery-thumbs">
                        <?php foreach ($nh_lightbox_image_ids as $nh_index => $nh_image_id) : ?>
                            <?php
                            echo wp_get_attachment_image((int) $nh_image_id, 'thumbnail', false, [
                                'class'              => 'nh-single-product__thumb' . ($nh_index === 0 ? ' is-active' : ''),
                                'data-full-src'      => wp_get_attachment_image_url((int) $nh_image_id, 'woocommerce_single'),
                                'data-gallery-index' => (string) $nh_index,
                            ]);
                            ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($nh_has_lightbox_gallery) : ?>
                    <div class="nh-single-product__lightbox-source" data-nh-sp-lightbox-source aria-hidden="true">
                        <?php foreach ($nh_lightbox_image_ids as $nh_index => $nh_image_id) : ?>
                            <?php echo wc_get_gallery_image_html((int) $nh_image_id, $nh_index === 0, $nh_index); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="nh-single-product__gallery-links">
                    <?php if ($nh_video_url) : ?>
                        <a href="<?php echo esc_url($nh_video_url); ?>"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="nh-single-product__action-link">
                            [ VIDEO ] <span class="nh-single-product__link-arrow">&rarr;</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="nh-single-product__info">
                <h1 class="nh-single-product__title"><?php the_title(); ?></h1>

                <?php if ($nh_selected_sku || $nh_sku) : ?>
                    <span class="nh-single-product__sku" data-nh-sp-sku-wrap<?php echo ($nh_selected_sku === '' && $nh_sku === '') ? ' hidden' : ''; ?>>
                        SKU: <span data-nh-sp-sku><?php echo esc_html($nh_selected_sku !== '' ? $nh_selected_sku : $nh_sku); ?></span>
                    </span>
                <?php endif; ?>

                <?php if ($product->get_short_description()) : ?>
                    <div class="nh-single-product__excerpt">
                        <?php echo wp_kses_post($product->get_short_description()); ?>
                    </div>
                <?php endif; ?>

                <?php if ($nh_variation_options !== []) : ?>
                    <div class="nh-single-product__option-group">
                        <span class="nh-single-product__option-label">[Size:]</span>

                        <div class="nh-single-product__option-list" data-nh-variation-options>
                            <?php foreach ($nh_variation_options as $nh_option) : ?>
                                <button
                                    type="button"
                                    class="nh-single-product__option-chip<?php echo $nh_selected_variation_id === (int) $nh_option['variation_id'] ? ' is-active' : ''; ?><?php echo ! $nh_option['is_purchasable'] ? ' is-disabled' : ''; ?>"
                                    data-nh-variation-option
                                    data-variation-id="<?php echo esc_attr((string) $nh_option['variation_id']); ?>"
                                    data-sku="<?php echo esc_attr((string) $nh_option['sku']); ?>"
                                    data-weight-value="<?php echo esc_attr((string) $nh_option['weight_value']); ?>"
                                    data-price-html="<?php echo esc_attr((string) $nh_option['price_html']); ?>"
                                    data-purchasable="<?php echo $nh_option['is_purchasable'] ? '1' : '0'; ?>"
                                    data-max-qty="<?php echo esc_attr($nh_option['max_qty'] > 0 ? (string) $nh_option['max_qty'] : ''); ?>"
                                    data-unit-price="<?php echo esc_attr((string) $nh_option['unit_price']); ?>"
                                    data-unit-regular-price="<?php echo esc_attr($nh_option['regular_unit_price'] !== null ? (string) $nh_option['regular_unit_price'] : ''); ?>"
                                    data-original-price-html="<?php echo esc_attr((string) $nh_option['price_html']); ?>"
                                    <?php disabled(! $nh_option['is_purchasable']); ?>
                                >
                                    <?php echo esc_html((string) $nh_option['weight_label']); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="nh-single-product__purchase">
                    <?php if ($nh_selected_price_html !== '') : ?>
                       <div class="nh-single-product__price"
     data-nh-sp-price
     data-nh-sp-price-decimals="<?php echo esc_attr((string) wc_get_price_decimals()); ?>"
     data-nh-sp-decimal-separator="<?php echo esc_attr(wc_get_price_decimal_separator()); ?>"
     data-nh-sp-thousand-separator="<?php echo esc_attr(wc_get_price_thousand_separator()); ?>"
     data-nh-sp-price-format="<?php echo esc_attr(get_woocommerce_price_format()); ?>"
     data-nh-sp-currency-symbol="<?php echo esc_attr(get_woocommerce_currency_symbol()); ?>">
    <?php echo $nh_selected_price_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>
                    <?php else : ?>
                        <p class="nh-single-product__price-request" data-nh-sp-price><?php esc_html_e('Price on request', 'nice-hair'); ?></p>
                    <?php endif; ?>

                    <form class="nh-single-product__cart-form"
                          action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>"
                          method="post"
                          enctype="multipart/form-data"
                          data-product-id="<?php echo esc_attr((string) $product->get_id()); ?>"
                          data-product-type="<?php echo esc_attr($product->get_type()); ?>">
                        <input type="hidden" name="add-to-cart" value="<?php echo esc_attr((string) $product->get_id()); ?>">
                        <input type="hidden" name="product_id" value="<?php echo esc_attr((string) $product->get_id()); ?>">

                        <?php if ($product->is_type('variable')) : ?>
                            <input type="hidden" name="variation_id" value="<?php echo esc_attr((string) $nh_selected_variation_id); ?>" data-nh-variation-id>
                            <input type="hidden" name="attribute_pa_weight" value="<?php echo esc_attr($nh_selected_weight_value); ?>" data-nh-variation-attribute="attribute_pa_weight">
                        <?php endif; ?>

                        <div class="nh-single-product__actions<?php echo ! $nh_can_purchase ? ' nh-single-product__actions--stockless' : ''; ?>">
                            <?php if ($nh_can_purchase) : ?>
                                <div class="nh-single-product__qty">
                                    <button type="button" class="nh-single-product__qty-btn" data-nh-sp-qty="minus" aria-label="Decrease quantity">&minus;</button>
                                    <input type="number"
                                           class="nh-single-product__qty-input"
                                           name="quantity"
                                           value="1"
                                           min="1"
                                           max="<?php echo esc_attr($nh_selected_max_qty > 0 ? (string) $nh_selected_max_qty : ''); ?>"
                                           step="1"
                                           inputmode="numeric"
                                           aria-label="Quantity">
                                    <button type="button" class="nh-single-product__qty-btn" data-nh-sp-qty="plus" aria-label="Increase quantity">+</button>
                                </div>
                            <?php else : ?>
                                <p class="nh-single-product__out-of-stock"><?php esc_html_e('Currently unavailable', 'nice-hair'); ?></p>
                            <?php endif; ?>

                            <div class="nh-single-product__cta nh-single-product__cta--primary">
                                <span class="nh-single-product__cta-control nh-cta-link">
                                    <button type="submit"
                                            class="nh-single-product__cta-btn nh-single-product__cta-btn--primary wp-block-button__link"
                                            data-nh-atc-submit
                                            <?php disabled(! $nh_can_purchase); ?>>
                                        Add to cart
                                    </button>
                                </span>
                            </div>

                            <div class="nh-single-product__cta nh-single-product__cta--secondary">
                                <?php nice_hair_render_shop_consultation_cta($product); ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php nice_hair_render_shop_consultation_drawer($product); ?>
</section>
