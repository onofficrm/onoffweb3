<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

/**
 * 비밀글 여부
 *
 * @param array $row
 * @return bool
 */
function g5b_faq_is_secret($row)
{
    return isset($row['wr_option']) && strstr($row['wr_option'], 'secret');
}

/**
 * 목록·글보기 FAQ 답변 HTML
 *
 * @param array $row
 * @return string
 */
function g5b_faq_answer_html($row)
{
    if (!empty($row['list_content'])) {
        return $row['list_content'];
    }
    if (!empty($row['wr_content'])) {
        return $row['wr_content'];
    }
    if (!empty($row['content'])) {
        return $row['content'];
    }
    return '';
}

/**
 * Schema·요약용 평문 (HTML 제거)
 *
 * @param array $row
 * @param int   $max_len 0이면 제한 없음
 * @return string
 */
function g5b_faq_answer_plain($row, $max_len = 500)
{
    $text = trim(strip_tags(g5b_faq_answer_html($row)));
    $text = preg_replace('/\s+/u', ' ', $text);
    if ($text === '') {
        return '';
    }
    if ($max_len > 0 && function_exists('cut_str')) {
        return cut_str($text, (int) $max_len, '…');
    }
    if ($max_len > 0 && strlen($text) > $max_len) {
        return substr($text, 0, $max_len).'…';
    }
    return $text;
}

/**
 * FAQ 질문 텍스트
 *
 * @param array $row
 * @return string
 */
function g5b_faq_question_text($row)
{
    if (!empty($row['wr_subject'])) {
        return get_text(strip_tags($row['wr_subject']));
    }
    if (!empty($row['subject'])) {
        return get_text(strip_tags($row['subject']));
    }
    return '';
}

/**
 * 화면에 표시된 FAQ 행 → FAQPage Schema 항목
 *
 * @param array $rows
 * @return array
 */
function g5b_faq_build_schema_items($rows)
{
    $items = array();
    if (!is_array($rows)) {
        return $items;
    }

    foreach ($rows as $row) {
        if (!is_array($row) || g5b_faq_is_secret($row)) {
            continue;
        }
        $question = g5b_faq_question_text($row);
        $answer = g5b_faq_answer_plain($row, 500);
        if ($question === '' || $answer === '') {
            continue;
        }
        $items[] = array(
            'question' => $question,
            'answer'   => $answer,
        );
    }

    return $items;
}

/**
 * FAQPage JSON-LD 출력 (파일 없으면 무시)
 *
 * @param array $items
 */
function g5b_faq_print_schema($items)
{
    if (empty($items) || !is_array($items)) {
        return;
    }

    $faq_schema_items = $items;
    $schema_file = defined('G5_PATH') ? G5_PATH.'/components/schema/faq.php' : '';
    if ($schema_file !== '' && is_file($schema_file)) {
        include_once $schema_file;
    }
}
