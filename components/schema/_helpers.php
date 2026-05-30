<?php
/**
 * Schema JSON-LD 공통 헬퍼
 * - seo-meta.php(g5b_seo_*)와 함수명 분리
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('g5b_schema_load_config')) {
    function g5b_schema_load_config()
    {
        if (!function_exists('g5site_cfg') && defined('G5_PATH') && is_file(G5_PATH . '/_site.config.php')) {
            include_once G5_PATH . '/_site.config.php';
        }
    }
}

if (!function_exists('g5b_schema_site_url')) {
    function g5b_schema_site_url()
    {
        return defined('G5_URL') ? rtrim((string) G5_URL, '/') : '';
    }
}

if (!function_exists('g5b_schema_abs_url')) {
    /**
     * 상대·절대 URL을 절대 URL로
     *
     * @param string $url
     * @return string
     */
    function g5b_schema_abs_url($url)
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        $base = g5b_schema_site_url();
        if ($base === '') {
            return $url;
        }

        return $base . '/' . ltrim($url, '/');
    }
}

if (!function_exists('g5b_schema_clean_text')) {
    /**
     * @param string $str
     * @return string
     */
    function g5b_schema_clean_text($str)
    {
        return trim(strip_tags((string) $str));
    }
}

if (!function_exists('g5b_schema_sanitize_type')) {
    /**
     * @param string $type
     * @param string $default
     * @return string
     */
    function g5b_schema_sanitize_type($type, $default = 'Organization')
    {
        $type = preg_replace('/[^a-zA-Z]/', '', (string) $type);

        return $type !== '' ? $type : $default;
    }
}

if (!function_exists('g5b_schema_json_encode')) {
    /**
     * @param array $data
     * @return string
     */
    function g5b_schema_json_encode($data)
    {
        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
            $flags |= JSON_INVALID_UTF8_SUBSTITUTE;
        }

        $json = json_encode($data, $flags);
        if ($json === false || $json === '') {
            return '';
        }

        return str_replace('</', '<\/', $json);
    }
}

if (!function_exists('g5b_schema_print_jsonld')) {
    /**
     * JSON-LD script 출력
     *
     * @param array $data
     * @return bool 출력했으면 true
     */
    function g5b_schema_print_jsonld($data)
    {
        if (!is_array($data) || empty($data)) {
            return false;
        }

        $json = g5b_schema_json_encode($data);
        if ($json === '') {
            return false;
        }

        echo '<script type="application/ld+json">' . $json . '</script>' . "\n";

        return true;
    }
}

if (!function_exists('g5b_schema_cfg')) {
    /**
     * @param string $key
     * @param string $default
     * @return string
     */
    function g5b_schema_cfg($key, $default = '')
    {
        g5b_schema_load_config();

        if (function_exists('g5site_cfg')) {
            return g5b_schema_clean_text(g5site_cfg($key, $default));
        }

        return g5b_schema_clean_text($default);
    }
}

if (!function_exists('g5b_schema_cfg_url')) {
    /**
     * @param string $key
     * @param string $default
     * @return string
     */
    function g5b_schema_cfg_url($key, $default = '')
    {
        g5b_schema_load_config();

        if (function_exists('g5site_cfg_url')) {
            return g5b_schema_abs_url(g5site_cfg_url($key, $default));
        }

        return g5b_schema_abs_url(g5b_schema_cfg($key, $default));
    }
}
