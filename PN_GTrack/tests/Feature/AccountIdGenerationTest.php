<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Student;
use App\Models\StudentAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AccountIdGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_ids_are_generated_from_class_and_unique_record_id(): void
    {
        $this->withoutMiddleware();
        DB::table('student_classes')->insertOrIgnore([
            'name' => '2026',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentInput = [
            'student_id' => 'MANUAL-ID',
            'first_name' => 'Jamie',
            'last_name' => 'Student',
            'email' => 'jamie@example.com',
            'class' => '2026',
            'gender' => 'Female',
            'contact' => '09123456789',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $this->post('/admin/students', $studentInput)->assertRedirect();
        $this->post('/admin/students', array_merge($studentInput, [
            'email' => 'jamie2@example.com',
        ]))->assertRedirect();

        $student = Student::where('email', 'jamie@example.com')->firstOrFail();
        $secondStudent = Student::where('email', 'jamie2@example.com')->firstOrFail();
        $this->assertMatchesRegularExpression('/^STU2026\d{3,}$/', $student->student_id);
        $this->assertNotSame('MANUAL-ID', $student->student_id);
        $this->assertNotSame($student->student_id, $secondStudent->student_id);
        $this->assertSame($student->student_id, StudentAuth::where('email', $student->email)->value('student_id'));

        $this->put('/admin/students/' . $student->id, [
            'student_id' => 'CHANGED-ID',
            'first_name' => 'Jamie',
            'last_name' => 'Updated',
            'email' => $student->email,
            'class' => '2026',
            'gender' => 'Female',
            'contact' => '09123456789',
        ])->assertRedirect();

        $this->assertSame($student->student_id, $student->fresh()->student_id);
    }

    public function test_admin_ids_are_generated_from_role_and_unique_record_id(): void
    {
        $this->withoutMiddleware();

        $accounts = [
            ['education', 'EDU', 'education'],
            ['education-copy', 'EDU', 'education'],
            ['main', 'MAIN', 'main'],
        ];

        foreach ($accounts as [$emailPrefix, $prefix, $role]) {
            $this->post('/admin/admins', [
                'staff_id' => 'MANUAL-ID',
                'first_name' => 'Jamie',
                'last_name' => 'Admin',
                'email' => $emailPrefix . '@example.com',
                'role' => $role,
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
            ])->assertRedirect();

            $admin = Admin::where('email', $emailPrefix . '@example.com')->firstOrFail();
            $this->assertMatchesRegularExpression('/^' . $prefix . '\d{3,}$/', $admin->staff_id);
            $this->assertNotSame('MANUAL-ID', $admin->staff_id);
        }

        $this->assertSame(3, Admin::query()->count());
        $this->assertSame(3, Admin::query()->distinct('staff_id')->count('staff_id'));

        $admin = Admin::where('email', 'education@example.com')->firstOrFail();
        $this->put('/admin/admins/' . $admin->id, [
            'staff_id' => 'CHANGED-ID',
            'first_name' => 'Jamie',
            'last_name' => 'Updated',
            'email' => $admin->email,
            'role' => 'main',
        ])->assertRedirect();

        $this->assertSame('EDU' . str_pad((string) $admin->id, 3, '0', STR_PAD_LEFT), $admin->fresh()->staff_id);
    }
}
