<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Notification;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NotificationsMessagingFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_send_a_broadcast_to_all_students(): void
    {
        $admin = $this->createAdmin('main');

        $this->actingAs($admin, 'admin')
            ->post('/notifications/send', [
                'target' => 'all',
                'subject' => 'Service update',
                'message' => 'The system will be unavailable briefly.',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('notifications', [
            'type' => 'broadcast',
            'class' => 'all',
            'subject' => 'Service update',
            'message' => 'The system will be unavailable briefly.',
            'sender_type' => 'admin',
            'sender_name' => 'Admin - Main Admin',
            'read' => true,
        ]);
    }

    public function test_admin_can_acknowledge_and_resolve_an_emergency_alert(): void
    {
        $admin = $this->createAdmin('main');
        $student = $this->createStudent();
        $alert = Notification::create([
            'student_id' => $student->id,
            'admin_id' => $admin->id,
            'type' => 'sos',
            'sender_type' => 'student',
            'message' => 'Emergency',
            'read' => false,
            'status' => 'pending',
        ]);

        $this->actingAs($admin, 'admin')
            ->post("/notifications/{$alert->id}/acknowledge")
            ->assertSessionHas('success');

        $this->assertDatabaseHas('notifications', [
            'id' => $alert->id,
            'acknowledged_by_admin_id' => $admin->id,
            'read' => true,
        ]);
        $this->assertDatabaseHas('notifications', [
            'student_id' => $student->id,
            'admin_id' => $admin->id,
            'type' => 'admin_reply',
            'sender_type' => 'admin',
            'reply_to_id' => $alert->id,
            'message' => 'Your SOS alert has been acknowledged.',
            'read' => false,
        ]);

        $this->post("/notifications/{$alert->id}/resolve")
            ->assertSessionHas('success');

        $this->assertDatabaseHas('notifications', [
            'id' => $alert->id,
            'status' => 'resolved',
            'resolved_by_admin_id' => $admin->id,
        ]);
        $this->assertSame('safe', $student->fresh()->sos_status);
    }

    public function test_admin_can_acknowledge_a_blackout_alert_and_message_the_student_once(): void
    {
        $admin = $this->createAdmin('main');
        $student = $this->createStudent();
        $alert = Notification::create([
            'student_id' => $student->id,
            'admin_id' => $admin->id,
            'type' => 'blackout',
            'sender_type' => 'student',
            'message' => 'Blackout alert',
            'read' => false,
            'status' => 'pending',
        ]);

        $this->actingAs($admin, 'admin')
            ->post("/notifications/{$alert->id}/acknowledge")
            ->assertSessionHas('success');

        $this->assertDatabaseHas('notifications', [
            'student_id' => $student->id,
            'admin_id' => $admin->id,
            'type' => 'admin_reply',
            'sender_type' => 'admin',
            'reply_to_id' => $alert->id,
            'message' => 'Your blackout alert has been acknowledged.',
            'read' => false,
        ]);

        $this->post("/notifications/{$alert->id}/acknowledge")
            ->assertSessionHas('info', 'This alert has already been acknowledged; no additional message was sent.');

        $this->assertSame(
            1,
            Notification::where('reply_to_id', $alert->id)
                ->where('type', 'admin_reply')
                ->count()
        );
    }

    public function test_staff_can_load_conversation_messages_and_mark_student_messages_read(): void
    {
        $staff = $this->createAdmin('education');
        $student = $this->createStudent();
        $message = Notification::create([
            'student_id' => $student->id,
            'admin_id' => $staff->id,
            'class' => $student->class,
            'type' => 'student_message',
            'sender_type' => 'student',
            'message' => 'Where is the bus?',
            'read' => false,
            'status' => 'pending',
        ]);

        $this->actingAs($staff, 'admin')
            ->getJson("/messages/{$student->student_id}/json")
            ->assertOk()
            ->assertJsonPath('messages.0.id', $message->id)
            ->assertJsonPath('messages.0.message', 'Where is the bus?');

        $this->assertDatabaseHas('notifications', [
            'id' => $message->id,
            'read' => true,
        ]);
    }

    public function test_staff_can_send_a_reply_and_empty_messages_are_rejected(): void
    {
        $staff = $this->createAdmin('education');
        $student = $this->createStudent();
        $parent = Notification::create([
            'student_id' => $student->id,
            'admin_id' => $staff->id,
            'class' => $student->class,
            'type' => 'student_message',
            'sender_type' => 'student',
            'message' => 'Where is the bus?',
            'read' => false,
            'status' => 'pending',
        ]);

        $this->actingAs($staff, 'admin')
            ->postJson("/messages/new/{$student->student_id}", [
                'message' => 'It will arrive soon.',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('notifications', [
            'student_id' => $student->id,
            'admin_id' => $staff->id,
            'type' => 'admin_reply',
            'sender_type' => 'admin',
            'reply_to_id' => $parent->id,
            'message' => 'It will arrive soon.',
        ]);

        $this->postJson("/messages/new/{$student->student_id}", [
            'message' => '   ',
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Message cannot be empty.');
    }

    public function test_conversations_are_scoped_to_the_logged_in_admin(): void
    {
        $staff = $this->createAdmin('education');
        $otherStaff = $this->createAdmin('other');
        $student = $this->createStudent();

        Notification::create([
            'student_id' => $student->id,
            'admin_id' => $otherStaff->id,
            'class' => $student->class,
            'type' => 'student_message',
            'sender_type' => 'student',
            'message' => 'Assigned to other staff',
            'read' => false,
            'status' => 'pending',
        ]);

        $this->actingAs($staff, 'admin')
            ->getJson("/messages/{$student->student_id}/json")
            ->assertOk()
            ->assertJsonCount(0, 'messages');
    }

    private function createAdmin(string $role): Admin
    {
        return Admin::create([
            'staff_id' => strtoupper($role).str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT),
            'first_name' => $role === 'main' ? 'Main' : 'Education',
            'last_name' => 'Admin',
            'email' => $role.'-'.uniqid().'@example.com',
            'password' => Hash::make('password123'),
            'role' => $role,
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
