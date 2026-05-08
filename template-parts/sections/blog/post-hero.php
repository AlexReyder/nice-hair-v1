<?php
declare(strict_types=1);

$blog_page_id  = (int) get_option('page_for_posts');
$blog_page_url = $blog_page_id ? get_permalink($blog_page_id) : '/';
$contact_phone = nice_hair_get_contact_phone_display('home');
$contact_phone_link = nice_hair_get_contact_phone_link('home');
$contact_address = nice_hair_get_contact_address_plain('home');
?>
<section class="nh-post-hero">
	<div class="nh-post-hero__shell">
		<div class="nh-post-hero__left">
			<p class="nh-post-hero__breadcrumbs">
				<a href="<?php echo esc_url(home_url('/')); ?>">[Home]</a>
				<span class="nh-post-hero__breadcrumbs-sep">-</span>
				<a href="<?php echo esc_url($blog_page_url); ?>">[Blog]</a>
				<span class="nh-post-hero__breadcrumbs-sep">-</span>
				<span>[ <?php the_title(); ?> ]</span>
			</p>

			<h1 class="nh-post-hero__title">/ <?php the_title(); ?></h1>
		</div>

		<div class="nh-post-hero__right">
			<a href="tel:<?php echo esc_attr($contact_phone_link); ?>" class="nh-post-hero__phone"><?php echo esc_html($contact_phone); ?></a>
			<p class="nh-post-hero__address"><?php echo esc_html($contact_address); ?></p>
		</div>
	</div>
</section>
