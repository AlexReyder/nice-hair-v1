<?php
/**
 * Checkout Form
 *
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_checkout_form', $checkout);

if (! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in()) {
    echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'nice-hair')));
    return;
}

$billing_fields = $checkout->get_checkout_fields('billing');
$shipping_fields = $checkout->get_checkout_fields('shipping');
$order_fields = $checkout->get_checkout_fields('order');

$billing_address_field_keys = function_exists('nice_hair_get_checkout_billing_address_field_keys')
    ? nice_hair_get_checkout_billing_address_field_keys()
    : [];
$show_billing_address = function_exists('nice_hair_checkout_uses_separate_billing_address')
    ? nice_hair_checkout_uses_separate_billing_address()
    : false;
$privacy_policy_url = function_exists('get_privacy_policy_url')
    ? get_privacy_policy_url()
    : '';
$primary_shipping_field_keys = [
    'shipping_address_1',
    'shipping_city',
    'shipping_postcode',
];
$advanced_shipping_field_keys = [
    'shipping_country',
    'shipping_state',
];
$first_name_field = $billing_fields['billing_first_name'] ?? null;
$last_name_field = $billing_fields['billing_last_name'] ?? null;
$advanced_panel_open = $show_billing_address;

if (is_array($first_name_field)) {
    $first_name_field['label'] = __('First name', 'nice-hair');
    $first_name_field['placeholder'] = '';
}

if (is_array($last_name_field)) {
    $last_name_field['label'] = __('Last name', 'nice-hair');
    $last_name_field['placeholder'] = '';
}

$prepare_checkout_field = static function ($field) {
    if (! is_array($field)) {
        return $field;
    }

    $field['placeholder'] = '';

    return $field;
};
?>

<div class="nh-checkout-shell">
    <div class="nh-checkout-shell__backdrop" aria-hidden="true"></div>

    <form
        name="checkout"
        method="post"
        class="checkout woocommerce-checkout nh-checkout"
        action="<?php echo esc_url(wc_get_checkout_url()); ?>"
        enctype="multipart/form-data"
        aria-label="<?php echo esc_attr__('Checkout', 'nice-hair'); ?>"
        data-cart-url="<?php echo esc_url(wc_get_cart_url()); ?>"
    >
        <div class="nh-checkout__panel">
            <header class="nh-checkout__header">
                <span class="nh-checkout__eyebrow">[ CHECKOUT ]</span>
                <div class="nh-checkout__rule" aria-hidden="true"></div>
                <h1 class="nh-checkout__title"><?php esc_html_e('Your order', 'nice-hair'); ?></h1>
            </header>

            <?php wc_print_notices(); ?>

            <?php do_action('woocommerce_checkout_before_order_review'); ?>

            <section class="nh-checkout__summary">
                <div id="order_review" class="woocommerce-checkout-review-order">
                    <?php woocommerce_order_review(); ?>
                </div>
            </section>

            <?php do_action('woocommerce_checkout_after_order_review'); ?>

            <?php if ($checkout->get_checkout_fields()) : ?>
                <?php do_action('woocommerce_checkout_before_customer_details'); ?>

                <div class="nh-checkout__lead">
                    <?php esc_html_e('AFTER PLACING YOUR ORDER, WE WILL CONTACT YOU TO CONFIRM THE DETAILS.', 'nice-hair'); ?>
                </div>

                <div id="customer_details" class="nh-checkout__details">
                    <div class="nh-checkout__fields-grid">
                        <div class="nh-checkout__column nh-checkout__column--left">
                            <?php if (is_array($first_name_field) && is_array($last_name_field)) : ?>
                                <div class="nh-checkout__name-group">
                                    <!-- <label class="nh-checkout__group-label"><?php esc_html_e('Name', 'nice-hair'); ?></label> -->

                                    <div class="nh-checkout__name-fields">
                                        <div class="nh-checkout__name-field">
                                            <?php woocommerce_form_field('billing_first_name', $first_name_field, $checkout->get_value('billing_first_name')); ?>
                                        </div>

                                        <div class="nh-checkout__name-field">
                                            <?php woocommerce_form_field('billing_last_name', $last_name_field, $checkout->get_value('billing_last_name')); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php foreach (['billing_email', 'billing_phone', 'billing_whatsapp'] as $field_key) : ?>
                                <?php if (! isset($billing_fields[$field_key])) {
                                    continue;
                                } ?>
                                <?php woocommerce_form_field($field_key, $prepare_checkout_field($billing_fields[$field_key]), $checkout->get_value($field_key)); ?>
                            <?php endforeach; ?>
                        </div>

                        <div class="nh-checkout__column nh-checkout__column--right">
                            <div class="shipping_address nh-checkout__shipping-main">
                                <?php foreach ($primary_shipping_field_keys as $field_key) : ?>
                                    <?php if (! isset($shipping_fields[$field_key])) {
                                        continue;
                                    } ?>
                                    <?php woocommerce_form_field($field_key, $prepare_checkout_field($shipping_fields[$field_key]), $checkout->get_value($field_key)); ?>
                                <?php endforeach; ?>
                            </div>

                            <?php if (isset($order_fields['order_comments'])) : ?>
                                <div class="nh-checkout__comment">
                                    <?php woocommerce_form_field('order_comments', $prepare_checkout_field($order_fields['order_comments']), $checkout->get_value('order_comments')); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div id="ship-to-different-address" class="nh-checkout__shipping-toggle" hidden>
                            <input
                                id="ship-to-different-address-checkbox"
                                class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox"
                                type="checkbox"
                                name="ship_to_different_address"
                                value="1"
                                checked="checked"
                            />
                        </div>

                    </div>
                </div>

                <div class="nh-checkout__payment-meta">
                    <?php woocommerce_checkout_payment(); ?>

                    <div class="nh-checkout__payment-copy">
                        <?php if ($privacy_policy_url !== '') : ?>
                            <?php
                            printf(
                                /* translators: %s: privacy policy URL */
                                wp_kses(
                                    __('By clicking the button, you agree to the <a href="%s">privacy policy</a>.', 'nice-hair'),
                                    ['a' => ['href' => [], 'target' => [], 'rel' => []]]
                                ),
                                esc_url($privacy_policy_url)
                            );
                            ?>
                        <?php else : ?>
                            <?php esc_html_e('By clicking the button, you agree to the privacy policy.', 'nice-hair'); ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="nh-checkout__advanced" data-advanced-checkout>
                    <button
                        type="button"
                        class="nh-checkout__advanced-toggle"
                        data-advanced-toggle
                        aria-expanded="<?php echo $advanced_panel_open ? 'true' : 'false'; ?>"
                    >
                        <?php esc_html_e('Additional address options', 'nice-hair'); ?>
                    </button>

                    <div class="nh-checkout__advanced-panel<?php echo $advanced_panel_open ? ' is-open' : ''; ?>" data-advanced-panel>
                        <div class="shipping_address nh-checkout__shipping-advanced">
                            <?php foreach ($advanced_shipping_field_keys as $field_key) : ?>
                                <?php if (! isset($shipping_fields[$field_key])) {
                                    continue;
                                } ?>
                                <?php woocommerce_form_field($field_key, $prepare_checkout_field($shipping_fields[$field_key]), $checkout->get_value($field_key)); ?>
                            <?php endforeach; ?>
                        </div>

                        <div class="nh-checkout__billing-switch">
                            <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
                                <input
                                    id="nh_use_different_billing_address"
                                    class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox"
                                    type="checkbox"
                                    name="nh_use_different_billing_address"
                                    value="1"
                                    <?php checked($show_billing_address); ?>
                                />
                                <span><?php esc_html_e('Use different billing address', 'nice-hair'); ?></span>
                            </label>
                        </div>

                        <div class="nh-checkout__billing-address<?php echo $show_billing_address ? ' is-open' : ''; ?>" data-billing-address>
                            <?php foreach ($billing_address_field_keys as $field_key) : ?>
                                <?php if (! isset($billing_fields[$field_key])) {
                                    continue;
                                } ?>
                                <?php woocommerce_form_field($field_key, $prepare_checkout_field($billing_fields[$field_key]), $checkout->get_value($field_key)); ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <?php do_action('woocommerce_checkout_after_customer_details'); ?>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
