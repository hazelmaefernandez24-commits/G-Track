# GTRACK Proposed Entity Relationship Diagram

This is the target design for GTrack's separate `gtrack_system` database. Its dedicated migration lives under `database/migrations/gtrack_system`; the existing migrations and the application's default database connection remain unchanged. The application code still uses the existing schema and must be migrated separately before it can run against this design.

The design separates emergency alerts, conversations, and broadcasts so records do not need unrelated columns. Battery level and signal status are intentionally excluded from the entire target schema, including student and emergency-alert records.

```mermaid
erDiagram
    STUDENT_CLASSES {
        bigint id PK
        int year UK
    }

    STUDENTS {
        bigint id PK
        string student_id UK
        string first_name
        string middle_name
        string last_name
        string email UK
        string gender
        bigint student_class_id FK
        string contact
        string profile_picture
    }

    STUDENT_STATUSES {
        bigint id PK
        bigint student_id FK
        string status
        string sos_status
        timestamp recorded_at
    }

    LOCATIONS {
        bigint id PK
        bigint student_id FK
        decimal latitude
        decimal longitude
        timestamp recorded_at
    }

    STUDENT_AUTHS {
        bigint id PK
        string student_id UK_LOGICAL
        string email UK
        string password
    }

    ADMINS {
        bigint id PK
        string staff_id UK
        string first_name
        string middle_name
        string last_name
        string email UK
        string password
        string role
    }

    EMERGENCY_ALERTS {
        bigint id PK
        bigint student_id FK
        bigint location_id FK
        bigint assigned_admin_id FK
        string type
        text description
        string status
        timestamp read_at
        bigint acknowledged_by_admin_id FK
        timestamp acknowledged_at
        bigint resolved_by_admin_id FK
        timestamp resolved_at
    }

    ALERT_MEDIA {
        bigint id PK
        bigint emergency_alert_id FK
        string file_path
        string mime_type
    }

    CONVERSATIONS {
        bigint id PK
        bigint student_id FK
        bigint admin_id FK
        string subject
    }

    MESSAGES {
        bigint id PK
        bigint conversation_id FK
        string sender_type
        bigint sender_id
        text message
        timestamp read_at
        bigint reply_to_id FK
    }

    BROADCASTS {
        bigint id PK
        bigint admin_id FK
        string subject
        text message
    }

    BROADCAST_RECIPIENTS {
        bigint id PK
        bigint broadcast_id FK
        bigint student_id FK
        timestamp read_at
    }

    STUDENT_CLASSES ||--o{ STUDENTS : "groups by year"
    STUDENTS ||--o{ STUDENT_STATUSES : "has status history"
    STUDENTS ||--o{ LOCATIONS : "has many"
    STUDENTS ||--o{ EMERGENCY_ALERTS : "triggers"
    LOCATIONS o|--o{ EMERGENCY_ALERTS : "records incident location"
    ADMINS o|--o{ EMERGENCY_ALERTS : "is assigned"
    ADMINS o|--o{ EMERGENCY_ALERTS : "acknowledges or resolves"
    EMERGENCY_ALERTS ||--o{ ALERT_MEDIA : "has media"
    STUDENTS ||--o{ CONVERSATIONS : "participates in"
    ADMINS o|--o{ CONVERSATIONS : "handles"
    CONVERSATIONS ||--o{ MESSAGES : "contains"
    MESSAGES o|--o{ MESSAGES : "is replied to by"
    ADMINS ||--o{ BROADCASTS : "creates"
    BROADCASTS ||--o{ BROADCAST_RECIPIENTS : "is delivered to"
    STUDENTS ||--o{ BROADCAST_RECIPIENTS : "receives"
    STUDENT_AUTHS }o..|| STUDENTS : "authenticates by student_id"
```

## Design Notes

| Area | Proposed structure |
| --- | --- |
| Emergency alerts | Store SOS/blackout data, its description, and lifecycle in `emergency_alerts`. The description accompanies the SOS video, whose file reference is stored in `alert_media`. Its nullable `location_id` references the captured incident location in `locations`, avoiding duplicate coordinates or a separate location string. |
| Conversations | Store the student/admin thread in `conversations`; individual text-only messages and replies go in `messages`. Messaging does not support file attachments. |
| Broadcasts | Store the broadcast content once in `broadcasts`. `broadcast_recipients` stores the audience and each student's `read_at` timestamp. |
| Battery and signal | Removed from the proposed schema entirely, including student/device monitoring and emergency alert records. |
| Student location | Store coordinates only in `locations`; obtain a student's current location from their latest location record instead of duplicating latitude and longitude in `students`. |
| Student status | Store online/offline and SOS status changes in `student_statuses` with their `recorded_at` timestamp. The latest status record represents the student's current status. |

Tables that store editable records also include Laravel's standard `created_at` and `updated_at` timestamps; the ERD omits those implementation columns. `locations` and `student_statuses` use `recorded_at` for their historical events.

`sender_type` and `sender_id` in `messages` identify whether a message was sent by a student, admin, or system. This is a polymorphic association rather than a direct foreign key; the implementation should validate that the sender exists for student/admin message types.

`students.student_class_id` references `student_classes.id`. `student_auths.student_id` to `students.student_id` is shown as a logical relationship in the current design; the target implementation should decide whether to convert it to a stable ID-based foreign key as part of the migration.

## Migration Considerations

To create only the new-design tables in the separate database, configure `DB_GTRACK_SYSTEM_DATABASE=gtrack_system` and run `php artisan migrate --database=gtrack_system --path=database/migrations/gtrack_system`. This records the migration in that database's Laravel migration table. Do not use `migrate:fresh`; that command drops tables.

This target schema changes table names and splits existing `notifications` rows. Before moving existing records, classify them as emergency alerts, messages, or broadcasts and copy them to their corresponding target tables. Broadcast recipient rows must be generated for the intended students, and prior broadcast read states may not be recoverable if they were stored only once on the original notification. The current application remains on its existing database until its models, controllers, and related views are migrated.

The current application also reads and writes battery/signal values in several places. Removing those fields from the database will require updating those endpoints, models, views, and related device-monitoring behavior along with the schema migration.

The default Laravel support tables (`users`, `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, and `migrations`) are excluded from this domain diagram.
