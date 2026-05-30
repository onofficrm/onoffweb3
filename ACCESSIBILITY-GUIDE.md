# 접근성 가이드

상담 모달, 모바일 메뉴, FAQ, 버튼, 폼 등에서 **기본적인 웹 접근성**을 지키기 위한 기준입니다.  
WCAG 2.1 AA 전체 준수 매뉴얼이 아니라, onoff-g5-base **납품·빌더 적용** 시 실무에서 확인할 항목에 초점을 맞췄습니다.

| 관련 문서 |
|-----------|
| [PERFORMANCE-GUIDE.md](PERFORMANCE-GUIDE.md) — `prefers-reduced-motion` 등 |
| [IMAGE-GUIDE.md](IMAGE-GUIDE.md) — alt·파일명 |
| [SECTION-GUIDE.md](SECTION-GUIDE.md) — FAQ·섹션 |
| [INQUIRY-FORM-GUIDE.md](INQUIRY-FORM-GUIDE.md) — 문의 폼 |
| [LAUNCH-CHECKLIST.md](LAUNCH-CHECKLIST.md) — 납품 전 점검 |

---

## 1. 접근성이 중요한 이유

| 대상 | 이유 |
|------|------|
| **키보드·스크린리더 사용자** | 마우스 없이도 메뉴·문의·FAQ 이용 |
| **고령·저시력 사용자** | 대비·터치 영역·명확한 라벨 |
| **모바일 전체** | 터치 타깃·줌·가독성 |
| **법·공공·대기업 납품** | 웹 접근성 품질인증·계약 요구 증가 |
| **SEO·사용성** | 구조화된 HTML은 검색·유지보수에도 유리 |

접근성은 디자인 적용 **후반**에 한 번에 맞추기 어렵습니다. 빌더 export 시 버튼·폼·모달부터 점검하세요.

---

## 2. 버튼

### 2.1 기본 원칙

| 권장 | 비권장 |
|------|--------|
| **`<button type="button">`** (동작) | `<div onclick>` / `<a href="#">` 로 토글 |
| **`<a href="URL">`** (이동) | 링크인데 `button` 사용 |
| **보이는 텍스트** 또는 **`aria-label`** | 아이콘만 있고 이름 없음 |
| **최소 터치 44×44px** (모바일) | 32px 이하 아이콘 버튼 |

### 2.2 베이스 예시

| 위치 | 구현 |
|------|------|
| 상담 열기 | `.consult-modal-open` — `button` 권장 |
| 모달 닫기 | `aria-label="닫기"` ([components/consult-modal.php](components/consult-modal.php)) |
| 모바일 메뉴 | `aria-controls="siteMobileNav"` `aria-expanded` ([head.php](head.php)) |
| FAQ | `.faq-question` — `button type="button"` ([section/faq.php](section/faq.php)) |

그누보드 관례 `sound_only` 클래스는 스크린리더용 보조 텍스트로 유지합니다.

### 2.3 링크 vs 버튼

| 역할 | 태그 | 예 |
|------|------|-----|
| **다른 URL로 이동** | `<a href="...">` | 메뉴, 글보기, 전화 `tel:` |
| **화면 내 동작** | `<button>` | 모달 열기, FAQ 펼치기, 메뉴 토글 |
| **새 탭** | `target="_blank"` + **「새 창」 안내** | 외부 링크 |

---

## 3. 이미지

상세: [IMAGE-GUIDE.md](IMAGE-GUIDE.md)

| 유형 | alt |
|------|-----|
| **정보 전달** (제품, 인물, 차트) | 내용을 설명하는 짧은 문장 |
| **링크·버튼 안 이미지** | 링크/버튼 목적과 동일 |
| **장식·배경** | `alt=""` 또는 `role="presentation"` |
| **아이콘 폰트(FA)** | 인접 텍스트 또는 `aria-hidden="true"` + 라벨 |

