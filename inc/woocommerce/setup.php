<?php

declare(strict_types=1);

/**
 * WooCommerce theme integration.
 */

/* ---------- Disable ALL WooCommerce styles (we use our own SCSS) ---------- */

// Classic WC styles.
add_filter('woocommerce_enqueue_styles', function (array $styles): array {
    return [];
});

// WC Block styles (wc-blocks-*, woocommerce-*).
add_action('wp_enqueue_scripts', function (): void {
    global $wp_styles;

    if (! $wp_styles instanceof WP_Styles) {
        return;
    }

    foreach (array_keys($wp_styles->registered) as $handle) {
        if (str_starts_with($handle, 'wc-blocks-') || str_starts_with($handle, 'woocommerce-')) {
            wp_deregister_style($handle);
        }
    }
}, 100);

function nice_hair_cleanup_classic_checkout_assets(): void
{
    if (is_admin() || ! function_exists('is_checkout') || ! is_checkout()) {
        return;
    }

    global $wp_scripts, $wp_styles;

    $blocked_exact_script_handles = [
        'wc_stripe_express_checkout',
    ];

    $blocked_script_prefixes = [
        'wc-stripe-express',
        'wc_stripe_express',
    ];

    if ($wp_scripts instanceof WP_Scripts) {
        foreach (array_keys($wp_scripts->registered) as $handle) {
            $should_remove = in_array($handle, $blocked_exact_script_handles, true);

            if (! $should_remove) {
                foreach ($blocked_script_prefixes as $prefix) {
                    if (str_starts_with($handle, $prefix)) {
                        $should_remove = true;
                        break;
                    }
                }
            }

            if (! $should_remove) {
                continue;
            }

            wp_dequeue_script($handle);
            wp_deregister_script($handle);
        }
    }

    if ($wp_styles instanceof WP_Styles) {
        foreach (array_keys($wp_styles->registered) as $handle) {
            $should_remove = str_starts_with($handle, 'wc-stripe-express')
                || str_starts_with($handle, 'wc_stripe_express');

            if (! $should_remove) {
                continue;
            }

            wp_dequeue_style($handle);
            wp_deregister_style($handle);
        }
    }
}

add_action('wp_enqueue_scripts', 'nice_hair_cleanup_classic_checkout_assets', 1000);
add_action('wp_print_scripts', 'nice_hair_cleanup_classic_checkout_assets', 1000);
add_action('wp_print_styles', 'nice_hair_cleanup_classic_checkout_assets', 1000);
add_action('wp_print_footer_scripts', 'nice_hair_cleanup_classic_checkout_assets', 1);

/* ---------- Replace default WC wrapper markup with theme wrapper ---------- */

remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

add_action('woocommerce_before_main_content', function (): void {
    echo '<main class="nh-main nh-main--shop">';
}, 10);

add_action('woocommerce_after_main_content', function (): void {
    echo '</main>';
}, 10);

/* ---------- Guest-only store flow ---------- */

add_action('init', function (): void {
    if (is_admin()) {
        return;
    }

    remove_all_actions('woocommerce_blocks_payment_method_type_registration');
}, 1);

add_filter('option_woocommerce_enable_guest_checkout', static fn (): string => 'yes');
add_filter('option_woocommerce_enable_checkout_login_reminder', static fn (): string => 'no');
add_filter('option_woocommerce_enable_signup_and_login_from_checkout', static fn (): string => 'no');
add_filter('option_woocommerce_enable_myaccount_registration', static fn (): string => 'no');
add_filter('option_woocommerce_enable_checkout_registration', static fn (): string => 'no');

add_filter('woocommerce_checkout_registration_enabled', '__return_false');
add_filter('woocommerce_checkout_registration_required', '__return_false');

remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);

/* ---------- Keep frontend WooCommerce copy in English ---------- */

function nice_hair_should_use_english_woocommerce_frontend(): bool
{
    return ! is_admin();
}

add_filter('gettext', function (string $translation, string $text, string $domain): string {
    if ($domain !== 'woocommerce' || ! nice_hair_should_use_english_woocommerce_frontend()) {
        return $translation;
    }

    return $text;
}, 20, 3);

add_filter('gettext_with_context', function (string $translation, string $text, string $context, string $domain): string {
    if ($domain !== 'woocommerce' || ! nice_hair_should_use_english_woocommerce_frontend()) {
        return $translation;
    }

    return $text;
}, 20, 4);

add_filter('ngettext', function (string $translation, string $single, string $plural, $number, string $domain): string {
    if ($domain !== 'woocommerce' || ! nice_hair_should_use_english_woocommerce_frontend()) {
        return $translation;
    }

    return (int) $number === 1 ? $single : $plural;
}, 20, 5);

add_filter('ngettext_with_context', function (string $translation, string $single, string $plural, $number, string $context, string $domain): string {
    if ($domain !== 'woocommerce' || ! nice_hair_should_use_english_woocommerce_frontend()) {
        return $translation;
    }

    return (int) $number === 1 ? $single : $plural;
}, 20, 6);

/* ---------- Keep frontend checkout locations in English ---------- */

function nice_hair_should_use_english_woocommerce_locations(): bool
{
    return nice_hair_should_use_english_woocommerce_frontend();
}

function nice_hair_load_woocommerce_i18n_file_in_locale(string $relative_file, string $locale = 'en_US'): array
{
    if (! function_exists('WC')) {
        return [];
    }

    $woocommerce = WC();

    if (! is_object($woocommerce) || ! method_exists($woocommerce, 'plugin_path')) {
        return [];
    }

    $path = trailingslashit($woocommerce->plugin_path()) . 'i18n/' . ltrim($relative_file, '/\\');

    if (! is_readable($path)) {
        return [];
    }

    $switched_locale = function_exists('switch_to_locale')
        ? switch_to_locale($locale)
        : false;

    try {
        $data = include $path;
    } finally {
        if ($switched_locale && function_exists('restore_previous_locale')) {
            restore_previous_locale();
        }
    }

    return is_array($data) ? $data : [];
}

function nice_hair_apply_english_woocommerce_location_labels(array $current, string $relative_file): array
{
    if (! nice_hair_should_use_english_woocommerce_locations()) {
        return $current;
    }

    $english = nice_hair_load_woocommerce_i18n_file_in_locale($relative_file);

    if ($english === []) {
        return $current;
    }

    foreach ($current as $key => $value) {
        if (is_array($value)) {
            if (! isset($english[$key]) || ! is_array($english[$key])) {
                continue;
            }

            foreach ($value as $nested_key => $nested_value) {
                if (isset($english[$key][$nested_key]) && ! is_array($nested_value)) {
                    $current[$key][$nested_key] = $english[$key][$nested_key];
                }
            }

            continue;
        }

        if (isset($english[$key]) && ! is_array($english[$key])) {
            $current[$key] = $english[$key];
        }
    }

    return $current;
}

