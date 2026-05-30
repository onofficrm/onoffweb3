<?php
/**
 * 상담 문의 폼 → inquiry 게시판 저장
 * URL: /proc/inquiry-submit.php (POST, JSON 응답)
 */
define('ONOFF_INQUIRY_SUBMIT', true);

include_once dirname(__FILE__) . '/../_common.php';

if (!defined('_GNUBOARD_')) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('success' => false, 'message' => '접근이 올바르지 않습니다.'));
    exit;
}

if (is_file(G5_PATH . '/_site.config.php')) {
    include_once G5_PATH . '/_site.config.php';
}

/**
 * JSON 응답 후 종료
 *
 * @param bool   $success
 * @param string $message
 * @param string $redirect_url 성공 시 이동 URL (선택)
 */
function onoff_inquiry_json_response($success, $message, $redirect_url = '')
{
    $payload = array(
        'success' => (bool) $success,
        'message' => (string) $message,
    );
    if ($redirect_url !== '') {
        $payload['redirect_url'] = (string) $redirect_url;
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 동일 IP 60초 내 재제출 제한 (session + data/cache)
 *
 * @param bool $record true일 때만 마지막 제출 시각 기록 (저장 성공 후 호출)
 */
function onoff_inquiry_check_ip_rate_limit($record = false)
{
    $ip = isset($_SERVER['REMOTE_ADDR']) ? trim($_SERVER['REMOTE_ADDR']) : '';
    if ($ip === '') {
        return;
    }

    $now = defined('G5_SERVER_TIME') ? (int) G5_SERVER_TIME : time();
    $session_key = 'onoff_inquiry_ip_' . md5($ip);
    $last = (int) get_session($session_key);

    if ($last > 0 && ($now - $last) < 60) {
        onoff_inquiry_json_response(false, '잠시 후 다시 시도해 주세요.');
    }

    if (defined('G5_DATA_PATH')) {
        $cache_dir = G5_DATA_PATH . '/cache';
        if (is_dir($cache_dir) || @mkdir($cache_dir, G5_DIR_PERMISSION, true)) {
            $cache_file = $cache_dir . '/inquiry_ip_' . md5($ip) . '.txt';
            if (is_file($cache_file) && is_readable($cache_file)) {
                $cached = (int) @file_get_contents($cache_file);
                if ($cached > 0 && ($now - $cached) < 60) {
                    onoff_inquiry_json_response(false, '잠시 후 다시 시도해 주세요.');
                }
            }
            if ($record && is_writable($cache_dir)) {
                @file_put_contents($cache_file, (string) $now, LOCK_EX);
            }
        }
    }

    if ($record) {
        set_session($session_key, $now);
    }
}

/**
 * 금지어 포함 여부
 *
 * @param string $text
 * @return bool
 */
function onoff_inquiry_has_banned_word($text)
{
    /* 운영자가 필요 시 단어 추가 — 과도한 차단 주의 */
    $banned = array(
        // 'casino',
        // 'viagra',
    );

    if (empty($banned)) {
        return false;
    }

    $lower = function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);

    foreach ($banned as $word) {
        $word = trim((string) $word);
        if ($word === '') {
            continue;
        }
        $needle = function_exists('mb_strtolower') ? mb_strtolower($word, 'UTF-8') : strtolower($word);
        if (function_exists('mb_strpos')) {
            if (mb_strpos($lower, $needle, 0, 'UTF-8') !== false) {
                return true;
            }
        } elseif (strpos($lower, $needle) !== false) {
            return true;
        }
    }

    return false;
}

/**
 * 연락처 기본 형식 검증
 *
 * @param string $phone
 * @return bool
 */
function onoff_inquiry_is_valid_phone($phone)
{
    $digits = preg_replace('/[^0-9]/', '', $phone);
    $len = strlen($digits);

    if ($len < 9 || $len > 15) {
        return false;
    }

    return (bool) preg_match('/^[0-9+\-\s().]+$/u', $phone);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    onoff_inquiry_json_response(false, '잘못된 요청입니다.');
}

/* 스팸 방지: honeypot */
if (!empty($_POST['website_url'])) {
    onoff_inquiry_json_response(false, '접수할 수 없습니다.');
}

/* User-Agent 없음 */
$http_ua = isset($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : '';
if ($http_ua === '') {
    onoff_inquiry_json_response(false, '접수할 수 없습니다.');
}

/* 동일 IP 60초 제한 (검증 실패 시에는 기록하지 않음) */
onoff_inquiry_check_ip_rate_limit(false);

/* CSRF 토큰 */
$token = isset($_POST['onoff_inquiry_token']) ? trim($_POST['onoff_inquiry_token']) : '';
$session_token = get_session('onoff_inquiry_token');
if ($token === '' || $session_token === '' || $token !== $session_token) {
    onoff_inquiry_json_response(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
}

/* 연속 제출 방지 */
$delay = isset($config['cf_delay_sec']) ? (int) $config['cf_delay_sec'] : 30;
if ($delay < 1) {
    $delay = 30;
}
$last_key = 'onoff_inquiry_last_submit';
if (get_session($last_key) && (int) get_session($last_key) >= (G5_SERVER_TIME - $delay)) {
    onoff_inquiry_json_response(false, '잠시 후 다시 시도해 주세요.');
}

/* 입력값 */
$name = isset($_POST['name']) ? trim(strip_tags($_POST['name'])) : '';
$phone = isset($_POST['phone']) ? trim(strip_tags($_POST['phone'])) : '';
$email = isset($_POST['email']) ? trim(strip_tags($_POST['email'])) : '';
$message = isset($_POST['message']) ? trim(strip_tags($_POST['message'])) : '';
$privacy = !empty($_POST['privacy_agree']) ? '동의' : '미동의';
$referer_page = isset($_POST['referer_page']) ? trim(strip_tags($_POST['referer_page'])) : '';

if ($name === '') {
    onoff_inquiry_json_response(false, '이름을 입력해 주세요.');
}
if (function_exists('mb_strlen') ? mb_strlen($name, 'UTF-8') > 50 : strlen($name) > 50) {
    onoff_inquiry_json_response(false, '이름이 너무 깁니다. 50자 이내로 입력해 주세요.');
}
if ($phone === '') {
    onoff_inquiry_json_response(false, '연락처를 입력해 주세요.');
}
if (function_exists('mb_strlen') ? mb_strlen($phone, 'UTF-8') > 30 : strlen($phone) > 30) {
    onoff_inquiry_json_response(false, '연락처가 너무 깁니다. 30자 이내로 입력해 주세요.');
}
if (!onoff_inquiry_is_valid_phone($phone)) {
    onoff_inquiry_json_response(false, '연락처 형식을 확인해 주세요.');
}
if ($message === '') {
    onoff_inquiry_json_response(false, '문의내용을 입력해 주세요.');
}
$msg_len = function_exists('mb_strlen') ? mb_strlen($message, 'UTF-8') : strlen($message);
if ($msg_len < 10) {
    onoff_inquiry_json_response(false, '문의내용을 10자 이상 입력해 주세요.');
}
if ($msg_len > 3000) {
    onoff_inquiry_json_response(false, '문의내용이 너무 깁니다. 3000자 이내로 입력해 주세요.');
}
if (empty($_POST['privacy_agree'])) {
    onoff_inquiry_json_response(false, '개인정보 수집·이용에 동의해 주세요.');
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    onoff_inquiry_json_response(false, '이메일 형식이 올바르지 않습니다.');
}
if ($email !== '' && (function_exists('mb_strlen') ? mb_strlen($email, 'UTF-8') > 100 : strlen($email) > 100)) {
    onoff_inquiry_json_response(false, '이메일이 너무 깁니다. 100자 이내로 입력해 주세요.');
}
if (onoff_inquiry_has_banned_word($name . ' ' . $message)) {
    onoff_inquiry_json_response(false, '접수할 수 없는 내용이 포함되어 있습니다.');
}

/* 게시판 확인 */
$bo_table = function_exists('g5site_cfg') ? g5site_cfg('inquiry_bo_table', 'inquiry') : 'inquiry';
$bo_table = preg_replace('/[^a-z0-9_]/i', '', $bo_table);
if ($bo_table === '') {
    $bo_table = 'inquiry';
}

$board = sql_fetch(" select * from {$g5['board_table']} where bo_table = '" . sql_real_escape_string($bo_table) . "' ");
if (!$board['bo_table']) {
    onoff_inquiry_json_response(false, '문의 게시판이 준비되지 않았습니다. 관리자에게 문의해 주세요.');
}

$write_table = $g5['write_prefix'] . $bo_table;

/* 제목·본문 */
$wr_subject_raw = '[상담] ' . $name;
if (function_exists('cut_str')) {
    $wr_subject_raw = cut_str($wr_subject_raw, 255);
} else {
    $wr_subject_raw = substr($wr_subject_raw, 0, 255);
}
$wr_content = "■ 상담 문의 (웹 폼 접수)\n\n";
$wr_content .= "이름: {$name}\n";
$wr_content .= "연락처: {$phone}\n";
if ($email !== '') {
    $wr_content .= "이메일: {$email}\n";
}
$wr_content .= "개인정보 동의: {$privacy}\n";
if ($referer_page !== '') {
    $wr_content .= "접수 페이지: {$referer_page}\n";
}
$wr_content .= "\n--- 문의내용 ---\n\n";
$wr_content .= $message;

$wr_subject = sql_real_escape_string($wr_subject_raw);
$wr_content = sql_real_escape_string($wr_content);
$wr_name = sql_real_escape_string($name);
$wr_email_sql = $email !== '' ? sql_real_escape_string($email) : '';
$wr_1 = sql_real_escape_string($phone);
$wr_2 = $wr_email_sql;
$wr_3 = sql_real_escape_string($referer_page);
$wr_4 = sql_real_escape_string($privacy);
$wr_ip = sql_real_escape_string(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
$wr_5 = $wr_ip;
$wr_6 = sql_real_escape_string('신규');

$wr_option = '';
if (!empty($board['bo_use_secret'])) {
    $wr_option = 'secret';
}

$wr_password = '';
if (!$member['mb_id'] && $wr_option === 'secret') {
    $wr_password = sql_real_escape_string(get_encrypt_string(substr(md5(uniqid('', true)), 0, 10)));
}

/* wr_seo_title은 wr_id 확정 후 icrm_ensure_wr_seo_title()로 설정 (중복 제목 -1, -2 처리) */
$wr_seo_title = '';

$mb_id_sql = isset($member['mb_id']) ? sql_real_escape_string($member['mb_id']) : '';

$sql = " insert into {$write_table}
            set wr_num = (SELECT IFNULL(MIN(wr_num) - 1, -1) FROM {$write_table} as sq),
                 wr_reply = '',
                 wr_comment = 0,
                 ca_name = '',
                 wr_option = '{$wr_option}',
                 wr_subject = '{$wr_subject}',
                 wr_content = '{$wr_content}',
                 wr_seo_title = '{$wr_seo_title}',
                 wr_link1 = '',
                 wr_link2 = '',
                 wr_link1_hit = 0,
                 wr_link2_hit = 0,
                 wr_hit = 0,
                 wr_good = 0,
                 wr_nogood = 0,
                 mb_id = '{$mb_id_sql}',
                 wr_password = '{$wr_password}',
                 wr_name = '{$wr_name}',
                 wr_email = '{$wr_email_sql}',
                 wr_homepage = '',
                 wr_datetime = '" . G5_TIME_YMDHIS . "',
                 wr_last = '" . G5_TIME_YMDHIS . "',
                 wr_ip = '{$wr_ip}',
                 wr_1 = '{$wr_1}',
                 wr_2 = '{$wr_2}',
                 wr_3 = '{$wr_3}',
                 wr_4 = '{$wr_4}',
                 wr_5 = '{$wr_5}',
                 wr_6 = '{$wr_6}',
                 wr_7 = '',
                 wr_8 = '',
                 wr_9 = '',
                 wr_10 = '' ";

$result = sql_query($sql, false);

if (!$result) {
    onoff_inquiry_json_response(false, '문의 접수 중 오류가 발생했습니다. 잠시 후 다시 시도해 주세요.');
}

$wr_id = sql_insert_id();
if (!$wr_id) {
    onoff_inquiry_json_response(false, '문의 접수 중 오류가 발생했습니다. 잠시 후 다시 시도해 주세요.');
}

sql_query(" update {$write_table} set wr_parent = '{$wr_id}' where wr_id = '{$wr_id}' ");
sql_query(" insert into {$g5['board_new_table']} ( bo_table, wr_id, wr_parent, bn_datetime, mb_id )
            values ( '{$bo_table}', '{$wr_id}', '{$wr_id}', '" . G5_TIME_YMDHIS . "', '{$mb_id_sql}' ) ");
sql_query(" update {$g5['board_table']} set bo_count_write = bo_count_write + 1 where bo_table = '{$bo_table}' ");

if (is_file(G5_LIB_PATH . '/icrm.lib.php')) {
    include_once G5_LIB_PATH . '/icrm.lib.php';
    if (function_exists('icrm_ensure_wr_seo_title')) {
        icrm_ensure_wr_seo_title($bo_table, (int) $wr_id);
    }
}

set_session($last_key, G5_SERVER_TIME);
onoff_inquiry_check_ip_rate_limit(true);

/* 저장 성공 — 알림 (실패해도 접수 완료) */
$site_name = function_exists('g5site_cfg') ? g5site_cfg('site_name', '') : '';
if ($site_name === '' && !empty($config['cf_title'])) {
    $site_name = $config['cf_title'];
}

$admin_url = G5_BBS_URL . '/board.php?bo_table=' . urlencode($bo_table);
if (function_exists('get_pretty_url')) {
    $pretty = get_pretty_url($bo_table);
    if ($pretty) {
        $admin_url = $pretty;
    }
}

$inquiry_data = array(
    'site_name'      => $site_name,
    'name'           => $name,
    'phone'          => $phone,
    'email'          => $email,
    'message'        => $message,
    'referer_page'   => $referer_page,
    'privacy_agree'  => $privacy,
    'ip'             => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
    'created_at'     => G5_TIME_YMDHIS,
    'admin_url'      => $admin_url,
    'bo_table'       => $bo_table,
    'wr_id'          => $wr_id,
);

$notifier_file = G5_PATH . '/components/inquiry-notifier.php';
if (is_file($notifier_file)) {
    include_once $notifier_file;
    if (function_exists('onoff_send_inquiry_notifications')) {
        try {
            onoff_send_inquiry_notifications($inquiry_data);
        } catch (Exception $e) {
            /* 알림 실패 무시 */
        }
    }
}

$thanks_path = function_exists('g5site_cfg') ? trim(g5site_cfg('inquiry_thanks_url', '/page/inquiry-thanks.php')) : '/page/inquiry-thanks.php';
if ($thanks_path === '') {
    $thanks_path = '/page/inquiry-thanks.php';
}
if (preg_match('#^https?://#i', $thanks_path)) {
    $redirect_url = $thanks_path;
} else {
    $redirect_url = rtrim(G5_URL, '/') . '/' . ltrim($thanks_path, '/');
}

onoff_inquiry_json_response(true, '문의가 접수되었습니다. 잠시 후 완료 페이지로 이동합니다.', $redirect_url);
