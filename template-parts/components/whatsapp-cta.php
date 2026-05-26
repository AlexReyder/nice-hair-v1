<?php
/**
 * Shared Shop WhatsApp CTA.
 *
 * @var array{
 *     class?: string,
 *     text?: string,
 *     button_text?: string,
 *     url?: string
 * } $args
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$nh_extra_class = trim((string) ($args['class'] ?? ''));
$nh_text = trim((string) ($args['text'] ?? "Didn’t find what you’re looking for?\nSend us a message — we’re here to help."));
$nh_button_text = trim((string) ($args['button_text'] ?? 'Get in Touch'));
$nh_url = trim((string) ($args['url'] ?? 'http://wa.me/971585988409'));

if ($nh_text === '' || $nh_button_text === '' || $nh_url === '') {
    return;
}

$nh_classes = ['nh-shop-whatsapp-cta'];

if ($nh_extra_class !== '') {
    $nh_extra_classes = preg_split('/\s+/', $nh_extra_class) ?: [];

    foreach ($nh_extra_classes as $nh_class) {
        $nh_class = sanitize_html_class((string) $nh_class);

        if ($nh_class !== '') {
            $nh_classes[] = $nh_class;
        }
    }
}

$nh_text_lines = preg_split('/\R/u', $nh_text) ?: [$nh_text];
$nh_text_lines = array_values(array_filter(array_map(
    static fn (string $line): string => trim($line),
    $nh_text_lines
)));
?>

<div class="<?php echo esc_attr(implode(' ', array_unique($nh_classes))); ?>">
    <p class="nh-shop-whatsapp-cta__text">
        <?php foreach ($nh_text_lines as $nh_index => $nh_line) : ?>
            <?php if ($nh_index > 0) : ?>
                <br>
            <?php endif; ?>

            <?php echo esc_html($nh_line); ?>
        <?php endforeach; ?>
    </p>

    <span class="nh-shop-whatsapp-cta__button nh-cta-link nh-cta-link--dark">
        <a
            class="wp-block-button__link"
            href="<?php echo esc_url($nh_url); ?>"
            target="_blank"
            rel="noopener noreferrer"
        >
            <?php echo esc_html($nh_button_text); ?>
        </a>
    </span>
</div>