<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Student;
use App\Models\StudentAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentManagementFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_main_admin_can_create_update_and_delete_a_student_account(): void
    {
        $admin = $this->createAdmin('main');

        $this->actingAs($admin, 'admin')
            ->post(route('students.store'), [
                'first_name' => 'Jamie',
                'middle_initial' => 'R',
                'last_name' => 'Student',
                'email' => 'jamie@example.com',
                'class' => '2026',
                'gender' => 'Female',
                'contact' => '09123456789',
                'password' => 'student123',
                'password_confirmation' => 'student123',
            ])
            ->assertSessionHas('success');

        $student = Student::where('email', 'jamie@example.com')->firstOrFail();
        $auth = StudentAuth::where('email', 'jamie@example.com')->firstOrFail();

        $this->assertSame('Jamie R. Student', $student->name);
        $this->assertStringStartsWith('STU2026', $student->student_id);
        $this->assertSame($student->student_id, $auth->student_id);
        $this->assertTrue(Hash::check('student123', $auth->password));

        $this->put(route('students.update', $student), [
            'first_name' => 'Jamie',
            'middle_initial' => '',
            'last_name' => 'Updated',
            'email' => 'jamie.updated@example.com',
            'class' => '2027',
            'gender' => 'Female',
            'contact' => '09987654321',
        ])->assertSessionHas('success');

        $student->refresh();
        $this->assertSame('Jamie Updated', $student->name);
        $this->assertSame('2027', $student->class);
        $this->assertSame('jamie.updated@example.com', $student->email);
        $this->assertSame('jamie.updated@example.com', $auth->fresh()->email);

        $this->delete(route('students.destroy', $student))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('students', ['id' => $student->id]);
        $this->assertDatabaseMissing('student_auths', ['student_id' => $student->student_id]);
    }

    public function test_education_staff_cannot_access_student_management_routes(): void
    {
        $staff = $this->createAdmin('education');

        $this->actingAs($staff, 'admin')
            ->get(route('students.index'))
            ->assertForbidden();
    }

    public function test_deleted_student_id_is_reused_when_adding_another_student_to_the_same_class(): void
    {
        $admin = $this->createAdmin('main');

        $this->actingAs($admin, 'admin');

        foreach (['first@example.com', 'second@example.com'] as $email) {
            $this->post(route('students.store'), [
                'first_name' => 'Test',
                'middle_initial' => '',
                'last_name' => 'Student',
                'email' => $email,
                'class' => '2026',
                'gender' => 'Female',
                'contact' => '09123456789',
                'password' => 'student123',
                'password_confirmation' => 'student123',
            ])->assertSessionHas('success');
        }

        $deletedStudent = Student::where('email', 'second@example.com')->firstOrFail();
        $reusableStudentId = $deletedStudent->student_id;
        $this->delete(route('students.destroy', $deletedStudent))
            ->assertSessionHas('success');

        $this->post(route('students.store'), [
            'first_name' => 'Replacement',
            'middle_initial' => '',
            'last_name' => 'Student',
            'email' => 'replacement@example.com',
            'class' => '2026',
            'gender' => 'Female',
            'contact' => '09987654321',
            'password' => 'student123',
            'password_confirmation' => 'student123',
        ])->assertSessionHas('success');

        $this->assertSame(
            $reusableStudentId,
            Student::where('email', 'replacement@example.com')->value('student_id')
        );
    }

    private function createAdmin(string $role): Admin
    {
        return Admin::create([
            'staff_id' => strtoupper($role).'001',
            'first_name' => 'Test',
            'last_name' => 'Admin',
            'email' => $role.'-'.uniqid().'@example.com',
            'password' => Hash::make('password123'),
            'role' => $role,
        ]);
    }
}
