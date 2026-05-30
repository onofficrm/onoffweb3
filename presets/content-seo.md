# 목적별 프리셋: 콘텐츠·SEO (content-seo)

블로그·칼럼·지역 키워드·**검색 유입** 중심 사이트 조합입니다.

---

## 목적

- SEO 콘텐츠·키워드 랜딩
- 블로그·칼럼·뉴스 **지속 발행**
- 메인에서 **최신글** 노출

---

## 추천 메뉴 구조

| 메뉴 | 링크 |
|------|------|
| 홈 | `/` |
| 칼럼 | `bbs/board.php?bo_table=column` |
| 블로그 | `bbs/board.php?bo_table=blog` |
| 소식 | `bbs/board.php?bo_table=news` |
| 문의 | `/page/contact.php` |

---

## 추천 메인 섹션 흐름

```
hero → latest → service(요약) → faq → contact
```

| 섹션 | 역할 |
|------|------|
| `hero` | 키워드·가치 제안 |
| `latest` | `story`, `news`, `blog` 등 최신글 카드 |
| `faq` | 롱테일 FAQ (정적) |
| `contact` | 문의 |

`section/latest.php`의 `$g5_latest_boards` 배열을 프로젝트 게시판 ID에 맞게 수정.

---

## 추천 게시판 구성

| bo_table | 스킨 | 용도 |
|----------|------|------|
| `blog` | post-media | 본문·이미지 중심 |
| `column` | post-thumb | 칼럼 목록 |
| `news` | basic-modern | 소식 |
| `review` | basic-card | 후기 (선택) |

---

## 추천 CTA 문구

- Hero: `최신 글 보기` → `#section-latest` 또는 게시판 링크
- 보조: `뉴스레터·상담` (낮은 강도)

---

## 추천 SEO 구조

| 항목 | 설정 |
|------|------|
| `_site.config.php` | `seo_title`, `seo_description`, `main_keyword`, `sub_keywords` |
| 게시판 글 | 제목·본문에 키워드 (관리자 작성) |
| 글보기 | `seo-meta`가 `$g5['title']` 반영 |
| `page/privacy.php` | 필수 링크 |

글별 SEO는 추후 `$page_*` 변수를 board view에 연동할 수 있음 (v1.0.0은 게시판 기본 title).

---

## 빌더 적용 시 주의

- **latest 섹션**은 게시판 ID 없어도 fallback 카드 — ID 연결 후 검수
- 본문 많은 카드 스킨 → `bo_use_list_content` on
- Tailwind typography를 `.post-media` 보기와 충돌 없게 `.board-wrap` 스코프 유지

---

## Cursor 프롬프트 예시

```
presets/content-seo.md 기준으로 section/latest.php 게시판 ID와
메인 섹션 순서(hero, latest, faq, contact)를 설정해주세요.
seo-meta와 _site.config.php main_keyword 반영.

코어/basic 수정 금지. 작업 전 파일 목록. git/FTP 금지.
```
