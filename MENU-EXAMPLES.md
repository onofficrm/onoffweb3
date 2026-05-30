# 메뉴 구성 예시

프로젝트 유형별 **참고용** 메뉴 구성입니다.  
빌더·디자인·고객 요청에 따라 **메뉴명·순서·URL은 자유롭게 변경**하세요.

실제 등록: 관리자 **메뉴설정** · 계획표: `setup/project.sample.json` → `menus`

---

## 1. 기본 회사형

| 메뉴 | URL | 연결 |
|------|-----|------|
| 홈 | `/` | 메인 |
| 회사소개 | `/page/about.php` | 서브페이지 |
| 서비스 | `/page/service.php` | 서브페이지 |
| 포트폴리오 | `/bbs/board.php?bo_table=portfolio` | 게시판 |
| 칼럼 | `/bbs/board.php?bo_table=column` | 게시판 |
| 문의 | `/page/contact.php` | 서브페이지 |

**주의:** 포트폴리오 게시판·`/page/portfolio.php` 중복 시 역할 분리 (소개 페이지 vs 사례 목록).

**모바일에서 줄이기:** 칼럼을 푸터·메인 최신글로 대체 가능.

---

## 2. SEO 콘텐츠형

| 메뉴 | URL |
|------|-----|
| 홈 | `/` |
| 칼럼 | `/bbs/board.php?bo_table=column` |
| 블로그 | `/bbs/board.php?bo_table=blog` |
| 뉴스 | `/bbs/board.php?bo_table=news` |
| FAQ | `/bbs/board.php?bo_table=faq` |
| 문의 | `/page/contact.php` |

**주의:** 콘텐츠 게시판 3개 이상이면 2차 메뉴 「콘텐츠」 아래로 묶기.

**모바일:** 1차 5~6개 유지, 뉴스·블로그 중 하나는 2차로.

---

## 3. 랜딩 상담형

| 메뉴 | URL |
|------|-----|
| 홈 | `/` |
| 서비스 | `/#section-service` 또는 `/page/service.php` |
| 후기 | `/bbs/board.php?bo_table=review` |
| FAQ | `/bbs/board.php?bo_table=faq` |
| 상담문의 | `/page/contact.php` |

**주의:** 앵커는 `section-service` 등 **실제 id** 사용. 플로팅 CTA와 동일 URL.

**모바일:** 후기·FAQ를 메인 섹션만 노출하고 GNB는 4개 이하.

---

## 4. 포트폴리오형

| 메뉴 | URL |
|------|-----|
| 홈 | `/` |
| 포트폴리오 | `/bbs/board.php?bo_table=portfolio` |
| 갤러리 | `/bbs/board.php?bo_table=gallery` |
| 영상 | `/bbs/board.php?bo_table=video` |
| 문의 | `/page/contact.php` |

**주의:** `gallery`·`portfolio` 스킨·권한은 [BOARD-CREATE-GUIDE.md](BOARD-CREATE-GUIDE.md) 참고.

**모바일:** 갤러리·영상을 2차 「작업물」로 통합 가능.

---

## 5. 지역 비즈니스형

| 메뉴 | URL |
|------|-----|
| 홈 | `/` |
| 서비스 | `/page/service.php` |
| 지역정보 | `/page/local-template.php` |
| 후기 | `/bbs/board.php?bo_table=review` |
| FAQ | `/bbs/board.php?bo_table=faq` |
| 오시는길 | `/page/contact.php` 또는 about 내 지도 |
| 문의 | `/page/contact.php` |

**주의:** `location.php` 없음 — `local-template`·`contact`에 지도·주소 통합.

**모바일:** 「지역정보」+「서비스」 2차 통합 검토.

---

## 6. 병원·학원·법률 등 상담형 (공통)

| 메뉴 | URL | 비고 |
|------|-----|------|
| 홈 | `/` | |
| 소개 | `/page/about.php` | |
| 서비스안내 | `/page/service.php` | |
| 사례 | `/bbs/board.php?bo_table=portfolio` 또는 review | |
| 칼럼 | `/bbs/board.php?bo_table=column` | SEO |
| FAQ | `/bbs/board.php?bo_table=faq` | |
| 상담문의 | `/page/contact.php` | 마지막·강조 |

**주의:** 업종별 명칭만 바꾸고 구조는 동일해도 됨. 과도한 업종 프리셋은 지양.

**모바일:** 상담문의·전화(`tel:`)는 헤더 CTA + 메뉴 1곳만.

---

## 푸터·필수 링크 (GNB와 별도)

| 항목 | URL |
|------|-----|
| 개인정보처리방침 | `/page/privacy.php` |
| 문의 | `/page/contact.php` |

---

## 다음 단계

1. `project.sample.json` → `menus` 복사·수정  
2. [MENU-GUIDE.md](MENU-GUIDE.md)대로 관리자 등록  
3. [LAUNCH-CHECKLIST.md](LAUNCH-CHECKLIST.md) 검수

*메뉴명은 빌더 섹션 제목·고객 브랜드에 맞게 최종 조정하세요.*
