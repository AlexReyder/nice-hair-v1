<?php
/**
 * Title: Nice Hair / Shipping Payment
 * Slug: nice-hair/shipping-payment
 * Categories: nice-hair-shipping
 * Inserter: yes
 */

$shipping_image_url = esc_url( get_template_directory_uri() . '/assets/images/shipping-image.png' );
?>

<!-- wp:group {"align":"full","className":"nh-shipping-payment","lock":{"move":true,"remove":true}} -->
<div class="wp-block-group alignfull nh-shipping-payment">

	<!-- wp:group {"className":"nh-shipping-payment__shell","lock":{"move":true,"remove":true}} -->
	<div class="wp-block-group nh-shipping-payment__shell">

		<!-- wp:group {"className":"nh-shipping-payment__intro","lock":{"move":true,"remove":true}} -->
		<div class="wp-block-group nh-shipping-payment__intro">

			<!-- wp:paragraph {"className":"nh-shipping-payment__eyebrow"} -->
			<p class="nh-shipping-payment__eyebrow">[ PAYMENT ]</p>
			<!-- /wp:paragraph -->

			<!-- wp:spacer {"height":"6px","className":"nh-shipping-payment__divider"} -->
			<div style="height:6px" aria-hidden="true" class="wp-block-spacer nh-shipping-payment__divider"></div>
			<!-- /wp:spacer -->

			<!-- wp:heading {"level":2,"className":"nh-shipping-payment__title"} -->
			<h2 class="nh-shipping-payment__title">Payment</h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"className":"nh-shipping-payment__text"} -->
			<p class="nh-shipping-payment__text">All payments are processed after we confirm every detail of your order. Since many products are custom-made, the final cost is calculated individually.</p>
			<!-- /wp:paragraph -->

		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"nh-shipping-payment__cards","lock":{"move":true,"remove":true}} -->
		<div class="wp-block-group nh-shipping-payment__cards">

			<!-- wp:group {"className":"nh-shipping-payment__card nh-shipping-payment__card--procedure","lock":{"move":true,"remove":true}} -->
			<div class="wp-block-group nh-shipping-payment__card nh-shipping-payment__card--procedure">
				<!-- wp:heading {"level":3,"className":"nh-shipping-payment__card-title"} -->
				<h3 class="nh-shipping-payment__card-title">Payment Procedure</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"className":"nh-shipping-payment__card-text"} -->
				<p class="nh-shipping-payment__card-text">You place an order request on the website.</p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph {"className":"nh-shipping-payment__card-text"} -->
				<p class="nh-shipping-payment__card-text">We clarify all details and confirm the final configuration.</p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph {"className":"nh-shipping-payment__card-text"} -->
				<p class="nh-shipping-payment__card-text">You receive an invoice with the exact total and payment link.</p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph {"className":"nh-shipping-payment__card-text"} -->
				<p class="nh-shipping-payment__card-text">Once payment is completed, production and shipping begin.</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"nh-shipping-payment__card nh-shipping-payment__card--cost","lock":{"move":true,"remove":true}} -->
			<div class="wp-block-group nh-shipping-payment__card nh-shipping-payment__card--cost">
				<!-- wp:heading {"level":3,"className":"nh-shipping-payment__card-title"} -->
				<h3 class="nh-shipping-payment__card-title">Since many products are custom-made, the final cost depends on:</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"className":"nh-shipping-payment__card-text"} -->
				<p class="nh-shipping-payment__card-text">Hair type and category,</p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph {"className":"nh-shipping-payment__card-text"} -->
				<p class="nh-shipping-payment__card-text">Product type (weft, bonds, tapes, etc.),</p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph {"className":"nh-shipping-payment__card-text"} -->
				<p class="nh-shipping-payment__card-text">Length, weight, and additional customization.</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"nh-shipping-payment__card nh-shipping-payment__card--methods","lock":{"move":true,"remove":true}} -->
			<div class="wp-block-group nh-shipping-payment__card nh-shipping-payment__card--methods">
				<!-- wp:heading {"level":3,"className":"nh-shipping-payment__card-title"} -->
				<h3 class="nh-shipping-payment__card-title">Payment Methods</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"className":"nh-shipping-payment__card-text"} -->
				<p class="nh-shipping-payment__card-text">Bank transfer</p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph {"className":"nh-shipping-payment__card-text"} -->
				<p class="nh-shipping-payment__card-text">Online payment link</p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph {"className":"nh-shipping-payment__card-text"} -->
				<p class="nh-shipping-payment__card-text">UAE local payment methods (optional)</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"nh-shipping-payment__card nh-shipping-payment__card--image","lock":{"move":true,"remove":true}} -->
			<div class="wp-block-group nh-shipping-payment__card nh-shipping-payment__card--image">
				<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"nh-shipping-payment__card-image"} -->
				<figure class="wp-block-image size-full nh-shipping-payment__card-image"><img src="<?php echo $shipping_image_url; ?>" alt="Payment" /></figure>
				<!-- /wp:image -->
			</div>
			<!-- /wp:group -->

		</div>
		<!-- /wp:group -->

	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
