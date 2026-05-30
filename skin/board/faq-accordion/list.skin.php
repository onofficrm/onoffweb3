<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once(G5_SKIN_PATH.'/board/_inc/g5b-faq.php');

add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

$faq_schema_items = array();
?>

<div class="faq-board board-wrap board-wrap--faq-accordion" id="bo_list" style="width:<?php echo $width; ?>">

    <header class="faq-board__head board-header">
        <h1 class="faq-board__title board-list-h1"><?php echo get_text($board['bo_subject']); ?></h1>
        <?php if (!empty($board['bo_content'])) { ?>
        <div class="faq-board__desc"><?php echo conv_content($board['bo_content'], 1); ?></div>
        <?php } ?>
        <div class="faq-board__info board-header__info" id="bo_list_total">
            <span class="board-header__count">Total <strong><?php echo number_format($total_count) ?></strong>건</span>
            <span class="board-header__page"><?php echo $page ?> 페이지</span>
        </div>
    </header>

    <?php if ($is_category) { ?>
    <nav class="faq-category board-cate" id="bo_cate">
        <h2 class="sound_only"><?php echo $board['bo_subject'] ?> 카테고리</h2>
        <ul id="bo_cate_ul"><?php echo $category_option ?></ul>
    </nav>
    <?php } ?>

    <form name="fboardlist" id="fboardlist" class="faq-board__form" action="<?php echo G5_BBS_URL; ?>/board_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="spt" value="<?php echo $spt ?>">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="sw" value="">

    <div class="faq-actions board-header" id="bo_btn_top">
        <ul class="board-actions btn_bo_user">
            <?php if ($admin_href) { ?><li><a href="<?php echo $admin_href ?>" class="btn_admin btn" title="관리자"><i class="fa fa-cog fa-spin fa-fw" aria-hidden="true"></i><span class="sound_only">관리자</span></a></li><?php } ?>
            <?php if ($rss_href) { ?><li><a href="<?php echo $rss_href ?>" class="btn_b01 btn" title="RSS"><i class="fa fa-rss" aria-hidden="true"></i><span class="sound_only">RSS</span></a></li><?php } ?>
            <li><button type="button" class="btn_bo_sch btn_b01 btn faq-search__open" title="검색"><i class="fa fa-search" aria-hidden="true"></i><span class="sound_only">검색</span></button></li>
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="board-actions__write btn_b01 btn" title="글쓰기"><i class="fa fa-pencil" aria-hidden="true"></i><span>글쓰기</span></a></li><?php } ?>
            <?php if ($is_admin == 'super' || $is_auth) { ?>
            <li>
                <button type="button" class="btn_more_opt is_list_btn btn_b01 btn" title="옵션"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></button>
                <?php if ($is_checkbox) { ?>
                <ul class="more_opt is_list_btn">
                    <li><button type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.value"><i class="fa fa-trash-o" aria-hidden="true"></i> 선택삭제</button></li>
                    <li><button type="submit" name="btn_submit" value="선택복사" onclick="document.pressed=this.value"><i class="fa fa-files-o" aria-hidden="true"></i> 선택복사</button></li>
                    <li><button type="submit" name="btn_submit" value="선택이동" onclick="document.pressed=this.value"><i class="fa fa-arrows" aria-hidden="true"></i> 선택이동</button></li>
                </ul>
                <?php } ?>
            </li>
            <?php } ?>
        </ul>
    </div>

    <?php if ($is_checkbox) { ?>
    <div class="faq-board__chkall all_chk chk_box">
        <input type="checkbox" id="chkall" onclick="if (this.checked) all_checked(true); else all_checked(false);" class="selec_chk">
        <label for="chkall"><span></span><b>전체선택</b></label>
    </div>
    <?php } ?>

    <?php if ($stx) { ?>
    <p class="faq-board__search-result" role="status">「<?php echo get_text(stripslashes($stx)) ?>」 검색 결과</p>
    <?php } ?>

    <div class="faq-list" role="list" data-accordion-mode="multiple">
        <?php if (count($list) == 0) { ?>
        <p class="faq-board__empty board-list__empty">등록된 FAQ가 없습니다.</p>
        <?php } else {
            for ($i = 0; $i < count($list); $i++) {
                $is_secret = g5b_faq_is_secret($list[$i]);
                $faq_q = g5b_faq_question_text($list[$i]);
                $faq_a_html = g5b_faq_answer_html($list[$i]);
                $faq_a_plain = g5b_faq_answer_plain($list[$i], 0);
                $item_id = 'faq-answer-'.$list[$i]['wr_id'];
                $btn_id = 'faq-question-'.$list[$i]['wr_id'];
                $item_class = 'faq-item';
                if ($list[$i]['is_notice']) {
                    $item_class .= ' is-notice';
                }
                if ($is_secret) {
                    $item_class .= ' is-secret';
                }

                if (!$is_secret && $faq_q !== '' && $faq_a_plain !== '') {
                    $faq_schema_items[] = array(
                        'question' => $faq_q,
                        'answer'   => cut_str($faq_a_plain, 500, '…'),
                    );
                }
        ?>
        <article class="<?php echo $item_class ?>" role="listitem">
            <?php if ($is_checkbox) { ?>
            <div class="faq-item__chk chk_box">
                <input type="checkbox" name="chk_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>" class="selec_chk">
                <label for="chk_wr_id_<?php echo $i ?>"><span></span><b class="sound_only"><?php echo $list[$i]['subject'] ?></b></label>
            </div>
            <?php } ?>

            <div class="faq-item__head">
                <?php if ($is_category && $list[$i]['ca_name']) { ?>
                <span class="faq-item__cate"><?php echo $list[$i]['ca_name'] ?></span>
                <?php } ?>
                <?php if ($list[$i]['is_notice']) { ?><span class="faq-item__badge board-badge board-badge--notice">공지</span><?php } ?>
                <button type="button" class="faq-question" id="<?php echo $btn_id ?>" aria-expanded="false" aria-controls="<?php echo $item_id ?>">
                    <span class="faq-question__label">Q.</span>
                    <span class="faq-question__text"><?php echo $list[$i]['subject'] ?></span>
                    <?php if ($list[$i]['icon_new']) { ?><span class="new_icon board-badge board-badge--new">N</span><?php } ?>
                    <?php if ($list[$i]['comment_cnt']) { ?><span class="cnt_cmt faq-item__cmt"><?php echo $list[$i]['wr_comment'] ?></span><?php } ?>
                    <?php if ($is_secret) { echo rtrim($list[$i]['icon_secret']); } ?>
                </button>
                <a href="<?php echo $list[$i]['href'] ?>" class="faq-item__view-link">답변 보기</a>
            </div>

            <div class="faq-answer" id="<?php echo $item_id ?>" role="region" aria-labelledby="<?php echo $btn_id ?>">
                <?php if ($is_secret) { ?>
                <p class="faq-answer__secret">비밀글입니다. 권한이 있는 경우 <a href="<?php echo $list[$i]['href'] ?>">내용보기</a>에서 확인할 수 있습니다.</p>
                <?php } elseif ($faq_a_html !== '') { ?>
                <div class="faq-answer__body">
                    <?php echo get_view_thumbnail($faq_a_html); ?>
                </div>
                <?php } else { ?>
                <p class="faq-answer__empty">목록에 답변이 표시되지 않습니다. 관리자에서 <strong>목록에서 내용 사용</strong>을 켜거나 <a href="<?php echo $list[$i]['href'] ?>">내용보기</a>에서 확인하세요.</p>
                <?php } ?>
                <footer class="faq-meta">
                    <?php $faq_item_ts = strtotime($list[$i]['wr_datetime']); ?>
                    <time<?php if ($faq_item_ts) { ?> datetime="<?php echo date('c', $faq_item_ts); ?>"<?php } ?>><?php echo $list[$i]['datetime2'] ?></time>
                    <span class="faq-meta__hit"><i class="fa fa-eye" aria-hidden="true"></i> <?php echo number_format($list[$i]['wr_hit']) ?></span>
                    <span class="sound_only">작성자</span>
                    <span class="faq-meta__author"><?php echo $list[$i]['name'] ?></span>
                </footer>
            </div>
        </article>
        <?php }
        } ?>
    </div>

    <nav class="faq-board__paging board-paging" aria-label="게시판 페이지"><?php echo $write_pages; ?></nav>

    <?php if ($list_href || $is_checkbox || $write_href) { ?>
    <footer class="faq-board__footer board-footer bo_fx">
        <?php if ($list_href || $write_href) { ?>
        <ul class="faq-actions board-actions btn_bo_user">
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="board-actions__write btn_b01 btn"><i class="fa fa-pencil" aria-hidden="true"></i><span>글쓰기</span></a></li><?php } ?>
        </ul>
        <?php } ?>
    </footer>
    <?php } ?>
    </form>

    <div class="faq-search board-search bo_sch_wrap">
        <fieldset class="bo_sch board-search__panel">
            <h3 class="board-search__title">검색</h3>
            <form name="fsearch" method="get">
            <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
            <input type="hidden" name="sca" value="<?php echo $sca ?>">
            <input type="hidden" name="sop" value="and">
            <label for="sfl" class="sound_only">검색대상</label>
            <select name="sfl" id="sfl" class="board-search__select">
                <?php echo get_board_sfl_select_options($sfl); ?>
            </select>
            <label for="stx" class="sound_only">검색어</label>
            <div class="sch_bar board-search__bar">
                <input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" required id="stx" class="sch_input board-search__input" maxlength="20" placeholder="검색어를 입력해주세요">
                <button type="submit" class="sch_btn board-search__submit"><i class="fa fa-search" aria-hidden="true"></i><span class="sound_only">검색</span></button>
            </div>
            <button type="button" class="bo_sch_cls board-search__close" title="닫기"><i class="fa fa-times" aria-hidden="true"></i></button>
            </form>
        </fieldset>
        <div class="bo_sch_bg board-search__backdrop"></div>
    </div>

    <?php
    if (!empty($faq_schema_items)) {
        g5b_faq_print_schema($faq_schema_items);
    }
    ?>
