<?php
/**
 * Title: Nice Hair / Thank You Hero
 * Slug: nice-hair/thank-you-hero
 * Categories: nice-hair-shared
 * Inserter: yes
 */

$hero = function_exists('nice_hair_get_thank_you_hero_context')
    ? nice_hair_get_thank_you_hero_context()
    : [];
?>
<!-- wp:cover {"url":"<?php echo esc_url((string) ($hero['image_url'] ?? '')); ?>","dimRatio":40,"customOverlayColor":"#1c1c1c","minHeight":100,"minHeightUnit":"vh","isDark":false,"align":"full","focalPoint":{"x":0.5,"y":0.5},"className":"nh-thank-you-hero","lock":{"move":true,"remove":true}} -->
<div class="wp-block-cover alignfull is-light nh-thank-you-hero" style="min-height:100vh">
	<span aria-hidden="true" class="wp-block-cover__background has-background-dim-40 has-background-dim" style="background-color:#1c1c1c"></span>
	<img class="wp-block-cover__image-background" alt="" src="<?php echo esc_url((string) ($hero['image_url'] ?? '')); ?>" style="object-position:50% 50%" data-object-fit="cover" data-object-position="50% 50%" />
	<div class="wp-block-cover__inner-container">
		<!-- wp:group {"className":"nh-thank-you-hero__shell","lock":{"move":true,"remove":true}} -->
		<div class="wp-block-group nh-thank-you-hero__shell">
			<!-- wp:group {"className":"nh-thank-you-hero__top","lock":{"move":true,"remove":true}} -->
			<div class="wp-block-group nh-thank-you-hero__top">
				<!-- wp:group {"className":"nh-thank-you-hero__contact","lock":{"move":true,"remove":true}} -->
				<div class="wp-block-group nh-thank-you-hero__contact">
					<!-- wp:paragraph {"className":"nh-thank-you-hero__phone"} -->
					<p class="nh-thank-you-hero__phone"><a href="tel:<?php echo esc_attr((string) ($hero['phone_link'] ?? '')); ?>"><?php echo esc_html((string) ($hero['phone_display'] ?? '')); ?></a></p>
					<!-- /wp:paragraph -->

					<!-- wp:paragraph {"className":"nh-thank-you-hero__address"} -->
					<p class="nh-thank-you-hero__address"><?php echo wp_kses_post((string) ($hero['address_html'] ?? '')); ?></p>
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"nh-thank-you-hero__content","lock":{"move":true,"remove":true}} -->
			<div class="wp-block-group nh-thank-you-hero__content">
				<!-- wp:group {"className":"nh-thank-you-hero__main","lock":{"move":true,"remove":true}} -->
				<div class="wp-block-group nh-thank-you-hero__main">
					<!-- wp:heading {"level":1,"className":"nh-thank-you-hero__title"} -->
					<h1 class="nh-thank-you-hero__title"><?php echo esc_html((string) ($hero['title'] ?? '')); ?></h1>
					<!-- /wp:heading -->

					<!-- wp:paragraph {"className":"nh-thank-you-hero__text"} -->
					<p class="nh-thank-you-hero__text"><?php echo esc_html((string) ($hero['text'] ?? '')); ?></p>
					<!-- /wp:paragraph -->

					<!-- wp:buttons {"className":"nh-thank-you-hero__actions"} -->
					<div class="wp-block-buttons nh-thank-you-hero__actions">
						<!-- wp:button {"className":"nh-cta-link"} -->
						<div class="wp-block-button nh-cta-link"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url((string) ($hero['cta_url'] ?? home_url('/shop/'))); ?>"><?php echo esc_html((string) ($hero['cta_label'] ?? 'START SHOPPING')); ?></a></div>
						<!-- /wp:button -->
					</div>
					<!-- /wp:buttons -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"nh-thank-you-hero__bottom","lock":{"move":true,"remove":true}} -->
			<div class="wp-block-group nh-thank-you-hero__bottom">
				<!-- wp:group {"className":"nh-thank-you-hero__features","lock":{"move":true,"remove":true}} -->
				<div class="wp-block-group nh-thank-you-hero__features">
					<?php foreach ((array) ($hero['feature_texts'] ?? []) as $feature_text) : ?>
						<!-- wp:group {"className":"nh-thank-you-hero__feature","lock":{"move":true,"remove":true}} -->
						<div class="wp-block-group nh-thank-you-hero__feature">
							<!-- wp:paragraph {"className":"nh-thank-you-hero__feature-text"} -->
							<p class="nh-thank-you-hero__feature-text"><?php echo wp_kses_post((string) $feature_text); ?></p>
							<!-- /wp:paragraph -->
						</div>
						<!-- /wp:group -->
					<?php endforeach; ?>
				</div>
				<!-- /wp:group -->

				<!-- wp:group {"className":"nh-thank-you-hero__hours","lock":{"move":true,"remove":true}} -->
				<div class="wp-block-group nh-thank-you-hero__hours">
					<!-- wp:paragraph {"className":"nh-thank-you-hero__hours-text"} -->
					<p class="nh-thank-you-hero__hours-text"><?php echo wp_kses_post((string) ($hero['hours_html'] ?? '')); ?></p>
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:group -->

				<!-- wp:group {"className":"nh-thank-you-hero__socials","lock":{"move":true,"remove":true}} -->
				<div class="wp-block-group nh-thank-you-hero__socials">
					<!-- wp:paragraph {"className":"nh-thank-you-hero__social"} -->
					<p class="nh-thank-you-hero__social"><a href="<?php echo esc_url((string) ($hero['telegram_url'] ?? '#')); ?>">Telegram</a></p>
					<!-- /wp:paragraph -->

					<!-- wp:paragraph {"className":"nh-thank-you-hero__social"} -->
					<p class="nh-thank-you-hero__social"><a href="<?php echo esc_url((string) ($hero['instagram_url'] ?? '#')); ?>">Instagram</a></p>
					<!-- /wp:paragraph -->

					<!-- wp:paragraph {"className":"nh-thank-you-hero__social"} -->
					<p class="nh-thank-you-hero__social"><a href="<?php echo esc_url((string) ($hero['whatsapp_url'] ?? '#')); ?>">WhatsApp</a></p>
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->
	</div>
</div>
<!-- /wp:cover -->

