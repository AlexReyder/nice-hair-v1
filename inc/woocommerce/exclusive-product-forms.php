<?php

declare(strict_types=1);

/**
 * Exclusive Hair product-level Product Forms visibility settings.
 *
 * Глобальный список форм продукта и доплаты остаются в Shop Pricing.
 * Этот файл добавляет настройку на уровне конкретного товара Exclusive Hair:
 * какие формы продукта показывать и разрешать к покупке.
 */

const NH_EXCLUSIVE_PRODUCT_FORMS_GROUP_KEY = 'group_nh_product_exclusive_hair_product_forms';

function nice_hair_get_product_form_choices_for_fields(): array
{
    $choices = [];

    if (function_exists('nice_hair_get_shop_product_form_catalog')) {
        $catalog = nice_hair_get_shop_product_form_catalog();

        if (is_array($catalog)) {
            foreach ($catalog as $catalog_key => $option) {
                if (! is_array($option)) {
                    continue;
                }

                $form_key = isset($option['key'])
                    ? nice_hair_normalize_shop_key((string) $option['key'])
                    : nice_hair_normalize_shop_key((string) $catalog_key);

                $label = trim((string) ($option['label'] ?? ''));

                if ($form_key === '' || $label === '') {
                    continue;
                }

                $choices[$form_key] = $label;
            }
        }
    }

    if ($choices === [] && function_exists('nice_hair_get_shop_product_form_options')) {
        foreach (nice_hair_get_shop_product_form_options() as $option) {
            if (! is_array($option)) {
                continue;
            }

            $form_key = isset($option['key'])
                ? nice_hair_normalize_shop_key((string) $option['key'])
                : '';

            $label = trim((string) ($option['label'] ?? ''));

            if ($form_key === '' || $label === '') {
                continue;
            }

            $choices[$form_key] = $label;
        }
    }

    return $choices;
}

function nice_hair_load_exclusive_product_form_choices(array $field): array
{
    $field['choices'] = nice_hair_get_product_form_choices_for_fields();

    if ($field['choices'] === []) {
        $field['instructions'] = trim((string) ($field['instructions'] ?? ''));
        $field['instructions'] .= ($field['instructions'] !== '' ? '<br>' : '')
            . 'Формы продукта не найдены. Сначала настройте формы в разделе настроек цен магазина.';
    }

    return $field;
}

add_filter(
    'acf/load_field/name=nh_exclusive_available_product_forms',
    'nice_hair_load_exclusive_product_form_choices'
);

function nice_hair_register_exclusive_product_forms_fields(): void
{
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key'      => NH_EXCLUSIVE_PRODUCT_FORMS_GROUP_KEY,
        'title'    => 'Товар: формы продукта Exclusive Hair',
        'fields'   => [
            [
                'key'           => 'field_nh_exclusive_product_forms_mode',
                'label'         => 'Формы продукта',
                'name'          => 'nh_exclusive_product_forms_mode',
                'type'          => 'radio',
                'choices'       => [
                    'all'    => 'Показывать все формы продукта',
                    'custom' => 'Выбрать формы продукта вручную',
                ],
                'default_value' => 'all',
                'layout'        => 'vertical',
                'return_format' => 'value',
                'instructions'  => 'Управляет тем, какие формы продукта отображаются на странице этого товара Exclusive Hair.',
            ],
            [
                'key'               => 'field_nh_exclusive_available_product_forms',
                'label'             => 'Доступные формы продукта',
                'name'              => 'nh_exclusive_available_product_forms',
                'type'              => 'checkbox',
                'choices'           => [],
                'return_format'     => 'value',
                'layout'            => 'vertical',
                'toggle'            => 1,
                'allow_custom'      => 0,
                'save_custom'       => 0,
                'instructions'      => 'Выберите, какие формы продукта должны отображаться и быть доступными для покупки у этого товара.',
                'conditional_logic' => [
                    [
                        [
                            'field'    => 'field_nh_exclusive_product_forms_mode',
                            'operator' => '==',
                            'value'    => 'custom',
                        ],
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'product',
                ],
            ],
        ],
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,
        'menu_order'            => 13,
    ]);
}

add_action('acf/init', 'nice_hair_register_exclusive_product_forms_fields');

