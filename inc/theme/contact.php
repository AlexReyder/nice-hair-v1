<?php

declare(strict_types=1);

function nice_hair_contact_post_id(): string
{
    return 'nh_contacts';
}

function nice_hair_get_contact_context(string $context = ''): string
{
    if (in_array($context, ['home', 'salon', 'shop'], true)) {
        return $context;
    }

    return nice_hair_get_current_section('home');
}

function nice_hair_get_phone_link_value(string $value): string
{
    $value = trim($value);

    if ($value === '') {
        return '';
    }

    $has_plus = str_starts_with($value, '+');
    $digits = preg_replace('/\D+/', '', $value) ?? '';

    if ($digits === '') {
        return '';
    }

    return ($has_plus ? '+' : '') . $digits;
}

function nice_hair_contact_strip_outer_brackets(string $value): string
{
    $value = trim($value);

    if (preg_match('/^\[(.*)\]$/s', $value, $matches) === 1) {
        return trim((string) $matches[1]);
    }

    return $value;
}

function nice_hair_contact_normalize_compare_text(string $value): string
{
    $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $value = str_replace(["\r\n", "\r", "\n", "\t", "\xc2\xa0"], ' ', $value);
    $value = preg_replace('/<br\s*\/?>/i', ' ', $value) ?? $value;
    $value = wp_strip_all_tags($value);
    $value = str_replace(['—', '–'], '-', $value);
    $value = preg_replace('/\s+/', ' ', $value) ?? $value;

    return trim($value);
}

