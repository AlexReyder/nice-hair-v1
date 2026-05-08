<?php

declare(strict_types=1);

$eyebrow = trim((string) get_field('nh_shop_assortment_eyebrow'));
$title = trim((string) get_field('nh_shop_assortment_title'));
$description = trim((string) get_field('nh_shop_assortment_description'));
$field_anchor = trim((string) get_field('nh_shop_assortment_anchor'));
$selected_forms_raw = get_field('nh_shop_assortment_selected_forms');
$selected_forms = is_array($selected_forms_raw) ? $selected_forms_raw : [];
$cards = function_exists('nice_hair_get_shop_assortment_cards')
    ? nice_hair_get_shop_assortment_cards($selected_forms)
    : [];
$has_navigation = count($cards) > 1;
$resolved_anchor = ! empty($block['anchor'])
    ? (string) $block['anchor']
    : $field_anchor;
$anchor = $resolved_anchor !== '' ? sanitize_title($resolved_anchor) : 'shop-assortment';
$class_name = ! empty($block['className']) ? ' ' . $block['className'] : '';
$preview_mode = isset($is_preview) ? (bool) $is_preview : false;

if ($cards === [] && ! $preview_mode) {
    return;
}
?>

<section class="nh-shop-assortment<?php echo esc_attr($class_name); ?>" id="<?php echo esc_attr($anchor); ?>">
    <div class="nh-shop-assortment__shell">
        <header class="nh-shop-assortment__header">
            <?php if ($eyebrow !== '') : ?>
                <p class="nh-shop-assortment__eyebrow"><?php echo esc_html($eyebrow); ?></p>
            <?php endif; ?>

            <div class="nh-shop-assortment__divider" aria-hidden="true"></div>

            <div class="nh-shop-assortment__top">
                <div class="nh-shop-assortment__intro">
                    <?php if ($title !== '') : ?>
                        <h2 class="nh-shop-assortment__title"><?php echo esc_html($title); ?></h2>
                    <?php endif; ?>

                    <?php if ($description !== '') : ?>
                        <p class="nh-shop-assortment__description"><?php echo wp_kses_post($description); ?></p>
                    <?php endif; ?>
                </div>

                <div class="nh-shop-assortment__tools">
                    <?php if ($has_navigation) : ?>
                        <div class="nh-shop-assortment__nav" role="group" aria-label="<?php esc_attr_e('Assortment navigation', 'nice-hair'); ?>">
                            <button
                                class="nh-shop-assortment__nav-btn nh-shop-assortment__nav-btn--prev"
                                type="button"
                                aria-label="<?php esc_attr_e('Previous assortment item', 'nice-hair'); ?>"
                                data-nh-shop-assortment-prev
                            >
                                <span class="nh-shop-assortment__nav-icon" aria-hidden="true"></span>
                            </button>
                            <button
                                class="nh-shop-assortment__nav-btn nh-shop-assortment__nav-btn--next"
                                type="button"
                                aria-label="<?php esc_attr_e('Next assortment item', 'nice-hair'); ?>"
                                data-nh-shop-assortment-next
                            >
                                <span class="nh-shop-assortment__nav-icon" aria-hidden="true"></span>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <?php if ($cards !== []) : ?>
            <div class="nh-shop-assortment__slider swiper" data-nh-shop-assortment-swiper>
                <div class="swiper-wrapper nh-shop-assortment__track">
                    <?php foreach ($cards as $card) : ?>
                        <?php
                        $image = is_array($card['image'] ?? null) ? $card['image'] : [];
                        $image_url = (string) ($image['url'] ?? '');
                        $image_alt = (string) ($image['alt'] ?? ($card['label'] ?? ''));
                        $card_url = (string) ($card['url'] ?? '');
                        $card_label = (string) ($card['label'] ?? '');

                        if ($card_url === '' || $card_label === '') {
                            continue;
                        }
                        ?>
                        <div class="swiper-slide nh-shop-assortment__slide">
                            <a class="nh-shop-assortment__card" href="<?php echo esc_url($card_url); ?>">
                                <span class="nh-shop-assortment__media">
                                    <?php if ($image_url !== '') : ?>
                                        <img
                                            class="nh-shop-assortment__image"
                                            src="<?php echo esc_url($image_url); ?>"
                                            alt="<?php echo esc_attr($image_alt); ?>"
                                            loading="lazy"
                                        />
                                    <?php endif; ?>
                                </span>
                                <span class="nh-shop-assortment__label"><?php echo esc_html($card_label); ?></span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else : ?>
            <div class="nh-shop-assortment__empty">
                <?php esc_html_e('There are no published Custom Hair products matching the current assortment filter yet.', 'nice-hair'); ?>
            </div>
        <?php endif; ?>
    </div>
</section>
