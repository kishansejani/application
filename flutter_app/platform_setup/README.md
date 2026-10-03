# Platform setup (Android & iOS)

This repository ships only the Dart code (`lib/`) – the `android/` and `ios/`
folders are generated on your machine. Run once from `flutter_app/`:

```bat
setup_platforms.bat
```

or manually:

```bash
flutter create --platforms=android,ios --org com.freshexpress .
flutter pub get
```

Then apply the changes below (they are required for networking, the map
picker, `url_launcher` and the in-app WebView).

---

## Android

### 1. `android/app/src/main/AndroidManifest.xml`

Add the permissions **above** the `<application>` tag, add
`android:usesCleartextTraffic="true"` to `<application>` (needed for the local
`http://` Laravel server – remove it for an HTTPS production server), and add
the `<queries>` block (Android 11+ package visibility for `url_launcher`).

```xml
<manifest xmlns:android="http://schemas.android.com/apk/res/android">

    <!-- Networking -->
    <uses-permission android:name="android.permission.INTERNET" />
    <uses-permission android:name="android.permission.ACCESS_NETWORK_STATE" />

    <!-- Map picker "use current location" (geolocator) -->
    <uses-permission android:name="android.permission.ACCESS_FINE_LOCATION" />
    <uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION" />

    <application
        android:label="Fresh Express"
        android:name="${applicationName}"
        android:icon="@mipmap/ic_launcher"
        android:usesCleartextTraffic="true">
        <!-- ... the generated <activity> stays as it is ... -->
    </application>

    <!-- url_launcher: links opened from the app / WebView -->
    <queries>
        <intent>
            <action android:name="android.intent.action.VIEW" />
            <data android:scheme="https" />
        </intent>
        <intent>
            <action android:name="android.intent.action.VIEW" />
            <data android:scheme="http" />
        </intent>
        <intent>
            <action android:name="android.intent.action.DIAL" />
            <data android:scheme="tel" />
        </intent>
        <intent>
            <action android:name="android.intent.action.SENDTO" />
            <data android:scheme="mailto" />
        </intent>
        <intent>
            <action android:name="android.intent.action.VIEW" />
            <data android:scheme="upi" />
        </intent>
        <intent>
            <action android:name="android.intent.action.VIEW" />
            <data android:scheme="whatsapp" />
        </intent>
        <intent>
            <action android:name="android.intent.action.VIEW" />
            <data android:scheme="geo" />
        </intent>
        <!-- Required by the Flutter engine for text processing -->
        <intent>
            <action android:name="android.intent.action.PROCESS_TEXT" />
            <data android:mimeType="text/plain" />
        </intent>
    </queries>
</manifest>
```

> If your generated manifest already contains a `<queries>` block (newer Flutter
> templates add `PROCESS_TEXT`), merge the `<intent>` entries into it instead of
> adding a second block.

### 2. Minimum SDK – `android/app/build.gradle` (or `build.gradle.kts`)

`webview_flutter`, `geolocator` and `url_launcher` need **minSdk 21** (Flutter's
default `flutter.minSdkVersion` is already ≥ 21 on recent SDKs – only change it
if your template shows a lower value):

```gradle
android {
    defaultConfig {
        minSdkVersion 21          // Groovy  (build.gradle)
        // minSdk = 21            // Kotlin  (build.gradle.kts)
    }
}
```

`geolocator` 11+ also requires `compileSdk 34` or higher – current Flutter
templates use `flutter.compileSdkVersion` which satisfies this.

---

## iOS – `ios/Runner/Info.plist`

Add these keys inside the top-level `<dict>`:

```xml
<!-- geolocator: map picker "use current location" -->
<key>NSLocationWhenInUseUsageDescription</key>
<string>Your location is used to pin your delivery address on the map.</string>

<!-- DEV ONLY: allow http:// to the local Laravel server. Remove for production (HTTPS). -->
<key>NSAppTransportSecurity</key>
<dict>
    <key>NSAllowsArbitraryLoads</key>
    <true/>
    <key>NSAllowsArbitraryLoadsInWebContent</key>
    <true/>
</dict>

<!-- url_launcher: schemes the app may open -->
<key>LSApplicationQueriesSchemes</key>
<array>
    <string>https</string>
    <string>http</string>
    <string>tel</string>
    <string>mailto</string>
    <string>whatsapp</string>
    <string>upi</string>
    <string>tez</string>
    <string>phonepe</string>
    <string>paytmmp</string>
</array>
```

Minimum iOS deployment target: **12.0** (set in `ios/Podfile`:
`platform :ios, '12.0'`) – newer Flutter templates already use 12+/13+.

---

## Verify

```bash
flutter doctor
flutter analyze
flutter run                       # Android emulator -> http://10.0.2.2:8000/api
flutter run --dart-define=API_BASE_URL=http://192.168.1.100:8000/api   # real device
```
