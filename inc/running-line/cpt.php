<?php

declare(strict_types=1);

function nice_hair_register_running_line_cpt(): void
{
    register_post_type('nh_running_line', [
        'labels' => [
            'name' => __('Бегущая строка', 'nice-hair'),
            'singular_name' => __('Бегущая строка', 'nice-hair'),
            'menu_name' => __('Бегущая строка', 'nice-hair'),
            'all_items' => __('Обзор', 'nice-hair'),
            'edit_item' => __('Редактор бегущей строки', 'nice-hair'),
            'view_item' => __('Редактор бегущей строки', 'nice-hair'),
            'not_found' => __('Бегущая строка ещё не создана', 'nice-hair'),
        ],
        'public' => false,
        'publicly_queryable' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'menu_position' => 34,
        'menu_icon' => 'dashicons-megaphone',
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
add_action('init', 'nice_hair_register_running_line_cpt');

function nice_hair_get_running_line_post_id(bool $create_if_missing = false): int
{
    static $cached_post_id = null;

    if (is_int($cached_post_id) && $cached_post_id > 0) {
        return $cached_post_id;
    }

    $option_post_id = (int) get_option('nh_running_line_post_id');

    if ($option_post_id > 0) {
        $option_post = get_post($option_post_id);

        if (
            $option_post instanceof WP_Post
            && $option_post->post_type === 'nh_running_line'
            && $option_post->post_status !== 'trash'
        ) {
            $cached_post_id = $option_post_id;

            return $cached_post_id;
        }
    }

    $existing_posts = get_posts([
        'post_type' => 'nh_running_line',
        'post_status' => ['publish', 'draft', 'private'],
        'numberposts' => 1,
        'orderby' => 'ID',
        'order' => 'ASC',
        'fields' => 'ids',
        'suppress_filters' => true,
    ]);

    if (is_array($existing_posts) && isset($existing_posts[0])) {
        $cached_post_id = (int) $existing_posts[0];
        update_option('nh_running_line_post_id', $cached_post_id);

        return $cached_post_id;
    }

    if (! $create_if_missing) {
        return 0;
    }

    $post_id = wp_insert_post([
        'post_type' => 'nh_running_line',
        'post_status' => 'publish',
        'post_title' => 'Running Line',
        'post_content' => '<!-- wp:paragraph --><p>New Client Offer - Get 20% Off Your First Hair Purchase</p><!-- /wp:paragraph -->',
    ], true);

    if (is_wp_error($post_id)) {
        return 0;
    }

    $cached_post_id = (int) $post_id;
    update_option('nh_running_line_post_id', $cached_post_id);

    return $cached_post_id;
}

function nice_hair_ensure_running_line_singleton(): void
{
    if (! is_admin()) {
        return;
    }

    nice_hair_get_running_line_post_id(true);
}
add_action('admin_init', 'nice_hair_ensure_running_line_singleton');

function nice_hair_running_line_overview_url(): string
{
    return admin_url('edit.php?post_type=nh_running_line&page=nh-running-line-overview');
}

function nice_hair_running_line_editor_redirect(): void
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

    if ($pagenow !== 'edit.php' || $post_type !== 'nh_running_line' || $page !== '') {
        return;
    }

    wp_safe_redirect(nice_hair_running_line_overview_url());
    exit;
}
add_action('admin_init', 'nice_hair_running_line_editor_redirect', 20);

function nice_hair_running_line_overview_page(): void
{
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('Бегущая строка', 'nice-hair'); ?></h1>
        <p><?php echo esc_html__('Выберите, куда перейти: к редактору содержимого строки или к настройкам её показа.', 'nice-hair'); ?></p>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px;max-width:760px;margin-top:24px;">
            <div class="card" style="max-width:none;padding:24px;">
                <h2 style="margin-top:0;"><?php echo esc_html__('Редактор', 'nice-hair'); ?></h2>
                <p><?php echo esc_html__('Изменить текст и ссылки бегущей строки в Gutenberg.', 'nice-hair'); ?></p>
                <p style="margin-bottom:0;">
                    <a class="button button-primary" href="<?php echo esc_url(admin_url('edit.php?post_type=nh_running_line&page=nh-running-line-editor')); ?>">
                        <?php echo esc_html__('Открыть редактор', 'nice-hair'); ?>
                    </a>
                </p>
            </div>

            <div class="card" style="max-width:none;padding:24px;">
                <h2 style="margin-top:0;"><?php echo esc_html__('Настройки', 'nice-hair'); ?></h2>
                <p><?php echo esc_html__('Управлять страницами, на которых должна показываться бегущая строка.', 'nice-hair'); ?></p>
                <p style="margin-bottom:0;">
                    <a class="button" href="<?php echo esc_url(admin_url('edit.php?post_type=nh_running_line&page=running-line-settings')); ?>">
                        <?php echo esc_html__('Открыть настройки', 'nice-hair'); ?>
                    </a>
                </p>
            </div>
        </div>
    </div>
    <?php
}

function nice_hair_running_line_editor_page(): void
{
    $post_id = nice_hair_get_running_line_post_id(true);

    if ($post_id <= 0) {
        wp_die(esc_html__('Не удалось открыть редактор бегущей строки.', 'nice-hair'));
    }

    wp_safe_redirect(admin_url('post.php?post=' . $post_id . '&action=edit'));
    exit;
}

function nice_hair_running_line_admin_menu(): void
{
    remove_submenu_page('edit.php?post_type=nh_running_line', 'edit.php?post_type=nh_running_line');
    remove_submenu_page('edit.php?post_type=nh_running_line', 'post-new.php?post_type=nh_running_line');

    add_submenu_page(
        'edit.php?post_type=nh_running_line',
        __('Обзор бегущей строки', 'nice-hair'),
        __('Обзор', 'nice-hair'),
        'edit_posts',
        'nh-running-line-overview',
        'nice_hair_running_line_overview_page',
        0
    );

    add_submenu_page(
        'edit.php?post_type=nh_running_line',
        __('Редактор бегущей строки', 'nice-hair'),
        __('Редактор', 'nice-hair'),
        'edit_posts',
        'nh-running-line-editor',
        'nice_hair_running_line_editor_page',
        1
    );
}
add_action('admin_menu', 'nice_hair_running_line_admin_menu', 100);

function nice_hair_running_line_submenu_file(?string $submenu_file): ?string
{
    global $pagenow, $post;

    if ($pagenow === 'post.php' && $post instanceof WP_Post && $post->post_type === 'nh_running_line') {
        return 'nh-running-line-editor';
    }

    return $submenu_file;
}
add_filter('submenu_file', 'nice_hair_running_line_submenu_file');
