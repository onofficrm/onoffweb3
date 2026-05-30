<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_SKIN_PATH.'/board/_inc/g5b-fallback.php');

/**
 * YouTube URL → 영상 ID (11자, 영숫자·_- 만 허용)
 */
function g5b_youtube_id_from_url($url)
{
    $url = trim((string) $url);
    if ($url === '') {
        return '';
    }

    $patterns = array(
        '#(?:https?://)?(?:www\.|m\.)?youtube\.com/watch\?(?:[^&\s]*&)*v=([a-zA-Z0-9_-]{11})#i',
        '#(?:https?://)?(?:www\.|m\.)?youtube\.com/watch\?v=([a-zA-Z0-9_-]{11})#i',
        '#(?:https?://)?youtu\.be/([a-zA-Z0-9_-]{11})#i',
        '#(?:https?://)?(?:www\.)?youtube\.com/embed/([a-zA-Z0-9_-]{11})#i',
        '#(?:https?://)?(?:www\.)?youtube\.com/shorts/([a-zA-Z0-9_-]{11})#i',
    );

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $m)) {
            return g5b_youtube_sanitize_id($m[1]);
        }
    }

    return '';
}

if (!function_exists('onoff_extract_youtube_id')) {
    /**
     * @param string $url
     * @return string 11자 영상 ID 또는 빈 문자열
     */
    function onoff_extract_youtube_id($url)
    {
        return g5b_youtube_id_from_url($url);
    }
}

function g5b_youtube_sanitize_id($id)
{
    $id = trim((string) $id);
    if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $id)) {
        return $id;
    }
    return '';
}

/**
 * 글 데이터에서 ID 추출 (wr_1 → 본문 iframe fallback)
 */
function g5b_youtube_id_from_write($write)
{
    if (!empty($write['wr_1'])) {
        $id = g5b_youtube_id_from_url($write['wr_1']);
        if ($id) {
            return $id;
        }
        $id = g5b_youtube_sanitize_id($write['wr_1']);
        if ($id) {
            return $id;
        }
    }

    if (!empty($write['wr_content'])) {
        $content = $write['wr_content'];
        if (preg_match('#youtube\.com/embed/([a-zA-Z0-9_-]{11})#i', $content, $m)) {
            return g5b_youtube_sanitize_id($m[1]);
        }
        if (preg_match('#youtu\.be/([a-zA-Z0-9_-]{11})#i', $content, $m)) {
            return g5b_youtube_sanitize_id($m[1]);
        }
        if (preg_match('#(?:v=|/vi/)([a-zA-Z0-9_-]{11})#i', $content, $m)) {
            return g5b_youtube_sanitize_id($m[1]);
        }
    }

    return '';
}

function g5b_youtube_thumb_url($video_id, $quality = 'hqdefault')
{
    $video_id = g5b_youtube_sanitize_id($video_id);
    if (!$video_id) {
        return '';
    }
    $allowed = array('default', 'mqdefault', 'hqdefault', 'sddefault', 'maxresdefault');
    if (!in_array($quality, $allowed, true)) {
        $quality = 'hqdefault';
    }
    return 'https://img.youtube.com/vi/'.$video_id.'/'.$quality.'.jpg';
}

function g5b_youtube_embed_url($video_id)
{
    $video_id = g5b_youtube_sanitize_id($video_id);
    if (!$video_id) {
        return '';
    }
    return 'https://www.youtube-nocookie.com/embed/'.$video_id;
}

/**
 * Schema·외부 링크용 watch URL (영상 ID만 사용)
 */
function g5b_youtube_watch_url($video_id)
{
    $video_id = g5b_youtube_sanitize_id($video_id);
    if (!$video_id) {
        return '';
    }
    return 'https://www.youtube.com/watch?v='.$video_id;
}

/**
 * VideoObject embedUrl (www.youtube.com, 영상 ID만 사용)
 */
function g5b_youtube_schema_embed_url($video_id)
{
    $video_id = g5b_youtube_sanitize_id($video_id);
    if (!$video_id) {
        return '';
    }
    return 'https://www.youtube.com/embed/'.$video_id;
}

/**
 * VideoObject용 설명 (wr_2 → wr_content, 최대 200자)
 */
function g5b_youtube_schema_description($write, $max_len = 200)
{
    $text = '';
    if (!empty($write['wr_2'])) {
        $text = get_text(strip_tags($write['wr_2']));
    } elseif (!empty($write['wr_content'])) {
        $text = get_text(strip_tags($write['wr_content']));
    }
    $text = trim((string) $text);
    if ($text === '') {
        return '';
    }
    if (function_exists('cut_str')) {
        return cut_str($text, (int) $max_len, '…');
    }
    if (strlen($text) > $max_len) {
        return substr($text, 0, $max_len).'…';
    }
    return $text;
}

