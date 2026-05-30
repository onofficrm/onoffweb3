# 게시판 생성 · 설정 — Cursor 프롬프트

새 프로젝트에서 게시판 계획·가이드·검수를 Cursor에 요청할 때 복사해 쓰는 프롬프트 모음입니다.

| 참고 문서 |
|-----------|
| [BOARD-CREATE-GUIDE.md](BOARD-CREATE-GUIDE.md) |
| [setup/project.sample.json](setup/project.sample.json) |
| [BOARD-SKIN-GUIDE.md](BOARD-SKIN-GUIDE.md) |

---

## 공통 주의사항 (모든 프롬프트에 포함)

- git commit, push, FTP 배포 **하지 않음**
- `/bbs`, `/lib`, `/adm` 코어 **수정하지 않음**
- `/bbs/write_update.php`, `skin/board/basic` **수정하지 않음**
- **실제 DB INSERT·게시판 자동 생성 SQL 실행 금지** (계획·가이드·검수만)
- **관리자 화면 수동 생성**을 1차로 안내
- 작업 전 **생성/수정 예정 파일 목록** 제시
- 작업 후 **요약** 출력

---

## 1. 게시판 생성 계획 수립

```
현재 onoff-g5-base 프로젝트입니다.

/setup/project.sample.json 의 boards 항목을 분석하고,
이 프로젝트(업종: [병원/학원/법률/일반회사 등])에 맞는 게시판 생성 계획을 정리해주세요.

포함할 내용:
- 생성할 bo_table 목록 (필요한 것만)
- 각 게시판 제목, 추천 스킨, 용도
- read_level / write_level 권한 추천
- 분류·비밀글·댓글 사용 여부
- 메뉴 연결 URL 예시

조건:
- 실제 DB 수정·SQL 실행·create-default-boards.example.php 실행 금지
- setup/project.sample.json 수정안은 제안만 (적용 전 목록 제시)
- BOARD-CREATE-GUIDE.md, BOARD-SKIN-GUIDE.md 기준
```

---

## 2. 관리자 수동 생성 가이드 작성

```
BOARD-CREATE-GUIDE.md를 기준으로,
이 프로젝트에 맞는 「관리자에서 게시판 만드는 순서」 체크리스트를 작성해주세요.

대상 게시판: [notice, column, portfolio, faq, inquiry 등 나열]

각 게시판별로:
- bo_table / 제목
- PC·모바일 스킨 (동일명)
- 권한(목록/읽기/쓰기)
- 분류·비밀글·댓글·공지·목록에서 내용 사용
- 메뉴 URL

DB 직접 수정 금지. 문서 출력만.
```

---

## 3. 게시판 생성 후 검수

```
게시판 생성·설정 작업을 검수해주세요.

확인 대상 bo_table: [목록]

검수 항목:
- /bbs/board.php?bo_table= 각 URL 접근
- 목록·글쓰기·내용보기·검색·분류·페이지네이션
- PC/모바일 스킨 적용 (skin/board vs mobile/skin/board 동일명)
- 권한(비회원 읽기/쓰기)
- 코어 파일 수정 여부

코어·DB 변경 없이 읽기 전용 검수. 문제만 최소 수정.
```

---

## 4. 문의 게시판 설정 확인

```
inquiry(상담문의) 게시판 설정을 검수해주세요.

확인:
- bo_table inquiry 존재 여부 (문서·설정 기준, DB 직접 조작 금지)
- 스킨 landing-inquiry (PC·모바일)
- 읽기 권한 관리자 권장 여부
- 비밀글·쓰기 권한(비회원 문의)
- INQUIRY-FORM-GUIDE.md, proc/inquiry-submit.php 연동
- _site.config.php inquiry_bo_table, 알림 설정

기능 코드 수정은 문제 있을 때만 최소 수정.
```

---

## 5. FAQ 게시판 설정 확인

```
faq 게시판 설정을 검수해주세요.

확인:
- bo_table faq, 스킨 faq-accordion (PC·모바일)
- 제목=질문, 본문=답변 구조
- 목록에서 내용 사용 (아코디언)
- FAQPage Schema (목록·글보기, components/schema/faq.php)
- custom.js 아코디언 (요소 없을 때 오류 없음)

DB·코어 수정 금지.
```

---

## 6. project.sample.json 보완

```
setup/project.sample.json 의 boards 배열을
[프로젝트명/업종]에 맞게 보완안을 제시해주세요.

- JSON 문법 유효
- bo_table 중복 없음
- 스킨명은 skin/board/ 에 실제 존재하는 커스텀 스킨만
- inquiry read_level 10, write_level 1 등 문의 게시판 권한 반영

파일 수정 전 변경 목록을 먼저 보여주세요. DB 반영 금지.
```

---

*프롬프트는 프로젝트 상황에 맞게 [대괄호] 부분만 수정해 사용하세요.*
