<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Student;
use App\Models\StudentAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MobilePasswordResetApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_change_password_using_student_auth_record(): void
    {
        $student = $this->createStudent();
        $studentAuth = StudentAuth::create([
            'student_id' => $student->student_id,
            'email' => $student->email,
            'password' => Hash::make('temporary1'),
        ]);

        $this->postJson('/api/student/reset-password', [
            'student_id' => $student->student_id,
            'current_password' => 'temporary1',
            'new_password' => 'newpass123',
        ])->assertOk()
            ->assertJsonPath('success', true);

        $this->assertTrue(Hash::check('newpass123', $studentAuth->fresh()->password));
    }

    public function test_student_password_change_rejects_missing_or_incorrect_current_password(): void
    {
        $student = $this->createStudent();
        $studentAuth = StudentAuth::create([
            'student_id' => $student->student_id,
            'email' => $student->email,
            'password' => Hash::make('temporary1'),
        ]);

        $this->postJson('/api/student/reset-password', [
            'student_id' => $student->student_id,
            'new_password' => 'newpass123',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('current_password');

        $this->postJson('/api/student/reset-password', [
            'student_id' => $student->student_id,
            'current_password' => 'wrongpass',
            'new_password' => 'newpass123',
        ])->assertUnauthorized();

        $this->assertTrue(Hash::check('temporary1', $studentAuth->fresh()->password));
    }

    public function test_admin_can_change_password_and_clear_first_login_requirement(): void
    {
        $admin = Admin::create([
            'staff_id' => 'MOB001',
            'first_name' => 'Mobile',
            'last_name' => 'Admin',
            'email' => 'mobile-admin@example.com',
            'password' => Hash::make('temporary1'),
            'role' => 'education',
            'must_change_password' => true,
        ]);

        $this->postJson('/api/reset-password', [
            'staff_id' => $admin->staff_id,
            'current_password' => 'temporary1',
            'password' => 'newpass123',
        ])->assertOk()
            ->assertJsonPath('success', true);

        $admin->refresh();
        $this->assertTrue(Hash::check('newpass123', $admin->password));
        $this->assertFalse($admin->must_change_password);
        $this->assertNotNull($admin->password_changed_at);
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
