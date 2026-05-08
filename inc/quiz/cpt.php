<?php

declare(strict_types=1);

/**
 * Custom post type `nh_price_quiz` for storing form submissions.
 *
 * The admin section "Заявки" acts as a shared inbox for both Price Quiz
 * and Popup Salon requests. Quiz entries stay stored in `nh_price_quiz`,
 * while Popup Salon entries live in `nh_salon_request`; the list screen is
 * merged at query level so the team manages all requests in one place.
 */

function nice_hair_register_price_quiz_cpt(): void
{
    register_post_type('nh_price_quiz', [
        'labels'          => [
            'name'          => __('Заявки', 'nice-hair'),
            'singular_name' => __('Заявка', 'nice-hair'),
            'menu_name'     => __('Заявки', 'nice-hair'),
            'all_items'     => __('Все заявки', 'nice-hair'),
            'view_item'     => __('Просмотр заявки', 'nice-hair'),
            'edit_item'     => __('Просмотр заявки', 'nice-hair'),
            'search_items'  => __('Поиск заявок', 'nice-hair'),
            'not_found'     => __('Заявок пока нет', 'nice-hair'),
        ],
        'public'          => false,
        'show_ui'         => true,
        'show_in_menu'    => true,
        'show_in_rest'    => false,
        'menu_position'   => 32,
        'menu_icon'       => 'dashicons-format-chat',
        'capability_type' => 'post',
        'capabilities'    => [
            'create_posts' => 'do_not_allow',
        ],
        'map_meta_cap'    => true,
        'hierarchical'    => false,
        'supports'        => [],
        'has_archive'     => false,
        'rewrite'         => false,
        'query_var'       => false,
    ]);
}
add_action('init', 'nice_hair_register_price_quiz_cpt');

function nice_hair_submissions_remove_post_type_supports(): void
{
    foreach (['nh_price_quiz', 'nh_salon_request'] as $post_type) {
        remove_post_type_support($post_type, 'title');
        remove_post_type_support($post_type, 'editor');
        remove_post_type_support($post_type, 'excerpt');
        remove_post_type_support($post_type, 'thumbnail');
        remove_post_type_support($post_type, 'author');
        remove_post_type_support($post_type, 'revisions');
        remove_post_type_support($post_type, 'comments');
    }
}
add_action('init', 'nice_hair_submissions_remove_post_type_supports', 20);

function nice_hair_submission_source_label(string $post_type, int $post_id = 0): string
{
    if ($post_type === 'nh_salon_request') {
        $source = $post_id > 0 ? (string) get_post_meta($post_id, '_nh_salon_request_source', true) : '';

        if ($source === 'shop_consultation') {
            return __('Консультация Shop', 'nice-hair');
        }

        return __('Попап Salon', 'nice-hair');
    }

    return __('Квиз', 'nice-hair');
}

function nice_hair_is_submissions_admin_screen(): bool
{
    if (! is_admin()) {
        return false;
    }

    global $pagenow;

    if ($pagenow === 'edit.php') {
        $post_type = isset($_GET['post_type']) && is_string($_GET['post_type'])
            ? sanitize_key(wp_unslash($_GET['post_type']))
            : '';

        return $post_type === 'nh_price_quiz';
    }

    if (! in_array($pagenow, ['post.php', 'post-new.php'], true)) {
        return false;
    }

    $post_id = isset($_GET['post']) ? (int) $_GET['post'] : 0;
    $post_type = $post_id > 0 ? get_post_type($post_id) : '';

    if ($post_type === '' && isset($_GET['post_type']) && is_string($_GET['post_type'])) {
        $post_type = sanitize_key(wp_unslash($_GET['post_type']));
    }

    return in_array($post_type, ['nh_price_quiz', 'nh_salon_request'], true);
}

function nice_hair_mark_submissions_seen(): void
{
    if (! nice_hair_is_submissions_admin_screen()) {
        return;
    }

    $user_id = get_current_user_id();

    if ($user_id <= 0) {
        return;
    }

    update_user_meta($user_id, 'nh_submissions_last_seen_gmt', current_time('mysql', true));
}
add_action('admin_init', 'nice_hair_mark_submissions_seen', 5);

function nice_hair_get_unread_submissions_count(?int $user_id = null): int
{
    $user_id = $user_id ?: get_current_user_id();

    if ($user_id <= 0) {
        return 0;
    }

    $query_args = [
        'post_type' => ['nh_price_quiz', 'nh_salon_request'],
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'no_found_rows' => false,
        'orderby' => 'date',
        'order' => 'DESC',
    ];

    $last_seen = (string) get_user_meta($user_id, 'nh_submissions_last_seen_gmt', true);

    if ($last_seen !== '') {
        $query_args['date_query'] = [[
            'column' => 'post_date_gmt',
            'after' => $last_seen,
            'inclusive' => false,
        ]];
    }

    $query = new WP_Query($query_args);

    return (int) $query->found_posts;
}

