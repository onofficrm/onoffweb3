# 지역 SEO 페이지 가이드

지역명 + 서비스명 키워드(예: **강남 피부과**, **수원 개인회생**)로 랜딩 페이지를 만들 때의 기준입니다.

| 관련 파일 |
|-----------|
| [page/local-template.php](page/local-template.php) — 페이지 템플릿 |
| [data/local-pages.sample.json](data/local-pages.sample.json) — 샘플 데이터 (참고용) |
| [presets/local-business.md](presets/local-business.md) — 메인·섹션 조합 프리셋 |
| [components/schema/local-business.php](components/schema/local-business.php) — LocalBusiness JSON-LD |

> **자동 대량 생성 기능은 없습니다.** JSON·템플릿은 구조 참고용이며, 각 URL마다 **실제 고유 콘텐츠** 작성이 필요합니다.

---

## 1. 지역 SEO 페이지의 목적

- 지역 + 서비스 **롱테일 키워드** 검색 유입
- 해당 지역 고객에게 **신뢰·절차·연락** 정보 제공
- LocalBusiness·FAQ·Breadcrumb 등 **구조화 데이터**로 검색 품질 보조

---

## 2. 키워드 구조

| 요소 | 예시 | 설정 위치 |
|------|------|-----------|
| 지역명 `area` | 강남, 수원, 세부 | `$local_area` |
| 서비스명 `service` | 피부과, 개인회생 | `$local_service` |
| 대표 키워드 | 강남 피부과 | `$local_main_keyword` |
| 설명 | 메타·히어로 문구 | `$local_description` |

**권장 title 형식:** `{지역} {서비스} | {사이트명}`  
**권장 description:** 지역·서비스·상담 방법을 80~160자 내외로 구체적으로 작성

---

## 3. 중복 콘텐츠 주의 (필수)

| 하지 말 것 | 해야 할 것 |
|------------|------------|
| 템플릿 문구를 10개 URL에 그대로 복사 | 지역별 **고유 소개·FAQ·후기·가격 안내** 작성 |
| 다른 사이트 문단 복붙 | 직접 작성 또는 검수된 원고 |
| 존재하지 않는 지점·가격 표기 | 실제 운영 정보와 일치 |
| Schema만 키워드 채우기 | **화면에 보이는 FAQ**와 Schema 동일 유지 |

구글·네이버 모두 **유사 페이지 다수**는 순위·노출에 불리할 수 있습니다.

---

## 4. 추천 페이지 구조

`local-template.php` 섹션 순서:

1. **Hero** — 키워드 H1, 지역 설명, 상담 CTA  
2. **지역 문제** — 해당 지역 검색자 Pain Point  
3. **서비스 소개** — 제공 범위  
4. **차별점** — 3~4 카드  
5. **진행 과정** — 문의 → 상담 → 진행 → 완료  
6. **후기/사례** — 실제 후기로 교체 (샘플 카드 → 게시판 또는 검증 후기)  
7. **FAQ** — 지역·서비스 특화 + FAQPage Schema  
8. **오시는 길** (`local`) 또는 **상담 CTA** (`online`)  
9. **관련 콘텐츠** — 내부 링크·관련글(컴포넌트)  
10. **Schema** — Breadcrumb, LocalBusiness, FAQ  

`$local_page_mode`:

- `local` — 오프라인 매장·병원·학원 → `kakao-map` + LocalBusiness  
- `online` — 해외·비대면 상담 → 상담 CTA 중심  

---

## 5. 실제 지역 페이지 만드는 방법

### 5.1 파일 복사

```text
/page/local-template.php
  → 복사
/page/local-gangnam-skin.php   (URL·파일명은 영문 권장)
```

### 5.2 변수 수정 (파일 상단)

```php
$local_area         = '강남';
$local_service      = '피부과';
$local_main_keyword = '강남 피부과';
$local_description  = '…고유 설명…';
$local_page_mode    = 'local';
```

### 5.3 본문 전부 검수

- `$local_problems`, `$local_differentiators`, `$g5_faq_items`, `$local_reviews_sample`  
- Hero·서비스·후기 문단 **지역별로 다르게** 수정

### 5.4 메뉴·내부 링크

