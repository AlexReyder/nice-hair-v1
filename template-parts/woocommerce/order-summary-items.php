<?php
/**
 * Shared cart / checkout order summary item list.
 *
 * @package NiceHair
 */

defined('ABSPATH') || exit;

$context = isset($args['context']) && $args['context'] === 'checkout' ? 'checkout' : 'cart';
$visibility_filter = $context === 'checkout'
    ? 'woocommerce_checkout_cart_item_visible'
    : 'woocommerce_cart_item_visible';
$priority_meta_labels = ['length', 'hair quality', 'texture'];
?>

<ul class="nh-order-summary__items" data-nh-order-summary="<?php echo esc_attr($context); ?>">
    <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) :
        $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
        $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

        if (! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0) {
            continue;
        }

        if (! apply_filters($visibility_filter, true, $cart_item, $cart_item_key)) {
            continue;
        }

        $product_name = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);
        $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image('woocommerce_thumbnail'), $cart_item, $cart_item_key);
        $line_total = apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key);
        $meta_rows = function_exists('nice_hair_get_cart_item_meta_rows')
            ? nice_hair_get_cart_item_meta_rows($cart_item)
            : [];
        $old_price_total = null;
        $current_unit_price = ! empty($cart_item['nh_exclusive_unit_price']) && is_numeric($cart_item['nh_exclusive_unit_price'])
            ? (float) $cart_item['nh_exclusive_unit_price']
            : (! empty($cart_item['nh_custom_hair_unit_price']) && is_numeric($cart_item['nh_custom_hair_unit_price'])
                ? (float) $cart_item['nh_custom_hair_unit_price']
                : (float) $_product->get_price());
        $max_quantity = $_product->get_max_purchase_quantity();

        if (
            ! empty($cart_item['nh_exclusive_regular_unit_price'])
            && is_numeric($cart_item['nh_exclusive_regular_unit_price'])
            && (float) $cart_item['nh_exclusive_regular_unit_price'] > $current_unit_price
        ) {
            $old_price_total = wc_price((float) $cart_item['nh_exclusive_regular_unit_price'] * $cart_item['quantity']);
        } elseif ($_product->is_on_sale()) {
            $old_price_total = wc_price((float) $_product->get_regular_price() * $cart_item['quantity']);
        }

        $visible_meta_rows = [];
        $remaining_meta_rows = [];

        foreach ($meta_rows as $meta_row) {
            $label = isset($meta_row['label']) ? strtolower(trim((string) $meta_row['label'])) : '';

            if (in_array($label, $priority_meta_labels, true)) {
                $visible_meta_rows[$label] = $meta_row;
                continue;
            }

            $remaining_meta_rows[] = $meta_row;
        }

        $ordered_meta_rows = [];

        foreach ($priority_meta_labels as $priority_label) {
            if (isset($visible_meta_rows[$priority_label])) {
                $ordered_meta_rows[] = $visible_meta_rows[$priority_label];
            }
        }

        foreach ($remaining_meta_rows as $meta_row) {
            if (count($ordered_meta_rows) >= 3) {
                break;
            }

            $ordered_meta_rows[] = $meta_row;
        }
        ?>

        <li class="nh-order-summary__item <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>" data-nh-order-summary-item>
            <div class="nh-order-summary__item-image">
                <?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>

            <div class="nh-order-summary__item-info">
                <h3 class="nh-order-summary__item-name"><?php echo wp_kses_post($product_name); ?></h3>

                <div class="nh-order-summary__item-meta">
                    <?php if ($_product->get_sku()) : ?>
                        <span class="nh-order-summary__item-sku">SKU: <?php echo esc_html($_product->get_sku()); ?></span>
                    <?php endif; ?>

                    <?php foreach ($ordered_meta_rows as $meta_row) : ?>
                        <span class="nh-order-summary__item-attr"><?php echo esc_html((string) ($meta_row['label'] ?? '')); ?>: <?php echo esc_html((string) ($meta_row['value'] ?? '')); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="nh-order-summary__item-actions">
                <div
                    class="nh-order-summary__qty"
                    data-cart-key="<?php echo esc_attr($cart_item_key); ?>"
                    data-qty-min="0"
                    <?php if ($_product->is_sold_individually()) : ?>
                        data-qty-max="1"
                    <?php elseif ($max_quantity > 0) : ?>
                        data-qty-max="<?php echo esc_attr((string) $max_quantity); ?>"
                    <?php endif; ?>
                >
                    <button type="button" class="nh-order-summary__qty-btn" data-nh-order-summary-qty="minus" aria-label="<?php esc_attr_e('Decrease quantity', 'nice-hair'); ?>">&#8722;</button>
                    <span class="nh-order-summary__qty-value"><?php echo esc_html((string) $cart_item['quantity']); ?></span>
                    <button type="button" class="nh-order-summary__qty-btn" data-nh-order-summary-qty="plus" aria-label="<?php esc_attr_e('Increase quantity', 'nice-hair'); ?>">&#43;</button>
                </div>

                <div class="nh-order-summary__item-price">
                    <?php echo $line_total; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

                    <?php if ($old_price_total !== null) : ?>
                        <span class="nh-order-summary__item-price-old">
                            <?php echo $old_price_total; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </span>
                    <?php endif; ?>
                </div>

                <?php
                echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    'woocommerce_cart_item_remove_link',
                    sprintf(
                        '<a href="%s" class="nh-order-summary__item-remove" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s">&times;</a>',
                        esc_url(wc_get_cart_remove_url($cart_item_key)),
                        esc_attr(sprintf(__('Remove %s from cart', 'nice-hair'), wp_strip_all_tags($product_name))),
                        esc_attr((string) $product_id),
                        esc_attr($cart_item_key),
                        esc_attr($_product->get_sku())
                    ),
                    $cart_item_key
                );
                ?>
            </div>
        </li>
    <?php endforeach; ?>
</ul>
