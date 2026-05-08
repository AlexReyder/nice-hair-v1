<?php
/**
 * Title: Nice Hair / Contact
 * Slug: nice-hair/shared-contact
 * Categories: nice-hair-shared
 * Inserter: yes
 */

$map_image_url = esc_url( get_template_directory_uri() . '/assets/images/contact-map.png' );
$arrow_url     = esc_url( get_template_directory_uri() . '/assets/images/arrow-mini.svg' );
$contact_phone = nice_hair_get_contact_phone_display('home');
$contact_phone_link = nice_hair_get_contact_phone_link('home');
$contact_address = nice_hair_get_contact_address_plain('home');
$contact_hours_compact = nice_hair_get_contact_hours_compact('home');
$whatsapp_url = nice_hair_get_contact_social_url('whatsapp', 'home', 'https://wa.me/971585988409');
$map_url = nice_hair_get_contact_map_url();
?>

<!-- wp:group {"tagName":"section","className":"nh-contact","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
<section class="wp-block-group nh-contact">
	<!-- wp:group {"className":"nh-contact__shell","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
	<div class="wp-block-group nh-contact__shell">
		<!-- wp:group {"className":"nh-contact__top","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
		<div class="wp-block-group nh-contact__top">
			<!-- wp:group {"className":"nh-contact__info","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
			<div class="wp-block-group nh-contact__info">
				<!-- wp:heading {"level":2,"className":"nh-contact__title","lock":{"move":true,"remove":true}} -->
				<h2 class="nh-contact__title">Didn't find your question here?</h2>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"className":"nh-contact__description","lock":{"move":true,"remove":true}} -->
				<p class="nh-contact__description">We're always <strong>online <?php echo esc_html( $contact_hours_compact ); ?></strong> - message us on WhatsApp and we'll help you out.</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"nh-contact__actions","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
			<div class="wp-block-group nh-contact__actions">
				<!-- wp:paragraph {"className":"nh-text-link nh-contact__link","lock":{"move":true,"remove":true}} -->
				<p class="nh-text-link nh-contact__link"><a href="<?php echo esc_url( $whatsapp_url ); ?>">[ ASK ON WHATSAPP ]</a></p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph {"className":"nh-contact__or","lock":{"move":true,"remove":true}} -->
				<p class="nh-contact__or">OR</p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph {"className":"nh-contact__phone","lock":{"move":true,"remove":true}} -->
				<p class="nh-contact__phone"><a href="tel:<?php echo esc_attr( $contact_phone_link ); ?>"><?php echo esc_html( $contact_phone ); ?></a></p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph {"className":"nh-contact__or","lock":{"move":true,"remove":true}} -->
				<p class="nh-contact__or">OR</p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph {"className":"nh-contact__address","lock":{"move":true,"remove":true}} -->
				<p class="nh-contact__address"><?php echo esc_html( $contact_address ); ?></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"nh-contact__map","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
		<div class="wp-block-group nh-contact__map">
			<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"nh-contact__map-image","lock":{"move":true,"remove":true}} -->
			<figure class="wp-block-image size-full nh-contact__map-image">
				<img src="<?php echo $map_image_url; ?>" alt="Map — Al Sufouh, Dubai" />
			</figure>
			<!-- /wp:image -->

			<!-- wp:paragraph {"className":"nh-contact__map-link","lock":{"move":true,"remove":true}} -->
			<p class="nh-contact__map-link"><a href="<?php echo esc_url( $map_url ); ?>">[ VIEW ON MAP ]<img class="nh-contact__map-arrow" src="<?php echo $arrow_url; ?>" alt="" /></a></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</section>
<!-- /wp:group -->
