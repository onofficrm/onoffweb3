<?php
/**
 * iCRM API 공통 부트스트랩
 */
if (!defined('ICRM_API_REQUEST')) {
    define('ICRM_API_REQUEST', true);
}

include_once dirname(__DIR__) . '/_common.php';

if (!defined('_GNUBOARD_')) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode(array('ok' => false, 'error' => 'gnuboard', 'message' => '그누보드 초기화에 실패했습니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

if (is_file(G5_PATH . '/_site.config.php')) {
    include_once G5_PATH . '/_site.config.php';
}

include_once G5_LIB_PATH . '/icrm.lib.php';

if (function_exists('icrm_bootstrap')) {
    icrm_bootstrap();
}

if (is_file(G5_DATA_PATH . '/icrm.config.php')) {
    include_once G5_DATA_PATH . '/icrm.config.php';
}

if (!function_exists('icrm_api_require_auth')) {
    function icrm_api_require_auth()
    {
        if (!function_exists('icrm_is_auth_configured') || !icrm_is_auth_configured()) {
            icrm_json_response(array(
                'ok'            => false,
                'error'         => 'not_configured',
                'message'       => '이 사이트의 iCRM 토큰이 아직 없습니다. 사이트를 한 번 열어 data/icrm.config.php 가 생성되게 하거나, _site.config.php / data/icrm.config.php 에 secret을 설정하세요.',
                'site_base_url' => function_exists('icrm_get_site_base_url') ? icrm_get_site_base_url() : '',
                'final_url_api' => function_exists('icrm_get_final_url_api_endpoint') ? icrm_get_final_url_api_endpoint() : '',
            ), 503);
        }

        if (icrm_check_auth()) {
            return;
        }

        icrm_json_response(array(
            'ok'            => false,
            'error'         => 'unauthorized',
            'message'       => 'iCRM 인증에 실패했습니다. 이 사이트용 secret token 또는 허용 IP를 확인하세요.',
            'site_base_url' => icrm_get_site_base_url(),
            'final_url_api' => icrm_get_final_url_api_endpoint(),
        ), 403);
    }
}

if (!function_exists('icrm_api_read_params')) {
    /**
     * @return array{bo_table:string,wr_id:int}
     */
    function icrm_api_read_params()
    {
        $bo_table = '';
        $wr_id = 0;

        if (isset($_GET['bo_table'])) {
            $bo_table = trim((string) $_GET['bo_table']);
        }
        if (isset($_GET['wr_id'])) {
            $wr_id = (int) $_GET['wr_id'];
        }

        if ($bo_table === '' && isset($_POST['bo_table'])) {
            $bo_table = trim((string) $_POST['bo_table']);
        }
        if (!$wr_id && isset($_POST['wr_id'])) {
            $wr_id = (int) $_POST['wr_id'];
        }

        $raw = file_get_contents('php://input');
        if ($raw !== false && $raw !== '') {
            $json = json_decode($raw, true);
            if (is_array($json)) {
                if ($bo_table === '' && isset($json['bo_table'])) {
                    $bo_table = trim((string) $json['bo_table']);
                }
                if (!$wr_id && isset($json['wr_id'])) {
                    $wr_id = (int) $json['wr_id'];
                }
            }
        }

        return array(
            'bo_table' => $bo_table,
            'wr_id'    => $wr_id,
        );
    }
}

if (!function_exists('icrm_api_handle_resolve')) {
    function icrm_api_handle_resolve()
    {
        icrm_api_require_auth();

        $params = icrm_api_read_params();
        $result = icrm_resolve_post_url($params['bo_table'], $params['wr_id']);

        if (empty($result['ok'])) {
            $code = 400;
            if (isset($result['error'])) {
                if ($result['error'] === 'board_not_found' || $result['error'] === 'post_not_found') {
                    $code = 404;
                }
            }
            icrm_json_response($result, $code);
        }

        icrm_json_response($result, 200);
    }
}
