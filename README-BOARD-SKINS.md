# 게시판 스킨 운영·테스트 가이드

그누보드 5 관리자에서 **커스텀 게시판 스킨 10종**을 선택·테스트·운영하는 방법을 정리한 문서입니다.

- 스킨 경로 (PC): `/skin/board/스킨명/`
- 스킨 경로 (모바일): `/mobile/skin/board/스킨명/` (PC CSS 재사용)
- 공통 CSS: `/css/g5b-board.css` · 디자인 토큰: `/css/custom.css`
- CSS 상세: [README-BOARD-CSS.md](README-BOARD-CSS.md)

---

## 1. 게시판 스킨 10개 목록

| # | 스킨 디렉토리 | 목록 형태 | 썸네일/미디어 |
|---|---------------|-----------|----------------|
| 1 | `basic-clean` | 테이블 (PC) → 카드 (모바일) | 없음 |
| 2 | `basic-modern` | 테이블 (날짜·분류 강조) | 없음 |
| 3 | `basic-card` | 카드 그리드 (3열) | 없음 (요약문) |
| 4 | `basic-notice` | 공지/일반 섹션 분리 테이블 | 없음 |
| 5 | `post-thumb` | 썸네일 + 텍스트 목록 | 첨부·본문 이미지 |
| 6 | `post-media` | 대형 썸네일 목록 | 첨부·본문 이미지 |
| 7 | `gallery-grid` | 이미지 그리드 (4→1열) | 첨부·본문 이미지 |
| 8 | `gallery-masonry` | Masonry 열 + 오버레이 제목 | 첨부·본문 이미지 |
| 9 | `youtube-list` | 유튜브 목록형 | `wr_1` URL |
| 10 | `youtube-gallery` | 유튜브 카드 그리드 | `wr_1` URL |

필수 스킨 파일 (각 디렉토리): `list.skin.php`, `write.skin.php`, `view.skin.php`, `view_comment.skin.php`, `style.css`

---

## 2. 스킨별 추천 용도

| 스킨 | 추천 용도 | 특징 |
|------|-----------|------|
| **basic-clean** | 기본 게시판, Q&A, 자료실(텍스트 위주) | 가장 단순한 기준형. 다른 스킨의 공통 베이스 |
| **basic-modern** | 회사 소식, IR, 보도자료 | 넓은 여백, 날짜·분류 시각 강조 |
| **basic-card** | FAQ, 후기, 칼럼 요약, 이벤트 | 카드형, 본문 앞부분 요약 노출 |
| **basic-notice** | 공지사항, 자료실(공지+일반) | 상단 공지 블록 + 일반 글 구분 |
| **post-thumb** | 블로그 목록, 칼럼, 뉴스 피드 | 작은 썸네일 + 제목·요약 |
| **post-media** | 매거진형 블로그, 인터뷰 | 큰 썸네일 + 메타 정보 |
| **gallery-grid** | 포트폴리오, 제품 갤러리 | 균등 그리드, 대표 이미지 중심 |
| **gallery-masonry** | 사진 갤러리, 전시 | 높이 다른 이미지, 제목 오버레이 |
| **youtube-list** | 강의 목록, 영상 아카이브 | 한 줄에 썸네일+제목+요약 |
| **youtube-gallery** | 영상 채널, 미디어 허브 | 카드형, 재생 아이콘 오버레이 |

---

## 3. 관리자에서 게시판 스킨 변경 방법

### 3-1. PC 스킨

1. **관리자** 로그인 (`/adm`)
2. **게시판관리** → **게시판 목록** (`board_list.php`)
3. 변경할 게시판 **「수정」** 클릭
4. **「기본 설정」** 탭 (또는 상단 탭 중 스킨 항목이 있는 영역)
5. **스킨 디렉토리** (`bo_skin`) 드롭다운에서 스킨 선택  
   - 예: `basic-modern`, `gallery-grid`, `youtube-list`
6. **「확인」** 저장

### 3-2. 모바일 스킨

같은 수정 화면에서 **모바일 스킨 디렉토리** (`bo_mobile_skin`)에 **PC와 동일한 디렉토리명**을 선택하는 것을 권장합니다.

| PC 스킨 | 모바일 스킨 (권장) |
|---------|-------------------|
| `post-thumb` | `post-thumb` |
| `youtube-gallery` | `youtube-gallery` |

> 모바일 스킨 CSS는 PC `style.css`를 `@import` 하므로, 이름만 맞추면 반응형이 동일하게 적용됩니다.