add_filter('woocommerce_countries', function (array $countries): array {
    return nice_hair_apply_english_woocommerce_location_labels($countries, 'countries.php');
}, 20);

add_filter('woocommerce_states', function (array $states): array {
    return nice_hair_apply_english_woocommerce_location_labels($states, 'states.php');
}, 20);

add_filter('woocommerce_get_script_data', function ($params, string $handle) {
    if ($handle !== 'wc-country-select' || ! is_array($params) || ! nice_hair_should_use_english_woocommerce_locations()) {
        return $params;
    }

    return array_replace($params, [
        'i18n_select_state_text'    => 'Select an option...',
        'i18n_no_matches'           => 'No matches found',
        'i18n_ajax_error'           => 'Loading failed',
        'i18n_input_too_short_1'    => 'Please enter 1 or more characters',
        'i18n_input_too_short_n'    => 'Please enter %qty% or more characters',
        'i18n_input_too_long_1'     => 'Please delete 1 character',
        'i18n_input_too_long_n'     => 'Please delete %qty% characters',
        'i18n_selection_too_long_1' => 'You can only select 1 item',
        'i18n_selection_too_long_n' => 'You can only select %qty% items',
        'i18n_load_more'            => 'Loading more results...',
        'i18n_searching'            => 'Searching...',
    ]);
}, 20, 2);

add_action('wp', function (): void {
    if (is_admin() || ! function_exists('is_checkout') || ! is_checkout()) {
        return;
    }

    global $post, $wp_query;

    $normalize_post = static function ($candidate): void {
        if (! $candidate instanceof WP_Post) {
            return;
        }

        $candidate->post_content = '';
    };

    $normalize_post($post);

    if ($wp_query instanceof WP_Query) {
        $normalize_post($wp_query->post ?? null);

        if (! empty($wp_query->posts) && is_array($wp_query->posts)) {
            foreach ($wp_query->posts as $query_post) {
                $normalize_post($query_post);
            }
        }
    }
}, 1);

add_action('template_redirect', function (): void {
    if (! function_exists('is_account_page') || ! is_account_page() || is_admin()) {
        return;
    }

    $shop_url = function_exists('wc_get_page_permalink')
        ? wc_get_page_permalink('shop')
        : nice_hair_get_page_url('shop', 'page-templates/page-shop.php');

    if (! is_string($shop_url) || $shop_url === '') {
        $shop_url = home_url('/shop/');
    }

    wp_safe_redirect($shop_url, 302);
    exit;
});

function nice_hair_resolve_order_received_order(): ?WC_Order
{
    if (! function_exists('wc_get_order')) {
        return null;
    }

    $order_id = 0;

    if (function_exists('get_query_var')) {
        $order_id = absint((string) get_query_var('order-received'));
    }

    $order_key = isset($_GET['key']) ? wc_clean(wp_unslash((string) $_GET['key'])) : '';

    if ($order_id <= 0 && $order_key !== '' && function_exists('wc_get_order_id_by_order_key')) {
        $order_id = (int) wc_get_order_id_by_order_key($order_key);
    }

    if ($order_id <= 0) {
        return null;
    }

    $order = wc_get_order($order_id);

    if (! $order instanceof WC_Order) {
        return null;
    }

    if ($order_key !== '' && $order->get_order_key() !== $order_key) {
        return null;
    }

    return $order;
}

function nice_hair_order_should_use_thank_you_page(WC_Order $order): bool
{
    return ! $order->has_status('failed');
}

add_filter('woocommerce_get_checkout_order_received_url', function (string $url, WC_Order $order): string {
    if (! function_exists('nice_hair_get_thank_you_page_url') || ! nice_hair_order_should_use_thank_you_page($order)) {
        return $url;
    }

    return nice_hair_get_thank_you_page_url($order);
}, 10, 2);

add_action('template_redirect', function (): void {
    if (is_admin() || ! function_exists('is_wc_endpoint_url') || ! is_wc_endpoint_url('order-received')) {
        return;
    }

    $order = nice_hair_resolve_order_received_order();

    if (! $order instanceof WC_Order || ! function_exists('nice_hair_get_thank_you_page_url')) {
        return;
    }

    if (! nice_hair_order_should_use_thank_you_page($order)) {
        return;
    }

    $target_url = nice_hair_get_thank_you_page_url($order);

    if ($target_url === '') {
        return;
    }

    wp_safe_redirect($target_url, 302);
    exit;
}, 11);

/* ---------- Use shop header/footer ---------- */

add_filter('woocommerce_get_template', function (string $template, string $template_name): string {
    return $template;
}, 10, 2);

/* ---------- Products per page ---------- */

add_filter('loop_shop_per_page', function (): int {
    return 12;
});

/* ---------- Product image sizes ---------- */

add_action('after_setup_theme', function (): void {
    add_theme_support('woocommerce', [
        'thumbnail_image_width' => 400,
        'single_image_width'    => 800,
        'product_grid'          => [
            'default_rows'    => 3,
            'default_columns' => 4,
            'min_columns'     => 2,
            'max_columns'     => 4,
        ],
    ]);
}, 20);

/* ---------- Remove default WC sidebar ---------- */

remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);

/* ---------- Declare currency as USD ---------- */

add_filter('woocommerce_currency', function (): string {
    return 'USD';
});

function nice_hair_checkout_uses_separate_billing_address(): bool
{
    return isset($_POST['nh_use_different_billing_address'])
        && wp_unslash((string) $_POST['nh_use_different_billing_address']) === '1';
}

function nice_hair_get_checkout_contact_field_keys(): array
{
    return [
        'billing_first_name',
        'billing_last_name',
        'billing_email',
        'billing_phone',
        'billing_whatsapp',
    ];
}

function nice_hair_get_checkout_shipping_field_keys(): array
{
    return [
        'shipping_country',
        'shipping_address_1',
        'shipping_city',
        'shipping_state',
        'shipping_postcode',
    ];
}

function nice_hair_get_checkout_billing_address_field_keys(): array
{
    return [
        'billing_country',
        'billing_address_1',
        'billing_city',
        'billing_state',
        'billing_postcode',
    ];
}

function nice_hair_get_checkout_field_map(): array
{
    if (! function_exists('WC')) {
        return [];
    }

    $checkout = WC()->checkout();

    if (! $checkout instanceof WC_Checkout) {
        return [];
    }

    $groups = $checkout->get_checkout_fields();
    $map = [];

    foreach ($groups as $group_key => $fields) {
        if (! is_array($fields)) {
            continue;
        }

        foreach ($fields as $field_key => $field) {
            if (! is_array($field)) {
                continue;
            }

            $field['fieldset'] = $group_key;
            $map[$field_key] = $field;
        }
    }

    return $map;
}

