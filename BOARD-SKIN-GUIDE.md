# 게시판 스킨 가이드

onoff-g5-base **커스텀 게시판 스킨 12종** 선택·적용·검수 가이드입니다.

| 상세 운영 문서 | [README-BOARD-SKINS.md](README-BOARD-SKINS.md) |
| CSS 구조 | [README-BOARD-CSS.md](README-BOARD-CSS.md) |
| **게시판 생성** | [BOARD-CREATE-GUIDE.md](BOARD-CREATE-GUIDE.md) · [setup/project.sample.json](setup/project.sample.json) |

### 추천 bo_table ↔ 스킨 (요약)

| bo_table | 스킨 | 비고 |
|----------|------|------|
| `notice` | `basic-notice` | 공지 |
| `news` | `basic-modern` | 소식 |
| `column` | `post-thumb` | SEO·칼럼 |
| `blog` | `post-media` | 매거진 |
| `portfolio` | `gallery-grid` | 사례 |
| `gallery` | `gallery-masonry` | 사진 |
| `video` | `youtube-gallery` | 영상 허브 |
| `lecture` | `youtube-list` | 강의 목록 |
| `review` | `basic-card` | 후기 |
| `faq` | `faq-accordion` | FAQ |
| `inquiry` | `landing-inquiry` | 문의 관리 |
| `free` | `basic-clean` | 일반 |

상세 권한·설정: [BOARD-CREATE-GUIDE.md](BOARD-CREATE-GUIDE.md) §3~5

---

## 1. 스킨 목록 요약

| # | 스킨 | 용도 한줄 |
|---|------|-----------|
| 0 | `landing-inquiry` | **문의·상담 관리** (inquiry 게시판 전용) |
| 1 | `basic-clean` | 기본·Q&A·텍스트 위주 |
| 2 | `basic-modern` | 소식·보도·날짜 강조 |
| 3 | `basic-card` | FAQ·후기·카드 요약 |
| 4 | `basic-notice` | 공지+일반 분리 |
| 5 | `post-thumb` | 블로그·칼럼 썸네일 |
| 6 | `post-media` | 매거진·대형 썸네일 |
| 7 | `gallery-grid` | 포트폴리오 그리드 |
| 8 | `gallery-masonry` | 사진·메이슨리 |
| 9 | `youtube-list` | 영상 목록형 |
| 10 | `youtube-gallery` | 영상 카드형 |
| 11 | `faq-accordion` | FAQ 아코디언 + FAQPage Schema |

경로: `skin/board/{스킨명}/` · 모바일: `mobile/skin/board/{스킨명}/` (`landing-inquiry` 포함)

### 1-1 SEO 강화 적용 스킨 (콘텐츠용)

| 스킨 | Article | Breadcrumb | 관련글 | 목록 h1 | 글보기 h1 | time | alt |
|------|:-------:|:----------:|:------:|:-------:|:---------:|:----:|:---:|
| `post-thumb` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| `post-media` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| `basic-modern` | ✅ | ✅ | — | ✅ | ✅ | ✅ | — |
| `basic-card` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | — |
| `basic-notice` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | — |
| `landing-inquiry` | — | — | — | — | — | — | — |

공통 헬퍼: `skin/board/_inc/g5b-seo-list.php`, `g5b-seo-view.php`  
Schema: `components/schema/article.php`, `breadcrumb.php` · 관련글: `components/related-posts.php`

- **목록:** Article Schema 출력 없음  
- **글보기:** Schema·관련글은 `g5b_seo_view_footer()` 한 번 호출  
- **썸네일 alt:** `g5b_list_thumb_html()` — 제목 기반, fallback `alt=""`  
- **날짜:** `<time datetime="">` — `g5b_seo_list_time()` / `g5b_seo_time_tag()`

**보류:** `gallery-*` (이미지 SEO), `landing-inquiry` (문의 관리)  
**유튜브:** `youtube-list`, `youtube-gallery` — 글보기 VideoObject (`components/schema/video.php`)

---

## 2. 스킨별 상세

### landing-inquiry

| 항목 | 내용 |
|------|------|
| **용도** | 상담문의, 견적문의, 예약문의, 교육신청, 제휴문의 **관리자 확인용** |
| **추천 bo_table** | `inquiry` |
| **목록** | PC 테이블(상태·연락처·접수 페이지) → 모바일 카드 |
| **보기** | 메타 카드·전화/메일/접수 페이지 버튼·관리 메모 |
| **글쓰기** | wr_1~wr_10 (상태·담당자·메모) |
| **주의** | **목록·읽기 권한 관리자 권장** (개인정보). 웹 폼 저장은 `proc/inquiry-submit.php` |
| **문서** | [INQUIRY-FORM-GUIDE.md](INQUIRY-FORM-GUIDE.md) §10 |

### basic-clean

