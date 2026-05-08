<?php
declare(strict_types=1);

get_header('home');
?>
<main class="nh-main nh-main--single-post">

	<?php while (have_posts()) : the_post(); ?>

		<?php get_template_part('template-parts/sections/blog/post-hero'); ?>

		<article class="nh-single-post">
			<div class="nh-single-post__shell">
				<?php the_content(); ?>
			</div>
		</article>

	<?php endwhile; ?>

	<?php
	// Related posts: same nh_post_type, exclude current post.
	$nh_terms = wp_get_post_terms(get_the_ID(), 'nh_post_type', ['fields' => 'slugs']);
	$nh_type  = !empty($nh_terms) ? $nh_terms[0] : 'salon';

	$nh_paged = max(1, (int) get_query_var('paged'));

	$nh_related = new WP_Query([
		'post_type'      => 'post',
		'post__not_in'   => [get_the_ID()],
		'posts_per_page' => get_option('posts_per_page'),
		'paged'          => $nh_paged,
		'tax_query'      => [[
			'taxonomy' => 'nh_post_type',
			'field'    => 'slug',
			'terms'    => $nh_type,
		]],
	]);
	?>

	<section class="nh-blog-grid">
		<div class="nh-blog-grid__shell">
			<?php if ($nh_related->have_posts()) : ?>
				<div class="nh-blog-grid__list">
					<?php while ($nh_related->have_posts()) : $nh_related->the_post(); ?>
						<?php get_template_part('template-parts/sections/blog/post-card'); ?>
					<?php endwhile; ?>
				</div>
			<?php else : ?>
				<p class="nh-blog-grid__empty"><?php esc_html_e('No related posts found.', 'nice-hair'); ?></p>
			<?php endif; ?>

			<?php
			$nh_total   = max(1, (int) $nh_related->max_num_pages);
			$nh_current = $nh_paged;

			$nh_links = paginate_links([
				'total'     => $nh_total,
				'current'   => $nh_current,
				'mid_size'  => 3,
				'prev_text' => '&lsaquo;',
				'next_text' => '&rsaquo;',
				'type'      => 'array',
			]);
			?>
			<nav class="navigation pagination nh-blog-grid__pagination" aria-label="<?php esc_attr_e('Related posts pagination', 'nice-hair'); ?>">
				<div class="nav-links">
					<?php if ($nh_current > 1) : ?>
						<a class="page-numbers first" href="<?php echo esc_url(get_pagenum_link(1)); ?>">&laquo;</a>
					<?php else : ?>
						<span class="page-numbers first">&laquo;</span>
					<?php endif; ?>

					<?php if ($nh_links) : ?>
						<?php foreach ($nh_links as $nh_link) { echo $nh_link; } ?>
					<?php else : ?>
						<span class="page-numbers current">1</span>
					<?php endif; ?>

					<?php if ($nh_current < $nh_total) : ?>
						<a class="page-numbers last" href="<?php echo esc_url(get_pagenum_link($nh_total)); ?>">&raquo;</a>
					<?php else : ?>
						<span class="page-numbers last">&raquo;</span>
					<?php endif; ?>
				</div>
			</nav>

			<?php wp_reset_postdata(); ?>
		</div>

		<?php get_template_part('template-parts/sections/blog/subscribe'); ?>
	</section>

	<?php
	$theme_dir = get_template_directory();
	ob_start();
	include $theme_dir . '/patterns/shared-contact.php';
	echo ob_get_clean();
	?>

</main>
<?php
get_footer('home');
