# 새 프로젝트 시작용 Cursor 프롬프트

onoff-g5-base 폴더를 **복사한 뒤** Cursor에 **순서대로** 붙여 넣을 프롬프트 모음입니다.

| 단계 | 관련 문서 |
|------|-----------|
| 복사·설정 | [README-START.md](README-START.md), [setup/replace-checklist.md](setup/replace-checklist.md) |
| 빌더 적용 | [BUILDER-WORKFLOW.md](BUILDER-WORKFLOW.md), [_BUILDER_INPUT/README.md](_BUILDER_INPUT/README.md) |
| 작업 중 | [PROMPTS.md](PROMPTS.md) (세부 작업별) |
| 납품 전 | [LAUNCH-CHECKLIST.md](LAUNCH-CHECKLIST.md) |
| 고객 전달 | [CLIENT-MANUAL.md](CLIENT-MANUAL.md) |

---

## 공통 주의사항 (모든 프롬프트에 포함)

- 그누보드 **코어 수정 금지** (`common.php`, `config.php` 등)
- **`/bbs`, `/lib`, `/adm` 수정 금지**
- **원본 `/skin/board/basic`, `/skin/board/gallery` 수정 금지**
- 기존 **게시판·글쓰기·내용보기·로그인·회원가입·관리자** 기능 유지
- **필요한 파일만** 수정
- 작업 **전** 수정·생성 예정 **파일 목록 먼저** 제시
- 작업 **후** 수정 파일 **요약**
- **git commit, push, FTP 배포 금지**

---

## 1. 새 프로젝트 복사 후 기본 점검

```
onoff-g5-base 폴더를 복사해 새 홈페이지 프로젝트를 시작했습니다.
아직 코드는 수정하지 말고 구조만 점검해 주세요.

확인할 항목:
- 루트 _site.config.php, setup/site.sample.json
- components/, section/, page/, skin/board 커스텀 10종
- head.php, tail.php, index.php section include
- _BUILDER_INPUT 작업 폴더 (빌더 보관용)
- _BASE_INFO 문서

출력:
- 현재 구조 요약
- 수정하면 안 되는 경로 (/bbs, /lib, /adm, skin/board/basic)
- 다음에 할 작업 순서 제안

git commit, push, FTP 배포 금지.
```

---

## 2. site.sample.json 기준 초기 설정 반영

```
/setup/site.sample.json(또는 제가 채운 setup/site.json) 값을 기준으로
/_site.config.php, 푸터·헤더·하단 버튼에 반영해 주세요.

반영 항목:
- site_name, company_name, ceo_name, business_no
- phone, kakao_url, email, address
- primary_color, secondary_color, logo_path, og_image
- consultation_text, footer_desc
- seo_title, seo_description, main_keyword (있으면)

조건:
- head.php의 :root 인라인 색상과 맞출 것
- /bbs, /lib, /adm, skin/board/basic 수정 금지
- data/dbconfig.php는 수정·공유하지 말 것

작업 전 수정 파일 목록 → 작업 후 요약.
git commit, push, FTP 배포 금지.
```

---

## 3. 빌더 결과물 분석

```
/_BUILDER_INPUT/app 와 /_BUILDER_INPUT/assets, /_BUILDER_INPUT/screenshots 를 분석해 주세요.

분석 내용:
1. App.tsx / Home.tsx / Header / Footer / 섹션 컴포넌트 구분
2. assets 이미지 목록과 /img/main, /img/common 매핑 제안
3. section/*.php 파일 단위 변환 계획 (index.php $g5_main_sections 순서 제안)
4. Tailwind → custom.css :root 토큰 매핑
5. 모달·애니메이션 → custom.js 연동 계획
6. 게시판·default.css와 충돌할 전역 CSS 위험

아직 파일 수정하지 말고 계획과 수정 예정 파일 목록만 제시.

/BUILDER-WORKFLOW.md, /SECTION-GUIDE.md 참고.
git commit, push, FTP 배포 금지.
```

---

## 4. Hero 섹션 적용

