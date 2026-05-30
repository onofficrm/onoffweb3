<?php
/**
 * 지역 SEO 페이지 템플릿 (샘플)
 *
 * URL: /page/local-template.php
 *
 * ★ 실제 운영 페이지는 이 파일을 복사해 page/local-{지역}-{서비스}.php 로 저장한 뒤
 *    아래 [지역 페이지 변수] 블록과 본문 문구를 지역·서비스에 맞게 전부 수정하세요.
 * ★ 동일 문구를 여러 URL에 그대로 복사하면 중복 콘텐츠로 불이익을 받을 수 있습니다.
 * ★ 자동 대량 생성 기능은 없습니다. JSON은 참고용 샘플입니다.
 */
include_once dirname(__FILE__) . '/_init.php';
include_once G5_PATH . '/section/_helpers.php';

if (!function_exists('g5site_cfg') && is_file(G5_PATH . '/_site.config.php')) {
    include_once G5_PATH . '/_site.config.php';
}

/**
 * 샘플 JSON에서 지역 페이지 데이터 조회 (참고·미리보기용)
 *
 * @param string $area
 * @param string $service
 * @return array|null
 */
if (!function_exists('g5_local_page_find_sample')) {
    function g5_local_page_find_sample($area, $service)
    {
        $json_file = G5_PATH . '/data/local-pages.sample.json';
        if (!is_file($json_file)) {
            return null;
        }

        $raw = @file_get_contents($json_file);
        if ($raw === false || $raw === '') {
            return null;
        }

        $list = json_decode($raw, true);
        if (!is_array($list)) {
            return null;
        }

        $area = trim((string) $area);
        $service = trim((string) $service);

        foreach ($list as $row) {
            if (!is_array($row)) {
                continue;
            }
            if (isset($row['area'], $row['service'])
                && trim((string) $row['area']) === $area
                && trim((string) $row['service']) === $service) {
                return $row;
            }
        }

        return null;
    }
}

/* --------------------------------------------------------------------------
 * [지역 페이지 변수] — 복사한 파일에서 반드시 수정
 * -------------------------------------------------------------------------- */
$local_area         = '강남';
$local_service      = '피부과';
$local_main_keyword = '강남 피부과';
$local_description  = '강남 지역 고객을 위한 피부과 안내 페이지입니다. 지역별 맞춤 상담과 진료 안내를 제공합니다.';
$local_page_mode    = 'local'; /* local: 오시는 길 | online: 상담 CTA 중심 */

/* 미리보기: /page/local-template.php?preview=1&area=수원&service=개인회생 (샘플 JSON 참고) */
if (!empty($_GET['preview']) && isset($_GET['area'], $_GET['service'])) {
    $preview_row = g5_local_page_find_sample($_GET['area'], $_GET['service']);
    if (is_array($preview_row)) {
        $local_area         = $preview_row['area'];
        $local_service      = $preview_row['service'];
        $local_main_keyword = isset($preview_row['main_keyword']) ? $preview_row['main_keyword'] : ($local_area . ' ' . $local_service);
        $local_description  = isset($preview_row['description']) ? $preview_row['description'] : $local_description;
        if (!empty($preview_row['page_mode'])) {
            $local_page_mode = $preview_row['page_mode'];
        }
    }
}

$local_area         = trim(strip_tags((string) $local_area));
$local_service      = trim(strip_tags((string) $local_service));
$local_main_keyword = trim(strip_tags((string) $local_main_keyword));
$local_description  = trim(strip_tags((string) $local_description));
$local_page_mode    = ($local_page_mode === 'online') ? 'online' : 'local';

if ($local_main_keyword === '' && $local_area !== '' && $local_service !== '') {
    $local_main_keyword = $local_area . ' ' . $local_service;
}

$local_site_name = function_exists('g5site_cfg') ? g5site_cfg('site_name', '본 사이트') : '본 사이트';
$local_company   = function_exists('g5site_cfg') ? g5site_cfg('company_name', $local_site_name) : $local_site_name;
$local_phone     = function_exists('g5site_cfg') ? g5site_cfg('phone', '010-0000-0000') : '010-0000-0000';
$local_tel_link  = function_exists('g5site_tel_link') ? g5site_tel_link($local_phone) : 'tel:' . preg_replace('/[^0-9+]/', '', $local_phone);
$local_kakao     = function_exists('g5site_cfg') ? g5site_cfg('kakao_url', '#') : '#';
$local_address   = function_exists('g5site_cfg') ? g5site_cfg('address', '') : '';

