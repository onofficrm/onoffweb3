# 빌더 → 그누보드 베이스 적용 워크플로우

**onoff-g5-base**에 웹 빌더·젠스파크(Genspark)·구글 스튜디오 빌더·React/Tailwind 결과물을 **안전하게** 붙이는 작업 순서입니다.

| 문서 | 역할 |
|------|------|
| **본 문서** | 단계별 워크플로우·검수·프롬프트 |
| [_BUILDER_INPUT/README.md](_BUILDER_INPUT/README.md) | 빌더 결과물 **임시 보관** (운영 서버 제외) |
| [START-PROJECT-PROMPTS.md](START-PROJECT-PROMPTS.md) | 새 프로젝트 Cursor **순서** 프롬프트 |
| [LAUNCH-CHECKLIST.md](LAUNCH-CHECKLIST.md) | 납품·오픈 전 체크 |
| [IMAGE-GUIDE.md](IMAGE-GUIDE.md) | 이미지 크기·용량·파일명 |
| [README-BUILDER-TO-GNUBOARD.md](README-BUILDER-TO-GNUBOARD.md) | 파일 매핑·HTML/CSS/JS 상세 규칙 |
| [PROMPTS.md](PROMPTS.md) | 작업별 Cursor 프롬프트 |
| [_BASE_INFO/DO-NOT-EDIT.md](_BASE_INFO/DO-NOT-EDIT.md) | 수정 금지 경로 |
| [plugin/onoff-builder-bridge/README.md](plugin/onoff-builder-bridge/README.md) | **dist ZIP** 업로드 → 독립 `page.php?id=` |

---

## 1. 목적

| 목표 | 설명 |
|------|------|
| **구조 유지** | 그누보드 게시판·회원·관리자·로그인은 그대로, **디자인(껍데기)** 만 교체 |
| **섹션 단위** | 메인은 `index.php` + `/section/*.php` — 빌더 블록 1개 = PHP 파일 1개 |
| **충돌 방지** | CSS는 `.site-main`, `.page-template`, `.site-header` 스코프 — `default.css`·게시판 전역 덮어쓰기 금지 |
| **재사용** | 헤더·푸터·모달·SEO·하단 버튼은 베이스 `head.php` / `tail.php` / `components/` 활용 |

**적용 대상 예시**

- 사내/외부 **웹 빌더** (HTML/CSS export)
- **젠스파크** (Genspark) HTML export
- **구글 스튜디오 빌더** (Google Studio Builder) export
- **React + Tailwind** (Vite, Next, CRA 등) → PHP + `custom.css` 변환

### 1.1 dist ZIP — 섹션 변환 없이 독립 페이지 (onoff-builder-bridge)

| 구분 | 설명 |
|------|------|
| **적합** | 랜딩·시안·이벤트·캠페인 페이지 **그대로** 올려 확인 |
| **방식** | `npm run build` → **dist** ZIP → 플러그인 업로드 |
| **출력** | `/plugin/onoff-builder-bridge/page.php?id=프로젝트ID` |
| **미지원** | `src/App.tsx` 원본 ZIP 자동 변환, 메인 `section/` 자동 이식 |

원본(`package.json` + `src/`)과 dist(`index.html` + `assets/`) 차이: [plugin/onoff-builder-bridge/docs/BUILDER-BUILD-GUIDE.md](plugin/onoff-builder-bridge/docs/BUILDER-BUILD-GUIDE.md)  
**업로드·URL·문제 해결:** [plugin/onoff-builder-bridge/README.md](plugin/onoff-builder-bridge/README.md)  
메인·서브를 그누보드 섹션으로 **완전 통합**하려면 기존 Cursor 변환 워크플로(§2 이하)가 여전히 필요합니다.

---

## 2. 빌더 결과물에서 확인할 파일

빌더·React 프로젝트를 받으면 **아래부터** 열어 구조를 파악합니다.

### 2.1 React / Tailwind (대표)

