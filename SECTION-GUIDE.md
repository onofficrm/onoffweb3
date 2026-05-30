# 섹션 구조 가이드

빌더·React/Tailwind 결과물을 **섹션 단위**로 그누보드 메인에 붙일 때의 기준입니다.

| 관련 문서 |
|-----------|
| [BUILDER-WORKFLOW.md](BUILDER-WORKFLOW.md) — 전체 적용 순서 |
| [index.php](index.php) — 섹션 include 목록 |
| [css/custom.css](css/custom.css) — 섹션 스타일 |

---

## 1. 기본 섹션 폴더 (`/section/`)

### 1.1 베이스에 포함된 섹션

| 파일 | 용도 |
|------|------|
| `hero.php` | 메인 히어로·비주얼·CTA |
| `service.php` | 서비스·제품 카드 |
| `advantage.php` | 강점·차별점 |
| `portfolio.php` | 포트폴리오·사례 |
| `latest.php` | 게시판 최신글 (`latest()` ) |
| `review.php` | 고객 후기 |
| `faq.php` | FAQ 아코디언 + FAQPage Schema (화면·Schema 동일 배열) |
| `contact.php` | 문의 CTA·연락처 |

### 1.2 빌더에서 추가할 때 자주 쓰는 섹션 (새 파일 생성)

| 파일명 예시 | 용도 |
|-------------|------|
| `process.php` | 진행 절차·단계 |
| `youtube.php` | 영상 소개 (임베드·링크) |
| `location.php` | 오시는 길 (`components/kakao-map.php` include 가능) |
| `cta.php` | 중간 전환 CTA |
| `client.php` | 고객사·파트너 로고 |
| `stats.php` | 숫자·실적 |
| `banner.php` | 띠 배너·이벤트 |
| `problem.php` / `solution.php` | 랜딩형 문제·해결 |

파일을 만든 뒤 **`index.php`의 `$g5_main_sections` 배열**에 이름을 추가합니다.

---

## 2. section 파일 기본 구조

```php
<?php
if (!defined('_GNUBOARD_')) exit;
// include_once(G5_PATH.'/section/_helpers.php'); // 이미지 사용 시
?>
<section class="section section-{이름}" id="section-{이름}">
  <div class="section-inner">
    <div class="section-head reveal">
      <p class="section-eyebrow">Eyebrow</p>
      <h2 class="section-title">섹션 제목</h2>
      <p class="section-desc">섹션 설명 문구</p>
    </div>
    <div class="section-content reveal">
      <!-- 카드·그리드·폼 등 -->
      <div class="section-actions">
        <a href="#section-contact" class="btn btn-primary">CTA</a>
      </div>
    </div>
  </div>
</section>
```

### 변형 class

| class | 용도 |
|-------|------|
| `section--alt` | 배경 surface (교차 배색) |
| `section--dark` | 다크 배경 (contact 등) |
| `section-hero` | 히어로 전용 레이아웃 (`section-hero__inner`, `section-hero__visual`) |

---

## 3. 공통 class 규칙

| class | 역할 |
|-------|------|
| `.section` | 섹션 루트 (세로 패딩·배경) |
| `.section-inner` | max-width 컨테이너 |
| `.section-eyebrow` | 상단 라벨 (소문자·primary 색) |
| `.section-title` | H2급 제목 |
| `.section-desc` | 부제·설명 |
| `.section-actions` | 버튼 그룹 |
| `.section-content` | 본문·그리드 래퍼 |
| `.card-grid` | 카드 그리드 (`card-grid--3`, `--auto`) |
| `.base-card` | 기본 카드 |
| `.media-card` | 썸네일 카드 |
| `.reveal` | 스크롤 등장 (`custom.js` `initReveal`) |

> `.section-grid`, `.section-card`는 빌더 전용 class를 **custom.css에 추가**할 때 사용해도 됩니다. 기존 `.card-grid`·`.base-card` 우선 권장.

---

## 4. index.php include 규칙

메인은 **배열 루프**로 섹션을 로드합니다 (`include_once` per file).

