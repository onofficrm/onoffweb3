<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (!isset($site_config) && is_file(G5_PATH.'/_site.config.php')) {
    include_once(G5_PATH.'/_site.config.php');
}

run_event('pre_head');

if (defined('G5_THEME_PATH')) {
    require_once(G5_THEME_PATH.'/head.php');
    return;
}

if (G5_IS_MOBILE) {
    include_once(G5_MOBILE_PATH.'/head.php');
    return;
}

// SEO 메타 (파일 없어도 fatal 방지)
if (is_file(G5_PATH.'/components/seo-meta.php')) {
    include_once(G5_PATH.'/components/seo-meta.php');
    if (function_exists('g5b_seo_init')) {
        g5b_seo_init();
    }
}

include_once(G5_PATH.'/head.sub.php');

// 방문 추적 (head) — ID 없으면 출력 없음
if (is_file(G5_PATH.'/components/tracking-head.php')) {
    ob_start();
    include_once(G5_PATH.'/components/tracking-head.php');
    $onoff_tracking_head = ob_get_clean();
    if (trim($onoff_tracking_head) !== '') {
        add_javascript($onoff_tracking_head, -20);
    }
}

// GTM noscript — body 시작 직후 (head.sub.php 직후)
if (is_file(G5_PATH.'/components/tracking-body.php')) {
    include_once(G5_PATH.'/components/tracking-body.php');
}

// 템플릿 전용 CSS/JS (default.css·common.js 이후 로드)
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/custom.css">', 10);
if ($g5_css_brand !== '') {
    add_stylesheet('<style>:root{'.$g5_css_brand.'}</style>', 11);
}
if (is_file(G5_LIB_PATH.'/icrm.lib.php')) {
    include_once G5_LIB_PATH.'/icrm.lib.php';
    if (function_exists('icrm_enqueue_board_assets')) {
        icrm_enqueue_board_assets();
    }
}
add_javascript('<script src="'.G5_JS_URL.'/custom.js"></script>', 20);

include_once(G5_LIB_PATH.'/latest.lib.php');
include_once(G5_LIB_PATH.'/outlogin.lib.php');
include_once(G5_LIB_PATH.'/poll.lib.php');
include_once(G5_LIB_PATH.'/visit.lib.php');
include_once(G5_LIB_PATH.'/connect.lib.php');
include_once(G5_LIB_PATH.'/popular.lib.php');

// 로고 경로 (site_config → svg/png 파일 → 텍스트 fallback)
$g5_logo_url = '';
if (function_exists('g5site_cfg')) {
    $g5_logo_rel = g5site_cfg('logo_path', '');
    if ($g5_logo_rel !== '' && preg_match('#^https?://#i', $g5_logo_rel)) {
        $g5_logo_url = $g5_logo_rel;
    } elseif ($g5_logo_rel !== '') {
        $g5_logo_rel = ($g5_logo_rel[0] === '/') ? $g5_logo_rel : '/'.$g5_logo_rel;
        if (is_file(G5_PATH.$g5_logo_rel)) {
            $g5_logo_url = G5_URL.$g5_logo_rel;
        }
    }
}
if ($g5_logo_url === '') {
    foreach (array('logo.svg', 'logo.png') as $g5_logo_file) {
        if (is_file(G5_PATH.'/img/logo/'.$g5_logo_file)) {
            $g5_logo_url = G5_URL.'/img/logo/'.$g5_logo_file;
            break;
        }
    }
}
$g5_site_title = function_exists('g5site_cfg')
    ? g5site_cfg('site_name', get_text($config['cf_title']))
    : get_text($config['cf_title']);
if ($g5_site_title === '') {
    $g5_site_title = get_text($config['cf_title']);
}

// 상담문의 URL (메인: contact 섹션 / 그 외: Q&A)
$g5_inquiry_url = defined('_INDEX_') ? G5_URL.'/#section-contact' : G5_BBS_URL.'/qalist.php';
$g5_consult_label = function_exists('g5site_cfg') ? g5site_cfg('consultation_text', '상담문의') : '상담문의';

// site_config 브랜드 색 → :root (hex만 허용)
$g5_css_brand = '';
if (function_exists('g5site_cfg')) {
    $g5_primary = g5site_cfg('primary_color', '');
    $g5_secondary = g5site_cfg('secondary_color', '');
    if ($g5_primary !== '' && preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6}|[0-9A-Fa-f]{8})$/', $g5_primary)) {
        $g5_css_brand .= '--color-primary:'.$g5_primary.';';
    }
    if ($g5_secondary !== '' && preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6}|[0-9A-Fa-f]{8})$/', $g5_secondary)) {
        $g5_css_brand .= '--color-secondary:'.$g5_secondary.';--color-muted:'.$g5_secondary.';';
    }
}