| 확인 파일 | 그누보드에서의 역할 |
|-----------|---------------------|
| **`App.tsx`** | 라우팅·전역 레이아웃 → `head.php` / `tail.php` / `index.php` 분배 기준 |
| **`Home.tsx`** (또는 `pages/index.tsx`) | 메인 본문 → `index.php` + `/section/*.php` |
| **`components/Header.tsx`** | GNB·로고·CTA → **`head.php`** (`#siteHeader`) |
| **`components/Footer.tsx`** | 푸터·사업자 정보 → **`tail.php`** (`#siteFooter`) |
| **섹션 컴포넌트** (`Hero.tsx`, `Services.tsx` …) | 각각 **`section/{이름}.php`** |
| **서브 라우트** (`About.tsx`, `Contact.tsx`) | **`page/about.php`** 등 |
| **`globals.css` / Tailwind config** | 색·간격·폰트 → **`css/custom.css` `:root`** |
| **각 컴포넌트의 `className`** | Tailwind → **일반 CSS class** (`.section-hero` 등) |
| **`assets/`, `public/` 이미지** | **`/img/main/`**, `/img/logo/`, `/img/common/` |
| **`Modal.tsx`, dialog** | **`components/consult-modal.php`** 또는 `section/contact.php` |
| **hooks / `useEffect` / 이벤트** | **`js/custom.js`** (`G5Template.init*`) |

### 2.2 젠스파크 / 구글 스튜디오 / 정적 HTML

| 확인 항목 | 비고 |
|-----------|------|
| 단일 `index.html` 또는 섹션별 HTML | `<section>` 단위로 잘라 `section/*.php` |
| `<style>` 블록 | `custom.css`로 이동 (인라인 최소화) |
| `<script>` | `custom.js` 패턴으로 이전 |
| CDN 폰트·아이콘 | `head.php`의 `add_stylesheet()` (order 10+) 권장 |
| 이미지 경로 | `/img/main/` 등으로 복사 후 상대 경로 수정 |

### 2.3 베이스 쪽 대조 파일 (적용 전에 열어 둘 것)

```
index.php              ← $g5_main_sections 섹션 순서
section/_helpers.php   ← 이미지·플레이스홀더
head.php / tail.php    ← 헤더·푸터·컴포넌트 include
css/custom.css         ← :root 토큰·섹션 스타일
js/custom.js           ← G5Template
_site.config.php       ← 사이트명·색상·연락처
components/*.php       ← 모달·CTA·지도·팝업
```

---

## 3. 변환 규칙

### 3.1 파일 매핑

| 빌더·React 원본 | 그누보드 대상 | 비고 |
|-----------------|---------------|------|
| `App.tsx` | 레이아웃 분배만 참고 | 메인 HTML은 App에 넣지 않음 |
| `Home.tsx` | `index.php` + `/section/*.php` | 본문은 **section 파일에만** |
| `Header.tsx` | **`head.php`** | `get_menu_db()`·로그인 URL 유지 |
| `Footer.tsx` | **`tail.php`** | `g5site_cfg()`·floating·모달 include 유지 |
| `Hero.tsx` 등 | **`section/hero.php`** | class: `section section-hero` |
| `FAQ.tsx` | **`section/faq.php`** | `.faq-item`, `.faq-question` 유지 |
| Tailwind / CSS | **`css/custom.css`** | `:root` + `.site-main` 스코프 |
| `import img from '...'` | **`/img/main/`**, `/img/common/` | `g5_sample_main_media()` |
| `Modal.tsx` | **`components/consult-modal.php`** 또는 section 내부 | `.consult-modal-open` |
| `/about` 라우트 | **`page/about.php`** | `page/_init.php` + `g5_page_start()` |
| 뉴스·공지 콘텐츠 | **게시판** `bbs/board.php` | 스킨 10종 중 선택 |
| 애니메이션·인터랙션 | **`js/custom.js`** 또는 CSS `transition` | jQuery 플러그인 추가 금지 |

