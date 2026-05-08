<?php

declare(strict_types=1);

$eyebrow  = get_field('nh_stylists_eyebrow');
$value    = get_field('nh_stylists_value');
$subtitle = get_field('nh_stylists_subtitle');
$text     = get_field('nh_stylists_text');
$link     = get_field('nh_stylists_link');
$items    = get_field('nh_stylists_items');

if (empty($items) || ! is_array($items)) {
    return;
}

$anchor    = ! empty($block['anchor']) ? $block['anchor'] : 'stylists';
$className = ! empty($block['className']) ? ' ' . $block['className'] : '';

$link_url    = is_array($link) && ! empty($link['url'])   ? $link['url']   : '#';
$link_title  = is_array($link) && ! empty($link['title']) ? $link['title'] : 'Learn more';
$link_target = is_array($link) && ! empty($link['target']) ? $link['target'] : '';
?>

<section class="nh-salon-stylists<?php echo esc_attr($className); ?>" id="<?php echo esc_attr($anchor); ?>">
    <div class="nh-salon-stylists__shell">
        <div class="nh-salon-stylists__slider swiper" data-nh-stylists-swiper>
            <div class="swiper-wrapper nh-salon-stylists__track">
                <?php foreach ($items as $item) :
                    $image = $item['item_image'] ?? null;
                    $name  = $item['item_title'] ?? '';
                    $exp   = $item['item_text']  ?? '';

                    if (empty($image) || ! is_array($image)) {
                        continue;
                    }

                    $image_url = $image['sizes']['large'] ?? $image['url'] ?? '';
                    $image_alt = $image['alt'] ?? $name;
                    ?>
                    <div class="swiper-slide nh-salon-stylists__slide">
                        <figure class="nh-salon-stylists__card">
                            <img
                                class="nh-salon-stylists__image"
                                src="<?php echo esc_url($image_url); ?>"
                                alt="<?php echo esc_attr($image_alt); ?>"
                                loading="lazy"
                            />
                            <figcaption class="nh-salon-stylists__caption">
                                <?php if ($name) : ?>
                                    <p class="nh-salon-stylists__name"><?php echo esc_html($name); ?></p>
                                <?php endif; ?>
                                <?php if ($exp) : ?>
                                    <p class="nh-salon-stylists__exp"><?php echo esc_html($exp); ?></p>
                                <?php endif; ?>
                            </figcaption>
                        </figure>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <aside class="nh-salon-stylists__aside">
            <div class="nh-salon-stylists__nav">
                <button
                    class="nh-salon-stylists__nav-btn nh-salon-stylists__nav-btn--prev"
                    type="button"
                    aria-label="<?php esc_attr_e('Previous stylist', 'nice-hair'); ?>"
                    data-nh-stylists-prev
                >
                    <span class="nh-salon-stylists__nav-icon" aria-hidden="true"></span>
                </button>
                <button
                    class="nh-salon-stylists__nav-btn nh-salon-stylists__nav-btn--next"
                    type="button"
                    aria-label="<?php esc_attr_e('Next stylist', 'nice-hair'); ?>"
                    data-nh-stylists-next
                >
                    <span class="nh-salon-stylists__nav-icon" aria-hidden="true"></span>
                </button>
            </div>

            <?php if ($eyebrow) : ?>
                <p class="nh-salon-stylists__eyebrow"><?php echo esc_html($eyebrow); ?></p>
            <?php endif; ?>

            <div class="nh-salon-stylists__divider" aria-hidden="true"></div>

            <?php if ($value) : ?>
                <p class="nh-salon-stylists__value"><?php echo esc_html($value); ?></p>
            <?php endif; ?>

            <?php if ($subtitle) : ?>
                <p class="nh-salon-stylists__subtitle"><?php echo esc_html($subtitle); ?></p>
            <?php endif; ?>

            <?php if ($text) : ?>
                <p class="nh-salon-stylists__text"><?php echo wp_kses_post($text); ?></p>
            <?php endif; ?>

            <p class="nh-text-link nh-salon-stylists__link">
                <a
                    href="<?php echo esc_url($link_url); ?>"
                    <?php if ($link_target) : ?>target="<?php echo esc_attr($link_target); ?>" rel="noopener"<?php endif; ?>
                >[ <?php echo esc_html($link_title); ?> ]</a>
            </p>
        </aside>
    </div>
</section>
