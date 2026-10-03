@echo off
TITLE Build Fresh Express Grocery Flutter APK
COLOR 0A

echo ========================================================
echo     Fresh Express Grocery - Flutter APK Builder
echo ========================================================
echo.

WHERE flutter >nul 2>nul
IF %ERRORLEVEL% NEQ 0 (
    echo [!] Flutter SDK is not detected in your system PATH.
    echo.
    echo Please ensure Flutter SDK is installed and added to PATH.
    echo Official Download: https://docs.flutter.dev/get-started/install/windows
    echo.
    echo Once installed, reopen this script to compile the APK automatically.
    echo.
    pause
    exit /b
)

echo [*] Flutter detected! Checking dependencies...
cd /d "%~dp0\flutter_app"

IF NOT EXIST "android\" (
    echo [*] android\ folder not found - generating platform projects...
    call flutter create --platforms=android,ios --org com.freshexpress .
    echo.
    echo [i] Remember to apply the AndroidManifest changes from flutter_app\platform_setup\README.md
    echo     ^(INTERNET, location permissions, usesCleartextTraffic, url_launcher queries^).
    echo.
)

echo [*] Fetching Flutter packages...
call flutter pub get

echo [*] Building Release APK for Android...
call flutter build apk --release

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================================
    echo  [SUCCESS] APK Generated Successfully!
    echo ========================================================
    echo File Location: %~dp0flutter_app\build\app\outputs\flutter-apk\app-release.apk
    echo.
    echo You can now transfer app-release.apk to your phone and install it!
    echo.
) else (
    echo.
    echo [!] Build encountered an error. Please ensure Android SDK and Java JDK are configured.
    echo.
)

pause