// 메뉴 (PC / 모바일)
$menu_datas_pc = get_menu_db(0, true);
$menu_datas_mo = get_menu_db(1, true);
if (!is_array($menu_datas_mo) || !count($menu_datas_mo)) {
    $menu_datas_mo = $menu_datas_pc;
}
?>

<!-- 상단 시작 { -->
<div id="hd">
    <header id="siteHeader" class="site-header">
        <h1 id="hd_h1" class="sound_only"><?php echo $g5['title']; ?></h1>
        <div id="skip_to_container" class="site-header__skip">
            <a href="#container">본문 바로가기</a>
        </div>

        <?php
        if (defined('_INDEX_')) {
            include G5_BBS_PATH.'/newwin.inc.php';
        }
        ?>

        <div class="site-header__inner">
            <div class="site-header__logo">
                <a href="<?php echo G5_URL; ?>" class="site-header__logo-link">
                    <?php if ($g5_logo_url) { ?>
                    <img src="<?php echo $g5_logo_url; ?>" alt="<?php echo $g5_site_title; ?>" class="site-header__logo-img">
                    <?php } else { ?>
                    <span class="site-header__logo-text"><?php echo $g5_site_title; ?></span>
                    <?php } ?>
                </a>
            </div>

            <nav id="siteGnb" class="site-header__gnb" aria-label="메인메뉴">
                <ul class="site-header__gnb-list">
                    <?php
                    $gnb_i = 0;
                    foreach ((array) $menu_datas_pc as $row) {
                        if (empty($row)) {
                            continue;
                        }
                        $has_sub = !empty($row['sub']);
                    ?>
                    <li class="site-header__gnb-item<?php echo $has_sub ? ' has-sub' : ''; ?>">
                        <a href="<?php echo $row['me_link']; ?>" target="_<?php echo $row['me_target']; ?>" class="site-header__gnb-link"><?php echo $row['me_name']; ?></a>
                        <?php if ($has_sub) { ?>
                        <ul class="site-header__gnb-sub">
                            <?php foreach ((array) $row['sub'] as $row2) {
                                if (empty($row2)) {
                                    continue;
                                }
                            ?>
                            <li class="site-header__gnb-sub-item">
                                <a href="<?php echo $row2['me_link']; ?>" target="_<?php echo $row2['me_target']; ?>" class="site-header__gnb-sub-link"><?php echo $row2['me_name']; ?></a>
                            </li>
                            <?php } ?>
                        </ul>
                        <?php } ?>
                    </li>
                    <?php
                        $gnb_i++;
                    }
                    if ($gnb_i === 0) {
                    ?>
                    <li class="site-header__gnb-item site-header__gnb-item--empty">
                        <span class="site-header__gnb-empty">메뉴 준비 중입니다.<?php if ($is_admin) { ?> <a href="<?php echo G5_ADMIN_URL; ?>/menu_list.php">메뉴설정</a><?php } ?></span>
                    </li>
                    <?php } ?>
                </ul>
            </nav>

            <div class="site-header__utils">
                <div class="site-header__search">
                    <fieldset id="hd_sch">
                        <legend class="sound_only">사이트 내 전체검색</legend>
                        <form name="fsearchbox" method="get" action="<?php echo G5_BBS_URL; ?>/search.php" onsubmit="return fsearchbox_submit(this);">
                            <input type="hidden" name="sfl" value="wr_subject||wr_content">
                            <input type="hidden" name="sop" value="and">
                            <label for="sch_stx" class="sound_only">검색어 필수</label>
                            <input type="text" name="stx" id="sch_stx" maxlength="20" placeholder="검색" class="site-header__search-input">
                            <button type="submit" id="sch_submit" value="검색" class="site-header__search-btn">
                                <i class="fa fa-search" aria-hidden="true"></i><span class="sound_only">검색</span>
                            </button>
                        </form>
                    </fieldset>
                </div>

                <ul class="site-header__account hd_login">
                    <?php if ($is_member) { ?>
                    <li><a href="<?php echo G5_BBS_URL; ?>/member_confirm.php?url=<?php echo G5_BBS_URL; ?>/register_form.php">정보수정</a></li>
                    <li><a href="<?php echo G5_BBS_URL; ?>/logout.php">로그아웃</a></li>
                    <?php if ($is_admin) { ?>
                    <li class="tnb_admin"><a href="<?php echo correct_goto_url(G5_ADMIN_URL); ?>">관리자</a></li>
                    <?php } ?>
                    <?php } else { ?>
                    <li><a href="<?php echo G5_BBS_URL; ?>/register.php">회원가입</a></li>
                    <li><a href="<?php echo G5_BBS_URL; ?>/login.php">로그인</a></li>
                    <?php } ?>
                </ul>

                <a href="<?php echo $g5_inquiry_url; ?>" class="btn btn-primary site-header__cta"><?php echo get_text($g5_consult_label); ?></a>

                <button type="button" class="site-header__menu-btn" aria-controls="siteMobileNav" aria-expanded="false" title="전체메뉴">
                    <i class="fa fa-bars" aria-hidden="true"></i>
                    <span class="sound_only">전체메뉴열기</span>
                </button>
            </div>
        </div>

        <div id="siteMobileNav" class="site-header__mobile-nav" aria-hidden="true">
            <div class="site-header__mobile-nav-head">
                <strong class="site-header__mobile-nav-title">전체메뉴</strong>
                <button type="button" class="site-header__mobile-close" title="메뉴 닫기">
                    <i class="fa fa-times" aria-hidden="true"></i>
                    <span class="sound_only">메뉴 닫기</span>
                </button>
            </div>
            <ul class="site-header__mobile-list">
                <?php
                $mnb_i = 0;
                foreach ((array) $menu_datas_mo as $row) {
                    if (empty($row)) {
                        continue;
                    }
                ?>
                <li class="site-header__mobile-item">
                    <a href="<?php echo $row['me_link']; ?>" target="_<?php echo $row['me_target']; ?>" class="site-header__mobile-link"><?php echo $row['me_name']; ?></a>
                    <?php
                    $mnb_k = 0;
                    foreach ((array) $row['sub'] as $row2) {
                        if (empty($row2)) {
                            continue;
                        }
                        if ($mnb_k === 0) {
                            echo '<ul class="site-header__mobile-sub">'.PHP_EOL;
                        }
                    ?>
                    <li><a href="<?php echo $row2['me_link']; ?>" target="_<?php echo $row2['me_target']; ?>"><?php echo $row2['me_name']; ?></a></li>
                    <?php
                        $mnb_k++;
                    }
                    if ($mnb_k > 0) {
                        echo '</ul>'.PHP_EOL;
                    }
                    ?>
                </li>
                <?php
                    $mnb_i++;
                }
                if ($mnb_i === 0) {
                ?>
                <li class="site-header__mobile-item site-header__mobile-item--empty">
                    <span>메뉴 준비 중입니다.<?php if ($is_admin) { ?> <a href="<?php echo G5_ADMIN_URL; ?>/menu_list.php">메뉴설정</a><?php } ?></span>
                </li>
                <?php } ?>
            </ul>
            <ul class="site-header__mobile-utils">
                <li><a href="<?php echo G5_BBS_URL; ?>/faq.php">FAQ</a></li>
                <li><a href="<?php echo G5_BBS_URL; ?>/qalist.php">Q&amp;A</a></li>
                <li><a href="<?php echo G5_BBS_URL; ?>/new.php">새글</a></li>
                <?php if (defined('G5_USE_SHOP') && G5_USE_SHOP) { ?>
                <li><a href="<?php echo G5_SHOP_URL; ?>">쇼핑몰</a></li>
                <?php } ?>
            </ul>
            <div class="site-header__mobile-account">
                <?php if ($is_member) { ?>
                <a href="<?php echo G5_BBS_URL; ?>/member_confirm.php?url=<?php echo G5_BBS_URL; ?>/register_form.php">정보수정</a>
                <a href="<?php echo G5_BBS_URL; ?>/logout.php">로그아웃</a>
                <?php if ($is_admin) { ?>
                <a href="<?php echo correct_goto_url(G5_ADMIN_URL); ?>">관리자</a>
                <?php } ?>
                <?php } else { ?>
                <a href="<?php echo G5_BBS_URL; ?>/register.php">회원가입</a>
                <a href="<?php echo G5_BBS_URL; ?>/login.php">로그인</a>
                <?php } ?>
            </div>
            <a href="<?php echo $g5_inquiry_url; ?>" class="btn btn-primary site-header__mobile-cta"><?php echo get_text($g5_consult_label); ?></a>
        </div>
        <div class="site-header__overlay" aria-hidden="true"></div>
    </header>
</div>

<script>
function fsearchbox_submit(f)
{
    var stx = f.stx.value.trim();
    if (stx.length < 2) {
        alert("검색어는 두글자 이상 입력하십시오.");
        f.stx.select();
        f.stx.focus();
        return false;
    }
    var cnt = 0;
    for (var i = 0; i < stx.length; i++) {
        if (stx.charAt(i) == ' ') cnt++;
    }
    if (cnt > 1) {
        alert("빠른 검색을 위하여 검색어에 공백은 한개만 입력할 수 있습니다.");
        f.stx.select();
        f.stx.focus();
        return false;
    }
    f.stx.value = stx;
    return true;
}
</script>
<!-- } 상단 끝 -->

<hr>

<!-- 콘텐츠 시작 { -->
<div id="wrapper">
    <div id="container_wr">
    <div id="container">
        <?php if (!defined('_INDEX_')) { ?><h2 id="container_title"><span title="<?php echo get_text($g5['title']); ?>"><?php echo get_head_title($g5['title']); ?></span></h2><?php } ?>
