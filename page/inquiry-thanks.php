<?php
/**
 * 문의 접수 완료 페이지
 * URL: /page/inquiry-thanks.php
 */
include_once dirname(__FILE__) . '/_init.php';

if (!function_exists('g5site_cfg') && is_file(G5_PATH . '/_site.config.php')) {
    include_once G5_PATH . '/_site.config.php';
}

$thanks_site_name = function_exists('g5site_cfg') ? g5site_cfg('site_name', '본 사이트') : '본 사이트';
$thanks_phone     = function_exists('g5site_cfg') ? g5site_cfg('phone', '010-0000-0000') : '010-0000-0000';
$thanks_kakao     = function_exists('g5site_cfg') ? g5site_cfg('kakao_url', '#') : '#';
$thanks_tel_link  = function_exists('g5site_tel_link') ? g5site_tel_link($thanks_phone) : 'tel:' . preg_replace('/[^0-9+]/', '', $thanks_phone);
$thanks_home      = defined('G5_URL') ? G5_URL : '/';

$page_title       = '문의 접수 완료';
$page_description = $thanks_site_name . '에 문의해 주셔서 감사합니다. 담당자가 확인 후 빠르게 연락드리겠습니다.';
$page_robots      = 'noindex,nofollow';

g5_page_start('문의 접수 완료');
?>
<div class="page-template page-inquiry-thanks">
    <header class="page-hero reveal">
        <div class="page-inner">
            <p class="page-eyebrow">Thank you</p>
            <h1 class="page-title">문의 접수 완료</h1>
            <p class="page-lead">문의가 정상적으로 접수되었습니다.</p>
            <p class="page-desc">담당자가 확인 후 빠르게 연락드리겠습니다.</p>
        </div>
    </header>

    <section class="page-section reveal">
        <div class="page-inner page-inner--narrow">
            <div class="page-actions page-actions--stack">
                <a href="<?php echo htmlspecialchars($thanks_tel_link, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary">전화 문의</a>
                <?php if ($thanks_kakao !== '' && $thanks_kakao !== '#') { ?>
                <a href="<?php echo htmlspecialchars($thanks_kakao, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-secondary" target="_blank" rel="noopener noreferrer">카카오톡 문의</a>
                <?php } ?>
                <a href="<?php echo htmlspecialchars($thanks_home, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline">홈으로 이동</a>
            </div>
            <p class="page-note text-muted" style="margin-top:1.5rem;font-size:0.875rem;">
                입력하신 개인정보는 문의 응대 목적으로만 이용되며, 관련 법령에 따라 안전하게 관리됩니다.
            </p>
        </div>
    </section>

    <!-- 전환 추적 코드는 /components/tracking-conversion.php 또는 이 영역에서 관리하세요. -->
    <div class="page-tracking-conversion" aria-hidden="true">
        <?php
        $conversion_event_label = 'inquiry_complete';
        $tracking_conv = G5_PATH . '/components/tracking-conversion.php';
        if (is_file($tracking_conv)) {
            include $tracking_conv;
        }
        ?>
    </div>
</div>
<?php
g5_page_end();
