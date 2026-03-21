# Notification System for Student Violations

## Overview

The notification system has been implemented to automatically notify students when they receive new violations. This system provides real-time notifications through the web interface and ensures students are immediately aware of any disciplinary actions taken against them.

## Features

### 1. Automatic Notification Creation
- When a new violation is created for a student, a notification is automatically generated
- The notification includes the violation type, date, and relevant details
- Notifications are marked as "unread" by default

### 2. Student Code of Conduct Update Notifications
- When educators update the student manual, all students receive notifications
- Notifications are sent for:
  - Manual content updates
  - New violation types added
  - Violation categories added/deleted
  - Penalty rule changes (severity configurations)
- All students are notified simultaneously about policy changes

### 3. Real-time Notification Display
- Notification badge shows the count of unread notifications
- Badge is hidden when there are no unread notifications
- Notifications are loaded dynamically when the notification dropdown is opened

### 4. Notification Management
- Students can mark individual notifications as read by clicking on them
- "Mark all as read" functionality to clear all unread notifications at once
- Notifications are displayed with appropriate icons based on their type (warning, info, success, danger)

### 5. Time-based Display
- Notifications show relative time (e.g., "2 minutes ago", "1 hour ago", "3 days ago")
- Recent notifications are prioritized in the display

## Technical Implementation

### Components Created

1. **NotificationController** (`app/Http/Controllers/NotificationController.php`)
   - Handles API endpoints for notification management
   - Provides methods for fetching, marking as read, and counting notifications

2. **ViolationCreatedListener** (`app/Listeners/ViolationCreatedListener.php`)
   - Listens for ViolationCreated events
   - Automatically creates notifications when violations are added

3. **EventServiceProvider** (`app/Providers/EventServiceProvider.php`)
   - Registers the event-listener mapping
   - Ensures ViolationCreated events trigger notification creation

4. **ManualUpdated Event** (`app/Events/ManualUpdated.php`)
   - Fired when the student manual/code of conduct is updated
   - Supports different update types (manual_update, new_violation_type, category_change, severity_config_update)

5. **ManualUpdatedListener** (`app/Listeners/ManualUpdatedListener.php`)
   - Listens for ManualUpdated events
   - Creates notifications for all students when manual changes occur
   - Generates appropriate messages based on update type

### Database Integration

The system uses the existing `notifications` table with the following structure:
- `user_id`: Links to the student's user account
- `title`: Notification title (e.g., "New Violation Recorded")
- `message`: Detailed notification message
- `type`: Notification type (warning, info, success, danger)
- `is_read`: Boolean flag for read status
- `related_id`: Links to the violation ID for reference
- `created_at`/`updated_at`: Timestamps

### Event-Driven Architecture

#### Violation Notifications:
1. **Violation Creation**: When a violation is saved to the database
2. **Event Firing**: The Violation model fires a ViolationCreated event
3. **Listener Execution**: ViolationCreatedListener handles the event
4. **Notification Creation**: A new notification record is created for the student
5. **UI Update**: The notification appears in the student's notification dropdown

#### Manual Update Notifications:
1. **Manual Changes**: When educators update the student manual through various actions:
   - `EducatorController@updateManual` - General manual updates
   - `ViolationController@storeViolationType` - New violation types
   - `EducatorController@deleteOffenseCategory` - Category deletions
   - `EducatorController@deleteViolationType` - Violation type deletions
   - `EducatorController@updateSeverityMaxCounts` - Penalty rule changes
2. **Event Firing**: Controllers fire ManualUpdated events with specific update types
3. **Listener Execution**: ManualUpdatedListener handles the event
4. **Mass Notification**: Notifications are created for ALL students simultaneously
5. **UI Update**: All students see the manual update notifications

### API Endpoints

#### For Students:
- `GET /student/notifications` - Fetch notifications
- `POST /student/notifications/{id}/mark-read` - Mark specific notification as read
- `POST /student/notifications/mark-all-read` - Mark all notifications as read
- `GET /student/notifications/unread-count` - Get unread notification count

#### For Educators:
- `GET /educator/notifications` - Fetch notifications
- `POST /educator/notifications/{id}/mark-read` - Mark specific notification as read
- `POST /educator/notifications/mark-all-read` - Mark all notifications as read
- `GET /educator/notifications/unread-count` - Get unread notification count

### Frontend Integration

#### Student Layout (`resources/views/layouts/student.blade.php`)
- Dynamic notification badge with real-time count
- AJAX-powered notification dropdown
- Automatic loading when dropdown is opened
- Click handlers for marking notifications as read

#### Educator Layout (`resources/views/layouts/educator.blade.php`)
- Similar functionality to student layout
- Separate notification system for educators
- Real-time updates and management

## Usage

### For Students
1. When a violation is recorded, students will see a notification badge appear
2. Clicking the bell icon opens the notification dropdown
3. Notifications show violation details and timestamps
4. Clicking on a notification marks it as read
5. "Mark all as read" button clears all unread notifications

### For Educators
1. Educators can receive notifications about system events
2. Same interface and functionality as students
3. Separate notification stream from students

## Configuration

### Notification Types
- **warning**: Used for violations and penalty changes (yellow icon)
- **info**: General information and manual updates (blue icon)
- **success**: Positive notifications (green icon)
- **danger**: Critical alerts (red icon)

### Manual Update Notification Types
- **manual_update**: General student manual updates (info type)
- **new_violation_type**: New violation types added (warning type)
- **category_change**: Categories added/deleted (info type)
- **severity_config_update**: Penalty rules updated (warning type)

### Customization
To modify notification messages, edit the `ViolationCreatedListener` class:
```php
$title = 'New Violation Recorded';
$message = "A new violation has been recorded against you: {$violationName} on {$violationDate}. Please review your violation history and contact your educator if you have any questions.";
```

## Testing

### Testing Violation Notifications:
1. Create a new violation for a student through the educator interface
2. Log in as that student
3. Check that the notification badge appears with count "1"
4. Click the notification bell to see the violation notification
5. Click on the notification to mark it as read
6. Verify the badge count decreases

### Testing Manual Update Notifications:
1. **General Manual Updates**: Edit the student manual through the educator interface
2. **New Violation Types**: Add a new violation type through the educator interface
3. **Category Changes**: Delete a violation category or offense category
4. **Penalty Updates**: Modify severity configurations in the manual
5. **Verification**: Log in as any student and verify they received the appropriate notification
6. **Mass Notification**: Confirm all students receive the same manual update notification

## Future Enhancements

Potential improvements to consider:
1. Email notifications for critical violations
2. Push notifications for mobile devices
3. Notification preferences for students
4. Bulk notification management for educators
5. Notification templates for different violation types
6. Integration with external messaging systems