```php
$g5_main_sections = array(
    'hero',
    'service',
    // 'process',  // 새 섹션 추가 시
    'contact',
);

foreach ($g5_main_sections as $section_name) {
    $section_file = G5_PATH.'/section/'.$section_name.'.php';
    if (is_file($section_file)) {
        include_once($section_file);
    }
}
```

- 파일이 없으면 **건너뜀** (fatal 없음)
- 순서 = 화면 표시 순서

---

## 5. 섹션 추가·삭제

| 작업 | 방법 |
|------|------|
| **추가** | `section/이름.php` 생성 → `$g5_main_sections`에 `'이름'` 추가 |
| **삭제** | 배열에서 이름 제거 (파일은 보관 가능) |
| **순서 변경** | 배열 순서만 변경 |
| **일시 숨김** | 배열에서 주석 처리 |

---

## 6. 빌더 섹션 매핑

| 빌더 블록 | 그누보드 |
|-----------|----------|
| Hero | `section/hero.php` |
| Services / Features | `section/service.php` |
| Why us / Advantage | `section/advantage.php` |
| Portfolio / Works | `section/portfolio.php` |
| Testimonials | `section/review.php` |
| FAQ | `section/faq.php` |
| Contact / CTA | `section/contact.php` |
| Blog / News feed | `section/latest.php` |
| Map / Location | `section/location.php` 또는 `components/kakao-map.php` |
| Mid CTA | `components/bottom-cta.php` 또는 `section/cta.php` |

HTML은 section 파일에, CSS는 **`custom.css`의 `.site-main .section-{이름}`** 스코프에 작성합니다.

---

## 7. Scroll Snap

| 항목 | 내용 |
|------|------|
| **기본** | 비활성 (`G5Template.config.scrollSnapEnabled = false`) |
| **활성** | `js/custom.js`에서 `scrollSnapEnabled: true` → `#siteMain`에 `snap-enabled` class |
| **CSS** | `custom.css` — PC(1025px+)만 `scroll-snap-type` 적용, 모바일 off |
| **주의** | 게시판·서브페이지·관리자에는 적용하지 않음 |

---

## 8. FAQ · FAQPage Schema

### 8.1 데이터 구조 (`section/faq.php`)

**한 배열(`$g5_faq_items`)만 수정**하면 아코디언 화면과 JSON-LD가 같이 반영됩니다.

```php
$g5_faq_items = array(
    array(
        'question' => '상담은 어떻게 진행되나요?',
        'answer'   => '문의 접수 후 담당자가 확인하여 연락드립니다.',
    ),
);
```

| 규칙 | 설명 |
|------|------|
| **화면 = Schema** | `$g5_faq_items`에 넣은 항목만 아코디언·FAQPage에 출력 |
| **미표시 FAQ 금지** | `display:none`, 주석 처리, 별도 배열로 Schema만 출력하지 않음 |
| **빈 항목** | question·answer가 비면 화면·Schema 모두에서 제외 |
| **구형 키** | `q` / `a` 키도 `g5_sample_faq_schema_items()`에서 호환 |

### 8.2 Schema 출력 흐름

```
$g5_faq_items
  → g5_sample_faq_schema_items()  → $faq_schema_items
  → section HTML (.faq-list)
  → include components/schema/faq.php  → <script type="application/ld+json">
```

메인 `index.php`에 `faq` 섹션이 포함되어 있으면 **별도 작업 없이** Schema가 출력됩니다.

### 8.3 섹션형 FAQ vs 게시판형 FAQ

| 방식 | 경로 | 언제 쓰나 |
|------|------|-----------|
| **섹션형 (고정)** | [section/faq.php](section/faq.php) | 랜딩·메인에 **고정 FAQ** — PHP 배열만 수정 |
| **게시판형 (관리)** | 스킨 **`faq-accordion`** | 고객·관리자가 **관리자에서 FAQ CRUD** |

게시판 FAQ는 `wr_subject`(질문)·`wr_content`(답변)로 저장하고, 목록 아코디언·`components/schema/faq.php`가 **현재 페이지에 보이는 글만** Schema에 넣습니다. 자세한 설정은 [BOARD-SKIN-GUIDE.md](BOARD-SKIN-GUIDE.md) §4-1.

