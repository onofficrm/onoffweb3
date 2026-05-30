<?php
/**
 * SEO 메타·OG·JSON-LD 컴포넌트
 * - head.php에서 include 후 g5b_seo_init() 호출
 * - 그누보드 html_process_add_meta / html_process_buffer 훅으로 head.sub.php 수정 없이 주입
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('g5site_cfg') && is_file(G5_PATH . '/_site.config.php')) {
    include_once G5_PATH . '/_site.config.php';
}

/**
 * HTML 속성·텍스트 이스케이프
 *
 * @param string $str
 * @return string
 */
if (!function_exists('g5b_seo_escape')) {
    function g5b_seo_escape($str)
    {
        return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * 현재 요청 URL (쿼리 제외, HTTPS 반영)
 *
 * @return string
 */
if (!function_exists('g5b_seo_current_url')) {
    function g5b_seo_current_url()
    {
        if (!empty($_SERVER['HTTP_HOST'])) {
            $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
            $scheme = $https ? 'https' : 'http';
            $uri = isset($_SERVER['REQUEST_URI']) ? strtok($_SERVER['REQUEST_URI'], '?') : '/';
            return $scheme . '://' . $_SERVER['HTTP_HOST'] . $uri;
        }

        return defined('G5_URL') ? G5_URL . '/' : '';
    }
}

/**
 * 페이지·site_config·fallback 병합
 *
 * @return array
 */
if (!function_exists('g5b_seo_resolve')) {
    function g5b_seo_resolve()
    {
        global $g5, $config, $page_title, $page_description, $page_keywords,
               $page_og_image, $page_canonical, $page_robots, $page_schema_type;

        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $site_name = function_exists('g5site_cfg') ? g5site_cfg('site_name', '') : '';
        $cf_title = isset($config['cf_title']) ? trim(strip_tags($config['cf_title'])) : '';
        if ($site_name === '') {
            $site_name = $cf_title !== '' ? $cf_title : '사이트';
        }
        if ($cf_title === '') {
            $cf_title = $site_name;
        }

        $fallback_desc = '웹사이트입니다.';
        $site_desc = function_exists('g5site_cfg') ? g5site_cfg('site_desc', $fallback_desc) : $fallback_desc;
        $seo_desc = function_exists('g5site_cfg') ? g5site_cfg('seo_description', '') : '';
        if ($seo_desc === '') {
            $seo_desc = $site_desc;
        }

        $title = '';
        if (!empty($page_title)) {
            $title = trim(strip_tags((string) $page_title));
        } elseif (function_exists('g5site_cfg') && g5site_cfg('seo_title', '') !== '') {
            $title = g5site_cfg('seo_title', '');
        } elseif (!empty($g5['title'])) {
            $parts = array_filter(array(strip_tags((string) $g5['title']), $cf_title));
            $title = implode(' | ', $parts);
        } else {
            $seo_title_cfg = function_exists('g5site_cfg') ? g5site_cfg('seo_title', '') : '';
            if ($seo_title_cfg !== '') {
                $title = $seo_title_cfg;
            } else {
                $main_kw = function_exists('g5site_cfg') ? g5site_cfg('main_keyword', '') : '';
                $title = $main_kw !== '' ? $site_name . ' - ' . $main_kw : $site_name;
            }
        }

        $description = '';
        if (!empty($page_description)) {
            $description = trim(strip_tags((string) $page_description));
        } else {
            $description = $seo_desc !== '' ? $seo_desc : $fallback_desc;
        }

        $keywords = '';
        if (!empty($page_keywords)) {
            $keywords = trim(strip_tags((string) $page_keywords));
        } elseif (function_exists('g5site_cfg')) {
            $main_kw = g5site_cfg('main_keyword', '');
            $sub_kw = g5site_cfg('sub_keywords', '');
            if (is_array($sub_kw)) {
                $sub_kw = implode(', ', array_filter(array_map('trim', $sub_kw)));
            }
            $kw_parts = array_filter(array($main_kw, $sub_kw));
            $keywords = implode(', ', $kw_parts);
        }

        $canonical = '';
        if (!empty($page_canonical)) {
            $canonical = trim((string) $page_canonical);
        } else {
            $canonical = g5b_seo_current_url();
        }
        if ($canonical !== '' && !preg_match('#^https?://#i', $canonical) && defined('G5_URL')) {
            $canonical = G5_URL . '/' . ltrim($canonical, '/');
        }

        $robots = 'index,follow';
        if (!empty($page_robots)) {
            $robots = trim(strip_tags((string) $page_robots));
        } elseif (function_exists('g5site_cfg') && g5site_cfg('robots', '') !== '') {
            $robots = g5site_cfg('robots', '');
        }

        $og_image = '';
        if (!empty($page_og_image)) {
            $og_image = trim((string) $page_og_image);
        } elseif (function_exists('g5site_cfg_url')) {
            $og_image = g5site_cfg_url('og_image', '');
        }
        if ($og_image !== '' && !preg_match('#^https?://#i', $og_image) && defined('G5_URL')) {
            $og_image = G5_URL . '/' . ltrim($og_image, '/');
        }

        $og_url = $canonical !== '' ? $canonical : g5b_seo_current_url();

        $company_name = function_exists('g5site_cfg') ? g5site_cfg('company_name', $site_name) : $site_name;
        $logo_url = function_exists('g5site_cfg_url') ? g5site_cfg_url('logo_path', '') : '';
        $phone = function_exists('g5site_cfg') ? g5site_cfg('phone', '') : '';
        $email = function_exists('g5site_cfg') ? g5site_cfg('email', '') : '';
        $address = function_exists('g5site_cfg') ? g5site_cfg('address', '') : '';

        $schema_type = 'Organization';
        if (!empty($page_schema_type)) {
            $schema_type = preg_replace('/[^a-zA-Z]/', '', (string) $page_schema_type);
            if ($schema_type === '') {
                $schema_type = 'Organization';
            }
        }

        $cache = array(
            'title'           => $title,
            'description'     => $description,
            'keywords'        => $keywords,
            'canonical'       => $canonical,
            'robots'          => $robots,
            'og_title'        => $title,
            'og_description'  => $description,
            'og_image'        => $og_image,
            'og_url'          => $og_url,
            'og_type'         => defined('_INDEX_') ? 'website' : 'article',
            'site_name'       => $site_name,
            'company_name'    => $company_name,
            'logo_url'        => $logo_url,
            'phone'           => $phone,
            'email'           => $email,
            'address'         => $address,
            'schema_type'     => $schema_type,
            'site_url'        => defined('G5_URL') ? G5_URL : '',
        );

        return $cache;
    }
}

/**
 * meta·OG·canonical·JSON-LD HTML
 *
 * @param array|null $data
 * @return string
 */
if (!function_exists('g5b_seo_build_meta_html')) {
    function g5b_seo_build_meta_html($data = null)
    {
        if ($data === null) {
            $data = g5b_seo_resolve();
        }

        $lines = array();

        if ($data['description'] !== '') {
            $lines[] = '<meta name="description" content="' . g5b_seo_escape($data['description']) . '">';
        }
        if ($data['keywords'] !== '') {
            $lines[] = '<meta name="keywords" content="' . g5b_seo_escape($data['keywords']) . '">';
        }
        if ($data['robots'] !== '') {
            $lines[] = '<meta name="robots" content="' . g5b_seo_escape($data['robots']) . '">';
        }
        if ($data['canonical'] !== '') {
            $lines[] = '<link rel="canonical" href="' . g5b_seo_escape($data['canonical']) . '">';
        }

        $lines[] = '<meta property="og:type" content="' . g5b_seo_escape($data['og_type']) . '">';
        $lines[] = '<meta property="og:site_name" content="' . g5b_seo_escape($data['site_name']) . '">';
        if ($data['og_title'] !== '') {
            $lines[] = '<meta property="og:title" content="' . g5b_seo_escape($data['og_title']) . '">';
        }
        if ($data['og_description'] !== '') {
            $lines[] = '<meta property="og:description" content="' . g5b_seo_escape($data['og_description']) . '">';
        }
        if ($data['og_url'] !== '') {
            $lines[] = '<meta property="og:url" content="' . g5b_seo_escape($data['og_url']) . '">';
        }
        if ($data['og_image'] !== '') {
            $lines[] = '<meta property="og:image" content="' . g5b_seo_escape($data['og_image']) . '">';
        }

        $lines[] = '<meta name="twitter:card" content="summary_large_image">';
        if ($data['og_title'] !== '') {
            $lines[] = '<meta name="twitter:title" content="' . g5b_seo_escape($data['og_title']) . '">';
        }
        if ($data['og_description'] !== '') {
            $lines[] = '<meta name="twitter:description" content="' . g5b_seo_escape($data['og_description']) . '">';
        }
        if ($data['og_image'] !== '') {
            $lines[] = '<meta name="twitter:image" content="' . g5b_seo_escape($data['og_image']) . '">';
        }

        $jsonld = g5b_seo_build_jsonld($data);
        if ($jsonld !== '') {
            $lines[] = '<script type="application/ld+json">' . $jsonld . '</script>';
        }

        return implode(PHP_EOL, $lines);
    }
}

/**
 * JSON-LD (@graph: Organization + WebSite)
 *
 * @param array $data
 * @return string
 */
if (!function_exists('g5b_seo_build_jsonld')) {
    function g5b_seo_build_jsonld($data)
    {
        $org = array(
            '@type' => $data['schema_type'],
            '@id'   => $data['site_url'] . '#organization',
            'name'  => $data['company_name'],
            'url'   => $data['site_url'],
        );
        if ($data['logo_url'] !== '') {
            $org['logo'] = $data['logo_url'];
        }
        if ($data['email'] !== '') {
            $org['email'] = $data['email'];
        }
        if ($data['phone'] !== '') {
            $org['telephone'] = $data['phone'];
        }
        if ($data['address'] !== '') {
            $org['address'] = array(
                '@type'           => 'PostalAddress',
                'streetAddress'   => $data['address'],
            );
        }

        /*
         * 추후 확장: LocalBusiness
         * $local = array_merge($org, array('@type' => 'LocalBusiness', 'priceRange' => '$$'));
         */

        $website = array(
            '@type' => 'WebSite',
            '@id'   => $data['site_url'] . '#website',
            'url'   => $data['site_url'],
            'name'  => $data['site_name'],
            'description' => $data['description'],
            'publisher'   => array('@id' => $data['site_url'] . '#organization'),
        );

        $graph = array(
            '@context' => 'https://schema.org',
            '@graph'     => array($org, $website),
        );

        $json = json_encode($graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            return '';
        }

        return str_replace('</', '<\/', $json);
    }
}

/**
 * html_process_add_meta 필터
 *
 * @param string $meta
 * @return string
 */
if (!function_exists('g5b_seo_filter_add_meta')) {
    function g5b_seo_filter_add_meta($meta)
    {
        return g5b_seo_build_meta_html();
    }
}

/**
 * html_process_buffer: <title> 내용을 SEO title로 교체
 *
 * @param string $buffer
 * @return string
 */
if (!function_exists('g5b_seo_filter_buffer')) {
    function g5b_seo_filter_buffer($buffer)
    {
        $data = g5b_seo_resolve();
        if ($data['title'] === '') {
            return $buffer;
        }

        $safe_title = g5b_seo_escape($data['title']);
        $replaced = preg_replace('#<title[^>]*>.*?</title>#is', '<title>' . $safe_title . '</title>', $buffer, 1);

        return is_string($replaced) ? $replaced : $buffer;
    }
}

/**
 * head.php에서 호출 — 훅 등록·$g5['title'] 보조 동기화
 */
if (!function_exists('g5b_seo_init')) {
    function g5b_seo_init()
    {
        static $initialized = false;
        if ($initialized) {
            return;
        }
        $initialized = true;

        global $g5, $page_title;

        if (!empty($page_title) && empty($g5['title'])) {
            $g5['title'] = strip_tags((string) $page_title);
        }

        if (function_exists('add_replace')) {
            add_replace('html_process_add_meta', 'g5b_seo_filter_add_meta', 10, 1);
            add_replace('html_process_buffer', 'g5b_seo_filter_buffer', 10, 1);
        }
    }
}