파일명은 `hero-main.webp`처럼 의미 있게 — 스크린리더가 읽지는 않지만 SEO·관리에 유리합니다.

---

## 4. 폼

베이스: [components/consult-modal.php](components/consult-modal.php)

### 4.1 label 연결

```html
<label class="cmp-form-label" for="cmp_consult_name">이름 <span class="cmp-form-required">*</span></label>
<input type="text" id="cmp_consult_name" name="name" required>
```

- 모든 입력에 **`for` / `id` 쌍**
- placeholder만으로 label 대체 **금지**

### 4.2 필수·오류 안내

| 항목 | 권장 |
|------|------|
| 필수 | `*` + `required` + 문구「필수」 |
| 선택 | `(선택)` 표기 |
| 오류 | `role="status"` `aria-live="polite"` — 베이스 `.cmp-consult-form__status` |
| 검증 실패 | 첫 오류 필드로 **포커스 이동** (추후 JS 보강 권장) |

### 4.3 개인정보 동의

- 체크박스 + **클릭 가능한 label** (`cmp-privacy-agree__label`)
- 수집 항목·목적·보관 기간 요약 ([page/privacy.php](page/privacy.php)와 문구 일치)
- 동의 없이 제출 불가 (`required`)

### 4.4 모바일 입력 편의성

| 필드 | input type / 속성 |
|------|-------------------|
| 연락처 | `type="tel"` `autocomplete="tel"` |
| 이메일 | `type="email"` `autocomplete="email"` |
| 이름 | `autocomplete="name"` |
| 글자 크기 | `font-size` **16px 이상** (iOS 줌 방지) |

---

## 5. 모달 (상담 문의)

베이스: `components/consult-modal.php` + `js/custom.js` → `initConsultModal()`

### 5.1 현재 베이스 동작

| 항목 | 상태 |
|------|------|
| `role="dialog"` `aria-modal="true"` | ✅ |
| `aria-labelledby` (제목) | ✅ |
| `aria-hidden` 열림/닫힘 | ✅ |
| ESC 닫기 | ✅ |
| 오버레이·닫기 버튼 클릭 | ✅ |
| `lockBodyScroll` (배경 스크롤 잠금) | ✅ |
| **포커스 트랩** (Tab 순환) | ⚠️ 추후 보강 권장 |
| **열 때 포커스 이동** (첫 필드/닫기) | ⚠️ 추후 보강 권장 |
| **닫을 때 트리거로 포커스 복귀** | ⚠️ 추후 보강 권장 |

### 5.2 체크 포인트

- 닫기 버튼에 **「닫기」** (`aria-label` 또는 `sound_only`)
- 열기 트리거는 **`button`**, `consult-modal-open`
- 모달 안에서만 Tab 이동 가능한지 키보드로 확인
- 배경 콘텐츠가 `aria-hidden`으로 가려지는지 (스크린리더가 배경 읽지 않도록 — 고급: `inert` 속성 검토)

---

## 6. 모바일 메뉴

베이스: `head.php` + `js/custom.js` → `initMobileMenu()`

### 6.1 현재 베이스 동작

| 항목 | 상태 |
|------|------|
| 열기 버튼 `aria-controls` / `aria-expanded` | ✅ |
| 메뉴 `aria-hidden` | ✅ |
| ESC·오버레이·닫기·바깥 클릭 | ✅ |
| `lockBodyScroll` | ✅ |
| 닫기 `sound_only` | ✅ (`aria-label` 추가 권장) |

### 6.2 키보드·포커스

- Tab으로 메뉴 버튼까지 도달 가능한지
- Enter/Space로 열기·닫기
- 열린 뒤 **첫 링크로 포커스** (추후 보강)
- 서브메뉴가 있으면 **펼침 상태** `aria-expanded` (빌더 추가 시)

### 6.3 빌더 적용 시

- 햄버거만 있고 **텍스트 라벨 없음** → `aria-label="전체메뉴"` 또는 `sound_only` 유지
- 메뉴 링크 간격 **44px 이상** 터치 높이

