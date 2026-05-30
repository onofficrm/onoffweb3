# 게시판 스킨 CSS 구조 (10종)

## 로드 순서

1. `head.php` → `/css/custom.css` (`:root` 디자인 토큰)
2. 각 스킨 `list|view|write.skin.php` → `add_stylesheet(.../style.css)`
3. 스킨 `style.css` → `@import ../../../css/g5b-board.css` (basic-clean 경유 또는 직접)

**전역에 g5b-board를 넣지 않음** — 그누보드 기본 화면·default.css와 충돌 방지.

## 공통 CSS: `/css/g5b-board.css`

`.board-wrap` 하위만 스타일 적용.

| 영역 | 표준 class | 기존 alias (PHP 수정 없이 CSS 매핑) |
|------|------------|-------------------------------------|
| 래퍼 | `.board-wrap` | — |
| 헤더 | `.board-header` | — |
| 툴바 | `.board-toolbar` | `.board-actions`, `.btn_bo_user` |
| 분류 | `.board-category` | `.board-cate`, `#bo_cate` |
| 검색 | `.board-search` | `.bo_sch_wrap` |
| 목록 테이블 | `.board-table` | `.board-list--table` |
| 카드 | `.board-card` | `.board-list__card` |
| 썸네일 | `.board-thumb` | — |
| 메타 | `.board-meta` | `.board-list__meta`, `.board-list__foot` |
| 요약 | `.board-desc` | `.board-list__excerpt` |
| 페이지네이션 | `.board-pagination` | `.board-paging` |
| 글보기 헤더 | `.board-view-header` | `.board-view__head` |
| 글보기 제목 | `.board-view-title` | `.board-title__text`, `.bo_v_tit` |
| 글보기 메타 | `.board-view-meta` | `.board-view__meta` |
| 글보기 본문 | `.board-view-content` | `.board-view__content`, `#bo_v_con` |
| 글쓰기 | `.board-write-form` | — |
| 폼 행/라벨/입력 | `.board-form-row` … | `.board-write-form__row` … |
| 버튼 | `.board-btn` | `.board-actions .btn` |
| Primary | `.board-btn-primary` | `.board-actions__write`, `.btn_submit` |
| Outline | `.board-btn-outline` | `.btn_cancel` |

## 스킨별 `style.css` 역할

| 스킨 | import | 고유 CSS |
|------|--------|----------|
| basic-clean | `g5b-board.css` | `.board-wrap--basic-clean` |
| basic-modern | `basic-clean` | 여백·날짜/분류 강조 테이블 |
| basic-card | `basic-clean` | 3열 카드 그리드 |
| basic-notice | `basic-clean` | 공지/일반 섹션 분리 |
| post-thumb | `basic-clean` | 96×72 썸네일 목록 |
| post-media | `basic-clean` | 대형 썸네일 목록 |
| gallery-grid | `basic-clean` | 4→3→2→1열 그리드 |
| gallery-masonry | `basic-clean` | CSS columns + 오버레이 |
| youtube-list | `basic-clean` | 유튜브 목록·embed |
| youtube-gallery | `basic-clean` + `youtube-list` | 카드 그리드 |

모바일 `mobile/skin/board/*` → PC 동일 경로 `@import` (중복 제거).

## 모바일

- **basic 계열**: `@media (max-width:767px)` — 테이블 → 카드 (`data-label` + `::before` 라벨)
- **카드/썸네일/갤러리/유튜브**: 스킨별 `@media` — 열 수·flex-direction 조정

## 테스트 체크리스트

- [ ] 10개 스킨 목록 / 글보기 / 글쓰기 / 댓글
- [ ] 검색 모달, 분류, 페이지네이션
- [ ] 글쓰기·목록 버튼 (primary / outline)
- [ ] 공지·비밀글·체크박스·more_opt
- [ ] PC·모바일 (767px 전후)
- [ ] 썸네일·유튜브 fallback SVG
- [ ] 그누보드 **기본 스킨** 게시판 — 레이아웃 깨짐 없음

## 토큰 변경

`/css/custom.css` `:root`만 수정하면 게시판 색·간격·버튼이 함께 반영됩니다.

게시판 시맨틱 토큰(`--board-media-bg`, `--shadow-elevated` 등)과 사이트별 적용 절차는 [README-BOARD-SKINS.md §9](README-BOARD-SKINS.md#9-디자인-색상-변경-방법) 참고.
