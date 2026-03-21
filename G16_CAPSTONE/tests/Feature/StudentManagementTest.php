<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\PNUser;
use App\Services\StudentValidationService;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test room
        Room::create([
            'room_number' => '201',
            'name' => 'Test Room 201',
            'capacity' => 6,
            'status' => 'active'
        ]);

        // Create test students
        PNUser::create([
            'user_id' => 'test001',
            'user_fname' => 'John',
            'user_lname' => 'Doe',
            'gender' => 'M',
            'user_email' => 'john@test.com',
            'user_role' => 'student',
            'status' => 'active',
            'user_password' => bcrypt('password')
        ]);

        PNUser::create([
            'user_id' => 'test002',
            'user_fname' => 'Jane',
            'user_lname' => 'Smith',
            'gender' => 'F',
            'user_email' => 'jane@test.com',
            'user_role' => 'student',
            'status' => 'active',
            'user_password' => bcrypt('password')
        ]);
    }

    /** @test */
    public function it_can_add_student_to_room()
    {
        $response = $this->postJson('/api/room/add-student', [
            'room' => '201',
            'name' => 'John Doe'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Student added successfully.'
                ]);

        $this->assertDatabaseHas('room_assignments', [
            'room_number' => '201',
            'student_name' => 'John Doe',
            'student_gender' => 'M'
        ]);
    }

    /** @test */
    public function it_prevents_adding_non_existent_student()
    {
        $response = $this->postJson('/api/room/add-student', [
            'room' => '201',
            'name' => 'Non Existent Student'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => false,
                    'message' => 'Student not found in the system. Only students from the Login database can be assigned to rooms.'
                ]);
    }

    /** @test */
    public function it_prevents_gender_mismatch_in_room()
    {
        // Add male student first
        $this->postJson('/api/room/add-student', [
            'room' => '201',
            'name' => 'John Doe'
        ]);

        // Try to add female student to same room
        $response = $this->postJson('/api/room/add-student', [
            'room' => '201',
            'name' => 'Jane Smith'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => false
                ])
                ->assertJsonFragment([
                    'message' => 'Gender mismatch. This room is assigned to male students only.'
                ]);
    }

    /** @test */
    public function it_can_edit_student_assignment()
    {
        // Add initial student
        $this->postJson('/api/room/add-student', [
            'room' => '201',
            'name' => 'John Doe'
        ]);

        // Create another male student for replacement
        PNUser::create([
            'user_id' => 'test003',
            'user_fname' => 'Mike',
            'user_lname' => 'Johnson',
            'gender' => 'M',
            'user_email' => 'mike@test.com',
            'user_role' => 'student',
            'status' => 'active',
            'user_password' => bcrypt('password')
        ]);

        // Edit student assignment
        $response = $this->postJson('/api/room/edit-student', [
            'room' => '201',
            'old_name' => 'John Doe',
            'new_name' => 'Mike Johnson'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Student assignment updated successfully.'
                ]);

        $this->assertDatabaseHas('room_assignments', [
            'room_number' => '201',
            'student_name' => 'Mike Johnson'
        ]);

        $this->assertDatabaseMissing('room_assignments', [
            'room_number' => '201',
            'student_name' => 'John Doe'
        ]);
    }

    /** @test */
    public function it_can_remove_student_from_room()
    {
        // Add student first
        $this->postJson('/api/room/add-student', [
            'room' => '201',
            'name' => 'John Doe'
        ]);

        // Remove student
        $response = $this->postJson('/api/room/delete-student', [
            'room' => '201',
            'name' => 'John Doe'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ])
                ->assertJsonFragment([
                    'message' => 'Student \'John Doe\' removed successfully from room 201.'
                ]);

        $this->assertDatabaseMissing('room_assignments', [
            'room_number' => '201',
            'student_name' => 'John Doe'
        ]);
    }

    /** @test */
    public function it_prevents_exceeding_room_capacity()
    {
        // Fill room to capacity (6 students)
        for ($i = 1; $i <= 6; $i++) {
            PNUser::create([
                'user_id' => "test00{$i}",
                'user_fname' => "Student{$i}",
                'user_lname' => 'Test',
                'gender' => 'M',
                'user_email' => "student{$i}@test.com",
                'user_role' => 'student',
                'status' => 'active',
                'user_password' => bcrypt('password')
            ]);

            $this->postJson('/api/room/add-student', [
                'room' => '201',
                'name' => "Student{$i} Test"
            ]);
        }

        // Try to add 7th student
        PNUser::create([
            'user_id' => 'test007',
            'user_fname' => 'Student7',
            'user_lname' => 'Test',
            'gender' => 'M',
            'user_email' => 'student7@test.com',
            'user_role' => 'student',
            'status' => 'active',
            'user_password' => bcrypt('password')
        ]);

        $response = $this->postJson('/api/room/add-student', [
            'room' => '201',
            'name' => 'Student7 Test'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => false,
                    'message' => 'Room is at full capacity. Cannot add more students.'
                ]);
    }

    /** @test */
    public function student_validation_service_works_correctly()
    {
        // Test student exists validation
        $result = StudentValidationService::validateStudentExists('John Doe');
        $this->assertTrue($result['valid']);
        $this->assertNotNull($result['student']);

        // Test non-existent student
        $result = StudentValidationService::validateStudentExists('Non Existent');
        $this->assertFalse($result['valid']);

        // Test room assignment validation
        $result = StudentValidationService::validateRoomAssignment('201', 'test001', 'M');
        $this->assertTrue($result['valid']);

        // Test get all valid students
        $students = StudentValidationService::getAllValidStudents();
        $this->assertCount(2, $students);
        $this->assertEquals('John Doe', $students[0]['name']);
    }

    /** @test */
    public function it_can_get_valid_students_list()
    {
        $response = $this->getJson('/api/students/valid-list');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'total_count' => 2
                ])
                ->assertJsonStructure([
                    'success',
                    'students' => [
                        '*' => ['id', 'name', 'gender', 'batch']
                    ],
                    'total_count'
                ]);
    }
}
