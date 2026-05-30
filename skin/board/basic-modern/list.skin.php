<?php
if (!defined('_GNUBOARD_')) exit;

$colspan = 5;
if ($is_checkbox) $colspan++;
if ($is_good) $colspan++;
if ($is_nogood) $colspan++;

include_once(G5_SKIN_PATH.'/board/_inc/g5b-seo-list.php');

add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>

<div class="board-wrap board-wrap--basic-modern" id="bo_list" style="width:<?php echo $width; ?>">

    <?php if ($is_category) { ?>
    <nav class="board-cate board-cate--modern" id="bo_cate">
        <h2 class="sound_only"><?php echo $board['bo_subject'] ?> 카테고리</h2>
        <ul id="bo_cate_ul"><?php echo $category_option ?></ul>
    </nav>
    <?php } ?>

    <form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="spt" value="<?php echo $spt ?>">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="sw" value="">

    <header class="board-header board-header--modern" id="bo_btn_top">
        <div class="board-header__info" id="bo_list_total">
            <?php g5b_seo_list_h1($board, 'board-header__title board-list__h1'); ?>
            <span class="board-header__count">Total <strong><?php echo number_format($total_count) ?></strong>건</span>
            <span class="board-header__page"><?php echo $page ?> 페이지</span>
        </div>
        <ul class="board-actions btn_bo_user">
            <?php if ($admin_href) { ?><li><a href="<?php echo $admin_href ?>" class="btn_admin btn" title="관리자"><i class="fa fa-cog fa-spin fa-fw" aria-hidden="true"></i><span class="sound_only">관리자</span></a></li><?php } ?>
            <?php if ($rss_href) { ?><li><a href="<?php echo $rss_href ?>" class="btn_b01 btn" title="RSS"><i class="fa fa-rss" aria-hidden="true"></i><span class="sound_only">RSS</span></a></li><?php } ?>
            <li><button type="button" class="btn_bo_sch btn_b01 btn" title="게시판 검색"><i class="fa fa-search" aria-hidden="true"></i><span class="sound_only">게시판 검색</span></button></li>
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="board-actions__write btn_b01 btn" title="글쓰기"><i class="fa fa-pencil" aria-hidden="true"></i><span>글쓰기</span><span class="sound_only">글쓰기</span></a></li><?php } ?>
            <?php if ($is_admin == 'super' || $is_auth) { ?>
            <li>
                <button type="button" class="btn_more_opt is_list_btn btn_b01 btn" title="게시판 리스트 옵션"><i class="fa fa-ellipsis-v" aria-hidden="true"></i><span class="sound_only">게시판 리스트 옵션</span></button>
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
    </header>

    <div class="board-list board-list--modern tbl_head01 tbl_wrap">
        <table>
        <caption class="sound_only"><?php echo $board['bo_subject'] ?> 목록</caption>
        <thead>
        <tr>
            <?php if ($is_checkbox) { ?>
            <th scope="col" class="all_chk chk_box">
                <input type="checkbox" id="chkall" onclick="if (this.checked) all_checked(true); else all_checked(false);" class="selec_chk">
                <label for="chkall"><span></span><b class="sound_only">전체선택</b></label>
            </th>
            <?php } ?>
            <th scope="col" class="board-list__col-date"><?php echo subject_sort_link('wr_datetime', $qstr2, 1) ?>날짜</a></th>
            <?php if ($is_category) { ?><th scope="col" class="board-list__col-cate">분류</th><?php } ?>
            <th scope="col" class="board-list__col-subject">제목</th>
            <th scope="col" class="board-list__col-name">글쓴이</th>
            <th scope="col" class="board-list__col-hit"><?php echo subject_sort_link('wr_hit', $qstr2, 1) ?>조회</a></th>
            <?php if ($is_good) { ?><th scope="col" class="board-list__col-good"><?php echo subject_sort_link('wr_good', $qstr2, 1) ?>추천</a></th><?php } ?>
            <?php if ($is_nogood) { ?><th scope="col" class="board-list__col-nogood"><?php echo subject_sort_link('wr_nogood', $qstr2, 1) ?>비추천</a></th><?php } ?>
        </tr>
        </thead>
        <tbody>
        <?php
        for ($i=0; $i<count($list); $i++) {
            $lt_class = ($i % 2 == 0) ? 'even' : '';
        ?>
        <tr class="board-list__row <?php if ($list[$i]['is_notice']) echo 'bo_notice board-list__row--notice'; ?> <?php echo $lt_class ?>">
            <?php if ($is_checkbox) { ?>
            <td class="td_chk chk_box" data-label="">
                <input type="checkbox" name="chk_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>" class="selec_chk">
                <label for="chk_wr_id_<?php echo $i ?>"><span></span><b class="sound_only"><?php echo $list[$i]['subject'] ?></b></label>
            </td>
            <?php } ?>
            <td class="td_datetime board-list__cell-date" data-label="날짜">
                <?php echo g5b_seo_list_time($list[$i]); ?>
                <?php if ($list[$i]['is_notice']) { ?><span class="notice_icon board-badge board-badge--notice">공지</span><?php } ?>
                <?php if (!$list[$i]['is_notice'] && $wr_id == $list[$i]['wr_id']) { ?><span class="bo_current">열람중</span><?php } ?>
            </td>
            <?php if ($is_category) { ?>
            <td class="board-list__cell-cate" data-label="분류">
                <?php if ($list[$i]['ca_name']) { ?>
                <a href="<?php echo $list[$i]['ca_name_href'] ?>" class="bo_cate_link board-list__cate"><?php echo $list[$i]['ca_name'] ?></a>
                <?php } else { ?><span class="board-list__cate-empty">—</span><?php } ?>
            </td>
            <?php } ?>
            <td class="td_subject board-list__cell-subject" data-label="제목" style="padding-left:<?php echo $list[$i]['reply'] ? (strlen($list[$i]['wr_reply'])*10) : '0'; ?>px">
                <div class="bo_tit board-title board-title--modern">
                    <h3 class="board-title__heading">
                    <a href="<?php echo $list[$i]['href'] ?>" class="board-title__link">
                        <?php echo $list[$i]['icon_reply'] ?>
                        <?php if (isset($list[$i]['icon_secret'])) echo rtrim($list[$i]['icon_secret']); ?>
                        <span class="board-title__text"><?php echo $list[$i]['subject'] ?></span>
                    </a>
                    </h3>
                    <?php
                    if ($list[$i]['icon_new']) echo '<span class="new_icon board-badge board-badge--new">N<span class="sound_only">새글</span></span>';
                    if (isset($list[$i]['icon_hot'])) echo rtrim($list[$i]['icon_hot']);
                    if (isset($list[$i]['icon_file'])) echo rtrim($list[$i]['icon_file']);
                    if (isset($list[$i]['icon_link'])) echo rtrim($list[$i]['icon_link']);
                    ?>
                    <?php if ($list[$i]['comment_cnt']) { ?>
                    <span class="cnt_cmt board-list__cmt"><?php echo $list[$i]['wr_comment']; ?></span>
                    <?php } ?>
                </div>
            </td>
            <td class="td_name sv_use board-list__cell-name" data-label="글쓴이"><?php echo $list[$i]['name'] ?></td>
            <td class="td_num board-list__cell-hit" data-label="조회"><?php echo $list[$i]['wr_hit'] ?></td>
            <?php if ($is_good) { ?><td class="td_num board-list__cell-good" data-label="추천"><?php echo $list[$i]['wr_good'] ?></td><?php } ?>
            <?php if ($is_nogood) { ?><td class="td_num board-list__cell-nogood" data-label="비추천"><?php echo $list[$i]['wr_nogood'] ?></td><?php } ?>
        </tr>
        <?php } ?>
        <?php if (count($list) == 0) { echo '<tr><td colspan="'.$colspan.'" class="empty_table board-list__empty">게시물이 없습니다.</td></tr>'; } ?>
        </tbody>
        </table>
    </div>

    <nav class="board-paging" aria-label="게시판 페이지"><?php echo $write_pages; ?></nav>

    <?php if ($list_href || $is_checkbox || $write_href) { ?>
    <footer class="board-footer bo_fx">
        <?php if ($list_href || $write_href) { ?>
        <ul class="board-actions btn_bo_user">
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="board-actions__write btn_b01 btn" title="글쓰기"><i class="fa fa-pencil" aria-hidden="true"></i><span>글쓰기</span></a></li><?php } ?>
        </ul>
        <?php } ?>
    </footer>
    <?php } ?>
    </form>

    <div class="board-search bo_sch_wrap">
        <fieldset class="bo_sch board-search__panel">
            <h3 class="board-search__title">검색</h3>
            <form name="fsearch" method="get">
            <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
            <input type="hidden" name="sca" value="<?php echo $sca ?>">
            <input type="hidden" name="sop" value="and">
            <select name="sfl" id="sfl" class="board-search__select"><?php echo get_board_sfl_select_options($sfl); ?></select>
            <div class="sch_bar board-search__bar">
                <input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" required id="stx" class="sch_input board-search__input" maxlength="20" placeholder="검색어를 입력해주세요">
                <button type="submit" class="sch_btn board-search__submit"><i class="fa fa-search" aria-hidden="true"></i><span class="sound_only">검색</span></button>
            </div>
            <button type="button" class="bo_sch_cls board-search__close" title="닫기"><i class="fa fa-times" aria-hidden="true"></i></button>
            </form>
        </fieldset>
        <div class="bo_sch_bg board-search__backdrop"></div>
    </div>
    <script>
    jQuery(function($){
        $(".btn_bo_sch").on("click", function() { $(".bo_sch_wrap").toggle(); });
        $('.bo_sch_bg, .bo_sch_cls').click(function(){ $('.bo_sch_wrap').hide(); });
    });
    </script>
</div>

<?php if ($is_checkbox) { ?>
<noscript><p>자바스크립트 미사용 시 선택삭제가 즉시 처리됩니다.</p></noscript>
<script>
function all_checked(sw) {
    var f = document.fboardlist;
    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]") f.elements[i].checked = sw;
    }
}
function fboardlist_submit(f) {
    var chk_count = 0;
    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]" && f.elements[i].checked) chk_count++;
    }
    if (!chk_count) { alert(document.pressed + "할 게시물을 하나 이상 선택하세요."); return false; }
    if(document.pressed == "선택복사") { select_copy("copy"); return; }
    if(document.pressed == "선택이동") { select_copy("move"); return; }
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
    f.sw.value = sw; f.target = "move"; f.action = g5_bbs_url+"/move.php"; f.submit();
}
jQuery(function($){
    $(".btn_more_opt.is_list_btn").on("click", function(e) { e.stopPropagation(); $(".more_opt.is_list_btn").toggle(); });
    $(document).on("click", function (e) {
        if(!$(e.target).closest('.is_list_btn').length) $(".more_opt.is_list_btn").hide();
    });
});
</script>
<?php } ?>
