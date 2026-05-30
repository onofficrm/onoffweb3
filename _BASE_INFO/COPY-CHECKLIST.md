# 새 프로젝트 복사 체크리스트

onoff-g5-base 폴더를 **통째 복사**한 뒤, 아래 순서대로 확인하세요.  
상세 항목·JSON 매핑: [`../setup/replace-checklist.md`](../setup/replace-checklist.md)  
보안·백업: [`../SECURITY-CHECKLIST.md`](../SECURITY-CHECKLIST.md) · [`../BACKUP-GUIDE.md`](../BACKUP-GUIDE.md)

---

## 1. 파일 복사

- [ ] 베이스 폴더 전체를 새 작업 경로·서버에 복사
- [ ] macOS `._*` 임시 파일 삭제 (있을 경우): `find . -name '._*' -delete`
- [ ] **`data/dbconfig.php`는 다른 서버 설정을 그대로 쓰지 않음** — 새로 작성 또는 `dbconfig.local.php`만 로컬 사용
- [ ] `data/cache/`, 불필요한 세션·로그는 비우거나 서버에서 재생성
- [ ] Git 사용 시: `dbconfig.php`, `dbconfig.local.php`, `._*` 제외 규칙 적용

---

## 2. DB 설정 확인

- [ ] **자동댓글** — `plugin/auto_comment/` + `extend/auto_comment.extend.php` 포함 시 별도 `install.php` 불필요 (첫 접속 시 DB 자동 생성). 관리자 → **자동댓글 관리**에서 게시판·API 설정
- [ ] **iCRM final_url** — `icrm/` + `extend/icrm.extend.php` + `css/icrm-template.css`. 도메인은 **G5_URL** 자동. DB 직접 INSERT 시 글보기·API에서 `wr_seo_title` 보정. iCRM에 사이트별 `https://{도메인}/icrm/final-url.php` + 토큰 등록 ([icrm/README.md](../icrm/README.md))
- [ ] MySQL/MariaDB DB·계정 생성
- [ ] `data/dbconfig.php` (운영) 또는 `data/dbconfig.local.php` (로컬) 호스트·DB명·계정·비밀번호 입력
- [ ] 그누보드 설치 URL 접속 → 설치 완료 또는 DB import
- [ ] 관리자 계정 로그인 확인

---

## 3. data 권한 확인

- [ ] `data/` 및 하위 `cache/`, `session/`, `file/` 쓰기 권한 (일반적으로 707 또는 서버 권장값)
- [ ] 업로드·썸네일·에디터 이미지 저장 테스트

---

## 4. `_site.config.php` 수정

- [ ] `site_name`, `company_name`, `ceo_name`, `business_no`
- [ ] `phone`, `email`, `address`, `kakao_url`
- [ ] `primary_color`, `secondary_color`, `logo_path`, `og_image`
- [ ] `footer_desc`, `consultation_text`
- [ ] SEO: `seo_title`, `seo_description`, `main_keyword`, `robots` (선택)
- [ ] 문의: `inquiry_notify_email`, `inquiry_thanks_url`
- [ ] 텔레그램(선택): `inquiry_notify_telegram_enabled`, `bot_token`, `chat_id`
- [ ] 추적(선택): `gtm_id`, `ga4_id`, `meta_pixel_id`, `naver_analytics_id`, `kakao_pixel_id`
- [ ] 참고: [`setup/site.sample.json`](../setup/site.sample.json) → 키 매핑표

---

## 5. 로고 교체

- [ ] `/img/logo/` 에 SVG·PNG 업로드
- [ ] `_site.config.php` → `logo_path` 경로 일치
- [ ] 헤더·모바일에서 로고·텍스트 fallback 확인

---

## 6. 색상 교체

- [ ] `_site.config.php` → `primary_color`, `secondary_color` (head.php가 `:root`에 반영)
- [ ] 필요 시 `css/custom.css` `:root` 추가 조정
- [ ] 버튼·헤더 CTA·게시판(`.board-wrap`) 색 일치 확인

---

## 7. 메뉴 생성

