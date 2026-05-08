<?php

declare(strict_types=1);

function nice_hair_popup_settings_post_id(): string
{
    return 'nh_popup_settings';
}

function nice_hair_get_popup_setting(string $field_name, mixed $fallback = null): mixed
{
    if (! function_exists('get_field')) {
        return $fallback;
    }

    $value = get_field($field_name, nice_hair_popup_settings_post_id());

    return nice_hair_acf_has_value($value) ? $value : $fallback;
}

function nice_hair_popup_is_enabled(): bool
{
    return (bool) nice_hair_get_popup_setting('nh_popup_enabled', false);
}

function nice_hair_get_popup_locations(): array
{
    $locations = nice_hair_get_popup_setting('nh_popup_locations', []);

    if (! is_array($locations)) {
        return [];
    }

    return array_values(array_filter(array_map(
        static fn (mixed $value): string => is_string($value) ? sanitize_key($value) : '',
        $locations
    )));
}

function nice_hair_get_popup_specific_object_ids(): array
{
    $object_ids = nice_hair_get_popup_setting('nh_popup_specific_objects', []);

    if (! is_array($object_ids)) {
        return [];
    }

    return array_values(array_filter(array_map('intval', $object_ids)));
}

function nice_hair_get_popup_display_mode(): string
{
    $display_mode = nice_hair_get_popup_setting('nh_popup_display_mode', null);

    if (is_string($display_mode) && in_array($display_mode, ['once', 'session', 'days'], true)) {
        return $display_mode;
    }

    $legacy_open_once = nice_hair_get_popup_setting('nh_popup_open_once', null);

    if ($legacy_open_once === true || $legacy_open_once === 1 || $legacy_open_once === '1') {
        return 'once';
    }

    return 'once';
}

function nice_hair_get_popup_delay_seconds(): int
{
    return max(0, (int) nice_hair_get_popup_setting('nh_popup_delay_seconds', 10));
}

function nice_hair_get_popup_repeat_days(): int
{
    return max(1, (int) nice_hair_get_popup_setting('nh_popup_repeat_days', 7));
}

function nice_hair_popup_is_page_variant(string $slug, string $template_slug = ''): bool
{
    if (! is_page()) {
        return false;
    }

    $object_id = get_queried_object_id();

    if ($object_id <= 0) {
        return false;
    }

    if ($template_slug !== '' && get_page_template_slug($object_id) === $template_slug) {
        return true;
    }

    $post = get_post($object_id);

    return $post instanceof WP_Post && $post->post_name === $slug;
}

function nice_hair_popup_is_blog_home(): bool
{
    if (is_home()) {
        return true;
    }

    $object_id = get_queried_object_id();
    $blog_page_id = (int) get_option('page_for_posts');

    return $blog_page_id > 0 && $object_id === $blog_page_id;
}

function nice_hair_popup_matches_specific_objects(array $object_ids): bool
{
    if ($object_ids === []) {
        return false;
    }

    $current_object_id = get_queried_object_id();

    if ($current_object_id > 0 && in_array($current_object_id, $object_ids, true)) {
        return true;
    }

    if (nice_hair_popup_is_blog_home()) {
        $blog_page_id = (int) get_option('page_for_posts');

        if ($blog_page_id > 0 && in_array($blog_page_id, $object_ids, true)) {
            return true;
        }
    }

    if (function_exists('is_shop') && is_shop() && function_exists('wc_get_page_id')) {
        $shop_page_id = (int) wc_get_page_id('shop');

        if ($shop_page_id > 0 && in_array($shop_page_id, $object_ids, true)) {
            return true;
        }
    }

    return false;
}

function nice_hair_popup_matches_locations(array $locations): bool
{
    if ($locations === []) {
        return false;
    }

    if (is_front_page() && in_array('front_page', $locations, true)) {
        return true;
    }

    if (nice_hair_popup_is_page_variant('salon', 'page-templates/page-salon.php') && in_array('salon_page', $locations, true)) {
        return true;
    }

    if (function_exists('is_shop') && is_shop() && in_array('shop_page', $locations, true)) {
        return true;
    }

    if (nice_hair_popup_is_page_variant('shipping-payment', 'page-templates/page-shipping-payment.php') && in_array('shipping_page', $locations, true)) {
        return true;
    }

    if (nice_hair_popup_is_page_variant('tools', 'page-templates/page-tools.php') && in_array('tools_page', $locations, true)) {
        return true;
    }

    if (nice_hair_popup_is_blog_home() && in_array('blog_home', $locations, true)) {
        return true;
    }

    if (is_singular('post') && in_array('blog_single', $locations, true)) {
        return true;
    }

    if (function_exists('is_product') && is_product() && in_array('product_single', $locations, true)) {
        return true;
    }

    if (function_exists('is_product_taxonomy') && is_product_taxonomy() && in_array('product_archive', $locations, true)) {
        return true;
    }

    if (
        is_page()
        && ! is_front_page()
        && ! nice_hair_popup_is_page_variant('salon', 'page-templates/page-salon.php')
        && ! nice_hair_popup_is_page_variant('shipping-payment', 'page-templates/page-shipping-payment.php')
        && ! nice_hair_popup_is_page_variant('tools', 'page-templates/page-tools.php')
        && ! (function_exists('is_shop') && is_shop())
        && in_array('default_pages', $locations, true)
    ) {
        return true;
    }

    return false;
}

