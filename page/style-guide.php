<?php
/**
 * =============================================================================
 * [개발·검수 전용] 디자인 스타일 가이드
 * =============================================================================
 * - 운영(라이브) 사이트에 노출하면 안 됩니다.
 * - 런칭 전 이 파일을 삭제하거나, 웹서버에서 접근을 차단하세요.
 * - URL 예: /page/style-guide.php
 * =============================================================================
 */
include_once(dirname(__FILE__).'/_init.php');
include_once(G5_PATH.'/section/_helpers.php');

global $is_admin, $is_member;

$page_title       = '디자인 스타일 가이드 (개발용)';
$page_description = '버튼·카드·폼·섹션·게시판 스킨 등 템플릿 디자인 요소 검수 페이지입니다. 운영 사이트에 노출하지 마세요.';
$page_robots      = 'noindex,nofollow';

$sg_allowed = !empty($is_admin);

if (!$sg_allowed) {
    g5_page_start('접근 제한');
    ?>
    <div class="page-template page-style-guide page-style-guide--denied">
        <header class="page-hero">
            <div class="page-inner">
                <p class="page-eyebrow">Style Guide</p>
                <h1 class="page-title">접근 제한</h1>
                <p class="page-desc">이 페이지는 <strong>관리자 로그인</strong> 후에만 열람할 수 있는 개발·검수용 페이지입니다.</p>
            </div>
        </header>
        <section class="page-section">
            <div class="page-inner">
                <div class="sg-alert sg-alert--danger" role="alert">
                    <p><strong>운영 사이트에 노출되면 안 되는 테스트 페이지입니다.</strong></p>
                    <p>그누보드 관리자(최고·그룹·게시판 관리자) 계정으로 로그인한 뒤 다시 접속해 주세요.</p>
                    <?php if (empty($is_member)) { ?>
                    <p><a href="<?php echo G5_BBS_URL; ?>/login.php?url=<?php echo urlencode(G5_URL.'/page/style-guide.php'); ?>" class="btn btn-primary">관리자 로그인</a></p>
                    <?php } ?>
                </div>
            </div>
        </section>
    </div>
    <?php
    g5_page_end();
    return;
}

$sg_phone     = function_exists('g5site_cfg') ? g5site_cfg('phone', '010-0000-0000') : '010-0000-0000';
$sg_kakao     = function_exists('g5site_cfg') ? g5site_cfg('kakao_url', '#') : '#';
$sg_tel_link  = function_exists('g5site_tel_link') ? g5site_tel_link($sg_phone) : 'tel:01000000000';

$sg_board_skins = array(
    array('skin' => 'basic-clean',      'label' => 'Basic Clean',      'bo_table' => 'notice'),
    array('skin' => 'basic-modern',     'label' => 'Basic Modern',     'bo_table' => 'notice'),
    array('skin' => 'basic-card',       'label' => 'Basic Card',       'bo_table' => 'notice'),
    array('skin' => 'basic-notice',     'label' => 'Basic Notice',     'bo_table' => 'notice'),
    array('skin' => 'post-thumb',       'label' => 'Post Thumb',       'bo_table' => 'news'),
    array('skin' => 'post-media',       'label' => 'Post Media',       'bo_table' => 'news'),
    array('skin' => 'gallery-grid',     'label' => 'Gallery Grid',     'bo_table' => 'gallery'),
    array('skin' => 'gallery-masonry',  'label' => 'Gallery Masonry',  'bo_table' => 'gallery'),
    array('skin' => 'youtube-list',     'label' => 'YouTube List',     'bo_table' => 'video'),
    array('skin' => 'youtube-gallery',  'label' => 'YouTube Gallery',  'bo_table' => 'video'),
);

