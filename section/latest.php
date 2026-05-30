<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_PATH.'/section/_helpers.php');

/**
 * 메인 최신글 게시판 설정
 * - bo_table: 그누보드 게시판 ID (관리자에서 생성)
 * - label: 카드 제목 (게시판 미생성 시에도 표시)
 * - rows / subject_len: latest() 인자
 * - skin: card 권장, 없으면 basic 자동
 */
$g5_latest_boards = array(
    array(
        'bo_table'    => 'story',
        'label'       => '신용회복경험담',
        'rows'        => 5,
        'subject_len' => 42,
        'skin'        => 'card',
    ),
    array(
        'bo_table'    => 'news',
        'label'       => '뉴스',
        'rows'        => 5,
        'subject_len' => 42,
        'skin'        => 'card',
    ),
    array(
        'bo_table'    => 'sample',
        'label'       => '샘플',
        'rows'        => 5,
        'subject_len' => 42,
        'skin'        => 'card',
    ),
);
?>
<section class="section section-latest section--alt" id="section-latest">
  <div class="section-inner">
    <div class="section-head reveal">
      <p class="section-eyebrow">Latest</p>
      <h2 class="section-title">최신 소식</h2>
      <p class="section-desc">SEO형 홈페이지를 위해 게시판 최신글을 메인에 노출합니다. 게시판 ID는 아래 배열에서 변경할 수 있습니다.</p>
    </div>
    <div class="section-content">
      <div class="latest-grid">
        <?php foreach ($g5_latest_boards as $board_cfg) { ?>
        <div class="latest-card reveal">
          <?php
          echo g5_sample_latest_render(
              $board_cfg['bo_table'],
              $board_cfg['label'],
              isset($board_cfg['rows']) ? (int) $board_cfg['rows'] : 5,
              isset($board_cfg['subject_len']) ? (int) $board_cfg['subject_len'] : 40,
              isset($board_cfg['skin']) ? $board_cfg['skin'] : 'card'
          );
          ?>
        </div>
        <?php } ?>
      </div>
    </div>
  </div>
</section>
