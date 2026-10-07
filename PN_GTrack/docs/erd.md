# GTRACK Entity Relationship Diagram

This ERD reflects the GTRACK application tables created by the current Laravel migrations. `created_at` and `updated_at` are omitted from the diagram where they do not affect relationships.

```mermaid
erDiagram
    STUDENT_CLASSES {
        bigint id PK
        string name UK
        string description
    }

    STUDENTS {
        bigint id PK
        string student_id UK
        string name
        string email UK
        string gender
        string class FK_LOGICAL
        boolean status
        int battery_level
        string signal_status
        string last_update
        string contact
        string sos_status
        decimal latitude
        decimal longitude
        string profile_picture
    }

    LOCATIONS {
        bigint id PK
        bigint student_id FK
        decimal latitude
        decimal longitude
        timestamp recorded_at
        string sos_status
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
        string email UK
        string password
        string role
    }

    NOTIFICATIONS {
        bigint id PK
        bigint student_id FK
        bigint admin_id FK_LOGICAL
        bigint reply_to_id FK
        string class FK_LOGICAL
        string type
        string subject
        string sender_type
        string sender_name
        text message
        boolean read
        string status
        int battery_level
        string signal_status
        string location
        decimal latitude
        decimal longitude
        string media_url
        string video_url
        string audio_url
    }

    STUDENT_CLASSES ||--o{ STUDENTS : "groups by name"
    STUDENTS ||--o{ LOCATIONS : "has many"
    STUDENTS ||--o{ NOTIFICATIONS : "receives"
    NOTIFICATIONS o|--o{ NOTIFICATIONS : "replies to"
    STUDENT_AUTHS }o..|| STUDENTS : "authenticates by student_id"
    ADMINS }o..|| NOTIFICATIONS : "sends or manages"
    STUDENT_CLASSES ||..o{ NOTIFICATIONS : "class label"
```

## Relationship Notes

| Relationship | Join | Database constraint |
| --- | --- | --- |
| `student_classes` to `students` | `student_classes.name` = `students.class` | Logical/Eloquent only |
| `students` to `locations` | `locations.student_id` = `students.id` | Foreign key, `ON DELETE SET NULL` |
| `students` to `notifications` | `notifications.student_id` = `students.id` | Foreign key, `ON DELETE SET NULL` |
| `notifications` to `notifications` | `notifications.reply_to_id` = `notifications.id` | Self-referencing foreign key, `ON DELETE CASCADE` |
| `student_auths` to `students` | `student_auths.student_id` = `students.student_id` | Logical only; migration constraint is commented out |
| `admins` to `notifications` | `notifications.admin_id` = `admins.id` | Logical only; current migration adds an index but no foreign key |
| `student_classes` to `notifications` | `student_classes.name` = `notifications.class` | Logical label only; no foreign key |

`FK_LOGICAL` and dotted Mermaid relationships identify associations used by the application but not enforced by a database foreign key.

## Laravel Infrastructure Tables

The default Laravel tables also exist in the project: `users`, `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, and `migrations`. They are framework support tables and are excluded from the domain ERD above because they do not participate in the GTrack student/location/notification relationships.

## Source of Truth

The diagram is based on the migrations in `database/migrations`, especially the student, location, notification, authentication, admin, and student class migrations. The SQL schema dump contains additional legacy tables that are not included because they are not created by the current migration set shown above.

## Generated Account IDs

Student IDs are generated when an account is created in the compact format `STU{CLASS}{NUMBER}` (for example, `STU2026009`), where the class portion is derived from the selected class and the zero-padded number is based on the student's unique database record ID. Admin staff IDs use the role prefix followed directly by the zero-padded unique record ID (for example, `EDU009` for Education). Database unique constraints remain in place for both identifiers.