---

## 7. FAQ 아코디언

베이스: [section/faq.php](section/faq.php) · 게시판 [skin/board/faq-accordion](skin/board/faq-accordion/) + `initFaqAccordion()`

### 7.1 구조

```html
<div class="faq-item">
  <button type="button" class="faq-question" aria-expanded="false">질문</button>
  <div class="faq-answer"><p>답변</p></div>
</div>
```

| 항목 | 권장 |
|------|------|
| 질문 | **`button`** (링크 아님) |
| `aria-expanded` | true/false — 베이스 JS가 토글 ✅ |
| `aria-controls` | 답변 영역 `id` 연결 — **faq-accordion** 목록 ✅ |
| 답변 | 버튼 **다음** 형제 — DOM 순서 유지 |
| 제목 계층 | 섹션 `h2` + 질문은 `button` (질문을 `h3`로 감싸지 않음 — 중복 랜드마크 주의) |
| **focus** | `:focus-visible` outline 유지 — 스킨 CSS에서 제거 금지 |
| **fallback** | `faq-item__view-link` — JS 없이 내용보기 이동 |

### 7.2 게시판 스킨 (`faq-accordion`)

| 항목 | 내용 |
|------|------|
| 아코디언 모드 | `data-accordion-mode="multiple"` — 여러 FAQ 동시 열림 |
| 랜딩 섹션 | `section/faq.php` — `data-accordion-mode="single"` (첫 항목 펼침 유지) |
| 터치 | 질문 버튼 최소 높이·모바일 「답변 보기」 링크 44px 권장 |

### 7.3 추후 보강 (선택)

- `aria-controls="faq-answer-1"` + 답변 `id="faq-answer-1"` (게시판 목록 적용됨)
- `hidden` 속성 또는 `aria-hidden`으로 닫힌 답변 스크린리더 처리
- 키보드 **Arrow Up/Down** (선택)

### 7.4 FAQPage Schema

화면 FAQ와 Schema 내용 일치 — [SECTION-GUIDE.md](SECTION-GUIDE.md) §8

---

## 8. 색상 대비

베이스 토큰: `css/custom.css` `:root`