function nice_hair_admin_exclusive_product_forms_visibility_css(): void
{
    if (
        function_exists('nice_hair_admin_is_product_edit_screen')
        && ! nice_hair_admin_is_product_edit_screen()
    ) {
        return;
    }

    $group_key = esc_attr(NH_EXCLUSIVE_PRODUCT_FORMS_GROUP_KEY);
    ?>
    <style id="nice-hair-exclusive-product-forms-admin-css">
        body.nh-product-admin-fields .acf-postbox[data-key="<?php echo $group_key; ?>"],
        body.nh-product-admin-fields #acf-<?php echo $group_key; ?> {
            display: none !important;
        }

        body.nh-product-admin-fields.nh-product-family--exclusive_hair .acf-postbox[data-key="<?php echo $group_key; ?>"],
        body.nh-product-admin-fields.nh-product-family--exclusive_hair #acf-<?php echo $group_key; ?> {
            display: block !important;
        }

         .acf-field[data-key="field_nh_exclusive_use_custom_product_forms"] {
            display: none !important;
        }
    </style>
    <?php
}

add_action('admin_head-post.php', 'nice_hair_admin_exclusive_product_forms_visibility_css', 30);
add_action('admin_head-post-new.php', 'nice_hair_admin_exclusive_product_forms_visibility_css', 30);

function nice_hair_exclusive_product_forms_are_restricted(WC_Product|int|null $product = null): bool
{
    $resolved = nice_hair_resolve_product($product);

    if (! $resolved instanceof WC_Product || ! function_exists('get_field')) {
        return false;
    }

    $mode = sanitize_key((string) get_field('nh_exclusive_product_forms_mode', $resolved->get_id()));

    return $mode === 'custom';
}

function nice_hair_get_exclusive_allowed_product_form_keys(WC_Product|int|null $product = null): array
{
    $resolved = nice_hair_resolve_product($product);

    if (
        ! $resolved instanceof WC_Product
        || ! nice_hair_exclusive_product_forms_are_restricted($resolved)
        || ! function_exists('get_field')
    ) {
        return [];
    }

    $selected = get_field('nh_exclusive_available_product_forms', $resolved->get_id());

    if (! is_array($selected)) {
        return [];
    }

    $selected_keys = array_values(array_unique(array_filter(array_map(
        static fn (mixed $value): string => nice_hair_normalize_shop_key((string) $value),
        $selected
    ))));

    $available_keys = array_keys(nice_hair_get_product_form_choices_for_fields());

    if ($available_keys === []) {
        return $selected_keys;
    }

    return array_values(array_intersect($selected_keys, $available_keys));
}

function nice_hair_exclusive_product_form_is_allowed(WC_Product|int|null $product, string $form_key): bool
{
    $resolved = nice_hair_resolve_product($product);

    if (! $resolved instanceof WC_Product) {
        return false;
    }

    if (! nice_hair_exclusive_product_forms_are_restricted($resolved)) {
        return true;
    }

    $normalized_key = nice_hair_normalize_shop_key($form_key);

    if ($normalized_key === '') {
        return false;
    }

    return in_array($normalized_key, nice_hair_get_exclusive_allowed_product_form_keys($resolved), true);
}

function nice_hair_get_current_exclusive_product_for_forms(): ?WC_Product
{
    if (is_admin() || ! function_exists('is_product') || ! is_product()) {
        return null;
    }

    $product = nice_hair_resolve_product(null);

    if (! $product instanceof WC_Product || ! function_exists('nice_hair_product_is_family')) {
        return null;
    }

    return nice_hair_product_is_family('exclusive_hair', $product) ? $product : null;
}

function nice_hair_render_exclusive_product_forms_frontend_css(): void
{
    $product = nice_hair_get_current_exclusive_product_for_forms();

    if (! $product instanceof WC_Product || ! nice_hair_exclusive_product_forms_are_restricted($product)) {
        return;
    }

    $allowed_keys = nice_hair_get_exclusive_allowed_product_form_keys($product);
    $all_keys = array_keys(nice_hair_get_product_form_choices_for_fields());

    $selectors = [];

    if ($all_keys === []) {
        $selectors[] = '.nh-single-product--exclusive-hair [data-nh-product-form-option]';
    } else {
        foreach ($all_keys as $form_key) {
            if (in_array($form_key, $allowed_keys, true)) {
                continue;
            }

            $selectors[] = sprintf(
                '.nh-single-product--exclusive-hair [data-nh-product-form-option][data-form-key="%s"]',
                esc_attr($form_key)
            );
        }
    }

    if ($selectors === []) {
        return;
    }
    ?>
    <style id="nice-hair-exclusive-product-forms-css">
        <?php echo implode(",\n        ", $selectors); ?> {
            display: none !important;
        }
    </style>
    <?php
}

