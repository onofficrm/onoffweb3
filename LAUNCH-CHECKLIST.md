# 납품·오픈 전 최종 체크리스트

홈페이지 제작 완료 후 **고객 납품·라이브 오픈** 전에 확인합니다.  
복사 후 초기 설정: [_BASE_INFO/COPY-CHECKLIST.md](_BASE_INFO/COPY-CHECKLIST.md)

---

## 1. 기본 정보 확인

- [ ] **사이트명** — `_site.config.php` + 관리자 환경설정 제목 일치
- [ ] **로고** — `/img/logo/` 파일·`logo_path` 경로
- [ ] **전화번호** — 표기·`tel:` 링크·하단 버튼
- [ ] **카카오톡 링크** — `kakao_url` 실제 채널 URL
- [ ] **이메일** — 푸터·문의
- [ ] **주소** — 푸터·카카오/Google 지도 placeholder 또는 실지도
- [ ] **Google Maps** (사용 시) — `_site.config.php` `google_maps_api_key` · [MAP-GUIDE.md](MAP-GUIDE.md)
- [ ] **map-locator** — API 없음 placeholder · 키 있음 지도·마커·목록·위치 거부 fallback
- [ ] **map-location** 스킨 (사용 시) — wr_3·wr_4 좌표·글보기 지도
- [ ] **사업자정보** — 회사명, 대표, 사업자번호, 통신판매업(해당 시)
- [ ] **푸터 정보** — `footer_desc`, 개인정보책임자

---

## 2. PC 화면 확인

- [ ] **메인 첫 화면** — 히어로·이미지·CTA
- [ ] **섹션 간 여백** — 겹침·빈 공간 없음
- [ ] **버튼 클릭** — primary, outline, 전화, 카카오
- [ ] **이미지 깨짐** — 404·비율·플레이스홀더
- [ ] **폰트 크기** — 제목·본문 가독성
- [ ] **헤더** — GNB·로고·로그인·상담 CTA
- [ ] **PC 메뉴** — 1·2차 링크 클릭·404 없음 ([MENU-GUIDE.md](MENU-GUIDE.md))
- [ ] **모바일 메뉴** — 햄버거·동일 링크·긴 메뉴명 줄바꿈
- [ ] **2차 메뉴** — 과다 항목·접근성
- [ ] **게시판 메뉴** — URL `bo_table` ↔ 관리자 게시판 ID (FAQ·공지·칼럼 등)
- [ ] **서브페이지 메뉴** — `/page/*.php` 존재 확인
- [ ] **외부 링크** — `https://`·새창(`_blank`) 여부
- [ ] **개인정보처리방침** — `/page/privacy.php` (푸터·약관)
- [ ] **상담문의** — `/page/contact.php` vs CTA URL 일치
- [ ] **푸터** — 링크·사업자 정보
- [ ] **Scroll Snap** — 사용 시 PC에서만 동작·이탈 가능
- [ ] **onoff-builder-bridge** (사용 시) — dist ZIP만·`page.php?id=`·assets·콘솔 오류 없음
- [ ] 빌더 독립 페이지 — API 키·비밀번호 dist 포함 여부 확인

---

## 3. 모바일 화면 확인

- [ ] **1024px** — 태블릿 레이아웃
- [ ] **768px** — GNB·카드 1열
- [ ] **480px** — 좁은 화면·버튼 터치 영역
- [ ] **햄버거 메뉴** — 열기·닫기·링크
- [ ] **플로팅 버튼** — 가리지 않음·safe area
- [ ] **CTA 버튼** — full-width·간격
- [ ] **모달** — 상담 모달·팝업 — [ACCESSIBILITY-GUIDE.md](ACCESSIBILITY-GUIDE.md)
- [ ] **이미지 비율** — 잘림·늘어짐 없음

---

## 4. 게시판 확인 (사용 중인 게시판마다)

