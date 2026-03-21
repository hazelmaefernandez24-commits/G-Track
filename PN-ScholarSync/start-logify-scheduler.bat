@echo off
echo ========================================
echo  Logify Database Integration Scheduler
echo ========================================
echo.
echo This will run the Laravel scheduler every minute to import Logify data.
echo Using DATABASE INTEGRATION (API integration is disabled).
echo Press Ctrl+C to stop the scheduler.
echo.
echo Current Integration Status:
php artisan logify:info
echo.
echo Starting scheduler loop...
echo Logs will be written to: storage/logs/logify-sync.log
echo.

:loop
echo [%date% %time%] Running scheduler...
php artisan schedule:run
timeout /t 60 /nobreak >nul
goto loop
