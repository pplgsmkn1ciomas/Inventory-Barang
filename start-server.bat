@echo off
setlocal EnableDelayedExpansion

set "HOST=127.0.0.1"
set "PORT=8000"
set "TEST_PORT=8001"
set "MODE=dev"

if exist ".env" (
  for /f "usebackq tokens=1,* delims==" %%A in (".env") do (
    set "ENV_KEY=%%A"
    set "ENV_VAL=%%B"

    if /I "!ENV_KEY!"=="DEV_SERVER_HOST" set "HOST=!ENV_VAL!"
    if /I "!ENV_KEY!"=="DEV_SERVER_PORT" set "PORT=!ENV_VAL!"
    if /I "!ENV_KEY!"=="TEST_SERVER_PORT" set "TEST_PORT=!ENV_VAL!"
  )
)

if /I "%~1"=="test" (
  set "MODE=test"
  set "PORT=%TEST_PORT%"
  if not "%~2"=="" set "PORT=%~2"
) else (
  if not "%~1"=="" set "PORT=%~1"
)

where php >nul 2>nul
if errorlevel 1 (
  color 0C
  echo PHP tidak ditemukan di PATH.
  pause
  exit /b 1
)

if not exist ".tmp" mkdir ".tmp"
set "TEMP=%CD%\.tmp"
set "TMP=%CD%\.tmp"

color 0A
title Inventory Barang Laravel Server [%MODE%] (%HOST%:%PORT%)

echo ===============================================
echo  MENJALANKAN INVENTORY BARANG (LARAVEL)
echo ===============================================
echo Mode: %MODE%
echo URL: http://%HOST%:%PORT%
echo.

echo Menyiapkan database...
php artisan migrate --force
if errorlevel 1 (
  color 0C
  echo Migrasi database gagal.
  pause
  exit /b 1
)

php artisan db:seed --force
if errorlevel 1 (
  color 0C
  echo Seeding database gagal.
  pause
  exit /b 1
)

echo Database siap.
echo.

start "" /b php -d display_errors=0 -d log_errors=1 artisan serve --host=%HOST% --port=%PORT%

echo Menunggu server siap...
set "SERVER_READY=0"
for /L %%I in (1,1,30) do (
  powershell -NoProfile -Command "try { $response = Invoke-WebRequest -UseBasicParsing -Uri 'http://%HOST%:%PORT%' -TimeoutSec 1; if ($response.StatusCode -ge 200) { exit 0 } exit 1 } catch { exit 1 }" >nul 2>nul
  if not errorlevel 1 (
    set "SERVER_READY=1"
    goto server_ready
  )
  timeout /t 1 /nobreak >nul
)

echo Server belum merespons setelah menunggu, browser tetap akan dibuka.
:server_ready
start "" http://%HOST%:%PORT%
pause
