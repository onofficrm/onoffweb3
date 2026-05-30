# 빌더 → 그누보드 적용 가이드

> **Sample GNU Board Base Template**  
> 빌더·젠스파크·구글 스튜디오 빌더·React/Tailwind 결과물을 그누보드 5 기본 설치본에 안전하게 붙이기 위한 **내부 작업 규칙**입니다.

---

## 1. 이 샘플 템플릿의 목적

| 목표 | 설명 |
|------|------|
| **기본 구조 유지** | `bbs/`, `adm/`, `lib/`, `plugin/` 등 그누보드 핵심은 그대로 두고 **껍데기(레이아웃·디자인)** 만 교체 |
| **테마 미사용** | `theme/basic`·리빌더 테마 없이 루트 `head.php` / `tail.php` / `index.php` 기준 |
| **섹션 단위 작업** | 메인은 `/section/*.php` 조각으로 분리 → 빌더 섹션 1:1 매핑 |
| **SEO·콘텐츠** | 메인 `latest()` 최신글, 서브 `/page/*.php`, 관리자 메뉴 DB 연동 |
| **충돌 최소화** | 스타일·JS는 `.site-main`, `.page-template`, `.site-header` 등 **스코프 class** 로 한정 |

**적용 대상 빌더 예시**

- 사내/외부 **웹 빌더** (HTML/CSS export)
- **젠스파크** (Genspark) HTML export
- **구글 스튜디오 빌더** (Google Studio Builder) export
- **React + Tailwind** (Vite/Next 등) → PHP + `custom.css` 변환

---

## 2. 주요 파일 구조

```
sample/                          # 그누보드 루트 (G5_PATH)
├── index.php                    # 메인: section include 목록
├── head.php                     # PC 공통 헤더 (#siteHeader)
├── tail.php                     # PC 공통 푸터 + 하단 dock
├── head.sub.php / tail.sub.php  # ⚠️ 최소 수정 (HTML 골격·에셋 로드)
├── common.php / config.php      # ⚠️ 수정 금지에 가깝게
│
├── css/
│   └── custom.css               # ★ 디자인 토큰·섹션·헤더·푸터·페이지 CSS
├── js/
│   └── custom.js                # ★ G5Template 인터랙션 (Vanilla JS)
│
├── section/                     # ★ 메인 섹션 (빌더 블록 → 1파일)
│   ├── _helpers.php             # 이미지·latest 헬퍼
│   ├── hero.php
│   ├── service.php
│   ├── advantage.php
│   ├── portfolio.php
│   ├── latest.php               # 게시판 최신글 (story/news/sample)
│   ├── review.php
│   ├── faq.php
│   └── contact.php
│
├── page/                        # ★ 서브페이지 (독립 URL)
│   ├── _init.php                # _common.php + head/tail 래퍼
│   ├── about.php
│   ├── service.php
│   ├── portfolio.php
│   └── contact.php
│
├── img/
│   ├── logo/                    # logo.svg, logo.png
│   ├── main/                    # 섹션·히어로 이미지
│   ├── icons/
│   └── common/
│
├── skin/latest/
│   └── card/                    # 메인 최신글 카드 스킨
│
├── bbs/                         # ⚠️ 게시판·회원·로그인 로직 — 건드리지 않음
├── adm/                         # ⚠️ 관리자
├── lib/                         # ⚠️ latest(), get_menu_db() 등
├── mobile/                      # 모바일 전용 head/tail/index (별도 작업)
└── theme/basic/                 # 사용 안 함 (cf_theme 비움)
```

### 렌더링 흐름 (PC 메인)

```
index.php
  → _common.php
  → head.php → head.sub.php (+ custom.css / custom.js)
  → <main id="siteMain">
       → section/hero.php … section/contact.php
  → tail.php → tail.sub.php
```

### 서브페이지

```
page/about.php
  → page/_init.php → ../_common.php
  → g5_page_start('제목') → head.php
  → <div class="page-template page-about"> …
  → g5_page_end() → tail.php
```

---

## 3. 빌더 결과물 변환 규칙

### 3.1 파일 매핑 (React / 빌더 공통)