function nice_hair_get_checkout_field_label(string $field_key, array $field = []): string
{
    $label = isset($field['label']) ? trim((string) $field['label']) : '';

    if ($label !== '') {
        return wp_strip_all_tags($label);
    }

    return ucwords(str_replace('_', ' ', preg_replace('/^(billing|shipping|order)_/', '', $field_key)));
}

function nice_hair_get_checkout_posted_value(array $data, string $field_key): string
{
    $value = $data[$field_key] ?? '';

    if (is_array($value)) {
        return '';
    }

    return wc_clean(wp_unslash((string) $value));
}

function nice_hair_checkout_is_valid_state(string $value, string $country): bool
{
    $value = trim($value);
    $country = strtoupper(trim($country));

    if ($value === '' || $country === '' || ! function_exists('WC')) {
        return true;
    }

    $states = WC()->countries ? WC()->countries->get_states($country) : [];

    if (! is_array($states) || $states === []) {
        return true;
    }

    return isset($states[$value]) || in_array($value, array_values($states), true);
}

function nice_hair_get_checkout_field_error_message(string $field_key, array $field, array $data): string
{
    $label = nice_hair_get_checkout_field_label($field_key, $field);
    $value = nice_hair_get_checkout_posted_value($data, $field_key);
    $validate = isset($field['validate']) && is_array($field['validate']) ? $field['validate'] : [];
    $is_required = ! empty($field['required']);

    if ($is_required && $value === '') {
        return sprintf('%s is a required field.', $label);
    }

    if ($value === '') {
        return '';
    }

    if (in_array('email', $validate, true) && ! is_email($value)) {
        return sprintf('%s is not a valid email address.', $label);
    }

    if (in_array('phone', $validate, true) && class_exists('WC_Validation') && ! WC_Validation::is_phone($value)) {
        return sprintf('%s is not a valid phone number.', $label);
    }

    if (in_array('postcode', $validate, true) && class_exists('WC_Validation')) {
        $country_key = str_starts_with($field_key, 'billing_') ? 'billing_country' : 'shipping_country';
        $country = nice_hair_get_checkout_posted_value($data, $country_key);

        if ($country !== '' && ! WC_Validation::is_postcode($value, $country)) {
            return sprintf('%s is not a valid postal code.', $label);
        }
    }

    if (in_array('state', $validate, true)) {
        $country_key = str_starts_with($field_key, 'billing_') ? 'billing_country' : 'shipping_country';
        $country = nice_hair_get_checkout_posted_value($data, $country_key);

        if (! nice_hair_checkout_is_valid_state($value, $country)) {
            return sprintf('%s is not a valid state / region.', $label);
        }
    }

    return '';
}

function nice_hair_get_order_whatsapp_number(WC_Order|int $order): string
{
    $resolved_order = $order instanceof WC_Order ? $order : wc_get_order($order);

    if (! $resolved_order instanceof WC_Order) {
        return '';
    }

    return trim((string) $resolved_order->get_meta('_billing_whatsapp', true));
}

add_filter('woocommerce_checkout_fields', function (array $fields): array {
    $use_separate_billing = nice_hair_checkout_uses_separate_billing_address();

    $fields['billing']['billing_whatsapp'] = [
        'type'         => 'tel',
        'label'        => __('WhatsApp number', 'nice-hair'),
        'required'     => false,
        'priority'     => 45,
        'autocomplete' => 'tel',
    ];

    $fields['billing']['billing_first_name']['label'] = __('First name', 'nice-hair');
    $fields['billing']['billing_last_name']['label'] = __('Last name', 'nice-hair');
    $fields['billing']['billing_email']['label'] = __('Email', 'nice-hair');
    $fields['billing']['billing_phone']['label'] = __('Phone', 'nice-hair');

    foreach (['billing_company', 'billing_address_2', 'shipping_company', 'shipping_address_2', 'shipping_first_name', 'shipping_last_name'] as $field_key) {
        foreach (['billing', 'shipping'] as $fieldset_key) {
            if (isset($fields[$fieldset_key][$field_key])) {
                unset($fields[$fieldset_key][$field_key]);
            }
        }
    }

    foreach (nice_hair_get_checkout_billing_address_field_keys() as $field_key) {
        if (! isset($fields['billing'][$field_key])) {
            continue;
        }

        $fields['billing'][$field_key]['required'] = $use_separate_billing;
    }

    $billing_address_labels = [
        'billing_country' => __('Country', 'nice-hair'),
        'billing_address_1' => __('Address', 'nice-hair'),
        'billing_city' => __('City', 'nice-hair'),
        'billing_state' => __('State / Region', 'nice-hair'),
        'billing_postcode' => __('Postal code', 'nice-hair'),
    ];

    foreach ($billing_address_labels as $field_key => $label) {
        if (isset($fields['billing'][$field_key])) {
            $fields['billing'][$field_key]['label'] = $label;
        }
    }

    $shipping_labels = [
        'shipping_country' => __('Country', 'nice-hair'),
        'shipping_address_1' => __('Address', 'nice-hair'),
        'shipping_city' => __('City', 'nice-hair'),
        'shipping_state' => __('State / Region', 'nice-hair'),
        'shipping_postcode' => __('Postal code', 'nice-hair'),
    ];

    foreach ($shipping_labels as $field_key => $label) {
        if (isset($fields['shipping'][$field_key])) {
            $fields['shipping'][$field_key]['label'] = $label;
        }
    }

    if (isset($fields['order']['order_comments'])) {
        $fields['order']['order_comments']['label'] = __('Comment for the courier', 'nice-hair');
        $fields['order']['order_comments']['placeholder'] = '';
        $fields['order']['order_comments']['type'] = 'text';
        $fields['order']['order_comments']['required'] = false;
    }

    return $fields;
});

add_action('woocommerce_after_checkout_validation', function (array $data, WP_Error $errors): void {
    if (! $errors->has_errors()) {
        return;
    }

    $field_map = nice_hair_get_checkout_field_map();

    if ($field_map === []) {
        return;
    }

    $field_error_codes = [];

    foreach ($errors->get_error_codes() as $code) {
        $error_data = $errors->get_error_data($code);
        $field_id = is_array($error_data) ? (string) ($error_data['id'] ?? '') : '';

        if ($field_id === '' || ! isset($field_map[$field_id])) {
            continue;
        }

        $field_error_codes[$field_id][] = (string) $code;
    }

    foreach ($field_error_codes as $field_id => $codes) {
        foreach ($codes as $code) {
            $errors->remove($code);
        }

        $message = nice_hair_get_checkout_field_error_message($field_id, $field_map[$field_id], $data);

        if ($message === '') {
            continue;
        }

        $errors->add(
            'nh_checkout_' . $field_id,
            $message,
            ['id' => $field_id]
        );
    }
}, 20, 2);

