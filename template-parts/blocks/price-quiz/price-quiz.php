<?php

declare(strict_types=1);

$eyebrow    = get_field('nh_pq_eyebrow');
$title      = get_field('nh_pq_title');
$text       = get_field('nh_pq_text');
$bg_image   = get_field('nh_pq_bg_image');
$intro_text = get_field('nh_pq_intro_text');

$bg_url = '';
if (is_array($bg_image)) {
    $bg_url = $bg_image['sizes']['large'] ?? $bg_image['url'] ?? '';
}
if ($bg_url === '') {
    $bg_url = get_theme_file_uri('assets/images/quiz-bg.jpg');
}

$privacy_url = (string) nice_hair_get_layout_field('nh_footer_privacy_url', 'salon', '');
if ($privacy_url === '') {
    $privacy_url = (string) nice_hair_get_layout_field('nh_footer_privacy_url', 'home', '#');
}

$anchor    = ! empty($block['anchor'])    ? $block['anchor'] : 'price-quiz';
$className = ! empty($block['className']) ? ' ' . $block['className'] : '';
$rest_url  = esc_url_raw(rest_url('nice-hair/v1/price-quiz'));
$nonce     = wp_create_nonce('wp_rest');
?>

<section
    class="nh-salon-price-quiz<?php echo esc_attr($className); ?>"
    id="<?php echo esc_attr($anchor); ?>"
    data-nh-price-quiz
    data-nh-price-quiz-step="1"
    data-nh-price-quiz-endpoint="<?php echo esc_attr($rest_url); ?>"
    data-nh-price-quiz-nonce="<?php echo esc_attr($nonce); ?>"
    data-nh-price-quiz-privacy="<?php echo esc_url($privacy_url); ?>"
