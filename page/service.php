<?php
include_once(dirname(__FILE__).'/_init.php');
include_once(G5_PATH.'/section/_helpers.php');

$g5_page_services = array(
    array('img' => 'service-01.jpg', 'title' => '홈페이지 제작', 'desc' => '기업·브랜드 맞춤 반응형 사이트를 기획부터 오픈까지 진행합니다.'),
    array('img' => 'service-02.jpg', 'title' => '쇼핑몰 구축', 'desc' => '영카트 연동, 상품·주문·결제 흐름을 설계합니다.'),
    array('img' => 'service-03.jpg', 'title' => '유지보수', 'desc' => '보안·백업·콘텐츠 업데이트로 안정적인 운영을 지원합니다.'),
    array('img' => 'service-04.jpg', 'title' => 'SEO·마케팅', 'desc' => '검색 최적화와 랜딩 페이지로 유입을 개선합니다.'),
);

g5_page_start('서비스');
?>
<div class="page-template page-service">
  <header class="page-hero reveal">
    <div class="page-inner">
      <p class="page-eyebrow">Service</p>
      <h1 class="page-title">서비스 안내</h1>
      <p class="page-desc">필요한 서비스만 선택해 적용하거나, 전체 패키지로 진행할 수 있습니다.</p>
    </div>
  </header>

  <section class="page-section reveal">
    <div class="page-inner">
      <h2 class="page-section__title">제공 서비스</h2>
      <p class="page-section__desc">카드 내용·개수는 이 섹션 HTML만 수정하면 됩니다.</p>
      <div class="card-grid card-grid--auto">
        <?php foreach ($g5_page_services as $item) { ?>
        <article class="base-card media-card">
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
  </section>

  <section class="page-section page-section--alt reveal">
    <div class="page-inner">
      <h2 class="page-section__title">진행 프로세스</h2>
      <ol class="page-steps">
        <li class="page-steps__item"><strong>01. 상담</strong><span>요구사항·일정·예산 확인</span></li>
        <li class="page-steps__item"><strong>02. 기획·디자인</strong><span>와이어프레임·시안 확정</span></li>
        <li class="page-steps__item"><strong>03. 개발</strong><span>그누보드 연동·퍼블리싱</span></li>
        <li class="page-steps__item"><strong>04. 오픈·운영</strong><span>검수 후 런칭·유지보수</span></li>
      </ol>
    </div>
  </section>

  <section class="page-section page-cta reveal">
    <div class="page-inner page-cta__inner">
      <h2 class="page-cta__title">맞춤 견적이 필요하신가요?</h2>
      <p class="page-cta__desc">서비스 조합과 일정을 알려주시면 제안서를 보내드립니다.</p>
      <div class="page-cta__actions">
        <a href="<?php echo G5_URL; ?>/page/contact.php" class="btn btn-primary">견적 문의</a>
        <a href="<?php echo G5_URL; ?>/" class="btn btn-outline">메인으로</a>
      </div>
    </div>
  </section>
</div>
<?php
g5_page_end();
