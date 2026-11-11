# G!Track Use Case Narratives

**Project Name:** G!Track Student Tracking and Monitoring System
**Document Version:** 1.1
**Date:** November 7, 2026

This document uses the detailed narrative layout for the student mobile workflows and administrator web portal. Actor actions and system responses are listed in sequence for each use case.

## Actors

| Actor | Description |
|---|---|
| **Student User** | A registered student who uses the mobile application to authenticate, share device status and location, send safety alerts, and communicate with administrators. |
| **Student App** | The student's mobile application, including automated background tasks that transmit student status and location. |
| **Administrative User** | An Education Staff or Main Administrator who uses the G!Track web portal to monitor students, manage alerts, and communicate. |
| **Main Administrator** | An administrator with additional permission to manage student, class, and administrator records. |

---

## UC-01: Student Log In

**Student Log In Use Case Narrative**

The Student Log In use case authenticates a registered student in the G!Track mobile application so the student can use location sharing, safety alerts, and messaging.

*Table 1: Student Log In Use Case Narrative*

**Use Case Name:** Student Log In

**Actor(s):**  

**Purpose**

· Authenticate the student using their registered student ID and password.

**Overview**

· The student submits their credentials through the mobile app. The system verifies the account and opens the student's app session.

**Pre-condition**

· The student has an account and valid credentials, and the mobile app can connect to G!Track.

**Post-condition**

· The student is authenticated and can access the mobile app's student features.

**Trigger Event**

· The student opens the app and submits their student ID and password.

**Flow of Events**

| **Actor Actions** | **System Response** |
|---|---|
| 1. Opens the G!Track mobile app. | 2. Displays the student sign-in screen. |
| 3. Enters the registered Student ID and password. | 4. Receives the submitted credentials and checks them against the student account. |
| 5. Waits for authentication. | 6. On success, returns the student account information and allows access to the app. |

**Alternative Flow**

· If the credentials are invalid or the account cannot be found, the system rejects the request and displays an authentication error.

---

## UC-02: Administrator Log In

**Administrator Log In Use Case Narrative**

The Administrator Log In use case authenticates Education Staff and Main Administrators in the G!Track web portal using their Staff ID and password. Newly provisioned accounts are required to set a personal password before accessing the dashboard.

*Table 2: Administrator Log In Use Case Narrative*

**Use Case Name:** Administrator Log In

**Actor(s):** Administrative User, Main Administrator

**Purpose**

· Verify an administrator's identity and grant access to the web portal according to their account role.

**Overview**

· The administrator signs in with a Staff ID and password. On first login, the system requires a password change; otherwise, it routes the administrator to the dashboard.

**Pre-condition**

· The administrator has an account in G!Track and can access the web portal.

**Post-condition**

· The administrator has an authenticated web session and is directed to the first-login password screen or the role-appropriate dashboard.

**Trigger Event**

· The administrator opens the web portal and submits their Staff ID and password.

**Flow of Events**

| **Actor Actions** | **System Response** |
|---|---|
| 1. Opens the G!Track web portal. | 2. Displays the administrator sign-in page. |
| 3. Enters the Staff ID and password. | 4. Verifies the credentials against the administrator account. |
| 5. Submits the sign-in form. | 6. Starts an authenticated session and regenerates the session ID. |
| 7. If prompted for first-time setup, enters and confirms a new personal password. | 8. Validates and saves the new password, clears the first-login requirement, and directs the administrator to the dashboard. |
| 9. Otherwise, waits for the dashboard to load. | 10. Directs the administrator to the dashboard. |

**Alternative Flow**

· If the Staff ID or password is invalid, the system returns an error and keeps the administrator on the sign-in page.

· If first-time password confirmation or password requirements are not satisfied, the system displays validation errors and does not complete the password update.

---

## UC-03: Recover or Change Administrator Password

**Recover or Change Administrator Password Use Case Narrative**

This use case allows an administrator to recover access using their Staff ID and registered email, or to change their password while signed in.

*Table 3: Recover or Change Administrator Password Use Case Narrative*

**Use Case Name:** Recover or Change Administrator Password

**Actor(s):** Administrative User

**Purpose**

· Let an administrator restore access or update their password without changing account identity.

**Overview**

· Password recovery checks the Staff ID and registered email before saving a replacement password. A signed-in Education Staff user can change their password by providing the current password and confirming a new one.

