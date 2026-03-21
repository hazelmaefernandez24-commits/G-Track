@echo off
echo Setting up Laravel Logify Scheduler...
echo.
echo This will create a Windows Task Scheduler entry to run the Logify sync every minute.
echo Please run this batch file as Administrator.
echo.

cd /d "C:\GROUP10\PN_Systems\PN-ScholarSync"

echo Creating scheduled task...
schtasks /create /tn "Laravel Logify Scheduler" /tr "cmd /c cd /d \"C:\GROUP10\PN_Systems\PN-ScholarSync\" && php artisan schedule:run" /sc minute /mo 1 /ru SYSTEM /f

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ✅ SUCCESS: Scheduled task created successfully!
    echo.
    echo The Logify sync will now run automatically every 60 seconds.
    echo.
    echo To verify the task was created, run:
    echo schtasks /query /tn "Laravel Logify Scheduler"
    echo.
    echo To delete the task later, run:
    echo schtasks /delete /tn "Laravel Logify Scheduler" /f
) else (
    echo.
    echo ❌ ERROR: Failed to create scheduled task.
    echo Please make sure you're running this as Administrator.
)

echo.
pause
