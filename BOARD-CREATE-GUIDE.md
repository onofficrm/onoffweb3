# 게시판 생성 가이드

새 프로젝트(onoff-g5-base)에서 **그누보드 관리자**를 통해 기본 게시판을 안전하게 만드는 방법입니다.

| 관련 문서 | 용도 |
|-----------|------|
| [BOARD-SKIN-GUIDE.md](BOARD-SKIN-GUIDE.md) | 스킨 선택·적용 |
| [setup/project.sample.json](setup/project.sample.json) | 게시판 계획 JSON (DB 미반영) |
| [BOARD-CREATE-PROMPTS.md](BOARD-CREATE-PROMPTS.md) | Cursor 요청 프롬프트 |
| [setup/tools/create-default-boards.example.php](setup/tools/create-default-boards.example.php) | 개발자 참고용 예시 (실행 차단) |

> **1차 권장:** 관리자 화면에서 **수동 생성**  
> **2차 참고:** `project.sample.json` + 예시 스크립트 (자동화는 개발 환경·백업 후에만)

---

## 1. 문서 목적

- 새 프로젝트 시작 시 자주 쓰는 게시판을 **빠르게·일관되게** 만들기 위한 기준
- `bo_table` ID, 스킨, 권한을 미리 정해 두고 작업자·Cursor가 동일하게 적용
- **운영 DB를 스크립트로 즉시 수정하지 않음** — 관리자 수동 생성이 기본

---

## 2. 관리자에서 게시판 생성하는 기본 순서

1. **관리자 로그인** — `https://도메인/adm`
2. **게시판관리** 메뉴 이동
3. **게시판 추가** 클릭
4. **게시판 테이블명** (`bo_table`) 입력 — 영문 소문자·숫자·`_` (한 번 정하면 변경 어려움)
5. **게시판 제목** 입력
6. **스킨 디렉토리** (PC) · **모바일 스킨** (PC와 **동일 이름** 권장)
7. **권한** — 목록·읽기·쓰기·댓글·다운로드 레벨 설정
8. **분류·비밀글·댓글·공지** 등 옵션 설정
9. **저장**
10. **메뉴관리**에서 해당 게시판 URL 연결
11. **PC·모바일** 브라우저에서 목록·글쓰기·글보기 확인

---

## 3. 추천 기본 게시판 목록

| bo_table | 게시판 제목 | 추천 스킨 | 용도 | 목록/읽기 | 쓰기 |
|----------|-------------|-----------|------|-----------|------|
| `notice` | 공지사항 | `basic-notice` | 공지, 안내, 업데이트 | 전체(1) | 관리자(10) |
| `news` | 뉴스 | `basic-modern` | 회사·병원·학원 소식 | 전체(1) | 관리자(10) |
| `column` | 칼럼 | `post-thumb` | SEO 글, 정보글, 블로그형 | 전체(1) | 관리자(10) |
| `blog` | 블로그 | `post-media` | 매거진형, 브랜드 스토리 | 전체(1) | 관리자(10) |
| `portfolio` | 포트폴리오 | `gallery-grid` | 제작·시공 사례 | 전체(1) | 관리자(10) |
| `gallery` | 갤러리 | `gallery-masonry` | 시설·현장 사진 | 전체(1) | 관리자(10) |
| `video` | 영상 | `youtube-gallery` | 유튜브 영상 허브 | 전체(1) | 관리자(10) |
| `lecture` | 강의영상 | `youtube-list` | 강의·인터뷰 영상 | 전체(1) | 관리자(10) |
| `review` | 후기 | `basic-card` | 고객·수강 후기 | 전체(1) | 관리자(2~) |
| `faq` | 자주 묻는 질문 | `faq-accordion` | FAQ, 비용·절차 | 전체(1) | 관리자(10) |
| `inquiry` | 상담문의 | `landing-inquiry` | 문의 저장·상담 관리 | 관리자(10) | 비회원(1) |
| `free` | 자유게시판 | `basic-clean` | 일반 게시판 | 정책에 따름 | 회원(2) 권장 |

> 레벨 숫자는 그누보드 기본(1=비회원, 2=회원, 10=관리자 등). 프로젝트 정책에 맞게 조정하세요.

---

## 4. 권한 설정 추천

| 유형 | 목록/읽기 | 쓰기 | 비밀글 | 비고 |
|------|-----------|------|--------|------|
| **공개 콘텐츠** | 1 (전체) | 10 (관리자) | 선택 | notice, news, column, portfolio … |
| **관리자 전용** | 10 | 10 | 사용 | 내부 공지·초안 (필요 시) |
| **문의 게시판** | 10 | 1 | **사용 권장** | inquiry — 개인정보 보호 |
| **회원 참여** | 1 | 2 (회원) | 선택 | review, free |

---

## 5. 게시판별 세부 추천 설정

### 공지사항 (`notice` + `basic-notice`)

- **공지글** 사용
- **댓글** 미사용 권장
- 분류는 선택

### 칼럼·블로그 (`column`, `blog`)

- SEO 콘텐츠용 — 제목·첫 문단에 키워드
- **댓글** 선택
- 이미지 **alt** 작성 권장 (`post-thumb` / `post-media` SEO 스킨)

### 포트폴리오·갤러리 (`portfolio`, `gallery`)