| 용도 | 변수·주의 |
|------|-----------|
| 본문 | `--color-text` on `--color-bg` |
| 보조 문구 | `--color-muted` (#64748b) — **작은 글씨·얇은 폰트**는 대비 부족 주의 |
| 버튼 primary | `--color-on-primary` on `--color-primary` |
| 다크 섹션 (`section--dark`) | 흰색 계열 텍스트·버튼 outline 대비 |

### 8.1 목표 (실무 기준)

| 요소 | 권장 |
|------|------|
| 일반 텍스트 | 대비 **4.5:1** 이상 (WCAG AA) |
| 큰 텍스트(18px+ bold 등) | **3:1** 이상 |
| UI 컴포넌트·아이콘 | **3:1** 이상 |

### 8.2 빌더 적용 시

- `#999` on `#fff` 본문 — **위험**
- primary 버튼 위 흰 글자 — primary 색이 너무 밝으면 실패
- 링크만 색으로 구분 — **밑줄** 또는 굵기 병행
- `:focus-visible` 스타일 유지·강화 ([custom.css](css/custom.css) 버튼 `:focus` 규칙 참고)

점검 도구: Chrome DevTools → Accessibility / Lighthouse Accessibility / [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)

---

## 9. 체크리스트 (납품·오픈 전)

### 키보드

- [ ] Tab으로 헤더 메뉴·CTA·본문 링크·푸터까지 순서 논리적
- [ ] **Shift+Tab** 역순 이동 가능
- [ ] 포커스 링크·버튼 **시각적으로 보임** (`:focus-visible`)
- [ ] FAQ `button` — Enter/Space로 열기·닫기

### 모달·모바일 메뉴

- [ ] 상담 모달 ESC·오버레이·닫기로 닫힘
- [ ] 모달 열린 동안 배경 스크롤 안 됨
- [ ] 모바일 메뉴 열기/닫기·바깥 클릭·ESC
- [ ] (권장) 모달·메뉴 **포커스 트랩** 수동 확인

### 폼

- [ ] 모든 필드 **label 연결**
- [ ] 필수·선택 표시
- [ ] 오류 시 메시지 인지 가능 (`aria-live` / 시각 표시)
- [ ] 개인정보 동의 체크 + 정책 링크

### 이미지·버튼

- [ ] 의미 있는 `alt` / 장식 `alt=""`
- [ ] 아이콘-only 버튼에 이름 (`aria-label` / `sound_only`)
- [ ] 모바일 CTA·플로팅 버튼 **터치 44px** 이상

### 색·동작

- [ ] 본문·버튼·푸터 대비 확인
- [ ] `prefers-reduced-motion` — 과한 애니메이션 [PERFORMANCE-GUIDE.md](PERFORMANCE-GUIDE.md)
- [ ] Lighthouse **Accessibility** 90+ 목표 (참고)

---

## 10. 베이스 구현 요약 (현재)

| 영역 | 파일 | 비고 |
|------|------|------|
| 모바일 메뉴 | `head.php`, `js/custom.js` | aria-expanded, ESC, scroll lock |
| 상담 모달 | `consult-modal.php`, `custom.js` | dialog, aria-hidden, ESC |
| FAQ | `section/faq.php`, `custom.js` | button, aria-expanded |
| 폼 | `consult-modal.php` | label, required, live region |
| 그누보드 | `sound_only` | 스크린리더 전용 텍스트 관례 |

---

## 11. 추후 기능 파일에 반영하면 좋은 항목

기능 변경 시 최소 범위로 적용 (코어 `/bbs` 수정 불필요).

| 우선순위 | 항목 | 대상 파일 |
|:--------:|------|-----------|
| **높음** | 모달 **포커스 트랩** + 열 때 첫 입력 포커스 + 닫을 때 트리거 복귀 | `js/custom.js` `initConsultModal` |
| **높음** | 모바일 메뉴 닫기 `aria-label="메뉴 닫기"` | `head.php` |
| **중간** | FAQ `aria-controls` / 답변 `id` / `hidden` | `section/faq.php`, `custom.js` |
| **중간** | 폼 검증 실패 시 **첫 오류 필드 focus** + `aria-invalid` | `custom.js` `initConsultForm` |
| **중간** | 모달 열릴 때 **배경 `inert`** (지원 브라우저) | `custom.js` |
| **낮음** | Skip link 「본문 바로가기」 | `head.php` |
| **낮음** | `:focus-visible` 전역 outline 통일 | `custom.css` |

게시판 스킨 SEO 작업 시 **view 제목 h1**, 관련글 블록은 접근성·SEO 동시 이점 — [BOARD-SKIN-GUIDE.md](BOARD-SKIN-GUIDE.md) 예정 작업과 병행.

---

## 12. Cursor 검수 프롬프트 예시

```
이 프로젝트 접근성만 점검해주세요.
ACCESSIBILITY-GUIDE.md 기준, git/배포/bbs/lib/adm 수정 금지.
키보드·모달·FAQ·폼·대비 위주로 목록만 보고, 수정은 항목별 최소 diff.

확인: consult-modal, siteMobileNav, section/faq.php, custom.js initConsultModal/initMobileMenu/initFaqAccordion.
```

---

## 13. 관련 문서

| 문서 | 용도 |
|------|------|
| [PERFORMANCE-GUIDE.md](PERFORMANCE-GUIDE.md) | reduced motion |
| [SEO-CHECKLIST.md](SEO-CHECKLIST.md) | 납품 |
| [CLIENT-MANUAL.md](CLIENT-MANUAL.md) | 고객 안내 |
| [CLEANUP-PROMPTS.md](CLEANUP-PROMPTS.md) | 최종 검수 프롬프트 |
