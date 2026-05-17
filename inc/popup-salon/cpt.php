<?php

declare(strict_types=1);

function nice_hair_popup_salon_form_title_content_class(): string
{
    return 'nh-popup-salon__form-title-content';
}

function nice_hair_popup_salon_disclaimer_content_class(): string
{
    return 'nh-popup-salon__disclaimer-content';
}

function nice_hair_get_popup_salon_default_form_title_text(): string
{
    return (string) __('Your contact info', 'nice-hair');
}

function nice_hair_get_popup_salon_default_privacy_url(): string
{
    if (function_exists('nice_hair_get_layout_field')) {
        $privacy_url = (string) nice_hair_get_layout_field('nh_footer_privacy_url', 'salon', '#');

        return $privacy_url !== '' ? $privacy_url : '#';
    }

    return '#';
}

function nice_hair_get_popup_salon_default_disclaimer_html(): string
{
    return sprintf(
        '%1$s <a href="%2$s">%3$s</a>.',
        esc_html__('By clicking the button, you agree to the', 'nice-hair'),
        esc_url(nice_hair_get_popup_salon_default_privacy_url()),
        esc_html__('privacy policy', 'nice-hair')
    );
}

function nice_hair_get_popup_salon_form_title_block_markup(): string
{
    $class_name = nice_hair_popup_salon_form_title_content_class();

    return sprintf(
        <<<'HTML'
<!-- wp:paragraph {"className":"%1$s"} -->
<p class="%1$s">%2$s</p>
<!-- /wp:paragraph -->
HTML,
        esc_attr($class_name),
        esc_html(nice_hair_get_popup_salon_default_form_title_text())
    );
}

function nice_hair_get_popup_salon_disclaimer_block_markup(): string
{
    $class_name = nice_hair_popup_salon_disclaimer_content_class();

    return sprintf(
        <<<'HTML'
<!-- wp:paragraph {"className":"%1$s"} -->
<p class="%1$s">%2$s</p>
<!-- /wp:paragraph -->
HTML,
        esc_attr($class_name),
        wp_kses_post(nice_hair_get_popup_salon_default_disclaimer_html())
    );
}

function nice_hair_get_popup_salon_default_content(): string
{
    $image_url = esc_url(get_theme_file_uri('/assets/images/quiz-bg.jpg'));

    return sprintf(
        <<<'HTML'
<!-- wp:group {"className":"nh-popup-salon__canvas","layout":{"type":"constrained"}} -->
<div class="wp-block-group nh-popup-salon__canvas">
<!-- wp:group {"className":"nh-popup-salon__copy","layout":{"type":"constrained"}} -->
<div class="wp-block-group nh-popup-salon__copy">
<!-- wp:heading {"level":2,"className":"nh-popup-salon__title"} -->
<h2 class="wp-block-heading nh-popup-salon__title">Book an appointment.<br>Fill out the contact form and we will contact you shortly.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"nh-popup-salon__description"} -->
<p class="nh-popup-salon__description">Share your contact details and we will get back to you shortly to confirm your visit.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"nh-popup-salon__image"} -->
<figure class="wp-block-image size-large nh-popup-salon__image"><img src="%s" alt="Nice Hair salon popup image" /></figure>
<!-- /wp:image -->

%s

%s
</div>
<!-- /wp:group -->
HTML,
        $image_url,
        nice_hair_get_popup_salon_form_title_block_markup(),
        nice_hair_get_popup_salon_disclaimer_block_markup()
    );
}

function nice_hair_popup_salon_block_has_class(array $block, string $class_name): bool
{
    $block_class_name = $block['attrs']['className'] ?? '';

    if (! is_string($block_class_name) || $block_class_name === '') {
        return false;
    }

    $classes = preg_split('/\s+/', trim($block_class_name));

    return is_array($classes) && in_array($class_name, $classes, true);
}

function nice_hair_popup_salon_collect_contract_classes(array $blocks, array &$contract): void
{
    foreach ($blocks as $block) {
        if (nice_hair_popup_salon_block_has_class($block, nice_hair_popup_salon_form_title_content_class())) {
            $contract['has_form_title'] = true;
        }

        if (nice_hair_popup_salon_block_has_class($block, nice_hair_popup_salon_disclaimer_content_class())) {
            $contract['has_disclaimer'] = true;
        }

        if (! empty($block['innerBlocks']) && is_array($block['innerBlocks'])) {
            nice_hair_popup_salon_collect_contract_classes($block['innerBlocks'], $contract);
        }
    }
}

