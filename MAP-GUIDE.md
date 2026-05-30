# Google Maps 내 주변 찾기 모듈 가이드

지역·업체·매장·학원·병원 등 **장소 찾기** 기능을 프로젝트에 붙이기 위한 재사용 모듈입니다.  
완성형 앱이 아니라 **구조·샘플·스킨**만 제공합니다.

| 관련 문서 | 용도 |
|-----------|------|
| [MAP-BUILDER-WORKFLOW.md](MAP-BUILDER-WORKFLOW.md) | 빌더·Cursor 요청 프롬프트 |
| [MENU-GUIDE.md](MENU-GUIDE.md) | `/page/map-locator.php` 메뉴 연결 |

---

## 1. 모듈 목적

- Google Maps API로 **내 주변 찾기** UI·지도·마커·목록 제공
- 장소 데이터: **JSON 샘플** 또는 **게시판(map-location 스킨)** 연동 준비
- API 키 없음·위치 거부·데이터 없음 시에도 **페이지 깨짐 방지**

---

## 2. 파일 구조

```
components/maps/
  map-config.php      — 설정·fallback
  google-map.php      — 지도 컨테이너·placeholder
  store-locator.php   — 검색·필터·목록 UI
  marker-info.php     — 마커 정보창 HTML 헬퍼

js/google-map.js
css/map.css
page/map-locator.php
data/map-locations.sample.json
skin/board/map-location/   — list, write, view, style.css
```

---

## 3. Google Maps API 키 설정

`_site.config.php`:

```php
'google_maps_api_key' => '',  // 운영 시 발급 키 입력
'map_default_lat'     => '10.3157',
'map_default_lng'     => '123.8854',
'map_default_zoom'    => 13,
```

- 키가 **비어 있으면** placeholder만 표시 (script 미로드)
- [Google Cloud Console](https://console.cloud.google.com/)에서 Maps JavaScript API 활성화·키 발급
- **HTTP 리퍼러(도메인) 제한** 권장
- 사용량·**과금** 모니터링 필수
- 키·결제 정보를 Git·문서에 넣지 마세요

---

## 4. 기본 위치

| 설정 | 설명 |
|------|------|
| `map_default_lat` / `map_default_lng` | 위치 권한 거부·기본 중심 (샘플: 세부시티 근처) |
| `map_default_zoom` | 초기 줌 (1–21) |
| `map_default_radius_km` | 기본 반경 필터 |
| `map_use_current_location` | 현재 위치 버튼 표시 여부 |

---

## 5. JSON 데이터 방식

파일: `/data/map-locations.sample.json`

| 필드 | 설명 |
|------|------|
| id | 고유 ID |
| name | 장소명 |
| category | 카테고리 |
| address | 주소 |
| lat, lng | 좌표 (필수) |
| phone, hours | 연락·시간 |
| link | 상세 URL |
| description, tags | 설명·검색용 |

샘플 페이지는 `data-map-data-url`로 이 JSON을 `fetch` 합니다.  
운영 시 파일명을 복사·수정하거나 PHP에서 경로만 바꿉니다.

---

## 6. 게시판 연동 (map-location 스킨)

| 항목 | 권장 |
|------|------|
| bo_table | `location` |
| 스킨 | `map-location` |
| 여분필드 | 게시판 설정에서 wr_1~wr_10 사용 |

| 필드 | 용도 |
|------|------|
| wr_1 | 카테고리 |
| wr_2 | 주소 |
| wr_3 | 위도 |
| wr_4 | 경도 |
| wr_5 | 전화 |
| wr_6 | 영업시간 |
| wr_7 | 홈페이지/상세링크 |
| wr_8 | 태그 |
| wr_9 | 지역 |
| wr_10 | 예비 |

글보기(view)에서 좌표가 있으면 단일 마커 지도 표시.  
**목록 전체 → 지도 마커** 일괄 연동은 별도 PHP/API 확장(코어 수정 없이 컴포넌트 추가)을 권장합니다.

---

## 7. 내 주변 찾기 기능

- **현재 위치** — `navigator.geolocation` (거부 시 기본 좌표)
- **거리** — Haversine, km
- **정렬** — 가까운 순
- **필터** — 카테고리·키워드·반경

---

## 8. 적용 순서

1. `_site.config.php` API 키·기본 좌표
2. JSON 또는 게시판으로 장소 데이터 준비
3. `/page/map-locator.php` 브라우저 확인
4. 관리자 **메뉴**에 `/page/map-locator.php` 연결 ([MENU-GUIDE.md](MENU-GUIDE.md))
5. PC·모바일·위치 거부·키 없음 시나리오 테스트

---

## 9. 주의사항

- API 키 노출·도메인 제한·HTTPS 권장
- **실시간 위치 추적·저장**은 본 모듈 범위 밖
- 좌표·주소 정확도는 운영 데이터로 검증
- 개인 위치정보 DB 저장 금지 (정책 준수)

---

## 10. 빌더 / Cursor

디자인은 빌더, 기능은 본 모듈 — [MAP-BUILDER-WORKFLOW.md](MAP-BUILDER-WORKFLOW.md)

---

*onoff-g5-base · Google Maps 모듈*
