<?php
if (!defined('_GNUBOARD_')) exit;

add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>

<section class="board-wrap board-wrap--post-media board-write" id="bo_w" style="width:<?php echo $width; ?>">
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
        $option = '';
        if ($is_notice) {
            $option .= PHP_EOL.'<li class="chk_box"><input type="checkbox" id="notice" name="notice" class="selec_chk" value="1" '.$notice_checked.'>'.PHP_EOL.'<label for="notice"><span></span>공지</label></li>';
        }
        if ($is_html) {
            if ($is_dhtml_editor) {
                $option_hidden .= '<input type="hidden" value="html1" name="html">';
            } else {
                $option .= PHP_EOL.'<li class="chk_box"><input type="checkbox" id="html" name="html" onclick="html_auto_br(this);" class="selec_chk" value="'.$html_value.'" '.$html_checked.'>'.PHP_EOL.'<label for="html"><span></span>html</label></li>';
            }
        }
        if ($is_secret) {
            if ($is_admin || $is_secret==1) {
                $option .= PHP_EOL.'<li class="chk_box"><input type="checkbox" id="secret" name="secret" class="selec_chk" value="secret" '.$secret_checked.'>'.PHP_EOL.'<label for="secret"><span></span>비밀글</label></li>';
            } else {
                $option_hidden .= '<input type="hidden" name="secret" value="secret">';
            }
        }
        if ($is_mail) {
            $option .= PHP_EOL.'<li class="chk_box"><input type="checkbox" id="mail" name="mail" class="selec_chk" value="mail" '.$recv_email_checked.'>'.PHP_EOL.'<label for="mail"><span></span>답변메일받기</label></li>';
        }
    }
    echo $option_hidden;
    ?>

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
            <label for="wr_name" class="board-write-form__label sound_only">이름<strong>필수</strong></label>
            <input type="text" name="wr_name" value="<?php echo $name ?>" id="wr_name" required class="frm_input half_input required" placeholder="이름">
        </div>
        <?php } ?>
        <?php if ($is_password) { ?>
        <div class="board-write-form__field">
            <label for="wr_password" class="board-write-form__label sound_only">비밀번호<strong>필수</strong></label>
            <input type="password" name="wr_password" id="wr_password" <?php echo $password_required ?> class="frm_input half_input <?php echo $password_required ?>" placeholder="비밀번호">
        </div>
        <?php } ?>
        <?php if ($is_email) { ?>
        <div class="board-write-form__field">
            <label for="wr_email" class="board-write-form__label sound_only">이메일</label>
            <input type="text" name="wr_email" value="<?php echo $email ?>" id="wr_email" class="frm_input half_input email" placeholder="이메일">
        </div>
        <?php } ?>
        <?php if ($is_homepage) { ?>
        <div class="board-write-form__field">
            <label for="wr_homepage" class="board-write-form__label sound_only">홈페이지</label>
            <input type="text" name="wr_homepage" value="<?php echo $homepage ?>" id="wr_homepage" class="frm_input half_input" placeholder="홈페이지">
        </div>
        <?php } ?>
    </div>

    <?php if ($option) { ?>
    <div class="board-write-form__row write_div board-write-form__options">
        <span class="sound_only">옵션</span>
        <ul class="bo_v_option"><?php echo $option ?></ul>
    </div>
    <?php } ?>

    <div class="board-write-form__row bo_w_tit write_div">
        <label for="wr_subject" class="board-write-form__label">제목<strong class="required">필수</strong></label>
        <div id="autosave_wrapper" class="board-write-form__subject">
            <input type="text" name="wr_subject" value="<?php echo $subject ?>" id="wr_subject" required class="frm_input full_input required" size="50" maxlength="255" placeholder="제목을 입력하세요">
            <?php if ($is_member) { ?>
            <script src="<?php echo G5_JS_URL; ?>/autosave.js"></script>
            <?php if ($editor_content_js) echo $editor_content_js; ?>
            <button type="button" id="btn_autosave" class="btn_frmline">임시 저장 (<span id="autosave_count"><?php echo $autosave_count; ?></span>)</button>
            <div id="autosave_pop">
                <strong>임시 저장된 글 목록</strong>
                <ul></ul>
                <div><button type="button" class="autosave_close">닫기</button></div>
            </div>
            <?php } ?>
        </div>
    </div>

    <div class="board-write-form__row write_div board-write-form__content">
        <label for="wr_content" class="board-write-form__label">내용<strong class="required">필수</strong></label>
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

    <?php for ($i=1; $is_link && $i<=G5_LINK_COUNT; $i++) { ?>
    <div class="board-write-form__row bo_w_link write_div">
        <label for="wr_link<?php echo $i ?>" class="board-write-form__label"><i class="fa fa-link" aria-hidden="true"></i> 링크 <?php echo $i ?></label>
        <input type="text" name="wr_link<?php echo $i ?>" value="<?php if ($w=='u') { echo $write['wr_link'.$i]; } ?>" id="wr_link<?php echo $i ?>" class="frm_input full_input" size="50" placeholder="https://">
    </div>
    <?php } ?>

    <?php for ($i=0; $is_file && $i<$file_count; $i++) { ?>
    <div class="board-write-form__row bo_w_flie write_div board-write-form__file">
        <label for="bf_file_<?php echo $i+1 ?>" class="board-write-form__label lb_icon"><i class="fa fa-folder-open" aria-hidden="true"></i> 파일 <?php echo $i+1 ?></label>
        <div class="file_wr write_div">
            <input type="file" name="bf_file[]" id="bf_file_<?php echo $i+1 ?>" title="파일첨부 <?php echo $i+1 ?> : <?php echo $upload_max_filesize ?> 이하" class="frm_file">
        </div>
        <?php if ($is_file_content) { ?>
        <input type="text" name="bf_content[]" value="<?php echo ($w == 'u') ? $file[$i]['bf_content'] : ''; ?>" class="full_input frm_input" size="50" placeholder="파일 설명">
        <?php } ?>
        <?php if ($w == 'u' && $file[$i]['file']) { ?>
        <span class="file_del">
            <input type="checkbox" id="bf_file_del<?php echo $i ?>" name="bf_file_del[<?php echo $i; ?>]" value="1">
            <label for="bf_file_del<?php echo $i ?>"><?php echo $file[$i]['source'].'('.$file[$i]['size'].')'; ?> 삭제</label>
        </span>
        <?php } ?>
    </div>
    <?php } ?>

    <?php if ($is_use_captcha) { ?>
    <div class="board-write-form__row write_div board-write-form__captcha">
        <?php echo $captcha_html ?>
    </div>
    <?php } ?>

    <div class="btn_confirm write_div board-write-form__submit">
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
    function html_auto_br(obj)
    {
        if (obj.checked) {
            result = confirm("자동 줄바꿈을 하시겠습니까?\n\n자동 줄바꿈은 게시물 내용중 줄바뀐 곳을<br>태그로 변환하는 기능입니다.");
            if (result)
                obj.value = "html2";
            else
                obj.value = "html1";
        } else {
            obj.value = "";
        }
    }
    function fwrite_submit(f)
    {
        <?php echo $editor_js; ?>

        var subject = "";
        var content = "";
        $.ajax({
            url: g5_bbs_url+"/ajax.filter.php",
            type: "POST",
            data: {
                "subject": f.wr_subject.value,
                "content": f.wr_content.value
            },
            dataType: "json",
            async: false,
            cache: false,
            success: function(data) {
                subject = data.subject;
                content = data.content;
            }
        });

        if (subject) {
            alert("제목에 금지단어('"+subject+"')가 포함되어있습니다");
            f.wr_subject.focus();
            return false;
        }
        if (content) {
            alert("내용에 금지단어('"+content+"')가 포함되어있습니다");
            if (typeof(ed_wr_content) != "undefined")
                ed_wr_content.returnFalse();
            else
                f.wr_content.focus();
            return false;
        }

        if (document.getElementById("char_count")) {
            if (char_min > 0 || char_max > 0) {
                var cnt = parseInt(check_byte("wr_content", "char_count"));
                if (char_min > 0 && char_min > cnt) {
                    alert("내용은 "+char_min+"글자 이상 쓰셔야 합니다.");
                    return false;
                } else if (char_max > 0 && char_max < cnt) {
                    alert("내용은 "+char_max+"글자 이하로 쓰셔야 합니다.");
                    return false;
                }
            }
        }

        <?php echo $captcha_js; ?>

        document.getElementById("btn_submit").disabled = "disabled";
        return true;
    }
    </script>
</section>
