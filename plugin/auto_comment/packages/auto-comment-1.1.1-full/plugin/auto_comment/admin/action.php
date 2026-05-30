<?php
define('G5_IS_ADMIN', true);
require_once __DIR__.'/../../../common.php';

define('AUTO_COMMENT_ACTION_VERSION', '20260429-1330');

if ($is_admin != 'super') {
    ac_action_redirect('최고관리자만 접근 가능합니다. (자동댓글 실행 '.AUTO_COMMENT_ACTION_VERSION.')', G5_URL);
}

include_once G5_PLUGIN_PATH.'/auto_comment/auto_comment.lib.php';

function ac_action_redirect($msg, $url)
{
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }

    echo '<script>alert('.json_encode($msg).');location.replace('.json_encode($url).');</script>';
    echo '<noscript><p>'.htmlspecialchars($msg, ENT_QUOTES, 'UTF-8').'</p><p><a href="'.htmlspecialchars($url, ENT_QUOTES, 'UTF-8').'">이동</a></p></noscript>';
    exit;
}

function ac_admin_url($tab)
{
    return G5_PLUGIN_URL.'/auto_comment/admin/index.php?tab='.$tab;
}

function ac_action_create_test_queue()
{
    global $g5;

    $board_table = auto_comment_table('board');
    $result = sql_query(" select bo_table, acb_template_group, acb_review_mode
                            from {$board_table}
                           where acb_enabled = 1
                           order by bo_table asc ", false);

    while ($cfg = sql_fetch_array($result)) {
        $bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', $cfg['bo_table']);
        if (!$bo_table || empty($g5['write_prefix'])) {
            continue;
        }

        $write_table = $g5['write_prefix'].$bo_table;
        if (!auto_comment_table_exists($write_table)) {
            continue;
        }

        $posts = sql_query(" select wr_id
                               from {$write_table}
                              where wr_is_comment = 0
                              order by wr_id desc
                              limit 10 ", false);
        while ($post = sql_fetch_array($posts)) {
            try {
                $preview = auto_comment_generate_preview($bo_table, (int) $post['wr_id'], $cfg['acb_template_group']);
                $content = auto_comment_validate_content($bo_table, (int) $post['wr_id'], $preview['content'], 0);
            } catch (Exception $e) {
                continue;
            }

            $status = isset($cfg['acb_review_mode']) && (int) $cfg['acb_review_mode'] === 1 ? 'review' : 'pending';
            $meta = $status === 'review' ? '테스트 예약: 게시판 검수모드 적용' : '';

            sql_query(" insert into ".auto_comment_table('queue')."
                            set bo_table = '".auto_comment_escape($bo_table)."',
                                wr_id = '".(int) $post['wr_id']."',
                                acq_subject = '".auto_comment_escape($preview['subject'])."',
                                acq_author = '".auto_comment_escape($preview['author'])."',
                                acq_content = '".auto_comment_escape($content)."',
                                acq_scheduled_at = '".G5_TIME_YMDHIS."',
                                acq_status = '{$status}',
                                acq_error = '".auto_comment_escape($meta)."',
                                acq_created_at = '".G5_TIME_YMDHIS."' ", false);

            $queue_id = sql_insert_id();
            auto_comment_log('test_queue', $bo_table.' #'.$post['wr_id'].' 테스트 예약 생성', $queue_id);
            return $queue_id;
        }
    }

    throw new Exception('테스트 예약을 만들 게시글을 찾지 못했습니다. 게시판설정 ON 여부와 원글 존재 여부를 확인해주세요.');
}

function ac_action_run_comment_reanalysis()
{
    $rows = array();
    $keys = array();
    $result = sql_query(" select acq_id, acq_content
                            from ".auto_comment_table('queue')."
                           where acq_status = 'inserted'
                           order by acq_inserted_at desc, acq_id desc
                           limit 300 ", false);
    while ($row = sql_fetch_array($result)) {
        $key = auto_comment_similarity_key($row['acq_content']);
        $row['_similarity_key'] = $key;
        $rows[] = $row;
        if ($key !== '') {
            $keys[$key] = isset($keys[$key]) ? $keys[$key] + 1 : 1;
        }
    }

    $warning = 0;
    $danger = 0;
    foreach ($rows as $row) {
        $duplicate_count = isset($keys[$row['_similarity_key']]) ? (int) $keys[$row['_similarity_key']] : 1;
        $analysis = auto_comment_reanalysis($row['acq_content'], $duplicate_count);
        if ($analysis['level'] === 'danger') {
            $danger++;
        } else if ($analysis['level'] === 'warning') {
            $warning++;
        }
    }

    $total = count($rows);
    auto_comment_log('comment_reanalysis', '등록 댓글 재평가: 대상 '.$total.'개, 주의 '.$warning.'개, 수정권장 '.$danger.'개', 0);

    return array('total' => $total, 'warning' => $warning, 'danger' => $danger);
}

function ac_action_queue_latest_post($bo_table, $wr_id, $insert_now, $author, $content, $tone)
{
    global $g5;

    $bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', $bo_table);
    $wr_id = (int) $wr_id;
    if (!$bo_table || $wr_id < 1) {
        throw new Exception('게시판 또는 글번호가 올바르지 않습니다.');
    }

    $cfg = auto_comment_get_board_config($bo_table);
    if (!$cfg || (int) $cfg['acb_enabled'] !== 1 || (isset($cfg['acb_manual_comment']) && (int) $cfg['acb_manual_comment'] !== 1)) {
        throw new Exception('이 게시판은 관리자 직접댓글이 비활성화되어 있습니다.');
    }
    $group = ($cfg && $cfg['acb_template_group']) ? $cfg['acb_template_group'] : 'default';
    $author = trim(strip_tags($author));
    $content = trim($content);
    if ($author === '' || $content === '') {
        $preview = auto_comment_generate_preview($bo_table, $wr_id, $group, $tone);
        $author = $author !== '' ? $author : auto_comment_random_korean_nickname();
        $content = $content !== '' ? $content : $preview['content'];
        $subject = $preview['subject'];
    } else {
        if (empty($g5['write_prefix'])) {
            throw new Exception('게시판 테이블 정보를 찾지 못했습니다.');
        }
        $write_table = $g5['write_prefix'].$bo_table;
        if (!auto_comment_table_exists($write_table)) {
            throw new Exception('게시판 글 테이블을 찾지 못했습니다.');
        }
        $write = sql_fetch(" select wr_subject from {$write_table} where wr_id = '{$wr_id}' and wr_is_comment = 0 ", false);
        if (empty($write['wr_subject'])) {
            throw new Exception('원글을 찾지 못했습니다.');
        }
        $subject = $write['wr_subject'];
    }

    $content = auto_comment_validate_content($bo_table, $wr_id, $content, 0);

    sql_query(" insert into ".auto_comment_table('queue')."
                    set bo_table = '".auto_comment_escape($bo_table)."',
                        wr_id = '{$wr_id}',
                        acq_subject = '".auto_comment_escape($subject)."',
                        acq_author = '".auto_comment_escape($author)."',
                        acq_content = '".auto_comment_escape($content)."',
                        acq_scheduled_at = '".G5_TIME_YMDHIS."',
                        acq_status = 'pending',
                        acq_error = '".auto_comment_escape('관리자 목록에서 바로 댓글 예약')."',
                        acq_created_at = '".G5_TIME_YMDHIS."' ", false);

    $queue_id = sql_insert_id();
    auto_comment_log('direct_queue', $bo_table.' #'.$wr_id.' 관리자 목록 바로 댓글 예약', $queue_id);

    if ($insert_now) {
        $queue_table = auto_comment_table('queue');
        $queue = sql_fetch(" select * from {$queue_table} where acq_id = '{$queue_id}' and acq_status = 'pending' ");
        if (!$queue) {
            throw new Exception('즉시 등록할 예약 댓글을 찾지 못했습니다.');
        }

        try {
            $comment_id = auto_comment_insert_queue($queue);
            sql_query(" update {$queue_table}
                          set acq_status = 'inserted',
                              acq_inserted_at = '".G5_TIME_YMDHIS."',
                              acq_error = '".auto_comment_escape('등록 결과: '.auto_comment_comment_url($queue['bo_table'], (int) $queue['wr_id'], $comment_id))."'
                        where acq_id = '{$queue_id}' ", false);
            auto_comment_log('direct_insert', $bo_table.' #'.$wr_id.' 관리자 목록 즉시 댓글 등록 #'.$comment_id, $queue_id);
        } catch (Exception $e) {
            sql_query(" update {$queue_table}
                          set acq_status = 'failed',
                              acq_error = '".auto_comment_escape($e->getMessage())."'
                        where acq_id = '{$queue_id}' ", false);
            throw new Exception('댓글 즉시 등록 실패: '.$e->getMessage());
        }
    }

    return $queue_id;
}

function ac_action_apply_queue_input($queue_id)
{
    $queue_table = auto_comment_table('queue');
    $queue_id = (int) $queue_id;
    $queue = sql_fetch(" select bo_table, wr_id from {$queue_table} where acq_id = '{$queue_id}' and acq_status in ('review', 'pending') ");
    if (!$queue) {
        return null;
    }

    $author = trim(strip_tags(isset($_POST['acq_author']) ? $_POST['acq_author'] : ''));
    $content = trim(isset($_POST['acq_content']) ? $_POST['acq_content'] : '');
    $scheduled_at = trim(isset($_POST['acq_scheduled_at']) ? $_POST['acq_scheduled_at'] : '');
    if ($author === '' || $content === '' || !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $scheduled_at)) {
        ac_action_redirect('작성자, 댓글내용, 예약시간을 확인해주세요.', ac_admin_url('queue'));
    }

    try {
        $content = auto_comment_validate_content($queue['bo_table'], (int) $queue['wr_id'], $content, $queue_id);
    } catch (Exception $e) {
        ac_action_redirect($e->getMessage(), ac_admin_url('queue'));
    }

    sql_query(" update {$queue_table}
                  set acq_author = '".auto_comment_escape($author)."',
                      acq_content = '".auto_comment_escape($content)."',
                      acq_scheduled_at = '".auto_comment_escape($scheduled_at)."'
                where acq_id = '{$queue_id}'
                  and acq_status in ('review', 'pending') ", false);

    return sql_fetch(" select * from {$queue_table} where acq_id = '{$queue_id}' and acq_status in ('review', 'pending') ");
}

function ac_action_regenerate_queue_content($queue_id)
{
    global $g5;

    $queue_table = auto_comment_table('queue');
    $queue_id = (int) $queue_id;
    $queue = sql_fetch(" select * from {$queue_table} where acq_id = '{$queue_id}' and acq_status in ('review', 'pending') ");
    if (!$queue) {
        ac_action_redirect('재생성할 예약 댓글이 없습니다.', ac_admin_url('queue'));
    }

    $bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', $queue['bo_table']);
    $wr_id = (int) $queue['wr_id'];
    if (!$bo_table || $wr_id < 1 || empty($g5['write_prefix'])) {
        ac_action_redirect('게시판 또는 원글 번호가 올바르지 않습니다.', ac_admin_url('queue'));
    }

    $board = get_board_db($bo_table, true);
    if (!$board || empty($board['bo_table'])) {
        ac_action_redirect('게시판을 찾을 수 없습니다.', ac_admin_url('queue'));
    }

    $write_table = $g5['write_prefix'].$bo_table;
    $write = sql_fetch(" select * from {$write_table} where wr_id = '{$wr_id}' and wr_is_comment = 0 ");
    if (empty($write['wr_id'])) {
        ac_action_redirect('원글을 찾을 수 없습니다.', ac_admin_url('queue'));
    }

    $cfg = auto_comment_get_board_config($bo_table);
    $group = ($cfg && $cfg['acb_template_group']) ? $cfg['acb_template_group'] : 'default';
    try {
        $content = auto_comment_generate_content($board, $write, $group);
        $content = auto_comment_validate_content($bo_table, $wr_id, $content, $queue_id);
    } catch (Exception $e) {
        ac_action_redirect('댓글 재생성 실패: '.$e->getMessage(), ac_admin_url('queue'));
    }

    sql_query(" update {$queue_table}
                  set acq_content = '".auto_comment_escape($content)."'
                where acq_id = '{$queue_id}'
                  and acq_status in ('review', 'pending') ", false);
    auto_comment_log('regenerate', '예약 댓글 재생성', $queue_id);
}

$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';

if ($act === 'install') {
    auto_comment_install();
    ac_action_redirect('자동댓글 모듈 설치가 완료되었습니다.', ac_admin_url('dashboard'));
}

if ($act === 'update') {
    auto_comment_update();
    ac_action_redirect('자동댓글 모듈 업데이트가 완료되었습니다. 현재 버전: '.AUTO_COMMENT_VERSION, ac_admin_url('tools'));
}

if (!auto_comment_is_installed()) {
    ac_action_redirect('먼저 모듈을 설치해주세요.', ac_admin_url('dashboard'));
}

if ($act === 'save_global') {
    auto_comment_set_setting('enabled', isset($_REQUEST['enabled']) ? '1' : '0');
    auto_comment_set_setting('trigger_percent', (string) max(1, min(100, (int) $_REQUEST['trigger_percent'])));
    auto_comment_set_setting('trigger_interval', (string) max(30, min(3600, (int) $_REQUEST['trigger_interval'])));
    auto_comment_set_setting('max_run_items', (string) max(1, min(10, (int) $_REQUEST['max_run_items'])));
    auto_comment_set_setting('max_run_seconds', (string) max(1, min(5, (int) $_REQUEST['max_run_seconds'])));
    auto_comment_set_setting('daily_limit', (string) max(1, min(200, (int) $_REQUEST['daily_limit'])));
    auto_comment_set_setting('pending_expire_days', (string) max(1, min(90, (int) $_REQUEST['pending_expire_days'])));
    auto_comment_set_setting('strategy_enabled', isset($_REQUEST['strategy_enabled']) ? '1' : '0');
    auto_comment_set_setting('strategy_recent_days', (string) max(1, min(365, (int) $_REQUEST['strategy_recent_days'])));
    auto_comment_set_setting('strategy_scan_limit', (string) max(1, min(10, (int) $_REQUEST['strategy_scan_limit'])));
    auto_comment_set_setting('strategy_review_mode', '0');
    $auto_min_comments = max(0, min(20, (int) $_REQUEST['auto_min_comments']));
    $auto_max_comments = max($auto_min_comments, min(20, (int) $_REQUEST['auto_max_comments']));
    auto_comment_set_setting('auto_min_comments', (string) $auto_min_comments);
    auto_comment_set_setting('auto_max_comments', (string) $auto_max_comments);
    $views_min = max(1, min(10000, (int) (isset($_REQUEST['auto_views_per_comment_min']) ? $_REQUEST['auto_views_per_comment_min'] : 20)));
    $views_max = max(1, min(10000, (int) (isset($_REQUEST['auto_views_per_comment_max']) ? $_REQUEST['auto_views_per_comment_max'] : 50)));
    if ($views_max < $views_min) {
        $tmp = $views_min;
        $views_min = $views_max;
        $views_max = $tmp;
    }
    auto_comment_set_setting('auto_views_per_comment_min', (string) $views_min);
    auto_comment_set_setting('auto_views_per_comment_max', (string) $views_max);
    auto_comment_set_setting('auto_views_per_comment', (string) $views_min);
    auto_comment_set_setting('forbidden_words', trim(isset($_REQUEST['forbidden_words']) ? $_REQUEST['forbidden_words'] : ''));
    $generator_mode = isset($_REQUEST['generator_mode']) && $_REQUEST['generator_mode'] === 'ai' ? 'ai' : 'template';
    auto_comment_set_setting('generator_mode', $generator_mode);
    auto_comment_set_setting('ai_provider', 'icrm');
    $icrm_api_base_url = isset($_REQUEST['icrm_api_base_url']) ? trim($_REQUEST['icrm_api_base_url']) : '';
    auto_comment_set_setting('icrm_api_base_url', $icrm_api_base_url !== '' ? $icrm_api_base_url : 'https://icrm.co.kr/api/auto-comment');
    if (isset($_REQUEST['icrm_license_key']) && trim($_REQUEST['icrm_license_key']) !== '') {
        auto_comment_set_setting('icrm_license_key', trim($_REQUEST['icrm_license_key']));
    }
    auto_comment_set_setting('ai_author_name', trim(isset($_REQUEST['ai_author_name']) ? $_REQUEST['ai_author_name'] : '세부나이트 AI 가이드'));
    auto_comment_set_setting('auto_author_mode', 'random_korean');
    auto_comment_set_setting('auto_author_reuse_percent', (string) max(0, min(100, (int) $_REQUEST['auto_author_reuse_percent'])));
    auto_comment_set_setting('skip_bots', isset($_REQUEST['skip_bots']) && $_REQUEST['skip_bots'] === '1' ? '1' : '0');
    auto_comment_log('settings', '기본설정 저장', 0);
    ac_action_redirect('기본설정을 저장했습니다.', ac_admin_url('settings'));
}

if ($act === 'create_test_queue') {
    try {
        $queue_id = ac_action_create_test_queue();
        ac_action_redirect('테스트 예약을 생성했습니다. 예약번호: '.$queue_id, ac_admin_url('queue'));
    } catch (Exception $e) {
        ac_action_redirect($e->getMessage(), ac_admin_url('dashboard'));
    }
}

if ($act === 'test_icrm_api') {
    $result = auto_comment_test_icrm_api();
    ac_action_redirect($result['message'], ac_admin_url('settings'));
}

if ($act === 'run_strategy_scan') {
    $count = auto_comment_strategy_scan((int) auto_comment_get_setting('strategy_scan_limit', '3'));
    ac_action_redirect('전략 스캔을 실행했습니다. 예약 생성: '.$count.'개', ac_admin_url('queue'));
}

if ($act === 'run_comment_reanalysis') {
    $summary = ac_action_run_comment_reanalysis();
    ac_action_redirect('등록 댓글 재평가를 실행했습니다. 대상 '.$summary['total'].'개 / 주의 '.$summary['warning'].'개 / 수정권장 '.$summary['danger'].'개', ac_admin_url('reeval'));
}

if ($act === 'queue_latest_post') {
    $request = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
    $return_tab = isset($request['return_tab']) && $request['return_tab'] === 'recent_viewed' ? 'recent_viewed' : 'latest';
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ac_action_redirect('댓글 작성 화면에서 내용을 확인한 뒤 등록해주세요.', ac_admin_url($return_tab));
    }
    try {
        $insert_now = isset($request['mode']) && $request['mode'] === 'insert';
        $queue_id = ac_action_queue_latest_post(
            isset($request['bo_table']) ? $request['bo_table'] : '',
            isset($request['wr_id']) ? (int) $request['wr_id'] : 0,
            $insert_now,
            isset($request['acq_author']) ? $request['acq_author'] : '',
            isset($request['acq_content']) ? $request['acq_content'] : '',
            isset($request['tone']) ? $request['tone'] : 'random'
        );
        ac_action_redirect($insert_now ? '선택한 글에 댓글을 즉시 등록했습니다.' : '선택한 글에 댓글을 예약했습니다. 예약번호: '.$queue_id, $insert_now ? ac_admin_url('history') : ac_admin_url('queue'));
    } catch (Exception $e) {
        ac_action_redirect($e->getMessage(), ac_admin_url($return_tab));
    }
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
    ac_action_redirect('게시판설정을 저장했습니다.', ac_admin_url('boards'));
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
        ac_action_redirect('가져올 JSON 내용을 입력해주세요.', ac_admin_url('tools'));
    }
    $data = json_decode($json, true);
    if (!is_array($data)) {
        ac_action_redirect('JSON 형식이 올바르지 않습니다.', ac_admin_url('tools'));
    }
    try {
        auto_comment_import_config($data);
    } catch (Exception $e) {
        ac_action_redirect($e->getMessage(), ac_admin_url('tools'));
    }
    ac_action_redirect('설정을 가져왔습니다.', ac_admin_url('tools'));
}

if ($act === 'queue_action') {
    $queue_id = (int) $_POST['acq_id'];
    $mode = isset($_POST['mode']) ? $_POST['mode'] : '';
    $queue_table = auto_comment_table('queue');

    if ($mode === 'cancel') {
        sql_query(" update {$queue_table} set acq_status = 'cancelled' where acq_id = '{$queue_id}' and acq_status in ('review', 'pending') ", false);
        auto_comment_log('cancel', '예약 댓글 취소', $queue_id);
        ac_action_redirect('예약 댓글을 취소했습니다.', ac_admin_url('queue'));
    }

    if ($mode === 'delete') {
        sql_query(" delete from {$queue_table} where acq_id = '{$queue_id}' and acq_status <> 'inserted' ", false);
        auto_comment_log('delete', '예약 댓글 삭제', $queue_id);
        ac_action_redirect('예약 댓글을 삭제했습니다.', ac_admin_url('queue'));
    }

    if ($mode === 'save') {
        ac_action_apply_queue_input($queue_id);
        auto_comment_log('queue_update', '예약 댓글 수정', $queue_id);
        ac_action_redirect('예약 댓글을 수정했습니다.', ac_admin_url('queue'));
    }

    if ($mode === 'regenerate') {
        ac_action_apply_queue_input($queue_id);
        ac_action_regenerate_queue_content($queue_id);
        ac_action_redirect('댓글 내용을 다시 생성했습니다.', ac_admin_url('queue'));
    }

    if ($mode === 'approve') {
        ac_action_apply_queue_input($queue_id);
        sql_query(" update {$queue_table}
                      set acq_status = 'pending'
                    where acq_id = '{$queue_id}'
                      and acq_status = 'review' ", false);
        auto_comment_log('approve', '검수 댓글 승인예약', $queue_id);
        ac_action_redirect('검수 댓글을 승인예약으로 변경했습니다.', ac_admin_url('queue'));
    }

    if ($mode === 'insert') {
        $queue = ac_action_apply_queue_input($queue_id);
        if ($queue) {
            try {
                $comment_id = auto_comment_insert_queue($queue);
                sql_query(" update {$queue_table}
                              set acq_status = 'inserted',
                                  acq_inserted_at = '".G5_TIME_YMDHIS."',
                                  acq_error = '".auto_comment_escape('등록 결과: '.auto_comment_comment_url($queue['bo_table'], (int) $queue['wr_id'], $comment_id))."'
                            where acq_id = '{$queue_id}' ", false);
                auto_comment_log('insert_manual', '수동 댓글 등록 #'.$comment_id, $queue_id);
                ac_action_redirect('댓글을 즉시 등록했습니다.', ac_admin_url('queue'));
            } catch (Exception $e) {
                sql_query(" update {$queue_table}
                              set acq_status = 'failed',
                                  acq_error = '".auto_comment_escape($e->getMessage())."'
                            where acq_id = '{$queue_id}' ", false);
                ac_action_redirect('댓글 등록 실패: '.$e->getMessage(), ac_admin_url('queue'));
            }
        }
    }

    ac_action_redirect('처리할 예약 댓글이 없습니다.', ac_admin_url('queue'));
}

if ($act === 'run_worker') {
    $count = auto_comment_run_worker(10);
    ac_action_redirect('worker를 실행했습니다. 처리: '.$count.'개', ac_admin_url('queue'));
}

ac_action_redirect('처리할 작업이 없습니다.', ac_admin_url('dashboard'));