| 항목 | 내용 |
|------|------|
| **용도** | 일반 게시판, 1:1 문의, 자료실(텍스트) |
| **목록** | PC 테이블 → 모바일 카드 |
| **글쓰기/보기** | 표준 폼·본문 |
| **추천 bo_table** | `free`, `qna` (문의 전용은 `landing-inquiry` + `inquiry`) |
| **주의** | 다른 스킨의 CSS 베이스 — **수정 시 10종에 영향** |

### basic-modern

| 항목 | 내용 |
|------|------|
| **용도** | 회사 소식, IR, 뉴스 |
| **목록** | 날짜·분류 강조 테이블 |
| **추천 bo_table** | `news` |
| **주의** | 분류(`sca`) 사용 시 탭 확인 |
| **SEO** | Article·Breadcrumb·목록 h3·time (관련글 없음) |

### basic-card

| 항목 | 내용 |
|------|------|
| **용도** | FAQ, 후기, 이벤트 요약 |
| **목록** | 3열 카드 + 요약문 |
| **추천 bo_table** | `review`, `faq`, `event` |
| **주의** | **목록에서 내용 사용** (`bo_use_list_content`) 권장 |
| **SEO** | Article·Breadcrumb·관련글·카드 h3·time |

### basic-notice

| 항목 | 내용 |
|------|------|
| **용도** | 공지사항, 공지+일반 혼합 |
| **목록** | 상단 공지 블록 + 일반 글 |
| **추천 bo_table** | `notice` |
| **주의** | 공지글(`wr_notice`)·목록 공지 수 설정 |
| **SEO** | Article·Breadcrumb·관련글·time |

### post-thumb

| 항목 | 내용 |
|------|------|
| **용도** | 블로그, 칼럼, 뉴스 피드 |
| **목록** | 작은 썸네일 + 제목·요약 |
| **추천 bo_table** | `column`, `blog` |
| **주의** | 첨부 이미지 또는 본문 첫 이미지 |
| **SEO** | Article·Breadcrumb·관련글·썸네일 alt·time |

### post-media

| 항목 | 내용 |
|------|------|
| **용도** | 매거진, 인터뷰, 미디어 강조 |
| **목록** | 큰 썸네일 + 메타 |
| **추천 bo_table** | `blog`, `media` |
| **주의** | 대표 이미지 비율·용량 |
| **SEO** | Article·Breadcrumb·관련글·썸네일 alt·time |

### gallery-grid

| 항목 | 내용 |
|------|------|
| **용도** | 포트폴리오, 제품 갤러리 |
| **목록** | 균등 그리드 (4→1열) |
| **추천 bo_table** | `portfolio`, `gallery` |
| **주의** | 이미지 첨부 필수에 가깝게 운영 |

### gallery-masonry

| 항목 | 내용 |
|------|------|
| **용도** | 사진 갤러리, 전시 |
| **목록** | 메이슨리 + 제목 오버레이 |
| **추천 bo_table** | `gallery`, `photo` |
| **주의** | 세로 비율 다른 이미지 다수 |

### youtube-list

| 항목 | 내용 |
|------|------|
| **용도** | 강의·영상 아카이브 목록 |
| **목록** | 썸네일 + 제목 한 줄 |
| **추천 bo_table** | `video`, `lecture` |
| **주의** | `wr_1`에 URL (아래 §4) |

### youtube-gallery

| 항목 | 내용 |
|------|------|
| **용도** | 영상 허브, 채널형 |
| **목록** | 카드 그리드 + 재생 아이콘 |
| **추천 bo_table** | `video`, `media` |
| **주의** | 목록·보기 모두 `wr_1` 확인 |

---

## 3. 추천 매핑 (bo_table → 스킨)

| bo_table 예시 | 스킨 | 사용처 |
|---------------|------|--------|
| `notice` | basic-notice | 공지 |
| `news` | basic-modern | 소식 |
| `column` | post-thumb | 칼럼 |
| `blog` | post-media | 블로그 |
| `portfolio` | gallery-grid | 시공·제작 사례 |
| `gallery` | gallery-masonry | 사진 |
| `video` | youtube-gallery | 영상 허브 |
| `lecture` | youtube-list | 강의 목록 |
| `review` | basic-card | 후기 |
| `free` | basic-clean | 자유·문의 |

> `bo_table`은 프로젝트마다 다릅니다. 관리자에서 게시판 생성 후 위 표를 참고해 스킨만 지정하세요.

---

## 4. 유튜브 게시판 (`youtube-list`, `youtube-gallery`)

