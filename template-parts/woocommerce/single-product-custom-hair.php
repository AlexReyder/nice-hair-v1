<?php
/**
 * Single Product - Custom Hair configurator.
 *
 * One product equals one Extension Type / product form.
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

global $product;

$nh_sku = $product->get_sku();
$nh_video_url = function_exists('get_field') ? get_field('nh_product_video_url') : '';
$nh_how_to_use = function_exists('nice_hair_get_product_how_to_use_fallback_data')
    ? nice_hair_get_product_how_to_use_fallback_data($product)
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
$nh_how_to_use_drawer_id = 'custom-hair-how-to-use-' . (string) $product->get_id();
$nh_custom_hair_guide_base_url = trailingslashit(get_template_directory_uri()) . 'assets/images/ecommerce';
$nh_custom_hair_guide_drawer_ids = [
    'length'  => 'custom-hair-length-guide-' . (string) $product->get_id(),
    'quality' => 'custom-hair-quality-guide-' . (string) $product->get_id(),
    'texture' => 'custom-hair-texture-guide-' . (string) $product->get_id(),
];
$nh_custom_hair_guide_drawers = [
    [
        'key'     => 'length',
        'id'      => $nh_custom_hair_guide_drawer_ids['length'],
        'eyebrow' => '[LENGTH]',
        'title'   => 'What length should I choose?',
        'layout'  => 'image',
        'image'   => [
            'src' => $nh_custom_hair_guide_base_url . '/length drawer/measure.jpg',
            'alt' => 'Hair extension length guide',
        ],
    ],
    [
        'key'     => 'quality',
        'id'      => $nh_custom_hair_guide_drawer_ids['quality'],
        'eyebrow' => '[ HAIR QUALITY ]',
        'title'   => 'What\'s the difference in hair quality?',
        'layout'  => 'items',
        'items'   => [
            [
                'title' => 'Premium',
                'text'  => 'Perfectly smooth and naturally flowing texture. Ideal for clients who prefer a sleek, timeless look or love styling versatility - straight, curled or waved. Holds shape beautifully while maintaining a refined, luxurious appearance.',
                'image' => [
                    'src' => $nh_custom_hair_guide_base_url . '/quality drawer/premium.png',
                    'alt' => 'Premium hair quality',
                ],
            ],
            [
                'title' => 'Lux',
                'text'  => 'Soft, natural movement with a gentle wave pattern. Balanced volume and texture that creates effortless dimension without looking over-styled. After washing, the hair forms a subtle, elegant wave.',
                'image' => [
                    'src' => $nh_custom_hair_guide_base_url . '/quality drawer/lux.png',
                    'alt' => 'Lux hair quality',
                ],
            ],
            [
                'title' => 'Exclusive',
                'text'  => 'Defined, elastic curls with natural bounce and character. Lightweight yet full, with a soft, touchable feel and dynamic movement. Each bundle has its own unique curl pattern, giving an authentic and luxurious finish.',
                'image' => [
                    'src' => $nh_custom_hair_guide_base_url . '/quality drawer/exclusive.png',
                    'alt' => 'Exclusive hair quality',
                ],
            ],
        ],
    ],
    [
        'key'     => 'texture',
        'id'      => $nh_custom_hair_guide_drawer_ids['texture'],
        'eyebrow' => '[ TEXTURE ]',
        'title'   => 'What\'s the difference in hair texture?',
        'layout'  => 'items',
        'items'   => [
            [
                'title' => 'Soft straight',
                'text'  => 'Single-donor bundles (no mix). Dyed using our exclusive technology while preserving the highest hair quality. Fine, smooth, and soft texture. The choice for the discerning and sophisticated client.',
                'image' => [
                    'src' => $nh_custom_hair_guide_base_url . '/texture drawer/soft.png',
                    'alt' => 'Soft straight texture',
                ],
            ],
            [
                'title' => 'Silky wavy',
                'text'  => 'Bulks are sourced from several donors (mix). Thick ends, dense tops. After washing - slightly wavy. Ideal for those who like maximum volume and thickness.',
                'image' => [
                    'src' => $nh_custom_hair_guide_base_url . '/texture drawer/silky.png',
                    'alt' => 'Silky wavy texture',
                ],
            ],
            [
                'title' => 'Amazing curly',
                'text'  => 'Undyed, baby\'s super-selected hair. Each bundle is unique. Exceptionally soft, fine, and shiny. Texture varies from bone straight to bouncy curls. Ideal for anyone seeking a rare, natural texture.',
                'image' => [
                    'src' => $nh_custom_hair_guide_base_url . '/texture drawer/curly.png',
                    'alt' => 'Amazing curly texture',
                ],
            ],
        ],
    ],
];

$nh_configurator = function_exists('nice_hair_get_custom_hair_configurator')
    ? nice_hair_get_custom_hair_configurator($product)
    : [
        'product_form' => [],
        'color_options' => [],
        'length_options' => [],
        'quality_options' => [],
        'texture_options' => [],
        'weight_config' => ['min' => 30, 'step' => 10, 'default' => 30],
        'base_price_map' => [],
        'default_selection' => [],
        'selected_color' => null,
        'selected_price_html' => '',
        'selected_total_price' => null,
        'price_note' => '',
        'is_complete' => false,
        'selection' => [],
    ];

$nh_product_form = is_array($nh_configurator['product_form'] ?? null) ? $nh_configurator['product_form'] : [];
$nh_color_options = isset($nh_configurator['color_options']) && is_array($nh_configurator['color_options'])
    ? $nh_configurator['color_options']
    : [];

if (function_exists('nice_hair_get_product_custom_hair_color_options')) {
    $nh_color_options = nice_hair_get_product_custom_hair_color_options($product, $nh_color_options);
}
$nh_length_options = isset($nh_configurator['length_options']) && is_array($nh_configurator['length_options'])
    ? $nh_configurator['length_options']
    : [];
$nh_quality_options = isset($nh_configurator['quality_options']) && is_array($nh_configurator['quality_options'])
    ? $nh_configurator['quality_options']
    : [];
$nh_texture_options = isset($nh_configurator['texture_options']) && is_array($nh_configurator['texture_options'])
    ? $nh_configurator['texture_options']
    : [];
$nh_weight_config = isset($nh_configurator['weight_config']) && is_array($nh_configurator['weight_config'])
    ? $nh_configurator['weight_config']
    : ['min' => 30, 'step' => 10, 'default' => 30];
$nh_selection = is_array($nh_configurator['selection'] ?? null) ? $nh_configurator['selection'] : [];
$nh_selected_values = is_array($nh_selection['selection'] ?? null) ? $nh_selection['selection'] : [];
$nh_selected_color = is_array($nh_configurator['selected_color'] ?? null) ? $nh_configurator['selected_color'] : null;
$nh_selected_color_image = is_array($nh_selected_color['main_image'] ?? null) ? $nh_selected_color['main_image'] : null;
$nh_selected_color_key = (string) ($nh_selected_values['color'] ?? '');
$nh_available_color_keys = array_values(array_filter(array_map(
    static fn (mixed $color_option): string => is_array($color_option) ? (string) ($color_option['key'] ?? '') : '',
    $nh_color_options
)));

if (($nh_selected_color_key === '' || ! in_array($nh_selected_color_key, $nh_available_color_keys, true)) && $nh_available_color_keys !== []) {
    $nh_selected_color_key = (string) $nh_available_color_keys[0];
}
$nh_selected_length = (string) ($nh_selected_values['length'] ?? '');
$nh_selected_quality = (string) ($nh_selected_values['quality'] ?? '');
$nh_selected_texture = (string) ($nh_selected_values['texture'] ?? '');
$nh_selected_weight = isset($nh_selected_values['weight']) && is_numeric($nh_selected_values['weight'])
    ? (int) $nh_selected_values['weight']
    : (int) ($nh_weight_config['default'] ?? 30);
$nh_selected_price_html = (string) ($nh_configurator['selected_price_html'] ?? '');
$nh_price_note = trim((string) ($nh_configurator['price_note'] ?? ''));
$nh_is_complete = (bool) ($nh_configurator['is_complete'] ?? false);
$nh_product_form_key = (string) ($nh_product_form['key'] ?? '');
$nh_product_form_label = (string) ($nh_product_form['label'] ?? '');
$nh_product_form_surcharge = isset($nh_product_form['surcharge_per_gram']) && is_numeric($nh_product_form['surcharge_per_gram'])
    ? (float) $nh_product_form['surcharge_per_gram']
    : null;

$nh_shop_url = get_permalink(wc_get_page_id('shop'));
$nh_contact_phone = nice_hair_get_contact_phone_display('shop');
$nh_contact_phone_link = nice_hair_get_contact_phone_link('shop');
$nh_contact_address = nice_hair_get_contact_address_plain('shop');
$nh_is_in_stock = $product->is_in_stock();
$nh_can_purchase = $product->is_purchasable() && $nh_is_in_stock && $nh_is_complete;
$nh_purchase_notice = '';

if (! $nh_is_in_stock) {
    $nh_purchase_notice = __('Out of stock', 'nice-hair');
} elseif (! $nh_is_complete) {
    $nh_purchase_notice = __('Custom Hair pricing is incomplete for this item. Please contact us for assistance.', 'nice-hair');
}

$nh_product_terms = wp_get_post_terms($product->get_id(), 'product_cat');
$nh_product_terms = is_array($nh_product_terms) ? $nh_product_terms : [];
$nh_custom_root_term = null;
$nh_custom_child_term = null;

foreach ($nh_product_terms as $nh_term) {
    if (! $nh_term instanceof WP_Term || ! function_exists('nice_hair_get_product_category_family')) {
        continue;
    }

    if (nice_hair_get_product_category_family($nh_term) !== 'custom_hair') {
        continue;
    }

    if ((int) $nh_term->parent > 0) {
        $nh_custom_child_term = $nh_term;

        if (! $nh_custom_root_term instanceof WP_Term) {
            $nh_root_candidate = get_term((int) $nh_term->parent, 'product_cat');

            if ($nh_root_candidate instanceof WP_Term) {
                $nh_custom_root_term = $nh_root_candidate;
            }
        }
    } elseif (! $nh_custom_root_term instanceof WP_Term) {
        $nh_custom_root_term = $nh_term;
    }
}

$nh_custom_root_url = $nh_custom_root_term instanceof WP_Term ? get_term_link($nh_custom_root_term) : '';
$nh_custom_child_url = $nh_custom_child_term instanceof WP_Term ? get_term_link($nh_custom_child_term) : '';

if (is_wp_error($nh_custom_root_url)) {
    $nh_custom_root_url = '';
}

if (is_wp_error($nh_custom_child_url)) {
    $nh_custom_child_url = '';
}

$nh_gallery_ids = $product->get_gallery_image_ids();
$nh_featured_id = (int) get_post_thumbnail_id();
$nh_shared_gallery_ids = array_values(array_unique(array_filter(array_map(
    'intval',
    array_merge($nh_featured_id > 0 ? [$nh_featured_id] : [], $nh_gallery_ids)
))));
$nh_color_image_ids = [];

foreach ($nh_color_options as $nh_color_option) {
    if (! is_array($nh_color_option)) {
        continue;
    }

    $nh_color_image = is_array($nh_color_option['main_image'] ?? null) ? $nh_color_option['main_image'] : null;
    $nh_color_image_id = isset($nh_color_image['ID']) && is_numeric($nh_color_image['ID'])
        ? (int) $nh_color_image['ID']
        : 0;

    if ($nh_color_image_id > 0) {
        $nh_color_image_ids[] = $nh_color_image_id;
    }
}

$nh_color_image_ids = array_values(array_unique($nh_color_image_ids));
$nh_lightbox_image_ids = array_values(array_unique(array_merge($nh_shared_gallery_ids, $nh_color_image_ids)));
$nh_lightbox_index_map = array_flip($nh_lightbox_image_ids);
$nh_has_lightbox_gallery = $nh_lightbox_image_ids !== [] && function_exists('wc_get_gallery_image_html');

foreach ($nh_color_options as $nh_index => $nh_color_option) {
    if (! is_array($nh_color_option)) {
        continue;
    }

    $nh_color_image = is_array($nh_color_option['main_image'] ?? null) ? $nh_color_option['main_image'] : null;
    $nh_color_image_id = isset($nh_color_image['ID']) && is_numeric($nh_color_image['ID'])
        ? (int) $nh_color_image['ID']
        : 0;
    $nh_color_options[$nh_index]['lightbox_index'] = $nh_color_image_id > 0 && isset($nh_lightbox_index_map[$nh_color_image_id])
        ? (int) $nh_lightbox_index_map[$nh_color_image_id]
        : null;
}

$nh_main_image_id = $nh_featured_id ?: ($nh_shared_gallery_ids[0] ?? ($nh_color_image_ids[0] ?? 0));

$nh_active_gallery_index = $nh_main_image_id > 0 && isset($nh_lightbox_index_map[$nh_main_image_id])
    ? (int) $nh_lightbox_index_map[$nh_main_image_id]
    : 0;
$nh_main_image_url = $nh_main_image_id > 0
    ? wp_get_attachment_image_url($nh_main_image_id, 'woocommerce_single')
    : wc_placeholder_img_src('woocommerce_single');
$nh_main_image_full_url = $nh_main_image_id > 0
    ? wp_get_attachment_image_url($nh_main_image_id, 'full')
    : $nh_main_image_url;
$nh_base_price_map = isset($nh_configurator['base_price_map']) && is_array($nh_configurator['base_price_map'])
    ? $nh_configurator['base_price_map']
    : [];
$nh_js_color_options = [];

foreach ($nh_color_options as $nh_color_option) {
    if (! is_array($nh_color_option)) {
        continue;
    }

    $nh_color_image = is_array($nh_color_option['main_image'] ?? null) ? $nh_color_option['main_image'] : null;
    $nh_color_image_id = isset($nh_color_image['ID']) && is_numeric($nh_color_image['ID'])
        ? (int) $nh_color_image['ID']
        : 0;
    $nh_color_preview_image = is_array($nh_color_option['preview_image'] ?? null) ? $nh_color_option['preview_image'] : null;
    $nh_color_preview_image_id = isset($nh_color_preview_image['ID']) && is_numeric($nh_color_preview_image['ID'])
        ? (int) $nh_color_preview_image['ID']
        : 0;
    $nh_js_color_options[] = [
        'key' => (string) ($nh_color_option['key'] ?? ''),
        'label' => (string) ($nh_color_option['label'] ?? ''),
        'value' => (string) ($nh_color_option['value'] ?? ''),
        'group' => (string) ($nh_color_option['group_key'] ?? ''),
        'previewImage' => [
            'url' => $nh_color_preview_image_id > 0 ? (string) wp_get_attachment_image_url($nh_color_preview_image_id, 'thumbnail') : '',
            'alt' => (string) ($nh_color_preview_image['alt'] ?? ($nh_color_option['label'] ?? get_the_title())),
        ],
        'mainImage' => [
            'url' => $nh_color_image_id > 0 ? (string) wp_get_attachment_image_url($nh_color_image_id, 'woocommerce_single') : '',
            'fullUrl' => $nh_color_image_id > 0 ? (string) wp_get_attachment_image_url($nh_color_image_id, 'full') : '',
            'alt' => (string) ($nh_color_image['alt'] ?? ($nh_color_option['label'] ?? get_the_title())),
            'lightboxIndex' => isset($nh_color_option['lightbox_index']) && is_numeric($nh_color_option['lightbox_index'])
                ? (int) $nh_color_option['lightbox_index']
                : null,
        ],
    ];
}

$nh_custom_config_payload = [
    'basePriceMap' => $nh_base_price_map,
    'productForm' => [
        'key' => $nh_product_form_key,
        'label' => $nh_product_form_label,
        'surchargePerGram' => $nh_product_form_surcharge,
    ],
    'weight' => [
        'min' => (int) ($nh_weight_config['min'] ?? 30),
        'step' => (int) ($nh_weight_config['step'] ?? 10),
        'default' => (int) ($nh_weight_config['default'] ?? 30),
    ],
    'selections' => [
        'color' => $nh_selected_color_key,
        'length' => $nh_selected_length,
        'quality' => $nh_selected_quality,
        'texture' => $nh_selected_texture,
        'weight' => $nh_selected_weight,
    ],
    'colors' => $nh_js_color_options,
];
?>

<section id="product-<?php the_ID(); ?>" <?php wc_product_class('nh-single-product nh-single-product--custom-hair'); ?> data-nh-custom-configurator>
    <div class="nh-single-product__container">
        <div class="nh-single-product__top">
            <nav class="nh-single-product__breadcrumb" aria-label="Breadcrumb">
                <a href="<?php echo esc_url(home_url('/')); ?>">[Home]</a> -
                <a href="<?php echo esc_url($nh_shop_url); ?>">[Shop]</a> -
                <?php if ($nh_custom_root_term instanceof WP_Term && $nh_custom_root_url !== '') : ?>
                    <a href="<?php echo esc_url($nh_custom_root_url); ?>">[<?php echo esc_html($nh_custom_root_term->name); ?>]</a> -
                <?php endif; ?>
                <?php if ($nh_custom_child_term instanceof WP_Term && $nh_custom_child_url !== '') : ?>
                    <a href="<?php echo esc_url($nh_custom_child_url); ?>">[<?php echo esc_html($nh_custom_child_term->name); ?>]</a> -
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
                            data-active-index="<?php echo esc_attr((string) $nh_active_gallery_index); ?>"
                            aria-controls="photoswipe-fullscreen-dialog"
                            aria-label="<?php esc_attr_e('Open product gallery', 'nice-hair'); ?>">
                        <img
                            class="nh-single-product__main-img"
                            data-nh-custom-main-image
                            src="<?php echo esc_url((string) $nh_main_image_url); ?>"
                            data-full-src="<?php echo esc_attr((string) $nh_main_image_full_url); ?>"
                            alt="<?php echo esc_attr(get_the_title()); ?>"
                        >
                    </button>
                <?php else : ?>
                    <div class="nh-single-product__gallery-main">
                        <img
                            class="nh-single-product__main-img"
                            data-nh-custom-main-image
                            src="<?php echo esc_url((string) $nh_main_image_url); ?>"
                            data-full-src="<?php echo esc_attr((string) $nh_main_image_full_url); ?>"
                            alt="<?php echo esc_attr(get_the_title()); ?>"
                        >
                    </div>
                <?php endif; ?>

                <?php if ($nh_shared_gallery_ids !== []) : ?>
                    <div class="nh-single-product__gallery-thumbs">
                        <?php foreach ($nh_shared_gallery_ids as $nh_thumb_image_id) : ?>
                            <?php
                            $nh_thumb_gallery_index = $nh_lightbox_index_map[$nh_thumb_image_id] ?? 0;
                            echo wp_get_attachment_image((int) $nh_thumb_image_id, 'thumbnail', false, [
                                'class' => 'nh-single-product__thumb' . ($nh_main_image_id === (int) $nh_thumb_image_id ? ' is-active' : ''),
                                'data-full-src' => wp_get_attachment_image_url((int) $nh_thumb_image_id, 'woocommerce_single'),
                                'data-gallery-index' => (string) $nh_thumb_gallery_index,
                            ]);
                            ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($nh_has_lightbox_gallery) : ?>
                    <div class="nh-single-product__lightbox-source" data-nh-sp-lightbox-source aria-hidden="true">
                        <?php foreach ($nh_lightbox_image_ids as $nh_index => $nh_image_id) : ?>
                            <?php echo wc_get_gallery_image_html((int) $nh_image_id, $nh_index === $nh_active_gallery_index, $nh_index); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
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

                <form class="nh-single-product__cart-form"
                      action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>"
                      method="post"
                      enctype="multipart/form-data"
                      data-product-id="<?php echo esc_attr((string) $product->get_id()); ?>">
                    <input type="hidden" name="add-to-cart" value="<?php echo esc_attr((string) $product->get_id()); ?>">
                    <input type="hidden" name="product_id" value="<?php echo esc_attr((string) $product->get_id()); ?>">
                    <input type="hidden" name="quantity" value="1">
                    <input type="hidden" name="nh_custom_hair_color" value="<?php echo esc_attr($nh_selected_color_key); ?>" data-nh-custom-color-input>
                    <input type="hidden" name="nh_custom_hair_length" value="<?php echo esc_attr($nh_selected_length); ?>" data-nh-custom-length-input>
                    <input type="hidden" name="nh_custom_hair_quality" value="<?php echo esc_attr($nh_selected_quality); ?>" data-nh-custom-quality-input>
                    <input type="hidden" name="nh_custom_hair_texture" value="<?php echo esc_attr($nh_selected_texture); ?>" data-nh-custom-texture-input>
                    <input type="hidden" name="nh_custom_hair_weight" value="<?php echo esc_attr((string) $nh_selected_weight); ?>" data-nh-custom-weight-hidden>

                    <div class="nh-single-product__option-group nh-single-product__option-group--custom-hair">
                        <?php if ($nh_color_options !== []) : ?>
                            <div class="nh-single-product__config-row">
                                <div class="nh-single-product__config-heading">
                                    <span class="nh-single-product__option-label">[Color:]</span>
                                </div>

                                <div class="nh-single-product__color-shell">
                                    <div class="nh-single-product__color-list" data-nh-custom-color-list>
                                        <?php foreach ($nh_color_options as $nh_color_option) : ?>
                                            <?php
                                            if (! is_array($nh_color_option)) {
                                                continue;
                                            }

                                            $nh_color_key = (string) ($nh_color_option['key'] ?? '');
                                            $nh_color_label = (string) ($nh_color_option['label'] ?? '');
                                            $nh_color_value = (string) ($nh_color_option['value'] ?? $nh_color_label);
                                            $nh_color_image = is_array($nh_color_option['main_image'] ?? null) ? $nh_color_option['main_image'] : null;
                                            $nh_color_preview_image = is_array($nh_color_option['preview_image'] ?? null) ? $nh_color_option['preview_image'] : null;
                                            $nh_color_image_id = isset($nh_color_image['ID']) && is_numeric($nh_color_image['ID'])
                                                ? (int) $nh_color_image['ID']
                                                : 0;
                                            $nh_color_preview_image_id = isset($nh_color_preview_image['ID']) && is_numeric($nh_color_preview_image['ID'])
                                                ? (int) $nh_color_preview_image['ID']
                                                : 0;
                                            $nh_color_sample_src = $nh_color_preview_image_id > 0
                                                ? (string) wp_get_attachment_image_url($nh_color_preview_image_id, 'thumbnail')
                                                : ($nh_color_image_id > 0 ? (string) wp_get_attachment_image_url($nh_color_image_id, 'thumbnail') : '');
                                            $nh_color_main_src = $nh_color_image_id > 0
                                                ? (string) wp_get_attachment_image_url($nh_color_image_id, 'woocommerce_single')
                                                : '';
                                            $nh_color_full_src = $nh_color_image_id > 0
                                                ? (string) wp_get_attachment_image_url($nh_color_image_id, 'full')
                                                : '';
                                            $nh_color_lightbox_index = isset($nh_color_option['lightbox_index']) && is_numeric($nh_color_option['lightbox_index'])
                                                ? (int) $nh_color_option['lightbox_index']
                                                : '';
                                            ?>
                                            <button
                                                type="button"
                                                class="nh-single-product__color-option<?php echo $nh_selected_color_key === $nh_color_key ? ' is-active' : ''; ?>"
                                                data-nh-custom-color-option
                                                data-color-key="<?php echo esc_attr($nh_color_key); ?>"
                                                data-color-label="<?php echo esc_attr($nh_color_label); ?>"
                                                data-color-value="<?php echo esc_attr($nh_color_value); ?>"
                                                data-main-src="<?php echo esc_attr($nh_color_main_src); ?>"
                                                data-full-src="<?php echo esc_attr($nh_color_full_src); ?>"
                                                data-lightbox-index="<?php echo esc_attr((string) $nh_color_lightbox_index); ?>"
                                                aria-pressed="<?php echo $nh_selected_color_key === $nh_color_key ? 'true' : 'false'; ?>">
                                                <span class="nh-single-product__color-thumb">
                                                    <?php if ($nh_color_sample_src !== '') : ?>
                                                        <img src="<?php echo esc_url($nh_color_sample_src); ?>" alt="<?php echo esc_attr($nh_color_label); ?>" loading="lazy">
                                                    <?php else : ?>
                                                        <span class="nh-single-product__color-thumb-fallback">
                                                            <?php echo esc_html(function_exists('mb_substr') ? mb_substr($nh_color_value, 0, 3) : substr($nh_color_value, 0, 3)); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </span>
                                                <span class="nh-single-product__color-name"><?php echo esc_html($nh_color_value); ?></span>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="nh-single-product__product-form-rail" aria-hidden="true">
                                        <span class="nh-single-product__product-form-rail-line"></span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($nh_length_options !== []) : ?>
                            <div class="nh-single-product__config-row">
                                <div class="nh-single-product__config-heading">
                                    <span class="nh-single-product__option-label">[Length:]</span>
                                    <button type="button"
                                            class="nh-single-product__config-help nh-single-product__config-help--button"
                                            data-nh-content-drawer-target="<?php echo esc_attr($nh_custom_hair_guide_drawer_ids['length']); ?>"
                                            aria-haspopup="dialog"
                                            aria-controls="<?php echo esc_attr($nh_custom_hair_guide_drawer_ids['length']); ?>">
                                        What length should I choose?
                                    </button>
                                </div>

                                <div class="nh-single-product__option-list">
                                    <?php foreach ($nh_length_options as $nh_length_option) : ?>
                                        <button
                                            type="button"
                                            class="nh-single-product__option-chip<?php echo $nh_selected_length === (string) ($nh_length_option['key'] ?? '') ? ' is-active' : ''; ?>"
                                            data-nh-custom-choice
                                            data-nh-custom-choice-group="length"
                                            data-value="<?php echo esc_attr((string) ($nh_length_option['key'] ?? '')); ?>">
                                            <?php echo esc_html((string) ($nh_length_option['label'] ?? '')); ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($nh_quality_options !== []) : ?>
                            <div class="nh-single-product__config-row">
                                <div class="nh-single-product__config-heading">
                                    <span class="nh-single-product__option-label">[Hair Quality:]</span>
                                    <button type="button"
                                            class="nh-single-product__config-help nh-single-product__config-help--button"
                                            data-nh-content-drawer-target="<?php echo esc_attr($nh_custom_hair_guide_drawer_ids['quality']); ?>"
                                            aria-haspopup="dialog"
                                            aria-controls="<?php echo esc_attr($nh_custom_hair_guide_drawer_ids['quality']); ?>">
                                        What&apos;s the difference in hair quality?
                                    </button>
                                </div>

                                <div class="nh-single-product__option-list">
                                    <?php foreach ($nh_quality_options as $nh_quality_option) : ?>
                                        <button
                                            type="button"
                                            class="nh-single-product__option-chip<?php echo $nh_selected_quality === (string) ($nh_quality_option['key'] ?? '') ? ' is-active' : ''; ?>"
                                            data-nh-custom-choice
                                            data-nh-custom-choice-group="quality"
                                            data-value="<?php echo esc_attr((string) ($nh_quality_option['key'] ?? '')); ?>">
                                            <?php echo esc_html((string) ($nh_quality_option['label'] ?? '')); ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($nh_texture_options !== []) : ?>
                            <div class="nh-single-product__config-row">
                                <div class="nh-single-product__config-heading">
                                    <span class="nh-single-product__option-label">[Texture:]</span>
                                    <button type="button"
                                            class="nh-single-product__config-help nh-single-product__config-help--button"
                                            data-nh-content-drawer-target="<?php echo esc_attr($nh_custom_hair_guide_drawer_ids['texture']); ?>"
                                            aria-haspopup="dialog"
                                            aria-controls="<?php echo esc_attr($nh_custom_hair_guide_drawer_ids['texture']); ?>">
                                        What texture should I choose?
                                    </button>
                                </div>

                                <div class="nh-single-product__option-list">
                                    <?php foreach ($nh_texture_options as $nh_texture_option) : ?>
                                        <button
                                            type="button"
                                            class="nh-single-product__option-chip<?php echo $nh_selected_texture === (string) ($nh_texture_option['key'] ?? '') ? ' is-active' : ''; ?>"
                                            data-nh-custom-choice
                                            data-nh-custom-choice-group="texture"
                                            data-value="<?php echo esc_attr((string) ($nh_texture_option['key'] ?? '')); ?>">
                                            <?php echo esc_html((string) ($nh_texture_option['label'] ?? '')); ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="nh-single-product__config-row">
                            <div class="nh-single-product__config-heading">
                                <span class="nh-single-product__option-label">[Weight:]</span>
                                <span class="nh-single-product__config-help">Minimum <?php echo esc_html((string) ($nh_weight_config['min'] ?? 30)); ?> gr, step <?php echo esc_html((string) ($nh_weight_config['step'] ?? 10)); ?> gr</span>
                            </div>

                            <div class="nh-single-product__weight-shell" data-nh-custom-weight-shell>
                                <button type="button" class="nh-single-product__weight-btn" data-nh-custom-weight="minus" aria-label="<?php esc_attr_e('Decrease weight', 'nice-hair'); ?>">-</button>
                                <input
                                    type="number"
                                    class="nh-single-product__weight-input"
                                    value="<?php echo esc_attr((string) $nh_selected_weight); ?>"
                                    min="<?php echo esc_attr((string) ($nh_weight_config['min'] ?? 30)); ?>"
                                    step="<?php echo esc_attr((string) ($nh_weight_config['step'] ?? 10)); ?>"
                                    inputmode="numeric"
                                    data-nh-custom-weight-input
                                >
                                <span class="nh-single-product__weight-unit">gr</span>
                                <button type="button" class="nh-single-product__weight-btn" data-nh-custom-weight="plus" aria-label="<?php esc_attr_e('Increase weight', 'nice-hair'); ?>">+</button>
                            </div>
                        </div>
                    </div>

                    <div class="nh-single-product__purchase">
                        <?php if ($nh_selected_price_html !== '') : ?>
                            <div class="nh-single-product__price" data-nh-sp-price>
                                <?php echo $nh_selected_price_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </div>
                        <?php else : ?>
                            <p class="nh-single-product__price-request" data-nh-sp-price><?php esc_html_e('Price on request', 'nice-hair'); ?></p>
                        <?php endif; ?>

                        <p class="nh-single-product__price-note" data-nh-custom-price-note<?php echo $nh_price_note === '' ? ' hidden' : ''; ?>>
                            <?php echo esc_html($nh_price_note); ?>
                        </p>

                        <div class="nh-single-product__actions<?php echo $nh_purchase_notice !== '' ? ' nh-single-product__actions--stockless' : ''; ?>">
                            <?php if ($nh_purchase_notice !== '') : ?>
                                <p class="nh-single-product__out-of-stock"><?php echo esc_html($nh_purchase_notice); ?></p>
                            <?php endif; ?>

                            <div class="nh-single-product__cta nh-single-product__cta--primary">
                                <span class="nh-single-product__cta-control nh-cta-link">
                                    <button type="submit"
                                            name="add-to-cart"
                                            value="<?php echo esc_attr((string) $product->get_id()); ?>"
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
                    </div>
                </form>

                <script type="application/json" data-nh-custom-config><?php echo wp_json_encode($nh_custom_config_payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
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

    <?php foreach ($nh_custom_hair_guide_drawers as $nh_guide_drawer) : ?>
        <?php
        $nh_guide_drawer_id = (string) ($nh_guide_drawer['id'] ?? '');
        $nh_guide_drawer_key = (string) ($nh_guide_drawer['key'] ?? '');
        $nh_guide_layout = (string) ($nh_guide_drawer['layout'] ?? '');

        if ($nh_guide_drawer_id === '') {
            continue;
        }
        ?>
        <div class="nh-content-drawer nh-content-drawer--custom-hair-guide nh-content-drawer--custom-hair-guide-<?php echo esc_attr($nh_guide_drawer_key); ?>"
             id="<?php echo esc_attr($nh_guide_drawer_id); ?>"
             data-nh-content-drawer
             data-nh-content-drawer-id="<?php echo esc_attr($nh_guide_drawer_id); ?>"
             hidden>
            <div class="nh-content-drawer__backdrop" data-nh-content-drawer-close></div>

            <div class="nh-content-drawer__panel"
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="<?php echo esc_attr($nh_guide_drawer_id . '-title'); ?>">
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
                    <span class="nh-content-drawer__eyebrow"><?php echo esc_html((string) ($nh_guide_drawer['eyebrow'] ?? '')); ?></span>
                    <div class="nh-content-drawer__rule" aria-hidden="true"></div>
                    <h2 class="nh-content-drawer__title" id="<?php echo esc_attr($nh_guide_drawer_id . '-title'); ?>">
                        <?php echo esc_html((string) ($nh_guide_drawer['title'] ?? '')); ?>
                    </h2>
                </div>

                <div class="nh-content-drawer__body">
                    <?php if ($nh_guide_layout === 'image') : ?>
                        <?php $nh_guide_image = is_array($nh_guide_drawer['image'] ?? null) ? $nh_guide_drawer['image'] : []; ?>
                        <div class="nh-single-product__guide nh-single-product__guide--image">
                            <figure class="nh-single-product__guide-single-media">
                                <img
                                    src="<?php echo esc_url((string) ($nh_guide_image['src'] ?? '')); ?>"
                                    alt="<?php echo esc_attr((string) ($nh_guide_image['alt'] ?? '')); ?>"
                                    loading="lazy"
                                    decoding="async"
                                >
                            </figure>
                        </div>
                    <?php else : ?>
                        <?php $nh_guide_items = is_array($nh_guide_drawer['items'] ?? null) ? $nh_guide_drawer['items'] : []; ?>
                        <div class="nh-single-product__guide nh-single-product__guide--items">
                            <div class="nh-single-product__guide-list">
                                <?php foreach ($nh_guide_items as $nh_guide_item) : ?>
                                    <?php
                                    if (! is_array($nh_guide_item)) {
                                        continue;
                                    }

                                    $nh_guide_item_image = is_array($nh_guide_item['image'] ?? null) ? $nh_guide_item['image'] : [];
                                    ?>
                                    <article class="nh-single-product__guide-item">
                                        <figure class="nh-single-product__guide-item-media">
                                            <img
                                                src="<?php echo esc_url((string) ($nh_guide_item_image['src'] ?? '')); ?>"
                                                alt="<?php echo esc_attr((string) ($nh_guide_item_image['alt'] ?? '')); ?>"
                                                loading="lazy"
                                                decoding="async"
                                            >
                                        </figure>

                                        <div class="nh-single-product__guide-item-content">
                                            <h3 class="nh-single-product__guide-item-title">
                                                <?php echo esc_html((string) ($nh_guide_item['title'] ?? '')); ?>
                                            </h3>
                                            <p class="nh-single-product__guide-item-text">
                                                <?php echo esc_html((string) ($nh_guide_item['text'] ?? '')); ?>
                                            </p>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php nice_hair_render_shop_consultation_drawer($product); ?>
</section>
