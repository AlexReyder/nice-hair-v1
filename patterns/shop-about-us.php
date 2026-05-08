<?php
/**
 * Title: Nice Hair / Shop About Us
 * Slug: nice-hair/shop-about-us
 * Categories: nice-hair-shop
 * Inserter: yes
 */

$about_image_1_url = esc_url( get_template_directory_uri() . '/assets/images/about-1.jpg' );
$about_image_2_url = esc_url( get_template_directory_uri() . '/assets/images/about-2.jpg' );
$about_image_3_url = esc_url( get_template_directory_uri() . '/assets/images/about-3.jpg' );
?>

<!-- wp:group {"align":"full","className":"nh-shop-about-us","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull nh-shop-about-us">
	<!-- wp:group {"className":"nh-shop-about-us__shell","layout":{"type":"default"}} -->
	<div class="wp-block-group nh-shop-about-us__shell">
		<!-- wp:group {"className":"nh-shop-about-us__intro","layout":{"type":"default"}} -->
		<div class="wp-block-group nh-shop-about-us__intro">
			<!-- wp:paragraph {"className":"nh-shop-about-us__eyebrow"} -->
			<p class="nh-shop-about-us__eyebrow">[ ABOUT US ]</p>
			<!-- /wp:paragraph -->

			<!-- wp:spacer {"height":"6px","className":"nh-shop-about-us__divider"} -->
			<div style="height:6px" aria-hidden="true" class="wp-block-spacer nh-shop-about-us__divider"></div>
			<!-- /wp:spacer -->

			<!-- wp:heading {"level":2,"className":"nh-shop-about-us__title"} -->
			<h2 class="nh-shop-about-us__title">Hair Quality: Origin and Categories</h2>
			<!-- /wp:heading -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"nh-shop-about-us__main","layout":{"type":"default"}} -->
		<div class="wp-block-group nh-shop-about-us__main">
			<!-- wp:group {"className":"nh-shop-about-us__content","layout":{"type":"default"}} -->
			<div class="wp-block-group nh-shop-about-us__content">
				<!-- wp:paragraph {"className":"nh-shop-about-us__lead"} -->
				<p class="nh-shop-about-us__lead">Our Approach</p>
				<!-- /wp:paragraph -->

				<!-- wp:group {"className":"nh-shop-about-us__body","layout":{"type":"default"}} -->
				<div class="wp-block-group nh-shop-about-us__body">
					<!-- wp:paragraph {"className":"nh-shop-about-us__body-text nh-shop-about-us__body-text--primary"} -->
					<p class="nh-shop-about-us__body-text nh-shop-about-us__body-text--primary">We offer a wide range of hair extensions to meet the needs of professionals and clients alike. Our in-house production allows us to control every stage, from raw hair selection to the final result. This level of control ensures consistent quality, precision, and reliability.</p>
					<!-- /wp:paragraph -->

					<!-- wp:paragraph {"className":"nh-shop-about-us__body-text"} -->
					<p class="nh-shop-about-us__body-text">We don't adapt to standards — we create them. That's why our hair performs exactly as expected.</p>
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"nh-shop-about-us__stats","layout":{"type":"default"}} -->
			<div class="wp-block-group nh-shop-about-us__stats">
				<!-- wp:group {"className":"nh-shop-about-us__stat","layout":{"type":"default"}} -->
				<div class="wp-block-group nh-shop-about-us__stat">
					<!-- wp:heading {"level":3,"className":"nh-shop-about-us__stat-title"} -->
					<h3 class="nh-shop-about-us__stat-title">Full production cycle</h3>
					<!-- /wp:heading -->

					<!-- wp:paragraph {"className":"nh-shop-about-us__stat-text"} -->
					<p class="nh-shop-about-us__stat-text">From sourcing raw hair to creating finished products — full control at every stage.</p>
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:group -->

				<!-- wp:group {"className":"nh-shop-about-us__stat","layout":{"type":"default"}} -->
				<div class="wp-block-group nh-shop-about-us__stat">
					<!-- wp:heading {"level":3,"className":"nh-shop-about-us__stat-title"} -->
					<h3 class="nh-shop-about-us__stat-title">100% Slavic premium hair</h3>
					<!-- /wp:heading -->

					<!-- wp:paragraph {"className":"nh-shop-about-us__stat-text"} -->
					<p class="nh-shop-about-us__stat-text">We work only with high-quality Slavic raw hair known for its softness and durability.</p>
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:group -->

				<!-- wp:group {"className":"nh-shop-about-us__stat","layout":{"type":"default"}} -->
				<div class="wp-block-group nh-shop-about-us__stat">
					<!-- wp:heading {"level":3,"className":"nh-shop-about-us__stat-title"} -->
					<h3 class="nh-shop-about-us__stat-title">10,000+ bundles<br>& 50+ shades</h3>
					<!-- /wp:heading -->

					<!-- wp:paragraph {"className":"nh-shop-about-us__stat-text"} -->
					<p class="nh-shop-about-us__stat-text">A wide assortment always in stock — ready for immediate shipping.</p>
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:group -->

				<!-- wp:group {"className":"nh-shop-about-us__stat","layout":{"type":"default"}} -->
				<div class="wp-block-group nh-shop-about-us__stat">
					<!-- wp:heading {"level":3,"className":"nh-shop-about-us__stat-title"} -->
					<h3 class="nh-shop-about-us__stat-title">Fast international delivery</h3>
					<!-- /wp:heading -->

					<!-- wp:paragraph {"className":"nh-shop-about-us__stat-text"} -->
					<p class="nh-shop-about-us__stat-text">Worldwide shipping with reliable logistics partners.</p>
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"nh-shop-about-us__bottom","layout":{"type":"default"}} -->
		<div class="wp-block-group nh-shop-about-us__bottom">
			<!-- wp:group {"className":"nh-shop-about-us__gallery","templateLock":"all","layout":{"type":"default"}} -->
			<div class="wp-block-group nh-shop-about-us__gallery">
				<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"nh-shop-about-us__gallery-image"} -->
				<figure class="wp-block-image size-full nh-shop-about-us__gallery-image">
					<img src="<?php echo $about_image_1_url; ?>" alt="Hair product detail one" />
				</figure>
				<!-- /wp:image -->

				<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"nh-shop-about-us__gallery-image"} -->
				<figure class="wp-block-image size-full nh-shop-about-us__gallery-image">
					<img src="<?php echo $about_image_2_url; ?>" alt="Hair product detail two" />
				</figure>
				<!-- /wp:image -->

				<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"nh-shop-about-us__gallery-image"} -->
				<figure class="wp-block-image size-full nh-shop-about-us__gallery-image">
					<img src="<?php echo $about_image_3_url; ?>" alt="Hair product detail three" />
				</figure>
				<!-- /wp:image -->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"nh-shop-about-us__aside","layout":{"type":"default"}} -->
			<div class="wp-block-group nh-shop-about-us__aside">
				<!-- wp:paragraph {"className":"nh-shop-about-us__aside-eyebrow"} -->
				<p class="nh-shop-about-us__aside-eyebrow">[ ABOUT US ]</p>
				<!-- /wp:paragraph -->

				<!-- wp:spacer {"height":"6px","className":"nh-shop-about-us__aside-divider"} -->
				<div style="height:6px" aria-hidden="true" class="wp-block-spacer nh-shop-about-us__aside-divider"></div>
				<!-- /wp:spacer -->

				<!-- wp:heading {"level":3,"className":"nh-shop-about-us__aside-title"} -->
				<h3 class="nh-shop-about-us__aside-title">Own production</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"className":"nh-shop-about-us__aside-text"} -->
				<p class="nh-shop-about-us__aside-text">We operate our own production facility, where we fully control every stage of working with hair. Each hair extension is handcrafted, with great attention to detail and consistent results.</p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph {"className":"nh-text-link nh-shop-about-us__aside-link"} -->
				<p class="nh-text-link nh-shop-about-us__aside-link"><a href="#">[ LEARN MORE ]</a></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
