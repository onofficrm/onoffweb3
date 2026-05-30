<?php
include_once(dirname(__DIR__) . '/_common.php');
include_once(dirname(__DIR__) . '/bootstrap.php');

onoff_builder_require_admin(defined('G5_ADMIN_URL') ? G5_ADMIN_URL : G5_URL);

$zip_ok = class_exists('ZipArchive');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ZIP 업로드 — onoff-builder-bridge</title>
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
      <a class="is-active" href="<?php echo onoff_builder_escape(onoff_builder_admin_url('upload.php')); ?>">업로드</a>
      <a href="<?php echo onoff_builder_escape(onoff_builder_admin_url('list.php')); ?>">목록</a>
    </nav>
  </div>
</header>
<main class="onoff-builder-admin__main">
  <div class="onoff-builder-admin__inner">
    <div class="onoff-builder-admin__page-head">
      <h1>ZIP 업로드</h1>
      <p class="onoff-builder-admin__lead">빌드 완료된 <strong>dist ZIP</strong>만 업로드하세요. 업로드 후 목록에서 미리보기 URL로 확인할 수 있습니다.</p>
    </div>

    <?php if (!$zip_ok) { ?>
    <p class="onoff-builder-admin__alert">서버에 PHP ZipArchive 확장이 없어 ZIP 업로드를 사용할 수 없습니다. 호스팅 관리자에게 ZipArchive 활성화를 요청하세요.</p>
    <?php } ?>

    <div class="onoff-builder-admin__warn">
      <p><strong>dist ZIP만 업로드하세요.</strong></p>
      <ul>
        <li>ZIP 안에 <code>index.html</code>과 <code>assets</code> 폴더(또는 <code>dist/index.html</code> 구조)가 있어야 합니다.</li>
        <li><code>src/App.tsx</code>, <code>package.json</code>, <code>vite.config.ts</code>가 있는 <strong>원본 React/Vite 프로젝트</strong>는 자동으로 거부됩니다.</li>
        <li>로컬에서 <code>npm install</code> → <code>npm run build</code> 후, 생성된 <strong>dist 폴더 내용</strong>만 압축해 주세요.</li>
      </ul>
    </div>

    <div class="onoff-builder-admin__help">
      <p><strong>ZIP 구성 예시</strong></p>
      <ul>
        <li>방법 A: ZIP 루트에 <code>index.html</code> + <code>assets/</code></li>
        <li>방법 B: ZIP 루트에 <code>dist/index.html</code> + <code>dist/assets/</code></li>
      </ul>
    </div>

    <form class="onoff-builder-admin__form" method="post" action="<?php echo onoff_builder_escape(onoff_builder_admin_url('upload_update.php')); ?>" enctype="multipart/form-data" id="onoffBuilderUploadForm">
      <div class="onoff-builder-admin__field">
        <label for="project_id">프로젝트 ID <span class="req">*</span></label>
        <input type="text" name="project_id" id="project_id" required pattern="[a-z0-9_-]{2,50}" maxlength="50" placeholder="muraku-main" <?php echo $zip_ok ? '' : 'disabled'; ?>>
        <p class="hint">영문 소문자, 숫자, 하이픈(-), 언더바(_) · 2~50자 · URL에 사용 (<code>page.php?id=...</code>)</p>
      </div>
      <div class="onoff-builder-admin__field">
        <label for="project_name">프로젝트 이름 <span class="req">*</span></label>
        <input type="text" name="project_name" id="project_name" required maxlength="100" placeholder="무라커 메인 시안" <?php echo $zip_ok ? '' : 'disabled'; ?>>
        <p class="hint">관리자 목록에 표시되는 이름입니다.</p>
      </div>
      <div class="onoff-builder-admin__field">
        <label for="zip_file">dist ZIP 파일 <span class="req">*</span></label>
        <input type="file" name="zip_file" id="zip_file" accept=".zip,application/zip" required <?php echo $zip_ok ? '' : 'disabled'; ?>>
        <p class="hint">확장자 .zip 만 허용됩니다.</p>
      </div>
      <div class="onoff-builder-admin__form-actions">
        <button type="submit" class="onoff-builder-admin__btn onoff-builder-admin__btn--primary" <?php echo $zip_ok ? '' : 'disabled'; ?>>업로드 및 압축 해제</button>
        <a class="onoff-builder-admin__btn" href="<?php echo onoff_builder_escape(onoff_builder_admin_url('list.php')); ?>">목록 보기</a>
        <a class="onoff-builder-admin__btn" href="<?php echo onoff_builder_escape(onoff_builder_admin_url()); ?>">관리 홈</a>
      </div>
    </form>
  </div>
</main>
<script src="<?php echo onoff_builder_escape(ONOFF_BUILDER_ASSETS_URL . '/js/admin.js'); ?>"></script>
</body>
</html>