**Pre-condition**

· For recovery, the administrator has access to the registered email information. For a signed-in password change, the administrator is authenticated.

**Post-condition**

· The new password is saved and the password-change timestamp is updated. After recovery, the administrator is returned to the sign-in page.

**Trigger Event**

· The administrator selects Forgot Password or opens the Change Password option in their profile menu.

**Flow of Events — Password Recovery**

| **Actor Actions** | **System Response** |
|---|---|
| 1. Opens the Forgot Password page. | 2. Displays the password recovery form. |
| 3. Enters Staff ID, registered email, new password, and confirmation. | 4. Validates the input and checks whether the Staff ID and email match an account. |
| 5. Submits the recovery form. | 6. Hashes and saves the new password, clears any first-login password requirement, and redirects to sign-in with a confirmation message. |

**Flow of Events — Change Password While Signed In**

| **Actor Actions** | **System Response** |
|---|---|
| 1. Opens the profile menu and selects Change Password. | 2. Displays the password change form. |
| 3. Enters the current password and a new password with confirmation. | 4. Checks the current password and validates the new password requirements. |
| 5. Submits the form. | 6. Hashes and saves the new password, updates the password-change timestamp, and displays a success message. |

**Alternative Flow**

· If the Staff ID and email do not match, or the submitted passwords fail validation, the system displays an error and does not save the new password.

· The current web recovery flow uses Staff ID and email verification; it does not use a 6-digit OTP.

---

## UC-04: Manage Own Profile

**Manage Own Profile Use Case Narrative**

This use case allows a student to update their own profile information and profile picture through the mobile app, and allows authorized Education Staff to update their own profile information in the web portal.

*Table 4: Manage Own Profile Use Case Narrative*

**Use Case Name:** Manage Own Profile

**Actor(s):** Student User, Administrative User

**Purpose**

· Keep the user's personal account information current.

**Overview**

· The user edits the profile fields available to their role. The system validates and saves the changes.

**Pre-condition**

· The user is authenticated.

**Post-condition**

· Valid profile changes are saved and displayed in the user's account.

**Trigger Event**

· The user opens the profile page or profile settings.

**Flow of Events**

| **Actor Actions** | **System Response** |
|---|---|
| 1. Opens their profile settings. | 2. Displays the profile information and editable fields allowed for the user's role. |
| 3. Updates available profile fields or selects a profile picture. | 4. Validates the submitted information and, for an uploaded picture, checks its file type and size. |
| 5. Saves the changes. | 6. Stores valid changes and returns a confirmation. |

**Alternative Flow**

· If a field or uploaded file fails validation, the system displays an error and does not apply the invalid change.

---

## UC-05: Send Heartbeat and Device Status

**Send Heartbeat and Device Status Use Case Narrative**

The Student App periodically sends a heartbeat so G!Track can keep the student's connection status and device telemetry current for administrator monitoring.

*Table 5: Send Heartbeat and Device Status Use Case Narrative*

**Use Case Name:** Send Heartbeat and Device Status

**Actor(s):** Student App (Automated Background Process)

**Purpose**

· Report that the student's device is active and provide the latest available device status.

**Overview**

· While the student app is operating, it sends a periodic status update. G!Track records the update and the web dashboard reflects the student's status.

**Pre-condition**

· The student is signed in, and the device can connect to G!Track.

**Post-condition**

· The student's status and submitted device information are updated. The student may be marked offline after the configured heartbeat timeout if updates stop.

**Trigger Event**

· The app's periodic heartbeat process runs.

**Flow of Events**

| **Actor Actions** | **System Response** |
|---|---|
| 1. Collects the available student/device status information. | 2. Receives the heartbeat request and validates the student identifier. |
| 3. Sends the heartbeat to G!Track. | 4. Updates the student's online status and the latest status timestamp. |
| 5. Waits for the response. | 6. Returns a success response to the app. |

**Alternative Flow**

· If the student record cannot be found or the request is invalid, the system returns an error and does not record the heartbeat.

· If no heartbeat is received within the configured timeout, the web dashboard marks the student offline during a subsequent status check.

---

## UC-06: Share Current Location

**Share Current Location Use Case Narrative**

The Student App sends the student's current GPS coordinates to G!Track so administrators can monitor the latest available location and review location history.