function nice_hair_popup_salon_find_canvas_block_index(array $blocks): ?int
{
    foreach ($blocks as $index => $block) {
        if (nice_hair_popup_salon_block_has_class($block, 'nh-popup-salon__canvas')) {
            return $index;
        }
    }

    return null;
}

function nice_hair_popup_salon_build_block_from_markup(string $markup): ?array
{
    $blocks = parse_blocks(trim($markup));

    if (! is_array($blocks) || ! isset($blocks[0]) || ! is_array($blocks[0])) {
        return null;
    }

    return $blocks[0];
}

function nice_hair_popup_salon_append_inner_block(array &$parent_block, array $child_block): void
{
    if (! isset($parent_block['innerBlocks']) || ! is_array($parent_block['innerBlocks'])) {
        $parent_block['innerBlocks'] = [];
    }

    if (! isset($parent_block['innerContent']) || ! is_array($parent_block['innerContent']) || $parent_block['innerContent'] === []) {
        $parent_block['innerContent'] = [
            '',
            null,
            '',
        ];
    }

    $closing_markup = array_pop($parent_block['innerContent']);

    if (! is_string($closing_markup)) {
        $closing_markup = '';
    }

    $parent_block['innerBlocks'][] = $child_block;
    $parent_block['innerContent'][] = "\n\n";
    $parent_block['innerContent'][] = null;
    $parent_block['innerContent'][] = $closing_markup;
}

function nice_hair_maybe_migrate_popup_salon_content(int $post_id): void
{
    $post = get_post($post_id);

    if (! $post instanceof WP_Post || $post->post_type !== 'nh_popup_salon' || $post->post_status === 'trash') {
        return;
    }

    $content = trim((string) $post->post_content);

    if ($content === '') {
        return;
    }

    $blocks = parse_blocks($content);

    if (! is_array($blocks) || $blocks === []) {
        return;
    }

    $contract = [
        'has_form_title' => false,
        'has_disclaimer' => false,
    ];

    nice_hair_popup_salon_collect_contract_classes($blocks, $contract);

    if ($contract['has_form_title'] && $contract['has_disclaimer']) {
        return;
    }

    $canvas_index = nice_hair_popup_salon_find_canvas_block_index($blocks);

    if ($canvas_index === null || ! isset($blocks[$canvas_index]) || ! is_array($blocks[$canvas_index])) {
        return;
    }

    if (! $contract['has_form_title']) {
        $form_title_block = nice_hair_popup_salon_build_block_from_markup(nice_hair_get_popup_salon_form_title_block_markup());

        if (is_array($form_title_block)) {
            nice_hair_popup_salon_append_inner_block($blocks[$canvas_index], $form_title_block);
        }
    }

    if (! $contract['has_disclaimer']) {
        $disclaimer_block = nice_hair_popup_salon_build_block_from_markup(nice_hair_get_popup_salon_disclaimer_block_markup());

        if (is_array($disclaimer_block)) {
            nice_hair_popup_salon_append_inner_block($blocks[$canvas_index], $disclaimer_block);
        }
    }

    $updated_content = serialize_blocks($blocks);

    if ($updated_content === '' || $updated_content === $content) {
        return;
    }

    $result = wp_update_post([
        'ID' => $post_id,
        'post_content' => $updated_content,
    ], true);

    if (is_wp_error($result)) {
        return;
    }

    clean_post_cache($post_id);
}

