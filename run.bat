@echo off
set "PATH=%~dp0.tools\php;%PATH%"
echo ===================================================
echo   SIMONPOKJA - LPSE Kabupaten Banjarnegara
echo   Running with Portable PHP 8.3
echo   URL: http://127.0.0.1:8080
echo ===================================================
php artisan serve --host=127.0.0.1 --port=8080
