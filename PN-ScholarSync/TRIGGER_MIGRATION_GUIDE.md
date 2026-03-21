# Trigger Migration Guide

## Overview
This guide explains what migrations need to be run for the Logify triggers to work properly.

## Required Tables in ScholarSync Database

The triggers insert violations into the ScholarSync database, so these tables must exist:

1. **violations** - Main violations table
2. **violation_types** - Types of violations
3. **offense_categories** - Categories of offenses (must have "Schedule" category)

## Required Tables in Logify Database

The triggers are created on these Logify database tables:

1. **academics** - Academic attendance records
2. **going_outs** - Going out records
3. **intern_log** - Internship attendance records
4. **going_home** - Going home records

## Migrations to Run (In Order)

### 1. First, run the main trigger migrations:

```powershell
# For academics and going_outs triggers
php artisan migrate --path=database/migrations/2025_10_07_112100_fix_absent_validation_triggers.php

# For intern_log and going_home triggers
php artisan migrate --path=database/migrations/2025_10_11_121700_add_intern_and_going_home_triggers.php
```

### 2. Fix the monitor_name column issue:

```powershell
# Add missing monitor_name columns to Logify tables
php artisan migrate --path=database/migrations/2025_10_30_020800_add_monitor_name_columns_to_logify.php
```

## What Each Trigger Does

### 1. tr_after_academic_validation (academics table)
- Triggers on: `AFTER UPDATE ON academics`
- Creates violations for:
  - Academic absence (when `time_out_consideration = 'Not Excused'` and both remarks are 'absent')
  - Late academic login (when `educator_consideration = 'Not Excused'` and `time_in_remark = 'late'`)
  - Late academic logout (when `time_out_consideration = 'Not Excused'` and `time_out_remark = 'late'`)

### 2. tr_after_going_out_validation (going_outs table)
- Triggers on: `AFTER UPDATE ON going_outs`
- Creates violations for:
  - Going out absence (when `time_out_consideration = 'Not Excused'` and both remarks are 'absent')
  - Late going out login (when `educator_consideration = 'Not Excused'` and `time_in_remark = 'late'`)
  - Late going out logout (when `time_out_consideration = 'Not Excused'` and `time_out_remark = 'late'`)

### 3. tr_after_intern_log_validation (intern_log table)
- Triggers on: `AFTER UPDATE ON intern_log`
- Creates violations for:
  - Intern absence (when `time_out_consideration = 'Not Excused'` and both remarks are 'absent')
  - Late intern logout (when `time_out_consideration = 'Not Excused'` and `time_out_remark = 'late'`)

### 4. tr_after_going_home_validation (going_home table)
- Triggers on: `AFTER UPDATE ON going_home`
- Creates violations for:
  - Late going home login (when `time_in_consideration = 'Not Excused'` and `time_in_remarks = 'late'`)
  - Late going home logout (when `time_out_consideration = 'Not Excused'` and `time_out_remarks = 'late'`)

## Columns Used by Triggers

### From Logify Tables (NEW/OLD):
- `time_out_consideration` - Consideration for time out
- `educator_consideration` - Educator's consideration
- `time_in_consideration` - Consideration for time in (going_home only)
- `time_in_remark`, `time_out_remark` - Remarks (late/absent)
- `time_in_reason`, `time_out_reason` - Reasons provided
- `student_id` - Student identifier
- `date` / `academic_date` / `going_out_date` - Date fields
- `time_in`, `time_out` - Time fields

### Inserted into ScholarSync:
- `violations` table with fields:
  - student_id, violation_type_id, severity, violation_date
  - penalty, consequence, incident_details, status
  - action_taken, consequence_status, incident_datetime
  - place_of_incident, prepared_by, offense_count
  - logify_sync_batch_id, created_at, updated_at

## Monitor Name Columns

The Logify system tries to save these columns when updating considerations:
- `time_in_monitor_name` - Name of educator who validated time in
- `time_out_monitor_name` - Name of educator who validated time out

These columns are added by the migration `2025_10_30_020800_add_monitor_name_columns_to_logify.php` to:
- academics
- going_outs
- intern_log
- going_home

**Note:** The triggers do NOT use these columns. They are only for the Logify system's own record-keeping.

## Verification

After running migrations, verify triggers are created:

```sql
-- Check triggers in Logify database
SHOW TRIGGERS;

-- Should see:
-- tr_after_academic_validation
-- tr_after_going_out_validation
-- tr_after_intern_log_validation
-- tr_after_going_home_validation
```

## Troubleshooting

### Error: "Column not found: time_in_monitor_name"
**Solution:** Run the monitor_name columns migration:
```powershell
php artisan migrate --path=database/migrations/2025_10_30_020800_add_monitor_name_columns_to_logify.php
```

### Error: "Column not found: time_in_consideration"
**Solution:** This column only exists in `going_home` table, not in `intern_log`. The migration has been fixed to remove invalid references.

### Error: "Table 'violations' doesn't exist"
**Solution:** Make sure ScholarSync database migrations are run first:
```powershell
php artisan migrate
```
