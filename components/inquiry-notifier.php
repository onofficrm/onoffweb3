<?php
/**
 * 문의(inquiry) 접수 알림 — 이메일 (확장: 텔레그램·웹훅)
 * - 문의 저장 성공 후에만 호출 (proc/inquiry-submit.php)
 * - 알림 실패가 저장 성공 응답을 막지 않음
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('g5site_cfg') && is_file(G5_PATH . '/_site.config.php')) {
    include_once G5_PATH . '/_site.config.php';
}

/**
 * 알림 사용 여부 (_site.config.php inquiry_notify_enabled)
 *
 * @return bool
 */
if (!function_exists('onoff_inquiry_notify_is_enabled')) {
    function onoff_inquiry_notify_is_enabled()
    {
        global $site_config;

        if (!isset($site_config['inquiry_notify_enabled'])) {
            return true;
        }

        $val = $site_config['inquiry_notify_enabled'];

        if ($val === false || $val === 0 || $val === '0' || $val === 'false' || $val === 'off') {
            return false;
        }

        return true;
    }
}

/**
 * 수신 이메일 목록 (쉼표·세미콜론 구분 가능)
 *
 * @return array
 */
if (!function_exists('onoff_get_inquiry_notify_emails')) {
    function onoff_get_inquiry_notify_emails()
    {
        $raw = function_exists('g5site_cfg') ? g5site_cfg('inquiry_notify_email', '') : '';

        if ($raw === '' && function_exists('g5site_cfg')) {
            $raw = g5site_cfg('email', '');
        }

        if ($raw === '') {
            global $config;
            if (!empty($config['cf_admin_email'])) {
                $raw = $config['cf_admin_email'];
            }
        }

        $parts = preg_split('/[\s,;]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
        $valid = array();

        foreach ($parts as $email) {
            $email = trim($email);
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $valid[$email] = $email;
            }
        }

        return array_values($valid);
    }
}

/**
 * 메일 본문용 텍스트 이스케이프 (HTML)
 *
 * @param string $str
 * @return string
 */
