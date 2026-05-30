<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

/**
 * /img/main/ 이미지 URL (파일 없으면 빈 문자열)
 * @param string $filename
 * @return string
 */
function g5_sample_main_img_url($filename)
{
    $file = basename($filename);
    $path = G5_PATH.'/img/main/'.$file;
    if (is_file($path)) {
        return G5_URL.'/img/main/'.$file;
    }
    return '';
}

/**
 * 이미지 또는 플레이스홀더 출력
 * @param string $filename  img/main/ 파일명
 * @param string $alt
 * @param string $class     img 태그 class
 * @param string $ratio     img-placeholder modifier (hero, card, wide)
 */
function g5_sample_main_media($filename, $alt = '', $class = '', $ratio = 'card')
{
    $url = g5_sample_main_img_url($filename);
    $alt = htmlspecialchars($alt, ENT_QUOTES, 'UTF-8');
    $class_attr = $class ? ' class="'.htmlspecialchars($class, ENT_QUOTES, 'UTF-8').'"' : '';

    if ($url) {
        echo '<img src="'.htmlspecialchars($url, ENT_QUOTES, 'UTF-8').'" alt="'.$alt.'"'.$class_attr.' loading="lazy">';
        return;
    }

    $ratio_class = ' img-placeholder--'.preg_replace('/[^a-z0-9-]/', '', $ratio);
    echo '<div class="img-placeholder'.$ratio_class.'" role="img" aria-label="'.$alt.'">';
    echo '<span class="img-placeholder__label">'.($alt ?: '이미지 영역').'</span>';
    echo '</div>';
}

/**
 * 게시판 사용 가능 여부 (설정 + 글 테이블)
 * @param string $bo_table
 * @return bool
 */
function g5_sample_board_available($bo_table)
{
    global $g5;

    $bo_table = preg_replace('/[^a-z0-9_]/i', '', $bo_table);
    if (!$bo_table) {
        return false;
    }

    $board = get_board_db($bo_table, true);
    if (empty($board['bo_table'])) {
        return false;
    }

    $write_table = $g5['write_prefix'].$bo_table;
    $check = sql_fetch(" SHOW TABLES LIKE '{$write_table}' ", false);
    if (!is_array($check) || !$check) {
        return false;
    }

    return true;
}

/**
 * latest 스킨 경로 resolve (card → basic fallback)
 * @param string $prefer
 * @return string
 */
function g5_sample_resolve_latest_skin($prefer = 'card')
{
    $candidates = array($prefer, 'basic');

    foreach ($candidates as $skin) {
        $skin = preg_replace('/[^a-z0-9_]/i', '', $skin);
        $skin_path = G5_SKIN_PATH.'/latest/'.$skin;
        if (is_dir($skin_path) && is_file($skin_path.'/latest.skin.php')) {
            return $skin;
        }
    }

    return '';
}

/**
 * 최신글 출력 (게시판·스킨 없을 때 fallback HTML)
 *
 * @param string $bo_table
 * @param string $label    카드 표시 제목
 * @param int    $rows
 * @param int    $subject_len
 * @param string $skin_prefer
 * @return string
 */
function g5_sample_latest_render($bo_table, $label, $rows = 5, $subject_len = 40, $skin_prefer = 'card')
{
    if (!g5_sample_board_available($bo_table)) {
        return g5_sample_latest_fallback_html($label, $bo_table, 'board');
    }

    $skin = g5_sample_resolve_latest_skin($skin_prefer);
    if ($skin === '') {
        return g5_sample_latest_fallback_html($label, $bo_table, 'skin');
    }

    $html = latest($skin, $bo_table, $rows, $subject_len);
    if (!is_string($html) || trim($html) === '') {
        return g5_sample_latest_fallback_html($label, $bo_table, 'empty');
    }

    return $html;
}

/**
 * @param string $label
 * @param string $bo_table
 * @param string $reason board|skin|empty
 * @return string
 */
function g5_sample_latest_fallback_html($label, $bo_table, $reason = 'board')
{
    $label = get_text($label);
    $bo_table = preg_replace('/[^a-z0-9_]/i', '', $bo_table);

    if ($reason === 'board') {
        $message = '게시판「'.htmlspecialchars($bo_table, ENT_QUOTES, 'UTF-8').'」이 아직 준비되지 않았습니다.';
    } elseif ($reason === 'skin') {
        $message = '최신글 스킨을 불러올 수 없습니다.';
    } else {
        $message = '등록된 게시물이 없습니다.';
    }

    return '<div class="latest-card__body latest-card__body--fallback">'
        .'<h3 class="latest-card-title">'.htmlspecialchars($label, ENT_QUOTES, 'UTF-8').'</h3>'
        .'<p class="latest-card-empty">'.$message.'</p>'
        .'</div>';
}

/**
 * FAQ 항목을 Schema용 배열로 정규화 (화면·JSON-LD 동일 데이터)
 * - question/answer 또는 q/a 키 지원
 * - 질문·답변이 비어 있으면 제외
 *
 * @param array $items
 * @return array
 */
function g5_sample_faq_schema_items($items)
{
    if (!is_array($items) || empty($items)) {
        return array();
    }

    $schema_items = array();

    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $question = '';
        $answer = '';

        if (isset($item['question'])) {
            $question = trim(strip_tags((string) $item['question']));
        } elseif (isset($item['q'])) {
            $question = trim(strip_tags((string) $item['q']));
        }

        if (isset($item['answer'])) {
            $answer = trim(strip_tags((string) $item['answer']));
        } elseif (isset($item['a'])) {
            $answer = trim(strip_tags((string) $item['a']));
        }

        if ($question === '' || $answer === '') {
            continue;
        }

        $schema_items[] = array(
            'question' => $question,
            'answer'   => $answer,
        );
    }

    return $schema_items;
}

/**
 * FAQPage JSON-LD 출력 (components/schema/faq.php)
 * - 화면에 표시한 FAQ와 동일한 $items만 전달
 *
 * @param array $items FAQ 원본 배열 (question/answer 또는 q/a)
 * @return bool 출력했으면 true
 */
function g5_sample_faq_output_schema($items)
{
    $faq_schema_items = g5_sample_faq_schema_items($items);

    if (empty($faq_schema_items)) {
        return false;
    }

    if (!defined('G5_PATH')) {
        return false;
    }

    $schema_file = G5_PATH . '/components/schema/faq.php';
    if (!is_file($schema_file)) {
        return false;
    }

    include $schema_file;

    return true;
}
