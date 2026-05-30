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
$cmp_inquiry_url = isset($g5_inquiry_url) ? $g5_inquiry_url : (defined('_INDEX_') ? G5_URL . '/#section-contact' : G5_BBS_URL . '/qalist.php');
?>

<aside id="siteDock" class="site-dock cmp-floating is-all-pages" aria-label="하단 빠른 메뉴">
    <a href="<?php echo htmlspecialchars($cmp_tel_link, ENT_QUOTES, 'UTF-8'); ?>" class="site-dock__btn site-dock__btn--tel cmp-floating__btn cmp-floating__btn--tel">
        <i class="fa fa-phone" aria-hidden="true"></i>
        <span>전화문의</span>
    </a>
    <?php if ($cmp_kakao !== '' && $cmp_kakao !== '#') { ?>
    <a href="<?php echo htmlspecialchars($cmp_kakao, ENT_QUOTES, 'UTF-8'); ?>" class="site-dock__btn site-dock__btn--kakao cmp-floating__btn cmp-floating__btn--kakao" target="_blank" rel="noopener noreferrer">
        <i class="fa fa-comment" aria-hidden="true"></i>
        <span>카카오톡</span>
    </a>
    <?php } ?>
    <button type="button" class="site-dock__btn site-dock__btn--inquiry cmp-floating__btn cmp-floating__btn--consult consult-modal-open" data-target="#cmpConsultModal" aria-haspopup="dialog">
        <i class="fa fa-envelope" aria-hidden="true"></i>
        <span><?php echo htmlspecialchars($cmp_consult_label, ENT_QUOTES, 'UTF-8'); ?></span>
    </button>
    <button type="button" id="top_btn" class="site-dock__btn site-dock__btn--top cmp-floating__btn cmp-floating__btn--top" title="상단으로">
        <i class="fa fa-arrow-up" aria-hidden="true"></i>
        <span class="sound_only">상단으로</span>
    </button>
</aside>