- [ ] [setup/project.sample.json](../setup/project.sample.json) **`menus`** — 프로젝트에 맞게 수정 (계획표)
- [ ] [MENU-GUIDE.md](../MENU-GUIDE.md) · [MENU-EXAMPLES.md](../MENU-EXAMPLES.md) 참고
- [ ] 관리자 → 환경설정 → **메뉴설정** (게시판·서브페이지 준비 **후**)
- [ ] PC 메뉴(0), 모바일 메뉴(1) 등록
- [ ] `head.php` GNB·모바일 드로어 링크 동작·**active** 확인
- [ ] 게시판·서브페이지·외부 링크·개인정보·상담문의 URL 검수

---

## 8. 게시판 생성

- [ ] [BOARD-CREATE-GUIDE.md](../BOARD-CREATE-GUIDE.md) 기준 계획 수립
- [ ] [setup/project.sample.json](../setup/project.sample.json) `boards` — 프로젝트에 맞게 수정
- [ ] 관리자 → 게시판관리 → **수동 생성** (`bo_table` 중복 없음)
- [ ] 주요 게시판: `notice`, `column`, `portfolio`, `faq`, `inquiry` 등 (프로젝트별)
- [ ] 글쓰기·목록·권한·카테고리·비밀글 설정
- [ ] 샘플 글 등록 ([`SAMPLE-CONTENT.md`](../SAMPLE-CONTENT.md) 참고)
- [ ] `setup/tools/create-default-boards.example.php` — **운영 서버에서 실행 금지**

---

## 8-1. 빌더 dist ZIP (선택)

- [ ] [plugin/onoff-builder-bridge/README.md](../plugin/onoff-builder-bridge/README.md) 확인
- [ ] `npm run build` → dist ZIP (원본 src ZIP 아님)
- [ ] 관리자 `/plugin/onoff-builder-bridge/admin/` 업로드
- [ ] `page.php?id=` · 미리보기 · 삭제 테스트
- [ ] API 키·`.env` 미포함 확인

---

## 9. 게시판 스킨 선택

- [ ] 관리자 → 게시판관리 → **스킨 디렉토리** (PC)
- [ ] 모바일 스킨도 **동일 폴더명** (`mobile/skin/board/`)
- [ ] 13종 커스텀 스킨: … `faq-accordion`, **`map-location`** (장소·좌표 wr_1~wr_10)
- [ ] Google Maps (선택): [MAP-GUIDE.md](../MAP-GUIDE.md) · `google_maps_api_key` · `/page/map-locator.php` 테스트
- [ ] `inquiry` → `landing-inquiry` · `faq` → `faq-accordion` 확인
- [ ] 목록·보기·쓰기·댓글·모바일 레이아웃 확인

---

## 10. SEO 확인

- [ ] `components/seo-meta.php` 존재·`head.php` include 확인
- [ ] 페이지 소스: `<title>` 1개, description, canonical, OG, JSON-LD
- [ ] 관리자 **추가 메타태그**와 description **중복 없는지** 확인
- [ ] 메인·서브·게시판 title 적절 여부
- [ ] `$page_robots` / noindex 테스트 페이지 제외

---

## 11. 개인정보처리방침 확인

- [ ] **`/page/privacy.php`** — 회사명·책임자·연락처·시행일을 실제 정보로 수정 (법무 검토)
- [ ] 푸터 링크 `개인정보처리방침` → `/page/privacy.php` 동작 확인
- [ ] `consult-modal` 개인정보 동의 문구·수집 항목을 방침과 **일치**시키기
- [ ] `_site.config.php` → `privacy_manager` (선택)

---

## 12. 문의폼·상담 테스트

- [ ] 하단 **floating** — 전화·카카오·상담·TOP
- [ ] **상담 모달** (`#cmpConsultModal`) 열기·닫기·ESC
- [ ] `section/contact.php` 또는 1:1 문의 게시판 연동
- [ ] `quick-contact`, `bottom-cta` 문구·링크

---

## 13. 모바일 테스트