### 3.2 PHP·HTML 규칙

1. **section·page 상단**
   ```php
   <?php if (!defined('_GNUBOARD_')) exit; ?>
   ```
2. **인라인 `style=""` 지양** → `custom.css`
3. **섹션 class 유지**
   - `section`, `section-inner`, `section-title`, `section-desc`, `section-actions`
   - 페이지: `page-template`, `page-hero`, `page-section`
4. **URL·출력**
   - `G5_URL`, `G5_BBS_URL`, `get_pretty_url()`
   - 텍스트: `get_text()`, 속성: `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`
5. **섹션 추가·순서** → `index.php`의 `$g5_main_sections` 배열만 수정

### 3.3 Tailwind → custom.css

| Tailwind | custom.css |
|----------|------------|
| `text-blue-600`, `bg-brand` | `var(--color-primary)` |
| `text-gray-500` | `var(--color-muted)` |
| `bg-gray-50` | `var(--color-surface)` |
| `gap-4`, `p-6` | `var(--space-md)`, `var(--space-lg)` |
| `rounded-lg` | `var(--radius-lg)` |
| `shadow-lg` | `var(--shadow-soft)` |
| `md:grid-cols-3` | `.card-grid` + `@media (max-width: 768px)` |
| `hover:opacity-90` | `.btn-primary:hover` 등 |

선택자는 **`.site-main .section-hero ...`**, **`.page-template ...`**, **`.site-header ...`** 안에만 작성.

### 3.4 이미지

| React / 빌더 | 그누보드 |
|--------------|----------|
| `hero.jpg`, `service-01.jpg` | `img/main/hero.jpg` |
| 로고 | `img/logo/logo.svg` + `_site.config.php` → `logo_path` |
| OG·공통 | `img/common/og-image.jpg` |
| 출력 | `<?php g5_sample_main_media('hero.jpg', '설명', 'section-hero__img', 'hero'); ?>` |

파일이 없어도 **플레이스홀더**가 나와 레이아웃이 유지됩니다.

### 3.5 JavaScript

| React | custom.js |
|-------|-----------|
| 모바일 메뉴 | `G5Template.initMobileMenu()` |
| 헤더 스크롤 | `G5Template.initHeaderScroll()` |
| FAQ 아코디언 | `initFaqAccordion()` — `.faq-question` · 게시판 `faq-accordion` · 섹션 `section/faq.php` |
| 상담 모달 | `.consult-modal-open` → `#cmpConsultModal` |
| 앵커 스크롤 | `initSmoothAnchor()` |
| 스크롤 등장 | `.reveal` → `initReveal()` |
| TOP 버튼 | `initGoTop()` |

새 전역 함수·jQuery 플러그인 추가는 **`common.js`와 충돌**할 수 있어 `G5Template` 안에만 추가합니다.

### 3.6 모달·문의

- 베이스 상담 모달: **`#cmpConsultModal`** (`components/consult-modal.php`, `tail.php` include)
- 열기: `class="consult-modal-open"` + `data-target="#cmpConsultModal"`
- 메인 CTA: `section/contact.php` 패턴 참고
- 빌더 전용 모달이 필요하면 **id만 구분**하고 `custom.js` 패턴 재사용

---

## 4. 적용 순서 (권장)

한 번에 전체를 덮어쓰지 말고 **아래 순서**로 진행합니다.

