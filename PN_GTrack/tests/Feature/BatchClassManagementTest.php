<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\BatchClass;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BatchClassManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_main_admin_can_create_and_rename_a_class_with_enrolled_records(): void
    {
        $admin = $this->createMainAdmin();

        $this->actingAs($admin, 'admin')
            ->post(route('classes.store'), ['name' => 'Grade 9'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('student_classes', ['name' => 'Grade 9']);

        $class = BatchClass::where('name', '2026')->firstOrFail();
        BatchClass::where('name', '2027')->delete();
        $student = Student::create([
            'student_id' => 'STU2026001',
            'name' => 'Jamie Student',
            'first_name' => 'Jamie',
            'last_name' => 'Student',
            'email' => 'jamie@example.com',
            'class' => '2026',
            'gender' => 'Female',
            'contact' => '09123456789',
        ]);
        $notificationId = DB::table('notifications')->insertGetId([
            'student_id' => $student->id,
            'class' => '2026',
            'type' => 'broadcast',
            'message' => 'Class update',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->put(route('classes.update', $class), ['name' => '2027'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('student_classes', ['id' => $class->id, 'name' => '2027']);
        $this->assertDatabaseHas('students', ['id' => $student->id, 'class' => '2027']);
        $this->assertDatabaseHas('notifications', ['id' => $notificationId, 'class' => '2027']);

        $newClass = BatchClass::where('name', 'Grade 9')->firstOrFail();
        $this->delete(route('classes.destroy', $newClass))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('student_classes', ['id' => $newClass->id]);
    }

    public function test_class_cannot_be_deleted_while_students_are_enrolled(): void
    {
        $admin = $this->createMainAdmin();
        $class = BatchClass::where('name', '2026')->firstOrFail();

        Student::create([
            'student_id' => 'STU2026001',
            'name' => 'Jamie Student',
            'first_name' => 'Jamie',
            'last_name' => 'Student',
            'email' => 'jamie@example.com',
            'class' => '2026',
            'gender' => 'Female',
            'contact' => '09123456789',
        ]);

        $this->actingAs($admin, 'admin')
            ->delete(route('classes.destroy', $class))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('student_classes', ['id' => $class->id]);
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
