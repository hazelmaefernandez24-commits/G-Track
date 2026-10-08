<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Student;
use App\Models\StudentAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentMobileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_log_in_send_heartbeat_and_go_offline(): void
    {
        $student = $this->createStudent();
        StudentAuth::create([
            'student_id' => $student->student_id,
            'email' => $student->email,
            'password' => Hash::make('student123'),
        ]);

        $this->postJson('/api/student/login', [
            'student_id' => $student->student_id,
            'password' => 'student123',
        ])->assertOk()
            ->assertJsonPath('role', 'student');

        $this->assertTrue($student->fresh()->status);

        $this->postJson('/api/student/heartbeat', [
            'student_id' => $student->student_id,
            'battery_level' => 72,
            'signal' => 'Good',
        ])->assertOk()
            ->assertJsonPath('student_id', $student->student_id);

        $this->assertSame(72, $student->fresh()->battery_level);
        $this->assertSame('Good', $student->fresh()->signal_status);

        $this->postJson('/api/student/offline', [
            'student_id' => $student->student_id,
        ])->assertOk();

        $this->assertFalse($student->fresh()->status);
    }

    public function test_student_sos_status_changes_resolve_active_sos_when_safe(): void
    {
        $student = $this->createStudent();

        $this->postJson('/api/student/sos', [
            'student_id' => $student->student_id,
            'sos_status' => 'help',
            'latitude' => 14.6,
            'longitude' => 121.0,
            'battery_level' => 45,
        ])->assertOk()
            ->assertJsonPath('sos_status', 'help');

        $this->assertSame('help', $student->fresh()->sos_status);
        $this->assertSame(45, $student->fresh()->battery_level);

        $alert = Notification::create([
            'student_id' => $student->id,
            'admin_id' => 1,
            'type' => 'sos',
            'sender_type' => 'student',
            'message' => 'Emergency',
            'read' => false,
            'status' => 'pending',
        ]);

        $this->postJson('/api/student/sos', [
            'student_id' => $student->student_id,
            'sos_status' => 'safe',
        ])->assertOk()
            ->assertJsonPath('sos_status', 'safe');

        $this->assertSame('safe', $student->fresh()->sos_status);
        $this->assertDatabaseHas('notifications', [
            'id' => $alert->id,
            'status' => 'resolved',
            'read' => true,
        ]);
    }

    public function test_student_login_rejects_invalid_credentials(): void
    {
        $student = $this->createStudent();
        StudentAuth::create([
            'student_id' => $student->student_id,
            'email' => $student->email,
            'password' => Hash::make('student123'),
        ]);

        $this->postJson('/api/student/login', [
            'student_id' => $student->student_id,
            'password' => 'wrong-password',
        ])->assertUnauthorized();

        $this->assertFalse($student->fresh()->status);
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
