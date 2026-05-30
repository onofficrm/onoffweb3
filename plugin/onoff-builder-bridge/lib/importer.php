<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('onoff_builder_validate_project_id')) {
    function onoff_builder_validate_project_id($id)
    {
        $id = trim((string) $id);
        if ($id === '') {
            return false;
        }
        if (strlen($id) < 2 || strlen($id) > 50) {
            return false;
        }
        if (!preg_match('/^[a-z0-9][a-z0-9_-]*$/', $id)) {
            return false;
        }
        if (strpos($id, '..') !== false) {
            return false;
        }

        return true;
    }
}

if (!function_exists('onoff_builder_sanitize_project_id')) {
    function onoff_builder_sanitize_project_id($id)
    {
        $id = strtolower(trim((string) $id));
        $id = preg_replace('/[^a-z0-9_-]/', '', $id);
        $id = preg_replace('/^-+/', '', $id);

        return substr($id, 0, 50);
    }
}

if (!function_exists('onoff_builder_project_dir')) {
    function onoff_builder_project_dir($project_id)
    {
        $id = onoff_builder_sanitize_project_id($project_id);
        if ($id === '') {
            return '';
        }

        return ONOFF_BUILDER_IMPORTS_PATH . '/' . $id;
    }
}

if (!function_exists('onoff_builder_project_exists')) {
    function onoff_builder_project_exists($project_id)
    {
        if (onoff_builder_has_import($project_id)) {
            return true;
        }

        $dir = onoff_builder_project_dir($project_id);
        if ($dir === '' || !is_dir($dir)) {
            return false;
        }

        $items = @scandir($dir);
        if (!is_array($items)) {
            return false;
        }

        foreach ($items as $item) {
            if ($item !== '.' && $item !== '..') {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('onoff_builder_remove_dir')) {
    function onoff_builder_remove_dir($dir)
    {
        if (!is_dir($dir)) {
            return true;
        }

        $items = @scandir($dir);
        if (!is_array($items)) {
            return false;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                if (!onoff_builder_remove_dir($path)) {
                    return false;
                }
            } else {
                if (!@unlink($path)) {
                    return false;
                }
            }
        }

        return @rmdir($dir);
    }
}

if (!function_exists('onoff_builder_is_vite_source_project')) {
    function onoff_builder_is_vite_source_project($project_dir)
    {
        if (!is_dir($project_dir)) {
            return false;
        }

        if (!is_file($project_dir . '/package.json')) {
            return false;
        }
        if (!is_dir($project_dir . '/src')) {
            return false;
        }
        if (!is_file($project_dir . '/vite.config.ts')) {
            return false;
        }

        return is_file($project_dir . '/src/App.tsx')
            || is_file($project_dir . '/src/App.jsx')
            || is_file($project_dir . '/src/main.tsx')
            || is_file($project_dir . '/src/main.jsx');
    }
}

if (!function_exists('onoff_builder_vite_source_message')) {
    function onoff_builder_vite_source_message()
    {
        return "이 ZIP은 빌드된 dist 결과물이 아니라 React/Vite 원본 프로젝트로 보입니다.\n"
            . "터미널에서 npm install 후 npm run build를 실행하고, 생성된 dist 폴더를 ZIP으로 업로드해주세요.";
    }
}

if (!function_exists('onoff_builder_find_index_html')) {
    /**
     * index.html 상대 경로 반환 (없으면 빈 문자열)
     */
    function onoff_builder_find_index_html($project_dir)
    {
        if (!is_dir($project_dir)) {
            return '';
        }

        if (is_file($project_dir . '/index.html')) {
            return 'index.html';
        }

        if (is_file($project_dir . '/dist/index.html')) {
            return 'dist/index.html';
        }

        $candidates = array();
        $items = @scandir($project_dir);
        if (!is_array($items)) {
            return '';
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $level1 = $project_dir . '/' . $item;
            if (!is_dir($level1)) {
                continue;
            }

            if (is_file($level1 . '/index.html')) {
                $candidates[] = $item . '/index.html';
            }

            if (is_file($level1 . '/dist/index.html')) {
                $candidates[] = $item . '/dist/index.html';
            }

            $sub = @scandir($level1);
            if (!is_array($sub)) {
                continue;
            }
            foreach ($sub as $subitem) {
                if ($subitem === '.' || $subitem === '..') {
                    continue;
                }
                $level2 = $level1 . '/' . $subitem;
                if (is_dir($level2) && is_file($level2 . '/index.html')) {
                    $candidates[] = $item . '/' . $subitem . '/index.html';
                }
            }
        }

        $candidates = array_values(array_unique($candidates));
        if ($candidates === array()) {
            return '';
        }

        foreach ($candidates as $rel) {
            if ($rel === 'dist/index.html' || substr($rel, -15) === '/dist/index.html') {
                return $rel;
            }
        }

        if (count($candidates) === 1) {
            return $candidates[0];
        }

        return '';
    }
}

if (!function_exists('onoff_builder_zip_blocked_entry')) {
    function onoff_builder_zip_blocked_entry($name)
    {
        $name = str_replace('\\', '/', (string) $name);
        $lower = strtolower($name);

        foreach (explode('/', $lower) as $part) {
            if ($part === '' || $part === '.' || $part === '..') {
                continue;
            }
            if (in_array($part, array('node_modules', '.git', 'vendor'), true)) {
                return true;
            }
        }

        $base = basename($lower);

        if (in_array($base, array('.htaccess', 'web.config', '.env'), true)) {
            return true;
        }
        if (preg_match('/\.php\d*$/i', $base)) {
            return true;
        }
        if (preg_match('/\.(phtml|phar|cgi|pl|asp|aspx|jsp)$/i', $base)) {
            return true;
        }

        return false;
    }
}

if (!function_exists('onoff_builder_extract_zip')) {
    function onoff_builder_extract_zip($zip_path, $dest_dir)
    {
        if (!class_exists('ZipArchive')) {
            return array('ok' => false, 'message' => 'PHP ZipArchive 확장이 필요합니다.');
        }

        $zip = new ZipArchive();
        if ($zip->open($zip_path) !== true) {
            return array('ok' => false, 'message' => 'ZIP 파일을 열 수 없습니다.');
        }

        $dest_real = realpath($dest_dir);
        if ($dest_real === false) {
            if (!onoff_builder_ensure_dir($dest_dir)) {
                $zip->close();
                return array('ok' => false, 'message' => '저장 폴더를 만들 수 없습니다.');
            }
            $dest_real = realpath($dest_dir);
        }
        if ($dest_real === false) {
            $zip->close();
            return array('ok' => false, 'message' => '저장 경로를 확인할 수 없습니다.');
        }

        $dest_real = rtrim($dest_real, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name === false || $name === '') {
                continue;
            }

            $name = str_replace('\\', '/', $name);
            if (strpos($name, "\0") !== false) {
                $zip->close();
                return array('ok' => false, 'message' => 'ZIP에 허용되지 않는 경로가 포함되어 있습니다.');
            }

            if (onoff_builder_zip_blocked_entry($name)) {
                $zip->close();
                return array('ok' => false, 'message' => 'ZIP에 허용되지 않는 파일이 포함되어 있습니다. (' . basename($name) . ')');
            }

            if (strpos($name, '../') !== false || strpos($name, '/..') !== false || strpos($name, '..\\') !== false) {
                $zip->close();
                return array('ok' => false, 'message' => 'ZIP에 허용되지 않는 경로가 포함되어 있습니다.');
            }

            $target = $dest_real . $name;
            $target_dir = realpath(dirname($target));
            if ($target_dir === false) {
                $parent = dirname($target);
                if (!onoff_builder_ensure_dir($parent)) {
                    $zip->close();
                    return array('ok' => false, 'message' => 'ZIP 압축 해제 중 폴더 생성에 실패했습니다.');
                }
                $target_dir = realpath($parent);
            }
            if ($target_dir === false || strpos($target_dir . DIRECTORY_SEPARATOR, $dest_real) !== 0) {
                $zip->close();
                return array('ok' => false, 'message' => 'ZIP 경로가 안전하지 않습니다 (Zip Slip).');
            }

            if (substr($name, -1) === '/') {
                if (!is_dir($target) && !@mkdir($target, 0755, true)) {
                    $zip->close();
                    return array('ok' => false, 'message' => 'ZIP 압축 해제 중 폴더 생성에 실패했습니다.');
                }
                continue;
            }

            $stream = $zip->getStream($name);
            if ($stream === false) {
                $zip->close();
                return array('ok' => false, 'message' => 'ZIP 파일 읽기에 실패했습니다.');
            }

            $dir = dirname($target);
            if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
                fclose($stream);
                $zip->close();
                return array('ok' => false, 'message' => 'ZIP 압축 해제 중 폴더 생성에 실패했습니다.');
            }

            $out = @fopen($target, 'wb');
            if ($out === false) {
                fclose($stream);
                $zip->close();
                return array('ok' => false, 'message' => 'ZIP 압축 해제 중 파일 저장에 실패했습니다.');
            }

            while (!feof($stream)) {
                $chunk = fread($stream, 8192);
                if ($chunk === false) {
                    break;
                }
                fwrite($out, $chunk);
            }
            fclose($stream);
            fclose($out);
        }

        $zip->close();

        return array('ok' => true, 'message' => '');
    }
}

