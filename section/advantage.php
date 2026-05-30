<?php
if (!defined('_GNUBOARD_')) exit;

$g5_advantages = array(
    array('icon' => '01', 'title' => '빠른 구축', 'desc' => '검증된 섹션 구조로 초기 런칭 기간을 단축합니다.'),
    array('icon' => '02', 'title' => '그누보드 호환', 'desc' => '게시판·회원·관리자 기능을 유지한 채 디자인만 교체합니다.'),
    array('icon' => '03', 'title' => '반응형 기본', 'desc' => 'PC·태블릿·모바일에서 자연스러운 1열·다열 레이아웃을 제공합니다.'),
    array('icon' => '04', 'title' => '유지보수 용이', 'desc' => '섹션 단위 파일 분리로 수정·추가 범위가 명확합니다.'),
);
?>
<section class="section section-advantage" id="section-advantage">
  <div class="section-inner">
    <div class="section-head reveal">
      <p class="section-eyebrow">Advantage</p>
      <h2 class="section-title">우리의 강점</h2>
      <p class="section-desc">차별점은 아이콘·수치·문구를 바꿔 빌더 디자인에 맞게 확장할 수 있습니다.</p>
    </div>
    <div class="section-content">
      <div class="card-grid card-grid--4">
        <?php foreach ($g5_advantages as $item) { ?>
        <article class="base-card icon-card reveal">
          <div class="icon-card__icon" aria-hidden="true"><?php echo $item['icon']; ?></div>
          <h3 class="base-card-title"><?php echo get_text($item['title']); ?></h3>
          <p class="base-card-desc"><?php echo get_text($item['desc']); ?></p>
        </article>
        <?php } ?>
      </div>
    </div>
  </div>
</section>
