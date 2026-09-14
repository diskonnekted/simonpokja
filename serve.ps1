# SIMONPOKJA PowerShell Server Runner
$scriptPath = Split-Path -Parent $MyInvocation.MyCommand.Path
$env:PATH = "$scriptPath\.tools\php;$env:PATH"

Write-Host "===================================================" -ForegroundColor Cyan
Write-Host "  SIMONPOKJA - LPSE Kabupaten Banjarnegara" -ForegroundColor Green
Write-Host "  Running with Portable PHP 8.3" -ForegroundColor Yellow
Write-Host "  URL: http://127.0.0.1:8080" -ForegroundColor Cyan
Write-Host "===================================================" -ForegroundColor Cyan

php artisan serve --host=127.0.0.1 --port=8080