| 항목 | 내용 |
|------|------|
| **입력 필드** | 글쓰기 **여분필드 `wr_1`** 에 유튜브 URL 또는 11자 ID |
| **설명 필드** | **`wr_2`** — 목록 요약·VideoObject `description` (선택) |
| **지원 URL** | `watch?v=`, `youtu.be/`, `/embed/`, `/shorts/`, `m.youtube.com/watch` |
| **ID 추출** | `g5b_youtube_id_from_url()` · 별칭 `onoff_extract_youtube_id()` |
| **ID만 입력** | 11자 영숫자·`_` `-` (예: `dQw4w9WgXcQ`) |
| **썸네일** | `https://img.youtube.com/vi/{ID}/hqdefault.jpg` (`g5b_youtube_thumb_html`) |
| **embed iframe** | `youtube-nocookie.com/embed/{ID}` — 사용자 입력 URL을 src에 넣지 않음 |
| **본문 fallback** | `wr_1` 비어 있으면 본문 iframe에서 ID 추출 |
| **잘못된 URL** | `/img/common/no-youtube.svg` placeholder · 글보기 fallback 안내 |
| **VideoObject** | 글보기만 · `g5b_youtube_print_video_schema()` → `components/schema/video.php` |
| **Schema URL** | `embedUrl` / `contentUrl` / `thumbnailUrl` — 모두 **영상 ID 기반** 재구성 |
| **YouTube API** | 사용하지 않음 (`duration`, `interactionStatistic` 미포함) |

공통 PHP: `skin/board/_inc/g5b-youtube.php`

---

## 4-1. FAQ 게시판 (`faq-accordion`)

| 항목 | 내용 |
|------|------|
| **용도** | FAQ, 비용 안내, 진행 절차, 상담 전 확인사항 |
| **추천 `bo_table`** | `faq` |
| **목록** | 아코디언 (질문=`wr_subject`, 답변=`wr_content`) · `data-accordion-mode="multiple"` |
| **글보기** | Q/A 상세 + Breadcrumb·관련글(파일 있을 때) |
| **글쓰기** | 라벨 「질문」「답변」 · `wr_1` 키워드·`wr_2` 정렬(선택) |
| **Schema** | `components/schema/faq.php` · **현재 페이지에 보이는 FAQ만** |
| **비밀글** | Schema 제외 · 목록 fallback 안내 |
| **목록 답변 표시** | 관리자 **목록에서 내용 사용** 권장 |
| **헬퍼** | `skin/board/_inc/g5b-faq.php` |
| **JS** | `js/custom.js` → `initFaqAccordion()` (요소 없으면 무시) |

### 관리자 추천 설정

| 설정 | 권장 |
|------|------|
| 게시판 제목 | 자주 묻는 질문 |
| 스킨 | `faq-accordion` (PC·모바일 동일) |
| 분류 | **사용** (서비스·비용·진행절차·상담·결제·기타 등) |
| 목록/읽기 권한 | 전체 공개 |
| 쓰기 권한 | **관리자** |
| 댓글 | 필요 없으면 **미사용** |
| 비밀글 | 일반 FAQ에서는 **미사용** |
| 검색 | 사용 |
| 페이지당 목록 | **10~20** |
| 목록에서 내용 사용 | **사용** (아코디언에 답변 표시) |

---

## 5. 이미지 게시판 (`post-*`, `gallery-*`)

| 항목 | 내용 |
|------|------|
| **우선순위** | 첨부 이미지 → 본문 첫 `<img>` |
| **썸네일** | `g5b-thumb.php` — 리사이즈·경로 |
| **이미지 없음** | `/img/common/no-image.svg` |
| **설정** | 파일 업로드 사용, 목록 내용 사용(카드형) 권장 |

---

## 6. 관리자 적용 방법

1. **관리자 로그인** → `/adm`
2. **게시판관리** → 게시판 **수정**
3. **스킨 디렉토리** (`bo_skin`) → 위 10종 중 선택
4. **모바일 스킨** (`bo_mobile_skin`) → **PC와 동일 이름** 권장
5. 저장 후 `bbs/board.php?bo_table=ID` 접속·캐시 새로고침

---

## 7. 검수 체크리스트

각 사용 스킨·게시판마다 확인:

- [ ] 목록 (PC·모바일)
- [ ] 글쓰기 (필드·에디터·`wr_1` 유튜브)
- [ ] 내용보기 (본문·이미지·영상 embed)
- [ ] 수정
- [ ] 삭제
- [ ] 답변 (사용 시)
- [ ] 비밀글
- [ ] 공지글
- [ ] 첨부파일
- [ ] 검색
- [ ] 분류 (`sca`)
- [ ] 페이지네이션
- [ ] 댓글
- [ ] **primary 색**·버튼 (`g5b-board.css` 토큰)

---

## 8. Cursor 프롬프트

- 스킨 색 통일: [PROMPTS.md](PROMPTS.md) §5
- 전체 검수: [PROMPTS.md](PROMPTS.md) §9

**수정 금지:** `/bbs`, `/lib`, `/adm`, `skin/board/basic`, `skin/board/gallery`
