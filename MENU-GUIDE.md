# 관리자 메뉴 생성 가이드

새 프로젝트(onoff-g5-base)에서 **그누보드 관리자**로 GNB(상단 메뉴)를 수동 구성하는 방법입니다.

| 관련 문서 | 용도 |
|-----------|------|
| [setup/project.sample.json](setup/project.sample.json) | `menus` · `boards` 계획 (DB 미반영) |
| [MENU-EXAMPLES.md](MENU-EXAMPLES.md) | 유형별 메뉴 예시 |
| [MENU-PROMPTS.md](MENU-PROMPTS.md) | Cursor 프롬프트 |
| [BOARD-CREATE-GUIDE.md](BOARD-CREATE-GUIDE.md) | 게시판 생성 후 URL 연결 |

> **메뉴 DB 자동 생성 스크립트는 없습니다.** 관리자 화면에서 직접 등록하는 것을 기본으로 합니다.

---

## 1. 문서 목적

- 새 프로젝트 시작 후 **메뉴를 빠르게** 맞추기 위한 기준
- **PC 메뉴**와 **모바일 메뉴**를 각각 등록·확인하는 절차
- 서브페이지·게시판·외부 링크·메인 앵커 링크 형식 정리

### 이 베이스의 메뉴 출력 방식

`head.php`에서 그누보드 **메뉴 DB**를 읽어 출력합니다.

```php
$menu_datas_pc = get_menu_db(0, true);  // PC
$menu_datas_mo = get_menu_db(1, true);  // 모바일
```

- PC: `#siteGnb` (상단 GNB)
- 모바일: `#siteMobileNav` (햄버거 메뉴, `custom.js` 연동)
- 모바일 메뉴가 비어 있으면 **PC 메뉴를 그대로** 사용합니다.

관리자에서 등록한 메뉴가 곧 사이트 헤더에 반영됩니다. (코어 `get_menu_db()` 사용, 메뉴 PHP 하드코딩 아님)

---

## 2. 그누보드 관리자 메뉴 생성 기본 순서

1. **관리자 로그인** — `https://도메인/adm`
2. **환경설정** → **메뉴설정** (또는 **메뉴관리** — 그누보드/관리자 스킨에 따라 메뉴명이 다를 수 있음)
3. **PC용 메뉴(0)** · **모바일용 메뉴(1)** 탭(또는 구분) 확인
4. **메뉴 추가** (또는 하위 메뉴 추가)
5. **메뉴명** 입력
6. **링크(URL)** 입력
7. **새창** 여부 선택 (`_self` = 같은 창, `_blank` = 새 창)
8. **사용(표시)** 여부 확인
9. **순서** 조정 (위·아래 또는 순서 번호)
10. **저장**
11. 홈페이지 **PC·모바일**에서 클릭·표시 확인

> 모바일 메뉴를 따로 만들지 않으면 PC 메뉴가 모바일에도 쓰입니다. **둘 다 저장**하는 것을 권장합니다.

---

## 3. 메뉴 링크 기본 형식

### 서브페이지 (`/page/`)

| 파일 | 용도 | 비고 |
|------|------|------|
| `/page/about.php` | 회사소개 | ✅ 기본 포함 |
| `/page/service.php` | 서비스 | ✅ |
| `/page/portfolio.php` | 포트폴리오 소개 페이지 | ✅ (게시판과 별도) |
| `/page/contact.php` | 문의·상담 | ✅ |
| `/page/privacy.php` | 개인정보처리방침 | ✅ 푸터·메뉴 하단 |
| `/page/local-template.php` | 지역 SEO 랜딩 | ✅ 지역형 |
| `/page/inquiry-thanks.php` | 문의 완료 | 폼 완료 후 이동 |
| `/page/style-guide.php` | 개발용 | 운영 전 메뉴에서 제외 권장 |

> **`/page/location.php`는 기본 베이스에 없습니다.** 오시는 길은 `about`·`contact`·`local-template`에 통합하거나 페이지를 새로 만듭니다.