add_action('woocommerce_checkout_create_order', function (WC_Order $order, array $data): void {
    $whatsapp = isset($data['billing_whatsapp'])
        ? wc_clean(wp_unslash((string) $data['billing_whatsapp']))
        : '';

    if ($whatsapp !== '') {
        $order->update_meta_data('_billing_whatsapp', $whatsapp);
    }

    $billing_first_name = isset($data['billing_first_name']) ? wc_clean(wp_unslash((string) $data['billing_first_name'])) : '';
    $billing_last_name = isset($data['billing_last_name']) ? wc_clean(wp_unslash((string) $data['billing_last_name'])) : '';

    if ($billing_first_name !== '') {
        $order->set_shipping_first_name($billing_first_name);
    }

    if ($billing_last_name !== '') {
        $order->set_shipping_last_name($billing_last_name);
    }

    if (! nice_hair_checkout_uses_separate_billing_address()) {
        $copy_map = [
            'country' => 'shipping_country',
            'address_1' => 'shipping_address_1',
            'city' => 'shipping_city',
            'state' => 'shipping_state',
            'postcode' => 'shipping_postcode',
        ];

        foreach ($copy_map as $setter_suffix => $shipping_key) {
            $shipping_value = isset($data[$shipping_key])
                ? wc_clean(wp_unslash((string) $data[$shipping_key]))
                : '';

            if ($shipping_value === '') {
                continue;
            }

            $setter = 'set_billing_' . $setter_suffix;

            if (method_exists($order, $setter)) {
                $order->{$setter}($shipping_value);
            }
        }
    }
}, 10, 2);

add_action('woocommerce_admin_order_data_after_billing_address', function (WC_Order $order): void {
    $whatsapp = nice_hair_get_order_whatsapp_number($order);

    if ($whatsapp === '') {
        return;
    }
    ?>
    <p><strong><?php esc_html_e('WhatsApp', 'nice-hair'); ?>:</strong> <?php echo esc_html($whatsapp); ?></p>
    <?php
});

add_filter('woocommerce_email_order_meta_fields', function (array $fields, bool $sent_to_admin, WC_Order $order): array {
    $whatsapp = nice_hair_get_order_whatsapp_number($order);

    if ($whatsapp === '') {
        return $fields;
    }

    $fields['billing_whatsapp'] = [
        'label' => __('WhatsApp', 'nice-hair'),
        'value' => $whatsapp,
    ];

    return $fields;
}, 10, 3);

/* ---------- Mini-cart drawer ---------- */

add_action('wp_footer', function (): void {
    if (is_cart() || is_checkout()) {
        return;
    }
    get_template_part('template-parts/woocommerce/mini-cart-drawer');
});

/* ---------- Cart fragments (AJAX cart updates) ---------- */

add_action('wp_enqueue_scripts', function (): void {
    if (is_cart() || is_checkout()) {
        return;
    }
    wp_enqueue_script('wc-cart-fragments');
});

add_filter('woocommerce_add_to_cart_fragments', function (array $fragments): array {
    ob_start();
    woocommerce_mini_cart();
    $fragments['#nh-cart-drawer-body'] = '<div class="nh-cart-drawer__body" id="nh-cart-drawer-body">' . ob_get_clean() . '</div>';

    $count = WC()->cart->get_cart_contents_count();
    $fragments['.nh-cart-count'] = '<span class="nh-cart-count">' . esc_html($count) . '</span>';

    return $fragments;
});

function nice_hair_format_weight_grams(float|int|string|null $weight): string
{
    if (! is_numeric($weight)) {
        return '';
    }

    $numeric_weight = (float) $weight;

    if ($numeric_weight <= 0) {
        return '';
    }

    $formatted = floor($numeric_weight) === $numeric_weight
        ? number_format($numeric_weight, 0, '.', '')
        : rtrim(rtrim(number_format($numeric_weight, 2, '.', ''), '0'), '.');

    return $formatted . ' g';
}

function nice_hair_get_cart_item_meta_rows(array $cart_item): array
{
    $rows = [];
    $product = $cart_item['data'] ?? null;

    if ($product instanceof WC_Product && ! empty($cart_item['variation'])) {
        foreach ((array) $cart_item['variation'] as $attr_key => $attr_val) {
            if (empty($attr_val)) {
                continue;
            }

            $rows[] = [
                'label' => wc_attribute_label(str_replace('attribute_', '', (string) $attr_key), $product),
                'value' => (string) $attr_val,
            ];
        }
    }

    if (! empty($cart_item['nh_exclusive_product_form_label'])) {
        $rows[] = [
            'label' => __('Product form', 'nice-hair'),
            'value' => (string) $cart_item['nh_exclusive_product_form_label'],
        ];
    }

    if (! empty($cart_item['nh_exclusive_fixed_weight_grams'])) {
        $weight_label = nice_hair_format_weight_grams($cart_item['nh_exclusive_fixed_weight_grams']);

        if ($weight_label !== '') {
            $rows[] = [
                'label' => __('Weight', 'nice-hair'),
                'value' => $weight_label,
            ];
        }
    }

    $custom_hair_meta_map = [
        'nh_custom_hair_color_label' => __('Color', 'nice-hair'),
        'nh_custom_hair_length_label' => __('Length', 'nice-hair'),
        'nh_custom_hair_quality_label' => __('Hair Quality', 'nice-hair'),
        'nh_custom_hair_texture_label' => __('Texture', 'nice-hair'),
        'nh_custom_hair_product_form_label' => __('Product form', 'nice-hair'),
    ];

    foreach ($custom_hair_meta_map as $meta_key => $label) {
        if (empty($cart_item[$meta_key])) {
            continue;
        }

        $rows[] = [
            'label' => $label,
            'value' => (string) $cart_item[$meta_key],
        ];
    }

    if (! empty($cart_item['nh_custom_hair_weight_grams'])) {
        $weight_label = nice_hair_format_weight_grams($cart_item['nh_custom_hair_weight_grams']);

        if ($weight_label !== '') {
            $rows[] = [
                'label' => __('Weight', 'nice-hair'),
                'value' => $weight_label,
            ];
        }
    }

    return $rows;
}

function nice_hair_get_current_admin_product(): ?WC_Product
{
    if (! is_admin()) {
        return null;
    }

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;

    if (! $screen instanceof WP_Screen || $screen->base !== 'post' || $screen->post_type !== 'product') {
        return null;
    }

    $product_id = isset($_GET['post'])
        ? absint(wp_unslash((string) $_GET['post']))
        : 0;

    if ($product_id <= 0) {
        return null;
    }

    $product = wc_get_product($product_id);

    return $product instanceof WC_Product ? $product : null;
}