### 3-3. 스킨 적용 후 확인

- 해당 게시판 **목록 URL** 접속 (`/bbs/board.php?bo_table=게시판ID`)
- 브라우저 캐시가 남아 있으면 **강력 새로고침** (Ctrl+Shift+R / Cmd+Shift+R)
- 스킨 목록에 이름이 안 보이면 `skin/board/` 폴더명과 디렉토리명이 일치하는지 확인

### 3-4. 카드·썸네일 스킨 권장 게시판 설정

관리자 → 게시판 수정 → **기능** / **목록** 관련 항목:

| 설정 | 권장 | 이유 |
|------|------|------|
| **목록에서 내용 사용** (`bo_use_list_content`) | ✅ 사용 | `basic-card`, `post-thumb`, `post-media` 등 목록 요약에 `list_content` 사용 |
| **분류 사용** | 용도에 따라 | `basic-modern`, `basic-notice` 등 분류 탭 노출 |
| **검색 사용** | ✅ 사용 | 스킨 공통 검색 모달 |
| **파일 업로드** | 이미지 스킨 필수 | `post-*`, `gallery-*` 썸네일 생성 |

---

## 4. 추천 게시판 예시

| 용도 | 게시판 ID 예시 | 추천 스킨 | 비고 |
|------|----------------|-----------|------|
| 공지사항 | `notice` | **basic-notice** | 공지 상단 고정 UI |
| 회사 소식 | `news` | **basic-modern** | 날짜·분류 강조 |
| 칼럼 | `column` | **post-thumb** | 작은 썸네일 + 요약 |
| 블로그 | `blog` | **post-media** | 큰 이미지 중심 |
| 포트폴리오 | `portfolio` | **gallery-grid** | 균등 그리드 |
| 사진 갤러리 | `gallery` | **gallery-masonry** | 비율 다른 이미지 |
| 영상 허브 | `video` | **youtube-gallery** | 카드형 채널 느낌 |
| 강의·세미나 | `lecture` | **youtube-list** | 목록형 아카이브 |
| FAQ | `faq` | **basic-card** | (또는 추후 FAQ 전용 스킨) |
| 이용후기 | `review` | **basic-card** 또는 **post-thumb** | 짧은 후기는 card, 사진 후기는 thumb |
| 일반 게시판 | `free` | **basic-clean** | 기본형 |

게시판을 새로 만들 때: **게시판관리 → 게시판 추가** 후 위 표와 같이 스킨만 지정하면 됩니다.

---

## 5. 유튜브 게시판 사용 방법

대상 스킨: **`youtube-list`**, **`youtube-gallery`**

### 5-1. 입력 필드

| 필드 | 용도 | 필수 |
|------|------|------|
| **wr_1** | 유튜브 영상 URL | ✅ 필수 (글쓰기 폼에서 검증) |
| **wr_2** | 영상 설명 / 요약 | 선택 (목록·글보기 요약) |
| **wr_subject** | 제목 | ✅ |
| **wr_content** | 본문 (추가 설명) | 선택 |

글쓰기 화면에 「유튜브 URL」「영상 설명 / 요약」 입력란이 표시됩니다.

### 5-2. 지원 URL 형식

다음 형식에서 **11자리 영상 ID**를 추출합니다.

| 형식 | 예시 |
|------|------|
| watch | `https://www.youtube.com/watch?v=dQw4w9WgXcQ` |
| watch (파라미터 순서 무관) | `https://www.youtube.com/watch?feature=share&v=dQw4w9WgXcQ` |
| 단축 URL | `https://youtu.be/dQw4w9WgXcQ` |
| embed | `https://www.youtube.com/embed/dQw4w9WgXcQ` |
| Shorts | `https://www.youtube.com/shorts/dQw4w9WgXcQ` |
| ID만 직접 입력 | `dQw4w9WgXcQ` (11자, `A-Za-z0-9_-`) |

### 5-3. 잘못된 URL일 때 처리

| 상황 | 목록 | 글보기 |
|------|------|--------|
| URL 없음 / 형식 오류 | `/img/common/no-youtube.svg` 또는 빈 썸네일 박스 | 안내 문구 + 지원 형식 힌트 (`board-yt-fallback`) |
| 비밀글 | 자물쇠 아이콘 (썸네일 미노출) | 권한에 따라 본문 제한 |
| ID 추출 성공 | YouTube `img.youtube.com/vi/{ID}/hqdefault.jpg` | `youtube-nocookie.com/embed/{ID}` iframe |

