# 백업 가이드 (onoff-g5-base)

새 프로젝트로 **복사하기 전**·**운영 반영 전** 백업 기준입니다.

---

## 1. 언제 백업하나

| 시점 | 권장 |
|------|------|
| 베이스 폴더를 새 고객 프로젝트로 **복사하기 전** | 원본 베이스 1회 스냅샷 |
| 운영 서버 **파일·DB 변경 전** | 전체 백업 |
| 게시판 대량 작업·스킨 교체 전 | DB + `skin/` |
| 플러그인 dist ZIP **대량 업로드 전** | `plugin/onoff-builder-bridge/imports/`, `data/imports.json` |

---

## 2. DB 백업

1. phpMyAdmin 또는 `mysqldump`로 **전체 DB** export (`.sql`)
2. 파일명 예: `backup_YYYYMMDD_HHMM_site.sql`
3. 백업 파일은 **웹 공개 폴더 밖**에 보관 (`/data/` 업로드 금지)

```bash
# 예시 (계정·DB명은 환경에 맞게)
mysqldump -u DB_USER -p DB_NAME > backup_$(date +%Y%m%d).sql
```

---

## 3. 파일 백업

| 포함 | 경로 예 |
|------|---------|
| 필수 | `data/` (단, `cache/`, `session/`는 복원 후 재생성 가능) |
| 필수 | `skin/`, `mobile/skin/`, `section/`, `page/`, `components/` |
| 필수 | `_site.config.php`, `css/custom.css`, `js/custom.js` |
| 선택 | `img/`, `plugin/onoff-builder-bridge/imports/`, `plugin/onoff-builder-bridge/data/` |
| 제외 권장 | `/_BUILDER_INPUT/`, `/page/style-guide.php`, `data/dbconfig.local.php` |

```bash
# 예시: 프로젝트 루트에서
tar -czvf backup_files_$(date +%Y%m%d).tar.gz \
  data skin mobile section page components css js img plugin _site.config.php
```

---

## 4. 복사용 원본 베이스 보관

- **Git** 사용 시: `data/dbconfig.php`, `data/dbconfig.local.php`는 **커밋 제외** (`.gitignore` 확인)
- 폴더 통째 복사 시: 복사본의 `dbconfig.php`를 **새 서버 정보로 즉시 교체**
- 이 베이스의 `setup/site.sample.json`, `_site.config.php`는 **샘플 값** — 복사 후 반드시 수정

---

## 5. 복원 순서 (요약)

1. DB import (`.sql`)
2. 파일 압축 해제·업로드
3. `data/` 권한 (cache, session, file 쓰기)
4. `data/dbconfig.php` (또는 로컬만 `dbconfig.local.php`) 확인
5. 사이트 URL·관리자 로그인·게시판·문의 테스트

---

## 6. 관련 문서

| 문서 | 용도 |
|------|------|
| [SECURITY-CHECKLIST.md](SECURITY-CHECKLIST.md) | 배포 전 보안 |
| [_BASE_INFO/COPY-CHECKLIST.md](_BASE_INFO/COPY-CHECKLIST.md) | 새 프로젝트 복사 |
| [LAUNCH-CHECKLIST.md](LAUNCH-CHECKLIST.md) | 오픈 전 최종 |
