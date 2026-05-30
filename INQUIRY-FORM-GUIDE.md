# 상담 문의 폼 · inquiry 게시판 · 이메일 알림 가이드

웹 상담 폼 → **inquiry 게시판 저장** → **관리자 이메일 알림** 흐름입니다.

| 파일 | 역할 |
|------|------|
| `components/consult-modal.php` | 상담 모달 HTML·폼 |
| `proc/inquiry-submit.php` | 검증·게시판 저장·JSON 응답 |
| `components/inquiry-notifier.php` | 저장 **성공 후** 이메일 알림 |
| `js/custom.js` | `initConsultForm()` AJAX 제출 → 완료 페이지 이동 |
| `page/inquiry-thanks.php` | 접수 완료·전환 추적 영역 |
| `_site.config.php` | 게시판 ID·알림 수신 설정 |

> `/bbs/write_update.php`는 수정하지 않습니다. 저장은 `proc/inquiry-submit.php`에서 그누보드 DB 규칙에 맞게 처리합니다.

---

## 1. 사전 준비

### 1.1 inquiry 게시판 생성

관리자 → **게시판관리** → 게시판 추가

| 항목 | 권장 |
|------|------|
| **게시판 ID** (`bo_table`) | `inquiry` (또는 `_site.config.php`의 `inquiry_bo_table`과 동일) |
| **스킨** | **`landing-inquiry`** (문의 관리 전용, 권장) |
| **게시판 제목** | 상담문의 (또는 견적문의·예약문의 등) |
| **비밀글** | 문의 보호용 **사용** 권장 |
| **글쓰기 권한** | 비회원 글쓰기 허용 (웹 폼은 `proc/inquiry-submit.php`) |
| **목록·읽기 권한** | **관리자(레벨 10) 이상만** 권장 — 개인정보 보호 |

### 1.2 이메일 발송 (그누보드)

관리자 → **환경설정** → 기본환경설정

- **메일발송 사용** — ✅ 사용
- **관리자 메일** — 발신 주소로 사용 (SPF·도메인 인증 권장)
- SMTP 사용 시 `config.php` / `data/dbconfig.php`의 `G5_SMTP` 설정

---

## 2. 이메일 알림 설정 (`/_site.config.php`)

```php
'inquiry_bo_table'        => 'inquiry',           // 게시판 ID
'inquiry_notify_enabled'  => true,               // false면 메일 미발송
'inquiry_notify_email'    => 'admin@example.com', // 운영 시 실제 주소로 변경
'inquiry_notify_name'     => '관리자',            // 메일 발신자 표시명(수신 측 From 이름 보조)
```

| 키 | 설명 |
|----|------|
| `inquiry_notify_enabled` | `false`, `0`, `'off'` 이면 발송 안 함 |
| `inquiry_notify_email` | 비우면 `email` → 그누보드 `cf_admin_email` 순으로 fallback |
| **여러 수신자** | 한 줄에 `a@x.com, b@x.com` 또는 `;` 구분 (각각 발송) |

### 2.1 운영 시 반드시 변경

- `inquiry_notify_email` → **실제 담당자 메일**
- 샘플 `admin@example.com` 그대로 두지 않기

---

## 3. 접수·알림 흐름

```
[사용자] 상담 모달 폼 제출
    ↓ POST /proc/inquiry-submit.php
[1] 토큰·honeypot·입력값 검증  ── 실패 → JSON 오류 (알림 없음)
[2] inquiry 게시판 존재 확인   ── 없음 → JSON 오류 (알림 없음)
[3] g5_write_{bo_table} INSERT  ── 실패 → JSON 오류 (알림 없음)
[4] 저장 성공
[5] inquiry-notifier.php → 이메일·텔레그램·웹훅  ── 실패해도 [6] 유지
[6] JSON { success: true, redirect_url } → /page/inquiry-thanks.php 이동
```

- **저장 실패 시 알림 없음**
- **알림 실패 시** 사용자에게는 여전히 접수 완료 안내

---

## 4. 메일이 오지 않을 때

| 확인 | 내용 |
|------|------|
| 설정 | `inquiry_notify_enabled` = true |
| 주소 | `inquiry_notify_email` 오타·빈 값 |
| 그누보드 | 환경설정 **메일발송 사용** |
| 서버 | PHP mail / SMTP 발송 가능 (관리자 **메일 테스트**) |
| 스팸함 | 수신 메일함·스팸함 |
| 발신 도메인 | SPF, DKIM, 호스팅 발송 제한 |
| 문의 저장 | 게시판에 글이 **실제로 등록**됐는지 먼저 확인 (저장 실패면 메일도 없음) |

`mailer()` 함수가 없거나 `cf_email_use`가 꺼져 있으면 조용히 스킵됩니다 (접수는 성공).

---

## 5. 개인정보 주의

- 알림 메일 본문에 **이름·연락처·이메일·문의내용·IP** 포함
- 수신 메일함·전달·모바일 알림 **보안** 관리
- 외부 메신저·개인 메일로 **무단 공유 금지**
- `consult-modal.php`·`page/privacy.php` 문구와 **수집 항목·보관 기간** 일치

---

## 6. 텔레그램·웹훅 알림 (`/_site.config.php`)

