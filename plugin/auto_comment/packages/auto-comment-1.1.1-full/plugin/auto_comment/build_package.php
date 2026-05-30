<?php
if (PHP_SAPI !== 'cli') {
    echo "CLI only\n";
    exit(1);
}

$plugin_dir = __DIR__;
$root = dirname(dirname($plugin_dir));
$manifest = include $plugin_dir.'/manifest.php';
$version = isset($manifest['version']) ? $manifest['version'] : date('YmdHis');
$package_dir = $plugin_dir.'/packages';
$simple_update = isset($_SERVER['AUTO_COMMENT_SIMPLE_UPDATE']) && $_SERVER['AUTO_COMMENT_SIMPLE_UPDATE'] === '1';
$mode = ($simple_update || (isset($argv[1]) && $argv[1] === 'update')) ? 'update' : 'full';
$changed = array();

foreach ($argv as $arg) {
    if (strpos($arg, '--changed=') === 0) {
        $changed = array_filter(array_map('trim', explode(',', substr($arg, 10))));
    }
}

if ($mode === 'update' && !$changed) {
    $base = getenv('AUTO_COMMENT_BASE_REF');
    if ($base) {
        $cmd = 'git diff --name-only '.escapeshellarg($base).' -- '.escapeshellarg('plugin/auto_comment').' '.escapeshellarg('extend/auto_comment.extend.php');
        $output = array();
        exec($cmd, $output);
        $changed = $output;
    }
}

$files = isset($manifest['files']) ? $manifest['files'] : array();
if ($mode === 'update' && !$simple_update) {
    $changed_map = array();
    foreach ($changed as $path) {
        $changed_map[str_replace('\\', '/', ltrim($path, '/'))] = true;
    }
    $files = array_values(array_filter($files, function ($path) use ($changed_map) {
        return isset($changed_map[$path]);
    }));
    if (!$files) {
        echo "No changed package files. Use --changed=file1,file2 or AUTO_COMMENT_BASE_REF.\n";
        exit(1);
    }
}

$files = array_values(array_filter($files, function ($path) {
    return strpos($path, 'plugin/auto_comment/packages/') !== 0;
}));

if (!is_dir($package_dir)) {
    mkdir($package_dir, 0755, true);
}

$name = 'auto-comment-'.$version.'-'.$mode;
$staging = $package_dir.'/'.$name;
auto_comment_package_remove_dir($staging);
mkdir($staging, 0755, true);

foreach ($files as $path) {
    $source = $root.'/'.$path;
    if (!is_file($source)) {
        echo "Skip missing file: {$path}\n";
        continue;
    }

    $target = $staging.'/'.$path;
    $target_dir = dirname($target);
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    copy($source, $target);
}

$note = $mode === 'full'
    ? "자동댓글 배포 패키지 (버전 {$version})\n\n"
        ."[설치 방법 — 신규·기존 사이트 동일]\n"
        ."1) 이 폴더 안의 plugin, extend 를 그누보드 루트(public_html 등)에 그대로 덮어씁니다.\n"
        ."   예) plugin/auto_comment → /plugin/auto_comment\n"
        ."       extend/auto_comment.extend.php → /extend/auto_comment.extend.php\n"
        ."2) 브라우저에서 아래 중 하나를 최고관리자로 1회 실행합니다.\n"
        ."   - 처음 설치: /plugin/auto_comment/install.php\n"
        ."   - 이미 사용 중(덮어쓰기만 한 경우): /plugin/auto_comment/update.php\n"
        ."     (기존 설정, API 키, 게시판 설정, 예약목록은 유지됩니다.)\n\n"
        ."※ 별도 업데이트 전용 패키지 없이, 항상 이 full 패키지로 덮어쓰면 됩니다.\n"
    : "업데이트 패키지입니다.\n1) 이 폴더 안의 plugin, extend 폴더를 그누보드 루트에 덮어쓰세요.\n2) 최고관리자로 /plugin/auto_comment/update.php 에 접속하세요.\n기존 설정, API 키, 예약목록은 유지됩니다.\n";
file_put_contents($staging.'/PACKAGE_README.txt', $note);

if (class_exists('ZipArchive')) {
    $zip_path = $package_dir.'/'.$name.'.zip';
    @unlink($zip_path);
    $zip = new ZipArchive();
    if ($zip->open($zip_path, ZipArchive::CREATE) === true) {
        auto_comment_package_zip_dir($zip, $staging, $staging);
        $zip->close();
        echo $zip_path."\n";
    }
}

echo $staging."\n";

function auto_comment_package_remove_dir($dir)
{
    if (!is_dir($dir)) {
        return;
    }

    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir.'/'.$item;
        if (is_dir($path)) {
            auto_comment_package_remove_dir($path);
        } else {
            unlink($path);
        }
    }
    rmdir($dir);
}

function auto_comment_package_zip_dir($zip, $dir, $base)
{
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir.'/'.$item;
        $local = ltrim(str_replace($base, '', $path), '/');
        if (is_dir($path)) {
            auto_comment_package_zip_dir($zip, $path, $base);
        } else {
            $zip->addFile($path, $local);
        }
    }
}
