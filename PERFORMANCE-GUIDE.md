# 성능 최적화 가이드

빌더 디자인 적용 후 **이미지·CSS·JS·애니메이션**으로 사이트가 느려지지 않도록 하는 기준입니다.  
onoff-g5-base(그누보드 5.6 제작 베이스) 납품·운영 시 참고용 문서입니다.

| 관련 문서 |
|-----------|
| [IMAGE-GUIDE.md](IMAGE-GUIDE.md) — 이미지 형식·크기·파일명 |
| [SECTION-GUIDE.md](SECTION-GUIDE.md) — Scroll Snap, 섹션 구조 |
| [SEO-CHECKLIST.md](SEO-CHECKLIST.md) — Lighthouse·모바일 항목 |
| [BUILDER-WORKFLOW.md](BUILDER-WORKFLOW.md) — 빌더 적용 순서 |

---

## 1. 성능 최적화가 중요한 이유

| 영향 | 설명 |
|------|------|
| **체감 속도** | 모바일·저사양 기기에서 이탈률 증가 |
| **SEO** | Core Web Vitals(LCP, INP, CLS)는 검색 품질 신호로 활용됨 |
| **전환** | 문의·상담 CTA까지 도달 시간이 길면 문의율 하락 |
| **운영 비용** | 대용량 이미지·동영상은 트래픽·호스팅 부담 |

베이스는 그누보드 기능을 유지한 채 **디자인 레이어만** 두꺼워지기 쉽습니다. 빌더 export 직후 **용량·스크립트 점검**을 습관화하세요.

---

## 2. 이미지 최적화

상세 수치·파일명 규칙: [IMAGE-GUIDE.md](IMAGE-GUIDE.md)

### 2.1 형식·용량

| 항목 | 권장 |
|------|------|
| 사진·배경 | **WebP** (미지원 환경용 JPG fallback) |
| 로고·아이콘 | **SVG** 또는 소형 PNG |
| Hero | 가로 **1920px 이하**, **500KB 이하** |
| 섹션·카드 | **1200px / 600px 이하**, **300KB / 150KB 이하** |
| 1MB 이상 원본 | 업로드 전 반드시 압축 |

### 2.2 lazy loading

```html
<img src="..." alt="설명" loading="lazy" decoding="async">
```

| 적용 | 비적용 |
|------|--------|
| 스크롤 아래 섹션 이미지 | **첫 화면 Hero(LCP 후보)** |
| 게시판 목록·관련글 썸네일 | 로고·작은 아이콘(필요 시만 lazy) |
| `components/*-posts.php` 카드 | |

**Hero 주의:** 메인 첫 배경·대표 비주얼에 `loading="lazy"`를 쓰면 **LCP가 늦어집니다.** Hero는 일반 로딩(속성 생략) 또는 `fetchpriority="high"`(필요 시)를 검토하세요.

베이스 `section/_helpers.php`의 `g5_sample_main_media()`는 lazy를 기본 적용합니다. Hero 섹션만 별도 `<img>`로 교체하거나 헬퍼 옵션을 분리하는 것을 권장합니다.

### 2.3 decoding="async"

- 메인 스레드 블로킹을 줄여 스크롤·입력 반응성에 도움
- lazy와 함께 사용 권장 (Hero 제외)

### 2.4 모바일 이미지 크기

- 모바일 뷰포트(360~430px)에 **4000px 원본**을 그대로 넣지 않기
- CSS로만 `width:100%` 축소해도 **다운로드 용량은 그대로**
- `<picture>` + `srcset`으로 모바일용 작은 파일 제공 (선택, 효과 큼)

---

## 3. CSS 최적화

주요 파일: `/css/custom.css`, `/css/g5b-board.css`, 스킨별 `skin/board/*/style.css`

### 3.1 custom.css

| 권장 | 피할 것 |
|------|---------|
| `:root` 디자인 토큰만 한곳에서 관리 | 동일 속성을 섹션마다 반복 정의 |
| `.site-main .section-*` 스코프 | `*`·`div`·`!important` 남발 |
| 빌더 적용 후 **미사용 섹션 CSS 삭제** | export된 전체 CSS 통째 붙여넣기 |
| 주기적 검색으로 **중복 블록 제거** | |

### 3.2 전역 선택자

```css
/* 비권장 */
* { transition: all 0.3s; }
div { box-sizing: border-box; } /* 이미 reset에 있음 */

/* 권장 */
.site-main .section-service .base-card { ... }
```