- 관리자 메뉴 또는 푸터에서 해당 URL 링크 (필요 시)  
- 다른 지역 페이지끼리 **관련 링크**로 연결 (과도한 상호 링크는 지양)

### 5.5 샘플 JSON 미리보기 (개발용)

```
/page/local-template.php?preview=1&area=수원&service=개인회생
```

`data/local-pages.sample.json`에서 해당 행을 읽어 변수만 채웁니다. **운영 페이지는 preview 없이 복사본 사용.**

---

## 6. FAQ 작성법

- `$g5_faq_items`에 **화면에 보이는 질문만** 등록  
- 지역명·서비스명을 자연스럽게 포함 (키워드 나열 금지)  
- Schema는 `g5_sample_faq_schema_items()` → `components/schema/faq.php` 자동 연동  
- [SECTION-GUIDE.md](SECTION-GUIDE.md) §8 FAQ Schema 참고  

---

## 7. 후기·사례 연결

| 단계 | 방법 |
|------|------|
| 현재 템플릿 | `$local_reviews_sample` 카드 3개 (샘플) |
| 운영 권장 | `review` 게시판 + 커스텀 스킨 또는 검증된 텍스트로 교체 |
| Schema | Review 스키마는 별도 설계 시 `components/schema/` 확장 (선택) |

허위·출처 불명 후기는 표기하지 마세요.

---

## 8. 관련글 연결

`local-template.php` 하단 `#local-related` 주석 참고.

```php
// (related-posts.php 생성 후)
// $related_bo_table = 'story';
// $related_wr_id = (int) $view['wr_id'];
// include_once G5_PATH . '/components/related-posts.php';
```

- 같은 주제 게시판 글 3~5개  
- 다른 지역 페이지 2~3개 (실제 관련 있을 때만)  

---

## 9. LocalBusiness Schema

- `$local_page_mode === 'local'` 일 때 `components/schema/local-business.php` include  
- `_site.config.php`: `company_name`, `phone`, `email`, `address`, `logo_path`, `kakao_map_lat/lng`  
- `head.php`의 seo-meta Organization과 **중복**될 수 있음 → 필요 시 한쪽만 사용하거나 이후 `@graph` 통합  

---

## 10. Search Console · 네이버 서치어드바이저

1. 사이트 소유 확인 (도메인·HTML 태그)  
2. **사이트맵** 제출 — [SITEMAP-ROBOTS-GUIDE.md](SITEMAP-ROBOTS-GUIDE.md)  
3. 지역 페이지 URL **색인 요청** (중요 페이지만)  
4. FAQ·LocalBusiness **리치 결과** 오류 모니터링  

---

## 11. Cursor 적용 프롬프트 예시

```
/page/local-template.php 를 복사해 /page/local-suwon-rehab.php 를 만들어 주세요.

조건:
- git/배포/FTP 금지, bbs/lib/adm/코어 수정 금지
- $local_area=수원, $local_service=개인회생, main_keyword=수원 개인회생
- Hero·문제·서비스·FAQ·후기 문구를 수원 지역에 맞게 전부 새로 작성 (템플릿 문구 복붙 금지)
- $local_page_mode=local
- FAQ Schema 연결 유지
- LocalBusiness·breadcrumb schema include 유지
- 수정 파일 목록 먼저 제시
```

---

## 12. 체크리스트 (납품 전)

- [ ] title·description·H1에 키워드 자연스럽게 포함  
- [ ] 다른 지역 페이지와 **본문 중복** 없음  
- [ ] FAQ 화면 = FAQ Schema  
- [ ] 연락처·주소·지도 실제 정보와 일치  
- [ ] 후기·가격·효과 표현 과장·허위 없음  
- [ ] canonical·내부 링크 정상  
- [ ] 모바일 CTA·FAQ 아코디언 동작  

납품용 SEO 전체 목록: [SEO-CHECKLIST.md](SEO-CHECKLIST.md) · sitemap: [SITEMAP-ROBOTS-GUIDE.md](SITEMAP-ROBOTS-GUIDE.md)

---

## 관련 문서

- [README-START.md](README-START.md)  
- [LAUNCH-CHECKLIST.md](LAUNCH-CHECKLIST.md)  
- [SECTION-GUIDE.md](SECTION-GUIDE.md)  
- [BOARD-SKIN-GUIDE.md](BOARD-SKIN-GUIDE.md)
