<?php
/**
 * ============================================================================
 * [경고] 개발자 참고용 예시 스크립트 — 기본 실행 차단
 * ============================================================================
 *
 * - 이 파일은 예시입니다. 운영 서버에서 바로 실행하지 마세요.
 * - 실행 전 DB 백업이 필요합니다.
 * - 그누보보드 버전별 게시판 생성 필드·권한 구조가 다를 수 있습니다.
 * - 가능하면 관리자 화면(게시판관리)에서 수동 생성하는 것을 권장합니다.
 *
 * 사용 방법:
 * 1. BOARD-CREATE-GUIDE.md 로 관리자 수동 생성 (권장)
 * 2. setup/project.sample.json 의 boards 항목 참고
 * 3. 자동화가 필요할 때만 이 파일을 복사·수정 후 개발 환경에서 검증
 *
 * 파일명: create-default-boards.example.php → 실수 실행 방지용
 * ============================================================================
 */

exit('이 파일은 예시입니다. 실행할 수 없습니다. BOARD-CREATE-GUIDE.md를 참고하세요.');

/*
 * --------------------------------------------------------------------------
 * 아래 코드는 참고용입니다. exit 이후에는 실행되지 않습니다.
 * --------------------------------------------------------------------------
 */

// include_once './_common.php';
// if (!defined('_GNUBOARD_')) exit;

// --- 관리자 권한 확인 예시 (실행 시 필수) ---
// if (!$is_admin || $is_admin !== 'super') {
//     alert('최고관리자만 실행할 수 있습니다.');
// }

// --- 기본 게시판 정의 (setup/project.sample.json 과 동일 구조 권장) ---
$default_boards = array(
    array(
        'bo_table'      => 'notice',
        'bo_subject'    => '공지사항',
        'bo_skin'       => 'basic-notice',
        'bo_mobile_skin'=> 'basic-notice',
        'bo_read_level' => 1,
        'bo_write_level'=> 10,
        'bo_use_category'=> 0,
        'bo_use_secret' => 0,
        'bo_use_comment'=> 0,
    ),
    array(
        'bo_table'      => 'column',
        'bo_subject'    => '칼럼',
        'bo_skin'       => 'post-thumb',
        'bo_mobile_skin'=> 'post-thumb',
        'bo_read_level' => 1,
        'bo_write_level'=> 10,
        'bo_use_category'=> 1,
        'bo_use_secret' => 0,
        'bo_use_comment'=> 0,
    ),
    array(
        'bo_table'      => 'faq',
        'bo_subject'    => '자주 묻는 질문',
        'bo_skin'       => 'faq-accordion',
        'bo_mobile_skin'=> 'faq-accordion',
        'bo_read_level' => 1,
        'bo_write_level'=> 10,
        'bo_use_category'=> 1,
        'bo_use_secret' => 0,
        'bo_use_comment'=> 0,
        // FAQ: 관리자에서 「목록에서 내용 사용」 켜기 권장
    ),
    array(
        'bo_table'      => 'inquiry',
        'bo_subject'    => '상담문의',
        'bo_skin'       => 'landing-inquiry',
        'bo_mobile_skin'=> 'landing-inquiry',
        'bo_read_level' => 10,
        'bo_write_level'=> 1,
        'bo_use_category'=> 0,
        'bo_use_secret' => 1,
        'bo_use_comment'=> 0,
    ),
);

// foreach ($default_boards as $board) {
//     $bo_table = preg_replace('/[^a-z0-9_]/', '', $board['bo_table']);
//     if ($bo_table === '') {
//         continue;
//     }
//
//     // --- 중복 체크 예시 ---
//     $row = sql_fetch(" select count(*) as cnt from {$g5['board_table']} where bo_table = '{$bo_table}' ");
//     if (!empty($row['cnt'])) {
//         echo "SKIP: {$bo_table} (이미 존재)\n";
//         continue;
//     }
//
//     // --- g5_board INSERT 예시 (필드는 그누보드 5.6 기준, 버전별 차이 있음) ---
//     // sql_query(" insert into {$g5['board_table']} set ... ");
//
//     // --- g5_write_{bo_table} 테이블 생성 ---
//     // 그누보드는 게시판 추가 시 write 테이블·권한·기본값을 함께 만듭니다.
//     // 관리자 게시판 추가 로직(bbs/board_update.php 등)과 동일한지 반드시 확인하세요.
//     // 직접 CREATE TABLE 하면 코어와 불일치할 수 있습니다.
//
//     echo "PLAN: {$bo_table} 생성 예정\n";
// }

// echo "\n완료 후 관리자 → 게시판관리에서 스킨·권한·분류를 다시 확인하세요.\n";
