<?php
/**
 * iCRM 연동 — wr_seo_title 확정 및 final_url 생성
 *
 * 그누보드 write_update.php 와 동일하게 generate_seo_title() + exist_seo_title_recursive() 사용.
 * iCRM은 제목으로 URL을 예측하지 말고, 이 모듈이 반환한 final_url만 사용하세요.
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('icrm_load_uri_lib')) {
    function icrm_load_uri_lib()
    {
        static $loaded = false;

        if ($loaded) {
            return true;
        }

        if (!is_file(G5_LIB_PATH . '/uri.lib.php')) {
            return false;
        }

        include_once G5_LIB_PATH . '/uri.lib.php';
        $loaded = function_exists('generate_seo_title') && function_exists('exist_seo_title_recursive');

        return $loaded;
    }
}

if (!function_exists('icrm_is_placeholder_secret')) {
    function icrm_is_placeholder_secret($secret)
    {
        $secret = trim((string) $secret);
        $placeholders = array(
            '',
            'change-me-to-long-random-secret',
            'change-me',
            'your-secret',
            'your_secret',
            '긴랜덤문자열',
        );

        return in_array($secret, $placeholders, true);
    }
}

if (!function_exists('icrm_detect_request_base_url')) {
    /**
     * 현재 HTTP 요청 기준 공개 URL (G5_URL이 localhost 등일 때 보조)
     *
     * @return string
     */
    function icrm_detect_request_base_url()
    {
        if (empty($_SERVER['HTTP_HOST'])) {
            return '';
        }

        $host = preg_replace('/[^a-zA-Z0-9.\-:_\[\]]/', '', (string) $_SERVER['HTTP_HOST']);
        if ($host === '') {
            return '';
        }

        $https = false;
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $https = true;
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
            $https = true;
        } elseif (!empty($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443) {
            $https = true;
        }

        $scheme = $https ? 'https' : 'http';
        $path = '';

        if (defined('G5_PATH') && G5_PATH !== '' && !empty($_SERVER['DOCUMENT_ROOT'])) {
            $doc_root = rtrim(str_replace('\\', '/', (string) $_SERVER['DOCUMENT_ROOT']), '/');
            $g5_path = rtrim(str_replace('\\', '/', G5_PATH), '/');
            if ($doc_root !== '' && strpos($g5_path, $doc_root) === 0) {
                $path = substr($g5_path, strlen($doc_root));
                $path = rtrim(str_replace('\\', '/', $path), '/');
            }
        }

        return $scheme.'://'.$host.$path;
    }
}

if (!function_exists('icrm_get_site_base_url')) {
    /**
     * final_url 기준 사이트 루트 (끝 슬래시 없음)
     * 우선순위: icrm.config / _site.config → G5_DOMAIN → G5_URL → 현재 요청 Host
     *
     * @return string
     */
    function icrm_get_site_base_url()
    {
        static $cached = null;

        if ($cached !== null) {
            return $cached;
        }

        $candidates = array();

        if (defined('ICRM_SITE_BASE_URL') && trim((string) ICRM_SITE_BASE_URL) !== '') {
            $candidates[] = (string) ICRM_SITE_BASE_URL;
        }

        if (function_exists('g5site_cfg')) {
            $cfg_url = g5site_cfg('icrm_site_base_url', '');
            if ($cfg_url !== '') {
                $candidates[] = $cfg_url;
            }
        }

        if (defined('G5_DOMAIN') && G5_DOMAIN !== '') {
            $candidates[] = G5_DOMAIN;
        }

        if (defined('G5_URL') && G5_URL !== '') {
            $candidates[] = G5_URL;
        }

        $candidates[] = icrm_detect_request_base_url();

        foreach ($candidates as $url) {
            $url = rtrim(trim((string) $url), '/');
            if ($url === '') {
                continue;
            }
            if (preg_match('#^https?://#i', $url)) {
                $cached = $url;
                return $cached;
            }
        }

        $cached = '';
        return $cached;
    }
}

