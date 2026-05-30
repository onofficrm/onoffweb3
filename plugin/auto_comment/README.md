# GnuBoard Auto Comment

방문 트리거 방식으로 게시글별 예약 댓글을 생성하고 등록하는 그누보드용 모듈입니다.

## onoff-g5-base (홈페이지 빌더 리빌드) 내장

이 베이스에는 아래가 **이미 포함**되어 있습니다.

- `plugin/auto_comment/` — 모듈 본체
- `extend/auto_comment.extend.php` — 이벤트·관리자 메뉴 연동

**별도 설치 절차 없이** 그누보드를 한 번이라도 띄우면(`common.php` 로드) DB 테이블이 자동 생성됩니다.  
관리: **관리자 → 자동댓글 관리** (`/plugin/auto_comment/admin/index.php`)

비활성화: `_site.config.php` → `'auto_comment_builtin' => false`

레거시 수동 설치(`install.php`, packages zip)는 다른 그누보드 사이트 배포용입니다.

## 제일 쉬운 사용법 (외부 그누보드 배포)

### 처음 설치 파일 만들기

```bash
php plugin/auto_comment/make_full_package.php
```

생성된 `auto-comment-버전-full` 폴더 또는 zip 안의 `plugin`, `extend` 폴더를 다른 그누보드 사이트 루트에 업로드한 뒤, 최고관리자로 아래 주소에 접속하세요.

```text
/plugin/auto_comment/install.php
```

### 업데이트 파일 만들기

```bash
php plugin/auto_comment/make_update_package.php
```

생성된 `auto-comment-버전-update` 폴더 또는 zip 안의 `plugin`, `extend` 폴더를 기존 사이트 루트에 덮어쓴 뒤, 최고관리자로 아래 주소에 접속하세요.

```text
/plugin/auto_comment/update.php
```

업데이트는 기존 설정, API 키, 게시판 설정, 작성자명, 템플릿, 예약목록을 지우지 않습니다.

## 설치 상세

1. 전체 설치 패키지의 `plugin/auto_comment` 폴더를 그누보드 루트의 `plugin/auto_comment`에 업로드합니다.
2. 전체 설치 패키지의 `extend/auto_comment.extend.php` 파일을 그누보드 루트의 `extend/auto_comment.extend.php`에 업로드합니다.
3. 최고관리자로 로그인 후 `/plugin/auto_comment/install.php`에 접속합니다.
4. `/plugin/auto_comment/admin/index.php`에서 설정합니다.

## 업데이트 상세

기존 운영 사이트에서는 설정과 예약목록을 보존하기 위해 전체 설치를 다시 실행하지 말고 업데이트 절차를 사용하세요.

1. 업데이트 패키지에 포함된 파일만 그누보드 루트 기준으로 덮어씁니다.
2. 최고관리자로 로그인 후 `/plugin/auto_comment/update.php`에 접속하거나 관리자 화면의 `백업/업데이트 > 업데이트 실행`을 누릅니다.
3. 업데이트는 신규 DB 테이블/컬럼과 신규 기본 설정만 추가하며, 기존 `모듈 사용`, API 키, 게시판 설정, 작성자명, 템플릿, 예약목록은 덮어쓰지 않습니다.

## 고급 패키지 만들기

배포 패키지는 `plugin/auto_comment/manifest.php`의 파일 목록을 기준으로 만듭니다.

```bash
php plugin/auto_comment/build_package.php
```

위 명령은 전체 설치 패키지를 `plugin/auto_comment/packages/auto-comment-{버전}-full` 경로에 만듭니다. 서버에 `ZipArchive` 확장이 있으면 zip 파일도 함께 생성합니다.

부분 업데이트 패키지는 바뀐 파일만 지정해서 만들 수 있습니다.

```bash
php plugin/auto_comment/build_package.php update --changed=plugin/auto_comment/auto_comment.lib.php,plugin/auto_comment/admin/index.php
```

또는 기준 브랜치를 지정하면 git diff 결과 중 매니페스트에 포함된 파일만 묶습니다.

```bash
AUTO_COMMENT_BASE_REF=origin/main php plugin/auto_comment/build_package.php update
```

## 기본 운영 순서

1. `기본설정`에서 `추천 설정 자동 적용`을 누릅니다.
2. `게시판설정`에서 사용할 게시판만 ON으로 둡니다.
3. `작성자명`과 `댓글템플릿`을 사이트 성격에 맞게 수정합니다.
4. `수동예약`에서 원글 번호를 입력해 미리보기 생성 후 예약 테스트를 합니다.
5. 문제가 없으면 `기본설정`에서 모듈 사용을 ON으로 변경합니다.

## 동작 방식

- 기본은 **조회수 기준**으로 목표 댓글 수를 계산하고, 방문 트리거·신규글·전략스캔으로 예약합니다.
- 게시판설정에서 **간격 예약**을 켠 게시판은 게시판마다 지정한 **시간·분 간격**(예: 1시간, 6시간 30분)마다 댓글을 예약하며, 조회수·신규글·전략스캔은 사용하지 않습니다.
- 새 게시글 등록 후 댓글을 바로 쓰지 않고 예약목록에 저장합니다.
- 일반 방문자의 페이지 요청 중 낮은 확률로 worker가 실행됩니다.
- 예약 시간이 지난 댓글만 제한된 개수만큼 등록됩니다.
- cron 설정 없이 사용할 수 있습니다.

## 안전장치

- 기본 설치 후 모듈은 OFF 상태입니다.
- 하루 최대 등록 수 제한이 있습니다.
- 금칙어 설정이 가능합니다.
- 같은 원글에 동일 댓글 중복 예약/등록을 차단합니다.
- 예약 댓글은 등록 전 관리자 화면에서 수정/취소/삭제할 수 있습니다.