### 게시판

```
/bbs/board.php?bo_table=테이블명
```

| bo_table | 예시 URL |
|----------|----------|
| notice | `/bbs/board.php?bo_table=notice` |
| news | `/bbs/board.php?bo_table=news` |
| column | `/bbs/board.php?bo_table=column` |
| blog | `/bbs/board.php?bo_table=blog` |
| portfolio | `/bbs/board.php?bo_table=portfolio` |
| gallery | `/bbs/board.php?bo_table=gallery` |
| faq | `/bbs/board.php?bo_table=faq` |
| video | `/bbs/board.php?bo_table=video` |
| lecture | `/bbs/board.php?bo_table=lecture` |
| review | `/bbs/board.php?bo_table=review` |
| inquiry | `/bbs/board.php?bo_table=inquiry` (관리자용, GNB 비권장) |
| free | `/bbs/board.php?bo_table=free` |

### 메인 페이지 앵커 (랜딩형)

메인 `index.php` 섹션 `id` 기준 (실제 ID는 `section-` 접두사):

| 메뉴에 쓸 때 | 실제 앵커 |
|-------------|-----------|
| `/#section-service` | 서비스 섹션 |
| `/#section-portfolio` | 포트폴리오 섹션 |
| `/#section-faq` | FAQ 섹션 |
| `/#section-contact` | 문의 섹션 |
| `/#section-review` | 후기 섹션 |

> `/#service`처럼 짧은 이름은 **동작하지 않을 수 있습니다.** `index.php`·`section/*.php`의 `id`와 맞출 것.

### 외부·기타 링크

| 유형 | 형식 | 새창 |
|------|------|------|
| 외부 사이트 | `https://example.com` | `_blank` 권장 |
| 전화 | `tel:01012345678` (하이픈 없이도 가능) | `_self` |
| 카카오 채널 | `https://pf.kakao.com/...` | `_blank` |
| 홈 | `/` 또는 `<?php echo G5_URL; ?>` 와 동일 경로 | `_self` |

---

## 4. 추천 기본 메뉴 구성

### 기본 회사형

| 메뉴 | URL |
|------|-----|
| 홈 | `/` |
| 회사소개 | `/page/about.php` |
| 서비스 | `/page/service.php` |
| 포트폴리오 | `/bbs/board.php?bo_table=portfolio` |
| 칼럼 | `/bbs/board.php?bo_table=column` |
| 문의 | `/page/contact.php` |

### SEO 콘텐츠형

홈 · 칼럼 · 블로그 · 뉴스 · FAQ · 문의

### 랜딩/상담형

홈 · `/#section-service` 또는 `/page/service.php` · 후기 · FAQ · 상담문의(`/page/contact.php`)

### 포트폴리오형

홈 · 포트폴리오 · 갤러리 · 영상 · 문의

### 지역 SEO형

홈 · 서비스 · `/page/local-template.php` · 칼럼 · FAQ · 문의  
(오시는 길은 `contact`·`local-template`·지도 블록 활용)

더 많은 예시: [MENU-EXAMPLES.md](MENU-EXAMPLES.md)

---

## 5. 게시판 생성 후 메뉴 연결 순서

1. [BOARD-CREATE-GUIDE.md](BOARD-CREATE-GUIDE.md)대로 **게시판 먼저** 생성
2. `bo_table` 이름 확정 (오타 주의)
3. 관리자 **메뉴설정**에서 링크에 `/bbs/board.php?bo_table=이름` 입력
4. 저장 후 브라우저에서 URL 직접 접속해 목록·글쓰기 확인
5. GNB·모바일 메뉴에서 동일 링크 클릭 확인

---

## 6. 서브페이지 생성 후 메뉴 연결 순서

