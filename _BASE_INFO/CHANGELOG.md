# 변경 이력 (CHANGELOG)

형식은 [Keep a Changelog](https://keepachangelog.com/ko/1.0.0/)를 참고합니다.

---

## [v1.0.0] — 2026-05-21

### Added — 초기 구성

#### 그누보드 베이스
- 그누보드5 5.6.x 전체 포함 (코어 `/bbs`, `/lib`, `/adm` 유지)
- 폴더 복사형 제작 베이스 (`onoff-g5-base`)

#### 메인·섹션
- **`index.php`** — 메인 진입, `section/` 배열 기반 include 구조
- **`section/`** — hero, service, advantage, portfolio, latest, review, faq, contact
- **`section/_helpers.php`** — `img/main/` 미디어·플레이스홀더 헬퍼

#### 사이트 설정
- **`/_site.config.php`** — `$site_config`, `g5site_cfg()`, `g5site_cfg_url()`, `g5site_tel_link()`
- **`head.php` / `tail.php`** — 사이트 설정·로고·브랜드 색·컴포넌트·푸터 연동

#### 컴포넌트 (`/components/`)
- `floating-buttons.php` — 하단 고정 전화·카카오·상담·TOP
- `consult-modal.php` — 상담 모달 (`#cmpConsultModal`)
- `quick-contact.php` — 섹션 내 빠른 문의
- `bottom-cta.php` — 하단 전환 CTA
- `kakao-map.php` — 카카오맵 placeholder / 키 연동
- `popup-banner.php` — 이벤트 팝업
- `seo-meta.php` — title·description·canonical·robots·OG·JSON-LD

#### 스타일·스크립트
- **`css/custom.css`** — `:root` 디자인 토큰, `.site-main` / `.page-template` 스코프
- **`css/g5b-board.css`** — 게시판 공통 (`.board-wrap` 스코프)
- **`js/custom.js`** — 모바일 메뉴, 상담 모달, FAQ, 팝업, go-top

#### 게시판 스킨 10종 (PC + mobile)
| 스킨 | 용도 |
|------|------|
| `basic-clean` | 깔끔한 기본 목록 |
| `basic-modern` | 모던 리스트 |
| `basic-card` | 카드형 목록 |
| `basic-notice` | 공지 강조 |
| `post-thumb` | 썸네일 글목록 |
| `post-media` | 미디어 강조 글목록 |
| `gallery-grid` | 갤러리 그리드 |
| `gallery-masonry` | 갤러리 메이슨리 |
| `youtube-list` | 유튜브 리스트 |
| `youtube-gallery` | 유튜브 갤러리 |

- **`skin/board/_inc/`** — 썸네일·유튜브·fallback 공통 PHP
- **`img/common/`** — no-image.svg, no-youtube.svg

#### 서브 페이지 (`/page/`)
- `about.php`, `service.php`, `portfolio.php`, `contact.php`
- `page/_init.php` — `g5_page_start()` / `g5_page_end()`
- **`style-guide.php`** — 디자인 검수용 (관리자 전용, noindex)

#### setup 문서 (`/setup/`)
- `site.sample.json` — 사이트 정보 샘플 JSON
- `replace-checklist.md` — 복사 후 교체 체크리스트·JSON 매핑

#### 빌더·문서
- **`BUILDER-WORKFLOW.md`** — 빌더 적용 워크플로우
- **`SECTION-GUIDE.md`** — 섹션 구조·include·Scroll Snap
- **`BOARD-SKIN-GUIDE.md`** — 게시판 10종 선택·검수
- **`PROMPTS.md`** — Cursor 프롬프트 10종
- **`/presets/`** — 목적별 5종 (업종별 아님)

#### SEO
- **`components/seo-meta.php`** — 페이지 변수 → `_site.config.php` → fallback 우선순위
- **`head.php`** — seo-meta 안전 include, `html_process` 훅으로 메타 주입·title 교체
- **`_site.config.php`** — `seo_title`, `seo_description`, `main_keyword`, `robots` 등

#### 개인정보처리방침
- **`page/privacy.php`** — 샘플 방침 (법무 검토 안내)
- **`tail.php`** 푸터 → `/page/privacy.php` 링크
- `consult-modal.php` — 개인정보 동의 체크박스·샘플 문구

#### 운영·납품 문서
- **`/_BUILDER_INPUT/`** — 빌더 임시 보관 (app, assets, screenshots)
- **`START-PROJECT-PROMPTS.md`** — 새 프로젝트 Cursor 순서
- **`LAUNCH-CHECKLIST.md`** — 납품·오픈 전 체크
- **`CLIENT-MANUAL.md`** — 고객·관리자 운영 안내

#### 기타 문서
- `README-START.md`, `README-BOARD-SKINS.md`, `README-BOARD-CSS.md`
- `README-BUILDER-TO-GNUBOARD.md`, `SAMPLE-CONTENT.md`
- **`_BASE_INFO/`** — VERSION, CHANGELOG, FILE-STRUCTURE, DO-NOT-EDIT, COPY-CHECKLIST

### Notes
- 모바일 레이아웃은 그누보드 기본 `mobile/head.php` 사용 — PC `head.php` SEO·커스텀 GNB와 **완전 동일하지 않을 수 있음**
- `data/dbconfig.php`는 서버별 — 베이스 복사·공유 시 제외 권장

---

## 향후 계획 (참고)

- `mobile/head.php` SEO·GNB 통일 (선택)
- JSON-LD LocalBusiness 확장 (seo-meta)
