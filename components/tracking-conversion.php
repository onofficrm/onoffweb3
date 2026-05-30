<?php
/**
 * 전환(완료) 페이지 이벤트 — 문의·신청·구매 완료 등
 * _site.config.php 추적 ID가 있을 때만 출력
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

if ($gtm_id === '' && $ga4_id === '' && $meta_pixel_id === '') {
    return;
}

$event_label = isset($conversion_event_label) ? (string) $conversion_event_label : 'inquiry_complete';
$event_label = preg_replace('/[^a-zA-Z0-9_\-]/', '', $event_label);
if ($event_label === '') {
    $event_label = 'inquiry_complete';
}

?>
<!-- 전환 추적: 운영 시 이벤트명·파라미터를 광고 계정에 맞게 수정 -->
<?php if ($gtm_id !== '') { ?>
<script>
window.dataLayer = window.dataLayer || [];
dataLayer.push({
  event: 'conversion',
  conversion_type: '<?php echo htmlspecialchars($event_label, ENT_QUOTES, 'UTF-8'); ?>'
});
</script>
<?php } ?>
<?php if ($ga4_id !== '') { ?>
<script>
if (typeof gtag === 'function') {
  gtag('event', 'generate_lead', {
    event_category: 'inquiry',
    event_label: '<?php echo htmlspecialchars($event_label, ENT_QUOTES, 'UTF-8'); ?>'
  });
}
</script>
<?php } ?>
<?php if ($meta_pixel_id !== '') { ?>
<script>
if (typeof fbq === 'function') {
  fbq('track', 'Lead');
}
</script>
<?php } ?>
