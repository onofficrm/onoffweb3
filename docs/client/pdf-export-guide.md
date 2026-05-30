# Markdown 운영 가이드 → PDF 변환 가이드

고객 납품용 **사이트 운영 가이드**를 PDF로 만들 때 참고하는 내부 문서입니다.

| 관련 파일 | 용도 |
|-----------|------|
| [site-operation-guide-template.md](site-operation-guide-template.md) | 프로젝트별 치환용 원본 |
| [site-operation-guide-sample.md](site-operation-guide-sample.md) | 작성·레이아웃 참고 샘플 |

---

## 1. PDF 변환 목적

| 용도 | 설명 |
|------|------|
| **고객 전달** | 인쇄·이메일 첨부·보관용 |
| **내부 보관** | 납품 이력·버전 관리 |
| **납품 문서** | 계약·검수 자료에 첨부 |

---

## 2. 변환 전 확인할 항목

PDF로 만들기 **전** 아래를 확인합니다.

- [ ] `{{SITE_NAME}}` → 실제 사이트명으로 교체
- [ ] `{{CLIENT_NAME}}` → 실제 고객명으로 교체
- [ ] `{{DOMAIN}}` → 실제 도메인으로 교체
- [ ] `{{ADMIN_URL}}` → 실제 관리자 URL로 교체
- [ ] `{{SUPPORT_CONTACT}}` · `{{AGENCY_NAME}}` · `{{DELIVERY_DATE}}` 교체
- [ ] 「샘플」「example.com」 등 **샘플 문구 삭제**
- [ ] **관리자 비밀번호**가 문서에 없는지 확인
- [ ] **FTP·DB·API 키**가 문서에 없는지 확인
- [ ] **실제 고객 개인정보**(실명·실전화 등) 불필요 포함 여부 확인

> 관리자 계정은 **PDF와 별도**로 안전하게 전달하세요.

---

## 3. 권장 변환 방법

### 방법 A: VS Code — Markdown PDF 확장

1. VS Code에서 `site-operation-guide-고객명.md` 열기
2. 확장 **「Markdown PDF」** 설치 (없는 경우)
3. 명령 팔레트 → **Markdown PDF: Export (pdf)** 실행
4. 생성된 PDF 열어 표·제목 깨짐 여부 확인

### 방법 B: Typora

1. Typora에서 Markdown 파일 열기
2. **파일 →보내기 → PDF**
3. 여백·글꼴 확인 후 저장

### 방법 C: Pandoc (명령줄)

프로젝트 루트 또는 `docs/client/`에서:

```bash
pandoc site-operation-guide-template.md -o client-site-operation-guide.pdf \
  --pdf-engine=xelatex \
  -V geometry:margin=2.5cm \
  -V mainfont="Apple SD Gothic Neo"
```

- 한글 깨짐 시 `--pdf-engine=xelatex`와 한글 폰트 지정 필요
- macOS·Windows마다 폰트명이 다를 수 있음

### 방법 D: 브라우저 인쇄

1. VS Code **Markdown 미리보기** 또는 Typora 미리보기
2. 브라우저에서 열기 (또는 미리보기 → 인쇄)
3. **인쇄 → PDF로 저장**
4. **배경 그래픽** 포함 여부·여백 확인

---

## 4. PDF 파일명 규칙

| 패턴 | 예시 |
|------|------|
| 고객명-용도-연도 | `acme-site-operation-guide-2026.pdf` |
| 사이트명-버전 | `sample-home-manual-v1.0.pdf` |

- 공백 대신 **하이픈(-)** 권장
- 내부용·고객용 구분 시 `-internal` 접미사 사용 가능

---

## 5. PDF 디자인 권장사항

1. **표지** — 사이트명·고객사·납품일·제작사 (1페이지, 선택)
2. **목차** — Pandoc·Typora에서 자동 생성 가능
3. **문단** — 한 단락이 너무 길지 않게 (PDF 가독성)
4. **캡처 이미지** — 필요한 단계만 삽입 (용량·인쇄 품질)
5. **표·체크리스트** — 변환 후 페이지 나눔 깨짐 확인
6. **머리말/꼬리말** — 「샘플」·「기밀」 표시 (필요 시)

---

## 6. 보안 주의사항

| 금지 | 이유 |
|------|------|
| 관리자 **비밀번호** PDF 포함 | 유출 시 전체 관리 권한 노출 |
| **FTP·DB** 접속 정보 | 서버 침해 위험 |
| **API 키·웹훅 URL** | 악용 가능 |
| 불필요한 **고객 개인정보** | 개인정보보호법 |

- PDF 공유 범위를 **고객 담당자**로 제한
- 메신저 전송 시 **압축+비밀번호** 또는 안전한 채널 사용

---

## 7. 납품 시 함께 전달하면 좋은 것

| 항목 | 전달 방식 |
|------|-----------|
| **사이트 운영 가이드 PDF** | 이메일·USB·클라우드 링크 |
| **관리자 계정** | **별도** (전화·암호 메모 등) |
| **유지보수 연락처** | PDF §20 또는 명함 |
| **오픈 체크 요약** | [LAUNCH-CHECKLIST.md](../../LAUNCH-CHECKLIST.md) 요약본 (선택) |

---

## 8. 작업 흐름 (권장)

1. `site-operation-guide-template.md` → `docs/client/프로젝트명-site-operation-guide.md` 로 복사
2. `{{...}}` 변수 일괄 치환
3. [§2 체크리스트](#2-변환-전-확인할-항목) 확인
4. PDF 변환 (방법 A~D 중 팀 표준)
5. PDF 열어 제목·표·체크박스 확인
6. 고객 전달 + 관리자 계정 별도 전달

---

*내부 제작용 — 고객 PDF에는 FTP·DB·개발자 전용 경로를 넣지 마세요.*