if (!function_exists('icrm_get_final_url_api_endpoint')) {
    /**
     * 이 사이트의 final-url API 전체 URL (iCRM 등록용)
     *
     * @return string
     */
    function icrm_get_final_url_api_endpoint()
    {
        $base = icrm_get_site_base_url();
        if ($base === '') {
            return '/icrm/final-url.php';
        }

        return $base.'/icrm/final-url.php';
    }
}

if (!function_exists('icrm_is_auth_configured')) {
    function icrm_is_auth_configured()
    {
        if (!icrm_is_placeholder_secret(icrm_get_secret_token())) {
            return true;
        }

        return icrm_get_allowed_ips() !== array();
    }
}

if (!function_exists('icrm_write_data_config')) {
    /**
     * @param array<string,string> $values
     */
    function icrm_write_data_config(array $values)
    {
        if (!defined('G5_DATA_PATH') || G5_DATA_PATH === '') {
            return false;
        }

        $site_base = isset($values['ICRM_SITE_BASE_URL']) ? (string) $values['ICRM_SITE_BASE_URL'] : '';
        $token = isset($values['ICRM_SECRET_TOKEN']) ? (string) $values['ICRM_SECRET_TOKEN'] : '';
        $ips = isset($values['ICRM_ALLOWED_IPS']) ? (string) $values['ICRM_ALLOWED_IPS'] : '';

        $php = "<?php\n";
        $php .= "if (!defined('_GNUBOARD_')) exit;\n\n";
        $php .= "/** 사이트별 iCRM 연동 (자동·수동 생성) — Git 업로드 금지 */\n";
        $php .= "define('ICRM_SITE_BASE_URL', ".var_export($site_base, true).");\n";
        $php .= "define('ICRM_SECRET_TOKEN', ".var_export($token, true).");\n";
        $php .= "define('ICRM_ALLOWED_IPS', ".var_export($ips, true).");\n";

        $path = G5_DATA_PATH.'/icrm.config.php';
        $ok = @file_put_contents($path, $php) !== false;

        if ($ok && defined('G5_FILE_PERMISSION')) {
            @chmod($path, G5_FILE_PERMISSION);
        } elseif ($ok) {
            @chmod($path, 0600);
        }

        return $ok;
    }
}

if (!function_exists('icrm_bootstrap')) {
    /**
     * onoff-g5-base: 사이트 복사 시 도메인은 G5_URL 자동, 토큰은 최초 1회 data/icrm.config.php 생성
     */
    function icrm_bootstrap()
    {
        static $done = false;

        if ($done) {
            return;
        }

        $done = true;

        if (!defined('G5_DATA_PATH') || !is_dir(G5_DATA_PATH)) {
            return;
        }

        $config_file = G5_DATA_PATH.'/icrm.config.php';

        if (is_file($config_file)) {
            include_once $config_file;
        }

        if (icrm_is_auth_configured()) {
            return;
        }

        if (!function_exists('random_bytes')) {
            return;
        }

        $site_base = '';
        if (defined('ICRM_SITE_BASE_URL')) {
            $site_base = (string) ICRM_SITE_BASE_URL;
        }
        $ips = defined('ICRM_ALLOWED_IPS') ? (string) ICRM_ALLOWED_IPS : '';

        icrm_write_data_config(array(
            'ICRM_SITE_BASE_URL' => $site_base,
            'ICRM_SECRET_TOKEN'  => bin2hex(random_bytes(32)),
            'ICRM_ALLOWED_IPS'   => $ips,
        ));

        if (is_file($config_file)) {
            include_once $config_file;
        }
    }
}

if (!function_exists('icrm_get_secret_token')) {
    function icrm_get_secret_token()
    {
        if (defined('ICRM_SECRET_TOKEN') && ICRM_SECRET_TOKEN !== '') {
            return (string) ICRM_SECRET_TOKEN;
        }

        return function_exists('g5site_cfg') ? g5site_cfg('icrm_secret_token', '') : '';
    }
}