*Table 6: Share Current Location Use Case Narrative*

**Use Case Name:** Share Current Location

**Actor(s):** Student App (Automated Background Process)

**Purpose**

· Provide current student location information to G!Track for safety monitoring.

**Overview**

· The app obtains GPS coordinates and submits them to the server. G!Track validates and records the location for live tracking and history.

**Pre-condition**

· The student is signed in, location permission is enabled, and the device can connect to G!Track.

**Post-condition**

· The latest location is saved and is available for authorized administrator views.

**Trigger Event**

· The app's location service obtains a new GPS reading.

**Flow of Events**

| **Actor Actions** | **System Response** |
|---|---|
| 1. Reads the current GPS coordinates. | 2. Receives the location update and validates the student identifier and coordinate values. |
| 3. Sends the coordinates to G!Track. | 4. Records the location with its timestamp and updates the student's latest location information. |
| 5. Waits for confirmation. | 6. Returns a success response to the app. |

**Alternative Flow**

· If location permission is disabled, GPS is unavailable, or the device has no network connection, no new location is recorded.

· If the student cannot be found or coordinates are invalid, the system returns an error.

---

## UC-07: Send or Cancel Safety Alert

**Send or Cancel Safety Alert Use Case Narrative**

This use case allows a student to send an SOS alert when assistance is needed and to mark the alert safe when the situation is resolved.

*Table 7: Send or Cancel Safety Alert Use Case Narrative*

**Use Case Name:** Send or Cancel Safety Alert

**Actor(s):** Student User, Student App, Administrative User

**Purpose**

· Notify administrators of a student's safety concern and keep the alert state current.

**Overview**

· The student initiates an SOS alert from the app. G!Track records it for administrator review. When the student is safe, the alert can be canceled or resolved.

**Pre-condition**

· The student has an account and the app can communicate with G!Track.

**Post-condition**

· A sent alert is available for administrator review. A canceled or resolved alert is marked as no longer active.

**Trigger Event**

· The student presses the SOS control or marks themselves safe in the app.

**Flow of Events — Send Alert**

| **Actor Actions** | **System Response** |
|---|---|
| 1. Presses the SOS control. | 2. Receives the safety alert and associated information submitted by the app. |
| 3. Confirms the alert submission. | 4. Records the alert and makes it available in the administrator's alert view. |
| 5. Waits for confirmation. | 6. Returns the alert submission result to the app. |

**Flow of Events — Cancel or Mark Safe**

| **Actor Actions** | **System Response** |
|---|---|
| 1. Selects the safe or cancel action. | 2. Receives the updated safety state. |
| 3. Confirms the action. | 4. Updates the student's safety status and resolves the applicable active alert. |

**Alternative Flow**

· If the app cannot connect, the alert state cannot be updated until a request reaches G!Track.

· An authorized administrator may also acknowledge or resolve an alert through the web portal.

---

## UC-08: Upload SOS Media

**Upload SOS Media Use Case Narrative**

The Student App can upload media associated with an SOS event so administrators have additional information when reviewing the alert.

*Table 8: Upload SOS Media Use Case Narrative*

**Use Case Name:** Upload SOS Media

**Actor(s):** Student App, Student User

**Purpose**

· Attach supporting media to an SOS alert for administrator review.

**Overview**

· The app submits a media file associated with an SOS event. G!Track validates and stores the file and associates it with the alert.

**Pre-condition**

· An SOS event has been initiated, the app has access to the media, and the device can connect to G!Track.

**Post-condition**

· Successfully uploaded media is stored and associated with the relevant alert.

**Trigger Event**

· The student app completes or initiates a media upload for an SOS event.

**Flow of Events**

| **Actor Actions** | **System Response** |
|---|---|
| 1. Selects or records media for the SOS event. | 2. Receives the upload and checks that the file meets the allowed type and size requirements. |
| 3. Submits the media file. | 4. Stores the file and associates it with the relevant alert. |
| 5. Waits for the upload result. | 6. Returns a success response so the app can report that the media was uploaded. |

**Alternative Flow**

· If the file is invalid or upload fails, the system reports the failure. The student can retry when a connection is available.

---

## UC-09: View Dashboard

**View Dashboard Use Case Narrative**

The dashboard gives administrators an at-a-glance overview of student connection status, recent updates, and active safety alerts.

*Table 9: View Dashboard Use Case Narrative*

