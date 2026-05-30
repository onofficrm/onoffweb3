# 메뉴 구성 Cursor 프롬프트

새 프로젝트에서 메뉴 **계획·검수**할 때 복사해 쓰는 프롬프트 모음입니다.

**공통 주의 (모든 프롬프트에 적용)**

- git commit / push / FTP 배포 **금지**
- `/bbs`, `/lib`, `/adm` 코어 **수정 금지**
- 메뉴 **DB 직접 생성·수정 금지** — 관리자 수동 설정 우선
- 작업 전 **수정·생성 예정 파일 목록** 제시 후 진행
- 작업 후 **요약** (생성/수정 파일, 확인 항목)

---

## 1. 메뉴 구성 계획 프롬프트

```
이 프로젝트는 onoff-g5-base 복사본입니다.

/setup/project.sample.json의 menus 항목을 분석하고,
현재 /page/*.php 와 boards(bo_table) 구조와 비교해주세요.

출력:
1. 실제 연결 가능한 메뉴 목록 (URL 포함)
2. JSON에 있지만 파일·게시판이 없는 항목
3. 있지만 menus에 없는 page/board
4. 관리자에서 수동으로 메뉴를 만드는 순서 (MENU-GUIDE.md 기준)

조건:
- 메뉴 DB 수정·SQL·자동 삽입 금지
- /bbs, /lib, /adm 수정 금지
- git/배포 금지
- 수정 전 파일 목록만 제시 (이번은 분석만)
```

---

## 2. 게시판 메뉴 연결 확인 프롬프트

```
/setup/project.sample.json의 boards와 menus를 비교해주세요.

확인:
1. menus URL의 bo_table과 boards[].bo_table 일치 여부
2. 오타·존재하지 않는 bo_table
3. 게시판 생성 전 연결된 메뉴 (BOARD-CREATE-GUIDE.md 순서 위반)
4. 각 게시판에 쓸 메뉴 URL 예시 (/bbs/board.php?bo_table=...)

조건:
- DB·관리자 데이터 변경 금지
- 코어 수정 금지
- 문서·JSON만 참고
```

---

## 3. 서브페이지 메뉴 연결 확인 프롬프트

```
menus 항목 중 /page/ 로 시작하는 URL을 모두 찾고,
실제 /page/*.php 파일 존재 여부와 비교해주세요.

출력:
1. 연결 가능한 페이지 목록
2. menus에 있으나 파일 없음 → 생성 필요 목록
3. 파일은 있으나 menus에 없음 → 메뉴 추가 후보
4. location.php, process.php 등 베이스 미포함 경로 안내

조건:
- page 파일은 이번에 만들지 말고 목록만 (만들 때는 별도 요청)
- DB 수정 금지
```

---

## 4. 모바일 메뉴 검수 프롬프트

```
/setup/project.sample.json menus 기준으로 모바일 GNB 검수 체크리스트를 만들어주세요.

확인:
1. 메뉴명 길이 (모바일 줄바꿈·잘림)
2. 2차 메뉴 개수 (과다 여부)
3. head.php get_menu_db(1) 모바일 메뉴 등록 필요 안내
4. 햄버거 메뉴(#siteMobileNav)에서 접근 가능한 구조인지
5. active 표시는 그누보드 메뉴 URL 기준임을 안내

조건:
- 실제 브라우저 대신 문서·구조 기준
- CSS/JS 대규모 수정 금지
```

---

## 5. 오픈 전 메뉴 링크 검수 프롬프트

```
LAUNCH-CHECKLIST.md와 MENU-GUIDE.md 기준으로
menus + 푸터(개인정보·문의) 링크 검수 목록을 작성해주세요.

확인:
1. 모든 menus URL — 404 가능성 (page 파일, bo_table)
2. 외부 링크 https:// 및 target _blank
3. /page/privacy.php 연결 (푸터·약관)
4. /page/contact.php vs 상담 CTA URL 일치
5. 메인 앵커 /#section-* 와 index.php 섹션 id 일치
6. PC·모바일 메뉴 각각 확인 항목

조건:
- DB 수정 금지
- git/배포 금지
- 검수 체크리스트만 출력 (코드 변경 없으면 파일 수정 없음)
```

---

## 참고 문서

- [MENU-GUIDE.md](MENU-GUIDE.md)
- [MENU-EXAMPLES.md](MENU-EXAMPLES.md)
- [BOARD-CREATE-GUIDE.md](BOARD-CREATE-GUIDE.md)
- [LAUNCH-CHECKLIST.md](LAUNCH-CHECKLIST.md)
