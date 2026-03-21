@echo off
echo ========================================
echo Logify Database Triggers Installation
echo ========================================
echo.

echo This script will help you install Logify database triggers
echo for real-time ScholarSync integration.
echo.

echo Prerequisites:
echo - Logify MySQL database access
echo - ScholarSync database with proper tables
echo - Database user with CREATE TRIGGER privileges
echo.

pause

echo.
echo Step 1: Checking Laravel environment...
php artisan --version
if %errorlevel% neq 0 (
    echo ERROR: Laravel not found. Please run this from your Laravel project directory.
    pause
    exit /b 1
)

echo.
echo Step 2: Checking Logify configuration...
php artisan logify:status
if %errorlevel% neq 0 (
    echo WARNING: Logify integration not properly configured.
    echo Please check your .env file for LOGIFY_DB_* settings.
    pause
)

echo.
echo Step 3: Installing database triggers...
php artisan logify:triggers install --force
if %errorlevel% neq 0 (
    echo ERROR: Failed to install triggers.
    echo Please check database connection and privileges.
    pause
    exit /b 1
)

echo.
echo Step 4: Verifying installation...
php artisan logify:triggers status
if %errorlevel% neq 0 (
    echo WARNING: Could not verify trigger status.
)

echo.
echo Step 5: Testing integration...
php artisan logify:test-triggers --detailed
if %errorlevel% neq 0 (
    echo WARNING: Some tests failed. Please review the output above.
)

echo.
echo ========================================
echo Installation Complete!
echo ========================================
echo.
echo Next steps:
echo 1. Monitor the system for 24-48 hours
echo 2. Check logs: storage/logs/laravel.log
echo 3. Test with actual Logify data
echo 4. Consider disabling scheduled import if triggers work well
echo.
echo Commands available:
echo - php artisan logify:triggers status
echo - php artisan logify:triggers test
echo - php artisan logify:triggers enable/disable
echo - php artisan logify:test-triggers --detailed
echo.
echo For support, check: LOGIFY_TRIGGERS_INTEGRATION.md
echo.

pause