**Use Case Name:** View Dashboard

**Actor(s):** Administrative User, Main Administrator

**Purpose**

· Provide a summary of student and system status for monitoring.

**Overview**

· The web portal retrieves student status and alert information and displays summary cards and monitoring data. Dashboard statistics refresh periodically.

**Pre-condition**

· The administrator is signed in and has access to the dashboard.

**Post-condition**

· The administrator has reviewed the latest dashboard information.

**Trigger Event**

· The administrator signs in or selects Dashboard from the web portal.

**Flow of Events**

| **Actor Actions** | **System Response** |
|---|---|
| 1. Opens the dashboard. | 2. Retrieves student status, latest update, and active alert information. |
| 3. Reviews the summary information. | 4. Displays online/offline counts, recent updates, and relevant alert indicators. |
| 5. Keeps the dashboard open. | 6. Refreshes dashboard statistics periodically. |

**Alternative Flow**

· If there are no registered students or alerts, the dashboard displays the available empty or zero-count state.

---

## UC-10: View Live Tracking

**View Live Tracking Use Case Narrative**

Live Tracking allows administrators to inspect available student locations on a map and filter the displayed students by class.

*Table 10: View Live Tracking Use Case Narrative*

**Use Case Name:** View Live Tracking

**Actor(s):** Administrative User, Main Administrator

**Purpose**

· Monitor the latest student locations and safety status.

**Overview**

· The administrator opens the tracking map, reviews student markers, and may filter the map by class.

**Pre-condition**

· The administrator is signed in. Location data has been submitted for students whose positions should appear.

**Post-condition**

· The administrator has reviewed the available location information and any displayed safety indicators.

**Trigger Event**

· The administrator selects Live Tracking from the web portal.

**Flow of Events**

| **Actor Actions** | **System Response** |
|---|---|
| 1. Opens the Live Tracking page. | 2. Loads the map and available class filters. |
| 3. Selects a class filter or keeps the default selection. | 4. Displays available student locations matching the selected filter. |
| 5. Selects or inspects a map marker. | 6. Displays the available student and status details for that location. |

**Alternative Flow**

· If a student has not submitted a location, no current marker is available for that student.

· If no class is selected, the map displays the default set of available student locations.

---

## UC-11: View Location History

**View Location History Use Case Narrative**

Location History allows an administrator to inspect a student's previously recorded location entries and review the associated timeline.

*Table 11: View Location History Use Case Narrative*

**Use Case Name:** View Location History

**Actor(s):** Administrative User, Main Administrator

**Purpose**

· Review the recorded location timeline for a selected student.

**Overview**

· The administrator opens a student's history from the activity or student view. The portal displays the student details and available location records.

**Pre-condition**

· The administrator is signed in and the selected student record exists.

**Post-condition**

· The administrator has viewed the available location history for the selected student.

**Trigger Event**

· The administrator selects View History for a student.

**Flow of Events**

| **Actor Actions** | **System Response** |
|---|---|
| 1. Selects a student and opens the student's history. | 2. Retrieves the student information and recorded location entries. |
| 3. Reviews the history timeline and map. | 4. Displays available location records and their timestamps. |
| 5. Selects a history entry, if available. | 6. Focuses the map on the selected recorded location. |

**Alternative Flow**

· If there are no location records, the portal displays an empty state instead of a populated timeline.

---

## UC-12: View Notifications and Activity

**View Notifications and Activity Use Case Narrative**

This use case allows administrators to review safety alerts, student messages, broadcasts, and student activity, and to take the actions permitted for each item.

*Table 12: View Notifications and Activity Use Case Narrative*

**Use Case Name:** View Notifications and Activity

**Actor(s):** Administrative User, Main Administrator

**Purpose**

· Keep administrators informed about student status and messages requiring attention.

**Overview**

· The portal presents alert, message, and broadcast views. Administrators can filter available information and acknowledge or resolve alerts.

**Pre-condition**

· The administrator is signed in.

**Post-condition**

· The administrator has reviewed relevant notification or activity information. Any acknowledged or resolved alert has its updated state recorded.

**Trigger Event**

· The administrator opens Notifications, Activity, or an alert indicator.

**Flow of Events**

