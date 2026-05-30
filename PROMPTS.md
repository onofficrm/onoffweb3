# AI 작업용 프롬프트 모음

그누보드 제작 베이스에 빌더 디자인·게시판·반응형을 적용할 때 Cursor 등 AI에 붙여 넣을 프롬프트입니다.

**새 프로젝트를 처음부터 진행할 때**는 순서형 [START-PROJECT-PROMPTS.md](START-PROJECT-PROMPTS.md)를 먼저 보세요.  
빌더 원본은 [/_BUILDER_INPUT/](_BUILDER_INPUT/)에 두고 분석 요청합니다.

**모든 프롬프트에 공통으로 포함할 주의사항:**

- 그누보드 **코어 파일 수정 금지** (`/bbs`, `/lib`, `/adm`, `common.php`)
- **원본 `/skin/board/basic`** 수정 금지
- 기존 **게시판·로그인·회원가입·관리자** 기능 유지
- **필요한 파일만** 수정
- 작업 **전** 수정/생성 예정 파일 목록 먼저 제시
- 작업 **후** 수정 파일 요약
- **git commit, push, FTP 배포 금지**

---

## 0. dist ZIP 독립 페이지 (onoff-builder-bridge)

```
onoff-g5-base에 plugin/onoff-builder-bridge가 있습니다.
_BUILDER_INPUT 또는 첨부한 Vite 빌드 dist ZIP을 기준으로:

1. 원본(src/App.tsx, package.json)인지 dist(index.html, assets)인지 판별
2. dist면 관리자 업로드 절차 안내 (import-form.php)
3. page.php?id= URL 예시
4. 섹션(section/) 자동 변환은 하지 않음을 명시

코어(/bbs, /lib, /adm, head.php) 수정 금지. git/배포 금지.
```

---

## 1. 빌더 결과물 분석 요청

```
아래는 빌더(또는 Figma/HTML)에서 export한 메인 페이지 코드입니다.
이 그누보드 베이스 템플릿에 붙이기 위해 분석해 주세요.

[코드 또는 파일 경로 붙여넣기]

분석해 줄 내용:
1. Hero / 서비스 / FAQ / CTA / 푸터 섹션 구분
2. custom.css :root 토큰과 매핑할 색·폰트·간격
3. section/*.php 로 쪼갤 때 파일 단위 제안
4. 게시판 영역과 충돌할 전역 CSS 여부
5. 수정하면 안 되는 경로

주의: /bbs, /lib, /adm, skin/board/basic 수정 금지.
작업 전 수정 예정 파일 목록만 먼저 제시해 주세요.
```

---

## 2. Hero 섹션만 적용 요청

```
빌더 Hero 섹션만 그누보드 베이스에 적용해 주세요.

대상: section/hero.php (필요 시 custom.css .section-hero 영역만)

조건:
- head.php/tail.php 구조 유지, 최소 수정
- :root 토큰(--color-primary 등) 활용
- _site.config.php site_name, consultation_text 참고 가능
- 코어·기본 게시판 스킨 수정 금지

작업 전: 수정/생성 파일 목록 제시
작업 후: 변경 요약
```

---

## 3. 메인 전체 섹션 적용 요청

```
index.php에 연결된 section/ 전체를 빌더 디자인에 맞게 업데이트해 주세요.

포함: hero, service, advantage, review, faq, contact, latest, portfolio 등
스타일: css/custom.css (.site-main 스코프)
설정: _site.config.php 연락처·CTA 문구 반영

주의:
- /bbs, /lib, /adm 수정 금지
- .board-wrap 게시판 스타일 덮어쓰기 금지
- components/quick-contact.php 등 기존 컴포넌트 class 유지

작업 전 파일 목록 → 작업 후 요약
```

---

## 4. 서브페이지 적용 요청

```
page/ 아래 서브페이지 템플릿에 빌더 서브 디자인을 적용해 주세요.

대상 예: page/about.php, page/service.php (실제 파일 확인 후)
공통: page/_init.php, .page-template 스코프

주의: 코어 수정 금지, 게시판/로그인 영향 없게

작업 전 수정 파일 목록 제시 후 진행
```

---

## 5. 게시판 스킨 색상 맞춤 요청

```
/css/custom.css :root 와 _site.config.php primary_color에 맞춰
게시판 10종 스킨 분위기를 통일해 주세요.

수정 가능: css/g5b-board.css, skin/board/*/style.css (10종만)
수정 금지: /bbs, /lib, /adm, skin/board/basic

하드코딩 색상은 토큰 기반으로만 변경. 레이아웃 변경 최소화.
README-BOARD-CSS.md 참고.

작업 전/후 파일 목록과 변경 요약
```