function nice_hair_get_exclusive_admin_warnings(WC_Product $product): array
{
    if (! function_exists('nice_hair_product_is_family') || ! nice_hair_product_is_family('exclusive_hair', $product)) {
        return [];
    }

    $warnings = [];
    $texture_terms = wp_get_post_terms($product->get_id(), 'pa_texture', ['fields' => 'names']);
    $color_terms = wp_get_post_terms($product->get_id(), 'pa_color_group', ['fields' => 'names']);
    $texture_terms = is_array($texture_terms) ? array_values(array_filter(array_map('strval', $texture_terms))) : [];
    $color_terms = is_array($color_terms) ? array_values(array_filter(array_map('strval', $color_terms))) : [];

    if (count($texture_terms) !== 1) {
        $warnings[] = __('Exclusive Hair expects exactly one Texture term per product.', 'nice-hair');
    }

    if (count($color_terms) !== 1) {
        $warnings[] = __('Exclusive Hair expects exactly one Color term per product.', 'nice-hair');
    }

    $is_purchasable_candidate = $product->get_status() === 'publish' && $product->get_stock_status() !== 'outofstock';
    $base_lot_price = function_exists('nice_hair_get_product_base_lot_price')
        ? nice_hair_get_product_base_lot_price($product)
        : null;
    $fixed_weight = function_exists('nice_hair_get_product_fixed_weight_grams')
        ? nice_hair_get_product_fixed_weight_grams($product)
        : null;

    if ($is_purchasable_candidate && $base_lot_price === null) {
        $warnings[] = __('Base Lot Price is required for a purchasable Exclusive Hair item.', 'nice-hair');
    }

    if ($is_purchasable_candidate && $fixed_weight === null) {
        $warnings[] = __('Fixed Weight (grams) is required for a purchasable Exclusive Hair item.', 'nice-hair');
    }

    return array_values(array_unique($warnings));
}

add_action('admin_notices', function (): void {
    $product = nice_hair_get_current_admin_product();

    if (! $product instanceof WC_Product) {
        return;
    }

    $warnings = nice_hair_get_exclusive_admin_warnings($product);

    $notice_class = $warnings === [] ? 'notice-info' : 'notice-warning';
    ?>
    <div class="notice <?php echo esc_attr($notice_class); ?>">
        <p><strong><?php esc_html_e('Exclusive Hair editor checks', 'nice-hair'); ?></strong></p>
        <?php if ($warnings !== []) : ?>
            <ul style="margin: 0.5em 0 0 1.2em; list-style: disc;">
                <?php foreach ($warnings as $warning) : ?>
                    <li><?php echo esc_html($warning); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <p style="margin-top: 0.75em;"><?php esc_html_e('How to use for Exclusive Hair stays product-level. Fill it on this product only when needed.', 'nice-hair'); ?></p>
    </div>
    <?php
});

add_filter('woocommerce_add_to_cart_validation', function (
    bool $passed,
    int $product_id,
    int $quantity,
    int $variation_id = 0,
    array $variations = []
): bool {
    $product = wc_get_product($variation_id > 0 ? $variation_id : $product_id);

    if (! $product instanceof WC_Product || ! function_exists('nice_hair_product_is_family')) {
        return $passed;
    }

    if (nice_hair_product_is_family('custom_hair', $product)) {
        $selection = function_exists('nice_hair_resolve_custom_hair_selection')
            ? nice_hair_resolve_custom_hair_selection($product, [
                'color' => isset($_REQUEST['nh_custom_hair_color'])
                    ? wc_clean(wp_unslash((string) $_REQUEST['nh_custom_hair_color']))
                    : '',
                'length' => isset($_REQUEST['nh_custom_hair_length'])
                    ? wc_clean(wp_unslash((string) $_REQUEST['nh_custom_hair_length']))
                    : '',
                'quality' => isset($_REQUEST['nh_custom_hair_quality'])
                    ? wc_clean(wp_unslash((string) $_REQUEST['nh_custom_hair_quality']))
                    : '',
                'texture' => isset($_REQUEST['nh_custom_hair_texture'])
                    ? wc_clean(wp_unslash((string) $_REQUEST['nh_custom_hair_texture']))
                    : '',
                'weight' => isset($_REQUEST['nh_custom_hair_weight'])
                    ? wc_clean(wp_unslash((string) $_REQUEST['nh_custom_hair_weight']))
                    : '',
            ])
            : ['is_valid' => false];

        if (empty($selection['request_is_valid'])) {
            wc_add_notice(__('The selected Custom Hair configuration is no longer available for this product.', 'nice-hair'), 'error');
            return false;
        }

        if (empty($selection['is_complete']) || ! isset($selection['total_price']) || ! is_numeric($selection['total_price'])) {
            wc_add_notice(__('Custom Hair pricing is incomplete for this item. Please contact us for assistance.', 'nice-hair'), 'error');
            return false;
        }

        return $passed;
    }

    if (nice_hair_product_is_family('exclusive_hair', $product)) {
        $selected_form_key = isset($_REQUEST['nh_exclusive_product_form'])
            ? wc_clean(wp_unslash((string) $_REQUEST['nh_exclusive_product_form']))
            : '';
        $selected_form = function_exists('nice_hair_get_exclusive_product_form_option_for_product')
            ? nice_hair_get_exclusive_product_form_option_for_product($product, $selected_form_key)
            : null;
        $selected_total = function_exists('nice_hair_calculate_exclusive_product_form_total')
            ? nice_hair_calculate_exclusive_product_form_total($product, $selected_form_key)
            : null;

        if ($selected_form_key === '' || ! is_array($selected_form)) {
            wc_add_notice(__('Select the product form before adding this item to the cart.', 'nice-hair'), 'error');
            return false;
        }

        if ($selected_total === null) {
            wc_add_notice(__('Exclusive Hair pricing is incomplete for this item. Please contact us for assistance.', 'nice-hair'), 'error');
            return false;
        }
    }

    return $passed;
}, 10, 5);

