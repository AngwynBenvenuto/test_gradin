param([string]$ProjectName = "courier-api")
$ErrorActionPreference = 'Stop'
if (-not (Get-Command composer -ErrorAction SilentlyContinue)) { throw 'Composer belum terinstall / belum ada di PATH.' }
if (Test-Path $ProjectName) { throw "Folder '$ProjectName' sudah ada. Gunakan nama lain." }
composer create-project laravel/laravel $ProjectName "10.*"
if ($LASTEXITCODE -ne 0) { throw 'Gagal mengunduh Laravel 10.' }
$source = Split-Path -Parent $MyInvocation.MyCommand.Path
Copy-Item (Join-Path $source 'app') (Join-Path $ProjectName 'app') -Recurse -Force
Copy-Item (Join-Path $source 'database') (Join-Path $ProjectName 'database') -Recurse -Force
Copy-Item (Join-Path $source 'routes/api.php') (Join-Path $ProjectName 'routes/api.php') -Force
Copy-Item (Join-Path $source 'tests/Feature/CourierApiTest.php') (Join-Path $ProjectName 'tests/Feature/CourierApiTest.php') -Force
Write-Host "Laravel 10 siap di folder: $ProjectName"
Write-Host 'Atur .env lalu jalankan: php artisan migrate; php artisan test --filter=CourierApiTest'
