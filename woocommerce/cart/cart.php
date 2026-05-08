<?php
/**
 * WooCommerce — Cart page.
 *
 * @see https://woocommerce.com/document/template-structure/
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_cart');

$ajax_url = class_exists('WC_AJAX')
    ? WC_AJAX::get_endpoint('%%endpoint%%')
    : home_url('/?wc-ajax=%%endpoint%%');
?>

<div class="nh-cart" data-wc-ajax-url="<?php echo esc_attr($ajax_url); ?>">
    <div class="nh-cart__header">
        <span class="nh-cart__eyebrow">[ CART ]</span>
        <div class="nh-cart__rule"></div>
        <h1 class="nh-cart__title"><?php esc_html_e('Your order', 'nice-hair'); ?></h1>
    </div>

    <?php if (! WC()->cart->is_empty()) : ?>
        <form class="woocommerce-cart-form nh-cart__form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
            <?php do_action('woocommerce_before_cart_table'); ?>

            <?php get_template_part('template-parts/woocommerce/order-summary-items', null, ['context' => 'cart']); ?>

            <?php do_action('woocommerce_after_cart_table'); ?>

            <div class="nh-cart__footer">
                <div class="nh-cart__footer-main">
                    <div class="nh-cart__buttons">
                        <div class="wp-block-button nh-cta-link nh-cart__cta-link">
                            <a class="wp-block-button__link wp-element-button" href="<?php echo esc_url(wc_get_checkout_url()); ?>">
                                <?php esc_html_e('Checkout', 'nice-hair'); ?>
                            </a>
                        </div>
                    </div>

                    <div class="nh-order-summary__total nh-cart__total">
                        <span class="nh-order-summary__total-label"><?php esc_html_e('Total:', 'nice-hair'); ?></span>
                        <span class="nh-order-summary__total-amount"><?php echo WC()->cart->get_cart_subtotal(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                    </div>
                </div>
            </div>

            <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
        </form>
    <?php else : ?>
        <div class="nh-cart__empty">
            <p class="nh-cart__empty-text"><?php esc_html_e('Your cart is currently empty.', 'nice-hair'); ?></p>
            <a href="<?php echo esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))); ?>" class="nh-cart__checkout-btn">
                <?php esc_html_e('Return to shop', 'nice-hair'); ?>
            </a>
        </div>
    <?php endif; ?>
</div>

<?php do_action('woocommerce_after_cart'); ?>
