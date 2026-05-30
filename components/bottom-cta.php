<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('g5site_cfg')) {
    if (is_file(G5_PATH . '/_site.config.php')) {
        include_once G5_PATH . '/_site.config.php';
    }
}

$cmp_title = g5site_cfg('consultation_text', '상담문의');
$cmp_desc = g5site_cfg('footer_desc', '지금 바로 상담을 요청해 보세요.');
$cmp_phone = g5site_cfg('phone', '010-0000-0000');
$cmp_kakao = g5site_cfg('kakao_url', '#');
$cmp_tel_link = g5site_tel_link($cmp_phone);
?>

<section class="cmp-bottom-cta" aria-labelledby="cmpBottomCtaTitle">
    <div class="cmp-bottom-cta__inner">
        <h2 id="cmpBottomCtaTitle" class="cmp-bottom-cta__title"><?php echo htmlspecialchars($cmp_title, ENT_QUOTES, 'UTF-8'); ?>가 필요하신가요?</h2>
        <p class="cmp-bottom-cta__desc"><?php echo htmlspecialchars($cmp_desc, ENT_QUOTES, 'UTF-8'); ?></p>
        <div class="cmp-bottom-cta__actions">
            <a href="<?php echo htmlspecialchars($cmp_tel_link, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary cmp-bottom-cta__btn">
                <i class="fa fa-phone" aria-hidden="true"></i> 전화문의
            </a>
            <?php if ($cmp_kakao !== '' && $cmp_kakao !== '#') { ?>
            <a href="<?php echo htmlspecialchars($cmp_kakao, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline cmp-bottom-cta__btn" target="_blank" rel="noopener noreferrer">
                <i class="fa fa-comment" aria-hidden="true"></i> 카카오톡 문의
            </a>
            <?php } else { ?>
            <button type="button" class="btn btn-outline cmp-bottom-cta__btn consult-modal-open" data-target="#cmpConsultModal">
                <i class="fa fa-envelope" aria-hidden="true"></i> <?php echo htmlspecialchars($cmp_title, ENT_QUOTES, 'UTF-8'); ?>
            </button>
            <?php } ?>
        </div>
    </div>
</section>
