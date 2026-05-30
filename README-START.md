# 그누보드 제작 베이스 시작 가이드

이 폴더는 **그누보드 5.6** 전체 + 빌더형 메인/서브 + **커스텀 게시판 스킨 10종**이 포함된 **제작 베이스**입니다.  
Git/FTP 없이 **폴더 통째 복사** → 설정만 바꿔 새 사이트를 만듭니다.

---

## 1. 이 템플릿의 목적

- 홈페이지(빌더) 디자인을 `css/custom.css` 토큰 + `section/` / `page/`에 붙이기 쉽게 함
- 게시판은 스킨 10종 중 선택만으로 운영 ([README-BOARD-SKINS.md](README-BOARD-SKINS.md))
- 사이트명·연락처·로고 등은 **`/_site.config.php` 한 파일**에서 관리
- 그누보드 **코어(`/bbs`, `/lib`, `/adm`)는 수정하지 않음**

---

## 2. 새 프로젝트 시작 순서

1. 이 폴더 전체를 새 작업 경로에 **복사**
2. **`/_site.config.php`** 수정 (사이트 정보·색상·로고 경로)
3. **`/css/custom.css`** `:root`에서 메인 컬러·폰트 조정 (선택, `_site.config.php` primary와 연동됨)
4. **`/img/logo/`** 로고 파일 교체
5. 관리자 로그인 → **환경설정** 사이트 제목·SEO
6. **메뉴·게시판 계획** — [setup/project.sample.json](setup/project.sample.json)의 `boards`·`menus` 수정 (JSON만, DB 미반영)
7. **게시판 생성** — [BOARD-CREATE-GUIDE.md](BOARD-CREATE-GUIDE.md)대로 관리자에서 추가 (PC·모바일 스킨 **동일명** 권장)
8. **메뉴 등록** — [MENU-GUIDE.md](MENU-GUIDE.md)대로 관리자 **메뉴관리**에서 GNB·모바일 메뉴 연결 (게시판·`/page` 준비 **후**)
9. `index.php` 섹션(`section/`) 문구·이미지 교체
10. [SAMPLE-CONTENT.md](SAMPLE-CONTENT.md) 샘플 글·문구로 게시판 채우기
11. PC·모바일 브라우저 최종 검수

---

## 3. 복사 후 반드시 변경할 항목

| 항목 | 수정 위치 |
|------|-----------|
| 사이트명 | `_site.config.php` → `site_name` + 관리자 환경설정 |
| 로고 | `_site.config.php` → `logo_path` + `/img/logo/` 파일 |
| 메인 컬러 | `_site.config.php` → `primary_color` 또는 `custom.css` `:root` |
| 회사명·대표·사업자번호 | `_site.config.php` |
| 전화·카카오·이메일·주소 | `_site.config.php` |
| 푸터 설명 | `_site.config.php` → `footer_desc` |
| OG 이미지 | `_site.config.php` → `og_image` + 파일 업로드 |
| SEO title/description | 관리자 환경설정 |
| 관리자 메뉴 | 관리자 메뉴관리 |
| 게시판 ID·스킨 | 관리자 게시판관리 |
| DB 접속 | `data/dbconfig.local.php` (복사본에 포함하지 말 것) |

---

## 4. `_site.config.php` 수정 방법

경로: **`/_site.config.php`** (그누보드 루트)

```php
$site_config = array(
    'site_name'         => '실제 사이트명',
    'phone'             => '02-1234-5678',
    'kakao_url'         => 'https://pf.kakao.com/채널ID',
    'primary_color'     => '#2563eb',
    'logo_path'         => '/img/logo/logo.svg',
    // ...
);
```

- `head.php` / `tail.php`에서 자동 include
- `g5site_cfg('키', '기본값')` 으로 PHP에서 조회
- 값이 비어 있으면 기본값 사용 (fatal 없음)

---

## 5. 로고 교체 방법

1. SVG 또는 PNG를 `/img/logo/logo.svg` (또는 `logo.png`)에 업로드
2. `_site.config.php`의 `logo_path`를 실제 경로로 수정  
   예: `'/img/logo/my-logo.svg'`
3. 헤더에서 이미지 없으면 **사이트명 텍스트**로 fallback

---

## 6. 색상 변경 방법

**방법 A (권장):** `_site.config.php`

```php
'primary_color'   => '#00F0FF',
'secondary_color' => '#7C3AED',
```

→ `head.php`가 `:root`에 인라인으로 반영 (버튼·헤더 CTA·게시판 토큰 연동)

**방법 B:** `/css/custom.css` `:root` 직접 수정

