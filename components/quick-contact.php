<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('g5site_cfg')) {
    if (is_file(G5_PATH . '/_site.config.php')) {
        include_once G5_PATH . '/_site.config.php';
    }
}

$cmp_phone = g5site_cfg('phone', '010-0000-0000');
$cmp_kakao = g5site_cfg('kakao_url', '#');
$cmp_consult_label = g5site_cfg('consultation_text', '상담문의');
$cmp_tel_link = g5site_tel_link($cmp_phone);
?>

<aside class="cmp-quick-contact" aria-label="빠른 문의">
    <div class="cmp-quick-contact__inner">
        <p class="cmp-quick-contact__title">빠른 상담</p>
        <p class="cmp-quick-contact__desc">전화·카카오톡·온라인 문의로 편하게 연락해 주세요.</p>
        <div class="cmp-quick-contact__actions">
            <a href="<?php echo htmlspecialchars($cmp_tel_link, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline cmp-quick-contact__btn">
                <i class="fa fa-phone" aria-hidden="true"></i> <?php echo htmlspecialchars($cmp_phone, ENT_QUOTES, 'UTF-8'); ?>
            </a>
            <?php if ($cmp_kakao !== '' && $cmp_kakao !== '#') { ?>
            <a href="<?php echo htmlspecialchars($cmp_kakao, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-secondary cmp-quick-contact__btn" target="_blank" rel="noopener noreferrer">
                <i class="fa fa-comment" aria-hidden="true"></i> 카카오톡
            </a>
            <?php } ?>
            <button type="button" class="btn btn-primary cmp-quick-contact__btn consult-modal-open" data-target="#cmpConsultModal">
                <i class="fa fa-envelope" aria-hidden="true"></i> <?php echo htmlspecialchars($cmp_consult_label, ENT_QUOTES, 'UTF-8'); ?>
            </button>
        </div>
    </div>
</aside>