>
    <div class="nh-salon-price-quiz__shell">
        <!-- Row 1: eyebrow + divider (full width) -->
        <div class="nh-salon-price-quiz__top">
            <?php if ($eyebrow) : ?>
                <p class="nh-salon-price-quiz__eyebrow"><?php echo esc_html($eyebrow); ?></p>
            <?php endif; ?>
            <div class="nh-salon-price-quiz__divider" aria-hidden="true"></div>
        </div>

        <!-- Row 2: main grid (heading left, quiz card right) -->
        <div class="nh-salon-price-quiz__main">
            <div class="nh-salon-price-quiz__intro-col">
                <?php if ($title) : ?>
                    <h2 class="nh-salon-price-quiz__title"><?php echo wp_kses_post($title); ?></h2>
                <?php endif; ?>

                <?php if ($text) : ?>
                    <div class="nh-salon-price-quiz__text">
                        <?php echo wp_kses_post(wpautop($text)); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- RIGHT: quiz card -->
        <div class="nh-salon-price-quiz__card" style="background-image: url('<?php echo esc_url($bg_url); ?>');">
            <div class="nh-salon-price-quiz__card-tint" aria-hidden="true"></div>

            <div class="nh-salon-price-quiz__card-body">
                <header class="nh-salon-price-quiz__card-header">
                   

                    <p class="nh-salon-price-quiz__stepper" data-nh-pq-stepper>Step 1. / 4</p>

                    <?php if ($intro_text) : ?>
                        <p class="nh-salon-price-quiz__intro"><?php echo esc_html($intro_text); ?></p>
                    <?php endif; ?>
                </header>

                <div class="nh-salon-price-quiz__card-divider" aria-hidden="true"></div>

                <div class="nh-salon-price-quiz__steps">
                    <!-- Step 1 — Goal (checkbox multi) -->
                    <div class="nh-salon-price-quiz__step" data-step="1" role="group" aria-labelledby="nh-pq-q-1">
                        <p class="nh-salon-price-quiz__question" id="nh-pq-q-1"><?php esc_html_e('What would you like to change?', 'nice-hair'); ?></p>
                        <div class="nh-salon-price-quiz__options">
                            <label class="nh-salon-price-quiz__option">
                                <input type="checkbox" name="goal" value="add_length">
                                <span class="nh-salon-price-quiz__option-box" aria-hidden="true"></span>
                                <span class="nh-salon-price-quiz__option-label"><?php esc_html_e('Add length', 'nice-hair'); ?></span>
                            </label>
                            <label class="nh-salon-price-quiz__option">
                                <input type="checkbox" name="goal" value="add_volume">
                                <span class="nh-salon-price-quiz__option-box" aria-hidden="true"></span>
                                <span class="nh-salon-price-quiz__option-label"><?php esc_html_e('Add volume', 'nice-hair'); ?></span>
                            </label>
                            <label class="nh-salon-price-quiz__option">
                                <input type="checkbox" name="goal" value="fill_sparse">
                                <span class="nh-salon-price-quiz__option-box" aria-hidden="true"></span>
                                <span class="nh-salon-price-quiz__option-label"><?php esc_html_e('Fill in thin or sparse areas', 'nice-hair'); ?></span>
                            </label>
                            <label class="nh-salon-price-quiz__option">
                                <input type="checkbox" name="goal" value="refresh_previous">
                                <span class="nh-salon-price-quiz__option-box" aria-hidden="true"></span>
                                <span class="nh-salon-price-quiz__option-label"><?php esc_html_e('Refresh previous extensions', 'nice-hair'); ?></span>
                            </label>
                            <label class="nh-salon-price-quiz__option">
                                <input type="checkbox" name="goal" value="not_sure">
                                <span class="nh-salon-price-quiz__option-box" aria-hidden="true"></span>
                                <span class="nh-salon-price-quiz__option-label"><?php esc_html_e('Not sure — I need a consultation', 'nice-hair'); ?></span>
                            </label>
                        </div>
                    </div>

                    <!-- Step 2 — Desired length (radio single) -->
                    <div class="nh-salon-price-quiz__step" data-step="2" role="group" aria-labelledby="nh-pq-q-2">
                        <p class="nh-salon-price-quiz__question" id="nh-pq-q-2"><?php esc_html_e('Desired length', 'nice-hair'); ?></p>
                        <div class="nh-salon-price-quiz__options">
                            <label class="nh-salon-price-quiz__option">
                                <input type="radio" name="length" value="shoulder">
                                <span class="nh-salon-price-quiz__option-box" aria-hidden="true"></span>
                                <span class="nh-salon-price-quiz__option-label"><?php esc_html_e('Shoulder-length', 'nice-hair'); ?></span>
                            </label>
                            <label class="nh-salon-price-quiz__option">
                                <input type="radio" name="length" value="below_shoulders">
                                <span class="nh-salon-price-quiz__option-box" aria-hidden="true"></span>
                                <span class="nh-salon-price-quiz__option-label"><?php esc_html_e('Below shoulders', 'nice-hair'); ?></span>
                            </label>
                            <label class="nh-salon-price-quiz__option">
                                <input type="radio" name="length" value="waist">
                                <span class="nh-salon-price-quiz__option-box" aria-hidden="true"></span>
                                <span class="nh-salon-price-quiz__option-label"><?php esc_html_e('Waist-length', 'nice-hair'); ?></span>
                            </label>
                            <label class="nh-salon-price-quiz__option">
                                <input type="radio" name="length" value="longer_than_waist">
                                <span class="nh-salon-price-quiz__option-box" aria-hidden="true"></span>
                                <span class="nh-salon-price-quiz__option-label"><?php esc_html_e('Longer than waist', 'nice-hair'); ?></span>
                            </label>
                        </div>
                    </div>

                    <!-- Step 3 — Current extensions (radio yes/no) -->
                    <div class="nh-salon-price-quiz__step" data-step="3" role="group" aria-labelledby="nh-pq-q-3">
                        <p class="nh-salon-price-quiz__question" id="nh-pq-q-3"><?php esc_html_e('Do you wear hair extension now?', 'nice-hair'); ?></p>
                        <div class="nh-salon-price-quiz__options">
                            <label class="nh-salon-price-quiz__option">
                                <input type="radio" name="current_ext" value="yes">
                                <span class="nh-salon-price-quiz__option-box" aria-hidden="true"></span>
                                <span class="nh-salon-price-quiz__option-label"><?php esc_html_e('Yes', 'nice-hair'); ?></span>
                            </label>
                            <label class="nh-salon-price-quiz__option">
                                <input type="radio" name="current_ext" value="no">
                                <span class="nh-salon-price-quiz__option-box" aria-hidden="true"></span>
                                <span class="nh-salon-price-quiz__option-label"><?php esc_html_e('No', 'nice-hair'); ?></span>
                            </label>
                        </div>
                    </div>

                    <!-- Step 4 — Photo upload -->
                    <div class="nh-salon-price-quiz__step" data-step="4" role="group" aria-labelledby="nh-pq-q-4">
                        <p class="nh-salon-price-quiz__question" id="nh-pq-q-4"><?php esc_html_e('Your current hair', 'nice-hair'); ?></p>

                        <div class="nh-salon-price-quiz__upload">
                            <p class="nh-salon-price-quiz__upload-hint">
                                <?php esc_html_e('Upload 1–3 photos (front, side, back) — this helps us accurately calculate the amount and match the hair.', 'nice-hair'); ?>
                            </p>

                            <div class="nh-salon-price-quiz__upload-row">
                                <button type="button" class="nh-salon-price-quiz__upload-btn nh-salon-price-quiz__upload-btn--primary" data-nh-pq-upload>
                                    <span class="nh-salon-price-quiz__upload-btn-label"><?php esc_html_e('UPLOAD PHOTOS', 'nice-hair'); ?></span>
                                    <span class="nh-salon-price-quiz__upload-badge" data-nh-pq-badge hidden>0</span>
                                </button>

                                <div class="nh-salon-price-quiz__upload-or">
                                    <button type="button" class="nh-salon-price-quiz__upload-btn nh-salon-price-quiz__upload-btn--secondary" data-nh-pq-no-photos>
                                        <span class="nh-salon-price-quiz__upload-btn-label"><?php esc_html_e('NO PHOTOS', 'nice-hair'); ?></span>
                                    </button>
                                </div>
                            </div>

                            <input
                                type="file"
                                name="photos[]"
                                accept="image/jpeg,image/png,image/webp"
                                multiple
                                class="nh-salon-price-quiz__file-input"
                                data-nh-pq-file-input
                                aria-hidden="true"
                                tabindex="-1"
                            >
                        </div>
                    </div>

                    <!-- Contact screen -->
                    <div class="nh-salon-price-quiz__step" data-step="contact" role="group" aria-labelledby="nh-pq-q-contact">
                        <p class="nh-salon-price-quiz__question" id="nh-pq-q-contact"><?php esc_html_e('Your contact info', 'nice-hair'); ?></p>

                        <div class="nh-salon-price-quiz__contact-fields">
                            <label class="nh-salon-price-quiz__field">
                                <span class="nh-salon-price-quiz__field-label"><?php esc_html_e('NAME', 'nice-hair'); ?></span>
                                <input
                                    type="text"
                                    name="nh_pq_name"
                                    class="nh-salon-price-quiz__input"
                                    autocomplete="name"
                                    maxlength="100"
                                    required
                                >
                            </label>

                            <label class="nh-salon-price-quiz__field">
                                <span class="nh-salon-price-quiz__field-label"><?php esc_html_e('WHATSAPP NUMBER', 'nice-hair'); ?></span>
                                <input
                                    type="tel"
                                    name="nh_pq_whatsapp"
                                    class="nh-salon-price-quiz__input"
                                    autocomplete="tel"
                                    inputmode="tel"
                                    required
                                >
                            </label>
                        </div>
                    </div>

                    <!-- Success state -->
                    <div class="nh-salon-price-quiz__success" data-nh-pq-success hidden>
                        <svg class="nh-salon-price-quiz__success-icon" viewBox="0 0 44 44" aria-hidden="true">
                            <circle cx="22" cy="22" r="20" fill="none" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M14 22 L20 28 L31 17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <p class="nh-salon-price-quiz__success-text" data-nh-pq-success-message>
                            <?php esc_html_e('Thanks! We will reach out via WhatsApp shortly.', 'nice-hair'); ?>
                        </p>
                        <a class="nh-salon-price-quiz__success-link" data-nh-pq-whatsapp-btn href="#" target="_blank" rel="noopener" hidden>
                            <?php esc_html_e('Open WhatsApp', 'nice-hair'); ?>
                        </a>
                    </div>
                </div>

                <p class="nh-salon-price-quiz__hint" data-nh-pq-hint role="alert" aria-live="polite" hidden></p>

                <footer class="nh-salon-price-quiz__card-footer">
                    <button type="button" class="nh-salon-price-quiz__next" data-nh-pq-next>
                        <span class="nh-salon-price-quiz__next-label"><?php esc_html_e('NEXT', 'nice-hair'); ?></span>
                    </button>

                    <p class="nh-salon-price-quiz__disclaimer" data-nh-pq-disclaimer hidden>
                        <?php
                        printf(
                            /* translators: %s: link to the privacy policy */
                            esc_html__('By clicking the button, you agree to the %s', 'nice-hair'),
                            sprintf(
                                '<a href="%s" target="_blank" rel="noopener">%s</a>',
                                esc_url($privacy_url),
                                esc_html__('privacy policy', 'nice-hair')
                            )
                        );
                        ?>
                    </p>
                </footer>

                <!-- Honeypot — hidden from real users, bots auto-fill it. -->
                <input
                    type="text"
                    name="hp_field"
                    value=""
                    tabindex="-1"
                    autocomplete="off"
                    aria-hidden="true"
                    class="nh-salon-price-quiz__hp"
                    data-nh-pq-honeypot
                >
            </div>
        </div>
        </div>
    </div>
</section>
