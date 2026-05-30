# AI 포인트 과금 확장 계획

현재 설치형 플러그인은 포인트가 없어도 작동해야 하므로 포인트 차감/잔액 관리는 적용하지 않는다. 나중에 사용자가 많아지면 icrm.co.kr 중앙관리 기능으로만 추가한다.

## 원칙

- 포인트 차감은 icrm.co.kr 서버에서만 처리한다.
- 고객 사이트 플러그인은 라이선스 키, 도메인, 글 내용만 전송한다.
- 고객 사이트에서 넘어온 포인트 값은 신뢰하지 않는다.
- AI 댓글 생성 성공 시에만 포인트를 차감한다.
- 실패, 포인트 부족, 템플릿 fallback은 차감하지 않는다.

## 초기 과금 정책

- AI 댓글 생성 성공 1건 = 1포인트
- 재생성 성공 1건 = 1포인트
- 실패 = 0포인트
- 포인트 부족 = 0포인트

## icrm 개발 범위

### 고객 사이트 테이블 확장

- `monthly_point_limit`
- `monthly_used_points`
- `point_balance`
- `point_reset_ym`
- `plan_name`
- `status`
- `expires_at`

### 포인트 거래 내역 테이블

- `id`
- `site_id`
- `license_key`
- `type`: `charge`, `use`, `refund`, `expire`, `adjust`
- `points`
- `balance_before`
- `balance_after`
- `reason`
- `request_id`
- `created_at`

### AI 요청 로그 확장

- `request_id`
- `points_charged`
- `point_balance`
- `monthly_used_points`
- `prompt_tokens`
- `output_tokens`
- `total_tokens`
- `cost_krw`
- `status`
- `error_message`

## API 흐름

1. POST JSON 요청만 허용한다.
2. `license_key`, `domain`을 검증한다.
3. 라이선스 상태가 `active`인지 확인한다.
4. 만료일을 확인한다.
5. 월 한도와 잔액을 확인한다.
6. 포인트가 부족하면 Gemini 호출 없이 실패 JSON을 반환한다.
7. Gemini 호출이 성공하면 댓글을 반환하고 포인트를 차감한다.
8. 포인트 거래 내역과 AI 요청 로그를 저장한다.
9. `request_id`로 중복 차감을 방지한다.

## 성공 응답 예시

```json
{
  "success": true,
  "comment": "생성된 댓글",
  "model": "gemini-2.0-flash-lite",
  "prompt_tokens": 100,
  "output_tokens": 30,
  "total_tokens": 130,
  "cost_krw": 0.05,
  "points_charged": 1,
  "point_balance": 999,
  "monthly_used_points": 1,
  "message": "success"
}
```

## 포인트 부족 응답 예시

```json
{
  "success": false,
  "status": "point_insufficient",
  "message": "포인트가 부족합니다.",
  "points_charged": 0,
  "point_balance": 0,
  "monthly_used_points": 1000
}
```

## 설치형 플러그인 반영 시점

포인트 과금이 실제로 활성화될 때 설치형 플러그인은 icrm 응답의 아래 값을 AI 사용기록에 저장하고 관리자 화면에 표시하면 된다.

- `points_charged`
- `point_balance`
- `monthly_used_points`

