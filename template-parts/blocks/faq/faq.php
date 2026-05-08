<?php

declare(strict_types=1);

$eyebrow  = get_field('nh_faq_eyebrow');
$title    = get_field('nh_faq_title');
$subtitle = get_field('nh_faq_subtitle');
$items    = get_field('nh_faq_items');

if (empty($items) || ! is_array($items)) {
    return;
}

$anchor    = ! empty($block['anchor']) ? $block['anchor'] : 'faq';
$className = ! empty($block['className']) ? ' ' . $block['className'] : '';

$mid  = (int) ceil(count($items) / 2);
$col1 = array_slice($items, 0, $mid);
$col2 = array_slice($items, $mid);
$block_id = esc_attr($block['id']);
?>

<section class="nh-faq<?php echo esc_attr($className); ?>" id="<?php echo esc_attr($anchor); ?>">
    <div class="nh-faq__shell">
        <div class="nh-faq__header">
            <?php if ($eyebrow) : ?>
                <p class="nh-faq__eyebrow"><?php echo esc_html($eyebrow); ?></p>
            <?php endif; ?>

            <div class="nh-faq__divider"></div>

            <?php if ($title) : ?>
                <h2 class="nh-faq__title"><?php echo esc_html($title); ?></h2>
            <?php endif; ?>

            <?php if ($subtitle) : ?>
                <p class="nh-faq__subtitle"><?php echo wp_kses_post($subtitle); ?></p>
            <?php endif; ?>
        </div>

        <div class="nh-faq__grid">
            <?php
            $columns = [$col1, $col2];
            $global_index = 0;

            foreach ($columns as $column) :
                if (empty($column)) {
                    continue;
                }
                ?>
                <div class="nh-faq__column">
                    <?php foreach ($column as $item) :
                        $is_open   = ! empty($item['is_open']);
                        $item_id   = $block_id . '-item-' . $global_index;
                        $panel_id  = $item_id . '-panel';
                        $btn_id    = $item_id . '-btn';
                        $open_class = $is_open ? ' nh-faq__item--open' : '';
                        ?>
                        <div class="nh-faq__item<?php echo $open_class; ?>" data-faq-item>
                            <button
                                class="nh-faq__question"
                                id="<?php echo esc_attr($btn_id); ?>"
                                data-faq-toggle
                                aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
                                aria-controls="<?php echo esc_attr($panel_id); ?>"
                            >
                                <span class="nh-faq__question-text"><?php echo esc_html($item['question']); ?></span>
                                <span class="nh-faq__icon" aria-hidden="true"></span>
                            </button>
                            <div
                                class="nh-faq__answer"
                                id="<?php echo esc_attr($panel_id); ?>"
                                role="region"
                                aria-labelledby="<?php echo esc_attr($btn_id); ?>"
                            >
                                <p class="nh-faq__answer-text"><?php echo wp_kses_post($item['answer']); ?></p>
                            </div>
                        </div>
                    <?php
                    $global_index++;
                    endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
