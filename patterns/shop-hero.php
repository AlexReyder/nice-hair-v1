<?php
/**
 * Title: Nice Hair / Shop Hero
 * Slug: nice-hair/shop-hero
 * Categories: nice-hair-shop
 * Inserter: yes
 */

$hero_image_url = esc_url( get_template_directory_uri() . '/assets/images/shop-hero.jpg' );
$contact_phone = nice_hair_get_contact_phone_display('shop');
$contact_phone_link = nice_hair_get_contact_phone_link('shop');
$contact_address = nice_hair_get_contact_address_display('shop');
$contact_hours = str_replace(': ', ":<br>", nice_hair_get_contact_hours('shop'));
$telegram_url = nice_hair_get_contact_social_url('telegram', 'shop', '#');
$instagram_url = nice_hair_get_contact_social_url('instagram', 'shop', '#');
$whatsapp_url = nice_hair_get_contact_social_url('whatsapp', 'shop', '#');
?>

<!-- wp:cover {"url":"<?php echo $hero_image_url; ?>","dimRatio":40,"customOverlayColor":"#1c1c1c","minHeight":100,"minHeightUnit":"vh","isDark":false,"align":"full","focalPoint":{"x":0.5,"y":0.5},"className":"nh-shop-hero","lock":{"move":true,"remove":true}} -->
<div class="wp-block-cover alignfull is-light nh-shop-hero" style="min-height:100vh">
	<span aria-hidden="true" class="wp-block-cover__background has-background-dim-40 has-background-dim" style="background-color:#1c1c1c"></span>
	<img class="wp-block-cover__image-background" alt="" src="<?php echo $hero_image_url; ?>" style="object-position:50% 50%" data-object-fit="cover" data-object-position="50% 50%" />
	<div class="wp-block-cover__inner-container">

		<!-- wp:group {"className":"nh-shop-hero__shell","lock":{"move":true,"remove":true}} -->
		<div class="wp-block-group nh-shop-hero__shell">

			<!-- wp:group {"className":"nh-shop-hero__top","lock":{"move":true,"remove":true}} -->
			<div class="wp-block-group nh-shop-hero__top">
				<!-- wp:group {"className":"nh-shop-hero__contact","lock":{"move":true,"remove":true}} -->
				<div class="wp-block-group nh-shop-hero__contact">
					<!-- wp:paragraph {"className":"nh-shop-hero__phone"} -->
					<p class="nh-shop-hero__phone"><a href="tel:<?php echo esc_attr( $contact_phone_link ); ?>"><?php echo esc_html( $contact_phone ); ?></a></p>
					<!-- /wp:paragraph -->

					<!-- wp:paragraph {"className":"nh-shop-hero__address"} -->
					<p class="nh-shop-hero__address"><?php echo wp_kses_post( $contact_address ); ?></p>
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"nh-shop-hero__content","lock":{"move":true,"remove":true}} -->
			<div class="wp-block-group nh-shop-hero__content">

				<!-- wp:group {"className":"nh-shop-hero__aside","lock":{"move":true,"remove":true}} -->
				<div class="wp-block-group nh-shop-hero__aside">
					<!-- wp:heading {"level":2,"className":"nh-shop-hero__secondary-title"} -->
					<h2 class="nh-shop-hero__secondary-title">Exceptional quality,<br>trusted by hair experts</h2>
					<!-- /wp:heading -->
				</div>
				<!-- /wp:group -->

				<!-- wp:group {"className":"nh-shop-hero__main","lock":{"move":true,"remove":true}} -->
				<div class="wp-block-group nh-shop-hero__main">
					<!-- wp:heading {"level":1,"className":"nh-shop-hero__title"} -->
					<h1 class="nh-shop-hero__title">/ Premium Hair and Hair Extensions for Professionals</h1>
					<!-- /wp:heading -->

					<!-- wp:paragraph {"className":"nh-shop-hero__text"} -->
					<p class="nh-shop-hero__text">Hair and hair extensions, keratin, tools - all in one place.</p>
					<!-- /wp:paragraph -->

					<!-- wp:buttons {"className":"nh-shop-hero__actions"} -->
					<div class="wp-block-buttons nh-shop-hero__actions">
						<!-- wp:button {"className":"nh-cta-link"} -->
						<div class="wp-block-button nh-cta-link"><a class="wp-block-button__link wp-element-button" href="#">START SHOPPING</a></div>
						<!-- /wp:button -->
					</div>
					<!-- /wp:buttons -->
				</div>
				<!-- /wp:group -->

			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"nh-shop-hero__bottom","lock":{"move":true,"remove":true}} -->
			<div class="wp-block-group nh-shop-hero__bottom">

				<!-- wp:group {"className":"nh-shop-hero__features","lock":{"move":true,"remove":true}} -->
				<div class="wp-block-group nh-shop-hero__features">

					<!-- wp:group {"className":"nh-shop-hero__feature","lock":{"move":true,"remove":true}} -->
					<div class="wp-block-group nh-shop-hero__feature">
						<!-- wp:paragraph {"className":"nh-shop-hero__feature-text"} -->
						<p class="nh-shop-hero__feature-text">More than 10.000<br>bulks in stock</p>
						<!-- /wp:paragraph -->
					</div>
					<!-- /wp:group -->

					<!-- wp:group {"className":"nh-shop-hero__feature","lock":{"move":true,"remove":true}} -->
					<div class="wp-block-group nh-shop-hero__feature">
						<!-- wp:paragraph {"className":"nh-shop-hero__feature-text"} -->
						<p class="nh-shop-hero__feature-text">Invisible keratin<br>bonds</p>
						<!-- /wp:paragraph -->
					</div>
					<!-- /wp:group -->

					<!-- wp:group {"className":"nh-shop-hero__feature","lock":{"move":true,"remove":true}} -->
					<div class="wp-block-group nh-shop-hero__feature">
						<!-- wp:paragraph {"className":"nh-shop-hero__feature-text"} -->
						<p class="nh-shop-hero__feature-text">Flawless and<br>natural result</p>
						<!-- /wp:paragraph -->
					</div>
					<!-- /wp:group -->

				</div>
				<!-- /wp:group -->

				<!-- wp:group {"className":"nh-shop-hero__hours","lock":{"move":true,"remove":true}} -->
				<div class="wp-block-group nh-shop-hero__hours">
					<!-- wp:paragraph {"className":"nh-shop-hero__hours-text"} -->
					<p class="nh-shop-hero__hours-text"><?php echo wp_kses_post( $contact_hours ); ?></p>
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:group -->

				<!-- wp:group {"className":"nh-shop-hero__socials","lock":{"move":true,"remove":true}} -->
				<div class="wp-block-group nh-shop-hero__socials">
					<!-- wp:paragraph {"className":"nh-shop-hero__social"} -->
					<p class="nh-shop-hero__social"><a href="<?php echo esc_url( $telegram_url ); ?>">Telegram</a></p>
					<!-- /wp:paragraph -->

					<!-- wp:paragraph {"className":"nh-shop-hero__social"} -->
					<p class="nh-shop-hero__social"><a href="<?php echo esc_url( $instagram_url ); ?>">Instagram</a></p>
					<!-- /wp:paragraph -->

					<!-- wp:paragraph {"className":"nh-shop-hero__social"} -->
					<p class="nh-shop-hero__social"><a href="<?php echo esc_url( $whatsapp_url ); ?>">WhatsApp</a></p>
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