/**
 * wr_datetime → ISO 8601 (실패 시 빈 문자열)
 */
function g5b_youtube_schema_upload_date($datetime)
{
    $datetime = trim((string) $datetime);
    if ($datetime === '' || $datetime === '0000-00-00 00:00:00') {
        return '';
    }
    $ts = strtotime($datetime);
    if (!$ts) {
        return '';
    }
    return date('c', $ts);
}

/**
 * 글보기 VideoObject Schema 출력 (파일·ID 없으면 무시)
 *
 * @param array  $write
 * @param string $video_id 이미 추출된 ID (선택)
 */
function g5b_youtube_print_video_schema($write, $video_id = '')
{
    if ($video_id === '') {
        $video_id = g5b_youtube_id_from_write($write);
    }
    $video_id = g5b_youtube_sanitize_id($video_id);
    if (!$video_id) {
        return;
    }

    $title = !empty($write['wr_subject']) ? get_text(strip_tags($write['wr_subject'])) : '';
    $title = trim((string) $title);
    if ($title === '') {
        return;
    }

    $video_schema_title = $title;
    $video_schema_description = g5b_youtube_schema_description($write);
    $video_schema_id = $video_id;
    $video_schema_thumbnail = g5b_youtube_thumb_url($video_id);
    $video_schema_upload_date = g5b_youtube_schema_upload_date(isset($write['wr_datetime']) ? $write['wr_datetime'] : '');
    $video_schema_embed_url = g5b_youtube_schema_embed_url($video_id);
    $video_schema_content_url = g5b_youtube_watch_url($video_id);

    $schema_file = defined('G5_PATH') ? G5_PATH.'/components/schema/video.php' : '';
    if ($schema_file !== '' && is_file($schema_file)) {
        include_once $schema_file;
    }
}

/**
 * 목록용 썸네일 HTML
 */
function g5b_youtube_thumb_html($video_id, $alt = '', $is_secret = false)
{
    if ($is_secret) {
        return '<span class="board-yt-thumb board-yt-thumb--secret" title="비밀글">'
            .'<i class="fa fa-lock" aria-hidden="true"></i><span class="sound_only">비밀글</span></span>';
    }

    $video_id = g5b_youtube_sanitize_id($video_id);
    if ($video_id) {
        $src = g5b_youtube_thumb_url($video_id);
        $alt_attr = $alt ? htmlspecialchars(get_text(strip_tags($alt)), ENT_QUOTES, 'UTF-8') : '';
        return '<span class="board-yt-thumb board-yt-thumb--has youtube-thumb-wrap">'
            .'<img src="'.htmlspecialchars($src, ENT_QUOTES, 'UTF-8').'" alt="'.$alt_attr.'" class="board-yt-thumb__img youtube-thumb" loading="lazy" decoding="async">'
            .'</span>';
    }

    if (g5b_fallback_file_exists('youtube')) {
        return '<span class="board-yt-thumb board-yt-thumb--fallback">'
            .g5b_fallback_img_html('youtube', 'board-yt-thumb__img board-yt-thumb__img--placeholder')
            .'</span>';
    }

    return '<span class="board-yt-thumb board-yt-thumb--empty" aria-hidden="true"></span>';
}

/**
 * 글보기 embed (src는 검증된 ID만 사용)
 */
function g5b_youtube_embed_html($video_id, $title = '')
{
    $video_id = g5b_youtube_sanitize_id($video_id);
    if (!$video_id) {
        return '';
    }

    $src = g5b_youtube_embed_url($video_id);
    $title_attr = $title ? htmlspecialchars(get_text(strip_tags($title)), ENT_QUOTES, 'UTF-8') : 'YouTube video player';

    return '<div class="board-yt-embed youtube-embed-wrap">'
        .'<div class="board-yt-embed__ratio youtube-embed">'
        .'<iframe src="'.htmlspecialchars($src, ENT_QUOTES, 'UTF-8').'?rel=0" '
        .'title="'.$title_attr.'" '
        .'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" '
        .'referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"></iframe>'
        .'</div></div>';
}

function g5b_youtube_fallback_html($message = '')
{
    if ($message === '') {
        $message = '등록된 유튜브 영상이 없거나 URL 형식이 올바르지 않습니다.';
    }
    $msg = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $icon = '';
    if (g5b_fallback_file_exists('youtube')) {
        $icon = g5b_fallback_img_html('youtube', 'board-yt-fallback__icon');
    }

    return '<div class="board-yt-fallback" role="alert">'
        .$icon
        .'<p class="board-yt-fallback__text">'.$msg.'</p>'
        .'<p class="board-yt-fallback__hint">지원 형식: youtube.com/watch?v= · youtu.be/ · embed/ · shorts/</p>'
        .'</div>';
}
