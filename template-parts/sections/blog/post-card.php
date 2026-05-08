<?php
declare(strict_types=1);
?>
<article <?php post_class('nh-post-card'); ?>>
	<?php if (has_post_thumbnail()) : ?>
		<a href="<?php the_permalink(); ?>" class="nh-post-card__image-link" aria-hidden="true" tabindex="-1">
			<?php the_post_thumbnail('large', ['class' => 'nh-post-card__image']); ?>
		</a>
	<?php endif; ?>

	<div class="nh-post-card__body">
		<time class="nh-post-card__meta" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
			<?php echo esc_html(date('l, F j', get_post_timestamp())); ?>
		</time>

		<h3 class="nh-post-card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>

		<div class="nh-post-card__excerpt">
			<?php the_excerpt(); ?>
		</div>

		<p class="nh-text-link nh-post-card__more">
			<a href="<?php the_permalink(); ?>">[ MORE ]</a>
		</p>
	</div>
</article>
