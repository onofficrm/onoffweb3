<?php
if (!defined('_GNUBOARD_')) exit;
include_once(G5_PATH.'/section/_helpers.php');

$g5_services = array(
    array('img' => 'service-01.jpg', 'title' => '홈페이지 제작', 'desc' => '기업·브랜드 맞춤 반응형 웹사이트를 기획부터 퍼블리싱까지 제공합니다.'),
    array('img' => 'service-02.jpg', 'title' => '유지보수·운영', 'desc' => '콘텐츠 업데이트, 보안 패치, 성능 점검으로 안정적인 운영을 지원합니다.'),
    array('img' => 'service-03.jpg', 'title' => 'SEO 최적화', 'desc' => '검색 노출을 위한 구조·메타·속도 개선으로 방문자 유입을 돕습니다.'),
    array('img' => 'service-04.jpg', 'title' => '브랜딩 디자인', 'desc' => '로고·키비주얼·가이드라인을 정립해 일관된 브랜드 경험을 만듭니다.'),
    array('img' => 'service-05.jpg', 'title' => '쇼핑몰 연동', 'desc' => '그누보드·영카트 기반 쇼핑 기능과 결제·배송 흐름을 설계합니다.'),
    array('img' => 'service-06.jpg', 'title' => '컨설팅', 'desc' => '현황 분석과 로드맵 제안으로 프로젝트 방향을 명확히 합니다.'),
);
?>
<section class="section section-service section--alt" id="section-service">
  <div class="section-inner">
    <div class="section-head reveal">
      <p class="section-eyebrow">Service</p>
      <h2 class="section-title">제공 서비스</h2>
      <p class="section-desc">필요한 항목만 골라 사용하거나, 카드 수·문구를 자유롭게 수정할 수 있습니다.</p>
    </div>
    <div class="section-content">
      <div class="card-grid card-grid--auto">
        <?php foreach ($g5_services as $item) { ?>
        <article class="base-card media-card reveal">
          <div class="media-card__thumb">
            <?php g5_sample_main_media($item['img'], $item['title'], 'media-card__img', 'card'); ?>
          </div>
          <div class="media-card__body">
            <h3 class="base-card-title"><?php echo get_text($item['title']); ?></h3>
            <p class="base-card-desc"><?php echo get_text($item['desc']); ?></p>
          </div>
        </article>
        <?php } ?>
      </div>
    </div>
  </div>
</section>