if (!function_exists('onoff_inquiry_mail_escape')) {
    function onoff_inquiry_mail_escape($str)
    {
        return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * @param array $inquiry_data
 * @return string
 */
if (!function_exists('onoff_build_inquiry_email_subject')) {
    function onoff_build_inquiry_email_subject($inquiry_data)
    {
        $site_name = isset($inquiry_data['site_name']) ? $inquiry_data['site_name'] : '';
        $name = isset($inquiry_data['name']) ? $inquiry_data['name'] : '';

        if ($site_name === '' && function_exists('g5site_cfg')) {
            $site_name = g5site_cfg('site_name', '사이트');
        }

        $subject = '[' . $site_name . '] 새로운 상담문의가 접수되었습니다';

        if ($name !== '') {
            $subject .= ' - ' . $name;
        }

        return $subject;
    }
}

/**
 * @param array $inquiry_data
 * @return string HTML
 */
if (!function_exists('onoff_build_inquiry_email_body')) {
    function onoff_build_inquiry_email_body($inquiry_data)
    {
        $site_name = onoff_inquiry_mail_escape(isset($inquiry_data['site_name']) ? $inquiry_data['site_name'] : '');
        $name = onoff_inquiry_mail_escape(isset($inquiry_data['name']) ? $inquiry_data['name'] : '');
        $phone = onoff_inquiry_mail_escape(isset($inquiry_data['phone']) ? $inquiry_data['phone'] : '');
        $email = onoff_inquiry_mail_escape(isset($inquiry_data['email']) ? $inquiry_data['email'] : '');
        $message = onoff_inquiry_mail_escape(isset($inquiry_data['message']) ? $inquiry_data['message'] : '');
        $message = nl2br($message);
        $referer = onoff_inquiry_mail_escape(isset($inquiry_data['referer_page']) ? $inquiry_data['referer_page'] : '');
        $privacy = onoff_inquiry_mail_escape(isset($inquiry_data['privacy_agree']) ? $inquiry_data['privacy_agree'] : '');
        $ip = onoff_inquiry_mail_escape(isset($inquiry_data['ip']) ? $inquiry_data['ip'] : '');
        $created = onoff_inquiry_mail_escape(isset($inquiry_data['created_at']) ? $inquiry_data['created_at'] : '');
        $admin_url = onoff_inquiry_mail_escape(isset($inquiry_data['admin_url']) ? $inquiry_data['admin_url'] : '');

        $html = '<p><strong>새로운 상담문의가 접수되었습니다.</strong></p>';
        $html .= '<table style="border-collapse:collapse;width:100%;max-width:560px;">';
        $html .= '<tr><th style="text-align:left;padding:6px 8px;border-bottom:1px solid #e2e8f0;">사이트명</th><td style="padding:6px 8px;border-bottom:1px solid #e2e8f0;">' . $site_name . '</td></tr>';
        $html .= '<tr><th style="text-align:left;padding:6px 8px;border-bottom:1px solid #e2e8f0;">이름</th><td style="padding:6px 8px;border-bottom:1px solid #e2e8f0;">' . $name . '</td></tr>';
        $html .= '<tr><th style="text-align:left;padding:6px 8px;border-bottom:1px solid #e2e8f0;">연락처</th><td style="padding:6px 8px;border-bottom:1px solid #e2e8f0;">' . $phone . '</td></tr>';
        $html .= '<tr><th style="text-align:left;padding:6px 8px;border-bottom:1px solid #e2e8f0;">이메일</th><td style="padding:6px 8px;border-bottom:1px solid #e2e8f0;">' . ($email !== '' ? $email : '-') . '</td></tr>';
        $html .= '<tr><th style="text-align:left;padding:6px 8px;border-bottom:1px solid #e2e8f0;vertical-align:top;">문의내용</th><td style="padding:6px 8px;border-bottom:1px solid #e2e8f0;">' . $message . '</td></tr>';
        $html .= '<tr><th style="text-align:left;padding:6px 8px;border-bottom:1px solid #e2e8f0;">접수 페이지</th><td style="padding:6px 8px;border-bottom:1px solid #e2e8f0;">' . ($referer !== '' ? $referer : '-') . '</td></tr>';
        $html .= '<tr><th style="text-align:left;padding:6px 8px;border-bottom:1px solid #e2e8f0;">개인정보 동의</th><td style="padding:6px 8px;border-bottom:1px solid #e2e8f0;">' . $privacy . '</td></tr>';
        $html .= '<tr><th style="text-align:left;padding:6px 8px;border-bottom:1px solid #e2e8f0;">접수 일시</th><td style="padding:6px 8px;border-bottom:1px solid #e2e8f0;">' . $created . '</td></tr>';
        $html .= '<tr><th style="text-align:left;padding:6px 8px;border-bottom:1px solid #e2e8f0;">접수 IP</th><td style="padding:6px 8px;border-bottom:1px solid #e2e8f0;">' . $ip . '</td></tr>';
        $html .= '</table>';

        if ($admin_url !== '') {
            $html .= '<p style="margin-top:16px;"><a href="' . $admin_url . '">관리자에서 문의 확인</a></p>';
        }

        $html .= '<p style="margin-top:16px;font-size:12px;color:#64748b;">';
        $html .= '본 메일에는 이름·연락처·문의내용 등 개인정보가 포함됩니다. 수신 메일함 보안 관리에 유의하고 외부 공유를 금지해 주세요.';
        $html .= '</p>';

        return $html;
    }
}

/**
 * 이메일 알림 발송 (mailer 우선)
 *
 * @param array $inquiry_data
 * @return bool 하나 이상 성공 시 true
 */
if (!function_exists('onoff_send_inquiry_email_notification')) {
    function onoff_send_inquiry_email_notification($inquiry_data)
    {
        if (!onoff_inquiry_notify_is_enabled()) {
            return false;
        }

        $recipients = onoff_get_inquiry_notify_emails();
        if (empty($recipients)) {
            return false;
        }

        if (!function_exists('mailer') && defined('G5_LIB_PATH') && is_file(G5_LIB_PATH . '/mailer.lib.php')) {
            include_once G5_LIB_PATH . '/mailer.lib.php';
        }

        if (!function_exists('mailer')) {
            return false;
        }

        global $config;

        $from_name = function_exists('g5site_cfg') ? g5site_cfg('inquiry_notify_name', '') : '';
        if ($from_name === '' && function_exists('g5site_cfg')) {
            $from_name = g5site_cfg('site_name', '사이트');
        }
        if ($from_name === '' && !empty($config['cf_admin_email_name'])) {
            $from_name = $config['cf_admin_email_name'];
        }

        $from_email = '';
        if (!empty($config['cf_admin_email']) && filter_var($config['cf_admin_email'], FILTER_VALIDATE_EMAIL)) {
            $from_email = $config['cf_admin_email'];
        } elseif (function_exists('g5site_cfg')) {
            $from_email = g5site_cfg('email', '');
            if (!filter_var($from_email, FILTER_VALIDATE_EMAIL)) {
                $from_email = '';
            }
        }

        if ($from_email === '') {
            return false;
        }

        $subject = onoff_build_inquiry_email_subject($inquiry_data);
        $body = onoff_build_inquiry_email_body($inquiry_data);

        $sent_any = false;

        foreach ($recipients as $to) {
            try {
                $result = @mailer($from_name, $from_email, $to, $subject, $body, 1);
                if ($result) {
                    $sent_any = true;
                }
            } catch (Exception $e) {
                /* 알림 실패는 무시 */
            }
        }

        return $sent_any;
    }
}

/**
 * 알림 통합 진입점 (저장 성공 후)
 *
 * @param array $inquiry_data
 * @return void
 */
if (!function_exists('onoff_send_inquiry_notifications')) {
    function onoff_send_inquiry_notifications($inquiry_data)
    {
        if (!is_array($inquiry_data)) {
            return;
        }

        try {
            onoff_send_inquiry_email_notification($inquiry_data);
        } catch (Exception $e) {
            /* 알림 실패 무시 */
        }

        try {
            onoff_send_inquiry_telegram_notification($inquiry_data);
        } catch (Exception $e) {
            /* 알림 실패 무시 */
        }

        try {
            onoff_send_inquiry_webhook_notification($inquiry_data);
        } catch (Exception $e) {
            /* 알림 실패 무시 */
        }
    }
}

/**
 * 텔레그램 알림 사용 여부
 *
 * @return bool
 */
if (!function_exists('onoff_inquiry_telegram_is_enabled')) {
    function onoff_inquiry_telegram_is_enabled()
    {
        if (function_exists('g5site_cfg_bool')) {
            return g5site_cfg_bool('inquiry_notify_telegram_enabled', false);
        }

        global $site_config;
        if (!isset($site_config['inquiry_notify_telegram_enabled'])) {
            return false;
        }

        $val = $site_config['inquiry_notify_telegram_enabled'];

        return !($val === false || $val === 0 || $val === '0' || $val === 'false' || $val === 'off');
    }
}

/**
 * @param array $inquiry_data
 * @return string
 */
if (!function_exists('onoff_build_inquiry_telegram_message')) {
    function onoff_build_inquiry_telegram_message($inquiry_data)
    {
        $site_name = isset($inquiry_data['site_name']) ? $inquiry_data['site_name'] : '';
        if ($site_name === '' && function_exists('g5site_cfg')) {
            $site_name = g5site_cfg('site_name', '사이트');
        }

        $name = isset($inquiry_data['name']) ? $inquiry_data['name'] : '';
        $phone = isset($inquiry_data['phone']) ? $inquiry_data['phone'] : '';
        $email = isset($inquiry_data['email']) ? $inquiry_data['email'] : '';
        $message = isset($inquiry_data['message']) ? $inquiry_data['message'] : '';
        $referer = isset($inquiry_data['referer_page']) ? $inquiry_data['referer_page'] : '';
        $created = isset($inquiry_data['created_at']) ? $inquiry_data['created_at'] : '';
        $admin_url = isset($inquiry_data['admin_url']) ? $inquiry_data['admin_url'] : '';

        $lines = array();
        $lines[] = '새 상담문의 접수';
        $lines[] = '';
        $lines[] = '사이트: ' . $site_name;
        $lines[] = '이름: ' . $name;
        $lines[] = '연락처: ' . $phone;
        $lines[] = '이메일: ' . ($email !== '' ? $email : '-');
        $lines[] = '';
        $lines[] = '문의내용:';
        $lines[] = $message;
        $lines[] = '';
        $lines[] = '접수 페이지: ' . ($referer !== '' ? $referer : '-');
        $lines[] = '접수 일시: ' . $created;

        if ($admin_url !== '') {
            $lines[] = '';
            $lines[] = '관리자 확인:';
            $lines[] = $admin_url;
        }

        $text = implode("\n", $lines);

        if (function_exists('mb_strlen') && mb_strlen($text, 'UTF-8') > 3500) {
            $text = mb_substr($text, 0, 3497, 'UTF-8') . '...';
        } elseif (strlen($text) > 3500) {
            $text = substr($text, 0, 3497) . '...';
        }

        return $text;
    }
}

/**
 * Telegram Bot API sendMessage (curl)
 *
 * @param string $bot_token
 * @param string $chat_id
 * @param string $text
 * @return bool
 */
if (!function_exists('onoff_telegram_send_message')) {
    function onoff_telegram_send_message($bot_token, $chat_id, $text)
    {
        if ($bot_token === '' || $chat_id === '' || $text === '') {
            return false;
        }

        $api_url = 'https://api.telegram.org/bot' . $bot_token . '/sendMessage';
        $payload = array(
            'chat_id' => $chat_id,
            'text'    => $text,
        );

        if (!function_exists('curl_init')) {
            /* curl 미설치 시 서버에 php-curl 확장 설치 필요 — 토큰·응답은 로그에 남기지 않음 */
            return false;
        }

        $ch = curl_init($api_url);
        if ($ch === false) {
            return false;
        }

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = @curl_exec($ch);
        $http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $http_code < 200 || $http_code >= 300) {
            return false;
        }

        $decoded = json_decode($response, true);

        return is_array($decoded) && !empty($decoded['ok']);
    }
}

/**
 * @param array $inquiry_data
 * @return bool
 */
if (!function_exists('onoff_send_inquiry_telegram_notification')) {
    function onoff_send_inquiry_telegram_notification($inquiry_data)
    {
        if (!onoff_inquiry_telegram_is_enabled()) {
            return false;
        }

        $bot_token = function_exists('g5site_cfg') ? trim(g5site_cfg('inquiry_notify_telegram_bot_token', '')) : '';
        $chat_id = function_exists('g5site_cfg') ? trim(g5site_cfg('inquiry_notify_telegram_chat_id', '')) : '';

        if ($bot_token === '' || $chat_id === '') {
            return false;
        }

        $text = onoff_build_inquiry_telegram_message($inquiry_data);

        return onoff_telegram_send_message($bot_token, $chat_id, $text);
    }
}

/**
 * 웹훅 알림 (JSON POST) — inquiry_notify_webhook_enabled + URL
 *
 * @param array $inquiry_data
 * @return bool
 */
if (!function_exists('onoff_send_inquiry_webhook_notification')) {
    function onoff_send_inquiry_webhook_notification($inquiry_data)
    {
        if (function_exists('g5site_cfg_bool') && !g5site_cfg_bool('inquiry_notify_webhook_enabled', false)) {
            return false;
        }

        $webhook_url = function_exists('g5site_cfg') ? trim(g5site_cfg('inquiry_notify_webhook_url', '')) : '';
        if ($webhook_url === '' || !preg_match('#^https?://#i', $webhook_url)) {
            return false;
        }

        if (!function_exists('curl_init')) {
            return false;
        }

        $payload = array(
            'event'  => 'inquiry_received',
            'site'   => isset($inquiry_data['site_name']) ? $inquiry_data['site_name'] : '',
            'name'   => isset($inquiry_data['name']) ? $inquiry_data['name'] : '',
            'phone'  => isset($inquiry_data['phone']) ? $inquiry_data['phone'] : '',
            'email'  => isset($inquiry_data['email']) ? $inquiry_data['email'] : '',
            'message'=> isset($inquiry_data['message']) ? $inquiry_data['message'] : '',
            'created_at' => isset($inquiry_data['created_at']) ? $inquiry_data['created_at'] : '',
            'admin_url'  => isset($inquiry_data['admin_url']) ? $inquiry_data['admin_url'] : '',
        );

        $ch = curl_init($webhook_url);
        if ($ch === false) {
            return false;
        }

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        @curl_exec($ch);
        $http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $http_code >= 200 && $http_code < 300;
    }
}
