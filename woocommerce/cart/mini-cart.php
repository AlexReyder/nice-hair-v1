<?php
/**
 * Mini-cart contents — custom design for Nice Hair.
 *
 * Overrides: woocommerce/cart/mini-cart.php
 *
 * @see https://woocommerce.com/document/template-structure/
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_mini_cart');
?>

<?php if (! WC()->cart->is_empty()) : ?>

    <ul class="nh-minicart__items woocommerce-mini-cart cart_list product_list_widget">
        <?php
        do_action('woocommerce_before_mini_cart_contents');

        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) :
            $_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
            $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

            if (! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0) {
                continue;
            }

            if (! apply_filters('woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key)) {
                continue;
            }

            $product_name  = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);
            $thumbnail     = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image('woocommerce_thumbnail'), $cart_item, $cart_item_key);
            $product_price = apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key);
            $line_total    = apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key);
            $max_quantity  = $_product->get_max_purchase_quantity();
            $meta_rows     = function_exists('nice_hair_get_cart_item_meta_rows')
                ? nice_hair_get_cart_item_meta_rows($cart_item)
                : [];
            $old_price_total = null;
            $current_unit_price = ! empty($cart_item['nh_exclusive_unit_price']) && is_numeric($cart_item['nh_exclusive_unit_price'])
                ? (float) $cart_item['nh_exclusive_unit_price']
                : (float) $_product->get_price();

            if (
                ! empty($cart_item['nh_exclusive_regular_unit_price'])
                && is_numeric($cart_item['nh_exclusive_regular_unit_price'])
                && (float) $cart_item['nh_exclusive_regular_unit_price'] > $current_unit_price
            ) {
                $old_price_total = wc_price((float) $cart_item['nh_exclusive_regular_unit_price'] * $cart_item['quantity']);
            } elseif ($_product->is_on_sale()) {
                $old_price_total = wc_price((float) $_product->get_regular_price() * $cart_item['quantity']);
            }
            ?>

            <li class="nh-minicart__item woocommerce-mini-cart-item <?php echo esc_attr(apply_filters('woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key)); ?>">
                <div class="nh-minicart__item-image">
                    <?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>

                <div class="nh-minicart__item-info">
                    <h3 class="nh-minicart__item-name">
                        <?php echo wp_kses_post($product_name); ?>
                    </h3>

                    <div class="nh-minicart__item-meta">
                        <?php if ($_product->get_sku()) : ?>
                            <span class="nh-minicart__item-sku">SKU: <?php echo esc_html($_product->get_sku()); ?></span>
                        <?php endif; ?>

                        <?php foreach ($meta_rows as $meta_row) : ?>
                            <span class="nh-minicart__item-attr"><?php echo esc_html((string) ($meta_row['label'] ?? '')); ?>: <?php echo esc_html((string) ($meta_row['value'] ?? '')); ?></span>
                        <?php endforeach; ?>
                    </div>

                    <div class="nh-minicart__item-actions">
                        <div
                            class="nh-minicart__qty"
                            data-cart-key="<?php echo esc_attr($cart_item_key); ?>"
                            <?php if (! $_product->is_sold_individually()) : ?>
                                data-qty-min="0"
                                <?php if ($max_quantity > 0) : ?>data-qty-max="<?php echo esc_attr((string) $max_quantity); ?>"<?php endif; ?>
                            <?php endif; ?>
                        >
                            <?php if ($_product->is_sold_individually()) : ?>
                                <span class="nh-minicart__qty-value">1</span>
                            <?php else : ?>
                                <button type="button" class="nh-minicart__qty-btn" data-nh-qty="minus" aria-label="Decrease quantity">&#8722;</button>
                                <span class="nh-minicart__qty-value"><?php echo esc_html($cart_item['quantity']); ?></span>
                                <button type="button" class="nh-minicart__qty-btn" data-nh-qty="plus" aria-label="Increase quantity">&#43;</button>
                            <?php endif; ?>
                        </div>

                        <div class="nh-minicart__item-price">
                            <?php echo $line_total; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

                            <?php if ($old_price_total !== null) : ?>
                                <span class="nh-minicart__item-price-old">
                                    <?php echo $old_price_total; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php
                        echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            'woocommerce_cart_item_remove_link',
                            sprintf(
                                '<a href="%s" class="nh-minicart__item-remove" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s">&times;</a>',
                                esc_url(wc_get_cart_remove_url($cart_item_key)),
                                esc_attr(sprintf(__('Remove %s from cart', 'nice-hair'), wp_strip_all_tags($product_name))),
                                esc_attr($product_id),
                                esc_attr($cart_item_key),
                                esc_attr($_product->get_sku())
                            ),
                            $cart_item_key
                        );
                        ?>
                    </div>
                </div>
            </li>
        <?php endforeach; ?>

        <?php do_action('woocommerce_mini_cart_contents'); ?>
    </ul>

    <div class="nh-minicart__footer">
        <div class="nh-minicart__total">
            <span class="nh-minicart__total-label">Total:</span>
            <span class="nh-minicart__total-amount"><?php echo WC()->cart->get_cart_subtotal(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
        </div>

        <div class="nh-minicart__buttons">
            <div class="wp-block-button nh-cta-link nh-minicart__cta-link">
                <a class="wp-block-button__link wp-element-button" href="<?php echo esc_url(wc_get_checkout_url()); ?>">
                    <?php esc_html_e('Checkout', 'nice-hair'); ?>
                </a>
            </div>
        </div>
    </div>

<?php else : ?>

    <div class="nh-minicart__empty-state">
        <p class="nh-minicart__empty woocommerce-mini-cart__empty-message">
            <?php esc_html_e('Your cart is empty.', 'nice-hair'); ?>
        </p>

        <div class="nh-minicart__buttons nh-minicart__buttons--empty">
            <div class="wp-block-button nh-cta-link nh-minicart__cta-link nh-minicart__empty-cta">
                <a class="wp-block-button__link wp-element-button" href="<?php echo esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))); ?>">
                    <?php esc_html_e('Return to shop', 'nice-hair'); ?>
                </a>
            </div>
        </div>
    </div>

<?php endif; ?>

<?php do_action('woocommerce_after_mini_cart'); ?>
