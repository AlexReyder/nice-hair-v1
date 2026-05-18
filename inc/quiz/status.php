<?php

declare(strict_types=1);

/**
 * Submission status model for `nh_price_quiz` and `nh_salon_request`.
 *
 * The status belongs to the submissions domain, not to the access plugin.
 * Access plugin only grants capabilities; the theme owns the status field,
 * status labels and admin rendering.
 */

function nice_hair_submission_post_types(): array
{
    return ['nh_price_quiz', 'nh_salon_request'];
}

function nice_hair_submission_statuses(): array
{
    return [
        'new'            => __('Новая', 'nice-hair'),
        'in_progress'    => __('В работе', 'nice-hair'),
        'closed_success' => __('Закрыта успешно', 'nice-hair'),
        'closed_lost'    => __('Отказ / неактуально', 'nice-hair'),
    ];
}

function nice_hair_normalize_submission_status(string $status): string
{
    $status = sanitize_key($status);

    return array_key_exists($status, nice_hair_submission_statuses()) ? $status : 'new';
}

function nice_hair_submission_status_label(string $status): string
{
    $status = nice_hair_normalize_submission_status($status);
    $statuses = nice_hair_submission_statuses();

    return (string) ($statuses[$status] ?? $statuses['new']);
}

function nice_hair_get_submission_status(int $post_id): string
{
    $status = (string) get_post_meta($post_id, 'nh_submission_status', true);

    /**
     * Backward compatibility with older plugin versions that stored the status
     * in `_nh_submission_status`. ACF itself uses underscore meta for field refs,
     * so all new writes go to `nh_submission_status`.
     */
    if ($status === '') {
        $legacy_status = (string) get_post_meta($post_id, '_nh_submission_status', true);

        if ($legacy_status !== '' && strpos($legacy_status, 'field_') !== 0) {
            $status = $legacy_status;
        }
    }

    return nice_hair_normalize_submission_status($status);
}

function nice_hair_user_can_manage_submission_statuses(): bool
{
    return current_user_can('nh_manage_submission_statuses')
        || current_user_can('nh_edit_submissions')
        || current_user_can('manage_options');
}

function nice_hair_submission_post_type_capabilities(): array
{
    return [
        'edit_post'              => 'nh_edit_submission',
        'read_post'              => 'nh_read_submission',
        'delete_post'            => 'nh_delete_submission',
        'edit_posts'             => 'nh_view_submissions',
        'edit_others_posts'      => 'nh_view_submissions',
        'edit_private_posts'     => 'nh_view_submissions',
        'edit_published_posts'   => 'nh_view_submissions',
        'read_private_posts'     => 'nh_view_submissions',
        'delete_posts'           => 'nh_delete_submissions',
        'delete_others_posts'    => 'nh_delete_submissions',
        'delete_private_posts'   => 'nh_delete_submissions',
        'delete_published_posts' => 'nh_delete_submissions',
        'publish_posts'          => 'do_not_allow',
        'create_posts'           => 'do_not_allow',
    ];
}

function nice_hair_map_submission_meta_caps(array $caps, string $cap, int $user_id, array $args): array
{
    if (! in_array($cap, ['nh_edit_submission', 'nh_read_submission', 'nh_delete_submission'], true)) {
        return $caps;
    }

    $post_id = isset($args[0]) ? (int) $args[0] : 0;
    $post_type = $post_id > 0 ? (string) get_post_type($post_id) : '';

    if (! in_array($post_type, nice_hair_submission_post_types(), true)) {
        return ['do_not_allow'];
    }

    if ($cap === 'nh_delete_submission') {
        return ['nh_delete_submissions'];
    }

    return ['nh_view_submissions'];
}
add_filter('map_meta_cap', 'nice_hair_map_submission_meta_caps', 10, 4);

function nice_hair_grant_submission_caps_to_administrators(): void
{
    $administrator = get_role('administrator');

    if (! $administrator) {
        return;
    }

    foreach (['nh_view_submissions', 'nh_manage_submission_statuses', 'nh_delete_submissions'] as $capability) {
        $administrator->add_cap($capability, true);
    }
}
add_action('admin_init', 'nice_hair_grant_submission_caps_to_administrators', 5);

function nice_hair_register_submission_status_acf_field(): void
{
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key'                   => 'group_nh_submission_status',
        'title'                 => __('Статус заявки', 'nice-hair'),
        'fields'                => [
            [
                'key'           => 'field_nh_submission_status',
                'label'         => __('Статус заявки', 'nice-hair'),
                'name'          => 'nh_submission_status',
                'type'          => 'select',
                'choices'       => nice_hair_submission_statuses(),
                'default_value' => 'new',
                'return_format' => 'value',
                'ui'            => 1,
                'allow_null'    => 0,
            ],
        ],
        'location'              => [
            [
                [
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'nh_price_quiz',
                ],
            ],
            [
                [
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'nh_salon_request',
                ],
            ],
        ],
        'position'              => 'side',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,
    ]);
}
add_action('acf/init', 'nice_hair_register_submission_status_acf_field', 40);

function nice_hair_submission_status_load_value(mixed $value, mixed $post_id, array $field): string
{
    $resolved_post_id = is_numeric($post_id) ? (int) $post_id : 0;

    if ($resolved_post_id <= 0 || ! in_array((string) get_post_type($resolved_post_id), nice_hair_submission_post_types(), true)) {
        return nice_hair_normalize_submission_status((string) $value);
    }

    if (is_string($value) && $value !== '') {
        return nice_hair_normalize_submission_status($value);
    }

    return nice_hair_get_submission_status($resolved_post_id);
}
add_filter('acf/load_value/name=nh_submission_status', 'nice_hair_submission_status_load_value', 10, 3);

function nice_hair_submission_status_prepare_field(array|false $field): array|false
{
    if (! is_array($field)) {
        return $field;
    }

    if (nice_hair_user_can_manage_submission_statuses()) {
        return $field;
    }

    $field['disabled'] = 1;
    $field['instructions'] = __('У вашей роли нет права менять статус заявки.', 'nice-hair');

    return $field;
}
add_filter('acf/prepare_field/name=nh_submission_status', 'nice_hair_submission_status_prepare_field');

function nice_hair_submission_status_update_value(mixed $value, mixed $post_id, array $field): string
{
    $resolved_post_id = is_numeric($post_id) ? (int) $post_id : 0;

    if ($resolved_post_id <= 0 || ! in_array((string) get_post_type($resolved_post_id), nice_hair_submission_post_types(), true)) {
        return nice_hair_normalize_submission_status((string) $value);
    }

    $old_status = nice_hair_get_submission_status($resolved_post_id);

    if (! nice_hair_user_can_manage_submission_statuses()) {
        return $old_status;
    }

    $new_status = nice_hair_normalize_submission_status((string) $value);

    if ($new_status !== $old_status) {
        update_post_meta($resolved_post_id, 'nh_submission_status_updated_at', current_time('mysql', true));
        update_post_meta($resolved_post_id, 'nh_submission_status_updated_by', get_current_user_id());
    }

    return $new_status;
}
add_filter('acf/update_value/name=nh_submission_status', 'nice_hair_submission_status_update_value', 10, 3);
