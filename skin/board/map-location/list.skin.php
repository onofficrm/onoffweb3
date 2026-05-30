<?php
if (!defined('_GNUBOARD_')) exit;

add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>

<div class="board-wrap board-wrap--map-location" id="bo_list" style="width:<?php echo $width; ?>">

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
            <?php if ($admin_href) { ?><li><a href="<?php echo $admin_href ?>" class="btn_admin btn" title="관리자"><i class="fa fa-cog" aria-hidden="true"></i></a></li><?php } ?>
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b01 btn" title="장소 등록"><i class="fa fa-pencil" aria-hidden="true"></i> 장소 등록</a></li><?php } ?>
        </ul>
    </header>

    <div class="map-location-cards">
        <?php
        for ($i = 0; $i < count($list); $i++) {
            $loc_cat = isset($list[$i]['wr_1']) ? get_text($list[$i]['wr_1']) : '';
            $loc_addr = isset($list[$i]['wr_2']) ? get_text($list[$i]['wr_2']) : '';
            $loc_phone = isset($list[$i]['wr_5']) ? get_text($list[$i]['wr_5']) : '';
            $loc_region = isset($list[$i]['wr_9']) ? get_text($list[$i]['wr_9']) : '';
            $map_href = G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&amp;wr_id='.$list[$i]['wr_id'];
        ?>
        <article class="map-location-card <?php if ($list[$i]['is_notice']) echo 'map-location-card--notice'; ?>">
            <?php if ($is_checkbox) { ?>
            <input type="checkbox" name="chk_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>" class="selec_chk">
            <?php } ?>
            <h3 class="map-location-card__title">
                <a href="<?php echo $list[$i]['href'] ?>"><?php echo $list[$i]['subject'] ?></a>
            </h3>
            <p class="map-location-card__meta">
                <?php if ($loc_cat) { ?><span><?php echo $loc_cat; ?></span><?php } ?>
                <?php if ($loc_region) { ?> · <span><?php echo $loc_region; ?></span><?php } ?>
            </p>
            <?php if ($loc_addr) { ?><p class="map-location-card__meta"><?php echo $loc_addr; ?></p><?php } ?>
            <?php if ($loc_phone) { ?><p class="map-location-card__meta"><?php echo $loc_phone; ?></p><?php } ?>
            <div class="map-location-card__actions">
                <a href="<?php echo $list[$i]['href'] ?>" class="btn_b01 btn btn-sm">상세보기</a>
                <a href="<?php echo $map_href ?>#map-location-view-map" class="btn_b01 btn btn-sm">지도 보기</a>
            </div>
        </article>
        <?php } ?>
        <?php if (count($list) == 0) { ?>
        <p class="empty_table board-list__empty">등록된 장소가 없습니다.</p>
        <?php } ?>
    </div>

    <nav class="board-paging" aria-label="게시판 페이지"><?php echo $write_pages; ?></nav>

    <?php if ($list_href || $write_href) { ?>
    <footer class="board-footer bo_fx">
        <ul class="board-actions btn_bo_user">
            <?php if ($list_href) { ?><li><a href="<?php echo $list_href ?>" class="btn_b01 btn">목록</a></li><?php } ?>
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b01 btn">장소 등록</a></li><?php } ?>
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
            <select name="sfl" id="sfl" class="board-search__select"><?php echo get_board_sfl_select_options($sfl); ?></select>
            <input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" id="stx" class="sch_input board-search__input" maxlength="20" placeholder="장소명·주소 검색">
            <button type="submit" class="sch_btn board-search__submit"><i class="fa fa-search" aria-hidden="true"></i></button>
            </form>
        </fieldset>
    </div>
</div>
