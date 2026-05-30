<?php
if (!defined('_GNUBOARD_')) exit;
?>

<script>
var char_min = parseInt(<?php echo $comment_min ?>);
var char_max = parseInt(<?php echo $comment_max ?>);
</script>

<button type="button" class="cmt_btn board-view__cmt-toggle"><span class="total"><b>댓글</b> <?php echo $view['wr_comment']; ?></span><span class="cmt_more"></span></button>

<section id="bo_vc" class="board-view__comments">
    <h2 class="sound_only">댓글목록</h2>
    <?php
    $cmt_amt = count($list);
    for ($i=0; $i<$cmt_amt; $i++) {
        $comment_id = $list[$i]['wr_id'];
        $cmt_depth = strlen($list[$i]['wr_comment_reply']) * 50;
        $comment = $list[$i]['content'];
        $comment = preg_replace("/\[\<a\s.*href\=\"(http|https|ftp|mms)\:\/\/([^[:space:]]+)\.(mp3|wma|wmv|asf|asx|mpg|mpeg)\".*\<\/a\>\]/i", "<script>doc_write(obj_movie('$1://$2.$3'));</script>", $comment);
        $cmt_sv = $cmt_amt - $i + 1;
        $c_reply_href = $comment_common_url.'&amp;c_id='.$comment_id.'&amp;w=c#bo_vc_w';
        $c_edit_href = $comment_common_url.'&amp;c_id='.$comment_id.'&amp;w=cu#bo_vc_w';
        $is_comment_reply_edit = ($list[$i]['is_reply'] || $list[$i]['is_edit'] || $list[$i]['is_del']) ? 1 : 0;
    ?>
    <article id="c_<?php echo $comment_id ?>" class="board-view__comment" <?php if ($cmt_depth) { ?>style="margin-left:<?php echo $cmt_depth ?>px"<?php } ?>>
        <div class="pf_img"><?php echo get_member_profile_img($list[$i]['mb_id']); ?></div>
        <div class="cm_wrap">
            <header style="z-index:<?php echo $cmt_sv; ?>">
                <h3 class="sound_only"><?php echo get_text($list[$i]['wr_name']); ?>님의 댓글</h3>
                <?php echo $list[$i]['name'] ?>
                <?php if ($is_ip_view) { ?><span class="sound_only">아이피</span><span>(<?php echo $list[$i]['ip']; ?>)</span><?php } ?>
                <span class="bo_vc_hdinfo"><i class="fa fa-clock-o" aria-hidden="true"></i>
                    <time datetime="<?php echo date('Y-m-d\TH:i:s+09:00', strtotime($list[$i]['datetime'])) ?>"><?php echo $list[$i]['datetime'] ?></time>
                </span>
                <?php include(G5_SNS_PATH.'/view_comment_list.sns.skin.php'); ?>
            </header>
            <div class="cmt_contents">
                <p>
                    <?php if (strstr($list[$i]['wr_option'], 'secret')) { ?><i class="fa fa-lock" aria-hidden="true" title="비밀댓글"></i><span class="sound_only">비밀글</span><?php } ?>
                    <?php echo $comment ?>
                </p>
            </div>
            <span id="edit_<?php echo $comment_id ?>" class="bo_vc_w"></span>
            <span id="reply_<?php echo $comment_id ?>" class="bo_vc_w"></span>
            <input type="hidden" value="<?php echo strstr($list[$i]['wr_option'],'secret') ?>" id="secret_comment_<?php echo $comment_id ?>">
            <textarea id="save_comment_<?php echo $comment_id ?>" style="display:none"><?php echo get_text($list[$i]['content1'], 0) ?></textarea>
        </div>
        <?php if ($is_comment_reply_edit) { ?>
        <div class="bo_vl_opt">
            <button type="button" class="btn_cm_opt btn_b01 btn"><i class="fa fa-ellipsis-v" aria-hidden="true"></i><span class="sound_only">댓글 옵션</span></button>
            <ul class="bo_vc_act">
                <?php if ($list[$i]['is_reply']) { ?><li><a href="<?php echo $c_reply_href; ?>" onclick="comment_box('<?php echo $comment_id ?>', 'c'); return false;">답변</a></li><?php } ?>
                <?php if ($list[$i]['is_edit']) { ?><li><a href="<?php echo $c_edit_href; ?>" onclick="comment_box('<?php echo $comment_id ?>', 'cu'); return false;">수정</a></li><?php } ?>
                <?php if ($list[$i]['is_del']) { ?><li><a href="<?php echo $list[$i]['del_link']; ?>" onclick="return comment_delete();">삭제</a></li><?php } ?>
            </ul>
        </div>
        <script>
        $(function() {
            $(".btn_cm_opt").on("click", function(){
                $(this).parent("div").children(".bo_vc_act").show();
            });
            $(document).mouseup(function (e){
                var container = $(".bo_vc_act");
                if (container.has(e.target).length === 0)
                    container.hide();
            });
        });
        </script>
        <?php } ?>
    </article>
    <?php } ?>
    <?php if ($i == 0) { ?><p id="bo_vc_empty" class="board-view__comments-empty">등록된 댓글이 없습니다.</p><?php } ?>