---

## 6. 모바일 반응형만 수정 요청

```
모바일(768px 이하) 레이아웃만 점검·수정해 주세요.

대상: css/custom.css, section/*.php, 필요 시 skin style.css @media
게시판 테이블→카드 변환은 g5b-board.css 유지

PC 레이아웃은 변경하지 마세요.
코어·basic 스킨 수정 금지.

작업 전 파일 목록 → 작업 후 요약
```

---

## 7. Scroll Snap 적용 요청

```
메인 #siteMain 에 Scroll Snap을 선택 적용해 주세요.

js/custom.js G5Template.config.scrollSnapEnabled 또는
css/custom.css .site-main.snap-enabled 만 수정

접근성·모바일 스크롤 문제 없는지 확인
다른 페이지·게시판에는 적용하지 마세요

작업 전/후 요약
```

---

## 8. 문의 모달·CTA 적용 요청

```
빌더의 문의 모달·CTA·하단 고정 버튼을 그누보드 베이스에 맞춰주세요.

대상:
- section/contact.php (contact-cta)
- components/consult-modal.php (#cmpConsultModal)
- components/floating-buttons.php, bottom-cta.php (tail include 유지)
- js/custom.js — consult-modal-open, initConsultModal

조건:
- class: .btn .btn-primary / .btn-outline, .consult-modal-open, data-target="#cmpConsultModal"
- _site.config.php phone, kakao_url, consultation_text 반영
- /bbs, /lib, /adm, basic 스킨 수정 금지
- head.php/tail.php 대규모 삭제 금지

작업 전 파일 목록 → 작업 후 요약. git/FTP 금지.
```

---

## 9. 전체 검수 요청

```
그누보드 제작 베이스 전체를 검수해 주세요.

항목:
- PHP 문법 (head/tail/_site.config.php/components)
- 로그인/회원가입/관리자/게시판 링크
- custom.css / custom.js 로딩
- 상담 모달·floating 버튼
- 코어(/bbs,/lib,/adm) 수정 여부
- skin/board/basic 수정 여부
- 민감정보(dbconfig) 포함 여부

수정은 발견된 오류만. git commit/FTP 하지 마세요.
```

---

## 10. 새 프로젝트 복사 후 초기 설정 요청

```
폴더를 복사해 새 홈페이지를 만들었습니다.
README-START.md 기준으로 초기 설정 체크리스트를 적용해 주세요.

1. _site.config.php 항목 채우기 (샘플→실값)
2. logo, og_image 경로 확인
3. 관리자 메뉴·게시판 스킨 권장 매핑
4. SAMPLE-CONTENT.md 참고 샘플 문구 제안

코어 수정 금지. data/dbconfig.php는 건드리지 말고
dbconfig.local.php.example 안내만 확인.

작업 전 수정 파일 목록 제시
```

---

## 참고 경로

| 문서 | 내용 |
|------|------|
| [BUILDER-WORKFLOW.md](BUILDER-WORKFLOW.md) | 빌더 적용 순서 |
| [SECTION-GUIDE.md](SECTION-GUIDE.md) | 섹션 구조 |
| [BOARD-SKIN-GUIDE.md](BOARD-SKIN-GUIDE.md) | 게시판 10종 |
| [presets/](presets/) | 목적별 페이지 조합 (업종별 아님) |
| [README-START.md](README-START.md) | 복사 후 시작 |
| [README-BUILDER-TO-GNUBOARD.md](README-BUILDER-TO-GNUBOARD.md) | 빌더→섹션 상세 |
| [README-BOARD-SKINS.md](README-BOARD-SKINS.md) | 게시판 운영 |
| [SAMPLE-CONTENT.md](SAMPLE-CONTENT.md) | 샘플 글·문구 |
| [setup/replace-checklist.md](setup/replace-checklist.md) | 복사 체크리스트 |
| [START-PROJECT-PROMPTS.md](START-PROJECT-PROMPTS.md) | 프로젝트 시작 순서 |
| [LAUNCH-CHECKLIST.md](LAUNCH-CHECKLIST.md) | 납품 전 검수 |
| [CLIENT-MANUAL.md](CLIENT-MANUAL.md) | 고객·관리자 운영 |
| [_BUILDER_INPUT/README.md](_BUILDER_INPUT/README.md) | 빌더 임시 보관 |