| **Actor Actions** | **System Response** |
|---|---|
| 1. Opens a notification or activity view. | 2. Retrieves and displays relevant alerts, messages, broadcasts, or activity details. |
| 3. Selects an item or applies an available filter. | 4. Displays the selected item or filtered results. |
| 5. Acknowledges or resolves an alert, when applicable. | 6. Records the alert state change and refreshes the displayed information. |

**Alternative Flow**

· If there are no items matching the selected view or filter, the portal displays an empty state.

· The available conversations and management actions depend on the administrator's role and access permissions.

---

## UC-13: Send and Receive Student Messages

**Send and Receive Student Messages Use Case Narrative**

This use case allows students and authorized administrators to exchange messages through the student app and the administrator web portal.

*Table 13: Send and Receive Student Messages Use Case Narrative*

**Use Case Name:** Send and Receive Student Messages

**Actor(s):** Student User, Administrative User

**Purpose**

· Support direct communication between a student and an authorized administrator.

**Overview**

· Students submit messages through the app. Administrators review conversations and send replies through the web portal.

**Pre-condition**

· The student and administrator have accounts. The administrator is signed in to the portal and is permitted to use messaging.

**Post-condition**

· The submitted message or reply is saved in its conversation and is available to the other participant.

**Trigger Event**

· A student sends a message, or an administrator opens a student conversation.

**Flow of Events — Student Sends a Message**

| **Actor Actions** | **System Response** |
|---|---|
| 1. Selects an administrator and writes a message in the student app. | 2. Receives the message and associates it with the student-administrator conversation. |
| 3. Submits the message. | 4. Saves the message and makes it available in the administrator's conversation view. |

**Flow of Events — Administrator Replies**

| **Actor Actions** | **System Response** |
|---|---|
| 1. Opens the Messages view and selects a student conversation. | 2. Retrieves and displays the conversation history. |
| 3. Writes and sends a reply. | 4. Saves the reply and makes it available to the student. |
| 5. Reviews the updated conversation. | 6. Refreshes the conversation and read status as applicable. |

**Alternative Flow**

· If a message is empty or invalid, the system displays a validation error and does not save it.

· If the conversation cannot be loaded or a message cannot be delivered, the system reports the failure.

---

## UC-14: Send Broadcast

**Send Broadcast Use Case Narrative**

The Send Broadcast use case allows an administrator to send an announcement to all students, a selected class, or an individual student.

*Table 14: Send Broadcast Use Case Narrative*

**Use Case Name:** Send Broadcast

**Actor(s):** Administrative User, Main Administrator

**Purpose**

· Distribute an announcement to a selected student audience.

**Overview**

· The administrator selects the audience, enters the subject and message, and submits the broadcast. The system records the broadcast and its recipients.

**Pre-condition**

· The administrator is signed in and the selected audience contains at least one student.

**Post-condition**

· The broadcast and recipient records are saved and the targeted students can view the announcement.

**Trigger Event**

· The administrator opens Broadcasts and chooses to compose a new broadcast.

**Flow of Events**

| **Actor Actions** | **System Response** |
|---|---|
| 1. Opens the Broadcasts view. | 2. Displays the broadcast form and available audience options. |
| 3. Selects all students, a class, or an individual student. | 4. Resolves the selected audience to student recipients. |
| 5. Enters a subject and message. | 6. Validates the required fields and selected audience. |
| 7. Submits the broadcast. | 8. Saves the broadcast and recipient records, then displays a success confirmation. |

**Alternative Flow**

· If required fields are missing or invalid, the system displays validation errors and does not send the broadcast.

· If the selected audience has no students, the system reports that no recipients were found.

---

## Use Case Relationships

| Relationship | Source Use Case | Related Use Case |
|---|---|---|
| Student account access | UC-01: Student Log In | UC-03: Manage Own Profile; UC-05: Send Heartbeat and Device Status; UC-06: Share Current Location |
| Alert review | UC-07: Send or Cancel Safety Alert | UC-12: View Notifications and Activity |
| Location records | UC-06: Share Current Location | UC-11: View Location History |
| Alert evidence | UC-08: Upload SOS Media | UC-12: View Notifications and Activity |
| Student communication | UC-13: Send and Receive Student Messages | UC-12: View Notifications and Activity |
| Administrator access | UC-02: Administrator Log In | UC-09: View Dashboard; UC-10: View Live Tracking; UC-12: View Notifications and Activity; UC-14: Send Broadcast |