$local_page_url = '';
if (!empty($_SERVER['HTTP_HOST'])) {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
    $scheme = $https ? 'https' : 'http';
    $uri = isset($_SERVER['REQUEST_URI']) ? strtok($_SERVER['REQUEST_URI'], '?') : '/page/local-template.php';
    $local_page_url = $scheme . '://' . $_SERVER['HTTP_HOST'] . $uri;
} elseif (defined('G5_URL')) {
    $local_page_url = G5_URL . '/page/local-template.php';
}

$page_title       = $local_main_keyword !== '' ? $local_main_keyword . ' | ' . $local_site_name : '지역 서비스 안내 | ' . $local_site_name;
$page_description = $local_description !== '' ? $local_description : $local_area . ' ' . $local_service . ' 안내 — ' . $local_site_name;
$page_keywords    = $local_main_keyword;
/* LocalBusiness JSON-LD는 하단 components/schema/local-business.php 만 사용.
 * $page_schema_type = 'LocalBusiness' 는 seo-meta @graph 와 중복되므로 설정하지 않습니다. */

/* --------------------------------------------------------------------------
 * FAQ — 화면·FAQPage Schema 공통 (표시되는 항목만)
 * -------------------------------------------------------------------------- */
$g5_faq_items = array(
    array(
        'question' => $local_area . ' ' . $local_service . ' 상담은 어떻게 받나요?',
        'answer'   => '전화·카카오·온라인 상담 폼으로 접수해 주시면 담당자가 ' . $local_area . ' 지역 조건에 맞춰 안내드립니다.',
    ),
    array(
        'question' => $local_area . ' 외 지역도 이용할 수 있나요?',
        'answer'   => '서비스 특성에 따라 가능 여부가 다릅니다. 문의 시 거주·방문 지역을 알려 주시면 확인해 드립니다.',
    ),
    array(
        'question' => '비용은 어떻게 확인하나요?',
        'answer'   => '상담 후 서비스 범위·일정에 따라 견적을 안내합니다. 페이지에 표기된 금액은 샘플이며 실제와 다를 수 있습니다.',
    ),
);

$faq_schema_items = g5_sample_faq_schema_items($g5_faq_items);

/* 지역별 문제·차별점·후기 샘플 — 복사 후 반드시 실제 내용으로 교체 */
$local_problems = array(
    $local_area . '에서 ' . $local_service . '를 찾을 때 정보가 분산되어 비교가 어렵습니다.',
    '지역 특성(교통·상권·상담 시간)을 반영한 맞춤 안내가 필요합니다.',
    '첫 방문·첫 상담 전에 신뢰할 수 있는 설명이 부족합니다.',
);

$local_differentiators = array(
    array('title' => $local_area . ' 맞춤 상담', 'desc' => '지역 고객 사례와 절차를 기준으로 안내합니다.'),
    array('title' => '명확한 진행 절차', 'desc' => '문의부터 완료까지 단계별로 안내합니다.'),
    array('title' => '신속한 응대', 'desc' => '접수 후 담당자가 확인하여 연락드립니다.'),
    array('title' => '사후 안내', 'desc' => '진행 중 궁금한 사항을 지속적으로 지원합니다.'),
);

$local_reviews_sample = array(
    array('name' => '김○○', 'text' => $local_area . '에서 ' . $local_service . ' 알아볼 때 절차 설명이 자세해 선택했습니다.'),
    array('name' => '이○○', 'text' => '상담 응대가 빨라 일정 조율이 수월했습니다.'),
    array('name' => '박○○', 'text' => '지역 조건에 맞는 안내를 받아 만족했습니다.'),
);

