<?php

declare(strict_types=1);

$eyebrow = trim((string) get_field('nh_results_gallery_shop_eyebrow'));
$title = trim((string) get_field('nh_results_gallery_shop_title'));
$text_1 = trim((string) get_field('nh_results_gallery_shop_text_1'));
$text_2 = trim((string) get_field('nh_results_gallery_shop_text_2'));
$field_anchor = trim((string) get_field('nh_results_gallery_shop_anchor'));
$gallery_rows = get_field('nh_results_gallery_shop_items');
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
    $full_image_width = isset($image['width']) ? (int) $image['width'] : 0;
    $full_image_height = isset($image['height']) ? (int) $image['height'] : 0;

    if ($image_id > 0) {
        $resolved_large_image = wp_get_attachment_image_src($image_id, 'large');
        $resolved_full_image = wp_get_attachment_image_src($image_id, 'full');

        if (is_array($resolved_large_image) && ! empty($resolved_large_image[0])) {
            $image_url = (string) $resolved_large_image[0];
        }

        if (is_array($resolved_full_image) && ! empty($resolved_full_image[0])) {
            $full_image_url = (string) $resolved_full_image[0];
            $full_image_width = isset($resolved_full_image[1]) ? (int) $resolved_full_image[1] : $full_image_width;
            $full_image_height = isset($resolved_full_image[2]) ? (int) $resolved_full_image[2] : $full_image_height;
        }
    }

    if ($image_url === '') {
        continue;
    }

    if ($full_image_url === '') {
        $full_image_url = $image_url;
    }

    if ($full_image_width <= 0) {
        $full_image_width = 1200;
    }

    if ($full_image_height <= 0) {
        $full_image_height = 1650;
    }

    $image_alt = trim((string) ($image['alt'] ?? ''));

    if ($image_alt === '') {
        $image_alt = trim((string) ($image['title'] ?? ''));
    }

    if ($image_alt === '') {
        $image_alt = __('Results gallery image', 'nice-hair');
    }

    $gallery_items[] = [
        'id'       => $image_id,
        'url'      => $image_url,
        'full_url' => $full_image_url,
        'width'    => $full_image_width,
        'height'   => $full_image_height,
        'alt'      => $image_alt,
    ];
}

if ($gallery_items === [] && ! $preview_mode) {
    return;
}

$has_navigation = count($gallery_items) > 1;
$resolved_anchor = ! empty($block['anchor'])
    ? (string) $block['anchor']
    : $field_anchor;
$anchor = $resolved_anchor !== '' ? sanitize_title($resolved_anchor) : 'shop-results-gallery';
$classes = ['nh-salon-results', 'nh-results-gallery-shop'];

if (! empty($block['className'])) {
    $classes[] = (string) $block['className'];
}

$class_name = implode(' ', array_filter($classes));
?>

<section
    class="<?php echo esc_attr($class_name); ?>"
    id="<?php echo esc_attr($anchor); ?>"
    data-nh-results-gallery-shop
    data-nh-results-gallery-shop-gallery
>
    <div class="nh-salon-results__shell">
        <header class="nh-salon-results__header">
            <?php if ($eyebrow !== '') : ?>
                <p class="nh-salon-results__eyebrow"><?php echo esc_html($eyebrow); ?></p>
            <?php endif; ?>

            <div class="nh-salon-results__divider" aria-hidden="true"></div>

            <?php if ($title !== '') : ?>
                <h2 class="nh-salon-results__title"><?php echo wp_kses_post($title); ?></h2>
            <?php endif; ?>

            <div class="nh-salon-results__intro">
                <?php if ($text_1 !== '') : ?>
                    <p class="nh-salon-results__text"><?php echo wp_kses_post($text_1); ?></p>
                <?php endif; ?>
                <?php if ($text_2 !== '') : ?>
                    <p class="nh-salon-results__text"><?php echo wp_kses_post($text_2); ?></p>
                <?php endif; ?>
            </div>
        </header>

        <?php if ($has_navigation) : ?>
            <div class="nh-salon-results__nav" role="group" aria-label="<?php esc_attr_e('Results gallery navigation', 'nice-hair'); ?>">
                <button
                    class="nh-salon-results__nav-btn nh-salon-results__nav-btn--prev"
                    type="button"
                    aria-label="<?php esc_attr_e('Previous result image', 'nice-hair'); ?>"
                    data-nh-results-gallery-shop-prev
                >
                    <span class="nh-salon-results__nav-icon" aria-hidden="true"></span>
                </button>
                <button
                    class="nh-salon-results__nav-btn nh-salon-results__nav-btn--next"
                    type="button"
                    aria-label="<?php esc_attr_e('Next result image', 'nice-hair'); ?>"
                    data-nh-results-gallery-shop-next
                >
                    <span class="nh-salon-results__nav-icon" aria-hidden="true"></span>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($gallery_items !== []) : ?>
            <div class="nh-salon-results__slider swiper" data-nh-results-gallery-shop-swiper>
                <div class="swiper-wrapper nh-salon-results__track">
                    <?php foreach ($gallery_items as $gallery_item) : ?>
                        <div class="swiper-slide nh-salon-results__slide">
                            <a
                                class="nh-salon-results__card"
                                href="<?php echo esc_url($gallery_item['full_url']); ?>"
                                aria-label="<?php esc_attr_e('Open result image fullscreen', 'nice-hair'); ?>"
                                data-nh-results-gallery-shop-item
                                data-nh-results-gallery-shop-width="<?php echo esc_attr((string) $gallery_item['width']); ?>"
                                data-nh-results-gallery-shop-height="<?php echo esc_attr((string) $gallery_item['height']); ?>"
                            >
                                <figure class="nh-salon-results__figure">
                                    <div class="nh-salon-results__media">
                                        <img
                                            class="nh-salon-results__image"
                                            src="<?php echo esc_url($gallery_item['url']); ?>"
                                            alt="<?php echo esc_attr($gallery_item['alt']); ?>"
                                            loading="lazy"
                                        />
                                    </div>
                                </figure>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else : ?>
            <div class="nh-results-gallery-shop__empty">
                <?php esc_html_e('Добавьте фотографии галереи в настройках блока Results Gallery Shop.', 'nice-hair'); ?>
            </div>
        <?php endif; ?>
    </div>
</section>
