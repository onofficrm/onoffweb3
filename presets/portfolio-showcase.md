# 목적별 프리셋: 포트폴리오 쇼케이스 (portfolio-showcase)

제작·시공·디자인 **이미지 중심** 사이트 조합입니다.

---

## 목적

- 작품·사례 **비주얼** 강조
- 갤러리·그리드 탐색
- 문의 전환

---

## 추천 메뉴 구조

| 메뉴 | 링크 |
|------|------|
| 포트폴리오 | `bbs/board.php?bo_table=portfolio` |
| 갤러리 | `bbs/board.php?bo_table=gallery` |
| 프로세스 | `/page/service.php` 또는 `#section-process` |
| 문의 | `/page/contact.php` |

---

## 추천 메인 섹션 흐름

```
hero → portfolio(정적 미리보기) → latest(portfolio) → process → review → contact
```

| 섹션 | 역할 |
|------|------|
| `hero` | 대표 비주얼 1장 |
| `portfolio` | 카드 6~12 (정적) + "더보기" → 게시판 |
| `latest` | 최신 사례 (`portfolio` bo_table) |
| `process` | `process.php` 신규 (선택) |
| `contact` | 견적 문의 |

---

## 추천 게시판 구성

| bo_table | 스킨 |
|----------|------|
| `portfolio` | gallery-grid |
| `gallery` | gallery-masonry |
| `review` | basic-card |

**설정:** 파일 업로드 필수, 목록 썸네일·이미지 fallback 확인.

---

## 추천 CTA 문구

- `포트폴리오 보기` / `프로젝트 문의` / `견적 받기`

---

## 추천 SEO 구조

- 이미지 alt·게시판 글 제목에 **프로젝트명·지역·업종 키워드**
- `og_image` 대표 작품 1장 (`_site.config.php`)

---

## 빌더 적용 시 주의

- 빌더 갤러리 Masonry → `gallery-masonry` 스킨 + `section/portfolio.php` 정적 그리드 **역할 분리**
- 이미지 lazy·용량 — `img/main/` vs 게시판 첨부 구분
- **wr_1 유튜브** 스킨과 혼용 시 게시판 분리 (`video` vs `portfolio`)

---

## Cursor 프롬프트 예시

```
presets/portfolio-showcase.md 기준으로 section/portfolio.php와
gallery-grid 스킨 색상을 맞춰주세요. 빌더 갤러리 HTML: [붙여넣기]

수정: section/portfolio.php, custom.css, g5b-board.css만.
코어/basic 금지. 작업 전 파일 목록.
```