g5_page_start($local_main_keyword !== '' ? $local_main_keyword : '지역 서비스 안내');
?>
<div class="page-template page-local">
  <!-- Hero -->
  <header class="page-hero page-local__hero reveal">
    <div class="page-inner">
      <p class="page-eyebrow">Local SEO</p>
      <h1 class="page-title"><?php echo get_text($local_main_keyword); ?></h1>
      <p class="page-desc"><?php echo get_text($local_description); ?></p>
      <p class="page-local__notice">이 페이지는 <strong>템플릿 샘플</strong>입니다. 실제 납품 시 지역·서비스·가격·후기·FAQ를 모두 수정한 뒤 별도 URL로 공개하세요.</p>
      <div class="page-cta__actions page-local__hero-actions">
        <button type="button" class="btn btn-primary consult-modal-open">상담 문의</button>
        <a href="<?php echo htmlspecialchars($local_tel_link, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline">전화 문의</a>
      </div>
    </div>
  </header>

  <!-- 지역 고객 문제 -->
  <section class="page-section reveal" id="local-problems">
    <div class="page-inner">
      <h2 class="page-section__title"><?php echo get_text($local_area); ?>에서 흔한 고민</h2>
      <p class="page-section__desc"><?php echo get_text($local_area); ?> 지역 고객이 <?php echo get_text($local_service); ?>를 검색할 때 자주 겪는 상황입니다. <em>아래 문구는 샘플이며 지역별로 수정하세요.</em></p>
      <ul class="page-list">
        <?php foreach ($local_problems as $problem) { ?>
        <li><?php echo get_text($problem); ?></li>
        <?php } ?>
      </ul>
    </div>
  </section>

  <!-- 서비스 소개 -->
  <section class="page-section page-section--alt reveal" id="local-service">
    <div class="page-inner page-inner--split">
      <div class="page-section__text">
        <h2 class="page-section__title"><?php echo get_text($local_service); ?> 서비스 안내</h2>
        <p class="page-section__desc">
          <?php echo get_text($local_company); ?>는 <?php echo get_text($local_area); ?> 지역 고객을 위한 <?php echo get_text($local_service); ?> 상담·안내를 제공합니다.
          검색 키워드 <strong><?php echo get_text($local_main_keyword); ?></strong>에 맞춰 필요한 정보를 정리했습니다.
        </p>
        <ul class="page-list">
          <li>초기 상담 및 맞춤 안내</li>
          <li>진행 절차·준비 사항 설명</li>
          <li>지역·일정에 따른 옵션 제안</li>
        </ul>
      </div>
      <div class="page-section__media">
        <?php g5_sample_main_media('service.jpg', $local_main_keyword . ' 안내 이미지', 'page-section__img', 'wide'); ?>
      </div>
    </div>
  </section>

  <!-- 차별점 -->
  <section class="page-section reveal" id="local-advantages">
    <div class="page-inner">
      <h2 class="page-section__title">선택해야 하는 이유</h2>
      <div class="card-grid card-grid--auto">
        <?php foreach ($local_differentiators as $card) { ?>
        <article class="base-card icon-card">
          <h3 class="base-card-title"><?php echo get_text($card['title']); ?></h3>
          <p class="base-card-desc"><?php echo get_text($card['desc']); ?></p>
        </article>
        <?php } ?>
      </div>
    </div>
  </section>

  <!-- 진행 과정 -->
  <section class="page-section page-section--alt reveal" id="local-process">
    <div class="page-inner">
      <h2 class="page-section__title">진행 과정</h2>
      <ol class="page-list page-local__process">
        <li><strong>문의</strong> — 전화·카카오·온라인 폼으로 접수</li>
        <li><strong>상담</strong> — <?php echo get_text($local_area); ?> 조건에 맞춰 안내</li>
        <li><strong>진행</strong> — 일정·서비스 범위 확정 후 진행</li>
        <li><strong>완료</strong> — 결과 안내 및 사후 문의 지원</li>
      </ol>
    </div>
  </section>

  <!-- 후기/사례 (샘플 구조 — 게시판 연동 없음) -->
  <section class="page-section reveal" id="local-reviews">
    <div class="page-inner">
      <h2 class="page-section__title">후기 · 사례</h2>
      <p class="page-section__desc">실제 운영 시 review 게시판 연동 또는 검증된 후기로 교체하세요. 아래는 레이아웃 샘플입니다.</p>
      <div class="card-grid card-grid--3">
        <?php foreach ($local_reviews_sample as $review) { ?>
        <article class="base-card">
          <p class="base-card-desc">“<?php echo get_text($review['text']); ?>”</p>
          <p class="base-card-title" style="font-size:0.875rem;margin-top:0.75rem;">— <?php echo get_text($review['name']); ?></p>
        </article>
        <?php } ?>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section class="page-section page-section--alt reveal" id="local-faq">
    <div class="page-inner">
      <h2 class="page-section__title"><?php echo get_text($local_area . ' ' . $local_service); ?> FAQ</h2>
      <?php if (!empty($g5_faq_items)) { ?>
      <div class="faq-list">
        <?php foreach ($g5_faq_items as $i => $faq) {
            $faq_q = isset($faq['question']) ? $faq['question'] : '';
            $faq_a = isset($faq['answer']) ? $faq['answer'] : '';
            if ($faq_q === '' || $faq_a === '') {
                continue;
            }
            ?>
        <div class="faq-item<?php echo $i === 0 ? ' is-open' : ''; ?>">
          <button type="button" class="faq-question" aria-expanded="<?php echo $i === 0 ? 'true' : 'false'; ?>">
            <?php echo get_text($faq_q); ?>
          </button>
          <div class="faq-answer">
            <p><?php echo get_text($faq_a); ?></p>
          </div>
        </div>
        <?php } ?>
      </div>
      <?php } ?>
    </div>
  </section>

  <?php if ($local_page_mode === 'local') { ?>
  <!-- 오시는 길 (로컬 비즈니스) -->
  <section class="page-section reveal" id="local-location">
    <div class="page-inner">
      <h2 class="page-section__title">오시는 길</h2>
      <p class="page-section__desc"><?php echo get_text($local_address !== '' && $local_address !== '주소를 입력하세요' ? $local_address : $local_area . ' 지역 — _site.config.php address 항목을 수정하세요.'); ?></p>
      <?php
      $kakao_map_file = G5_PATH . '/components/kakao-map.php';
      if (is_file($kakao_map_file)) {
          include $kakao_map_file;
      }
      ?>
    </div>
  </section>
  <?php } else { ?>
  <!-- 온라인 상담 CTA -->
  <section class="page-section page-cta page-cta--dark reveal" id="local-contact">
    <div class="page-inner page-cta__inner">
      <h2 class="page-cta__title"><?php echo get_text($local_area); ?> <?php echo get_text($local_service); ?> 상담</h2>
      <p class="page-cta__desc">온라인·전화 상담으로 일정과 프로그램을 안내해 드립니다.</p>
      <div class="page-cta__actions">
        <button type="button" class="btn btn-primary consult-modal-open">상담 문의</button>
        <a href="<?php echo htmlspecialchars($local_tel_link, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline">전화 문의</a>
        <?php if ($local_kakao !== '' && $local_kakao !== '#') { ?>
        <a href="<?php echo htmlspecialchars($local_kakao, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline" target="_blank" rel="noopener noreferrer">카카오톡</a>
        <?php } ?>
      </div>
    </div>
  </section>
  <?php } ?>

  <!-- 관련 콘텐츠 -->
  <section class="page-section page-section--alt reveal" id="local-related">
    <div class="page-inner">
      <h2 class="page-section__title">관련 콘텐츠</h2>
      <p class="page-section__desc">관련 글·다른 지역 페이지 링크를 연결하면 내부 SEO에 도움이 됩니다.</p>
      <?php
      $latest_bo_table = 'news';
      $latest_limit = 4;
      $latest_title = '최신 소식';
      include_once G5_PATH . '/components/latest-posts.php';
      ?>
      <ul class="page-list">
        <li><a href="<?php echo G5_URL; ?>/page/service.php">서비스 소개</a></li>
        <li><a href="<?php echo G5_URL; ?>/page/contact.php">문의하기</a></li>
        <li><a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=notice">공지사항</a></li>
      </ul>
    </div>
  </section>

  <section class="page-section page-cta reveal">
    <div class="page-inner page-cta__inner">
      <h2 class="page-cta__title"><?php echo get_text($local_main_keyword); ?> 상담 받기</h2>
      <p class="page-cta__desc">지역·일정·예산을 알려 주시면 맞춤 안내를 드립니다.</p>
      <button type="button" class="btn btn-primary consult-modal-open">무료 상담 신청</button>
    </div>
  </section>
</div>
<?php
/* Schema — BreadcrumbList, LocalBusiness, FAQPage */
$breadcrumb_items = array(
    array('name' => '홈', 'url' => defined('G5_URL') ? G5_URL : '/'),
    array('name' => $local_main_keyword !== '' ? $local_main_keyword : ($local_area . ' ' . $local_service), 'url' => $local_page_url),
);
if (is_file(G5_PATH . '/components/schema/breadcrumb.php')) {
    include G5_PATH . '/components/schema/breadcrumb.php';
}

if ($local_page_mode === 'local' && is_file(G5_PATH . '/components/schema/local-business.php')) {
    include G5_PATH . '/components/schema/local-business.php';
}

if (!empty($faq_schema_items) && is_file(G5_PATH . '/components/schema/faq.php')) {
    include G5_PATH . '/components/schema/faq.php';
}

/* Service schema (선택) — 서비스명 강조 시
 * $service_name = $local_main_keyword;
 * $service_description = $local_description;
 * $service_area = $local_area;
 * include G5_PATH . '/components/schema/service.php';
 */

g5_page_end();
