<?php
/**
 * FAQPage JSON-LD
 *
 * $faq_schema_items = array(
 *   array('question' => '질문', 'answer' => '답변'),
 * );
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once dirname(__FILE__) . '/_helpers.php';

if (empty($faq_schema_items) || !is_array($faq_schema_items)) {
    return;
}

$main_entities = array();

foreach ($faq_schema_items as $item) {
    if (!is_array($item)) {
        continue;
    }

    $question = isset($item['question']) ? g5b_schema_clean_text($item['question']) : '';
    $answer = isset($item['answer']) ? g5b_schema_clean_text($item['answer']) : '';

    if ($question === '' || $answer === '') {
        continue;
    }

    $main_entities[] = array(
        '@type'          => 'Question',
        'name'           => $question,
        'acceptedAnswer' => array(
            '@type' => 'Answer',
            'text'  => $answer,
        ),
    );
}

if (empty($main_entities)) {
    return;
}

$schema = array(
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => $main_entities,
);

g5b_schema_print_jsonld($schema);
