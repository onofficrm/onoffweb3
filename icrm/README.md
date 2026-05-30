# iCRM × 그누보드 final_url 연동 (onoff-g5-base 내장)

**모든 복사 사이트**에 동일하게 포함됩니다. 도메인은 사이트마다 `G5_URL`로 자동, iCRM에는 **사이트별 토큰**만 등록하면 됩니다.

iCRM은 **글 제목으로 URL을 만들지 않습니다.**  
홈페이지가 확정한 `wr_seo_title`·`final_url`만 사용하세요.

## 포함 파일 (별도 설치 불필요)

| 경로 | 역할 |
|------|------|
| `lib/icrm.lib.php` | slug 확정·URL 생성 |
| `extend/icrm.extend.php` | 글 저장 훅·부트스트랩 |
| `icrm/final-url.php` | iCRM 조회 API |

비활성: `_site.config.php` → `'icrm_builtin' => false`

## 사이트마다 할 일 (복사 후)

1. 그누보드 **기본환경 → 사이트 URL(`G5_URL`)** 이 실제 도메인과 일치하는지 확인  
   (예: `https://고객도메인.com` — thecebu 등 특정 도메인 하드코딩 없음)
2. 짧은주소 **글이름** + `.htaccess` rewrite
3. **iCRM에 이 사이트 등록**
   - API URL: `https://{고객도메인}/icrm/final-url.php`
   - Secret: 아래 토큰

### 토큰 받기

**A. 자동 (권장)**  
사이트를 한 번 열면 `data/icrm.config.php` 가 생성되고 랜덤 토큰이 들어갑니다.  
서버에서 `ICRM_SECRET_TOKEN` 값을 iCRM에 등록하세요.

**B. 수동**  
```bash
cp data/icrm.config.sample.php data/icrm.config.php
```
토큰·IP 입력. 또는 `_site.config.php`:

```php
'icrm_site_base_url' => '',  // 비우면 G5_URL
'icrm_secret_token'    => '사이트별-긴-랜덤-문자열',
'icrm_allowed_ips'     => '203.0.113.10',
```

`icrm_site_base_url`은 CDN·리버스프록시 등으로 `G5_URL`과 다를 때만 지정합니다.

## DB 직접 INSERT (write_update 미실행)

iCRM이 홈페이지 DB에 직접 INSERT해도:

- **글보기** 시 `wr_subject` 기준으로 `wr_seo_title` 자동 보정 (`generate_seo_title` + `exist_seo_title_recursive`)
- iCRM이 넣은 slug는 **최종값으로 쓰지 않음** — 그누보드가 확정한 slug만 사용
- **`GET /icrm/final-url.php`** 로 저장된 slug·`final_url` 조회 (사이트 도메인은 `G5_URL` 자동)

## iCRM 연동 흐름 (모든 사이트 공통)

1. iCRM이 해당 사이트 DB/API로 글 등록 → `bo_table`, `wr_id` 수신  
2. **그 사이트** API 호출:

```http
GET https://{사이트도메인}/icrm/final-url.php?bo_table=community&wr_id=123
X-ICRM-Token: {이 사이트의 secret}
```

3. 응답의 **`final_url`만** 저장·노출 (제목으로 URL 생성 금지)

POST·JSON body `{ "bo_table", "wr_id" }` 도 동일합니다.

## 성공 응답 예시

```json
{
  "ok": true,
  "site_base_url": "https://example-client.com",
  "final_url_api": "https://example-client.com/icrm/final-url.php",
  "bo_table": "community",
  "wr_id": 123,
  "wr_seo_title": "my-post",
  "final_url": "https://example-client.com/community/my-post/"
}
```

- slug 없을 때: `{site_base_url}/bbs/board.php?bo_table=…&wr_id=…`
- 글이름 URL 끝 **`/`** 필수

## wr_seo_title

- `generate_seo_title()` + `exist_seo_title_recursive()` (`write_update.php` 동일)
- DB 직접 INSERT 후 slug 비어 있으면 API 호출 시 `wr_subject` 기준 확정
- 같은 제목 2건 → `my-post`, `my-post-1`, …

## iCRM 템플릿 CSS

`css/icrm-template.css` — 글보기에서 카드·그리드·CTA 버튼 스타일 적용.

로드 경로 (중복 안전):

- `common_header` → `add_stylesheet`
- `head.php` (사이트 템플릿)
- `board_content_head` / `board_mobile_content_head` (게시판 상단, head 미포함 시)
- 커스텀 스킨 `g5b-board.css` `@import`

본문 HTMLPurifier: `class`·`style`·`data-icrm-template` 등 iCRM 마크업 유지 (`extend/icrm.extend.php`).

`_site.config.php` → `icrm_css_only_when_markup` = `true` 이면 iCRM 마크업 있는 글만 CSS 로드.

## 검증

- [ ] 사이트 A·B가 **서로 다른** `final_url`·토큰을 가짐  
- [ ] 동일 제목 2건 slug 중복 없음  
- [ ] DB INSERT 직후 글보기·final-url API 모두 slug 채움  
- [ ] `final_url` 브라우저 200 OK  
- [ ] iCRM 템플릿 카드·버튼 레이아웃 정상  
- [ ] 404 → 글·짧은주소·`.htaccess`  
- [ ] 403 → 해당 사이트 토큰/IP  
- [ ] 503 `not_configured` → `data/icrm.config.php` 생성 또는 secret 설정

## Apache rewrite (글이름)

```apache
RewriteRule ^([0-9a-zA-Z_]+)/([^/]+)/$ bbs/board.php?bo_table=$1&wr_seo_title=$2&rewrite=1 [QSA,L]
```