**본문(`wr_content`)에 embed/링크가 있을 때** `wr_1`이 비어 있으면 본문에서 ID를 **보조 추출**합니다. 가능하면 **wr_1에 URL을 등록**하는 것을 권장합니다.

### 5-4. 운영 팁

- 게시판 스킨: PC·모바일 모두 `youtube-list` 또는 `youtube-gallery`
- 목록이 카드형이면 **youtube-gallery**, 텍스트 목록이면 **youtube-list**
- 영상 설명은 **wr_2**에 짧게 작성하면 목록 가독성이 좋아집니다

---

## 6. 이미지 게시판 사용 방법

대상 스킨: **`post-thumb`**, **`post-media`**, **`gallery-grid`**, **`gallery-masonry`**

### 6-1. 썸네일 우선순위

`get_list_thumbnail()` (그누보드 코어) 기준:

1. **첨부 이미지** (게시판 파일 업로드)
2. **본문(`wr_content`) 첫 번째 이미지**
3. 없을 때 → fallback (아래)

### 6-2. 첨부 이미지 등록

1. 게시판 설정에서 **파일 업로드 개수·용량** 허용
2. 글쓰기 시 **파일 첨부**로 JPG/PNG/WebP 등 업로드
3. 갤러리 스킨은 **대표 이미지 1장**이 목록 썸네일로 쓰이는 경우가 많음 — 첫 번째 이미지 파일을 대표로 사용

### 6-3. 본문 첫 이미지

- 에디터로 본문에 이미지를 넣으면, 첨부가 없어도 목록 썸네일로 사용될 수 있습니다.
- **외부 URL 이미지**만 있고 첨부가 없으면 환경에 따라 썸네일 생성이 안 될 수 있으므로, 운영 시에는 **첨부 이미지 등록을 권장**합니다.

### 6-4. 이미지 없을 때 fallback

| 단계 | 표시 |
|------|------|
| 1 | `/img/common/no-image.svg` (NO IMAGE) |
| 2 | SVG 파일 없음 | 회색 빈 박스 (`.board-thumb--empty`) |
| 비밀글 | 자물쇠 아이콘, 이미지 미노출 |
| 공지 (이미지 없음) | 「공지」 뱃지 박스 |

### 6-5. 스킨별 이미지 권장

| 스킨 | 권장 이미지 비율 |
|------|------------------|
| post-thumb | 가로형, 약 4:3 (목록 96×72 영역) |
| post-media | 16:9 또는 가로형 큰 이미지 |
| gallery-grid | 정사각형~4:3 통일 시 그리드가 깔끔함 |
| gallery-masonry | 세로·가로 혼합 가능 |

---

## 7. 테스트 체크리스트

게시판 스킨을 변경한 뒤, **PC와 모바일** 각각 아래 항목을 확인하세요.

### 7-1. 기본 기능

- [ ] **목록** — 레이아웃·공지·빈 목록 메시지
- [ ] **글쓰기** — 필수 항목, 유튜브 URL(해당 스킨), 파일 첨부
- [ ] **내용보기** — 제목, 메타(작성자·날짜·조회), 본문·이미지
- [ ] **수정** — 기존 값 유지, 저장
- [ ] **삭제** — 목록 반영
- [ ] **답변** — 답변글 목록·보기 (답변 사용 게시판)
- [ ] **댓글** — 작성·수정·삭제·비밀댓글

### 7-2. 권한·특수 글

- [ ] **비밀글** — 목록 썸네일/요약 숨김, 보기 권한
- [ ] **공지글** — 상단 노출·스타일 (`basic-notice`는 섹션 분리 확인)

### 7-3. 부가 기능

- [ ] **첨부파일** — 다운로드·이미지 썸네일 연동
- [ ] **검색** — 검색 버튼 → 모달 → 결과
- [ ] **분류** — 분류 탭·필터 (사용 시)
- [ ] **페이지네이션** — 이전·다음·번호, 현재 페이지 강조

### 7-4. 스킨별 추가

- [ ] **유튜브** — watch / youtu.be / shorts URL 각 1건
- [ ] **유튜브 오류 URL** — fallback 문구·NO VIDEO 이미지
- [ ] **이미지** — 첨부만 / 본문만 / 없음 fallback
- [ ] **카드·갤러리** — PC 다열 → 모바일 1~2열 줄바꿈

