<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once dirname(__FILE__) . '/inquiry-helper.php';
include_once G5_LIB_PATH . '/thumbnail.lib.php';

add_stylesheet('<link rel="stylesheet" href="' . $board_skin_url . '/style.css">', 0);

$status_raw   = g5b_inquiry_get_extra($view, 'wr_6');
$status_label = g5b_inquiry_status_label($status_raw);
$status_class = g5b_inquiry_status_class($status_raw);
$phone        = g5b_inquiry_get_extra($view, 'wr_1');
$email_extra  = g5b_inquiry_get_extra($view, 'wr_2');
$email_board  = !empty($view['wr_email']) ? get_text($view['wr_email']) : '';
$email        = $email_extra !== '' ? $email_extra : $email_board;
$page_url     = g5b_inquiry_get_extra($view, 'wr_3');
$privacy      = g5b_inquiry_get_extra($view, 'wr_4');
$ip_extra     = g5b_inquiry_get_extra($view, 'wr_5');
$ip           = $ip_extra !== '' ? $ip_extra : (isset($view['wr_ip']) ? get_text($view['wr_ip']) : '');
$manager      = g5b_inquiry_get_extra($view, 'wr_7');
$admin_memo   = g5b_inquiry_get_extra($view, 'wr_8');
$source       = g5b_inquiry_get_extra($view, 'wr_9');
$tel_href     = g5b_inquiry_phone_tel($phone);
$page_href    = g5b_inquiry_safe_url($page_url);
$mailto       = $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) ? 'mailto:' . rawurlencode($email) : '';
?>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>

