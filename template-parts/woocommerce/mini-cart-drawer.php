<?php
/**
 * Mini-cart drawer — slides in from the right on "add to cart".
 *
 * Inserted via wp_footer hook in inc/woocommerce/setup.php.
 */

defined('ABSPATH') || exit;

if (! function_exists('WC')) {
    return;
}
?>

<div class="nh-cart-drawer" id="nh-cart-drawer" hidden>
    <div class="nh-cart-drawer__backdrop" data-nh-cart-close></div>

    <div class="nh-cart-drawer__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Shopping cart', 'nice-hair'); ?>">
        <button type="button" class="nh-cart-drawer__close" data-nh-cart-close aria-label="<?php esc_attr_e('Close cart', 'nice-hair'); ?>">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" aria-hidden="true">
                <circle cx="16" cy="16" r="15.5" stroke="currentColor"/>
                <path d="M11 11l10 10M21 11l-10 10" stroke="currentColor" stroke-width="1.5"/>
            </svg>
        </button>

        <div class="nh-cart-drawer__header">
            <span class="nh-cart-drawer__eyebrow">[ CART ]</span>
            <div class="nh-cart-drawer__rule"></div>
            <h2 class="nh-cart-drawer__title">Your order</h2>
        </div>

        <div class="nh-cart-drawer__body" id="nh-cart-drawer-body">
            <?php woocommerce_mini_cart(); ?>
        </div>
    </div>
</div>
