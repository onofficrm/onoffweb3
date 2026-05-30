<?php
include_once(dirname(__FILE__).'/_init.php');
include_once(G5_PATH.'/section/_helpers.php');

g5_page_start('회사소개');
?>
<div class="page-template page-about">
  <header class="page-hero reveal">
    <div class="page-inner">
      <p class="page-eyebrow">About</p>
      <h1 class="page-title">회사 소개</h1>
      <p class="page-desc">고객의 비즈니스 성장을 돕는 웹·디지털 파트너입니다. 빌더 디자인으로 이 영역을 교체할 수 있습니다.</p>
    </div>
  </header>

  <section class="page-section page-section--vision reveal">
    <div class="page-inner">
      <h2 class="page-section__title">비전과 미션</h2>
      <p class="page-section__desc">기술과 디자인의 균형으로 신뢰할 수 있는 디지털 경험을 만듭니다.</p>
      <div class="card-grid card-grid--3">
        <article class="base-card icon-card">
          <div class="icon-card__icon" aria-hidden="true">V</div>
          <h3 class="base-card-title">Vision</h3>
          <p class="base-card-desc">모든 기업이 온라인에서 가치를 전달할 수 있는 환경을 만듭니다.</p>
        </article>
        <article class="base-card icon-card">
          <div class="icon-card__icon" aria-hidden="true">M</div>
          <h3 class="base-card-title">Mission</h3>
          <p class="base-card-desc">그누보드 기반의 안정적인 플랫폼과 맞춤 디자인을 제공합니다.</p>
        </article>
        <article class="base-card icon-card">
          <div class="icon-card__icon" aria-hidden="true">C</div>
          <h3 class="base-card-title">Core Value</h3>
          <p class="base-card-desc">소통, 품질, 지속 가능한 유지보수를 핵심 가치로 합니다.</p>
        </article>
      </div>
    </div>
  </section>

  <section class="page-section page-section--story page-section--alt reveal">
    <div class="page-inner page-inner--split">
      <div class="page-section__text">
        <h2 class="page-section__title">우리의 이야기</h2>
        <p class="page-section__desc">2010년 설립 이후 500건 이상의 웹 프로젝트를 수행했습니다. 기획·디자인·개발·운영을 한 팀에서 진행해 커뮤니케이션 비용을 줄입니다.</p>
        <ul class="page-list">
          <li>그누보드·영카트 기반 구축 경험</li>
          <li>반응형·접근성·SEO 기본 적용</li>
          <li>런칭 이후 유지보수·콘텐츠 지원</li>
        </ul>
      </div>
      <div class="page-section__media">
        <?php g5_sample_main_media('about.jpg', '회사 소개 이미지', 'page-section__img', 'wide'); ?>
      </div>
    </div>
  </section>

  <section class="page-section page-cta reveal">
    <div class="page-inner page-cta__inner">
      <h2 class="page-cta__title">함께 일할 파트너를 찾고 계신가요?</h2>
      <p class="page-cta__desc">프로젝트 규모와 관계없이 편하게 문의해 주세요.</p>
      <div class="page-cta__actions">
        <a href="<?php echo G5_URL; ?>/page/contact.php" class="btn btn-primary">문의하기</a>
        <a href="<?php echo G5_URL; ?>/page/service.php" class="btn btn-outline">서비스 보기</a>
      </div>
    </div>
  </section>
</div>
<?php
g5_page_end();
