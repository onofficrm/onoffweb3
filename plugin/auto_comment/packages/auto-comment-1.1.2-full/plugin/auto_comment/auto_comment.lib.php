<?php
if (!defined('_GNUBOARD_')) exit;

define('AUTO_COMMENT_VERSION', '1.1.2');

function auto_comment_table($name)
{
    return G5_TABLE_PREFIX.'auto_comment_'.$name;
}

function auto_comment_escape($value)
{
    return function_exists('sql_real_escape_string') ? sql_real_escape_string($value) : addslashes($value);
}

function auto_comment_table_exists($table, $refresh = false)
{
    static $cache = array();

    if ($refresh && isset($cache[$table])) {
        unset($cache[$table]);
    }

    if (isset($cache[$table])) {
        return $cache[$table];
    }

    $table = auto_comment_escape($table);
    $row = sql_fetch(" show tables like '{$table}' ", false);
    $cache[$table] = is_array($row) && count($row) > 0;

    return $cache[$table];
}

function auto_comment_column_exists($table, $column)
{
    $table = auto_comment_escape($table);
    $column = auto_comment_escape($column);
    $row = sql_fetch(" show columns from {$table} like '{$column}' ", false);

    return is_array($row) && !empty($row['Field']);
}

function auto_comment_ensure_board_columns()
{
    $board = auto_comment_table('board');
    if (!auto_comment_table_exists($board)) {
        return;
    }

    $columns = array(
        'acb_auto_new_post' => "alter table {$board} add acb_auto_new_post tinyint not null default 1",
        'acb_strategy_scan' => "alter table {$board} add acb_strategy_scan tinyint not null default 1",
        'acb_manual_comment' => "alter table {$board} add acb_manual_comment tinyint not null default 1",
        'acb_review_mode' => "alter table {$board} add acb_review_mode tinyint not null default 0",
        'acb_tone_profile' => "alter table {$board} add acb_tone_profile varchar(30) not null default 'random'",
        'acb_midnight_schedule' => "alter table {$board} add acb_midnight_schedule tinyint not null default 0",
        'acb_interval_minutes' => "alter table {$board} add acb_interval_minutes int not null default 0"
    );
    foreach ($columns as $column => $sql) {
        if (!auto_comment_column_exists($board, $column)) {
            sql_query($sql, false);
        }
    }
}

function auto_comment_is_installed()
{
    static $installed = null;

    if ($installed !== null) {
        return $installed;
    }

    $installed = auto_comment_table_exists(auto_comment_table('setting'))
        && auto_comment_table_exists(auto_comment_table('queue'));

    return $installed;
}

