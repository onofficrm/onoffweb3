<?php
if (!defined('_GNUBOARD_')) exit;

add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

$loc_cat    = isset($wr_1) ? get_text($wr_1) : '';
$loc_addr   = isset($wr_2) ? get_text($wr_2) : '';
$loc_lat    = isset($wr_3) ? get_text($wr_3) : '';
$loc_lng    = isset($wr_4) ? get_text($wr_4) : '';
$loc_phone  = isset($wr_5) ? get_text($wr_5) : '';
$loc_hours  = isset($wr_6) ? get_text($wr_6) : '';
$loc_link   = isset($wr_7) ? get_text($wr_7) : '';
$loc_tags   = isset($wr_8) ? get_text($wr_8) : '';
$loc_region = isset($wr_9) ? get_text($wr_9) : '';
$loc_extra  = isset($wr_10) ? get_text($wr_10) : '';
?>

<section class="board-wrap board-wrap--map-location board-write" id="bo_w" style="width:<?php echo $width; ?>">
    <h2 class="sound_only"><?php echo $g5['title'] ?></h2>
    <p class="board-write-form__hint map-location-write-hint">여분필드: wr_1 카테고리 · wr_2 주소 · wr_3 위도 · wr_4 경도 · wr_5 전화 · wr_6 영업시간 · wr_7 링크 · wr_8 태그 · wr_9 지역 · wr_10 예비</p>

    <form name="fwrite" id="fwrite" class="board-write-form" action="<?php echo $action_url ?>" onsubmit="return fwrite_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
    <input type="hidden" name="uid" value="<?php echo get_uniqid(); ?>">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="spt" value="<?php echo $spt ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <?php
    $option = '';
    $option_hidden = '';
    if ($is_notice || $is_html || $is_secret || $is_mail) {
        if ($is_notice) {
            $option .= '<li class="chk_box"><input type="checkbox" id="notice" name="notice" value="1" '.$notice_checked.'><label for="notice"><span></span>공지</label></li>';
        }
        if ($is_html && !$is_dhtml_editor) {
            $option .= '<li class="chk_box"><input type="checkbox" id="html" name="html" value="'.$html_value.'" '.$html_checked.'><label for="html"><span></span>html</label></li>';
        } elseif ($is_dhtml_editor) {
            $option_hidden .= '<input type="hidden" value="html1" name="html">';
        }
        if ($is_secret) {
            if ($is_admin || $is_secret == 1) {
                $option .= '<li class="chk_box"><input type="checkbox" id="secret" name="secret" value="secret" '.$secret_checked.'><label for="secret"><span></span>비밀글</label></li>';
            } else {
                $option_hidden .= '<input type="hidden" name="secret" value="secret">';
            }
        }
    }
    echo $option_hidden;
    ?>

    <?php if ($is_category) { ?>
    <div class="board-write-form__row">
        <label for="ca_name" class="board-write-form__label">분류</label>
        <select name="ca_name" id="ca_name" class="frm_input"><?php echo $category_option ?></select>
    </div>
    <?php } ?>

    <div class="board-write-form__row bo_w_tit">
        <label for="wr_subject" class="board-write-form__label">장소명<strong class="required">필수</strong></label>
        <input type="text" name="wr_subject" value="<?php echo $subject ?>" id="wr_subject" required class="frm_input full_input" maxlength="255" placeholder="장소명">
    </div>

    <div class="board-write-form__row">
        <label for="wr_content" class="board-write-form__label">설명</label>
        <div class="wr_content"><?php echo $editor_html; ?></div>
    </div>

    <div class="map-location-fields">
        <div class="board-write-form__row">
            <label for="wr_1" class="board-write-form__label">카테고리 (wr_1)</label>
            <input type="text" name="wr_1" value="<?php echo $loc_cat ?>" id="wr_1" class="frm_input full_input" maxlength="255" placeholder="병원, 학원, 음식점…">
        </div>
        <div class="board-write-form__row">
            <label for="wr_2" class="board-write-form__label">주소 (wr_2)</label>
            <input type="text" name="wr_2" value="<?php echo $loc_addr ?>" id="wr_2" class="frm_input full_input" maxlength="255">
        </div>
        <div class="map-location-fields map-location-fields--coords">
            <div class="board-write-form__row">
                <label for="wr_3" class="board-write-form__label">위도 (wr_3)</label>
                <input type="text" name="wr_3" value="<?php echo $loc_lat ?>" id="wr_3" class="frm_input full_input" maxlength="50" placeholder="10.3157">
            </div>
            <div class="board-write-form__row">
                <label for="wr_4" class="board-write-form__label">경도 (wr_4)</label>
                <input type="text" name="wr_4" value="<?php echo $loc_lng ?>" id="wr_4" class="frm_input full_input" maxlength="50" placeholder="123.8854">
            </div>
        </div>
        <div class="board-write-form__row">
            <label for="wr_5" class="board-write-form__label">전화번호 (wr_5)</label>
            <input type="text" name="wr_5" value="<?php echo $loc_phone ?>" id="wr_5" class="frm_input full_input" maxlength="50">
        </div>
        <div class="board-write-form__row">
            <label for="wr_6" class="board-write-form__label">영업시간 (wr_6)</label>
            <input type="text" name="wr_6" value="<?php echo $loc_hours ?>" id="wr_6" class="frm_input full_input" maxlength="255">
        </div>
        <div class="board-write-form__row">
            <label for="wr_7" class="board-write-form__label">홈페이지/상세링크 (wr_7)</label>
            <input type="url" name="wr_7" value="<?php echo $loc_link ?>" id="wr_7" class="frm_input full_input" maxlength="255" placeholder="https://">
        </div>
        <div class="board-write-form__row">
            <label for="wr_8" class="board-write-form__label">태그 (wr_8)</label>
            <input type="text" name="wr_8" value="<?php echo $loc_tags ?>" id="wr_8" class="frm_input full_input" maxlength="255" placeholder="쉼표로 구분">
        </div>
        <div class="board-write-form__row">
            <label for="wr_9" class="board-write-form__label">지역 (wr_9)</label>
            <input type="text" name="wr_9" value="<?php echo $loc_region ?>" id="wr_9" class="frm_input full_input" maxlength="255">
        </div>
        <div class="board-write-form__row">
            <label for="wr_10" class="board-write-form__label">예비 (wr_10)</label>
            <input type="text" name="wr_10" value="<?php echo $loc_extra ?>" id="wr_10" class="frm_input full_input" maxlength="255">
        </div>
    </div>

    <?php for ($i=0; $is_file && $i<$file_count; $i++) { ?>
    <div class="board-write-form__row bo_w_flie">
        <label for="bf_file_<?php echo $i+1 ?>" class="board-write-form__label">파일 <?php echo $i+1 ?></label>
        <input type="file" name="bf_file[]" id="bf_file_<?php echo $i+1 ?>" class="frm_file">
        <?php if ($w == 'u' && $file[$i]['file']) { ?>
        <span class="file_del">
            <input type="checkbox" id="bf_file_del<?php echo $i ?>" name="bf_file_del[<?php echo $i; ?>]" value="1">
            <label for="bf_file_del<?php echo $i ?>"><?php echo $file[$i]['source'].'('.$file[$i]['size'].')'; ?> 삭제</label>
        </span>
        <?php } ?>
    </div>
    <?php } ?>

    <?php if ($is_use_captcha) { ?><div class="board-write-form__row"><?php echo $captcha_html ?></div><?php } ?>

    <div class="btn_confirm write_div board-write-form__submit">
        <a href="<?php echo get_pretty_url($bo_table); ?>" class="btn_cancel btn">취소</a>
        <button type="submit" id="btn_submit" class="btn_submit btn">저장</button>
    </div>
    </form>

    <script>
    function fwrite_submit(f) {
        <?php echo $editor_js; ?>
        <?php echo $captcha_js; ?>
        document.getElementById("btn_submit").disabled = "disabled";
        return true;
    }
    </script>
</section>
