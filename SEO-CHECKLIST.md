# SEO 납품 체크리스트

홈페이지 **오픈·납품 전** 검색엔진 최적화 점검용 목록입니다.  
항목별 상세: [SITEMAP-ROBOTS-GUIDE.md](SITEMAP-ROBOTS-GUIDE.md), [LOCAL-SEO-GUIDE.md](LOCAL-SEO-GUIDE.md), [SECTION-GUIDE.md](SECTION-GUIDE.md)

> git commit · FTP 배포 · 그누보드 코어(`/bbs`, `/lib`, `/adm`) 수정은 이 문서 작업 범위가 아닙니다.

---

## 1. 메타 · 기본 SEO

| # | 항목 | 확인 | 비고 |
|---|------|:----:|------|
| 1 | **title** | [ ] | 페이지당 1개, 60자 내외 권장 |
| 2 | **meta description** | [ ] | `_site.config.php` · `$page_description` |
| 3 | **canonical** | [ ] | [components/seo-meta.php](components/seo-meta.php), 중복 URL 없음 |
| 4 | **robots** | [ ] | 공개 페이지 `index,follow`, 완료·404·가이드 `noindex` |
| 5 | **H1** | [ ] | 페이지당 1개; **게시판 글보기** 제목 h1 — SEO 스킨 5종 |
| 5-1 | **게시판 목록 h1** | [ ] | 게시판 제목(`bo_subject`) — `g5b_seo_list_h1` |
| 5-2 | **목록 글 제목 heading** | [ ] | h2/h3 구조 (post-thumb·card 등) |

---

## 2. Open Graph · SNS

| # | 항목 | 확인 | 비고 |
|---|------|:----:|------|
| 6 | **OG title** | [ ] | seo-meta 자동·페이지 변수 |
| 7 | **OG description** | [ ] | |
| 8 | **OG image** | [ ] | 1200×630 권장, [IMAGE-GUIDE.md](IMAGE-GUIDE.md) |

---

## 3. Sitemap · robots

| # | 항목 | 확인 | 비고 |
|---|------|:----:|------|
| 9 | **sitemap.xml** | [ ] | [sitemap.sample.xml](sitemap.sample.xml) → 운영 복사·도메인 교체 |
| 10 | **robots.txt** | [ ] | [robots.sample.txt](robots.sample.txt) → 운영 복사 |

---

## 4. 콘텐츠 · 구조

| # | 항목 | 확인 | 비고 |
|---|------|:----:|------|
| 11 | **이미지 alt** | [ ] | 의미 있는 설명, 장식은 `alt=""` |
| 11-1 | **게시판 썸네일 alt** | [ ] | post-thumb·post-media — 제목 기반 (`g5b-thumb`) |
| 11-2 | **날짜 time 태그** | [ ] | 목록·글보기 `datetime` — SEO 스킨 5종 |
| 12 | **내부 링크** | [ ] | GNB·푸터·CTA·관련글 깨짐 없음 |
| 13 | **게시판 글 URL** | [ ] | 목록·글보기 접근, 비밀글 노출 없음 |
| 14 | **중복 콘텐츠** | [ ] | 지역 페이지·타이틀·본문 유사도 점검 |
| 14-1 | **관련글/최신글 블록** | [ ] | 글보기 하단 `related-posts.php` (modern 제외) |

### 컴포넌트 include 예시 (게시판 글보기)

```php
$related_bo_table = $bo_table;
$related_exclude_wr_id = $view['wr_id'];
include_once G5_PATH . '/components/related-posts.php';
```

---

## 5. Schema (JSON-LD)

