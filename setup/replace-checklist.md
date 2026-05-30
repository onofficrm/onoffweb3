# 새 프로젝트 교체 체크리스트

그누보드 제작 베이스 폴더를 **통째 복사**한 뒤, 아래 항목을 순서대로 확인하세요.  
기준 샘플 값: [`site.sample.json`](site.sample.json)

> **주의:** `data/dbconfig.php`는 서버마다 다릅니다. 복사·공유·커밋하지 마세요.  
> 로컬은 `data/dbconfig.local.php`를 사용하세요. ([`data/dbconfig.local.php.example`](../data/dbconfig.local.php.example))

---

## 1. 복사 직후 (5분)

- [ ] 폴더 전체 복사 완료 (Git/FTP 없이 로컬·서버에 붙여넣기)
- [ ] `setup/site.sample.json`을 복사해 **`setup/site.json`** 또는 메모장에 **실제 값** 기록
- [ ] macOS `._*` 임시 파일 삭제 (있을 경우): `find . -name '._*' -delete`
- [ ] DB 연결: `data/dbconfig.local.php` 생성 후 import

---

## 2. 사이트 기본 정보 → `/_site.config.php`

| 체크 | 항목 | JSON 키 | 반영 위치 |
|:---:|------|---------|-----------|
| [ ] | 사이트명 | `site_name` | `_site.config.php`, 관리자 환경설정 제목 |
| [ ] | 사이트 한줄 설명 | `site_desc` | `_site.config.php` (SEO·메타 확장 시 사용) |
| [ ] | 회사명 | `company_name` | `_site.config.php` → 푸터 |
| [ ] | 대표자명 | `ceo_name` | `_site.config.php` → 푸터 |
| [ ] | 사업자번호 | `business_no` | `_site.config.php` → 푸터 |
| [ ] | 전화번호 | `phone` | `_site.config.php` → 푸터·하단 버튼·`tel:` 링크 |
| [ ] | 카카오톡 링크 | `kakao_url` | `_site.config.php` → 하단 카카오 버튼 |
| [ ] | 이메일 | `email` | `_site.config.php` → 푸터 |
| [ ] | 주소 | `address` | `_site.config.php` → 푸터·지도 placeholder |
| [ ] | 상담 버튼 문구 | `consultation_text` | `_site.config.php` → 헤더 CTA·모달 |
| [ ] | 푸터 소개 문구 | `footer_desc` | `_site.config.php` → 푸터 소개 |
| [ ] | 대표 컬러 | `primary_color` | `_site.config.php` → `head.php`가 `:root`에 반영 |
| [ ] | 보조 컬러 | `secondary_color` | `_site.config.php` (동일) |
| [ ] | 팩스 (선택) | `fax` | `_site.config.php` |
| [ ] | 통신판매업신고 (선택) | `sales_no` | `_site.config.php` |
| [ ] | 개인정보책임자 (선택) | `privacy_manager` | `_site.config.php` |

**JSON만 있고 `_site.config.php`에 없는 항목 (수동·추후 연동):**

| 체크 | 항목 | JSON 키 | 참고 |
|:---:|------|---------|------|
| [ ] | 업종 구분 | `business_type` | `company` / `hospital` / `academy` 등 — `/presets/` 문구 참고(추후) |
| [ ] | 대표 키워드 | `main_keyword` | SEO·본문 교체 시 |
| [ ] | 서브 키워드 | `sub_keywords` | 배열, 메타·FAQ 등 |
| [ ] | SEO 제목 (선택) | `seo_title` | 비우면 `site_name` 사용 권장 |
| [ ] | SEO 설명 (선택) | `seo_description` | 비우면 `site_desc` 사용 권장 |

---

## 3. 파일·디자인 교체

| 체크 | 항목 | 작업 |
|:---:|------|------|
| [ ] | **로고 교체** | `logo_path` 경로에 SVG/PNG 업로드 (예: `/img/logo/logo.svg`) |
| [ ] | **OG 이미지 교체** | `og_image` 경로에 JPG/PNG (1200×630 권장), 예: `/img/common/og-image.jpg` |
| [ ] | **대표 컬러** | `primary_color` / `secondary_color` + 필요 시 `/css/custom.css` `:root` |
| [ ] | **파비콘** (선택) | `/img/favicon` 또는 관리자 설정 |

