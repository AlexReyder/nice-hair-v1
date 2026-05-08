<?php
/**
 * Review order table
 *
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

defined('ABSPATH') || exit;

$packages = WC()->shipping()->get_packages();
$chosen_shipping_methods = WC()->session->get('chosen_shipping_methods', []);
?>
<div class="nh-checkout-summary woocommerce-checkout-review-order-table">
    <?php
    do_action('woocommerce_review_order_before_cart_contents');
    get_template_part('template-parts/woocommerce/order-summary-items', null, ['context' => 'checkout']);
    do_action('woocommerce_review_order_after_cart_contents');
    ?>

    <?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>
        <?php do_action('woocommerce_review_order_before_shipping'); ?>

        <?php foreach ($packages as $package_index => $package) : ?>
            <?php
            $rates = isset($package['rates']) && is_array($package['rates']) ? $package['rates'] : [];
            $chosen_method = isset($chosen_shipping_methods[$package_index]) ? (string) $chosen_shipping_methods[$package_index] : '';

            if ($rates === []) {
                continue;
            }

            if ($chosen_method === '') {
                $chosen_method = (string) array_key_first($rates);
            }

            $show_shipping_methods = count($rates) > 1;
            ?>
            <div class="nh-checkout-summary__shipping-methods<?php echo $show_shipping_methods ? '' : ' nh-checkout-summary__shipping-methods--single'; ?>">
                <?php if ($show_shipping_methods) : ?>
                    <div class="nh-checkout-summary__shipping-title"><?php esc_html_e('Shipping method', 'nice-hair'); ?></div>
                <?php endif; ?>

                <?php foreach ($rates as $rate_id => $rate) : ?>
                    <?php if ($show_shipping_methods) : ?>
                        <?php $input_id = 'shipping_method_' . $package_index . '_' . sanitize_title((string) $rate_id); ?>
                        <label class="nh-checkout-summary__shipping-option" for="<?php echo esc_attr($input_id); ?>">
                            <input
                                id="<?php echo esc_attr($input_id); ?>"
                                class="shipping_method"
                                type="radio"
                                name="shipping_method[<?php echo esc_attr((string) $package_index); ?>]"
                                data-index="<?php echo esc_attr((string) $package_index); ?>"
                                value="<?php echo esc_attr((string) $rate_id); ?>"
                                <?php checked($rate_id, $chosen_method); ?>
                            />
                            <span><?php echo wp_kses_post(wc_cart_totals_shipping_method_label($rate)); ?></span>
                        </label>
                    <?php elseif ((string) $rate_id === $chosen_method) : ?>
                        <input
                            type="hidden"
                            class="shipping_method"
                            name="shipping_method[<?php echo esc_attr((string) $package_index); ?>]"
                            data-index="<?php echo esc_attr((string) $package_index); ?>"
                            value="<?php echo esc_attr((string) $rate_id); ?>"
                        />
                        <div class="nh-checkout-summary__shipping-line">
                            <span class="nh-checkout-summary__shipping-label"><?php esc_html_e('Delivery:', 'nice-hair'); ?></span>
                            <span class="nh-checkout-summary__shipping-value"><?php echo wp_kses_post(wc_price((float) $rate->cost + (float) array_sum((array) $rate->taxes))); ?></span>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <?php do_action('woocommerce_review_order_after_shipping'); ?>
    <?php endif; ?>

    <div class="nh-order-summary__total nh-checkout-summary__total">
        <span class="nh-order-summary__total-label"><?php esc_html_e('Total:', 'nice-hair'); ?></span>
        <span class="nh-order-summary__total-amount"><?php wc_cart_totals_order_total_html(); ?></span>
    </div>
</div>
