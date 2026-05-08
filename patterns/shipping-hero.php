<?php
/**
 * Title: Nice Hair / Shipping Hero
 * Slug: nice-hair/shipping-hero
 * Categories: nice-hair-shipping
 * Inserter: yes
 */
?>

<?php
$contact_phone = nice_hair_get_contact_phone_display('shop');
$contact_phone_link = nice_hair_get_contact_phone_link('shop');
$contact_address = nice_hair_get_contact_address_display('shop');
?>

<!-- wp:group {"align":"full","style":{"color":{"background":"#0f0f10"}},"className":"nh-shipping-hero","lock":{"move":true,"remove":true}} -->
<div class="wp-block-group alignfull nh-shipping-hero has-background">

	<!-- wp:group {"className":"nh-shipping-hero__shell","lock":{"move":true,"remove":true}} -->
	<div class="wp-block-group nh-shipping-hero__shell">

		<!-- wp:group {"className":"nh-shipping-hero__top","lock":{"move":true,"remove":true}} -->
		<div class="wp-block-group nh-shipping-hero__top">

			<!-- wp:paragraph {"className":"nh-shipping-hero__breadcrumbs"} -->
			<p class="nh-shipping-hero__breadcrumbs"><a href="/hair/">[Home]</a> — <span>[International Shipping &amp; Easy Payment]</span></p>
			<!-- /wp:paragraph -->

			<!-- wp:group {"className":"nh-shipping-hero__contact","lock":{"move":true,"remove":true}} -->
			<div class="wp-block-group nh-shipping-hero__contact">
				<!-- wp:paragraph {"className":"nh-shipping-hero__phone"} -->
				<p class="nh-shipping-hero__phone"><a href="tel:<?php echo esc_attr( $contact_phone_link ); ?>"><?php echo esc_html( $contact_phone ); ?></a></p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph {"className":"nh-shipping-hero__address"} -->
				<p class="nh-shipping-hero__address"><?php echo wp_kses_post( $contact_address ); ?></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"nh-shipping-hero__content","lock":{"move":true,"remove":true}} -->
		<div class="wp-block-group nh-shipping-hero__content">

			<!-- wp:heading {"level":1,"className":"nh-shipping-hero__title"} -->
			<h1 class="nh-shipping-hero__title">/ International Shipping<br>&amp; Easy Payment</h1>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"className":"nh-shipping-hero__text"} -->
			<p class="nh-shipping-hero__text">We ship worldwide and help you choose the best delivery method.<br>The invoice is issued after all order details are confirmed.</p>
			<!-- /wp:paragraph -->

		</div>
		<!-- /wp:group -->

	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