- [ ] **목록** 화면
- [ ] **글쓰기** 화면
- [ ] **내용보기** 화면
- [ ] **수정** / **삭제**
- [ ] **답변** (사용 시)
- [ ] **비밀글**
- [ ] **공지글**
- [ ] **첨부파일** 업·다운로드
- [ ] **검색**
- [ ] **분류** (`sca`)
- [ ] **페이지네이션**
- [ ] **모바일** 게시판 목록·보기
- [ ] **메뉴 연결** — GNB URL ↔ `bo_table` 일치

### 주요 게시판 유형별

- [ ] **inquiry** — 읽기 권한·비밀글·문의폼·알림 ([INQUIRY-FORM-GUIDE.md](INQUIRY-FORM-GUIDE.md))
- [ ] **faq** — 아코디언·FAQPage Schema·목록에서 내용 사용
- [ ] **column / blog** — SEO 스킨·썸네일·alt
- [ ] **portfolio / gallery** — 이미지·첨부
- [ ] **video / lecture** — `wr_1` 유튜브 URL·재생·썸네일

생성 기준: [BOARD-CREATE-GUIDE.md](BOARD-CREATE-GUIDE.md)

---

## 5. 게시판 스킨별 확인 (프로젝트에서 쓰는 스킨만)

- [ ] **basic-clean**
- [ ] **basic-modern**
- [ ] **basic-card**
- [ ] **basic-notice**
- [ ] **post-thumb**
- [ ] **post-media**
- [ ] **gallery-grid**
- [ ] **gallery-masonry**
- [ ] **youtube-list** (`wr_1` URL)
- [ ] **youtube-gallery**
- [ ] **landing-inquiry**
- [ ] **faq-accordion**

상세: [BOARD-SKIN-GUIDE.md](BOARD-SKIN-GUIDE.md)

---

## 6. 문의·전환 확인

- [ ] **전화 버튼** — `tel:` 연결
- [ ] **카카오톡 버튼** — 채널/오픈채팅
- [ ] **상담 모달** — `#cmpConsultModal` 열기·닫기·ESC
- [ ] **quick-contact** (사용 시)
- [ ] **bottom-cta**
- [ ] **개인정보 동의** — 문구·체크박스
- [ ] **inquiry/Q&A 게시판** — 글 저장·관리자 확인 (사용 시)

---

## 7. SEO 확인

- [ ] **title** — 페이지당 1개
- [ ] **meta description**
- [ ] **canonical**
- [ ] **OG title / description / image**
- [ ] **robots** — noindex 테스트 페이지 제외
- [ ] **sitemap** — [sitemap.sample.xml](sitemap.sample.xml) → `sitemap.xml`, 도메인 교체·제출
- [ ] **robots.txt** — [robots.sample.txt](robots.sample.txt) → `robots.txt`, [SITEMAP-ROBOTS-GUIDE.md](SITEMAP-ROBOTS-GUIDE.md)
- [ ] **JSON-LD** — 콘솔/리치 결과 오류 없음
- [ ] 관리자 **추가 메타**와 description **중복 없음**

---

## 8. 관리자 기능 확인

- [ ] **관리자 로그인**
- [ ] **메뉴** 수정·저장
- [ ] **게시판** 생성·수정·스킨 지정
- [ ] **게시글** 작성·수정·삭제
- [ ] **문의** 게시판 확인 (해당 시)
- [ ] **문의 완료 페이지** — `/page/inquiry-thanks.php` 이동·전화·카카오 버튼
- [ ] **텔레그램 알림** — `_site.config.php` 토큰·chat_id (테스트 후 확인)
- [ ] **전환 추적** — `gtm_id`, `ga4_id` 등 설정 시 완료 페이지 이벤트
- [ ] **404 페이지** — `/page/404.php` (서버 `ErrorDocument` 연결 여부)
- [ ] **환경설정** 접근

고객 안내: [CLIENT-MANUAL.md](CLIENT-MANUAL.md)