```php
'inquiry_notify_telegram_enabled'   => true,   // 운영 시 true
'inquiry_notify_telegram_bot_token' => '',    // @BotFather 토큰 (코드에 하드코딩 금지)
'inquiry_notify_telegram_chat_id'   => '',    // 채팅 ID
'inquiry_notify_webhook_enabled'    => false,
'inquiry_notify_webhook_url'        => '',
```

| 조건 | 동작 |
|------|------|
| `telegram_enabled` = false 또는 토큰·chat_id 비움 | 발송 안 함 |
| curl 미설치 | 텔레그램 발송 안 함 (서버에 php-curl 필요) |
| 알림 실패 | 접수·완료 페이지 이동은 **유지** (오류 미노출) |

## 7. 문의 완료 페이지

- URL: `inquiry_thanks_url` (기본 `/page/inquiry-thanks.php`)
- 전환 추적: `components/tracking-conversion.php` (GA4·Meta·GTM — ID 있을 때만 출력)
- 추적 ID: `gtm_id`, `ga4_id`, `meta_pixel_id` 등 `_site.config.php`에서 설정

## 8. 스팸 방지 (`proc/inquiry-submit.php`)

- honeypot, CSRF 토큰
- 동일 IP **60초** 1회 (session + `data/cache`)
- 문의내용 최소 10자, 이름·연락처·이메일·문의 길이 상한
- 연락처 형식 검증, User-Agent 없음 차단
- 금지어 배열(기본 비어 있음 — 운영자가 `onoff_inquiry_has_banned_word` 내 배열에 추가)

---

## 9. 테스트 방법

1. 관리자에서 `inquiry` 게시판 생성·`inquiry_bo_table` 일치 확인
2. `_site.config.php`에 **본인 테스트 메일** 입력
3. PC에서 상담 모달 → 문의 보내기
4. 게시판 목록에 새 글 확인
5. 수신 메일·스팸함 확인
6. 의도적 오류(이름 비움) → 알림·저장 없음 확인

---

## 10. landing-inquiry 스킨 (문의 관리 UI)

경로: `skin/board/landing-inquiry/`

| 파일 | 역할 |
|------|------|
| `list.skin.php` | 상태·연락처·접수 페이지 테이블(모바일 카드) |
| `view.skin.php` | 문의 상세·전화/메일/접수 페이지 버튼 |
| `write.skin.php` | 문의·관리 필드(wr_1~wr_10) 수정 |
| `style.css` | 관리 UI 전용 스타일 |
| `inquiry-helper.php` | 상태 뱃지·마스킹 헬퍼 |

### 10.1 추천 게시판 설정

| 항목 | 값 |
|------|-----|
| 테이블명 (`bo_table`) | `inquiry` |
| 제목 | 상담문의 |
| 스킨 | `landing-inquiry` |
| 비밀글 | 사용 |
| 목록 권한 | **관리자(10)** |
| 읽기 권한 | **관리자(10)** |
| 쓰기 권한 | 비회원 1 또는 관리자만 (폼 접수는 API) |

> 일반 회원·비회원에게 **목록·글보기**가 열려 있으면 연락처·이메일이 노출됩니다. 반드시 권한을 확인하세요.

### 10.2 여분필드 (wr_1 ~ wr_10)

| 필드 | 용도 | 웹 폼 저장 |
|------|------|:----------:|
| `wr_1` | 연락처 | ✅ |
| `wr_2` | 이메일 | ✅ |
| `wr_3` | 접수 페이지 URL/경로 | ✅ |
| `wr_4` | 개인정보 동의 (동의/미동의) | ✅ |
| `wr_5` | 접수 IP | ✅ |
| `wr_6` | 문의 상태 (기본 **신규**) | ✅ |
| `wr_7` | 담당자 | 관리자 수정 |
| `wr_8` | 관리자 메모 | 관리자 수정 |
| `wr_9` | 유입경로·캠페인 | 관리자 수정 |
| `wr_10` | 예비 | 선택 |

**문의 상태 값:** 신규 · 확인중 · 상담완료 · 계약완료 · 보류 · 스팸  
`wr_6`이 비어 있으면 스킨에서 **「신규」**로 표시합니다.

**상태 변경:** 목록/보기에서 AJAX 없음 → 글 **수정** 화면에서 `wr_6` 선택 후 저장.

### 10.3 개인정보 관리

- 목록에서 연락처는 **마스킹** 표시, 모바일에서 `tel:` 링크 가능
- 보기 화면 상단에 **외부 공유 주의** 문구
- 알림(메일·텔레그램)과 게시판 권한을 함께 점검

### 10.4 적용 절차

1. 관리자 → 게시판관리 → `inquiry` (또는 신규 생성)
2. **스킨 디렉토리** → `landing-inquiry` 선택·저장
3. **모바일 스킨** → 동일하게 `landing-inquiry` (`mobile/skin/board/landing-inquiry/` 필요)
4. 목록/읽기 권한을 관리자로 제한
5. 테스트 문의 1건 접수 후 목록·보기·수정 확인

---

## 11. 관련 문서

- [BOARD-SKIN-GUIDE.md](BOARD-SKIN-GUIDE.md) — `landing-inquiry` 스킨 상세
- [CLIENT-MANUAL.md](CLIENT-MANUAL.md) — 고객용 게시판 운영
- [LAUNCH-CHECKLIST.md](LAUNCH-CHECKLIST.md) — 납품 전 문의·SEO 점검
- [START-PROJECT-PROMPTS.md](START-PROJECT-PROMPTS.md) — Cursor 작업 프롬프트
