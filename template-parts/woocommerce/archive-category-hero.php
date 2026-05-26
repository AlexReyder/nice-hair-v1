<?php
/**
 * Shared WooCommerce product category hero.
 *
 * @var array{
 *     title?: string,
 *     description?: string,
 *     breadcrumbs?: array<int, array{label?: string, url?: string}>,
 *     phone?: string,
 *     phone_link?: string,
 *     address?: string,
 *     modifier?: string
 * } $args
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$nh_title = trim((string) ($args['title'] ?? ''));
$nh_description = trim((string) ($args['description'] ?? ''));
$nh_breadcrumbs = is_array($args['breadcrumbs'] ?? null) ? $args['breadcrumbs'] : [];
$nh_phone = trim((string) ($args['phone'] ?? ''));
$nh_phone_link = trim((string) ($args['phone_link'] ?? ''));
$nh_address = trim((string) ($args['address'] ?? ''));
$nh_modifier = sanitize_html_class((string) ($args['modifier'] ?? ''));
$nh_show_whatsapp_cta = in_array($nh_modifier, ['custom-hair', 'ready-to-install'], true);
$nh_classes = ['nh-shop-category-hero'];

if ($nh_modifier !== '') {
    $nh_classes[] = 'nh-shop-category-hero--' . $nh_modifier;
}

if ($nh_show_whatsapp_cta) {
    $nh_classes[] = 'nh-shop-category-hero--has-whatsapp-cta';
}

if ($nh_title === '') {
    return;
}
?>

<section class="<?php echo esc_attr(implode(' ', $nh_classes)); ?>">
    <div class="nh-shop-category-hero__shell">
        <div class="nh-shop-category-hero__top">
            <?php if ($nh_breadcrumbs !== []) : ?>
                <p class="nh-shop-category-hero__breadcrumb">
                    <?php foreach ($nh_breadcrumbs as $nh_index => $nh_crumb) : ?>
                        <?php
                        $nh_label = trim((string) ($nh_crumb['label'] ?? ''));
                        $nh_url = trim((string) ($nh_crumb['url'] ?? ''));
                        $nh_is_last = $nh_index === array_key_last($nh_breadcrumbs);

                        if ($nh_label === '') {
                            continue;
                        }
                        ?>
                        <?php if ($nh_index > 0) : ?>
                            <span class="nh-shop-category-hero__breadcrumb-separator" aria-hidden="true">-</span>
                        <?php endif; ?>

                        <?php if (! $nh_is_last && $nh_url !== '') : ?>
                            <a href="<?php echo esc_url($nh_url); ?>"><?php echo esc_html($nh_label); ?></a>
                        <?php else : ?>
                            <span><?php echo esc_html($nh_label); ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </p>
            <?php endif; ?>

            <?php if ($nh_phone !== '' || $nh_address !== '') : ?>
                <div class="nh-shop-category-hero__contact">
                    <?php if ($nh_phone !== '') : ?>
                        <p class="nh-shop-category-hero__phone">
                            <?php if ($nh_phone_link !== '') : ?>
                                <a href="tel:<?php echo esc_attr($nh_phone_link); ?>"><?php echo esc_html($nh_phone); ?></a>
                            <?php else : ?>
                                <?php echo esc_html($nh_phone); ?>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>

                    <?php if ($nh_address !== '') : ?>
                        <p class="nh-shop-category-hero__address"><?php echo esc_html($nh_address); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <h1 class="nh-shop-category-hero__title">/ <?php echo esc_html($nh_title); ?></h1>

        <?php if ($nh_description !== '') : ?>
            <p class="nh-shop-category-hero__subtitle"><?php echo esc_html($nh_description); ?></p>
        <?php endif; ?>

        <?php if ($nh_show_whatsapp_cta) : ?>
            <?php
            get_template_part('template-parts/shop/whatsapp-cta', null, [
                'class' => 'nh-shop-category-hero__cta',
            ]);
            ?>
        <?php endif; ?>
    </div>
</section>