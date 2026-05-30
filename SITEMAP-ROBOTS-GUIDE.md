# Sitemap · robots.txt 가이드

새 프로젝트 오픈 시 **Google Search Console**, **네이버 서치어드바이저** 등록을 위한 기본 안내입니다.

| 샘플 파일 | 운영 시 |
|-----------|---------|
| [sitemap.sample.xml](sitemap.sample.xml) | 복사 → `sitemap.xml` (웹 루트) |
| [robots.sample.txt](robots.sample.txt) | 복사 → `robots.txt` (웹 루트) |

> **자동 sitemap 생성 기능은 없습니다.** 게시글 증가 시 수동·외부 도구·추후 스크립트로 갱신하세요.

---

## 1. sitemap.xml 역할

- 검색엔진에 **색인할 URL 목록**을 알려 줍니다.
- 신규·변경 페이지를 빠르게 발견하는 데 도움이 됩니다.
- **노출·순위를 보장하지는 않습니다** (품질·권한·중복 여부가 더 중요).

### 포함 권장

| URL 유형 | 예 |
|----------|-----|
| 메인 | `/` |
| 서브페이지 | `/page/about.php`, `service.php`, `contact.php` |
| 게시판 목록 | `/bbs/board.php?bo_table=notice` |
| 중요 개별 글 | `wr_id` 있는 글 URL (선택·수동) |
| 지역 SEO | 실제 공개한 `page/local-*.php`만 |

### 포함하지 말 것

| URL | 이유 |
|-----|------|
| `/page/inquiry-thanks.php` | 전환 완료, noindex 권장 |
| `/page/404.php` | 오류 안내 |
| `/page/local-template.php` | 템플릿 샘플 |
| `/page/style-guide.php` | 개발용 |
| 관리자·로그인·비밀글 직접 URL | robots + noindex |

---

## 2. robots.txt 역할

- 크롤러에게 **허용·차단 경로**와 **sitemap 위치**를 알립니다.
- 차단해도 URL이 외부 링크로 알려지면 색인될 수 있음 → 중요 페이지는 **noindex 메타**도 함께 검토.

### 베이스 기본 차단 권장

| 경로 | 이유 |
|------|------|
| `/adm/` | 관리자 |
| `/data/` | DB·캐시·설정 |
| `/_BUILDER_INPUT/` | 빌더 임시 파일 |
| `/page/style-guide.php` | 스타일 가이드 |

---

## 3. sample → 실제 파일로 바꾸는 방법

### 3.1 사전 확인

```bash
# 웹 루트( index.php 있는 폴더 )에서
ls -la robots.txt sitemap.xml
```

- **이미 있으면** 백업 후 샘플과 **병합** (무조건 덮어쓰지 말 것)
- 없으면 샘플 복사

### 3.2 복사

```bash
cp sitemap.sample.xml sitemap.xml
cp robots.sample.txt robots.txt
```

### 3.3 반드시 수정

1. `https://example.com` → **실제 도메인** (전체 일괄 치환)
2. 사용하지 않는 `bo_table` URL 삭제
3. 공개한 지역 페이지·중요 글 URL 추가
4. `robots.txt`의 `Sitemap:` 줄 도메인 확인
5. 브라우저에서 `https://도메인/robots.txt`, `https://도메인/sitemap.xml` 접속 확인

### 3.4 HTTPS·www 통일

- canonical·sitemap·robots의 호스트가 **하나로 통일** (www vs non-www)
- 리다이렉트 정책과 Search Console 속성 일치

---

## 4. Google Search Console 등록

1. [Google Search Console](https://search.google.com/search-console) 접속
2. **속성 추가** — URL 접두어 `https://www.고객도메인.com` (실제 주소와 동일)
3. **소유권 확인** — HTML 태그, DNS, Google Analytics 등
4. **Sitemaps** 메뉴 → `https://고객도메인.com/sitemap.xml` 제출
5. **URL 검사**로 메인·대표 서브페이지 색인 요청 (필요 시)
6. **페이지 색인**·**리치 결과**에서 오류·FAQ·LocalBusiness 확인
7. **robots.txt** 테스트 도구로 차단 경로 확인

---

## 5. 네이버 서치어드바이저 등록

1. [네이버 서치어드바이저](https://searchadvisor.naver.com/) 로그인
2. **사이트 등록** — 실제 도메인
3. **소유 확인** — HTML 메타·파일 업로드 등
4. **요청 → 사이트맵 제출** — `https://고객도메인.com/sitemap.xml`
5. **수집 요청** — 메인·핵심 URL (과도한 반복 요청 지양)
6. **검색 노출·클릭** 리포트로 반영 여부 확인 (수일~수주 소요 가능)

---

## 6. 게시판 URL 포함 기준

| 포함 | 조건 |
|------|------|
| 게시판 **목록** | 공개·색인 의도, 품질 있는 목록 |
| **개별 글** | 공개 글, 중복·자동생성 스팸 아님, 본문 가치 있음 |
| 제외 | 비밀글, 임시글, 테스트, 관리자 전용 |

그누보드 URL 형식 예:

```text
/bbs/board.php?bo_table=news
/bbs/board.php?bo_table=news&wr_id=12
```

짧은 URL(rewrite) 사용 시 **실제 공개 URL** 형식으로 sitemap에 기입하세요.

---

## 7. noindex · 저품질 페이지 기준

| 페이지 | 권장 |
|--------|------|
| 문의 완료 | `noindex` + sitemap·robots 제외 |
| 404 | `noindex` |
| style-guide, local-template | robots Disallow + 미색인 |
| 검색 결과·필터 URL | noindex 또는 canonical 정리 |
| 중복 지역 페이지 | 통합·삭제·noindex |
| 얇은 콘텐츠 | 본문 보강 또는 noindex |

`$page_robots = 'noindex,nofollow';` — [components/seo-meta.php](components/seo-meta.php)

---

## 8. 개인정보처리방침 색인 여부

| 상황 | 권장 |
|------|------|
| 일반 기업 사이트 | **색인 허용** 가능 (신뢰·투명성, sitemap priority 낮게) |
| 최소 노출 희망 | `noindex` 또는 sitemap에서 제외 |
| 법무 요청 | 담당자 지침 우선 |

필수는 아니나, 푸터 링크와 URL 일치 여부는 [SEO-CHECKLIST.md](SEO-CHECKLIST.md)에서 확인하세요.

---

## 9. 점검 체크리스트

- [ ] `sitemap.xml` 도메인·경로 오타 없음
- [ ] `robots.txt` Sitemap URL 정확
- [ ] noindex 페이지가 sitemap에 없음
- [ ] adm/data/_BUILDER_INPUT 차단
- [ ] Search Console·서치어드바이저 사이트맵 제출
- [ ] canonical과 sitemap URL 스킴·호스트 일치

전체 SEO: [SEO-CHECKLIST.md](SEO-CHECKLIST.md) · 납품: [LAUNCH-CHECKLIST.md](LAUNCH-CHECKLIST.md)

---

## 관련 문서

- [LOCAL-SEO-GUIDE.md](LOCAL-SEO-GUIDE.md) — 지역 페이지·LocalBusiness
- [SECTION-GUIDE.md](SECTION-GUIDE.md) — FAQ Schema
- [README-START.md](README-START.md) — 프로젝트 시작