add_filter('woocommerce_add_cart_item_data', function (array $cart_item_data, int $product_id, int $variation_id): array {
    $product = wc_get_product($variation_id > 0 ? $variation_id : $product_id);

    if (! $product instanceof WC_Product || ! function_exists('nice_hair_product_is_family')) {
        return $cart_item_data;
    }

    if (nice_hair_product_is_family('custom_hair', $product)) {
        $selection = function_exists('nice_hair_resolve_custom_hair_selection')
            ? nice_hair_resolve_custom_hair_selection($product, [
                'color' => isset($_REQUEST['nh_custom_hair_color'])
                    ? wc_clean(wp_unslash((string) $_REQUEST['nh_custom_hair_color']))
                    : '',
                'length' => isset($_REQUEST['nh_custom_hair_length'])
                    ? wc_clean(wp_unslash((string) $_REQUEST['nh_custom_hair_length']))
                    : '',
                'quality' => isset($_REQUEST['nh_custom_hair_quality'])
                    ? wc_clean(wp_unslash((string) $_REQUEST['nh_custom_hair_quality']))
                    : '',
                'texture' => isset($_REQUEST['nh_custom_hair_texture'])
                    ? wc_clean(wp_unslash((string) $_REQUEST['nh_custom_hair_texture']))
                    : '',
                'weight' => isset($_REQUEST['nh_custom_hair_weight'])
                    ? wc_clean(wp_unslash((string) $_REQUEST['nh_custom_hair_weight']))
                    : '',
            ])
            : ['is_valid' => false];

        if (empty($selection['is_valid']) || ! isset($selection['total_price']) || ! is_numeric($selection['total_price'])) {
            return $cart_item_data;
        }

        $labels = is_array($selection['labels'] ?? null) ? $selection['labels'] : [];
        $selected = is_array($selection['selection'] ?? null) ? $selection['selection'] : [];
        $product_form = is_array($selection['product_form'] ?? null) ? $selection['product_form'] : [];

        $cart_item_data['nh_custom_hair_color_key'] = (string) ($selected['color'] ?? '');
        $cart_item_data['nh_custom_hair_color_label'] = (string) ($labels['color'] ?? '');
        $cart_item_data['nh_custom_hair_length_key'] = (string) ($selected['length'] ?? '');
        $cart_item_data['nh_custom_hair_length_label'] = (string) ($labels['length'] ?? '');
        $cart_item_data['nh_custom_hair_quality_key'] = (string) ($selected['quality'] ?? '');
        $cart_item_data['nh_custom_hair_quality_label'] = (string) ($labels['quality'] ?? '');
        $cart_item_data['nh_custom_hair_texture_key'] = (string) ($selected['texture'] ?? '');
        $cart_item_data['nh_custom_hair_texture_label'] = (string) ($labels['texture'] ?? '');
        $cart_item_data['nh_custom_hair_weight_grams'] = isset($selected['weight']) && is_numeric($selected['weight'])
            ? (int) $selected['weight']
            : null;
        $cart_item_data['nh_custom_hair_product_form_key'] = (string) ($product_form['key'] ?? '');
        $cart_item_data['nh_custom_hair_product_form_label'] = (string) ($labels['product_form'] ?? ($product_form['label'] ?? ''));
        $cart_item_data['nh_custom_hair_unit_price'] = (float) $selection['total_price'];

        return $cart_item_data;
    }

    if (nice_hair_product_is_family('exclusive_hair', $product)) {
        $selected_form_key = isset($_REQUEST['nh_exclusive_product_form'])
            ? wc_clean(wp_unslash((string) $_REQUEST['nh_exclusive_product_form']))
            : '';
        $selected_form = function_exists('nice_hair_get_exclusive_product_form_option_for_product')
            ? nice_hair_get_exclusive_product_form_option_for_product($product, $selected_form_key)
            : null;
        $selected_total = function_exists('nice_hair_calculate_exclusive_product_form_total')
            ? nice_hair_calculate_exclusive_product_form_total($product, $selected_form_key)
            : null;
        $fixed_weight = function_exists('nice_hair_get_product_fixed_weight_grams')
            ? nice_hair_get_product_fixed_weight_grams($product)
            : null;

        if ($selected_form_key === '' || ! is_array($selected_form) || $selected_total === null) {
            return $cart_item_data;
        }

        $cart_item_data['nh_exclusive_product_form_key'] = $selected_form_key;
        $cart_item_data['nh_exclusive_product_form_label'] = (string) ($selected_form['label'] ?? $selected_form_key);
        $cart_item_data['nh_exclusive_unit_price'] = $selected_total;
        $selected_totals = function_exists('nice_hair_get_exclusive_product_form_totals')
            ? nice_hair_get_exclusive_product_form_totals($product, $selected_form_key)
            : [];
        $cart_item_data['nh_exclusive_regular_unit_price'] = isset($selected_totals['regular_total_price']) && is_numeric($selected_totals['regular_total_price'])
            ? (float) $selected_totals['regular_total_price']
            : null;
        $cart_item_data['nh_exclusive_fixed_weight_grams'] = $fixed_weight;
    }

    return $cart_item_data;
}, 10, 3);

add_filter('woocommerce_get_cart_item_from_session', function (array $cart_item, array $session_values): array {
    $keys = [
        'nh_exclusive_product_form_key',
        'nh_exclusive_product_form_label',
        'nh_exclusive_unit_price',
        'nh_exclusive_regular_unit_price',
        'nh_exclusive_fixed_weight_grams',
        'nh_custom_hair_color_key',
        'nh_custom_hair_color_label',
        'nh_custom_hair_length_key',
        'nh_custom_hair_length_label',
        'nh_custom_hair_quality_key',
        'nh_custom_hair_quality_label',
        'nh_custom_hair_texture_key',
        'nh_custom_hair_texture_label',
        'nh_custom_hair_weight_grams',
        'nh_custom_hair_product_form_key',
        'nh_custom_hair_product_form_label',
        'nh_custom_hair_unit_price',
    ];

    foreach ($keys as $key) {
        if (array_key_exists($key, $session_values)) {
            $cart_item[$key] = $session_values[$key];
        }
    }

    return $cart_item;
}, 10, 2);

add_action('woocommerce_before_calculate_totals', function (WC_Cart $cart): void {
    if (is_admin() && ! wp_doing_ajax()) {
        return;
    }

    foreach ($cart->get_cart() as $cart_item) {
        if (empty($cart_item['data']) || ! $cart_item['data'] instanceof WC_Product) {
            continue;
        }

        if (! empty($cart_item['nh_custom_hair_unit_price']) && is_numeric($cart_item['nh_custom_hair_unit_price'])) {
            $cart_item['data']->set_price((float) $cart_item['nh_custom_hair_unit_price']);
            continue;
        }

        if (! empty($cart_item['nh_exclusive_unit_price']) && is_numeric($cart_item['nh_exclusive_unit_price'])) {
            $cart_item['data']->set_price((float) $cart_item['nh_exclusive_unit_price']);
        }
    }
}, 20);

