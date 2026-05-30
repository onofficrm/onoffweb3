# ZIP 가져오기 가이드

## 1. 준비

1. 빌더에서 `npm run build` 실행
2. `dist` 폴더에 `index.html`, `assets/` 확인
3. **dist 안의 파일들**을 ZIP으로 압축 (폴더 자체가 아닌 내용이 루트에 오도록 권장)

## 2. 관리자 업로드

1. `/plugin/onoff-builder-bridge/admin/import-form.php`
2. **프로젝트 ID** — `muraku-main` 형식 (영문 소문자, 숫자, `-`, `_` / 2~50자)
3. **프로젝트 이름** — 관리용 표시명
4. **ZIP** 선택 → 업로드

## 3. 확인

- 목록: `import-list.php`
- 공개 URL: `/plugin/onoff-builder-bridge/page.php?id=프로젝트ID`
- 미리보기: `preview.php?id=프로젝트ID` (비활성도 가능)

## 4. 삭제

목록에서 **삭제** (POST) — `imports/{id}/` + `data/imports/{id}.json` 제거

## 5. 덮어쓰기

같은 ID가 있으면 **업로드 차단** → 먼저 삭제 후 재업로드
