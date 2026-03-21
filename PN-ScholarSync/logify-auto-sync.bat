@echo off
echo Starting Logify Auto-Sync Service...
echo This will run every 60 seconds. Press Ctrl+C to stop.
echo.

cd /d "C:\GROUP10\PN_Systems\PN-ScholarSync"

:loop
echo [%date% %time%] Running Logify sync...
php artisan logify:import
echo [%date% %time%] Sync completed. Waiting 60 seconds...
echo.
timeout /t 60 /nobreak >nul
goto loop
