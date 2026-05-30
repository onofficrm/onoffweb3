<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_LIB_PATH.'/thumbnail.lib.php');
include_once(G5_SKIN_PATH.'/board/_inc/g5b-thumb.php');

$gal_w = (int)$board['bo_gallery_width'] > 0 ? (int)$board['bo_gallery_width'] : 420;
$gal_h = (int)$board['bo_gallery_height'] > 0 ? (int)$board['bo_gallery_height'] : 0;

add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>

<div class="board-wrap board-wrap--gallery-masonry" id="bo_gall" style="width:<?php echo $width; ?>">

    <?php if ($is_category) { ?>
    <nav class="board-cate" id="bo_cate">
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

    <header class="board-header" id="bo_btn_top">
        <div class="board-header__info" id="bo_list_total">
            <span class="board-header__count">Total <strong><?php echo number_format($total_count) ?></strong>건</span>
            <span class="board-header__page"><?php echo $page ?> 페이지</span>
        </div>
        <ul class="board-actions btn_bo_user">
            <?php if ($admin_href) { ?><li><a href="<?php echo $admin_href ?>" class="btn_admin btn" title="관리자"><i class="fa fa-cog fa-spin fa-fw" aria-hidden="true"></i><span class="sound_only">관리자</span></a></li><?php } ?>
            <?php if ($rss_href) { ?><li><a href="<?php echo $rss_href ?>" class="btn_b01 btn" title="RSS"><i class="fa fa-rss" aria-hidden="true"></i><span class="sound_only">RSS</span></a></li><?php } ?>
            <li><button type="button" class="btn_bo_sch btn_b01 btn" title="검색"><i class="fa fa-search" aria-hidden="true"></i><span class="sound_only">검색</span></button></li>
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
    </header>

    <?php if ($is_checkbox) { ?>
    <div class="board-list__chkall all_chk chk_box" id="gall_allchk">
        <input type="checkbox" id="chkall" onclick="if (this.checked) all_checked(true); else all_checked(false);" class="selec_chk">
        <label for="chkall"><span></span><b>현재 페이지 전체선택</b></label>
    </div>
    <?php } ?>

    <div class="board-list board-list--gallery-masonry">
        <?php if (count($list) == 0) { ?>
        <p class="board-list__empty">게시물이 없습니다.</p>
        <?php } else { ?>
        <ul class="board-list__masonry" id="gall_ul">
        <?php for ($i=0; $i<count($list); $i++) {
            $is_secret = isset($list[$i]['wr_option']) && strstr($list[$i]['wr_option'], 'secret');
            $thumb_h_use = $gal_h > 0 ? $gal_h : 480;
            $thumb_html = g5b_list_thumb_html($bo_table, $list[$i]['wr_id'], $gal_w, $thumb_h_use, $list[$i]['subject'], $is_secret, $list[$i]['is_notice'], false);
            $row_class = 'board-list__masonry-item';
            if ($list[$i]['is_notice']) $row_class .= ' board-list__masonry-item--notice bo_notice';
            if ($wr_id && $wr_id == $list[$i]['wr_id']) $row_class .= ' board-list__masonry-item--current';
        ?>
            <li class="<?php echo $row_class ?>">
                <?php if ($is_checkbox) { ?>
                <div class="board-list__card-chk chk_box gall_chk">
                    <input type="checkbox" name="chk_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>" class="selec_chk">
                    <label for="chk_wr_id_<?php echo $i ?>"><span></span><b class="sound_only"><?php echo $list[$i]['subject'] ?></b></label>
                </div>
                <?php } ?>
                <a href="<?php echo $list[$i]['href'] ?>" class="board-list__masonry-link">
                    <span class="board-list__thumb-wrap board-list__thumb-wrap--masonry">
                        <?php echo run_replace('thumb_image_tag', $thumb_html, array('bo_table'=>$bo_table, 'wr_id'=>$list[$i]['wr_id'])); ?>
                    </span>
                    <span class="board-list__masonry-overlay">
                        <?php if ($is_category && $list[$i]['ca_name']) { ?>
                        <span class="bo_cate_link board-list__cate"><?php echo $list[$i]['ca_name'] ?></span>
                        <?php } ?>
                        <span class="board-title board-title--masonry">
                            <?php if (isset($list[$i]['icon_secret'])) echo rtrim($list[$i]['icon_secret']); ?>
                            <span class="board-title__text"><?php echo $list[$i]['subject'] ?></span>
                        </span>
                        <span class="board-list__masonry-meta">
                            <time><?php echo $list[$i]['datetime2'] ?></time>
                            <span><i class="fa fa-eye" aria-hidden="true"></i> <?php echo number_format($list[$i]['wr_hit']) ?></span>
                            <?php if ($list[$i]['comment_cnt']) { ?><span class="cnt_cmt board-list__cmt"><?php echo $list[$i]['wr_comment']; ?></span><?php } ?>
                        </span>
                    </span>
                </a>
            </li>
        <?php } ?>
        </ul>
        <?php } ?>
    </div>

    <nav class="board-paging" aria-label="게시판 페이지"><?php echo $write_pages; ?></nav>

    <?php if ($write_href) { ?>
    <footer class="board-footer bo_fx">
        <ul class="board-actions btn_bo_user">
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="board-actions__write btn_b01 btn"><i class="fa fa-pencil" aria-hidden="true"></i><span>글쓰기</span></a></li><?php } ?>
        </ul>
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
            <select name="sfl" id="sfl"><?php echo get_board_sfl_select_options($sfl); ?></select>
            <div class="sch_bar board-search__bar">
                <input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" required id="stx" class="sch_input" maxlength="20" placeholder="검색어를 입력해주세요">
                <button type="submit" class="sch_btn"><i class="fa fa-search" aria-hidden="true"></i></button>
            </div>
            <button type="button" class="bo_sch_cls board-search__close"><i class="fa fa-times" aria-hidden="true"></i></button>
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
    window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1);
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