function auto_comment_log($action, $message, $queue_id)
{
    if (!auto_comment_table_exists(auto_comment_table('log'))) {
        return;
    }

    $action = auto_comment_escape($action);
    $message = auto_comment_escape($message);
    $queue_id = (int) $queue_id;

    sql_query(" insert into ".auto_comment_table('log')."
                    set acq_id = '{$queue_id}',
                        acl_action = '{$action}',
                        acl_message = '{$message}',
                        acl_datetime = '".G5_TIME_YMDHIS."' ", false);
}

function auto_comment_default_settings()
{
    return array(
        'enabled' => '0',
        'trigger_percent' => '3',
        'trigger_interval' => '180',
        'max_run_items' => '2',
        'max_run_seconds' => '2',
        'daily_limit' => '20',
        'pending_expire_days' => '7',
        'forbidden_words' => '',
        'generator_mode' => 'ai',
        'ai_provider' => 'icrm',
        'ai_model' => 'gemini-2.0-flash-lite',
        'ai_api_key' => '',
        'icrm_api_base_url' => 'https://icrm.co.kr/api/auto-comment',
        'icrm_license_key' => '',
        'ai_input_usd_per_million' => '0.075',
        'ai_output_usd_per_million' => '0.30',
        'ai_usd_krw' => '1400',
        'ai_author_name' => '세부나이트 AI 가이드',
        'auto_author_mode' => 'random_korean',
        'auto_author_reuse_percent' => '65',
        'strategy_enabled' => '1',
        'strategy_recent_days' => '14',
        'strategy_scan_limit' => '3',
        'strategy_review_mode' => '0',
        'auto_min_comments' => '0',
        'auto_max_comments' => '20',
        'auto_views_per_comment' => '100',
        'auto_views_per_comment_min' => '20',
        'auto_views_per_comment_max' => '50',
        'skip_bots' => '1'
    );
}

function auto_comment_setting_exists($key)
{
    $table = auto_comment_table('setting');
    if (!auto_comment_table_exists($table)) {
        return false;
    }

    $row = sql_fetch(" select ac_key from {$table} where ac_key = '".auto_comment_escape($key)."' limit 1 ", false);

    return !empty($row['ac_key']);
}

function auto_comment_set_setting_if_missing($key, $value)
{
    if (!auto_comment_setting_exists($key)) {
        auto_comment_set_setting($key, $value);
    }
}

function auto_comment_seed_default_settings($overwrite)
{
    foreach (auto_comment_default_settings() as $key => $value) {
        if ($overwrite) {
            auto_comment_set_setting($key, $value);
        } else {
            auto_comment_set_setting_if_missing($key, $value);
        }
    }
    auto_comment_set_setting('schema_version', AUTO_COMMENT_VERSION);
}

function auto_comment_install()
{
    $setting = auto_comment_table('setting');
    $board = auto_comment_table('board');
    $author = auto_comment_table('author');
    $template = auto_comment_table('template');
    $queue = auto_comment_table('queue');
    $log = auto_comment_table('log');
    $ai_usage = auto_comment_table('ai_usage');
    $visitor = auto_comment_table('visitor');
    sql_query(" create table if not exists {$setting} (
        ac_key varchar(50) not null,
        ac_value text not null,
        primary key (ac_key)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", false);

    sql_query(" create table if not exists {$board} (
        acb_id int not null auto_increment,
        bo_table varchar(20) not null,
        acb_enabled tinyint not null default 0,
        acb_auto_new_post tinyint not null default 1,
        acb_strategy_scan tinyint not null default 1,
        acb_midnight_schedule tinyint not null default 0,
        acb_interval_minutes int not null default 0,
        acb_manual_comment tinyint not null default 1,
        acb_review_mode tinyint not null default 0,
        acb_tone_profile varchar(30) not null default 'random',
        acb_comments_per_post tinyint not null default 1,
        acb_min_delay int not null default 30,
        acb_max_delay int not null default 360,
        acb_template_group varchar(50) not null default '',
        acb_updated_at datetime not null default '0000-00-00 00:00:00',
        primary key (acb_id),
        unique key bo_table (bo_table)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", false);
    auto_comment_ensure_board_columns();

    sql_query(" create table if not exists {$author} (
        aca_id int not null auto_increment,
        aca_name varchar(100) not null,
        aca_enabled tinyint not null default 1,
        aca_sort int not null default 0,
        primary key (aca_id)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", false);

    sql_query(" create table if not exists {$template} (
        act_id int not null auto_increment,
        act_group varchar(50) not null default 'default',
        act_content text not null,
        act_enabled tinyint not null default 1,
        act_sort int not null default 0,
        primary key (act_id),
        key act_group (act_group)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", false);

    sql_query(" create table if not exists {$queue} (
        acq_id int not null auto_increment,
        bo_table varchar(20) not null,
        wr_id int not null default 0,
        acq_subject varchar(255) not null default '',
        acq_author varchar(100) not null default '',
        acq_content text not null,
        acq_scheduled_at datetime not null default '0000-00-00 00:00:00',
        acq_inserted_at datetime not null default '0000-00-00 00:00:00',
        acq_status varchar(20) not null default 'pending',
        acq_error text not null,
        acq_created_at datetime not null default '0000-00-00 00:00:00',
        primary key (acq_id),
        key status_schedule (acq_status, acq_scheduled_at),
        key post_key (bo_table, wr_id)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", false);

    sql_query(" create table if not exists {$log} (
        acl_id int not null auto_increment,
        acq_id int not null default 0,
        acl_action varchar(50) not null default '',
        acl_message text not null,
        acl_datetime datetime not null default '0000-00-00 00:00:00',
        primary key (acl_id),
        key acq_id (acq_id)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", false);

    sql_query(" create table if not exists {$ai_usage} (
        acu_id int not null auto_increment,
        bo_table varchar(20) not null default '',
        wr_id int not null default 0,
        acu_model varchar(100) not null default '',
        acu_status varchar(20) not null default '',
        acu_prompt_tokens int not null default 0,
        acu_output_tokens int not null default 0,
        acu_total_tokens int not null default 0,
        acu_cost_usd decimal(12,8) not null default 0,
        acu_cost_krw decimal(12,4) not null default 0,
        acu_error text not null,
        acu_created_at datetime not null default '0000-00-00 00:00:00',
        primary key (acu_id),
        key created_at (acu_created_at),
        key post_key (bo_table, wr_id)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", false);

    sql_query(" create table if not exists {$visitor} (
        acv_id int not null auto_increment,
        acv_key char(64) not null,
        acv_ip varchar(45) not null default '',
        acv_first_seen_at datetime not null default '0000-00-00 00:00:00',
        acv_last_seen_at datetime not null default '0000-00-00 00:00:00',
        acv_view_count int not null default 0,
        primary key (acv_id),
        unique key visitor_key (acv_key),
        key first_seen (acv_first_seen_at),
        key last_seen (acv_last_seen_at)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", false);

    auto_comment_ensure_post_view_table();
    auto_comment_table_exists($setting, true);
    auto_comment_table_exists($board, true);
    auto_comment_table_exists($author, true);
    auto_comment_table_exists($template, true);
    auto_comment_table_exists($queue, true);
    auto_comment_table_exists($log, true);

    auto_comment_seed_default_settings(false);

    auto_comment_seed_defaults(false);
    auto_comment_seed_board_configs(false);

    auto_comment_log('install', '자동댓글 모듈 설치 완료: '.AUTO_COMMENT_VERSION, 0);
}

function auto_comment_update()
{
    if (!auto_comment_is_installed()) {
        auto_comment_install();
        return;
    }

    auto_comment_ensure_board_columns();
    sql_query(" update ".auto_comment_table('board')."
                  set acb_interval_minutes = 60
                where acb_midnight_schedule = 1
                  and acb_interval_minutes < 1 ", false);
    auto_comment_ensure_post_view_table();
    auto_comment_ensure_ai_usage_table();
    auto_comment_ensure_visitor_table();
    auto_comment_seed_default_settings(false);
    auto_comment_seed_defaults(false);
    auto_comment_seed_board_configs(false);
    auto_comment_log('update', '자동댓글 모듈 업데이트 완료: '.AUTO_COMMENT_VERSION, 0);
}

function auto_comment_ensure_post_view_table()
{
    $post_view = auto_comment_table('post_view');

    sql_query(" create table if not exists {$post_view} (
        acv_id int not null auto_increment,
        bo_table varchar(20) not null,
        wr_id int not null default 0,
        acv_subject varchar(255) not null default '',
        acv_view_count int not null default 0,
        acv_ip varchar(45) not null default '',
        acv_visitor_key char(64) not null default '',
        acv_visitor_type varchar(20) not null default '',
        acv_last_viewed_at datetime not null default '0000-00-00 00:00:00',
        acv_created_at datetime not null default '0000-00-00 00:00:00',
        primary key (acv_id),
        unique key post_key (bo_table, wr_id),
        key last_viewed (acv_last_viewed_at)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", false);
    if (!auto_comment_column_exists($post_view, 'acv_ip')) {
        sql_query(" alter table {$post_view} add acv_ip varchar(45) not null default '' after acv_view_count ", false);
    }
    if (!auto_comment_column_exists($post_view, 'acv_visitor_key')) {
        sql_query(" alter table {$post_view} add acv_visitor_key char(64) not null default '' after acv_ip ", false);
    }
    if (!auto_comment_column_exists($post_view, 'acv_visitor_type')) {
        sql_query(" alter table {$post_view} add acv_visitor_type varchar(20) not null default '' after acv_visitor_key ", false);
    }
}

function auto_comment_ensure_visitor_table()
{
    $visitor = auto_comment_table('visitor');
    sql_query(" create table if not exists {$visitor} (
        acv_id int not null auto_increment,
        acv_key char(64) not null,
        acv_ip varchar(45) not null default '',
        acv_first_seen_at datetime not null default '0000-00-00 00:00:00',
        acv_last_seen_at datetime not null default '0000-00-00 00:00:00',
        acv_view_count int not null default 0,
        primary key (acv_id),
        unique key visitor_key (acv_key),
        key first_seen (acv_first_seen_at),
        key last_seen (acv_last_seen_at)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", false);
}

function auto_comment_ensure_ai_usage_table()
{
    $table = auto_comment_table('ai_usage');

    sql_query(" create table if not exists {$table} (
        acu_id int not null auto_increment,
        bo_table varchar(20) not null default '',
        wr_id int not null default 0,
        acu_model varchar(100) not null default '',
        acu_status varchar(20) not null default '',
        acu_prompt_tokens int not null default 0,
        acu_output_tokens int not null default 0,
        acu_total_tokens int not null default 0,
        acu_cost_usd decimal(12,8) not null default 0,
        acu_cost_krw decimal(12,4) not null default 0,
        acu_error text not null,
        acu_created_at datetime not null default '0000-00-00 00:00:00',
        primary key (acu_id),
        key created_at (acu_created_at),
        key post_key (bo_table, wr_id)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", false);
}

function auto_comment_get_setting($key, $default)
{
    if (!auto_comment_is_installed()) {
        return $default;
    }

    $row = sql_fetch(" select ac_value from ".auto_comment_table('setting')." where ac_key = '".auto_comment_escape($key)."' ");
    return isset($row['ac_value']) ? $row['ac_value'] : $default;
}

function auto_comment_set_setting($key, $value)
{
    $table = auto_comment_table('setting');
    if (!auto_comment_table_exists($table)) {
        return;
    }

    $key = auto_comment_escape($key);
    $value = auto_comment_escape($value);
    sql_query(" replace into {$table} set ac_key = '{$key}', ac_value = '{$value}' ", false);
}

function auto_comment_default_authors()
{
    $prefixes = array('세부나이트', '세부현지', '세부여행', '세부가이드', '막탄가이드', '라푸라푸가이드', '세부정보', '세부일정', '밤문화가이드', '현지케어');
    $roles = array('가이드', '매니저', '안내팀', '정보팀', '일정팀', '상담팀', '운영팀', '리뷰팀', '체크팀', '플래너');
    $names = array();
    $count = 1;

    foreach ($prefixes as $prefix) {
        foreach ($roles as $role) {
            $names[] = $prefix.' '.$role.' '.sprintf('%02d', $count);
            $count++;
        }
    }

    return $names;
}

function auto_comment_random_korean_nickname()
{
    $prefixes = array('세부', '막탄', '라푸라푸', '여행', '현지', '바다', '노을', '야시장', '골목', '휴양', '남국', '트립', '밤산책', '느긋한', '조용한', '가벼운');
    $nouns = array('나들이', '산책', '메모', '수첩', '체크', '바람', '달빛', '파도', '코스', '여정', '쉼표', '구름', '마실', '길잡이', '참고장', '동선');
    $suffixes = array('', '', '', '러', '중', '메이트', '노트', '톡', '픽', '리스트');

    $prefix = $prefixes[array_rand($prefixes)];
    $noun = $nouns[array_rand($nouns)];
    $suffix = $suffixes[array_rand($suffixes)];
    $nickname = $prefix.$noun.$suffix;

    if (function_exists('mb_substr')) {
        return mb_substr($nickname, 0, 20, 'UTF-8');
    }

    return substr($nickname, 0, 60);
}

function auto_comment_author_key($author)
{
    $author = trim(strip_tags((string) $author));
    $author = preg_replace('/\s+/u', ' ', $author);
    if (function_exists('mb_strtolower')) {
        return mb_strtolower($author, 'UTF-8');
    }

    return strtolower($author);
}

function auto_comment_random_base36($length)
{
    $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $value = '';
    for ($i = 0; $i < $length; $i++) {
        $value .= $chars[mt_rand(0, strlen($chars) - 1)];
    }

    return $value;
}

function auto_comment_author_id_base($author)
{
    $author = trim(strip_tags((string) $author));
    $map = array(
        '라푸라푸' => 'lapu',
        '세부나이트' => 'cebu',
        '세부' => 'cebu',
        '막탄' => 'mactan',
        '여행' => 'trip',
        '현지' => 'local',
        '바다' => 'sea',
        '노을' => 'sunset',
        '야시장' => 'night',
        '골목' => 'alley',
        '휴양' => 'resort',
        '남국' => 'south',
        '트립' => 'trip',
        '밤산책' => 'nightwalk',
        '느긋한' => 'slow',
        '조용한' => 'calm',
        '가벼운' => 'light',
        '나들이' => 'outing',
        '산책' => 'walk',
        '메모' => 'memo',
        '수첩' => 'note',
        '체크' => 'check',
        '바람' => 'wind',
        '달빛' => 'moon',
        '파도' => 'wave',
        '코스' => 'course',
        '여정' => 'journey',
        '쉼표' => 'rest',
        '구름' => 'cloud',
        '마실' => 'walk',
        '길잡이' => 'guide',
        '참고장' => 'note',
        '동선' => 'route',
        '가이드' => 'guide',
        '매니저' => 'manager',
        '안내' => 'guide',
        '정보' => 'info',
        '일정' => 'plan',
        '상담' => 'care',
        '운영' => 'staff',
        '리뷰' => 'review',
        '플래너' => 'planner'
    );
    $tokens = array();
    foreach ($map as $needle => $token) {
        if (strpos($author, $needle) !== false && !in_array($token, $tokens, true)) {
            $tokens[] = $token;
        }
        if (count($tokens) >= 2) {
            break;
        }
    }

    if (!$tokens) {
        $tokens[] = 'ac';
    }

    $base = preg_replace('/[^a-z0-9_]/', '', implode('_', $tokens));
    $base = trim($base, '_');
    if ($base === '') {
        $base = 'ac';
    }

    return substr($base, 0, 11);
}

function auto_comment_generate_member_id($author)
{
    global $g5;

    if (empty($g5['member_table'])) {
        return '';
    }

    $base = auto_comment_author_id_base($author);
    for ($i = 0; $i < 80; $i++) {
        $suffix = auto_comment_random_base36($i < 20 ? 6 : 8);
        $mb_id = substr($base.'_'.$suffix, 0, 20);
        $row = sql_fetch(" select mb_id from {$g5['member_table']} where mb_id = '".auto_comment_escape($mb_id)."' ", false);
        if (empty($row['mb_id'])) {
            return $mb_id;
        }
    }

    return '';
}

function auto_comment_unique_member_nick($author)
{
    global $g5;

    $author = trim(strip_tags((string) $author));
    if ($author === '' || empty($g5['member_table'])) {
        return '';
    }

    $bot = sql_fetch(" select mb_id
                         from {$g5['member_table']}
                        where mb_nick = '".auto_comment_escape($author)."'
                          and mb_10 = 'auto_comment_bot'
                        limit 1 ", false);
    if (!empty($bot['mb_id'])) {
        return $author;
    }

    $exists = sql_fetch(" select mb_id from {$g5['member_table']} where mb_nick = '".auto_comment_escape($author)."' limit 1 ", false);
    if (empty($exists['mb_id'])) {
        return $author;
    }

    $base = function_exists('mb_substr') ? mb_substr($author, 0, 14, 'UTF-8') : substr($author, 0, 42);
    for ($i = 0; $i < 50; $i++) {
        $nick = trim($base.' '.mt_rand(10, 99));
        $exists = sql_fetch(" select mb_id from {$g5['member_table']} where mb_nick = '".auto_comment_escape($nick)."' limit 1 ", false);
        if (empty($exists['mb_id'])) {
            return $nick;
        }
    }

    return $base.' '.auto_comment_random_base36(4);
}

function auto_comment_avatar_color($hash, $offset)
{
    return 48 + (($hash >> $offset) & 0x7f);
}

function auto_comment_avatar_point($size, $ratio)
{
    return (int) round($size * $ratio);
}

function auto_comment_avatar_draw_person($img, $size, $main, $accent, $dark)
{
    imagefilledellipse($img, auto_comment_avatar_point($size, 0.50), auto_comment_avatar_point($size, 0.34), auto_comment_avatar_point($size, 0.30), auto_comment_avatar_point($size, 0.30), $main);
    imagefilledellipse($img, auto_comment_avatar_point($size, 0.43), auto_comment_avatar_point($size, 0.32), auto_comment_avatar_point($size, 0.04), auto_comment_avatar_point($size, 0.04), $dark);
    imagefilledellipse($img, auto_comment_avatar_point($size, 0.57), auto_comment_avatar_point($size, 0.32), auto_comment_avatar_point($size, 0.04), auto_comment_avatar_point($size, 0.04), $dark);
    imagearc($img, auto_comment_avatar_point($size, 0.50), auto_comment_avatar_point($size, 0.39), auto_comment_avatar_point($size, 0.16), auto_comment_avatar_point($size, 0.10), 15, 165, $dark);
    imagefilledellipse($img, auto_comment_avatar_point($size, 0.50), auto_comment_avatar_point($size, 0.83), auto_comment_avatar_point($size, 0.58), auto_comment_avatar_point($size, 0.54), $accent);
}

function auto_comment_avatar_draw_cat($img, $size, $main, $accent, $dark)
{
    imagefilledpolygon($img, array(
        auto_comment_avatar_point($size, 0.26), auto_comment_avatar_point($size, 0.35),
        auto_comment_avatar_point($size, 0.35), auto_comment_avatar_point($size, 0.15),
        auto_comment_avatar_point($size, 0.44), auto_comment_avatar_point($size, 0.36)
    ), 3, $main);
    imagefilledpolygon($img, array(
        auto_comment_avatar_point($size, 0.56), auto_comment_avatar_point($size, 0.36),
        auto_comment_avatar_point($size, 0.65), auto_comment_avatar_point($size, 0.15),
        auto_comment_avatar_point($size, 0.74), auto_comment_avatar_point($size, 0.35)
    ), 3, $main);
    imagefilledellipse($img, auto_comment_avatar_point($size, 0.50), auto_comment_avatar_point($size, 0.48), auto_comment_avatar_point($size, 0.56), auto_comment_avatar_point($size, 0.48), $main);
    imagefilledellipse($img, auto_comment_avatar_point($size, 0.41), auto_comment_avatar_point($size, 0.45), auto_comment_avatar_point($size, 0.05), auto_comment_avatar_point($size, 0.07), $dark);
    imagefilledellipse($img, auto_comment_avatar_point($size, 0.59), auto_comment_avatar_point($size, 0.45), auto_comment_avatar_point($size, 0.05), auto_comment_avatar_point($size, 0.07), $dark);
    imagefilledellipse($img, auto_comment_avatar_point($size, 0.50), auto_comment_avatar_point($size, 0.55), auto_comment_avatar_point($size, 0.07), auto_comment_avatar_point($size, 0.05), $accent);
    imageline($img, auto_comment_avatar_point($size, 0.37), auto_comment_avatar_point($size, 0.56), auto_comment_avatar_point($size, 0.18), auto_comment_avatar_point($size, 0.50), $dark);
    imageline($img, auto_comment_avatar_point($size, 0.37), auto_comment_avatar_point($size, 0.61), auto_comment_avatar_point($size, 0.18), auto_comment_avatar_point($size, 0.64), $dark);
    imageline($img, auto_comment_avatar_point($size, 0.63), auto_comment_avatar_point($size, 0.56), auto_comment_avatar_point($size, 0.82), auto_comment_avatar_point($size, 0.50), $dark);
    imageline($img, auto_comment_avatar_point($size, 0.63), auto_comment_avatar_point($size, 0.61), auto_comment_avatar_point($size, 0.82), auto_comment_avatar_point($size, 0.64), $dark);
}

function auto_comment_avatar_draw_dog($img, $size, $main, $accent, $dark)
{
    imagefilledellipse($img, auto_comment_avatar_point($size, 0.28), auto_comment_avatar_point($size, 0.45), auto_comment_avatar_point($size, 0.22), auto_comment_avatar_point($size, 0.34), $accent);
    imagefilledellipse($img, auto_comment_avatar_point($size, 0.72), auto_comment_avatar_point($size, 0.45), auto_comment_avatar_point($size, 0.22), auto_comment_avatar_point($size, 0.34), $accent);
    imagefilledellipse($img, auto_comment_avatar_point($size, 0.50), auto_comment_avatar_point($size, 0.50), auto_comment_avatar_point($size, 0.54), auto_comment_avatar_point($size, 0.46), $main);
    imagefilledellipse($img, auto_comment_avatar_point($size, 0.40), auto_comment_avatar_point($size, 0.45), auto_comment_avatar_point($size, 0.05), auto_comment_avatar_point($size, 0.05), $dark);
    imagefilledellipse($img, auto_comment_avatar_point($size, 0.60), auto_comment_avatar_point($size, 0.45), auto_comment_avatar_point($size, 0.05), auto_comment_avatar_point($size, 0.05), $dark);
    imagefilledellipse($img, auto_comment_avatar_point($size, 0.50), auto_comment_avatar_point($size, 0.58), auto_comment_avatar_point($size, 0.18), auto_comment_avatar_point($size, 0.13), $accent);
    imagefilledellipse($img, auto_comment_avatar_point($size, 0.50), auto_comment_avatar_point($size, 0.55), auto_comment_avatar_point($size, 0.06), auto_comment_avatar_point($size, 0.05), $dark);
}

function auto_comment_avatar_draw_palm($img, $size, $main, $accent, $dark)
{
    imagesetthickness($img, max(2, auto_comment_avatar_point($size, 0.05)));
    imageline($img, auto_comment_avatar_point($size, 0.48), auto_comment_avatar_point($size, 0.86), auto_comment_avatar_point($size, 0.55), auto_comment_avatar_point($size, 0.44), $dark);
    imagesetthickness($img, 1);
    imagefilledellipse($img, auto_comment_avatar_point($size, 0.33), auto_comment_avatar_point($size, 0.42), auto_comment_avatar_point($size, 0.38), auto_comment_avatar_point($size, 0.14), $main);
    imagefilledellipse($img, auto_comment_avatar_point($size, 0.66), auto_comment_avatar_point($size, 0.38), auto_comment_avatar_point($size, 0.40), auto_comment_avatar_point($size, 0.15), $main);
    imagefilledellipse($img, auto_comment_avatar_point($size, 0.52), auto_comment_avatar_point($size, 0.28), auto_comment_avatar_point($size, 0.18), auto_comment_avatar_point($size, 0.34), $accent);
    imagefilledellipse($img, auto_comment_avatar_point($size, 0.50), auto_comment_avatar_point($size, 0.88), auto_comment_avatar_point($size, 0.62), auto_comment_avatar_point($size, 0.13), $accent);
}

function auto_comment_avatar_draw_cup($img, $size, $main, $accent, $dark)
{
    imagefilledrectangle($img, auto_comment_avatar_point($size, 0.28), auto_comment_avatar_point($size, 0.38), auto_comment_avatar_point($size, 0.63), auto_comment_avatar_point($size, 0.72), $main);
    imagefilledellipse($img, auto_comment_avatar_point($size, 0.46), auto_comment_avatar_point($size, 0.38), auto_comment_avatar_point($size, 0.36), auto_comment_avatar_point($size, 0.12), $accent);
    imagearc($img, auto_comment_avatar_point($size, 0.65), auto_comment_avatar_point($size, 0.53), auto_comment_avatar_point($size, 0.24), auto_comment_avatar_point($size, 0.22), 285, 75, $main);
    imagearc($img, auto_comment_avatar_point($size, 0.65), auto_comment_avatar_point($size, 0.53), auto_comment_avatar_point($size, 0.16), auto_comment_avatar_point($size, 0.14), 285, 75, $main);
    imageline($img, auto_comment_avatar_point($size, 0.34), auto_comment_avatar_point($size, 0.25), auto_comment_avatar_point($size, 0.34), auto_comment_avatar_point($size, 0.16), $dark);
    imageline($img, auto_comment_avatar_point($size, 0.47), auto_comment_avatar_point($size, 0.25), auto_comment_avatar_point($size, 0.47), auto_comment_avatar_point($size, 0.14), $dark);
    imagefilledellipse($img, auto_comment_avatar_point($size, 0.48), auto_comment_avatar_point($size, 0.78), auto_comment_avatar_point($size, 0.54), auto_comment_avatar_point($size, 0.08), $dark);
}

function auto_comment_generate_member_avatar($mb_id, $author)
{
    global $config;

    if (!defined('G5_DATA_PATH') || $mb_id === '' || !function_exists('imagecreatetruecolor') || !function_exists('imagegif')) {
        return false;
    }

    $mb_dir = substr($mb_id, 0, 2);
    $file_name = function_exists('get_mb_icon_name') ? get_mb_icon_name($mb_id).'.gif' : $mb_id.'.gif';
    $avatar_dir = G5_DATA_PATH.'/member_image/'.$mb_dir;
    $avatar_path = $avatar_dir.'/'.$file_name;
    if (is_file($avatar_path)) {
        return true;
    }

    @mkdir(G5_DATA_PATH.'/member_image', G5_DIR_PERMISSION);
    @chmod(G5_DATA_PATH.'/member_image', G5_DIR_PERMISSION);
    @mkdir($avatar_dir, G5_DIR_PERMISSION);
    @chmod($avatar_dir, G5_DIR_PERMISSION);

    $width = isset($config['cf_member_img_width']) && (int) $config['cf_member_img_width'] > 0 ? (int) $config['cf_member_img_width'] : 60;
    $height = isset($config['cf_member_img_height']) && (int) $config['cf_member_img_height'] > 0 ? (int) $config['cf_member_img_height'] : 60;
    $size = max(40, min(120, min($width, $height)));
    $hash = (int) sprintf('%u', crc32($author.'|'.$mb_id));
    $img = imagecreatetruecolor($size, $size);
    if (!$img) {
        return false;
    }

    if (function_exists('imageantialias')) {
        imageantialias($img, true);
    }

    $bg = imagecolorallocate($img, 238, 242, 247);
    $main = imagecolorallocate($img, auto_comment_avatar_color($hash, 0), auto_comment_avatar_color($hash, 7), auto_comment_avatar_color($hash, 14));
    $accent = imagecolorallocate($img, auto_comment_avatar_color($hash, 21), auto_comment_avatar_color($hash, 5), auto_comment_avatar_color($hash, 12));
    $dark = imagecolorallocate($img, 57, 63, 78);
    imagefilledrectangle($img, 0, 0, $size, $size, $bg);

    switch ($hash % 5) {
        case 0:
            auto_comment_avatar_draw_person($img, $size, $main, $accent, $dark);
            break;
        case 1:
            auto_comment_avatar_draw_cat($img, $size, $main, $accent, $dark);
            break;
        case 2:
            auto_comment_avatar_draw_dog($img, $size, $main, $accent, $dark);
            break;
        case 3:
            auto_comment_avatar_draw_palm($img, $size, $main, $accent, $dark);
            break;
        default:
            auto_comment_avatar_draw_cup($img, $size, $main, $accent, $dark);
            break;
    }

    $ok = imagegif($img, $avatar_path);
    if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 80500) {
        imagedestroy($img);
    }
    if ($ok) {
        @chmod($avatar_path, G5_FILE_PERMISSION);
    }

    return $ok;
}

function auto_comment_ensure_bot_member($author)
{
    global $g5;

    $author = trim(strip_tags((string) $author));
    if ($author === '' || empty($g5['member_table'])) {
        return '';
    }

    $author_key = auto_comment_author_key($author);
    $row = sql_fetch(" select mb_id
                         from {$g5['member_table']}
                        where mb_10 = 'auto_comment_bot'
                          and (mb_nick = '".auto_comment_escape($author)."' or mb_2 = '".auto_comment_escape($author_key)."')
                          and mb_leave_date = ''
                          and mb_intercept_date = ''
                        order by mb_datetime asc
                        limit 1 ", false);
    if (!empty($row['mb_id'])) {
        auto_comment_generate_member_avatar($row['mb_id'], $author);
        return $row['mb_id'];
    }

    $mb_id = auto_comment_generate_member_id($author);
    $mb_nick = auto_comment_unique_member_nick($author);
    if ($mb_id === '' || $mb_nick === '') {
        return '';
    }

    $password = get_encrypt_string(auto_comment_random_base36(24).mt_rand(1000, 9999));
    $email = $mb_id.'@auto-comment.local';
    $ip = isset($_SERVER['REMOTE_ADDR']) ? preg_replace('/[^0-9a-fA-F:\.]/', '', $_SERVER['REMOTE_ADDR']) : auto_comment_system_ip();
    $name = auto_comment_escape($mb_nick);
    $profile = auto_comment_escape('자동댓글 전용 회원');

    sql_query(" insert into {$g5['member_table']}
                    set mb_id = '".auto_comment_escape($mb_id)."',
                        mb_password = '".auto_comment_escape($password)."',
                        mb_name = '{$name}',
                        mb_nick = '{$name}',
                        mb_email = '".auto_comment_escape($email)."',
                        mb_homepage = '',
                        mb_level = '2',
                        mb_sex = '',
                        mb_birth = '',
                        mb_tel = '',
                        mb_hp = '',
                        mb_certify = '',
                        mb_adult = '0',
                        mb_zip1 = '',
                        mb_zip2 = '',
                        mb_addr1 = '',
                        mb_addr2 = '',
                        mb_addr3 = '',
                        mb_addr_jibeon = '',
                        mb_signature = '',
                        mb_recommend = '',
                        mb_point = '0',
                        mb_today_login = '0000-00-00 00:00:00',
                        mb_login_ip = '',
                        mb_datetime = '".G5_TIME_YMDHIS."',
                        mb_ip = '".auto_comment_escape($ip)."',
                        mb_leave_date = '',
                        mb_intercept_date = '',
                        mb_email_certify = '".G5_TIME_YMDHIS."',
                        mb_memo = '{$profile}',
                        mb_lost_certify = '',
                        mb_mailling = '0',
                        mb_sms = '0',
                        mb_open = '0',
                        mb_open_date = '".G5_TIME_YMD."',
                        mb_profile = '{$profile}',
                        mb_memo_call = '',
                        mb_1 = 'auto_comment',
                        mb_2 = '".auto_comment_escape(auto_comment_author_key($author))."',
                        mb_10 = 'auto_comment_bot' ", false);

    $created = sql_fetch(" select mb_id from {$g5['member_table']} where mb_id = '".auto_comment_escape($mb_id)."' ", false);
    if (empty($created['mb_id'])) {
        return '';
    }

    auto_comment_generate_member_avatar($mb_id, $author);

    return $mb_id;
}

function auto_comment_award_bot_point($mb_id, $board, $wr_id, $is_comment, $parent_id = 0)
{
    if ($mb_id === '' || !function_exists('insert_point') || empty($board['bo_table'])) {
        return 0;
    }

    $wr_id = (int) $wr_id;
    $parent_id = (int) $parent_id;
    $point = $is_comment ? (int) $board['bo_comment_point'] : (int) $board['bo_write_point'];
    if ($wr_id < 1 || $point <= 0) {
        return 0;
    }

    $bo_table = $board['bo_table'];
    $content = $is_comment
        ? "{$board['bo_subject']} {$parent_id}-{$wr_id} 댓글쓰기"
        : "{$board['bo_subject']} {$wr_id} 글쓰기";

    return insert_point($mb_id, $point, $content, $bo_table, $wr_id, $is_comment ? '댓글' : '쓰기');
}

function auto_comment_sync_queue_bot_points($limit = 30)
{
    global $g5;

    $limit = max(1, min(100, (int) $limit));
    if (!auto_comment_is_installed() || empty($g5['write_prefix']) || !function_exists('insert_point')) {
        return 0;
    }

    $synced = 0;
    $result = sql_query(" select *
                            from ".auto_comment_table('queue')."
                           where acq_status = 'inserted'
                             and acq_author <> ''
                           order by acq_inserted_at desc, acq_id desc
                           limit {$limit} ", false);
    while ($queue = sql_fetch_array($result)) {
        $bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', $queue['bo_table']);
        $wr_parent = (int) $queue['wr_id'];
        if (!$bo_table || $wr_parent < 1) {
            continue;
        }

        $board = get_board_db($bo_table, true);
        $write_table = $g5['write_prefix'].$bo_table;
        if (!$board || empty($board['bo_table']) || !auto_comment_table_exists($write_table)) {
            continue;
        }

        $comment = sql_fetch(" select wr_id, wr_parent, mb_id
                                  from {$write_table}
                                 where wr_parent = '{$wr_parent}'
                                   and wr_is_comment = 1
                                   and wr_name = '".auto_comment_escape($queue['acq_author'])."'
                                   and wr_content = '".auto_comment_escape($queue['acq_content'])."'
                                 order by wr_id desc
                                 limit 1 ", false);
        if (empty($comment['wr_id'])) {
            continue;
        }

        $mb_id = auto_comment_ensure_bot_member($queue['acq_author']);
        if ($mb_id === '') {
            continue;
        }

        if ($comment['mb_id'] !== $mb_id) {
            sql_query(" update {$write_table}
                           set mb_id = '".auto_comment_escape($mb_id)."'
                         where wr_id = '".(int) $comment['wr_id']."' ", false);
            sql_query(" update {$g5['board_new_table']}
                           set mb_id = '".auto_comment_escape($mb_id)."'
                         where bo_table = '".auto_comment_escape($bo_table)."'
                           and wr_id = '".(int) $comment['wr_id']."'
                           and wr_parent = '{$wr_parent}' ", false);
        }

        if (auto_comment_award_bot_point($mb_id, $board, (int) $comment['wr_id'], true, $wr_parent) === 1) {
            $synced++;
        }
    }

    return $synced;
}

function auto_comment_sync_bot_write_points($limit = 50)
{
    global $g5;

    $limit = max(1, min(100, (int) $limit));
    if (empty($g5['write_prefix']) || empty($g5['board_table']) || !function_exists('insert_point')) {
        return 0;
    }

    $synced = 0;
    $boards = sql_query(" select bo_table, bo_subject, bo_write_point, bo_comment_point
                            from {$g5['board_table']}
                           order by bo_table asc ", false);
    while ($board = sql_fetch_array($boards)) {
        $bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', $board['bo_table']);
        if (!$bo_table) {
            continue;
        }

        $write_table = $g5['write_prefix'].$bo_table;
        if (!auto_comment_table_exists($write_table)) {
            continue;
        }

        $rows = sql_query(" select w.wr_id, w.wr_parent, w.wr_is_comment, w.mb_id
                              from {$write_table} w
                              inner join {$g5['member_table']} m on w.mb_id = m.mb_id
                             where m.mb_10 = 'auto_comment_bot'
                               and w.mb_id <> ''
                             order by w.wr_datetime desc, w.wr_id desc
                             limit {$limit} ", false);
        while ($write = sql_fetch_array($rows)) {
            $is_comment = (int) $write['wr_is_comment'] === 1;
            $parent_id = $is_comment ? (int) $write['wr_parent'] : 0;
            if (auto_comment_award_bot_point($write['mb_id'], $board, (int) $write['wr_id'], $is_comment, $parent_id) === 1) {
                $synced++;
            }
        }
    }

    return $synced;
}

function auto_comment_maybe_sync_bot_points()
{
    if (!auto_comment_is_installed()) {
        return;
    }

    $last_run = (int) auto_comment_get_setting('bot_point_sync_at', '0');
    if ($last_run > 0 && G5_SERVER_TIME - $last_run < 600) {
        return;
    }

    auto_comment_set_setting('bot_point_sync_at', (string) G5_SERVER_TIME);
    $synced = auto_comment_sync_queue_bot_points(40) + auto_comment_sync_bot_write_points(60);
    if ($synced > 0) {
        auto_comment_log('bot_point_sync', '자동생성 봇 포인트 '.$synced.'건 보정', 0);
    }
}

function auto_comment_author_is_used($used_authors, $author)
{
    $key = auto_comment_author_key($author);
    return $key !== '' && isset($used_authors[$key]);
}

function auto_comment_used_authors_for_post($bo_table, $wr_id, $exclude_queue_id = 0)
{
    global $g5;

    $used = array();
    $bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', $bo_table);
    $wr_id = (int) $wr_id;
    $exclude_queue_id = (int) $exclude_queue_id;
    if (!$bo_table || $wr_id < 1) {
        return $used;
    }

    $exclude_where = $exclude_queue_id > 0 ? " and acq_id <> '{$exclude_queue_id}' " : '';
    $result = sql_query(" select acq_author
                            from ".auto_comment_table('queue')."
                           where bo_table = '".auto_comment_escape($bo_table)."'
                             and wr_id = '{$wr_id}'
                             and acq_author <> ''
                             and acq_status in ('review', 'pending', 'inserted')
                             {$exclude_where} ", false);
    while ($row = sql_fetch_array($result)) {
        $key = auto_comment_author_key($row['acq_author']);
        if ($key !== '') {
            $used[$key] = true;
        }
    }

    if (!empty($g5['write_prefix'])) {
        $write_table = $g5['write_prefix'].$bo_table;
        if (auto_comment_table_exists($write_table)) {
            $comments = sql_query(" select wr_name
                                      from {$write_table}
                                     where wr_parent = '{$wr_id}'
                                       and wr_is_comment = 1
                                       and wr_name <> '' ", false);
            while ($row = sql_fetch_array($comments)) {
                $key = auto_comment_author_key($row['wr_name']);
                if ($key !== '') {
                    $used[$key] = true;
                }
            }
        }
    }

    return $used;
}

function auto_comment_pick_unused_random_korean_nickname($used_authors)
{
    for ($i = 0; $i < 80; $i++) {
        $nickname = auto_comment_random_korean_nickname();
        if (!auto_comment_author_is_used($used_authors, $nickname)) {
            return $nickname;
        }
    }

    return '세부메모'.mt_rand(1000, 9999);
}

function auto_comment_pick_author_name($bo_table, $wr_id, $exclude_queue_id = 0)
{
    $used_authors = auto_comment_used_authors_for_post($bo_table, $wr_id, $exclude_queue_id);

    if (auto_comment_get_setting('auto_author_mode', 'random_korean') === 'random_korean') {
        return auto_comment_pick_unused_random_korean_nickname($used_authors);
    }

    $author_name = trim(auto_comment_get_setting('ai_author_name', '세부나이트 AI 가이드'));
    if ($author_name !== '' && !auto_comment_author_is_used($used_authors, $author_name) && auto_comment_get_setting('generator_mode', 'template') === 'ai') {
        return $author_name;
    }

    $authors = sql_query(" select aca_name from ".auto_comment_table('author')." where aca_enabled = 1 order by rand() limit 80 ", false);
    while ($author = sql_fetch_array($authors)) {
        $candidate = trim($author['aca_name']);
        if ($candidate !== '' && !auto_comment_author_is_used($used_authors, $candidate)) {
            return $candidate;
        }
    }

    if ($author_name !== '') {
        return $author_name.' '.mt_rand(100, 999);
    }

    return auto_comment_pick_unused_random_korean_nickname($used_authors);
}

function auto_comment_default_templates()
{
    $templates = array();
    $groups = array(
        'default' => array(
            '{keyword} 쪽은 조건이 은근 갈리니까 대충 보고 고르기보단 몇 군데 비교해보는 게 낫겠네요.',
            '이런 건 위치랑 시간대만 먼저 잡아도 일정 짜기가 훨씬 편해져요.',
            '{board} 쪽은 날짜나 인원 따라 느낌이 달라져서 최신 정보 보고 가는 게 좋죠.',
            '{subject} 이 내용은 처음 알아보는 분들한테 꽤 도움 될 듯해요.',
            '현지 쪽은 그때그때 바뀌는 게 있어서 최신 기준으로 한 번 더 보는 게 안전해요.',
            '가기 전에 포함된 거랑 추가 비용만 체크해도 나중에 헷갈릴 일이 줄어요.',
            '동선 짤 때 위치랑 이동시간 같이 보면 일정 꼬일 확률이 확 줄더라고요.',
            '처음이면 시스템부터 가볍게 보고 가는 게 제일 무난해요.',
            '{keyword} 쪽은 선택지가 많아서 목적 맞춰서 보는 게 제일 편합니다.',
            '문의할 때 날짜랑 인원만 정리해도 답변 받기 훨씬 수월해요.',
            '내용 정리 잘 돼 있어서 처음 찾아보는 분들은 참고하기 좋겠네요.',
            '예약 전에 조건만 한 번 더 체크하면 크게 헷갈릴 건 없어 보여요.'
        ),
        'poolvila' => array(
            '풀빌라는 인원수와 객실 구성에 따라 조건이 달라질 수 있어 미리 확인하는 게 좋습니다.',
            '{keyword} 예약 전에는 체크인 시간과 포함 옵션을 같이 확인해보세요.',
            '풀빌라 이용은 위치와 이동 동선도 중요해서 일정과 함께 보는 게 좋습니다.',
            '가족/단체 일정이라면 침실 수와 욕실 수를 먼저 체크하는 편이 좋습니다.',
            '성수기에는 조건이 빨리 바뀔 수 있으니 가능한 날짜를 먼저 확인해보세요.',
            '수영장 이용 시간과 주변 편의시설도 같이 보면 선택하기 편합니다.',
            '풀빌라는 사진 분위기만큼 실제 위치와 관리 상태도 중요합니다.',
            '예약 전 보증금, 취소 규정, 추가 인원 비용을 꼭 같이 확인해보세요.',
            '여러 명이 이용할 때는 공용공간 크기도 만족도에 영향을 많이 줍니다.',
            '막탄/세부시티 위치에 따라 이동시간 차이가 있으니 동선을 먼저 잡아보세요.',
            '풀빌라 일정은 픽업 가능 여부까지 같이 확인하면 편합니다.',
            '숙소 선택 전 주변 식당, 마트, 마사지 접근성도 같이 보는 걸 추천합니다.'
        ),
        'night' => array(
            '나이트투어는 방문 시간대와 동선에 따라 분위기가 많이 달라질 수 있습니다.',
            '{keyword} 쪽은 처음 가는 분들이라면 시스템을 먼저 확인하는 게 좋습니다.',
            '방문 전 위치와 이동 방법을 확인하면 일정이 훨씬 편해집니다.',
            '나이트 일정은 안전한 이동수단과 귀가 동선을 먼저 생각해두는 게 좋습니다.',
            '현지 분위기는 요일과 시간대에 따라 달라질 수 있어 참고하면 좋겠습니다.',
            '처음 방문하는 분들은 단독 이동보다 안내를 받고 움직이는 편이 편합니다.',
            '예산과 원하는 분위기를 먼저 정하면 장소 선택이 쉬워집니다.',
            '나이트투어는 무리한 일정이 되지 않도록 다음날 일정까지 고려하는 게 좋습니다.',
            '방문 전 드레스코드나 입장 조건이 있는지 확인해보세요.',
            '일정이 늦게 끝날 수 있으니 숙소 위치와 이동시간을 같이 체크하세요.',
            '동행 인원에 따라 추천 동선이 달라질 수 있습니다.',
            '현지 상황은 수시로 바뀔 수 있어서 최신 안내를 확인하는 게 좋습니다.'
        ),
        'massage' => array(
            '출장마사지는 가능 지역과 이용 가능 시간을 먼저 확인해보시면 좋습니다.',
            '{keyword} 이용 전에는 코스 시간과 포함 서비스를 같이 확인하세요.',
            '숙소 위치에 따라 도착 시간이 달라질 수 있어 예약 전 주소 확인이 필요합니다.',
            '늦은 시간 이용은 사전 예약 가능 여부를 먼저 보는 게 좋습니다.',
            '마사지 코스는 가격만 보지 말고 관리 시간과 내용을 같이 비교해보세요.',
            '출장 가능 여부는 지역별로 다를 수 있으니 위치 확인이 중요합니다.',
            '이용 전 결제 방식과 추가 비용 여부를 미리 확인하면 편합니다.',
            '여행 일정 중 피로가 쌓였을 때는 이동 없이 이용 가능한 옵션이 편합니다.',
            '처음 이용하는 분들은 후기와 기본 시스템을 같이 보는 게 좋습니다.',
            '예약 시간은 교통 상황에 따라 여유 있게 잡는 걸 추천합니다.',
            '숙소 보안 규정에 따라 출입 방식이 다를 수 있어 미리 확인하세요.',
            '코스 선택 전 원하는 강도와 관리 부위를 미리 정리해두면 좋습니다.'
        ),
        'gallery' => array(
            '사진 분위기가 좋아서 세부 일정 참고용으로 보기 좋네요.',
            '{keyword} 느낌이 잘 보여서 처음 가는 분들도 분위기를 파악하기 좋겠습니다.',
            '현장 분위기를 사진으로 볼 수 있어서 장소 선택에 도움이 되네요.',
            '여행 일정 짤 때 이런 사진 자료가 꽤 유용합니다.',
            '사진을 보니 위치와 분위기를 같이 확인해보고 싶어지네요.',
            '갤러리 자료는 실제 방문 전 기대감을 잡는 데 도움이 됩니다.',
            '분위기가 잘 담겨 있어서 관련 장소 찾는 분들에게 참고가 되겠습니다.',
            '사진 기준으로 보면 세부 여행 코스에 넣어도 괜찮아 보입니다.',
            '현장 느낌이 잘 보여서 처음 방문하는 분들에게 도움이 되겠네요.',
            '이런 사진은 일정 비교할 때 저장해두면 좋겠습니다.',
            '장소 분위기를 미리 확인할 수 있어서 선택이 쉬워질 것 같습니다.',
            '세부 여행 준비 중이라면 참고 이미지로 보기 좋습니다.'
        ),
        'ktv' => array(
            'KTV는 룸 컨디션과 시스템을 미리 확인하면 선택이 훨씬 편합니다.',
            '{keyword} 이용 전에는 인원수와 예상 시간을 먼저 정해두는 게 좋습니다.',
            '장소마다 분위기와 가격대가 다를 수 있어 비교 확인이 필요합니다.',
            '처음 방문하는 분들은 기본 이용 방식부터 확인하는 걸 추천합니다.',
            'KTV 일정은 이동 동선과 귀가 시간을 같이 고려하면 좋습니다.',
            '예약 전 룸 가능 여부와 포함 사항을 확인해보세요.',
            '요일과 시간대에 따라 분위기가 달라질 수 있습니다.',
            '동행 인원에 따라 추천 장소가 달라질 수 있어 미리 상담하면 편합니다.',
            '예산 범위를 정해두면 선택지가 더 명확해집니다.',
            '현지 시스템은 업소별로 다를 수 있어 최신 정보를 확인하세요.',
            '방문 전 위치와 픽업 가능 여부까지 같이 보면 좋습니다.',
            'KTV는 분위기, 가격, 위치를 함께 비교하는 게 중요합니다.'
        ),
        'jtv' => array(
            'JTV는 시스템과 분위기를 미리 알고 가면 이용이 훨씬 편합니다.',
            '{keyword} 관련해서는 방문 시간대와 위치를 같이 확인해보세요.',
            '처음 방문하는 분들은 기본 요금 체계를 먼저 확인하는 게 좋습니다.',
            '장소마다 분위기가 달라서 원하는 스타일에 맞게 고르는 게 중요합니다.',
            'JTV 일정은 이동 동선과 숙소 위치를 같이 고려하면 좋습니다.',
            '방문 전 예약 가능 여부와 좌석 상황을 확인해보세요.',
            '요일별 분위기 차이가 있을 수 있으니 일정에 맞게 체크하면 좋습니다.',
            '동행 인원에 따라 추천 동선이 달라질 수 있습니다.',
            '예산과 원하는 분위기를 먼저 정하면 선택이 쉬워집니다.',
            '현지 상황은 변동이 있을 수 있어 최신 안내를 확인하는 게 좋습니다.',
            '처음이라면 무리한 일정 대신 편한 동선부터 잡는 걸 추천합니다.',
            'JTV는 위치, 가격대, 분위기 정보를 함께 보는 게 좋습니다.'
        ),
        'escot' => array(
            '에코걸 관련 정보는 조건과 가능 일정을 먼저 확인하는 게 좋습니다.',
            '{keyword} 이용 전에는 시간, 지역, 기본 조건을 정확히 체크해보세요.',
            '처음 문의할 때 날짜와 숙소 위치를 같이 전달하면 상담이 빠릅니다.',
            '조건은 상황에 따라 달라질 수 있어 최신 확인이 중요합니다.',
            '이용 전 포함 사항과 추가 비용 여부를 꼭 확인해보세요.',
            '지역별 가능 여부가 다를 수 있으니 위치 확인이 먼저입니다.',
            '일정이 정해져 있다면 가능한 시간대를 미리 확인해두면 좋습니다.',
            '처음 이용하는 분들은 기본 안내를 충분히 보고 결정하는 게 편합니다.',
            '문의 전 원하는 조건을 정리해두면 비교가 쉬워집니다.',
            '현지 사정에 따라 변동이 있을 수 있어 사전 확인을 권장합니다.',
            '가격만 보기보다 조건과 응대 방식도 함께 보는 게 좋습니다.',
            '안전하고 편한 일정이 되도록 이동 동선까지 같이 고려해보세요.'
        ),
        'golf' => array(
            '골프 일정은 티타임과 이동시간을 같이 확인하는 게 중요합니다.',
            '{keyword} 예약 전에는 인원수와 희망 시간대를 먼저 정해두세요.',
            '골프장은 위치에 따라 이동 시간이 크게 달라질 수 있습니다.',
            '장비 대여, 캐디, 카트 포함 여부를 같이 확인하면 좋습니다.',
            '성수기에는 티타임이 빠르게 마감될 수 있어 사전 확인이 필요합니다.',
            '라운딩 후 이동 일정까지 고려하면 하루 계획이 편해집니다.',
            '처음 가는 골프장은 코스 난이도와 컨디션도 같이 보는 게 좋습니다.',
            '비용 비교 시 포함 사항을 꼭 같이 확인하세요.',
            '픽업 가능 여부를 확인하면 이동이 훨씬 수월합니다.',
            '날씨에 따라 일정 변동이 있을 수 있으니 여유 있게 계획하세요.',
            '동행 인원과 실력에 맞는 코스를 선택하는 게 만족도에 좋습니다.',
            '골프 일정은 예약 확정 전 취소 규정도 확인해두는 게 좋습니다.'
        ),
        'map' => array(
            '위치 정보는 실제 이동 동선 잡을 때 가장 먼저 확인하면 좋습니다.',
            '{keyword} 주변 이동 시간과 교통 상황을 같이 보는 게 좋습니다.',
            '세부는 시간대별 교통 차이가 있어서 여유 있게 움직이는 걸 추천합니다.',
            '숙소 기준으로 이동 거리를 확인하면 일정 잡기가 편합니다.',
            '지도 정보는 픽업 가능 여부와 함께 확인하면 좋습니다.',
            '처음 가는 장소라면 주변 랜드마크를 같이 체크해두세요.',
            '이동 동선이 짧을수록 일정 만족도가 높아지는 경우가 많습니다.',
            '여러 장소를 하루에 돌 계획이면 방향을 맞춰서 잡는 게 좋습니다.',
            '위치 확인 후 예약 시간을 정하면 지연을 줄일 수 있습니다.',
            '현지 교통은 변수가 있어 예상보다 여유 있게 계획하세요.',
            '지도 기준으로 가까워 보여도 실제 이동시간은 다를 수 있습니다.',
            '숙소와 목적지 사이 동선을 먼저 확인해두면 좋습니다.'
        )
    );

    foreach ($groups as $group => $items) {
        foreach ($items as $content) {
            $templates[] = array($group, $content);
        }
    }

    return $templates;
}

function auto_comment_seed_defaults($replace)
{
    $author_table = auto_comment_table('author');
    $template_table = auto_comment_table('template');

    $row = sql_fetch(" select count(*) as cnt from {$author_table} ");
    if ($replace || !(int) $row['cnt']) {
        sql_query(" delete from {$author_table} ", false);
        foreach (auto_comment_default_authors() as $idx => $name) {
            sql_query(" insert into {$author_table}
                            set aca_name = '".auto_comment_escape($name)."',
                                aca_enabled = 1,
                                aca_sort = '".((int) $idx + 1)."' ", false);
        }
    }

    $row = sql_fetch(" select count(*) as cnt from {$template_table} ");
    if ($replace || !(int) $row['cnt']) {
        sql_query(" delete from {$template_table} ", false);
        foreach (auto_comment_default_templates() as $idx => $tpl) {
            sql_query(" insert into {$template_table}
                            set act_group = '".auto_comment_escape($tpl[0])."',
                                act_content = '".auto_comment_escape($tpl[1])."',
                                act_enabled = 1,
                                act_sort = '".((int) $idx + 1)."' ", false);
        }
    }
}

function auto_comment_guess_template_group($bo_table)
{
    $bo_table = strtolower($bo_table);
    if (strpos($bo_table, 'pool') !== false || strpos($bo_table, 'villa') !== false) {
        return 'poolvila';
    }
    if (strpos($bo_table, 'night') !== false) {
        return 'night';
    }
    if (strpos($bo_table, 'massage') !== false) {
        return 'massage';
    }
    if (strpos($bo_table, 'gallery') !== false || strpos($bo_table, 'photo') !== false) {
        return 'gallery';
    }
    if (strpos($bo_table, 'notice') !== false || strpos($bo_table, 'faq') !== false || strpos($bo_table, 'qa') !== false) {
        return 'default';
    }

    return 'default';
}

function auto_comment_seed_board_configs($replace)
{
    global $g5;

    if (empty($g5['board_table']) || !auto_comment_table_exists(auto_comment_table('board'))) {
        return;
    }

    auto_comment_ensure_board_columns();
    $board_table = auto_comment_table('board');
    $result = sql_query(" select bo_table from {$g5['board_table']} order by bo_table ", false);
    while ($board = sql_fetch_array($result)) {
        $bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', $board['bo_table']);
        if (!$bo_table) {
            continue;
        }

        if (!$replace) {
            $exists = sql_fetch(" select acb_id from {$board_table} where bo_table = '".auto_comment_escape($bo_table)."' limit 1 ", false);
            if (!empty($exists['acb_id'])) {
                continue;
            }
        }

        $group = auto_comment_guess_template_group($bo_table);
        $enabled = preg_match('/notice|faq|qa/i', $bo_table) ? 0 : 1;
        sql_query(" replace into {$board_table}
                        set bo_table = '".auto_comment_escape($bo_table)."',
                            acb_enabled = '{$enabled}',
                            acb_auto_new_post = '{$enabled}',
                            acb_strategy_scan = '{$enabled}',
                            acb_manual_comment = '{$enabled}',
                            acb_review_mode = 0,
                            acb_tone_profile = 'random',
                            acb_comments_per_post = 1,
                            acb_min_delay = 30,
                            acb_max_delay = 360,
                            acb_template_group = '".auto_comment_escape($group)."',
                            acb_updated_at = '".G5_TIME_YMDHIS."' ", false);
    }
}

function auto_comment_apply_recommended_settings()
{
    global $g5;

    auto_comment_seed_defaults(true);
    auto_comment_set_setting('enabled', '0');
    auto_comment_set_setting('trigger_percent', '3');
    auto_comment_set_setting('trigger_interval', '180');
    auto_comment_set_setting('max_run_items', '2');
    auto_comment_set_setting('max_run_seconds', '2');
    auto_comment_set_setting('daily_limit', '20');
    auto_comment_set_setting('pending_expire_days', '7');
    auto_comment_set_setting('generator_mode', 'ai');
    auto_comment_set_setting('ai_provider', 'icrm');
    auto_comment_set_setting('ai_model', 'gemini-2.0-flash-lite');
    auto_comment_set_setting('icrm_api_base_url', 'https://icrm.co.kr/api/auto-comment');
    auto_comment_set_setting('ai_input_usd_per_million', '0.075');
    auto_comment_set_setting('ai_output_usd_per_million', '0.30');
    auto_comment_set_setting('ai_usd_krw', '1400');
    auto_comment_set_setting('ai_author_name', '세부나이트 AI 가이드');
    auto_comment_set_setting('auto_author_mode', 'random_korean');
    auto_comment_set_setting('auto_author_reuse_percent', '65');
    auto_comment_set_setting('strategy_enabled', '1');
    auto_comment_set_setting('strategy_recent_days', '14');
    auto_comment_set_setting('strategy_scan_limit', '3');
    auto_comment_set_setting('strategy_review_mode', '0');
    auto_comment_set_setting('auto_min_comments', '0');
    auto_comment_set_setting('auto_max_comments', '20');
    auto_comment_set_setting('auto_views_per_comment', '100');
    auto_comment_set_setting('auto_views_per_comment_min', '20');
    auto_comment_set_setting('auto_views_per_comment_max', '50');
    auto_comment_set_setting('skip_bots', '1');

    auto_comment_seed_board_configs(true);

    auto_comment_log('recommended', '추천 설정을 적용했습니다.', 0);
}

function auto_comment_export_config()
{
    auto_comment_ensure_board_columns();
    $data = array(
        'version' => AUTO_COMMENT_VERSION,
        'exported_at' => G5_TIME_YMDHIS,
        'settings' => array(),
        'boards' => array(),
        'authors' => array(),
        'templates' => array()
    );

    $result = sql_query(" select ac_key, ac_value from ".auto_comment_table('setting')." order by ac_key ");
    while ($row = sql_fetch_array($result)) {
        $data['settings'][$row['ac_key']] = $row['ac_value'];
    }

    $result = sql_query(" select * from ".auto_comment_table('board')." order by bo_table ");
    while ($row = sql_fetch_array($result)) {
        $data['boards'][] = array(
            'bo_table' => $row['bo_table'],
            'enabled' => (int) $row['acb_enabled'],
            'auto_new_post' => isset($row['acb_auto_new_post']) ? (int) $row['acb_auto_new_post'] : 1,
            'strategy_scan' => isset($row['acb_strategy_scan']) ? (int) $row['acb_strategy_scan'] : 1,
            'manual_comment' => isset($row['acb_manual_comment']) ? (int) $row['acb_manual_comment'] : 1,
            'review_mode' => isset($row['acb_review_mode']) ? (int) $row['acb_review_mode'] : 0,
            'tone_profile' => isset($row['acb_tone_profile']) ? $row['acb_tone_profile'] : 'random',
            'comments_per_post' => (int) $row['acb_comments_per_post'],
            'min_delay' => (int) $row['acb_min_delay'],
            'max_delay' => (int) $row['acb_max_delay'],
            'template_group' => $row['acb_template_group'],
            'interval_schedule' => isset($row['acb_midnight_schedule']) ? (int) $row['acb_midnight_schedule'] : 0,
            'interval_minutes' => isset($row['acb_interval_minutes']) ? (int) $row['acb_interval_minutes'] : 0
        );
    }

    $result = sql_query(" select aca_name from ".auto_comment_table('author')." where aca_enabled = 1 order by aca_sort asc, aca_id asc ");
    while ($row = sql_fetch_array($result)) {
        $data['authors'][] = $row['aca_name'];
    }

    $result = sql_query(" select act_group, act_content from ".auto_comment_table('template')." where act_enabled = 1 order by act_group asc, act_sort asc, act_id asc ");
    while ($row = sql_fetch_array($result)) {
        $data['templates'][] = array(
            'group' => $row['act_group'],
            'content' => $row['act_content']
        );
    }

    return $data;
}

function auto_comment_json_encode($data)
{
    $flags = defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT : 0;
    return json_encode($data, $flags);
}

function auto_comment_import_config($data)
{
    if (!is_array($data)) {
        throw new Exception('JSON 형식이 올바르지 않습니다.');
    }

    if (isset($data['settings']) && is_array($data['settings'])) {
        $allowed = array('enabled', 'trigger_percent', 'trigger_interval', 'max_run_items', 'max_run_seconds', 'daily_limit', 'pending_expire_days', 'forbidden_words', 'generator_mode', 'icrm_api_base_url', 'icrm_license_key', 'ai_author_name', 'auto_author_mode', 'auto_author_reuse_percent', 'strategy_enabled', 'strategy_recent_days', 'strategy_scan_limit', 'strategy_review_mode', 'auto_min_comments', 'auto_max_comments', 'auto_views_per_comment', 'auto_views_per_comment_min', 'auto_views_per_comment_max', 'skip_bots');
        foreach ($allowed as $key) {
            if (array_key_exists($key, $data['settings'])) {
                auto_comment_set_setting($key, (string) $data['settings'][$key]);
            }
        }
    }

    if (isset($data['authors']) && is_array($data['authors'])) {
        $table = auto_comment_table('author');
        sql_query(" delete from {$table} ", false);
        $sort = 1;
        foreach ($data['authors'] as $name) {
            $name = trim(strip_tags($name));
            if ($name === '') continue;
            sql_query(" insert into {$table}
                            set aca_name = '".auto_comment_escape($name)."',
                                aca_enabled = 1,
                                aca_sort = '{$sort}' ", false);
            $sort++;
        }
    }

    if (isset($data['templates']) && is_array($data['templates'])) {
        $table = auto_comment_table('template');
        sql_query(" delete from {$table} ", false);
        $sort = 1;
        foreach ($data['templates'] as $tpl) {
            if (is_array($tpl)) {
                $group = isset($tpl['group']) ? trim($tpl['group']) : 'default';
                $content = isset($tpl['content']) ? trim($tpl['content']) : '';
            } else {
                $group = 'default';
                $content = trim($tpl);
            }
            if ($content === '') continue;
            sql_query(" insert into {$table}
                            set act_group = '".auto_comment_escape($group ? $group : 'default')."',
                                act_content = '".auto_comment_escape($content)."',
                                act_enabled = 1,
                                act_sort = '{$sort}' ", false);
            $sort++;
        }
    }

    if (isset($data['boards']) && is_array($data['boards'])) {
        auto_comment_ensure_board_columns();
        $table = auto_comment_table('board');
        foreach ($data['boards'] as $board) {
            if (!is_array($board) || empty($board['bo_table'])) continue;
            $bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', $board['bo_table']);
            if (!$bo_table) continue;
            $enabled = !empty($board['enabled']) ? 1 : 0;
            $auto_new_post = array_key_exists('auto_new_post', $board) ? (!empty($board['auto_new_post']) ? 1 : 0) : 1;
            $strategy_scan = array_key_exists('strategy_scan', $board) ? (!empty($board['strategy_scan']) ? 1 : 0) : 1;
            $manual_comment = array_key_exists('manual_comment', $board) ? (!empty($board['manual_comment']) ? 1 : 0) : 1;
            $review_mode = array_key_exists('review_mode', $board) ? (!empty($board['review_mode']) ? 1 : 0) : 0;
            $interval_schedule = array_key_exists('interval_schedule', $board) ? (!empty($board['interval_schedule']) ? 1 : 0) : (array_key_exists('midnight_schedule', $board) ? (!empty($board['midnight_schedule']) ? 1 : 0) : 0);
            $interval_minutes = isset($board['interval_minutes']) ? (int) $board['interval_minutes'] : 0;
            $tone_profile = isset($board['tone_profile']) ? auto_comment_sanitize_tone_profile($board['tone_profile']) : 'random';
            $comments = max(1, min(5, (int) $board['comments_per_post']));
            if ($interval_schedule) {
                $auto_new_post = 0;
                $strategy_scan = 0;
                $interval_minutes = auto_comment_normalize_interval_minutes($interval_minutes);
            } else {
                $interval_minutes = 0;
            }
            $min_delay = max(1, (int) $board['min_delay']);
            $max_delay = max($min_delay, (int) $board['max_delay']);
            $group = isset($board['template_group']) ? trim($board['template_group']) : auto_comment_guess_template_group($bo_table);
            sql_query(" replace into {$table}
                            set bo_table = '".auto_comment_escape($bo_table)."',
                                acb_enabled = '{$enabled}',
                                acb_auto_new_post = '{$auto_new_post}',
                                acb_strategy_scan = '{$strategy_scan}',
                                acb_manual_comment = '{$manual_comment}',
                                acb_review_mode = '{$review_mode}',
                                acb_midnight_schedule = '{$interval_schedule}',
                                acb_interval_minutes = '{$interval_minutes}',
                                acb_tone_profile = '".auto_comment_escape($tone_profile)."',
                                acb_comments_per_post = '{$comments}',
                                acb_min_delay = '{$min_delay}',
                                acb_max_delay = '{$max_delay}',
                                acb_template_group = '".auto_comment_escape($group)."',
                                acb_updated_at = '".G5_TIME_YMDHIS."' ", false);
        }
    }

    auto_comment_log('import', '설정 JSON을 가져왔습니다.', 0);
}

function auto_comment_get_board_config($bo_table)
{
    if (!auto_comment_is_installed()) {
        return null;
    }

    auto_comment_ensure_board_columns();
    $bo_table = auto_comment_escape($bo_table);
    $row = sql_fetch(" select * from ".auto_comment_table('board')." where bo_table = '{$bo_table}' ");
    if (!$row) {
        return null;
    }

    return $row;
}

function auto_comment_normalize_interval_minutes($minutes)
{
    return max(1, min(10080, (int) $minutes));
}

function auto_comment_board_interval_minutes($cfg)
{
    if (!is_array($cfg)) {
        return 0;
    }

    $minutes = isset($cfg['acb_interval_minutes']) ? (int) $cfg['acb_interval_minutes'] : 0;
    if ($minutes > 0) {
        return auto_comment_normalize_interval_minutes($minutes);
    }

    return 60;
}

function auto_comment_board_uses_interval_schedule($cfg)
{
    return is_array($cfg)
        && (int) $cfg['acb_enabled'] === 1
        && isset($cfg['acb_midnight_schedule'])
        && (int) $cfg['acb_midnight_schedule'] === 1
        && auto_comment_board_interval_minutes($cfg) > 0;
}

function auto_comment_board_uses_views_trigger($cfg)
{
    if (!is_array($cfg) || (int) $cfg['acb_enabled'] !== 1) {
        return false;
    }

    return !auto_comment_board_uses_interval_schedule($cfg);
}

function auto_comment_interval_target_comments_for_board($cfg)
{
    return max(1, min(20, (int) (isset($cfg['acb_comments_per_post']) ? $cfg['acb_comments_per_post'] : 1)));
}

function auto_comment_format_interval_minutes($minutes)
{
    $minutes = max(1, (int) $minutes);
    $hours = (int) floor($minutes / 60);
    $remain = $minutes % 60;
    if ($hours > 0 && $remain > 0) {
        return $hours.'시간 '.$remain.'분';
    }
    if ($hours > 0) {
        return $hours.'시간';
    }

    return $remain.'분';
}

function auto_comment_post_last_comment_time($bo_table, $wr_id, $write)
{
    $bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', $bo_table);
    $wr_id = (int) $wr_id;
    if (!$bo_table || $wr_id < 1) {
        return '';
    }

    $row = sql_fetch(" select max(
                            greatest(
                                if(acq_inserted_at > '0000-00-00 00:00:00', acq_inserted_at, acq_scheduled_at),
                                acq_scheduled_at
                            )
                        ) as last_at
                        from ".auto_comment_table('queue')."
                       where bo_table = '".auto_comment_escape($bo_table)."'
                         and wr_id = '{$wr_id}'
                         and acq_status in ('review', 'pending', 'inserted') ", false);
    if (!empty($row['last_at']) && $row['last_at'] !== '0000-00-00 00:00:00') {
        return $row['last_at'];
    }

    return isset($write['wr_datetime']) ? $write['wr_datetime'] : '';
}

function auto_comment_post_has_pending_interval_queue($bo_table, $wr_id)
{
    $row = sql_fetch(" select count(*) as cnt
                        from ".auto_comment_table('queue')."
                       where bo_table = '".auto_comment_escape($bo_table)."'
                         and wr_id = '".(int) $wr_id."'
                         and acq_status in ('review', 'pending')
                         and acq_scheduled_at > '".G5_TIME_YMDHIS."' ");

    return isset($row['cnt']) && (int) $row['cnt'] > 0;
}

function auto_comment_interval_next_scheduled_at($cfg, $write, $bo_table, $wr_id)
{
    $interval_minutes = auto_comment_board_interval_minutes($cfg);
    $last_at = auto_comment_post_last_comment_time($bo_table, $wr_id, $write);
    $base_ts = $last_at ? strtotime($last_at) : G5_SERVER_TIME;
    if (!$base_ts) {
        $base_ts = G5_SERVER_TIME;
    }

    $next_ts = $base_ts + ($interval_minutes * 60);
    if ($next_ts <= G5_SERVER_TIME) {
        $next_ts = G5_SERVER_TIME + mt_rand(60, 180);
    }

    return date('Y-m-d H:i:s', $next_ts);
}

function auto_comment_random_row($table, $where)
{
    $row = sql_fetch(" select count(*) as cnt from {$table} where {$where} ");
    $count = isset($row['cnt']) ? (int) $row['cnt'] : 0;
    if ($count < 1) {
        return null;
    }

    $offset = mt_rand(0, $count - 1);
    return sql_fetch(" select * from {$table} where {$where} order by rand() limit {$offset}, 1 ");
}

function auto_comment_extract_keyword($subject, $content)
{
    $text = trim($subject.' '.strip_tags($content));
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    $text = preg_replace('/[^\pL\pN\s]+/u', ' ', $text);
    $parts = preg_split('/\s+/u', $text);
    $keywords = array();

    foreach ($parts as $part) {
        $part = trim($part);
        if ($part === '') {
            continue;
        }
        if (function_exists('mb_strlen')) {
            if (mb_strlen($part, 'UTF-8') < 2) continue;
        } else if (strlen($part) < 4) {
            continue;
        }
        if (preg_match('/^(그리고|하지만|합니다|있습니다|관련|내용|세부)$/u', $part)) {
            continue;
        }
        $keywords[] = $part;
        if (count($keywords) >= 2) {
            break;
        }
    }

    return $keywords ? implode(' ', $keywords) : '게시글';
}

function auto_comment_render_template($template, $board, $write)
{
    global $config;

    $subject = isset($write['wr_subject']) ? get_text($write['wr_subject']) : '';
    $content = isset($write['wr_content']) ? $write['wr_content'] : '';
    $keyword = auto_comment_extract_keyword($subject, $content);
    $board_name = isset($board['bo_subject']) ? get_text($board['bo_subject']) : '';
    $site = isset($config['cf_title']) ? get_text($config['cf_title']) : '';

    return strtr($template, array(
        '{subject}' => $subject,
        '{keyword}' => $keyword,
        '{board}' => $board_name,
        '{site}' => $site
    ));
}

function auto_comment_tone_options()
{
    return array(
        'random' => '랜덤',
        'friendly' => '친구형',
        'consult' => '상담형',
        'casual' => '반말형',
        'empathy' => '공감형',
        'check' => '체크포인트형',
        'question' => '질문유도형',
        'short' => '짧은리액션형'
    );
}

function auto_comment_sanitize_tone_profile($tone)
{
    $tone = preg_replace('/[^a-z_]/', '', (string) $tone);
    $options = auto_comment_tone_options();

    return isset($options[$tone]) ? $tone : 'random';
}

function auto_comment_pick_tone($tone)
{
    $tone = auto_comment_sanitize_tone_profile($tone);
    $options = auto_comment_tone_options();
    if ($tone !== '' && $tone !== 'random' && isset($options[$tone])) {
        return $tone;
    }

    $keys = array_keys($options);
    $keys = array_values(array_diff($keys, array('random')));
    return $keys[array_rand($keys)];
}

function auto_comment_tone_label($tone)
{
    $options = auto_comment_tone_options();
    return isset($options[$tone]) ? $options[$tone] : $options['random'];
}

function auto_comment_tone_prompt($tone)
{
    $tone = auto_comment_pick_tone($tone);
    $map = array(
        'friendly' => '친구에게 알려주듯 가볍고 자연스럽게. 존댓말은 유지하되 너무 공손하지 않게.',
        'consult' => '상담하듯 핵심 체크 포인트를 짚어주는 말투. 단정하지 말고 부드럽게.',
        'casual' => '커뮤니티 반말 느낌. 짧고 편하게 쓰되 무례하거나 과격하지 않게.',
        'empathy' => '글 내용에 공감하는 반응 중심. 과한 칭찬이나 광고 느낌은 피하기.',
        'check' => '실제로 확인할 만한 체크 포인트를 1개만 짚는 말투.',
        'question' => '자연스럽게 대화를 이어갈 수 있는 질문이나 궁금증을 섞는 말투.',
        'short' => '20~45자 정도의 짧은 리액션. 한 문장으로 간단하게.'
    );

    return isset($map[$tone]) ? $map[$tone] : $map['friendly'];
}

function auto_comment_tone_template($tone, $board, $write)
{
    $tone = auto_comment_pick_tone($tone);
    $templates = array(
        'friendly' => array(
            '{keyword} 쪽은 처음 보면 좀 헷갈릴 수 있는데 이 글은 감 잡기 괜찮네요.',
            '이런 내용은 일정 짤 때 은근 도움 돼요. 위치랑 시간만 같이 보면 좋을 듯해요.',
            '{subject} 내용은 처음 보는 분들도 대충 흐름 잡기 괜찮겠네요.'
        ),
        'consult' => array(
            '이 경우엔 날짜, 위치, 포함 조건만 먼저 체크하면 선택이 훨씬 쉬워집니다.',
            '{keyword} 관련해서는 이동시간이랑 가능 시간대를 같이 보는 게 좋습니다.',
            '처음이면 조건을 한 번에 보기보다 필요한 부분부터 좁혀서 보는 게 편해요.'
        ),
        'casual' => array(
            '이건 처음 보면 좀 헷갈리긴 한데 정리된 거 보니까 보기 편하네요.',
            '{keyword} 쪽은 대충 고르면 애매할 수 있어서 체크는 꼭 해야겠네요.',
            '위치랑 시간대만 봐도 선택이 좀 쉬워질 듯.'
        ),
        'empathy' => array(
            '이런 정보 찾는 분들 꽤 많을 텐데 정리돼 있으니 참고하기 좋겠네요.',
            '처음 알아보는 입장에선 이런 기본 정보가 은근 필요하죠.',
            '내용 보니까 고민하는 분들이 어떤 걸 먼저 봐야 할지 감 잡기 좋겠어요.'
        ),
        'check' => array(
            '예약 전에는 포함 사항이랑 추가 비용 여부만 먼저 체크해도 헷갈림이 줄어요.',
            '{keyword} 쪽은 위치, 시간대, 이동 동선 이 세 가지만 먼저 보면 좋습니다.',
            '본문 기준으로 보면 조건 확인이 제일 먼저고, 그다음 동선 보면 될 듯해요.'
        ),
        'question' => array(
            '이 내용이면 시간대별 차이도 같이 보면 선택하기 더 쉬울 것 같아요.',
            '{keyword} 쪽은 인원이나 날짜에 따라 차이가 클 수도 있겠네요?',
            '처음 가는 분들은 위치 기준으로 보는 게 더 편하지 않을까 싶어요.'
        ),
        'short' => array(
            '이건 처음 보는 분들도 보기 편하네요.',
            '동선 잡을 때 참고하기 좋을 듯해요.',
            '조건 체크할 때 도움 되겠네요.'
        )
    );

    $list = isset($templates[$tone]) ? $templates[$tone] : $templates['friendly'];
    return auto_comment_render_template($list[array_rand($list)], $board, $write);
}

function auto_comment_add_emotion_marker($content)
{
    $content = trim(strip_tags((string) $content));
    if ($content === '' || preg_match('/(\^\^|ㅠㅠ|ㅜㅜ|ㅎㅎ|ㅋㅋ|~~|[~]{1})/u', $content)) {
        return $content;
    }

    // Keep the reaction subtle so comments feel human without becoming noisy.
    if (mt_rand(1, 100) > 70) {
        return $content;
    }

    if (preg_match('/(헷갈|어렵|아쉽|고민|처음|걱정|비싸|애매)/u', $content)) {
        $markers = array('ㅠㅠ', 'ㅜㅜ', '^^');
    } else if (strpos($content, '?') !== false) {
        $markers = array('ㅎㅎ', '^^', '~');
    } else {
        $markers = array('^^', 'ㅎㅎ', '~', '^^');
    }
    $marker = $markers[array_rand($markers)];
    $content = rtrim($content);

    if ($marker === '~') {
        $content = rtrim($content, '.!?');
        $content .= '~';
    } else {
        $content .= ' '.$marker;
    }

    if (function_exists('mb_substr')) {
        return mb_substr($content, 0, 180, 'UTF-8');
    }

    return substr($content, 0, 360);
}

function auto_comment_flow_tone($bo_table, $wr_id, $offset)
{
    $flow = array('question', 'empathy', 'check', 'short', 'friendly', 'consult', 'casual');
    $existing = auto_comment_existing_count_for_post($bo_table, $wr_id);
    $idx = max(0, ((int) $existing + (int) $offset) % count($flow));

    return $flow[$idx];
}

function auto_comment_board_tone_profile($cfg)
{
    if (is_array($cfg) && isset($cfg['acb_tone_profile'])) {
        return auto_comment_sanitize_tone_profile($cfg['acb_tone_profile']);
    }

    return 'random';
}

function auto_comment_tone_for_board($cfg, $bo_table, $wr_id, $offset)
{
    $profile = auto_comment_board_tone_profile($cfg);
    if ($profile !== 'random') {
        return $profile;
    }

    return auto_comment_flow_tone($bo_table, $wr_id, $offset);
}

function auto_comment_quality_score($content)
{
    $plain = trim(strip_tags($content));
    $score = 100;
    $reasons = array();
    $length = function_exists('mb_strlen') ? mb_strlen($plain, 'UTF-8') : strlen($plain);

    if ($length < 18) {
        $score -= 25;
        $reasons[] = '너무 짧음';
    } else if ($length > 120) {
        $score -= 15;
        $reasons[] = '다소 김';
    }

    if (preg_match('/(확인하면 좋습니다|참고하면 좋습니다|좋습니다)/u', $plain)) {
        $score -= 12;
        $reasons[] = '반복 안내문 말투';
    }
    if (preg_match('/(최고|무조건|100%|보장|강추|대박)/u', $plain)) {
        $score -= 18;
        $reasons[] = '과장 표현';
    }
    if (preg_match('/(예약|문의|상담)/u', $plain)) {
        $score -= 8;
        $reasons[] = '유도 표현';
    }
    if (preg_match('/(다녀왔|이용했|제 경험|제가 갔)/u', $plain)) {
        $score -= 30;
        $reasons[] = '직접 경험처럼 보임';
    }

    $score = max(0, min(100, $score));
    if (!$reasons) {
        $reasons[] = '양호';
    }

    return array('score' => $score, 'reasons' => $reasons);
}

function auto_comment_quality_meta($content)
{
    $quality = auto_comment_quality_score($content);
    return '품질점수: '.$quality['score'].'점 ('.implode(', ', $quality['reasons']).')';
}

function auto_comment_reanalysis($content, $duplicate_count = 1)
{
    $plain = trim(strip_tags(html_entity_decode($content, ENT_QUOTES, 'UTF-8')));
    $plain = preg_replace('/\s+/u', ' ', $plain);
    $quality = auto_comment_quality_score($plain);
    $score = (int) $quality['score'];
    $reasons = $quality['reasons'];
    $mechanical = array();
    $repeated = array();

    $mechanical_patterns = array(
        '확인하면 좋습니다',
        '참고하면 좋습니다',
        '도움이 될 것 같습니다',
        '체크해보시면 좋',
        '선택하시면 좋',
        '이용해보시면 좋',
        '추천드립니다',
        '만족하실 수',
        '좋은 선택이 될',
        '정보 감사합니다'
    );
    foreach ($mechanical_patterns as $pattern) {
        if (strpos($plain, $pattern) !== false) {
            $mechanical[] = $pattern;
        }
    }

    $endings = array('좋습니다', '같습니다', '듯합니다', '추천합니다', '체크해보세요', '참고하세요');
    foreach ($endings as $ending) {
        $count = substr_count($plain, $ending);
        if ($count >= 2) {
            $repeated[] = $ending.' '.$count.'회';
        }
    }

    if (preg_match_all('/([\p{L}\p{N}]{2,})/u', $plain, $matches)) {
        $word_counts = array();
        foreach ($matches[1] as $word) {
            if (function_exists('mb_strlen') && mb_strlen($word, 'UTF-8') < 2) {
                continue;
            }
            $word_counts[$word] = isset($word_counts[$word]) ? $word_counts[$word] + 1 : 1;
        }
        foreach ($word_counts as $word => $count) {
            if ($count >= 3) {
                $repeated[] = $word.' '.$count.'회';
            }
        }
    }

    if ($duplicate_count > 1) {
        $repeated[] = '유사 댓글 '.$duplicate_count.'개';
        $score -= 20;
    }
    if ($mechanical) {
        $score -= min(30, count($mechanical) * 10);
        $reasons[] = '기계적 표현 의심';
    }
    if ($repeated) {
        $score -= min(25, count($repeated) * 8);
        $reasons[] = '반복 문구 의심';
    }

    $score = max(0, min(100, $score));
    $level = 'good';
    if ($score < 65) {
        $level = 'danger';
    } else if ($score < 80 || $mechanical || $repeated) {
        $level = 'warning';
    }

    $reasons = array_values(array_unique($reasons));
    if (!$reasons) {
        $reasons[] = '양호';
    }

    return array(
        'score' => $score,
        'level' => $level,
        'reasons' => $reasons,
        'mechanical' => array_values(array_unique($mechanical)),
        'repeated' => array_values(array_unique($repeated))
    );
}

function auto_comment_reanalysis_label($level)
{
    if ($level === 'danger') {
        return '수정권장';
    }
    if ($level === 'warning') {
        return '주의';
    }

    return '양호';
}

function auto_comment_excerpt_for_ai($content, $limit)
{
    $text = trim(strip_tags(html_entity_decode($content, ENT_QUOTES, 'UTF-8')));
    $text = preg_replace('/\s+/u', ' ', $text);
    if (function_exists('mb_substr')) {
        return mb_substr($text, 0, $limit, 'UTF-8');
    }

    return substr($text, 0, $limit);
}

function auto_comment_http_response_is_html($response)
{
    if (!is_string($response) || $response === '') {
        return false;
    }

    $snippet = strtolower(ltrim(substr($response, 0, 512)));

    return strpos($snippet, '<html') !== false
        || strpos($snippet, '<!doctype html') !== false
        || strpos($snippet, '<body') !== false;
}

function auto_comment_http_post_json($url, $payload, $timeout)
{
    $body = json_encode($payload);
    if ($body === false) {
        throw new Exception('AI 요청 JSON 생성에 실패했습니다.');
    }

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json'
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, min(5, $timeout));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $redirect_url = (string) curl_getinfo($ch, CURLINFO_REDIRECT_URL);
        curl_close($ch);

        if ($errno) {
            throw new Exception('AI API 연결 실패: '.$error);
        }
        if ($status >= 300 && $status < 400) {
            $hint = ' icrm API가 로그인/리다이렉트로 응답했습니다.';
            if ($redirect_url !== '') {
                $hint .= ' 이동 URL: '.$redirect_url;
            }
            $hint .= ' API 주소('.auto_comment_icrm_api_url('generate').')와 icrm.co.kr 서버의 auto-comment API 배포 상태를 확인하세요.';
            throw new Exception('AI API 응답 오류: HTTP '.$status.$hint);
        }
        if ($status < 200 || $status >= 300) {
            throw new Exception('AI API 응답 오류: HTTP '.$status);
        }
        if (auto_comment_http_response_is_html($response)) {
            throw new Exception('AI API가 HTML 페이지를 반환했습니다. icrm API 엔드포인트가 로그인 화면으로 연결된 것 같습니다. icrm.co.kr에 auto-comment API 배포 및 URL 라우팅을 확인하세요.');
        }

        return $response;
    }

    $context = stream_context_create(array(
        'http' => array(
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
            'content' => $body,
            'timeout' => $timeout,
            'ignore_errors' => true
        ),
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false
        )
    ));
    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        throw new Exception('AI API 연결에 실패했습니다.');
    }
    if (auto_comment_http_response_is_html($response)) {
        throw new Exception('AI API가 HTML 페이지를 반환했습니다. icrm API 주소와 서버 배포 상태를 확인하세요.');
    }

    return $response;
}

function auto_comment_test_icrm_api()
{
    $license_key = trim(auto_comment_get_setting('icrm_license_key', ''));
    if ($license_key === '') {
        return array(
            'ok' => false,
            'message' => 'icrm 라이선스 키가 저장되어 있지 않습니다. 기본설정에서 키를 저장한 뒤 다시 테스트하세요.'
        );
    }

    $url = auto_comment_icrm_api_url('generate');
    $payload = array(
        'license_key' => $license_key,
        'domain' => auto_comment_site_domain(),
        'bo_table' => 'healthcheck',
        'wr_id' => 0,
        'board_name' => '연결테스트',
        'subject' => 'icrm API 연결 테스트',
        'content' => '자동댓글 플러그인에서 icrm API 연결 상태를 확인하는 테스트 요청입니다.',
        'previous_comments' => '',
        'tone' => 'random',
        'emotion_style' => '',
        'max_length' => 40
    );

    try {
        $response = auto_comment_http_post_json($url, $payload, 15);
    } catch (Exception $e) {
        return array('ok' => false, 'message' => $e->getMessage());
    }

    $data = json_decode($response, true);
    if (!is_array($data)) {
        return array(
            'ok' => false,
            'message' => 'icrm API가 JSON이 아닌 응답을 반환했습니다. URL: '.$url
        );
    }
    if (empty($data['success'])) {
        $message = isset($data['message']) && $data['message'] !== '' ? $data['message'] : 'icrm API가 success=false를 반환했습니다.';
        return array('ok' => false, 'message' => $message);
    }

    $comment = isset($data['comment']) ? trim($data['comment']) : '';
    return array(
        'ok' => true,
        'message' => 'icrm API 연결 성공. 모델: '.(isset($data['model']) ? $data['model'] : 'icrm-central')
            .($comment !== '' ? ' / 샘플: '.$comment : '')
    );
}

function auto_comment_ai_metric($action, $message)
{
    auto_comment_log($action, $message, 0);
}

function auto_comment_ai_unit_price($key, $default)
{
    $value = trim(auto_comment_get_setting($key, $default));
    return max(0, (float) $value);
}

function auto_comment_ai_cost($prompt_tokens, $output_tokens)
{
    $input_usd = auto_comment_ai_unit_price('ai_input_usd_per_million', '0.075');
    $output_usd = auto_comment_ai_unit_price('ai_output_usd_per_million', '0.30');
    $usd_krw = auto_comment_ai_unit_price('ai_usd_krw', '1400');
    $cost_usd = (($prompt_tokens * $input_usd) + ($output_tokens * $output_usd)) / 1000000;

    return array($cost_usd, $cost_usd * $usd_krw);
}

function auto_comment_effective_generator_mode()
{
    if (auto_comment_get_setting('generator_mode', 'ai') === 'template') {
        return 'template';
    }

    if (trim(auto_comment_get_setting('icrm_api_base_url', '')) !== '' && trim(auto_comment_get_setting('icrm_license_key', '')) !== '') {
        return 'ai';
    }

    return 'template';
}

function auto_comment_record_ai_usage($board, $write, $model, $status, $prompt_tokens, $output_tokens, $total_tokens, $error, $cost_usd_override = null, $cost_krw_override = null)
{
    auto_comment_ensure_ai_usage_table();

    $bo_table = isset($board['bo_table']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $board['bo_table']) : '';
    $wr_id = isset($write['wr_id']) ? (int) $write['wr_id'] : 0;
    $prompt_tokens = max(0, (int) $prompt_tokens);
    $output_tokens = max(0, (int) $output_tokens);
    $total_tokens = max($prompt_tokens + $output_tokens, (int) $total_tokens);
    list($cost_usd, $cost_krw) = auto_comment_ai_cost($prompt_tokens, $output_tokens);
    if ($cost_usd_override !== null) {
        $cost_usd = max(0, (float) $cost_usd_override);
    }
    if ($cost_krw_override !== null) {
        $cost_krw = max(0, (float) $cost_krw_override);
    }

    sql_query(" insert into ".auto_comment_table('ai_usage')."
                    set bo_table = '".auto_comment_escape($bo_table)."',
                        wr_id = '{$wr_id}',
                        acu_model = '".auto_comment_escape($model)."',
                        acu_status = '".auto_comment_escape($status)."',
                        acu_prompt_tokens = '{$prompt_tokens}',
                        acu_output_tokens = '{$output_tokens}',
                        acu_total_tokens = '{$total_tokens}',
                        acu_cost_usd = '".auto_comment_escape(sprintf('%.8f', $cost_usd))."',
                        acu_cost_krw = '".auto_comment_escape(sprintf('%.4f', $cost_krw))."',
                        acu_error = '".auto_comment_escape($error)."',
                        acu_created_at = '".G5_TIME_YMDHIS."' ", false);
}

function auto_comment_previous_comments_context($board, $write, $limit = 8)
{
    global $g5;

    $bo_table = isset($board['bo_table']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $board['bo_table']) : '';
    $wr_id = isset($write['wr_id']) ? (int) $write['wr_id'] : 0;
    $limit = max(1, min(12, (int) $limit));
    if (!$bo_table || $wr_id < 1 || empty($g5['write_prefix'])) {
        return '아직 참고할 이전 댓글이 없습니다.';
    }

    $write_table = $g5['write_prefix'].$bo_table;
    if (!auto_comment_table_exists($write_table)) {
        return '아직 참고할 이전 댓글이 없습니다.';
    }

    $items = array();
    $comments = sql_query(" select wr_name, wr_content, wr_datetime
                              from {$write_table}
                             where wr_parent = '{$wr_id}'
                               and wr_is_comment = 1
                               and wr_content <> ''
                             order by wr_comment asc, wr_id asc
                             limit {$limit} ", false);
    while ($row = sql_fetch_array($comments)) {
        $name = trim(strip_tags($row['wr_name']));
        $content = auto_comment_excerpt_for_ai($row['wr_content'], 160);
        if ($content !== '') {
            $items[] = ($name !== '' ? $name : '익명').' : '.$content;
        }
    }

    $queued = sql_query(" select acq_author, acq_content
                            from ".auto_comment_table('queue')."
                           where bo_table = '".auto_comment_escape($bo_table)."'
                             and wr_id = '{$wr_id}'
                             and acq_status in ('review', 'pending', 'inserted')
                             and acq_content <> ''
                           order by acq_id asc
                           limit {$limit} ", false);
    while ($row = sql_fetch_array($queued)) {
        $name = trim(strip_tags($row['acq_author']));
        $content = auto_comment_excerpt_for_ai($row['acq_content'], 160);
        if ($content !== '') {
            $items[] = '[자동예약] '.($name !== '' ? $name : '익명').' : '.$content;
        }
    }

    if (!$items) {
        return '아직 참고할 이전 댓글이 없습니다.';
    }

    return implode("\n", array_slice($items, -$limit));
}

function auto_comment_site_domain()
{
    if (defined('G5_URL') && G5_URL) {
        $host = parse_url(G5_URL, PHP_URL_HOST);
        if ($host) {
            return strtolower($host);
        }
    }

    return isset($_SERVER['HTTP_HOST']) ? strtolower(preg_replace('/[^a-zA-Z0-9\.\-:]/', '', $_SERVER['HTTP_HOST'])) : '';
}

function auto_comment_icrm_api_url($endpoint)
{
    $base_url = trim(auto_comment_get_setting('icrm_api_base_url', 'https://icrm.co.kr/api/auto-comment'));
    $base_url = rtrim($base_url, '/');
    if ($base_url === '' || !preg_match('#^https?://#i', $base_url)) {
        throw new Exception('icrm API 주소가 올바르지 않습니다.');
    }

    return $base_url.'/'.ltrim($endpoint, '/');
}

function auto_comment_clean_ai_comment($text)
{
    $text = trim(strip_tags((string) $text));
    $text = preg_replace('/\s+/u', ' ', $text);
    $text = trim($text, "\"' ");
    if (function_exists('mb_substr')) {
        return mb_substr($text, 0, 180, 'UTF-8');
    }

    return substr($text, 0, 360);
}

function auto_comment_generate_icrm_content($board, $write, $tone = 'random')
{
    $license_key = trim(auto_comment_get_setting('icrm_license_key', ''));
    if ($license_key === '') {
        auto_comment_record_ai_usage($board, $write, 'icrm-central', 'failed', 0, 0, 0, 'icrm 라이선스 키가 설정되지 않았습니다.');
        throw new Exception('icrm 라이선스 키가 설정되지 않았습니다.');
    }

    $subject = isset($write['wr_subject']) ? get_text($write['wr_subject']) : '';
    $board_name = isset($board['bo_subject']) ? get_text($board['bo_subject']) : '';
    $content = auto_comment_excerpt_for_ai(isset($write['wr_content']) ? $write['wr_content'] : '', 1400);
    $previous_comments = auto_comment_previous_comments_context($board, $write, 8);
    $picked_tone = auto_comment_pick_tone($tone);

    $payload = array(
        'license_key' => $license_key,
        'domain' => auto_comment_site_domain(),
        'bo_table' => isset($board['bo_table']) ? $board['bo_table'] : '',
        'wr_id' => isset($write['wr_id']) ? (int) $write['wr_id'] : 0,
        'board_name' => $board_name,
        'subject' => $subject,
        'content' => $content,
        'previous_comments' => $previous_comments,
        'tone' => $picked_tone,
        'emotion_style' => '가끔 ^^, ㅎㅎ, ㅠㅠ, ㅜㅜ, ~ 같은 텍스트 감정표현을 자연스럽게 1개만 사용',
        'max_length' => 85
    );

    auto_comment_ai_metric('ai_request', 'icrm 중앙 API 요청');
    try {
        $response = auto_comment_http_post_json(auto_comment_icrm_api_url('generate'), $payload, 12);
    } catch (Exception $e) {
        auto_comment_record_ai_usage($board, $write, 'icrm-central', 'failed', 0, 0, 0, $e->getMessage());
        throw $e;
    }

    $data = json_decode($response, true);
    if (!is_array($data)) {
        auto_comment_record_ai_usage($board, $write, 'icrm-central', 'failed', 0, 0, 0, 'icrm API 응답 JSON을 읽을 수 없습니다.');
        throw new Exception('icrm API 응답 JSON을 읽을 수 없습니다.');
    }
    if (empty($data['success'])) {
        $message = isset($data['message']) && $data['message'] !== '' ? $data['message'] : 'icrm API 댓글 생성에 실패했습니다.';
        auto_comment_record_ai_usage($board, $write, isset($data['model']) ? $data['model'] : 'icrm-central', 'failed', 0, 0, 0, $message);
        throw new Exception($message);
    }

    $comment = trim(strip_tags(isset($data['comment']) ? $data['comment'] : ''));
    if ($comment === '') {
        auto_comment_record_ai_usage($board, $write, isset($data['model']) ? $data['model'] : 'icrm-central', 'failed', 0, 0, 0, 'icrm API가 빈 댓글을 반환했습니다.');
        throw new Exception('icrm API가 빈 댓글을 반환했습니다.');
    }

    $prompt_tokens = isset($data['prompt_tokens']) ? (int) $data['prompt_tokens'] : 0;
    $output_tokens = isset($data['output_tokens']) ? (int) $data['output_tokens'] : 0;
    $total_tokens = isset($data['total_tokens']) ? (int) $data['total_tokens'] : ($prompt_tokens + $output_tokens);
    $cost_krw = isset($data['cost_krw']) ? (float) $data['cost_krw'] : null;
    $cost_usd = isset($data['cost_usd']) ? (float) $data['cost_usd'] : null;
    auto_comment_record_ai_usage($board, $write, isset($data['model']) ? $data['model'] : 'icrm-central', 'success', $prompt_tokens, $output_tokens, $total_tokens, '', $cost_usd, $cost_krw);

    return auto_comment_clean_ai_comment($comment);
}

function auto_comment_generate_gemini_content($board, $write, $tone = 'random')
{
    $model = trim(auto_comment_get_setting('ai_model', 'gemini-2.0-flash-lite'));
    if ($model === '') {
        $model = 'gemini-2.0-flash-lite';
    }
    $api_key = trim(auto_comment_get_setting('ai_api_key', ''));
    if ($api_key === '') {
        auto_comment_ai_metric('ai_failed', 'Gemini API Key가 설정되지 않았습니다.');
        auto_comment_record_ai_usage($board, $write, $model, 'failed', 0, 0, 0, 'Gemini API Key가 설정되지 않았습니다.');
        throw new Exception('Gemini API Key가 설정되지 않았습니다.');
    }

    $subject = isset($write['wr_subject']) ? get_text($write['wr_subject']) : '';
    $board_name = isset($board['bo_subject']) ? get_text($board['bo_subject']) : '';
    $content = auto_comment_excerpt_for_ai(isset($write['wr_content']) ? $write['wr_content'] : '', 1400);
    $previous_comments = auto_comment_previous_comments_context($board, $write, 8);
    $keyword = auto_comment_extract_keyword($subject, $content);
    $picked_tone = auto_comment_pick_tone($tone);
    $tone_label = auto_comment_tone_label($picked_tone);
    $tone_prompt = auto_comment_tone_prompt($picked_tone);

    $prompt = "다음 게시글을 읽고 커뮤니티에 자연스럽게 달릴 만한 짧은 한국어 댓글을 1개 작성하세요.\n"
        ."조건:\n"
        ."- 실제 일반 이용자, 방문자, 구매자인 척하지 말 것\n"
        ."- 직접 경험한 것처럼 보이는 표현 금지: 다녀왔어요, 이용했어요, 제 경험상 등\n"
        ."- 광고 문구, 과장 표현, 가격 단정, 그림 이모지, 해시태그 금지\n"
        ."- ^^, ㅎㅎ, ㅠㅠ, ㅜㅜ, ~ 같은 텍스트 감정표현은 어울릴 때 1개만 자연스럽게 사용 가능\n"
        ."- 딱딱한 안내문 말투 금지: 확인하면 좋습니다, 참고하면 좋습니다 같은 표현 반복 금지\n"
        ."- 너무 공손한 사무체보다 실제 사람이 댓글 쓰듯 편한 반말 섞인 존댓말 사용\n"
        ."- 줄임말/요즘 말투를 자연스럽게 1개 정도 사용 가능: 괜찮네요, 은근, 무난, 체크, 살짝, 듯해요 등\n"
        ."- 게시글 맥락에 맞게 35~85자\n"
        ."- 이전 댓글들과 같은 말, 같은 관점, 같은 질문을 반복하지 말 것\n"
        ."- 댓글 흐름상 바로 다음 사람이 덧붙이는 느낌으로 작성\n"
        ."- 질문이면 가볍게 답을 보태고, 정보글이면 공감이나 한 줄 의견처럼 작성\n"
        ."- 예약/문의 유도보다 자연스러운 리액션이나 체크 포인트 중심\n"
        ."- 성인/밤문화 게시판이어도 노골적 표현 없이 안전하고 중립적으로 작성\n"
        ."- 예시 톤: 이건 처음 보는 분들도 감 잡기 괜찮겠네요 / 위치랑 시간대만 봐도 선택이 좀 쉬워질 듯해요\n"
        ."- 이번 댓글 톤앤매너: {$tone_label} - {$tone_prompt}\n"
        ."- 출력은 댓글 문장만\n\n"
        ."게시판: {$board_name}\n"
        ."제목: {$subject}\n"
        ."키워드: {$keyword}\n"
        ."본문: {$content}\n\n"
        ."이전 댓글/예약 댓글:\n{$previous_comments}";

    $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode($model).':generateContent?key='.rawurlencode($api_key);
    $payload = array(
        'contents' => array(
            array(
                'role' => 'user',
                'parts' => array(
                    array('text' => $prompt)
                )
            )
        ),
        'generationConfig' => array(
            'temperature' => 0.85,
            'topP' => 0.95,
            'maxOutputTokens' => 120
        )
    );

    auto_comment_ai_metric('ai_request', 'Gemini 요청: '.$model);
    try {
        $response = auto_comment_http_post_json($endpoint, $payload, 12);
    } catch (Exception $e) {
        auto_comment_ai_metric('ai_failed', $e->getMessage());
        auto_comment_record_ai_usage($board, $write, $model, 'failed', 0, 0, 0, $e->getMessage());
        throw $e;
    }

    $data = json_decode($response, true);
    if (!is_array($data)) {
        auto_comment_ai_metric('ai_failed', 'AI API 응답 JSON을 읽을 수 없습니다.');
        auto_comment_record_ai_usage($board, $write, $model, 'failed', 0, 0, 0, 'AI API 응답 JSON을 읽을 수 없습니다.');
        throw new Exception('AI API 응답 JSON을 읽을 수 없습니다.');
    }

    $usage = isset($data['usageMetadata']) && is_array($data['usageMetadata']) ? $data['usageMetadata'] : array();
    $prompt_tokens = isset($usage['promptTokenCount']) ? (int) $usage['promptTokenCount'] : 0;
    $output_tokens = isset($usage['candidatesTokenCount']) ? (int) $usage['candidatesTokenCount'] : 0;
    $total_tokens = isset($usage['totalTokenCount']) ? (int) $usage['totalTokenCount'] : ($prompt_tokens + $output_tokens);

    $text = '';
    if (isset($data['candidates'][0]['content']['parts']) && is_array($data['candidates'][0]['content']['parts'])) {
        foreach ($data['candidates'][0]['content']['parts'] as $part) {
            if (isset($part['text'])) {
                $text .= $part['text'];
            }
        }
    }

    $text = trim(strip_tags($text));
    $text = preg_replace('/\s+/u', ' ', $text);
    $text = trim($text, "\"' ");
    if ($text === '') {
        auto_comment_ai_metric('ai_failed', 'AI가 빈 댓글을 반환했습니다.');
        auto_comment_record_ai_usage($board, $write, $model, 'failed', $prompt_tokens, $output_tokens, $total_tokens, 'AI가 빈 댓글을 반환했습니다.');
        throw new Exception('AI가 빈 댓글을 반환했습니다.');
    }

    if (function_exists('mb_substr')) {
        $text = mb_substr($text, 0, 180, 'UTF-8');
    } else {
        $text = substr($text, 0, 360);
    }

    auto_comment_ai_metric('ai_success', 'Gemini 댓글 생성 성공');
    auto_comment_record_ai_usage($board, $write, $model, 'success', $prompt_tokens, $output_tokens, $total_tokens, '');
    return $text;
}

function auto_comment_generate_content($board, $write, $template_group, $tone = 'random')
{
    $mode = auto_comment_effective_generator_mode();
    $picked_tone = auto_comment_pick_tone($tone);
    if ($mode === 'ai') {
        try {
            return auto_comment_add_emotion_marker(auto_comment_generate_icrm_content($board, $write, $picked_tone));
        } catch (Exception $e) {
            auto_comment_log('generator_fallback', 'AI 생성 실패 후 템플릿 사용: '.$e->getMessage(), 0);
        }
    }

    if ($tone !== '' || mt_rand(1, 100) <= 80) {
        return auto_comment_add_emotion_marker(auto_comment_tone_template($picked_tone, $board, $write));
    }

    $template = auto_comment_random_row(auto_comment_table('template'), " act_enabled = 1 and act_group = '".auto_comment_escape($template_group)."' ");
    if (!$template) {
        $template = auto_comment_random_row(auto_comment_table('template'), " act_enabled = 1 and act_group = 'default' ");
    }
    if (!$template) {
        throw new Exception('사용 가능한 댓글 템플릿이 없습니다.');
    }

    return auto_comment_add_emotion_marker(auto_comment_render_template($template['act_content'], $board, $write));
}

function auto_comment_forbidden_words()
{
    $raw = auto_comment_get_setting('forbidden_words', '');
    $parts = preg_split('/[\r\n,]+/', $raw);
    $words = array();
    foreach ($parts as $part) {
        $part = trim($part);
        if ($part !== '') {
            $words[] = $part;
        }
    }

    return $words;
}

function auto_comment_validate_content($bo_table, $wr_id, $content, $exclude_queue_id)
{
    $content_plain = trim(strip_tags($content));
    if ($content_plain === '') {
        throw new Exception('댓글내용이 비어 있습니다.');
    }

    foreach (auto_comment_forbidden_words() as $word) {
        if ($word !== '' && function_exists('mb_stripos')) {
            if (mb_stripos($content_plain, $word, 0, 'UTF-8') !== false) {
                throw new Exception('금칙어가 포함되어 있습니다: '.$word);
            }
        } else if ($word !== '' && stripos($content_plain, $word) !== false) {
            throw new Exception('금칙어가 포함되어 있습니다: '.$word);
        }
    }

    $where_exclude = $exclude_queue_id ? " and acq_id <> '".(int) $exclude_queue_id."' " : '';
    $dup = sql_fetch(" select count(*) as cnt from ".auto_comment_table('queue')."
                        where bo_table = '".auto_comment_escape($bo_table)."'
                          and wr_id = '".(int) $wr_id."'
                          and acq_content = '".auto_comment_escape($content_plain)."'
                          and acq_status in ('review', 'pending', 'inserted')
                          {$where_exclude} ");
    if ((int) $dup['cnt'] > 0) {
        throw new Exception('같은 원글에 동일한 댓글이 이미 예약 또는 등록되어 있습니다.');
    }

    $content_key = auto_comment_similarity_key($content_plain);
    if ($content_key !== '') {
        $similar = sql_query(" select acq_content
                                 from ".auto_comment_table('queue')."
                                where bo_table = '".auto_comment_escape($bo_table)."'
                                  and wr_id = '".(int) $wr_id."'
                                  and acq_status in ('review', 'pending', 'inserted')
                                  {$where_exclude}
                                order by acq_id desc
                                limit 30 ", false);
        while ($row = sql_fetch_array($similar)) {
            $other_key = auto_comment_similarity_key($row['acq_content']);
            if ($other_key === '') {
                continue;
            }
            similar_text($content_key, $other_key, $percent);
            if ($percent >= 72) {
                throw new Exception('비슷한 댓글이 이미 있어 재생성이 필요합니다.');
            }
        }
    }

    return $content_plain;
}

function auto_comment_similarity_key($content)
{
    $text = trim(strip_tags($content));
    $text = preg_replace('/https?:\/\/\S+/i', '', $text);
    $text = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text);
    $text = preg_replace('/\s+/u', '', $text);

    if (function_exists('mb_strtolower')) {
        $text = mb_strtolower($text, 'UTF-8');
    } else {
        $text = strtolower($text);
    }

    if (function_exists('mb_substr')) {
        return mb_substr($text, 0, 120, 'UTF-8');
    }

    return substr($text, 0, 240);
}

function auto_comment_generate_preview($bo_table, $wr_id, $template_group, $tone = 'random')
{
    global $g5;

    $bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', $bo_table);
    $wr_id = (int) $wr_id;
    if (!$bo_table || $wr_id < 1 || empty($g5['write_prefix'])) {
        throw new Exception('게시판 또는 원글 번호가 올바르지 않습니다.');
    }

    $board = get_board_db($bo_table, true);
    if (!$board || empty($board['bo_table'])) {
        throw new Exception('게시판을 찾을 수 없습니다.');
    }

    $write_table = $g5['write_prefix'].$bo_table;
    $write = sql_fetch(" select * from {$write_table} where wr_id = '{$wr_id}' and wr_is_comment = 0 ");
    if (empty($write['wr_id'])) {
        throw new Exception('원글을 찾을 수 없습니다.');
    }

    $template_group = trim($template_group);
    if ($template_group === '') {
        $cfg = auto_comment_get_board_config($bo_table);
        $template_group = ($cfg && $cfg['acb_template_group']) ? $cfg['acb_template_group'] : 'default';
    }

    $author_name = auto_comment_pick_author_name($bo_table, $wr_id);
    $cfg = isset($cfg) && is_array($cfg) ? $cfg : auto_comment_get_board_config($bo_table);
    $picked_tone = auto_comment_pick_tone($tone === 'random' ? auto_comment_board_tone_profile($cfg) : $tone);

    return array(
        'bo_table' => $bo_table,
        'wr_id' => $wr_id,
        'subject' => $write['wr_subject'],
        'author' => $author_name,
        'content' => auto_comment_generate_content($board, $write, $template_group, $picked_tone),
        'scheduled_at' => date('Y-m-d H:i:s', G5_SERVER_TIME + 600),
        'template_group' => $template_group,
        'tone' => $picked_tone
    );
}

function auto_comment_min_comments()
{
    return max(0, min(20, (int) auto_comment_get_setting('auto_min_comments', '0')));
}

function auto_comment_max_comments()
{
    return max(auto_comment_min_comments(), min(20, (int) auto_comment_get_setting('auto_max_comments', '20')));
}

function auto_comment_views_per_comment_range()
{
    $min = (int) auto_comment_get_setting('auto_views_per_comment_min', '20');
    $max = (int) auto_comment_get_setting('auto_views_per_comment_max', '50');

    if ($min < 1 && $max < 1) {
        $legacy = max(1, min(10000, (int) auto_comment_get_setting('auto_views_per_comment', '100')));
        return array($legacy, $legacy);
    }

    $min = max(1, min(10000, $min));
    $max = max(1, min(10000, $max));
    if ($max < $min) {
        $tmp = $min;
        $min = $max;
        $max = $tmp;
    }

    return array($min, $max);
}

function auto_comment_views_per_comment()
{
    list($min, $max) = auto_comment_views_per_comment_range();
    return $min === $max ? $min : mt_rand($min, $max);
}

function auto_comment_views_per_comment_for_write($write)
{
    list($min, $max) = auto_comment_views_per_comment_range();
    if ($min === $max) {
        return $min;
    }

    $seed = implode('|', array(
        isset($write['wr_id']) ? $write['wr_id'] : '',
        isset($write['wr_datetime']) ? $write['wr_datetime'] : '',
        isset($write['wr_subject']) ? $write['wr_subject'] : ''
    ));
    $hash = (int) sprintf('%u', crc32($seed));

    return $min + ($hash % (($max - $min) + 1));
}

function auto_comment_target_comments_for_write($write)
{
    $min = auto_comment_min_comments();
    $max = auto_comment_max_comments();
    $views_per_comment = auto_comment_views_per_comment_for_write($write);
    $hit = isset($write['wr_hit']) ? max(0, (int) $write['wr_hit']) : 0;
    $target = (int) floor($hit / $views_per_comment);

    return max($min, min($max, $target));
}

function auto_comment_existing_count_for_post($bo_table, $wr_id)
{
    $row = sql_fetch(" select count(*) as cnt from ".auto_comment_table('queue')."
                        where bo_table = '".auto_comment_escape($bo_table)."'
                          and wr_id = '".(int) $wr_id."'
                          and acq_status in ('review', 'pending', 'inserted') ");

    return (int) $row['cnt'];
}

function auto_comment_inserted_count_for_post($bo_table, $wr_id)
{
    $row = sql_fetch(" select count(*) as cnt from ".auto_comment_table('queue')."
                        where bo_table = '".auto_comment_escape($bo_table)."'
                          and wr_id = '".(int) $wr_id."'
                          and acq_status = 'inserted' ");

    return (int) $row['cnt'];
}

function auto_comment_schedule_for_post($board, $wr_id, $w)
{
    global $g5;

    if ($w !== '' || !auto_comment_is_installed()) {
        return;
    }
    if (auto_comment_get_setting('enabled', '0') !== '1') {
        return;
    }
    if (empty($board['bo_table']) || empty($g5['write_prefix'])) {
        return;
    }

    $bo_table = $board['bo_table'];
    $cfg = auto_comment_get_board_config($bo_table);
    if (!$cfg || (int) $cfg['acb_enabled'] !== 1) {
        return;
    }
    if (auto_comment_board_uses_interval_schedule($cfg)) {
        auto_comment_interval_schedule_new_post($board, $wr_id);
        return;
    }
    if (isset($cfg['acb_auto_new_post']) && (int) $cfg['acb_auto_new_post'] !== 1) {
        return;
    }

    $write_table = $g5['write_prefix'].$bo_table;
    $write = sql_fetch(" select * from {$write_table} where wr_id = '".(int) $wr_id."' and wr_is_comment = 0 ");
    if (empty($write['wr_id'])) {
        return;
    }

    $existing_count = auto_comment_existing_count_for_post($bo_table, (int) $wr_id);
    $comments = auto_comment_target_comments_for_write($write) - $existing_count;
    if ($comments <= 0) {
        return;
    }
    $comments = min(auto_comment_max_comments(), $comments);

    $min_delay = max(1, (int) $cfg['acb_min_delay']);
    $max_delay = max($min_delay, (int) $cfg['acb_max_delay']);
    $group = $cfg['acb_template_group'] ? $cfg['acb_template_group'] : 'default';

    for ($i = 0; $i < $comments; $i++) {
        try {
            $author_name = auto_comment_pick_author_name($bo_table, (int) $wr_id);
        } catch (Exception $e) {
            auto_comment_log('schedule_failed', $e->getMessage(), 0);
            return;
        }

        $delay = mt_rand($min_delay, $max_delay) + ($i * mt_rand(5, 30));
        $scheduled_at = date('Y-m-d H:i:s', G5_SERVER_TIME + ($delay * 60));
        try {
            $content = auto_comment_generate_content($board, $write, $group, auto_comment_tone_for_board($cfg, $bo_table, (int) $wr_id, $i));
        } catch (Exception $e) {
            auto_comment_log('schedule_failed', $e->getMessage(), 0);
            return;
        }
        try {
            $content = auto_comment_validate_content($bo_table, $wr_id, $content, 0);
        } catch (Exception $e) {
            auto_comment_log('schedule_skip', $e->getMessage(), 0);
            continue;
        }

        $quality = auto_comment_quality_score($content);
        $review_mode = isset($cfg['acb_review_mode']) && (int) $cfg['acb_review_mode'] === 1;
        $status = ($review_mode || $quality['score'] < 65) ? 'review' : 'pending';
        $meta = '자동 예약: 조회수 '.(int) $write['wr_hit'].' 기준 목표 '.auto_comment_target_comments_for_write($write).'개 중 '.($existing_count + $i + 1).'번째 / '.auto_comment_quality_meta($content);
        if ($review_mode) {
            $meta .= ' / 게시판 검수모드';
        }

        sql_query(" insert into ".auto_comment_table('queue')."
                        set bo_table = '".auto_comment_escape($bo_table)."',
                            wr_id = '".(int) $wr_id."',
                            acq_subject = '".auto_comment_escape($write['wr_subject'])."',
                            acq_author = '".auto_comment_escape($author_name)."',
                            acq_content = '".auto_comment_escape($content)."',
                            acq_scheduled_at = '{$scheduled_at}',
                            acq_status = '{$status}',
                            acq_error = '".auto_comment_escape($meta)."',
                            acq_created_at = '".G5_TIME_YMDHIS."' ", false);
        auto_comment_log('auto_queue', $bo_table.' #'.$wr_id.' 새 글 자동댓글 예약 '.($existing_count + $i + 1).'/'.auto_comment_target_comments_for_write($write), sql_insert_id());
    }

    auto_comment_log('schedule', $bo_table.' #'.$wr_id.' 새 글 자동댓글 '.$comments.'개 예약', 0);
}

function auto_comment_strategy_candidate_exists($bo_table, $wr_id)
{
    return auto_comment_existing_count_for_post($bo_table, $wr_id) > 0;
}

function auto_comment_strategy_reason($write, $target_comments, $existing_count)
{
    $reasons = array();
    $comment_count = isset($write['wr_comment']) ? (int) $write['wr_comment'] : 0;
    $hit = isset($write['wr_hit']) ? (int) $write['wr_hit'] : 0;
    $reasons[] = '조회수 '.$hit.' 기준 목표 '.$target_comments.'개';
    $reasons[] = '현재 자동댓글 '.$existing_count.'개';
    $reasons[] = '현재 실제댓글 '.$comment_count.'개';

    $content = auto_comment_excerpt_for_ai(isset($write['wr_content']) ? $write['wr_content'] : '', 600);
    $length = function_exists('mb_strlen') ? mb_strlen($content, 'UTF-8') : strlen($content);
    if ($length >= 80) {
        $reasons[] = '본문 충분';
    }

    if ($hit >= 100) {
        $reasons[] = '조회수 높음';
    }

    if (isset($write['wr_datetime']) && $write['wr_datetime']) {
        $reasons[] = '최근 글';
    }

    return implode(', ', $reasons);
}

function auto_comment_strategy_post_score($write, $target_comments, $existing_count)
{
    $score = 0;
    $comment_count = isset($write['wr_comment']) ? (int) $write['wr_comment'] : 0;
    if ($existing_count < $target_comments) {
        $score += 40;
    }
    if ($comment_count <= 0) {
        $score += 50;
    } else if ($comment_count < $target_comments) {
        $score += 30;
    }

    $content = auto_comment_excerpt_for_ai(isset($write['wr_content']) ? $write['wr_content'] : '', 600);
    $length = function_exists('mb_strlen') ? mb_strlen($content, 'UTF-8') : strlen($content);
    if ($length >= 80) {
        $score += 20;
    } else if ($length < 20) {
        $score -= 50;
    }

    $hit = isset($write['wr_hit']) ? (int) $write['wr_hit'] : 0;
    if ($hit >= 100) {
        $score += 10;
    }

    return $score;
}

function auto_comment_strategy_schedule_for_write($board, $write, $cfg, $delay_minutes, $reason)
{
    $bo_table = $board['bo_table'];
    $wr_id = (int) $write['wr_id'];
    $group = !empty($cfg['acb_template_group']) ? $cfg['acb_template_group'] : 'default';
    $author_name = auto_comment_pick_author_name($bo_table, $wr_id);

    $content = auto_comment_generate_content($board, $write, $group, auto_comment_tone_for_board($cfg, $bo_table, $wr_id, 0));
    $content = auto_comment_validate_content($bo_table, $wr_id, $content, 0);
    $scheduled_at = date('Y-m-d H:i:s', G5_SERVER_TIME + ($delay_minutes * 60));
    $quality = auto_comment_quality_score($content);
    $review_mode = isset($cfg['acb_review_mode']) && (int) $cfg['acb_review_mode'] === 1;
    $status = ($review_mode || $quality['score'] < 65) ? 'review' : 'pending';
    $reason = trim($reason);
    if ($reason !== '') {
        $reason = '선정 이유: '.$reason;
    }
    $reason = trim($reason.($reason !== '' ? ' / ' : '').auto_comment_quality_meta($content));
    if ($review_mode) {
        $reason = trim($reason.' / 게시판 검수모드');
    }

    sql_query(" insert into ".auto_comment_table('queue')."
                    set bo_table = '".auto_comment_escape($bo_table)."',
                        wr_id = '{$wr_id}',
                        acq_subject = '".auto_comment_escape($write['wr_subject'])."',
                        acq_author = '".auto_comment_escape($author_name)."',
                        acq_content = '".auto_comment_escape($content)."',
                        acq_scheduled_at = '{$scheduled_at}',
                        acq_status = '{$status}',
                        acq_error = '".auto_comment_escape($reason)."',
                        acq_created_at = '".G5_TIME_YMDHIS."' ", false);

    return sql_insert_id();
}

function auto_comment_strategy_scan($limit)
{
    global $g5;

    if (!auto_comment_is_installed() || auto_comment_get_setting('enabled', '0') !== '1') {
        return 0;
    }
    if (auto_comment_get_setting('strategy_enabled', '1') !== '1') {
        return 0;
    }
    if (empty($g5['write_prefix'])) {
        return 0;
    }

    auto_comment_ensure_board_columns();
    $limit = max(1, min(10, (int) ($limit ? $limit : auto_comment_get_setting('strategy_scan_limit', '3'))));
    $recent_days = max(1, min(365, (int) auto_comment_get_setting('strategy_recent_days', '14')));
    $max_comments = auto_comment_max_comments();
    $min_datetime = date('Y-m-d H:i:s', G5_SERVER_TIME - ($recent_days * 86400));
    $created = 0;
    $checked = 0;
    $started_at = time();
    $max_seconds = max(1, min(5, (int) auto_comment_get_setting('max_run_seconds', '2')));

    $result = sql_query(" select *
                            from ".auto_comment_table('board')."
                           where acb_enabled = 1
                             and acb_strategy_scan = 1
                             and acb_midnight_schedule = 0
                           order by bo_table asc ", false);
    while ($cfg = sql_fetch_array($result)) {
        if ($created >= $limit) {
            break;
        }

        $bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', $cfg['bo_table']);
        if (!$bo_table) {
            continue;
        }
        $write_table = $g5['write_prefix'].$bo_table;
        if (!auto_comment_table_exists($write_table)) {
            continue;
        }

        $board = get_board_db($bo_table, true);
        if (!$board || empty($board['bo_table'])) {
            continue;
        }

        $posts = sql_query(" select *
                               from {$write_table}
                              where wr_is_comment = 0
                                and wr_datetime >= '".auto_comment_escape($min_datetime)."'
                                and wr_comment < '{$max_comments}'
                              order by wr_comment asc, wr_hit desc, wr_id desc
                              limit 20 ", false);
        while ($write = sql_fetch_array($posts)) {
            if ((time() - $started_at) >= $max_seconds) {
                break 2;
            }
            if ($created >= $limit) {
                break;
            }
            $checked++;

            $target_comments = auto_comment_target_comments_for_write($write);
            $existing_count = auto_comment_existing_count_for_post($bo_table, (int) $write['wr_id']);
            if ($existing_count >= $target_comments) {
                continue;
            }
            if (auto_comment_strategy_post_score($write, $target_comments, $existing_count) < 20) {
                continue;
            }

            try {
                $delay = mt_rand(5, 45) + ($created * mt_rand(10, 40));
                $reason = auto_comment_strategy_reason($write, $target_comments, $existing_count);
                $queue_id = auto_comment_strategy_schedule_for_write($board, $write, $cfg, $delay, $reason);
                auto_comment_log('strategy_queue', $bo_table.' #'.$write['wr_id'].' 전략 예약 생성: '.$reason, $queue_id);
                $created++;
            } catch (Exception $e) {
                auto_comment_log('strategy_skip', $bo_table.' #'.$write['wr_id'].' '.$e->getMessage(), 0);
            }
        }
    }

    auto_comment_log('strategy_scan', '전략 스캔 완료: 확인 '.$checked.'개, 예약 '.$created.'개', 0);
    return $created;
}

function auto_comment_interval_schedule_for_write($board, $write, $cfg, $scheduled_at, $slot_index)
{
    $bo_table = $board['bo_table'];
    $wr_id = (int) $write['wr_id'];
    $group = !empty($cfg['acb_template_group']) ? $cfg['acb_template_group'] : 'default';

    try {
        $author_name = auto_comment_pick_author_name($bo_table, $wr_id);
        $content = auto_comment_generate_content($board, $write, $group, auto_comment_tone_for_board($cfg, $bo_table, $wr_id, $slot_index));
        $content = auto_comment_validate_content($bo_table, $wr_id, $content, 0);
    } catch (Exception $e) {
        auto_comment_log('interval_skip', $bo_table.' #'.$wr_id.' '.$e->getMessage(), 0);
        return 0;
    }

    $quality = auto_comment_quality_score($content);
    $review_mode = isset($cfg['acb_review_mode']) && (int) $cfg['acb_review_mode'] === 1;
    $status = ($review_mode || $quality['score'] < 65) ? 'review' : 'pending';
    $target = auto_comment_interval_target_comments_for_board($cfg);
    $existing_count = auto_comment_existing_count_for_post($bo_table, $wr_id);
    $interval_label = auto_comment_format_interval_minutes(auto_comment_board_interval_minutes($cfg));
    $meta = '간격 예약: '.$interval_label.'마다 / 글당 '.$target.'개 중 '.($existing_count + 1).'번째 / '.auto_comment_quality_meta($content);
    if ($review_mode) {
        $meta .= ' / 게시판 검수모드';
    }

    sql_query(" insert into ".auto_comment_table('queue')."
                    set bo_table = '".auto_comment_escape($bo_table)."',
                        wr_id = '{$wr_id}',
                        acq_subject = '".auto_comment_escape($write['wr_subject'])."',
                        acq_author = '".auto_comment_escape($author_name)."',
                        acq_content = '".auto_comment_escape($content)."',
                        acq_scheduled_at = '".auto_comment_escape($scheduled_at)."',
                        acq_status = '{$status}',
                        acq_error = '".auto_comment_escape($meta)."',
                        acq_created_at = '".G5_TIME_YMDHIS."' ", false);

    $queue_id = sql_insert_id();
    auto_comment_log('interval_queue', $bo_table.' #'.$wr_id.' 간격 예약 '.($existing_count + 1).'/'.$target.' @ '.$scheduled_at, $queue_id);

    return 1;
}

function auto_comment_interval_schedule_new_post($board, $wr_id)
{
    global $g5;

    if (empty($board['bo_table']) || empty($g5['write_prefix'])) {
        return;
    }

    $bo_table = $board['bo_table'];
    $cfg = auto_comment_get_board_config($bo_table);
    if (!auto_comment_board_uses_interval_schedule($cfg)) {
        return;
    }

    $write_table = $g5['write_prefix'].$bo_table;
    $write = sql_fetch(" select * from {$write_table} where wr_id = '".(int) $wr_id."' and wr_is_comment = 0 ");
    if (empty($write['wr_id'])) {
        return;
    }

    if (auto_comment_existing_count_for_post($bo_table, (int) $wr_id) > 0) {
        return;
    }
    if (auto_comment_post_has_pending_interval_queue($bo_table, (int) $wr_id)) {
        return;
    }

    $scheduled_at = auto_comment_interval_next_scheduled_at($cfg, $write, $bo_table, (int) $wr_id);
    auto_comment_interval_schedule_for_write($board, $write, $cfg, $scheduled_at, 0);
}

function auto_comment_interval_schedule_scan()
{
    global $g5;

    if (!auto_comment_is_installed() || auto_comment_get_setting('enabled', '0') !== '1') {
        return 0;
    }
    if (empty($g5['write_prefix'])) {
        return 0;
    }

    auto_comment_ensure_board_columns();
    $recent_days = max(1, min(365, (int) auto_comment_get_setting('strategy_recent_days', '14')));
    $min_datetime = date('Y-m-d H:i:s', G5_SERVER_TIME - ($recent_days * 86400));
    $created = 0;
    $checked = 0;
    $started_at = time();
    $max_seconds = max(2, min(10, (int) auto_comment_get_setting('max_run_seconds', '2') * 2));

    $result = sql_query(" select *
                            from ".auto_comment_table('board')."
                           where acb_enabled = 1
                             and acb_midnight_schedule = 1
                             and acb_interval_minutes > 0
                           order by bo_table asc ", false);
    while ($cfg = sql_fetch_array($result)) {
        if ((time() - $started_at) >= $max_seconds) {
            break;
        }

        $bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', $cfg['bo_table']);
        if (!$bo_table || !auto_comment_board_uses_interval_schedule($cfg)) {
            continue;
        }

        $write_table = $g5['write_prefix'].$bo_table;
        if (!auto_comment_table_exists($write_table)) {
            continue;
        }

        $board = get_board_db($bo_table, true);
        if (!$board || empty($board['bo_table'])) {
            continue;
        }

        $target = auto_comment_interval_target_comments_for_board($cfg);
        $interval_minutes = auto_comment_board_interval_minutes($cfg);
        $posts = sql_query(" select *
                               from {$write_table}
                              where wr_is_comment = 0
                                and wr_datetime >= '".auto_comment_escape($min_datetime)."'
                              order by wr_id desc
                              limit 30 ", false);
        while ($write = sql_fetch_array($posts)) {
            if ((time() - $started_at) >= $max_seconds) {
                break 2;
            }

            $checked++;
            $wr_id = (int) $write['wr_id'];
            $existing_count = auto_comment_existing_count_for_post($bo_table, $wr_id);
            if ($existing_count >= $target) {
                continue;
            }
            if (auto_comment_post_has_pending_interval_queue($bo_table, $wr_id)) {
                continue;
            }

            $last_at = auto_comment_post_last_comment_time($bo_table, $wr_id, $write);
            $due_ts = strtotime($last_at) + ($interval_minutes * 60);
            if ($due_ts > G5_SERVER_TIME) {
                continue;
            }

            $scheduled_at = auto_comment_interval_next_scheduled_at($cfg, $write, $bo_table, $wr_id);
            $created += auto_comment_interval_schedule_for_write($board, $write, $cfg, $scheduled_at, $existing_count);
        }
    }

    if ($created > 0) {
        auto_comment_log('interval_scan', '간격 예약 스캔 완료: 확인 '.$checked.'개, 예약 '.$created.'개', 0);
    }

    return $created;
}

function auto_comment_system_ip()
{
    if (!empty($_SERVER['SERVER_ADDR'])) {
        return $_SERVER['SERVER_ADDR'];
    }

    return '127.0.0.1';
}

function auto_comment_comment_url($bo_table, $wr_id, $comment_id)
{
    $bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', $bo_table);
    $wr_id = (int) $wr_id;
    $comment_id = (int) $comment_id;

    if (function_exists('get_pretty_url')) {
        return get_pretty_url($bo_table, $wr_id, '#c_'.$comment_id);
    }

    return G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$wr_id.'#c_'.$comment_id;
}

function auto_comment_insert_queue($queue, $enforce_target = false)
{
    global $g5;

    $bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', $queue['bo_table']);
    if (!$bo_table || empty($g5['write_prefix'])) {
        throw new Exception('게시판 정보가 올바르지 않습니다.');
    }

    $board = get_board_db($bo_table, true);
    if (!$board || empty($board['bo_table'])) {
        throw new Exception('게시판을 찾을 수 없습니다.');
    }

    $write_table = $g5['write_prefix'].$bo_table;
    $wr_id = (int) $queue['wr_id'];
    $wr = sql_fetch(" select * from {$write_table} where wr_id = '{$wr_id}' and wr_is_comment = 0 ");
    if (empty($wr['wr_id'])) {
        throw new Exception('원글을 찾을 수 없습니다.');
    }

    if ($enforce_target) {
        $target_comments = auto_comment_target_comments_for_write($wr);
        $inserted_count = auto_comment_inserted_count_for_post($bo_table, $wr_id);
        if ($inserted_count >= $target_comments) {
            throw new Exception('조회수 기준 목표 댓글 수에 도달했습니다. 현재 '.$inserted_count.'개 / 목표 '.$target_comments.'개');
        }
    }

    $row = sql_fetch(" select max(wr_comment) as max_comment from {$write_table} where wr_parent = '{$wr_id}' and wr_is_comment = 1 ");
    $comment_no = (int) $row['max_comment'] + 1;
    auto_comment_validate_content($bo_table, $wr_id, $queue['acq_content'], (int) $queue['acq_id']);
    $content = auto_comment_escape($queue['acq_content']);
    $author_name = trim($queue['acq_author']);
    if ($author_name === '' || auto_comment_author_is_used(auto_comment_used_authors_for_post($bo_table, $wr_id, (int) $queue['acq_id']), $author_name)) {
        $author_name = auto_comment_pick_author_name($bo_table, $wr_id, (int) $queue['acq_id']);
        sql_query(" update ".auto_comment_table('queue')."
                      set acq_author = '".auto_comment_escape($author_name)."'
                    where acq_id = '".(int) $queue['acq_id']."' ", false);
    }
    $author = auto_comment_escape($author_name);
    $comment_mb_id = auto_comment_escape(auto_comment_ensure_bot_member($author_name));
    $ip = auto_comment_escape(auto_comment_system_ip());

    sql_query(" insert into {$write_table}
                    set ca_name = '".auto_comment_escape($wr['ca_name'])."',
                        wr_option = '',
                        wr_num = '{$wr['wr_num']}',
                        wr_reply = '',
                        wr_parent = '{$wr_id}',
                        wr_is_comment = 1,
                        wr_comment = '{$comment_no}',
                        wr_comment_reply = '',
                        wr_subject = '',
                        wr_content = '{$content}',
                        mb_id = '{$comment_mb_id}',
                        wr_password = '',
                        wr_name = '{$author}',
                        wr_email = '',
                        wr_homepage = '',
                        wr_datetime = '".G5_TIME_YMDHIS."',
                        wr_last = '',
                        wr_ip = '{$ip}' ", false);

    $comment_id = sql_insert_id();
    sql_query(" update {$write_table} set wr_comment = wr_comment + 1, wr_last = '".G5_TIME_YMDHIS."' where wr_id = '{$wr_id}' ", false);
    sql_query(" insert into {$g5['board_new_table']} (bo_table, wr_id, wr_parent, bn_datetime, mb_id)
                values ('".auto_comment_escape($bo_table)."', '{$comment_id}', '{$wr_id}', '".G5_TIME_YMDHIS."', '{$comment_mb_id}') ", false);
    sql_query(" update {$g5['board_table']} set bo_count_comment = bo_count_comment + 1 where bo_table = '".auto_comment_escape($bo_table)."' ", false);
    auto_comment_award_bot_point($comment_mb_id, $board, $comment_id, true, $wr_id);

    if (function_exists('delete_cache_latest')) {
        delete_cache_latest($bo_table);
    }

    auto_comment_interval_schedule_after_insert($board, $wr);

    return $comment_id;
}

function auto_comment_interval_schedule_after_insert($board, $write)
{
    if (empty($board['bo_table']) || empty($write['wr_id'])) {
        return;
    }

    $bo_table = $board['bo_table'];
    $wr_id = (int) $write['wr_id'];
    $cfg = auto_comment_get_board_config($bo_table);
    if (!auto_comment_board_uses_interval_schedule($cfg)) {
        return;
    }

    $target = auto_comment_interval_target_comments_for_board($cfg);
    if (auto_comment_existing_count_for_post($bo_table, $wr_id) >= $target) {
        return;
    }
    if (auto_comment_post_has_pending_interval_queue($bo_table, $wr_id)) {
        return;
    }

    $scheduled_at = auto_comment_interval_next_scheduled_at($cfg, $write, $bo_table, $wr_id);
    auto_comment_interval_schedule_for_write($board, $write, $cfg, $scheduled_at, auto_comment_existing_count_for_post($bo_table, $wr_id));
}

function auto_comment_cleanup_old_pending()
{
    $days = max(1, min(90, (int) auto_comment_get_setting('pending_expire_days', '7')));
    $expire_at = date('Y-m-d H:i:s', G5_SERVER_TIME - ($days * 86400));
    $row = sql_fetch(" select count(*) as cnt
                         from ".auto_comment_table('queue')."
                        where acq_status = 'pending'
                          and acq_created_at < '".auto_comment_escape($expire_at)."' ");
    $count = isset($row['cnt']) ? (int) $row['cnt'] : 0;
    if ($count <= 0) {
        return 0;
    }

    sql_query(" update ".auto_comment_table('queue')."
                  set acq_status = 'cancelled',
                      acq_error = '".auto_comment_escape('오래된 대기 예약 자동정리: '.$days.'일 초과')."'
                where acq_status = 'pending'
                  and acq_created_at < '".auto_comment_escape($expire_at)."' ", false);
    auto_comment_log('cleanup_pending', '오래된 pending '.$count.'개 자동정리', 0);

    return $count;
}

function auto_comment_run_worker($limit)
{
    auto_comment_cleanup_old_pending();

    $limit = $limit ? (int) $limit : (int) auto_comment_get_setting('max_run_items', '2');
    $limit = max(1, min(10, $limit));
    $daily_limit = max(1, min(200, (int) auto_comment_get_setting('daily_limit', '20')));
    $today = date('Y-m-d 00:00:00', G5_SERVER_TIME);
    $inserted_today = sql_fetch(" select count(*) as cnt from ".auto_comment_table('queue')."
                                  where acq_status = 'inserted'
                                    and acq_inserted_at >= '{$today}' ");
    $remaining_today = $daily_limit - (int) $inserted_today['cnt'];
    if ($remaining_today <= 0) {
        auto_comment_log('worker_skip', '하루 등록 제한에 도달했습니다.', 0);
        return 0;
    }
    $limit = min($limit, $remaining_today);
    $processed = 0;
    $result = sql_query(" select * from ".auto_comment_table('queue')."
                           where acq_status = 'pending'
                             and acq_scheduled_at <= '".G5_TIME_YMDHIS."'
                           order by acq_scheduled_at asc
                           limit {$limit} ", false);

    while ($row = sql_fetch_array($result)) {
        try {
            $cfg = auto_comment_get_board_config($row['bo_table']);
            $enforce_target = auto_comment_board_uses_views_trigger($cfg);
            $comment_id = auto_comment_insert_queue($row, $enforce_target);
            sql_query(" update ".auto_comment_table('queue')."
                          set acq_status = 'inserted',
                              acq_inserted_at = '".G5_TIME_YMDHIS."',
                              acq_error = '".auto_comment_escape('등록 결과: '.auto_comment_comment_url($row['bo_table'], (int) $row['wr_id'], $comment_id))."'
                        where acq_id = '".(int) $row['acq_id']."' ", false);
            auto_comment_log('insert', '댓글 등록 완료 #'.$comment_id, (int) $row['acq_id']);
            $processed++;
        } catch (Exception $e) {
            if (strpos($e->getMessage(), '조회수 기준 목표 댓글 수에 도달했습니다.') === 0) {
                sql_query(" update ".auto_comment_table('queue')."
                              set acq_status = 'cancelled',
                                  acq_error = '".auto_comment_escape($e->getMessage())."'
                            where acq_id = '".(int) $row['acq_id']."' ", false);
                auto_comment_log('target_skip', $e->getMessage(), (int) $row['acq_id']);
                continue;
            }

            sql_query(" update ".auto_comment_table('queue')."
                          set acq_status = 'failed',
                              acq_error = '".auto_comment_escape($e->getMessage())."'
                        where acq_id = '".(int) $row['acq_id']."' ", false);
            auto_comment_log('failed', $e->getMessage(), (int) $row['acq_id']);
        }
    }

    if ($processed) {
        auto_comment_log('worker', '댓글 '.$processed.'개 처리', 0);
    }

    return $processed;
}

function auto_comment_is_bot()
{
    $agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
    return $agent && preg_match('/bot|crawl|spider|slurp|facebook|kakao|naver|daum|google/i', $agent);
}

function auto_comment_visitor_context()
{
    $ip = isset($_SERVER['REMOTE_ADDR']) ? preg_replace('/[^0-9a-fA-F:\.]/', '', $_SERVER['REMOTE_ADDR']) : '';
    $agent = isset($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : '';
    if ($ip === '') {
        return array('ip' => '', 'key' => '', 'type' => '');
    }

    auto_comment_ensure_visitor_table();
    $visitor_key = hash('sha256', $ip.'|'.$agent);
    $visitor = sql_fetch(" select acv_first_seen_at, acv_last_seen_at, acv_view_count
                             from ".auto_comment_table('visitor')."
                            where acv_key = '".auto_comment_escape($visitor_key)."' ", false);
    $today = date('Y-m-d', G5_SERVER_TIME);
    $visitor_type = 'new';
    if (!empty($visitor['acv_first_seen_at']) && $visitor['acv_first_seen_at'] !== '0000-00-00 00:00:00') {
        $first_day = substr($visitor['acv_first_seen_at'], 0, 10);
        $last_day = !empty($visitor['acv_last_seen_at']) ? substr($visitor['acv_last_seen_at'], 0, 10) : '';
        $visitor_type = ($first_day < $today && $last_day < $today) ? 'returning' : 'existing';
    }

    sql_query(" insert into ".auto_comment_table('visitor')."
                    set acv_key = '".auto_comment_escape($visitor_key)."',
                        acv_ip = '".auto_comment_escape($ip)."',
                        acv_first_seen_at = '".G5_TIME_YMDHIS."',
                        acv_last_seen_at = '".G5_TIME_YMDHIS."',
                        acv_view_count = 1
             on duplicate key update
                        acv_ip = values(acv_ip),
                        acv_last_seen_at = values(acv_last_seen_at),
                        acv_view_count = acv_view_count + 1 ", false);

    return array(
        'ip' => $ip,
        'key' => $visitor_key,
        'type' => $visitor_type
    );
}

function auto_comment_maybe_run_worker()
{
    if (!auto_comment_is_installed() || auto_comment_get_setting('enabled', '0') !== '1') {
        return;
    }
    if (defined('G5_IS_ADMIN') || (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'GET')) {
        return;
    }
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        return;
    }
    if (auto_comment_get_setting('skip_bots', '1') === '1' && auto_comment_is_bot()) {
        return;
    }

    $percent = max(1, min(100, (int) auto_comment_get_setting('trigger_percent', '3')));
    if (mt_rand(1, 100) > $percent) {
        return;
    }

    $cache_dir = G5_DATA_PATH.'/cache';
    $last_file = $cache_dir.'/auto_comment_last_run.php';
    $lock_file = $cache_dir.'/auto_comment_worker.lock';
    $interval = max(30, (int) auto_comment_get_setting('trigger_interval', '180'));

    if (is_file($last_file) && filemtime($last_file) > (G5_SERVER_TIME - $interval)) {
        return;
    }
    if (is_file($lock_file) && filemtime($lock_file) > (G5_SERVER_TIME - 60)) {
        return;
    }

    @file_put_contents($lock_file, (string) G5_SERVER_TIME);
    @chmod($lock_file, G5_FILE_PERMISSION);

    $start = time();
    $max_seconds = max(1, min(5, (int) auto_comment_get_setting('max_run_seconds', '2')));
    try {
        auto_comment_interval_schedule_scan();
        auto_comment_strategy_scan((int) auto_comment_get_setting('strategy_scan_limit', '3'));
        auto_comment_run_worker((int) auto_comment_get_setting('max_run_items', '2'));
    } catch (Exception $e) {
        auto_comment_log('worker_failed', $e->getMessage(), 0);
    }

    @file_put_contents($last_file, '<?php exit; ?> '.G5_TIME_YMDHIS.' '.(time() - $start));
    @chmod($last_file, G5_FILE_PERMISSION);
    @unlink($lock_file);
}

function auto_comment_track_post_view()
{
    global $bo_table, $wr_id, $write;

    if (!auto_comment_is_installed() || defined('G5_IS_ADMIN')) {
        return;
    }
    if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'GET') {
        return;
    }
    if (auto_comment_get_setting('skip_bots', '1') === '1' && auto_comment_is_bot()) {
        return;
    }

    $bo_table = isset($bo_table) ? preg_replace('/[^a-zA-Z0-9_]/', '', $bo_table) : '';
    $wr_id = isset($wr_id) ? (int) $wr_id : 0;
    if (!$bo_table || $wr_id < 1 || empty($write['wr_id']) || !empty($write['wr_is_comment'])) {
        return;
    }

    $cfg = auto_comment_get_board_config($bo_table);
    if (!$cfg || (int) $cfg['acb_enabled'] !== 1) {
        return;
    }

    auto_comment_ensure_post_view_table();
    $subject = isset($write['wr_subject']) ? $write['wr_subject'] : '';
    $visitor = auto_comment_visitor_context();
    sql_query(" insert into ".auto_comment_table('post_view')."
                    set bo_table = '".auto_comment_escape($bo_table)."',
                        wr_id = '{$wr_id}',
                        acv_subject = '".auto_comment_escape($subject)."',
                        acv_view_count = 1,
                        acv_ip = '".auto_comment_escape($visitor['ip'])."',
                        acv_visitor_key = '".auto_comment_escape($visitor['key'])."',
                        acv_visitor_type = '".auto_comment_escape($visitor['type'])."',
                        acv_last_viewed_at = '".G5_TIME_YMDHIS."',
                        acv_created_at = '".G5_TIME_YMDHIS."'
             on duplicate key update
                        acv_subject = values(acv_subject),
                        acv_view_count = acv_view_count + 1,
                        acv_ip = values(acv_ip),
                        acv_visitor_key = values(acv_visitor_key),
                        acv_visitor_type = values(acv_visitor_type),
                        acv_last_viewed_at = values(acv_last_viewed_at) ", false);
}
 