<article class="inquiry-board inquiry-detail" id="bo_v" style="width:<?php echo $width; ?>">

    <p class="privacy-warning inquiry-detail__warning">이 문의에는 개인정보가 포함되어 있으므로 외부 공유·캡처·전달에 주의하세요.</p>

    <header class="inquiry-detail__head">
        <div class="inquiry-detail__status-row">
            <span class="inquiry-status <?php echo $status_class ?>"><?php echo get_text($status_label) ?></span>
            <?php if ($category_name) { ?>
            <span class="inquiry-detail__cate"><?php echo get_text($view['ca_name']); ?></span>
            <?php } ?>
            <time class="inquiry-detail__date" datetime="<?php echo date('c', strtotime($view['wr_datetime'])) ?>">
                <?php echo date('Y-m-d H:i', strtotime($view['wr_datetime'])) ?>
            </time>
        </div>
        <h2 class="inquiry-detail__title"><?php echo get_text($view['wr_subject']) ?></h2>
        <p class="inquiry-detail__status-hint">상태 변경은 <strong>수정</strong> 버튼에서 문의 상태(wr_6)를 변경한 뒤 저장하세요.</p>
    </header>

    <section class="inquiry-detail__meta inquiry-meta">
        <h3 class="sound_only">문의자 정보</h3>
        <dl class="inquiry-meta__grid">
            <div class="inquiry-meta__item">
                <dt>문의자</dt>
                <dd><?php echo $view['name'] ?><?php if ($is_ip_view && $ip !== '') { ?> <span class="inquiry-meta__sub">(<?php echo get_text($ip) ?>)</span><?php } ?></dd>
            </div>
            <div class="inquiry-meta__item">
                <dt>연락처</dt>
                <dd>
                    <?php if ($phone !== '') {
                        echo get_text($phone);
                    } else {
                        echo '<span class="inquiry-meta__empty">—</span>';
                    } ?>
                </dd>
            </div>
            <div class="inquiry-meta__item">
                <dt>이메일</dt>
                <dd>
                    <?php if ($email !== '') {
                        echo get_text($email);
                    } else {
                        echo '<span class="inquiry-meta__empty">—</span>';
                    } ?>
                </dd>
            </div>
            <div class="inquiry-meta__item">
                <dt>접수 페이지</dt>
                <dd>
                    <?php if ($page_href !== '') { ?>
                    <a href="<?php echo htmlspecialchars($page_href, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"><?php echo get_text($page_url) ?></a>
                    <?php } elseif ($page_url !== '') {
                        echo get_text($page_url);
                    } else {
                        echo '<span class="inquiry-meta__empty">—</span>';
                    } ?>
                </dd>
            </div>
            <div class="inquiry-meta__item">
                <dt>접수 IP</dt>
                <dd><?php echo $ip !== '' ? get_text($ip) : '—' ?></dd>
            </div>
            <div class="inquiry-meta__item">
                <dt>개인정보 동의</dt>
                <dd><?php echo $privacy !== '' ? get_text($privacy) : '—' ?></dd>
            </div>
            <?php if ($source !== '') { ?>
            <div class="inquiry-meta__item">
                <dt>유입경로</dt>
                <dd><?php echo get_text($source) ?></dd>
            </div>
            <?php } ?>
        </dl>
    </section>

    <div class="inquiry-actions inquiry-detail__quick">
        <?php if ($tel_href !== '') { ?>
        <a href="<?php echo htmlspecialchars($tel_href, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary inquiry-actions__call"><i class="fa fa-phone" aria-hidden="true"></i> 전화걸기</a>
        <?php } ?>
        <?php if ($mailto !== '') { ?>
        <a href="<?php echo htmlspecialchars($mailto, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline inquiry-actions__mail"><i class="fa fa-envelope-o" aria-hidden="true"></i> 이메일</a>
        <?php } ?>
        <?php if ($page_href !== '') { ?>
        <a href="<?php echo htmlspecialchars($page_href, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline inquiry-actions__page" target="_blank" rel="noopener noreferrer"><i class="fa fa-external-link" aria-hidden="true"></i> 접수 페이지</a>
        <?php } ?>
    </div>

    <section class="inquiry-detail__body" id="bo_v_atc">
        <h3 class="inquiry-detail__section-title">문의 내용</h3>
        <div id="bo_v_con" class="inquiry-detail__content"><?php echo get_view_thumbnail($view['content']); ?></div>
    </section>

    <?php if ($manager !== '' || $admin_memo !== '') { ?>
    <section class="inquiry-detail__admin inquiry-admin-note">
        <h3 class="inquiry-detail__section-title">관리 정보</h3>
        <?php if ($manager !== '') { ?>
        <p class="inquiry-admin-note__manager"><strong>담당자</strong> <?php echo get_text($manager) ?></p>
        <?php } ?>
        <?php if ($admin_memo !== '') { ?>
        <div class="inquiry-admin-note__memo">
            <strong>관리자 메모</strong>
            <pre class="inquiry-admin-note__text"><?php echo htmlspecialchars($admin_memo, ENT_QUOTES, 'UTF-8') ?></pre>
        </div>
        <?php } ?>
    </section>
    <?php } ?>

    <?php
    $v_img_count = count($view['file']);
    if ($v_img_count) {
        echo '<div id="bo_v_img" class="inquiry-detail__images">';
        foreach ($view['file'] as $view_file) {
            echo get_file_thumbnail($view_file);
        }
        echo '</div>';
    }
    ?>

    <?php
    $cnt = 0;
    if ($view['file']['count']) {
        for ($i = 0; $i < count($view['file']); $i++) {
            if (isset($view['file'][$i]['source']) && $view['file'][$i]['source'] && !$view['file'][$i]['view']) {
                $cnt++;
            }
        }
    }
    ?>

    <?php if ($cnt) { ?>
    <section id="bo_v_file" class="inquiry-detail__files">
        <h3 class="inquiry-detail__section-title">첨부파일</h3>
        <ul>
        <?php for ($i = 0; $i < count($view['file']); $i++) {
            if (isset($view['file'][$i]['source']) && $view['file'][$i]['source'] && !$view['file'][$i]['view']) { ?>
            <li>
                <a href="<?php echo $view['file'][$i]['href']; ?>" class="view_file_download">
                    <?php echo get_text($view['file'][$i]['source']) ?> (<?php echo $view['file'][$i]['size'] ?>)
                </a>
            </li>
        <?php }
        } ?>
        </ul>
    </section>
    <?php } ?>

    <?php if (isset($view['link']) && array_filter($view['link'])) { ?>
    <section id="bo_v_link" class="inquiry-detail__links">
        <h3 class="inquiry-detail__section-title">관련링크</h3>
        <ul>
        <?php for ($i = 1; $i <= count($view['link']); $i++) {
            if ($view['link'][$i]) { ?>
            <li><a href="<?php echo $view['link_href'][$i] ?>" target="_blank" rel="noopener noreferrer"><?php echo get_text(cut_str($view['link'][$i], 70)) ?></a></li>
        <?php }
        } ?>
        </ul>
    </section>
    <?php } ?>

    <section class="inquiry-detail__toolbar" id="bo_v_info">
        <h2 class="sound_only">게시판 옵션</h2>
        <div class="inquiry-detail__stats">
            <?php if ($is_ip_view && $ip !== '') { ?><span>IP <?php echo get_text($ip) ?></span><?php } ?>
            <span>조회 <?php echo number_format($view['wr_hit']) ?></span>
            <?php if ($view['wr_comment']) { ?><span>댓글 <?php echo number_format($view['wr_comment']) ?></span><?php } ?>
        </div>
        <div class="inquiry-actions inquiry-detail__actions" id="bo_v_top">
            <?php ob_start(); ?>
            <ul class="btn_bo_user bo_v_com">
                <li><a href="<?php echo $list_href ?>" class="btn_b01 btn" title="목록"><i class="fa fa-list" aria-hidden="true"></i><span class="sound_only">목록</span></a></li>
                <?php if ($reply_href) { ?><li><a href="<?php echo $reply_href ?>" class="btn_b01 btn" title="답변"><i class="fa fa-reply" aria-hidden="true"></i><span class="sound_only">답변</span></a></li><?php } ?>
                <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b01 btn" title="글쓰기"><i class="fa fa-pencil" aria-hidden="true"></i><span class="sound_only">글쓰기</span></a></li><?php } ?>
                <?php if ($update_href || $delete_href || $copy_href || $move_href || $search_href) { ?>
                <li>
                    <button type="button" class="btn_more_opt is_view_btn btn_b01 btn" title="옵션"><i class="fa fa-ellipsis-v" aria-hidden="true"></i><span class="sound_only">옵션</span></button>
                    <ul class="more_opt is_view_btn">
                        <?php if ($update_href) { ?><li><a href="<?php echo $update_href ?>">수정<i class="fa fa-pencil-square-o" aria-hidden="true"></i></a></li><?php } ?>
                        <?php if ($delete_href) { ?><li><a href="<?php echo $delete_href ?>" onclick="del(this.href); return false;">삭제<i class="fa fa-trash-o" aria-hidden="true"></i></a></li><?php } ?>
                        <?php if ($copy_href) { ?><li><a href="<?php echo $copy_href ?>" onclick="board_move(this.href); return false;">복사</a></li><?php } ?>
                        <?php if ($move_href) { ?><li><a href="<?php echo $move_href ?>" onclick="board_move(this.href); return false;">이동</a></li><?php } ?>
                        <?php if ($search_href) { ?><li><a href="<?php echo $search_href ?>">검색</a></li><?php } ?>
                    </ul>
                </li>
                <?php } ?>
            </ul>
            <script>
            jQuery(function($){
                $(".btn_more_opt.is_view_btn").on("click", function(e) {
                    e.stopPropagation();
                    $(".more_opt.is_view_btn").toggle();
                });
                $(document).on("click", function (e) {
                    if (!$(e.target).closest('.is_view_btn').length) {
                        $(".more_opt.is_view_btn").hide();
                    }
                });
            });
            </script>
            <?php
            $link_buttons = ob_get_contents();
            ob_end_flush();
            ?>
        </div>
    </section>

    <?php if ($prev_href || $next_href) { ?>
    <nav class="bo_v_nb inquiry-detail__nav" aria-label="이전글 다음글">
        <ul>
            <?php if ($prev_href) { ?><li class="btn_prv"><span class="nb_tit">이전</span><a href="<?php echo $prev_href ?>"><?php echo $prev_wr_subject; ?></a></li><?php } ?>
            <?php if ($next_href) { ?><li class="btn_next"><span class="nb_tit">다음</span><a href="<?php echo $next_href ?>"><?php echo $next_wr_subject; ?></a></li><?php } ?>
        </ul>
    </nav>
    <?php } ?>

    <?php include_once G5_BBS_PATH . '/view_comment.php'; ?>
</article>

<script>
<?php if ($board['bo_download_point'] < 0) { ?>
$(function() {
    $("a.view_file_download").click(function() {
        if (!g5_is_member) {
            alert("다운로드 권한이 없습니다.");
            return false;
        }
        if (confirm("다운로드 시 포인트가 차감됩니다. 계속하시겠습니까?")) {
            $(this).attr("href", $(this).attr("href") + "&js=on");
            return true;
        }
        return false;
    });
});
<?php } ?>
function board_move(href) {
    window.open(href, "boardmove", "left=50, top=50, width=500, height=550, scrollbars=1");
}
$(function() {
    $("#bo_v_atc").viewimageresize();
});
</script>
