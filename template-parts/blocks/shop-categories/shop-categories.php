<?php

declare(strict_types=1);

$eyebrow = get_field('nh_categories_eyebrow');
$title   = get_field('nh_categories_title');
$text    = get_field('nh_categories_text');
$items   = get_field('nh_categories_items');

if (empty($items) || ! is_array($items)) {
    return;
}

$anchor    = ! empty($block['anchor'])    ? $block['anchor'] : 'categories';
$className = ! empty($block['className']) ? ' ' . $block['className'] : '';
?>

<section class="nh-shop-categories<?php echo esc_attr($className); ?>" id="<?php echo esc_attr($anchor); ?>">
    <div class="nh-shop-categories__shell">
        <header class="nh-shop-categories__header">
            <?php if ($eyebrow) : ?>
                <p class="nh-shop-categories__eyebrow"><?php echo esc_html($eyebrow); ?></p>
            <?php endif; ?>

            <div class="nh-shop-categories__divider" aria-hidden="true"></div>

            <?php if ($title) : ?>
                <h2 class="nh-shop-categories__title"><?php echo wp_kses_post($title); ?></h2>
            <?php endif; ?>

            <?php if ($text) : ?>
                <p class="nh-shop-categories__text"><?php echo wp_kses_post($text); ?></p>
            <?php endif; ?>
        </header>

        <div class="nh-shop-categories__grid">
            <?php foreach ($items as $item) :
                $card_title = $item['item_title']    ?? '';
                $card_text  = $item['item_text']     ?? '';
                $image      = $item['item_image']    ?? null;
                $link       = $item['item_link']     ?? '#';
                $cta_text   = $item['item_cta_text'] ?? '';

                if (empty($card_title) || empty($image) || ! is_array($image)) {
                    continue;
                }

                $img_url = $image['sizes']['large'] ?? $image['url'] ?? '';
                $img_alt = $image['alt'] ?? '';

                $link = function_exists('nice_hair_resolve_shop_category_card_link')
                    ? nice_hair_resolve_shop_category_card_link((string) $link, (string) $card_title)
                    : ((string) $link ?: '#');
                ?>
                <a href="<?php echo esc_url($link); ?>" class="nh-shop-categories__card">
                    <img
                        class="nh-shop-categories__card-bg"
                        src="<?php echo esc_url($img_url); ?>"
                        alt="<?php echo esc_attr($img_alt); ?>"
                        loading="lazy"
                    />
                    <div class="nh-shop-categories__card-body">
                        <h3 class="nh-shop-categories__card-title"><?php echo esc_html($card_title); ?></h3>

                        <?php if ($card_text) : ?>
                            <p class="nh-shop-categories__card-text"><?php echo wp_kses_post($card_text); ?></p>
                        <?php endif; ?>

                        <?php if ($cta_text) : ?>
                            <span class="wp-block-button nh-cta-link nh-shop-categories__card-cta" aria-hidden="true">
                                <span class="wp-block-button__link wp-element-button"><?php echo esc_html($cta_text); ?></span>
                            </span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
