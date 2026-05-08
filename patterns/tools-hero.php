<?php
/**
 * Title: Nice Hair / Tools Hero
 * Slug: nice-hair/tools-hero
 * Categories: nice-hair-shop
 * Inserter: yes
 */

$home_url = esc_url( home_url( '/' ) );
$shop_url = esc_url( get_permalink( wc_get_page_id( 'shop' ) ) );
$contact_phone = nice_hair_get_contact_phone_display('shop');
$contact_phone_link = nice_hair_get_contact_phone_link('shop');
$contact_address = nice_hair_get_contact_address_plain('shop');
?>

<!-- wp:group {"className":"nh-tools-hero","lock":{"move":true,"remove":true}} -->
<div class="wp-block-group nh-tools-hero">

	<!-- wp:group {"className":"nh-tools-hero__shell","lock":{"move":true,"remove":true}} -->
	<div class="wp-block-group nh-tools-hero__shell">

		<!-- wp:group {"className":"nh-tools-hero__top","lock":{"move":true,"remove":true}} -->
		<div class="wp-block-group nh-tools-hero__top">

			<!-- wp:paragraph {"className":"nh-tools-hero__breadcrumb","lock":{"move":true,"remove":true}} -->
			<p class="nh-tools-hero__breadcrumb"><a href="<?php echo $home_url; ?>">Home</a> — <a href="<?php echo $shop_url; ?>">Shop</a> — Tools and Accessories</p>
			<!-- /wp:paragraph -->

			<!-- wp:group {"className":"nh-tools-hero__contact","lock":{"move":true,"remove":true}} -->
			<div class="wp-block-group nh-tools-hero__contact">

				<!-- wp:paragraph {"className":"nh-tools-hero__phone","lock":{"move":true,"remove":false}} -->
				<p class="nh-tools-hero__phone"><a href="tel:<?php echo esc_attr( $contact_phone_link ); ?>"><?php echo esc_html( $contact_phone ); ?></a></p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph {"className":"nh-tools-hero__address","lock":{"move":true,"remove":false}} -->
				<p class="nh-tools-hero__address"><?php echo esc_html( $contact_address ); ?></p>
				<!-- /wp:paragraph -->

			</div>
			<!-- /wp:group -->

		</div>
		<!-- /wp:group -->

		<!-- wp:heading {"level":1,"className":"nh-tools-hero__title","lock":{"move":true,"remove":false}} -->
		<h1 class="wp-block-heading nh-tools-hero__title">/ Tools and Accessories<br>for Hair Extensions</h1>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"className":"nh-tools-hero__subtitle","lock":{"move":true,"remove":false}} -->
		<p class="nh-tools-hero__subtitle">Everything you need for precise, safe, and comfortable application — all in one place.</p>
		<!-- /wp:paragraph -->

	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
