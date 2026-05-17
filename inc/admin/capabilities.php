<?php

declare(strict_types=1);

/**
 * Centralized custom capabilities for Nice Hair admin zones.
 *
 * This file only defines and registers capabilities. It does not create
 * restricted manager roles yet. Roles will be configured in a separate step.
 */

function nice_hair_submission_cpt_capabilities(): array
{
    return [
        'read'                   => 'read_nh_submissions',

        'edit_post'              => 'edit_nh_submission',
        'read_post'              => 'read_nh_submission',
        'delete_post'            => 'delete_nh_submission',

        'edit_posts'             => 'edit_nh_submissions',
        'edit_others_posts'      => 'edit_others_nh_submissions',
        'edit_private_posts'     => 'edit_private_nh_submissions',
        'edit_published_posts'   => 'edit_published_nh_submissions',

        'publish_posts'          => 'publish_nh_submissions',
        'read_private_posts'     => 'read_private_nh_submissions',

        'delete_posts'           => 'delete_nh_submissions',
        'delete_private_posts'   => 'delete_private_nh_submissions',
        'delete_published_posts' => 'delete_published_nh_submissions',
        'delete_others_posts'    => 'delete_others_nh_submissions',

        'create_posts'           => 'do_not_allow',
    ];
}

function nice_hair_submission_custom_capabilities(): array
{
    $capabilities = array_values(nice_hair_submission_cpt_capabilities());

    $capabilities[] = 'manage_nh_submission_statuses';

    return array_values(array_unique(array_filter(
        $capabilities,
        static fn (string $capability): bool => $capability !== '' && $capability !== 'do_not_allow'
    )));
}

function nice_hair_submission_manager_capabilities(): array
{
    return [
        'read',

        'read_nh_submission',
        'read_nh_submissions',

        'edit_nh_submission',
        'edit_nh_submissions',
        'edit_others_nh_submissions',
        'edit_private_nh_submissions',
        'edit_published_nh_submissions',

        'read_private_nh_submissions',

        'manage_nh_submission_statuses',
    ];
}

function nice_hair_ensure_admin_has_submission_capabilities(): void
{
    $administrator = get_role('administrator');

    if (! $administrator instanceof WP_Role) {
        return;
    }

    foreach (nice_hair_submission_custom_capabilities() as $capability) {
        $administrator->add_cap($capability);
    }
}
add_action('admin_init', 'nice_hair_ensure_admin_has_submission_capabilities');