function nice_hair_register_popup_salon_cpt(): void
{
    register_post_type('nh_popup_salon', [
        'labels' => [
            'name' => __('Popup Salon', 'nice-hair'),
            'singular_name' => __('Popup Salon', 'nice-hair'),
            'menu_name' => __('Popup Salon', 'nice-hair'),
            'all_items' => __('Обзор', 'nice-hair'),
            'edit_item' => __('Редактор Popup Salon', 'nice-hair'),
            'view_item' => __('Редактор Popup Salon', 'nice-hair'),
            'not_found' => __('Popup Salon ещё не создан.', 'nice-hair'),
        ],
        'public' => false,
        'publicly_queryable' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'menu_position' => 36,
        'menu_icon' => 'dashicons-welcome-view-site',
       'capability_type' => ['nh_submission', 'nh_submissions'],
        'capabilities' => function_exists('nice_hair_submission_cpt_capabilities')
            ? nice_hair_submission_cpt_capabilities()
            : [
                'create_posts' => 'do_not_allow',
            ],
        'map_meta_cap' => true,
        'hierarchical' => false,
        'supports' => ['editor'],
        'has_archive' => false,
        'rewrite' => false,
        'query_var' => false,
    ]);

    register_post_type('nh_salon_request', [
        'labels' => [
            'name' => __('Заявки Popup Salon', 'nice-hair'),
            'singular_name' => __('Заявка Popup Salon', 'nice-hair'),
            'edit_item' => __('Просмотр заявки', 'nice-hair'),
            'view_item' => __('Просмотр заявки', 'nice-hair'),
        ],
        'public' => false,
        'publicly_queryable' => false,
        'show_ui' => true,
        'show_in_menu' => false,
        'show_in_rest' => false,
        'capability_type' => ['nh_submission', 'nh_submissions'],
        'capabilities' => function_exists('nice_hair_submission_cpt_capabilities')
            ? nice_hair_submission_cpt_capabilities()
            : [
                'create_posts' => 'do_not_allow',
            ],
        'map_meta_cap' => true,
        'hierarchical' => false,
        'supports' => [],
        'has_archive' => false,
        'rewrite' => false,
        'query_var' => false,
    ]);
}
add_action('init', 'nice_hair_register_popup_salon_cpt');

function nice_hair_get_popup_salon_post_id(bool $create_if_missing = false): int
{
    static $cached_post_id = null;

    if (is_int($cached_post_id) && $cached_post_id > 0) {
        return $cached_post_id;
    }

    $option_post_id = (int) get_option('nh_popup_salon_post_id');

    if ($option_post_id > 0) {
        $option_post = get_post($option_post_id);

        if (
            $option_post instanceof WP_Post
            && $option_post->post_type === 'nh_popup_salon'
            && $option_post->post_status !== 'trash'
        ) {
            $cached_post_id = $option_post_id;

            return $cached_post_id;
        }
    }

    $existing_posts = get_posts([
        'post_type' => 'nh_popup_salon',
        'post_status' => ['publish', 'draft', 'private'],
        'numberposts' => 1,
        'orderby' => 'ID',
        'order' => 'ASC',
        'fields' => 'ids',
        'suppress_filters' => true,
    ]);

    if (is_array($existing_posts) && isset($existing_posts[0])) {
        $cached_post_id = (int) $existing_posts[0];
        update_option('nh_popup_salon_post_id', $cached_post_id);

        return $cached_post_id;
    }

    if (! $create_if_missing) {
        return 0;
    }

    $post_id = wp_insert_post([
        'post_type' => 'nh_popup_salon',
        'post_status' => 'publish',
        'post_title' => 'Popup Salon',
        'post_content' => nice_hair_get_popup_salon_default_content(),
    ], true);

    if (is_wp_error($post_id)) {
        return 0;
    }

    $cached_post_id = (int) $post_id;
    update_option('nh_popup_salon_post_id', $cached_post_id);

    return $cached_post_id;
}

function nice_hair_ensure_popup_salon_singleton(): void
{
    if (! is_admin()) {
        return;
    }

    $post_id = nice_hair_get_popup_salon_post_id(true);

    if ($post_id > 0) {
        nice_hair_maybe_migrate_popup_salon_content($post_id);
    }
}
add_action('admin_init', 'nice_hair_ensure_popup_salon_singleton');

function nice_hair_popup_salon_overview_url(): string
{
    return admin_url('admin.php?page=nh-popup-salon-overview');
}

function nice_hair_popup_salon_editor_redirect(): void
{
    if (! is_admin()) {
        return;
    }

    global $pagenow;

    $post_type = isset($_GET['post_type']) && is_string($_GET['post_type'])
        ? sanitize_key(wp_unslash($_GET['post_type']))
        : '';

    $page = isset($_GET['page']) && is_string($_GET['page'])
        ? sanitize_key(wp_unslash($_GET['page']))
        : '';

    if ($pagenow !== 'edit.php' || $post_type !== 'nh_popup_salon' || $page !== '') {
        return;
    }

    wp_safe_redirect(nice_hair_popup_salon_overview_url());
    exit;
}
add_action('admin_init', 'nice_hair_popup_salon_editor_redirect', 20);