```
┌─────────────────────────────────────────────────────────┐
│ 0. 준비 — 빌더 → /_BUILDER_INPUT/app·assets 에 보관     │
│         베이스 복사본, _site.config, START-PROJECT-PROMPTS │
└───────────────────────────┬─────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────┐
│ 1. 빌더 결과물 분석 (섹션 목록, 색상, 컴포넌트, 라우트)    │
└───────────────────────────┬─────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────┐
│ 2. 섹션 분리 — Hero / Service / FAQ … 파일 단위 매핑     │
└───────────────────────────┬─────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────┐
│ 3. :root 토큰 — _site.config.php + custom.css 색·폰트    │
└───────────────────────────┬─────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────┐
│ 4. Hero 먼저 — section/hero.php + .section-hero CSS      │
│    → PC 메인 1화면 확인                                  │
└───────────────────────────┬─────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────┐
│ 5. Header/Footer — head.php, tail.php (최소 diff)        │
└───────────────────────────┬─────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────┐
│ 6. 나머지 섹션 순차 — service → … → contact              │
│    index.php $g5_main_sections 순서 조정                 │
└───────────────────────────┬─────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────┐
│ 7. 서브페이지 — page/*.php (필요 시)                     │
└───────────────────────────┬─────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────┐
│ 8. custom.css 정리 — 중복 제거, 모바일 @media            │
└───────────────────────────┬─────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────┐
│ 9. custom.js 정리 — 모달·FAQ·메뉴 연결                   │
└───────────────────────────┬─────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────┐
│ 10. 이미지 — img/main, img/logo 실파일 교체              │
└───────────────────────────┬─────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────┐
│ 11. 모바일 검수 — 768px 이하, 게시판 카드 레이아웃       │
└───────────────────────────┬─────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────┐
│ 12. 게시판 색상 — g5b-board.css·스킨 style.css 토큰 통일  │
└───────────────────────────┬─────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────┐
│ 13. 메뉴 최종 정리 — GNB명·/#section-*·page 링크         │
│     (setup/project.sample.json menus · MENU-GUIDE.md)   │
└───────────────────────────┬─────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────┐
│ 14. 전체 검수 — §7 체크리스트 + style-guide.php (선택)  │
└─────────────────────────────────────────────────────────┘
```

**13단계 메뉴 정리:** 빌더·섹션 적용 후 관리자 **메뉴설정**에서 메뉴명·메인 앵커(`/#section-service` 등)·`/page/*.php`·게시판 URL을 [MENU-GUIDE.md](MENU-GUIDE.md) 기준으로 맞춥니다. JSON `menus`는 계획표만 갱신합니다.

**Google Maps 모듈:** 지도 **디자인**만 빌더·젠스파크에 요청하고, 기능은 [MAP-GUIDE.md](MAP-GUIDE.md) · [MAP-BUILDER-WORKFLOW.md](MAP-BUILDER-WORKFLOW.md) 구조를 유지합니다. `_site.config.php` → `google_maps_api_key`.

| 단계 | 작업 | 주요 파일 |
|:----:|------|-----------|
| 1 | 분석·매핑표 작성 | (문서/메모) |
| 2 | 섹션 파일 목록 확정 | `index.php`, `section/` |
| 3 | 브랜드 색·폰트 | `_site.config.php`, `custom.css` `:root` |
| 4 | **Hero** | `section/hero.php`, `.section-hero` |
| 5 | 헤더·푸터 | `head.php`, `tail.php` |
| 6 | 섹션 본문 | `section/*.php` |
| 7 | 서브 | `page/*.php` |
| 8–9 | CSS·JS | `custom.css`, `custom.js` |
| 10 | 에셋 | `img/main/`, `img/logo/` |
| 11 | 모바일 | `custom.css` @media, `g5b-board.css` |
| 12 | 게시판 | `css/g5b-board.css`, `skin/board/*/style.css` |
| 13 | 검수 | 브라우저·관리자·SEO |

---

## 5. 절대 하지 말아야 할 것