### 7-5. 모바일

- [ ] **767px 이하** — 테이블→카드(basic 계열), 그리드 열 수
- [ ] **터치** — 글쓰기·검색·페이지 버튼 크기
- [ ] **모바일 스킨명** — PC와 동일 디렉토리 적용 여부

### 7-6. 회귀 테스트 (다른 화면 깨짐 없음)

- [ ] 그누보드 **기본 스킨(`basic`)** 게시판 — 기존과 동일
- [ ] 메인·커스텀 페이지 (`custom.css`) — 레이아웃 이상 없음

---

## 8. 주의사항

### 8-1. 수정 금지 (코어·원본)

| 경로 | 설명 |
|------|------|
| `/bbs/`, `/lib/`, `/adm/` | 그누보드 **코어** — 업데이트 시 덮어씌워짐 |
| `common.php`, `theme/` | 샘플 템플릿 정책상 코어급 취급 |
| `/skin/board/basic/` (그누보드 기본) | **원본 basic 스킨** 수정 금지 |
| `/skin/board/gallery/` (그누보드 기본) | 원본 갤러리 스킨 수정 금지 |

게시판 디자인·기능 변경은 **`/skin/board/스킨명/`** (및 `mobile/skin/board/스킨명/`) 안에서만 하세요.

### 8-2. 스킨·CSS 구조

- 공통 스타일: `/css/g5b-board.css` (`.board-wrap` 스코프)
- 스킨별: `skin/board/스킨명/style.css` 만 수정
- 사이트 전역 색·폰트: `/css/custom.css` `:root` 토큰

### 8-3. 개인정보·법적 고지

- 회원가입, 문의, 예약 등 **개인정보를 받는 게시판**은 글쓰기 폼에 **개인정보 수집·이용 동의** 문구를 반드시 포함하세요. (스킨 기본 폼에는 포함되어 있지 않을 수 있음)
- 관리자는 **개인정보처리방침** 페이지 링크·보관 기간 안내를 별도 운영 정책에 맞게 설정합니다.

### 8-4. 업그레이드·배포

- 그누보드 버전 업그레이드 후 **10개 스킨만** 백업·복원하고, 코어는 공식 패키지로 교체
- `_inc/g5b-thumb.php`, `g5b-youtube.php` 등 공통 include는 스킨 간 공유 — 삭제 시 여러 스킨 동시 오류

---

## 9. 디자인 색상 변경 방법

홈페이지(빌더)마다 **게시판 스킨 10종을 새로 만들지 않고**, `/css/custom.css`의 `:root` 토큰만 바꾸면 게시판 분위기가 맞춰집니다.

### 9-1. 변경 위치 (우선순위)

| 순서 | 파일 | 역할 |
|------|------|------|
| 1 | **`/css/custom.css` `:root`** | 브랜드 색·배경·라운드·그림자 (필수) |
| 2 | `/css/g5b-board.css` | 버튼·검색·페이징·글보기·글쓰기 공통 (토큰 참조, 직접 수정 최소화) |
| 3 | `skin/board/스킨명/style.css` | 레이아웃·그리드·유튜브/갤러리 전용 (토큰 참조) |

`head.php`가 `custom.css`를 먼저 로드하고, 게시판 접속 시 각 스킨 `style.css` → `g5b-board.css`가 이어집니다.

### 9-2. 자주 바꾸는 토큰

```css
:root {
  --color-primary: #2563eb;      /* 버튼·공지·링크 hover·페이지 현재 */
  --color-primary-hover: #1d4ed8;
  --color-secondary: #64748b;    /* 보조 텍스트·유튜브 그라데이션 끝 */
  --color-bg: #ffffff;           /* 카드·입력 배경 */
  --color-surface: #f8fafc;     /* 테이블 헤더·호버·빈 영역 */
  --color-text: #1e293b;         /* 본문·제목·테이블 상단선 */
  --color-muted: #64748b;        /* 날짜·조회·요약 */
  --color-line: #e2e8f0;         /* 테두리·구분선 */
  --color-on-primary: #ffffff;   /* primary 버튼 글자 */
  --radius-sm: 4px;
  --radius-md: 8px;
  --radius-lg: 12px;
  --shadow-soft: 0 4px 24px rgba(15, 23, 42, 0.08);
  --container: 1200px;           /* 사이트 레이아웃 (게시판 폭은 bo_table 설정) */
}
```

### 9-3. 게시판 전용 시맨틱 토큰 (`custom.css`)

