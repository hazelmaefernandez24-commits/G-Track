# Logify Database Triggers Integration

## Overview

This integration provides **real-time synchronization** between the Logify attendance system and ScholarSync violation management system using MySQL database triggers. Instead of relying on scheduled imports every 60 seconds, the system now automatically syncs data whenever students are marked as late or absent in the Logify system.

## How It Works

### 1. Database Triggers
- **Triggers are installed directly in the Logify database**
- Monitor `academics` and `going_outs` tables for INSERT/UPDATE operations
- Automatically detect late/absent status changes
- Immediately sync data to ScholarSync database

### 2. Real-time Processing
- **INSERT triggers**: Fire when new attendance records are created
- **UPDATE triggers**: Fire when existing records are modified (e.g., late status changes)
- **Automatic violation creation**: Creates violations in ScholarSync based on late/absent counts
- **Duplicate prevention**: Prevents creating duplicate violations for the same month/year

### 3. Data Flow
```
Logify Database → Triggers → ScholarSync Database
     ↓              ↓              ↓
academics      → SyncLateRecord → logify_late_records
going_outs     → SyncAbsentRecord → logify_absent_records
                ↓              ↓
            CreateViolations → violations
```

## Installation

### Prerequisites
1. Access to Logify MySQL database
2. ScholarSync database with proper tables and violation types
3. Database user with CREATE TRIGGER and CREATE PROCEDURE privileges

### Step 1: Install Triggers
```bash
# Install triggers in Logify database
php artisan logify:triggers install

# Verify installation
php artisan logify:triggers status
```

### Step 2: Configure Environment
Add to your `.env` file:
```env
# Logify Database Connection
LOGIFY_DB_HOST=127.0.0.1
LOGIFY_DB_PORT=3306
LOGIFY_DB_DATABASE=logify
LOGIFY_DB_USERNAME=your_username
LOGIFY_DB_PASSWORD=your_password

# Trigger Settings
LOGIFY_TRIGGERS_ENABLED=true
LOGIFY_AUTO_CREATE_VIOLATIONS=true
LOGIFY_UPDATE_EXISTING=true
LOGIFY_LOG_TRIGGERS=true
```

### Step 3: Verify Violation Types
Ensure these violation types exist in ScholarSync:
- Academic Login Late
- Academic Logout Late
- Going-out Login Late
- Academic Absent

## Commands

### Management Commands
```bash
# Install triggers
php artisan logify:triggers install

# Uninstall triggers
php artisan logify:triggers uninstall

# Check status
php artisan logify:triggers status

# Enable/disable triggers
php artisan logify:triggers enable
php artisan logify:triggers disable

# Test triggers
php artisan logify:triggers test
```

### Integration Commands (Still Available)
```bash
# Manual import (fallback)
php artisan logify:import --force

# Check integration status
php artisan logify:status

# Cleanup violations
php artisan logify:cleanup-violations --dry-run
```

## Database Schema

### Logify Tables (Source)
- **academics**: Student login/logout times, late/absent status
- **going_outs**: Student going-out login times, late status
- **student_details**: Student information
- **pnph_users**: User details

### ScholarSync Tables (Target)
- **logify_late_records**: Aggregated late records by student/month/year
- **logify_absent_records**: Aggregated absent records by student/month/year
- **violations**: Individual violation records
- **violation_types**: Violation type definitions

## Trigger Details

### Academics Table Triggers
- **tr_academics_after_insert**: Processes new academic records
- **tr_academics_after_update**: Processes updated academic records

### Going Outs Table Triggers
- **tr_going_outs_after_insert**: Processes new going-out records
- **tr_going_outs_after_update**: Processes updated going-out records

### Stored Procedures
- **SyncLateRecord**: Syncs late attendance data
- **SyncAbsentRecord**: Syncs absent attendance data
- **CreateLateViolations**: Creates violations for late incidents
- **CreateAbsentViolations**: Creates violations for absent incidents

## Configuration Options

### Behavior Settings
```php
// config/logify_triggers.php
'behavior' => [
    'auto_create_violations' => true,        // Auto-create violations
    'update_existing_records' => true,       // Update vs create new records
    'process_deleted_records' => false,      // Process deleted records
    'max_violations_per_month' => 50,        // Limit violations per student
],
```

### Logging Settings
```php
'logging' => [
    'log_trigger_executions' => true,        // Log trigger activity
    'log_level' => 'info',                  // Log level
    'log_detailed' => false,                // Detailed logging
],
```

### Performance Settings
```php
'performance' => [
    'use_batch_processing' => true,         // Batch processing
    'batch_size' => 100,                    // Batch size
    'use_transactions' => true,             // Use transactions
],
```

## Monitoring

### Health Check
```bash
# Check trigger health
php artisan logify:triggers status

# Get statistics
php artisan logify:status
```

### Performance Monitoring
The system tracks:
- Number of triggers installed and enabled
- Recent sync activity
- Violation creation rates
- Trigger efficiency metrics

### Logging
Trigger activities are logged with:
- Student ID and violation details
- Sync batch IDs for tracking
- Performance metrics
- Error handling

## Troubleshooting

### Common Issues

#### 1. Triggers Not Firing
```bash
# Check trigger status
php artisan logify:triggers status

# Verify procedures exist
php artisan logify:triggers test
```

#### 2. Duplicate Violations
```bash
# Cleanup duplicates
php artisan logify:cleanup-violations --force
```

#### 3. Database Connection Issues
```bash
# Test connection
php artisan logify:import --test
```

#### 4. Missing Violation Types
```bash
# Setup violation types
php artisan logify:setup-violation-types
```

### Debug Mode
Enable detailed logging:
```env
LOGIFY_LOG_TRIGGERS=true
LOGIFY_LOG_DETAILED=true
LOGIFY_LOG_LEVEL=debug
```

## Fallback Mechanism

If triggers fail, the system can fallback to the original scheduled import:

```env
LOGIFY_FALLBACK_TO_IMPORT=true
LOGIFY_HEALTH_CHECK_INTERVAL=60
LOGIFY_MAX_FAILURES=5
```

## Security Considerations

1. **Database Privileges**: Triggers require elevated database privileges
2. **Cross-Database Access**: Triggers access ScholarSync database from Logify
3. **Data Validation**: Input validation in stored procedures
4. **Error Handling**: Comprehensive error handling and logging

## Performance Impact

### Benefits
- **Real-time sync**: Immediate data synchronization
- **Reduced server load**: No scheduled batch processing
- **Better user experience**: Instant violation creation

### Considerations
- **Database load**: Triggers add overhead to INSERT/UPDATE operations
- **Cross-database calls**: Triggers make calls to ScholarSync database
- **Transaction management**: Proper transaction handling required

## Migration from Scheduled Import

### Before (Scheduled Import)
- Runs every 60 seconds
- Processes all records in batch
- Potential delays in violation creation
- Higher server resource usage

### After (Database Triggers)
- Real-time processing
- Processes only changed records
- Immediate violation creation
- Lower overall resource usage

### Migration Steps
1. Install triggers: `php artisan logify:triggers install`
2. Test triggers: `php artisan logify:triggers test`
3. Monitor for 24-48 hours
4. Disable scheduled import (optional)
5. Keep fallback enabled for safety

## Support

For issues or questions:
1. Check logs: `storage/logs/laravel.log`
2. Run diagnostics: `php artisan logify:triggers status`
3. Test functionality: `php artisan logify:triggers test`
4. Review configuration: `config/logify_triggers.php`


