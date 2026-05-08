<?php
/**
 * Title: Nice Hair / Salon Bundle
 * Slug: nice-hair/salon-bundle
 * Categories: nice-hair-salon
 * Inserter: yes
 */

$bundle_image_url = esc_url( get_template_directory_uri() . '/assets/images/salon-bundle.jpg' );
?>

<!-- wp:group {"align":"full","className":"nh-salon-bundle","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
<div class="wp-block-group alignfull nh-salon-bundle">
	<!-- wp:group {"className":"nh-salon-bundle__shell","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
	<div class="wp-block-group nh-salon-bundle__shell">

		<!-- wp:cover {"url":"<?php echo $bundle_image_url; ?>","dimRatio":0,"isDark":false,"focalPoint":{"x":0.5,"y":0.5},"className":"nh-salon-bundle__card","lock":{"move":true,"remove":true}} -->
		<div class="wp-block-cover is-light nh-salon-bundle__card">
			<span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span>
			<img class="wp-block-cover__image-background" alt="" src="<?php echo $bundle_image_url; ?>" style="object-position:50% 50%" data-object-fit="cover" data-object-position="50% 50%" />
			<div class="wp-block-cover__inner-container">

				<!-- wp:group {"className":"nh-salon-bundle__content","layout":{"type":"default"},"lock":{"move":true,"remove":true}} -->
				<div class="wp-block-group nh-salon-bundle__content">

					<!-- wp:paragraph {"className":"nh-salon-bundle__description"} -->
					<p class="nh-salon-bundle__description">Each set is hand-selected to match your color, hair type, and desired result.</p>
					<!-- /wp:paragraph -->

					<!-- wp:heading {"level":2,"className":"nh-salon-bundle__title"} -->
					<h2 class="nh-salon-bundle__title">Over 10,000 bundles in stock — in every length, texture, and shade.</h2>
					<!-- /wp:heading -->

				</div>
				<!-- /wp:group -->

			</div>
		</div>
		<!-- /wp:cover -->

	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
