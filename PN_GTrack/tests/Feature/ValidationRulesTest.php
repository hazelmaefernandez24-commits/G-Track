<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ValidationRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_creation_rejects_invalid_name_contact_and_email(): void
    {
        $admin = Admin::create([
            'staff_id' => 'MAIN001',
            'first_name' => 'Main',
            'last_name' => 'Admin',
            'email' => 'main@example.com',
            'password' => Hash::make('password123'),
            'role' => 'main',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->from('/admin/students')
            ->post('/admin/students', [
            'student_id' => 'STU-001',
            'first_name' => 'John1',
            'middle_initial' => '1',
            'last_name' => 'Doe2',
            'email' => 'not-an-email',
            'class' => '2026',
            'gender' => 'Male',
            'contact' => '12345',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertSessionHasErrors(['first_name', 'last_name', 'middle_initial', 'email', 'contact']);
    }
}
