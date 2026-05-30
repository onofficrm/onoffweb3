<?php
if (!defined('_GNUBOARD_')) exit;

include_once G5_PATH . '/section/_helpers.php';

/**
 * FAQ 데이터 — 화면 아코디언 + FAQPage Schema 공통
 *
 * ★ 이 배열만 수정하세요. 화면에 보이는 항목 = Schema에 출력되는 항목입니다.
 * - 숨김·초안·미표시 FAQ는 넣지 마세요.
 * - 키: question, answer (구형 q/a 도 헬퍼에서 호환)
 */
$g5_faq_items = array(
    array(
        'question' => '제작 기간은 얼마나 걸리나요?',
        'answer'   => '페이지 규모와 기능에 따라 다르며, 일반 기업 홈페이지는 2~4주를 기준으로 안내드립니다.',
    ),
    array(
        'question' => '그누보드 기본 기능은 유지되나요?',
        'answer'   => '네. 게시판·회원·관리자 기능은 그대로 두고 레이아웃·디자인만 템플릿 구조에 맞게 적용합니다.',
    ),
    array(
        'question' => '이미지만 교체해도 되나요?',
        'answer'   => 'img/main/ 경로에 동일 파일명으로 업로드하면 섹션에 자동 반영됩니다. 없으면 플레이스홀더가 표시됩니다.',
    ),
    array(
        'question' => '유지보수는 어떻게 진행되나요?',
        'answer'   => '월 단위 또는 건별로 콘텐츠·기능 업데이트, 보안·백업 점검을 지원합니다.',
    ),
);

/* 하위 호환: 기존 $g5_faqs 참조 */
$g5_faqs = $g5_faq_items;

$faq_schema_items = g5_sample_faq_schema_items($g5_faq_items);
?>
<section class="section section-faq section--alt" id="section-faq">
  <div class="section-inner">
    <div class="section-head reveal">
      <p class="section-eyebrow">FAQ</p>
      <h2 class="section-title">자주 묻는 질문</h2>
      <p class="section-desc">질문·답변은 아래 <code>$g5_faq_items</code> 배열에서 관리합니다. 화면과 FAQ Schema가 동일한 내용을 사용합니다.</p>
    </div>
    <div class="section-content reveal">
      <?php if (!empty($g5_faq_items)) { ?>
      <div class="faq-list" data-accordion-mode="single">
        <?php foreach ($g5_faq_items as $i => $faq) {
            $faq_q = isset($faq['question']) ? $faq['question'] : (isset($faq['q']) ? $faq['q'] : '');
            $faq_a = isset($faq['answer']) ? $faq['answer'] : (isset($faq['a']) ? $faq['a'] : '');
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
  </div>
</section>
<?php
/* FAQPage JSON-LD — 화면에 표시된 FAQ와 동일 배열 ($faq_schema_items) */
if (!empty($faq_schema_items)) {
    $schema_file = G5_PATH . '/components/schema/faq.php';
    if (is_file($schema_file)) {
        include $schema_file;
    }
}