---

## 4. SEO

| 체크 | 항목 | 작업 |
|:---:|------|------|
| [ ] | **SEO title** | 관리자 → 환경설정 → 사이트 제목 + `site.sample.json`의 `seo_title` |
| [ ] | **SEO description** | 환경설정 추가 메타 또는 `seo_description` / `site_desc` |
| [ ] | **추가 메타** | 관리자 환경설정 → `cf_add_meta` (네이버·구글 인증 등) |
| [ ] | **OG 이미지** | 파일 교체 + (추후) `components/seo-meta.php` 연동 시 자동 출력 |

---

## 5. 그누보드 관리자

| 체크 | 항목 | 작업 |
|:---:|------|------|
| [ ] | **관리자 계정** | 비밀번호 변경, 2차 인증(가능 시) |
| [ ] | **환경설정** | 사이트 제목 = `site_name` |
| [ ] | **메뉴 생성** | 환경설정 → 메뉴설정 (PC·모바일) |
| [ ] | **게시판 생성** | notice, news, blog, portfolio, video 등 용도별 |
| [ ] | **게시판 스킨** | PC·모바일 동일 디렉토리명 ([README-BOARD-SKINS.md](../README-BOARD-SKINS.md)) |
| [ ] | **목록에서 내용 사용** | 카드·썸네일 스킨은 체크 권장 |
| [ ] | **내용관리** | 회사소개·개인정보·이용약관 (`content` ID: company, privacy, provision) |

**스킨 추천 예시**

| 용도 | 스킨 |
|------|------|
| 공지 | `basic-notice` |
| 소식 | `basic-modern` |
| 블로그/칼럼 | `post-thumb` / `post-media` |
| 포트폴리오 | `gallery-grid` |
| 영상 | `youtube-gallery` |

---

## 6. 콘텐츠·문구

| 체크 | 항목 | 작업 |
|:---:|------|------|
| [ ] | **메인 섹션** | `/section/*.php` — hero, service, faq, contact 등 |
| [ ] | **서브페이지** | `/page/about.php`, `service.php`, `portfolio.php`, `contact.php` |
| [ ] | **샘플 글** | [SAMPLE-CONTENT.md](../SAMPLE-CONTENT.md) 참고 |
| [ ] | **목적별 프리셋** | `/presets/*.md` — landing-conversion, content-seo 등 (업종별 아님) |

---

## 7. 법적·문의

| 체크 | 항목 | 작업 |
|:---:|------|------|
| [ ] | **개인정보처리방침** | `/page/privacy.php` 본문·책임자 정보 — 법무 검토 후 시행일 수정 |
| [ ] | **이용약관** | 내용관리 `provision` |
| [ ] | **문의폼 테스트** | 하단 전화·카카오·상담 모달 열기/닫기 |
| [ ] | **Q&A 게시판** | `head.php` 서브페이지 상담 링크 → `qalist.php` 동작 |
| [ ] | **개인정보 동의 문구** | `components/consult-modal.php` 안내 문구 실제 정책에 맞게 수정 |

---

## 8. 최종 확인

| 체크 | 항목 |
|:---:|------|
| [ ] | **PC** — 메인·서브·게시판 목록/글보기/글쓰기 |
| [ ] | **모바일** — GNB·게시판·하단 버튼 (`mobile/` 경로 별도 확인) |
| [ ] | **로그인·회원가입·관리자** 접속 |
| [ ] | **브라우저 콘솔** JS 오류 없음 |
| [ ] | `data/dbconfig.php` **미공유** 확인 |

---

## 9. 수정하면 안 되는 것

- `/bbs/`, `/lib/`, `/adm/`, `common.php`
- `/skin/board/basic/`, `/skin/board/gallery/` (그누보드 원본)
- 서버별 `data/dbconfig.php`

자세히: [README-START.md](../README-START.md) · [_BASE_INFO/DO-NOT-EDIT.md](../_BASE_INFO/DO-NOT-EDIT.md)

---

## 10. Cursor 초기 세팅 프롬프트 예시

아래 문구를 복사해 Cursor에 붙여 넣으세요. **코어 미수정** 조건을 항상 포함합니다.