add_filter('woocommerce_get_item_data', function (array $item_data, array $cart_item): array {
    $custom_hair_meta_map = [
        'nh_custom_hair_color_label' => __('Color', 'nice-hair'),
        'nh_custom_hair_length_label' => __('Length', 'nice-hair'),
        'nh_custom_hair_quality_label' => __('Hair Quality', 'nice-hair'),
        'nh_custom_hair_texture_label' => __('Texture', 'nice-hair'),
        'nh_custom_hair_product_form_label' => __('Product form', 'nice-hair'),
    ];

    foreach ($custom_hair_meta_map as $meta_key => $label) {
        if (empty($cart_item[$meta_key])) {
            continue;
        }

        $item_data[] = [
            'key'   => $label,
            'value' => (string) $cart_item[$meta_key],
        ];
    }

    if (! empty($cart_item['nh_custom_hair_weight_grams'])) {
        $weight_label = nice_hair_format_weight_grams($cart_item['nh_custom_hair_weight_grams']);

        if ($weight_label !== '') {
            $item_data[] = [
                'key'   => __('Weight', 'nice-hair'),
                'value' => $weight_label,
            ];
        }
    }

    if (! empty($cart_item['nh_exclusive_product_form_label'])) {
        $item_data[] = [
            'key'   => __('Product form', 'nice-hair'),
            'value' => (string) $cart_item['nh_exclusive_product_form_label'],
        ];
    }

    if (! empty($cart_item['nh_exclusive_fixed_weight_grams'])) {
        $weight_label = nice_hair_format_weight_grams($cart_item['nh_exclusive_fixed_weight_grams']);

        if ($weight_label !== '') {
            $item_data[] = [
                'key'   => __('Weight', 'nice-hair'),
                'value' => $weight_label,
            ];
        }
    }

    return $item_data;
}, 10, 2);

add_action('woocommerce_checkout_create_order_line_item', function (
    WC_Order_Item_Product $item,
    string $cart_item_key,
    array $values
): void {
    $custom_hair_meta_map = [
        'nh_custom_hair_color_label' => __('Color', 'nice-hair'),
        'nh_custom_hair_length_label' => __('Length', 'nice-hair'),
        'nh_custom_hair_quality_label' => __('Hair Quality', 'nice-hair'),
        'nh_custom_hair_texture_label' => __('Texture', 'nice-hair'),
        'nh_custom_hair_product_form_label' => __('Product form', 'nice-hair'),
    ];

    foreach ($custom_hair_meta_map as $meta_key => $label) {
        if (! empty($values[$meta_key])) {
            $item->add_meta_data($label, (string) $values[$meta_key], true);
        }
    }

    if (! empty($values['nh_custom_hair_weight_grams'])) {
        $weight_label = nice_hair_format_weight_grams($values['nh_custom_hair_weight_grams']);

        if ($weight_label !== '') {
            $item->add_meta_data(__('Weight', 'nice-hair'), $weight_label, true);
        }
    }

    if (! empty($values['nh_exclusive_product_form_label'])) {
        $item->add_meta_data(__('Product form', 'nice-hair'), (string) $values['nh_exclusive_product_form_label'], true);
    }

    if (! empty($values['nh_exclusive_fixed_weight_grams'])) {
        $weight_label = nice_hair_format_weight_grams($values['nh_exclusive_fixed_weight_grams']);

        if ($weight_label !== '') {
            $item->add_meta_data(__('Weight', 'nice-hair'), $weight_label, true);
        }
    }
}, 10, 3);

add_action('wc_ajax_update_cart_item', 'nice_hair_update_cart_item');
add_action('wc_ajax_nopriv_update_cart_item', 'nice_hair_update_cart_item');
add_action('wc_ajax_nh_add_to_cart', 'nice_hair_ajax_add_to_cart');
add_action('wc_ajax_nopriv_nh_add_to_cart', 'nice_hair_ajax_add_to_cart');

add_action('wp_loaded', function (): void {
    $wc_ajax = isset($_GET['wc-ajax'])
        ? wc_clean(wp_unslash((string) $_GET['wc-ajax']))
        : '';

    if ($wc_ajax !== 'nh_add_to_cart') {
        return;
    }

    unset($_GET['add-to-cart'], $_POST['add-to-cart'], $_REQUEST['add-to-cart']);
}, 0);

function nice_hair_ajax_add_to_cart(): void
{
    if (! function_exists('WC') || ! WC()->cart) {
        wp_send_json(['error' => true, 'message' => 'Cart is unavailable.'], 400);
    }

    $product_id = isset($_POST['product_id'])
        ? absint(wp_unslash((string) $_POST['product_id']))
        : 0;
    $variation_id = isset($_POST['variation_id'])
        ? absint(wp_unslash((string) $_POST['variation_id']))
        : 0;
    $quantity = isset($_POST['quantity'])
        ? (int) wc_stock_amount(wp_unslash((string) $_POST['quantity']))
        : 1;
    $variations = [];

    if (isset($_POST['variation']) && is_array($_POST['variation'])) {
        $variations = array_map(
            static fn ($value): string => wc_clean(wp_unslash((string) $value)),
            (array) $_POST['variation']
        );
    }

    foreach ($_POST as $key => $value) {
        $key = (string) $key;

        if (! str_starts_with($key, 'attribute_') || isset($variations[$key])) {
            continue;
        }

        if (is_array($value)) {
            continue;
        }

        $variations[$key] = wc_clean(wp_unslash((string) $value));
    }

    if ($product_id <= 0 || $quantity <= 0) {
        wp_send_json(['error' => true, 'message' => 'Product or quantity is invalid.'], 400);
    }

    $passed_validation = apply_filters(
        'woocommerce_add_to_cart_validation',
        true,
        $product_id,
        $quantity,
        $variation_id,
        $variations
    );

    if (! $passed_validation) {
        wp_send_json([
            'error'       => true,
            'product_url' => get_permalink($product_id),
            'notices'     => wc_get_notices(),
        ], 400);
    }

    $product = wc_get_product($variation_id > 0 ? $variation_id : $product_id);
    $cart_item_data = apply_filters('woocommerce_add_cart_item_data', [], $product_id, $variation_id);

    if ($product instanceof WC_Product && $product->is_sold_individually()) {
        $cart_id = WC()->cart->generate_cart_id($product_id, $variation_id, $variations, $cart_item_data);
        $existing_cart_item_key = WC()->cart->find_product_in_cart($cart_id);

        if ($existing_cart_item_key) {
            do_action('woocommerce_ajax_added_to_cart', $product_id);
            WC_AJAX::get_refreshed_fragments();
        }
    }

    $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variations, $cart_item_data);

    if (! $cart_item_key) {
        wp_send_json([
            'error'       => true,
            'product_url' => get_permalink($product_id),
            'notices'     => wc_get_notices(),
        ], 400);
    }

    do_action('woocommerce_ajax_added_to_cart', $product_id);
    WC_AJAX::get_refreshed_fragments();
}

function nice_hair_get_cart_page_markup(): string
{
    if (! function_exists('WC') || ! WC()->cart) {
        return '';
    }

    ob_start();

    if (WC()->cart->is_empty()) {
        wc_get_template('cart/cart-empty.php');
    } else {
        wc_get_template('cart/cart.php');
    }

    return (string) ob_get_clean();
}