```
/_BUILDER_INPUT 기준(또는 아래 붙여넣은 HTML)으로 Hero만 적용해 주세요.

대상:
- section/hero.php
- css/custom.css .section-hero 영역만

조건:
- head.php, tail.php 구조 유지·최소 수정
- index.php include 구조 유지
- /bbs, /lib, /adm, skin/board/basic 수정 금지
- 적용 후 PC·모바일(768px) 확인 항목 안내

[빌더 Hero 코드 또는 경로]

작업 전 파일 목록 → 작업 후 요약.
git commit, push, FTP 배포 금지.
```

---

## 5. 메인 전체 섹션 적용

```
빌더 메인을 section 단위로 그누보드에 적용해 주세요.

대상:
- index.php $g5_main_sections 순서 (필요 시만)
- section/*.php (hero, service, advantage, portfolio, latest, review, faq, contact 등)
- css/custom.css (.site-main 스코프)
- js/custom.js (FAQ, reveal, 앵커 등 필요한 부분만)

조건:
- components/quick-contact, consult-modal class 유지
- .board-wrap 게시판 CSS 덮어쓰기 금지
- /bbs, /lib, /adm, basic 스킨 수정 금지

/_BUILDER_INPUT/app 참고 가능.

작업 전 파일 목록 → 작업 후 요약.
git commit, push, FTP 배포 금지.
```

---

## 6. 서브페이지 적용

```
빌더 서브 디자인을 /page/*.php 에 적용해 주세요.

대상 예: page/about.php, service.php, portfolio.php, contact.php
공통: page/_init.php, g5_page_start/g5_page_end 유지

각 페이지:
- $page_title, $page_description (seo-meta용) 설정
- .page-template 스코프

head.php, tail.php 대규모 삭제 금지.
코어·게시판·로그인 영향 없게.

작업 전 파일 목록 → 작업 후 요약.
git commit, push, FTP 배포 금지.
```

---

## 7. 게시판 스킨 색상 맞춤

```
css/custom.css :root 와 _site.config.php primary_color에 맞춰
게시판 10종 스킨 색상을 통일해 주세요.

수정 가능:
- css/g5b-board.css
- skin/board/{10종}/style.css

수정 금지:
- /bbs, /lib, /adm
- skin/board/basic, skin/board/gallery
- 게시판 list/write/view PHP 로직

README-BOARD-CSS.md, BOARD-SKIN-GUIDE.md 참고.

작업 전/후 파일 목록과 요약.
git commit, push, FTP 배포 금지.
```

---

## 8. 모바일 반응형 검수

```
모바일 반응형만 점검·수정해 주세요.

기준: 1024px, 768px, 480px
대상: css/custom.css @media, section 마크업 최소 수정
게시판: g5b-board.css 테이블→카드 유지

PC(1025px+) 레이아웃은 변경하지 마세요.
햄버거 메뉴, 플로팅 버튼, CTA, 모달, 이미지 비율 확인.

작업 전 파일 목록 → 작업 후 요약.
git commit, push, FTP 배포 금지.
```

---

## 9. 최종 검수

```
제작이 거의 끝났습니다. LAUNCH-CHECKLIST.md 항목 기준으로 전체 검수해 주세요.

점검:
- 메인, 서브, 게시판(목록/쓰기/보기), 로그인, 회원가입, 관리자
- 문의 모달, floating, SEO 메타, 모바일
- style-guide.php, _BUILDER_INPUT 운영 제외 여부(문서 안내)
- dbconfig, API 키, FTP 정보 노출 여부

발견된 문제만 최소 수정.
/bbs, /lib, /adm, basic 스킨 수정 금지.

git commit, push, FTP 배포 금지.
작업 전/후 요약.
```

---

## PROMPTS.md와의 관계

| START-PROJECT-PROMPTS (본 문서) | PROMPTS.md |
|--------------------------------|------------|
| **프로젝트 시작 ~ 납품** 순서 | Hero·Scroll Snap·게시판 등 **세부** 작업 |
| 9단계 온보딩 | 10종 작업별 템플릿 |

Scroll Snap, 문의 모달만 따로 요청할 때는 [PROMPTS.md](PROMPTS.md) §7·§8을 사용하세요.
