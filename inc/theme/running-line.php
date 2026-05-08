<?php

declare(strict_types=1);

function nice_hair_running_line_settings_post_id(): string
{
    return 'nh_running_line_settings';
}

function nice_hair_get_running_line_setting(string $field_name, mixed $fallback = null): mixed
{
    if (! function_exists('get_field')) {
        return $fallback;
    }

    $value = get_field($field_name, nice_hair_running_line_settings_post_id());

    return nice_hair_acf_has_value($value) ? $value : $fallback;
}

function nice_hair_running_line_is_enabled(): bool
{
    return (bool) nice_hair_get_running_line_setting('nh_running_line_enabled', false);
}

function nice_hair_get_running_line_locations(): array
{
    $locations = nice_hair_get_running_line_setting('nh_running_line_locations', []);

    if (! is_array($locations)) {
        return [];
    }

    return array_values(array_filter(array_map(
        static fn (mixed $value): string => is_string($value) ? sanitize_key($value) : '',
        $locations
    )));
}

function nice_hair_get_running_line_specific_object_ids(): array
{
    $object_ids = nice_hair_get_running_line_setting('nh_running_line_specific_objects', []);

    if (! is_array($object_ids)) {
        return [];
    }

    return array_values(array_filter(array_map('intval', $object_ids)));
}

function nice_hair_running_line_is_page_variant(string $slug, string $template_slug = ''): bool
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

function nice_hair_running_line_is_blog_home(): bool
{
    if (is_home()) {
        return true;
    }

    $object_id = get_queried_object_id();
    $blog_page_id = (int) get_option('page_for_posts');

    return $blog_page_id > 0 && $object_id === $blog_page_id;
}

function nice_hair_running_line_matches_specific_objects(array $object_ids): bool
{
    if ($object_ids === []) {
        return false;
    }

    $current_object_id = get_queried_object_id();

    if ($current_object_id > 0 && in_array($current_object_id, $object_ids, true)) {
        return true;
    }

    if (nice_hair_running_line_is_blog_home()) {
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

function nice_hair_running_line_matches_locations(array $locations): bool
{
    if ($locations === []) {
        return false;
    }

    if (is_front_page() && in_array('front_page', $locations, true)) {
        return true;
    }

    if (nice_hair_running_line_is_page_variant('salon', 'page-templates/page-salon.php') && in_array('salon_page', $locations, true)) {
        return true;
    }

    if (function_exists('is_shop') && is_shop() && in_array('shop_page', $locations, true)) {
        return true;
    }

    if (nice_hair_running_line_is_page_variant('shipping-payment', 'page-templates/page-shipping-payment.php') && in_array('shipping_page', $locations, true)) {
        return true;
    }

    if (nice_hair_running_line_is_page_variant('tools', 'page-templates/page-tools.php') && in_array('tools_page', $locations, true)) {
        return true;
    }

    if (nice_hair_running_line_is_blog_home() && in_array('blog_home', $locations, true)) {
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
        && ! nice_hair_running_line_is_page_variant('salon', 'page-templates/page-salon.php')
        && ! nice_hair_running_line_is_page_variant('shipping-payment', 'page-templates/page-shipping-payment.php')
        && ! nice_hair_running_line_is_page_variant('tools', 'page-templates/page-tools.php')
        && ! (function_exists('is_shop') && is_shop())
        && in_array('default_pages', $locations, true)
    ) {
        return true;
    }

    return false;
}

function nice_hair_should_render_running_line(): bool
{
    static $should_render = null;

    if (is_bool($should_render)) {
        return $should_render;
    }

    if (is_admin() || is_feed() || (function_exists('wp_is_json_request') && wp_is_json_request())) {
        $should_render = false;

        return $should_render;
    }

    if (! nice_hair_running_line_is_enabled()) {
        $should_render = false;

        return $should_render;
    }

    if (! nice_hair_running_line_has_content()) {
        $should_render = false;

        return $should_render;
    }

    $specific_object_ids = nice_hair_get_running_line_specific_object_ids();

    if (nice_hair_running_line_matches_specific_objects($specific_object_ids)) {
        $should_render = true;

        return $should_render;
    }

    $should_render = nice_hair_running_line_matches_locations(nice_hair_get_running_line_locations());

    return $should_render;
}

function nice_hair_get_running_line_content_html(): string
{
    static $content_html = null;

    if (is_string($content_html)) {
        return $content_html;
    }

    $post_id = nice_hair_get_running_line_post_id(false);

    if ($post_id <= 0) {
        $content_html = '';

        return $content_html;
    }

    $post = get_post($post_id);

    if (! $post instanceof WP_Post || $post->post_type !== 'nh_running_line' || $post->post_status === 'trash') {
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

function nice_hair_running_line_has_content(): bool
{
    return nice_hair_get_running_line_content_html() !== '';
}

function nice_hair_running_line_body_class(array $classes): array
{
    if (nice_hair_should_render_running_line()) {
        $classes[] = 'nh-has-running-line';
    }

    return $classes;
}
add_filter('body_class', 'nice_hair_running_line_body_class');

function nice_hair_render_running_line(): void
{
    if (! nice_hair_should_render_running_line()) {
        return;
    }

    $content_html = nice_hair_get_running_line_content_html();

    if ($content_html === '') {
        return;
    }
    ?>
    <div class="nh-running-line" data-nh-running-line role="region" aria-label="<?php echo esc_attr__('Running line', 'nice-hair'); ?>">
        <div class="nh-running-line__viewport" data-nh-running-line-viewport>
            <div class="nh-running-line__track" data-nh-running-line-track>
                <div class="nh-running-line__set" data-nh-running-line-source>
                    <div class="nh-running-line__sequence" data-nh-running-line-sequence>
                        <div class="nh-running-line__item">
                            <?php echo $content_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </div>
                        <span class="nh-running-line__separator" aria-hidden="true"></span>
                    </div>
                </div>
                <div class="nh-running-line__set" data-nh-running-line-copy aria-hidden="true"></div>
            </div>
        </div>
    </div>
    <?php
}
add_action('wp_footer', 'nice_hair_render_running_line', 15);