### A. 기본 반영 (가장 많이 사용)

```
/setup/site.sample.json(또는 내가 수정한 setup/site.json) 값을 기준으로
/_site.config.php, 푸터·헤더·하단 버튼 링크에 반영해주세요.
primary_color, secondary_color는 head.php의 :root 인라인과 맞춰주세요.

조건:
- 그누보드 코어(/bbs, /lib, /adm) 수정 금지
- /skin/board/basic 원본 수정 금지
- git commit, push, FTP 하지 않음
- 수정 전 변경할 파일 목록을 먼저 보여주세요.
```

### B. JSON → PHP만 수동 매핑 (최소 변경)

```
setup/site.sample.json과 _site.config.php 키를 비교해서,
_site.config.php에 없는 JSON 키(business_type, main_keyword, sub_keywords,
seo_title, seo_description)는 주석으로 _site.config.php 하단에
"향후 사용" 섹션만 추가해주세요. 값은 site.sample.json과 동일하게.

실제 PHP 배열 키는 기존 g5site_cfg()와 호환되게 유지해주세요.
수정 전 파일 목록 먼저 제시.
```

### C. SEO·메타 (seo-meta 추가 후)

```
/setup/site.sample.json의 site_name, site_desc, seo_title, seo_description,
og_image, main_keyword를 기준으로 SEO 기본 구조를 잡아주세요.
components/seo-meta.php를 만들고 head.php 또는 page/_init.php에서만 include.

head.sub.php는 가급적 수정하지 마세요.
코어 수정 금지. 수정 전/후 파일 목록 제시.
```

### D. 메인·서브 문구 일괄 (업종 지정)

```
business_type이 "hospital"인 것으로 가정하고,
section/hero.php, section/service.php, section/contact.php와
page/about.php의 샘플 문구만 병원 톤으로 바꿔주세요.
숫자·고객명은 가짜 샘플로 표시해주세요.

코어·게시판 스킨 PHP·CSS는 수정하지 마세요.
```

### E. 게시판·메뉴만 가이드 (코드 수정 없음)

```
README-BOARD-SKINS.md와 site.sample.json business_type을 참고해
이 프로젝트에 만들 게시판 5개(이름, bo_table, 추천 스킨, 모바일 스킨) 표만
마크다운으로 제안해주세요. 코드는 수정하지 마세요.
```

### F. 복사 후 검수만

```
/setup/replace-checklist.md 기준으로
현재 프로젝트에서 아직 안 된 항목만 짧게 점검해주세요.
파일 생성/수정은 하지 말고 체크리스트 형태로 결과만 알려주세요.
```

---

## 11. `site.sample.json` ↔ `_site.config.php` 키 대응표

| site.sample.json | _site.config.php | 비고 |
|------------------|------------------|------|
| site_name | site_name | ✅ |
| site_desc | site_desc | ✅ |
| company_name | company_name | ✅ |
| ceo_name | ceo_name | ✅ |
| business_no | business_no | ✅ |
| phone | phone | ✅ |
| kakao_url | kakao_url | ✅ |
| email | email | ✅ |
| address | address | ✅ |
| primary_color | primary_color | ✅ |
| secondary_color | secondary_color | ✅ |
| logo_path | logo_path | ✅ |
| og_image | og_image | ✅ |
| consultation_text | consultation_text | ✅ |
| footer_desc | footer_desc | ✅ |
| fax | fax | ✅ |
| sales_no | sales_no | ✅ |
| privacy_manager | privacy_manager | ✅ |
| kakao_map_key | kakao_map_key | ✅ |
| kakao_map_lat | kakao_map_lat | ✅ |
| kakao_map_lng | kakao_map_lng | ✅ |
| business_type | — | JSON·체크리스트만 |
| main_keyword | — | SEO 작업 시 |
| sub_keywords | — | SEO 작업 시 |
| seo_title | — | 관리자·추후 seo-meta |
| seo_description | — | 동일 |

---

## 관련 문서

- [README-START.md](../README-START.md) — 시작 가이드
- [SAMPLE-CONTENT.md](../SAMPLE-CONTENT.md) — 샘플 글·문구
- [PROMPTS.md](../PROMPTS.md) — 빌더·디자인 적용 프롬프트
