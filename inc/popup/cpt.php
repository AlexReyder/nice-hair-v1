<?php

declare(strict_types=1);

function nice_hair_get_popup_default_content(): string
{
    $image_url = esc_url(get_theme_file_uri('/assets/images/nice-hair-decor.png'));

    return sprintf(
        <<<'HTML'
<!-- wp:columns {"verticalAlignment":"center","className":"nh-site-popup__layout"} -->
<div class="wp-block-columns are-vertically-aligned-center nh-site-popup__layout">
<!-- wp:column {"verticalAlignment":"center","className":"nh-site-popup__content-column"} -->
<div class="wp-block-column is-vertically-aligned-center nh-site-popup__content-column">
<!-- wp:heading {"level":2,"className":"nh-site-popup__title"} -->
<h2 class="wp-block-heading nh-site-popup__title">New Client<br>Hair Offer</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"nh-site-popup__description"} -->
<p class="nh-site-popup__description">Enjoy 20%% off your first hair purchase as a welcome to our studio.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"nh-site-popup__actions"} -->
<div class="wp-block-buttons nh-site-popup__actions">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#">BOOK AN APPOINTMENT</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->

<!-- wp:paragraph {"className":"nh-site-popup__socials"} -->
<p class="nh-site-popup__socials"><a href="#">Telegram</a> <a href="#">Instagram</a> <a href="#">WhatsApp</a></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column {"className":"nh-site-popup__media-column"} -->
<div class="wp-block-column nh-site-popup__media-column">
<!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"nh-site-popup__image"} -->
<figure class="wp-block-image size-large nh-site-popup__image"><img src="%s" alt="Nice Hair popup image"/></figure>
<!-- /wp:image -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
HTML,
        $image_url
    );
}

function nice_hair_register_popup_cpt(): void
{
    register_post_type('nh_popup', [
        'labels' => [
            'name' => __('Popup', 'nice-hair'),
            'singular_name' => __('Popup', 'nice-hair'),
            'menu_name' => __('Popup', 'nice-hair'),
            'all_items' => __('Обзор', 'nice-hair'),
            'edit_item' => __('Редактор popup', 'nice-hair'),
            'view_item' => __('Редактор popup', 'nice-hair'),
            'not_found' => __('Popup ещё не создан.', 'nice-hair'),
        ],
        'public' => false,
        'publicly_queryable' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'menu_position' => 35,
        'menu_icon' => 'dashicons-format-status',
        'capability_type' => 'post',
        'capabilities' => [
            'create_posts' => 'do_not_allow',
        ],
        'map_meta_cap' => true,
        'hierarchical' => false,
        'supports' => ['editor'],
        'has_archive' => false,
        'rewrite' => false,
        'query_var' => false,
    ]);
}
add_action('init', 'nice_hair_register_popup_cpt');

function nice_hair_get_popup_post_id(bool $create_if_missing = false): int
{
    static $cached_post_id = null;

    if (is_int($cached_post_id) && $cached_post_id > 0) {
        return $cached_post_id;
    }

    $option_post_id = (int) get_option('nh_popup_post_id');

    if ($option_post_id > 0) {
        $option_post = get_post($option_post_id);

        if (
            $option_post instanceof WP_Post
            && $option_post->post_type === 'nh_popup'
            && $option_post->post_status !== 'trash'
        ) {
            $cached_post_id = $option_post_id;

            return $cached_post_id;
        }
    }

    $existing_posts = get_posts([
        'post_type' => 'nh_popup',
        'post_status' => ['publish', 'draft', 'private'],
        'numberposts' => 1,
        'orderby' => 'ID',
        'order' => 'ASC',
        'fields' => 'ids',
        'suppress_filters' => true,
    ]);

    if (is_array($existing_posts) && isset($existing_posts[0])) {
        $cached_post_id = (int) $existing_posts[0];
        update_option('nh_popup_post_id', $cached_post_id);

        return $cached_post_id;
    }

    if (! $create_if_missing) {
        return 0;
    }

    $post_id = wp_insert_post([
        'post_type' => 'nh_popup',
        'post_status' => 'publish',
        'post_title' => 'Popup',
        'post_content' => nice_hair_get_popup_default_content(),
    ], true);

    if (is_wp_error($post_id)) {
        return 0;
    }

    $cached_post_id = (int) $post_id;
    update_option('nh_popup_post_id', $cached_post_id);

    return $cached_post_id;
}