| 빌더·React 원본 | 그누보드 대상 | 비고 |
|-----------------|---------------|------|
| `App.tsx`, `Home.tsx`, 메인 페이지 JSX | `index.php` + `/section/*.php` | 메인 본문은 **section 파일에만** HTML |
| `Header.tsx`, `Navbar` | `head.php` (`#siteHeader`) | `get_menu_db()` 유지, 로그인·검색 URL 유지 |
| `Footer.tsx` | `tail.php` (`#siteFooter`, `#siteDock`) | 연락처 변수·하단 고정 버튼 |
| `AboutPage.tsx` 등 라우트 | `/page/about.php` 등 | `_init.php` 패턴 필수 |
| `components/Hero.tsx` | `section/hero.php` | |
| `components/FAQ.tsx` | `section/faq.php` | `.faq-item`, `.faq-question` 유지 |
| `globals.css`, Tailwind | `css/custom.css` | `:root` 토큰 + 일반 class |
| `hooks`, `onClick` | `js/custom.js` | `G5Template` 네임스페이스 |
| `import img from '...'` | `/img/main/`, `/img/common/` | PHP `g5_sample_main_media()` |
| `Modal.tsx` | `section/contact.php` 또는 `tail.php` HTML + `.consult-modal` | `custom.js` `initConsultModal` |
| `react-router` `/news` | `page/*.php` 또는 게시판 `bbs/board.php` | 콘텐츠성 → 게시판, 랜딩 → page |

### 3.2 HTML / PHP 규칙

1. **모든 section·page PHP 상단**
   ```php
   <?php if (!defined('_GNUBOARD_')) exit; ?>
   ```
2. **인라인 `style=""` 사용 금지** → `custom.css`로 이동
3. **class 이름 유지·확장**
   - 섹션: `section section-{이름}`, `section-inner`, `section-title`, `section-desc`
   - 페이지: `page-template page-{이름}`, `page-hero`, `page-title`, `page-section`
4. **그누보드 함수·URL**
   - 링크: `G5_URL`, `G5_BBS_URL`, `get_pretty_url()`
   - 출력 이스케이프: `get_text()`
5. **메인 섹션 추가** → `index.php`의 `$g5_main_sections` 배열에 이름 추가

### 3.3 Tailwind → custom.css 변환

| Tailwind | custom.css 권장 |
|----------|-----------------|
| `flex`, `grid` | `.card-grid`, `.latest-grid`, BEM 블록 |
| `gap-4`, `p-6` | `:root` `--space-*` 또는 섹션 전용 class |
| `text-blue-600` | `--color-primary` |
| `bg-gray-50` | `--color-surface` |
| `rounded-lg` | `--radius-lg` |
| `shadow-lg` | `--shadow-soft` |
| `md:grid-cols-3` | `@media (max-width: 768px)` 블록 |
| `hover:opacity-90` | `.btn-primary:hover` |

**절차**

1. 빌더 HTML에서 **반복 패턴**을 class로 추출 (예: `.service-card`)
2. 색·간격은 **`:root` 변수**에만 모아둠
3. 스타일 선택자는 **`.site-main` / `.page-template` / `.site-header`** 안으로 한정

### 3.4 이미지

| React / 빌더 | 그누보드 |
|--------------|----------|
| `import hero from './assets/hero.jpg'` | `img/main/hero.jpg` |
| `<img src={hero} />` | `<?php g5_sample_main_media('hero.jpg', '설명', 'class', 'hero'); ?>` |
| 로고 | `img/logo/logo.svg` 또는 `logo.png` (없으면 텍스트 로고) |
| 아이콘 SVG | `img/icons/` |

파일이 없어도 **플레이스홀더**가 나오므로 레이아웃이 깨지지 않음.

### 3.5 JavaScript / 이벤트

| React | custom.js |
|-------|-----------|
| `useState` 메뉴 열림 | `G5Template.initMobileMenu()` |
| `useEffect` scroll | `G5Template.initHeaderScroll()` |
| `onClick` FAQ | `.faq-question` → `initFaqAccordion()` |
| 모달 open/close | `.consult-modal-open`, `.consult-modal-close` |
| `scrollIntoView` | `initSmoothAnchor()` |
| Intersection Observer | `.reveal` → `initReveal()` |
| 상단 이동 | `#top_btn`, `.go-top` → `initGoTop()` |

- **jQuery 플러그인·전역 함수 추가 금지** (`common.js`와 충돌 방지)
- 설정: `G5Template.config.*`

### 3.6 모달

```html
<button type="button" class="consult-modal-open" data-target="#consultModal">문의</button>

<div id="consultModal" class="consult-modal" aria-hidden="true">
  <div class="consult-modal-overlay"></div>
  <div class="consult-modal__panel">
    <button type="button" class="consult-modal-close">닫기</button>
  </div>
</div>
```

