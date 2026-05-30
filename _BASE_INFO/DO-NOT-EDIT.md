# 수정 금지·주의 파일

onoff-g5-base는 **그누보드 업데이트·재설치**를 고려해 코어와 템플릿을 분리합니다.  
아래 경로를 수정하면 업데이트 시 **덮어쓰기·충돌·보안 사고**가 날 수 있습니다.

---

## 1. `/bbs/` — 게시판·회원·인증 코어

| 이유 |
|------|
| 글쓰기, 목록, 댓글, 로그인, 권한 등 **그누보드 핵심 로직**이 들어 있습니다. |
| 베이스를 커스터마이징하지 않았으며, 그누보드 공식 패치·보안 업데이트 대상입니다. |
| 여기를 수정하면 **업데이트 시 전부 손실**되고, 버그 추적이 어렵습니다. |
| 게시판 디자인은 **`/skin/board/{커스텀스킨}/`** 만 수정하세요. |

---

## 2. `/lib/` — 공통 라이브러리

| 이유 |
|------|
| DB, 세션, 훅(`add_replace`, `run_event`), HTML 버퍼, XSS 필터 등 **전역 함수**가 있습니다. |
| `common.lib.php`, `hook.lib.php` 등은 그누보드 전역에 영향을 줍니다. |
| SEO 메타 주입도 **훅을 호출**하는 방식이며, lib 자체를 고치지 않습니다. |
| 기능 확장은 `/_site.config.php`, `/components/`, `head.php`에서 처리하세요. |

---

## 3. `/adm/` — 관리자 화면

| 이유 |
|------|
| 관리자 UI·DB 업그레이드·환경설정 폼이 포함됩니다. |
| 수정 시 **관리자 업데이트 불가**·보안 취약점 유지 위험이 있습니다. |
| 사이트 설정은 **환경설정 메뉴** + `_site.config.php`로 분리합니다. |

---

## 4. `/data/` — 데이터·설정·런타임

| 이유 |
|------|
| **`dbconfig.php`** — DB 호스트·계정·비밀번호. **복사·Git·FTP 설정 파일 공유 금지.** |
| **`dbconfig.local.php`** — 로컬 개발 전용. 운영 서버에 업로드하지 마세요. |
| **`cache/`, `session/`, `file/`** — 서버가 자동 생성·갱신. 베이스 복사 시 비우거나 서버에서 재생성. |
| 잘못 공유하면 **DB 탈취·개인정보 유출**로 이어집니다. |

**권장:** 복사 후 `data/dbconfig.local.php`만 로컬에 두고, 운영은 서버에서 `dbconfig.php`를 새로 작성.

---

## 5. 원본 `/skin/board/basic/` · `/skin/board/gallery/`

| 이유 |
|------|
| 그누보드 **배포 기본 스킨**입니다. 참고·비교용으로 유지합니다. |
| 커스텀 10종(`basic-clean` 등)과 **이름·경로가 다릅니다.** |
| 원본을 수정하면 그누보드 패치·다른 게시판(기본 스킨 사용 시)에 영향을 줄 수 있습니다. |
| 새 디자인은 **`basic-clean` 등 복사본 스킨** 또는 `skin/board/_inc/`만 확장하세요. |

---

## 6. DB 설정 파일 — `data/dbconfig.php`

| 이유 |
|------|
| 서버마다 DB 정보가 다릅니다. |
| 베이스 ZIP·Git에 **실제 비밀번호가 들어가면 안 됩니다.** |
| `setup/replace-checklist.md` — 복사·커밋 제외 명시. |

---

## 7. FTP·배포 정보 파일

| 이유 |
|------|
| `.ftpconfig`, FileZilla XML, `.env`, 배포 스크립트에 **호스트·계정·비밀번호**가 들어갈 수 있습니다. |
| 문서·베이스 폴더에 넣지 말고, **비밀 관리 도구**만 사용하세요. |
| 실수 업로드 시 **전체 서버 접근** 위험이 있습니다. |

---

## 8. 그누보드 코어 파일 (루트·진입점)

| 파일·경로 | 이유 |
|-----------|------|
| **`common.php`** | 모든 페이지 진입. 한 줄 변경도 전역 장애 가능. |
| **`head.sub.php`**, **`tail.sub.php`** | 그누보드 기본 HTML 골격. SEO는 `components/seo-meta.php` + 훅으로 처리 (head.sub 직접 수정 최소화). |
| **`config.php`** | 설치 경로·URL 상수. 설치 후 서버별로만 조정. |
| **`plugin/`** (일부) | 결제·SMS 등 — 필요 플러그인만 사용, 임의 패치 금지. |

---

## 9. 운영에 노출하면 안 되는 테스트·작업 파일

| 파일·폴더 | 이유 |
|-----------|------|
| **`/page/style-guide.php`** | 디자인 검수용. **런칭 전 삭제·차단** 권장. |
| **`/_BUILDER_INPUT/`** | 빌더·React **임시 보관**. 운영/FTP 업로드 제외. 변환 후 `/img`, `/section` 사용. |
| **`setup/site.sample.json`** | 샘플 값 — 실제 고객 정보로 커밋·공유 금지. |

납품 전: [LAUNCH-CHECKLIST.md](../LAUNCH-CHECKLIST.md) §9

---

## 10. 수정해도 되는 영역 (요약)

| 영역 | 예시 |
|------|------|
| 사이트 설정 | `_site.config.php` |
| 레이아웃 | `head.php`, `tail.php` (최소 수정) |
| 메인·서브 | `index.php`, `section/`, `page/` |
| 컴포넌트 | `components/` |
| 스타일·JS | `css/custom.css`, `css/g5b-board.css`, `js/custom.js` |
| 게시판 스킨 | `skin/board/{10종}/`, `mobile/skin/board/{10종}/` |
| 이미지 | `img/logo/`, `img/main/`, `img/common/` |

---

## 관련 문서

- [`FILE-STRUCTURE.md`](FILE-STRUCTURE.md)
- [`COPY-CHECKLIST.md`](COPY-CHECKLIST.md)
- [`../README-START.md`](../README-START.md) §12
- [`../START-PROJECT-PROMPTS.md`](../START-PROJECT-PROMPTS.md)
- [`../LAUNCH-CHECKLIST.md`](../LAUNCH-CHECKLIST.md)
- [`../CLIENT-MANUAL.md`](../CLIENT-MANUAL.md)
