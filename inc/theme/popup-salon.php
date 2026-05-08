<?php

declare(strict_types=1);

function nice_hair_popup_salon_settings_post_id(): string
{
    return 'nh_popup_salon_settings';
}

function nice_hair_get_popup_salon_setting(string $field_name, mixed $fallback = null): mixed
{
    if (! function_exists('get_field')) {
        return $fallback;
    }

    $value = get_field($field_name, nice_hair_popup_salon_settings_post_id());

    return nice_hair_acf_has_value($value) ? $value : $fallback;
}

function nice_hair_is_salon_page(): bool
{
    if (! is_page()) {
        return false;
    }

    $object_id = get_queried_object_id();

    if ($object_id <= 0) {
        return false;
    }

    if (get_page_template_slug($object_id) === 'page-templates/page-salon.php') {
        return true;
    }

    $post = get_post($object_id);

    return $post instanceof WP_Post && $post->post_name === 'salon';
}

function nice_hair_get_popup_salon_default_form_title_html(): string
{
    return sprintf(
        '<p class="nh-popup-salon__form-title">%s</p>',
        esc_html(nice_hair_get_popup_salon_default_form_title_text())
    );
}

function nice_hair_get_popup_salon_default_disclaimer_shell_html(): string
{
    return sprintf(
        '<p class="nh-popup-salon__disclaimer">%s</p>',
        wp_kses_post(nice_hair_get_popup_salon_default_disclaimer_html())
    );
}

function nice_hair_popup_salon_dom_class_query(string $class_name): string
{
    return sprintf(
        ".//*[contains(concat(' ', normalize-space(@class), ' '), ' %s ')]",
        esc_attr($class_name)
    );
}

function nice_hair_popup_salon_dom_outer_html(DOMNode $node): string
{
    $document = $node->ownerDocument;

    if (! $document instanceof DOMDocument) {
        return '';
    }

    $html = $document->saveHTML($node);

    return is_string($html) ? trim($html) : '';
}

function nice_hair_popup_salon_dom_inner_html(DOMNode $node): string
{
    $document = $node->ownerDocument;

    if (! $document instanceof DOMDocument) {
        return '';
    }

    $html = '';

    foreach ($node->childNodes as $child_node) {
        $child_html = $document->saveHTML($child_node);

        if (is_string($child_html)) {
            $html .= $child_html;
        }
    }

    return trim($html);
}

function nice_hair_popup_salon_prepare_contract_html(DOMElement $node, string $source_class, string $render_class): string
{
    $clone = $node->cloneNode(true);

    if (! $clone instanceof DOMElement) {
        return '';
    }

    $class_name = trim((string) $clone->getAttribute('class'));
    $classes = $class_name !== '' ? preg_split('/\s+/', $class_name) : [];

    if (! is_array($classes)) {
        $classes = [];
    }

    $classes = array_values(array_filter($classes, static fn ($class): bool => is_string($class) && $class !== '' && $class !== $source_class));

    array_unshift($classes, $render_class);
    $clone->setAttribute('class', implode(' ', array_unique($classes)));

    return nice_hair_popup_salon_dom_outer_html($clone);
}

