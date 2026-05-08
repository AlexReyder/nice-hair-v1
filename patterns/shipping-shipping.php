<?php
/**
 * Title: Nice Hair / Shipping Shipping
 * Slug: nice-hair/shipping-shipping
 * Categories: nice-hair-shipping
 * Inserter: yes
 */

$shipping_image_url = esc_url( get_template_directory_uri() . '/assets/images/shipping-image.png' );
?>

<!-- wp:group {"align":"full","className":"nh-shipping-shipping","lock":{"move":true,"remove":true}} -->
<div class="wp-block-group alignfull nh-shipping-shipping">

	<!-- wp:group {"className":"nh-shipping-shipping__shell","lock":{"move":true,"remove":true}} -->
	<div class="wp-block-group nh-shipping-shipping__shell">

		<!-- wp:group {"className":"nh-shipping-shipping__intro","lock":{"move":true,"remove":true}} -->
		<div class="wp-block-group nh-shipping-shipping__intro">

			<!-- wp:paragraph {"className":"nh-shipping-shipping__eyebrow"} -->
			<p class="nh-shipping-shipping__eyebrow">[ SHIPPING ]</p>
			<!-- /wp:paragraph -->

			<!-- wp:spacer {"height":"6px","className":"nh-shipping-shipping__divider"} -->
			<div style="height:6px" aria-hidden="true" class="wp-block-spacer nh-shipping-shipping__divider"></div>
			<!-- /wp:spacer -->

			<!-- wp:heading {"level":2,"className":"nh-shipping-shipping__title"} -->
			<h2 class="nh-shipping-shipping__title">Shipping</h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"className":"nh-shipping-shipping__text"} -->
			<p class="nh-shipping-shipping__text">We deliver orders worldwide using reliable international carriers. Every order is carefully inspected and securely packaged before dispatch</p>
			<!-- /wp:paragraph -->

		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"nh-shipping-shipping__cards","lock":{"move":true,"remove":true}} -->
		<div class="wp-block-group nh-shipping-shipping__cards">

			<!-- wp:group {"className":"nh-shipping-shipping__card nh-shipping-shipping__card--how-we-ship","lock":{"move":true,"remove":true}} -->
			<div class="wp-block-group nh-shipping-shipping__card nh-shipping-shipping__card--how-we-ship">
				<!-- wp:heading {"level":3,"className":"nh-shipping-shipping__card-title"} -->
				<h3 class="nh-shipping-shipping__card-title">How We Ship</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"className":"nh-shipping-shipping__card-text"} -->
				<p class="nh-shipping-shipping__card-text"><strong>Worldwide shipping</strong><br>DHL/EMX — depending on your country.</p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph {"className":"nh-shipping-shipping__card-text"} -->
				<p class="nh-shipping-shipping__card-text"><strong>Shipping within the UAE</strong><br>Local courier delivery to your door.</p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph {"className":"nh-shipping-shipping__card-text"} -->
				<p class="nh-shipping-shipping__card-text"><strong>Shipping to Russia, Ukraine, Kazakhstan, Belarus, Uzbekistan</strong><br>Local delivery partners (confirmed individually).</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"nh-shipping-shipping__card nh-shipping-shipping__card--shipping-cost","lock":{"move":true,"remove":true}} -->
			<div class="wp-block-group nh-shipping-shipping__card nh-shipping-shipping__card--shipping-cost">
				<!-- wp:heading {"level":3,"className":"nh-shipping-shipping__card-title"} -->
				<h3 class="nh-shipping-shipping__card-title">Shipping Cost</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"className":"nh-shipping-shipping__card-text"} -->
				<p class="nh-shipping-shipping__card-text">A flat shipping fee of $20 USD will apply. There are no limits on weight order, shipping fee is fixed.</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"nh-shipping-shipping__card nh-shipping-shipping__card--order-tracking","lock":{"move":true,"remove":true}} -->
			<div class="wp-block-group nh-shipping-shipping__card nh-shipping-shipping__card--order-tracking">
				<!-- wp:heading {"level":3,"className":"nh-shipping-shipping__card-title"} -->
				<h3 class="nh-shipping-shipping__card-title">Order Tracking</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"className":"nh-shipping-shipping__card-text"} -->
				<p class="nh-shipping-shipping__card-text">Once shipped, you will receive a tracking number to follow your delivery online.</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"nh-shipping-shipping__card nh-shipping-shipping__card--delivery-timeframes","lock":{"move":true,"remove":true}} -->
			<div class="wp-block-group nh-shipping-shipping__card nh-shipping-shipping__card--delivery-timeframes">
				<!-- wp:heading {"level":3,"className":"nh-shipping-shipping__card-title"} -->
				<h3 class="nh-shipping-shipping__card-title">Delivery Timeframes</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"className":"nh-shipping-shipping__card-text"} -->
				<p class="nh-shipping-shipping__card-text"><strong>2-10 days</strong><br>Timeframes depend on your country and logistics in your region</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"nh-shipping-shipping__card nh-shipping-shipping__card--image","lock":{"move":true,"remove":true}} -->
			<div class="wp-block-group nh-shipping-shipping__card nh-shipping-shipping__card--image">
				<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"nh-shipping-shipping__card-image"} -->
				<figure class="wp-block-image size-full nh-shipping-shipping__card-image"><img src="<?php echo $shipping_image_url; ?>" alt="Shipping" /></figure>
				<!-- /wp:image -->
			</div>
			<!-- /wp:group -->

		</div>
		<!-- /wp:group -->

	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
