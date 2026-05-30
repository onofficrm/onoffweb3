<?php
if (!defined('_GNUBOARD_')) exit;
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>

<article class="board-wrap board-wrap--basic-clean board-view" id="bo_v" style="width:<?php echo $width; ?>">

    <header class="board-view__head">
        <h2 class="board-title" id="bo_v_title">
            <?php if ($category_name) { ?>
            <span class="bo_v_cate board-view__cate"><?php echo $view['ca_name']; ?></span>
            <?php } ?>
            <span class="bo_v_tit board-title__text"><?php echo cut_str(get_text($view['wr_subject']), 70); ?></span>
        </h2>
    </header>

    <section class="board-view__meta" id="bo_v_info">
        <h2 class="sound_only">페이지 정보</h2>
        <div class="profile_info board-view__author">
            <div class="pf_img"><?php echo get_member_profile_img($view['mb_id']) ?></div>
            <div class="profile_info_ct board-view__author-info">
                <span class="sound_only">작성자</span>
                <strong class="board-view__name"><?php echo $view['name'] ?><?php if ($is_ip_view) { echo '&nbsp;('.$ip.')'; } ?></strong>
                <ul class="board-view__stats">
                    <li>
                        <span class="sound_only">댓글</span>
                        <a href="#bo_vc"><i class="fa fa-commenting-o" aria-hidden="true"></i> <?php echo number_format($view['wr_comment']) ?>건</a>
                    </li>
                    <li>
                        <span class="sound_only">조회</span>
                        <i class="fa fa-eye" aria-hidden="true"></i> <?php echo number_format($view['wr_hit']) ?>회
                    </li>
                    <li class="if_date">
                        <span class="sound_only">작성일</span>
                        <i class="fa fa-clock-o" aria-hidden="true"></i>
                        <time datetime="<?php echo date('c', strtotime($view['wr_datetime'])) ?>"><?php echo date('Y-m-d H:i', strtotime($view['wr_datetime'])) ?></time>
                    </li>
                </ul>
            </div>
        </div>

        <div class="board-actions board-view__actions" id="bo_v_top">
            <?php ob_start(); ?>
            <ul class="btn_bo_user bo_v_com">
                <li><a href="<?php echo $list_href ?>" class="btn_b01 btn" title="목록"><i class="fa fa-list" aria-hidden="true"></i><span class="sound_only">목록</span></a></li>
                <?php if ($reply_href) { ?><li><a href="<?php echo $reply_href ?>" class="btn_b01 btn" title="답변"><i class="fa fa-reply" aria-hidden="true"></i><span class="sound_only">답변</span></a></li><?php } ?>
                <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b01 btn" title="글쓰기"><i class="fa fa-pencil" aria-hidden="true"></i><span class="sound_only">글쓰기</span></a></li><?php } ?>
                <?php if ($update_href || $delete_href || $copy_href || $move_href || $search_href) { ?>
                <li>
                    <button type="button" class="btn_more_opt is_view_btn btn_b01 btn" title="게시판 옵션"><i class="fa fa-ellipsis-v" aria-hidden="true"></i><span class="sound_only">게시판 옵션</span></button>
                    <ul class="more_opt is_view_btn">
                        <?php if ($update_href) { ?><li><a href="<?php echo $update_href ?>">수정<i class="fa fa-pencil-square-o" aria-hidden="true"></i></a></li><?php } ?>
                        <?php if ($delete_href) { ?><li><a href="<?php echo $delete_href ?>" onclick="del(this.href); return false;">삭제<i class="fa fa-trash-o" aria-hidden="true"></i></a></li><?php } ?>
                        <?php if ($copy_href) { ?><li><a href="<?php echo $copy_href ?>" onclick="board_move(this.href); return false;">복사<i class="fa fa-files-o" aria-hidden="true"></i></a></li><?php } ?>
                        <?php if ($move_href) { ?><li><a href="<?php echo $move_href ?>" onclick="board_move(this.href); return false;">이동<i class="fa fa-arrows" aria-hidden="true"></i></a></li><?php } ?>
                        <?php if ($search_href) { ?><li><a href="<?php echo $search_href ?>">검색<i class="fa fa-search" aria-hidden="true"></i></a></li><?php } ?>
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
                    if(!$(e.target).closest('.is_view_btn').length) {
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

    <section class="board-view__body" id="bo_v_atc">
        <h2 class="sound_only" id="bo_v_atc_title">본문</h2>
        <div class="board-view__share" id="bo_v_share">
            <?php include_once(G5_SNS_PATH.'/view.sns.skin.php'); ?>
            <?php if ($scrap_href) { ?><a href="<?php echo $scrap_href; ?>" target="_blank" class="btn btn_b03" onclick="win_scrap(this.href); return false;"><i class="fa fa-bookmark" aria-hidden="true"></i> 스크랩</a><?php } ?>
        </div>

        <?php
        $v_img_count = count($view['file']);
        if ($v_img_count) {
            echo '<div id="bo_v_img" class="board-view__images">';
            foreach ($view['file'] as $view_file) {
                echo get_file_thumbnail($view_file);
            }
            echo '</div>';
        }
        ?>

        <div id="bo_v_con" class="board-view__content"><?php echo get_view_thumbnail($view['content']); ?></div>

        <?php if ($is_signature) { ?><div class="board-view__signature"><?php echo $signature ?></div><?php } ?>

        <?php if ($good_href || $nogood_href) { ?>
        <div id="bo_v_act" class="board-view__vote">
            <?php if ($good_href) { ?>
            <span class="bo_v_act_gng">
                <a href="<?php echo $good_href.'&amp;'.$qstr ?>" id="good_button" class="bo_v_good"><i class="fa fa-thumbs-o-up" aria-hidden="true"></i><span class="sound_only">추천</span><strong><?php echo number_format($view['wr_good']) ?></strong></a>
                <b id="bo_v_act_good"></b>
            </span>
            <?php } ?>
            <?php if ($nogood_href) { ?>
            <span class="bo_v_act_gng">
                <a href="<?php echo $nogood_href.'&amp;'.$qstr ?>" id="nogood_button" class="bo_v_nogood"><i class="fa fa-thumbs-o-down" aria-hidden="true"></i><span class="sound_only">비추천</span><strong><?php echo number_format($view['wr_nogood']) ?></strong></a>
                <b id="bo_v_act_nogood"></b>
            </span>
            <?php } ?>
        </div>
        <?php } else {
            if ($board['bo_use_good'] || $board['bo_use_nogood']) {
        ?>
        <div id="bo_v_act" class="board-view__vote">
            <?php if ($board['bo_use_good']) { ?><span class="bo_v_good"><i class="fa fa-thumbs-o-up" aria-hidden="true"></i><span class="sound_only">추천</span><strong><?php echo number_format($view['wr_good']) ?></strong></span><?php } ?>
            <?php if ($board['bo_use_nogood']) { ?><span class="bo_v_nogood"><i class="fa fa-thumbs-o-down" aria-hidden="true"></i><span class="sound_only">비추천</span><strong><?php echo number_format($view['wr_nogood']) ?></strong></span><?php } ?>
        </div>
        <?php
            }
        }
        ?>
    </section>

    <?php
    $cnt = 0;
    if ($view['file']['count']) {
        for ($i=0; $i<count($view['file']); $i++) {
            if (isset($view['file'][$i]['source']) && $view['file'][$i]['source'] && !$view['file'][$i]['view'])
                $cnt++;
        }
    }
    ?>

    <?php if ($cnt) { ?>
    <section id="bo_v_file" class="board-view__files">
        <h3 class="board-view__section-title">첨부파일</h3>
        <ul>
        <?php for ($i=0; $i<count($view['file']); $i++) {
            if (isset($view['file'][$i]['source']) && $view['file'][$i]['source'] && !$view['file'][$i]['view']) {
        ?>
            <li>
                <i class="fa fa-folder-open" aria-hidden="true"></i>
                <a href="<?php echo $view['file'][$i]['href']; ?>" class="view_file_download">
                    <strong><?php echo $view['file'][$i]['source'] ?></strong> <?php echo $view['file'][$i]['content'] ?> (<?php echo $view['file'][$i]['size'] ?>)
                </a>
                <span class="bo_v_file_cnt"><?php echo $view['file'][$i]['download'] ?>회 다운로드 · <?php echo $view['file'][$i]['datetime'] ?></span>
            </li>
        <?php
            }
        }
        ?>
        </ul>
    </section>
    <?php } ?>

    <?php if (isset($view['link']) && array_filter($view['link'])) { ?>
    <section id="bo_v_link" class="board-view__links">
        <h3 class="board-view__section-title">관련링크</h3>
        <ul>
        <?php
        $cnt = 0;
        for ($i=1; $i<=count($view['link']); $i++) {
            if ($view['link'][$i]) {
                $cnt++;
                $link = cut_str($view['link'][$i], 70);
        ?>
            <li>
                <i class="fa fa-link" aria-hidden="true"></i>
                <a href="<?php echo $view['link_href'][$i] ?>" target="_blank" rel="noopener noreferrer"><strong><?php echo $link ?></strong></a>
                <span class="bo_v_link_cnt"><?php echo $view['link_hit'][$i] ?>회 연결</span>
            </li>
        <?php
            }
        }
        ?>
        </ul>
    </section>
    <?php } ?>

    <?php if ($prev_href || $next_href) { ?>
    <nav class="bo_v_nb board-view__nav" aria-label="이전글 다음글">
        <ul>
            <?php if ($prev_href) { ?><li class="btn_prv"><span class="nb_tit"><i class="fa fa-chevron-up" aria-hidden="true"></i> 이전글</span><a href="<?php echo $prev_href ?>"><?php echo $prev_wr_subject;?></a><span class="nb_date"><?php echo str_replace('-', '.', substr($prev_wr_date, '2', '8')); ?></span></li><?php } ?>
            <?php if ($next_href) { ?><li class="btn_next"><span class="nb_tit"><i class="fa fa-chevron-down" aria-hidden="true"></i> 다음글</span><a href="<?php echo $next_href ?>"><?php echo $next_wr_subject;?></a><span class="nb_date"><?php echo str_replace('-', '.', substr($next_wr_date, '2', '8')); ?></span></li><?php } ?>
        </ul>
    </nav>
    <?php } ?>

    <?php include_once(G5_BBS_PATH.'/view_comment.php'); ?>
</article>

<script>
<?php if ($board['bo_download_point'] < 0) { ?>
$(function() {
    $("a.view_file_download").click(function() {
        if(!g5_is_member) {
            alert("다운로드 권한이 없습니다.\n회원이시라면 로그인 후 이용해 보십시오.");
            return false;
        }
        var msg = "파일을 다운로드 하시면 포인트가 차감(<?php echo number_format($board['bo_download_point']) ?>점)됩니다.\n\n포인트는 게시물당 한번만 차감되며 다음에 다시 다운로드 하셔도 중복하여 차감하지 않습니다.\n\n그래도 다운로드 하시겠습니까?";
        if(confirm(msg)) {
            var href = $(this).attr("href")+"&js=on";
            $(this).attr("href", href);
            return true;
        } else {
            return false;
        }
    });
});
<?php } ?>

function board_move(href)
{
    window.open(href, "boardmove", "left=50, top=50, width=500, height=550, scrollbars=1");
}
</script>

<script>
$(function() {
    $("a.view_image").click(function() {
        window.open(this.href, "large_image", "location=yes,links=no,toolbar=no,top=10,left=10,width=10,height=10,resizable=yes,scrollbars=no,status=no");
        return false;
    });

    $("#good_button, #nogood_button").click(function() {
        var $tx;
        if (this.id == "good_button")
            $tx = $("#bo_v_act_good");
        else
            $tx = $("#bo_v_act_nogood");
        excute_good(this.href, $(this), $tx);
        return false;
    });

    $("#bo_v_atc").viewimageresize();
});

function excute_good(href, $el, $tx)
{
    $.post(
        href,
        { js: "on" },
        function(data) {
            if (data.error) {
                alert(data.error);
                return false;
            }
            if (data.count) {
                $el.find("strong").text(number_format(String(data.count)));
                if ($tx.attr("id").search("nogood") > -1) {
                    $tx.text("이 글을 비추천하셨습니다.");
                    $tx.fadeIn(200).delay(2500).fadeOut(200);
                } else {
                    $tx.text("이 글을 추천하셨습니다.");
                    $tx.fadeIn(200).delay(2500).fadeOut(200);
                }
            }
        }, "json"
    );
}
</script>
