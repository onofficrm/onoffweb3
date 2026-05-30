<?php
define('G5_IS_ADMIN', true);
require_once __DIR__.'/../../common.php';

function auto_comment_update_redirect($msg, $url)
{
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }

    echo '<script>alert('.json_encode($msg).');location.replace('.json_encode($url).');</script>';
    echo '<noscript><p>'.htmlspecialchars($msg, ENT_QUOTES, 'UTF-8').'</p><p><a href="'.htmlspecialchars($url, ENT_QUOTES, 'UTF-8').'">이동</a></p></noscript>';
    exit;
}

if ($is_admin != 'super') {
    auto_comment_update_redirect('최고관리자만 접근 가능합니다.', G5_URL);
}

require_once G5_ADMIN_PATH.'/admin.lib.php';

include_once G5_PLUGIN_PATH.'/auto_comment/auto_comment.lib.php';

auto_comment_update();

auto_comment_update_redirect('자동댓글 모듈 업데이트가 완료되었습니다. 현재 버전: '.AUTO_COMMENT_VERSION, G5_PLUGIN_URL.'/auto_comment/admin/index.php?tab=tools');
