<?php
/**
 * Title: Nice Hair / Home FAQ
 * Slug: nice-hair/home-faq
 * Categories: nice-hair-home
 * Inserter: yes
 */

$faq_items = [
    ['question' => 'What method of extensions do you use in the salon?', 'answer' => 'We use safe and invisible hot keratin (Italian) technique.', 'is_open' => '1'],
    ['question' => 'Do you offer consultations before booking a salon appointment?', 'answer' => 'Yes, we provide free consultations to help you choose the right hair, method, and volume before your appointment.', 'is_open' => '0'],
    ['question' => 'How long does the hair last?', 'answer' => 'With proper care, our hair can last from 6 to 12 months or longer, depending on the type of hair and usage. We provide detailed care recommendations to help maintain the quality and appearance of the hair.', 'is_open' => '0'],
    ['question' => 'Where is your hair sourced from?', 'answer' => 'Our hair is ethically sourced from Russia and CIS countries. We carefully select raw materials to ensure natural texture, durability, and premium quality.', 'is_open' => '0'],
    ['question' => 'How long does shipping take?', 'answer' => "Shipping time depends on your location. International orders are shipped via DHL, and delivery timelines are shared individually after order confirmation, but usually timeframes are around 2\u{2013}10 business days.", 'is_open' => '0'],
    ['question' => 'What guarantees do you provide?', 'answer' => 'We are confident in our skills and offer a guarantee for our services so that you can be calm about the result. If the extended capsules require adjustment within 14 days after the procedure, we will fix it free of charge. Regarding purchasing in our online shop, you can return your hair within a week if the color/length/texture does not suit you (except for hairpieces), provided that the tags and elastic bands on the bulk are not cut off.', 'is_open' => '0'],
    ['question' => 'Is your hair 100% virgin?', 'answer' => 'Yes, we work only with 100% virgin human hair.', 'is_open' => '0'],
    ['question' => 'Are your hair products handmade?', 'answer' => 'Yes, all NICEHAIR hair products are handcrafted and produced under strict quality standards.', 'is_open' => '0'],
    ['question' => 'What if I want to remove the extensions?', 'answer' => 'Extensions can be safely removed at our salon at any time. The removal process is gentle and does not damage your natural hair when performed by our specialists.', 'is_open' => '0'],
    ['question' => 'What does cold hair coloring technology mean?', 'answer' => 'Cold hair dying is a gentle industrial coloring method performed without aggressive high-temperature processing. This technology helps preserve the natural structure of the hair cuticle, preventing dryness and damage, while maintaining softness, shine, and long-lasting color quality.', 'is_open' => '0'],
    ['question' => 'Do you work with international clients and offer worldwide shipping?', 'answer' => 'Yes, we work with clients worldwide. We offer international shipping via DHL for hair orders and welcome clients from different countries at our salon by appointment.', 'is_open' => '0'],
    ['question' => 'How can I pay?', 'answer' => 'You can pay for your hair via bank transfer or using the Stripe or Zina payment systems. We accept card and cash payments for service at the salon, and also offer installment plans with Tabby and Tamara.', 'is_open' => '0'],
];

$data = [
    'nh_faq_eyebrow'   => '[ FAQ ]',
    '_nh_faq_eyebrow'  => 'field_nh_faq_eyebrow',
    'nh_faq_title'     => "No stress — we’ve got answers",
    '_nh_faq_title'    => 'field_nh_faq_title',
    'nh_faq_subtitle'  => 'Here are the most common questions clients ask before getting extensions.',
    '_nh_faq_subtitle' => 'field_nh_faq_subtitle',
    'nh_faq_items'     => count($faq_items),
    '_nh_faq_items'    => 'field_nh_faq_items',
];

foreach ($faq_items as $i => $item) {
    $data["nh_faq_items_{$i}_question"]  = $item['question'];
    $data["_nh_faq_items_{$i}_question"] = 'field_nh_faq_question';
    $data["nh_faq_items_{$i}_answer"]    = $item['answer'];
    $data["_nh_faq_items_{$i}_answer"]   = 'field_nh_faq_answer';
    $data["nh_faq_items_{$i}_is_open"]   = $item['is_open'];
    $data["_nh_faq_items_{$i}_is_open"]  = 'field_nh_faq_is_open';
}

$block_json = wp_json_encode([
    'name' => 'acf/nh-faq',
    'data' => $data,
    'mode' => 'preview',
]);
?>
<!-- wp:acf/nh-faq <?php echo $block_json; ?> /-->