</section>

<?php if ($is_comment_write) {
    if ($w == '')
        $w = 'c';
?>
<aside id="bo_vc_w" class="bo_vc_w board-view__comment-form">
    <h2 class="board-view__section-title">댓글쓰기</h2>
    <form name="fviewcomment" id="fviewcomment" action="<?php echo $comment_action_url; ?>" onsubmit="return fviewcomment_submit(this);" method="post" autocomplete="off">
    <input type="hidden" name="w" value="<?php echo $w ?>" id="w">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
    <input type="hidden" name="comment_id" value="<?php echo $c_id ?>" id="comment_id">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="spt" value="<?php echo $spt ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="is_good" value="">

    <span class="sound_only">내용</span>
    <?php if ($comment_min || $comment_max) { ?><strong id="char_cnt"><span id="char_count"></span>글자</strong><?php } ?>
    <textarea id="wr_content" name="wr_content" maxlength="10000" required class="required" title="내용" placeholder="댓글내용을 입력해주세요"
    <?php if ($comment_min || $comment_max) { ?>onkeyup="check_byte('wr_content', 'char_count');"<?php } ?>><?php echo $c_wr_content; ?></textarea>
    <?php if ($comment_min || $comment_max) { ?><script> check_byte('wr_content', 'char_count'); </script><?php } ?>
    <script>
    $(document).on("keyup change", "textarea#wr_content[maxlength]", function() {
        var str = $(this).val();
        var mx = parseInt($(this).attr("maxlength"));
        if (str.length > mx) {
            $(this).val(str.substr(0, mx));
            return false;
        }
    });
    </script>
    <div class="bo_vc_w_wr">
        <div class="bo_vc_w_info">
            <?php if ($is_guest) { ?>
            <input type="text" name="wr_name" value="<?php echo get_cookie('ck_sns_name'); ?>" id="wr_name" required class="frm_input required" size="25" placeholder="이름">
            <input type="password" name="wr_password" id="wr_password" required class="frm_input required" size="25" placeholder="비밀번호">
            <?php echo $captcha_html; ?>
            <?php } ?>
            <?php if ($board['bo_use_sns'] && ($config['cf_facebook_appid'] || $config['cf_twitter_key'])) { ?>
            <span id="bo_vc_send_sns"></span>
            <?php } ?>
        </div>
        <div class="btn_confirm">
            <span class="secret_cm chk_box">
                <input type="checkbox" name="wr_secret" value="secret" id="wr_secret" class="selec_chk">
                <label for="wr_secret"><span></span>비밀글</label>
            </span>
            <button type="submit" id="btn_submit" class="btn_submit">댓글등록</button>
        </div>
    </div>
    </form>
</aside>

<script>
var save_before = '';
var save_html = document.getElementById('bo_vc_w').innerHTML;

function good_and_write()
{
    var f = document.fviewcomment;
    if (fviewcomment_submit(f)) {
        f.is_good.value = 1;
        f.submit();
    } else {
        f.is_good.value = 0;
    }
}

function fviewcomment_submit(f)
{
    var pattern = /(^\s*)|(\s*$)/g;
    f.is_good.value = 0;
    var subject = "";
    var content = "";
    $.ajax({
        url: g5_bbs_url+"/ajax.filter.php",
        type: "POST",
        data: { "subject": "", "content": f.wr_content.value },
        dataType: "json",
        async: false,
        cache: false,
        success: function(data) {
            subject = data.subject;
            content = data.content;
        }
    });
    if (content) {
        alert("내용에 금지단어('"+content+"')가 포함되어있습니다");
        f.wr_content.focus();
        return false;
    }
    document.getElementById('wr_content').value = document.getElementById('wr_content').value.replace(pattern, "");
    if (char_min > 0 || char_max > 0) {
        check_byte('wr_content', 'char_count');
        var cnt = parseInt(document.getElementById('char_count').innerHTML);
        if (char_min > 0 && char_min > cnt) {
            alert("댓글은 "+char_min+"글자 이상 쓰셔야 합니다.");
            return false;
        } else if (char_max > 0 && char_max < cnt) {
            alert("댓글은 "+char_max+"글자 이하로 쓰셔야 합니다.");
            return false;
        }
    } else if (!document.getElementById('wr_content').value) {
        alert("댓글을 입력하여 주십시오.");
        return false;
    }
    if (typeof(f.wr_name) != 'undefined') {
        f.wr_name.value = f.wr_name.value.replace(pattern, "");
        if (f.wr_name.value == '') {
            alert('이름이 입력되지 않았습니다.');
            f.wr_name.focus();
            return false;
        }
    }
    if (typeof(f.wr_password) != 'undefined') {
        f.wr_password.value = f.wr_password.value.replace(pattern, "");
        if (f.wr_password.value == '') {
            alert('비밀번호가 입력되지 않았습니다.');
            f.wr_password.focus();
            return false;
        }
    }
    <?php if ($is_guest) echo chk_captcha_js(); ?>
    set_comment_token(f);
    document.getElementById("btn_submit").disabled = "disabled";
    return true;
}

function comment_box(comment_id, work)
{
    var el_id, form_el = 'fviewcomment', respond = document.getElementById(form_el);
    if (comment_id) {
        if (work == 'c')
            el_id = 'reply_' + comment_id;
        else
            el_id = 'edit_' + comment_id;
    } else {
        el_id = 'bo_vc_w';
    }
    if (save_before != el_id) {
        if (save_before)
            document.getElementById(save_before).style.display = 'none';
        document.getElementById(el_id).style.display = '';
        document.getElementById(el_id).appendChild(respond);
        document.getElementById('wr_content').value = '';
        if (work == 'cu') {
            document.getElementById('wr_content').value = document.getElementById('save_comment_' + comment_id).value;
            if (typeof char_count != 'undefined')
                check_byte('wr_content', 'char_count');
            if (document.getElementById('secret_comment_'+comment_id).value)
                document.getElementById('wr_secret').checked = true;
            else
                document.getElementById('wr_secret').checked = false;
        }
        document.getElementById('comment_id').value = comment_id;
        document.getElementById('w').value = work;
        if (save_before)
            $("#captcha_reload").trigger("click");
        save_before = el_id;
    }
}

function comment_delete()
{
    return confirm("이 댓글을 삭제하시겠습니까?");
}

comment_box('', 'c');

<?php if ($board['bo_use_sns'] && ($config['cf_facebook_appid'] || $config['cf_twitter_key'])) { ?>
$(function() {
    $("#bo_vc_send_sns").load(
        "<?php echo G5_SNS_URL; ?>/view_comment_write.sns.skin.php?bo_table=<?php echo $bo_table; ?>",
        function() {
            save_html = document.getElementById('bo_vc_w').innerHTML;
        }
    );
});
<?php } ?>
</script>
<?php } ?>

<script>
jQuery(function($) {
    $(".cmt_btn").click(function(e){
        e.preventDefault();
        $(this).toggleClass("cmt_btn_op");
        $("#bo_vc").toggle();
    });
});
</script>
