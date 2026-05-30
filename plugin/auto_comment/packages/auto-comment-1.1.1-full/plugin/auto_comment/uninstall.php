<?php
define('G5_IS_ADMIN', true);
require_once __DIR__.'/../../common.php';

function auto_comment_uninstall_redirect($msg, $url)
{
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }

    echo '<script>alert('.json_encode($msg).');location.replace('.json_encode($url).');</script>';
    echo '<noscript><p>'.htmlspecialchars($msg, ENT_QUOTES, 'UTF-8').'</p><p><a href="'.htmlspecialchars($url, ENT_QUOTES, 'UTF-8').'">이동</a></p></noscript>';
    exit;
}

if ($is_admin != 'super') {
    auto_comment_uninstall_redirect('최고관리자만 접근 가능합니다.', G5_URL);
}

require_once G5_ADMIN_PATH.'/admin.lib.php';

include_once G5_PLUGIN_PATH.'/auto_comment/auto_comment.lib.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_admin_token();

    $tables = array('setting', 'board', 'author', 'template', 'queue', 'log', 'ai_usage', 'visitor', 'post_view');
    foreach ($tables as $name) {
        sql_query(" drop table if exists ".auto_comment_table($name), false);
    }

    @unlink(G5_DATA_PATH.'/cache/auto_comment_last_run.php');
    @unlink(G5_DATA_PATH.'/cache/auto_comment_worker.lock');

    auto_comment_uninstall_redirect('자동댓글 모듈 DB 테이블을 삭제했습니다.', G5_URL);
}

$g5['title'] = '자동댓글 모듈 제거';
include_once G5_ADMIN_PATH.'/admin.head.php';
?>
<form method="post" style="margin:20px;padding:20px;background:#fff;border:1px solid #ddd">
    <input type="hidden" name="token" value="<?php echo get_admin_token(); ?>">
    <p style="margin:0 0 12px">자동댓글 모듈의 설정, 템플릿, 예약목록, 로그 테이블을 삭제합니다. 실제 게시글과 이미 등록된 댓글은 삭제하지 않습니다.</p>
    <button type="submit" style="padding:8px 14px;border:0;background:#c0392b;color:#fff">모듈 DB 삭제</button>
</form>
<?php
include_once G5_ADMIN_PATH.'/admin.tail.php';
