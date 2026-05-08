<?php
/**
 * Title: Nice Hair / Home One Brand
 * Slug: nice-hair/home-one-brand
 * Categories: nice-hair-home
 * Inserter: yes
 */

$salon_image_url = esc_url( get_template_directory_uri() . '/assets/images/one-brand-first.jpg' );
$shop_image_url  = esc_url( get_template_directory_uri() . '/assets/images/one-brand-second.jpg' );
?>

<!-- wp:group {"align":"full","className":"nh-home-one-brand","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
<div class="wp-block-group alignfull nh-home-one-brand">
	<!-- wp:group {"className":"nh-home-one-brand__shell","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
	<div class="wp-block-group nh-home-one-brand__shell">
		<!-- wp:group {"className":"nh-home-one-brand__intro","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
		<div class="wp-block-group nh-home-one-brand__intro">
			<!-- wp:paragraph {"className":"nh-home-one-brand__eyebrow","lock":{"move":true,"remove":true}} -->
			<p class="nh-home-one-brand__eyebrow">[ ONE BRAND ]</p>
			<!-- /wp:paragraph -->

			<!-- wp:spacer {"height":"6px","className":"nh-home-one-brand__divider","lock":{"move":true,"remove":true}} -->
			<div style="height:6px" aria-hidden="true" class="wp-block-spacer nh-home-one-brand__divider"></div>
			<!-- /wp:spacer -->

			<!-- wp:heading {"level":2,"className":"nh-home-one-brand__title","lock":{"move":true,"remove":true}} -->
			<h2 class="nh-home-one-brand__title">Two directions — one standard of quality</h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"className":"nh-home-one-brand__text","lock":{"move":true,"remove":true}} -->
			<p class="nh-home-one-brand__text">Choose what you need today: professional salon services or premium hair products — one brand, one production, one standard of quality. Whether you’re here for expert care or for the finest raw hair, you’ll get the same level of precision and trust.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"nh-home-one-brand__cards","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
		<div class="wp-block-group nh-home-one-brand__cards">
			<!-- wp:group {"className":"nh-home-one-brand__card","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
			<div class="wp-block-group nh-home-one-brand__card">
				<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"nh-home-one-brand__image","lock":{"move":true,"remove":true}} -->
				<figure class="wp-block-image size-full nh-home-one-brand__image">
					<img src="<?php echo $salon_image_url; ?>" alt="Hair Extensions salon" />
				</figure>
				<!-- /wp:image -->

				<!-- wp:group {"className":"nh-home-one-brand__card-body","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
				<div class="wp-block-group nh-home-one-brand__card-body">
					<!-- wp:heading {"level":3,"className":"nh-home-one-brand__card-title","lock":{"move":true,"remove":true}} -->
					<h3 class="nh-home-one-brand__card-title">Hair Extensions salon</h3>
					<!-- /wp:heading -->

					<!-- wp:paragraph {"className":"nh-home-one-brand__card-text","lock":{"move":true,"remove":true}} -->
					<p class="nh-home-one-brand__card-text">Short description: professional extensions, 1:1 stylist work, premium materials.</p>
					<!-- /wp:paragraph -->

					<!-- wp:buttons {"className":"nh-home-one-brand__card-actions","lock":{"move":true,"remove":true}} -->
					<div class="wp-block-buttons nh-home-one-brand__card-actions">
						<!-- wp:button {"className":"nh-cta-link","lock":{"move":true,"remove":true}} -->
						<div class="wp-block-button nh-cta-link"><a class="wp-block-button__link wp-element-button" href="#">VISIT SALON</a></div>
						<!-- /wp:button -->
					</div>
					<!-- /wp:buttons -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"nh-home-one-brand__card","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
			<div class="wp-block-group nh-home-one-brand__card">
				<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"nh-home-one-brand__image","lock":{"move":true,"remove":true}} -->
				<figure class="wp-block-image size-full nh-home-one-brand__image">
					<img src="<?php echo $shop_image_url; ?>" alt="Online Shop" />
				</figure>
				<!-- /wp:image -->

				<!-- wp:group {"className":"nh-home-one-brand__card-body","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
				<div class="wp-block-group nh-home-one-brand__card-body">
					<!-- wp:heading {"level":3,"className":"nh-home-one-brand__card-title","lock":{"move":true,"remove":true}} -->
					<h3 class="nh-home-one-brand__card-title">Online Shop</h3>
					<!-- /wp:heading -->

					<!-- wp:paragraph {"className":"nh-home-one-brand__card-text","lock":{"move":true,"remove":true}} -->
					<p class="nh-home-one-brand__card-text">Short description: raw hair, handcrafted products, tools, and strict quality control.</p>
					<!-- /wp:paragraph -->

					<!-- wp:buttons {"className":"nh-home-one-brand__card-actions","lock":{"move":true,"remove":true}} -->
					<div class="wp-block-buttons nh-home-one-brand__card-actions">
						<!-- wp:button {"className":"nh-cta-link","lock":{"move":true,"remove":true}} -->
						<div class="wp-block-button nh-cta-link"><a class="wp-block-button__link wp-element-button" href="#">VISIT SHOP</a></div>
						<!-- /wp:button -->
					</div>
					<!-- /wp:buttons -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->