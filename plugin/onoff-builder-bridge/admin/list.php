<?php
include_once(dirname(__DIR__) . '/_common.php');
include_once(dirname(__DIR__) . '/bootstrap.php');

onoff_builder_require_admin(defined('G5_ADMIN_URL') ? G5_ADMIN_URL : G5_URL);

$msg = isset($_GET['msg']) ? trim(strip_tags($_GET['msg'])) : '';
$projects = onoff_builder_get_imports();
$count = count($projects);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>프로젝트 목록 — onoff-builder-bridge</title>
<link rel="stylesheet" href="<?php echo onoff_builder_escape(ONOFF_BUILDER_ASSETS_URL . '/css/admin.css'); ?>">
</head>
<body class="onoff-builder-admin">
<header class="onoff-builder-admin__header">
  <div class="onoff-builder-admin__inner">
    <p class="onoff-builder-admin__brand">
      <a href="<?php echo onoff_builder_escape(onoff_builder_admin_url()); ?>">onoff-builder-bridge</a>
      <span class="onoff-builder-admin__brand-sub">빌더 dist ZIP 업로드 플러그인</span>
    </p>
    <nav class="onoff-builder-admin__topnav" aria-label="관리 메뉴">
      <a href="<?php echo onoff_builder_escape(onoff_builder_admin_url()); ?>">홈</a>
      <a href="<?php echo onoff_builder_escape(onoff_builder_admin_url('upload.php')); ?>">업로드</a>
      <a class="is-active" href="<?php echo onoff_builder_escape(onoff_builder_admin_url('list.php')); ?>">목록</a>
    </nav>
  </div>
</header>
<main class="onoff-builder-admin__main">
  <div class="onoff-builder-admin__inner">
    <div class="onoff-builder-admin__toolbar">
      <div>
        <h1>프로젝트 목록</h1>
        <p class="onoff-builder-admin__lead">등록 <?php echo (int) $count; ?>건 · 업로드된 dist 페이지 URL을 확인·복사할 수 있습니다.</p>
      </div>
      <a class="onoff-builder-admin__btn onoff-builder-admin__btn--primary" href="<?php echo onoff_builder_escape(onoff_builder_admin_url('upload.php')); ?>">+ 새 ZIP 업로드</a>
    </div>

    <?php if ($msg !== '') { ?>
    <p class="onoff-builder-admin__notice"><?php echo onoff_builder_escape($msg); ?></p>
    <?php } ?>

    <div class="onoff-builder-admin__help">
      <p><strong>보기 URL</strong> · <strong>미리보기 ↗</strong>를 눌러 새 창에서 확인하세요. 메뉴·게시판·배너에 연결할 때는 아래 URL 전체를 복사해 사용합니다.</p>
      <p class="onoff-builder-admin__hint" style="margin-top:0.5rem;margin-bottom:0">형식: <code><?php echo onoff_builder_escape(ONOFF_BUILDER_URL); ?>/page.php?id=프로젝트ID</code></p>
    </div>

    <?php if (!$count) { ?>
    <div class="onoff-builder-admin__empty">
      <p class="onoff-builder-admin__empty-title">등록된 프로젝트가 없습니다</p>
      <p>빌드된 dist ZIP을 업로드하면 이 목록에 프로젝트가 표시되고,<br>보기 URL로 페이지를 확인할 수 있습니다.</p>
      <a class="onoff-builder-admin__btn onoff-builder-admin__btn--primary" href="<?php echo onoff_builder_escape(onoff_builder_admin_url('upload.php')); ?>">첫 ZIP 업로드하기</a>
    </div>
    <?php } else { ?>
    <div class="onoff-builder-admin__table-wrap">
      <table class="onoff-builder-admin__table">
        <thead>
          <tr>
            <th>프로젝트명</th>
            <th>project_id</th>
            <th>보기 URL</th>
            <th>업로드일</th>
            <th>관리</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($projects as $p) {
              $pid = isset($p['id']) ? $p['id'] : '';
              $view_url = onoff_builder_page_url($pid);
              $pname = isset($p['name']) ? $p['name'] : $pid;
              ?>
          <tr>
            <td><?php echo onoff_builder_escape($pname); ?></td>
            <td><code><?php echo onoff_builder_escape($pid); ?></code></td>
            <td class="onoff-builder-admin__url-cell">
              <?php if ($view_url !== '') { ?>
              <a class="onoff-builder-admin__view-link" href="<?php echo onoff_builder_escape($view_url); ?>" target="_blank" rel="noopener noreferrer" title="새 창에서 미리보기">미리보기 ↗</a>
              <code class="onoff-builder-admin__url-copy"><?php echo onoff_builder_escape($view_url); ?></code>
              <?php } else { ?>
              <span class="onoff-builder-admin__hint">—</span>
              <?php } ?>
            </td>
            <td><?php echo onoff_builder_escape(isset($p['created_at']) ? $p['created_at'] : '—'); ?></td>
            <td>
              <form method="post"
                action="<?php echo onoff_builder_escape(onoff_builder_admin_url('delete.php')); ?>"
                class="onoff-builder-admin__inline-form js-onoff-builder-delete-form"
                data-confirm="프로젝트 「<?php echo onoff_builder_escape($pname); ?>」 (<?php echo onoff_builder_escape($pid); ?>)을(를) 삭제할까요?&#10;&#10;imports 폴더와 등록 정보가 함께 삭제되며 복구할 수 없습니다.">
                <input type="hidden" name="project_id" value="<?php echo onoff_builder_escape($pid); ?>">
                <button type="submit" class="onoff-builder-admin__btn onoff-builder-admin__btn--danger">삭제</button>
              </form>
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
    <?php } ?>

    <div class="onoff-builder-admin__footer-actions">
      <a class="onoff-builder-admin__btn" href="<?php echo onoff_builder_escape(onoff_builder_admin_url('upload.php')); ?>">ZIP 업로드</a>
      <a class="onoff-builder-admin__btn" href="<?php echo onoff_builder_escape(onoff_builder_admin_url()); ?>">관리 홈</a>
    </div>
  </div>
</main>
<script src="<?php echo onoff_builder_escape(ONOFF_BUILDER_ASSETS_URL . '/js/admin.js'); ?>"></script>
</body>
</html>
