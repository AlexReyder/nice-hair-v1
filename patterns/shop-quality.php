<?php
/**
 * Title: Nice Hair / Shop Quality
 * Slug: nice-hair/shop-quality
 * Categories: nice-hair-shop
 * Inserter: yes
 */

$quality_premium_url   = esc_url( get_template_directory_uri() . '/assets/images/quality/quality-premium.png' );
$quality_lux_url       = esc_url( get_template_directory_uri() . '/assets/images/quality/quality-lux.png' );
$quality_exclusive_url = esc_url( get_template_directory_uri() . '/assets/images/quality/quality-exclusive.png' );
?>

<!-- wp:group {"align":"full","className":"nh-shop-quality","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull nh-shop-quality">
	<!-- wp:group {"className":"nh-shop-quality__shell","layout":{"type":"default"}} -->
	<div class="wp-block-group nh-shop-quality__shell">
		<!-- wp:group {"className":"nh-shop-quality__main","layout":{"type":"default"}} -->
		<div class="wp-block-group nh-shop-quality__main">

			<!-- wp:group {"className":"nh-shop-quality__left","lock":{"move":true,"remove":true},"layout":{"type":"default"}} -->
			<div class="wp-block-group nh-shop-quality__left">
				<!-- wp:group {"className":"nh-shop-quality__intro","layout":{"type":"default"}} -->
				<div class="wp-block-group nh-shop-quality__intro">
					<!-- wp:paragraph {"className":"nh-shop-quality__eyebrow"} -->
					<p class="nh-shop-quality__eyebrow">[ QUALITY ]</p>
					<!-- /wp:paragraph -->

					<!-- wp:spacer {"height":"6px","className":"nh-shop-quality__divider"} -->
					<div style="height:6px" aria-hidden="true" class="wp-block-spacer nh-shop-quality__divider"></div>
					<!-- /wp:spacer -->

					<!-- wp:heading {"level":2,"className":"nh-shop-quality__title"} -->
					<h2 class="nh-shop-quality__title">Hair Quality: Origin and Categories</h2>
					<!-- /wp:heading -->
				</div>
				<!-- /wp:group -->

				<!-- wp:group {"className":"nh-shop-quality__content","layout":{"type":"default"}} -->
				<div class="wp-block-group nh-shop-quality__content">
					<!-- wp:heading {"level":3,"className":"nh-shop-quality__lead"} -->
					<h3 class="nh-shop-quality__lead">Where Our Hair Comes From</h3>
					<!-- /wp:heading -->

					<!-- wp:group {"className":"nh-shop-quality__body","layout":{"type":"default"}} -->
					<div class="wp-block-group nh-shop-quality__body">
						<!-- wp:paragraph {"className":"nh-shop-quality__body-text nh-shop-quality__body-text--primary"} -->
						<p class="nh-shop-quality__body-text nh-shop-quality__body-text--primary">We source raw hair from Russia, Ukraine, Belarus, Kazakhstan, and Uzbekistan. These regions are known for producing Slavic-type hair — naturally soft, smooth, and often light in color.</p>
						<!-- /wp:paragraph -->

						<!-- wp:paragraph {"className":"nh-shop-quality__body-text"} -->
						<p class="nh-shop-quality__body-text">All raw materials undergo strict quality control: structure check, elasticity testing, color consistency, and verification of natural growth direction.</p>
						<!-- /wp:paragraph -->
					</div>
					<!-- /wp:group -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"nh-shop-quality__right","lock":{"move":true,"remove":true},"layout":{"type":"default"}} -->
			<div class="wp-block-group nh-shop-quality__right">
				<!-- wp:group {"className":"nh-shop-quality__cards","layout":{"type":"default"}} -->
				<div class="wp-block-group nh-shop-quality__cards">

					<!-- wp:group {"className":"nh-shop-quality__card","layout":{"type":"default"}} -->
					<div class="wp-block-group nh-shop-quality__card">
						<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"nh-shop-quality__card-image"} -->
						<figure class="wp-block-image size-full nh-shop-quality__card-image">
							<img src="<?php echo $quality_premium_url; ?>" alt="Premium quality hair" />
						</figure>
						<!-- /wp:image -->

						<!-- wp:group {"className":"nh-shop-quality__card-info","layout":{"type":"default"}} -->
						<div class="wp-block-group nh-shop-quality__card-info">
							<!-- wp:heading {"level":3,"className":"nh-shop-quality__card-title"} -->
							<h3 class="nh-shop-quality__card-title">Premium</h3>
							<!-- /wp:heading -->

							<!-- wp:paragraph {"className":"nh-shop-quality__card-text"} -->
							<p class="nh-shop-quality__card-text">Single-donor bundles (no mix). Dyed using our exclusive technology while preserving the highest hair quality. Fine, smooth, and soft texture. The choice for the discerning and sophisticated client.</p>
							<!-- /wp:paragraph -->
						</div>
						<!-- /wp:group -->
					</div>
					<!-- /wp:group -->

					<!-- wp:group {"className":"nh-shop-quality__card","layout":{"type":"default"}} -->
					<div class="wp-block-group nh-shop-quality__card">
						<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"nh-shop-quality__card-image"} -->
						<figure class="wp-block-image size-full nh-shop-quality__card-image">
							<img src="<?php echo $quality_lux_url; ?>" alt="Lux quality hair" />
						</figure>
						<!-- /wp:image -->

						<!-- wp:group {"className":"nh-shop-quality__card-info","layout":{"type":"default"}} -->
						<div class="wp-block-group nh-shop-quality__card-info">
							<!-- wp:heading {"level":3,"className":"nh-shop-quality__card-title"} -->
							<h3 class="nh-shop-quality__card-title">Lux</h3>
							<!-- /wp:heading -->

							<!-- wp:paragraph {"className":"nh-shop-quality__card-text"} -->
							<p class="nh-shop-quality__card-text">Bulks are sourced from several donors (mix). Thick ends, dense tops. After washing – slightly wavy. Ideal for those who like maximum volume and thickness.</p>
							<!-- /wp:paragraph -->
						</div>
						<!-- /wp:group -->
					</div>
					<!-- /wp:group -->

					<!-- wp:group {"className":"nh-shop-quality__card","layout":{"type":"default"}} -->
					<div class="wp-block-group nh-shop-quality__card">
						<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"nh-shop-quality__card-image"} -->
						<figure class="wp-block-image size-full nh-shop-quality__card-image">
							<img src="<?php echo $quality_exclusive_url; ?>" alt="Exclusive quality hair" />
						</figure>
						<!-- /wp:image -->

						<!-- wp:group {"className":"nh-shop-quality__card-info","layout":{"type":"default"}} -->
						<div class="wp-block-group nh-shop-quality__card-info">
							<!-- wp:heading {"level":3,"className":"nh-shop-quality__card-title"} -->
							<h3 class="nh-shop-quality__card-title">Exclusive</h3>
							<!-- /wp:heading -->

							<!-- wp:paragraph {"className":"nh-shop-quality__card-text"} -->
							<p class="nh-shop-quality__card-text">Undyed, baby's super-selected hair. Each bundle is unique. Exceptionally soft, fine, and shiny. Texture varies from bone straight to bouncy curls. Ideal for anyone seeking a rare, natural texture.</p>
							<!-- /wp:paragraph -->
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
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