유튜브·갤러리 오버레이 등에 쓰입니다. **사이트 primary만 바꿔도** 함께 따라갑니다.

| 토큰 | 용도 |
|------|------|
| `--board-media-bg` | 유튜브 썸네일·embed 배경 (= `--color-text`) |
| `--board-media-gradient-end` | 빈 영상 박스 그라데이션 끝 |
| `--board-on-media` | 어두운 위 텍스트·재생 아이콘 (= `--color-on-primary`) |
| `--board-overlay-scrim` / `--board-overlay-scrim-light` | 재생 오버레이·검색 백드롭 |
| `--board-overlay-gradient-end` / `--mid` | masonry 제목 오버레이 |
| `--board-overlay-on-media` | 오버레이 위 뱃지 배경 |
| `--shadow-elevated` / `--shadow-elevated-lg` | 카드·갤러리 hover 그림자 |
| `--color-accent-new` | NEW 뱃지 |
| `--color-danger` | 필수 입력 표시 |

### 9-4. 사이트별 적용 예

1. 빌더에서 추출한 **primary / 배경 / 본문색**을 `:root`에 반영
2. 게시판 목록·글보기 한 페이지 확인 (버튼·공지·검색 모달)
3. **유튜브·갤러리** 스킨 1개씩 추가 확인 (어두운 썸네일·오버레이)
4. 문제 없으면 나머지 게시판은 스킨 디렉토리만 지정

### 9-5. 스킨별로 건드리지 않아도 되는 것

- `basic-clean` ~ `basic-notice`: 거의 전부 `g5b-board.css`만 사용
- `post-thumb` / `post-media`: 썸네일 크기·그리드만 스킨 CSS, 색은 토큰
- `youtube-list` / `youtube-gallery`: `--board-media-*` 토큰으로 영상 UI 색 통일

### 9-6. 주의할 점

- **`var(--색상, #백업hex)`** 형태는 토큰 미로드 시 백업용입니다. `custom.css`가 항상 로드되면 `:root`만 수정하면 됩니다.
- **`color-mix()`** 는 최신 브라우저 필요 (이미 스킨에서 사용 중).
- 유튜브 **썸네일 이미지**는 YouTube CDN 고정색이라, 영상 미리보기 자체는 사이트 색과 무관합니다.
- **그누보드 기본 스킨**(`basic`, `gallery`)은 `custom.css` 토큰을 쓰지 않습니다.

### 9-7. 수정하지 않은 항목 (제안만)

| 항목 | 이유 |
|------|------|
| `g5b-board.css` 전체 `var()` fallback hex 제거 | `custom.css` 미적용 페이지 대비 — 일괄 제거 시 리스크 |
| `--container`를 게시판 폭에 직접 연결 | 게시판 폭은 관리자 `bo_table` / 스킨 `width` 변수 사용 |
| Font Awesome 아이콘 색 | 클래스·상속으로 처리, 별도 토큰 불필요 |

---

## 10. 관련 파일·문서

| 문서/경로 | 내용 |
|-----------|------|
| [README-BOARD-CSS.md](README-BOARD-CSS.md) | CSS 구조·class 규칙·토큰 |
| [README-BUILDER-TO-GNUBOARD.md](README-BUILDER-TO-GNUBOARD.md) | 사이트 템플릿·섹션 구조 |
| `/skin/board/_inc/` | 썸네일·유튜브·fallback 공통 PHP |
| `/img/common/no-image.svg` | 이미지 없음 |
| `/img/common/no-youtube.svg` | 영상 없음 |

---

## 11. 빠른 운영 요약

1. **게시판 추가** 또는 기존 게시판 **수정**
2. **스킨 디렉토리** / **모바일 스킨 디렉토리** 에 위 10개 중 하나 입력
3. 용도에 맞게 **목록에서 내용 사용**, **파일 업로드**, **분류** 설정
4. 유튜브 게시판은 글마다 **wr_1 URL** 등록
5. 이미지 게시판은 **첨부 이미지** 우선 등록
6. [§7 테스트 체크리스트](#7-테스트-체크리스트) 로 PC·모바일 확인
7. 디자인 변경은 [§9 디자인 색상 변경 방법](#9-디자인-색상-변경-방법) — `custom.css` `:root` 우선

문의·버그는 해당 **스킨 디렉토리명**과 **게시판 ID(`bo_table`)** 를 함께 기록하면 재현·수정이 빠릅니다.
