@echo off
TITLE Fresh Express Grocery - Platform Setup
COLOR 0A

echo ========================================================
echo     Fresh Express Grocery - Flutter Platform Setup
echo ========================================================
echo.

WHERE flutter >nul 2>nul
IF %ERRORLEVEL% NEQ 0 (
    echo [!] Flutter SDK is not detected in your system PATH.
    echo     Download: https://docs.flutter.dev/get-started/install/windows
    echo.
    pause
    exit /b 1
)

cd /d "%~dp0"

IF NOT EXIST "android\" (
    echo [*] android\ folder not found - generating Android and iOS platform projects...
    call flutter create --platforms=android,ios --org com.freshexpress .
    IF %ERRORLEVEL% NEQ 0 (
        echo [!] flutter create failed.
        pause
        exit /b 1
    )
    echo.
    echo [i] Platform folders created. Now apply the manifest / Info.plist changes from:
    echo     platform_setup\README.md
    echo     ^(INTERNET + location permissions, cleartext HTTP for local dev, url_launcher queries^)
    echo.
) ELSE (
    echo [*] android\ folder already exists - skipping flutter create.
)

echo [*] Fetching Flutter packages...
call flutter pub get

echo.
echo [OK] Setup complete. Run the app with:  flutter run
echo      Real device? flutter run --dart-define=API_BASE_URL=http://YOUR_PC_IP:8000/api
echo.
pause