if (!function_exists('onoff_builder_handle_zip_upload')) {
    function onoff_builder_handle_zip_upload($project_id, $project_name, $file)
    {
        if (!onoff_builder_validate_project_id($project_id)) {
            return array('ok' => false, 'message' => '프로젝트 ID는 영문 소문자, 숫자, 하이픈(-), 언더스코어(_)만 사용할 수 있습니다.');
        }

        $project_id = onoff_builder_sanitize_project_id($project_id);
        $project_name = trim((string) $project_name);
        if ($project_name === '') {
            $project_name = $project_id;
        }

        if (!is_array($file) || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return array('ok' => false, 'message' => 'ZIP 파일을 선택해주세요.');
        }

        if (!empty($file['error']) && (int) $file['error'] !== UPLOAD_ERR_OK) {
            return array('ok' => false, 'message' => '파일 업로드 중 오류가 발생했습니다.');
        }

        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if ($ext !== 'zip') {
            return array('ok' => false, 'message' => 'ZIP 파일만 업로드할 수 있습니다.');
        }

        if (onoff_builder_project_exists($project_id)) {
            return array('ok' => false, 'message' => '이미 사용 중인 프로젝트 ID입니다. 다른 ID를 사용하거나 기존 항목을 삭제해주세요.');
        }

        $project_dir = onoff_builder_project_dir($project_id);
        if ($project_dir === '') {
            return array('ok' => false, 'message' => '프로젝트 경로를 확인할 수 없습니다.');
        }

        if (is_dir($project_dir)) {
            onoff_builder_remove_dir($project_dir);
        }
        if (!onoff_builder_ensure_dir($project_dir)) {
            return array('ok' => false, 'message' => '프로젝트 폴더를 만들 수 없습니다.');
        }

        $extract = onoff_builder_extract_zip($file['tmp_name'], $project_dir);
        if (!$extract['ok']) {
            onoff_builder_remove_dir($project_dir);
            return array('ok' => false, 'message' => $extract['message']);
        }

        if (onoff_builder_is_vite_source_project($project_dir)) {
            onoff_builder_remove_dir($project_dir);
            return array('ok' => false, 'message' => onoff_builder_vite_source_message());
        }

        $entry = onoff_builder_find_index_html($project_dir);
        if ($entry === '') {
            onoff_builder_remove_dir($project_dir);
            return array(
                'ok'      => false,
                'message' => 'index.html을 찾을 수 없습니다. 빌드된 dist 결과물(ZIP)을 업로드해주세요.',
            );
        }

        return array(
            'ok'           => true,
            'message'      => '업로드가 완료되었습니다.',
            'project_id'   => $project_id,
            'project_name' => $project_name,
            'entry'        => $entry,
        );
    }
}
