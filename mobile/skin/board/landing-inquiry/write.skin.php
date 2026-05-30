<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once dirname(__FILE__) . '/inquiry-helper.php';

add_stylesheet('<link rel="stylesheet" href="' . $board_skin_url . '/style.css">', 0);

$inquiry_phone   = isset($wr_1) ? get_text($wr_1) : '';
$inquiry_email   = isset($wr_2) ? get_text($wr_2) : '';
$inquiry_page    = isset($wr_3) ? get_text($wr_3) : '';
$inquiry_privacy = isset($wr_4) ? get_text($wr_4) : '';
$inquiry_ip      = isset($wr_5) ? get_text($wr_5) : '';
$inquiry_status  = isset($wr_6) ? get_text($wr_6) : '';
$inquiry_manager = isset($wr_7) ? get_text($wr_7) : '';
$inquiry_memo    = isset($wr_8) ? get_text($wr_8) : '';
$inquiry_source  = isset($wr_9) ? get_text($wr_9) : '';

if ($inquiry_status === '') {
    $inquiry_status = '신규';
}
if ($inquiry_ip === '' && $w === 'u' && isset($write['wr_ip'])) {
    $inquiry_ip = get_text($write['wr_ip']);
}
$status_options = g5b_inquiry_status_options();
?>

