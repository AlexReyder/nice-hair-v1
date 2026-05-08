<?php
declare(strict_types=1);

get_header('home');
?>
<main class="nh-main nh-main--blog-index">

	<?php
	// Render the Blog page content (hero pattern) from the posts page.
	$blog_page_id = (int) get_option('page_for_posts');
	if ($blog_page_id) {
		$blog_page = get_post($blog_page_id);
		if ($blog_page && $blog_page->post_content) {
			echo apply_filters('the_content', $blog_page->post_content);
		}
	}
	?>

	<?php $nh_type = nice_hair_get_blog_type(); ?>
	<section class="nh-blog-grid">
		<div class="nh-blog-grid__shell">
			<div class="nh-blog-filter">
				<a href="<?php echo esc_url(add_query_arg('type', 'salon', get_permalink(get_option('page_for_posts')))); ?>"
				   class="nh-blog-filter__btn nh-blog-filter__btn--salon<?php echo $nh_type === 'salon' ? ' nh-blog-filter__btn--active' : ''; ?>">For Clients</a>
				<a href="<?php echo esc_url(add_query_arg('type', 'shop', get_permalink(get_option('page_for_posts')))); ?>"
				   class="nh-blog-filter__btn nh-blog-filter__btn--shop<?php echo $nh_type === 'shop' ? ' nh-blog-filter__btn--active' : ''; ?>">For specialists</a>
			</div>

			<?php if (have_posts()) : ?>
				<div class="nh-blog-grid__list">
					<?php while (have_posts()) : the_post(); ?>
						<?php get_template_part('template-parts/sections/blog/post-card'); ?>
					<?php endwhile; ?>
				</div>

				<?php
				global $wp_query;
				$nh_total   = max(1, (int) $wp_query->max_num_pages);
				$nh_current = max(1, get_query_var('paged'));

				$nh_links = paginate_links([
					'total'     => $nh_total,
					'current'   => $nh_current,
					'mid_size'  => 3,
					'prev_text' => '&lsaquo;',
					'next_text' => '&rsaquo;',
					'type'      => 'array',
					'add_args'  => ['type' => $nh_type],
				]);
				?>
				<nav class="navigation pagination nh-blog-grid__pagination" aria-label="<?php esc_attr_e('Blog pagination', 'nice-hair'); ?>">
					<div class="nav-links">
						<?php if ($nh_current > 1) : ?>
							<a class="page-numbers first" href="<?php echo esc_url(add_query_arg('type', $nh_type, get_pagenum_link(1))); ?>">&laquo;</a>
						<?php else : ?>
							<span class="page-numbers first">&laquo;</span>
						<?php endif; ?>

						<?php if ($nh_links) : ?>
							<?php foreach ($nh_links as $nh_link) { echo $nh_link; } ?>
						<?php else : ?>
							<span class="page-numbers current">1</span>
						<?php endif; ?>

						<?php if ($nh_current < $nh_total) : ?>
							<a class="page-numbers last" href="<?php echo esc_url(add_query_arg('type', $nh_type, get_pagenum_link($nh_total))); ?>">&raquo;</a>
						<?php else : ?>
							<span class="page-numbers last">&raquo;</span>
						<?php endif; ?>
					</div>
				</nav>
			<?php else : ?>
				<p class="nh-blog-grid__empty"><?php esc_html_e('No posts found.', 'nice-hair'); ?></p>
			<?php endif; ?>
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
