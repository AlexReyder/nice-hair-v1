<?php
/**
 * Title: Nice Hair / Salon Hero
 * Slug: nice-hair/salon-hero
 * Categories: nice-hair-salon
 * Inserter: yes
 */

$hero_image_url = esc_url( get_template_directory_uri() . '/assets/images/salon-hero.png' );
$contact_phone = nice_hair_get_contact_phone_display('salon');
$contact_phone_link = nice_hair_get_contact_phone_link('salon');
$contact_address = nice_hair_get_contact_address_display('salon');
$contact_hours = str_replace(': ', ":<br>", nice_hair_get_contact_hours('salon'));
$telegram_url = nice_hair_get_contact_social_url('telegram', 'salon', '#');
$instagram_url = nice_hair_get_contact_social_url('instagram', 'salon', '#');
$whatsapp_url = nice_hair_get_contact_social_url('whatsapp', 'salon', '#');
?>

<!-- wp:cover {"url":"<?php echo $hero_image_url; ?>","dimRatio":40,"customOverlayColor":"#1c1c1c","minHeight":100,"minHeightUnit":"vh","isDark":false,"align":"full","focalPoint":{"x":0.5,"y":0.5},"className":"nh-salon-hero","lock":{"move":true,"remove":true}} -->
<div class="wp-block-cover alignfull is-light nh-salon-hero" style="min-height:100vh">
	<span aria-hidden="true" class="wp-block-cover__background has-background-dim-40 has-background-dim" style="background-color:#1c1c1c"></span>
	<img class="wp-block-cover__image-background" alt="" src="<?php echo $hero_image_url; ?>" style="object-position:50% 50%" data-object-fit="cover" data-object-position="50% 50%" />
	<div class="wp-block-cover__inner-container">

		<!-- wp:group {"className":"nh-salon-hero__shell","lock":{"move":true,"remove":true}} -->
		<div class="wp-block-group nh-salon-hero__shell">

			<!-- wp:group {"className":"nh-salon-hero__top","lock":{"move":true,"remove":true}} -->
			<div class="wp-block-group nh-salon-hero__top">
				<!-- wp:group {"className":"nh-salon-hero__contact","lock":{"move":true,"remove":true}} -->
				<div class="wp-block-group nh-salon-hero__contact">
					<!-- wp:paragraph {"className":"nh-salon-hero__phone"} -->
					<p class="nh-salon-hero__phone"><a href="tel:<?php echo esc_attr( $contact_phone_link ); ?>"><?php echo esc_html( $contact_phone ); ?></a></p>
					<!-- /wp:paragraph -->

					<!-- wp:paragraph {"className":"nh-salon-hero__address"} -->
					<p class="nh-salon-hero__address"><?php echo wp_kses_post( $contact_address ); ?></p>
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"nh-salon-hero__content","lock":{"move":true,"remove":true}} -->
			<div class="wp-block-group nh-salon-hero__content">

				<!-- wp:group {"className":"nh-salon-hero__aside","lock":{"move":true,"remove":true}} -->
				<div class="wp-block-group nh-salon-hero__aside">
					<!-- wp:heading {"level":2,"className":"nh-salon-hero__secondary-title"} -->
					<h2 class="nh-salon-hero__secondary-title">Natural result,<br>premium quality.</h2>
					<!-- /wp:heading -->
				</div>
				<!-- /wp:group -->

				<!-- wp:group {"className":"nh-salon-hero__main","lock":{"move":true,"remove":true}} -->
				<div class="wp-block-group nh-salon-hero__main">
					<!-- wp:heading {"level":1,"className":"nh-salon-hero__title"} -->
					<h1 class="nh-salon-hero__title">/ Premium hair extensions salon in the heart of Dubai</h1>
					<!-- /wp:heading -->

					<!-- wp:paragraph {"className":"nh-salon-hero__text"} -->
					<p class="nh-salon-hero__text">The volume and length you've always dreamed of. A personalized experience by experts with 14+ years of experience, using premium-quality hair from our own production and in-house showroom.</p>
					<!-- /wp:paragraph -->

					<!-- wp:buttons {"className":"nh-salon-hero__actions"} -->
					<div class="wp-block-buttons nh-salon-hero__actions">
						<!-- wp:button {"className":"nh-cta-link"} -->
						<div class="wp-block-button nh-cta-link"><a class="wp-block-button__link wp-element-button" href="#book" data-popup-salon-trigger data-popup-label="Book an appointment">BOOK AN APPOINTMENT</a></div>
						<!-- /wp:button -->
					</div>
					<!-- /wp:buttons -->
				</div>
				<!-- /wp:group -->

			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"nh-salon-hero__bottom","lock":{"move":true,"remove":true}} -->
			<div class="wp-block-group nh-salon-hero__bottom">

				<!-- wp:group {"className":"nh-salon-hero__features","lock":{"move":true,"remove":true}} -->
				<div class="wp-block-group nh-salon-hero__features">

					<!-- wp:group {"className":"nh-salon-hero__feature","lock":{"move":true,"remove":true}} -->
					<div class="wp-block-group nh-salon-hero__feature">
						<!-- wp:paragraph {"className":"nh-salon-hero__feature-text"} -->
						<p class="nh-salon-hero__feature-text">More than 10.000<br>bulks in stock</p>
						<!-- /wp:paragraph -->
					</div>
					<!-- /wp:group -->

					<!-- wp:group {"className":"nh-salon-hero__feature","lock":{"move":true,"remove":true}} -->
					<div class="wp-block-group nh-salon-hero__feature">
						<!-- wp:paragraph {"className":"nh-salon-hero__feature-text"} -->
						<p class="nh-salon-hero__feature-text">Invisible keratin<br>bonds</p>
						<!-- /wp:paragraph -->
					</div>
					<!-- /wp:group -->

					<!-- wp:group {"className":"nh-salon-hero__feature","lock":{"move":true,"remove":true}} -->
					<div class="wp-block-group nh-salon-hero__feature">
						<!-- wp:paragraph {"className":"nh-salon-hero__feature-text"} -->
						<p class="nh-salon-hero__feature-text">Flawless and<br>natural result</p>
						<!-- /wp:paragraph -->
					</div>
					<!-- /wp:group -->

				</div>
				<!-- /wp:group -->

				<!-- wp:group {"className":"nh-salon-hero__hours","lock":{"move":true,"remove":true}} -->
				<div class="wp-block-group nh-salon-hero__hours">
					<!-- wp:paragraph {"className":"nh-salon-hero__hours-text"} -->
					<p class="nh-salon-hero__hours-text"><?php echo wp_kses_post( $contact_hours ); ?></p>
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:group -->

				<!-- wp:group {"className":"nh-salon-hero__socials","lock":{"move":true,"remove":true}} -->
				<div class="wp-block-group nh-salon-hero__socials">
					<!-- wp:paragraph {"className":"nh-salon-hero__social"} -->
					<p class="nh-salon-hero__social"><a href="<?php echo esc_url( $telegram_url ); ?>">Telegram</a></p>
					<!-- /wp:paragraph -->

					<!-- wp:paragraph {"className":"nh-salon-hero__social"} -->
					<p class="nh-salon-hero__social"><a href="<?php echo esc_url( $instagram_url ); ?>">Instagram</a></p>
					<!-- /wp:paragraph -->

					<!-- wp:paragraph {"className":"nh-salon-hero__social"} -->
					<p class="nh-salon-hero__social"><a href="<?php echo esc_url( $whatsapp_url ); ?>">WhatsApp</a></p>
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
