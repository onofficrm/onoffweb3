<?php
if (!defined('_GNUBOARD_')) exit;

add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

$yt_url = isset($wr_1) ? get_text($wr_1) : '';
$yt_summary = isset($wr_2) ? get_text($wr_2) : '';
?>

<section class="board-wrap board-wrap--youtube-list board-write" id="bo_w" style="width:<?php echo $width; ?>">
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
            if ($is_admin || $is_secret==1) {
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

    <?php if ($option) { ?>
    <div class="board-write-form__row write_div board-write-form__options">
        <ul class="bo_v_option"><?php echo $option ?></ul>
    </div>
    <?php } ?>

    <div class="board-write-form__row bo_w_tit write_div">
        <label for="wr_subject" class="board-write-form__label">제목<strong class="required">필수</strong></label>
        <input type="text" name="wr_subject" value="<?php echo $subject ?>" id="wr_subject" required class="frm_input full_input required" maxlength="255" placeholder="영상 제목을 입력하세요">
    </div>

    <div class="board-write-form__row board-write-form__youtube write_div">
        <label for="wr_1" class="board-write-form__label">유튜브 URL<strong class="required">필수</strong></label>
        <input type="url" name="wr_1" value="<?php echo $yt_url ?>" id="wr_1" class="frm_input full_input board-write-form__yt-url" maxlength="255" placeholder="https://www.youtube.com/watch?v=영상ID" required>
        <p class="board-write-form__hint board-write-form__yt-guide">
            <strong>유튜브 URL</strong> — 여분필드 <code>wr_1</code>에 저장됩니다. 아래 형식을 지원합니다.<br>
            · https://www.youtube.com/watch?v=VIDEO_ID<br>
            · https://youtu.be/VIDEO_ID<br>
            · https://www.youtube.com/embed/VIDEO_ID<br>
            · https://www.youtube.com/shorts/VIDEO_ID<br>
            · https://m.youtube.com/watch?v=VIDEO_ID<br>
            · 11자 영상 ID만 입력 (예: <code>dQw4w9WgXcQ</code>)<br>
            <span class="board-write-form__yt-warn">형식이 맞지 않으면 목록·글보기에서 영상이 표시되지 않습니다.</span>
        </p>
    </div>

    <div class="board-write-form__row write_div">
        <label for="wr_2" class="board-write-form__label">영상 설명 / 요약</label>
        <p class="board-write-form__hint">목록 요약·VideoObject 설명에 사용됩니다. (선택, <code>wr_2</code>)</p>
        <textarea name="wr_2" id="wr_2" class="frm_input full_input board-write-form__yt-summary" rows="3" placeholder="목록에 표시될 짧은 설명 (선택)"><?php echo $yt_summary ?></textarea>
    </div>

    <div class="board-write-form__row write_div board-write-form__content">
        <label for="wr_content" class="board-write-form__label">본문</label>
        <p class="board-write-form__hint">상세 설명·자막·추가 정보를 입력하세요. (선택)</p>
        <div class="wr_content <?php echo $is_dhtml_editor ? $config['cf_editor'] : ''; ?>">
            <?php echo $editor_html; ?>
        </div>
    </div>

    <?php if ($is_file && $file_count) { ?>
    <div class="board-write-form__row board-write-form__files">
        <p class="board-write-form__label">첨부파일 <span class="board-write-form__optional">(선택)</span></p>
        <?php for ($i=0; $i<$file_count; $i++) { ?>
        <div class="bo_w_flie write_div board-write-form__file">
            <input type="file" name="bf_file[]" id="bf_file_<?php echo $i+1 ?>" class="frm_file" title="<?php echo $upload_max_filesize ?> 이하">
            <?php if ($w == 'u' && !empty($file[$i]['file'])) { ?>
            <span class="file_del">
                <input type="checkbox" id="bf_file_del<?php echo $i ?>" name="bf_file_del[<?php echo $i; ?>]" value="1">
                <label for="bf_file_del<?php echo $i ?>"><?php echo $file[$i]['source']; ?> 삭제</label>
            </span>
            <?php } ?>
        </div>
        <?php } ?>
    </div>
    <?php } ?>

    <?php if ($is_use_captcha) { ?>
    <div class="board-write-form__row write_div board-write-form__captcha"><?php echo $captcha_html ?></div>
    <?php } ?>

    <div class="btn_confirm write_div board-write-form__submit">
        <a href="<?php echo get_pretty_url($bo_table); ?>" class="btn_cancel btn">취소</a>
        <button type="submit" id="btn_submit" class="btn_submit btn board-actions__write">작성완료</button>
    </div>
    </form>

    <script>
    function html_auto_br(obj) {
        if (obj.checked) {
            if (confirm("자동 줄바꿈을 사용하시겠습니까?")) obj.value = "html2";
            else obj.value = "html1";
        } else obj.value = "";
    }
    function fwrite_submit(f) {
        <?php echo $editor_js; ?>
        var subject = "", content = "";
        $.ajax({
            url: g5_bbs_url+"/ajax.filter.php",
            type: "POST",
            data: { "subject": f.wr_subject.value, "content": f.wr_content.value },
            dataType: "json", async: false,
            success: function(data) { subject = data.subject; content = data.content; }
        });
        if (subject) { alert("제목에 금지단어가 포함되어 있습니다."); f.wr_subject.focus(); return false; }
        if (content) { alert("내용에 금지단어가 포함되어 있습니다."); return false; }
        if (!f.wr_1.value.trim()) { alert("유튜브 URL을 입력해 주세요."); f.wr_1.focus(); return false; }
        <?php echo $captcha_js; ?>
        document.getElementById("btn_submit").disabled = "disabled";
        return true;
    }
    </script>
</section>
