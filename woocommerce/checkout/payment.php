<?php
/**
 * Checkout Payment Section
 *
 * @package WooCommerce\Templates
 * @version 9.8.0
 */

defined('ABSPATH') || exit;

if (! wp_doing_ajax()) {
    do_action('woocommerce_review_order_before_payment');
}
?>
<div id="payment" class="woocommerce-checkout-payment nh-checkout__payment">
    <?php if (WC()->cart && WC()->cart->needs_payment()) : ?>
        <?php if (! empty($available_gateways)) : ?>
            <ul class="wc_payment_methods payment_methods methods nh-checkout__payment-methods">
                <?php foreach ($available_gateways as $gateway) : ?>
                    <?php wc_get_template('checkout/payment-method.php', ['gateway' => $gateway]); ?>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <?php
            wc_print_notice(
                apply_filters(
                    'woocommerce_no_available_payment_methods_message',
                    WC()->customer->get_billing_country()
                        ? esc_html__('Sorry, it seems that there are no available payment methods. Please contact us if you require assistance or wish to make alternate arrangements.', 'nice-hair')
                        : esc_html__('Please fill in your details above to see available payment methods.', 'nice-hair')
                ),
                'notice'
            );
            ?>
        <?php endif; ?>
    <?php endif; ?>

    <div class="form-row place-order nh-checkout__place-order">
        <noscript>
            <?php
            printf(
                esc_html__('Since your browser does not support JavaScript, or it is disabled, please ensure you click the %1$sUpdate Totals%2$s button before placing your order.', 'nice-hair'),
                '<em>',
                '</em>'
            );
            ?>
            <br />
            <button type="submit" class="nh-checkout__fallback-update" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e('Update totals', 'nice-hair'); ?>">
                <?php esc_html_e('Update totals', 'nice-hair'); ?>
            </button>
        </noscript>

        <?php wc_get_template('checkout/terms.php'); ?>

        <?php do_action('woocommerce_review_order_before_submit'); ?>

        <span class="nh-checkout__submit-cta nh-cta-link">
            <button type="submit" class="nh-checkout__submit wp-block-button__link" name="woocommerce_checkout_place_order" id="place_order" value="<?php esc_attr_e('Checkout', 'nice-hair'); ?>" data-value="<?php esc_attr_e('Checkout', 'nice-hair'); ?>">
                <?php esc_html_e('Checkout', 'nice-hair'); ?>
            </button>
        </span>

        <?php do_action('woocommerce_review_order_after_submit'); ?>

        <?php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); ?>
    </div>
</div>
<?php
if (! wp_doing_ajax()) {
    do_action('woocommerce_review_order_after_payment');
}