function nice_hair_update_cart_item(): void
{
    if (! function_exists('WC') || ! WC()->cart) {
        wp_send_json_error(['message' => 'Cart is unavailable.'], 400);
    }

    $cart_item_key = isset($_POST['cart_item_key'])
        ? wc_clean(wp_unslash((string) $_POST['cart_item_key']))
        : '';

    if ($cart_item_key === '') {
        wp_send_json_error(['message' => 'Cart item key is required.'], 400);
    }

    $cart_item = WC()->cart->get_cart_item($cart_item_key);

    if (! is_array($cart_item) || ! isset($cart_item['data']) || ! $cart_item['data'] instanceof WC_Product) {
        wp_send_json_error(['message' => 'Cart item was not found.'], 404);
    }

    $quantity = isset($_POST['quantity'])
        ? (int) wc_stock_amount(wp_unslash((string) $_POST['quantity']))
        : -1;

    if ($quantity < 0) {
        wp_send_json_error(['message' => 'Quantity is invalid.'], 400);
    }

    $product = $cart_item['data'];

    if ($product->is_sold_individually()) {
        $quantity = min(1, $quantity);
    }

    $updated = WC()->cart->set_quantity($cart_item_key, $quantity, true);

    if ($updated === false) {
        wp_send_json_error(['message' => 'Quantity could not be updated.'], 400);
    }

    wp_send_json_success([
        'quantity'  => $quantity,
        'count'     => WC()->cart->get_cart_contents_count(),
        'cart_html' => nice_hair_get_cart_page_markup(),
    ]);
}

/* ---------- Redirect add-to-cart to same page (no redirect to cart) ---------- */

add_filter('woocommerce_add_to_cart_redirect', function (): false {
    return false;
});

add_filter('option_woocommerce_cart_redirect_after_add', function (): string {
    return 'no';
});

/* ---------- Resolve nested shop product URLs without breaking child categories ---- */
// We keep Woo's default category-first rules so `/shop/parent/child/`
// remains a valid product category archive. If the matched `product_cat`
// path does not exist as a real term, we reinterpret the last path segment
// as a product slug inside the parent category path.

function nice_hair_normalize_product_cat_path(string $path): string
{
    return trim($path, '/');
}

function nice_hair_get_shop_page_path(): string
{
    if (! function_exists('wc_get_page_id')) {
        return '';
    }

    $shop_id = (int) wc_get_page_id('shop');

    if ($shop_id <= 0) {
        return '';
    }

    return nice_hair_normalize_product_cat_path((string) get_page_uri($shop_id));
}

function nice_hair_resolve_product_cat_path_term(string $path): ?WP_Term
{
    $path = nice_hair_normalize_product_cat_path($path);

    if ($path === '') {
        return null;
    }

    $segments = array_values(array_filter(explode('/', $path)));
    $parent_id = 0;
    $resolved = null;

    foreach ($segments as $segment) {
        $terms = get_terms([
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
            'slug'       => $segment,
            'parent'     => $parent_id,
            'number'     => 1,
        ]);

        if (! is_array($terms) || ! isset($terms[0]) || ! $terms[0] instanceof WP_Term) {
            return null;
        }

        $resolved = $terms[0];
        $parent_id = (int) $resolved->term_id;
    }

    return $resolved instanceof WP_Term ? $resolved : null;
}

add_filter('request', function (array $query_vars): array {
    if (is_admin() || ! isset($query_vars['pagename']) || isset($query_vars['product_cat']) || isset($query_vars['product'])) {
        return $query_vars;
    }

    $requested_page_path = nice_hair_normalize_product_cat_path((string) $query_vars['pagename']);
    $shop_page_path = nice_hair_get_shop_page_path();

    if ($requested_page_path === '' || $shop_page_path === '' || $requested_page_path === $shop_page_path) {
        return $query_vars;
    }

    $shop_prefix = $shop_page_path . '/';

    if (! str_starts_with($requested_page_path, $shop_prefix)) {
        return $query_vars;
    }

    if (get_page_by_path($requested_page_path, OBJECT, 'page') instanceof WP_Post) {
        return $query_vars;
    }

    $candidate_path = nice_hair_normalize_product_cat_path(substr($requested_page_path, strlen($shop_prefix)));

    if ($candidate_path === '') {
        return $query_vars;
    }

    unset($query_vars['pagename'], $query_vars['name'], $query_vars['page']);

    $query_vars['product_cat'] = $candidate_path;

    return $query_vars;
}, 15);

add_filter('request', function (array $query_vars): array {
    if (is_admin() || ! isset($query_vars['product_cat']) || isset($query_vars['product'])) {
        return $query_vars;
    }

    $requested_path = nice_hair_normalize_product_cat_path((string) $query_vars['product_cat']);

    if ($requested_path === '' || strpos($requested_path, '/') === false) {
        return $query_vars;
    }

    if (nice_hair_resolve_product_cat_path_term($requested_path) instanceof WP_Term) {
        return $query_vars;
    }

    $segments = array_values(array_filter(explode('/', $requested_path)));
    $product_slug = array_pop($segments);
    $category_path = nice_hair_normalize_product_cat_path(implode('/', $segments));

    if ($product_slug === '' || $category_path === '') {
        return $query_vars;
    }

    $category_term = nice_hair_resolve_product_cat_path_term($category_path);

    if (! $category_term instanceof WP_Term) {
        return $query_vars;
    }

    $product_post = get_page_by_path($product_slug, OBJECT, 'product');

    if (! $product_post instanceof WP_Post || $product_post->post_status !== 'publish') {
        return $query_vars;
    }

    if (! has_term($category_term->term_id, 'product_cat', $product_post)) {
        return $query_vars;
    }

    unset($query_vars['product_cat'], $query_vars['taxonomy'], $query_vars['term']);

    $query_vars['product'] = $product_slug;
    $query_vars['name'] = $product_slug;
    $query_vars['post_type'] = 'product';

    return $query_vars;
}, 20);

/* ---------- Preserve page template for WC shop page ---------- */
// WP treats the WC shop page as a product archive (is_page() = false,
// is_shop() = true). We force the custom page template and reset the
// global $post so the_content() outputs the Gutenberg blocks.

add_filter('template_include', function (string $template): string {
    if (! function_exists('is_shop') || ! is_shop()) {
        return $template;
    }

    $shop_id = (int) get_option('woocommerce_shop_page_id');

    if (! $shop_id) {
        return $template;
    }

    $page_tpl = get_page_template_slug($shop_id) ?: 'page-templates/page-shop.php';

    $located = locate_template($page_tpl);

    if (! $located) {
        return $template;
    }

    // Reset global $post to the shop page so the_content() works.
    global $post;
    $post = get_post($shop_id);
    setup_postdata($post);

    return $located;
}, 99);
