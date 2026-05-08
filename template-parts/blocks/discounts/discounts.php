<?php

declare(strict_types=1);

$eyebrow = get_field('nh_discounts_eyebrow');
$title   = get_field('nh_discounts_title');
$text    = get_field('nh_discounts_text');
$items   = get_field('nh_discounts_items');

if (empty($items) || ! is_array($items)) {
    return;
}

$anchor    = ! empty($block['anchor'])    ? $block['anchor'] : 'discounts';
$className = ! empty($block['className']) ? ' ' . $block['className'] : '';
?>

<section class="nh-salon-discounts<?php echo esc_attr($className); ?>" id="<?php echo esc_attr($anchor); ?>">
    <div class="nh-salon-discounts__shell">
        <header class="nh-salon-discounts__header">
            <?php if ($eyebrow) : ?>
                <p class="nh-salon-discounts__eyebrow"><?php echo esc_html($eyebrow); ?></p>
            <?php endif; ?>

            <div class="nh-salon-discounts__divider" aria-hidden="true"></div>

            <?php if ($title) : ?>
                <h2 class="nh-salon-discounts__title"><?php echo wp_kses_post($title); ?></h2>
            <?php endif; ?>

            <?php if ($text) : ?>
                <p class="nh-salon-discounts__text"><?php echo wp_kses_post($text); ?></p>
            <?php endif; ?>
        </header>

        <div class="nh-salon-discounts__grid">
            <?php foreach ($items as $item) :
                $card_title   = $item['item_title']       ?? '';
                $card_text    = $item['item_text']        ?? '';
                $image        = $item['item_image']       ?? null;
                $cta_text     = $item['item_cta_text']    ?? 'BOOK AN APPOINTMENT';
                $popup_label  = $item['item_popup_label'] ?? '';
                $telegram     = $item['item_telegram']    ?? '';
                $instagram    = $item['item_instagram']   ?? '';
                $whatsapp     = $item['item_whatsapp']    ?? '';

                if (empty($card_title) || empty($image) || ! is_array($image)) {
                    continue;
                }

                if (! is_string($popup_label) || trim($popup_label) === '') {
                    $popup_label = 'Book an appointment';
                }

                $img_url = $image['sizes']['large'] ?? $image['url'] ?? '';
                $img_alt = $image['alt'] ?? '';

                $has_socials = $telegram || $instagram || $whatsapp;
                ?>
                <article class="nh-salon-discounts__card">
                    <div class="nh-salon-discounts__card-body">
                        <h3 class="nh-salon-discounts__card-title"><?php echo esc_html($card_title); ?></h3>

                        <?php if ($card_text) : ?>
                            <p class="nh-salon-discounts__card-text"><?php echo wp_kses_post($card_text); ?></p>
                        <?php endif; ?>

                        <div class="nh-salon-discounts__cta">
                            <a class="nh-salon-discounts__cta-link" href="#book" data-popup-salon-trigger data-popup-label="<?php echo esc_attr($popup_label); ?>">
                                <?php echo esc_html($cta_text); ?>
                            </a>
                        </div>

                        <?php if ($has_socials) : ?>
                            <ul class="nh-salon-discounts__socials">
                                <?php if ($telegram) : ?>
                                    <li><a href="<?php echo esc_url($telegram); ?>" target="_blank" rel="noopener noreferrer">Telegram</a></li>
                                <?php endif; ?>
                                <?php if ($instagram) : ?>
                                    <li><a href="<?php echo esc_url($instagram); ?>" target="_blank" rel="noopener noreferrer">Instagram</a></li>
                                <?php endif; ?>
                                <?php if ($whatsapp) : ?>
                                    <li><a href="<?php echo esc_url($whatsapp); ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a></li>
                                <?php endif; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <div class="nh-salon-discounts__card-media">
                        <img
                            src="<?php echo esc_url($img_url); ?>"
                            alt="<?php echo esc_attr($img_alt); ?>"
                            loading="lazy"
                        />
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
