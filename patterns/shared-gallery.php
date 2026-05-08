<?php
/**
 * Title: Nice Hair / Gallery
 * Slug: nice-hair/shared-gallery
 * Categories: nice-hair-shared
 * Inserter: yes
 */

$gallery_base = esc_url( get_template_directory_uri() . '/assets/images/gallery' );
$instagram_url = nice_hair_get_contact_social_url('instagram', 'home', 'https://www.instagram.com/nice_hair_dxb/');
?>

<!-- wp:group {"tagName":"section","className":"nh-gallery","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
<section class="wp-block-group nh-gallery">
	<!-- wp:group {"className":"nh-gallery__shell","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
	<div class="wp-block-group nh-gallery__shell">
		<!-- wp:group {"className":"nh-gallery__header","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
		<div class="wp-block-group nh-gallery__header">
			<!-- wp:paragraph {"className":"nh-gallery__eyebrow","lock":{"move":true,"remove":true}} -->
			<p class="nh-gallery__eyebrow">[ GALLERY ]</p>
			<!-- /wp:paragraph -->

			<!-- wp:spacer {"height":"6px","className":"nh-gallery__divider","lock":{"move":true,"remove":true}} -->
			<div style="height:6px" aria-hidden="true" class="wp-block-spacer nh-gallery__divider"></div>
			<!-- /wp:spacer -->

			<!-- wp:heading {"level":2,"className":"nh-gallery__title","lock":{"move":true,"remove":true}} -->
			<h2 class="nh-gallery__title">What we've been doing every day for 14 years</h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"className":"nh-gallery__subtitle","lock":{"move":true,"remove":true}} -->
			<p class="nh-gallery__subtitle">From raw hair to flawless results.<br>Every piece you see is the result of expert color matching,<br>custom manufacturing, and years of hands-on experience.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"nh-gallery__grid","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
		<div class="wp-block-group nh-gallery__grid">
			<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"nh-gallery__item nh-gallery__item--s1","lock":{"move":true,"remove":true}} -->
			<figure class="wp-block-image size-full nh-gallery__item nh-gallery__item--s1">
				<img src="<?php echo $gallery_base; ?>/gallery-item-small-1.jpg" alt="Gallery image 1" />
			</figure>
			<!-- /wp:image -->

			<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"nh-gallery__item nh-gallery__item--s2","lock":{"move":true,"remove":true}} -->
			<figure class="wp-block-image size-full nh-gallery__item nh-gallery__item--s2">
				<img src="<?php echo $gallery_base; ?>/gallery-item-small-2.jpg" alt="Gallery image 2" />
			</figure>
			<!-- /wp:image -->

			<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"nh-gallery__item nh-gallery__item--s3","lock":{"move":true,"remove":true}} -->
			<figure class="wp-block-image size-full nh-gallery__item nh-gallery__item--s3">
				<img src="<?php echo $gallery_base; ?>/gallery-item-small-3.jpg" alt="Gallery image 3" />
			</figure>
			<!-- /wp:image -->

			<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"nh-gallery__item nh-gallery__item--s4","lock":{"move":true,"remove":true}} -->
			<figure class="wp-block-image size-full nh-gallery__item nh-gallery__item--s4">
				<img src="<?php echo $gallery_base; ?>/gallery-item-small-4.jpg" alt="Gallery image 4" />
			</figure>
			<!-- /wp:image -->

			<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"nh-gallery__item nh-gallery__item--s5","lock":{"move":true,"remove":true}} -->
			<figure class="wp-block-image size-full nh-gallery__item nh-gallery__item--s5">
				<img src="<?php echo $gallery_base; ?>/gallery-item-small-5.jpg" alt="Gallery image 5" />
			</figure>
			<!-- /wp:image -->

			<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"nh-gallery__item nh-gallery__item--s6","lock":{"move":true,"remove":true}} -->
			<figure class="wp-block-image size-full nh-gallery__item nh-gallery__item--s6">
				<img src="<?php echo $gallery_base; ?>/gallery-item-small-6.jpg" alt="Gallery image 6" />
			</figure>
			<!-- /wp:image -->

			<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"nh-gallery__item nh-gallery__item--big","lock":{"move":true,"remove":true}} -->
			<figure class="wp-block-image size-full nh-gallery__item nh-gallery__item--big">
				<img src="<?php echo $gallery_base; ?>/gallery-item-big.jpg" alt="Gallery featured image" />
			</figure>
			<!-- /wp:image -->

			<!-- wp:group {"className":"nh-gallery__instagram","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
			<div class="wp-block-group nh-gallery__instagram">
				<!-- wp:heading {"level":3,"className":"nh-gallery__instagram-title","lock":{"move":true,"remove":true}} -->
				<h3 class="nh-gallery__instagram-title">More looks on our Instagram</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"className":"nh-gallery__instagram-link","lock":{"move":true,"remove":true}} -->
				<p class="nh-gallery__instagram-link">Instagram <a href="<?php echo esc_url( $instagram_url ); ?>">[ @nice_hair_dxb ]</a></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

			<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"nh-gallery__item nh-gallery__item--s7","lock":{"move":true,"remove":true}} -->
			<figure class="wp-block-image size-full nh-gallery__item nh-gallery__item--s7">
				<img src="<?php echo $gallery_base; ?>/gallery-item-small-7.jpg" alt="Gallery image 7" />
			</figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</section>
<!-- /wp:group -->
