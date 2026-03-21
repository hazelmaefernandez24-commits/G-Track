# Task Violation Integration System

## Overview

This system automatically creates violation records in **PN-ScholarSync** when task reports are marked as **"Invalid"** in **G16_CAPSTONE**. It bridges the gap between the task management system and the violation tracking system.

## How It Works

### 1. **Data Flow**
```
G16_CAPSTONE (Task Reports) → PN-ScholarSync (Violations)
     ↓
Invalid Reports → Automatic Violation Creation
     ↓
Student Names Resolved → Proper Identification
```

### 2. **Integration Process**

#### Step 1: Invalid Report Detection
- System scans `G16_CAPSTONE.task_submissions` table
- Identifies reports with `status = 'invalid'`
- Extracts student information using `user_id`

#### Step 2: Student Identification
- Maps `user_id` to actual student names from `pnph_users` table
- Retrieves student details from `student_details` table
- Gets student ID codes (e.g., "2025010027C1")

#### Step 3: Violation Creation
- Creates violation record in PN-ScholarSync
- Sets appropriate violation type: "Non-compliance with [Task Category] task assignment"
- Assigns Low severity and Verbal Warning penalty
- Links back to original task submission

## Database Structure

### New Fields Added

#### `violations` table:
- `task_submission_id` - Links to G16_CAPSTONE task submission
- `g16_user_id` - Original user ID from G16_CAPSTONE
- `recorded_by` - Name of person who validated the task

#### `pnph_users` table:
- `g16_user_id` - Links to G16_CAPSTONE user
- `student_id` - Student identification code
- `batch` - Student batch year

## Configuration

### Database Connection
Add to `.env` file:
```env
# G16_CAPSTONE Database Connection
G16_DB_HOST=127.0.0.1
G16_DB_PORT=3306
G16_DB_DATABASE=g16_capstone
G16_DB_USERNAME=root
G16_DB_PASSWORD=
```

### Migration Commands
```bash
# Run migrations to add new fields
php artisan migrate

# Create basic violation system data (if needed)
php artisan db:seed --class=OffenseCategoriesSeeder
php artisan db:seed --class=SeveritiesSeeder
php artisan db:seed --class=ViolationsSeeder
```

## Usage

### 1. **Web Interface**
Access: `/educator/task-violation-integration`

Features:
- View invalid task submissions
- Preview what violations would be created
- Sync invalid reports to violations
- Monitor synced violations

### 2. **Command Line**
```bash
# Dry run (preview only)
php artisan sync:task-violations --dry-run

# Actual sync
php artisan sync:task-violations

# Force sync (ignore warnings)
php artisan sync:task-violations --force
```

### 3. **Programmatic Usage**
```php
$integrationService = new TaskViolationIntegrationService();

// Get invalid submissions
$invalidSubmissions = $integrationService->getInvalidSubmissionsWithStudentNames();

// Sync to violations
$result = $integrationService->syncInvalidTaskSubmissions();
```

## Example Data Flow

### Input (G16_CAPSTONE):
```
task_submissions table:
- id: 123
- user_id: 30
- task_category: "kitchen"
- status: "invalid"
- description: "Student did not complete assigned kitchen tasks properly"
- validated_at: "2025-09-28 10:30:00"
```

### Student Resolution:
```
pnph_users table:
- user_id: 30
- user_fname: "Angelo"
- user_lname: "Parrocho"

student_details table:
- user_id: 30
- student_id: "2025010027C1"
- batch: "2025"
```

### Output (PN-ScholarSync):
```
violations table:
- student_id: "2025010027C1"
- violation_type_id: [Center Tasking violation type]
- severity: "Low"
- penalty: "VW"
- consequence: "Student must complete additional Kitchen tasks..."
- task_submission_id: 123
- g16_user_id: "30"
- status: "active"
```

## Violation Details Created

### Violation Type
- **Category**: "Center Tasking"
- **Name**: "Non-compliance with [Task Category] task assignment"
- **Description**: "Student failed to properly complete assigned [Task Category] tasks"

### Penalty Structure
- **Severity**: Low (task non-compliance is typically minor)
- **Penalty**: VW (Verbal Warning)
- **Consequence**: "Student must complete additional [Task Category] tasks and demonstrate proper task completion procedures."

### Additional Information
- **Incident Details**: Original task submission description
- **Prepared By**: Name of admin who marked task as invalid
- **Link**: Maintains connection to original task submission

## Monitoring and Maintenance

### Check Integration Status
```bash
# Test the integration
php test_integration.php

# View integration dashboard
Visit: /educator/task-violation-integration
```

### Troubleshooting

#### Common Issues:
1. **Database Connection Failed**
   - Check G16_CAPSTONE database credentials
   - Ensure both databases are accessible

2. **No Invalid Submissions Found**
   - Verify task reports are being marked as "invalid" in G16_CAPSTONE
   - Check task_submissions table status values

3. **Student Names Not Resolved**
   - Verify pnph_users table has correct student information
   - Check user_id mapping between systems

4. **Violations Not Appearing**
   - Ensure violation system is set up (categories, severities, types)
   - Check if violations table has proper relationships

### Logs
- Integration activities are logged in Laravel logs
- Check `storage/logs/laravel.log` for detailed information

## Security Considerations

- Only educators and inspectors can access integration features
- All database connections use proper authentication
- Original task submission data is preserved and linked
- Student privacy is maintained through proper access controls

## Future Enhancements

1. **Real-time Sync**: Automatic sync when reports are marked invalid
2. **Notification System**: Alert educators when new violations are created
3. **Bulk Operations**: Mass sync and management capabilities
4. **Reporting**: Analytics on task compliance and violations
5. **Appeal Integration**: Link violation appeals back to task system

## API Endpoints

- `GET /educator/task-violation-integration` - Integration dashboard
- `POST /educator/task-violation-integration/sync` - Sync invalid reports
- `GET /educator/task-violation-integration/preview` - Preview sync results
- `GET /educator/task-violation-integration/invalid-submissions` - Get invalid submissions data

---

**Note**: This integration ensures that task non-compliance is properly tracked in the violation system while maintaining student identification accuracy and system data integrity.
