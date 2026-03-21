<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\DynamicTaskCategory;
use App\Models\DynamicTaskAssignment;
use App\Models\DynamicTaskMember;
use PN_Systems\Login\app\Models\PNUser;

class DynamicTaskTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminUser;
    protected $studentUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users
        $this->adminUser = PNUser::factory()->create([
            'user_role' => 'admin',
            'status' => 'active'
        ]);
        
        $this->studentUser = PNUser::factory()->create([
            'user_role' => 'student',
            'status' => 'active'
        ]);
    }

    /** @test */
    public function admin_can_create_category()
    {
        $this->actingAs($this->adminUser);

        $categoryData = [
            'name' => 'Test Category',
            'description' => 'Test Description',
            'color_code' => '#ff0000',
            'max_students' => 10,
            'max_boys' => 5,
            'max_girls' => 5
        ];

        $response = $this->postJson('/dynamic-tasks/categories', $categoryData);

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertDatabaseHas('dynamic_task_categories', [
            'name' => 'Test Category',
            'description' => 'Test Description'
        ]);
    }

    /** @test */
    public function non_admin_cannot_create_category()
    {
        $this->actingAs($this->studentUser);

        $categoryData = [
            'name' => 'Test Category',
            'description' => 'Test Description'
        ];

        $response = $this->postJson('/dynamic-tasks/categories', $categoryData);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_update_category()
    {
        $this->actingAs($this->adminUser);

        $category = DynamicTaskCategory::create([
            'name' => 'Original Name',
            'description' => 'Original Description',
            'color_code' => '#000000',
            'is_active' => true,
            'sort_order' => 1
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'description' => 'Updated Description',
            'color_code' => '#ffffff'
        ];

        $response = $this->putJson("/dynamic-tasks/categories/{$category->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertDatabaseHas('dynamic_task_categories', [
            'id' => $category->id,
            'name' => 'Updated Name',
            'description' => 'Updated Description'
        ]);
    }

    /** @test */
    public function admin_can_delete_category_without_assignments()
    {
        $this->actingAs($this->adminUser);

        $category = DynamicTaskCategory::create([
            'name' => 'Test Category',
            'is_active' => true,
            'sort_order' => 1
        ]);

        $response = $this->deleteJson("/dynamic-tasks/categories/{$category->id}");

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('dynamic_task_categories', [
            'id' => $category->id
        ]);
    }

    /** @test */
    public function admin_can_create_assignment()
    {
        $this->actingAs($this->adminUser);

        $category = DynamicTaskCategory::create([
            'name' => 'Test Category',
            'is_active' => true,
            'sort_order' => 1
        ]);

        $students = PNUser::factory()->count(3)->create([
            'user_role' => 'student',
            'status' => 'active'
        ]);

        $assignmentData = [
            'category_id' => $category->id,
            'assignment_name' => 'Test Assignment',
            'description' => 'Test Description',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addWeek()->toDateString(),
            'student_ids' => $students->pluck('user_id')->toArray(),
            'coordinators' => [$students->first()->user_id]
        ];

        $response = $this->postJson('/dynamic-tasks/assignments', $assignmentData);

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertDatabaseHas('dynamic_task_assignments', [
            'category_id' => $category->id,
            'assignment_name' => 'Test Assignment'
        ]);

        $this->assertDatabaseHas('dynamic_task_members', [
            'student_id' => $students->first()->user_id,
            'is_coordinator' => true
        ]);
    }

    /** @test */
    public function category_shows_correct_student_counts()
    {
        $category = DynamicTaskCategory::create([
            'name' => 'Test Category',
            'is_active' => true,
            'sort_order' => 1
        ]);

        $assignment = DynamicTaskAssignment::create([
            'category_id' => $category->id,
            'assignment_name' => 'Test Assignment',
            'start_date' => now(),
            'end_date' => now()->addWeek(),
            'status' => 'current',
            'created_by' => $this->adminUser->user_id
        ]);

        // Create male and female students
        $maleStudents = PNUser::factory()->count(2)->create([
            'user_role' => 'student',
            'status' => 'active',
            'gender' => 'M'
        ]);

        $femaleStudents = PNUser::factory()->count(3)->create([
            'user_role' => 'student',
            'status' => 'active',
            'gender' => 'F'
        ]);

        // Assign students to the assignment
        foreach ($maleStudents as $student) {
            DynamicTaskMember::create([
                'assignment_id' => $assignment->id,
                'student_id' => $student->user_id,
                'assigned_by' => $this->adminUser->user_id
            ]);
        }

        foreach ($femaleStudents as $student) {
            DynamicTaskMember::create([
                'assignment_id' => $assignment->id,
                'student_id' => $student->user_id,
                'assigned_by' => $this->adminUser->user_id
            ]);
        }

        $counts = $category->getCurrentStudentCounts();

        $this->assertEquals(2, $counts['boys']);
        $this->assertEquals(3, $counts['girls']);
        $this->assertEquals(5, $counts['total']);
    }

    /** @test */
    public function validation_prevents_invalid_category_data()
    {
        $this->actingAs($this->adminUser);

        $invalidData = [
            'name' => '', // Required field empty
            'color_code' => 'invalid-color', // Invalid color format
            'max_students' => -1 // Invalid number
        ];

        $response = $this->postJson('/dynamic-tasks/categories', $invalidData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'color_code', 'max_students']);
    }
}
