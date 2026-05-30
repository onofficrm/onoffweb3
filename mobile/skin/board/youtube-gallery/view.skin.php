<?php
if (!defined('_GNUBOARD_')) exit;
include_once(G5_LIB_PATH.'/thumbnail.lib.php');
include_once(G5_SKIN_PATH.'/board/_inc/g5b-youtube.php');

add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

$yt_id = g5b_youtube_id_from_write($view);
$yt_summary = !empty($view['wr_2']) ? get_text($view['wr_2']) : '';
?>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>

<article class="board-wrap board-wrap--youtube-gallery board-view board-view--youtube" id="bo_v" style="width:<?php echo $width; ?>">

    <header class="board-view__head">
        <h2 class="board-title" id="bo_v_title">
            <?php if ($category_name) { ?><span class="bo_v_cate board-view__cate"><?php echo $view['ca_name']; ?></span><?php } ?>
            <span class="bo_v_tit board-title__text"><?php echo cut_str(get_text($view['wr_subject']), 70); ?></span>
        </h2>
        <?php if ($yt_summary) { ?><p class="board-view__summary"><?php echo nl2br($yt_summary); ?></p><?php } ?>
    </header>

    <section class="board-view__meta" id="bo_v_info">
        <h2 class="sound_only">페이지 정보</h2>
        <div class="profile_info board-view__author">
            <div class="pf_img"><?php echo get_member_profile_img($view['mb_id']) ?></div>
            <div class="profile_info_ct board-view__author-info">
                <strong class="board-view__name"><?php echo $view['name'] ?></strong>
                <ul class="board-view__stats">
                    <li><a href="#bo_vc"><i class="fa fa-commenting-o" aria-hidden="true"></i> <?php echo number_format($view['wr_comment']) ?>건</a></li>
                    <li><i class="fa fa-eye" aria-hidden="true"></i> <?php echo number_format($view['wr_hit']) ?>회</li>
                    <li><i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo date('Y-m-d H:i', strtotime($view['wr_datetime'])) ?></li>
                </ul>
            </div>
        </div>
        <div class="board-actions board-view__actions" id="bo_v_top">
            <ul class="btn_bo_user bo_v_com">
                <li><a href="<?php echo $list_href ?>" class="btn_b01 btn" title="목록"><i class="fa fa-list" aria-hidden="true"></i><span class="sound_only">목록</span></a></li>
                <?php if ($reply_href) { ?><li><a href="<?php echo $reply_href ?>" class="btn_b01 btn" title="답변"><i class="fa fa-reply" aria-hidden="true"></i><span class="sound_only">답변</span></a></li><?php } ?>
                <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b01 btn" title="글쓰기"><i class="fa fa-pencil" aria-hidden="true"></i><span class="sound_only">글쓰기</span></a></li><?php } ?>
                <?php if ($update_href || $delete_href || $copy_href || $move_href || $search_href) { ?>
                <li>
                    <button type="button" class="btn_more_opt is_view_btn btn_b01 btn" title="옵션"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></button>
                    <ul class="more_opt is_view_btn">
                        <?php if ($update_href) { ?><li><a href="<?php echo $update_href ?>">수정</a></li><?php } ?>
                        <?php if ($delete_href) { ?><li><a href="<?php echo $delete_href ?>" onclick="del(this.href); return false;">삭제</a></li><?php } ?>
                        <?php if ($copy_href) { ?><li><a href="<?php echo $copy_href ?>" onclick="board_move(this.href); return false;">복사</a></li><?php } ?>
                        <?php if ($move_href) { ?><li><a href="<?php echo $move_href ?>" onclick="board_move(this.href); return false;">이동</a></li><?php } ?>
                        <?php if ($search_href) { ?><li><a href="<?php echo $search_href ?>">검색</a></li><?php } ?>
                    </ul>
                </li>
                <?php } ?>
            </ul>
            <script>
            jQuery(function($){
                $(".btn_more_opt.is_view_btn").on("click", function(e) { e.stopPropagation(); $(".more_opt.is_view_btn").toggle(); });
                $(document).on("click", function (e) {
                    if(!$(e.target).closest('.is_view_btn').length) $(".more_opt.is_view_btn").hide();
                });
            });
            </script>
        </div>
    </section>

    <section class="board-view__body board-view__body--youtube" id="bo_v_atc">
        <h2 class="sound_only">영상</h2>
        <div class="board-view__player">
            <?php
            if ($yt_id) {
                echo g5b_youtube_embed_html($yt_id, $view['wr_subject']);
            } else {
                echo g5b_youtube_fallback_html();
            }
            ?>
        </div>

        <div class="board-view__share" id="bo_v_share">
            <?php include_once(G5_SNS_PATH.'/view.sns.skin.php'); ?>
            <?php if ($scrap_href) { ?><a href="<?php echo $scrap_href; ?>" target="_blank" class="btn btn_b03" onclick="win_scrap(this.href); return false;"><i class="fa fa-bookmark" aria-hidden="true"></i> 스크랩</a><?php } ?>
        </div>

        <?php if (trim($view['content'])) { ?>
        <div class="board-view__content-wrap">
            <h3 class="board-view__section-title">상세 내용</h3>
            <div id="bo_v_con" class="board-view__content"><?php echo get_view_thumbnail($view['content']); ?></div>
        </div>
        <?php } ?>

        <?php if ($good_href || $nogood_href) { ?>
        <div id="bo_v_act" class="board-view__vote">
            <?php if ($good_href) { ?><a href="<?php echo $good_href.'&amp;'.$qstr ?>" id="good_button" class="bo_v_good"><i class="fa fa-thumbs-o-up" aria-hidden="true"></i><strong><?php echo number_format($view['wr_good']) ?></strong></a><?php } ?>
            <?php if ($nogood_href) { ?><a href="<?php echo $nogood_href.'&amp;'.$qstr ?>" id="nogood_button" class="bo_v_nogood"><i class="fa fa-thumbs-o-down" aria-hidden="true"></i><strong><?php echo number_format($view['wr_nogood']) ?></strong></a><?php } ?>
        </div>
        <?php } ?>
    </section>

    <?php
    $cnt = 0;
    if (!empty($view['file']['count'])) {
        for ($i=0; $i<count($view['file']); $i++) {
            if (!empty($view['file'][$i]['source']) && empty($view['file'][$i]['view'])) $cnt++;
        }
    }
    ?>
    <?php if ($cnt) { ?>
    <section id="bo_v_file" class="board-view__files">
        <h3 class="board-view__section-title">첨부파일</h3>
        <ul>
        <?php for ($i=0; $i<count($view['file']); $i++) {
            if (!empty($view['file'][$i]['source']) && empty($view['file'][$i]['view'])) {
        ?>
            <li><a href="<?php echo $view['file'][$i]['href']; ?>" class="view_file_download"><strong><?php echo $view['file'][$i]['source'] ?></strong></a></li>
        <?php } } ?>
        </ul>
    </section>
    <?php } ?>

    <?php if (isset($view['link']) && array_filter($view['link'])) { ?>
    <section id="bo_v_link" class="board-view__links">
        <h3 class="board-view__section-title">관련링크</h3>
        <ul>
        <?php for ($i=1; $i<=count($view['link']); $i++) {
            if (!empty($view['link'][$i])) {
        ?>
            <li><a href="<?php echo $view['link_href'][$i] ?>" target="_blank" rel="noopener noreferrer"><?php echo cut_str($view['link'][$i], 70); ?></a></li>
        <?php } } ?>
        </ul>
    </section>
    <?php } ?>

    <?php if ($prev_href || $next_href) { ?>
    <nav class="bo_v_nb board-view__nav">
        <ul>
            <?php if ($prev_href) { ?><li class="btn_prv"><span class="nb_tit">이전글</span><a href="<?php echo $prev_href ?>"><?php echo $prev_wr_subject;?></a></li><?php } ?>
            <?php if ($next_href) { ?><li class="btn_next"><span class="nb_tit">다음글</span><a href="<?php echo $next_href ?>"><?php echo $next_wr_subject;?></a></li><?php } ?>
        </ul>
    </nav>
    <?php } ?>

    <?php
    if ($yt_id) {
        g5b_youtube_print_video_schema($view, $yt_id);
    }
    ?>

    <?php include_once(G5_BBS_PATH.'/view_comment.php'); ?>
</article>

<script>
<?php if ($board['bo_download_point'] < 0) { ?>
$(function() {
    $("a.view_file_download").click(function() {
        if(!g5_is_member) { alert("다운로드 권한이 없습니다."); return false; }
        if(confirm("포인트가 차감됩니다. 다운로드하시겠습니까?")) {
            $(this).attr("href", $(this).attr("href")+"&js=on");
            return true;
        }
        return false;
    });
});
<?php } ?>
function board_move(href) { window.open(href, "boardmove", "left=50, top=50, width=500, height=550, scrollbars=1"); }
$(function() {
    $("a.view_image").click(function() { window.open(this.href, "large_image", "resizable=yes,scrollbars=yes"); return false; });
    $("#bo_v_atc").viewimageresize();
});
</script>
