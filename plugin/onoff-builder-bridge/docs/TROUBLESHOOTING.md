# 문제 해결

## 업로드가 안 될 때

- `settings.php`에서 `upload_max_filesize`, `post_max_size` 확인
- ZIP만 허용
- 최고관리자 로그인 여부

## ZipArchive 없음

- PHP `zip` 확장 설치 필요
- `settings.php`에서 상태 확인

## index.html을 찾을 수 없음

- dist **내용**이 ZIP 루트에 있는지 확인
- `dist` 폴더만 통째로 넣었다면: 단일 하위 폴더면 자동 끌어올림 시도

## 원본 Vite 프로젝트 안내

→ `npm run build` 후 **dist만** ZIP

## 화면이 흰색

- 브라우저 개발자 도구 → Console
- `assets/*.js` 404 여부
- `page.php` 출력 소스에서 script `src`가 `/plugin/.../imports/.../assets/` 인지

## CSS만 되고 JS 안 됨

- `type="module"` 스크립트 경로 보정 확인
- MIME type (서버가 `.js` 를 text/html로 주지 않는지)

## assets 경로 깨짐

- index.html이 `/assets/` 절대경로를 쓰는지 (Vite 기본) — 플러그인이 import URL 기준으로 보정
- CSS `url()` 상대경로는 대부분 유지됨 — 깨지면 빌드 `base` 옵션 검토

## `<base href="/">` 충돌

- dist에 base 태그가 있으면 제거하거나 빌드 설정 조정

## 모바일 깨짐

- 빌더 반응형 CSS가 dist에 포함됐는지 확인

## 그누보드 head/tail과 섞기

- 기본 **standalone** 권장
- 실험: 메타 `mode` = `gnuboard-layout`

## 미리보기만 되고 공개 안 됨

- 목록에서 **사용(ON)** 체크 후 저장 — 업로드 시 enabled 확인