</div>

<script>
jQuery(function($){
    $(".btn_bo_sch, .faq-search__open").on("click", function() {
        $(".bo_sch_wrap").toggle();
    });
    $('.bo_sch_bg, .bo_sch_cls').click(function(){
        $('.bo_sch_wrap').hide();
    });
});
</script>

<?php if ($is_checkbox) { ?>
<noscript>
<p>자바스크립트를 사용하지 않는 경우 별도의 확인 절차 없이 바로 선택삭제 처리하므로 주의하시기 바랍니다.</p>
</noscript>
<script>
function all_checked(sw) {
    var f = document.fboardlist;
    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]")
            f.elements[i].checked = sw;
    }
}
function fboardlist_submit(f) {
    var chk_count = 0;
    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]" && f.elements[i].checked)
            chk_count++;
    }
    if (!chk_count) {
        alert(document.pressed + "할 게시물을 하나 이상 선택하세요.");
        return false;
    }
    if(document.pressed == "선택복사") {
        select_copy("copy");
        return;
    }
    if(document.pressed == "선택이동") {
        select_copy("move");
        return;
    }
    if(document.pressed == "선택삭제") {
        if (!confirm("선택한 게시물을 정말 삭제하시겠습니까?")) return false;
        f.removeAttribute("target");
        f.action = g5_bbs_url+"/board_list_update.php";
    }
    return true;
}
function select_copy(sw) {
    var f = document.fboardlist;
    var sub_win = window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1");
    f.sw.value = sw;
    f.target = "move";
    f.action = g5_bbs_url+"/move.php";
    f.submit();
}
jQuery(function($){
    $(".btn_more_opt.is_list_btn").on("click", function(e) {
        e.stopPropagation();
        $(".more_opt.is_list_btn").toggle();
    });
    $(document).on("click", function (e) {
        if(!$(e.target).closest('.is_list_btn').length) $(".more_opt.is_list_btn").hide();
    });
});
</script>
<?php } ?>
