<?php
/**
 * Title: Nice Hair / Salon Our Approach
 * Slug: nice-hair/salon-our-approach
 * Categories: nice-hair-salon
 * Inserter: yes
 */

$approach_dir_uri = esc_url( get_template_directory_uri() . '/assets/images/approach' );
$telegram_url = nice_hair_get_contact_social_url('telegram', 'salon', '#');
$instagram_url = nice_hair_get_contact_social_url('instagram', 'salon', '#');
$whatsapp_url = nice_hair_get_contact_social_url('whatsapp', 'salon', '#');
?>

<!-- wp:group {"align":"full","className":"nh-salon-our-approach","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
<div class="wp-block-group alignfull nh-salon-our-approach">

	<!-- wp:group {"className":"nh-salon-our-approach__top","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
	<div class="wp-block-group nh-salon-our-approach__top">
		<!-- wp:paragraph {"className":"nh-salon-our-approach__eyebrow","lock":{"move":true,"remove":true}} -->
		<p class="nh-salon-our-approach__eyebrow">[ OUR APPROACH ]</p>
		<!-- /wp:paragraph -->

		<!-- wp:spacer {"height":"6px","className":"nh-salon-our-approach__divider","lock":{"move":true,"remove":true}} -->
		<div style="height:6px" aria-hidden="true" class="wp-block-spacer nh-salon-our-approach__divider"></div>
		<!-- /wp:spacer -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"className":"nh-salon-our-approach__intro","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
	<div class="wp-block-group nh-salon-our-approach__intro">
		<!-- wp:heading {"level":2,"className":"nh-salon-our-approach__title","lock":{"move":true,"remove":true}} -->
		<h2 class="nh-salon-our-approach__title">Simple and Seamless — From Consultation to Results</h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"className":"nh-salon-our-approach__subtitle","lock":{"move":true,"remove":true}} -->
		<p class="nh-salon-our-approach__subtitle">We're with you at every step, with attention to detail and genuine care for your comfort.</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"className":"nh-salon-our-approach__grid","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
	<div class="wp-block-group nh-salon-our-approach__grid">

		<!-- wp:group {"className":"nh-salon-our-approach__card nh-salon-our-approach__card--consultation","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
		<div class="wp-block-group nh-salon-our-approach__card nh-salon-our-approach__card--consultation">
			<!-- wp:heading {"level":3,"className":"nh-salon-our-approach__card-title","lock":{"move":true,"remove":true}} -->
			<h3 class="nh-salon-our-approach__card-title">1. Consultation</h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"className":"nh-salon-our-approach__card-text","lock":{"move":true,"remove":true}} -->
			<p class="nh-salon-our-approach__card-text">We discuss your goals and assess your natural hair — in person or online.</p>
			<!-- /wp:paragraph -->

			<!-- wp:buttons {"className":"nh-salon-our-approach__cta","lock":{"move":true,"remove":true}} -->
			<div class="wp-block-buttons nh-salon-our-approach__cta">
				<!-- wp:button {"className":"nh-cta-link","lock":{"move":true,"remove":true}} -->
				<div class="wp-block-button nh-cta-link"><a class="wp-block-button__link wp-element-button" href="#book" data-popup-salon-trigger data-popup-label="Book an appointment">BOOK AN APPOINTMENT</a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->

			<!-- wp:list {"className":"nh-salon-our-approach__socials","lock":{"move":true,"remove":true}} -->
			<ul class="wp-block-list nh-salon-our-approach__socials">
				<!-- wp:list-item {"lock":{"move":true,"remove":true}} -->
				<li><a href="<?php echo esc_url( $telegram_url ); ?>">Telegram</a></li>
				<!-- /wp:list-item -->
				<!-- wp:list-item {"lock":{"move":true,"remove":true}} -->
				<li><a href="<?php echo esc_url( $instagram_url ); ?>">Instagram</a></li>
				<!-- /wp:list-item -->
				<!-- wp:list-item {"lock":{"move":true,"remove":true}} -->
				<li><a href="<?php echo esc_url( $whatsapp_url ); ?>">WhatsApp</a></li>
				<!-- /wp:list-item -->
			</ul>
			<!-- /wp:list -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"nh-salon-our-approach__card nh-salon-our-approach__card--photo","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
		<div class="wp-block-group nh-salon-our-approach__card nh-salon-our-approach__card--photo">
			<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"nh-salon-our-approach__card-bg","lock":{"move":true,"remove":true}} -->
			<figure class="wp-block-image size-full nh-salon-our-approach__card-bg"><img src="<?php echo $approach_dir_uri; ?>/approach-2.jpg" alt="" /></figure>
			<!-- /wp:image -->

			<!-- wp:heading {"level":3,"className":"nh-salon-our-approach__card-title","lock":{"move":true,"remove":true}} -->
			<h3 class="nh-salon-our-approach__card-title">2. Hair Selection</h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"className":"nh-salon-our-approach__card-text","lock":{"move":true,"remove":true}} -->
			<p class="nh-salon-our-approach__card-text">From over 10,000 in-stock ponytails, we hand-pick the perfect match in length, color, and texture.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"nh-salon-our-approach__card nh-salon-our-approach__card--photo","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
		<div class="wp-block-group nh-salon-our-approach__card nh-salon-our-approach__card--photo">
			<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"nh-salon-our-approach__card-bg","lock":{"move":true,"remove":true}} -->
			<figure class="wp-block-image size-full nh-salon-our-approach__card-bg"><img src="<?php echo $approach_dir_uri; ?>/approach-3.jpg" alt="" /></figure>
			<!-- /wp:image -->

			<!-- wp:heading {"level":3,"className":"nh-salon-our-approach__card-title","lock":{"move":true,"remove":true}} -->
			<h3 class="nh-salon-our-approach__card-title">3. Application</h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"className":"nh-salon-our-approach__card-text","lock":{"move":true,"remove":true}} -->
			<p class="nh-salon-our-approach__card-text">Your stylist applies the extensions with precision using our gentle Italian method.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"nh-salon-our-approach__card nh-salon-our-approach__card--photo","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
		<div class="wp-block-group nh-salon-our-approach__card nh-salon-our-approach__card--photo">
			<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"nh-salon-our-approach__card-bg","lock":{"move":true,"remove":true}} -->
			<figure class="wp-block-image size-full nh-salon-our-approach__card-bg"><img src="<?php echo $approach_dir_uri; ?>/approach-4.jpg" alt="" /></figure>
			<!-- /wp:image -->

			<!-- wp:heading {"level":3,"className":"nh-salon-our-approach__card-title","lock":{"move":true,"remove":true}} -->
			<h3 class="nh-salon-our-approach__card-title">4. Aftercare</h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"className":"nh-salon-our-approach__card-text","lock":{"move":true,"remove":true}} -->
			<p class="nh-salon-our-approach__card-text">We guide you on maintenance and stay in touch for any questions or support.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

	</div>
	<!-- /wp:group -->

	<!-- wp:group {"className":"nh-salon-our-approach__guarantee","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
	<div class="wp-block-group nh-salon-our-approach__guarantee">
		<!-- wp:heading {"level":3,"className":"nh-salon-our-approach__guarantee-title","lock":{"move":true,"remove":true}} -->
		<h3 class="nh-salon-our-approach__guarantee-title">Quality Guarantee</h3>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"className":"nh-salon-our-approach__guarantee-text","lock":{"move":true,"remove":true}} -->
		<p class="nh-salon-our-approach__guarantee-text">Free corrections within 14 days if any issues arise with the work. Adding extra strands or making changes by request is not included in the guarantee.</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