| # | 항목 | 확인 | 비고 |
|---|------|:----:|------|
| 15 | **Organization / WebSite** | [ ] | seo-meta 기본 출력 |
| 16 | **Article schema** | [ ] | 글보기만 · post-thumb/media/modern/card/notice 스킨 |
| 16-1 | **Breadcrumb schema** | [ ] | 글보기 · `breadcrumb.php` · 중복 없음 확인 |
| 16-2 | **Schema 중복** | [ ] | 목록에 Article 없음 · seo-meta Organization과 별도 |
| 16-3 | **VideoObject (유튜브)** | [ ] | `youtube-list` · `youtube-gallery` 글보기만 |
| 16-4 | **유튜브 iframe src** | [ ] | `youtube-nocookie.com/embed/{ID}` · 입력 URL 그대로 미사용 |
| 16-5 | **유튜브 썸네일 alt** | [ ] | 게시글 제목 기반 · fallback `alt=""` |
| 16-6 | **유튜브 URL fallback** | [ ] | 잘못된 URL → no-youtube.svg · 목록·보기 깨짐 없음 |
| 17 | **FAQ schema** | [ ] | [section/faq.php](section/faq.php) · 화면과 동일 배열 |
| 17-1 | **FAQ 게시판 (faq-accordion)** | [ ] | 목록·글보기 FAQPage · 현재 페이지 FAQ만 |
| 17-2 | **FAQ Schema 일치** | [ ] | 화면 아코디언 내용 = JSON-LD question/answer |
| 17-3 | **비밀글 FAQ Schema** | [ ] | 비밀글 항목 Schema 미포함 |
| 17-4 | **FAQ 제목 구조** | [ ] | 목록 `button.faq-question` · 글보기 `h1` 1개 |
| 18 | **LocalBusiness schema** | [ ] | [components/schema/local-business.php](components/schema/local-business.php) |
| 19 | **BreadcrumbList** | [ ] | 서브·지역 페이지 (선택) |

Rich Results Test · Search Console **리치 결과** 오류 없음.

---

## 6. 기술 · 성능 · 모바일

| # | 항목 | 확인 | 비고 |
|---|------|:----:|------|
| 20 | **모바일 속도** | [ ] | Lighthouse / PageSpeed, LCP·CLS — [PERFORMANCE-GUIDE.md](PERFORMANCE-GUIDE.md) |
| 21 | **모바일 UI** | [ ] | 1024·768·480px, [LAUNCH-CHECKLIST.md](LAUNCH-CHECKLIST.md) §3 |
| 22 | **HTTPS** | [ ] | 혼합 콘텐츠 없음 |
| 23 | **404 페이지** | [ ] | [page/404.php](page/404.php), noindex |

---

## 7. 색인 제외 · 보안

| # | 항목 | 확인 | 비고 |
|---|------|:----:|------|
| 24 | **style-guide.php** | [ ] | robots Disallow · 운영 전 삭제 권장 |
| 25 | **_BUILDER_INPUT** | [ ] | 배포·robots 제외 |
| 26 | **inquiry-thanks** | [ ] | noindex, sitemap 미포함 |
| 27 | **local-template.php** | [ ] | 템플릿 URL 미색인 |

---

## 8. 검색엔진 등록

| # | 항목 | 확인 | 비고 |
|---|------|:----:|------|
| 28 | **Google Search Console** | [ ] | 속성·소유 확인·sitemap 제출 |
| 29 | **네이버 서치어드바이저** | [ ] | 사이트맵·수집 요청 |

---

## 9. 전환 · 추적 (선택)

| # | 항목 | 확인 | 비고 |
|---|------|:----:|------|
| 30 | **GTM / GA4** | [ ] | `_site.config.php` ID, [README-START.md](README-START.md) |
| 31 | **문의 완료 전환** | [ ] | [page/inquiry-thanks.php](page/inquiry-thanks.php) |

---

## 10. 최종 확인 순서 (권장)

1. [SEO-CHECKLIST.md](SEO-CHECKLIST.md) (본 문서)  
2. [LAUNCH-CHECKLIST.md](LAUNCH-CHECKLIST.md) — PC·모바일·관리자  
3. [CLEANUP-PROMPTS.md](CLEANUP-PROMPTS.md) — 샘플 문구·민감정보  
4. Search Console / 서치어드바이저 사이트맵 제출  
5. 대표 URL 5~10개 수동 브라우저·소스 보기  

---

## Cursor 검수 프롬프트 예시

```
이 프로젝트 SEO 납품 검수를 해주세요.
SEO-CHECKLIST.md 항목 기준으로 점검만 하고, git/배포/FTP/bbs/lib/adm 수정 금지.
문제만 최소 수정, 수정 전 파일 목록 제시.
```

---

## 관련 문서

| 문서 | 용도 |
|------|------|
| [SITEMAP-ROBOTS-GUIDE.md](SITEMAP-ROBOTS-GUIDE.md) | sitemap·robots·등록 방법 |
| [LOCAL-SEO-GUIDE.md](LOCAL-SEO-GUIDE.md) | 지역 페이지 |
| [INQUIRY-FORM-GUIDE.md](INQUIRY-FORM-GUIDE.md) | 문의·알림 |
| [IMAGE-GUIDE.md](IMAGE-GUIDE.md) | 이미지·OG |
| [LAUNCH-CHECKLIST.md](LAUNCH-CHECKLIST.md) | 전체 납품 |