- **첨부 이미지** 사용 권장
- 이미지 **용량·가로 크기** 최적화 ([IMAGE-GUIDE.md](IMAGE-GUIDE.md))
- **목록에서 내용 사용** — 카드형 요약에 유리

### 유튜브 (`video`, `lecture`)

- 글쓰기 **여분필드 `wr_1`** 에 유튜브 URL
- 지원: `watch?v=`, `youtu.be/`, `shorts/`
- [BOARD-SKIN-GUIDE.md](BOARD-SKIN-GUIDE.md) §4 참고

### FAQ (`faq` + `faq-accordion`)

- **제목 = 질문**, **본문 = 답변**
- **분류** 사용 권장 (서비스·비용·절차 등)
- **목록에서 내용 사용** — 아코디언에 답변 표시
- 댓글 미사용 권장

### 문의 (`inquiry` + `landing-inquiry`)

- **개인정보** 포함 — 읽기 권한 **관리자** 권장
- **비밀글** 사용 권장
- `_site.config.php` 문의 알림·`proc/inquiry-submit.php` 연동 확인 ([INQUIRY-FORM-GUIDE.md](INQUIRY-FORM-GUIDE.md))
- 댓글 미사용 권장

### 자유게시판 (`free` + `basic-clean`)

- 사이트 정책에 따라 회원 쓰기·비밀글·댓글 설정

---

## 6. 메뉴 연결 방법

게시판 생성·`bo_table` 확정 **이후** [MENU-GUIDE.md](MENU-GUIDE.md)를 참고해 GNB에 연결하세요.  
메뉴 계획은 [setup/project.sample.json](setup/project.sample.json)의 `menus`와 [MENU-EXAMPLES.md](MENU-EXAMPLES.md)를 사용합니다.

1. 관리자 → **환경설정** → **메뉴설정** (또는 메뉴관리)
2. PC·모바일 메뉴에 항목 추가
3. 링크 예시:

| 게시판 | URL 예시 |
|--------|----------|
| 공지 | `/bbs/board.php?bo_table=notice` |
| 칼럼 | `/bbs/board.php?bo_table=column` |
| 포트폴리오 | `/bbs/board.php?bo_table=portfolio` |
| FAQ | `/bbs/board.php?bo_table=faq` |
| 문의 | `/bbs/board.php?bo_table=inquiry` |

4. 저장 후 GNB에서 클릭·모바일 메뉴 확인

---

## 7. 새 프로젝트별 추천 조합

### 기본 회사형

`notice` · `news` · `portfolio` · `column` · `inquiry`

### SEO 콘텐츠형

`column` · `blog` · `news` · `faq` · `inquiry`

### 랜딩·상담형

`review` · `faq` · `inquiry` · (`news` 선택)

### 포트폴리오형

`portfolio` · `gallery` · `video` · `inquiry`

### 교육·학원형

`notice` · `lecture` · `faq` · `review` · `inquiry`

> 실제 프로젝트에 맞게 `setup/project.sample.json`의 `boards` 배열에서 **필요한 것만** 남기세요.

---

## 8. 게시판 생성 후 체크리스트

각 `bo_table`마다 확인:

- [ ] 목록 페이지 (`/bbs/board.php?bo_table=ID`)
- [ ] 글쓰기 (권한에 맞는 계정)
- [ ] 내용보기
- [ ] 수정·삭제 (관리자)
- [ ] 모바일 레이아웃
- [ ] 메뉴 연결·활성 표시
- [ ] 권한 (비회원/회원/관리자)
- [ ] 비밀글 (inquiry 등)
- [ ] 첨부파일 업·다운로드
- [ ] 문의 알림 (inquiry)
- [ ] FAQ 아코디언·Schema (faq)
- [ ] 유튜브 URL·재생 (video, lecture)

상세: [LAUNCH-CHECKLIST.md](LAUNCH-CHECKLIST.md) §4~5

---

## 9. 주의사항

| 주의 | 설명 |
|------|------|
| **bo_table 중복** | 이미 있는 테이블명으로 새로 만들지 않음 |
| **테이블명 변경** | 운영 중 `bo_table` 변경은 URL·DB 모두 영향 — 가급적 금지 |
| **개인정보 게시판** | inquiry 읽기 권한·비밀글·알림 설정 재확인 |
| **원본 스킨 수정 금지** | `skin/board/basic`, `gallery` 원본 수정하지 않음 |
| **코어 수정 금지** | `/bbs`, `/lib`, `/adm` 수정하지 않음 |
| **예시 스크립트** | `create-default-boards.example.php` 운영 서버에서 실행 금지 |
| **DB 직접 INSERT** | 백업 없이 SQL로 게시판 추가하지 않음 |

---

## 10. JSON·자동화 참고

### `setup/project.sample.json`

- 프로젝트명·도메인·`boards[]` 계획표
- Cursor·작업자가 **어떤 게시판을 만들지** 참고
- **파일을 수정해도 DB에는 자동 반영되지 않음**

### `setup/tools/create-default-boards.example.php`

- 개발자 참고용 · **상단 `exit`로 실행 차단**
- 복사·개선 후 **개발/staging**에서만, **DB 백업 후** 검토
- `g5_write_{bo_table}` 테이블 생성은 관리자 로직과 일치하는지 반드시 확인

---

*onoff-g5-base · 게시판은 관리자 수동 생성을 기본으로 합니다.*
