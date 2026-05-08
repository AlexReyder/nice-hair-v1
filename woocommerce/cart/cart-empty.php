<?php
/**
 * WooCommerce — Empty cart page.
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_cart');

wc_print_notices();

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

    <div class="nh-cart__empty">
        <p class="nh-cart__empty-text"><?php esc_html_e('Your cart is currently empty.', 'nice-hair'); ?></p>
        <a href="<?php echo esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))); ?>" class="nh-cart__checkout-btn">
            <?php esc_html_e('Return to shop', 'nice-hair'); ?>
        </a>
    </div>
</div>