### 3.3 게시판 스킨 CSS 충돌

- `g5b-board.css` + 스킨 `style.css` + `default.css` **동시 로드**
- 게시판 페이지에서만 필요한 규칙은 **`.board-wrap` 하위**로 스코프
- 메인 `custom.css`에서 `.tbl`(게시판 테이블) 전역 덮어쓰기 금지 → [README-BOARD-CSS.md](README-BOARD-CSS.md)

### 3.4 효과 줄이기

- `box-shadow`·`filter: blur()`·`backdrop-filter` 다층 → 페인트 비용 증가
- `background-attachment: fixed` → 모바일에서 성능·버그 이슈

---

## 4. JS 최적화

주요 파일: `/js/custom.js` (그누보드 `common.js`는 코어 — 수정 금지)

### 4.1 불필요한 애니메이션

베이스 `custom.js` 기능:

| 기능 | 성능 메모 |
|------|-----------|
| `initReveal` (스크롤 등장) | IntersectionObserver 사용 — 요소 많으면 관찰 대상 줄이기 |
| Scroll Snap | 기본 **비활성** — 모바일·저사양에서 주의 |
| 상담 모달·플로팅 | 필요 기능 — 중복 초기화 금지 |

빌더에서 가져온 **AOS·GSAP·particles** 등은 페이지당 1개만, 메인에만 적용 검토.

### 4.2 스크롤 이벤트

| 권장 | 비권장 |
|------|--------|
| `{ passive: true }` 스크롤 리스너 | scroll마다 layout 읽기·쓰기 |
| `requestAnimationFrame` throttle | jQuery `$(window).scroll()` 무거운 DOM 조작 |
| `initGoTop` 등 베이스 패턴 유지 | |

### 4.3 요소 없을 때 오류 방지

베이스 `G5Template.qsa()` 패턴은 **요소 없으면 return** — 빌더 추가 스크립트도 동일하게:

```javascript
var el = document.querySelector('.my-widget');
if (!el) return;
```

콘솔 `Cannot read properties of null` → 불필요 스크립트가 전 페이지에서 실행 중일 가능성.

### 4.4 외부 스크립트 최소화

| 항목 | 주의 |
|------|------|
| 카카오맵 SDK | `kakao_map_key` 있을 때만 로드 ([components/kakao-map.php](components/kakao-map.php)) |
| GTM·GA4·픽셀 | [components/tracking-*.php](components/tracking-head.php) — ID 없으면 미출력 |
| 폰트·아이콘 CDN | 필요한 weight만 |
| jQuery 플러그인 중복 | 그누보드 기본 + 1개 |

---

## 5. 폰트 최적화

베이스 `custom.css`:

```css
--font-main: 'Pretendard', 'Malgun Gothic', 'Apple SD Gothic Neo', dotum, sans-serif;
```

### 5.1 웹폰트 최소화

| 권장 | 피할 것 |
|------|--------|
| **2종 이하** (본문 + 제목) | 5~6개 패밀리 |
| Regular(400)·Bold(700)만 | Black, Light 등 전 weight |
| 서브셋(한글·라틴) CDN | 전체 unicode 범위 |

### 5.2 Pretendard / Noto Sans KR

- CDN `@font-face` 사용 시 **preconnect** 고려:

```html
<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
```

- 시스템 폰트 fallback(`Malgun Gothic` 등)을 유지해 **FOUT 동안도 가독성** 확보
- 로컬 호스팅 시 woff2만 제공·캐시 헤더 설정

### 5.3 font-display

```css
@font-face {
  font-family: 'Pretendard';
  src: url('...woff2') format('woff2');
  font-display: swap; /* 또는 optional — 텍스트 즉시 표시 우선 */
}
```

| 값 | 특징 |
|----|------|
| `swap` | fallback 먼저 → 웹폰트 로드 후 교체 (FOUT) |
| `optional` | 네트워크 느리면 fallback 유지 (LCP에 유리할 수 있음) |

---

## 6. 빌더 디자인 적용 시 주의

[BUILDER-WORKFLOW.md](BUILDER-WORKFLOW.md) 적용 후 아래를 반드시 확인하세요.

### 6.1 무거운 요소

