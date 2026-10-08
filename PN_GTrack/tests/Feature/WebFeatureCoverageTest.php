<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\BatchClass;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Student;
use App\Models\StudentAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WebFeatureCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_admin_login_and_logout_flow(): void
    {
        $admin = $this->createAdmin('EDU001', 'education');

        $this->get('/')->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/reset-password')->assertOk();

        $this->post('/login', [
            'staff_id' => $admin->staff_id,
            'password' => 'password123',
        ])->assertRedirect('/dashboard');

        $this->get('/dashboard')->assertOk();
        $this->post('/logout')->assertRedirect('/login');
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/logout')->assertRedirect('/login');

        $this->post('/login', [
            'staff_id' => $admin->staff_id,
            'password' => 'incorrect',
        ])->assertSessionHasErrors('staff_id');
    }

    public function test_password_reset_requires_matching_staff_id_and_email_and_minimum_length(): void
    {
        $admin = $this->createAdmin('EDU001', 'education');

        $this->post('/reset-password', [
            'staff_id' => $admin->staff_id,
            'email' => 'wrong@example.com',
            'password' => 'newpass1',
            'password_confirmation' => 'newpass1',
        ])->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('password123', $admin->fresh()->password));

        $this->post('/reset-password', [
            'staff_id' => $admin->staff_id,
            'email' => $admin->email,
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertSessionHasErrors('password');

        $this->post('/reset-password', [
            'staff_id' => $admin->staff_id,
            'email' => $admin->email,
            'password' => 'newpass1',
            'password_confirmation' => 'newpass1',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('newpass1', $admin->fresh()->password));
    }

    public function test_dashboard_tracking_activity_and_student_history_pages_render(): void
    {
        $admin = $this->createAdmin('MAIN001', 'main');
        $student = $this->createStudent();
        Location::create([
            'student_id' => $student->id,
            'latitude' => 14.5,
            'longitude' => 121.0,
            'recorded_at' => now(),
            'sos_status' => 'safe',
        ]);

        $this->actingAs($admin, 'admin');
        $this->get('/dashboard')->assertOk();
        $this->get('/tracking')->assertOk();
        $this->get('/activity')->assertOk();
        $this->get(route('students.history', $student))->assertOk();
    }

    public function test_management_index_views_render_for_main_admin(): void
    {
        $admin = $this->createAdmin('MAIN001', 'main');

        $this->actingAs($admin, 'admin');
        $this->get(route('students.index'))->assertOk();
        $this->get(route('classes.index'))->assertOk();
        $this->get(route('admins.index'))->assertOk();
    }

    public function test_admin_can_create_account_and_cannot_delete_self(): void
    {
        $admin = $this->createAdmin('MAIN001', 'main');

        $this->actingAs($admin, 'admin')
            ->post(route('admins.store'), [
                'first_name' => 'New',
                'middle_initial' => 'A',
                'last_name' => 'Educator',
                'email' => 'new.educator@example.com',
                'password' => 'newpass1',
                'password_confirmation' => 'newpass1',
                'role' => 'education',
            ])->assertSessionHas('success');

        $created = Admin::where('email', 'new.educator@example.com')->firstOrFail();
        $this->assertStringStartsWith('EDU', $created->staff_id);
        $this->assertTrue(Hash::check('newpass1', $created->password));

        $this->delete(route('admins.destroy', $admin))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('admins', ['id' => $admin->id]);
    }

    public function test_admin_can_list_students_and_filter_by_class_as_json(): void
    {
        $admin = $this->createAdmin('EDU001', 'education');
        $student = $this->createStudent();
        $otherStudent = Student::create([
            'student_id' => 'STU2027002',
            'name' => 'Taylor Student',
            'first_name' => 'Taylor',
            'last_name' => 'Student',
            'email' => 'taylor@example.com',
            'class' => '2027',
            'gender' => 'Male',
            'contact' => '09987654321',
        ]);

        $this->actingAs($admin, 'admin')
            ->getJson('/students/all/json?class=2026')
            ->assertOk()
            ->assertJsonCount(1, 'students')
            ->assertJsonPath('students.0.student_id', $student->student_id);

        $this->getJson('/students/all/json?class=all')
            ->assertOk()
            ->assertJsonCount(2, 'students');
        $this->assertNotSame($student->id, $otherStudent->id);
    }

    public function test_notification_pages_reply_read_and_archive_actions(): void
    {
        $staff = $this->createAdmin('EDU001', 'education');
        $student = $this->createStudent();
        $message = Notification::create([
            'student_id' => $student->id,
            'admin_id' => $staff->id,
            'class' => $student->class,
            'type' => 'student_message',
            'sender_type' => 'student',
            'message' => 'Please call me.',
            'read' => false,
            'status' => 'pending',
        ]);
        $readable = Notification::create([
            'student_id' => $student->id,
            'type' => 'broadcast',
            'message' => 'Read this update.',
            'read' => false,
            'status' => 'pending',
        ]);
        $sosArchive = Notification::create([
            'student_id' => $student->id,
            'type' => 'sos',
            'message' => 'Resolved SOS.',
            'read' => true,
            'status' => 'resolved',
            'video_url' => 'https://example.test/video.mp4',
        ]);
        $blackoutArchive = Notification::create([
            'student_id' => $student->id,
            'type' => 'blackout',
            'message' => 'Resolved blackout.',
            'read' => true,
            'status' => 'resolved',
        ]);

        $this->actingAs($staff, 'admin');
        $this->get('/notifications?tab=student')->assertOk();
        $this->post("/notifications/{$message->id}/reply", [
                'message' => 'I will call shortly.',
            ])->assertSessionHas('success');

        $this->assertDatabaseHas('notifications', [
            'student_id' => $student->id,
            'admin_id' => $staff->id,
            'type' => 'admin_reply',
            'reply_to_id' => $message->id,
            'message' => 'I will call shortly.',
        ]);

        $this->get("/notifications/{$readable->id}/read")
            ->assertSessionHas('success');
        $this->assertDatabaseHas('notifications', [
            'id' => $readable->id,
            'read' => true,
        ]);

        $this->post('/notifications/delete-all-sos-archives')
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('notifications', ['id' => $sosArchive->id]);

        $this->post('/notifications/delete-all-blackout-archives')
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('notifications', ['id' => $blackoutArchive->id]);
    }

    public function test_staff_can_delete_only_their_conversation_and_non_educator_cannot_reply(): void
    {
        $staff = $this->createAdmin('EDU001', 'education');
        $otherStaff = $this->createAdmin('EDU002', 'education', 'other@example.com');
        $student = $this->createStudent();
        $staffMessage = Notification::create([
            'student_id' => $student->id,
            'admin_id' => $staff->id,
            'class' => $student->class,
            'type' => 'student_message',
            'sender_type' => 'student',
            'message' => 'My conversation.',
            'read' => false,
            'status' => 'pending',
        ]);
        $otherMessage = Notification::create([
            'student_id' => $student->id,
            'admin_id' => $otherStaff->id,
            'class' => $student->class,
            'type' => 'student_message',
            'sender_type' => 'student',
            'message' => 'Other staff conversation.',
            'read' => false,
            'status' => 'pending',
        ]);

        $this->actingAs($staff, 'admin')
            ->deleteJson('/messages/'.$student->student_id)
            ->assertOk()
            ->assertJsonPath('success', true);
        $this->assertDatabaseMissing('notifications', ['id' => $staffMessage->id]);
        $this->assertDatabaseHas('notifications', ['id' => $otherMessage->id]);

        $unauthorizedAdmin = $this->createAdmin('OTH001', 'other');
        $this->actingAs($unauthorizedAdmin, 'admin')
            ->postJson("/messages/new/{$student->student_id}", ['message' => 'Reply'])
            ->assertForbidden();
    }

    public function test_student_management_syncs_auth_credentials_after_email_and_password_changes(): void
    {
        $admin = $this->createAdmin('MAIN001', 'main');
        $student = $this->createStudent();
        StudentAuth::create([
            'student_id' => $student->student_id,
            'email' => $student->email,
            'password' => Hash::make('oldpass1'),
        ]);

        $this->actingAs($admin, 'admin')
            ->put(route('students.update', $student), [
                'first_name' => 'Jamie',
                'middle_initial' => '',
                'last_name' => 'Student',
                'email' => 'jamie.updated@example.com',
                'class' => '2026',
                'gender' => 'Female',
                'contact' => '09123456789',
                'current_password' => 'oldpass1',
                'new_password' => 'newpass1',
                'new_password_confirmation' => 'newpass1',
            ])->assertSessionHas('success');

        $auth = StudentAuth::where('student_id', $student->student_id)->firstOrFail();
        $this->assertSame('jamie.updated@example.com', $auth->email);
        $this->assertTrue(Hash::check('newpass1', $auth->password));
    }

    private function createAdmin(string $staffId, string $role, ?string $email = null): Admin
    {
        return Admin::create([
            'staff_id' => $staffId,
            'first_name' => 'Test',
            'last_name' => 'Admin',
            'email' => $email ?? strtolower($staffId).'@example.com',
            'password' => Hash::make('password123'),
            'role' => $role,
        ]);
    }

    private function createStudent(): Student
    {
        BatchClass::firstOrCreate(['name' => '2026']);
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
