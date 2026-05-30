# 보안 점검 체크리스트 (onoff-g5-base)

새 프로젝트 **복사·배포 전** 보안 확인용입니다.  
상세 복사 절차: [`_BASE_INFO/COPY-CHECKLIST.md`](_BASE_INFO/COPY-CHECKLIST.md) · 런칭: [`LAUNCH-CHECKLIST.md`](LAUNCH-CHECKLIST.md)

> git commit · FTP · 그누보드 코어(`/bbs`, `/lib`, `/adm`) 수정은 이 문서 작업 범위가 아닙니다.

---

## 1. DB·설정 파일

- [ ] **`data/dbconfig.php`** — 실제 호스트·계정·비밀번호로 교체, **다른 프로젝트 설정 복사 금지**
- [ ] **`data/dbconfig.local.php`** — 로컬 전용, **운영 서버·고객 전달·Git 업로드 금지**
- [ ] **`G5_TOKEN_ENCRYPTION_KEY`** — 복사 후 **새 랜덤 키** (기존 운영과 공유 금지)
- [ ] DB 덤프·백업 파일을 웹에서 다운로드 가능한 경로에 두지 않음

---

## 2. 사이트 설정·알림

- [ ] **`_site.config.php`** — `phone`, `email`, `address`, `inquiry_notify_email` 등 **샘플 값 제거**
- [ ] 텔레그램 `bot_token`, `chat_id` — 코드·문서에 **하드코딩 금지**, `_site.config.php`만
- [ ] **`google_maps_api_key`** — 발급 키 입력·키 제한(HTTP 리퍼러) 설정 ([MAP-GUIDE.md](MAP-GUIDE.md))

---

## 3. 업로드·플러그인 (onoff-builder-bridge)

- [ ] **최고관리자만** ZIP 업로드·삭제
- [ ] dist ZIP만 — 원본 Vite·`node_modules`·`.env`·PHP 포함 ZIP 금지
- [ ] 신뢰할 수 있는 빌드 결과물만 업로드 ([plugin/onoff-builder-bridge/README.md](plugin/onoff-builder-bridge/README.md))

---

## 4. 운영 서버에 올리지 않을 것

- [ ] **`/_BUILDER_INPUT/`** — 빌더 임시 보관
- [ ] **`/page/style-guide.php`** — 개발용 (삭제 또는 접근 차단)
- [ ] **`data/dbconfig.local.php`**, `.env`, FTP 설정 파일
- [ ] macOS `._*`, 테스트 ZIP·대용량 샘플 이미지(불필요 시)

---

## 5. 문서·납품물

- [ ] 고객 PDF·매뉴얼에 **FTP·DB 비밀번호·API 키** 없음 ([CLIENT-MANUAL.md](CLIENT-MANUAL.md))
- [ ] `docs/client/site-operation-guide-*.md`에 실제 관리자 비밀번호 없음

---

## 6. 그누보드 코어 (확인만)

- [ ] `/bbs/`, `/lib/`, `/adm/`, `common.php`, `head.php`, `tail.php`, `index.php` **미수정**
- [ ] `/skin/board/basic/` **미수정**

---

## 7. 빠른 검색 (Cursor·grep)

```
010-0000-0000
admin@example.com
샘플 사이트
wuk2002
qwer4321
AIza
bot_token
sk-
```

샘플 문구·테스트 연락처가 **남아 있으면** `_site.config.php`·문서·납품 PDF를 다시 확인하세요.

---

## 관련 문서

| 문서 | 용도 |
|------|------|
| [BACKUP-GUIDE.md](BACKUP-GUIDE.md) | 백업·복원 |
| [CLEANUP-PROMPTS.md](CLEANUP-PROMPTS.md) | AI 검수 프롬프트 |
| [plugin/onoff-builder-bridge/docs/SECURITY.md](plugin/onoff-builder-bridge/docs/SECURITY.md) | ZIP 업로드 보안 |