g5_page_start('스타일 가이드');
?>
<div class="page-template page-style-guide">
    <div class="sg-alert sg-alert--warn" role="alert">
        <p class="sg-alert__title"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> 개발·검수 전용 페이지</p>
        <p>운영(라이브) 사이트에 노출하지 마세요. 런칭 전 <strong>/page/style-guide.php</strong> 파일 삭제 또는 서버 접근 차단을 권장합니다. (<code>noindex</code> 적용됨)</p>
    </div>

    <header class="page-hero reveal">
        <div class="page-inner">
            <p class="page-eyebrow">Style Guide</p>
            <h1 class="page-title">디자인 스타일 가이드</h1>
            <p class="page-desc">custom.css 토큰·컴포넌트·섹션·게시판 스킨을 한 화면에서 확인합니다. 관리자 로그인 상태에서만 전체 내용이 표시됩니다.</p>
            <nav class="sg-toc" aria-label="스타일 가이드 목차">
                <a href="#sg-colors">색상</a>
                <a href="#sg-type">타이포</a>
                <a href="#sg-buttons">버튼</a>
                <a href="#sg-cards">카드</a>
                <a href="#sg-forms">폼</a>
                <a href="#sg-components">컴포넌트</a>
                <a href="#sg-sections">섹션</a>
                <a href="#sg-boards">게시판</a>
            </nav>
        </div>
    </header>

    <!-- 1. 색상 팔레트 -->
    <section class="page-section sg-section" id="sg-colors">
        <div class="page-inner">
            <h2 class="page-section__title">1. 색상 팔레트</h2>
            <p class="page-section__desc"><code>:root</code> CSS 변수 — <code>/_site.config.php</code>의 primary/secondary가 head.php에서 덮어씁니다.</p>
            <ul class="sg-palette">
                <li class="sg-swatch"><span class="sg-swatch__chip" style="background:var(--color-primary)"></span><span class="sg-swatch__name">primary</span><code>--color-primary</code></li>
                <li class="sg-swatch"><span class="sg-swatch__chip" style="background:var(--color-secondary)"></span><span class="sg-swatch__name">secondary</span><code>--color-secondary</code></li>
                <li class="sg-swatch"><span class="sg-swatch__chip" style="background:var(--color-bg);border:1px solid var(--color-line)"></span><span class="sg-swatch__name">background</span><code>--color-bg</code></li>
                <li class="sg-swatch"><span class="sg-swatch__chip" style="background:var(--color-surface)"></span><span class="sg-swatch__name">surface</span><code>--color-surface</code></li>
                <li class="sg-swatch"><span class="sg-swatch__chip" style="background:var(--color-text)"></span><span class="sg-swatch__name">text</span><code>--color-text</code></li>
                <li class="sg-swatch"><span class="sg-swatch__chip" style="background:var(--color-muted)"></span><span class="sg-swatch__name">muted</span><code>--color-muted</code></li>
                <li class="sg-swatch"><span class="sg-swatch__chip" style="background:var(--color-line)"></span><span class="sg-swatch__name">line</span><code>--color-line</code></li>
            </ul>
        </div>
    </section>

    <!-- 2. 타이포그래피 -->
    <section class="page-section page-section--alt sg-section" id="sg-type">
        <div class="page-inner">
            <h2 class="page-section__title">2. 타이포그래피</h2>
            <div class="sg-type-sample">
                <h1 class="sg-h1">H1 — 페이지 대제목 (.page-title / .section-title)</h1>
                <h2 class="sg-h2">H2 — 섹션 제목 (.page-section__title / .section-title)</h2>
                <h3 class="sg-h3">H3 — 카드·블록 제목 (.base-card-title)</h3>
                <p class="sg-body">본문 — 기본 문단 텍스트입니다. line-height와 color-text 토큰이 적용됩니다. 링크는 <a href="#">primary 색상</a>으로 호버됩니다.</p>
                <p class="sg-small">작은 글씨 — 보조 설명, 캡션, 메타 정보 (--font-size-sm, --color-muted)</p>
            </div>
        </div>
    </section>

    <!-- 3. 버튼 -->
    <section class="page-section sg-section" id="sg-buttons">
        <div class="page-inner">
            <h2 class="page-section__title">3. 버튼</h2>
            <div class="sg-btn-row">
                <a href="#" class="btn sg-btn-default" onclick="return false;">기본 버튼</a>
                <a href="#" class="btn btn-primary" onclick="return false;">Primary</a>
                <a href="#" class="btn btn-secondary" onclick="return false;">Secondary</a>
                <a href="#" class="btn btn-outline" onclick="return false;">Outline</a>
            </div>
            <div class="sg-btn-row">
                <a href="<?php echo htmlspecialchars($sg_tel_link, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary">
                    <i class="fa fa-phone" aria-hidden="true"></i> 전화 버튼
                </a>
                <a href="<?php echo htmlspecialchars($sg_kakao, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-secondary" target="_blank" rel="noopener noreferrer" onclick="return false;">
                    <i class="fa fa-comment" aria-hidden="true"></i> 카카오톡 버튼
                </a>
            </div>
            <p class="sg-note">클래스: <code>.btn</code> + <code>.btn-primary</code> / <code>.btn-secondary</code> / <code>.btn-outline</code></p>
        </div>
    </section>

    <!-- 4. 카드 -->
    <section class="page-section page-section--alt sg-section" id="sg-cards">
        <div class="page-inner">
            <h2 class="page-section__title">4. 카드</h2>
            <div class="card-grid card-grid--3 sg-card-demo">
                <article class="base-card">
                    <h3 class="base-card-title">기본 카드</h3>
                    <p class="base-card-desc">.base-card — 테두리·그림자·호버 상승</p>
                </article>
                <article class="base-card media-card">
                    <div class="media-card__thumb">
                        <?php g5_sample_main_media('service-01.jpg', '서비스', 'media-card__img', 'card'); ?>
                    </div>
                    <div class="media-card__body">
                        <h3 class="base-card-title">서비스 카드</h3>
                        <p class="base-card-desc">.media-card — 썸네일 + 제목·설명</p>
                    </div>
                </article>
                <article class="base-card review-card">
                    <div class="review-card__stars" aria-label="별점 5점">★★★★★</div>
                    <blockquote class="review-card__text">후기 카드 샘플 문구입니다.</blockquote>
                    <footer class="review-card__author">
                        <strong>홍길동</strong>
                        <span>대표</span>
                    </footer>
                </article>
            </div>
            <div class="card-grid card-grid--auto" style="margin-top:var(--space-xl)">
                <article class="base-card media-card">
                    <a href="#" class="media-card__link" onclick="return false;">
                        <div class="media-card__thumb">
                            <?php g5_sample_main_media('portfolio-01.jpg', '포트폴리오', 'media-card__img', 'wide'); ?>
                        </div>
                        <div class="media-card__body">
                            <h3 class="base-card-title">포트폴리오 카드</h3>
                            <p class="base-card-desc">.media-card__link — 클릭 영역 확장</p>
                        </div>
                    </a>
                </article>
            </div>
        </div>
    </section>

    <!-- 5. 폼 -->
    <section class="page-section sg-section" id="sg-forms">
        <div class="page-inner">
            <h2 class="page-section__title">5. 폼 요소</h2>
            <form class="sg-form-demo" action="#" method="post" onsubmit="return false;" novalidate>
                <div class="cmp-form-row">
                    <label class="cmp-form-label" for="sg_input">Input <span class="cmp-form-required">*</span></label>
                    <input type="text" id="sg_input" class="cmp-form-input" placeholder="텍스트 입력">
                </div>
                <div class="cmp-form-row">
                    <label class="cmp-form-label" for="sg_textarea">Textarea</label>
                    <textarea id="sg_textarea" class="cmp-form-input cmp-form-textarea" rows="4" placeholder="여러 줄 입력"></textarea>
                </div>
                <div class="cmp-form-row">
                    <label class="cmp-form-label" for="sg_select">Select</label>
                    <select id="sg_select" class="cmp-form-input">
                        <option value="">선택하세요</option>
                        <option value="a">옵션 A</option>
                        <option value="b">옵션 B</option>
                    </select>
                </div>
                <div class="cmp-form-row">
                    <label class="cmp-privacy-agree__label">
                        <input type="checkbox" id="sg_check" value="1">
                        <span>Checkbox — 동의 항목 샘플</span>
                    </label>
                </div>
                <div class="cmp-form-row cmp-privacy-agree">
                    <label class="cmp-privacy-agree__label">
                        <input type="checkbox" id="sg_privacy" value="1">
                        <span>개인정보 수집·이용에 동의합니다. <span class="cmp-form-required">*</span></span>
                    </label>
                    <p class="cmp-privacy-agree__note">개인정보 처리방침 링크·상세 문구는 프로젝트별로 수정하세요.</p>
                </div>
                <button type="submit" class="btn btn-primary">제출 (샘플)</button>
            </form>
        </div>
    </section>

    <!-- 6. 컴포넌트 -->
    <section class="page-section page-section--alt sg-section" id="sg-components">
        <div class="page-inner">
            <h2 class="page-section__title">6. 컴포넌트</h2>

            <h3 class="sg-subtitle">quick-contact</h3>
            <?php
            if (is_file(G5_PATH.'/components/quick-contact.php')) {
                include G5_PATH.'/components/quick-contact.php';
            }
            ?>

            <h3 class="sg-subtitle">bottom-cta</h3>
            <?php
            if (is_file(G5_PATH.'/components/bottom-cta.php')) {
                include G5_PATH.'/components/bottom-cta.php';
            }
            ?>

            <h3 class="sg-subtitle">kakao-map (placeholder)</h3>
            <?php
            if (is_file(G5_PATH.'/components/kakao-map.php')) {
                include G5_PATH.'/components/kakao-map.php';
            }
            ?>

            <h3 class="sg-subtitle">consult modal</h3>
            <p class="sg-note">tail.php에 포함된 <code>#cmpConsultModal</code>을 엽니다. (페이지 하단 플로팅·모달과 동일)</p>
            <button type="button" class="btn btn-primary consult-modal-open" data-target="#cmpConsultModal">
                <i class="fa fa-envelope" aria-hidden="true"></i> 상담 모달 열기
            </button>

            <h3 class="sg-subtitle">popup-banner</h3>
            <p class="sg-note">메인 자동 팝업은 비활성일 수 있습니다. 아래 버튼으로 <code>#cmpPopupBanner</code> 미리보기를 엽니다.</p>
            <button type="button" class="btn btn-outline" id="sgOpenPopup">팝업 배너 열기</button>

            <div class="sg-popup-preview" aria-hidden="true">
                <p class="sg-note">정적 미리보기 (구조 참고용)</p>
                <div class="cmp-popup__panel sg-popup-preview__panel">
                    <p class="cmp-popup__badge">이벤트</p>
                    <h2 class="cmp-popup__title">팝업 제목 샘플</h2>
                    <p class="cmp-popup__desc">팝업 설명 문구입니다.</p>
                    <div class="cmp-popup__actions">
                        <span class="btn btn-primary">CTA</span>
                        <span class="btn btn-outline">닫기</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 7. 섹션 샘플 -->
    <section class="page-section sg-section" id="sg-sections">
        <div class="page-inner">
            <h2 class="page-section__title">7. 섹션 샘플</h2>
            <p class="page-section__desc">메인 <code>/section/*.php</code>와 동일 클래스를 축소해 재현합니다.</p>
        </div>

        <div class="sg-section-preview">
            <section class="section section-hero" id="sg-preview-hero">
                <div class="section-inner section-hero__inner">
                    <div class="section-hero__content">
                        <p class="section-eyebrow">Hero</p>
                        <h2 class="section-title">히어로 섹션 샘플</h2>
                        <p class="section-desc">.section-hero — 메인 비주얼·CTA 영역</p>
                        <div class="section-actions">
                            <a href="#" class="btn btn-primary" onclick="return false;">CTA Primary</a>
                            <a href="#" class="btn btn-outline" onclick="return false;">CTA Outline</a>
                        </div>
                    </div>
                    <div class="section-hero__visual">
                        <?php g5_sample_main_media('hero.jpg', '히어로', 'section-hero__img', 'hero'); ?>
                    </div>
                </div>
            </section>

            <section class="section section-service section--alt">
                <div class="section-inner">
                    <div class="section-head">
                        <p class="section-eyebrow">Service</p>
                        <h2 class="section-title">서비스 섹션</h2>
                        <p class="section-desc">.section-service · .card-grid · .media-card</p>
                    </div>
                    <div class="section-content">
                        <div class="card-grid card-grid--auto">
                            <article class="base-card media-card">
                                <div class="media-card__thumb">
                                    <?php g5_sample_main_media('service-01.jpg', '서비스', 'media-card__img', 'card'); ?>
                                </div>
                                <div class="media-card__body">
                                    <h3 class="base-card-title">서비스 항목</h3>
                                    <p class="base-card-desc">카드 그리드 샘플</p>
                                </div>
                            </article>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section section-faq section--alt">
                <div class="section-inner">
                    <div class="section-head">
                        <p class="section-eyebrow">FAQ</p>
                        <h2 class="section-title">FAQ 섹션</h2>
                    </div>
                    <div class="section-content">
                        <div class="faq-list">
                            <div class="faq-item is-open">
                                <button type="button" class="faq-question" aria-expanded="true">아코디언 질문 샘플</button>
                                <div class="faq-answer"><p>답변 영역입니다. custom.js FAQ 토글과 연동됩니다.</p></div>
                            </div>
                            <div class="faq-item">
                                <button type="button" class="faq-question" aria-expanded="false">두 번째 질문</button>
                                <div class="faq-answer"><p>접힌 상태 샘플</p></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section section-contact section--dark">
                <div class="section-inner">
                    <div class="section-head">
                        <p class="section-eyebrow">Contact</p>
                        <h2 class="section-title">문의 섹션</h2>
                        <p class="section-desc">.section-contact.section--dark</p>
                    </div>
                    <div class="section-content">
                        <div class="contact-cta">
                            <a href="<?php echo htmlspecialchars($sg_tel_link, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary contact-cta__tel">
                                <i class="fa fa-phone" aria-hidden="true"></i> <?php echo htmlspecialchars($sg_phone, ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                            <button type="button" class="btn btn-outline consult-modal-open contact-cta__form" data-target="#cmpConsultModal">
                                <i class="fa fa-envelope" aria-hidden="true"></i> 온라인 문의
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </section>

    <!-- 8. 게시판 스킨 -->
    <section class="page-section page-section--alt sg-section" id="sg-boards">
        <div class="page-inner">
            <h2 class="page-section__title">8. 게시판 스킨 확인</h2>
            <div class="sg-alert sg-alert--info">
                <p>아래 링크의 <code>bo_table</code>은 <strong>예시</strong>입니다. 실제 게시판이 없으면 404·빈 목록이 나올 수 있습니다.</p>
                <p>관리자 → 게시판관리에서 게시판을 만든 뒤 <strong>스킨</strong>을 해당 폴더명으로 지정하고, 아래 bo_table을 실제 테이블명으로 바꿔 확인하세요.</p>
                <p>스킨 경로: <code>/skin/board/{스킨명}/</code> · 모바일: <code>/mobile/skin/board/{스킨명}/</code></p>
            </div>
            <ul class="sg-board-list">
                <?php foreach ($sg_board_skins as $item) {
                    $url = G5_BBS_URL.'/board.php?bo_table='.urlencode($item['bo_table']);
                    ?>
                <li class="sg-board-list__item">
                    <span class="sg-board-list__skin"><code><?php echo htmlspecialchars($item['skin'], ENT_QUOTES, 'UTF-8'); ?></code></span>
                    <span class="sg-board-list__label"><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <a href="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline btn-sm" target="_blank" rel="noopener noreferrer">
                        bo_table=<?php echo htmlspecialchars($item['bo_table'], ENT_QUOTES, 'UTF-8'); ?> ↗
                    </a>
                </li>
                <?php } ?>
            </ul>
        </div>
    </section>
</div>
<script>
(function () {
    var btn = document.getElementById('sgOpenPopup');
    var popup = document.getElementById('cmpPopupBanner');
    if (!btn || !popup) return;
    btn.addEventListener('click', function () {
        popup.removeAttribute('hidden');
        popup.classList.add('is-open');
        popup.setAttribute('aria-hidden', 'false');
    });
})();
</script>
<?php
g5_page_end();
