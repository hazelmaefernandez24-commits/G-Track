<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Student;
use App\Models\StudentAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentCsvImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_main_admin_can_import_valid_students_and_sees_skipped_rows(): void
    {
        $admin = $this->createMainAdmin();
        $csv = implode("\r\n", [
            'first_name,middle_initial,last_name,email,class,gender,contact,password',
            'Jamie,R,Student,jamie@example.com,2026,Female,09123456789,student123',
            'Invalid,,Student,invalid@example.com,2026,Female,123,student123',
            'Another,,Student,jamie@example.com,2026,Male,09987654321,student123',
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('students.import'), [
                'students_csv' => UploadedFile::fake()->createWithContent('students.csv', $csv),
            ])
            ->assertRedirect(route('students.index'))
            ->assertSessionHas('success', 'Successfully added 1 student(s).')
            ->assertSessionHas('csv_import.imported', 1)
            ->assertSessionHas('csv_import.skipped', 2);

        $this->get(route('students.index'))
            ->assertSee('Successfully added 1 student(s)')
            ->assertSee('Skipped 2 row(s) during CSV upload.')
            ->assertSee('The contact field must be 11 digits.');

        $student = Student::where('email', 'jamie@example.com')->firstOrFail();
        $studentAuth = StudentAuth::where('email', 'jamie@example.com')->firstOrFail();

        $this->assertSame('Jamie R. Student', $student->name);
        $this->assertStringStartsWith('STU2026', $student->student_id);
        $this->assertSame($student->student_id, $studentAuth->student_id);
        $this->assertTrue(Hash::check('student123', $studentAuth->password));
        $this->assertSame(1, Student::count());
    }

    private function createMainAdmin(): Admin
    {
        return Admin::create([
            'staff_id' => 'MAIN001',
            'first_name' => 'Main',
            'last_name' => 'Admin',
            'email' => 'main@example.com',
            'password' => Hash::make('password123'),
            'role' => 'main',
        ]);
    }
}