1. `/page/파일명.php`가 서버에 있는지 확인 ([page/](page/) 목록)
2. 메뉴설정에 `/page/파일명.php` 연결 (앞에 도메인 붙이지 않고 **경로만** 넣는 경우가 많음)
3. 해당 페이지 **title·description** (`page/_init.php`·페이지 상단 변수) 확인
4. PC·모바일 메뉴·푸터 링크 확인

---

## 7. 1차·2차 메뉴 구성 기준

예시 (회사소개 + 서비스 하위):

```
회사소개          → /page/about.php
서비스            → /page/service.php
  └ 서비스 소개   → /page/service.php
  └ 진행 과정     → /#section-advantage (또는 별도 페이지)
포트폴리오        → /bbs/board.php?bo_table=portfolio
칼럼              → /bbs/board.php?bo_table=column
문의              → /page/contact.php
```

| 권장 | 설명 |
|------|------|
| 2차 메뉴 **5개 이하** | 모바일에서 과도한 깊이 방지 |
| **전환 메뉴** 우측·마지막 | 문의·상담·예약 |
| 푸터 전용 | 개인정보처리방침 — GNB와 별도 링크 가능 |

---

## 8. 메뉴명 작성 기준

- **짧고 명확** (2~6자 권장, 모바일 기준)
- 고객이 이해하는 단어 (내부 용어·약어 지양)
- SEO 키워드 **무리하게** 메뉴에 넣지 않기
- PC에서 한 줄, 모바일에서 **줄바꿈·잘림** 확인

---

## 9. 메뉴 연결 후 체크리스트

- [ ] PC GNB — 모든 1·2차 메뉴 클릭
- [ ] 모바일 햄버거 메뉴 — 동일 링크 접근
- [ ] 현재 페이지 **active** 표시 (해당 메뉴 강조)
- [ ] 게시판 URL — 목록·글보기 정상
- [ ] 서브페이지 — 404 없음
- [ ] 외부 링크 — `https://` 포함·새창 여부
- [ ] **개인정보처리방침** — `/page/privacy.php` (푸터·약관)
- [ ] **상담문의** — `/page/contact.php` vs 플로팅 CTA URL 일치
- [ ] 메인 앵커 — `/#section-*` 실제 섹션과 일치
- [ ] 사용하지 않는 샘플 메뉴 제거

상세: [LAUNCH-CHECKLIST.md](LAUNCH-CHECKLIST.md)

---

## 10. 자주 하는 실수

| 실수 | 해결 |
|------|------|
| 게시판 없이 메뉴만 먼저 연결 | 게시판·페이지 **먼저** 만든 뒤 메뉴 연결 |
| `bo_table` 오타 | URL·관리자 게시판 ID 재확인 |
| `/page` 경로 오타 | 파일명 대소문자·`.php` 확인 |
| 외부 URL에 `https://` 누락 | 전체 URL 입력 |
| 모바일 메뉴 미등록 | PC만 있고 모바일 비어 있음 — 복사·별도 등록 |
| 개인정보처리방침 누락 | 푸터·약관 링크 추가 |
| 문의 메뉴 ≠ 상담 버튼 | `contact.php`·모달·플로팅 URL 통일 |
| 잘못된 앵커 `/#service` | 실제 `id="section-service"` 확인 |
| `style-guide.php` 노출 | 운영 메뉴에서 제외 |

---

## 11. 추천 운영 방식

1. `setup/project.sample.json`에서 **`menus`**·**`boards`** 계획 작성
2. 관리자에서 **게시판** 생성 → [BOARD-CREATE-GUIDE.md](BOARD-CREATE-GUIDE.md)
3. 관리자에서 **메뉴** 등록 (본 문서)
4. 빌더·디자인 반영 후 메뉴명·앵커 최종 정리 — [BUILDER-WORKFLOW.md](BUILDER-WORKFLOW.md)
5. 오픈 전 [LAUNCH-CHECKLIST.md](LAUNCH-CHECKLIST.md)로 링크·404 검수

---

*onoff-g5-base · 메뉴는 관리자 DB 등록이 기준이며, JSON·문서는 계획용입니다.*
