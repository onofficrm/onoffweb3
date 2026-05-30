# 목적별 프리셋: 지역·매장·서비스업 (local-business)

**지역 키워드·매장·병원·학원·서비스업** 등 범용 로컬 비즈니스 조합입니다. (특정 업종 MD 아님)

---

## 목적

- 지역명 + 서비스 키워드 SEO
- 오시는 길·전화·카카오 **즉시 연락**
- 후기·FAQ로 신뢰

---

## 추천 메뉴 구조

| 메뉴 | 링크 |
|------|------|
| 서비스 | `#section-service` |
| 오시는 길 | `#section-location` 또는 contact |
| 후기 | `#section-review` |
| FAQ | `#section-faq` |
| 문의 | `#section-contact` |

---

## 추천 메인 섹션 흐름

```
hero → service → advantage → location → review → faq → contact
```

| 섹션 | 비고 |
|------|------|
| `location` | `section/location.php` 신규 + `components/kakao-map.php` include |
| `review` | 후기 카드 또는 `review` 게시판 |
| `contact` | 전화·카카오 강조 |

`kakao_map_key` → `_site.config.php` (없으면 placeholder).

---

## 추천 게시판 구성

| bo_table | 스킨 |
|----------|------|
| `notice` | basic-notice |
| `column` | post-thumb | 지역 정보·팁 |
| `review` | basic-card |
| `gallery` | gallery-grid | 시설·현장 사진 |

---

## 추천 CTA 문구

- Hero: `전화 상담` (`tel:`) + `카카오톡 채널`
- `floating-buttons` — 전화·카카오·상담 (tail 기본)

---

## 추천 SEO 구조

| 항목 | 예 |
|------|-----|
| `main_keyword` | `{지역} {서비스}` |
| `seo_description` | 지역·영업시간·핵심 서비스 1문장 |
| JSON-LD | Organization + (추후 LocalBusiness 확장 가능) |

---

## 빌더 적용 시 주의

- 지도는 **카카오 JavaScript 키** 없으면 placeholder — 문구만 교체
- 전화번호 `_site.config.php` ↔ `section/contact.php` ↔ `tail.php` **일치**
- 지역 키워드 **H1 중복** — hero 1개만

---

## Cursor 프롬프트 예시

```
presets/local-business.md 기준으로 section/location.php를 만들고
kakao-map 컴포넌트를 include해주세요. 메인 섹션 순서도 반영.

_site.config.php address, phone, kakao_map_key 반영.
코어/basic 금지. 작업 전 파일 목록. git/FTP 금지.
```