function nice_hair_ensure_popup_singleton(): void
{
    if (! is_admin()) {
        return;
    }

    nice_hair_get_popup_post_id(true);
}
add_action('admin_init', 'nice_hair_ensure_popup_singleton');

function nice_hair_popup_overview_url(): string
{
    return admin_url('edit.php?post_type=nh_popup&page=nh-popup-overview');
}

function nice_hair_popup_editor_redirect(): void
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

    if ($pagenow !== 'edit.php' || $post_type !== 'nh_popup' || $page !== '') {
        return;
    }

    wp_safe_redirect(nice_hair_popup_overview_url());
    exit;
}
add_action('admin_init', 'nice_hair_popup_editor_redirect', 20);

function nice_hair_popup_overview_page(): void
{
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('Popup', 'nice-hair'); ?></h1>
        <p><?php echo esc_html__('Выберите, куда перейти дальше: к редактированию popup или к его настройкам.', 'nice-hair'); ?></p>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px;max-width:760px;margin-top:24px;">
            <div class="card" style="max-width:none;padding:24px;">
                <h2 style="margin-top:0;"><?php echo esc_html__('Редактор', 'nice-hair'); ?></h2>
                <p><?php echo esc_html__('Изменить содержимое popup в Gutenberg: заголовок, текст, кнопку, ссылки и изображение.', 'nice-hair'); ?></p>
                <p style="margin-bottom:0;">
                    <a class="button button-primary" href="<?php echo esc_url(admin_url('edit.php?post_type=nh_popup&page=nh-popup-editor')); ?>">
                        <?php echo esc_html__('Открыть редактор', 'nice-hair'); ?>
                    </a>
                </p>
            </div>

            <div class="card" style="max-width:none;padding:24px;">
                <h2 style="margin-top:0;"><?php echo esc_html__('Настройки', 'nice-hair'); ?></h2>
                <p><?php echo esc_html__('Управлять страницами показа popup, задержкой открытия и режимом повторного показа.', 'nice-hair'); ?></p>
                <p style="margin-bottom:0;">
                    <a class="button" href="<?php echo esc_url(admin_url('edit.php?post_type=nh_popup&page=popup-settings')); ?>">
                        <?php echo esc_html__('Открыть настройки', 'nice-hair'); ?>
                    </a>
                </p>
            </div>
        </div>
    </div>
    <?php
}

function nice_hair_popup_editor_page(): void
{
    $post_id = nice_hair_get_popup_post_id(true);

    if ($post_id <= 0) {
        wp_die(esc_html__('Не удалось открыть редактор popup.', 'nice-hair'));
    }

    wp_safe_redirect(admin_url('post.php?post=' . $post_id . '&action=edit'));
    exit;
}

function nice_hair_popup_admin_menu(): void
{
    remove_submenu_page('edit.php?post_type=nh_popup', 'edit.php?post_type=nh_popup');
    remove_submenu_page('edit.php?post_type=nh_popup', 'post-new.php?post_type=nh_popup');

    add_submenu_page(
        'edit.php?post_type=nh_popup',
        __('Обзор popup', 'nice-hair'),
        __('Обзор', 'nice-hair'),
        'edit_posts',
        'nh-popup-overview',
        'nice_hair_popup_overview_page',
        0
    );

    add_submenu_page(
        'edit.php?post_type=nh_popup',
        __('Редактор popup', 'nice-hair'),
        __('Редактор', 'nice-hair'),
        'edit_posts',
        'nh-popup-editor',
        'nice_hair_popup_editor_page',
        1
    );
}
add_action('admin_menu', 'nice_hair_popup_admin_menu', 100);

function nice_hair_popup_submenu_file(?string $submenu_file): ?string
{
    global $pagenow, $post;

    if ($pagenow === 'post.php' && $post instanceof WP_Post && $post->post_type === 'nh_popup') {
        return 'nh-popup-editor';
    }

    return $submenu_file;
}
add_filter('submenu_file', 'nice_hair_popup_submenu_file');
