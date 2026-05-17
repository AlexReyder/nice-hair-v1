<?php

declare(strict_types=1);

$config = isset($args['config']) && is_array($args['config']) ? $args['config'] : [];

$version = max(1, (int) ($config['version'] ?? 1));
$expiration_days = max(1, (int) ($config['expiration_days'] ?? 180));
$title = (string) ($config['title'] ?? 'We use cookies');
$text = (string) ($config['text'] ?? 'We use necessary cookies to make the website work.');
$policy_link = is_array($config['policy_link'] ?? null) ? $config['policy_link'] : [];
$policy_url = (string) ($policy_link['url'] ?? '');
$policy_title = (string) ($policy_link['title'] ?? 'Privacy Policy');
$policy_target = (string) ($policy_link['target'] ?? '');
$accept_all_label = (string) ($config['accept_all_label'] ?? 'Accept all');
$reject_optional_label = (string) ($config['reject_optional_label'] ?? 'Reject optional');
$settings_label = (string) ($config['settings_label'] ?? 'Cookie settings');
$save_label = (string) ($config['save_label'] ?? 'Save choices');
$back_label = (string) ($config['back_label'] ?? 'Back');
$close_label = (string) ($config['close_label'] ?? 'Close cookie settings');
$categories = is_array($config['categories'] ?? null) ? $config['categories'] : [];
$necessary = is_array($categories['necessary'] ?? null) ? $categories['necessary'] : [];
$analytics = is_array($categories['analytics'] ?? null) ? $categories['analytics'] : [];
$marketing = is_array($categories['marketing'] ?? null) ? $categories['marketing'] : [];
$analytics_enabled = (bool) ($analytics['enabled'] ?? false);
$marketing_enabled = (bool) ($marketing['enabled'] ?? false);
$has_optional_categories = $analytics_enabled || $marketing_enabled;
$title_id = 'nh-cookie-consent-title';
$text_id = 'nh-cookie-consent-text';
?>
<div
    class="nh-cookie-consent"
    data-nh-cookie-consent
    data-consent-version="<?php echo esc_attr((string) $version); ?>"
    data-consent-expiration-days="<?php echo esc_attr((string) $expiration_days); ?>"
    role="dialog"
    aria-modal="false"
    aria-labelledby="<?php echo esc_attr($title_id); ?>"
    aria-describedby="<?php echo esc_attr($text_id); ?>"
    hidden
>
    <div class="nh-cookie-consent__panel">
        <button
            type="button"
            class="nh-cookie-consent__close"
            data-nh-cookie-action="close"
            aria-label="<?php echo esc_attr($close_label); ?>"
            hidden
        >
            <span aria-hidden="true">×</span>
        </button>

        <div class="nh-cookie-consent__main" data-nh-cookie-main-panel>
            <div class="nh-cookie-consent__content">
                <p class="nh-cookie-consent__title" id="<?php echo esc_attr($title_id); ?>">
                    <?php echo esc_html($title); ?>
                </p>
                <p class="nh-cookie-consent__text" id="<?php echo esc_attr($text_id); ?>">
                    <?php echo wp_kses_post($text); ?>
                    <?php if ($policy_url !== '') : ?>
                        <a
                            class="nh-cookie-consent__policy-link"
                            href="<?php echo esc_url($policy_url); ?>"
                            <?php if ($policy_target !== '') : ?>target="<?php echo esc_attr($policy_target); ?>" rel="noopener"<?php endif; ?>
                        ><?php echo esc_html($policy_title !== '' ? $policy_title : 'Privacy Policy'); ?></a>
                    <?php endif; ?>
                </p>
            </div>

            <div class="nh-cookie-consent__actions">
                <button type="button" class="nh-cookie-consent__button nh-cookie-consent__button--primary" data-nh-cookie-action="accept-all">
                    <?php echo esc_html($accept_all_label); ?>
                </button>
                <button type="button" class="nh-cookie-consent__button nh-cookie-consent__button--secondary" data-nh-cookie-action="reject-optional">
                    <?php echo esc_html($reject_optional_label); ?>
                </button>
                <?php if ($has_optional_categories) : ?>
                    <button type="button" class="nh-cookie-consent__button nh-cookie-consent__button--ghost" data-nh-cookie-settings-open>
                        <?php echo esc_html($settings_label); ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($has_optional_categories) : ?>
            <div class="nh-cookie-consent__settings" data-nh-cookie-settings-panel hidden>
                <div class="nh-cookie-consent__settings-header">
                    <p class="nh-cookie-consent__settings-title"><?php echo esc_html($settings_label); ?></p>
                </div>

                <div class="nh-cookie-consent__categories">
                    <label class="nh-cookie-consent__category nh-cookie-consent__category--disabled">
                        <input
                            type="checkbox"
                            data-nh-cookie-category="necessary"
                            checked
                            disabled
                        >
                        <span class="nh-cookie-consent__category-copy">
                            <span class="nh-cookie-consent__category-title">
                                <?php echo esc_html((string) ($necessary['title'] ?? 'Necessary cookies')); ?>
                            </span>
                            <span class="nh-cookie-consent__category-description">
                                <?php echo wp_kses_post((string) ($necessary['description'] ?? 'Required for basic website functionality.')); ?>
                            </span>
                        </span>
                    </label>

                    <?php if ($analytics_enabled) : ?>
                        <label class="nh-cookie-consent__category">
                            <input type="checkbox" data-nh-cookie-category="analytics">
                            <span class="nh-cookie-consent__category-copy">
                                <span class="nh-cookie-consent__category-title">
                                    <?php echo esc_html((string) ($analytics['title'] ?? 'Analytics cookies')); ?>
                                </span>
                                <span class="nh-cookie-consent__category-description">
                                    <?php echo wp_kses_post((string) ($analytics['description'] ?? 'Help us improve the website.')); ?>
                                </span>
                            </span>
                        </label>
                    <?php endif; ?>

                    <?php if ($marketing_enabled) : ?>
                        <label class="nh-cookie-consent__category">
                            <input type="checkbox" data-nh-cookie-category="marketing">
                            <span class="nh-cookie-consent__category-copy">
                                <span class="nh-cookie-consent__category-title">
                                    <?php echo esc_html((string) ($marketing['title'] ?? 'Marketing cookies')); ?>
                                </span>
                                <span class="nh-cookie-consent__category-description">
                                    <?php echo wp_kses_post((string) ($marketing['description'] ?? 'May be used for advertising and campaign measurement.')); ?>
                                </span>
                            </span>
                        </label>
                    <?php endif; ?>
                </div>

                <div class="nh-cookie-consent__settings-actions">
                    <button type="button" class="nh-cookie-consent__button nh-cookie-consent__button--primary" data-nh-cookie-action="save-choices">
                        <?php echo esc_html($save_label); ?>
                    </button>
                    <button type="button" class="nh-cookie-consent__button nh-cookie-consent__button--ghost" data-nh-cookie-settings-close>
                        <?php echo esc_html($back_label); ?>
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
