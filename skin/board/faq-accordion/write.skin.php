<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

/* 선택 여분필드 (필수 아님)
 * wr_1 — FAQ 요약·키워드
 * wr_2 — 정렬 우선순위 (숫자)
 */
$faq_keyword = isset($wr_1) ? get_text($wr_1) : '';
$faq_sort = isset($wr_2) ? get_text($wr_2) : '';
?>

<section class="faq-board faq-write-form board-wrap board-wrap--faq-accordion board-write" id="bo_w" style="width:<?php echo $width; ?>">
    <h2 class="sound_only"><?php echo $g5['title'] ?></h2>

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
            $option .= PHP_EOL.'<li class="chk_box"><input type="checkbox" id="notice" name="notice" class="selec_chk" value="1" '.$notice_checked.'><label for="notice"><span></span>공지</label></li>';
        }
        if ($is_html) {
            if ($is_dhtml_editor) {
                $option_hidden .= '<input type="hidden" value="html1" name="html">';
            } else {
                $option .= PHP_EOL.'<li class="chk_box"><input type="checkbox" id="html" name="html" onclick="html_auto_br(this);" class="selec_chk" value="'.$html_value.'" '.$html_checked.'><label for="html"><span></span>html</label></li>';
            }
        }
        if ($is_secret) {
            if ($is_admin || $is_secret == 1) {
                $option .= PHP_EOL.'<li class="chk_box"><input type="checkbox" id="secret" name="secret" class="selec_chk" value="secret" '.$secret_checked.'><label for="secret"><span></span>비밀글</label></li>';
            } else {
                $option_hidden .= '<input type="hidden" name="secret" value="secret">';
            }
        }
        if ($is_mail) {
            $option .= PHP_EOL.'<li class="chk_box"><input type="checkbox" id="mail" name="mail" class="selec_chk" value="mail" '.$recv_email_checked.'><label for="mail"><span></span>답변메일받기</label></li>';
        }
    }
    echo $option_hidden;
    ?>

    <fieldset class="faq-write-form__section faq-write-form__section--main">
        <legend class="faq-write-form__legend">질문 · 답변</legend>

        <?php if ($is_category) { ?>
        <div class="board-write-form__row bo_w_select write_div">
            <label for="ca_name" class="board-write-form__label">분류<strong class="required">필수</strong></label>
            <select name="ca_name" id="ca_name" class="frm_input" required>
                <option value="">분류를 선택하세요</option>
                <?php echo $category_option ?>
            </select>
        </div>
        <?php } ?>

        <div class="board-write-form__row bo_w_info write_div board-write-form__author">
            <?php if ($is_name) { ?>
            <div class="board-write-form__field">
                <input type="text" name="wr_name" value="<?php echo $name ?>" id="wr_name" required class="frm_input half_input required" placeholder="이름">
            </div>
            <?php } ?>
            <?php if ($is_password) { ?>
            <div class="board-write-form__field">
                <input type="password" name="wr_password" id="wr_password" <?php echo $password_required ?> class="frm_input half_input <?php echo $password_required ?>" placeholder="비밀번호">
            </div>
            <?php } ?>
            <?php if ($is_email) { ?>
            <div class="board-write-form__field">
                <input type="text" name="wr_email" value="<?php echo $email ?>" id="wr_email" class="frm_input half_input email" placeholder="이메일">
            </div>
            <?php } ?>
        </div>

        <div class="board-write-form__row bo_w_tit write_div">
            <label for="wr_subject" class="board-write-form__label">질문<strong class="required">필수</strong></label>
            <input type="text" name="wr_subject" value="<?php echo $subject ?>" id="wr_subject" required class="frm_input full_input required" maxlength="255" placeholder="자주 묻는 질문을 입력하세요">
        </div>

        <div class="board-write-form__row write_div board-write-form__content">
            <label for="wr_content" class="board-write-form__label">답변<strong class="required">필수</strong></label>
            <div class="wr_content <?php echo $is_dhtml_editor ? $config['cf_editor'] : ''; ?>">
                <?php if ($write_min || $write_max) { ?>
                <p id="char_count_desc" class="board-write-form__hint">최소 <strong><?php echo $write_min; ?></strong>글자, 최대 <strong><?php echo $write_max; ?></strong>글자</p>
                <?php } ?>
                <?php echo $editor_html; ?>
                <?php if ($write_min || $write_max) { ?>
                <div id="char_count_wrap"><span id="char_count"></span>글자</div>
                <?php } ?>
            </div>
        </div>
    </fieldset>

    <fieldset class="faq-write-form__section faq-write-form__section--seo">
        <legend class="faq-write-form__legend">SEO · 분류 옵션</legend>
        <?php if ($option) { ?>
        <div class="board-write-form__row write_div board-write-form__options">
            <ul class="bo_v_option"><?php echo $option ?></ul>
        </div>
        <?php } ?>
        <div class="board-write-form__row write_div">
            <label for="wr_1" class="board-write-form__label">FAQ 키워드 <span class="board-write-form__optional">(선택, wr_1)</span></label>
            <input type="text" name="wr_1" value="<?php echo $faq_keyword ?>" id="wr_1" class="frm_input full_input" maxlength="255" placeholder="검색·관리용 키워드">
        </div>
        <div class="board-write-form__row write_div">
            <label for="wr_2" class="board-write-form__label">정렬 우선순위 <span class="board-write-form__optional">(선택, wr_2)</span></label>
            <input type="text" name="wr_2" value="<?php echo $faq_sort ?>" id="wr_2" class="frm_input full_input" maxlength="50" placeholder="숫자가 작을수록 앞에 (미사용 시 무시)">
        </div>
    </fieldset>

    <fieldset class="faq-write-form__section faq-write-form__section--extra">
        <legend class="faq-write-form__legend">첨부 · 기타</legend>

        <?php for ($i=1; $is_link && $i<=G5_LINK_COUNT; $i++) { ?>
        <div class="board-write-form__row bo_w_link write_div">
            <label for="wr_link<?php echo $i ?>" class="board-write-form__label">링크 <?php echo $i ?></label>
            <input type="text" name="wr_link<?php echo $i ?>" value="<?php if ($w=='u') { echo $write['wr_link'.$i]; } ?>" id="wr_link<?php echo $i ?>" class="frm_input full_input" placeholder="https://">
        </div>
        <?php } ?>

        <?php for ($i=0; $is_file && $i<$file_count; $i++) { ?>
        <div class="board-write-form__row bo_w_flie write_div board-write-form__file">
            <label for="bf_file_<?php echo $i+1 ?>" class="board-write-form__label">파일 <?php echo $i+1 ?></label>
            <input type="file" name="bf_file[]" id="bf_file_<?php echo $i+1 ?>" class="frm_file" title="<?php echo $upload_max_filesize ?> 이하">
            <?php if ($w == 'u' && $file[$i]['file']) { ?>
            <span class="file_del">
                <input type="checkbox" id="bf_file_del<?php echo $i ?>" name="bf_file_del[<?php echo $i; ?>]" value="1">
                <label for="bf_file_del<?php echo $i ?>"><?php echo $file[$i]['source']; ?> 삭제</label>
            </span>
            <?php } ?>
        </div>
        <?php } ?>

        <?php if ($is_use_captcha) { ?>
        <div class="board-write-form__row write_div board-write-form__captcha">
            <?php echo $captcha_html ?>
        </div>
        <?php } ?>
    </fieldset>

    <div class="btn_confirm write_div board-write-form__submit faq-actions">
        <a href="<?php echo get_pretty_url($bo_table); ?>" class="btn_cancel btn">취소</a>
        <button type="submit" id="btn_submit" accesskey="s" class="btn_submit btn board-actions__write">작성완료</button>
    </div>
    </form>

    <script>
    <?php if ($write_min || $write_max) { ?>
    var char_min = parseInt(<?php echo $write_min; ?>);
    var char_max = parseInt(<?php echo $write_max; ?>);
    check_byte("wr_content", "char_count");
    $(function() {
        $("#wr_content").on("keyup", function() {
            check_byte("wr_content", "char_count");
        });
    });
    <?php } ?>
    function html_auto_br(obj) {
        if (obj.checked) {
            if (confirm("자동 줄바꿈을 하시겠습니까?")) obj.value = "html2";
            else obj.value = "html1";
        } else {
            obj.value = "";
        }
    }
    function fwrite_submit(f) {
        <?php echo $editor_js; ?>
        var subject = "", content = "";
        $.ajax({
            url: g5_bbs_url+"/ajax.filter.php",
            type: "POST",
            data: { "subject": f.wr_subject.value, "content": f.wr_content.value },
            dataType: "json", async: false, cache: false,
            success: function(data) { subject = data.subject; content = data.content; }
        });
        if (subject) { alert("제목에 금지단어('"+subject+"')가 포함되어있습니다"); f.wr_subject.focus(); return false; }
        if (content) {
            alert("내용에 금지단어('"+content+"')가 포함되어있습니다");
            if (typeof(ed_wr_content) != "undefined") ed_wr_content.returnFalse();
            else f.wr_content.focus();
            return false;
        }
        <?php echo $captcha_js; ?>
        document.getElementById("btn_submit").disabled = "disabled";
        return true;
    }
    </script>
</section>
