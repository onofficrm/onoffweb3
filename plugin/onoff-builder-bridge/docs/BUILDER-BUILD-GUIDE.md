# 빌더 빌드(dist) 가이드

## 원본 vs dist

| 구분 | 구조 예시 | 플러그인 |
|------|-----------|----------|
| **원본** | `src/App.tsx`, `components/*.tsx`, `package.json`, `vite.config.ts` | ❌ 업로드 거부 |
| **dist** | `index.html`, `assets/index-xxxxx.js` | ✅ ZIP 업로드 |

`src/App.tsx` 형태는 **React/Vite 소스 프로젝트**입니다. 플러그인이 자동 변환하지 않습니다.

## 빌드 절차

```bash
npm install
npm run build
```

## dist 확인

```
dist/
  index.html
  assets/
    index-xxxxxxxx.js
    index-xxxxxxxx.css
```

## ZIP 만들기

1. `dist` **폴더 안으로** 들어가서 전체 선택 후 압축  
   또는 `dist` 내용만 선택해 ZIP 루트에 `index.html`이 오게 함
2. ZIP 확장자만 업로드 (`.zip`)

## 잘못된 ZIP 예

- 프로젝트 루트 전체 (`node_modules`, `src` 포함) → 차단·실패
- `package.json`만 있는 소스 ZIP → “원본 프로젝트” 안내

## 빌더·젠스파크·구글 스튜디오

빌더 UI는 디자인용으로 두고, **보내기/빌드된 정적 결과**만 dist ZIP으로 올립니다.
