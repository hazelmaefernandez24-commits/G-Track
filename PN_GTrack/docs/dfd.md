# GTrack Data Flow Diagram

This DFD describes how data moves through the GTrack web dashboard, mobile/API endpoints, application services, and database. It is based on the routes in `routes/web.php` and `routes/api.php`.

## Context Diagram

```mermaid
flowchart LR
    STUDENT[Student mobile app]
    ADMIN[Admin user]
    GTRACK((GTrack system))
    DB[(GTrack database)]

    STUDENT -->|Login, heartbeat, location, SOS, messages, media| GTRACK
    GTRACK -->|Authentication result, status, alerts, messages| STUDENT
    ADMIN -->|Login, dashboard requests, management actions, replies| GTRACK
    GTRACK -->|Dashboard, tracking, notifications, reports| ADMIN
    GTRACK <-->|Read and write operational data| DB
```

## Level 1 DFD

```mermaid
flowchart TB
    STUDENT[Student mobile app]
    ADMIN[Admin user]

    P1((1. Authenticate users))
    P2((2. Manage student profiles and classes))
    P3((3. Capture device status and location))
    P4((4. Process SOS and notifications))
    P5((5. Serve dashboard and tracking views))

    D1[(D1 StudentAuths)]
    D2[(D2 Admins)]
    D3[(D3 Students)]
    D4[(D4 StudentClasses)]
    D5[(D5 Locations)]
    D6[(D6 Notifications)]

    STUDENT -->|student_id, email, password| P1
    ADMIN -->|admin credentials| P1
    P1 <-->|credential lookup and session result| D1
    P1 <-->|admin lookup and session result| D2
    P1 -->|login result| STUDENT
    P1 -->|login result| ADMIN

    ADMIN -->|create, update, delete students| P2
    ADMIN -->|create, update, delete classes| P2
    P2 <-->|student records| D3
    P2 <-->|class records| D4
    P2 -->|validation and operation result| ADMIN

    STUDENT -->|heartbeat, online/offline state| P3
    STUDENT -->|latitude, longitude, SOS state| P3
    P3 <-->|current status| D3
    P3 -->|location history| D5
    ADMIN -->|tracking/status request| P5
    P5 <-->|student status and location history| D3
    P5 <-->|location records| D5
    P5 -->|map, status, dashboard statistics| ADMIN

    STUDENT -->|SOS event, message, video/audio| P4
    ADMIN -->|broadcast, reply, acknowledge, resolve| P4
    P4 <-->|student context| D3
    P4 <-->|notification threads and statuses| D6
    P4 <-->|sender context| D2
    P4 -->|alerts and messages| STUDENT
    P4 -->|alerts, threads, counts| ADMIN

    ADMIN -->|notification and activity request| P5
    P5 <-->|notification data| D6
    P5 -->|activity, notification, and student views| ADMIN
```

## Process Descriptions

| Process | Responsibility | Main data flows |
| --- | --- | --- |
| `1. Authenticate users` | Verifies admin and student credentials and establishes access. | `StudentAuths`, `Admins`, login results |
| `2. Manage student profiles and classes` | Main admins maintain students, student authentication records, and class definitions. | `Students`, `StudentClasses`, profile operation results |
| `3. Capture device status and location` | Receives heartbeats, online/offline state, coordinates, and SOS status changes. | `Students`, `Locations`, tracking data |
| `4. Process SOS and notifications` | Creates alerts/messages, stores media references, handles broadcasts, replies, acknowledgement, and resolution. | `Notifications`, `Students`, `Admins`, alert/message responses |
| `5. Serve dashboard and tracking views` | Aggregates students, locations, and notifications for admin-facing screens and statistics. | Dashboard, tracking, activity, and notification views |

## Data Store Mapping

| DFD store | Database table | Purpose |
| --- | --- | --- |
| `D1 StudentAuths` | `student_auths` | Student login credentials |
| `D2 Admins` | `admins` | Admin accounts, roles, and staff identifiers |
| `D3 Students` | `students` | Student identity, class, device status, and SOS state |
| `D4 StudentClasses` | `student_classes` | Available class/batch definitions |
| `D5 Locations` | `locations` | Student coordinates and location history |
| `D6 Notifications` | `notifications` | SOS alerts, broadcasts, messages, replies, and statuses |

## Route Coverage

- Student API: `/api/student/login`, `/api/student/heartbeat`, `/api/student/sos`, `/api/location`, and `/api/notifications/send`
- Admin API: `/api/login`, `/api/admin/broadcast`, `/api/admin/message/send/{student_id}`, and `/api/admin/notification/resolve/{id}`
- Admin web dashboard: `/dashboard`, `/tracking`, `/activity`, and `/notifications`
- Main admin management: `/admin/students`, `/admin/classes`, and `/admin/admins`
