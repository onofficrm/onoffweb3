# 목적별 프리셋: 회사소개 (company-intro)

일반 **기업·기관 소개** 홈페이지 조합입니다. (특정 업종 전용 아님)

---

## 목적

- 회사 신뢰·조직·연혁 전달
- 서비스·실적·뉴스 균형
- B2B 문의

---

## 추천 메뉴 구조

| 메뉴 | 링크 |
|------|------|
| 회사소개 | `/page/about.php` |
| 서비스 | `/page/service.php` |
| 포트폴리오 | `/page/portfolio.php` 또는 게시판 |
| 소식 | `bbs/board.php?bo_table=news` |
| 문의 | `/page/contact.php` |

---

## 추천 메인 섹션 흐름

```
hero → service → advantage → portfolio → latest(news) → review → contact
```

| 섹션 | 비고 |
|------|------|
| `hero` | 슬로건·비주얼 |
| `service` | 사업 영역 카드 |
| `advantage` | 차별점 |
| `portfolio` | 대표 사례 (정적 또는 게시판 연동) |
| `latest` | `news` 게시판 |
| `review` | 선택 |
| `contact` | 문의 |

---

## 추천 게시판 구성

| bo_table | 스킨 |
|----------|------|
| `notice` | basic-notice |
| `news` | basic-modern |
| `portfolio` | gallery-grid |

---

## 추천 CTA 문구

- `회사소개 보기` / `서비스 문의` / `견적 요청`
- 푸터: 사업자번호·대표 (_site.config.php)

---

## 추천 SEO 구조

| 페이지 | `$page_title` 예 |
|--------|------------------|
| 메인 | `{company_name} | {main_keyword}` |
| about | `회사소개 | {site_name}` |
| service | `서비스 | {site_name}` |

Organization JSON-LD는 `seo-meta.php` 기본 출력 활용.

---

## 빌더 적용 시 주의

- 서브는 **`page/_init.php`** 패턴 유지
- about/service 빌더 페이지 → `page/about.php` HTML만 교체
- GNB `get_menu_db()` 링크와 메뉴관리 ID 일치

---

## Cursor 프롬프트 예시

```
presets/company-intro.md 기준으로 page/about.php, page/service.php와
메인 section 순서를 정리해주세요. 빌더 About/Service HTML: [붙여넣기]

코어 수정 금지. 작업 전 파일 목록. git/FTP 금지.
```
