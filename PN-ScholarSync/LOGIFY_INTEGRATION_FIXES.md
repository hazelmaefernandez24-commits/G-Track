# Logify Integration Fixes

## Issues Fixed

### 1. API Connection Failures
**Problem**: The `logify:sync` command was failing to connect to the Logify API on port 8002, returning 404 errors.

**Root Cause**: The Logify system doesn't have the required API endpoints (`/api/scholar-sync/recent-updates`) that the sync command was trying to access.

**Solution**: 
- Switched to direct database import approach using `logify:import` command
- Updated `.env` configuration to use database import instead of API sync
- Modified scheduler to use `logify:import` instead of `logify:sync`

### 2. Duplicate Violations
**Problem**: Multiple violations were being created for the same student and violation type each time the import ran.

**Root Cause**: The duplicate detection logic was not considering the current month/year, causing violations to be created repeatedly.

**Solution**:
- Enhanced duplicate detection in `createSpecificViolations()` method
- Added month/year filtering to prevent duplicates within the same period
- Improved logging to show existing vs new violations created

### 3. Mixed Integration Approaches
**Problem**: Both API-based sync and database import were running simultaneously, causing confusion and potential conflicts.

**Solution**:
- Standardized on database import approach (`logify:import`)
- Updated scheduler configuration to use only the import command
- Added configuration option `LOGIFY_USE_DATABASE_IMPORT=true` in `.env`

## New Commands Added

### 1. `php artisan logify:status`
Shows comprehensive status of the Logify integration including:
- Database connection status
- Available violation types
- Current month statistics
- Recent activity
- Scheduler status

### 2. `php artisan logify:cleanup-violations`
Cleans up duplicate or unwanted Logify violations:
- `--dry-run`: Shows what would be deleted without actually deleting
- `--force`: Force cleanup without confirmation
- Identifies and removes duplicate violations

### 3. `start-logify-scheduler.bat`
Windows batch file to run the Laravel scheduler continuously:
- Runs `php artisan schedule:run` every 60 seconds
- Shows initial status before starting
- Easy to start/stop with Ctrl+C

## Configuration Changes

### `.env` Updates
```env
# Logify Integration Configuration
# Set to true to disable API and use direct database import instead
LOGIFY_TEST_MODE=false
LOGIFY_USE_DATABASE_IMPORT=true
LOGIFY_API_BASE_URL=http://localhost:8002
LOGIFY_API_TIMEOUT=30
LOGIFY_API_RETRY_ATTEMPTS=3
LOGIFY_API_RETRY_DELAY=1000
```

### Scheduler Configuration
Updated `bootstrap/app.php` to use database import:
```php
$schedule->command('logify:import')
         ->everyMinute()
         ->withoutOverlapping(10)
         ->runInBackground()
         ->appendOutputTo(storage_path('logs/logify-sync.log'));
```

## How It Works Now

1. **Every 60 seconds**: Laravel scheduler runs `logify:import` command
2. **Database Connection**: Connects directly to Logify database using configured credentials
3. **Data Fetching**: Retrieves late/absent student data from Logify tables
4. **Duplicate Prevention**: Checks existing violations for current month/year before creating new ones
5. **Violation Creation**: Creates violations only for new incidents
6. **Logging**: All activity logged to `storage/logs/logify-sync.log`

## Testing Commands

```bash
# Test database connection and show available data
php artisan logify:import --test --detailed

# Force import (useful for testing)
php artisan logify:import --force --detailed

# Check overall status
php artisan logify:status

# Clean up duplicates (dry run first)
php artisan logify:cleanup-violations --dry-run
php artisan logify:cleanup-violations --force

# Run scheduler manually
php artisan schedule:run

# Check scheduler configuration
php artisan schedule:list
```

## Monitoring

- **Log File**: `storage/logs/logify-sync.log`
- **Status Command**: `php artisan logify:status`
- **Laravel Logs**: `storage/logs/laravel.log` for detailed error information

## Next Steps

1. **Start the Scheduler**: Run `start-logify-scheduler.bat` to begin automatic syncing
2. **Monitor Logs**: Check `storage/logs/logify-sync.log` for sync activity
3. **Verify Data**: Use `php artisan logify:status` to confirm violations are being created
4. **Production Setup**: Consider setting up Windows Task Scheduler for production deployment

## Violation Types Created

The system now creates specific violation types for different Logify incidents:
- **Academic Login Late** (ID: 53)
- **Academic Logout Late** (ID: 54) 
- **Going-out Login Late** (ID: 55)
- **Academic Absent** (ID: 56)

All violations are created with:
- Category: "Schedule"
- Default penalty: "VW" (Verbal Warning)
- Status: "active"
- Action taken: true (default)
- Consequence: "To be assigned by educator"
