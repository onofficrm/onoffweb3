# 목적별 프리셋: 랜딩·전환 (landing-conversion)

> **업종별 프리셋이 아닙니다.** 상담·이벤트·단일 서비스 **전환**에 맞춘 페이지·섹션·게시판 조합 가이드입니다.

---

## 목적

- 상담·견적 문의 **전환 극대화**
- 이벤트·프로모션·단일 서비스 홍보
- 스크롤 한 페이지 또는 짧은 메인 + 강한 CTA

---

## 추천 메뉴 구조

| 메뉴 | 링크 예 |
|------|---------|
| 홈 | `/` |
| 서비스 | `#section-service` 또는 `/page/service.php` |
| 후기 | `#section-review` |
| FAQ | `#section-faq` |
| 문의 | `#section-contact` |

GNB 항목은 **최소화** (5개 이하).

---

## 추천 메인 섹션 흐름

`index.php` → `$g5_main_sections` 예:

```
hero → service(또는 problem/solution) → advantage → review → faq → contact
```

| 빌더 블록 | section 파일 |
|-----------|--------------|
| Hero + CTA | `hero.php` |
| Problem / Pain | `problem.php` (신규) |
| Solution | `service.php` |
| Benefits | `advantage.php` |
| Social proof | `review.php` |
| FAQ | `faq.php` |
| Final CTA | `contact.php` + `components/bottom-cta.php` (tail) |

---

## 추천 게시판 구성

| bo_table | 스킨 | 용도 |
|----------|------|------|
| `review` | basic-card | 후기 (선택) |
| `faq` | basic-card | FAQ 긴 목록 (선택, 섹션 FAQ와 병행 가능) |
| `inquiry` | basic-clean | 1:1 문의 (선택) |

메인 FAQ는 **정적 `section/faq.php`** 가 더 흔함.

---

## 추천 CTA 문구

| 위치 | 문구 예 |
|------|---------|
| Hero | `무료 상담 신청` / `지금 문의하기` |
| 헤더 | `_site.config.php` → `consultation_text` |
| 하단 | `전화문의` + `카카오톡` + 상담 모달 |
| contact | 전화번호 + `consult-modal-open` |

---

## 추천 SEO 구조

| 항목 | 값 예 |
|------|------|
| `$page_title` (메인) | `{서비스명} | {site_name}` |
| `seo_description` | 전환 가치 1~2문장 |
| `main_keyword` | 핵심 키워드 1개 |
| `page_robots` | `index,follow` |

랜딩 단일 URL 집중 시 **canonical** 메인 URL 고정.

---

## 빌더 적용 시 주의

- CTA 버튼 class: `.btn .btn-primary`, 모달: `.consult-modal-open`
- **한 화면에 CTA 과다** → `contact` + `bottom-cta` 중복 조정
- Scroll Snap은 전환 페이지에서 **기본 off** 권장
- 전역 CSS로 `#bo_list` 덮어쓰기 금지

---

## Cursor 프롬프트 예시

```
presets/landing-conversion.md 기준으로 메인 섹션 흐름을 맞춰주세요.
index.php $g5_main_sections: hero, service, advantage, review, faq, contact.
빌더 HTML은 [붙여넣기].

조건: /bbs,/lib,/adm/basic 수정 금지. section/*.php와 custom.css만.
작업 전 파일 목록 제시. git/FTP 금지.
```
