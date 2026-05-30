<?php
if (!defined('_GNUBOARD_')) exit;

/** 상담 연락처 — tail.php 설정과 맞추려면 동일 값으로 수정 */
$g5_contact_tel_display = '02-123-4567';
$g5_contact_tel_link    = 'tel:021234567';
?>
<section class="section section-contact section--dark" id="section-contact">
  <div class="section-inner">
    <div class="section-head reveal">
      <p class="section-eyebrow">Contact</p>
      <h2 class="section-title">지금 바로 상담해 보세요</h2>
      <p class="section-desc">프로젝트 규모와 일정을 알려주시면 맞춤 제안서를 보내드립니다. 부담 없이 문의해 주세요.</p>
    </div>
    <div class="section-content reveal">
      <div class="contact-cta">
        <a href="<?php echo $g5_contact_tel_link; ?>" class="btn btn-primary contact-cta__tel">
          <i class="fa fa-phone" aria-hidden="true"></i>
          <?php echo get_text($g5_contact_tel_display); ?>
        </a>
        <button type="button" class="btn btn-outline consult-modal-open contact-cta__form" data-target="#consultModal">
          <i class="fa fa-envelope" aria-hidden="true"></i>
          온라인 문의
        </button>
        <a href="<?php echo G5_BBS_URL; ?>/qalist.php" class="btn btn-secondary contact-cta__link">1:1 문의 게시판</a>
      </div>
      <ul class="contact-info">
        <li><strong>이메일</strong> info@example.com</li>
        <li><strong>운영시간</strong> 평일 09:00 – 18:00</li>
        <li><strong>응답</strong> 영업일 기준 24시간 이내 회신</li>
      </ul>
    </div>
  </div>
</section>

<div id="consultModal" class="consult-modal" aria-hidden="true" role="dialog" aria-labelledby="consultModalTitle">
  <div class="consult-modal-overlay"></div>
  <div class="consult-modal__panel">
    <h3 id="consultModalTitle" class="consult-modal__title">상담 문의</h3>
    <p class="consult-modal__desc">아래 내용을 확인하신 후 1:1 문의 게시판 또는 전화로 연락해 주세요.</p>
    <ul class="consult-modal__list">
      <li>프로젝트명·희망 일정</li>
      <li>참고 사이트·기능 요구사항</li>
      <li>예산 범위(선택)</li>
    </ul>
    <div class="consult-modal__actions">
      <a href="<?php echo G5_BBS_URL; ?>/qalist.php" class="btn btn-primary">문의 게시판 이동</a>
      <button type="button" class="btn btn-outline consult-modal-close">닫기</button>
    </div>
  </div>
</div>
