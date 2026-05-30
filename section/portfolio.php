<?php
if (!defined('_GNUBOARD_')) exit;
include_once(G5_PATH.'/section/_helpers.php');

$g5_portfolios = array(
    array('img' => 'portfolio-01.jpg', 'title' => '기업 홈페이지 리뉴얼', 'desc' => '브랜드 아이덴티티 반영 · 반응형'),
    array('img' => 'portfolio-02.jpg', 'title' => '쇼핑몰 구축', 'desc' => '영카트 연동 · 상품·결제 플로우'),
    array('img' => 'portfolio-03.jpg', 'title' => '랜딩 페이지', 'desc' => '캠페인 전용 · 전환율 중심 구성'),
    array('img' => 'portfolio-04.jpg', 'title' => '커뮤니티 사이트', 'desc' => '게시판·회원 기능 커스텀'),
    array('img' => 'portfolio-05.jpg', 'title' => '포트폴리오 사이트', 'desc' => '갤러리형 콘텐츠 구조'),
    array('img' => 'portfolio-06.jpg', 'title' => '관리자 대시보드', 'desc' => '운영 효율 UI 개선'),
);
?>
<section class="section section-portfolio section--alt" id="section-portfolio">
  <div class="section-inner">
    <div class="section-head reveal">
      <p class="section-eyebrow">Portfolio</p>
      <h2 class="section-title">포트폴리오</h2>
      <p class="section-desc">실제 프로젝트 이미지는 img/main/ 폴더에 넣으면 자동으로 표시됩니다.</p>
    </div>
    <div class="section-content">
      <div class="card-grid card-grid--auto">
        <?php foreach ($g5_portfolios as $item) { ?>
        <article class="base-card media-card reveal">
          <a href="#" class="media-card__link">
            <div class="media-card__thumb">
              <?php g5_sample_main_media($item['img'], $item['title'], 'media-card__img', 'wide'); ?>
            </div>
            <div class="media-card__body">
              <h3 class="base-card-title"><?php echo get_text($item['title']); ?></h3>
              <p class="base-card-desc"><?php echo get_text($item['desc']); ?></p>
            </div>
          </a>
        </article>
        <?php } ?>
      </div>
    </div>
  </div>
</section>