function nice_hair_contact_normalize_href(string $value): string
{
    $value = trim(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

    if (str_starts_with($value, 'tel:')) {
        return 'tel:' . nice_hair_get_phone_link_value(substr($value, 4));
    }

    if ($value === '') {
        return '';
    }

    return rtrim($value, '/');
}

function nice_hair_contact_normalize_address_plain(string $value): string
{
    $value = nice_hair_contact_strip_outer_brackets($value);
    $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $value = preg_replace('/<br\s*\/?>/i', ', ', $value) ?? $value;
    $value = str_replace(["\r\n", "\r", "\n"], ', ', $value);
    $value = wp_strip_all_tags($value);
    $value = preg_replace('/\s*,\s*/', ', ', $value) ?? $value;
    $value = preg_replace('/\s+/', ' ', $value) ?? $value;
    $value = preg_replace('/,\s*,+/', ', ', $value) ?? $value;

    return trim($value, " \t\n\r\0\x0B,");
}

function nice_hair_contact_normalize_address_display(string $value): string
{
    $value = nice_hair_contact_strip_outer_brackets($value);
    $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $value = str_replace(["\r\n", "\r"], "\n", $value);
    $value = trim($value);

    if ($value === '') {
        return '';
    }

    if (preg_match('/<br\s*\/?>/i', $value) === 1) {
        return $value;
    }

    if (str_contains($value, "\n")) {
        return nl2br($value, false);
    }

    return nice_hair_contact_normalize_address_plain($value);
}

function nice_hair_contact_normalize_field_value(string $field_name, mixed $value): mixed
{
    if (! is_string($value)) {
        return $value;
    }

    return match ($field_name) {
        'nh_contact_phone_link' => nice_hair_get_phone_link_value($value),
        'nh_contact_address_plain' => nice_hair_contact_normalize_address_plain($value),
        'nh_contact_address_display' => nice_hair_contact_normalize_address_display($value),
        'nh_contact_whatsapp_url',
        'nh_contact_instagram_url',
        'nh_contact_telegram_url',
        'nh_contact_map_url' => trim($value),
        default => trim($value),
    };
}

function nice_hair_get_contact_option_field(string $field_name, mixed $fallback = null): mixed
{
    if (! function_exists('get_field')) {
        return $fallback;
    }

    $value = get_field($field_name, nice_hair_contact_post_id());

    if (nice_hair_acf_has_value($value)) {
        return nice_hair_contact_normalize_field_value($field_name, $value);
    }

    return $fallback;
}





function nice_hair_get_contact_field(string $field_name, string $context = '', mixed $fallback = null): mixed
{
    $option_value = nice_hair_get_contact_option_field($field_name, null);

    if (nice_hair_acf_has_value($option_value)) {
        return $option_value;
    }

    return $fallback;
}

function nice_hair_get_contact_phone_display(string $context = '', string $fallback = '+971 58 598 8409'): string
{
    return (string) nice_hair_get_contact_field('nh_contact_phone_display', $context, $fallback);
}

function nice_hair_get_contact_phone_link(string $context = '', string $fallback = '+971585988409'): string
{
    return (string) nice_hair_get_contact_field('nh_contact_phone_link', $context, $fallback);
}

function nice_hair_get_contact_address_display(
    string $context = '',
    string $fallback = "Al Noor st, Al Sufouh,<br>Al Sufouh 1, Dubai"
): string {
    return (string) nice_hair_get_contact_field('nh_contact_address_display', $context, $fallback);
}

function nice_hair_get_contact_address_plain(
    string $context = '',
    string $fallback = 'Al Noor st, Al Sufouh, Al Sufouh 1, Dubai'
): string {
    return (string) nice_hair_get_contact_field('nh_contact_address_plain', $context, $fallback);
}

function nice_hair_get_contact_hours(string $context = '', string $fallback = "We're open daily: 10 AM - 10 PM"): string
{
    return (string) nice_hair_get_contact_field('nh_contact_working_hours', $context, $fallback);
}

function nice_hair_get_contact_hours_compact(string $context = '', string $fallback = '10 AM - 10 PM'): string
{
    $hours = nice_hair_get_contact_hours($context, $fallback);
    $hours = preg_replace('/^we[\'’]re open daily:\s*/i', '', $hours) ?? $hours;
    $hours = preg_replace('/^open daily:\s*/i', '', $hours) ?? $hours;

    return trim($hours);
}

function nice_hair_get_contact_social_url(string $platform, string $context = '', string $fallback = '#'): string
{
    $field_name = match ($platform) {
        'telegram' => 'nh_contact_telegram_url',
        'instagram' => 'nh_contact_instagram_url',
        default => 'nh_contact_whatsapp_url',
    };

    return (string) nice_hair_get_contact_field($field_name, $context, $fallback);
}

function nice_hair_get_contact_map_url(string $fallback = 'https://maps.google.com/?q=Al+Sufouh+Dubai'): string
{
    return (string) nice_hair_get_contact_field('nh_contact_map_url', 'home', $fallback);
}

function nice_hair_contact_wrap_brackets(string $value): string
{
    $value = nice_hair_contact_normalize_address_plain($value);

    if ($value === '') {
        return '';
    }

    return '[' . $value . ']';
}

function nice_hair_build_whatsapp_url(
    string $message = '',
    string $context = '',
    string $fallback = 'https://wa.me/971585988409'
): string {
    $base_url = nice_hair_get_contact_social_url('whatsapp', $context, $fallback);

    if (trim($message) === '') {
        return $base_url;
    }

    return (string) add_query_arg('text', $message, $base_url);
}

function nice_hair_contact_html_fragment(string $html, callable $callback): string
{
    if ($html === '' || ! class_exists('DOMDocument')) {
        return $html;
    }

    $internal_errors = libxml_use_internal_errors(true);

    $document = new DOMDocument('1.0', 'UTF-8');
    $loaded = $document->loadHTML(
        '<?xml encoding="utf-8" ?><div id="nh-contact-root">' . $html . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );

    if ($loaded === false) {
        libxml_clear_errors();
        libxml_use_internal_errors($internal_errors);

        return $html;
    }

    $xpath = new DOMXPath($document);
    $callback($document, $xpath);

    $root = $document->getElementById('nh-contact-root');

    if (! $root instanceof DOMElement) {
        libxml_clear_errors();
        libxml_use_internal_errors($internal_errors);

        return $html;
    }

    $updated_html = '';

    foreach ($root->childNodes as $child_node) {
        $updated_html .= $document->saveHTML($child_node);
    }

    libxml_clear_errors();
    libxml_use_internal_errors($internal_errors);

    return $updated_html;
}

function nice_hair_contact_dom_class_query(string $class_name): string
{
    return "contains(concat(' ', normalize-space(@class), ' '), ' {$class_name} ')";
}

function nice_hair_contact_dom_query_all(DOMXPath $xpath, string $class_name, string $tag_name = '*'): DOMNodeList|false
{
    $query = sprintf('//%s[%s]', $tag_name, nice_hair_contact_dom_class_query($class_name));

    return $xpath->query($query);
}

function nice_hair_contact_dom_query_first(DOMXPath $xpath, string $class_name, string $tag_name = '*'): ?DOMElement
{
    $nodes = nice_hair_contact_dom_query_all($xpath, $class_name, $tag_name);

    if (! $nodes instanceof DOMNodeList || $nodes->length === 0) {
        return null;
    }

    $element = $nodes->item(0);

    return $element instanceof DOMElement ? $element : null;
}

function nice_hair_contact_dom_first_anchor(DOMElement $element): ?DOMElement
{
    if (strtolower($element->tagName) === 'a') {
        return $element;
    }

    foreach ($element->getElementsByTagName('a') as $anchor) {
        if ($anchor instanceof DOMElement) {
            return $anchor;
        }
    }

    return null;
}

function nice_hair_contact_dom_remove_children(DOMElement $element): void
{
    while ($element->firstChild !== null) {
        $element->removeChild($element->firstChild);
    }
}

function nice_hair_contact_dom_set_text(DOMDocument $document, DOMElement $element, string $text): void
{
    nice_hair_contact_dom_remove_children($element);
    $element->appendChild($document->createTextNode($text));
}

function nice_hair_contact_dom_set_text_with_breaks(DOMDocument $document, DOMElement $element, string $text): void
{
    nice_hair_contact_dom_remove_children($element);

    $parts = preg_split('/<br\s*\/?>/i', $text) ?: [$text];

    foreach ($parts as $index => $part) {
        if ($index > 0) {
            $element->appendChild($document->createElement('br'));
        }

        $element->appendChild(
            $document->createTextNode(html_entity_decode((string) $part, ENT_QUOTES | ENT_HTML5, 'UTF-8'))
        );
    }
}

function nice_hair_contact_dom_set_shared_contact_description(
    DOMDocument $document,
    DOMElement $element,
    string $compact_hours
): void {
    nice_hair_contact_dom_remove_children($element);
    $element->appendChild($document->createTextNode("We're always "));

    $strong = $document->createElement('strong');
    $strong->appendChild($document->createTextNode('online ' . $compact_hours));
    $element->appendChild($strong);

    $element->appendChild(
        $document->createTextNode(" - message us on WhatsApp and we'll help you out.")
    );
}

function nice_hair_contact_is_seed_text(string $value, array $seed_values): bool
{
    $normalized_value = nice_hair_contact_normalize_compare_text($value);

    if ($normalized_value === '') {
        return true;
    }

    foreach ($seed_values as $seed_value) {
        if ($normalized_value === nice_hair_contact_normalize_compare_text($seed_value)) {
            return true;
        }
    }

    return false;
}

function nice_hair_contact_is_seed_href(string $value, array $seed_values): bool
{
    $normalized_value = nice_hair_contact_normalize_href($value);

    if ($normalized_value === '') {
        return true;
    }

    foreach ($seed_values as $seed_value) {
        if ($normalized_value === nice_hair_contact_normalize_href($seed_value)) {
            return true;
        }
    }

    return false;
}

function nice_hair_contact_replace_phone_classes(
    DOMDocument $document,
    DOMXPath $xpath,
    array $class_names,
    string $context
): void {
    $phone_display = nice_hair_get_contact_phone_display($context);
    $phone_link = nice_hair_get_contact_phone_link($context);

    foreach ($class_names as $class_name) {
        $element = nice_hair_contact_dom_query_first($xpath, $class_name);

        if (! $element instanceof DOMElement) {
            continue;
        }

        $anchor = nice_hair_contact_dom_first_anchor($element);

        if (! $anchor instanceof DOMElement) {
            continue;
        }

        $text_is_seed = nice_hair_contact_is_seed_text(
            $anchor->textContent,
            ['+971 58 598 8409']
        );
        $href_is_seed = nice_hair_contact_is_seed_href(
            $anchor->getAttribute('href'),
            ['tel:+971585988409', 'tel:+971 58 598 8409']
        );

        if (! $text_is_seed || ! $href_is_seed) {
            continue;
        }

        $anchor->setAttribute('href', 'tel:' . $phone_link);
        nice_hair_contact_dom_set_text($document, $anchor, $phone_display);
    }
}

function nice_hair_contact_replace_address_classes(
    DOMDocument $document,
    DOMXPath $xpath,
    array $class_names,
    string $value
): void {
    foreach ($class_names as $class_name) {
        $element = nice_hair_contact_dom_query_first($xpath, $class_name);

        if (! $element instanceof DOMElement) {
            continue;
        }

        if (! nice_hair_contact_is_seed_text(
            $element->textContent,
            ['Al Noor st, Al Sufouh, Al Sufouh 1, Dubai']
        )) {
            continue;
        }

        nice_hair_contact_dom_set_text_with_breaks($document, $element, $value);
    }
}

function nice_hair_contact_replace_hours_classes(
    DOMDocument $document,
    DOMXPath $xpath,
    array $class_names,
    string $context
): void {
    $hours = nice_hair_get_contact_hours($context);
    $hours_with_break = str_replace(': ', ":<br>", $hours);
    $seed_values = [
        "We're open daily: 10 AM - 10 PM",
        "We're open daily: 10 AM – 10 PM",
        "We’re open daily: 10 AM – 10 PM",
    ];

    foreach ($class_names as $class_name) {
        $elements = nice_hair_contact_dom_query_all($xpath, $class_name);

        if (! $elements instanceof DOMNodeList || $elements->length === 0) {
            continue;
        }

        foreach ($elements as $element) {
            if (! $element instanceof DOMElement) {
                continue;
            }

            if (! nice_hair_contact_is_seed_text($element->textContent, $seed_values)) {
                continue;
            }

            nice_hair_contact_dom_set_text_with_breaks($document, $element, $hours_with_break);
        }
    }
}

function nice_hair_contact_replace_social_container_classes(
    DOMXPath $xpath,
    array $class_names,
    string $context,
    array $fallbacks = []
): void {
    foreach ($class_names as $class_name) {
        $containers = nice_hair_contact_dom_query_all($xpath, $class_name);

        if (! $containers instanceof DOMNodeList || $containers->length === 0) {
            continue;
        }

        foreach ($containers as $container) {
            if (! $container instanceof DOMElement) {
                continue;
            }

            $anchors = [];

            if (strtolower($container->tagName) === 'a') {
                $anchors[] = $container;
            } else {
                foreach ($container->getElementsByTagName('a') as $anchor) {
                    if ($anchor instanceof DOMElement) {
                        $anchors[] = $anchor;
                    }
                }
            }

            foreach ($anchors as $anchor) {
                $label = strtolower(nice_hair_contact_normalize_compare_text($anchor->textContent));

                $platform = match ($label) {
                    'telegram' => 'telegram',
                    'instagram' => 'instagram',
                    'whatsapp' => 'whatsapp',
                    default => '',
                };

                if ($platform === '') {
                    continue;
                }

                $fallback = is_string($fallbacks[$platform] ?? null) ? (string) $fallbacks[$platform] : '#';

                if (! nice_hair_contact_is_seed_href($anchor->getAttribute('href'), ['#', $fallback])) {
                    continue;
                }

                $anchor->setAttribute('href', nice_hair_get_contact_social_url($platform, $context, $fallback));
            }
        }
    }
}

function nice_hair_contact_replace_shared_contact_markup(string $html): string
{
    return nice_hair_contact_html_fragment($html, static function (DOMDocument $document, DOMXPath $xpath): void {
        nice_hair_contact_replace_phone_classes($document, $xpath, ['nh-contact__phone'], 'home');

        $address = nice_hair_contact_dom_query_first($xpath, 'nh-contact__address', 'p');

        if ($address instanceof DOMElement && nice_hair_contact_is_seed_text(
            $address->textContent,
            [
                'VISIT OUR SHOWROOM IN AL SUFOUH, DUBAI',
                'Al Noor st, Al Sufouh, Al Sufouh 1, Dubai',
            ]
        )) {
            nice_hair_contact_dom_set_text(
                $document,
                $address,
                nice_hair_get_contact_address_plain('home')
            );
        }

        $description = nice_hair_contact_dom_query_first($xpath, 'nh-contact__description', 'p');

        if ($description instanceof DOMElement && nice_hair_contact_is_seed_text(
            $description->textContent,
            ["We're always online 10:00-22:00 - message us on WhatsApp and we'll help you out."]
        )) {
            nice_hair_contact_dom_set_shared_contact_description(
                $document,
                $description,
                nice_hair_get_contact_hours_compact('home')
            );
        }

        $whatsapp_link = nice_hair_contact_dom_query_first($xpath, 'nh-contact__link');

        if ($whatsapp_link instanceof DOMElement) {
            $anchor = nice_hair_contact_dom_first_anchor($whatsapp_link);

            if (
                $anchor instanceof DOMElement
                && nice_hair_contact_is_seed_href(
                    $anchor->getAttribute('href'),
                    ['#', 'https://wa.me/971585988409']
                )
            ) {
                $anchor->setAttribute(
                    'href',
                    nice_hair_get_contact_social_url('whatsapp', 'home', 'https://wa.me/971585988409')
                );
            }
        }

        $map_link = nice_hair_contact_dom_query_first($xpath, 'nh-contact__map-link');

        if ($map_link instanceof DOMElement) {
            $anchor = nice_hair_contact_dom_first_anchor($map_link);

            if (
                $anchor instanceof DOMElement
                && nice_hair_contact_is_seed_href(
                    $anchor->getAttribute('href'),
                    ['#', 'https://maps.google.com/?q=Al+Sufouh+Dubai']
                )
            ) {
                $anchor->setAttribute('href', nice_hair_get_contact_map_url());
            }
        }
    });
}

function nice_hair_contact_replace_gallery_markup(string $html): string
{
    return nice_hair_contact_html_fragment($html, static function (DOMDocument $document, DOMXPath $xpath): void {
        $container = nice_hair_contact_dom_query_first($xpath, 'nh-gallery__instagram-link');

        if (! $container instanceof DOMElement) {
            return;
        }

        $anchor = nice_hair_contact_dom_first_anchor($container);

        if (
            ! $anchor instanceof DOMElement
            || ! nice_hair_contact_is_seed_href(
                $anchor->getAttribute('href'),
                ['#', 'https://www.instagram.com/nice_hair_dxb/']
            )
        ) {
            return;
        }

        $anchor->setAttribute(
            'href',
            nice_hair_get_contact_social_url('instagram', 'home', 'https://www.instagram.com/nice_hair_dxb/')
        );
    });
}

function nice_hair_contact_replace_salon_approach_markup(string $html): string
{
    return nice_hair_contact_html_fragment($html, static function (DOMDocument $document, DOMXPath $xpath): void {
        nice_hair_contact_replace_social_container_classes(
            $xpath,
            ['nh-salon-our-approach__socials'],
            'salon',
            [
                'telegram' => '#',
                'instagram' => '#',
                'whatsapp' => '#',
            ]
        );
    });
}

function nice_hair_contact_replace_pattern_markup(string $html, string $context, string $prefix): string
{
    $address_value = nice_hair_get_contact_address_display($context);
    $hour_class_names = [$prefix . '__hours-text'];

    if ($prefix === 'nh-home-hero') {
        $hour_class_names = ['nh-home-hero__feature-text'];
    }

    if ($prefix === 'nh-tools-hero') {
        $address_value = nice_hair_get_contact_address_plain($context);
        $hour_class_names = [];
    }

    if ($prefix === 'nh-shipping-hero') {
        $hour_class_names = [];
    }

    return nice_hair_contact_html_fragment($html, static function (DOMDocument $document, DOMXPath $xpath) use (
        $address_value,
        $context,
        $hour_class_names,
        $prefix
    ): void {
        nice_hair_contact_replace_phone_classes($document, $xpath, [$prefix . '__phone'], $context);
        nice_hair_contact_replace_address_classes($document, $xpath, [$prefix . '__address'], $address_value);
        nice_hair_contact_replace_hours_classes($document, $xpath, $hour_class_names, $context);
        nice_hair_contact_replace_social_container_classes(
            $xpath,
            [$prefix . '__social'],
            $context,
            [
                'telegram' => '#',
                'instagram' => '#',
                'whatsapp' => '#',
            ]
        );
    });
}

function nice_hair_contact_replace_post_hero_markup(string $html): string
{
    return nice_hair_contact_html_fragment($html, static function (DOMDocument $document, DOMXPath $xpath): void {
        nice_hair_contact_replace_phone_classes($document, $xpath, ['nh-post-hero__phone'], 'home');
        nice_hair_contact_replace_address_classes(
            $document,
            $xpath,
            ['nh-post-hero__address'],
            nice_hair_get_contact_address_plain('home')
        );
    });
}

function nice_hair_contact_replace_single_product_tools_markup(string $html): string
{
    return nice_hair_contact_html_fragment($html, static function (DOMDocument $document, DOMXPath $xpath): void {
        nice_hair_contact_replace_phone_classes($document, $xpath, ['nh-single-product__phone'], 'shop');
        nice_hair_contact_replace_address_classes(
            $document,
            $xpath,
            ['nh-single-product__address'],
            nice_hair_get_contact_address_plain('shop')
        );

        $consult_button = nice_hair_contact_dom_query_first($xpath, 'nh-single-product__consult-btn', 'a');

        if (! $consult_button instanceof DOMElement) {
            return;
        }

        $current_href = $consult_button->getAttribute('href');

        if (! nice_hair_contact_is_seed_href($current_href, ['#', 'https://wa.me/971585988409'])) {
            if (! str_contains($current_href, 'wa.me/971585988409?text=')) {
                return;
            }
        }

        $message = '';
        $normalized_href = html_entity_decode($current_href, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $query = parse_url($normalized_href, PHP_URL_QUERY);

        if (is_string($query) && $query !== '') {
            parse_str($query, $query_args);
            $message = isset($query_args['text']) && is_string($query_args['text'])
                ? $query_args['text']
                : '';
        }

        $consult_button->setAttribute(
            'href',
            nice_hair_build_whatsapp_url($message, 'shop', 'https://wa.me/971585988409')
        );
    });
}

function nice_hair_filter_contact_render_block(string $block_content, array $block): string
{
    if ($block_content === '' || ! str_contains($block_content, 'nh-')) {
        return $block_content;
    }

    return match (true) {
        str_contains($block_content, 'nh-home-hero__') => nice_hair_contact_replace_pattern_markup(
            $block_content,
            'home',
            'nh-home-hero'
        ),
        str_contains($block_content, 'nh-thank-you-hero__') => nice_hair_contact_replace_pattern_markup(
            $block_content,
            'home',
            'nh-thank-you-hero'
        ),
        str_contains($block_content, 'nh-404-hero__') => nice_hair_contact_replace_pattern_markup(
            $block_content,
            'home',
            'nh-404-hero'
        ),
        str_contains($block_content, 'nh-salon-hero__') => nice_hair_contact_replace_pattern_markup(
            $block_content,
            'salon',
            'nh-salon-hero'
        ),
        str_contains($block_content, 'nh-shop-hero__') => nice_hair_contact_replace_pattern_markup(
            $block_content,
            'shop',
            'nh-shop-hero'
        ),
        str_contains($block_content, 'nh-shipping-hero__') => nice_hair_contact_replace_pattern_markup(
            $block_content,
            'shop',
            'nh-shipping-hero'
        ),
        str_contains($block_content, 'nh-tools-hero__') => nice_hair_contact_replace_pattern_markup(
            $block_content,
            'shop',
            'nh-tools-hero'
        ),
        str_contains($block_content, 'nh-contact__') => nice_hair_contact_replace_shared_contact_markup($block_content),
        str_contains($block_content, 'nh-gallery__instagram') => nice_hair_contact_replace_gallery_markup($block_content),
        str_contains($block_content, 'nh-salon-our-approach__socials') => nice_hair_contact_replace_salon_approach_markup($block_content),
        str_contains($block_content, 'nh-post-hero__') => nice_hair_contact_replace_post_hero_markup($block_content),
        str_contains($block_content, 'nh-single-product__') => nice_hair_contact_replace_single_product_tools_markup($block_content),
        default => $block_content,
    };
}
add_filter('render_block', 'nice_hair_filter_contact_render_block', 20, 2);
