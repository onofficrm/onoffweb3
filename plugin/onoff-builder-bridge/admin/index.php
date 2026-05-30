<?php
include_once(dirname(__DIR__) . '/_common.php');
include_once(dirname(__DIR__) . '/bootstrap.php');

onoff_builder_require_admin(defined('G5_ADMIN_URL') ? G5_ADMIN_URL : G5_URL);

$obb_active = 'home';
$obb_title = 'onoff-builder-bridge 관리';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo onoff_builder_escape($obb_title); ?></title>
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
      <a class="is-active" href="<?php echo onoff_builder_escape(onoff_builder_admin_url()); ?>">홈</a>
      <a href="<?php echo onoff_builder_escape(onoff_builder_admin_url('upload.php')); ?>">업로드</a>
      <a href="<?php echo onoff_builder_escape(onoff_builder_admin_url('list.php')); ?>">목록</a>
    </nav>
  </div>
</header>
<main class="onoff-builder-admin__main">
  <div class="onoff-builder-admin__inner">
    <div class="onoff-builder-admin__page-head">
      <h1>onoff-builder-bridge</h1>
      <p class="onoff-builder-admin__lead">외부 빌더(React/Vite 등)에서 만든 <strong>dist 결과물</strong>을 ZIP으로 올려 그누보드 사이트에 독립 페이지로 연결합니다.</p>
    </div>

    <div class="onoff-builder-admin__actions">
      <a class="onoff-builder-admin__btn onoff-builder-admin__btn--primary" href="<?php echo onoff_builder_escape(onoff_builder_admin_url('upload.php')); ?>">ZIP 업로드</a>
      <a class="onoff-builder-admin__btn" href="<?php echo onoff_builder_escape(onoff_builder_admin_url('list.php')); ?>">프로젝트 목록</a>
    </div>

    <div class="onoff-builder-admin__card">
      <h2 class="onoff-builder-admin__card-title">사용 흐름</h2>
      <ol class="onoff-builder-admin__steps">
        <li>빌더에서 프로젝트를 다운로드합니다.</li>
        <li>터미널에서 <code>npm install</code>을 실행합니다.</li>
        <li><code>npm run build</code>로 dist를 생성합니다.</li>
        <li><strong>dist 폴더 내용</strong>을 ZIP으로 압축합니다. (<code>index.html</code>, <code>assets/</code> 포함)</li>
        <li>이 관리 화면에서 ZIP을 업로드합니다.</li>
        <li><code>page.php?id=프로젝트ID</code> URL로 화면을 확인하고 메뉴에 연결합니다.</li>
      </ol>
    </div>

    <div class="onoff-builder-admin__help">
      <p><strong>주의</strong> · 원본 소스(<code>src/App.tsx</code>, <code>vite.config.ts</code> 등) ZIP은 업로드할 수 없습니다. 반드시 빌드된 dist만 올려 주세요.</p>
    </div>

    <p class="onoff-builder-admin__footer-meta">버전 <?php echo onoff_builder_escape(ONOFF_BUILDER_VERSION); ?></p>
  </div>
</main>
<script src="<?php echo onoff_builder_escape(ONOFF_BUILDER_ASSETS_URL . '/js/admin.js'); ?>"></script>
</body>
</html>