```css
:root {
  --color-primary: #2563eb;
  --color-secondary: #64748b;
}
```

게시판 상세: [README-BOARD-SKINS.md §9](README-BOARD-SKINS.md#9-디자인-색상-변경-방법)

---

## 7. 메뉴 설정 방법

1. [setup/project.sample.json](setup/project.sample.json) → **`menus`** 항목을 프로젝트에 맞게 수정 (계획표, DB 미반영)
2. [MENU-GUIDE.md](MENU-GUIDE.md) · 유형별 [MENU-EXAMPLES.md](MENU-EXAMPLES.md) 참고
3. 관리자 → **환경설정 → 메뉴설정** — PC용(0)·모바일용(1) 각각 등록
4. `head.php`의 `#siteGnb`, `#siteMobileNav`가 `get_menu_db()` 메뉴 DB를 출력

---

## 8. 게시판 스킨 선택 방법

관리자 → **게시판관리 → 수정**

| 용도 | 스킨 예시 |
|------|-----------|
| 공지 | `basic-notice` |
| 소식 | `basic-modern` |
| 블로그/칼럼 | `post-thumb`, `post-media` |
| 포트폴리오 | `gallery-grid`, `gallery-masonry` |
| 영상 | `youtube-list`, `youtube-gallery` |

자세히: [README-BOARD-SKINS.md](README-BOARD-SKINS.md)

---

## 9. 메인 섹션 수정 방법

- `index.php` — 섹션 include 순서
- `section/hero.php`, `service.php`, `contact.php` 등 — HTML·문구
- `section/_helpers.php` — 공통 헬퍼

빌더 적용: [README-BUILDER-TO-GNUBOARD.md](README-BUILDER-TO-GNUBOARD.md), [PROMPTS.md](PROMPTS.md)

---

## 10. 컴포넌트 사용 방법

| 파일 | 용도 | include 예시 |
|------|------|----------------|
| `components/quick-contact.php` | 섹션 내 빠른 문의 | `<?php include_once(G5_PATH.'/components/quick-contact.php'); ?>` |
| `components/bottom-cta.php` | 하단 전환 CTA | 동일 |
| `components/kakao-map.php` | 오시는 길 지도 (카카오) | 동일 |
| `components/maps/` | Google Maps 내 주변 찾기 | [MAP-GUIDE.md](MAP-GUIDE.md), `/page/map-locator.php` |
| `components/popup-banner.php` | 이벤트 팝업 | `tail.php`에 이미 포함 |
| `components/floating-buttons.php` | 하단 고정 버튼 | `tail.php`에 이미 포함 |
| `components/consult-modal.php` | 상담 모달 | `tail.php`에 이미 포함 |

모달 열기 버튼 class: **`consult-modal-open`** (`data-target="#cmpConsultModal"`)

---

## 11. 빌더 디자인 적용 시 주의사항

- 코어·`/skin/board/basic` 수정 금지
- 스타일은 **`.site-main`**, **`.page-template`** 스코프 우선
- 게시판은 **`.board-wrap`** 스코프 (`g5b-board.css`) — 전역 `#bo_list` 덮어쓰기 금지
- 빌더 HTML 붙일 때 `section/` 또는 `page/`에만 추가

---

## 12. 수정하면 안 되는 파일

| 경로 | 이유 |
|------|------|
| `/bbs/`, `/lib/`, `/adm/` | 그누보드 코어, 업데이트 시 덮어씀 |
| `common.php` | 코어 진입점 |
| `/skin/board/basic/` | 그누보드 원본 스킨 |
| `/skin/board/gallery/` | 그누보드 원본 갤러리 스킨 |
| `data/dbconfig.php` | **서버별 DB 비밀번호** — 복사·공유 금지 |

---

## 13. 최종 검수 체크리스트

- [ ] 메인·서브 페이지 레이아웃
- [ ] GNB·모바일 메뉴·로그인·회원가입·관리자
- [ ] 게시판 10종 중 사용 스킨 목록/쓰기/보기/댓글
- [ ] 상담 모달 열기/닫기 (ESC, 바깥 클릭)
- [ ] 하단 floating 버튼 (전화·카카오·상담·TOP)
- [ ] 푸터 사업자 정보
- [ ] `custom.css` 색상·게시판 색 일치
- [ ] 모바일 768px 이하
- [ ] DB는 `dbconfig.local.php`로만 로컬 연결

---

## 전환 추적·404·완료 페이지

| 항목 | 설정·파일 |
|------|-----------|
| GTM·GA4·Meta·네이버·카카오 | `_site.config.php` → `gtm_id`, `ga4_id` 등 (비우면 미출력) |
| head 스크립트 | `components/tracking-head.php` ← `head.php` 자동 include |
| body GTM noscript | `components/tracking-body.php` ← `head.php` 직후 |
| 전환 이벤트 | `components/tracking-conversion.php` ← `/page/inquiry-thanks.php` |
| 문의 완료 URL | `inquiry_thanks_url` (기본 `/page/inquiry-thanks.php`) |
| 404 페이지 | `/page/404.php` — Apache 예: `ErrorDocument 404 /page/404.php` |

## 빌더 작업 폴더

- **`/_BUILDER_INPUT/`** — 빌더·React 결과물 **임시 보관** (운영 서버 업로드 제외 권장)
- **`/plugin/onoff-builder-bridge/`** — Vite **dist ZIP** 업로드 → `page.php?id=` 독립 랜딩 ([README](plugin/onoff-builder-bridge/README.md))
- **`src/App.tsx` 원본 ZIP**은 플러그인에 바로 적용되지 않음 → `npm run build` 후 dist만 업로드
- **`/page/style-guide.php`** — 개발용 스타일 가이드, **운영 전 숨김·삭제 권장**
- 적용 순서: [BUILDER-WORKFLOW.md](BUILDER-WORKFLOW.md) · Cursor: [START-PROJECT-PROMPTS.md](START-PROJECT-PROMPTS.md)

## 관련 문서

| 단계 | 문서 |
|------|------|
| 새 프로젝트 시작 | [START-PROJECT-PROMPTS.md](START-PROJECT-PROMPTS.md) |
| 게시판 생성 | [BOARD-CREATE-GUIDE.md](BOARD-CREATE-GUIDE.md), [setup/project.sample.json](setup/project.sample.json) |
| 빌더 적용 | [BUILDER-WORKFLOW.md](BUILDER-WORKFLOW.md), [SECTION-GUIDE.md](SECTION-GUIDE.md) |
| 게시판 | [BOARD-SKIN-GUIDE.md](BOARD-SKIN-GUIDE.md), [README-BOARD-SKINS.md](README-BOARD-SKINS.md) |
| 납품 전 | [LAUNCH-CHECKLIST.md](LAUNCH-CHECKLIST.md) |
| 보안 점검 | [SECURITY-CHECKLIST.md](SECURITY-CHECKLIST.md) |
| 백업 | [BACKUP-GUIDE.md](BACKUP-GUIDE.md) |
| 고객 전달 | [CLIENT-MANUAL.md](CLIENT-MANUAL.md) (요약) |
| 고객 운영 가이드·PDF | [docs/client/site-operation-guide-template.md](docs/client/site-operation-guide-template.md), [pdf-export-guide.md](docs/client/pdf-export-guide.md) |
| AI 작업 | [PROMPTS.md](PROMPTS.md) |
| 상담 폼·알림 | [INQUIRY-FORM-GUIDE.md](INQUIRY-FORM-GUIDE.md) |
| 이미지 최적화 | [IMAGE-GUIDE.md](IMAGE-GUIDE.md) |
| 성능 최적화 | [PERFORMANCE-GUIDE.md](PERFORMANCE-GUIDE.md) |
| 접근성 | [ACCESSIBILITY-GUIDE.md](ACCESSIBILITY-GUIDE.md) |
| 지역 SEO | [LOCAL-SEO-GUIDE.md](LOCAL-SEO-GUIDE.md), [page/local-template.php](page/local-template.php) |
| Google Maps | [MAP-GUIDE.md](MAP-GUIDE.md), [MAP-BUILDER-WORKFLOW.md](MAP-BUILDER-WORKFLOW.md), [page/map-locator.php](page/map-locator.php) |
| 빌더 dist ZIP | [plugin/onoff-builder-bridge/README.md](plugin/onoff-builder-bridge/README.md) — `page.php?id=` 독립 랜딩 |
| SEO 납품 | [SEO-CHECKLIST.md](SEO-CHECKLIST.md) |
| sitemap·robots | [SITEMAP-ROBOTS-GUIDE.md](SITEMAP-ROBOTS-GUIDE.md), `sitemap.sample.xml`, `robots.sample.txt` |
| 납품 전 AI 검수 | [CLEANUP-PROMPTS.md](CLEANUP-PROMPTS.md) |
| 복사·버전 | [_BASE_INFO/](_BASE_INFO/) |

- [README-BUILDER-TO-GNUBOARD.md](README-BUILDER-TO-GNUBOARD.md)
- [README-BOARD-CSS.md](README-BOARD-CSS.md)
- [SAMPLE-CONTENT.md](SAMPLE-CONTENT.md)
