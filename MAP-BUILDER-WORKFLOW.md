# 지도 모듈 — 빌더·젠스파크·Cursor 작업 가이드

실제 **Google Maps API 로직**은 빌더에 맡기지 않고, **UI 디자인만** 빌더·젠스파크에 요청한 뒤 Cursor로 `map-locator.php`·`map.css`에 반영합니다.

---

## 1. 빌더·젠스파크 요청

**하지 말 것:** Google Maps API 연동·거리 계산·마커 로직 구현 요청

**할 것:** 내 주변 찾기 **화면 디자인** (검색·필터·목록·지도 placeholder·모바일)

### 프롬프트 예시

```
지역 기반 장소 찾기 페이지 디자인을 만들어줘.
Google Maps를 사용하는 내 주변 찾기 서비스 화면이야.
상단에는 검색창, 카테고리 필터, 현재 위치 찾기 버튼이 있고,
본문은 왼쪽 장소 목록, 오른쪽 지도 영역으로 구성해줘.
모바일에서는 검색/필터가 위에 있고, 지도와 목록이 세로로 배치되게 해줘.
실제 지도 기능은 개발자가 연결할 예정이므로, 지도 영역은 placeholder로 디자인해줘.
```

---

## 2. Cursor — 빌더 결과 적용

```
/_BUILDER_INPUT에 있는 지도 페이지 디자인을 분석해서,
기존 Google Maps 모듈 구조를 유지한 채 /page/map-locator.php와 /css/map.css에 디자인만 반영해주세요.
지도 기능 로직인 /js/google-map.js는 필요한 경우에만 최소 수정하고,
Google Maps API 키 설정 구조와 components/maps는 유지해주세요.
/bbs, /lib, /adm 코어 파일은 수정하지 마세요.
```

---

## 3. 업종별 문구·필터 조정

### 병원 찾기

```
map-location 게시판 스킨과 map-locator 페이지를 병원 찾기에 맞게 문구와 필터명만 조정해주세요.
wr_1은 진료과목, wr_6은 진료시간으로 표시해주세요. 기능 로직은 유지하고 디자인/문구 중심으로 수정해주세요.
```

### 학원 찾기

- 카테고리: 영어, 수학, 코딩, 미술  
- wr_6 → 수업시간  
- 상담문의 버튼은 `_site.config.php` contact 연동

### 부동산·매물

- 카테고리: 매매, 임대, 콘도, 상가  
- 정확 주소 대신 **근처 위치**만 표시 가능 안내

---

## 4. 장소 데이터 연동 요청

### JSON

```
/data/map-locations.sample.json을 실제 장소 데이터 기준으로 업데이트하고,
map-locator 페이지에서 이 JSON을 불러오도록 유지해주세요.
필드: name, category, address, lat, lng, phone, hours, link, description
```

### 게시판 (확장 계획)

```
location 게시판 wr_1~wr_10을 장소 데이터로 사용해
map-locator가 게시판 글을 지도 마커로 출력할 수 있도록 확장 계획을 세워주세요.
이번에는 코어 수정 없이 components/ 쿼리 구조만 제안해주세요.
```

---

## 5. 검수 체크리스트

- [ ] API 키 없음 → placeholder, 콘솔 오류 없음
- [ ] API 키 있음 → 지도·마커 표시
- [ ] 현재 위치 허용 → 거리·정렬
- [ ] 위치 거부 → 기본 좌표
- [ ] 검색·카테고리·반경 필터
- [ ] 마커·목록 클릭 연동
- [ ] 모바일 레이아웃
- [ ] JSON 빈 배열·fetch 실패 시 안내

---

[MAP-GUIDE.md](MAP-GUIDE.md)
