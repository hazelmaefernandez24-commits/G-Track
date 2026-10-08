<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\BatchClass;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MobileApiFeatureCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_mobile_authentication_and_logout_endpoints(): void
    {
        $admin = $this->createAdmin();

        $this->postJson('/api/login', [
            'role' => 'admin',
            'staff_id' => $admin->staff_id,
            'password' => 'password123',
        ])->assertOk()
            ->assertJsonPath('role', 'admin')
            ->assertJsonPath('user.staff_id', $admin->staff_id);

        $this->postJson('/api/login', [
            'role' => 'admin',
            'staff_id' => $admin->staff_id,
            'password' => 'incorrect',
        ])->assertUnauthorized();

        $this->postJson('/api/login', ['role' => 'student'])
            ->assertBadRequest();

        $this->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Logged out successfully');
    }

    public function test_profile_picture_upload_saves_file_and_rejects_missing_student(): void
    {
        Storage::fake('public');
        $student = $this->createStudent();

        $this->post('/api/student/upload-profile-picture', [
            'student_id' => $student->student_id,
            'profile_picture' => UploadedFile::fake()->create('profile.png', 20, 'image/png'),
        ])->assertOk()
            ->assertJsonPath('success', true);

        $student->refresh();
        $this->assertNotNull($student->profile_picture);
        Storage::disk('public')->assertExists($student->profile_picture);

        $this->post('/api/student/upload-profile-picture', [
            'student_id' => 'UNKNOWN',
            'profile_picture' => UploadedFile::fake()->create('unknown.png', 20, 'image/png'),
        ])->assertNotFound()
            ->assertJsonPath('success', false);
    }

    public function test_device_status_and_dashboard_stats_report_student_state(): void
    {
        $student = $this->createStudent();
        $this->postJson('/api/device/status', [
            'student_id' => $student->student_id,
            'battery_level' => 63,
            'signal' => 'Weak',
        ])->assertOk()
            ->assertJsonPath('message', 'Student status updated');

        $this->assertTrue($student->fresh()->status);
        $this->assertSame(63, $student->fresh()->battery_level);
        $this->assertSame('Weak', $student->fresh()->signal_status);

        $this->getJson('/api/dashboard/stats')
            ->assertOk()
            ->assertJsonPath('onlineCount', 1)
            ->assertJsonPath('offlineCount', 0)
            ->assertJsonPath('students.0.student_id', $student->student_id);
    }

    public function test_status_endpoint_returns_latest_location_and_student_online_state(): void
    {
        $student = $this->createStudent();
        $older = Location::create([
            'student_id' => $student->id,
            'latitude' => 14.1,
            'longitude' => 120.1,
            'recorded_at' => now()->subMinutes(20),
            'created_at' => now()->subMinutes(20),
            'sos_status' => 'safe',
        ]);
        $latest = Location::create([
            'student_id' => $student->id,
            'latitude' => 14.2,
            'longitude' => 120.2,
            'recorded_at' => now()->subMinute(),
            'created_at' => now()->subMinute(),
            'sos_status' => 'help',
        ]);
        $older->created_at = now()->subMinutes(20);
        $older->save();
        $latest->created_at = now();
        $latest->save();

        $this->getJson('/api/status/all')
            ->assertOk()
            ->assertJsonPath('0.id', $student->id)
            ->assertJsonPath('0.status', true)
            ->assertJsonPath('0.last_seen', $latest->recorded_at->toJSON());

        $this->assertNotSame($older->id, $latest->id);
    }

    public function test_location_sos_endpoint_updates_student_alert_and_latest_history(): void
    {
        $student = $this->createStudent();
        $location = Location::create([
            'student_id' => $student->id,
            'latitude' => 14.5,
            'longitude' => 121.0,
            'recorded_at' => now(),
            'sos_status' => 'safe',
        ]);
        $alert = Notification::create([
            'student_id' => $student->id,
            'type' => 'sos',
            'message' => 'Need help',
            'read' => false,
            'status' => 'pending',
        ]);

        $this->postJson('/api/location/sos', [
            'student_id' => $student->student_id,
            'sos_status' => 'safe',
            'latitude' => 14.5,
            'longitude' => 121.0,
            'battery_level' => 40,
            'signal' => 'Good',
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('sos_status', 'safe');

        $this->assertSame('safe', $student->fresh()->sos_status);
        $this->assertSame(40, $student->fresh()->battery_level);
        $this->assertSame('safe', $location->fresh()->sos_status);
        $this->assertDatabaseHas('notifications', [
            'id' => $alert->id,
            'status' => 'resolved',
            'read' => true,
        ]);
    }

    public function test_student_notification_api_sends_and_reads_notifications(): void
    {
        $student = $this->createStudent();

        $this->postJson('/api/notifications/send', [
            'student_id' => $student->student_id,
            'target' => 'student_message',
            'message' => 'I need help finding the pickup point.',
        ])->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('notifications', [
            'student_id' => $student->id,
            'type' => 'student_message',
            'message' => 'I need help finding the pickup point.',
        ]);

        $this->getJson('/api/notifications/'.$student->student_id)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('student.name', $student->name)
            ->assertJsonCount(1, 'notifications');

        $this->getJson('/api/notifications/UNKNOWN')
            ->assertNotFound()
            ->assertJsonPath('success', false);
    }

    public function test_student_video_upload_and_video_lookup_endpoints(): void
    {
        Storage::fake('public');
        $student = $this->createStudent();

        $this->post('/api/upload-video', [
            'student_id' => $student->student_id,
            'target' => 'sos',
            'message' => 'Emergency video',
            'video' => UploadedFile::fake()->create('incident.mp4', 100, 'video/mp4'),
        ])->assertOk()
            ->assertJsonPath('success', true);

        $alert = Notification::where('student_id', $student->id)->where('type', 'sos')->firstOrFail();
        $this->assertNotEmpty($alert->video_url);

        $this->getJson('/api/notification/'.$alert->id.'/video')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('video_url', $alert->video_url);

        $this->getJson('/api/student/'.$student->student_id.'/latest-video')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('notification_id', $alert->id);

        $this->getJson('/api/notification/99999/video')
            ->assertNotFound();
    }

    public function test_admin_mobile_directory_broadcast_reply_and_alert_resolution(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent();
        BatchClass::firstOrCreate(['name' => '2026']);
        $message = Notification::create([
            'student_id' => $student->id,
            'admin_id' => $admin->id,
            'class' => $student->class,
            'type' => 'student_message',
            'sender_type' => 'student',
            'message' => 'Where should I wait?',
            'read' => false,
            'status' => 'pending',
        ]);
        $alert = Notification::create([
            'student_id' => $student->id,
            'admin_id' => $admin->id,
            'type' => 'sos',
            'message' => 'SOS alert',
            'read' => false,
            'status' => 'pending',
        ]);

        $this->getJson('/api/admins')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('admins.0.staff_id', $admin->staff_id);

        $this->postJson('/api/admin/broadcast', [
            'target' => '2026',
            'subject' => 'Pickup update',
            'message' => 'Wait at the front gate.',
            'admin_name' => 'Test Admin',
        ])->assertOk()
            ->assertJsonPath('success', true);
        $this->assertDatabaseHas('notifications', [
            'type' => 'broadcast',
            'class' => '2026',
            'subject' => 'Pickup update',
        ]);

        $this->postJson('/api/admin/message/send/'.$student->student_id, [
            'message' => 'Wait at the front gate.',
            'admin_id' => $admin->id,
        ])->assertOk()
            ->assertJsonPath('success', true);
        $this->assertDatabaseHas('notifications', [
            'student_id' => $student->id,
            'admin_id' => $admin->id,
            'type' => 'admin_reply',
            'reply_to_id' => $message->id,
        ]);

        $this->postJson('/api/admin/notification/resolve/'.$alert->id)
            ->assertOk()
            ->assertJsonPath('success', true);
        $this->assertSame('safe', $student->fresh()->sos_status);
        $this->assertDatabaseHas('notifications', [
            'id' => $alert->id,
            'status' => 'resolved',
            'read' => true,
        ]);
    }

    private function createAdmin(): Admin
    {
        return Admin::create([
            'staff_id' => 'EDU001',
            'first_name' => 'Test',
            'last_name' => 'Educator',
            'email' => 'educator@example.com',
            'password' => Hash::make('password123'),
            'role' => 'education',
        ]);
    }

    private function createStudent(): Student
    {
        return Student::create([
            'student_id' => 'STU2026001',
            'name' => 'Jamie Student',
            'first_name' => 'Jamie',
            'last_name' => 'Student',
            'email' => 'jamie@example.com',
            'class' => '2026',
            'gender' => 'Female',
            'contact' => '09123456789',
        ]);
    }
}