<section class="inquiry-board inquiry-form" id="bo_w" style="width:<?php echo $width; ?>">
    <h2 class="sound_only"><?php echo $g5['title'] ?></h2>
    <p class="privacy-warning inquiry-form__notice">문의·개인정보가 포함됩니다. 게시판 읽기·목록 권한은 관리자(운영 레벨)로 제한하세요.</p>

    <form name="fwrite" id="fwrite" class="inquiry-form__inner" action="<?php echo $action_url ?>" onsubmit="return fwrite_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
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
            $option .= PHP_EOL . '<li class="chk_box"><input type="checkbox" id="notice" name="notice" class="selec_chk" value="1" ' . $notice_checked . '><label for="notice"><span></span>공지</label></li>';
        }
        if ($is_html) {
            if ($is_dhtml_editor) {
                $option_hidden .= '<input type="hidden" value="html1" name="html">';
            } else {
                $option .= PHP_EOL . '<li class="chk_box"><input type="checkbox" id="html" name="html" onclick="html_auto_br(this);" class="selec_chk" value="' . $html_value . '" ' . $html_checked . '><label for="html"><span></span>html</label></li>';
            }
        }
        if ($is_secret) {
            if ($is_admin || $is_secret == 1) {
                $option .= PHP_EOL . '<li class="chk_box"><input type="checkbox" id="secret" name="secret" class="selec_chk" value="secret" ' . $secret_checked . '><label for="secret"><span></span>비밀글</label></li>';
            } else {
                $option_hidden .= '<input type="hidden" name="secret" value="secret">';
            }
        }
        if ($is_mail) {
            $option .= PHP_EOL . '<li class="chk_box"><input type="checkbox" id="mail" name="mail" class="selec_chk" value="mail" ' . $recv_email_checked . '><label for="mail"><span></span>답변메일받기</label></li>';
        }
    }
    echo $option_hidden;
    ?>

    <?php if ($is_category) { ?>
    <div class="inquiry-form__row write_div">
        <label for="ca_name" class="inquiry-form__label">분류<strong class="required">필수</strong></label>
        <select name="ca_name" id="ca_name" class="frm_input" required>
            <option value="">분류 선택</option>
            <?php echo $category_option ?>
        </select>
    </div>
    <?php } ?>

    <fieldset class="inquiry-form__section">
        <legend>기본 문의 정보</legend>

        <div class="inquiry-form__row write_div">
            <label for="wr_subject" class="inquiry-form__label">제목<strong class="required">필수</strong></label>
            <input type="text" name="wr_subject" value="<?php echo $subject ?>" id="wr_subject" required class="frm_input full_input required" maxlength="255" placeholder="문의 제목">
        </div>

        <div class="inquiry-form__row write_div inquiry-form__author">
            <?php if ($is_name) { ?>
            <div class="inquiry-form__field">
                <label for="wr_name" class="inquiry-form__label">이름<strong class="required">필수</strong></label>
                <input type="text" name="wr_name" value="<?php echo $name ?>" id="wr_name" required class="frm_input required" placeholder="문의자 이름">
            </div>
            <?php } ?>
            <?php if ($is_password) { ?>
            <div class="inquiry-form__field">
                <label for="wr_password" class="inquiry-form__label">비밀번호<?php echo $password_required ? '<strong class="required">필수</strong>' : '' ?></label>
                <input type="password" name="wr_password" id="wr_password" <?php echo $password_required ?> class="frm_input <?php echo $password_required ?>" placeholder="비밀번호">
            </div>
            <?php } ?>
            <?php if ($is_email) { ?>
            <div class="inquiry-form__field">
                <label for="wr_email" class="inquiry-form__label">이메일 (게시판)</label>
                <input type="email" name="wr_email" value="<?php echo $email ?>" id="wr_email" class="frm_input email" maxlength="100" placeholder="email@example.com" autocomplete="email">
            </div>
            <?php } ?>
        </div>

        <div class="inquiry-form__row write_div">
            <label for="wr_1" class="inquiry-form__label">연락처 (wr_1)</label>
            <input type="tel" name="wr_1" value="<?php echo $inquiry_phone ?>" id="wr_1" class="frm_input" maxlength="30" placeholder="010-0000-0000" autocomplete="tel">
        </div>

        <div class="inquiry-form__row write_div">
            <label for="wr_2" class="inquiry-form__label">이메일 (wr_2)</label>
            <input type="email" name="wr_2" value="<?php echo $inquiry_email ?>" id="wr_2" class="frm_input email" maxlength="100" placeholder="문의 폼과 동일하게 유지">
        </div>

        <div class="inquiry-form__row write_div">
            <label for="wr_3" class="inquiry-form__label">접수 페이지</label>
            <input type="text" name="wr_3" value="<?php echo $inquiry_page ?>" id="wr_3" class="frm_input full_input" maxlength="255" placeholder="/page/contact.php 또는 전체 URL">
        </div>

        <div class="inquiry-form__row write_div">
            <label for="wr_4" class="inquiry-form__label">개인정보 동의</label>
            <input type="text" name="wr_4" value="<?php echo $inquiry_privacy ?>" id="wr_4" class="frm_input" maxlength="20" placeholder="동의 / 미동의">
        </div>

        <div class="inquiry-form__row write_div">
            <label for="wr_5" class="inquiry-form__label">접수 IP</label>
            <input type="text" name="wr_5" value="<?php echo $inquiry_ip ?>" id="wr_5" class="frm_input" maxlength="50" placeholder="자동 기록" <?php echo $is_admin ? '' : 'readonly'; ?>>
        </div>
    </fieldset>

    <fieldset class="inquiry-form__section inquiry-form__section--admin">
        <legend>관리 정보</legend>
        <p class="inquiry-form__hint">상태·담당자·메모는 글 수정 후 저장됩니다. (별도 AJAX 없음)</p>

        <div class="inquiry-form__row write_div">
            <label for="wr_6" class="inquiry-form__label">문의 상태</label>
            <select name="wr_6" id="wr_6" class="frm_input">
                <?php foreach ($status_options as $opt) { ?>
                <option value="<?php echo htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>"<?php echo $inquiry_status === $opt ? ' selected' : '' ?>><?php echo htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="inquiry-form__row write_div">
            <label for="wr_7" class="inquiry-form__label">담당자</label>
            <input type="text" name="wr_7" value="<?php echo $inquiry_manager ?>" id="wr_7" class="frm_input" maxlength="50" placeholder="담당자 이름">
        </div>

        <div class="inquiry-form__row write_div">
            <label for="wr_8" class="inquiry-form__label">관리자 메모</label>
            <textarea name="wr_8" id="wr_8" class="frm_input full_input inquiry-admin-note" rows="4" placeholder="내부 메모 (고객에게 노출되지 않도록 주의)"><?php echo $inquiry_memo ?></textarea>
        </div>

        <div class="inquiry-form__row write_div">
            <label for="wr_9" class="inquiry-form__label">유입경로 / 캠페인</label>
            <input type="text" name="wr_9" value="<?php echo $inquiry_source ?>" id="wr_9" class="frm_input full_input" maxlength="100" placeholder="예: 네이버검색, 인스타광고">
        </div>

        <div class="inquiry-form__row write_div">
            <label for="wr_10" class="inquiry-form__label">예비 필드</label>
            <input type="text" name="wr_10" value="<?php echo isset($wr_10) ? get_text($wr_10) : '' ?>" id="wr_10" class="frm_input full_input" maxlength="255">
        </div>
    </fieldset>

    <?php if ($option) { ?>
    <div class="inquiry-form__row write_div">
        <span class="sound_only">옵션</span>
        <ul class="bo_v_option"><?php echo $option ?></ul>
    </div>
    <?php } ?>

    <fieldset class="inquiry-form__section">
        <legend>문의 내용</legend>
        <div class="inquiry-form__row write_div">
            <label for="wr_content" class="inquiry-form__label">내용<strong class="required">필수</strong></label>
            <div class="wr_content <?php echo $is_dhtml_editor ? $config['cf_editor'] : ''; ?>">
                <?php if ($write_min || $write_max) { ?>
                <p id="char_count_desc" class="inquiry-form__hint">최소 <strong><?php echo $write_min; ?></strong>글자, 최대 <strong><?php echo $write_max; ?></strong>글자</p>
                <?php } ?>
                <?php echo $editor_html; ?>
                <?php if ($write_min || $write_max) { ?>
                <div id="char_count_wrap"><span id="char_count"></span>글자</div>
                <?php } ?>
            </div>
        </div>
    </fieldset>

    <fieldset class="inquiry-form__section">
        <legend>첨부파일</legend>
        <?php for ($i = 1; $is_link && $i <= G5_LINK_COUNT; $i++) { ?>
        <div class="inquiry-form__row write_div">
            <label for="wr_link<?php echo $i ?>" class="inquiry-form__label">링크 <?php echo $i ?></label>
            <input type="text" name="wr_link<?php echo $i ?>" value="<?php if ($w == 'u') {
                echo $write['wr_link' . $i];
            } ?>" id="wr_link<?php echo $i ?>" class="frm_input full_input" placeholder="https://">
        </div>
        <?php } ?>

        <?php for ($i = 0; $is_file && $i < $file_count; $i++) { ?>
        <div class="inquiry-form__row write_div">
            <label for="bf_file_<?php echo $i + 1 ?>" class="inquiry-form__label">파일 <?php echo $i + 1 ?></label>
            <input type="file" name="bf_file[]" id="bf_file_<?php echo $i + 1 ?>" class="frm_file" title="파일첨부 <?php echo $i + 1 ?>">
            <?php if ($w == 'u' && $file[$i]['file']) { ?>
            <span class="file_del">
                <input type="checkbox" id="bf_file_del<?php echo $i ?>" name="bf_file_del[<?php echo $i; ?>]" value="1">
                <label for="bf_file_del<?php echo $i ?>"><?php echo $file[$i]['source'] . '(' . $file[$i]['size'] . ')'; ?> 삭제</label>
            </span>
            <?php } ?>
        </div>
        <?php } ?>
    </fieldset>

    <?php if ($is_use_captcha) { ?>
    <div class="inquiry-form__row write_div"><?php echo $captcha_html ?></div>
    <?php } ?>

    <div class="inquiry-form__actions btn_confirm write_div">
        <a href="<?php echo get_pretty_url($bo_table); ?>" class="btn_cancel btn">취소</a>
        <button type="submit" id="btn_submit" accesskey="s" class="btn_submit btn">저장</button>
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
            if (confirm("자동 줄바꿈을 사용하시겠습니까?")) obj.value = "html2";
            else obj.value = "html1";
        } else {
            obj.value = "";
        }
    }
    function fwrite_submit(f) {
        <?php echo $editor_js; ?>
        var subject = "", content = "";
        $.ajax({
            url: g5_bbs_url + "/ajax.filter.php",
            type: "POST",
            data: { "subject": f.wr_subject.value, "content": f.wr_content.value },
            dataType: "json",
            async: false,
            cache: false,
            success: function(data) { subject = data.subject; content = data.content; }
        });
        if (subject) { alert("제목에 금지단어('"+subject+"')가 포함되어있습니다"); f.wr_subject.focus(); return false; }
        if (content) {
            alert("내용에 금지단어('"+content+"')가 포함되어있습니다");
            if (typeof(ed_wr_content) != "undefined") ed_wr_content.returnFalse();
            else f.wr_content.focus();
            return false;
        }
        if (document.getElementById("char_count")) {
            if (char_min > 0 || char_max > 0) {
                var cnt = parseInt(check_byte("wr_content", "char_count"));
                if (char_min > 0 && char_min > cnt) { alert("내용은 "+char_min+"글자 이상 쓰셔야 합니다."); return false; }
                else if (char_max > 0 && char_max < cnt) { alert("내용은 "+char_max+"글자 이하로 쓰셔야 합니다."); return false; }
            }
        }
        <?php echo $captcha_js; ?>
        document.getElementById("btn_submit").disabled = "disabled";
        return true;
    }
    </script>
</section>
