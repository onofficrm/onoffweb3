# 파일·폴더 구조

onoff-g5-base v1.0.0 기준. 그누보드 전체 트리 중 **템플릿·커스텀 영역** 위주로 정리합니다.

```
onoff-g5-base/                    ← 프로젝트 루트 (G5_PATH)
├── _BUILDER_INPUT/               ← 빌더·React 임시 보관 (운영 업로드 제외)
│   ├── app/
│   ├── assets/
│   └── screenshots/
├── _BASE_INFO/                   ← 베이스 메타 문서 (본 폴더)
├── _site.config.php              ← ★ 사이트 공통 설정
├── index.php                     ← 메인 (section include)
├── head.php / tail.php           ← ★ PC 레이아웃
├── head.sub.php / tail.sub.php   ← 그누보드 head/tail 코어 (최소 연동)
│
├── section/                      ← ★ 메인 섹션 PHP
│   ├── _helpers.php
│   ├── hero.php, service.php, …
│   └── contact.php
│
├── page/                         ← ★ 커스텀 서브페이지
│   ├── _init.php
│   ├── about.php, service.php, …
│   ├── privacy.php               ← 개인정보처리방침 (샘플)
│   └── style-guide.php           ← 개발용 (운영 전 삭제 권장)
│
├── components/                   ← ★ 재사용 UI 컴포넌트
│   ├── seo-meta.php
│   ├── floating-buttons.php
│   └── …
│
├── css/
│   ├── custom.css                ← ★ 사이트·섹션·페이지 디자인
│   └── g5b-board.css             ← ★ 게시판 공통 CSS
│
├── js/
│   └── custom.js                 ← ★ 템플릿 JS
│
├── img/
│   ├── logo/                     ← ★ 로고
│   ├── main/                     ← ★ 메인 섹션 이미지
│   └── common/                   ← OG, placeholder SVG
│
├── skin/board/                   ← ★ 커스텀 게시판 스킨 10종
│   ├── _inc/
│   ├── basic-clean/ … youtube-gallery/
│   ├── basic/                    ← ✗ 그누보드 원본 (수정 금지)
│   └── gallery/                  ← ✗ 그누보드 원본 (수정 금지)
│
├── mobile/skin/board/            ← ★ 모바일 스킨 (PC와 동일 10종)
│
├── docs/                         ← 납품·고객 문서 (코어 무관)
│   └── client/
│       ├── site-operation-guide-template.md  ← ★ 고객 운영 가이드 템플릿
│       ├── site-operation-guide-sample.md
│       └── pdf-export-guide.md
│
├── setup/                        ← 복사·교체·게시판 계획
│   ├── site.sample.json
│   ├── project.sample.json       ← ★ 게시판 boards[] 계획 (DB 미반영)
│   ├── replace-checklist.md
│   └── tools/
│       └── create-default-boards.example.php  ← 실행 차단 예시
├── presets/                      ← 목적별 조합 (업종별 아님)
├── BUILDER-WORKFLOW.md
├── SECTION-GUIDE.md
├── BOARD-CREATE-GUIDE.md
├── BOARD-CREATE-PROMPTS.md
├── BOARD-SKIN-GUIDE.md
├── PROMPTS.md
├── START-PROJECT-PROMPTS.md
├── LAUNCH-CHECKLIST.md
├── CLIENT-MANUAL.md
│
├── data/                         ← ✗ DB·캐시·세션 (서버별)
│   ├── dbconfig.php
│   └── dbconfig.local.php        ← 로컬 전용 (복사 금지)
│
├── bbs/                          ← ✗ 그누보드 게시판 코어
├── lib/                          ← ✗ 그누보드 라이브러리
├── adm/                          ← ✗ 그누보드 관리자
├── plugin/                       ← 그누보드 플러그인 (필요 시만)
├── theme/                        ← 기본 테마 (테마 미사용 시 거의 비활성)
└── mobile/                       ← 모바일 head/tail (그누보드 기본)
```

---

## 주요 폴더 역할