- [ ] 768px 이하 GNB·섹션·카드·게시판
- [ ] 모바일 게시판 스킨·목록 카드화
- [ ] 전화 `tel:` 링크·카카오 앱 연동
- [ ] (참고) PC `head.php` SEO·GNB와 모바일 `mobile/head.php` 차이 인지

---

## 14. 관리자 계정 확인

- [ ] 최고관리자 비밀번호 변경 (기본값 사용 금지)
- [ ] 불필요한 테스트 계정 삭제
- [ ] 관리자 URL 접근·권한

---

## 15. 불필요한 테스트·작업 폴더 제외

- [ ] **`/page/style-guide.php`** — 런칭 전 **삭제** 또는 웹서버 접근 차단
- [ ] **`/_BUILDER_INPUT/`** — 운영 서버·FTP 업로드 **제외** (작업용만)
- [ ] `robots.txt` / 사이트맵에 테스트 URL 없음
- [ ] `setup/site.sample.json`에 실제 고객 정보 남기지 않기
- [ ] 샘플 게시판·테스트 글 정리

---

## 16. 최종 검수 (권장)

상세 항목: **[`../LAUNCH-CHECKLIST.md`](../LAUNCH-CHECKLIST.md)**

- [ ] 메인 `index.php` 섹션 순서·이미지·문구
- [ ] 서브 `page/*.php` 레이아웃
- [ ] 스타일 가이드로 디자인 토큰·버튼·카드 1회 확인 (삭제 전)
- [ ] 크로스 브라우저: Chrome, Safari, iOS/Android
- [ ] OG 이미지·파비콘 (프로젝트별 추가)
- [ ] 고객 전달: [`../CLIENT-MANUAL.md`](../CLIENT-MANUAL.md)

---

## 17. 고객 납품 문서 (운영 가이드·PDF)

- [ ] [docs/client/site-operation-guide-template.md](../docs/client/site-operation-guide-template.md) 복사 → 프로젝트명 파일로 저장
- [ ] `{{SITE_NAME}}`, `{{DOMAIN}}`, `{{ADMIN_URL}}` 등 치환
- [ ] 샘플·`example.com` 문구 제거
- [ ] 비밀번호·FTP·DB 정보 **미포함** 확인
- [ ] [pdf-export-guide.md](../docs/client/pdf-export-guide.md) 참고 PDF 변환
- [ ] 관리자 계정 **별도** 전달

---

## 문서 읽는 순서 (새 프로젝트)

1. **[`VERSION.md`](VERSION.md)** — 버전·포함 기능·환경
2. **[`../START-PROJECT-PROMPTS.md`](../START-PROJECT-PROMPTS.md)** — Cursor 순서 (복사 직후)
3. **[`COPY-CHECKLIST.md`](COPY-CHECKLIST.md)** — 본 문서 (복사 후 작업)
4. **[`../README-START.md`](../README-START.md)** — 상세 시작 가이드
5. **[`../setup/replace-checklist.md`](../setup/replace-checklist.md)** — JSON·항목별 교체
6. **[`FILE-STRUCTURE.md`](FILE-STRUCTURE.md)** — 어디를 수정할지
7. **[`DO-NOT-EDIT.md`](DO-NOT-EDIT.md)** — 손대면 안 되는 곳
8. 빌더: `/_BUILDER_INPUT/` → [BUILDER-WORKFLOW.md](../BUILDER-WORKFLOW.md), [SECTION-GUIDE.md](../SECTION-GUIDE.md)
9. 목적별: `presets/` · 작업: [PROMPTS.md](../PROMPTS.md)
10. 납품: [LAUNCH-CHECKLIST.md](../LAUNCH-CHECKLIST.md), [CLEANUP-PROMPTS.md](../CLEANUP-PROMPTS.md), [IMAGE-GUIDE.md](../IMAGE-GUIDE.md) · 고객: [CLIENT-MANUAL.md](../CLIENT-MANUAL.md), [docs/client/](../docs/client/)

---

## 관련 문서

- [`VERSION.md`](VERSION.md)
- [`DO-NOT-EDIT.md`](DO-NOT-EDIT.md)
- [`FILE-STRUCTURE.md`](FILE-STRUCTURE.md)