if (!function_exists('icrm_get_allowed_ips')) {
    /**
     * @return string[]
     */
    function icrm_get_allowed_ips()
    {
        $raw = '';

        if (defined('ICRM_ALLOWED_IPS') && ICRM_ALLOWED_IPS !== '') {
            $raw = (string) ICRM_ALLOWED_IPS;
        } elseif (function_exists('g5site_cfg')) {
            $raw = g5site_cfg('icrm_allowed_ips', '');
        }

        if ($raw === '') {
            return array();
        }

        $parts = preg_split('/[\s,;]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
        $ips = array();

        foreach ($parts as $ip) {
            $ip = trim($ip);
            if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP)) {
                $ips[] = $ip;
            }
        }

        return array_values(array_unique($ips));
    }
}

if (!function_exists('icrm_check_auth')) {
    /**
     * secret token 또는 허용 IP 중 하나로 인증
     */
    function icrm_check_auth()
    {
        if (!icrm_is_auth_configured()) {
            return false;
        }

        $secret = icrm_get_secret_token();
        $allowed_ips = icrm_get_allowed_ips();

        $provided = '';

        if (!empty($_SERVER['HTTP_X_ICRM_TOKEN'])) {
            $provided = trim((string) $_SERVER['HTTP_X_ICRM_TOKEN']);
        } elseif (isset($_GET['token'])) {
            $provided = trim((string) $_GET['token']);
        } elseif (isset($_POST['token'])) {
            $provided = trim((string) $_POST['token']);
        }

        if ($secret !== '' && $provided !== '' && hash_equals($secret, $provided)) {
            return true;
        }

        if ($allowed_ips !== array()) {
            $remote = isset($_SERVER['REMOTE_ADDR']) ? trim((string) $_SERVER['REMOTE_ADDR']) : '';
            if ($remote !== '' && in_array($remote, $allowed_ips, true)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('icrm_validate_bo_table')) {
    function icrm_validate_bo_table($bo_table)
    {
        $bo_table = (string) $bo_table;

        if ($bo_table === '' || !preg_match('/^[0-9a-zA-Z_]+$/', $bo_table)) {
            return false;
        }

        global $g5;

        $sql = " select bo_table from {$g5['board_table']} where bo_table = '" . sql_real_escape_string($bo_table) . "' limit 1 ";
        $row = sql_fetch($sql);

        return isset($row['bo_table']) && $row['bo_table'] !== '';
    }
}

if (!function_exists('icrm_validate_wr_id')) {
    function icrm_validate_wr_id($wr_id)
    {
        return (string) (int) $wr_id === (string) $wr_id && (int) $wr_id > 0;
    }
}

if (!function_exists('icrm_get_write_table')) {
    function icrm_get_write_table($bo_table)
    {
        global $g5;

        return $g5['write_prefix'] . $bo_table;
    }
}

if (!function_exists('icrm_canonical_seo_title_from_subject')) {
    /**
     * wr_subject → 그누보드 글이름 slug (write_update.php 와 동일)
     *
     * @param string $write_table
     * @param string $subject
     * @param int    $wr_id
     * @return string
     */
    function icrm_canonical_seo_title_from_subject($write_table, $subject, $wr_id)
    {
        if (!icrm_load_uri_lib()) {
            return '';
        }

        $subject = trim(stripslashes((string) $subject));
        if ($subject === '') {
            return '';
        }

        if (function_exists('exist_seo_title_recursive') && function_exists('generate_seo_title')) {
            return exist_seo_title_recursive('bbs', generate_seo_title($subject), $write_table, (int) $wr_id);
        }

        if (function_exists('seo_title_update')) {
            seo_title_update($write_table, (int) $wr_id, 'bbs');
            $write = get_write($write_table, (int) $wr_id, true);

            return isset($write['wr_seo_title']) ? trim($write['wr_seo_title']) : '';
        }

        return '';
    }
}

if (!function_exists('icrm_ensure_wr_seo_title')) {
    /**
     * wr_seo_title 확정 — iCRM DB 직접 INSERT·임의 slug 는 신뢰하지 않음.
     * wr_subject 기준 generate_seo_title + exist_seo_title_recursive 가 최종값.
     *
     * @param string $bo_table
     * @param int    $wr_id
     * @return string 확정된 wr_seo_title (실패 시 '')
     */
    function icrm_ensure_wr_seo_title($bo_table, $wr_id)
    {
        $wr_id = (int) $wr_id;

        if ($wr_id < 1 || !icrm_validate_bo_table($bo_table)) {
            return '';
        }

        $write_table = icrm_get_write_table($bo_table);
        $write = get_write($write_table, $wr_id, true);

        if (!isset($write['wr_id']) || !(int) $write['wr_id']) {
            return '';
        }

        if (!empty($write['wr_is_comment'])) {
            return isset($write['wr_seo_title']) ? trim($write['wr_seo_title']) : '';
        }

        $subject = isset($write['wr_subject']) ? $write['wr_subject'] : '';
        $canonical = icrm_canonical_seo_title_from_subject($write_table, $subject, $wr_id);

        if ($canonical === '') {
            return isset($write['wr_seo_title']) ? trim($write['wr_seo_title']) : '';
        }

        $current = isset($write['wr_seo_title']) ? trim($write['wr_seo_title']) : '';

        if ($current === '' || $current !== $canonical) {
            $canonical_esc = sql_real_escape_string($canonical);
            sql_query(" update `{$write_table}` set wr_seo_title = '{$canonical_esc}' where wr_id = '{$wr_id}' ");
            get_write($write_table, $wr_id, false);
        }

        return $canonical;
    }
}

if (!function_exists('icrm_is_board_post_view')) {
    /**
     * 게시판 글보기 여부 (짧은주소·리라이트 환경 포함, SCRIPT_NAME 비의존)
     */
    function icrm_is_board_post_view()
    {
        global $bo_table, $wr_id, $write;

        if (empty($bo_table) || !(int) $wr_id) {
            return false;
        }

        if (!is_array($write) || empty($write['wr_id']) || !empty($write['wr_is_comment'])) {
            return false;
        }

        if (defined('G5_IS_ADMIN') && G5_IS_ADMIN) {
            return false;
        }

        return true;
    }
}

if (!function_exists('icrm_is_board_view_request')) {
    /** @deprecated icrm_is_board_post_view() 사용 */
    function icrm_is_board_view_request()
    {
        if (!icrm_is_board_post_view()) {
            return false;
        }

        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'GET') {
            return false;
        }

        return true;
    }
}

if (!function_exists('icrm_write_has_template_markup')) {
    function icrm_write_has_template_markup($write = null)
    {
        if ($write === null) {
            global $write;
        }

        if (!is_array($write) || !isset($write['wr_content'])) {
            return false;
        }

        return (bool) preg_match(
            '/\b(icrm-template|icrm-section|icrm-facility|icrm-cta|icrm-content|data-icrm-template|data-icrm-generated)\b/i',
            (string) $write['wr_content']
        );
    }
}

if (!function_exists('icrm_should_load_template_css')) {
    /**
     * 기본: 모든 게시판 글보기에서 CSS 로드.
     * _site.config.php icrm_css_only_when_markup=true 이면 iCRM 마크업 있을 때만.
     */
    function icrm_should_load_template_css($write = null)
    {
        if (!icrm_is_board_post_view()) {
            return false;
        }

        if (function_exists('g5site_cfg_bool') && g5site_cfg_bool('icrm_css_only_when_markup', false)) {
            return icrm_write_has_template_markup($write);
        }

        return true;
    }
}

if (!function_exists('icrm_get_template_css_href')) {
    function icrm_get_template_css_href()
    {
        if (!defined('G5_CSS_URL')) {
            return '';
        }

        $ver = defined('G5_CSS_VER') ? G5_CSS_VER : '1';

        return G5_CSS_URL.'/icrm-template.css?ver='.rawurlencode((string) $ver);
    }
}

if (!function_exists('icrm_ensure_wr_seo_title_on_view')) {
    /**
     * 글보기 시 slug 보정 (write_update 미실행·DB 직접 INSERT 대응)
     */
    function icrm_ensure_wr_seo_title_on_view()
    {
        static $ran = false;

        if ($ran || !icrm_is_board_view_request()) {
            return;
        }

        $ran = true;

        global $bo_table, $wr_id, $write;

        $bo_table_safe = preg_replace('/[^0-9a-zA-Z_]/', '', (string) $bo_table);
        $wr_id_int = (int) $wr_id;

        if ($bo_table_safe === '' || $wr_id_int < 1) {
            return;
        }

        $seo = icrm_ensure_wr_seo_title($bo_table_safe, $wr_id_int);

        if ($seo !== '' && is_array($write)) {
            $write['wr_seo_title'] = $seo;
        }
    }
}

if (!function_exists('icrm_enqueue_board_assets')) {
    /**
     * iCRM 템플릿 CSS — html_process 큐 (테마·bo_include_head 없는 게시판 포함)
     */
    function icrm_enqueue_board_assets()
    {
        if (!icrm_should_load_template_css() || !function_exists('add_stylesheet')) {
            return;
        }

        static $enqueued = false;
        if ($enqueued) {
            return;
        }
        $enqueued = true;

        $href = icrm_get_template_css_href();
        if ($href === '') {
            return;
        }

        add_stylesheet('<link rel="stylesheet" href="'.$href.'">', 8);
    }
}

if (!function_exists('icrm_board_content_head_css')) {
    /**
     * head.php 미사용 게시판용 CSS 링크 (board_content_head 상단 주입)
     */
    function icrm_board_content_head_css($content, $board)
    {
        if (!icrm_should_load_template_css()) {
            return $content;
        }

        static $printed = false;
        if ($printed) {
            return $content;
        }
        $printed = true;

        $href = icrm_get_template_css_href();
        if ($href === '') {
            return $content;
        }

        $link = '<link rel="stylesheet" href="'.$href.'">'.PHP_EOL;

        return $link.$content;
    }
}

if (!function_exists('icrm_html_purifier_config')) {
    /**
     * iCRM 본문: class·style·data-icrm-* 속성 유지
     */
    function icrm_html_purifier_config($config, $ctx)
    {
        $html = '';
        if (is_array($ctx) && isset($ctx['html'])) {
            $html = (string) $ctx['html'];
        }

        if ($html === '' || !icrm_write_has_template_markup(array('wr_content' => $html))) {
            return;
        }

        if (!is_object($config) || !method_exists($config, 'set')) {
            return;
        }

        if (method_exists($config, 'isFinalized') && $config->isFinalized()) {
            return;
        }

        // Directive 설정은 getHTMLDefinition()/getDefinition() 호출 전에 끝내야 한다.
        $config->set('HTML.Trusted', true);
        $config->set('CSS.Trusted', true);
        $config->set('Attr.EnableID', true);
        $config->set('HTML.DefinitionID', 'icrm-template-html');
        $config->set('HTML.DefinitionRev', 1);

        // Definition 작업은 directive 설정 이후에만 수행한다.
        if (method_exists($config, 'getHTMLDefinition')) {
            $def = $config->getHTMLDefinition(true);
            if ($def && method_exists($def, 'addAttribute')) {
                foreach (array('div', 'section', 'article', 'a', 'span', 'p', 'ul', 'ol', 'li', 'img', 'strong', 'em', 'h2', 'h3') as $tag) {
                    foreach (array('data-icrm-template', 'data-design-template', 'data-icrm-generated') as $attr) {
                        $def->addAttribute($tag, $attr, 'Text');
                    }
                }
            }
        }
    }
}

if (!function_exists('icrm_html_purifier_result')) {
    /**
     * Purifier가 iCRM 마크업을 깨뜨린 경우 원본 유지
     */
    function icrm_html_purifier_result($html, $purifier, $original)
    {
        $original = (string) $original;
        if ($original === '' || !icrm_write_has_template_markup(array('wr_content' => $original))) {
            return $html;
        }

        if (stripos((string) $html, 'icrm-') === false && stripos($original, 'icrm-') !== false) {
            return $original;
        }

        return $html;
    }
}

if (!function_exists('icrm_build_final_url')) {
    /**
     * @param string $bo_table
     * @param int    $wr_id
     * @param string $wr_seo_title
     * @return string
     */
    function icrm_build_final_url($bo_table, $wr_id, $wr_seo_title = '')
    {
        $base = icrm_get_site_base_url();
        if ($base === '') {
            $base = '';
        }

        $wr_seo_title = trim((string) $wr_seo_title);
        $wr_id = (int) $wr_id;

        if ($wr_seo_title !== '') {
            return $base . '/' . $bo_table . '/' . rawurlencode($wr_seo_title) . '/';
        }

        return $base . '/bbs/board.php?bo_table=' . rawurlencode($bo_table) . '&wr_id=' . $wr_id;
    }
}

if (!function_exists('icrm_resolve_post_url')) {
    /**
     * 게시글 URL 메타 반환 (iCRM 등록·확인 공통)
     *
     * @param string $bo_table
     * @param int    $wr_id
     * @return array{ok:bool,bo_table?:string,wr_id?:int,wr_seo_title?:string,final_url?:string,error?:string,message?:string}
     */
    function icrm_resolve_post_url($bo_table, $wr_id)
    {
        $bo_table = (string) $bo_table;
        $wr_id = (int) $wr_id;

        if (!preg_match('/^[0-9a-zA-Z_]+$/', $bo_table)) {
            return array(
                'ok'      => false,
                'error'   => 'invalid_bo_table',
                'message' => 'bo_table은 영문, 숫자, 언더스코어(_)만 허용됩니다.',
            );
        }

        if ($wr_id < 1) {
            return array(
                'ok'      => false,
                'error'   => 'invalid_wr_id',
                'message' => 'wr_id는 1 이상의 정수여야 합니다.',
            );
        }

        if (!icrm_validate_bo_table($bo_table)) {
            return array(
                'ok'      => false,
                'error'   => 'board_not_found',
                'message' => '게시판을 찾을 수 없습니다.',
            );
        }

        $write_table = icrm_get_write_table($bo_table);
        $write = get_write($write_table, $wr_id, true);

        if (!isset($write['wr_id']) || !(int) $write['wr_id']) {
            return array(
                'ok'      => false,
                'error'   => 'post_not_found',
                'message' => '게시글을 찾을 수 없습니다.',
            );
        }

        $wr_seo_title = icrm_ensure_wr_seo_title($bo_table, $wr_id);

        $write_refresh = get_write($write_table, $wr_id, true);
        if (isset($write_refresh['wr_seo_title']) && trim($write_refresh['wr_seo_title']) !== '') {
            $wr_seo_title = trim($write_refresh['wr_seo_title']);
        }

        $site_base = icrm_get_site_base_url();

        return array(
            'ok'             => true,
            'site_base_url'  => $site_base,
            'final_url_api'  => icrm_get_final_url_api_endpoint(),
            'bo_table'       => $bo_table,
            'wr_id'          => $wr_id,
            'wr_seo_title'   => $wr_seo_title,
            'final_url'      => icrm_build_final_url($bo_table, $wr_id, $wr_seo_title),
        );
    }
}

if (!function_exists('icrm_json_response')) {
    function icrm_json_response(array $payload, $http_code = 200)
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code((int) $http_code);
        }

        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