## 백업/복원

`백업/복원` 탭에서 작성자명, 템플릿, 게시판 설정, 기본 설정을 JSON으로 내보내거나 가져올 수 있습니다.

## AI 확장

`기본설정`에서 댓글 생성 방식을 `AI`로 선택하면 AI 댓글 생성기를 사용할 수 있습니다.

- 상품 배포용 기본 방식은 `icrm 중앙관리 API`입니다.
- 고객 사이트에는 `icrm API 주소`와 `icrm 라이선스 키`만 저장합니다.
- Gemini 모델과 API Key는 icrm.co.kr 중앙 서버에서 관리합니다.
- 중앙 API 호출이 실패하면 템플릿 생성기로 자동 fallback 됩니다.

## 자동 작성자 회원 및 프로필 생성

자동댓글이 실제 회원 활동처럼 보이도록, 댓글이 등록되는 시점에 작성자명과 연결된 전용 회원을 자동으로 생성합니다.

### 회원 ID 생성 규칙

- 작성자명은 `auto_comment_author_key()`로 정규화해 같은 닉네임을 같은 작성자 키로 인식합니다.
- `auto_comment_author_id_base()`는 한글 작성자명의 의미를 일부 영문 토큰으로 바꿔 ID 기본값을 만듭니다.
  - 예: `세부`, `막탄`, `가이드`, `리뷰`, `여행`, `밤문화` 등 사이트 성격과 관련된 단어를 영문 토큰으로 변환합니다.
- 최종 ID는 기본값 뒤에 base36 랜덤 suffix를 붙여 생성합니다.
  - 예: `cebu_guide_x7k2p9`, `mactan_trip_4fd8qa`
- ID 길이는 그누보드 회원 ID 제한에 맞춰 최대 20자로 제한합니다.
- 이미 존재하는 ID와 충돌하면 다른 랜덤 suffix로 다시 생성합니다.

### 회원 메타 정보

자동 생성 회원은 일반 회원과 구분할 수 있도록 다음 값을 저장합니다.

- `mb_1 = auto_comment`
- `mb_2 = 작성자 키`
- `mb_10 = auto_comment_bot`
- `mb_profile = 자동댓글 전용 회원`
- `mb_memo = 자동댓글 전용 회원`

이 값은 관리자 점검, 포인트 보정, 기존 자동댓글과 회원 ID 재연결에 사용됩니다.

### 닉네임 처리

- 기존 자동댓글 봇 회원 중 같은 닉네임 또는 같은 작성자 키가 있으면 그 회원을 재사용합니다.
- 같은 닉네임이 일반 회원과 충돌하면 숫자 suffix를 붙인 닉네임을 생성합니다.
- 작성자명이 비어 있거나 이미 같은 게시글에 사용된 작성자라면 다른 자동 작성자명을 선택합니다.

### 프로필 이미지 자동 생성

관리자가 이미지를 업로드하지 않아도 `auto_comment_generate_member_avatar()`가 GD 라이브러리로 회원 이미지를 생성합니다.

- 저장 위치: `data/member_image/{회원ID 앞 2글자}/{회원ID}.gif`
- 이미지 크기: 그누보드 `cf_member_img_width`, `cf_member_img_height` 설정을 우선 사용합니다.
- 색상과 형태는 작성자명과 회원 ID의 `crc32` 해시를 기준으로 안정적으로 결정합니다.
- 너무 추상적으로 보이지 않도록 다음 형태 중 하나를 그립니다.
  - 사람
  - 고양이
  - 강아지
  - 야자수
  - 컵
- 같은 작성자/회원 ID 조합은 같은 스타일의 이미지가 유지됩니다.

서버에 PHP GD 확장이 없으면 회원은 생성되지만 프로필 이미지는 기본 이미지로 표시될 수 있습니다.

### 댓글/글 작성과 포인트 연동

- 신규 자동댓글 등록 시 자동 생성 회원의 `mb_id`를 댓글의 `mb_id`와 새글 테이블에 저장합니다.
- 댓글 등록 후 게시판의 `bo_comment_point`가 양수이면 해당 봇 회원에게 댓글 포인트를 지급합니다.
- 이미 등록된 과거 자동댓글은 `auto_comment_maybe_sync_bot_points()`가 주기적으로 보정합니다.
  - 자동댓글 큐와 실제 댓글을 작성자명/내용으로 매칭합니다.
  - 비어 있거나 다른 `mb_id`를 자동 생성 회원 ID로 연결합니다.
  - 누락된 댓글 포인트를 중복 없이 지급합니다.
- 봇 회원으로 작성된 게시글 또는 댓글도 누락 포인트가 있으면 보정합니다.
- 포인트 랭킹은 `mb_10 = auto_comment_bot` 회원도 제외하지 않으면 함께 표시됩니다.

### 운영 주의사항

- 자동 생성 봇 회원은 일반 회원처럼 보이지만, 운영상 구분을 위해 `mb_10 = auto_comment_bot` 값을 유지해야 합니다.
- 포인트 중복 지급은 그누보드 `insert_point()`의 `po_rel_table`, `po_rel_id`, `po_rel_action` 중복 체크를 사용해 방지합니다.
- 작성 내역을 공개적으로 모아 보여주면 자동화 흔적이 드러날 수 있으므로, 랭킹에서는 닉네임/아바타/포인트 정도만 노출하는 것을 권장합니다.
- 다른 사이트에 배포할 때는 `plugin/auto_comment` 폴더와 `extend/auto_comment.extend.php`를 함께 업로드해야 자동 worker, 조회수 추적, 포인트 보정이 동작합니다.
