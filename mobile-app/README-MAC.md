# CEBU 24 모바일 앱 프로젝트

이 폴더는 Mac에서 Android 앱과 iPhone 앱을 개발·배포하기 위한 전용 프로젝트입니다.

웹 화면과 서버는 현재 프로젝트에서 계속 개발하고, Mac은 네트워크로 이 폴더를 직접 엽니다.

- 웹 프로젝트: `긴급출동 세부24`
- 모바일 앱 프로젝트: `긴급출동 세부24/mobile-app`
- 서비스 주소: `https://onoffweb3.iwinv.net/`

`mobile-app`이 Android와 iOS 앱의 유일한 원본 폴더입니다. 예전에 사용하던 별도 Android 폴더에서는 더 이상 작업하지 않습니다.

## Mac에서 네트워크로 열기

1. Windows에서 `긴급출동 세부24` 폴더를 네트워크 공유합니다.
2. Mac Finder에서 `이동 > 서버에 연결`을 선택합니다.
3. Windows 공유 주소(`smb://컴퓨터주소/공유이름`)로 연결합니다.
4. Cursor에서는 공유 폴더 안의 `긴급출동 세부24/mobile-app`을 엽니다.

Android 배포 서명에 필요한 다음 파일도 폴더 안에 포함되어 있습니다.

- `android/upload-keystore.jks`
- `android/keystore.properties`

두 파일은 비밀 파일입니다. GitHub, 메일, 공개 드라이브에 올리지 마세요. Windows와 Mac에서 같은 파일을 동시에 수정하지 마세요.

네트워크 폴더이므로 Windows와 Mac에서 같은 소스 파일을 동시에 저장하면 마지막 저장 내용으로 덮어씌워질 수 있습니다. 웹 파일은 Windows에서, `mobile-app` 파일은 Mac에서 수정하는 원칙을 지키세요.

## Android 앱 열기

1. Mac에 Android Studio를 설치합니다.
2. Android Studio에서 네트워크 공유의 `긴급출동 세부24/mobile-app/android` 폴더를 엽니다.
3. Gradle 동기화가 끝날 때까지 기다립니다.
4. JDK는 17을 선택합니다.
5. 테스트 실행 또는 `Build > Generate Signed Bundle / APK`로 배포 파일을 만듭니다.

Mac 터미널에서 직접 빌드할 때 네트워크 드라이브의 실행 권한 문제로 `./gradlew`가 실행되지 않으면 다음처럼 실행합니다.

```bash
cd "/Volumes/공유이름/긴급출동 세부24/mobile-app/android"
sh ./gradlew bundleRelease
```

현재 Android 버전은 `android/app/build.gradle`에서 관리합니다.

```text
versionCode 9
versionName "1.0.8"
```

새 Play Store 버전을 만들 때마다 `versionCode`는 반드시 1씩 올리고 `versionName`도 변경합니다.

## iPhone 앱 열기

1. Mac App Store에서 Xcode를 설치합니다.
2. 터미널에서 XcodeGen을 한 번 설치합니다.

```bash
brew install xcodegen
```

3. 프로젝트를 생성합니다.

```bash
cd "/Volumes/공유이름/긴급출동 세부24/mobile-app/ios"
xcodegen generate
open Cebu24Rescue.xcodeproj
```

4. Xcode의 `Signing & Capabilities`에서 본인의 Apple Developer Team을 선택합니다.
5. iPhone을 연결하고 실행합니다.

## iOS 알림 관련 중요사항

iPhone 앱의 기본 웹 화면과 관리자 로그인 연동 구조는 준비되어 있습니다.

다만 iPhone은 Android처럼 백그라운드에서 주기적으로 서버를 확인할 수 없습니다. 앱을 닫은 상태에서도 관리자 새 접수 알림을 안정적으로 받으려면 다음 단계에서 APNs 푸시 알림을 서버에 연결해야 합니다.

Android의 기존 알림 기능은 현재 프로젝트에 그대로 포함되어 있습니다.

## 작업 구분

- 화면, 접수 기능, 관리자 페이지, 통계: Windows의 웹 프로젝트에서 수정
- Android WebView, Android 알림, Play Store 배포: Mac의 `android`
- iPhone WebView, APNs 알림, App Store 배포: Mac의 `ios`

웹 화면은 서버에서 불러오므로 일반적인 웹 화면 수정은 앱을 다시 배포하지 않아도 반영됩니다. 네이티브 알림, 권한, 앱 아이콘, 앱 버전 변경은 스토어 업데이트가 필요합니다.