---

## 9. 보안·민감정보 확인

- [ ] **FTP·서버 계정** 문서/폴더에 노출 없음
- [ ] **DB 정보** (`data/dbconfig.php`) 복사·공유·업로드 제외
- [ ] **API 키** (카카오맵·텔레그램 봇·웹훅·광고 ID) 공개 저장소·클라이언트 노출 없음
- [ ] **고객 개인정보** 샘플·스크린샷에 포함 없음
- [ ] **테스트 계정** 정리·비밀번호 변경
- [ ] **`/page/style-guide.php`** — 삭제 또는 접근 차단
- [ ] **`/_BUILDER_INPUT`** — 운영 서버·FTP 업로드 **제외**
- [ ] **`setup/site.sample.json`** — 실고객 정보 없음

---

## 10. 고객 납품 문서 (운영 가이드·PDF)

- [ ] **고객용 사이트 운영 가이드** 작성 — [docs/client/site-operation-guide-template.md](docs/client/site-operation-guide-template.md)
- [ ] **사이트명·도메인·관리자 URL** `{{...}}` 치환 완료
- [ ] **샘플 문구**(`example.com`, 샘플 고객사 등) 제거
- [ ] 문서·PDF에 **비밀번호·FTP·DB·API** 정보 없음
- [ ] **PDF 변환** 완료 — [docs/client/pdf-export-guide.md](docs/client/pdf-export-guide.md)
- [ ] **관리자 계정** 고객에게 **별도** 전달 (PDF와 분리)
- [ ] **개인정보 관리**·유지보수 **연락처** 안내
- [ ] (선택) [CLIENT-MANUAL.md](CLIENT-MANUAL.md) 요약본 함께 전달

---

## 11. 최종 오픈 전 확인

- [ ] **브라우저 콘솔** — JS 오류 없음
- [ ] **PHP 오류** — error_log·화면 warning 없음
- [ ] **404** — CSS, JS, 이미지, 깨진 링크
- [ ] **이미지 경로** — `/img/main`, `/img/logo`, OG
- [ ] **링크** — GNB·푸터·CTA·게시판
- [ ] **개인정보처리방침** — `/page/privacy.php` 실내용·시행일
- [ ] **이용약관** — 필요 시 내용관리 `provision`
- [ ] **백업** — 파일·DB 백업 완료

---

## 관련 문서

| 문서 | 용도 |
|------|------|
| [SEO-CHECKLIST.md](SEO-CHECKLIST.md) | SEO 납품 체크리스트 |
| [SITEMAP-ROBOTS-GUIDE.md](SITEMAP-ROBOTS-GUIDE.md) | sitemap·robots·검색엔진 등록 |
| [CLEANUP-PROMPTS.md](CLEANUP-PROMPTS.md) | Cursor 납품 전 청소·검수 프롬프트 |
| [IMAGE-GUIDE.md](IMAGE-GUIDE.md) | 이미지 용량·크기·파일명 |
| [START-PROJECT-PROMPTS.md](START-PROJECT-PROMPTS.md) | Cursor 최종 검수 프롬프트 §9 |
| [CLIENT-MANUAL.md](CLIENT-MANUAL.md) | 고객·관리자 운영 안내 (요약) |
| [docs/client/site-operation-guide-template.md](docs/client/site-operation-guide-template.md) | 고객 납품용 운영 가이드·PDF 원본 |
| [docs/client/pdf-export-guide.md](docs/client/pdf-export-guide.md) | 운영 가이드 PDF 변환 |
| [BOARD-CREATE-GUIDE.md](BOARD-CREATE-GUIDE.md) | 게시판 생성·권한·스킨 |
| [setup/project.sample.json](setup/project.sample.json) | 게시판 계획 JSON |
| [_BASE_INFO/COPY-CHECKLIST.md](_BASE_INFO/COPY-CHECKLIST.md) | 복사 직후 설정 |
