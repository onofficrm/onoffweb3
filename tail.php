<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (defined('G5_THEME_PATH')) {
    require_once(G5_THEME_PATH.'/tail.php');
    return;
}

if (G5_IS_MOBILE) {
    include_once(G5_MOBILE_PATH.'/tail.php');
    return;
}

if (!isset($site_config) && is_file(G5_PATH.'/_site.config.php')) {
    include_once(G5_PATH.'/_site.config.php');
}

// 푸터·하단 버튼 — _site.config.php 우선, 없으면 기본값
$g5_footer_tel_display = function_exists('g5site_cfg') ? g5site_cfg('phone', '02-123-4567') : '02-123-4567';
$g5_footer_tel_link    = function_exists('g5site_tel_link') ? g5site_tel_link($g5_footer_tel_display) : 'tel:021234567';
$g5_footer_kakao_url   = function_exists('g5site_cfg') ? g5site_cfg('kakao_url', 'https://pf.kakao.com/_xxxxx') : 'https://pf.kakao.com/_xxxxx';
$g5_footer_company     = function_exists('g5site_cfg') ? g5site_cfg('company_name', '회사명') : '회사명';
$g5_footer_ceo         = function_exists('g5site_cfg') ? g5site_cfg('ceo_name', '대표자명') : '대표자명';
$g5_footer_intro       = function_exists('g5site_cfg') ? g5site_cfg('footer_desc', '고객과 함께 성장하는 든든한 파트너입니다.') : '고객과 함께 성장하는 든든한 파트너입니다.';
$g5_footer_biz_no      = function_exists('g5site_cfg') ? g5site_cfg('business_no', '123-45-67890') : '123-45-67890';
$g5_footer_sales_no    = function_exists('g5site_cfg') ? g5site_cfg('sales_no', '제 OO구 - 123호') : '제 OO구 - 123호';
$g5_footer_privacy     = function_exists('g5site_cfg') ? g5site_cfg('privacy_manager', '정보책임자명') : '정보책임자명';
$g5_footer_email       = function_exists('g5site_cfg') ? g5site_cfg('email', 'info@example.com') : 'info@example.com';
$g5_footer_address     = function_exists('g5site_cfg') ? g5site_cfg('address', 'OO도 OO시 OO구 OO동 123-45') : 'OO도 OO시 OO구 OO동 123-45';
$g5_footer_fax         = function_exists('g5site_cfg') ? g5site_cfg('fax', '02-123-4568') : '02-123-4568';

if (!isset($g5_inquiry_url)) {
    $g5_inquiry_url = defined('_INDEX_') ? G5_URL.'/#section-contact' : G5_BBS_URL.'/qalist.php';
}
$g5_is_index_page = defined('_INDEX_');
?>

    </div>
    <div id="aside" class="site-aside">
        <div class="site-g5-widgets site-g5-widgets--aside">
            <?php echo outlogin(); ?>
            <?php echo poll(); ?>
        </div>
    </div>
</div>

</div>
<!-- } 콘텐츠 끝 -->

<?php if ($g5_is_index_page) { ?>
<script>document.documentElement.classList.add('page-index');</script>
<?php } else { ?>
<script>document.documentElement.classList.add('page-sub');</script>
<?php } ?>

<hr>

<!-- 하단 시작 { -->
<div id="ft" class="site-footer-wrap">
    <div class="site-g5-widgets site-g5-widgets--tail">
        <?php echo latest('notice', 'notice', 4, 13); ?>
        <?php echo visit(); ?>
    </div>

    <footer id="siteFooter" class="site-footer">
        <div class="site-footer__inner">
            <div class="site-footer__brand">
                <h2 class="site-footer__company"><?php echo get_text($g5_footer_company); ?></h2>
                <p class="site-footer__intro"><?php echo get_text($g5_footer_intro); ?></p>
            </div>

            <div class="site-footer__info">
                <h3 class="site-footer__info-title sound_only">사업자정보</h3>
                <dl class="site-footer__dl">
                    <div class="site-footer__row">
                        <dt>대표</dt>
                        <dd><?php echo get_text($g5_footer_ceo); ?></dd>
                    </div>
                    <div class="site-footer__row">
                        <dt>사업자등록번호</dt>
                        <dd><?php echo get_text($g5_footer_biz_no); ?></dd>
                    </div>
                    <?php if ($g5_footer_sales_no !== '') { ?>
                    <div class="site-footer__row">
                        <dt>통신판매업신고</dt>
                        <dd><?php echo get_text($g5_footer_sales_no); ?></dd>
                    </div>
                    <?php } ?>
                    <?php if ($g5_footer_privacy !== '') { ?>
                    <div class="site-footer__row">
                        <dt>개인정보관리책임자</dt>
                        <dd><?php echo get_text($g5_footer_privacy); ?></dd>
                    </div>
                    <?php } ?>
                    <div class="site-footer__row">
                        <dt>연락처</dt>
                        <dd>
                            <a href="<?php echo $g5_footer_tel_link; ?>"><?php echo get_text($g5_footer_tel_display); ?></a>
                            <?php if ($g5_footer_fax !== '') { ?> / 팩스 <?php echo get_text($g5_footer_fax); ?><?php } ?>
                        </dd>
                    </div>
                    <div class="site-footer__row">
                        <dt>이메일</dt>
                        <dd><a href="mailto:<?php echo get_text($g5_footer_email); ?>"><?php echo get_text($g5_footer_email); ?></a></dd>
                    </div>
                    <div class="site-footer__row">
                        <dt>주소</dt>
                        <dd><?php echo get_text($g5_footer_address); ?></dd>
                    </div>
                </dl>
            </div>

            <nav class="site-footer__nav" aria-label="푸터 메뉴">
                <ul class="site-footer__menu">
                    <li><a href="<?php echo get_pretty_url('content', 'company'); ?>">회사소개</a></li>
                    <li><a href="<?php echo G5_URL; ?>/page/privacy.php">개인정보처리방침</a></li>
                    <li><a href="<?php echo get_pretty_url('content', 'provision'); ?>">서비스이용약관</a></li>
                    <li><a href="<?php echo G5_BBS_URL; ?>/faq.php">FAQ</a></li>
                    <li><a href="<?php echo get_device_change_url(); ?>">모바일버전</a></li>
                </ul>
            </nav>

            <?php if ($g5_is_index_page) { ?>
            <p class="site-footer__index-note is-index-only">
                메인 전용 안내 영역입니다. 빌더 푸터 문구·배너를 이 블록에 넣을 수 있습니다.
            </p>
            <?php } ?>
        </div>

        <div class="site-footer__copy">
            <p id="ft_copy" class="site-footer__copyright">
                Copyright &copy; <strong><?php echo get_text($config['cf_title']); ?></strong>. All rights reserved.
            </p>
        </div>
    </footer>
</div>

<?php
include_once(G5_PATH.'/components/floating-buttons.php');
include_once(G5_PATH.'/components/consult-modal.php');
include_once(G5_PATH.'/components/popup-banner.php');
?>

<?php
if ($config['cf_analytics']) {
    echo $config['cf_analytics'];
}
?>

<!-- } 하단 끝 -->

<script>
$(function() {
    font_resize("container", get_cookie("ck_font_resize_rmv_class"), get_cookie("ck_font_resize_add_class"));
});
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');
