<?php

declare(strict_types=1);

function nice_hair_enqueue_editor_content_assets(): void
{
    if (! is_admin()) {
        return;
    }

    if (nice_hair_is_vite_dev_server_running()) {
        nice_hair_enqueue_vite_dev_style('nice-hair-editor-style', 'assets/scss/editor.scss');
        return;
    }

    if (file_exists(nice_hair_vite_manifest_path())) {
        nice_hair_enqueue_vite_style_entry('nice-hair-editor-style', 'assets/scss/editor.scss');
    }
}

add_action('enqueue_block_assets', 'nice_hair_enqueue_editor_content_assets');