add_action('wp_head', 'nice_hair_render_exclusive_product_forms_frontend_css', 30);

function nice_hair_render_exclusive_product_forms_frontend_script(): void
{
    $product = nice_hair_get_current_exclusive_product_for_forms();

    if (! $product instanceof WC_Product || ! nice_hair_exclusive_product_forms_are_restricted($product)) {
        return;
    }

    $allowed_keys = nice_hair_get_exclusive_allowed_product_form_keys($product);
    ?>
    <script id="nice-hair-exclusive-product-forms-js">
        window.nhExclusiveProductForms = <?php echo wp_json_encode([
            'allowedKeys' => $allowed_keys,
        ]); ?>;

        (function () {
            var config = window.nhExclusiveProductForms || {};
            var allowedKeys = Array.isArray(config.allowedKeys) ? config.allowedKeys : [];
            var allowed = new Set(allowedKeys.map(normalizeKey));
            var attempts = 0;

            function normalizeKey(value) {
                return String(value || '').trim().toLowerCase().replace(/-/g, '_');
            }

            function disablePurchase(root) {
                var input = root.querySelector('[data-nh-product-form]');
                var price = root.querySelector('[data-nh-sp-price]');
                var supportCopy = root.querySelector('[data-nh-product-form-support-copy]');

                if (input) {
                    input.value = '';
                }

                root.querySelectorAll('[data-nh-atc-submit]').forEach(function (button) {
                    button.disabled = true;
                });

                if (price) {
                    price.textContent = 'Price on request';
                    price.classList.remove('nh-single-product__price');
                    price.classList.add('nh-single-product__price-request');
                }

                if (supportCopy) {
                    supportCopy.textContent = '';
                    supportCopy.hidden = true;
                }
            }

            function applyExclusiveProductForms() {
                var root = document.querySelector('.nh-single-product--exclusive-hair');

                if (!root) {
                    return;
                }

                var options = Array.prototype.slice.call(
                    root.querySelectorAll('[data-nh-product-form-option]')
                );

                if (!options.length) {
                    return;
                }

                options.forEach(function (option) {
                    var key = normalizeKey(option.dataset.formKey);
                    var isAllowed = allowed.has(key);

                    option.hidden = !isAllowed;
                    option.style.display = isAllowed ? '' : 'none';
                });

                var visibleOptions = options.filter(function (option) {
                    return !option.hidden && option.style.display !== 'none';
                });

                if (!visibleOptions.length) {
                    disablePurchase(root);
                    return;
                }

                var activeAllowed = visibleOptions.find(function (option) {
                    return option.classList.contains('is-active')
                        && !option.disabled
                        && !option.classList.contains('is-disabled');
                });

                if (activeAllowed) {
                    return;
                }

                var firstAvailable = visibleOptions.find(function (option) {
                    return !option.disabled && !option.classList.contains('is-disabled');
                }) || visibleOptions[0];

                if (firstAvailable) {
                    firstAvailable.click();
                }
            }

            function scheduleApply() {
                applyExclusiveProductForms();

                if (attempts >= 10) {
                    return;
                }

                attempts += 1;
                window.setTimeout(scheduleApply, 100);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', scheduleApply);
            } else {
                scheduleApply();
            }

            window.addEventListener('load', applyExclusiveProductForms, { once: true });
        })();
    </script>
    <?php
}

add_action('wp_footer', 'nice_hair_render_exclusive_product_forms_frontend_script', 99);

function nice_hair_validate_exclusive_product_form_selection(bool $passed, int $product_id): bool
{
    if (! $passed) {
        return false;
    }

    $product = nice_hair_resolve_product($product_id);

    if (! $product instanceof WC_Product || ! function_exists('nice_hair_product_is_family')) {
        return $passed;
    }

    if (
        ! nice_hair_product_is_family('exclusive_hair', $product)
        || ! nice_hair_exclusive_product_forms_are_restricted($product)
    ) {
        return $passed;
    }

    $posted_form = isset($_REQUEST['nh_exclusive_product_form'])
        ? sanitize_text_field(wp_unslash((string) $_REQUEST['nh_exclusive_product_form']))
        : '';

    if (! nice_hair_exclusive_product_form_is_allowed($product, $posted_form)) {
        if (function_exists('wc_add_notice')) {
            wc_add_notice(
                __('Выбранная форма продукта недоступна для этого товара.', 'nice-hair'),
                'error'
            );
        }

        return false;
    }

    return $passed;
}

add_filter('woocommerce_add_to_cart_validation', 'nice_hair_validate_exclusive_product_form_selection', 20, 2);