<?php
/**
 * 사이트 공통 설정 (새 프로젝트마다 이 파일만 우선 수정)
 * 경로: /_site.config.php
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

$site_config = array(
    'site_name'           => '샘플 사이트',
    'site_desc'           => '빌더 디자인 적용이 쉬운 그누보드 베이스 템플릿',
    'company_name'        => '회사명',
    'ceo_name'            => '대표자명',
    'business_no'         => '000-00-00000',
    'phone'               => '010-0000-0000',
    'kakao_url'           => '#',
    'email'               => 'help@example.com',
    'address'             => '주소를 입력하세요',
    'primary_color'       => '#2563eb',
    'secondary_color'     => '#64748b',
    'logo_path'           => '/img/logo/logo.svg',
    'og_image'            => '/img/common/og-image.jpg',
    /* SEO (components/seo-meta.php) */
    'seo_title'           => '',
    'seo_description'     => '',
    'main_keyword'        => '',
    'sub_keywords'        => '',
    'robots'              => 'index,follow',
    'consultation_text'   => '상담문의',
    'footer_desc'         => '고객의 성장을 돕는 웹사이트 제작 베이스입니다.',
    /* 문의 폼 → inquiry 게시판 (proc/inquiry-submit.php) */
    'inquiry_bo_table'        => 'inquiry',
    'inquiry_notify_enabled'  => true,
    'inquiry_notify_email'    => 'admin@example.com',  /* 운영 시 실제 수신 주소로 변경 */
    'inquiry_notify_name'     => '관리자',
    /* 텔레그램 알림 — 운영 시 토큰·채팅 ID 입력 후 enabled true */
    'inquiry_notify_telegram_enabled'  => false,
    'inquiry_notify_telegram_bot_token' => '',
    'inquiry_notify_telegram_chat_id'   => '',
    /* 웹훅 알림 (Slack/Discord 등) — 추후 확장 */
    'inquiry_notify_webhook_enabled' => false,
    'inquiry_notify_webhook_url'     => '',
    /* 문의 접수 완료 페이지 (상대 경로) */
    'inquiry_thanks_url'      => '/page/inquiry-thanks.php',
    /* 전환·방문 추적 ID — 비우면 출력 안 함 */
    'gtm_id'              => '',
    'ga4_id'              => '',
    'meta_pixel_id'       => '',
    'naver_analytics_id'  => '',
    'kakao_pixel_id'      => '',
    /* 선택 항목 (비워 두면 기본값 사용) */
    'fax'                 => '',
    'sales_no'            => '',
    'privacy_manager'     => '',
    'kakao_map_key'       => '',
    'kakao_map_lat'       => '37.5665',
    'kakao_map_lng'       => '126.9780',
    /* Google Maps — 내 주변 찾기 (components/maps, page/map-locator.php) */
    'google_maps_api_key'       => '',
    'map_default_lat'           => '10.3157',
    'map_default_lng'           => '123.8854',
    'map_default_zoom'          => 13,
    'map_use_current_location'  => true,
    'map_default_radius_km'     => 5,
    'map_unit'                  => 'km',
    'map_placeholder_title'     => 'Google Maps API 키가 설정되지 않았습니다.',
    'map_placeholder_desc'      => '_site.config.php에서 google_maps_api_key 값을 입력하면 지도가 표시됩니다.',
    /* iCRM final_url (lib/icrm.lib.php, /icrm/final-url.php) — 사이트 복사마다 토큰만 다름, 도메인은 G5_URL 자동 */
    'icrm_builtin'              => true,
    'icrm_site_base_url'        => '',  /* 비우면 G5_DOMAIN/G5_URL. CDN 등 예외 시만 https://고객도메인 */
    'icrm_secret_token'         => '',  /* 비우면 data/icrm.config.php(자동 생성) 사용 */
    'icrm_allowed_ips'          => '',  /* iCRM 서버 IP, 쉼표 구분 (token 대신 가능) */
    'icrm_css_only_when_markup' => false, /* true: 본문에 icrm-* 있을 때만 icrm-template.css 로드 */
    /* 자동댓글 (plugin/auto_comment + extend/auto_comment.extend.php) — false 시 비활성 */
    'auto_comment_builtin'      => true,
);

/**
 * 설정값 조회 (없거나 비어 있으면 $default)
 *
 * @param string $key
 * @param string $default
 * @return string
 */
if (!function_exists('g5site_cfg')) {
    function g5site_cfg($key, $default = '')
    {
        global $site_config;

        if (!isset($site_config) || !is_array($site_config)) {
            return (string) $default;
        }

        if (!array_key_exists($key, $site_config)) {
            return (string) $default;
        }

        $val = $site_config[$key];

        if ($val === null || $val === false) {
            return (string) $default;
        }

        if (is_string($val)) {
            $val = trim($val);
            return $val !== '' ? $val : (string) $default;
        }

        if (is_bool($val)) {
            return $val ? '1' : '';
        }

        return (string) $val;
    }
}

/**
 * bool 설정값 (true/false/1/0/off)
 *
 * @param string $key
 * @param bool   $default
 * @return bool
 */
if (!function_exists('g5site_cfg_bool')) {
    function g5site_cfg_bool($key, $default = false)
    {
        global $site_config;

        if (!isset($site_config) || !is_array($site_config) || !array_key_exists($key, $site_config)) {
            return (bool) $default;
        }

        $val = $site_config[$key];

        if ($val === true || $val === 1 || $val === '1' || $val === 'on' || $val === 'true') {
            return true;
        }
        if ($val === false || $val === 0 || $val === '0' || $val === 'off' || $val === 'false') {
            return false;
        }

        return (bool) $default;
    }
}

/**
 * URL 또는 사이트 루트 기준 경로
 *
 * @param string $key site_config 키 (logo_path, og_image 등)
 * @param string $default
 * @return string
 */
if (!function_exists('g5site_cfg_url')) {
    function g5site_cfg_url($key, $default = '')
    {
        $path = g5site_cfg($key, $default);

        if ($path === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        if (!defined('G5_URL')) {
            return $path;
        }

        if ($path[0] === '/') {
            return G5_URL . $path;
        }

        return G5_URL . '/' . $path;
    }
}

/**
 * 전화번호 → tel: 링크
 *
 * @param string $phone
 * @return string
 */
if (!function_exists('g5site_tel_link')) {
    function g5site_tel_link($phone = '')
    {
        if ($phone === '') {
            $phone = g5site_cfg('phone', '');
        }

        $digits = preg_replace('/[^0-9+]/', '', $phone);

        return $digits !== '' ? 'tel:' . $digits : '#';
    }
}
