<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('g5site_cfg')) {
    if (is_file(G5_PATH . '/_site.config.php')) {
        include_once G5_PATH . '/_site.config.php';
    }
}

$cmp_site_name = g5site_cfg('site_name', '사이트');
$cmp_consult_label = g5site_cfg('consultation_text', '상담문의');

if (!get_session('onoff_inquiry_token')) {
    set_session('onoff_inquiry_token', md5(uniqid((string) mt_rand(), true)));
}
$cmp_inquiry_token = get_session('onoff_inquiry_token');
$cmp_inquiry_action = G5_URL . '/proc/inquiry-submit.php';
$cmp_referer_page = isset($_SERVER['HTTP_REFERER']) ? clean_xss_tags($_SERVER['HTTP_REFERER']) : '';
if ($cmp_referer_page === '' && defined('G5_URL')) {
    $cmp_referer_page = G5_URL;
}
?>

<div id="cmpConsultModal" class="consult-modal cmp-consult-modal" role="dialog" aria-modal="true" aria-labelledby="cmpConsultModalTitle" aria-hidden="true">
    <div class="consult-modal-overlay cmp-consult-modal__overlay" aria-hidden="true"></div>
    <div class="consult-modal__panel cmp-consult-modal__panel">
        <button type="button" class="consult-modal-close cmp-consult-modal__close" aria-label="닫기">
            <i class="fa fa-times" aria-hidden="true"></i>
        </button>
        <h2 id="cmpConsultModalTitle" class="consult-modal__title cmp-consult-modal__title"><?php echo htmlspecialchars($cmp_consult_label, ENT_QUOTES, 'UTF-8'); ?></h2>
        <p class="consult-modal__desc cmp-consult-modal__desc"><?php echo htmlspecialchars($cmp_site_name, ENT_QUOTES, 'UTF-8'); ?>에 문의해 주세요. 접수 후 빠르게 연락드리겠습니다.</p>

        <form class="cmp-consult-form" action="<?php echo htmlspecialchars($cmp_inquiry_action, ENT_QUOTES, 'UTF-8'); ?>" method="post" novalidate>
            <input type="hidden" name="onoff_inquiry_token" value="<?php echo htmlspecialchars($cmp_inquiry_token, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="referer_page" value="<?php echo htmlspecialchars($cmp_referer_page, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="cmp-form-row cmp-form-row--hp" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;">
                <label for="cmp_consult_website">웹사이트</label>
                <input type="text" id="cmp_consult_website" name="website_url" tabindex="-1" autocomplete="off">
            </div>
            <div class="cmp-form-row">
                <label class="cmp-form-label" for="cmp_consult_name">이름 <span class="cmp-form-required">*</span></label>
                <input type="text" id="cmp_consult_name" name="name" class="cmp-form-input" placeholder="이름을 입력하세요" autocomplete="name" required>
            </div>
            <div class="cmp-form-row">
                <label class="cmp-form-label" for="cmp_consult_phone">연락처 <span class="cmp-form-required">*</span></label>
                <input type="tel" id="cmp_consult_phone" name="phone" class="cmp-form-input" placeholder="010-0000-0000" autocomplete="tel" required>
            </div>
            <div class="cmp-form-row">
                <label class="cmp-form-label" for="cmp_consult_email">이메일 <span class="cmp-form-optional">(선택)</span></label>
                <input type="email" id="cmp_consult_email" name="email" class="cmp-form-input" placeholder="email@example.com" autocomplete="email">
            </div>
            <div class="cmp-form-row">
                <label class="cmp-form-label" for="cmp_consult_message">문의내용 <span class="cmp-form-required">*</span></label>
                <textarea id="cmp_consult_message" name="message" class="cmp-form-input cmp-form-textarea" rows="5" placeholder="문의 내용을 입력하세요" required></textarea>
            </div>
            <div class="cmp-form-row cmp-privacy-agree">
                <label class="cmp-privacy-agree__label">
                    <input type="checkbox" id="cmp_consult_privacy" name="privacy_agree" value="1" required>
                    <span>개인정보 수집·이용에 동의합니다. <span class="cmp-form-required">*</span></span>
                </label>
                <p class="cmp-privacy-agree__note">수집 항목: 이름, 연락처, 문의내용 · 목적: 상담 응대 · 보관 기간: 상담 완료 후 1년 이내 파기 (샘플 문구 — 실제 운영 시 개인정보처리방침에 맞게 수정)</p>
            </div>
            <div class="consult-modal__actions cmp-consult-modal__actions">
                <button type="button" class="btn btn-outline consult-modal-close">취소</button>
                <button type="submit" class="btn btn-primary cmp-consult-form__submit">문의 보내기</button>
            </div>
            <p class="cmp-consult-form__status" role="status" aria-live="polite" hidden></p>
            <p class="cmp-consult-form__note">접수 내용은 문의 게시판에 저장되며, 설정된 관리자 이메일로 알림이 발송될 수 있습니다.</p>
        </form>
    </div>
</div>
