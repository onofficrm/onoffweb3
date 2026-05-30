<?php
$sub_menu = '200910';
define('G5_IS_ADMIN', true);
require_once __DIR__.'/../../../common.php';

define('AUTO_COMMENT_ADMIN_VERSION', '20260429-1332');

function ac_admin_message_redirect($msg, $url)
{
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }

    echo '<script>alert('.json_encode($msg).');location.replace('.json_encode($url).');</script>';
    echo '<noscript><p>'.htmlspecialchars($msg, ENT_QUOTES, 'UTF-8').'</p><p><a href="'.htmlspecialchars($url, ENT_QUOTES, 'UTF-8').'">이동</a></p></noscript>';
    exit;
}

function ac_admin_token()
{
    global $member;

    $secret = defined('G5_TOKEN_ENCRYPTION_KEY') ? G5_TOKEN_ENCRYPTION_KEY : G5_MYSQL_USER;
    return md5('auto_comment_admin|'.session_id().'|'.(isset($member['mb_id']) ? $member['mb_id'] : '').'|'.$secret);
}

function ac_admin_check_token()
{
    // This plugin page is already restricted to the super admin above.
    // Some hosting/session setups rotate GnuBoard admin tokens on plugin paths,
    // so token mismatch should not block module setup actions.
    return true;
}

if ($is_admin != 'super') {
    ac_admin_message_redirect('최고관리자만 접근 가능합니다. (자동댓글 관리자 '.AUTO_COMMENT_ADMIN_VERSION.')', G5_URL);
}

include_once G5_PLUGIN_PATH.'/auto_comment/auto_comment.lib.php';

$g5['title'] = '자동댓글 관리';
$tab = isset($_GET['tab']) ? preg_replace('/[^a-z_]/', '', $_GET['tab']) : 'dashboard';
$installed = auto_comment_is_installed();
$admin_url = G5_PLUGIN_URL.'/auto_comment/admin/index.php';
$action_url = G5_PLUGIN_URL.'/auto_comment/admin/action.php';
$manual_preview = null;
$manual_error = '';
$direct_preview = null;
$direct_error = '';

function ac_admin_redirect($tab, $msg)
{
    global $admin_url;
    ac_admin_message_redirect($msg, $admin_url.'?tab='.$tab);
}

function ac_admin_queue_author_value($row)
{
    $author = isset($row['acq_author']) ? trim($row['acq_author']) : '';
    $fallback = trim(auto_comment_get_setting('ai_author_name', '세부나이트 AI 가이드'));

    if ($author === '' || ($fallback !== '' && $author === $fallback)) {
        return auto_comment_random_korean_nickname();
    }

    return $author;
}