### 8.4 다른 페이지에서 FAQ Schema만 쓸 때

서브페이지·지역 랜딩 등에서 FAQ 블록을 직접 넣는 경우:

```php
<?php
include_once G5_PATH . '/section/_helpers.php';

$g5_faq_items = array(
    array('question' => '질문', 'answer' => '답변'),
);

// 화면 HTML은 직접 작성하거나 section/faq.php 구조를 복사

g5_sample_faq_output_schema($g5_faq_items);
// 또는 $faq_schema_items = g5_sample_faq_schema_items($g5_faq_items);
// include G5_PATH . '/components/schema/faq.php';
```

### 8.4 아코디언 JS

- `js/custom.js` → `G5Template.initFaqAccordion()`
- 마크업: `.faq-list` > `.faq-item` > `.faq-question` + `.faq-answer`
- Schema `<script>`는 섹션 **바로 아래**에 출력되며 JS와 충돌하지 않습니다.

### 8.5 SEO 검수

- 납품용 SEO 체크리스트: [SEO-CHECKLIST.md](SEO-CHECKLIST.md)
- Rich Results Test에서 FAQPage 인식 여부 확인
- 화면에 없는 질문이 Schema에만 있는지 소스 보기로 대조

---

## 9. 모바일 검수 기준

- [ ] `section-inner` 좌우 패딩·제목 줄바꿈
- [ ] `card-grid` 1열 전환 (768px 이하)
- [ ] `section-actions` 버튼 full-width
- [ ] `section-hero` 이미지·텍스트 순서
- [ ] FAQ 터치 영역·아코디언
- [ ] `contact` 다크 섹션 버튼 대비

---

## 10. 관련글 · 최신글 · 분류글 컴포넌트

`section/latest.php`의 `latest()` 그리드와 **별도**입니다. 카드 마크업·`.content-list` 클래스를 사용합니다.

| 파일 | 용도 |
|------|------|
| `components/latest-posts.php` | 게시판 최신 N건 |
| `components/category-posts.php` | 분류(`ca_name`) 글 — 분류 없으면 최신 fallback |
| `components/related-posts.php` | 키워드 검색 → 없으면 최신 fallback |
| `components/content-posts-helper.php` | 공통 조회·렌더 (직접 include 불필요) |

### 메인·서브 하단

```php
<?php
$latest_bo_table = 'news';
$latest_limit = 5;
$latest_title = '최신 소식';
$latest_skin_type = 'card'; // card | list
include_once G5_PATH . '/components/latest-posts.php';
?>
```

### 게시판 글보기 하단 (view.skin.php)

```php
<?php
$related_bo_table = $bo_table;
$related_keyword = $view['wr_subject']; // 선택
$related_exclude_wr_id = $view['wr_id'];
$related_limit = 4;
$related_title = '관련 글';
include_once G5_PATH . '/components/related-posts.php';
?>
```

### 분류별 (서브페이지)

```php
<?php
$category_bo_table = 'news';
$category_name = '보도자료';
$category_limit = 5;
$category_title = '보도자료';
include_once G5_PATH . '/components/category-posts.php';
?>
```

게시판·글 없음 → **출력 없음** (fatal 없음). 비밀글은 목록에서 제외.

---

## 11. Google Maps · 장소 찾기

메인 `section/` 과 별도로 **내 주변 찾기**는 `/page/map-locator.php` + `components/maps/` 모듈을 사용합니다.

- API 키: `_site.config.php` → `google_maps_api_key` (비우면 placeholder)
- 카카오 지도: `components/kakao-map.php` (오시는 길 단일 지점)
- Google Maps: [MAP-GUIDE.md](MAP-GUIDE.md), 빌더 연동 [MAP-BUILDER-WORKFLOW.md](MAP-BUILDER-WORKFLOW.md)

---

## 12. Cursor 프롬프트

섹션 작업 시 [PROMPTS.md](PROMPTS.md) §2(Hero), §3(메인 전체)를 사용하세요.

목적별 섹션 조합: [`presets/`](presets/) 문서 참고.
