# onoff-builder-bridge

빌더(Vite/React 등)에서 만든 **dist ZIP**을 그누보드에 올려, `page.php?id=` 형태의 **독립 페이지**로 출력하는 플러그인입니다.

- 그누보드 **코어·DB 테이블 수정 없음**
- 메타 저장: `data/imports.json` (JSON 파일만)
- 빌드 파일 저장: `imports/{프로젝트ID}/`

버전: `0.4.0-page-render`

---

## 1. 플러그인 목적

외부 빌더에서 export한 **빌드 결과물(dist)** 을 ZIP으로 업로드하면, 그누보드 `head.php` / `tail.php` 없이 **단독 HTML 페이지**로 서비스합니다.

랜딩·시안·캠페인 페이지를 빠르게 붙이거나 URL로 공유할 때 사용합니다.

---

## 2. 지원하는 것

| 항목 | 설명 |
|------|------|
| Vite/React **dist ZIP** | `npm run build` 후 생성된 폴더 내용 |
| **index.html + assets** | 루트 또는 `dist/` 하위 구조 |
| **page.php 출력** | `/plugin/onoff-builder-bridge/page.php?id=프로젝트ID` |
| **assets 경로 보정** | HTML 내 `src`/`href`의 `/assets/`, `assets/`, `./assets/` 등 |
| **관리 기능** | ZIP 업로드, 목록, 삭제 (최고관리자) |

---

## 3. 지원하지 않는 것

- `src/App.tsx` 등 **원본 React/Vite 프로젝트** 자동 변환
- React/Tailwind → 그누보드 **`section/*.php` 자동 이식**
- 그누보드 메인 **`index.php` 자동 교체**
- **SEO/OG** 메타 자동 설정
- **예쁜 URL**(rewrite) 자동 설정
- CSS 내부 **`url()`** 경로 자동 보정 (1차 버전)

메인·서브를 그누보드 레이아웃에 **완전 통합**하려면 베이스의 [BUILDER-WORKFLOW.md](../../BUILDER-WORKFLOW.md) 섹션 변환 워크플로를 사용하세요.

---

## 4. 사용 순서

1. 빌더에서 프로젝트 다운로드
2. `npm install`
3. `npm run build`
4. **`dist` 폴더 내용**을 ZIP으로 압축 (`index.html`, `assets/` 포함)
5. 관리자 → **ZIP 업로드** (프로젝트 ID·이름 지정)
6. **프로젝트 목록**에서 미리보기 URL 확인 → 메뉴·게시판에 연결

---

## 5. 관리자 접근

| 화면 | URL |
|------|-----|
| 관리 홈 | `/plugin/onoff-builder-bridge/admin/index.php` |
| ZIP 업로드 | `/plugin/onoff-builder-bridge/admin/upload.php` |
| 프로젝트 목록 | `/plugin/onoff-builder-bridge/admin/list.php` |

**권한:** 그누보드 **최고관리자** (`$is_admin === 'super'`) 만 접근·업로드·삭제 가능합니다.

---

## 6. 출력 URL

```
/plugin/onoff-builder-bridge/page.php?id=프로젝트ID
```

예: `page.php?id=sample-landing`

- `head.php` / `tail.php` **미사용** (독립 HTML)
- HTML은 `file_get_contents`로 읽어 출력 (ZIP 내 PHP **실행 안 함**)
- `imports/` 아래 `.html` 직접 URL 접근은 `.htaccess`로 차단 (page.php 경유)

---

## 7. 업로드 ZIP 조건

### 필수

- ZIP 안에 **`index.html`** (또는 `dist/index.html` 등 플러그인이 인식하는 위치)
- **`assets`** 폴더(빌드 산출물 JS/CSS/이미지)
- 확장자 **`.zip`만** 허용

### 금지·거부

| 내용 | 처리 |
|------|------|
| 원본 Vite 프로젝트 | `package.json` + `src/` + `vite.config.ts` + `App.tsx` 등 → 업로드 거부 |
| **PHP** (`.php`, `.phtml` 등) | ZIP 내 파일 거부 |
| **`.htaccess`**, `web.config` | ZIP 내 파일 거부 |
| **`../` 경로**(Zip Slip) | 거부 |
| **`.env`**, API 키, 비밀번호 | 포함하지 말 것 (업로드 전 반드시 확인) |

ZIP 루트 예시:

```
index.html
assets/
  index-xxxxx.js
  index-xxxxx.css
```

또는:

```
dist/
  index.html
  assets/
```

---

## 8. 자주 발생하는 문제

| 증상 | 원인 | 해결 |
|------|------|------|
| “React/Vite 원본 프로젝트” 안내 | `src/`·`vite.config.ts` ZIP 업로드 | `npm run build` 후 **dist만** ZIP |
| “index.html을 찾을 수 없습니다” | dist 미포함·잘못된 ZIP 구조 | `index.html`이 ZIP 안에 있는지 확인 |
| CSS/JS 404, 레이아웃 깨짐 | `assets` 누락·경로 불일치 | 빌드 dist 전체를 ZIP에 포함 |
| 업로드 버튼 비활성 | **ZipArchive** 미설치 | PHP `zip` 확장 활성화 |
| **흰 화면** | JS 오류·경로 오류 | 브라우저 개발자 도구 Network/Console 확인 |
| 이미지만 안 보임 | CSS `url()` 미보정 | 1차 버전 한계 — 빌드 시 상대 경로 조정 또는 추후 보완 |

---

## 9. 보안 주의사항

- **관리자만** 업로드·삭제 (일반 회원·비로그인 차단)
- **신뢰할 수 있는 빌드 결과물만** 업로드 (출처 불명 ZIP 금지)
- ZIP에 **API 키·DB 비밀번호·`.env`** 가 들어가지 않았는지 업로드 전 확인
- 공개 URL(`page.php?id=`)은 목록에 등록된 프로젝트면 접근 가능 — 민감 정보를 dist에 넣지 마세요

---

## 10. 향후 추가 가능 기능

- 프로젝트별 **SEO/OG** 입력
- **예쁜 URL** (rewrite 규칙)
- 게시판/페이지에 **iframe** 삽입 모드
- dist → **`section/*.php`** 변환 보조
- 업로드 **백업·버전 관리**

---

## 폴더 구조 (요약)

```
plugin/onoff-builder-bridge/
├── admin/          # 관리 화면 (업로드·목록·삭제)
├── assets/         # 관리자 CSS/JS
├── data/
│   └── imports.json
├── imports/        # 프로젝트별 dist 파일
├── lib/            # functions.php, importer.php
├── page.php        # 프론트 출력
└── bootstrap.php
```

---

## 관련 문서 (베이스)

| 문서 | 내용 |
|------|------|
| [BUILDER-WORKFLOW.md](../../BUILDER-WORKFLOW.md) | 빌더 → 그누보드 전체 적용 순서 |
| [README-START.md](../../README-START.md) | 베이스 시작·플러그인 위치 안내 |