function nice_hair_add_submissions_menu_badge(): void
{
    $count = nice_hair_get_unread_submissions_count();

    if ($count <= 0) {
        return;
    }

    global $menu;

    $label = sprintf(
        _n('%s новая заявка', '%s новых заявок', $count, 'nice-hair'),
        number_format_i18n($count)
    );

    foreach ($menu as $index => $item) {
        if (! isset($item[2]) || $item[2] !== 'edit.php?post_type=nh_price_quiz') {
            continue;
        }

        $menu[$index][0] .= sprintf(
            ' <span class="awaiting-mod count-%1$d"><span class="pending-count" aria-hidden="true">%2$s</span><span class="screen-reader-text">%3$s</span></span>',
            $count,
            esc_html(number_format_i18n($count)),
            esc_html($label)
        );

        break;
    }
}
add_action('admin_menu', 'nice_hair_add_submissions_menu_badge', 999);

function nice_hair_submission_name(int $post_id): string
{
    if (get_post_type($post_id) === 'nh_salon_request') {
        return (string) get_post_meta($post_id, '_nh_popup_salon_name', true);
    }

    return (string) get_post_meta($post_id, '_nh_pq_name', true);
}

function nice_hair_submission_contact(int $post_id): string
{
    if (get_post_type($post_id) === 'nh_salon_request') {
        return (string) get_post_meta($post_id, '_nh_popup_salon_phone', true);
    }

    return (string) get_post_meta($post_id, '_nh_pq_whatsapp', true);
}

function nice_hair_submission_details(int $post_id): string
{
    if (get_post_type($post_id) === 'nh_salon_request') {
        return (string) get_post_meta($post_id, '_nh_popup_salon_label', true);
    }

    $goal = get_post_meta($post_id, '_nh_pq_goal', true);

    if (! is_array($goal) || $goal === []) {
        return '';
    }

    return implode(', ', array_map('strval', $goal));
}

function nice_hair_submission_photos_label(int $post_id): string
{
    if (get_post_type($post_id) === 'nh_salon_request') {
        return '—';
    }

    $mode = (string) get_post_meta($post_id, '_nh_pq_photos_mode', true);

    if ($mode === 'uploaded') {
        $ids = array_filter(array_map(
            'intval',
            explode(',', (string) get_post_meta($post_id, '_nh_pq_photo_ids', true))
        ));

        return (string) count($ids);
    }

    if ($mode === 'skipped') {
        return (string) __('Без фото', 'nice-hair');
    }

    return '';
}

function nice_hair_price_quiz_admin_columns(array $columns): array
{
    return [
        'cb'                    => $columns['cb'] ?? '<input type="checkbox" />',
        'title'                 => __('Заявка', 'nice-hair'),
        'nh_submission_source'  => __('Источник', 'nice-hair'),
        'nh_submission_name'    => __('Имя', 'nice-hair'),
        'nh_submission_contact' => __('Телефон / WhatsApp', 'nice-hair'),
        'nh_submission_details' => __('Детали', 'nice-hair'),
        'nh_submission_photos'  => __('Фото', 'nice-hair'),
        'date'                  => $columns['date'] ?? __('Дата', 'nice-hair'),
    ];
}
add_filter('manage_nh_price_quiz_posts_columns', 'nice_hair_price_quiz_admin_columns');
add_filter('manage_nh_salon_request_posts_columns', 'nice_hair_price_quiz_admin_columns');

