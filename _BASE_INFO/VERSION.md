# onoff-g5-base — 버전 정보

| 항목 | 내용 |
|------|------|
| **프로젝트명** | onoff-g5-base |
| **현재 버전** | v1.0.0 |
| **마지막 업데이트** | 2026-05-21 |
| **기반** | 그누보드5 (GNU Board 5) 5.6.x |

---

## 목적

홈페이지·랜딩·기업 사이트를 **폴더 복사만으로** 빠르게 시작할 수 있는 그누보드 제작 베이스입니다.

- 그누보드 코어(`/bbs`, `/lib`, `/adm`)는 유지하고, **레이아웃·메인·서브·게시판 스킨·공통 설정**만 템플릿화합니다.
- 빌더(HTML/CSS) 디자인을 `section/`, `page/`, `css/custom.css`에 붙이기 쉽게 합니다.
- 새 프로젝트마다 `_site.config.php`와 관리자 설정만 바꿔 운영합니다.

---

## 포함 기능 (v1.0.0)

| 영역 | 내용 |
|------|------|
| **메인** | `index.php` + `section/` 8종 include (hero, service, advantage, portfolio, latest, review, faq, contact) |
| **서브 페이지** | `page/about.php`, `service.php`, `portfolio.php`, `contact.php` + `page/_init.php` |
| **사이트 설정** | `/_site.config.php` (회사정보·색상·로고·SEO 키 등) |
| **레이아웃** | 커스텀 `head.php`, `tail.php` (PC) |
| **컴포넌트** | `components/` 7종 (floating, consult-modal, quick-contact, bottom-cta, kakao-map, popup-banner, seo-meta) |
| **스타일** | `css/custom.css`, `css/g5b-board.css` |
| **게시판 스킨** | PC·모바일 각 10종 (`skin/board/`, `mobile/skin/board/`) |
| **SEO** | `components/seo-meta.php` + `head.php` 훅 연동 |
| **문서·셋업** | `README-START.md`, `setup/`, `PROMPTS.md`, `_BASE_INFO/` |
| **검수** | `page/style-guide.php` (관리자 전용, noindex) |
| **개인정보** | `/page/privacy.php` + 푸터 링크 |
| **문서** | BUILDER-WORKFLOW, SECTION-GUIDE, BOARD-SKIN-GUIDE, PROMPTS, presets/ |

> `/presets/` — **목적별** 5종 (landing-conversion, content-seo, company-intro, portfolio-showcase, local-business). 업종별 MD는 포함하지 않습니다.

---

## 기준 환경

| 항목 | 권장 |
|------|------|
| **PHP** | 7.4 이상 (8.0+ 권장) |
| **DB** | MySQL 5.7+ / MariaDB 10.3+ |
| **웹서버** | Apache (mod_rewrite) 또는 Nginx + PHP-FPM |
| **그누보드** | 5.6.x (본 베이스에 포함) |
| **브라우저** | Chrome, Safari, Edge 최신 + iOS/Android 모바일 |
| **배포 방식** | Git/FTP 없이 **폴더 통째 복사** + 서버별 DB 설정 |

---

## 관련 문서

| 문서 | 경로 |
|------|------|
| 변경 이력 | [`CHANGELOG.md`](CHANGELOG.md) |
| 폴더 구조 | [`FILE-STRUCTURE.md`](FILE-STRUCTURE.md) |
| 수정 금지 | [`DO-NOT-EDIT.md`](DO-NOT-EDIT.md) |
| 복사 체크리스트 | [`COPY-CHECKLIST.md`](COPY-CHECKLIST.md) |
| 시작 가이드 | [`../README-START.md`](../README-START.md) |
