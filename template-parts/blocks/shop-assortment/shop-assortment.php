<?php

declare(strict_types=1);

$eyebrow = trim((string) get_field('nh_shop_assortment_eyebrow'));
$title = trim((string) get_field('nh_shop_assortment_title'));
$description = trim((string) get_field('nh_shop_assortment_description'));
$field_anchor = trim((string) get_field('nh_shop_assortment_anchor'));
$gallery_rows = get_field('nh_shop_assortment_gallery');
$gallery_rows = is_array($gallery_rows) ? $gallery_rows : [];
$preview_mode = isset($is_preview) ? (bool) $is_preview : false;

$gallery_items = [];

foreach ($gallery_rows as $gallery_row) {
    if (! is_array($gallery_row)) {
        continue;
    }

    $image = is_array($gallery_row['item_image'] ?? null) ? $gallery_row['item_image'] : [];

    if ($image === []) {
        continue;
    }

    $image_id = isset($image['ID'])
        ? (int) $image['ID']
        : (isset($image['id']) ? (int) $image['id'] : 0);

    $image_url = (string) ($image['sizes']['large'] ?? $image['url'] ?? '');
    $full_image_url = (string) ($image['url'] ?? $image_url);

    if ($image_id > 0) {
        $resolved_large_url = wp_get_attachment_image_url($image_id, 'large');
        $resolved_full_url = wp_get_attachment_image_url($image_id, 'full');

        if (is_string($resolved_large_url) && $resolved_large_url !== '') {
            $image_url = $resolved_large_url;
        }

        if (is_string($resolved_full_url) && $resolved_full_url !== '') {
            $full_image_url = $resolved_full_url;
        }
    }

    if ($image_url === '') {
        continue;
    }

    if ($full_image_url === '') {
        $full_image_url = $image_url;
    }

    $image_alt = trim((string) ($image['alt'] ?? ''));

    if ($image_alt === '') {
        $image_alt = trim((string) ($image['title'] ?? ''));
    }

    if ($image_alt === '') {
        $image_alt = __('Assortment gallery image', 'nice-hair');
    }

    $gallery_items[] = [
        'id'       => $image_id,
        'url'      => $image_url,
        'full_url' => $full_image_url,
        'alt'      => $image_alt,
    ];
}

$has_navigation = count($gallery_items) > 1;
$resolved_anchor = ! empty($block['anchor'])
    ? (string) $block['anchor']
    : $field_anchor;
$anchor = $resolved_anchor !== '' ? sanitize_title($resolved_anchor) : 'shop-assortment';
$class_name = ! empty($block['className']) ? ' ' . $block['className'] : '';

if ($gallery_items === [] && ! $preview_mode) {
    return;
}
?>

<section
    class="nh-shop-assortment<?php echo esc_attr($class_name); ?>"
    id="<?php echo esc_attr($anchor); ?>"
    data-nh-shop-assortment-gallery
>
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
                        <div class="nh-shop-assortment__nav" role="group" aria-label="<?php esc_attr_e('Assortment gallery navigation', 'nice-hair'); ?>">
                            <button
                                class="nh-shop-assortment__nav-btn nh-shop-assortment__nav-btn--prev"
                                type="button"
                                aria-label="<?php esc_attr_e('Previous assortment image', 'nice-hair'); ?>"
                                data-nh-shop-assortment-prev
                            >
                                <span class="nh-shop-assortment__nav-icon" aria-hidden="true"></span>
                            </button>
                            <button
                                class="nh-shop-assortment__nav-btn nh-shop-assortment__nav-btn--next"
                                type="button"
                                aria-label="<?php esc_attr_e('Next assortment image', 'nice-hair'); ?>"
                                data-nh-shop-assortment-next
                            >
                                <span class="nh-shop-assortment__nav-icon" aria-hidden="true"></span>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <?php if ($gallery_items !== []) : ?>
            <div class="nh-shop-assortment__slider swiper" data-nh-shop-assortment-swiper>
                <div class="swiper-wrapper nh-shop-assortment__track">
                    <?php foreach ($gallery_items as $gallery_item) : ?>
                        <div class="swiper-slide nh-shop-assortment__slide">
                            <button
                                class="nh-shop-assortment__card"
                                type="button"
                                aria-label="<?php esc_attr_e('Open assortment image fullscreen', 'nice-hair'); ?>"
                                data-nh-shop-assortment-gallery-item
                                data-nh-shop-assortment-gallery-src="<?php echo esc_url($gallery_item['full_url']); ?>"
                                data-nh-shop-assortment-gallery-alt="<?php echo esc_attr($gallery_item['alt']); ?>"
                            >
                                <span class="nh-shop-assortment__media">
                                    <img
                                        class="nh-shop-assortment__image"
                                        src="<?php echo esc_url($gallery_item['url']); ?>"
                                        alt="<?php echo esc_attr($gallery_item['alt']); ?>"
                                        loading="lazy"
                                    />
                                </span>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else : ?>
            <div class="nh-shop-assortment__empty">
                <?php esc_html_e('Добавьте фотографии галереи в настройках блока Our Assortment.', 'nice-hair'); ?>
            </div>
        <?php endif; ?>
    </div>
</section>
