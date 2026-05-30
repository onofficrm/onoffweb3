<?php
/**
 * head 영역 추적 스크립트 — _site.config.php ID가 있을 때만 출력
 * head.php에서 add_javascript(-20)로 </head> 직전 삽입
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('g5site_cfg') && is_file(G5_PATH . '/_site.config.php')) {
    include_once G5_PATH . '/_site.config.php';
}

if (!function_exists('onoff_tracking_escape_id')) {
    function onoff_tracking_escape_id($id)
    {
        $id = trim((string) $id);
        if ($id === '') {
            return '';
        }
        return preg_replace('/[^a-zA-Z0-9\-_]/', '', $id);
    }
}

$gtm_id = function_exists('g5site_cfg') ? onoff_tracking_escape_id(g5site_cfg('gtm_id', '')) : '';
$ga4_id = function_exists('g5site_cfg') ? onoff_tracking_escape_id(g5site_cfg('ga4_id', '')) : '';
$meta_pixel_id = function_exists('g5site_cfg') ? onoff_tracking_escape_id(g5site_cfg('meta_pixel_id', '')) : '';
$naver_id = function_exists('g5site_cfg') ? onoff_tracking_escape_id(g5site_cfg('naver_analytics_id', '')) : '';
$kakao_pixel_id = function_exists('g5site_cfg') ? onoff_tracking_escape_id(g5site_cfg('kakao_pixel_id', '')) : '';

if ($gtm_id !== '') {
    echo "<!-- Google Tag Manager -->\n";
    echo "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','" . htmlspecialchars($gtm_id, ENT_QUOTES, 'UTF-8') . "');</script>\n";
}

if ($ga4_id !== '') {
    echo "<!-- Google Analytics 4 -->\n";
    echo "<script async src=\"https://www.googletagmanager.com/gtag/js?id=" . htmlspecialchars($ga4_id, ENT_QUOTES, 'UTF-8') . "\"></script>\n";
    echo "<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','" . htmlspecialchars($ga4_id, ENT_QUOTES, 'UTF-8') . "');</script>\n";
}

if ($meta_pixel_id !== '') {
    echo "<!-- Meta Pixel -->\n";
    echo "<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','" . htmlspecialchars($meta_pixel_id, ENT_QUOTES, 'UTF-8') . "');fbq('track','PageView');</script>\n";
}

if ($naver_id !== '') {
    echo "<!-- Naver Analytics -->\n";
    echo "<script type=\"text/javascript\" src=\"//wcs.naver.net/wcslog.js\"></script>\n";
    echo "<script type=\"text/javascript\">if(!wcs_add) var wcs_add={};wcs_add[\"wa\"]=\"" . htmlspecialchars($naver_id, ENT_QUOTES, 'UTF-8') . "\";if(window.wcs){wcs_do();}</script>\n";
}

if ($kakao_pixel_id !== '') {
    echo "<!-- Kakao Pixel -->\n";
    echo "<script type=\"text/javascript\" charset=\"UTF-8\" src=\"https://t1.daumcdn.net/kas/static/ka/kp.js\"></script>\n";
    echo "<script type=\"text/javascript\">if(typeof kakaoPixel==='function'){kakaoPixel('" . htmlspecialchars($kakao_pixel_id, ENT_QUOTES, 'UTF-8') . "').pageView();}</script>\n";
}