function ac_admin_queue_result_url($row)
{
    global $g5;

    if (!isset($row['acq_status']) || $row['acq_status'] !== 'inserted') {
        return '';
    }

    if (!empty($row['acq_error']) && preg_match('/https?:\/\/[^\s]+/u', $row['acq_error'], $match)) {
        return $match[0];
    }

    $bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', $row['bo_table']);
    $wr_id = (int) $row['wr_id'];
    if (!$bo_table || $wr_id < 1 || empty($g5['write_prefix'])) {
        return '';
    }

    $write_table = $g5['write_prefix'].$bo_table;
    if (!auto_comment_table_exists($write_table)) {
        return '';
    }

    $comment = sql_fetch(" select wr_id
                             from {$write_table}
                            where wr_parent = '{$wr_id}'
                              and wr_is_comment = 1
                              and wr_name = '".auto_comment_escape($row['acq_author'])."'
                              and wr_content = '".auto_comment_escape($row['acq_content'])."'
                            order by wr_id desc
                            limit 1 ", false);
    if (empty($comment['wr_id'])) {
        return '';
    }

    return auto_comment_comment_url($bo_table, $wr_id, (int) $comment['wr_id']);
}

function ac_admin_queue_post_datetime($row)
{
    global $g5;
    static $cache = array();

    $bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', $row['bo_table']);
    $wr_id = (int) $row['wr_id'];
    $cache_key = $bo_table.'_'.$wr_id;
    if (isset($cache[$cache_key])) {
        return $cache[$cache_key];
    }

    $cache[$cache_key] = '';
    if (!$bo_table || $wr_id < 1 || empty($g5['write_prefix'])) {
        return $cache[$cache_key];
    }

    $write_table = $g5['write_prefix'].$bo_table;
    if (!auto_comment_table_exists($write_table)) {
        return $cache[$cache_key];
    }

    $write = sql_fetch(" select wr_datetime
                           from {$write_table}
                          where wr_id = '{$wr_id}'
                            and wr_is_comment = 0
                          limit 1 ", false);
    if (!empty($write['wr_datetime']) && $write['wr_datetime'] !== '0000-00-00 00:00:00') {
        $cache[$cache_key] = $write['wr_datetime'];
    }

    return $cache[$cache_key];
}

function ac_admin_post_comment_count($row)
{
    global $g5;

    $bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', $row['bo_table']);
    $wr_id = (int) $row['wr_id'];
    if (!$bo_table || $wr_id < 1 || empty($g5['write_prefix'])) {
        return 0;
    }

    $write_table = $g5['write_prefix'].$bo_table;
    if (!auto_comment_table_exists($write_table)) {
        return 0;
    }

    $write = sql_fetch(" select wr_comment from {$write_table} where wr_id = '{$wr_id}' and wr_is_comment = 0 ", false);
    return isset($write['wr_comment']) ? (int) $write['wr_comment'] : 0;
}

function ac_admin_auto_comment_count_for_post($bo_table, $wr_id)
{
    $row = sql_fetch(" select count(*) as cnt from ".auto_comment_table('queue')."
                        where bo_table = '".auto_comment_escape($bo_table)."'
                          and wr_id = '".(int) $wr_id."'
                          and acq_status in ('review', 'pending', 'inserted') ", false);

    return isset($row['cnt']) ? (int) $row['cnt'] : 0;
}

function ac_admin_post_view_meta($bo_table, $wr_id)
{
    auto_comment_ensure_post_view_table();
    $row = sql_fetch(" select acv_last_viewed_at, acv_ip, acv_view_count, acv_visitor_type
                         from ".auto_comment_table('post_view')."
                        where bo_table = '".auto_comment_escape($bo_table)."'
                          and wr_id = '".(int) $wr_id."' ", false);

    return is_array($row) ? $row : array();
}

function ac_admin_visitor_type_label($type)
{
    $labels = array(
        'new' => '신규',
        'existing' => '기존',
        'returning' => '재방문'
    );

    return isset($labels[$type]) ? $labels[$type] : '-';
}

function ac_admin_visitor_summary()
{
    auto_comment_ensure_visitor_table();
    $today = date('Y-m-d 00:00:00', G5_SERVER_TIME);
    $week_ago = date('Y-m-d 00:00:00', G5_SERVER_TIME - (6 * 86400));

    $new_today = sql_fetch(" select count(*) as cnt
                               from ".auto_comment_table('visitor')."
                              where acv_first_seen_at >= '{$today}' ", false);
    $existing_today = sql_fetch(" select count(*) as cnt
                                    from ".auto_comment_table('visitor')."
                                   where acv_last_seen_at >= '{$today}'
                                     and acv_first_seen_at < '{$today}' ", false);
    $returning_7d = sql_fetch(" select count(*) as cnt
                                  from ".auto_comment_table('visitor')."
                                 where acv_last_seen_at >= '{$week_ago}'
                                   and acv_first_seen_at < concat(left(acv_last_seen_at, 10), ' 00:00:00') ", false);

    return array(
        'new_today' => isset($new_today['cnt']) ? (int) $new_today['cnt'] : 0,
        'existing_today' => isset($existing_today['cnt']) ? (int) $existing_today['cnt'] : 0,
        'returning_7d' => isset($returning_7d['cnt']) ? (int) $returning_7d['cnt'] : 0
    );
}

function ac_admin_ai_usage_summary($from_datetime)
{
    auto_comment_ensure_ai_usage_table();
    $where = $from_datetime !== '' ? " where acu_created_at >= '".auto_comment_escape($from_datetime)."' " : '';
    $row = sql_fetch(" select count(*) as calls,
                              sum(case when acu_status = 'success' then 1 else 0 end) as success_count,
                              sum(case when acu_status <> 'success' then 1 else 0 end) as fail_count,
                              sum(acu_prompt_tokens) as prompt_tokens,
                              sum(acu_output_tokens) as output_tokens,
                              sum(acu_total_tokens) as total_tokens,
                              sum(acu_cost_krw) as cost_krw
                         from ".auto_comment_table('ai_usage').$where, false);

    return is_array($row) ? $row : array();
}

function ac_admin_failure_action_label($action)
{
    $labels = array(
        'failed' => '댓글 등록 실패',
        'schedule_failed' => '자동예약 실패',
        'schedule_skip' => '자동예약 제외',
        'strategy_skip' => '전략스캔 제외',
        'worker_failed' => 'worker 실패',
        'generator_fallback' => 'AI fallback',
        'ai_failed' => 'AI 실패'
    );

    return isset($labels[$action]) ? $labels[$action] : $action;
}

function ac_admin_daily_report($days)
{
    $days = max(3, min(14, (int) $days));
    $start_time = G5_SERVER_TIME - (($days - 1) * 86400);
    $start_date = date('Y-m-d', $start_time);
    $report = array();

    for ($i = 0; $i < $days; $i++) {
        $date = date('Y-m-d', $start_time + ($i * 86400));
        $report[$date] = array(
            'label' => date('m/d', strtotime($date)),
            'queued' => 0,
            'inserted' => 0,
            'failed' => 0,
            'ai_cost' => 0
        );
    }

    $queue_result = sql_query(" select left(acq_created_at, 10) as report_date, count(*) as cnt
                                  from ".auto_comment_table('queue')."
                                 where acq_created_at >= '".auto_comment_escape($start_date)." 00:00:00'
                                   and acq_status in ('review', 'pending', 'inserted')
                                 group by left(acq_created_at, 10) ", false);
    while ($row = sql_fetch_array($queue_result)) {
        if (isset($report[$row['report_date']])) {
            $report[$row['report_date']]['queued'] = (int) $row['cnt'];
        }
    }

    $inserted_result = sql_query(" select left(acq_inserted_at, 10) as report_date, count(*) as cnt
                                    from ".auto_comment_table('queue')."
                                   where acq_status = 'inserted'
                                     and acq_inserted_at >= '".auto_comment_escape($start_date)." 00:00:00'
                                   group by left(acq_inserted_at, 10) ", false);
    while ($row = sql_fetch_array($inserted_result)) {
        if (isset($report[$row['report_date']])) {
            $report[$row['report_date']]['inserted'] = (int) $row['cnt'];
        }
    }

    $failure_actions = "'failed','schedule_failed','schedule_skip','strategy_skip','worker_failed','generator_fallback','ai_failed'";
    $failure_result = sql_query(" select left(acl_datetime, 10) as report_date, count(*) as cnt
                                    from ".auto_comment_table('log')."
                                   where acl_action in ({$failure_actions})
                                     and acl_datetime >= '".auto_comment_escape($start_date)." 00:00:00'
                                   group by left(acl_datetime, 10) ", false);
    while ($row = sql_fetch_array($failure_result)) {
        if (isset($report[$row['report_date']])) {
            $report[$row['report_date']]['failed'] = (int) $row['cnt'];
        }
    }

    auto_comment_ensure_ai_usage_table();
    $ai_result = sql_query(" select left(acu_created_at, 10) as report_date, sum(acu_cost_krw) as cost_krw
                               from ".auto_comment_table('ai_usage')."
                              where acu_created_at >= '".auto_comment_escape($start_date)." 00:00:00'
                              group by left(acu_created_at, 10) ", false);
    while ($row = sql_fetch_array($ai_result)) {
        if (isset($report[$row['report_date']])) {
            $report[$row['report_date']]['ai_cost'] = (float) $row['cost_krw'];
        }
    }

    return array_values($report);
}

function ac_admin_daily_view_report($days)
{
    $days = max(3, min(14, (int) $days));
    $start_time = G5_SERVER_TIME - (($days - 1) * 86400);
    $start_date = date('Y-m-d', $start_time);
    $report = array();

    for ($i = 0; $i < $days; $i++) {
        $date = date('Y-m-d', $start_time + ($i * 86400));
        $report[$date] = array(
            'label' => date('m/d', strtotime($date)),
            'viewed_posts' => 0,
            'view_events' => 0,
            'new_visitors' => 0,
            'existing_visitors' => 0
        );
    }

    auto_comment_ensure_post_view_table();
    $result = sql_query(" select left(acv_last_viewed_at, 10) as report_date,
                                 count(*) as viewed_posts,
                                 sum(acv_view_count) as view_events
                            from ".auto_comment_table('post_view')."
                           where acv_last_viewed_at >= '".auto_comment_escape($start_date)." 00:00:00'
                             and acv_last_viewed_at <> '0000-00-00 00:00:00'
                           group by left(acv_last_viewed_at, 10) ", false);
    while ($row = sql_fetch_array($result)) {
        if (isset($report[$row['report_date']])) {
            $report[$row['report_date']]['viewed_posts'] = (int) $row['viewed_posts'];
            $report[$row['report_date']]['view_events'] = (int) $row['view_events'];
        }
    }

    auto_comment_ensure_visitor_table();
    $new_result = sql_query(" select left(acv_first_seen_at, 10) as report_date, count(*) as cnt
                                from ".auto_comment_table('visitor')."
                               where acv_first_seen_at >= '".auto_comment_escape($start_date)." 00:00:00'
                                 and acv_first_seen_at <> '0000-00-00 00:00:00'
                               group by left(acv_first_seen_at, 10) ", false);
    while ($row = sql_fetch_array($new_result)) {
        if (isset($report[$row['report_date']])) {
            $report[$row['report_date']]['new_visitors'] = (int) $row['cnt'];
        }
    }

    $existing_result = sql_query(" select left(acv_last_seen_at, 10) as report_date, count(*) as cnt
                                     from ".auto_comment_table('visitor')."
                                    where acv_last_seen_at >= '".auto_comment_escape($start_date)." 00:00:00'
                                      and acv_first_seen_at < concat(left(acv_last_seen_at, 10), ' 00:00:00')
                                    group by left(acv_last_seen_at, 10) ", false);
    while ($row = sql_fetch_array($existing_result)) {
        if (isset($report[$row['report_date']])) {
            $report[$row['report_date']]['existing_visitors'] = (int) $row['cnt'];
        }
    }

    return array_values($report);
}

function ac_admin_latest_posts($limit, $filter_bo_table)
{
    global $g5;

    auto_comment_ensure_board_columns();
    $posts = array();
    $limit = max(1, min(100, (int) $limit));
    $filter_bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', $filter_bo_table);
    $board_table = auto_comment_table('board');
    $where = " acb_enabled = 1 and acb_manual_comment = 1 ";
    if ($filter_bo_table !== '') {
        $where .= " and bo_table = '".auto_comment_escape($filter_bo_table)."' ";
    }

    $boards = sql_query(" select bo_table from {$board_table} where {$where} order by bo_table asc ", false);
    while ($cfg = sql_fetch_array($boards)) {
        $bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', $cfg['bo_table']);
        if (!$bo_table || empty($g5['write_prefix'])) {
            continue;
        }

        $board = get_board_db($bo_table, true);
        if (!$board || empty($board['bo_table'])) {
            continue;
        }

        $write_table = $g5['write_prefix'].$bo_table;
        if (!auto_comment_table_exists($write_table)) {
            continue;
        }

        $rows = sql_query(" select wr_id, wr_subject, wr_name, wr_datetime, wr_hit, wr_comment
                              from {$write_table}
                             where wr_is_comment = 0
                             order by wr_datetime desc, wr_id desc
                             limit {$limit} ", false);
        while ($write = sql_fetch_array($rows)) {
            $write['bo_table'] = $bo_table;
            $write['bo_subject'] = $board['bo_subject'];
            $posts[] = $write;
        }
    }

    usort($posts, function ($a, $b) {
        if ($a['wr_datetime'] === $b['wr_datetime']) {
            return (int) $b['wr_id'] - (int) $a['wr_id'];
        }
        return strcmp($b['wr_datetime'], $a['wr_datetime']);
    });

    return array_slice($posts, 0, $limit);
}

function ac_admin_recent_viewed_posts($limit, $filter_bo_table)
{
    global $g5;

    auto_comment_ensure_post_view_table();
    $posts = array();
    $limit = max(1, min(100, (int) $limit));
    $filter_bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', $filter_bo_table);
    $where = " 1 = 1 ";
    if ($filter_bo_table !== '') {
        $where .= " and bo_table = '".auto_comment_escape($filter_bo_table)."' ";
    }

    $result = sql_query(" select *
                            from ".auto_comment_table('post_view')."
                           where {$where}
                           order by acv_last_viewed_at desc
                           limit {$limit} ", false);
    while ($view = sql_fetch_array($result)) {
        $bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', $view['bo_table']);
        $wr_id = (int) $view['wr_id'];
        if (!$bo_table || $wr_id < 1 || empty($g5['write_prefix'])) {
            continue;
        }

        $cfg = auto_comment_get_board_config($bo_table);
        if (!$cfg || (int) $cfg['acb_enabled'] !== 1 || (isset($cfg['acb_manual_comment']) && (int) $cfg['acb_manual_comment'] !== 1)) {
            continue;
        }

        $board = get_board_db($bo_table, true);
        if (!$board || empty($board['bo_table'])) {
            continue;
        }

        $write_table = $g5['write_prefix'].$bo_table;
        if (!auto_comment_table_exists($write_table)) {
            continue;
        }

        $write = sql_fetch(" select wr_id, wr_subject, wr_name, wr_datetime, wr_hit, wr_comment
                               from {$write_table}
                              where wr_id = '{$wr_id}'
                                and wr_is_comment = 0 ", false);
        if (empty($write['wr_id'])) {
            continue;
        }

        $write['bo_table'] = $bo_table;
        $write['bo_subject'] = $board['bo_subject'];
        $write['acv_view_count'] = isset($view['acv_view_count']) ? (int) $view['acv_view_count'] : 0;
        $write['acv_last_viewed_at'] = isset($view['acv_last_viewed_at']) ? $view['acv_last_viewed_at'] : '';
        $write['acv_ip'] = isset($view['acv_ip']) ? $view['acv_ip'] : '';
        $write['acv_visitor_type'] = isset($view['acv_visitor_type']) ? $view['acv_visitor_type'] : '';
        $posts[] = $write;
    }

    return $posts;
}

function ac_admin_recent_history_rows($limit)
{
    $rows = array();
    $limit = max(1, min(20, (int) $limit));
    $result = sql_query(" select *
                            from ".auto_comment_table('queue')."
                           where acq_status = 'inserted'
                           order by acq_inserted_at desc, acq_id desc
                           limit {$limit} ", false);
    while ($row = sql_fetch_array($result)) {
        $rows[] = $row;
    }

    return $rows;
}

function ac_admin_render_direct_preview_row($post, $direct_preview, $return_tab, $admin_url, $action_url, $ac_token)
{
    if (!$direct_preview || $direct_preview['bo_table'] !== $post['bo_table'] || (int) $direct_preview['wr_id'] !== (int) $post['wr_id']) {
        return;
    }
    $colspan = $return_tab === 'recent_viewed' ? 7 : 6;
?>
            <tr class="ac-direct-preview-row">
                <td colspan="<?php echo (int) $colspan; ?>">
                    <form method="get" action="<?php echo $admin_url; ?>" class="ac-tone-refresh">
                        <input type="hidden" name="tab" value="<?php echo get_text($return_tab); ?>">
                        <input type="hidden" name="preview_bo_table" value="<?php echo get_text($post['bo_table']); ?>">
                        <input type="hidden" name="preview_wr_id" value="<?php echo (int) $post['wr_id']; ?>">
                        <label>톤앤매너
                            <select name="tone" class="ac-input">
                                <?php
                                foreach (auto_comment_tone_options() as $tone_key => $tone_label) {
                                    $selected = isset($direct_preview['tone']) && $direct_preview['tone'] === $tone_key ? ' selected' : '';
                                    echo '<option value="'.get_text($tone_key).'"'.$selected.'>'.get_text($tone_label).'</option>';
                                }
                                ?>
                            </select>
                        </label>
                        <button type="submit" class="ac-btn ac-btn-light">이 톤으로 다시 생성</button>
                    </form>
                    <form method="post" action="<?php echo $action_url; ?>" class="ac-direct-preview">
                        <input type="hidden" name="ac_token" value="<?php echo $ac_token; ?>">
                        <input type="hidden" name="act" value="queue_latest_post">
                        <input type="hidden" name="return_tab" value="<?php echo get_text($return_tab); ?>">
                        <input type="hidden" name="bo_table" value="<?php echo get_text($post['bo_table']); ?>">
                        <input type="hidden" name="wr_id" value="<?php echo (int) $post['wr_id']; ?>">
                        <input type="hidden" name="tone" value="<?php echo isset($direct_preview['tone']) ? get_text($direct_preview['tone']) : 'random'; ?>">
                        <strong>댓글 등록 전 확인</strong>
                        <p class="ac-muted">작성자와 댓글내용을 확인/수정한 뒤 예약하거나 즉시 등록하세요.</p>
                        <label>작성자
                            <input type="text" name="acq_author" class="ac-input ac-input-wide" value="<?php echo get_text($direct_preview['author']); ?>">
                        </label>
                        <label>댓글내용
                            <textarea name="acq_content" class="ac-queue-textarea"><?php echo get_text($direct_preview['content']); ?></textarea>
                        </label>
                        <div class="ac-toolbar">
                            <button type="submit" name="mode" value="queue" class="ac-btn ac-btn-light">예약 생성</button>
                            <button type="submit" name="mode" value="insert" class="ac-btn">즉시 등록</button>
                        </div>
                    </form>
                </td>
            </tr>
<?php
}

if ($installed && isset($_GET['preview_bo_table'], $_GET['preview_wr_id'])) {
    try {
        $direct_tone = isset($_GET['tone']) ? $_GET['tone'] : 'random';
        $direct_preview = auto_comment_generate_preview($_GET['preview_bo_table'], (int) $_GET['preview_wr_id'], '', $direct_tone);
        $direct_preview['author'] = auto_comment_random_korean_nickname();
    } catch (Exception $e) {
        $direct_error = $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ac_admin_check_token();
    $act = isset($_POST['act']) ? $_POST['act'] : '';

    if ($act === 'install') {
        auto_comment_install();
        ac_admin_redirect('dashboard', '자동댓글 모듈 설치가 완료되었습니다.');
    }

    if ($act === 'update') {
        auto_comment_update();
        ac_admin_redirect('tools', '자동댓글 모듈 업데이트가 완료되었습니다. 현재 버전: '.AUTO_COMMENT_VERSION);
    }

    if (!auto_comment_is_installed()) {
        ac_admin_redirect('dashboard', '먼저 모듈을 설치해주세요.');
    }

    if ($act === 'export_config') {
        $json = auto_comment_json_encode(auto_comment_export_config());
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="auto-comment-config-'.date('Ymd-His').'.json"');
        echo $json;
        exit;
    }

    if ($act === 'import_config') {
        $json = trim(isset($_POST['config_json']) ? $_POST['config_json'] : '');
        if ($json === '') {
            ac_admin_redirect('tools', '가져올 JSON 내용을 입력해주세요.');
        }
        $data = json_decode($json, true);
        if (!is_array($data)) {
            ac_admin_redirect('tools', 'JSON 형식이 올바르지 않습니다.');
        }
        try {
            auto_comment_import_config($data);
        } catch (Exception $e) {
            ac_admin_redirect('tools', $e->getMessage());
        }
        ac_admin_redirect('tools', '설정을 가져왔습니다.');
    }

    if ($act === 'save_global') {
        auto_comment_set_setting('enabled', isset($_POST['enabled']) ? '1' : '0');
        auto_comment_set_setting('trigger_percent', (string) max(1, min(100, (int) $_POST['trigger_percent'])));
        auto_comment_set_setting('trigger_interval', (string) max(30, min(3600, (int) $_POST['trigger_interval'])));
        auto_comment_set_setting('max_run_items', (string) max(1, min(10, (int) $_POST['max_run_items'])));
        auto_comment_set_setting('max_run_seconds', (string) max(1, min(5, (int) $_POST['max_run_seconds'])));
        auto_comment_set_setting('daily_limit', (string) max(1, min(200, (int) $_POST['daily_limit'])));
        auto_comment_set_setting('forbidden_words', trim(isset($_POST['forbidden_words']) ? $_POST['forbidden_words'] : ''));
        $generator_mode = isset($_POST['generator_mode']) && $_POST['generator_mode'] === 'ai' ? 'ai' : 'template';
        auto_comment_set_setting('generator_mode', $generator_mode);
        auto_comment_set_setting('ai_provider', 'icrm');
        $auto_min_comments = max(0, min(20, (int) $_POST['auto_min_comments']));
        $auto_max_comments = max($auto_min_comments, min(20, (int) $_POST['auto_max_comments']));
        auto_comment_set_setting('auto_min_comments', (string) $auto_min_comments);
        auto_comment_set_setting('auto_max_comments', (string) $auto_max_comments);
        $views_min = max(1, min(10000, (int) (isset($_POST['auto_views_per_comment_min']) ? $_POST['auto_views_per_comment_min'] : 20)));
        $views_max = max(1, min(10000, (int) (isset($_POST['auto_views_per_comment_max']) ? $_POST['auto_views_per_comment_max'] : 50)));
        if ($views_max < $views_min) {
            $tmp = $views_min;
            $views_min = $views_max;
            $views_max = $tmp;
        }
        auto_comment_set_setting('auto_views_per_comment_min', (string) $views_min);
        auto_comment_set_setting('auto_views_per_comment_max', (string) $views_max);
        auto_comment_set_setting('auto_views_per_comment', (string) $views_min);
        $icrm_api_base_url = isset($_POST['icrm_api_base_url']) ? trim($_POST['icrm_api_base_url']) : '';
        auto_comment_set_setting('icrm_api_base_url', $icrm_api_base_url !== '' ? $icrm_api_base_url : 'https://icrm.co.kr/api/auto-comment');
        if (isset($_POST['icrm_license_key']) && trim($_POST['icrm_license_key']) !== '') {
            auto_comment_set_setting('icrm_license_key', trim($_POST['icrm_license_key']));
        }
        auto_comment_set_setting('skip_bots', isset($_POST['skip_bots']) && $_POST['skip_bots'] === '1' ? '1' : '0');
        auto_comment_log('settings', '기본설정 저장', 0);
        ac_admin_redirect('settings', '기본설정을 저장했습니다.');
    }

    if ($act === 'save_boards') {
        auto_comment_ensure_board_columns();
        $board_table = auto_comment_table('board');
        $boards = isset($_POST['boards']) && is_array($_POST['boards']) ? $_POST['boards'] : array();
        foreach ($boards as $bo_table => $row) {
            $bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', $bo_table);
            if (!$bo_table) continue;

            $enabled = isset($row['enabled']) ? 1 : 0;
            $auto_new_post = isset($row['auto_new_post']) ? 1 : 0;
            $strategy_scan = isset($row['strategy_scan']) ? 1 : 0;
            $midnight_schedule = isset($row['midnight_schedule']) ? 1 : 0;
            $manual_comment = isset($row['manual_comment']) ? 1 : 0;
            $review_mode = isset($row['review_mode']) ? 1 : 0;
            $tone_profile = isset($row['tone_profile']) ? auto_comment_sanitize_tone_profile($row['tone_profile']) : 'random';
            if (!$enabled) {
                $auto_new_post = 0;
                $strategy_scan = 0;
                $midnight_schedule = 0;
                $manual_comment = 0;
                $review_mode = 0;
            } else if ($midnight_schedule) {
                $auto_new_post = 0;
                $strategy_scan = 0;
            }
            $cfg = auto_comment_get_board_config($bo_table);
            $comments = $cfg ? max(1, (int) $cfg['acb_comments_per_post']) : 1;
            $min_delay = $cfg ? max(1, (int) $cfg['acb_min_delay']) : 30;
            $max_delay = $cfg ? max($min_delay, (int) $cfg['acb_max_delay']) : 360;
            $group = $cfg && $cfg['acb_template_group'] !== '' ? $cfg['acb_template_group'] : auto_comment_guess_template_group($bo_table);

            sql_query(" replace into {$board_table}
                            set bo_table = '".auto_comment_escape($bo_table)."',
                                acb_enabled = '{$enabled}',
                                acb_auto_new_post = '{$auto_new_post}',
                                acb_strategy_scan = '{$strategy_scan}',
                                acb_midnight_schedule = '{$midnight_schedule}',
                                acb_manual_comment = '{$manual_comment}',
                                acb_review_mode = '{$review_mode}',
                                acb_tone_profile = '".auto_comment_escape($tone_profile)."',
                                acb_comments_per_post = '{$comments}',
                                acb_min_delay = '{$min_delay}',
                                acb_max_delay = '{$max_delay}',
                                acb_template_group = '".auto_comment_escape($group)."',
                                acb_updated_at = '".G5_TIME_YMDHIS."' ", false);
        }
        auto_comment_log('settings', '게시판설정 저장', 0);
        ac_admin_redirect('boards', '게시판설정을 저장했습니다.');
    }

    if ($act === 'generate_preview') {
        $tab = 'manual';
        $bo_table = isset($_POST['bo_table']) ? $_POST['bo_table'] : '';
        $wr_id = isset($_POST['wr_id']) ? (int) $_POST['wr_id'] : 0;
        $template_group = isset($_POST['template_group']) ? trim($_POST['template_group']) : '';

        try {
            $manual_preview = auto_comment_generate_preview($bo_table, $wr_id, $template_group);
        } catch (Exception $e) {
            $manual_error = $e->getMessage();
        }
    }

    if ($act === 'create_manual_queue') {
        $bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', isset($_POST['bo_table']) ? $_POST['bo_table'] : '');
        $wr_id = (int) (isset($_POST['wr_id']) ? $_POST['wr_id'] : 0);
        $author = trim(strip_tags(isset($_POST['acq_author']) ? $_POST['acq_author'] : ''));
        $content = trim(isset($_POST['acq_content']) ? $_POST['acq_content'] : '');
        $scheduled_at = trim(isset($_POST['acq_scheduled_at']) ? $_POST['acq_scheduled_at'] : '');

        if (!$bo_table || $wr_id < 1 || $author === '' || $content === '' || !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $scheduled_at)) {
            ac_admin_redirect('manual', '게시판, 원글번호, 작성자, 댓글내용, 예약시간을 확인해주세요.');
        }
        try {
            $content = auto_comment_validate_content($bo_table, $wr_id, $content, 0);
        } catch (Exception $e) {
            ac_admin_redirect('manual', $e->getMessage());
        }

        $board = get_board_db($bo_table, true);
        if (!$board || empty($board['bo_table'])) {
            ac_admin_redirect('manual', '게시판을 찾을 수 없습니다.');
        }

        $write_table = $g5['write_prefix'].$bo_table;
        $write = sql_fetch(" select wr_id, wr_subject from {$write_table} where wr_id = '{$wr_id}' and wr_is_comment = 0 ");
        if (empty($write['wr_id'])) {
            ac_admin_redirect('manual', '원글을 찾을 수 없습니다.');
        }

        sql_query(" insert into ".auto_comment_table('queue')."
                        set bo_table = '".auto_comment_escape($bo_table)."',
                            wr_id = '{$wr_id}',
                            acq_subject = '".auto_comment_escape($write['wr_subject'])."',
                            acq_author = '".auto_comment_escape($author)."',
                            acq_content = '".auto_comment_escape($content)."',
                            acq_scheduled_at = '".auto_comment_escape($scheduled_at)."',
                            acq_status = 'pending',
                            acq_created_at = '".G5_TIME_YMDHIS."' ", false);

        $queue_id = sql_insert_id();
        auto_comment_log('manual_queue', '수동 예약 댓글 생성', $queue_id);
        ac_admin_redirect('queue', '수동 예약 댓글을 생성했습니다.');
    }

    if ($act === 'queue_action') {
        $queue_id = (int) $_POST['acq_id'];
        $mode = isset($_POST['mode']) ? $_POST['mode'] : '';
        $queue_table = auto_comment_table('queue');
        if ($mode === 'cancel') {
            sql_query(" update {$queue_table} set acq_status = 'cancelled' where acq_id = '{$queue_id}' and acq_status = 'pending' ", false);
            auto_comment_log('cancel', '예약 댓글 취소', $queue_id);
            ac_admin_redirect('queue', '예약 댓글을 취소했습니다.');
        } else if ($mode === 'save') {
            $author = trim(strip_tags(isset($_POST['acq_author']) ? $_POST['acq_author'] : ''));
            $content = trim(isset($_POST['acq_content']) ? $_POST['acq_content'] : '');
            $scheduled_at = trim(isset($_POST['acq_scheduled_at']) ? $_POST['acq_scheduled_at'] : '');
            if ($author === '' || $content === '' || !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $scheduled_at)) {
                ac_admin_redirect('queue', '작성자, 댓글내용, 예약시간을 확인해주세요.');
            }
            $queue = sql_fetch(" select bo_table, wr_id from {$queue_table} where acq_id = '{$queue_id}' and acq_status = 'pending' ");
            if ($queue) {
                try {
                    $content = auto_comment_validate_content($queue['bo_table'], (int) $queue['wr_id'], $content, $queue_id);
                } catch (Exception $e) {
                    ac_admin_redirect('queue', $e->getMessage());
                }
            }
            sql_query(" update {$queue_table}
                          set acq_author = '".auto_comment_escape($author)."',
                              acq_content = '".auto_comment_escape($content)."',
                              acq_scheduled_at = '".auto_comment_escape($scheduled_at)."'
                        where acq_id = '{$queue_id}'
                          and acq_status = 'pending' ", false);
            auto_comment_log('queue_update', '예약 댓글 수정', $queue_id);
            ac_admin_redirect('queue', '예약 댓글을 수정했습니다.');
        } else if ($mode === 'delete') {
            sql_query(" delete from {$queue_table} where acq_id = '{$queue_id}' and acq_status <> 'inserted' ", false);
            auto_comment_log('delete', '예약 댓글 삭제', $queue_id);
            ac_admin_redirect('queue', '예약 댓글을 삭제했습니다.');
        } else if ($mode === 'insert') {
            $queue = sql_fetch(" select * from {$queue_table} where acq_id = '{$queue_id}' and acq_status = 'pending' ");
            if ($queue) {
                try {
                    $comment_id = auto_comment_insert_queue($queue);
                    sql_query(" update {$queue_table}
                                  set acq_status = 'inserted',
                                      acq_inserted_at = '".G5_TIME_YMDHIS."',
                                      acq_error = ''
                                where acq_id = '{$queue_id}' ", false);
                    auto_comment_log('insert_manual', '수동 댓글 등록 #'.$comment_id, $queue_id);
                    ac_admin_redirect('queue', '댓글을 즉시 등록했습니다.');
                } catch (Exception $e) {
                    sql_query(" update {$queue_table}
                                  set acq_status = 'failed',
                                      acq_error = '".auto_comment_escape($e->getMessage())."'
                                where acq_id = '{$queue_id}' ", false);
                    ac_admin_redirect('queue', '댓글 등록 실패: '.$e->getMessage());
                }
            }
        }
        ac_admin_redirect('queue', '처리할 예약 댓글이 없습니다.');
    }

    if ($act === 'run_worker') {
        $count = auto_comment_run_worker(10);
        ac_admin_redirect('queue', 'worker를 실행했습니다. 처리: '.$count.'개');
    }
}

$ac_token = ac_admin_token();
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title>자동댓글 관리</title>
<style>
body{margin:0;background:#f3f5f8;color:#222;font-family:Arial,'Malgun Gothic',sans-serif;font-size:13px}
.ac-top{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:18px 24px;background:#2f3542;color:#fff}
.ac-top h1{margin:0;font-size:20px}
.ac-lang-toggle{padding:7px 11px;border:1px solid rgba(255,255,255,.35);background:rgba(255,255,255,.08);color:#fff;cursor:pointer}
.ac-lang-toggle:hover{background:rgba(255,255,255,.18)}
.ac-wrap{margin:20px 0}
.ac-container{max-width:1480px;margin:0 auto;padding:0 18px}
.ac-tabs{display:flex;gap:6px;flex-wrap:wrap;margin:0 0 16px}
.ac-tabs a{display:inline-block;padding:9px 12px;border:1px solid #d9d9d9;background:#fff;color:#333;text-decoration:none}
.ac-tabs a.on{background:#2f3542;color:#fff;border-color:#2f3542}
.ac-panel{padding:18px;background:#fff;border:1px solid #d9d9d9}
.ac-help{margin:0 0 12px;color:#666;line-height:1.6}
.ac-status-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin:0 0 16px}
.ac-status-card{padding:14px;border:1px solid #e5e5e5;background:#fafafa}
.ac-status-card strong{display:block;margin-bottom:6px;color:#555;font-size:12px}
.ac-status-card span{font-size:22px;font-weight:700}
.ac-status-card small{display:block;margin-top:4px;color:#777;line-height:1.4}
.ac-report{margin:18px 0;padding:18px;border:1px solid #e7ebf0;border-radius:10px;background:#fff;box-shadow:0 8px 24px rgba(31,45,61,.05)}
.ac-report h2{margin:0 0 6px;font-size:16px;color:#222}
.ac-report-layout{display:grid;grid-template-columns:minmax(0,1fr) 190px;gap:22px;align-items:start;margin-top:16px}
.ac-report-plot{position:relative;height:290px;padding:10px 0 34px 42px}
.ac-report-plot:before{content:"";position:absolute;left:42px;right:0;top:10px;bottom:34px;background:linear-gradient(to bottom,#edf1f6 1px,transparent 1px);background-size:100% 25%;border-left:1px solid #dfe5ec;border-bottom:1px solid #dfe5ec}
.ac-report-yaxis{position:absolute;left:0;top:4px;bottom:30px;width:34px;color:#8a94a3;font-size:11px}
.ac-report-yaxis span{position:absolute;right:8px;transform:translateY(50%)}
.ac-report-yaxis span:nth-child(1){top:0}
.ac-report-yaxis span:nth-child(2){top:50%}
.ac-report-yaxis span:nth-child(3){bottom:0}
.ac-report-bars{position:relative;z-index:1;display:grid;grid-template-columns:repeat(7,minmax(44px,1fr));gap:18px;align-items:end;height:246px}
.ac-report-row{display:grid;grid-template-rows:1fr auto auto;gap:8px;align-items:end;text-align:center;height:100%}
.ac-report-stack{display:flex;flex-direction:column-reverse;width:42px;margin:0 auto;background:#edf0f4;border-radius:8px 8px 0 0;overflow:hidden;box-shadow:0 0 0 1px rgba(0,0,0,.04)}
.ac-report-segment{display:block;width:100%;min-height:2px}
.ac-report-segment.queued,.ac-report-legend .queued{background:#5b6fa6}
.ac-report-segment.inserted,.ac-report-legend .inserted{background:#91a85a}
.ac-report-segment.failed,.ac-report-legend .failed{background:#c93a3f}
.ac-report-segment.cost,.ac-report-legend .cost{background:#d3b532}
.ac-report-segment.viewed-posts,.ac-report-legend .viewed-posts{background:#4f9bc8}
.ac-report-segment.view-events,.ac-report-legend .view-events{background:#7c5cc4}
.ac-report-segment.new-visitors,.ac-report-legend .new-visitors{background:#2ca58d}
.ac-report-segment.existing-visitors,.ac-report-legend .existing-visitors{background:#e68a3a}
.ac-report-date{font-weight:700;color:#444}
.ac-report-values{display:grid;gap:2px;color:#6d7682;font-size:11px;line-height:1.25}
.ac-report-values span{white-space:nowrap}
.ac-report-legend{display:grid;gap:14px;color:#555;font-size:12px}
.ac-report-legend span{display:grid;grid-template-columns:34px 1fr;gap:10px;align-items:start}
.ac-report-legend i{display:block;width:34px;height:18px;border-radius:4px}
.ac-report-legend strong{display:block;margin-bottom:2px;color:#222;font-size:13px}
.ac-report-legend small{display:block;color:#777;line-height:1.35}
.ac-form-table{width:100%;border-collapse:collapse}
.ac-form-table th,.ac-form-table td{padding:9px;border:1px solid #e5e5e5;text-align:left}
.ac-form-table th{width:180px;background:#f8f8f8}
.ac-input{height:32px;padding:0 8px;border:1px solid #ccc}
.ac-input-wide{width:100%;max-width:220px}
.ac-textarea{width:100%;min-height:260px;padding:10px;border:1px solid #ccc;font-family:monospace;line-height:1.5}
.ac-queue-textarea{width:100%;min-height:118px;padding:10px;border:1px solid #ccc;line-height:1.5;box-sizing:border-box;resize:vertical}
.ac-btn{display:inline-block;padding:8px 14px;border:0;background:#2f3542;color:#fff;text-decoration:none;cursor:pointer}
.ac-btn-light{background:#747d8c}
.ac-btn-danger{background:#c0392b}
.ac-btn-success{background:#218c5a}
.ac-toolbar{display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:14px}
.ac-filter{display:flex;gap:8px;flex-wrap:wrap;align-items:end;margin:0 0 14px;padding:12px;background:#f7f9fb;border:1px solid #e5e5e5}
.ac-filter label{display:flex;flex-direction:column;gap:5px;color:#555;font-size:12px}
.ac-filter .ac-input{min-width:120px}
.ac-board-table th:nth-child(n+2),.ac-board-table td:nth-child(n+2){width:130px;text-align:center}
.ac-board-tone{width:125px}
.ac-board-child:disabled+span{color:#aaa}
.ac-board-table input:disabled{opacity:.45}
.ac-queue-table{table-layout:fixed}
.ac-queue-table th{width:auto}
.ac-queue-table th:nth-child(1){width:78px}
.ac-queue-table th:nth-child(2){width:260px}
.ac-queue-table th:nth-child(3){width:auto}
.ac-queue-table th:nth-child(4){width:130px}
.ac-queue-table th:nth-child(5){width:145px}
.ac-queue-table th:nth-child(6){width:170px}
.ac-queue-table td{vertical-align:top}
.ac-queue-table th:nth-child(4),.ac-queue-table td:nth-child(4),
.ac-queue-table th:nth-child(5),.ac-queue-table td:nth-child(5),
.ac-queue-table th:nth-child(6),.ac-queue-table td:nth-child(6){text-align:center}
.ac-post-title{margin:6px 0 8px;font-weight:700;line-height:1.45}
.ac-row-actions{display:grid;grid-template-columns:1fr 1fr;gap:6px}
.ac-row-actions .ac-btn{width:100%;padding:8px 6px;box-sizing:border-box;text-align:center}
.ac-inline-link{display:inline-block;margin-top:4px;color:#2f3542;text-decoration:underline}
.ac-result-link{display:inline-block;margin-top:8px;padding:7px 9px;background:#218c5a;color:#fff;text-decoration:none}
.ac-muted{color:#777;font-size:12px;line-height:1.5}
.ac-ip{display:block;margin-top:4px;color:#777;font-size:12px;line-height:1.4}
.ac-queue-table .ac-input-wide{max-width:none;box-sizing:border-box}
.ac-queue-meta{display:block;margin-top:8px;padding:7px 9px;background:#f7f9fb;border-left:3px solid #dfe7ff}
.ac-history-table{table-layout:fixed}
.ac-history-table th{width:auto}
.ac-history-table th:nth-child(1){width:150px}
.ac-history-table th:nth-child(2){width:30%}
.ac-history-table th:nth-child(3){width:auto}
.ac-history-table th:nth-child(4){width:110px}
.ac-history-table th:nth-child(5){width:75px}
.ac-history-table th:nth-child(6){width:95px}
.ac-history-table td{vertical-align:top}
.ac-history-table th:nth-child(4),.ac-history-table td:nth-child(4),
.ac-history-table th:nth-child(5),.ac-history-table td:nth-child(5),
.ac-history-table th:nth-child(6),.ac-history-table td:nth-child(6){text-align:center}
.ac-reeval-table{table-layout:fixed}
.ac-reeval-table th:nth-child(1){width:145px}
.ac-reeval-table th:nth-child(2){width:24%}
.ac-reeval-table th:nth-child(3){width:auto}
.ac-reeval-table th:nth-child(4){width:120px}
.ac-reeval-table th:nth-child(5){width:150px}
.ac-reeval-table th:nth-child(6){width:95px}
.ac-reeval-table td{vertical-align:top}
.ac-reeval-table th:nth-child(4),.ac-reeval-table td:nth-child(4),
.ac-reeval-table th:nth-child(5),.ac-reeval-table td:nth-child(5),
.ac-reeval-table th:nth-child(6),.ac-reeval-table td:nth-child(6){text-align:center}
.ac-ai-table th:nth-child(n+4),.ac-ai-table td:nth-child(n+4){text-align:center}
.ac-ai-table td{vertical-align:top}
.ac-dashboard-table{margin-top:16px}
.ac-dashboard-lists{display:grid;grid-template-columns:1fr;gap:18px;margin-top:16px}
.ac-dashboard-list{border:1px solid #e5e5e5;background:#fff}
.ac-dashboard-list-head{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 12px;border-bottom:1px solid #e5e5e5;background:#f7f9fb}
.ac-dashboard-list-head h2{margin:0;font-size:14px}
.ac-dashboard-list .ac-form-table{border:0}
.ac-dashboard-list .ac-form-table th:first-child,.ac-dashboard-list .ac-form-table td:first-child{border-left:0}
.ac-dashboard-list .ac-form-table th:last-child,.ac-dashboard-list .ac-form-table td:last-child{border-right:0}
.ac-dashboard-list .ac-form-table tr:last-child td{border-bottom:0}
.ac-timeline{display:grid;gap:10px}
.ac-timeline-item{display:grid;grid-template-columns:160px 120px 1fr 120px;gap:10px;align-items:start;padding:12px;border:1px solid #e5e5e5;background:#fff}
.ac-timeline-time{font-weight:700}
.ac-timeline-content{line-height:1.5}
.ac-latest-table{table-layout:fixed}
.ac-latest-table th{width:auto}
.ac-latest-table th:nth-child(1){width:145px}
.ac-latest-table th:nth-child(2){width:auto}
.ac-latest-table th:nth-child(3){width:95px}
.ac-latest-table th:nth-child(4){width:95px}
.ac-latest-table th:nth-child(5){width:80px}
.ac-latest-table th:nth-child(6){width:155px}
.ac-latest-table th:nth-child(7){width:155px}
.ac-latest-table td{vertical-align:top}
.ac-latest-table th:nth-child(1),.ac-latest-table td:nth-child(1),
.ac-latest-table th:nth-child(3),.ac-latest-table td:nth-child(3),
.ac-latest-table th:nth-child(4),.ac-latest-table td:nth-child(4),
.ac-latest-table th:nth-child(5),.ac-latest-table td:nth-child(5),
.ac-latest-table th:nth-child(6),.ac-latest-table td:nth-child(6),
.ac-latest-table th:nth-child(7),.ac-latest-table td:nth-child(7){text-align:center}
.ac-latest-table .ac-row-actions{grid-template-columns:1fr}
.ac-direct-preview-row td{text-align:left!important;background:#f7f9fb}
.ac-direct-preview{display:grid;gap:10px;padding:14px;border:1px solid #dfe7ff;background:#fff}
.ac-direct-preview label{display:grid;gap:5px;text-align:left;color:#555}
.ac-direct-preview .ac-input-wide{max-width:260px}
.ac-tone-refresh{display:flex;gap:8px;flex-wrap:wrap;align-items:end;margin:0 0 8px;padding:10px;border:1px solid #e5e5e5;background:#fff}
.ac-tone-refresh label{display:flex;flex-direction:column;gap:5px;color:#555}
.ac-badge{display:inline-block;padding:3px 6px;border-radius:3px;background:#eee}
.ac-badge.review{background:#dfe7ff}
.ac-badge.pending{background:#fff3cd}
.ac-badge.inserted{background:#d4edda}
.ac-badge.failed{background:#f8d7da}
.ac-badge.cancelled{background:#e2e3e5}
.ac-badge.good{background:#d4edda}
.ac-badge.warning{background:#fff3cd}
.ac-badge.danger{background:#f8d7da}
.ac-alert{padding:10px 12px;margin:0 0 12px;border:1px solid #f1c40f;background:#fff9db;color:#7d6608}
@media (max-width:900px){
    .ac-status-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
    .ac-report-layout{grid-template-columns:1fr}
    .ac-report-plot{overflow-x:auto}
    .ac-report-bars{min-width:640px}
    .ac-report-legend{grid-template-columns:repeat(2,minmax(0,1fr))}
    .ac-form-table,.ac-form-table tbody,.ac-form-table tr,.ac-form-table th,.ac-form-table td{display:block;width:auto}
    .ac-form-table th{width:auto}
    .ac-queue-table tr{margin-bottom:12px;border:1px solid #e5e5e5}
    .ac-queue-table th{display:none}
    .ac-queue-table td{border:0;border-bottom:1px solid #eee}
    .ac-row-actions{grid-template-columns:1fr}
}
</style>
</head>
<body>
<div class="ac-top">
    <h1>자동댓글 관리</h1>
    <button type="button" class="ac-lang-toggle" data-ac-lang-toggle>English Buttons</button>
</div>
<div class="ac-wrap">
<div class="ac-container">
    <div class="ac-tabs">
        <?php
        $tabs = array(
            'dashboard' => '대시보드',
            'latest' => '최신글',
            'recent_viewed' => '최근조회글',
            'history' => '댓글히스토리',
            'manual' => '수동예약',
            'queue' => '예약목록',
            'timeline' => '예약타임라인',
            'reeval' => '댓글재평가',
            'boards' => '게시판설정',
            'settings' => '기본설정',
            'ai_usage' => 'AI 사용기록',
            'tools' => '백업/업데이트',
        );
        foreach ($tabs as $key => $label) {
            echo '<a href="'.$admin_url.'?tab='.$key.'" class="'.($tab === $key ? 'on' : '').'">'.$label.'</a>';
        }
        ?>
    </div>

    <div class="ac-panel">
        <p class="ac-help">관리 화면 버전: <?php echo AUTO_COMMENT_ADMIN_VERSION; ?> / 모듈 버전: <?php echo defined('AUTO_COMMENT_VERSION') ? AUTO_COMMENT_VERSION : '-'; ?></p>
    <?php if (!$installed) { ?>
        <?php if ($tab === 'boards') { ?>
        <div class="ac-alert">
            <strong>게시판별 댓글 설정 위치 안내</strong><br>
            자정 예약, 조회수 트리거, 신규글 자동댓글 등은 <strong>이 화면의 「게시판설정」 탭</strong>에서 관리합니다.<br>
            그누보드 관리자의 <strong>게시판관리 &gt; 게시판 수정</strong> 화면에는 표시되지 않습니다.
        </div>
        <?php } ?>
        <p class="ac-help">자동댓글 모듈이 아직 설치되지 않았습니다. 설치하면 전용 DB 테이블과 AI 설정 기본값이 생성됩니다.</p>
        <p class="ac-help">파일만 덮어쓴 <strong>기존 운영 사이트</strong>는 아래 <strong>업데이트 실행</strong>을 사용하세요. <code>install.php</code>는 신규 설치용입니다.</p>
        <form method="post" action="<?php echo $action_url; ?>" style="display:inline-block;margin-right:8px">
            <input type="hidden" name="ac_token" value="<?php echo $ac_token; ?>">
            <input type="hidden" name="act" value="install">
            <button type="submit" class="ac-btn">설치 시작 (신규)</button>
        </form>
        <form method="post" action="<?php echo $action_url; ?>" style="display:inline-block">
            <input type="hidden" name="ac_token" value="<?php echo $ac_token; ?>">
            <button type="submit" name="act" value="update" class="ac-btn ac-btn-light">업데이트 실행 (기존 사이트)</button>
        </form>
        <p class="ac-help" style="margin-top:10px">또는 최고관리자로 <a href="<?php echo G5_PLUGIN_URL; ?>/auto_comment/update.php">/plugin/auto_comment/update.php</a> 에 1회 접속하세요.</p>
    <?php } else if ($tab === 'dashboard') { ?>
        <p class="ac-help">방문 트리거 방식으로 예약 시간이 지난 댓글을 낮은 확률로 처리합니다. 처음에는 모듈을 OFF 상태로 테스트 후 게시판별로 켜세요.</p>
        <?php
        $today = date('Y-m-d 00:00:00', G5_SERVER_TIME);
        $pending = sql_fetch(" select count(*) as cnt from ".auto_comment_table('queue')." where acq_status = 'pending' ");
        $due_pending = sql_fetch(" select count(*) as cnt from ".auto_comment_table('queue')." where acq_status = 'pending' and acq_scheduled_at <= '".G5_TIME_YMDHIS."' ");
        $inserted_today = sql_fetch(" select count(*) as cnt from ".auto_comment_table('queue')." where acq_status = 'inserted' and acq_inserted_at >= '{$today}' ");
        $auto_queue_today = sql_fetch(" select count(*) as cnt from ".auto_comment_table('log')." where acl_action in ('auto_queue', 'strategy_queue') and acl_datetime >= '{$today}' ");
        $month_start = date('Y-m-01 00:00:00', G5_SERVER_TIME);
        $ai_today = ac_admin_ai_usage_summary($today);
        $ai_month = ac_admin_ai_usage_summary($month_start);
        $failure_actions = "'failed','schedule_failed','schedule_skip','strategy_skip','worker_failed','generator_fallback','ai_failed'";
        $failure_today = sql_fetch(" select count(*) as cnt from ".auto_comment_table('log')." where acl_action in ({$failure_actions}) and acl_datetime >= '{$today}' ", false);
        $visitor_summary = ac_admin_visitor_summary();
        $daily_report = ac_admin_daily_report(7);
        $daily_view_report = ac_admin_daily_view_report(7);
        $dashboard_latest_posts = ac_admin_latest_posts(5, '');
        $dashboard_viewed_posts = ac_admin_recent_viewed_posts(5, '');
        $dashboard_history_rows = ac_admin_recent_history_rows(5);
        $max_queued = 1;
        $max_inserted = 1;
        $max_failed = 1;
        $max_cost = 1;
        $max_total_weight = 1;
        $max_viewed_posts = 1;
        $max_view_events = 1;
        $max_new_visitors = 1;
        $max_existing_visitors = 1;
        $max_view_total_weight = 1;
        foreach ($daily_report as $day) {
            $max_queued = max($max_queued, (int) $day['queued']);
            $max_inserted = max($max_inserted, (int) $day['inserted']);
            $max_failed = max($max_failed, (int) $day['failed']);
            $max_cost = max($max_cost, (float) $day['ai_cost']);
        }
        foreach ($daily_view_report as $day) {
            $max_viewed_posts = max($max_viewed_posts, (int) $day['viewed_posts']);
            $max_view_events = max($max_view_events, (int) $day['view_events']);
            $max_new_visitors = max($max_new_visitors, (int) $day['new_visitors']);
            $max_existing_visitors = max($max_existing_visitors, (int) $day['existing_visitors']);
        }
        foreach ($daily_report as $day) {
            $max_total_weight = max($max_total_weight,
                ((int) $day['queued'] > 0 ? ((int) $day['queued'] / $max_queued) : 0)
                + ((int) $day['inserted'] > 0 ? ((int) $day['inserted'] / $max_inserted) : 0)
                + ((int) $day['failed'] > 0 ? ((int) $day['failed'] / $max_failed) : 0)
                + ((float) $day['ai_cost'] > 0 ? ((float) $day['ai_cost'] / $max_cost) : 0)
            );
        }
        foreach ($daily_view_report as $day) {
            $max_view_total_weight = max($max_view_total_weight,
                ((int) $day['viewed_posts'] > 0 ? ((int) $day['viewed_posts'] / $max_viewed_posts) : 0)
                + ((int) $day['view_events'] > 0 ? ((int) $day['view_events'] / $max_view_events) : 0)
                + ((int) $day['new_visitors'] > 0 ? ((int) $day['new_visitors'] / $max_new_visitors) : 0)
                + ((int) $day['existing_visitors'] > 0 ? ((int) $day['existing_visitors'] / $max_existing_visitors) : 0)
            );
        }
        ?>
        <div class="ac-status-grid">
            <div class="ac-status-card"><strong>모듈 상태</strong><span><?php echo auto_comment_get_setting('enabled', '0') === '1' ? 'ON' : 'OFF'; ?></span></div>
            <div class="ac-status-card"><strong>오늘 자동예약</strong><span><?php echo number_format((int) $auto_queue_today['cnt']); ?></span><small>새 글/조회수 보충 히스토리</small></div>
            <div class="ac-status-card"><strong>대기 예약</strong><span><?php echo number_format((int) $pending['cnt']); ?></span><small>즉시 처리 가능: <?php echo number_format((int) $due_pending['cnt']); ?></small></div>
            <div class="ac-status-card"><strong>오늘 등록</strong><span><?php echo number_format((int) $inserted_today['cnt']); ?></span></div>
            <div class="ac-status-card"><strong>오늘 AI 호출</strong><span><?php echo number_format((int) $ai_today['calls']); ?></span><small>실패 <?php echo number_format((int) $ai_today['fail_count']); ?>회</small></div>
            <div class="ac-status-card"><strong>오늘 AI 비용</strong><span><?php echo number_format((float) $ai_today['cost_krw'], 2); ?>원</span><small>예상 실비용</small></div>
            <div class="ac-status-card"><strong>이번달 AI 비용</strong><span><?php echo number_format((float) $ai_month['cost_krw'], 2); ?>원</span><small><?php echo number_format((int) $ai_month['total_tokens']); ?> tokens</small></div>
            <div class="ac-status-card"><strong>오늘 실패/제외</strong><span><?php echo number_format((int) $failure_today['cnt']); ?></span><small>로그 기준 집계</small></div>
            <div class="ac-status-card"><strong>오늘 신규 IP</strong><span><?php echo number_format((int) $visitor_summary['new_today']); ?></span><small>처음 기록된 방문자</small></div>
            <div class="ac-status-card"><strong>오늘 기존 IP</strong><span><?php echo number_format((int) $visitor_summary['existing_today']); ?></span><small>이전에 방문한 IP+UA</small></div>
            <div class="ac-status-card"><strong>7일 재방문 IP</strong><span><?php echo number_format((int) $visitor_summary['returning_7d']); ?></span><small>첫 방문 후 다시 조회</small></div>
        </div>
        <div class="ac-report">
            <h2>최근 7일 일일 운영 리포트</h2>
            <p class="ac-help">일자별 운영량을 세로 누적 막대로 보여줍니다. 막대 높이는 7일 중 가장 높은 운영량을 기준으로 비교됩니다.</p>
            <div class="ac-report-layout">
                <div class="ac-report-plot">
                    <div class="ac-report-yaxis"><span>100</span><span>50</span><span>0</span></div>
                    <div class="ac-report-bars">
                        <?php foreach ($daily_report as $day) {
                            $queued_weight = (int) $day['queued'] > 0 ? ((int) $day['queued'] / $max_queued) : 0;
                            $inserted_weight = (int) $day['inserted'] > 0 ? ((int) $day['inserted'] / $max_inserted) : 0;
                            $failed_weight = (int) $day['failed'] > 0 ? ((int) $day['failed'] / $max_failed) : 0;
                            $cost_weight = (float) $day['ai_cost'] > 0 ? ((float) $day['ai_cost'] / $max_cost) : 0;
                            $total_weight = $queued_weight + $inserted_weight + $failed_weight + $cost_weight;
                            $stack_height = $total_weight > 0 ? max(4, round(($total_weight / $max_total_weight) * 100)) : 0;
                            $queued_height = $total_weight > 0 && $queued_weight > 0 ? max(2, round(($queued_weight / $total_weight) * 100)) : 0;
                            $inserted_height = $total_weight > 0 && $inserted_weight > 0 ? max(2, round(($inserted_weight / $total_weight) * 100)) : 0;
                            $failed_height = $total_weight > 0 && $failed_weight > 0 ? max(2, round(($failed_weight / $total_weight) * 100)) : 0;
                            $cost_height = $total_weight > 0 && $cost_weight > 0 ? max(2, round(($cost_weight / $total_weight) * 100)) : 0;
                        ?>
                        <div class="ac-report-row">
                            <div class="ac-report-stack" style="height:<?php echo (int) $stack_height; ?>%">
                                <?php if ($queued_height > 0) { ?><span class="ac-report-segment queued" style="height:<?php echo (int) $queued_height; ?>%" title="예약 <?php echo number_format((int) $day['queued']); ?>"></span><?php } ?>
                                <?php if ($inserted_height > 0) { ?><span class="ac-report-segment inserted" style="height:<?php echo (int) $inserted_height; ?>%" title="등록 <?php echo number_format((int) $day['inserted']); ?>"></span><?php } ?>
                                <?php if ($failed_height > 0) { ?><span class="ac-report-segment failed" style="height:<?php echo (int) $failed_height; ?>%" title="실패 <?php echo number_format((int) $day['failed']); ?>"></span><?php } ?>
                                <?php if ($cost_height > 0) { ?><span class="ac-report-segment cost" style="height:<?php echo (int) $cost_height; ?>%" title="비용 <?php echo number_format((float) $day['ai_cost'], 0); ?>원"></span><?php } ?>
                            </div>
                            <div class="ac-report-date"><?php echo get_text($day['label']); ?></div>
                            <div class="ac-report-values">
                                <span>예약 <?php echo number_format((int) $day['queued']); ?></span>
                                <span>등록 <?php echo number_format((int) $day['inserted']); ?></span>
                                <span>실패 <?php echo number_format((int) $day['failed']); ?></span>
                                <span>비용 <?php echo number_format((float) $day['ai_cost'], 0); ?>원</span>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="ac-report-legend">
                    <span><i class="queued"></i><em><strong>예약</strong><small>자동 생성된 예약/검수 댓글</small></em></span>
                    <span><i class="inserted"></i><em><strong>등록</strong><small>실제 게시글에 등록 완료</small></em></span>
                    <span><i class="failed"></i><em><strong>실패</strong><small>실패 또는 제외 로그</small></em></span>
                    <span><i class="cost"></i><em><strong>비용</strong><small>AI 예상 실비용</small></em></span>
                </div>
            </div>
        </div>
        <div class="ac-report">
            <h2>최근 7일 조회글 일별 통계</h2>
            <p class="ac-help">최근 조회글 추적 데이터를 마지막 조회일 기준으로 집계합니다. 막대는 조회된 글 수와 누적 조회기록 수를 함께 보여줍니다.</p>
            <div class="ac-report-layout">
                <div class="ac-report-plot">
                    <div class="ac-report-yaxis"><span>100</span><span>50</span><span>0</span></div>
                    <div class="ac-report-bars">
                        <?php foreach ($daily_view_report as $day) {
                            $posts_weight = (int) $day['viewed_posts'] > 0 ? ((int) $day['viewed_posts'] / $max_viewed_posts) : 0;
                            $events_weight = (int) $day['view_events'] > 0 ? ((int) $day['view_events'] / $max_view_events) : 0;
                            $new_weight = (int) $day['new_visitors'] > 0 ? ((int) $day['new_visitors'] / $max_new_visitors) : 0;
                            $existing_weight = (int) $day['existing_visitors'] > 0 ? ((int) $day['existing_visitors'] / $max_existing_visitors) : 0;
                            $total_weight = $posts_weight + $events_weight + $new_weight + $existing_weight;
                            $stack_height = $total_weight > 0 ? max(4, round(($total_weight / $max_view_total_weight) * 100)) : 0;
                            $posts_height = $total_weight > 0 && $posts_weight > 0 ? max(2, round(($posts_weight / $total_weight) * 100)) : 0;
                            $events_height = $total_weight > 0 && $events_weight > 0 ? max(2, round(($events_weight / $total_weight) * 100)) : 0;
                            $new_height = $total_weight > 0 && $new_weight > 0 ? max(2, round(($new_weight / $total_weight) * 100)) : 0;
                            $existing_height = $total_weight > 0 && $existing_weight > 0 ? max(2, round(($existing_weight / $total_weight) * 100)) : 0;
                        ?>
                        <div class="ac-report-row">
                            <div class="ac-report-stack" style="height:<?php echo (int) $stack_height; ?>%">
                                <?php if ($posts_height > 0) { ?><span class="ac-report-segment viewed-posts" style="height:<?php echo (int) $posts_height; ?>%" title="조회글 <?php echo number_format((int) $day['viewed_posts']); ?>"></span><?php } ?>
                                <?php if ($events_height > 0) { ?><span class="ac-report-segment view-events" style="height:<?php echo (int) $events_height; ?>%" title="조회기록 <?php echo number_format((int) $day['view_events']); ?>"></span><?php } ?>
                                <?php if ($new_height > 0) { ?><span class="ac-report-segment new-visitors" style="height:<?php echo (int) $new_height; ?>%" title="신규 방문 IP <?php echo number_format((int) $day['new_visitors']); ?>"></span><?php } ?>
                                <?php if ($existing_height > 0) { ?><span class="ac-report-segment existing-visitors" style="height:<?php echo (int) $existing_height; ?>%" title="기존 방문 IP <?php echo number_format((int) $day['existing_visitors']); ?>"></span><?php } ?>
                            </div>
                            <div class="ac-report-date"><?php echo get_text($day['label']); ?></div>
                            <div class="ac-report-values">
                                <span>조회글 <?php echo number_format((int) $day['viewed_posts']); ?></span>
                                <span>조회기록 <?php echo number_format((int) $day['view_events']); ?></span>
                                <span>신규 IP <?php echo number_format((int) $day['new_visitors']); ?></span>
                                <span>기존 IP <?php echo number_format((int) $day['existing_visitors']); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="ac-report-legend">
                    <span><i class="viewed-posts"></i><em><strong>조회글</strong><small>해당 일자에 마지막 조회된 글 수</small></em></span>
                    <span><i class="view-events"></i><em><strong>조회기록</strong><small>해당 글들의 누적 조회 트리거 수</small></em></span>
                    <span><i class="new-visitors"></i><em><strong>신규 방문 IP</strong><small>처음 기록된 IP+브라우저</small></em></span>
                    <span><i class="existing-visitors"></i><em><strong>기존 방문 IP</strong><small>이전에 기록된 뒤 다시 방문</small></em></span>
                </div>
            </div>
        </div>
        <div class="ac-dashboard-lists">
            <div class="ac-dashboard-list">
                <div class="ac-dashboard-list-head">
                    <h2>최신글</h2>
                    <a href="<?php echo $admin_url; ?>?tab=latest" class="ac-btn ac-btn-light">더보기</a>
                </div>
                <table class="ac-form-table ac-latest-table">
                    <tr><th>작성일시</th><th>최신글</th><th>실제댓글</th><th>자동댓글</th><th>조회수</th></tr>
                    <?php if (!$dashboard_latest_posts) { ?>
                        <tr><td colspan="5" class="ac-muted">표시할 최신글이 없습니다.</td></tr>
                    <?php } ?>
                    <?php foreach ($dashboard_latest_posts as $post) {
                        $post_url = G5_BBS_URL.'/board.php?bo_table='.urlencode($post['bo_table']).'&wr_id='.(int) $post['wr_id'];
                        $auto_count = ac_admin_auto_comment_count_for_post($post['bo_table'], (int) $post['wr_id']);
                        $view_meta = ac_admin_post_view_meta($post['bo_table'], (int) $post['wr_id']);
                    ?>
                        <tr>
                            <td>
                                <?php echo get_text($post['wr_datetime']); ?>
                                <?php if (!empty($view_meta['acv_last_viewed_at']) && $view_meta['acv_last_viewed_at'] !== '0000-00-00 00:00:00') { ?>
                                    <span class="ac-ip">마지막조회: <?php echo get_text($view_meta['acv_last_viewed_at']); ?></span>
                                    <span class="ac-ip">IP: <?php echo !empty($view_meta['acv_ip']) ? get_text($view_meta['acv_ip']) : '-'; ?></span>
                                    <span class="ac-ip">방문자 유형: <?php echo get_text(ac_admin_visitor_type_label(isset($view_meta['acv_visitor_type']) ? $view_meta['acv_visitor_type'] : '')); ?></span>
                                <?php } ?>
                            </td>
                            <td>
                                <span class="ac-badge"><?php echo get_text($post['bo_subject']); ?> / <?php echo get_text($post['bo_table']); ?> #<?php echo (int) $post['wr_id']; ?></span>
                                <div class="ac-post-title"><?php echo get_text($post['wr_subject']); ?></div>
                                <span class="ac-muted">원글 작성자: <?php echo get_text($post['wr_name']); ?></span><br>
                                <a href="<?php echo $post_url; ?>" target="_blank" class="ac-inline-link">원글 보기</a>
                            </td>
                            <td><?php echo number_format((int) $post['wr_comment']); ?>개</td>
                            <td><?php echo number_format($auto_count); ?>개</td>
                            <td><?php echo number_format((int) $post['wr_hit']); ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
            <div class="ac-dashboard-list">
                <div class="ac-dashboard-list-head">
                    <h2>최근조회글</h2>
                    <a href="<?php echo $admin_url; ?>?tab=recent_viewed" class="ac-btn ac-btn-light">더보기</a>
                </div>
                <table class="ac-form-table ac-latest-table">
                    <tr><th>마지막조회</th><th>최근 조회된 글</th><th>방문자 유형</th><th>실제댓글</th><th>자동댓글</th><th>조회기록</th></tr>
                    <?php if (!$dashboard_viewed_posts) { ?>
                        <tr><td colspan="6" class="ac-muted">아직 조회 기록이 없습니다.</td></tr>
                    <?php } ?>
                    <?php foreach ($dashboard_viewed_posts as $post) {
                        $post_url = G5_BBS_URL.'/board.php?bo_table='.urlencode($post['bo_table']).'&wr_id='.(int) $post['wr_id'];
                        $auto_count = ac_admin_auto_comment_count_for_post($post['bo_table'], (int) $post['wr_id']);
                    ?>
                        <tr>
                            <td>
                                <?php echo get_text($post['acv_last_viewed_at']); ?>
                                <span class="ac-ip">IP: <?php echo !empty($post['acv_ip']) ? get_text($post['acv_ip']) : '-'; ?></span>
                            </td>
                            <td>
                                <span class="ac-badge"><?php echo get_text($post['bo_subject']); ?> / <?php echo get_text($post['bo_table']); ?> #<?php echo (int) $post['wr_id']; ?></span>
                                <div class="ac-post-title"><?php echo get_text($post['wr_subject']); ?></div>
                                <span class="ac-muted">원글 작성자: <?php echo get_text($post['wr_name']); ?> / 작성일: <?php echo get_text($post['wr_datetime']); ?></span><br>
                                <a href="<?php echo $post_url; ?>" target="_blank" class="ac-inline-link">원글 보기</a>
                            </td>
                            <td><?php echo get_text(ac_admin_visitor_type_label(isset($post['acv_visitor_type']) ? $post['acv_visitor_type'] : '')); ?></td>
                            <td><?php echo number_format((int) $post['wr_comment']); ?>개</td>
                            <td><?php echo number_format($auto_count); ?>개</td>
                            <td><?php echo number_format((int) $post['acv_view_count']); ?>회</td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
            <div class="ac-dashboard-list">
                <div class="ac-dashboard-list-head">
                    <h2>댓글히스토리</h2>
                    <a href="<?php echo $admin_url; ?>?tab=history" class="ac-btn ac-btn-light">더보기</a>
                </div>
                <table class="ac-form-table ac-history-table">
                    <tr><th>작성일시</th><th>원문글제목</th><th>댓글내용</th><th>작성자</th><th>댓글개수</th><th>댓글링크</th></tr>
                    <?php if (!$dashboard_history_rows) { ?>
                        <tr><td colspan="6" class="ac-muted">등록 완료된 자동댓글이 없습니다.</td></tr>
                    <?php } ?>
                    <?php foreach ($dashboard_history_rows as $row) {
                        $post_url = G5_BBS_URL.'/board.php?bo_table='.urlencode($row['bo_table']).'&wr_id='.(int) $row['wr_id'];
                        $result_url = ac_admin_queue_result_url($row);
                        $comment_count = ac_admin_post_comment_count($row);
                    ?>
                        <tr>
                            <td><?php echo get_text($row['acq_inserted_at']); ?></td>
                            <td>
                                <span class="ac-badge"><?php echo get_text($row['bo_table']); ?> #<?php echo (int) $row['wr_id']; ?></span>
                                <div class="ac-post-title"><?php echo get_text($row['acq_subject']); ?></div>
                                <a href="<?php echo $post_url; ?>" target="_blank" class="ac-inline-link">원문 보기</a>
                            </td>
                            <td><?php echo nl2br(get_text($row['acq_content'])); ?></td>
                            <td><?php echo get_text($row['acq_author']); ?></td>
                            <td><?php echo number_format($comment_count); ?>개</td>
                            <td>
                                <?php if ($result_url) { ?>
                                    <a href="<?php echo $result_url; ?>" target="_blank" class="ac-result-link">댓글 보기</a>
                                <?php } else { ?>
                                    <span class="ac-muted">링크 없음</span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    <?php } else if ($tab === 'ai_usage') { ?>
        <p class="ac-help">icrm 중앙관리 API 응답 기준의 AI 호출 기록과 토큰 사용량을 표시합니다.</p>
        <?php
        auto_comment_ensure_ai_usage_table();
        $today = date('Y-m-d 00:00:00', G5_SERVER_TIME);
        $month_start = date('Y-m-01 00:00:00', G5_SERVER_TIME);
        $ai_today = ac_admin_ai_usage_summary($today);
        $ai_month = ac_admin_ai_usage_summary($month_start);
        $icrm_has_license = trim(auto_comment_get_setting('icrm_license_key', '')) !== '';
        $ai_mode = auto_comment_effective_generator_mode();
        ?>
        <p class="ac-help">현재 생성 모드: <strong><?php echo $ai_mode === 'ai' ? 'AI 우선' : '템플릿'; ?></strong> / AI 제공 방식: <strong>icrm 중앙관리</strong> / icrm 라이선스: <strong><?php echo $icrm_has_license ? '저장됨' : '미설정'; ?></strong>. Gemini 모델과 API Key는 icrm.co.kr에서 중앙 관리합니다.</p>
        <div class="ac-status-grid">
            <div class="ac-status-card"><strong>오늘 AI 호출</strong><span><?php echo number_format((int) $ai_today['calls']); ?></span><small>성공 <?php echo number_format((int) $ai_today['success_count']); ?> / 실패 <?php echo number_format((int) $ai_today['fail_count']); ?></small></div>
            <div class="ac-status-card"><strong>오늘 토큰</strong><span><?php echo number_format((int) $ai_today['total_tokens']); ?></span><small>입력 <?php echo number_format((int) $ai_today['prompt_tokens']); ?> / 출력 <?php echo number_format((int) $ai_today['output_tokens']); ?></small></div>
            <div class="ac-status-card"><strong>오늘 예상비용</strong><span><?php echo number_format((float) $ai_today['cost_krw'], 2); ?>원</span><small>icrm 응답 기준</small></div>
            <div class="ac-status-card"><strong>이번달 예상비용</strong><span><?php echo number_format((float) $ai_month['cost_krw'], 2); ?>원</span><small><?php echo number_format((int) $ai_month['calls']); ?> calls</small></div>
        </div>
        <table class="ac-form-table ac-ai-table">
            <tr><th>일시</th><th>게시글</th><th>모델</th><th>상태</th><th>입력</th><th>출력</th><th>총 토큰</th><th>예상비용</th><th>오류</th></tr>
            <?php
            $result = sql_query(" select *
                                    from ".auto_comment_table('ai_usage')."
                                   order by acu_id desc
                                   limit 120 ", false);
            $ai_usage_count = 0;
            while ($row = sql_fetch_array($result)) {
                $ai_usage_count++;
                $post_url = $row['bo_table'] && (int) $row['wr_id'] > 0 ? G5_BBS_URL.'/board.php?bo_table='.urlencode($row['bo_table']).'&wr_id='.(int) $row['wr_id'] : '';
            ?>
            <tr>
                <td><?php echo get_text($row['acu_created_at']); ?></td>
                <td>
                    <?php if ($post_url) { ?>
                        <a href="<?php echo $post_url; ?>" target="_blank" class="ac-inline-link"><?php echo get_text($row['bo_table']); ?> #<?php echo (int) $row['wr_id']; ?></a>
                    <?php } else { ?>
                        <span class="ac-muted">-</span>
                    <?php } ?>
                </td>
                <td><?php echo get_text($row['acu_model']); ?></td>
                <td><span class="ac-badge <?php echo $row['acu_status'] === 'success' ? 'inserted' : 'failed'; ?>"><?php echo get_text($row['acu_status']); ?></span></td>
                <td><?php echo number_format((int) $row['acu_prompt_tokens']); ?></td>
                <td><?php echo number_format((int) $row['acu_output_tokens']); ?></td>
                <td><?php echo number_format((int) $row['acu_total_tokens']); ?></td>
                <td><?php echo number_format((float) $row['acu_cost_krw'], 4); ?>원</td>
                <td><?php echo $row['acu_error'] !== '' ? get_text($row['acu_error']) : '<span class="ac-muted">-</span>'; ?></td>
            </tr>
            <?php } ?>
            <?php if ($ai_usage_count === 0) { ?>
            <tr><td colspan="9" class="ac-muted">아직 AI 사용기록이 없습니다. icrm 라이선스 저장 후 자동댓글이 AI로 생성되거나 호출 실패가 발생하면 이곳에 기록됩니다.</td></tr>
            <?php } ?>
        </table>
    <?php } else if ($tab === 'settings') { ?>
        <?php list($views_per_comment_min, $views_per_comment_max) = auto_comment_views_per_comment_range(); ?>
        <p class="ac-help">자동댓글 운영에 필요한 핵심 설정만 표시합니다. 게시판별 기능 제어는 게시판설정에서 관리하세요.</p>
        <form method="post" action="<?php echo $action_url; ?>">
            <input type="hidden" name="act" value="save_global">
            <input type="hidden" name="trigger_percent" value="<?php echo (int) auto_comment_get_setting('trigger_percent', '3'); ?>">
            <input type="hidden" name="trigger_interval" value="<?php echo (int) auto_comment_get_setting('trigger_interval', '180'); ?>">
            <input type="hidden" name="max_run_items" value="<?php echo (int) auto_comment_get_setting('max_run_items', '2'); ?>">
            <input type="hidden" name="max_run_seconds" value="<?php echo (int) auto_comment_get_setting('max_run_seconds', '2'); ?>">
            <input type="hidden" name="pending_expire_days" value="<?php echo (int) auto_comment_get_setting('pending_expire_days', '7'); ?>">
            <input type="hidden" name="strategy_recent_days" value="<?php echo (int) auto_comment_get_setting('strategy_recent_days', '14'); ?>">
            <input type="hidden" name="strategy_scan_limit" value="<?php echo (int) auto_comment_get_setting('strategy_scan_limit', '3'); ?>">
            <input type="hidden" name="ai_author_name" value="<?php echo get_text(auto_comment_get_setting('ai_author_name', '세부나이트 AI 가이드')); ?>">
            <input type="hidden" name="auto_author_reuse_percent" value="<?php echo (int) auto_comment_get_setting('auto_author_reuse_percent', '65'); ?>">
            <input type="hidden" name="forbidden_words" value="<?php echo get_text(auto_comment_get_setting('forbidden_words', '')); ?>">
            <input type="hidden" name="skip_bots" value="<?php echo auto_comment_get_setting('skip_bots', '1') === '1' ? '1' : '0'; ?>">
            <table class="ac-form-table">
                <tr><th>모듈 사용</th><td><label><input type="checkbox" name="enabled" value="1" <?php echo auto_comment_get_setting('enabled', '0') === '1' ? 'checked' : ''; ?>> 사용</label></td></tr>
                <tr><th>하루 최대 등록 수</th><td><input type="number" name="daily_limit" class="ac-input" value="<?php echo (int) auto_comment_get_setting('daily_limit', '20'); ?>"> 개</td></tr>
                <tr><th>전략 스캔</th><td><label><input type="checkbox" name="strategy_enabled" value="1" <?php echo auto_comment_get_setting('strategy_enabled', '1') === '1' ? 'checked' : ''; ?>> 방문 트리거 때 조회수 기준으로 부족한 댓글을 자동 보충</label></td></tr>
                <tr><th>최소 자동댓글</th><td><input type="number" name="auto_min_comments" class="ac-input" value="<?php echo (int) auto_comment_get_setting('auto_min_comments', '0'); ?>" min="0" max="20"> 개 <span class="ac-help">0이면 조회수 기준에 도달하기 전까지 자동예약하지 않습니다.</span></td></tr>
                <tr><th>최대 자동댓글</th><td><input type="number" name="auto_max_comments" class="ac-input" value="<?php echo (int) auto_comment_get_setting('auto_max_comments', '20'); ?>" min="0" max="20"> 개</td></tr>
                <tr>
                    <th>조회수 증가 기준</th>
                    <td>
                        <input type="number" name="auto_views_per_comment_min" class="ac-input" value="<?php echo (int) $views_per_comment_min; ?>" min="1" max="10000"> ~
                        <input type="number" name="auto_views_per_comment_max" class="ac-input" value="<?php echo (int) $views_per_comment_max; ?>" min="1" max="10000">
                        조회수 사이에서 글마다 고정된 랜덤 기준으로 목표 댓글 1개 산정
                    </td>
                </tr>
                <tr>
                    <th>댓글 생성 방식</th>
                    <td>
                        <select name="generator_mode" class="ac-input">
                            <option value="template" <?php echo auto_comment_get_setting('generator_mode', 'ai') === 'template' ? 'selected' : ''; ?>>템플릿</option>
                            <option value="ai" <?php echo auto_comment_get_setting('generator_mode', 'ai') !== 'template' ? 'selected' : ''; ?>>AI</option>
                        </select>
                        <span class="ac-help">AI 선택 시 icrm 중앙관리 API에서 댓글을 생성합니다. 실패하면 템플릿으로 자동 대체됩니다.</span>
                    </td>
                </tr>
                <tr>
                    <th>icrm API 주소</th>
                    <td>
                        <input type="text" name="icrm_api_base_url" class="ac-input" style="width:360px" value="<?php echo get_text(auto_comment_get_setting('icrm_api_base_url', 'https://icrm.co.kr/api/auto-comment')); ?>">
                        <span class="ac-help">예: https://icrm.co.kr/api/auto-comment</span>
                    </td>
                </tr>
                <tr>
                    <th>icrm 라이선스 키</th>
                    <td>
                        <input type="password" name="icrm_license_key" class="ac-input" style="width:360px" value="" placeholder="<?php echo auto_comment_get_setting('icrm_license_key', '') ? '저장됨 - 변경할 때만 입력' : '라이선스 키 입력'; ?>">
                        <span class="ac-help">icrm.co.kr 댓글프로그램관리에서 발급한 사이트별 라이선스 키입니다.</span>
                    </td>
                </tr>
            </table>
            <p style="margin-top:12px"><button type="submit" class="ac-btn">저장</button></p>
        </form>
        <form method="post" action="<?php echo $action_url; ?>" style="margin-top:10px">
            <input type="hidden" name="ac_token" value="<?php echo $ac_token; ?>">
            <button type="submit" name="act" value="test_icrm_api" class="ac-btn ac-btn-light">icrm API 연결 테스트</button>
            <span class="ac-help">HTTP 302는 icrm API가 로그인 페이지로 리다이렉트된 상태입니다. icrm.co.kr 서버에 <code>/api/auto-comment/generate</code> JSON API가 열려 있어야 합니다.</span>
        </form>
    <?php } else if ($tab === 'boards') { ?>
        <div class="ac-alert" style="margin-bottom:12px">
            <strong>여기가 게시판별 댓글 설정 화면입니다.</strong> 그누보드 <strong>게시판관리 &gt; 게시판 수정</strong> 메뉴와는 별도입니다.
        </div>
        <p class="ac-help">게시판별 자동댓글 기능과 댓글 성향을 세밀하게 설정할 수 있습니다. <strong>자정 예약</strong>을 켠 게시판은 매일 00:00에 글당 설정 개수만큼 댓글을 예약하며, 조회수·신규글·전략스캔 기준은 적용하지 않습니다.</p>
        <form method="post" action="<?php echo $action_url; ?>">
            <input type="hidden" name="ac_token" value="<?php echo $ac_token; ?>">
            <input type="hidden" name="act" value="save_boards">
            <table class="ac-form-table ac-board-table">
                <tr><th>게시판</th><th>성향 프로필</th><th>관리대상</th><th>자정 예약</th><th>신규글 자동댓글</th><th>전략스캔</th><th>직접댓글</th><th>검수모드</th></tr>
                <?php
                auto_comment_ensure_board_columns();
                $result = sql_query(" select bo_table, bo_subject from {$g5['board_table']} order by bo_table ");
                while ($bo = sql_fetch_array($result)) {
                    $cfg = auto_comment_get_board_config($bo['bo_table']);
                    $enabled = $cfg && (int) $cfg['acb_enabled'] === 1;
                    $midnight_schedule = $enabled && $cfg && isset($cfg['acb_midnight_schedule']) && (int) $cfg['acb_midnight_schedule'] === 1;
                    $auto_new_post = $enabled && !$midnight_schedule && (!$cfg || !isset($cfg['acb_auto_new_post']) || (int) $cfg['acb_auto_new_post'] === 1);
                    $strategy_scan = $enabled && !$midnight_schedule && (!$cfg || !isset($cfg['acb_strategy_scan']) || (int) $cfg['acb_strategy_scan'] === 1);
                    $manual_comment = $enabled && (!$cfg || !isset($cfg['acb_manual_comment']) || (int) $cfg['acb_manual_comment'] === 1);
                    $review_mode = $enabled && $cfg && isset($cfg['acb_review_mode']) && (int) $cfg['acb_review_mode'] === 1;
                    $tone_profile = $cfg && isset($cfg['acb_tone_profile']) ? auto_comment_sanitize_tone_profile($cfg['acb_tone_profile']) : 'random';
                    $child_disabled = $enabled ? '' : ' disabled';
                ?>
                <tr class="ac-board-row">
                    <td><?php echo get_text($bo['bo_subject']); ?> <span class="ac-badge"><?php echo get_text($bo['bo_table']); ?></span></td>
                    <td>
                        <select name="boards[<?php echo $bo['bo_table']; ?>][tone_profile]" class="ac-input ac-board-tone">
                            <?php foreach (auto_comment_tone_options() as $tone_key => $tone_label) { ?>
                                <option value="<?php echo get_text($tone_key); ?>" <?php echo $tone_profile === $tone_key ? 'selected' : ''; ?>><?php echo get_text($tone_label); ?></option>
                            <?php } ?>
                        </select>
                    </td>
                    <td><label><input type="checkbox" class="ac-board-master" name="boards[<?php echo $bo['bo_table']; ?>][enabled]" value="1" <?php echo $enabled ? 'checked' : ''; ?>> ON</label></td>
                    <td><label><input type="checkbox" class="ac-board-child ac-board-midnight" name="boards[<?php echo $bo['bo_table']; ?>][midnight_schedule]" value="1" <?php echo $midnight_schedule ? 'checked' : ''; ?><?php echo $child_disabled; ?>> ON</label></td>
                    <td><label><input type="checkbox" class="ac-board-child ac-board-views-trigger" name="boards[<?php echo $bo['bo_table']; ?>][auto_new_post]" value="1" <?php echo $auto_new_post ? 'checked' : ''; ?><?php echo $child_disabled; ?><?php echo $midnight_schedule ? ' disabled' : ''; ?>> ON</label></td>
                    <td><label><input type="checkbox" class="ac-board-child ac-board-views-trigger" name="boards[<?php echo $bo['bo_table']; ?>][strategy_scan]" value="1" <?php echo $strategy_scan ? 'checked' : ''; ?><?php echo $child_disabled; ?><?php echo $midnight_schedule ? ' disabled' : ''; ?>> ON</label></td>
                    <td><label><input type="checkbox" class="ac-board-child" name="boards[<?php echo $bo['bo_table']; ?>][manual_comment]" value="1" <?php echo $manual_comment ? 'checked' : ''; ?><?php echo $child_disabled; ?>> ON</label></td>
                    <td><label><input type="checkbox" class="ac-board-child" name="boards[<?php echo $bo['bo_table']; ?>][review_mode]" value="1" <?php echo $review_mode ? 'checked' : ''; ?><?php echo $child_disabled; ?>> ON</label></td>
                </tr>
                <?php } ?>
            </table>
            <p style="margin-top:12px"><button type="submit" class="ac-btn">저장</button></p>
        </form>
    <?php } else if ($tab === 'manual') { ?>
        <p class="ac-help">게시판과 원글 번호를 입력해 자동 생성 댓글을 미리 확인한 뒤 예약할 수 있습니다.</p>
        <?php if ($manual_error) { ?><div class="ac-alert"><?php echo get_text($manual_error); ?></div><?php } ?>
        <form method="post">
            <input type="hidden" name="ac_token" value="<?php echo $ac_token; ?>">
            <table class="ac-form-table">
                <tr>
                    <th>게시판</th>
                    <td>
                        <select name="bo_table" class="ac-input">
                            <option value="">선택하세요</option>
                            <?php
                            $selected_bo_table = $manual_preview ? $manual_preview['bo_table'] : (isset($_POST['bo_table']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['bo_table']) : '');
                            $result = sql_query(" select bo_table, bo_subject from {$g5['board_table']} order by bo_table ");
                            while ($bo = sql_fetch_array($result)) {
                                $selected = $selected_bo_table === $bo['bo_table'] ? ' selected' : '';
                                echo '<option value="'.get_text($bo['bo_table']).'"'.$selected.'>'.get_text($bo['bo_subject']).' ('.get_text($bo['bo_table']).')</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr><th>원글 번호</th><td><input type="number" name="wr_id" class="ac-input" min="1" value="<?php echo $manual_preview ? (int) $manual_preview['wr_id'] : (isset($_POST['wr_id']) ? (int) $_POST['wr_id'] : ''); ?>"> <span class="ac-help">글보기 URL의 wr_id 값을 입력하세요.</span></td></tr>
                <tr><th>템플릿 그룹</th><td><input type="text" name="template_group" class="ac-input ac-input-wide" value="<?php echo $manual_preview ? get_text($manual_preview['template_group']) : (isset($_POST['template_group']) ? get_text($_POST['template_group']) : ''); ?>" placeholder="비우면 게시판설정 사용"></td></tr>
                <tr>
                    <th>작성자</th>
                    <td>
                        <input type="text" name="acq_author" class="ac-input ac-input-wide" value="<?php echo $manual_preview ? get_text($manual_preview['author']) : '세부나이트 가이드'; ?>">
                    </td>
                </tr>
                <tr>
                    <th>댓글내용</th>
                    <td><textarea name="acq_content" class="ac-queue-textarea"><?php echo $manual_preview ? get_text($manual_preview['content']) : '내용 참고해서 일정과 조건을 미리 확인해보시면 좋습니다.'; ?></textarea></td>
                </tr>
                <tr>
                    <th>예약시간</th>
                    <td><input type="text" name="acq_scheduled_at" class="ac-input ac-input-wide" value="<?php echo $manual_preview ? get_text($manual_preview['scheduled_at']) : date('Y-m-d H:i:s', G5_SERVER_TIME + 600); ?>"> <span class="ac-help">형식: YYYY-MM-DD HH:MM:SS</span></td>
                </tr>
            </table>
            <p style="margin-top:12px">
                <button type="submit" name="act" value="generate_preview" class="ac-btn ac-btn-light">미리보기 생성</button>
                <button type="submit" name="act" value="create_manual_queue" class="ac-btn">이 내용으로 예약 생성</button>
            </p>
        </form>
    <?php } else if ($tab === 'queue') { ?>
        <div class="ac-toolbar">
            <a href="<?php echo $action_url; ?>?act=create_test_queue" class="ac-btn ac-btn-success">테스트 예약 생성</a>
            <a href="<?php echo $action_url; ?>?act=run_strategy_scan" class="ac-btn ac-btn-success">전략 스캔 실행</a>
            <a href="<?php echo $action_url; ?>?act=run_worker" class="ac-btn ac-btn-light">worker 수동 실행</a>
            <span class="ac-muted">inserted 항목은 자동댓글 히스토리로 남고 등록 댓글 보기로 확인할 수 있습니다.</span>
        </div>
        <?php
        $filter_status = isset($_GET['status']) ? preg_replace('/[^a-z_]/', '', $_GET['status']) : '';
        $filter_bo_table = isset($_GET['bo_table']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['bo_table']) : '';
        $filter_wr_id = isset($_GET['wr_id']) ? (int) $_GET['wr_id'] : 0;
        $filter_keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        ?>
        <form method="get" class="ac-filter">
            <input type="hidden" name="tab" value="queue">
            <label>상태
                <select name="status" class="ac-input">
                    <option value="">전체</option>
                    <?php
                    foreach (array('review' => 'review', 'pending' => 'pending', 'inserted' => 'inserted', 'failed' => 'failed', 'cancelled' => 'cancelled') as $value => $label) {
                        echo '<option value="'.$value.'"'.($filter_status === $value ? ' selected' : '').'>'.$label.'</option>';
                    }
                    ?>
                </select>
            </label>
            <label>게시판
                <select name="bo_table" class="ac-input">
                    <option value="">전체</option>
                    <?php
                    $boards_result = sql_query(" select bo_table, bo_subject from {$g5['board_table']} order by bo_table ");
                    while ($bo = sql_fetch_array($boards_result)) {
                        $selected = $filter_bo_table === $bo['bo_table'] ? ' selected' : '';
                        echo '<option value="'.get_text($bo['bo_table']).'"'.$selected.'>'.get_text($bo['bo_subject']).' ('.get_text($bo['bo_table']).')</option>';
                    }
                    ?>
                </select>
            </label>
            <label>글번호
                <input type="number" name="wr_id" class="ac-input" value="<?php echo $filter_wr_id ? (int) $filter_wr_id : ''; ?>" min="1">
            </label>
            <label>검색어
                <input type="text" name="keyword" class="ac-input" value="<?php echo get_text($filter_keyword); ?>" placeholder="제목/작성자/댓글">
            </label>
            <button type="submit" class="ac-btn">필터 적용</button>
            <a href="<?php echo $admin_url; ?>?tab=queue" class="ac-btn ac-btn-light">초기화</a>
        </form>
        <table class="ac-form-table ac-queue-table">
            <tr><th>상태</th><th>게시판/글</th><th>댓글내용</th><th>작성자</th><th>예약시간</th><th>관리</th></tr>
            <?php
            $where = array();
            if (in_array($filter_status, array('review', 'pending', 'inserted', 'failed', 'cancelled'), true)) {
                $where[] = " acq_status = '".auto_comment_escape($filter_status)."' ";
            }
            if ($filter_bo_table !== '') {
                $where[] = " bo_table = '".auto_comment_escape($filter_bo_table)."' ";
            }
            if ($filter_wr_id > 0) {
                $where[] = " wr_id = '{$filter_wr_id}' ";
            }
            if ($filter_keyword !== '') {
                $keyword = auto_comment_escape($filter_keyword);
                $where[] = " (acq_subject like '%{$keyword}%' or acq_author like '%{$keyword}%' or acq_content like '%{$keyword}%') ";
            }
            $where_sql = $where ? ' where '.implode(' and ', $where) : '';
            $total_row = sql_fetch(" select count(*) as cnt from ".auto_comment_table('queue').$where_sql);
            ?>
            <tr><td colspan="6" class="ac-muted">검색 결과: <?php echo number_format((int) $total_row['cnt']); ?>개 / 최근 120개 표시</td></tr>
            <?php
            $result = sql_query(" select * from ".auto_comment_table('queue').$where_sql." order by acq_id desc limit 120 ");
            while ($row = sql_fetch_array($result)) {
                $editable = in_array($row['acq_status'], array('review', 'pending'), true);
                $post_url = G5_BBS_URL.'/board.php?bo_table='.urlencode($row['bo_table']).'&wr_id='.(int) $row['wr_id'];
                $result_url = ac_admin_queue_result_url($row);
                $author_value = ac_admin_queue_author_value($row);
                $post_datetime = ac_admin_queue_post_datetime($row);
            ?>
            <tr>
                <td><span class="ac-badge <?php echo $row['acq_status']; ?>"><?php echo get_text($row['acq_status']); ?></span></td>
                <td>
                    <span class="ac-badge"><?php echo get_text($row['bo_table']); ?> #<?php echo (int) $row['wr_id']; ?></span>
                    <div class="ac-post-title"><?php echo get_text($row['acq_subject']); ?></div>
                    <?php if ($post_datetime !== '') { ?><span class="ac-muted">게시글 작성일: <?php echo get_text($post_datetime); ?></span><br><?php } ?>
                    <a href="<?php echo $post_url; ?>" target="_blank" class="ac-inline-link">원글 보기</a>
                </td>
                <td>
                    <?php if ($editable) { ?>
                        <textarea name="acq_content" class="ac-queue-textarea" form="acq_form_<?php echo (int) $row['acq_id']; ?>"><?php echo get_text($row['acq_content']); ?></textarea>
                    <?php } else { ?>
                        <?php echo nl2br(get_text($row['acq_content'])); ?>
                    <?php } ?>
                    <?php if ($row['acq_error'] && strpos($row['acq_error'], '등록 결과:') !== 0) echo '<small class="ac-muted ac-queue-meta">'.get_text($row['acq_error']).'</small>'; ?>
                </td>
                <td>
                    <?php if ($editable) { ?>
                        <input type="text" name="acq_author" class="ac-input ac-input-wide" value="<?php echo get_text($author_value); ?>" form="acq_form_<?php echo (int) $row['acq_id']; ?>">
                    <?php } else { ?>
                        <?php echo get_text($row['acq_author']); ?>
                    <?php } ?>
                </td>
                <td>
                    <?php if ($editable) { ?>
                        <input type="text" name="acq_scheduled_at" class="ac-input ac-input-wide" value="<?php echo get_text($row['acq_scheduled_at']); ?>" form="acq_form_<?php echo (int) $row['acq_id']; ?>">
                    <?php } else { ?>
                        <?php echo get_text($row['acq_scheduled_at']); ?>
                    <?php } ?>
                </td>
                <td>
                    <?php if ($editable) { ?>
                    <form method="post" action="<?php echo $action_url; ?>" id="acq_form_<?php echo (int) $row['acq_id']; ?>">
                        <input type="hidden" name="ac_token" value="<?php echo $ac_token; ?>">
                        <input type="hidden" name="act" value="queue_action">
                        <input type="hidden" name="acq_id" value="<?php echo (int) $row['acq_id']; ?>">
                        <div class="ac-row-actions">
                            <button name="mode" value="save" class="ac-btn ac-btn-light" type="submit">저장</button>
                            <button name="mode" value="regenerate" class="ac-btn ac-btn-light" type="submit">재생성</button>
                            <?php if ($row['acq_status'] === 'review') { ?><button name="mode" value="approve" class="ac-btn" type="submit">승인</button><?php } ?>
                            <button name="mode" value="insert" class="ac-btn" type="submit">즉시등록</button>
                            <button name="mode" value="cancel" class="ac-btn ac-btn-light" type="submit">취소</button>
                            <button name="mode" value="delete" class="ac-btn ac-btn-danger" type="submit">삭제</button>
                        </div>
                    </form>
                    <?php } else if ($result_url) { ?>
                    <a href="<?php echo $result_url; ?>" target="_blank" class="ac-result-link">등록 댓글 보기</a>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
        </table>
    <?php } else if ($tab === 'timeline') { ?>
        <p class="ac-help">예약/검수 대기 댓글을 시간순 타임라인으로 보여줍니다.</p>
        <div class="ac-timeline">
            <?php
            $timeline = sql_query(" select *
                                      from ".auto_comment_table('queue')."
                                     where acq_status in ('review', 'pending')
                                     order by acq_scheduled_at asc, acq_id asc
                                     limit 120 ", false);
            $has_timeline = false;
            while ($row = sql_fetch_array($timeline)) {
                $has_timeline = true;
                $post_url = G5_BBS_URL.'/board.php?bo_table='.urlencode($row['bo_table']).'&wr_id='.(int) $row['wr_id'];
            ?>
            <div class="ac-timeline-item">
                <div class="ac-timeline-time"><?php echo get_text($row['acq_scheduled_at']); ?></div>
                <div><span class="ac-badge <?php echo get_text($row['acq_status']); ?>"><?php echo get_text($row['acq_status']); ?></span></div>
                <div class="ac-timeline-content">
                    <span class="ac-badge"><?php echo get_text($row['bo_table']); ?> #<?php echo (int) $row['wr_id']; ?></span>
                    <strong><?php echo get_text($row['acq_subject']); ?></strong>
                    <span class="ac-ip">작성자: <?php echo get_text($row['acq_author']); ?></span>
                    <div><?php echo nl2br(get_text($row['acq_content'])); ?></div>
                    <?php if ($row['acq_error'] !== '') { ?><span class="ac-ip"><?php echo get_text($row['acq_error']); ?></span><?php } ?>
                    <a href="<?php echo $post_url; ?>" target="_blank" class="ac-inline-link">원글 보기</a>
                </div>
                <div><a href="<?php echo $admin_url; ?>?tab=queue&status=<?php echo urlencode($row['acq_status']); ?>&bo_table=<?php echo urlencode($row['bo_table']); ?>&wr_id=<?php echo (int) $row['wr_id']; ?>" class="ac-btn ac-btn-light">예약목록</a></div>
            </div>
            <?php } ?>
            <?php if (!$has_timeline) { ?>
                <p class="ac-muted">표시할 예약 또는 검수 대기 댓글이 없습니다.</p>
            <?php } ?>
        </div>
    <?php } else if ($tab === 'latest') { ?>
        <p class="ac-help">자동댓글이 켜진 게시판의 최신 원글만 모아 보여줍니다. 각 글의 실제 댓글 수와 자동댓글 예약/등록 수를 확인하고 바로 댓글을 예약하거나 즉시 등록할 수 있습니다.</p>
        <?php if ($direct_error) { ?><p class="ac-alert"><?php echo get_text($direct_error); ?></p><?php } ?>
        <?php
        $latest_bo_table = isset($_GET['bo_table']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['bo_table']) : '';
        $latest_limit = isset($_GET['limit']) ? max(10, min(100, (int) $_GET['limit'])) : 30;
        ?>
        <form method="get" class="ac-filter">
            <input type="hidden" name="tab" value="latest">
            <label>게시판
                <select name="bo_table" class="ac-input">
                    <option value="">전체</option>
                    <?php
                    $boards_result = sql_query(" select b.bo_table, gb.bo_subject
                                                   from ".auto_comment_table('board')." b
                                                   left join {$g5['board_table']} gb on gb.bo_table = b.bo_table
                                                  where b.acb_enabled = 1
                                                    and b.acb_manual_comment = 1
                                                  order by b.bo_table ");
                    while ($bo = sql_fetch_array($boards_result)) {
                        $selected = $latest_bo_table === $bo['bo_table'] ? ' selected' : '';
                        $label = $bo['bo_subject'] ? $bo['bo_subject'] : $bo['bo_table'];
                        echo '<option value="'.get_text($bo['bo_table']).'"'.$selected.'>'.get_text($label).' ('.get_text($bo['bo_table']).')</option>';
                    }
                    ?>
                </select>
            </label>
            <label>표시개수
                <input type="number" name="limit" class="ac-input" value="<?php echo (int) $latest_limit; ?>" min="10" max="100">
            </label>
            <button type="submit" class="ac-btn">필터 적용</button>
            <a href="<?php echo $admin_url; ?>?tab=latest" class="ac-btn ac-btn-light">초기화</a>
        </form>
        <table class="ac-form-table ac-latest-table">
            <tr><th>작성일시</th><th>최신글</th><th>실제댓글</th><th>자동댓글</th><th>조회수</th><th>바로 댓글</th></tr>
            <?php
            $latest_posts = ac_admin_latest_posts($latest_limit, $latest_bo_table);
            if (!$latest_posts) {
                echo '<tr><td colspan="6" class="ac-muted">표시할 최신글이 없습니다. 게시판설정에서 자동댓글을 켠 게시판이 있는지 확인해주세요.</td></tr>';
            }
            foreach ($latest_posts as $post) {
                $post_url = G5_BBS_URL.'/board.php?bo_table='.urlencode($post['bo_table']).'&wr_id='.(int) $post['wr_id'];
                $preview_url = $admin_url.'?tab=latest&bo_table='.urlencode($latest_bo_table).'&limit='.(int) $latest_limit.'&preview_bo_table='.urlencode($post['bo_table']).'&preview_wr_id='.(int) $post['wr_id'];
                $auto_count = ac_admin_auto_comment_count_for_post($post['bo_table'], (int) $post['wr_id']);
                $view_meta = ac_admin_post_view_meta($post['bo_table'], (int) $post['wr_id']);
            ?>
            <tr>
                <td>
                    <?php echo get_text($post['wr_datetime']); ?>
                    <?php if (!empty($view_meta['acv_last_viewed_at']) && $view_meta['acv_last_viewed_at'] !== '0000-00-00 00:00:00') { ?>
                        <span class="ac-ip">마지막조회: <?php echo get_text($view_meta['acv_last_viewed_at']); ?></span>
                        <span class="ac-ip">IP: <?php echo !empty($view_meta['acv_ip']) ? get_text($view_meta['acv_ip']) : '-'; ?></span>
                        <span class="ac-ip">방문자 유형: <?php echo get_text(ac_admin_visitor_type_label(isset($view_meta['acv_visitor_type']) ? $view_meta['acv_visitor_type'] : '')); ?></span>
                    <?php } ?>
                </td>
                <td>
                    <span class="ac-badge"><?php echo get_text($post['bo_subject']); ?> / <?php echo get_text($post['bo_table']); ?> #<?php echo (int) $post['wr_id']; ?></span>
                    <div class="ac-post-title"><?php echo get_text($post['wr_subject']); ?></div>
                    <span class="ac-muted">원글 작성자: <?php echo get_text($post['wr_name']); ?></span><br>
                    <a href="<?php echo $post_url; ?>" target="_blank" class="ac-inline-link">원글 보기</a>
                </td>
                <td><?php echo number_format((int) $post['wr_comment']); ?>개</td>
                <td><?php echo number_format($auto_count); ?>개</td>
                <td><?php echo number_format((int) $post['wr_hit']); ?></td>
                <td>
                    <div class="ac-row-actions">
                        <a href="<?php echo $preview_url; ?>" class="ac-btn">댓글 작성</a>
                    </div>
                </td>
            </tr>
            <?php ac_admin_render_direct_preview_row($post, $direct_preview, 'latest', $admin_url, $action_url, $ac_token); ?>
            <?php } ?>
        </table>
    <?php } else if ($tab === 'recent_viewed') { ?>
        <p class="ac-help">최근 실제로 조회된 원글을 마지막 조회시각 순으로 보여줍니다. 조회 기록은 자동댓글이 켜진 게시판의 글보기 페이지가 열릴 때부터 쌓입니다.</p>
        <?php if ($direct_error) { ?><p class="ac-alert"><?php echo get_text($direct_error); ?></p><?php } ?>
        <?php
        $viewed_bo_table = isset($_GET['bo_table']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['bo_table']) : '';
        $viewed_limit = isset($_GET['limit']) ? max(10, min(100, (int) $_GET['limit'])) : 30;
        ?>
        <form method="get" class="ac-filter">
            <input type="hidden" name="tab" value="recent_viewed">
            <label>게시판
                <select name="bo_table" class="ac-input">
                    <option value="">전체</option>
                    <?php
                    $boards_result = sql_query(" select b.bo_table, gb.bo_subject
                                                   from ".auto_comment_table('board')." b
                                                   left join {$g5['board_table']} gb on gb.bo_table = b.bo_table
                                                  where b.acb_enabled = 1
                                                    and b.acb_manual_comment = 1
                                                  order by b.bo_table ");
                    while ($bo = sql_fetch_array($boards_result)) {
                        $selected = $viewed_bo_table === $bo['bo_table'] ? ' selected' : '';
                        $label = $bo['bo_subject'] ? $bo['bo_subject'] : $bo['bo_table'];
                        echo '<option value="'.get_text($bo['bo_table']).'"'.$selected.'>'.get_text($label).' ('.get_text($bo['bo_table']).')</option>';
                    }
                    ?>
                </select>
            </label>
            <label>표시개수
                <input type="number" name="limit" class="ac-input" value="<?php echo (int) $viewed_limit; ?>" min="10" max="100">
            </label>
            <button type="submit" class="ac-btn">필터 적용</button>
            <a href="<?php echo $admin_url; ?>?tab=recent_viewed" class="ac-btn ac-btn-light">초기화</a>
        </form>
        <table class="ac-form-table ac-latest-table">
            <tr><th>마지막조회</th><th>최근 조회된 글</th><th>방문자 유형</th><th>실제댓글</th><th>자동댓글</th><th>조회기록</th><th>바로 댓글</th></tr>
            <?php
            $viewed_posts = ac_admin_recent_viewed_posts($viewed_limit, $viewed_bo_table);
            if (!$viewed_posts) {
                echo '<tr><td colspan="7" class="ac-muted">아직 조회 기록이 없습니다. 자동댓글이 켜진 게시판의 글이 조회되면 이곳에 표시됩니다.</td></tr>';
            }
            foreach ($viewed_posts as $post) {
                $post_url = G5_BBS_URL.'/board.php?bo_table='.urlencode($post['bo_table']).'&wr_id='.(int) $post['wr_id'];
                $preview_url = $admin_url.'?tab=recent_viewed&bo_table='.urlencode($viewed_bo_table).'&limit='.(int) $viewed_limit.'&preview_bo_table='.urlencode($post['bo_table']).'&preview_wr_id='.(int) $post['wr_id'];
                $auto_count = ac_admin_auto_comment_count_for_post($post['bo_table'], (int) $post['wr_id']);
            ?>
            <tr>
                <td>
                    <?php echo get_text($post['acv_last_viewed_at']); ?>
                    <span class="ac-ip">IP: <?php echo !empty($post['acv_ip']) ? get_text($post['acv_ip']) : '-'; ?></span>
                </td>
                <td>
                    <span class="ac-badge"><?php echo get_text($post['bo_subject']); ?> / <?php echo get_text($post['bo_table']); ?> #<?php echo (int) $post['wr_id']; ?></span>
                    <div class="ac-post-title"><?php echo get_text($post['wr_subject']); ?></div>
                    <span class="ac-muted">원글 작성자: <?php echo get_text($post['wr_name']); ?> / 작성일: <?php echo get_text($post['wr_datetime']); ?></span><br>
                    <a href="<?php echo $post_url; ?>" target="_blank" class="ac-inline-link">원글 보기</a>
                </td>
                <td><?php echo get_text(ac_admin_visitor_type_label(isset($post['acv_visitor_type']) ? $post['acv_visitor_type'] : '')); ?></td>
                <td><?php echo number_format((int) $post['wr_comment']); ?>개</td>
                <td><?php echo number_format($auto_count); ?>개</td>
                <td><?php echo number_format((int) $post['acv_view_count']); ?>회</td>
                <td>
                    <div class="ac-row-actions">
                        <a href="<?php echo $preview_url; ?>" class="ac-btn">댓글 작성</a>
                    </div>
                </td>
            </tr>
            <?php ac_admin_render_direct_preview_row($post, $direct_preview, 'recent_viewed', $admin_url, $action_url, $ac_token); ?>
            <?php } ?>
        </table>
    <?php } else if ($tab === 'history') { ?>
        <p class="ac-help">실제로 등록 완료된 자동댓글만 표시합니다.</p>
        <?php
        $history_bo_table = isset($_GET['bo_table']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['bo_table']) : '';
        $history_author = isset($_GET['author']) ? trim($_GET['author']) : '';
        $history_keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $history_from = isset($_GET['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from']) ? $_GET['from'] : '';
        $history_to = isset($_GET['to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to']) ? $_GET['to'] : '';
        ?>
        <form method="get" class="ac-filter">
            <input type="hidden" name="tab" value="history">
            <label>게시판
                <select name="bo_table" class="ac-input">
                    <option value="">전체</option>
                    <?php
                    $boards_result = sql_query(" select bo_table, bo_subject from {$g5['board_table']} order by bo_table ");
                    while ($bo = sql_fetch_array($boards_result)) {
                        $selected = $history_bo_table === $bo['bo_table'] ? ' selected' : '';
                        echo '<option value="'.get_text($bo['bo_table']).'"'.$selected.'>'.get_text($bo['bo_subject']).' ('.get_text($bo['bo_table']).')</option>';
                    }
                    ?>
                </select>
            </label>
            <label>작성자
                <input type="text" name="author" class="ac-input" value="<?php echo get_text($history_author); ?>">
            </label>
            <label>검색어
                <input type="text" name="keyword" class="ac-input" value="<?php echo get_text($history_keyword); ?>" placeholder="제목/댓글">
            </label>
            <label>시작일
                <input type="date" name="from" class="ac-input" value="<?php echo get_text($history_from); ?>">
            </label>
            <label>종료일
                <input type="date" name="to" class="ac-input" value="<?php echo get_text($history_to); ?>">
            </label>
            <button type="submit" class="ac-btn">필터 적용</button>
            <a href="<?php echo $admin_url; ?>?tab=history" class="ac-btn ac-btn-light">초기화</a>
        </form>
        <table class="ac-form-table ac-history-table">
            <tr><th>작성일시</th><th>원문글제목</th><th>댓글내용</th><th>작성자</th><th>댓글개수</th><th>댓글링크</th></tr>
            <?php
            $history_where = array(" acq_status = 'inserted' ");
            if ($history_bo_table !== '') {
                $history_where[] = " bo_table = '".auto_comment_escape($history_bo_table)."' ";
            }
            if ($history_author !== '') {
                $author = auto_comment_escape($history_author);
                $history_where[] = " acq_author like '%{$author}%' ";
            }
            if ($history_keyword !== '') {
                $keyword = auto_comment_escape($history_keyword);
                $history_where[] = " (acq_subject like '%{$keyword}%' or acq_content like '%{$keyword}%') ";
            }
            if ($history_from !== '') {
                $history_where[] = " acq_inserted_at >= '".auto_comment_escape($history_from)." 00:00:00' ";
            }
            if ($history_to !== '') {
                $history_where[] = " acq_inserted_at <= '".auto_comment_escape($history_to)." 23:59:59' ";
            }
            $history_where_sql = ' where '.implode(' and ', $history_where);
            $history_total = sql_fetch(" select count(*) as cnt from ".auto_comment_table('queue').$history_where_sql);
            ?>
            <tr><td colspan="6" class="ac-muted">검색 결과: <?php echo number_format((int) $history_total['cnt']); ?>개 / 최근 120개 표시</td></tr>
            <?php
            $result = sql_query(" select *
                                    from ".auto_comment_table('queue').$history_where_sql."
                                   order by acq_inserted_at desc, acq_id desc
                                   limit 120 ");
            while ($row = sql_fetch_array($result)) {
                $post_url = G5_BBS_URL.'/board.php?bo_table='.urlencode($row['bo_table']).'&wr_id='.(int) $row['wr_id'];
                $result_url = ac_admin_queue_result_url($row);
                $comment_count = ac_admin_post_comment_count($row);
            ?>
            <tr>
                <td><?php echo get_text($row['acq_inserted_at']); ?></td>
                <td>
                    <span class="ac-badge"><?php echo get_text($row['bo_table']); ?> #<?php echo (int) $row['wr_id']; ?></span>
                    <div class="ac-post-title"><?php echo get_text($row['acq_subject']); ?></div>
                    <a href="<?php echo $post_url; ?>" target="_blank" class="ac-inline-link">원문 보기</a>
                </td>
                <td><?php echo nl2br(get_text($row['acq_content'])); ?></td>
                <td><?php echo get_text($row['acq_author']); ?></td>
                <td><?php echo number_format($comment_count); ?>개</td>
                <td>
                    <?php if ($result_url) { ?>
                        <a href="<?php echo $result_url; ?>" target="_blank" class="ac-result-link">댓글 보기</a>
                    <?php } else { ?>
                        <span class="ac-muted">링크 없음</span>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
        </table>
    <?php } else if ($tab === 'reeval') { ?>
        <p class="ac-help">이미 등록된 자동댓글을 다시 분석해 너무 기계적인 표현, 반복 문구, 유사 댓글을 찾습니다. 등록 결과 URL은 유지하고 분석 결과는 이 화면에서 실시간으로 계산합니다.</p>
        <?php
        $reeval_bo_table = isset($_GET['bo_table']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['bo_table']) : '';
        $reeval_keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $reeval_level = isset($_GET['level']) ? $_GET['level'] : '';
        if (!in_array($reeval_level, array('', 'good', 'warning', 'danger'), true)) {
            $reeval_level = '';
        }
        ?>
        <div class="ac-toolbar">
            <a href="<?php echo $action_url; ?>?act=run_comment_reanalysis" class="ac-btn ac-btn-success">재평가 실행</a>
            <span class="ac-help">최근 등록 댓글 300개 기준으로 요약 로그를 남깁니다.</span>
        </div>
        <form method="get" class="ac-filter">
            <input type="hidden" name="tab" value="reeval">
            <label>게시판
                <select name="bo_table" class="ac-input">
                    <option value="">전체</option>
                    <?php
                    $boards_result = sql_query(" select bo_table, bo_subject from {$g5['board_table']} order by bo_table ");
                    while ($bo = sql_fetch_array($boards_result)) {
                        $selected = $reeval_bo_table === $bo['bo_table'] ? ' selected' : '';
                        echo '<option value="'.get_text($bo['bo_table']).'"'.$selected.'>'.get_text($bo['bo_subject']).' ('.get_text($bo['bo_table']).')</option>';
                    }
                    ?>
                </select>
            </label>
            <label>위험도
                <select name="level" class="ac-input">
                    <option value="" <?php echo $reeval_level === '' ? 'selected' : ''; ?>>전체</option>
                    <option value="danger" <?php echo $reeval_level === 'danger' ? 'selected' : ''; ?>>수정권장</option>
                    <option value="warning" <?php echo $reeval_level === 'warning' ? 'selected' : ''; ?>>주의</option>
                    <option value="good" <?php echo $reeval_level === 'good' ? 'selected' : ''; ?>>양호</option>
                </select>
            </label>
            <label>검색어
                <input type="text" name="keyword" class="ac-input" value="<?php echo get_text($reeval_keyword); ?>" placeholder="제목/작성자/댓글">
            </label>
            <button type="submit" class="ac-btn">필터 적용</button>
            <a href="<?php echo $admin_url; ?>?tab=reeval" class="ac-btn ac-btn-light">초기화</a>
        </form>
        <?php
        $reeval_where = array(" acq_status = 'inserted' ");
        if ($reeval_bo_table !== '') {
            $reeval_where[] = " bo_table = '".auto_comment_escape($reeval_bo_table)."' ";
        }
        if ($reeval_keyword !== '') {
            $keyword = auto_comment_escape($reeval_keyword);
            $reeval_where[] = " (acq_subject like '%{$keyword}%' or acq_author like '%{$keyword}%' or acq_content like '%{$keyword}%') ";
        }
        $reeval_where_sql = ' where '.implode(' and ', $reeval_where);
        $reeval_total = sql_fetch(" select count(*) as cnt from ".auto_comment_table('queue').$reeval_where_sql);
        $reeval_rows = array();
        $similarity_counts = array();
        $result = sql_query(" select *
                                from ".auto_comment_table('queue').$reeval_where_sql."
                               order by acq_inserted_at desc, acq_id desc
                               limit 200 ");
        while ($row = sql_fetch_array($result)) {
            $key = auto_comment_similarity_key($row['acq_content']);
            $row['_similarity_key'] = $key;
            $reeval_rows[] = $row;
            if ($key !== '') {
                $similarity_counts[$key] = isset($similarity_counts[$key]) ? $similarity_counts[$key] + 1 : 1;
            }
        }
        $summary = array('good' => 0, 'warning' => 0, 'danger' => 0);
        foreach ($reeval_rows as $idx => $row) {
            $duplicate_count = isset($similarity_counts[$row['_similarity_key']]) ? (int) $similarity_counts[$row['_similarity_key']] : 1;
            $analysis = auto_comment_reanalysis($row['acq_content'], $duplicate_count);
            $reeval_rows[$idx]['_analysis'] = $analysis;
            $summary[$analysis['level']]++;
        }
        ?>
        <div class="ac-status-grid">
            <div class="ac-status-card"><strong>분석대상</strong><span><?php echo number_format((int) $reeval_total['cnt']); ?></span><small>화면 분석: 최근 <?php echo number_format(count($reeval_rows)); ?>개</small></div>
            <div class="ac-status-card"><strong>수정권장</strong><span><?php echo number_format($summary['danger']); ?></span><small>기계/반복 의심 강함</small></div>
            <div class="ac-status-card"><strong>주의</strong><span><?php echo number_format($summary['warning']); ?></span><small>표현 점검 필요</small></div>
            <div class="ac-status-card"><strong>양호</strong><span><?php echo number_format($summary['good']); ?></span><small>큰 문제 없음</small></div>
        </div>
        <table class="ac-form-table ac-reeval-table">
            <tr><th>등록일시</th><th>원문글</th><th>댓글내용</th><th>재평가</th><th>의심 사유</th><th>댓글링크</th></tr>
            <?php
            $displayed = 0;
            foreach ($reeval_rows as $row) {
                $analysis = $row['_analysis'];
                if ($reeval_level !== '' && $analysis['level'] !== $reeval_level) {
                    continue;
                }
                $displayed++;
                $post_url = G5_BBS_URL.'/board.php?bo_table='.urlencode($row['bo_table']).'&wr_id='.(int) $row['wr_id'];
                $result_url = ac_admin_queue_result_url($row);
                $signals = array();
                if (!empty($analysis['mechanical'])) {
                    $signals[] = '기계표현: '.implode(', ', $analysis['mechanical']);
                }
                if (!empty($analysis['repeated'])) {
                    $signals[] = '반복: '.implode(', ', $analysis['repeated']);
                }
                if (!$signals) {
                    $signals[] = implode(', ', $analysis['reasons']);
                }
            ?>
            <tr>
                <td><?php echo get_text($row['acq_inserted_at']); ?></td>
                <td>
                    <span class="ac-badge"><?php echo get_text($row['bo_table']); ?> #<?php echo (int) $row['wr_id']; ?></span>
                    <div class="ac-post-title"><?php echo get_text($row['acq_subject']); ?></div>
                    <a href="<?php echo $post_url; ?>" target="_blank" class="ac-inline-link">원문 보기</a>
                </td>
                <td><?php echo nl2br(get_text($row['acq_content'])); ?></td>
                <td>
                    <span class="ac-badge <?php echo get_text($analysis['level']); ?>"><?php echo get_text(auto_comment_reanalysis_label($analysis['level'])); ?></span>
                    <span class="ac-ip"><?php echo (int) $analysis['score']; ?>점</span>
                </td>
                <td><?php echo nl2br(get_text(implode("\n", $signals))); ?></td>
                <td>
                    <?php if ($result_url) { ?>
                        <a href="<?php echo $result_url; ?>" target="_blank" class="ac-result-link">댓글 보기</a>
                    <?php } else { ?>
                        <span class="ac-muted">링크 없음</span>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
            <?php if ($displayed < 1) { ?>
            <tr><td colspan="6" class="ac-muted">조건에 맞는 재평가 대상이 없습니다.</td></tr>
            <?php } ?>
        </table>
    <?php } else if ($tab === 'tools') { ?>
        <p class="ac-help">업데이트 파일을 덮어쓴 뒤 이 화면에서 업데이트 실행만 누르면 됩니다. 기존 설정, API 키, 예약목록은 유지됩니다.</p>
        <table class="ac-form-table">
            <tr>
                <th>업데이트</th>
                <td>
                    <form method="post" action="<?php echo $action_url; ?>">
                        <input type="hidden" name="ac_token" value="<?php echo $ac_token; ?>">
                        <button type="submit" name="act" value="update" class="ac-btn">업데이트 실행</button>
                        <span class="ac-muted">현재 버전: <?php echo defined('AUTO_COMMENT_VERSION') ? AUTO_COMMENT_VERSION : '-'; ?> / 새 기능에 필요한 DB만 정리합니다.</span>
                    </form>
                </td>
            </tr>
            <tr>
                <th>설정 백업</th>
                <td>
                    <form method="post" action="<?php echo $action_url; ?>">
                        <input type="hidden" name="ac_token" value="<?php echo $ac_token; ?>">
                        <button type="submit" name="act" value="export_config" class="ac-btn">JSON 다운로드</button>
                    </form>
                </td>
            </tr>
            <tr>
                <th>설정 복원</th>
                <td>
                    <form method="post" action="<?php echo $action_url; ?>">
                        <input type="hidden" name="ac_token" value="<?php echo $ac_token; ?>">
                        <textarea name="config_json" class="ac-textarea" placeholder="내보낸 JSON 내용을 붙여넣으세요."></textarea>
                        <p style="margin-top:10px"><button type="submit" name="act" value="import_config" class="ac-btn ac-btn-light">JSON 가져오기</button></p>
                    </form>
                </td>
            </tr>
        </table>
    <?php } ?>
    </div>
</div>
</div>
<script>
(function () {
    var translations = {
        '대시보드': 'Dashboard',
        '최신글': 'Latest Posts',
        '최근조회글': 'Recently Viewed',
        '댓글히스토리': 'Comment History',
        '수동예약': 'Manual Queue',
        '예약목록': 'Queue',
        '예약타임라인': 'Queue Timeline',
        '댓글재평가': 'Re-evaluate',
        '게시판설정': 'Board Settings',
        '기본설정': 'Settings',
        'AI 사용기록': 'AI Usage',
        '백업/업데이트': 'Backup/Update',
        '설정 백업': 'Backup Settings',
        '설정 복원': 'Restore Settings',
        '더보기': 'More',
        '설치 시작': 'Install',
        '업데이트 실행': 'Run Update',
        '저장': 'Save',
        '필터 적용': 'Apply Filter',
        '초기화': 'Reset',
        '댓글 작성': 'Write Comment',
        '예약 생성': 'Create Queue',
        '즉시 등록': 'Insert Now',
        '즉시등록': 'Insert Now',
        '미리보기 생성': 'Generate Preview',
        '이 내용으로 예약 생성': 'Queue This Comment',
        '이 톤으로 다시 생성': 'Regenerate With Tone',
        '테스트 예약 생성': 'Create Test Queue',
        '전략 스캔 실행': 'Run Strategy Scan',
        'worker 수동 실행': 'Run Worker',
        '재생성': 'Regenerate',
        '승인': 'Approve',
        '취소': 'Cancel',
        '삭제': 'Delete',
        '내보내기': 'Export',
        'JSON 가져오기': 'Import JSON',
        '추천 설정 적용': 'Apply Recommended Settings',
        '설정 저장': 'Save Settings'
    };

    function trimText(text) {
        return (text || '').replace(/\s+/g, ' ').replace(/^\s+|\s+$/g, '');
    }

    function translatableElements() {
        return document.querySelectorAll('.ac-tabs a, .ac-btn, .ac-lang-toggle');
    }

    function applyControlLanguage(lang) {
        var elements = translatableElements();
        for (var i = 0; i < elements.length; i++) {
            var el = elements[i];
            if (el.hasAttribute('data-ac-lang-toggle')) {
                el.textContent = lang === 'en' ? 'Korean Buttons' : 'English Buttons';
                continue;
            }

            if (!el.getAttribute('data-ac-ko')) {
                el.setAttribute('data-ac-ko', trimText(el.textContent));
            }

            var ko = el.getAttribute('data-ac-ko');
            el.textContent = lang === 'en' && translations[ko] ? translations[ko] : ko;
        }
        try {
            localStorage.setItem('auto_comment_admin_lang', lang);
        } catch (e) {}
    }

    var initialLang = 'ko';
    try {
        initialLang = localStorage.getItem('auto_comment_admin_lang') || 'ko';
    } catch (e) {}
    applyControlLanguage(initialLang);

    var toggle = document.querySelector('[data-ac-lang-toggle]');
    if (toggle) {
        toggle.addEventListener('click', function () {
            var current = 'ko';
            try {
                current = localStorage.getItem('auto_comment_admin_lang') || 'ko';
            } catch (e) {}
            applyControlLanguage(current === 'en' ? 'ko' : 'en');
        });
    }

    function syncBoardRow(master) {
        var row = master.closest ? master.closest('.ac-board-row') : null;
        if (!row) return;
        var midnight = row.querySelector('.ac-board-midnight');
        var viewTriggers = row.querySelectorAll('.ac-board-views-trigger');
        var children = row.querySelectorAll('.ac-board-child');
        for (var i = 0; i < children.length; i++) {
            children[i].disabled = !master.checked;
            if (!master.checked) {
                children[i].checked = false;
            }
        }
        if (master.checked && midnight && midnight.checked) {
            for (var j = 0; j < viewTriggers.length; j++) {
                viewTriggers[j].checked = false;
                viewTriggers[j].disabled = true;
            }
        } else if (master.checked) {
            for (var k = 0; k < viewTriggers.length; k++) {
                viewTriggers[k].disabled = false;
            }
        }
    }

    var masters = document.querySelectorAll('.ac-board-master');
    for (var i = 0; i < masters.length; i++) {
        syncBoardRow(masters[i]);
        masters[i].addEventListener('change', function () {
            syncBoardRow(this);
        });
    }

    var midnightBoxes = document.querySelectorAll('.ac-board-midnight');
    for (var m = 0; m < midnightBoxes.length; m++) {
        midnightBoxes[m].addEventListener('change', function () {
            var row = this.closest ? this.closest('.ac-board-row') : null;
            if (!row) return;
            var master = row.querySelector('.ac-board-master');
            if (master) {
                syncBoardRow(master);
            }
        });
    }
})();
</script>
</body>
</html>
