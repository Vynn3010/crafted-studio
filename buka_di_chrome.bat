@echo off
:: Crafted Studio — Chrome Access Shortcut
title Membuka Crafted Studio di Chrome
echo ===================================================
echo             CRAFTED STUDIO - SHORTCUT
echo ===================================================
echo.
echo Pastikan Laragon Anda sudah aktif (klik "Start All").
echo.
echo Membuka Halaman Pelanggan (http://crafted-studio.test)...
start chrome "http://crafted-studio.test"
timeout /t 1 >nul

echo Membuka Halaman Admin (http://crafted-studio.test/admin)...
start chrome "http://crafted-studio.test/admin"
timeout /t 1 >nul

echo.
echo Jika domain .test tidak bisa diakses, coba alternatif localhost:
echo - Pelanggan: http://localhost/crafted-studio
echo - Admin: http://localhost/crafted-studio/admin
echo.
echo ===================================================
echo Selesai! Halaman sedang dibuka di Google Chrome.
echo ===================================================
pause
