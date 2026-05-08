<?php

declare(strict_types=1);

$eyebrow = get_field('nh_results_eyebrow');
$title   = get_field('nh_results_title');
$text_1  = get_field('nh_results_text_1');
$text_2  = get_field('nh_results_text_2');
$hide_nav_desktop = (bool) get_field('nh_results_hide_nav_desktop', 'option');
$hide_nav_tablet  = (bool) get_field('nh_results_hide_nav_tablet', 'option');
$hide_nav_mobile  = (bool) get_field('nh_results_hide_nav_mobile', 'option');
$items   = get_field('nh_results_items', 'option');

if (empty($items) || ! is_array($items)) {
    return;
}

$anchor  = ! empty($block['anchor']) ? $block['anchor'] : 'results';
$classes = ['nh-salon-results'];

if (! empty($block['className'])) {
    $classes[] = (string) $block['className'];
}

if ($hide_nav_desktop) {
    $classes[] = 'nh-salon-results--hide-nav-desktop';
}

if ($hide_nav_tablet) {
    $classes[] = 'nh-salon-results--hide-nav-tablet';
}

if ($hide_nav_mobile) {
    $classes[] = 'nh-salon-results--hide-nav-mobile';
}

$class_name = implode(' ', array_filter($classes));
?>

<section class="<?php echo esc_attr($class_name); ?>" id="<?php echo esc_attr($anchor); ?>">
    <div class="nh-salon-results__shell">
        <header class="nh-salon-results__header">
            <?php if ($eyebrow) : ?>
                <p class="nh-salon-results__eyebrow"><?php echo esc_html($eyebrow); ?></p>
            <?php endif; ?>

            <div class="nh-salon-results__divider" aria-hidden="true"></div>

            <?php if ($title) : ?>
                <h2 class="nh-salon-results__title"><?php echo wp_kses_post($title); ?></h2>
            <?php endif; ?>

            <div class="nh-salon-results__intro">
                <?php if ($text_1) : ?>
                    <p class="nh-salon-results__text"><?php echo wp_kses_post($text_1); ?></p>
                <?php endif; ?>
                <?php if ($text_2) : ?>
                    <p class="nh-salon-results__text"><?php echo wp_kses_post($text_2); ?></p>
                <?php endif; ?>
            </div>
        </header>

        <div class="nh-salon-results__nav">
            <button
                class="nh-salon-results__nav-btn nh-salon-results__nav-btn--prev"
                type="button"
                aria-label="<?php esc_attr_e('Previous result', 'nice-hair'); ?>"
                data-nh-results-prev
            >
                <span class="nh-salon-results__nav-icon" aria-hidden="true"></span>
            </button>
            <button
                class="nh-salon-results__nav-btn nh-salon-results__nav-btn--next"
                type="button"
                aria-label="<?php esc_attr_e('Next result', 'nice-hair'); ?>"
                data-nh-results-next
            >
                <span class="nh-salon-results__nav-icon" aria-hidden="true"></span>
            </button>
        </div>

        <div class="nh-salon-results__slider swiper" data-nh-results-swiper>
            <div class="swiper-wrapper nh-salon-results__track">
                <?php foreach ($items as $idx => $item) :
                    $before = $item['item_before_image'] ?? null;
                    $after  = $item['item_after_image']  ?? null;

                    if (empty($before) || empty($after) || ! is_array($before) || ! is_array($after)) {
                        continue;
                    }

                    $before_url = $before['sizes']['large'] ?? $before['url'] ?? '';
                    $after_url  = $after['sizes']['large']  ?? $after['url']  ?? '';
                    $before_alt = $before['alt'] ?? '';
                    $after_alt  = $after['alt']  ?? '';

                    $length   = $item['item_length']   ?? '';
                    $hair     = $item['item_hair']     ?? '';
                    $capsules = $item['item_capsules'] ?? '';
                    $comment  = $item['item_comment']  ?? '';

                    $sync_id = 'results-' . (int) $idx;
                    ?>
                    <div class="swiper-slide nh-salon-results__slide">
                        <article
                            class="nh-salon-results__card"
                            data-before-after-card
                            data-before-after-state="before"
                            data-before-after-sync-id="<?php echo esc_attr($sync_id); ?>"
                        >
                            <figure class="nh-salon-results__figure">
                                <div class="nh-salon-results__media">
                                    <img
                                        class="nh-salon-results__image nh-salon-results__image--before"
                                        src="<?php echo esc_url($before_url); ?>"
                                        alt="<?php echo esc_attr($before_alt); ?>"
                                        loading="lazy"
                                    />
                                    <img
                                        class="nh-salon-results__image nh-salon-results__image--after"
                                        src="<?php echo esc_url($after_url); ?>"
                                        alt="<?php echo esc_attr($after_alt); ?>"
                                        loading="lazy"
                                    />
                                    <div class="nh-salon-results__toggle">
                                        <button
                                            type="button"
                                            class="nh-salon-results__toggle-btn"
                                            data-before-after-toggle="before"
                                        >[ BEFORE ]</button>
                                        <button
                                            type="button"
                                            class="nh-salon-results__toggle-btn"
                                            data-before-after-toggle="after"
                                        >[ AFTER ]</button>
                                    </div>
                                </div>

                                <figcaption class="nh-salon-results__meta">
                                    <ul class="nh-salon-results__data">
                                        <?php if ($length) : ?>
                                            <li>
                                                <span class="nh-salon-results__data-label">length:</span>
                                                <b><?php echo esc_html($length); ?></b>
                                            </li>
                                        <?php endif; ?>
                                        <?php if ($hair) : ?>
                                            <li>
                                                <span class="nh-salon-results__data-label">hair:</span>
                                                <b><?php echo esc_html($hair); ?></b>
                                            </li>
                                        <?php endif; ?>
                                        <?php if ($capsules) : ?>
                                            <li>
                                                <span class="nh-salon-results__data-label">capsules:</span>
                                                <b><?php echo esc_html($capsules); ?></b>
                                            </li>
                                        <?php endif; ?>
                                    </ul>

                                    <?php if ($comment) : ?>
                                        <div class="nh-salon-results__comment">
                                            <span class="nh-salon-results__comment-icon" aria-hidden="true"></span>
                                            <p class="nh-salon-results__comment-text">
                                                <span class="nh-salon-results__comment-label">Stylist&rsquo;s comment: </span>
                                                <?php echo wp_kses_post($comment); ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                </figcaption>
                            </figure>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
