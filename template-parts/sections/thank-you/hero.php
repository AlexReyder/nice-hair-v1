<?php
declare(strict_types=1);

$hero = function_exists('nice_hair_get_thank_you_hero_context')
    ? nice_hair_get_thank_you_hero_context()
    : [];
?>
<section class="nh-thank-you-hero">
    <div class="nh-thank-you-hero__media" aria-hidden="true">
        <img src="<?php echo esc_url((string) ($hero['image_url'] ?? '')); ?>" alt="">
    </div>

    <div class="nh-thank-you-hero__shell">
        <div class="nh-thank-you-hero__top">
            <div class="nh-thank-you-hero__contact">
                <p class="nh-thank-you-hero__phone"><a href="tel:<?php echo esc_attr((string) ($hero['phone_link'] ?? '')); ?>"><?php echo esc_html((string) ($hero['phone_display'] ?? '')); ?></a></p>
                <p class="nh-thank-you-hero__address"><?php echo wp_kses_post((string) ($hero['address_html'] ?? '')); ?></p>
            </div>
        </div>

        <div class="nh-thank-you-hero__content">
            <div class="nh-thank-you-hero__main">
                <h1 class="nh-thank-you-hero__title"><?php echo esc_html((string) ($hero['title'] ?? '')); ?></h1>
                <p class="nh-thank-you-hero__text"><?php echo esc_html((string) ($hero['text'] ?? '')); ?></p>
                <div class="nh-thank-you-hero__actions">
                    <div class="wp-block-button nh-cta-link"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url((string) ($hero['cta_url'] ?? home_url('/shop/'))); ?>"><?php echo esc_html((string) ($hero['cta_label'] ?? 'START SHOPPING')); ?></a></div>
                </div>
            </div>
        </div>

        <div class="nh-thank-you-hero__bottom">
            <div class="nh-thank-you-hero__features">
                <?php foreach ((array) ($hero['feature_texts'] ?? []) as $feature_text) : ?>
                    <div class="nh-thank-you-hero__feature">
                        <p class="nh-thank-you-hero__feature-text"><?php echo wp_kses_post((string) $feature_text); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="nh-thank-you-hero__hours">
                <p class="nh-thank-you-hero__hours-text"><?php echo wp_kses_post((string) ($hero['hours_html'] ?? '')); ?></p>
            </div>

            <div class="nh-thank-you-hero__socials">
                <p class="nh-thank-you-hero__social"><a href="<?php echo esc_url((string) ($hero['telegram_url'] ?? '#')); ?>">Telegram</a></p>
                <p class="nh-thank-you-hero__social"><a href="<?php echo esc_url((string) ($hero['instagram_url'] ?? '#')); ?>">Instagram</a></p>
                <p class="nh-thank-you-hero__social"><a href="<?php echo esc_url((string) ($hero['whatsapp_url'] ?? '#')); ?>">WhatsApp</a></p>
            </div>
        </div>
    </div>
</section>

