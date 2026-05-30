<?php
include_once(dirname(__FILE__).'/_init.php');
include_once(G5_PATH.'/section/_helpers.php');

$g5_page_works = array(
    array('img' => 'portfolio-01.jpg', 'title' => '기업 홈페이지', 'tag' => 'Corporate'),
    array('img' => 'portfolio-02.jpg', 'title' => '쇼핑몰', 'tag' => 'Shop'),
    array('img' => 'portfolio-03.jpg', 'title' => '랜딩 페이지', 'tag' => 'Landing'),
    array('img' => 'portfolio-04.jpg', 'title' => '커뮤니티', 'tag' => 'Community'),
    array('img' => 'portfolio-05.jpg', 'title' => '브랜드 사이트', 'tag' => 'Branding'),
    array('img' => 'portfolio-06.jpg', 'title' => '리뉴얼', 'tag' => 'Renewal'),
);

g5_page_start('포트폴리오');
?>
<div class="page-template page-portfolio">
  <header class="page-hero reveal">
    <div class="page-inner">
      <p class="page-eyebrow">Portfolio</p>
      <h1 class="page-title">포트폴리오</h1>
      <p class="page-desc">대표 프로젝트 사례입니다. 썸네일은 img/main/ 폴더 이미지를 사용합니다.</p>
    </div>
  </header>

  <section class="page-section reveal">
    <div class="page-inner">
      <h2 class="page-section__title">프로젝트 사례</h2>
      <p class="page-section__desc">빌더에서 갤러리·필터 UI로 이 그리드 영역을 교체할 수 있습니다.</p>
      <div class="card-grid card-grid--auto">
        <?php foreach ($g5_page_works as $item) { ?>
        <article class="base-card media-card">
          <a href="#" class="media-card__link">
            <div class="media-card__thumb">
              <?php g5_sample_main_media($item['img'], $item['title'], 'media-card__img', 'wide'); ?>
            </div>
            <div class="media-card__body">
              <span class="page-tag"><?php echo get_text($item['tag']); ?></span>
              <h3 class="base-card-title"><?php echo get_text($item['title']); ?></h3>
            </div>
          </a>
        </article>
        <?php } ?>
      </div>
    </div>
  </section>

  <section class="page-section page-section--alt reveal">
    <div class="page-inner">
      <h2 class="page-section__title">작업 범위</h2>
      <div class="page-tags">
        <span class="page-tag">기획</span>
        <span class="page-tag">UI/UX</span>
        <span class="page-tag">퍼블리싱</span>
        <span class="page-tag">그누보드</span>
        <span class="page-tag">영카트</span>
        <span class="page-tag">SEO</span>
      </div>
      <p class="page-section__desc">산업군·규모별 레퍼런스는 상담 시 추가로 안내해 드립니다.</p>
    </div>
  </section>

  <section class="page-section page-cta reveal">
    <div class="page-inner page-cta__inner">
      <h2 class="page-cta__title">비슷한 프로젝트를 계획 중이신가요?</h2>
      <p class="page-cta__desc">레퍼런스 URL과 희망 일정을 알려주세요.</p>
      <div class="page-cta__actions">
        <a href="<?php echo G5_URL; ?>/page/contact.php" class="btn btn-primary">프로젝트 문의</a>
        <a href="<?php echo G5_URL; ?>/#section-portfolio" class="btn btn-outline">메인 포트폴리오</a>
      </div>
    </div>
  </section>
</div>
<?php
g5_page_end();
