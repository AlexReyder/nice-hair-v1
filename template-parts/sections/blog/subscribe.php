<?php
declare(strict_types=1);
?>
<div class="nh-subscribe">
	<div class="nh-subscribe__shell">
		<h2 class="nh-subscribe__title">SUBSCRIBE TO BLOG</h2>
		<p class="nh-subscribe__text">Email</p>

		<form class="nh-subscribe__form" data-subscribe-form>
			<input type="text" name="hp_field" class="nh-subscribe__hp" autocomplete="off" tabindex="-1" aria-hidden="true">
			<input type="hidden" name="source" value="blog">
			<div class="nh-subscribe__field">
				<input
					type="email"
					name="email"
					class="nh-subscribe__input"
					placeholder="Your email"
					required
					autocomplete="email"
				>
				<button type="submit" class="nh-subscribe__submit" aria-label="Subscribe">
					<svg width="20" height="14" viewBox="0 0 20 14" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M0 7H18" stroke="currentColor"/>
						<path d="M13 1L19 7L13 13" stroke="currentColor"/>
					</svg>
				</button>
			</div>
			<p class="nh-subscribe__status" data-subscribe-status aria-live="polite"></p>
		</form>
	</div>
</div>