| 요소 | 대안 |
|------|------|
| Hero 풀스크린 MP4/GIF | 정적 WebP + 짧은 loop video(선택) |
| 2MB PNG 배경 | WebP·압축 |
| 다수의 `background-image` 레이어 | 1장 합성 후 사용 |

### 6.2 blur / shadow / animation

- `filter: blur(40px)` 배경 장식 → 모바일에서 프레임 드롭
- 카드마다 `transition: all` → `transform`, `opacity`만
- 무한 `animation` 배경 → `prefers-reduced-motion` 고려

```css
@media (prefers-reduced-motion: reduce) {
  .reveal { transition: none; opacity: 1; transform: none; }
}
```

### 6.3 Scroll Snap (모바일 신중)

- 기본값: `G5Template.config.scrollSnapEnabled = false` ([js/custom.js](js/custom.js))
- PC 전용(1025px+)만 활성화하는 경우가 많음 — [SECTION-GUIDE.md](SECTION-GUIDE.md) §7
- 모바일에서 snap + fixed 헤더 + 모달 → 스크롤 충돌·체감 지연

### 6.4 LCP (Largest Contentful Paint)

| LCP 후보 | 점검 |
|----------|------|
| Hero 이미지·큰 제목 블록 | 용량·lazy 미적용 |
| 웹폰트 | `font-display`, weight 수 |
| 서버 TTFB | 호스팅·PHP·DB (코어 튜닝은 호스팅 영역) |

Lighthouse **Performance** → LCP 요소 이름 확인 후 해당 리소스만 우선 최적화.

---

## 7. 체크리스트 (납품·오픈 전)

### 이미지

- [ ] Hero·섹션 이미지 [IMAGE-GUIDE.md](IMAGE-GUIDE.md) 용량·크기 기준 충족
- [ ] Hero에 `loading="lazy"` 없음 (또는 의도적 예외 문서화)
- [ ] 썸네일·하단 섹션은 lazy + decoding async
- [ ] 깨진 이미지·404 없음

### CSS / JS

- [ ] `custom.css` 미사용 섹션·빌더 잔여 CSS 정리
- [ ] 게시판 페이지에서 메인 전용 과한 애니메이션 없음
- [ ] 브라우저 **콘솔 오류 0건** (경고는 검토)
- [ ] 요소 없는 페이지에서 JS null 오류 없음

### 모바일·측정

- [ ] 실기기 또는 에뮬 768px·480px 스크롤·모달·메뉴
- [ ] **Lighthouse** (모바일) — Performance, LCP, CLS
- [ ] PageSpeed Insights — Core Web Vitals 필드 데이터(있을 경우)

### 외부·추적

- [ ] 불필요 외부 스크립트 제거 (테스트 위젯·미사용 SDK)
- [ ] GTM·GA4·Meta·네이버·카카오 — **ID 중복 삽입 없음** ([components/tracking-head.php](components/tracking-head.php) + 수동 코드 이중 확인)
- [ ] 전환 추적은 완료 페이지 등 **필요 URL만**

### 서버·배포 (참고)

- [ ] `/_BUILDER_INPUT` 운영 서버 미업로드
- [ ] `data/cache` 권한·불필요 로그 파일 정리
- [ ] gzip/Brotli·브라우저 캐시 (서버 설정)

---

## 8. Cursor 검수 프롬프트 예시

```
이 프로젝트 성능 점검만 해주세요.
PERFORMANCE-GUIDE.md·IMAGE-GUIDE.md 기준으로 확인하고,
git/배포/FTP/bbs/lib/adm 수정 금지.
문제만 최소 수정, 수정 전 파일 목록 제시.

확인: Hero lazy, custom.css 중복, custom.js 스크롤/애니메이션,
tracking 중복, Lighthouse LCP 후보.
```

---

## 9. 관련 문서

| 문서 | 용도 |
|------|------|
| [IMAGE-GUIDE.md](IMAGE-GUIDE.md) | 이미지 상세 |
| [SEO-CHECKLIST.md](SEO-CHECKLIST.md) | SEO·Lighthouse |
| [LAUNCH-CHECKLIST.md](LAUNCH-CHECKLIST.md) | 전체 납품 |
| [ACCESSIBILITY-GUIDE.md](ACCESSIBILITY-GUIDE.md) | 키보드·모달·FAQ·폼·대비 |
| [CLEANUP-PROMPTS.md](CLEANUP-PROMPTS.md) | 샘플·스크립트·납품 전 정리 |