function nice_hair_popup_salon_extract_render_parts(string $content_html): array
{
    $parts = [
        'content_html' => $content_html,
        'form_title_html' => '',
        'disclaimer_html' => '',
    ];

    if ($content_html === '' || ! class_exists('DOMDocument')) {
        return $parts;
    }

    $document = new DOMDocument('1.0', 'UTF-8');
    $previous_internal_errors = libxml_use_internal_errors(true);
    $loaded = $document->loadHTML(
        '<?xml encoding="utf-8" ?><div id="nh-popup-salon-root">' . $content_html . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();
    libxml_use_internal_errors($previous_internal_errors);

    if (! $loaded) {
        return $parts;
    }

    $xpath = new DOMXPath($document);
    $root_node = $xpath->query("//*[@id='nh-popup-salon-root']")->item(0);

    if (! $root_node instanceof DOMElement) {
        return $parts;
    }

    $form_title_node = $xpath
        ->query(nice_hair_popup_salon_dom_class_query(nice_hair_popup_salon_form_title_content_class()), $root_node)
        ->item(0);

    if ($form_title_node instanceof DOMElement) {
        $parts['form_title_html'] = nice_hair_popup_salon_prepare_contract_html(
            $form_title_node,
            nice_hair_popup_salon_form_title_content_class(),
            'nh-popup-salon__form-title'
        );

        if ($form_title_node->parentNode instanceof DOMNode) {
            $form_title_node->parentNode->removeChild($form_title_node);
        }
    }

    $disclaimer_node = $xpath
        ->query(nice_hair_popup_salon_dom_class_query(nice_hair_popup_salon_disclaimer_content_class()), $root_node)
        ->item(0);

    if ($disclaimer_node instanceof DOMElement) {
        $parts['disclaimer_html'] = nice_hair_popup_salon_prepare_contract_html(
            $disclaimer_node,
            nice_hair_popup_salon_disclaimer_content_class(),
            'nh-popup-salon__disclaimer'
        );

        if ($disclaimer_node->parentNode instanceof DOMNode) {
            $disclaimer_node->parentNode->removeChild($disclaimer_node);
        }
    }

    $parts['content_html'] = nice_hair_popup_salon_dom_inner_html($root_node);

    return $parts;
}

function nice_hair_get_popup_salon_render_parts(): array
{
    static $render_parts = null;

    if (is_array($render_parts)) {
        return $render_parts;
    }

    $render_parts = [
        'content_html' => '',
        'form_title_html' => '',
        'disclaimer_html' => '',
    ];

    $post_id = nice_hair_get_popup_salon_post_id(false);

    if ($post_id <= 0) {
        return $render_parts;
    }

    $post = get_post($post_id);

    if (! $post instanceof WP_Post || $post->post_type !== 'nh_popup_salon' || $post->post_status === 'trash') {
        return $render_parts;
    }

    $content = trim((string) $post->post_content);

    if ($content === '') {
        return $render_parts;
    }

    $html = apply_filters('the_content', $content);
    $content_html = is_string($html) ? trim($html) : '';

    if ($content_html === '') {
        return $render_parts;
    }

    $has_form_title_contract = str_contains($content, nice_hair_popup_salon_form_title_content_class());
    $has_disclaimer_contract = str_contains($content, nice_hair_popup_salon_disclaimer_content_class());

    if (! $has_form_title_contract && ! $has_disclaimer_contract) {
        $render_parts['content_html'] = $content_html;
        $render_parts['form_title_html'] = nice_hair_get_popup_salon_default_form_title_html();
        $render_parts['disclaimer_html'] = nice_hair_get_popup_salon_default_disclaimer_shell_html();

        return $render_parts;
    }

    $render_parts = nice_hair_popup_salon_extract_render_parts($content_html);

    return $render_parts;
}

function nice_hair_should_render_popup_salon(): bool
{
    static $should_render = null;

    if (is_bool($should_render)) {
        return $should_render;
    }

    if (is_admin() || is_feed() || (function_exists('wp_is_json_request') && wp_is_json_request())) {
        $should_render = false;

        return $should_render;
    }

    if (! nice_hair_is_salon_page()) {
        $should_render = false;

        return $should_render;
    }

    $should_render = nice_hair_get_popup_salon_render_parts()['content_html'] !== '';

    return $should_render;
}

function nice_hair_render_popup_salon(): void
{
    if (! nice_hair_should_render_popup_salon()) {
        return;
    }

    $render_parts = nice_hair_get_popup_salon_render_parts();
    $content_html = $render_parts['content_html'];

    if ($content_html === '') {
        return;
    }
    ?>
    <div
        id="nh-popup-salon"
        class="nh-popup-salon"
        data-nh-popup-salon
        data-default-label="<?php echo esc_attr__('Book an appointment', 'nice-hair'); ?>"
        data-endpoint="<?php echo esc_url(rest_url('nice-hair/v1/popup-salon')); ?>"
        data-nonce="<?php echo esc_attr(wp_create_nonce('wp_rest')); ?>"
        hidden
    >
        <div class="nh-popup-salon__backdrop" data-nh-popup-salon-close></div>

        <div
            class="nh-popup-salon__dialog"
            role="dialog"
            aria-modal="true"
            aria-label="<?php echo esc_attr__('Book an appointment', 'nice-hair'); ?>"
        >
            <button
                type="button"
                class="nh-popup-salon__close"
                data-nh-popup-salon-close
                aria-label="<?php echo esc_attr__('Close popup', 'nice-hair'); ?>"
            >
                <span aria-hidden="true">&times;</span>
            </button>

            <div class="nh-popup-salon__surface">
                <div class="nh-popup-salon__content">
                    <?php echo $content_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>

                <div class="nh-popup-salon__panel">
                    <form class="nh-popup-salon__form" data-nh-popup-salon-form novalidate>
                        <?php
                        echo $render_parts['form_title_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        ?>

                        <input type="hidden" name="popup_label" value="<?php echo esc_attr__('Book an appointment', 'nice-hair'); ?>">
                        <input type="hidden" name="page_url" value="">

                        <label class="nh-popup-salon__field">
                            <span class="nh-popup-salon__field-label"><?php esc_html_e('NAME', 'nice-hair'); ?></span>
                            <input
                                type="text"
                                name="name"
                                class="nh-popup-salon__input"
                                autocomplete="name"
                                required
                            >
                        </label>

                        <label class="nh-popup-salon__field">
                            <span class="nh-popup-salon__field-label"><?php esc_html_e('PHONE NUMBER', 'nice-hair'); ?></span>
                            <input
                                type="tel"
                                name="phone"
                                class="nh-popup-salon__input"
                                autocomplete="tel"
                                required
                            >
                        </label>

                        <input
                            type="text"
                            name="hp_field"
                            class="nh-popup-salon__hp"
                            autocomplete="off"
                            tabindex="-1"
                            aria-hidden="true"
                        >

                        <div class="nh-popup-salon__actions">
                            <button type="submit" class="nh-popup-salon__submit" data-nh-popup-salon-submit>
                                <span class="nh-popup-salon__submit-label"><?php esc_html_e('SEND', 'nice-hair'); ?></span>
                            </button>

                            <?php
                            echo $render_parts['disclaimer_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            ?>
                        </div>

                        <p class="nh-popup-salon__hint" data-nh-popup-salon-hint hidden></p>
                    </form>

                    <div class="nh-popup-salon__success" data-nh-popup-salon-success hidden>
                        <div class="nh-popup-salon__success-body">
                            <svg class="nh-popup-salon__success-icon" width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <circle cx="32" cy="32" r="31" stroke="currentColor" stroke-width="2"/>
                                <path d="M20 33L28 41L45 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>

                            <div class="nh-popup-salon__success-copy">
                                <p class="nh-popup-salon__success-text" data-nh-popup-salon-success-message>
                                    <?php
                                    echo wp_kses_post(
                                        nl2br((string) nice_hair_get_popup_salon_setting(
                                            'nh_popup_salon_success_message',
                                            "Thanks! Your request has been sent.\nOur stylist will contact you shortly via WhatsApp."
                                        ))
                                    );
                                    ?>
                                </p>

                                <a
                                    class="nh-popup-salon__success-link"
                                    data-nh-popup-salon-whatsapp
                                    href="#"
                                    target="_blank"
                                    rel="noopener"
                                    hidden
                                >
                                    <?php esc_html_e('Open WhatsApp', 'nice-hair'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
add_action('wp_footer', 'nice_hair_render_popup_salon', 25);
