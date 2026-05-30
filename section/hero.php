<?php
if (!defined('_GNUBOARD_')) exit;
include_once(G5_PATH.'/section/_helpers.php');
?>
<section class="section section-hero" id="section-hero">
  <div class="section-inner section-hero__inner">
    <div class="section-hero__content reveal">
      <p class="section-eyebrow">Welcome</p>
      <h2 class="section-title">고객의 성장을 함께하는<br>디지털 파트너</h2>
      <p class="section-desc">기획부터 제작·운영까지 한 번에. 샘플 그누보드 베이스 템플릿으로 빠르게 홈페이지를 구축하고 빌더 디자인을 적용해 보세요.</p>
      <div class="section-actions">
        <a href="#section-contact" class="btn btn-primary">무료 상담 신청</a>
        <a href="#section-service" class="btn btn-outline">서비스 보기</a>
      </div>
    </div>
    <div class="section-hero__visual reveal">
      <?php g5_sample_main_media('hero.jpg', '메인 비주얼', 'section-hero__img', 'hero'); ?>
    </div>
  </div>
</section>
