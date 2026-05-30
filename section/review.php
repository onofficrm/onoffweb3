<?php
if (!defined('_GNUBOARD_')) exit;

$g5_reviews = array(
    array('name' => '김○○', 'role' => '스타트업 대표', 'text' => '일정 내에 런칭했고, 이후 유지보수도 빠릅니다. 섹션 구조 덕분에 디자인 수정이 수월했습니다.'),
    array('name' => '이○○', 'role' => '마케팅 팀장', 'text' => '랜딩·문의 전환 영역이 명확해 캠페인 성과가 좋아졌습니다. 모바일에서도 레이아웃이 안정적입니다.'),
    array('name' => '박○○', 'role' => '운영 담당', 'text' => '그누보드 게시판·회원 기능을 그대로 쓰면서 화면만 새롭게 바꿀 수 있어 만족합니다.'),
);
?>
<section class="section section-review" id="section-review">
  <div class="section-inner">
    <div class="section-head reveal">
      <p class="section-eyebrow">Review</p>
      <h2 class="section-title">고객 후기</h2>
      <p class="section-desc">실제 고객사 로고·후기 문구로 교체해 신뢰도를 높일 수 있습니다.</p>
    </div>
    <div class="section-content">
      <div class="card-grid card-grid--3">
        <?php foreach ($g5_reviews as $item) { ?>
        <article class="base-card review-card reveal">
          <div class="review-card__stars" aria-label="별점 5점">★★★★★</div>
          <blockquote class="review-card__text"><?php echo get_text($item['text']); ?></blockquote>
          <footer class="review-card__author">
            <strong><?php echo get_text($item['name']); ?></strong>
            <span><?php echo get_text($item['role']); ?></span>
          </footer>
        </article>
        <?php } ?>
      </div>
    </div>
  </div>
</section>