function nice_hair_should_render_popup(): bool
{
    static $should_render = null;

    if (is_bool($should_render)) {
        return $should_render;
    }

    if (is_admin() || is_feed() || (function_exists('wp_is_json_request') && wp_is_json_request())) {
        $should_render = false;

        return $should_render;
    }

    if (! nice_hair_popup_is_enabled()) {
        $should_render = false;

        return $should_render;
    }

    if (! nice_hair_popup_has_content()) {
        $should_render = false;

        return $should_render;
    }

    $specific_object_ids = nice_hair_get_popup_specific_object_ids();

    if (nice_hair_popup_matches_specific_objects($specific_object_ids)) {
        $should_render = true;

        return $should_render;
    }

    $should_render = nice_hair_popup_matches_locations(nice_hair_get_popup_locations());

    return $should_render;
}

function nice_hair_get_popup_content_html(): string
{
    static $content_html = null;

    if (is_string($content_html)) {
        return $content_html;
    }

    $post_id = nice_hair_get_popup_post_id(false);

    if ($post_id <= 0) {
        $content_html = '';

        return $content_html;
    }

    $post = get_post($post_id);

    if (! $post instanceof WP_Post || $post->post_type !== 'nh_popup' || $post->post_status === 'trash') {
        $content_html = '';

        return $content_html;
    }

    $content = trim((string) $post->post_content);

    if ($content === '') {
        $content_html = '';

        return $content_html;
    }

    $html = apply_filters('the_content', $content);

    $content_html = is_string($html) ? trim($html) : '';

    return $content_html;
}

function nice_hair_popup_has_content(): bool
{
    return nice_hair_get_popup_content_html() !== '';
}

function nice_hair_get_popup_storage_key(): string
{
    $post_id = nice_hair_get_popup_post_id(false);

    if ($post_id <= 0) {
        return 'nh-popup';
    }

    $post = get_post($post_id);

    if (! $post instanceof WP_Post || $post->post_type !== 'nh_popup') {
        return 'nh-popup';
    }

    $version_source = trim((string) $post->post_modified_gmt);

    if ($version_source === '' || $version_source === '0000-00-00 00:00:00') {
        $version_source = trim((string) $post->post_modified);
    }

    if ($version_source === '' || $version_source === '0000-00-00 00:00:00') {
        $version_source = (string) $post->ID;
    }

    $hash = substr(md5($version_source . '|' . $post->post_content), 0, 12);

    return 'nh-popup-' . $post->ID . '-' . $hash;
}

function nice_hair_render_popup(): void
{
    if (! nice_hair_should_render_popup()) {
        return;
    }

    $content_html = nice_hair_get_popup_content_html();

    if ($content_html === '') {
        return;
    }

    $delay_ms = nice_hair_get_popup_delay_seconds() * 1000;
    $display_mode = nice_hair_get_popup_display_mode();
    $repeat_days = nice_hair_get_popup_repeat_days();
    ?>
    <div
        id="nh-site-popup"
        class="nh-site-popup"
        data-nh-popup
        data-popup-id="site-popup"
        data-delay-ms="<?php echo esc_attr((string) $delay_ms); ?>"
        data-display-mode="<?php echo esc_attr($display_mode); ?>"
        data-repeat-days="<?php echo esc_attr((string) $repeat_days); ?>"
        data-storage-key="<?php echo esc_attr(nice_hair_get_popup_storage_key()); ?>"
        hidden
    >
        <div class="nh-site-popup__backdrop" data-nh-popup-close></div>
        <div class="nh-site-popup__dialog" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr__('Popup', 'nice-hair'); ?>">
            <button type="button" class="nh-site-popup__close" data-nh-popup-close-button data-nh-popup-close aria-label="<?php echo esc_attr__('Close popup', 'nice-hair'); ?>">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="nh-site-popup__content">
                <?php echo $content_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        </div>
    </div>
    <?php
}
add_action('wp_footer', 'nice_hair_render_popup', 20);
