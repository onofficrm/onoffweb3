<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once dirname(__FILE__) . '/inquiry-helper.php';

add_stylesheet('<link rel="stylesheet" href="' . $board_skin_url . '/style.css">', 0);

$colspan = 8;
if ($is_checkbox) {
    $colspan++;
}
?>

<div class="inquiry-board inquiry-list" id="bo_list" style="width:<?php echo $width; ?>">

    <?php if ($is_category) { ?>
    <nav class="inquiry-list__cate" id="bo_cate">
        <h2 class="sound_only"><?php echo $board['bo_subject'] ?> 분류</h2>
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

    <header class="inquiry-list__head" id="bo_btn_top">
        <div class="inquiry-list__summary" id="bo_list_total">
            <h2 class="inquiry-list__title"><?php echo get_text($board['bo_subject']) ?> <span class="inquiry-list__badge">문의 관리</span></h2>
            <p class="inquiry-list__count">총 <strong><?php echo number_format($total_count) ?></strong>건 · <?php echo $page ?> 페이지</p>
            <p class="inquiry-list__privacy-hint privacy-warning">개인정보가 포함된 목록입니다. 권한이 없는 계정에는 목록·읽기 권한을 부여하지 마세요.</p>
        </div>
        <ul class="inquiry-list__actions btn_bo_user">
            <?php if ($admin_href) { ?><li><a href="<?php echo $admin_href ?>" class="btn_admin btn" title="관리자"><i class="fa fa-cog fa-spin fa-fw" aria-hidden="true"></i><span class="sound_only">관리자</span></a></li><?php } ?>
            <?php if ($rss_href) { ?><li><a href="<?php echo $rss_href ?>" class="btn_b01 btn" title="RSS"><i class="fa fa-rss" aria-hidden="true"></i><span class="sound_only">RSS</span></a></li><?php } ?>
            <li>
                <button type="button" class="btn_bo_sch btn_b01 btn" title="검색"><i class="fa fa-search" aria-hidden="true"></i><span class="sound_only">검색</span></button>
            </li>
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b01 btn" title="문의 등록"><i class="fa fa-pencil" aria-hidden="true"></i><span>문의 등록</span><span class="sound_only">글쓰기</span></a></li><?php } ?>
            <?php if ($is_admin == 'super' || $is_auth) { ?>
            <li>
                <button type="button" class="btn_more_opt is_list_btn btn_b01 btn" title="옵션"><i class="fa fa-ellipsis-v" aria-hidden="true"></i><span class="sound_only">옵션</span></button>
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

    <div class="inquiry-table-wrap tbl_head01 tbl_wrap">
        <table class="inquiry-table">
        <caption class="sound_only"><?php echo $board['bo_subject'] ?> 문의 목록</caption>
        <thead>
        <tr>
            <?php if ($is_checkbox) { ?>
            <th scope="col" class="inquiry-table__chk all_chk chk_box">
                <input type="checkbox" id="chkall" onclick="if (this.checked) all_checked(true); else all_checked(false);" class="selec_chk">
                <label for="chkall"><span></span><b class="sound_only">전체선택</b></label>
            </th>
            <?php } ?>
            <th scope="col" class="inquiry-table__status">상태</th>
            <th scope="col" class="inquiry-table__name">문의자</th>
            <th scope="col" class="inquiry-table__phone">연락처</th>
            <th scope="col" class="inquiry-table__email">이메일</th>
            <th scope="col" class="inquiry-table__subject">제목</th>
            <th scope="col" class="inquiry-table__page">접수 페이지</th>
            <th scope="col" class="inquiry-table__date"><?php echo subject_sort_link('wr_datetime', $qstr2, 1) ?>접수일</a></th>
            <th scope="col" class="inquiry-table__ip">IP</th>
        </tr>
        </thead>
        <tbody>
        <?php
        for ($i = 0; $i < count($list); $i++) {
            $lt_class = ($i % 2 === 0) ? 'even' : '';
            $status_raw = g5b_inquiry_get_extra($list[$i], 'wr_6');
            $status_label = g5b_inquiry_status_label($status_raw);
            $status_class = g5b_inquiry_status_class($status_raw);
            $is_new_status = ($status_label === '신규');
            $phone = g5b_inquiry_get_extra($list[$i], 'wr_1');
            $email = g5b_inquiry_get_extra($list[$i], 'wr_2');
            $page_url = g5b_inquiry_get_extra($list[$i], 'wr_3');
            $ip = g5b_inquiry_get_extra($list[$i], 'wr_5');
            if ($ip === '' && !empty($list[$i]['wr_ip'])) {
                $ip = $list[$i]['wr_ip'];
            }
            $tel_href = g5b_inquiry_phone_tel($phone);
            $row_class = 'inquiry-table__row';
            if ($list[$i]['is_notice']) {
                $row_class .= ' inquiry-table__row--notice';
            }
            if ($is_new_status && !$list[$i]['is_notice']) {
                $row_class .= ' inquiry-table__row--new';
            }
        ?>
        <tr class="<?php echo $row_class ?> <?php echo $lt_class ?>">
            <?php if ($is_checkbox) { ?>
            <td class="td_chk chk_box inquiry-table__cell-chk" data-label="">
                <input type="checkbox" name="chk_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>" class="selec_chk">
                <label for="chk_wr_id_<?php echo $i ?>"><span></span><b class="sound_only"><?php echo $list[$i]['subject'] ?></b></label>
            </td>
            <?php } ?>
            <td class="inquiry-table__cell-status" data-label="상태">
                <span class="inquiry-status <?php echo $status_class ?>"><?php echo get_text($status_label) ?></span>
            </td>
            <td class="inquiry-table__cell-name" data-label="문의자">
                <?php echo $list[$i]['name'] ?>
            </td>
            <td class="inquiry-table__cell-phone" data-label="연락처">
                <?php if ($tel_href !== '') { ?>
                <a href="<?php echo htmlspecialchars($tel_href, ENT_QUOTES, 'UTF-8') ?>" class="inquiry-table__tel"><?php echo get_text(g5b_inquiry_mask_phone($phone)) ?></a>
                <?php } else { ?>
                <span class="inquiry-table__tel-text"><?php echo get_text(g5b_inquiry_mask_phone($phone)) ?></span>
                <?php } ?>
            </td>
            <td class="inquiry-table__cell-email" data-label="이메일">
                <?php if ($email !== '') { ?>
                <span class="inquiry-table__email"><?php echo get_text(g5b_inquiry_short_text($email, 28)) ?></span>
                <?php } else { ?>
                <span class="inquiry-table__muted">—</span>
                <?php } ?>
            </td>
            <td class="inquiry-table__cell-subject" data-label="제목">
                <?php if ($is_category && $list[$i]['ca_name']) { ?>
                <a href="<?php echo $list[$i]['ca_name_href'] ?>" class="bo_cate_link"><?php echo $list[$i]['ca_name'] ?></a>
                <?php } ?>
                <div class="inquiry-table__subject-inner">
                    <a href="<?php echo $list[$i]['href'] ?>" class="inquiry-table__subject-link">
                        <?php if ($list[$i]['is_notice']) { ?><span class="inquiry-table__notice">공지</span><?php } ?>
                        <?php echo $list[$i]['icon_reply'] ?>
                        <?php if (isset($list[$i]['icon_secret'])) {
                            echo rtrim($list[$i]['icon_secret']);
                        } ?>
                        <span><?php echo $list[$i]['subject'] ?></span>
                    </a>
                    <?php
                    if ($list[$i]['icon_new']) {
                        echo '<span class="new_icon">N<span class="sound_only">새글</span></span>';
                    }
                    if (isset($list[$i]['icon_hot'])) {
                        echo rtrim($list[$i]['icon_hot']);
                    }
                    if ($list[$i]['comment_cnt']) {
                        echo '<span class="cnt_cmt">+' . $list[$i]['wr_comment'] . '</span>';
                    }
                    ?>
                </div>
            </td>
            <td class="inquiry-table__cell-page" data-label="접수 페이지">
                <?php
                $page_short = g5b_inquiry_short_text($page_url, 36);
                $page_href = g5b_inquiry_safe_url($page_url);
                if ($page_href !== '') {
                    echo '<a href="' . htmlspecialchars($page_href, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener noreferrer" title="' . htmlspecialchars($page_url, ENT_QUOTES, 'UTF-8') . '">' . get_text($page_short) . '</a>';
                } elseif ($page_short !== '') {
                    echo get_text($page_short);
                } else {
                    echo '<span class="inquiry-table__muted">—</span>';
                }
                ?>
            </td>
            <td class="inquiry-table__cell-date" data-label="접수일"><?php echo $list[$i]['datetime2'] ?></td>
            <td class="inquiry-table__cell-ip" data-label="IP">
                <span class="inquiry-table__ip"><?php echo $ip !== '' ? get_text($ip) : '—' ?></span>
            </td>
        </tr>
        <?php } ?>
        <?php if (count($list) === 0) { ?>
        <tr><td colspan="<?php echo $colspan ?>" class="empty_table">등록된 문의가 없습니다.</td></tr>
        <?php } ?>
        </tbody>
        </table>
    </div>

    <nav class="inquiry-list__paging" aria-label="페이지"><?php echo $write_pages; ?></nav>

    <?php if ($list_href || $is_checkbox || $write_href) { ?>
    <footer class="inquiry-list__foot bo_fx">
        <?php if ($list_href || $write_href) { ?>
        <ul class="btn_bo_user">
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b01 btn">문의 등록</a></li><?php } ?>
        </ul>
        <?php } ?>
    </footer>
    <?php } ?>
    </form>

    <div class="board-search bo_sch_wrap">
        <fieldset class="bo_sch">
            <h3>문의 검색</h3>
            <form name="fsearch" method="get">
            <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
            <input type="hidden" name="sca" value="<?php echo $sca ?>">
            <input type="hidden" name="sop" value="and">
            <label for="sfl" class="sound_only">검색대상</label>
            <select name="sfl" id="sfl"><?php echo get_board_sfl_select_options($sfl); ?></select>
            <label for="stx" class="sound_only">검색어</label>
            <div class="sch_bar">
                <input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" required id="stx" class="sch_input" size="25" maxlength="20" placeholder="이름, 연락처, 제목 등">
                <button type="submit" class="sch_btn"><i class="fa fa-search" aria-hidden="true"></i><span class="sound_only">검색</span></button>
            </div>
            <button type="button" class="bo_sch_cls" title="닫기"><i class="fa fa-times" aria-hidden="true"></i></button>
            </form>
        </fieldset>
        <div class="bo_sch_bg"></div>
    </div>
    <script>
    jQuery(function($){
        $(".btn_bo_sch").on("click", function() { $(".bo_sch_wrap").toggle(); });
        $('.bo_sch_bg, .bo_sch_cls').click(function(){ $('.bo_sch_wrap').hide(); });
    });
    </script>
</div>

<?php if ($is_checkbox) { ?>
<noscript><p>자바스크립트 미사용 시 선택삭제 확인 없이 처리됩니다.</p></noscript>
<script>
function all_checked(sw) {
    var f = document.fboardlist;
    for (var i = 0; i < f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]")
            f.elements[i].checked = sw;
    }
}
function fboardlist_submit(f) {
    var chk_count = 0;
    for (var i = 0; i < f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]" && f.elements[i].checked)
            chk_count++;
    }
    if (!chk_count) {
        alert(document.pressed + "할 게시물을 하나 이상 선택하세요.");
        return false;
    }
    if (document.pressed == "선택복사") { select_copy("copy"); return; }
    if (document.pressed == "선택이동") { select_copy("move"); return; }
    if (document.pressed == "선택삭제") {
        if (!confirm("선택한 문의를 삭제하시겠습니까?\n복구할 수 없습니다."))
            return false;
        f.removeAttribute("target");
        f.action = g5_bbs_url + "/board_list_update.php";
    }
    return true;
}
function select_copy(sw) {
    var f = document.fboardlist;
    var sub_win = window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1");
    f.sw.value = sw;
    f.target = "move";
    f.action = g5_bbs_url + "/move.php";
    f.submit();
}
jQuery(function($){
    $(".btn_more_opt.is_list_btn").on("click", function(e) {
        e.stopPropagation();
        $(".more_opt.is_list_btn").toggle();
    });
    $(document).on("click", function (e) {
        if (!$(e.target).closest('.is_list_btn').length) {
            $(".more_opt.is_list_btn").hide();
        }
    });
});
</script>
<?php } ?>