| 금지 | 이유 |
|------|------|
| **그누보드 코어 수정** (`common.php`, `config.php`, `_common.php`) | 전역 장애·업데이트 불가 |
| **`/bbs`, `/lib`, `/adm` 수정** | 게시판·회원·관리자 로직 파괴 |
| **전체 파일 무분별 재작성** | diff 불가·회귀 버그 |
| **`head.php` / `tail.php` 대규모 삭제** | 메뉴·로그인·SEO·컴포넌트 단절 |
| **`head.sub.php` 대규모 변경** | jQuery·`html_process`·에셋 순서 깨짐 |
| **게시판 기능 제거** | 목록·쓰기·댓글·권한은 `bbs/` + 스킨으로 유지 |
| **관리자·로그인 UI/URL 삭제** | `G5_BBS_URL` 링크·`get_menu_db()` 유지 |
| **`default.css` 전역 덮어쓰기** | 그누보드 기본 화면 깨짐 |
| **`#bo_list`, `.board-wrap` 밖 게시판 CSS** | 커스텀 스킨만 `g5b-board.css` 스코프 |
| **`/skin/board/basic` 수정** | 원본 스킨 — 10종 커스텀 스킨 사용 |
| **`data/dbconfig.php` 공유·커밋** | DB 비밀번호 유출 |

---

## 6. Cursor 요청 프롬프트 예시

아래는 요약입니다. **전문 템플릿**은 [PROMPTS.md](PROMPTS.md)를 복사해 사용하세요.

공통으로 매번 포함:

- `/bbs`, `/lib`, `/adm`, `skin/board/basic` **수정 금지**
- **작업 전** 수정·생성 예정 파일 목록 제시
- **작업 후** 변경 요약
- git commit / FTP / push **하지 않음**

### 6.1 빌더 결과물 분석 요청

```
아래는 [빌더명]에서 export한 메인 페이지 코드입니다.
onoff-g5-base에 붙이기 위해 분석해 주세요.

[코드 또는 @파일 경로]

1. 섹션 구분 (Hero, Service, FAQ, CTA …)
2. custom.css :root에 넣을 색·폰트·간격
3. section/*.php 파일 단위 제안
4. 게시판·default.css와 충돌할 전역 CSS 여부
5. 수정 금지 경로

작업 전 수정 예정 파일 목록만 먼저 제시. 코어 수정 금지.
```

### 6.2 Hero 섹션만 적용 요청

```
빌더 Hero만 적용해 주세요.
대상: section/hero.php, custom.css .section-hero (필요 최소)
head.php/tail.php 구조 유지. _site.config.php 참고 가능.
코어·basic 스킨 수정 금지. 작업 전/후 파일 목록.
```

### 6.3 메인 전체 적용 요청

```
index.php $g5_main_sections에 연결된 section/ 전체를 빌더 디자인에 맞게 업데이트.
css/custom.css (.site-main 스코프), _site.config.php 연락처 반영.
.board-wrap·bbs/lib/adm 수정 금지. 작업 전/후 목록.
```

### 6.4 모바일만 수정 요청

```
768px 이하만 수정. custom.css @media, section 마크업 최소 조정.
PC 레이아웃·코어·basic 스킨 변경 금지. 작업 전/후 목록.
```

### 6.5 게시판 색상 맞춤 요청

```
_site.config.php primary_color·custom.css :root에 맞춰
게시판 10종 톤 통일. 수정: g5b-board.css, skin/board/*/style.css 만.
bbs/lib/adm/basic 수정 금지. README-BOARD-CSS.md 참고.
```

### 6.6 전체 검수 요청

```
onoff-g5-base 전체 검수. PHP·링크·모달·SEO·코어 수정 여부·dbconfig 노출.
발견된 오류만 수정. git/FTP 금지.
```

---

## 7. 검수 체크리스트

적용 완료 후 **PC → 모바일 → 기능** 순으로 확인합니다.  
디자인 토큰·컴포넌트는 [`page/style-guide.php`](page/style-guide.php)(관리자)로 1회 확인 후 **운영 전 삭제** 권장.

### 7.1 PC

- [ ] 메인 `index.php` — 섹션 순서·여백·이미지·플레이스홀더
- [ ] 헤더 — 로고·GNB·로그인·상담 CTA
- [ ] 푸터 — 사업자 정보·개인정보 링크
- [ ] 서브 `page/*.php` — `page-template` 레이아웃
- [ ] `:root` 색상 — 버튼·링크·섹션 eyebrow 일치
- [ ] 스크롤·앵커·FAQ·reveal 애니메이션
- [ ] 하단 floating — 전화·카카오·상담·TOP