function nice_hair_popup_salon_overview_page(): void
{
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('Popup Salon', 'nice-hair'); ?></h1>
        <p><?php echo esc_html__('Выберите, что хотите открыть: редактор контента popup или его настройки.', 'nice-hair'); ?></p>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px;max-width:760px;margin-top:24px;">
            <div class="card" style="max-width:none;padding:24px;">
                <h2 style="margin-top:0;"><?php echo esc_html__('Редактор', 'nice-hair'); ?></h2>
                <p><?php echo esc_html__('Изменить заголовок, текст и изображение Popup Salon в Gutenberg.', 'nice-hair'); ?></p>
                <p style="margin-bottom:0;">
                    <a class="button button-primary" href="<?php echo esc_url(admin_url('edit.php?post_type=nh_popup_salon&page=nh-popup-salon-editor')); ?>">
                        <?php echo esc_html__('Открыть редактор', 'nice-hair'); ?>
                    </a>
                </p>
            </div>

            <div class="card" style="max-width:none;padding:24px;">
                <h2 style="margin-top:0;"><?php echo esc_html__('Настройки', 'nice-hair'); ?></h2>
                <p><?php echo esc_html__('Управлять success-сообщением, email для заявок и WhatsApp номером.', 'nice-hair'); ?></p>
                <p style="margin-bottom:0;">
                    <a class="button" href="<?php echo esc_url(admin_url('edit.php?post_type=nh_popup_salon&page=popup-salon-settings')); ?>">
                        <?php echo esc_html__('Открыть настройки', 'nice-hair'); ?>
                    </a>
                </p>
            </div>
        </div>
    </div>
    <?php
}

function nice_hair_popup_salon_editor_page(): void
{
    $post_id = nice_hair_get_popup_salon_post_id(true);

    if ($post_id <= 0) {
        wp_die(esc_html__('Не удалось открыть редактор Popup Salon.', 'nice-hair'));
    }

    wp_safe_redirect(admin_url('post.php?post=' . $post_id . '&action=edit'));
    exit;
}

function nice_hair_popup_salon_admin_menu(): void
{
    remove_submenu_page('edit.php?post_type=nh_popup_salon', 'edit.php?post_type=nh_popup_salon');
    remove_submenu_page('edit.php?post_type=nh_popup_salon', 'post-new.php?post_type=nh_popup_salon');

    add_submenu_page(
        null,
        __('Обзор Popup Salon', 'nice-hair'),
        __('Обзор Popup Salon', 'nice-hair'),
        'edit_posts',
        'nh-popup-salon-overview',
        'nice_hair_popup_salon_overview_page'
    );

    add_submenu_page(
        'edit.php?post_type=nh_popup_salon',
        __('Редактор Popup Salon', 'nice-hair'),
        __('Редактор', 'nice-hair'),
        'edit_posts',
        'nh-popup-salon-editor',
        'nice_hair_popup_salon_editor_page',
        1
    );
}
add_action('admin_menu', 'nice_hair_popup_salon_admin_menu', 100);

function nice_hair_popup_salon_parent_file(?string $parent_file): ?string
{
    global $pagenow;

    $page = isset($_GET['page']) && is_string($_GET['page'])
        ? sanitize_key(wp_unslash($_GET['page']))
        : '';

    if ($pagenow === 'admin.php' && $page === 'nh-popup-salon-overview') {
        return 'edit.php?post_type=nh_popup_salon';
    }

    return $parent_file;
}
add_filter('parent_file', 'nice_hair_popup_salon_parent_file');

function nice_hair_popup_salon_submenu_file(?string $submenu_file): ?string
{
    global $pagenow, $post;

    $page = isset($_GET['page']) && is_string($_GET['page'])
        ? sanitize_key(wp_unslash($_GET['page']))
        : '';

    if ($pagenow === 'admin.php' && $page === 'nh-popup-salon-overview') {
        return 'nh-popup-salon-editor';
    }

    if ($pagenow === 'post.php' && $post instanceof WP_Post && $post->post_type === 'nh_popup_salon') {
        return 'nh-popup-salon-editor';
    }

    return $submenu_file;
}
add_filter('submenu_file', 'nice_hair_popup_salon_submenu_file');
