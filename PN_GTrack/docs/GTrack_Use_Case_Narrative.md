# GTrack Web System — Use Case Narrative

**Project Name:** GTrack (GPS Student Tracking & Monitoring System)  
**Document Version:** 1.0  
**Date:** October 8, 2026  

---

## Table of Contents

1. [UC-01: Authenticate (Student)](#uc-01-authenticate-student)
2. [UC-02: Authenticate (Admin)](#uc-02-authenticate-admin)
3. [UC-03: Manage Own Profile](#uc-03-manage-own-profile)
4. [UC-04: Send Heartbeat and Device Status](#uc-04-send-heartbeat-and-device-status)
5. [UC-05: Share Current Location](#uc-05-share-current-location)
6. [UC-06: Send or Cancel Alert](#uc-06-send-or-cancel-alert)
7. [UC-07: Upload SOS Media](#uc-07-upload-sos-media)
8. [UC-08: View Dashboard](#uc-08-view-dashboard)
9. [UC-09: View Live Tracking](#uc-09-view-live-tracking)
10. [UC-10: View Location History](#uc-10-view-location-history)
11. [UC-11: View Notification and Activity](#uc-11-view-notification-and-activity)
12. [UC-12: Send and Receive Student Messages](#uc-12-send-and-receive-student-messages)
13. [UC-13: Send Broadcast](#uc-13-send-broadcast)

---

## Actors

| Actor | Description |
|-------|-------------|
| **Student** | A registered student who uses the GTrack mobile application to share location, send alerts, manage their profile, and communicate with administrators. |
| **Admin (Education)** | An education staff member who logs into the GTrack web or mobile platform to monitor students, respond to SOS alerts, manage messages, and view live tracking data. |
| **Main Admin** | A senior administrator with elevated privileges who can manage all system entities (students, classes, admins), send broadcasts, view all notifications, and perform all Admin (Education) functions. |
| **Database** | The MySQL/MariaDB database that persists all system records including student data, admin accounts, locations, and notifications. |
| **Storage** | The Laravel file storage system (public disk) used to store uploaded media such as SOS video recordings and student profile pictures. |
| **Migration** | The Laravel migration system that manages database schema versioning and structural changes. |

---

## UC-01: Authenticate (Student)

| Field | Detail |
|-------|--------|
| **Use Case ID** | UC-01 |
| **Use Case Name** | Authenticate (Student) |
| **Primary Actor** | Student |
| **Description** | The student authenticates through the GTrack mobile application using their student ID and password to gain access to the system's features such as location sharing and SOS alerts. |
| **Trigger** | The student opens the GTrack mobile application and navigates to the login screen. |
| **Preconditions** | 1. The student must have a registered account in the system (created by the Main Admin). 2. The student's credentials must exist in the `student_auths` table. 3. The mobile application is installed and has network connectivity. |

### Main Success Scenario (Basic Flow)

| Step | Actor | System Response |
|------|-------|-----------------|
| 1 | Student enters their Student ID and password on the mobile login screen. | — |
| 2 | — | The system validates the student ID against the `student_auths` table. |
| 3 | — | The system verifies the password using a hashed comparison. |
| 4 | — | The system marks the student as **online** (`status = true`) and updates `last_update` with the current timestamp. |
| 5 | — | The system returns a success response containing the student's profile data and role. |
| 6 | Student is redirected to the mobile application home screen. | — |

### Alternative Flows

| Condition | Flow |
|-----------|------|
| **A1 – Invalid Credentials** | At Step 3, if the student ID does not exist or the password is incorrect, the system returns a 401 error with the message *"Invalid student ID or password."* The student remains on the login screen. |
| **A2 – Student Record Missing** | At Step 4, if the `student_auths` record exists but no corresponding `students` record is found, the system logs the student in but cannot update online status. The student can still use limited features. |

### Postconditions

- **Success:** The student is authenticated, marked as online, and can access all mobile features.
- **Failure:** The student is not authenticated and remains on the login screen with an error message displayed.

### Related Use Cases

- **UC-03:** Manage Own Profile — `<<updates>>` Authenticate (Student), since managing the profile modifies the authenticated student's data.

---

## UC-02: Authenticate (Admin)

| Field | Detail |
|-------|--------|
| **Use Case ID** | UC-02 |
| **Use Case Name** | Authenticate (Admin) |
| **Primary Actor** | Admin (Education), Main Admin |
| **Description** | An administrator authenticates through the GTrack web application or mobile app using their Staff ID and password to access the monitoring dashboard and management features. |
| **Trigger** | The admin navigates to the GTrack web login page (`/login`) or opens the mobile admin login screen. |
| **Preconditions** | 1. The admin must have a registered account in the `admins` table (created by the Main Admin). 2. The admin's Staff ID and password must be valid. |

### Main Success Scenario (Basic Flow)

| Step | Actor | System Response |
|------|-------|-----------------|
| 1 | Admin enters their Staff ID and password on the login form. | — |
| 2 | — | The system authenticates using the `admin` guard with the provided credentials. |
| 3 | — | The system creates a session (web) or returns user data (API). |
| 4 | Admin is redirected to the Dashboard (`/dashboard`). | — |

### Alternative Flows

| Condition | Flow |
|-----------|------|
| **A1 – Invalid Credentials** | At Step 2, if the Staff ID or password is incorrect, the system returns an error *"Invalid Staff ID or password."* The admin remains on the login page. |
| **A2 – Password Reset** | Before Step 1, the admin selects "Forgot Password." The system presents a reset form requiring Staff ID, email, and new password. If the Staff ID and email match an existing account, the password is updated. |

### Postconditions

- **Success:** The admin is authenticated and can access all dashboard features based on their role (`education` or `main`).
- **Failure:** The admin remains on the login page with an error message.

### Related Use Cases

- **UC-08:** View Dashboard — The dashboard is the first page displayed after successful authentication.

---

## UC-03: Manage Own Profile

| Field | Detail |
|-------|--------|
| **Use Case ID** | UC-03 |
| **Use Case Name** | Manage Own Profile |
| **Primary Actor** | Student |
| **Supporting Actor** | Admin (Education) — for admin profile updates |
| **Description** | The student updates their own profile information, including uploading a profile picture through the mobile application. Education admins can also update their own profile (name, email) and change their password through the web interface. |
| **Trigger** | The student navigates to the profile section in the mobile app, or the admin navigates to their account settings. |
| **Preconditions** | The user must be authenticated. |

### Main Success Scenario — Student Profile Picture Upload

| Step | Actor | System Response |
|------|-------|-----------------|
| 1 | Student selects a profile picture from their device (JPG, JPEG, PNG, or WebP; max 5 MB). | — |
| 2 | — | The system validates the uploaded file format and size. |
| 3 | — | If a previous profile picture exists, the system deletes the old file from storage. |
| 4 | — | The system stores the new image in `storage/profile_pictures/` on the public disk. |
| 5 | — | The system updates the student's `profile_picture` field with the relative file path. |
| 6 | — | The system returns a success response with the new profile picture URL. |

### Main Success Scenario — Admin Profile Update

| Step | Actor | System Response |
|------|-------|-----------------|
| 1 | Admin (Education) enters updated first name, middle initial, last name, and/or email. | — |
| 2 | — | The system validates the input (name fields accept letters only; email must be unique). |
| 3 | — | The system updates the admin record in the database. |
| 4 | — | The system returns a success message: *"Your profile has been updated successfully."* |

### Alternative Flows

| Condition | Flow |
|-----------|------|
| **A1 – Invalid File Type** | At Step 2 (Student), the system rejects files that are not JPG/JPEG/PNG/WebP and returns a validation error. |
| **A2 – File Too Large** | At Step 2 (Student), files exceeding 5 MB are rejected with a validation error. |
| **A3 – Password Change (Admin)** | The admin can change their password by providing the current password and a new password (minimum 6 characters, must be confirmed). If the current password is incorrect, the system returns an error. |

### Postconditions

- The user's profile is updated in the database, and the changes are reflected across the system.

### Relationship

- `<<updates>>` **UC-01: Authenticate (Student)** — Profile updates modify the authenticated student's data stored during login.

---

## UC-04: Send Heartbeat and Device Status

| Field | Detail |
|-------|--------|
| **Use Case ID** | UC-04 |
| **Use Case Name** | Send Heartbeat and Device Status |
| **Primary Actor** | Student (via mobile application — automated) |
| **Description** | The mobile application automatically sends periodic heartbeat signals (approximately every 30 seconds) to keep the student marked as online and to transmit real-time device telemetry (battery level, signal strength) to the server. |
| **Trigger** | Automatic timer within the mobile application fires every ~30 seconds while the app is running. |
| **Preconditions** | 1. The student is authenticated and the mobile app is running. 2. The device has network connectivity. |

### Main Success Scenario (Basic Flow)

| Step | Actor | System Response |
|------|-------|-----------------|
| 1 | The mobile app automatically sends a heartbeat request containing: `student_id`, `battery_level`, and `signal` status. | — |
| 2 | — | The system locates the student record by `student_id`. |
| 3 | — | The system updates the student's status to **online** (`status = true`). |
| 4 | — | The system updates `last_update` to the current timestamp. |
| 5 | — | The system updates `battery_level` and `signal_status` if provided. |
| 6 | — | The system touches the `updated_at` timestamp (even if data is unchanged) to prevent premature offline marking. |
| 7 | — | The system returns a confirmation: *"Heartbeat received."* |

### Alternative Flows

| Condition | Flow |
|-----------|------|
| **A1 – Student Not Found** | At Step 2, if no student record matches the provided ID, the system returns a 404 error. |
| **A2 – Heartbeat Timeout** | If no heartbeat is received for 15 minutes, the dashboard auto-marks the student as **offline** (`status = false`) during the next stats poll. |
| **A3 – App Closed / Logout** | When the student logs out or closes the app, a `goOffline` request is sent, explicitly setting `status = false`. |

### Postconditions

- The student's online status and device telemetry are current in the database.
- The admin dashboard reflects real-time online/offline counts.

### Relationship

- `<<updates>>` **UC-09: View Live Tracking** — Heartbeat data keeps the live tracking map and student status up to date.

---

## UC-05: Share Current Location

| Field | Detail |
|-------|--------|
| **Use Case ID** | UC-05 |
| **Use Case Name** | Share Current Location |
| **Primary Actor** | Student (via mobile application — automated) |
| **Description** | The mobile application periodically sends the student's GPS coordinates (latitude and longitude) to the server, creating a historical location log and updating the student's current position on the live tracking map. |
| **Trigger** | The mobile app's location service transmits GPS coordinates at regular intervals while the app is active. |
| **Preconditions** | 1. The student is authenticated and the mobile app is running. 2. GPS/location services are enabled on the device. 3. The device has network connectivity. |

### Main Success Scenario (Basic Flow)

| Step | Actor | System Response |
|------|-------|-----------------|
| 1 | The mobile app sends a location update containing: `student_id`, `latitude`, `longitude`, and optionally `battery_level` and `sos_status`. | — |
| 2 | — | The system validates the coordinates (latitude: -90 to 90; longitude: -180 to 180). |
| 3 | — | The system locates the student by `student_id` or `id`. |
| 4 | — | The system creates a new record in the `locations` table with the student ID, coordinates, and `recorded_at` timestamp. |
| 5 | — | The system marks the student as **online** and updates `last_update`. |
| 6 | — | The system updates battery level if provided. |
| 7 | — | The system returns a success response with the created location record. |

### Alternative Flows

| Condition | Flow |
|-----------|------|
| **A1 – Student Not Found** | At Step 3, the system returns a 404 error. |
| **A2 – Invalid Coordinates** | At Step 2, coordinates outside valid ranges are rejected with a validation error. |
| **A3 – SOS Status Change via Location Update** | If the location update includes `sos_status = 'safe'` and the student was previously in `'help'` status, the system automatically resolves all active SOS notifications for that student. |

### Postconditions

- A new location record is persisted in the `locations` table.
- The student's position on the live tracking map is updated.
- The location history for the student grows by one entry.

### Relationship

- `<<creates>>` **UC-10: View Location History** — Each shared location creates a new history entry that can be viewed by admins.

---

## UC-06: Send or Cancel Alert

| Field | Detail |
|-------|--------|
| **Use Case ID** | UC-06 |
| **Use Case Name** | Send or Cancel Alert (SOS) |
| **Primary Actor** | Student |
| **Supporting Actor** | Admin (Education), Main Admin |
| **Description** | The student triggers an SOS emergency alert from the mobile application when in danger, or cancels an active SOS alert when safe. The alert notifies administrators and updates the student's SOS status on the live tracking map. |
| **Trigger** | The student presses the SOS button on the mobile app to send an alert, or presses the Cancel/Safe button to cancel. |
| **Preconditions** | 1. The student is authenticated. 2. The mobile application is running. |

### Main Success Scenario — Send SOS Alert

| Step | Actor | System Response |
|------|-------|-----------------|
| 1 | Student presses the SOS button on the mobile app. | — |
| 2 | The mobile app collects current GPS coordinates, battery level, and signal strength. | — |
| 3 | The mobile app begins recording a video feed. | — |
| 4 | — | The system receives the SOS request and validates the data (`sos_status = 'help'`). |
| 5 | — | The system updates the student's `sos_status` to `'help'` along with coordinates, battery, and signal data. |
| 6 | — | The system updates `last_update` to the current timestamp. |
| 7 | — | The system returns a confirmation: *"SOS status updated."* |
| 8 | — | The admin dashboard and live tracking map immediately reflect the SOS status with a visual alert indicator. |

### Main Success Scenario — Cancel SOS Alert

| Step | Actor | System Response |
|------|-------|-----------------|
| 1 | Student presses the "I'm Safe" / Cancel button. | — |
| 2 | — | The system receives the cancellation request (`sos_status = 'safe'`). |
| 3 | — | The system updates the student's `sos_status` to `'safe'`. |
| 4 | — | The system resolves all open SOS notifications for that student (setting `status = 'resolved'` and `read = true`). |
| 5 | — | The SOS alert indicator is removed from the admin dashboard and tracking map. |

### Alternative Flows

| Condition | Flow |
|-----------|------|
| **A1 – Student Not Found** | The system returns a 404 error. |
| **A2 – Admin Resolves Alert** | An admin can manually resolve an SOS alert through the notifications panel, which sets the student's `sos_status` back to `'safe'`. |
| **A3 – Blackout Alert (Auto-detected)** | If the SOS message contains keywords like *"low on battery"* or *"battery is low"*, the system automatically categorizes it as a **Blackout** alert instead of an SOS alert. |

### Postconditions

- **Send:** The student's SOS status is `'help'` and admins are alerted in real time.
- **Cancel:** The student's SOS status is `'safe'` and all related notifications are resolved.

### Relationship

- `<<appears in>>` **UC-11: View Notification and Activity** — SOS and Blackout alerts appear in the notification center for admin review.

---

## UC-07: Upload SOS Media

| Field | Detail |
|-------|--------|
| **Use Case ID** | UC-07 |
| **Use Case Name** | Upload SOS Media |
| **Primary Actor** | Student (via mobile application — automated) |
| **Description** | When an SOS alert is triggered, the mobile application records and uploads a video feed (and optionally other media) to the server. This media is attached to the SOS notification for admin review. An SOS notification is only considered complete and visible to admins when a video has been successfully uploaded. |
| **Trigger** | The student triggers an SOS alert, and the mobile app begins recording video. |
| **Preconditions** | 1. The student has triggered an SOS alert (UC-06). 2. The device camera is accessible. 3. The device has sufficient network bandwidth to upload. |

### Main Success Scenario (Basic Flow)

| Step | Actor | System Response |
|------|-------|-----------------|
| 1 | The mobile app records a short video during the SOS event. | — |
| 2 | The mobile app sends the video file to `POST /api/upload-video` or `POST /api/notifications/send` with `target = 'sos'`. | — |
| 3 | — | The system validates the uploaded file (MP4/MOV/AVI, max 25 MB). |
| 4 | — | The system stores the video in `storage/recordings/videos/` on the public disk. |
| 5 | — | If an existing unresolved SOS notification exists for the student, the system updates it with the new video URL. Otherwise, a new SOS notification is created. |
| 6 | — | The system updates the student's real-time status (coordinates, battery, signal, `sos_status = 'help'`). |
| 7 | — | The system returns a success response with the notification ID. |

### Alternative Flows

| Condition | Flow |
|-----------|------|
| **A1 – No Video Attached** | If `target = 'sos'` but no video file is included, the system returns a 422 error: *"SOS alerts require a video feed before they are accepted."* |
| **A2 – Upload Failure** | If the file upload fails (storage error), the system returns a 500 error and logs the failure. |
| **A3 – Media Upload (Non-SOS)** | For `student_message` type notifications, media files (images, videos) up to 25 MB can also be attached. |

### Postconditions

- The video/media file is stored in the server's file storage.
- The SOS notification is created or updated with the media URL.
- Admins can view the video from the notification panel.

### Relationship

- `<<appears in>>` **Storage** — Uploaded SOS media files are persisted in the Laravel public storage disk.

---

## UC-08: View Dashboard

| Field | Detail |
|-------|--------|
| **Use Case ID** | UC-08 |
| **Use Case Name** | View Dashboard |
| **Primary Actor** | Admin (Education), Main Admin |
| **Description** | After authentication, the admin views the main dashboard which provides a real-time summary of the system's status including online/offline student counts, active SOS alerts, broadcast counts, and the latest system update time. The dashboard auto-refreshes statistics every 10 seconds. |
| **Trigger** | The admin logs in and is redirected to `/dashboard`, or manually navigates to the dashboard page. |
| **Preconditions** | The admin must be authenticated via the `admin` guard. |

### Main Success Scenario (Basic Flow)

| Step | Actor | System Response |
|------|-------|-----------------|
| 1 | Admin navigates to the Dashboard. | — |
| 2 | — | The system auto-marks students as **offline** if no heartbeat has been received in the last 15 minutes. |
| 3 | — | The system fetches all students with their latest location data. |
| 4 | — | The system computes summary statistics: online count, offline count, active SOS count, unread broadcast count, latest update time. |
| 5 | — | The system renders the dashboard view with student list and summary cards. |
| 6 | — | The dashboard JavaScript begins polling `/api/dashboard/stats` every 10 seconds to refresh counts in real time. |

### Alternative Flows

| Condition | Flow |
|-----------|------|
| **A1 – No Students Registered** | The dashboard displays zero counts and an empty student list. |
| **A2 – Active SOS Alert** | If there are active SOS alerts, the dashboard prominently highlights the SOS count badge to draw attention. |

### Postconditions

- The admin sees up-to-date system statistics and student statuses.
- The dashboard continues to poll for updates in the background.

---

## UC-09: View Live Tracking

| Field | Detail |
|-------|--------|
| **Use Case ID** | UC-09 |
| **Use Case Name** | View Live Tracking |
| **Primary Actor** | Admin (Education), Main Admin |
| **Description** | The admin views a real-time interactive map showing the current GPS positions of all students. Students are shown as markers on the map with indicators for online/offline status, SOS status, and gender. The map supports filtering by class. |
| **Trigger** | The admin clicks on the "Tracking" navigation item or navigates to `/tracking`. |
| **Preconditions** | 1. The admin must be authenticated. 2. Students must be sharing their location (UC-05). |

### Main Success Scenario (Basic Flow)

| Step | Actor | System Response |
|------|-------|-----------------|
| 1 | Admin navigates to the Live Tracking page. | — |
| 2 | — | The system loads the tracking view with an interactive map. |
| 3 | — | The system fetches all student locations from `GET /api/location/all`. |
| 4 | — | The system renders student markers on the map using their latest GPS coordinates. |
| 5 | — | Markers display color-coded indicators: online (green), offline (grey), SOS active (red/pulsing). |
| 6 | Admin can click a marker to view student details (name, class, battery, signal, SOS status). | — |
| 7 | — | The map auto-refreshes location data periodically. |

### Alternative Flows

| Condition | Flow |
|-----------|------|
| **A1 – Filter by Class** | The admin selects a specific class from the filter dropdown. The system re-fetches locations for only students in that class and updates the map. |
| **A2 – No Location Data** | Students who have never shared their location do not appear on the map. |
| **A3 – SOS Active** | Students with `sos_status = 'help'` are shown with a red alert marker, making them immediately identifiable. |

### Postconditions

- The admin can see the real-time geographic positions of monitored students.

---

## UC-10: View Location History

| Field | Detail |
|-------|--------|
| **Use Case ID** | UC-10 |
| **Use Case Name** | View Location History |
| **Primary Actor** | Admin (Education), Main Admin |
| **Description** | The admin views the complete location history of a specific student, including all previously recorded GPS coordinates with timestamps and SOS status at each point. The history is displayed on a map and in a tabular list. |
| **Trigger** | The admin clicks on a student's name or a "View History" link from the dashboard or tracking page. |
| **Preconditions** | 1. The admin must be authenticated. 2. The student must have at least one recorded location. |

### Main Success Scenario (Basic Flow)

| Step | Actor | System Response |
|------|-------|-----------------|
| 1 | Admin selects a student and navigates to `/students/{id}/history`. | — |
| 2 | — | The system retrieves the student's profile data. |
| 3 | — | The system queries all entries from the `locations` table for that student, ordered by `recorded_at` descending. |
| 4 | — | The system renders the history view showing: student details, a map with historical markers, and a sortable table listing each location record. |
| 5 | Admin can browse through the location timeline and click on individual entries to see details. | — |

### Alternative Flows

| Condition | Flow |
|-----------|------|
| **A1 – No History** | If the student has no recorded locations, the system displays an empty state message. |
| **A2 – SOS Locations** | Location entries recorded during an active SOS event are visually distinguished (e.g., red markers). |

### Postconditions

- The admin has reviewed the student's historical movement data.

---

## UC-11: View Notification and Activity

| Field | Detail |
|-------|--------|
| **Use Case ID** | UC-11 |
| **Use Case Name** | View Notification and Activity |
| **Primary Actor** | Admin (Education), Main Admin |
| **Description** | The admin views and manages all system notifications organized into tabs: **SOS/Blackout Alerts**, **Student Messages**, and **Broadcasts**. Admins can acknowledge, resolve, and delete notifications. The system tracks read/unread status and provides filtering by class. |
| **Trigger** | The admin navigates to `/notifications` or clicks the notification bell icon. |
| **Preconditions** | The admin must be authenticated. |

### Main Success Scenario (Basic Flow)

| Step | Actor | System Response |
|------|-------|-----------------|
| 1 | Admin navigates to the Notifications page. | — |
| 2 | — | The system loads notifications for the current tab (default: Student Messages). |
| 3 | — | For **SOS/Blackout** tab: The system displays alerts sorted by newest first with status indicators (pending, acknowledged, resolved). Only SOS alerts with a valid video are shown. |
| 4 | — | For **Student Messages** tab: The system groups messages by student in a Messenger-style layout. Education admins only see conversations assigned to them. |
| 5 | — | For **Broadcast** tab: The system displays all broadcast messages. |
| 6 | — | The system computes stats: unread count, active SOS count, broadcast count, online/offline counts. |

### Sub-Flows

| Action | Description |
|--------|-------------|
| **Acknowledge Alert** | Admin clicks "Acknowledge" on an SOS/Blackout alert. The system records the admin's name and timestamp, and marks it as read. |
| **Resolve Alert** | Admin clicks "Resolve" on an alert. The system marks the notification as `'resolved'`, records the resolver's name, and if it's an SOS alert, sets the student's `sos_status` to `'safe'`. |
| **Mark as Read** | Admin clicks to mark a notification as read, updating the `read` field. |
| **Delete Archives** | Admin can delete all resolved SOS or Blackout archives in bulk. |
| **Filter by Class** | Admin selects a class from the dropdown to filter notifications by student class. |

### Alternative Flows

| Condition | Flow |
|-----------|------|
| **A1 – No Notifications** | If there are no notifications for the selected tab, an empty state is displayed. |
| **A2 – Main Admin Scope** | Main admins see conversations assigned to them plus all broadcasts, SOS, and blackout alerts. |

### Postconditions

- The admin has reviewed and managed notifications.
- Alert statuses are updated accordingly (acknowledged/resolved).

---

## UC-12: Send and Receive Student Messages

| Field | Detail |
|-------|--------|
| **Use Case ID** | UC-12 |
| **Use Case Name** | Send and Receive Student Messages |
| **Primary Actor** | Admin (Education), Main Admin |
| **Supporting Actor** | Student |
| **Description** | Administrators and students communicate through a Messenger-style messaging interface. Students send messages from the mobile app (directed to a specific admin), and admins reply from the web interface. The system supports real-time message updates, read receipts, and conversation management. |
| **Trigger** | A student sends a message from the mobile app, or an admin opens a student conversation on the web. |
| **Preconditions** | 1. Both parties must be authenticated. 2. Only admins with `education` or `main` roles can send messages. |

### Main Success Scenario — Student Sends Message

| Step | Actor | System Response |
|------|-------|-----------------|
| 1 | Student composes a message in the mobile app and selects a recipient admin. | — |
| 2 | — | The system creates a notification record with `type = 'student_message'`, `sender_type = 'student'`, and the selected `admin_id`. |
| 3 | — | The message appears in the admin's Student Messages tab with an unread indicator. |

### Main Success Scenario — Admin Replies

| Step | Actor | System Response |
|------|-------|-----------------|
| 1 | Admin opens a student conversation in the Messenger panel. | — |
| 2 | — | The system fetches all messages for that student (via `GET /messages/{student_id}/json`). |
| 3 | — | The system marks all unread student messages in the conversation as read. |
| 4 | Admin types a reply and clicks Send. | — |
| 5 | — | The system creates a notification record with `type = 'admin_reply'`, `sender_type = 'admin'`, and the admin's name. |
| 6 | — | The message appears in the student's mobile notification feed. |

### Alternative Flows

| Condition | Flow |
|-----------|------|
| **A1 – New Conversation** | Admin opens the "New Message" modal, selects a student, and sends the first message. |
| **A2 – Delete Conversation** | Admin deletes an entire conversation (`DELETE /messages/{student_id}`). All non-SOS, non-broadcast messages between the admin and student are removed. |
| **A3 – Empty Message** | If the admin attempts to send an empty message, the system returns a 422 error. |

### Postconditions

- Messages are persisted in the `notifications` table.
- Unread counts are updated for both parties.

---

## UC-13: Send Broadcast

| Field | Detail |
|-------|--------|
| **Use Case ID** | UC-13 |
| **Use Case Name** | Send Broadcast |
| **Primary Actor** | Admin (Education), Main Admin |
| **Description** | An administrator sends a broadcast notification to all students, a specific class, or a specific student. Broadcasts appear in the student's mobile notification feed and in the Broadcast tab of the admin notifications panel. |
| **Trigger** | The admin navigates to the Broadcast tab and clicks "Send Broadcast," or sends a broadcast from the mobile admin app. |
| **Preconditions** | The admin must be authenticated. |

### Main Success Scenario (Basic Flow)

| Step | Actor | System Response |
|------|-------|-----------------|
| 1 | Admin selects the target audience: **All Students**, a **specific class**, or a **specific student**. | — |
| 2 | Admin enters a **subject** and **message body**. | — |
| 3 | Admin clicks "Send Broadcast." | — |
| 4 | — | The system validates the input (subject max 255 chars, message required). |
| 5 | — | The system determines the target class based on the selection: `'all'` for all students, the class name for a class, or the student's class for an individual student. |
| 6 | — | The system creates a notification record with `type = 'broadcast'`, `sender_type = 'admin'`, the admin's name as sender, and `read = true` (since the admin authored it). |
| 7 | — | The system redirects with a success message: *"Broadcast notification sent successfully!"* |
| 8 | — | The broadcast appears in students' mobile notification feeds (filtered by class or globally). |

### Alternative Flows

| Condition | Flow |
|-----------|------|
| **A1 – Mobile Admin Broadcast** | The admin sends a broadcast from the mobile app via `POST /api/admin/broadcast`. The flow is identical but returns a JSON response instead of a redirect. |
| **A2 – Missing Fields** | If subject or message is empty, the system returns a validation error. |

### Postconditions

- The broadcast notification is stored in the database.
- All targeted students can view the broadcast in their mobile notification feed.
- The broadcast appears in the admin's Broadcast tab.

---

## Use Case Relationships Summary

| Relationship | Source Use Case | Target Use Case | Type |
|-------------|-----------------|-----------------|------|
| `<<updates>>` | UC-03: Manage Own Profile | UC-01: Authenticate (Student) | Extend |
| `<<updates>>` | UC-04: Send Heartbeat and Device Status | UC-09: View Live Tracking | Extend |
| `<<creates>>` | UC-05: Share Current Location | UC-10: View Location History | Include |
| `<<appears in>>` | UC-06: Send or Cancel Alert | UC-11: View Notification and Activity | Include |
| `<<appears in>>` | UC-07: Upload SOS Media | Storage | Include |