### 7.2 모바일 (≤768px)

- [ ] GNB → 모바일 드로어
- [ ] 섹션·카드 1열·터치 영역
- [ ] `tel:` · 카카오 링크
- [ ] 게시판 목록 카드화 (`g5b-board.css`)
- [ ] (참고) PC `head.php`와 `mobile/head.php` 차이 인지

### 7.3 게시판

- [ ] 사용 스킨 목록·쓰기·보기·댓글
- [ ] PC·모바일 동일 스킨명
- [ ] primary 색·버튼·페이지네이션 톤 일치
- [ ] 썸네일·유튜브 fallback 이미지

### 7.4 로그인·회원

- [ ] 로그인·로그아웃·회원가입 URL
- [ ] 로그인 후 헤더 문구
- [ ] 글쓰기 권한·비회원 글쓰기 정책

### 7.5 관리자

- [ ] `/adm` 접속·메뉴·게시판·내용관리(privacy)
- [ ] 환경설정 제목·추가 메타와 SEO 중복 여부

### 7.6 SEO

- [ ] 페이지 소스 — title 1개, description, canonical, OG, JSON-LD
- [ ] `components/seo-meta.php` 동작
- [ ] 관리자 `cf_add_meta`와 description 중복 없음

### 7.7 문의폼·모달

- [ ] `#cmpConsultModal` 열기·닫기·ESC
- [ ] `consult-modal` 폼·개인정보 동의 문구
- [ ] 1:1 문의 게시판 링크 (사용 시)

### 7.8 콘솔·PHP 오류

- [ ] 브라우저 DevTools Console — 404 JS/CSS, undefined 없음
- [ ] Network — `custom.css`, `custom.js` 200
- [ ] PHP warning/notice 없음 (서버 error_log)
- [ ] 이미지 404 최소화 (플레이스홀더 정상)

---

## 8. 새 프로젝트에서 사용하는 방법

1. **베이스 복사** — [_BASE_INFO/COPY-CHECKLIST.md](_BASE_INFO/COPY-CHECKLIST.md)
2. **`_site.config.php`** — 사이트명·색상·연락처 (빌더 브랜드 가이드와 맞춤)
3. **빌더 결과물 수령** — §2 파일 목록으로 분석
4. **본 워크플로우 §4 순서** — Hero → 섹션 → CSS/JS → 모바일 → 게시판
5. **Cursor** — [PROMPTS.md](PROMPTS.md) 프롬프트 붙여 넣기
6. **검수** — §7 체크리스트 + `style-guide.php` (삭제 전)
7. **런칭** — `style-guide.php` 삭제·차단, `privacy` 내용관리 실내용 반영

---

## 9. 관련 문서

| 문서 | 내용 |
|------|------|
| [SECTION-GUIDE.md](SECTION-GUIDE.md) | 섹션 폴더·class·include |
| [BOARD-SKIN-GUIDE.md](BOARD-SKIN-GUIDE.md) | 게시판 10종 선택·검수 |
| [presets/](presets/) | 목적별 메뉴·섹션·게시판 조합 |
| [PROMPTS.md](PROMPTS.md) | Cursor 프롬프트 10종 |
| [README-START.md](README-START.md) | 복사 후 시작 |
| [README-BUILDER-TO-GNUBOARD.md](README-BUILDER-TO-GNUBOARD.md) | 변환 규칙·Scroll Snap·최신글 |
| [README-BOARD-SKINS.md](README-BOARD-SKINS.md) | 게시판 운영 상세 |
| [README-BOARD-CSS.md](README-BOARD-CSS.md) | `.board-wrap` CSS |
| [_BASE_INFO/COPY-CHECKLIST.md](_BASE_INFO/COPY-CHECKLIST.md) | 복사 체크리스트 |
| [page/style-guide.php](page/style-guide.php) | 디자인 검수 (개발용) |
