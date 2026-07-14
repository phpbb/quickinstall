@echo off
where php >nul 2>&1
if errorlevel 1 (
    echo QuickInstall requires PHP 8.0 or newer. 1>&2
    echo Install PHP, add php.exe to PATH, then reopen your terminal. 1>&2
    exit /b 1
)
php "%~dp0qi" %*
exit /b %ERRORLEVEL%