function nice_hair_price_quiz_render_column(string $column, int $post_id): void
{
    switch ($column) {
        case 'nh_submission_source':
            echo esc_html(nice_hair_submission_source_label((string) get_post_type($post_id), $post_id));
            break;

        case 'nh_submission_name':
            echo esc_html(nice_hair_submission_name($post_id));
            break;

        case 'nh_submission_contact':
            if (get_post_type($post_id) === 'nh_salon_request') {
                $phone = (string) get_post_meta($post_id, '_nh_popup_salon_phone', true);
                $whatsapp = (string) get_post_meta($post_id, '_nh_shop_consultation_whatsapp', true);

                if ($phone !== '') {
                    echo esc_html($phone);
                }

                if ($whatsapp !== '') {
                    if ($phone !== '') {
                        echo '<br>';
                    }

                    printf(
                        '<a href="https://wa.me/%s" target="_blank" rel="noopener">WhatsApp: %s</a>',
                        esc_attr($whatsapp),
                        esc_html($whatsapp)
                    );
                }

                break;
            }

            $number = nice_hair_submission_contact($post_id);

            if ($number !== '') {
                printf(
                    '<a href="https://wa.me/%s" target="_blank" rel="noopener">%s</a>',
                    esc_attr($number),
                    esc_html($number)
                );
            }
            break;

        case 'nh_submission_details':
            echo esc_html(nice_hair_submission_details($post_id));
            break;

        case 'nh_submission_photos':
            echo esc_html(nice_hair_submission_photos_label($post_id));
            break;
    }
}
add_action('manage_nh_price_quiz_posts_custom_column', 'nice_hair_price_quiz_render_column', 10, 2);
add_action('manage_nh_salon_request_posts_custom_column', 'nice_hair_price_quiz_render_column', 10, 2);

