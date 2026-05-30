# 보안 안내

## 위험성

ZIP 업로드는 **임의 정적 파일**을 서버에 올리는 것과 같습니다. **최고관리자만** 사용하세요.

## 차단 항목

- `*.php`, `*.phtml`, `*.phar`
- `.htaccess`, `web.config`
- `.env`, `composer.json`, `package.json`
- `node_modules/`, `.git/`
- ZIP Slip (`../`, 절대경로)

## 업로드 전 확인

- 빌드 **dist**만 포함
- API 키·DB 비밀번호·`.env` 제거
- Google Maps / 결제 키가 빌드에 박혀 있지 않은지 확인

## 실행

- 업로드된 PHP는 압축 해제 단계에서 **거부**
- `imports/.htaccess`로 PHP·**HTML 직접 열람** 차단(서버 지원 시) — 공개는 `page.php?id=` 만
- HTML 내 `<script>`는 dist 실행을 위해 **허용** — 신뢰할 수 있는 빌드만

## CSP·고급

엄격 CSP·서브리소스 무결성은 **추후 과제**입니다.
