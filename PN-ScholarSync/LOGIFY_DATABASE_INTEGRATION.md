# Logify Database Integration - Permanent Setup

## Overview

The Logify integration has been **permanently configured** to use **DATABASE INTEGRATION ONLY**. The API integration has been disabled because the required API endpoints don't exist in the Logify system.

## Integration Method

✅ **DATABASE INTEGRATION** (Active)
- Connects directly to Logify's MySQL database
- Queries student late/absent records from database tables
- Creates violations in PN-ScholarSync based on Logify data
- Runs automatically every 60 seconds via Laravel scheduler

❌ **API INTEGRATION** (Disabled)
- API endpoints don't exist in Logify system
- Returns 404 errors when attempted
- Has been permanently disabled in configuration

## Configuration

### .env Settings
```env
# Logify Integration Configuration - DATABASE INTEGRATION ONLY
LOGIFY_TEST_MODE=false
LOGIFY_USE_DATABASE_IMPORT=true

# API settings (DISABLED - not used)
LOGIFY_API_ENABLED=false
LOGIFY_API_BASE_URL=http://localhost:8002
```

### Database Connection
The system uses the existing Logify database connection configured in your .env file:
- `LOGIFY_DB_HOST`
- `LOGIFY_DB_PORT` 
- `LOGIFY_DB_DATABASE`
- `LOGIFY_DB_USERNAME`
- `LOGIFY_DB_PASSWORD`

## Commands to Use

### ✅ CORRECT Commands (Database Integration)
```bash
# Get integration information and status
php artisan logify:info

# Test database connection and show available data
php artisan logify:import --test --detailed

# Force import data from database
php artisan logify:import --force --detailed

# Show comprehensive status
php artisan logify:status

# Clean up duplicate violations
php artisan logify:cleanup-violations --dry-run
php artisan logify:cleanup-violations --force

# Setup violation types (run once)
php artisan logify:setup-violation-types
```

### ❌ AVOID These Commands (API Integration - Disabled)
```bash
# These will show "API integration is disabled" message
php artisan logify:sync --test
php artisan logify:sync --force
```

## Automatic Scheduling

The system automatically runs `logify:import` every 60 seconds:

### Laravel Scheduler (Configured)
```php
$schedule->command('logify:import')
         ->everyMinute()
         ->withoutOverlapping(10)
         ->runInBackground()
         ->appendOutputTo(storage_path('logs/logify-sync.log'));
```

### Start the Scheduler
```bash
# Option 1: Use the batch file (Windows)
start-logify-scheduler.bat

# Option 2: Run manually
php artisan schedule:run

# Option 3: Check scheduled tasks
php artisan schedule:list
```

## Monitoring

### Log Files
- **Sync Activity**: `storage/logs/logify-sync.log`
- **Laravel Logs**: `storage/logs/laravel.log`

### Status Commands
```bash
# Quick status check
php artisan logify:status

# Detailed integration info
php artisan logify:info

# Check violations created
php artisan tinker --execute="echo 'Logify violations: ' . \App\Models\Violation::whereNotNull('logify_sync_batch_id')->count();"
```

## How It Works

1. **Every 60 seconds**: Laravel scheduler runs `logify:import`
2. **Database Query**: Connects to Logify database and queries:
   - `academics` table for late login/logout records
   - `going_out` table for going-out late records  
   - `academics` table for absent records
3. **Duplicate Prevention**: Checks existing violations for current month/year
4. **Violation Creation**: Creates new violations only for new incidents
5. **Logging**: All activity logged to `storage/logs/logify-sync.log`

## Violation Types Created

The system creates these specific violation types:
- **Academic Login Late** (ID: 53) - Late login to academic sessions
- **Academic Logout Late** (ID: 54) - Late logout from academic sessions  
- **Going-out Login Late** (ID: 55) - Late login when going out
- **Academic Absent** (ID: 56) - Absent from academic sessions

All violations:
- Category: "Schedule"
- Default penalty: "VW" (Verbal Warning)
- Status: "active" 
- Action taken: true (default)
- Consequence: "To be assigned by educator"

## Troubleshooting

### If Import Fails
```bash
# Check database connection
php artisan logify:import --test --detailed

# Check configuration
php artisan logify:info

# View logs
tail -f storage/logs/logify-sync.log
```

### If Duplicates Appear
```bash
# Check for duplicates (dry run)
php artisan logify:cleanup-violations --dry-run

# Clean up duplicates
php artisan logify:cleanup-violations --force
```

### If Scheduler Not Running
```bash
# Check scheduled tasks
php artisan schedule:list

# Run manually
php artisan schedule:run

# Start continuous scheduler
start-logify-scheduler.bat
```

## Benefits of Database Integration

✅ **Reliable**: Direct database connection, no API dependencies
✅ **Fast**: Direct SQL queries, no HTTP overhead
✅ **Consistent**: No network timeouts or connection issues
✅ **Real-time**: Access to live data in Logify database
✅ **Efficient**: Batch processing of multiple records
✅ **Duplicate-safe**: Built-in duplicate prevention logic

This setup provides a robust, reliable integration that will work consistently without the issues that plagued the API approach.