- 메인: `section/contact.php`
- 서브: `page/contact.php` (`#pageConsultModal` 등 id만 구분)

### 3.7 최신글 (SEO)

- 메인 게시판 노출: `section/latest.php`의 `$g5_latest_boards`
- 스킨: `skin/latest/card/` (없으면 `basic` fallback)
- 게시판 없음 → 카드 fallback 문구 (페이지 깨짐 없음)

### 3.8 젠스파크 / 구글 스튜디오 빌더 / 정적 HTML

1. export HTML을 섹션 단위로 **잘라** `section/{name}.php`에 붙여넣기
2. `<style>` 블록 → `custom.css`로 이동
3. `<script>` → `custom.js`의 `G5Template.init*` 패턴으로 이전
4. 외부 CDN 폰트·CSS는 `head.sub.php` 수정보다 **`add_stylesheet()` in head.php** (order 10 이후) 권장

---

## 4. Cursor 작업 시 주의사항

1. **작업 전** `cf_theme` 비어 있는지, PC `head.php` 경로 쓰는지 확인
2. **한 번에 전체 덮어쓰기 금지** — 섹션·페이지 **파일 단위** PR/커밋
3. **`bbs/`, `adm/`, `lib/`, `common.php` 수정 제안 시 반드시 거절·분리**
4. 그누보드 기본 CSS(`default.css`) **전역 덮어쓰기 금지** → `custom.css`만
5. PHP **직접 URL**: `page/about.php`는 `page/_init.php` 필수, `section/*.php`는 `index.php` include만
6. 모바일은 **`mobile/head.php`, `mobile/tail.php`** 별도 — PC만 수정했다고 모바일 완료 아님
7. 변경 후 **스모크 테스트**: 메인, 서브 1개, 로그인, 글쓰기, 관리자, 메뉴 링크
8. 연락처·전화·카카오는 **`tail.php` + `section/contact.php` + `page/contact.php`** 값 일치

---

## 5. 수정하면 안 되는 파일

| 경로 | 이유 |
|------|------|
| `common.php`, `config.php`, `_common.php` | 부트스트랩·상수 |
| `data/dbconfig.php` | DB·`G5_USE_SHOP` (자격증명 주의) |
| `bbs/*` 처리 로직 (`write_update.php`, `register_form_update.php` 등) | 회원·게시판 핵심 |
| `adm/**` | 관리자 |
| `lib/*.lib.php` (함수 시그니처) | `latest()`, `get_menu_db()` 등 |
| `plugin/**` | 에디터·결제·인증 |
| `install/**` | 설치기 |
| `theme/basic/**` | 테마 미사용 시 불필요·혼선 |

**신중히 (형식·로드 순서만)**

- `head.sub.php`, `tail.sub.php` — `html_end()`, jQuery 로드 순서

---

## 6. 자주 수정하는 파일

| 파일 | 용도 |
|------|------|
| `css/custom.css` | 색상·레이아웃·반응형·빌더 class |
| `js/custom.js` | 인터랙션·설정 |
| `section/*.php` | 메인 섹션 HTML |
| `page/*.php` | 서브페이지 HTML |
| `index.php` | 섹션 순서 (`$g5_main_sections`) |
| `head.php` | 헤더 마크업·로고·GNB |
| `tail.php` | 푸터·연락처·하단 버튼 |
| `section/latest.php` | 최신글 게시판 ID |
| `skin/latest/card/latest.skin.php` | 최신글 카드 마크업 |
| `img/logo/`, `img/main/` | 이미지 에셋 |

---

## 7. Scroll Snap 켜는 방법

1. **CSS** (`custom.css`): `html.page-index` + `.site-main.snap-enabled` (PC 1025px+만, 모바일 off)
2. **JS** (`js/custom.js`):
   ```javascript
   G5Template.config.scrollSnapEnabled = true;
   ```
3. 또는 HTML: `<main id="siteMain" class="site-main snap-enabled">` (`index.php`)

---

## 8. 색상 변경 방법

`css/custom.css` 상단 `:root`만 수정:

```css
:root {
  --color-primary: #2563eb;
  --color-secondary: #64748b;
  --color-bg: #ffffff;
  --color-surface: #f8fafc;
  --color-text: #1e293b;
  --color-muted: #64748b;
  --color-line: #e2e8f0;
}
```