function nice_hair_price_quiz_add_meta_box(): void
{
    add_meta_box(
        'nh_pq_details',
        __('Детали заявки', 'nice-hair'),
        'nice_hair_price_quiz_render_meta_box',
        'nh_price_quiz',
        'normal',
        'high'
    );

    add_meta_box(
        'nh_salon_request_details',
        __('Детали заявки', 'nice-hair'),
        'nice_hair_price_quiz_render_meta_box',
        'nh_salon_request',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes_nh_price_quiz', 'nice_hair_price_quiz_add_meta_box');
add_action('add_meta_boxes_nh_salon_request', 'nice_hair_price_quiz_add_meta_box');

function nice_hair_price_quiz_render_meta_box(WP_Post $post): void
{
    if ($post->post_type === 'nh_salon_request') {
        $name = (string) get_post_meta($post->ID, '_nh_popup_salon_name', true);
        $phone = (string) get_post_meta($post->ID, '_nh_popup_salon_phone', true);
        $popup_label = (string) get_post_meta($post->ID, '_nh_popup_salon_label', true);
        $page_url = (string) get_post_meta($post->ID, '_nh_popup_salon_page_url', true);
        $submitted = (string) get_post_meta($post->ID, '_nh_popup_salon_submitted_at', true);
        $ip = (string) get_post_meta($post->ID, '_nh_popup_salon_user_ip', true);
        $source = (string) get_post_meta($post->ID, '_nh_salon_request_source', true);
        $whatsapp = (string) get_post_meta($post->ID, '_nh_shop_consultation_whatsapp', true);
        $product_name = (string) get_post_meta($post->ID, '_nh_shop_consultation_product_name', true);
        $product_url = (string) get_post_meta($post->ID, '_nh_shop_consultation_product_url', true);
        ?>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e('Источник', 'nice-hair'); ?></th>
                    <td><?php echo esc_html(nice_hair_submission_source_label('nh_salon_request', $post->ID)); ?></td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo esc_html($source === 'shop_consultation' ? __('Запрос', 'nice-hair') : __('Метка попапа', 'nice-hair')); ?>
                    </th>
                    <td><?php echo esc_html($popup_label); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Имя', 'nice-hair'); ?></th>
                    <td><?php echo esc_html($name); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Телефон', 'nice-hair'); ?></th>
                    <td>
                        <?php if ($phone !== '') : ?>
                            <a href="https://wa.me/<?php echo esc_attr($phone); ?>" target="_blank" rel="noopener">
                                <?php echo esc_html($phone); ?>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if ($source === 'shop_consultation') : ?>
                    <tr>
                        <th scope="row"><?php esc_html_e('WhatsApp', 'nice-hair'); ?></th>
                        <td>
                            <?php if ($whatsapp !== '') : ?>
                                <a href="https://wa.me/<?php echo esc_attr($whatsapp); ?>" target="_blank" rel="noopener">
                                    <?php echo esc_html($whatsapp); ?>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Товар', 'nice-hair'); ?></th>
                        <td>
                            <?php if ($product_url !== '') : ?>
                                <a href="<?php echo esc_url($product_url); ?>" target="_blank" rel="noopener">
                                    <?php echo esc_html($product_name !== '' ? $product_name : $product_url); ?>
                                </a>
                            <?php else : ?>
                                <?php echo esc_html($product_name); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <th scope="row"><?php esc_html_e('URL страницы', 'nice-hair'); ?></th>
                    <td>
                        <?php if ($page_url !== '') : ?>
                            <a href="<?php echo esc_url($page_url); ?>" target="_blank" rel="noopener"><?php echo esc_html($page_url); ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Отправлено', 'nice-hair'); ?></th>
                    <td><?php echo esc_html($submitted); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('IP клиента', 'nice-hair'); ?></th>
                    <td><code><?php echo esc_html($ip); ?></code></td>
                </tr>
            </tbody>
        </table>
        <?php

        return;
    }

    $name      = (string) get_post_meta($post->ID, '_nh_pq_name', true);
    $whatsapp  = (string) get_post_meta($post->ID, '_nh_pq_whatsapp', true);
    $goal      = get_post_meta($post->ID, '_nh_pq_goal', true);
    $length    = (string) get_post_meta($post->ID, '_nh_pq_length', true);
    $current   = (string) get_post_meta($post->ID, '_nh_pq_current_ext', true);
    $photos_m  = (string) get_post_meta($post->ID, '_nh_pq_photos_mode', true);
    $photo_ids = array_filter(array_map(
        'intval',
        explode(',', (string) get_post_meta($post->ID, '_nh_pq_photo_ids', true))
    ));
    $submitted = (string) get_post_meta($post->ID, '_nh_pq_submitted_at', true);
    $ip        = (string) get_post_meta($post->ID, '_nh_pq_user_ip', true);

    $goal_str = is_array($goal) ? implode(', ', array_map('strval', $goal)) : '';
    ?>
    <table class="form-table" role="presentation">
        <tbody>
            <tr>
                <th scope="row"><?php esc_html_e('Источник', 'nice-hair'); ?></th>
                <td><?php esc_html_e('Квиз', 'nice-hair'); ?></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Имя', 'nice-hair'); ?></th>
                <td><?php echo esc_html($name); ?></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('WhatsApp', 'nice-hair'); ?></th>
                <td>
                    <?php if ($whatsapp !== '') : ?>
                        <a href="https://wa.me/<?php echo esc_attr($whatsapp); ?>" target="_blank" rel="noopener">
                            <?php echo esc_html($whatsapp); ?>
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Цель', 'nice-hair'); ?></th>
                <td><?php echo esc_html($goal_str); ?></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Желаемая длина', 'nice-hair'); ?></th>
                <td><?php echo esc_html($length); ?></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Уже носит наращивание', 'nice-hair'); ?></th>
                <td><?php echo esc_html($current); ?></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Фото', 'nice-hair'); ?></th>
                <td>
                    <?php if ($photos_m === 'uploaded' && ! empty($photo_ids)) : ?>
                        <div style="display:flex;flex-wrap:wrap;gap:10px;">
                            <?php foreach ($photo_ids as $id) :
                                $thumb = wp_get_attachment_image($id, [160, 160], false, [
                                    'style' => 'border-radius:6px;object-fit:cover;',
                                ]);
                                $full  = wp_get_attachment_url($id);
                                if (! $thumb || ! $full) {
                                    continue;
                                }
                                ?>
                                <a href="<?php echo esc_url($full); ?>" target="_blank" rel="noopener">
                                    <?php echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($photos_m === 'skipped') : ?>
                        <em><?php esc_html_e('Клиент выбрал консультацию без фото', 'nice-hair'); ?></em>
                    <?php else : ?>
                        <em><?php esc_html_e('Нет данных о фото', 'nice-hair'); ?></em>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Отправлено', 'nice-hair'); ?></th>
                <td><?php echo esc_html($submitted); ?></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('IP клиента', 'nice-hair'); ?></th>
                <td><code><?php echo esc_html($ip); ?></code></td>
            </tr>
        </tbody>
    </table>
    <?php
}

function nice_hair_price_quiz_delete_photos(int $post_id): void
{
    if (get_post_type($post_id) !== 'nh_price_quiz') {
        return;
    }

    $photo_ids = array_filter(array_map(
        'intval',
        explode(',', (string) get_post_meta($post_id, '_nh_pq_photo_ids', true))
    ));

    foreach ($photo_ids as $id) {
        wp_delete_attachment($id, true);
    }
}
add_action('before_delete_post', 'nice_hair_price_quiz_delete_photos');

function nice_hair_submissions_include_popup_salon(WP_Query $query): void
{
    if (! is_admin() || ! $query->is_main_query()) {
        return;
    }

    global $pagenow;

    $post_type = isset($_GET['post_type']) && is_string($_GET['post_type'])
        ? sanitize_key(wp_unslash($_GET['post_type']))
        : '';

    if ($pagenow !== 'edit.php' || $post_type !== 'nh_price_quiz') {
        return;
    }

    $query->set('nh_unified_submissions', true);
}
add_action('pre_get_posts', 'nice_hair_submissions_include_popup_salon');

function nice_hair_submissions_expand_query_where(string $where, WP_Query $query): string
{
    if (! is_admin() || ! $query->is_main_query() || ! $query->get('nh_unified_submissions')) {
        return $where;
    }

    global $wpdb;

    $needle = $wpdb->prepare("{$wpdb->posts}.post_type = %s", 'nh_price_quiz');

    if (strpos($where, $needle) === false) {
        return $where;
    }

    return str_replace(
        $needle,
        "{$wpdb->posts}.post_type IN ('nh_price_quiz', 'nh_salon_request')",
        $where
    );
}
add_filter('posts_where', 'nice_hair_submissions_expand_query_where', 10, 2);

function nice_hair_submissions_post_counts(): array
{
    $counts = [
        'all' => 0,
        'publish' => 0,
        'trash' => 0,
    ];

    foreach (['nh_price_quiz', 'nh_salon_request'] as $post_type) {
        $post_counts = get_object_vars(wp_count_posts($post_type));

        foreach ($post_counts as $status => $count) {
            $count = (int) $count;

            if ($count <= 0) {
                continue;
            }

            if ($status === 'publish') {
                $counts['publish'] += $count;
            }

            if ($status === 'trash') {
                $counts['trash'] += $count;
            } elseif ($status !== 'auto-draft') {
                $counts['all'] += $count;
            }
        }
    }

    return $counts;
}

function nice_hair_submissions_admin_views(array $views): array
{
    $counts = nice_hair_submissions_post_counts();
    $current_status = isset($_GET['post_status']) && is_string($_GET['post_status'])
        ? sanitize_key(wp_unslash($_GET['post_status']))
        : 'all';

    $base_url = admin_url('edit.php?post_type=nh_price_quiz');
    $new_views = [];

    $new_views['all'] = sprintf(
        '<a href="%1$s" class="%2$s">%3$s <span class="count">(%4$s)</span></a>',
        esc_url($base_url),
        $current_status === 'all' ? 'current' : '',
        esc_html__('Все', 'nice-hair'),
        number_format_i18n($counts['all'])
    );

    $new_views['publish'] = sprintf(
        '<a href="%1$s&post_status=publish" class="%2$s">%3$s <span class="count">(%4$s)</span></a>',
        esc_url($base_url),
        $current_status === 'publish' ? 'current' : '',
        esc_html__('Опубликованные', 'nice-hair'),
        number_format_i18n($counts['publish'])
    );

    if ($counts['trash'] > 0) {
        $new_views['trash'] = sprintf(
            '<a href="%1$s&post_status=trash" class="%2$s">%3$s <span class="count">(%4$s)</span></a>',
            esc_url($base_url),
            $current_status === 'trash' ? 'current' : '',
            esc_html__('Корзина', 'nice-hair'),
            number_format_i18n($counts['trash'])
        );
    }

    return $new_views;
}
add_filter('views_edit-nh_price_quiz', 'nice_hair_submissions_admin_views');

function nice_hair_submissions_parent_file(?string $parent_file): ?string
{
    global $pagenow, $post;

    if ($pagenow === 'post.php' && $post instanceof WP_Post && in_array($post->post_type, ['nh_price_quiz', 'nh_salon_request'], true)) {
        return 'edit.php?post_type=nh_price_quiz';
    }

    return $parent_file;
}
add_filter('parent_file', 'nice_hair_submissions_parent_file');

function nice_hair_submissions_submenu_file(?string $submenu_file): ?string
{
    global $pagenow, $post;

    if ($pagenow === 'post.php' && $post instanceof WP_Post && in_array($post->post_type, ['nh_price_quiz', 'nh_salon_request'], true)) {
        return 'edit.php?post_type=nh_price_quiz';
    }

    return $submenu_file;
}
add_filter('submenu_file', 'nice_hair_submissions_submenu_file');

function nice_hair_price_quiz_remove_custom_fields_meta_box(): void
{
    remove_meta_box('titlediv', 'nh_price_quiz', 'normal');
    remove_meta_box('titlediv', 'nh_salon_request', 'normal');
    remove_meta_box('slugdiv', 'nh_price_quiz', 'normal');
    remove_meta_box('slugdiv', 'nh_salon_request', 'normal');
    remove_meta_box('postcustom', 'nh_price_quiz', 'normal');
    remove_meta_box('postcustom', 'nh_salon_request', 'normal');
}
add_action('admin_menu', 'nice_hair_price_quiz_remove_custom_fields_meta_box');