| 폴더·파일 | 역할 |
|-----------|------|
| **`/_site.config.php`** | 사이트명, 연락처, 색상, 로고, SEO 기본값 — **새 프로젝트 1순위 수정** |
| **`/section/`** | 메인 페이지 블록(히어로, 서비스, FAQ 등). `index.php`에서 순서만 조정 |
| **`/page/`** | 회사소개·서비스 등 서브페이지. `g5_page_start()`로 head/tail 포함 |
| **`/components/`** | tail·섹션에서 include하는 공통 UI (모달, CTA, SEO) |
| **`/css/custom.css`** | 디자인 토큰(`:root`), 버튼·카드·섹션·컴포넌트 스타일 |
| **`/css/g5b-board.css`** | 커스텀 게시판 10종 공통 스타일 (`.board-wrap`) |
| **`/skin/board/{스킨명}/`** | 게시판 list/write/view 스킨 — 관리자에서 스킨명 지정 |
| **`/mobile/skin/board/`** | 모바일용 동일 스킨 (PC와 이름 맞출 것) |
| **`/setup/`** | JSON 샘플·복사 후 교체 체크리스트 |
| **`/_BASE_INFO/`** | 버전·변경·구조·금지·체크리스트 문서 |
| **`/bbs/`, `/lib/`, `/adm/`** | 그누보드 코어 — **수정하지 않음** |
| **`/data/`** | DB 설정, 업로드, 캐시 — **서버별**, 복사 시 주의 |

---

## 새 프로젝트에서 자주 수정하는 파일

| 우선순위 | 파일·폴더 | 작업 내용 |
|:--------:|-----------|-----------|
| 1 | `_site.config.php` | 사이트·회사·연락처·색상·로고·SEO |
| 2 | `img/logo/`, `img/main/` | 로고·메인 이미지 교체 |
| 3 | `css/custom.css` | `:root` 토큰·섹션 스타일 (빌더 반영) |
| 4 | `section/*.php` | 메인 문구·구조 |
| 5 | `page/*.php` | 서브페이지 문구 |
| 6 | `index.php` | 섹션 순서·추가·삭제 |
| 7 | `head.php`, `tail.php` | 메뉴·푸터·include 조정 (필요 시) |
| 8 | 관리자 | 메뉴, 게시판, 내용관리(privacy), 환경설정 |
| 9 | `components/*.php` | 문구·폼·팝업 (필요 시) |
| 10 | `js/custom.js` | 인터랙션 (필요 시) |

---

## 수정하면 안 되는 파일·폴더

| 경로 | 이유 |
|------|------|
| `/bbs/` | 게시판·회원·인증 코어 — 업데이트 시 덮어씀 |
| `/lib/` | 공통 함수·DB·훅 — 업데이트 시 덮어씀 |
| `/adm/` | 관리자 화면 — 업데이트 시 덮어씀 |
| `common.php` | 그누보드 진입점 |
| `/skin/board/basic/` | 그누보드 **원본** 기본 스킨 |
| `/skin/board/gallery/` | 그누보드 **원본** 갤러리 스킨 |
| `/data/dbconfig.php` | **DB 비밀번호** — 복사·Git·공유 금지 |
| `/data/dbconfig.local.php` | 로컬 DB — 서버에 올리지 않음 |

자세한 이유: [`DO-NOT-EDIT.md`](DO-NOT-EDIT.md)

---

## 문서 파일 (루트·setup)

| 파일 | 용도 |
|------|------|
| `README-START.md` | 시작 가이드 |
| `README-BOARD-SKINS.md` | 게시판 10종 설명 |
| `README-BOARD-CSS.md` | 게시판 CSS 구조 |
| `README-BUILDER-TO-GNUBOARD.md` | 빌더 → 섹션 적용 |
| `PROMPTS.md` | AI 작업 프롬프트 |
| `SAMPLE-CONTENT.md` | 샘플 글·문구 |
| `setup/replace-checklist.md` | 상세 교체 체크리스트 |

---

## 관련 문서

- [`VERSION.md`](VERSION.md)
- [`DO-NOT-EDIT.md`](DO-NOT-EDIT.md)
- [`COPY-CHECKLIST.md`](COPY-CHECKLIST.md)