섹션별 예외는 `.section-hero { }`, `.page-cta--dark { }` 등 **파일 하단에 블록 추가**.

---

## 9. 로고 변경 방법

1. 파일 배치 (우선순위):
   - `img/logo/logo.svg`
   - `img/logo/logo.png`
2. 없으면 `head.php`에서 **사이트 제목 텍스트** (`cf_title`) 표시
3. 빌더 SVG는 `img/logo/logo.svg`로 export (가능하면 단색·단순 path)

---

## 10. 메뉴 변경 방법

**코드 수정 없이 (권장)**

- 관리자 → **환경설정 → 메뉴설정**
- PC: `me_use`, 모바일: `me_mobile_use`
- 링크 예: `/page/about.php`, `/page/service.php`, `/#section-contact`

**코드**

- `head.php`: `get_menu_db(0, true)` (PC), `get_menu_db(1, true)` (모바일)
- 메뉴 없을 때 fallback 문구 이미 처리됨

---

## 11. 빌더 디자인 적용 요청 프롬프트 예시

### 예시 A — 섹션 1개 교체 (Hero)

```
이 프로젝트는 그누보드 5 샘플 베이스 템플릿입니다.
README-BUILDER-TO-GNUBOARD.md 규칙을 따르세요.

작업:
- 첨부한 빌더 HTML을 section/hero.php에 적용
- Tailwind는 custom.css로 변환 (인라인 style 금지)
- class: section section-hero, section-inner, reveal 유지
- 이미지는 img/main/hero.jpg 기준, g5_sample_main_media() 사용
- bbs/, lib/, common.php 수정 금지
```

### 예시 B — React Home 전체 → 메인

```
README-BUILDER-TO-GNUBOARD.md 기준으로 React Home.tsx를 변환하세요.

- Header → head.php (#siteHeader), get_menu_db 유지
- Footer → tail.php
- 각 섹션 → section/{name}.php (hero, service, …)
- Tailwind → css/custom.css (:root 토큰 우선)
- 이벤트 → js/custom.js (G5Template)
- index.php $g5_main_sections 순서: hero, service, …, latest, contact
- 게시판/관리자 로직 건드리지 않기
```

### 예시 C — 서브페이지 About

```
빌더 About 페이지 HTML을 page/about.php에 적용하세요.

- page/_init.php + g5_page_start/end 패턴 유지
- class: page-template page-about, page-hero, page-title, page-section, page-cta
- CSS는 custom.css에 .page-about 블록으로 추가
- 직접 URL /page/about.php 동작 확인
```

### 예시 D — 최신글만 연동

```
section/latest.php의 $g5_latest_boards를
story, press, blog 로 변경하고
skin/latest/card/ 마크업은 유지하세요.
게시판 없을 때 fallback 동작 유지.
```

### 예시 E — 전역 리브랜딩

```
README-BUILDER-TO-GNUBOARD.md 참고.

1. custom.css :root 브랜드 컬러 첨부 팔레트로 변경
2. img/logo/logo.svg 교체
3. tail.php 연락처 변수 업데이트
4. section/contact.php CTA 문구만 빌더 카피로 변경
구조 변경·bbs 수정 없음
```

---

## 부록: 체크리스트 (적용 완료 전)

- [ ] `cf_theme` 비움 (테마 미사용)
- [ ] PC 메인·서브·로그인·게시판 글쓰기·관리자 접속 OK
- [ ] `custom.css` / `custom.js` 로드됨 (`head.php` add_stylesheet)
- [ ] 인라인 style 없음
- [ ] 이미지 경로 `img/main/`, `img/logo/` 정리
- [ ] 메뉴 URL이 `page/` 또는 `/#section-*` 와 일치
- [ ] 모바일 레이아웃 필요 시 `mobile/` 별도 작업 여부 결정
- [ ] 최신글 게시판 ID·fallback 확인 (`section/latest.php`)

---

## 문서 버전

| 항목 | 값 |
|------|-----|
| 템플릿 | 그누보드 5.6.x 기본 설치본 + sample 레이아웃 |
| 최종 갱신 | 프로젝트 내 `head.php` / `section/` / `page/` 구조 기준 |
| 관련 문서 | 프로젝트 루트 `README.md` (있을 경우), 본 파일 |

질문·예외 케이스는 이 문서를 Cursor 규칙·`AGENTS.md`에 링크해 두고 작업하세요.
