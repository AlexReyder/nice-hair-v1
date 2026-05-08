<?php
/**
 * Single Product - Ready to install layout.
 *
 * Reuses the dark storefront language from Tools/Keratin and adds
 * an Extension Type-driven "How to use" drawer with product fallback.
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

global $product;

$nh_sku = $product->get_sku();
$nh_video_url = function_exists('get_field') ? get_field('nh_product_video_url') : '';
$nh_how_to_use = function_exists('nice_hair_get_product_how_to_use_data')
    ? nice_hair_get_product_how_to_use_data($product)
    : [
        'image'       => null,
        'text'        => '',
        'has_image'   => false,
        'has_text'    => false,
        'has_content' => false,
        'source'      => '',
        'term_id'     => 0,
        'term_name'   => '',
    ];
$nh_how_to_use_image = is_array($nh_how_to_use['image'] ?? null) ? $nh_how_to_use['image'] : null;
$nh_how_to_use_text = is_string($nh_how_to_use['text'] ?? null) ? $nh_how_to_use['text'] : '';
$nh_has_how_to_use_image = (bool) ($nh_how_to_use['has_image'] ?? false);
$nh_has_how_to_use_text = (bool) ($nh_how_to_use['has_text'] ?? false);
$nh_has_how_to_use = (bool) ($nh_how_to_use['has_content'] ?? false);
$nh_how_to_use_drawer_id = 'ready-how-to-use-' . (string) $product->get_id();

$nh_shop_url = trailingslashit((string) get_permalink(wc_get_page_id('shop'))) . '#catalog';
$nh_ready_term = get_term_by('slug', 'ready-to-install', 'product_cat');
$nh_ready_term = $nh_ready_term instanceof WP_Term ? $nh_ready_term : null;
$nh_product_terms = wp_get_post_terms($product->get_id(), 'product_cat');
$nh_product_terms = is_array($nh_product_terms) ? $nh_product_terms : [];
$nh_ready_child_term = null;

foreach ($nh_product_terms as $nh_term) {
    if (! $nh_term instanceof WP_Term || ! $nh_ready_term instanceof WP_Term) {
        continue;
    }

    if ((int) $nh_term->parent === (int) $nh_ready_term->term_id) {
        $nh_ready_child_term = $nh_term;
        break;
    }

    $nh_ancestors = array_map('intval', get_ancestors($nh_term->term_id, 'product_cat', 'taxonomy'));

    if (in_array((int) $nh_ready_term->term_id, $nh_ancestors, true)) {
        $nh_ready_child_term = $nh_term;
        break;
    }
}

$nh_ready_url = $nh_ready_term instanceof WP_Term ? get_term_link($nh_ready_term) : '';
$nh_ready_child_url = $nh_ready_child_term instanceof WP_Term ? get_term_link($nh_ready_child_term) : '';

if (is_wp_error($nh_ready_url)) {
    $nh_ready_url = '';
}

if (is_wp_error($nh_ready_child_url)) {
    $nh_ready_child_url = '';
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

$nh_is_unique = function_exists('nice_hair_is_unique_item_product')
    ? nice_hair_is_unique_item_product($product)
    : false;
$nh_can_purchase = $product->is_purchasable() && $product->is_in_stock();
$nh_stock_label = $product->is_in_stock()
    ? ''
    : ($nh_is_unique ? __('Sold', 'nice-hair') : __('Out of stock', 'nice-hair'));
?>

<section id="product-<?php the_ID(); ?>" <?php wc_product_class('nh-single-product nh-single-product--ready-to-install'); ?>>
    <div class="nh-single-product__container">
        <div class="nh-single-product__top">
            <nav class="nh-single-product__breadcrumb" aria-label="Breadcrumb">
                <a href="<?php echo esc_url(home_url('/')); ?>">[Home]</a> -
                <a href="<?php echo esc_url($nh_shop_url); ?>">[Shop]</a> -
                <?php if ($nh_ready_url !== '') : ?>
                    <a href="<?php echo esc_url($nh_ready_url); ?>">[Ready to install]</a> -
                <?php endif; ?>
                <?php if ($nh_ready_child_term instanceof WP_Term && $nh_ready_child_url !== '') : ?>
                    <a href="<?php echo esc_url($nh_ready_child_url); ?>">[<?php echo esc_html($nh_ready_child_term->name); ?>]</a> -
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

                <?php if ($nh_video_url || $nh_has_how_to_use) : ?>
                    <div class="nh-single-product__gallery-links">
                        <?php if ($nh_video_url) : ?>
                            <a href="<?php echo esc_url($nh_video_url); ?>"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="nh-single-product__action-link">
                                [VIDEO] <span class="nh-single-product__link-arrow">&rarr;</span>
                            </a>
                        <?php endif; ?>

                        <?php if ($nh_has_how_to_use) : ?>
                            <button type="button"
                                    class="nh-single-product__action-link nh-single-product__action-link--button"
                                    data-nh-content-drawer-target="<?php echo esc_attr($nh_how_to_use_drawer_id); ?>"
                                    aria-haspopup="dialog"
                                    aria-controls="<?php echo esc_attr($nh_how_to_use_drawer_id); ?>">
                                [HOW TO USE?] <span class="nh-single-product__link-arrow">&rarr;</span>
                            </button>
                        <?php endif; ?>
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
                    <?php if ($product->get_price() !== '') : ?>
                        <div class="nh-single-product__price" data-nh-sp-price>
                            <?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </div>
                    <?php else : ?>
                        <p class="nh-single-product__price-request" data-nh-sp-price><?php esc_html_e('Price on request', 'nice-hair'); ?></p>
                    <?php endif; ?>

                    <?php if ($nh_can_purchase) : ?>
                        <form class="nh-single-product__cart-form"
                              action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>"
                              method="post"
                              enctype="multipart/form-data"
                              data-product-id="<?php echo esc_attr((string) $product->get_id()); ?>">
                            <input type="hidden" name="add-to-cart" value="<?php echo esc_attr((string) $product->get_id()); ?>">
                            <input type="hidden" name="product_id" value="<?php echo esc_attr((string) $product->get_id()); ?>">
                            <input type="hidden" name="quantity" value="1">

                            <div class="nh-single-product__actions">
                                <div class="nh-single-product__cta nh-single-product__cta--primary">
                                    <span class="nh-single-product__cta-control nh-cta-link">
                                        <button type="submit"
                                                name="add-to-cart"
                                                value="<?php echo esc_attr((string) $product->get_id()); ?>"
                                                class="nh-single-product__cta-btn nh-single-product__cta-btn--primary wp-block-button__link"
                                                data-nh-atc-submit>
                                            Add to cart
                                        </button>
                                    </span>
                                </div>

                                <div class="nh-single-product__cta nh-single-product__cta--secondary">
                                    <?php nice_hair_render_shop_consultation_cta($product); ?>
                                </div>
                            </div>
                        </form>
                    <?php else : ?>
                        <div class="nh-single-product__actions nh-single-product__actions--stockless">
                            <?php if ($nh_stock_label !== '') : ?>
                                <p class="nh-single-product__out-of-stock"><?php echo esc_html($nh_stock_label); ?></p>
                            <?php endif; ?>

                            <div class="nh-single-product__cta nh-single-product__cta--secondary">
                                <?php nice_hair_render_shop_consultation_cta($product); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($nh_has_how_to_use) : ?>
        <div class="nh-content-drawer nh-content-drawer--how-to-use"
             id="<?php echo esc_attr($nh_how_to_use_drawer_id); ?>"
             data-nh-content-drawer
             data-nh-content-drawer-id="<?php echo esc_attr($nh_how_to_use_drawer_id); ?>"
             hidden>
            <div class="nh-content-drawer__backdrop" data-nh-content-drawer-close></div>

            <div class="nh-content-drawer__panel"
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="<?php echo esc_attr($nh_how_to_use_drawer_id . '-title'); ?>">
                <button type="button"
                        class="nh-content-drawer__close"
                        data-nh-content-drawer-close
                        data-nh-content-drawer-close-button
                        aria-label="<?php esc_attr_e('Close drawer', 'nice-hair'); ?>">
                    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" aria-hidden="true">
                        <circle cx="16" cy="16" r="15.5" stroke="currentColor"/>
                        <path d="M11 11L21 21M21 11L11 21" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                </button>

                <div class="nh-content-drawer__header">
                    <span class="nh-content-drawer__eyebrow"><?php esc_html_e('[ HOW TO USE ]', 'nice-hair'); ?></span>
                    <div class="nh-content-drawer__rule" aria-hidden="true"></div>
                    <h2 class="nh-content-drawer__title" id="<?php echo esc_attr($nh_how_to_use_drawer_id . '-title'); ?>">
                        <?php esc_html_e('How to use?', 'nice-hair'); ?>
                    </h2>
                </div>

                <div class="nh-content-drawer__body">
                    <div class="nh-single-product__how-to-use-stack">
                        <?php if ($nh_has_how_to_use_image) : ?>
                            <div class="nh-single-product__how-to-use-media">
                                <img
                                    src="<?php echo esc_url((string) $nh_how_to_use_image['url']); ?>"
                                    alt="<?php echo esc_attr((string) ($nh_how_to_use_image['alt'] ?? get_the_title())); ?>"
                                    loading="lazy"
                                >
                            </div>
                        <?php endif; ?>

                        <?php if ($nh_has_how_to_use_text) : ?>
                            <div class="nh-single-product__how-to-use-text">
                                <?php echo wp_kses_post(wpautop($nh_how_to_use_text)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php nice_hair_render_shop_consultation_drawer($product); ?>
</section>
