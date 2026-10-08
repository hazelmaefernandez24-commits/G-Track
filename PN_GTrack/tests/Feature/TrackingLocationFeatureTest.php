<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingLocationFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_location_update_stores_history_and_tracking_api_returns_latest_location(): void
    {
        $student = $this->createStudent();

        $this->postJson('/api/location', [
            'student_id' => $student->student_id,
            'latitude' => 14.5995,
            'longitude' => 120.9842,
            'battery_level' => 81,
            'sos_status' => 'safe',
        ])->assertOk()
            ->assertJsonPath('message', 'Location updated successfully');

        $this->assertDatabaseHas('locations', [
            'student_id' => $student->id,
            'latitude' => '14.599500',
            'longitude' => '120.984200',
        ]);
        $this->assertTrue($student->fresh()->status);
        $this->assertSame(81, $student->fresh()->battery_level);

        $this->getJson('/api/location/all?class=2026')
            ->assertOk()
            ->assertJsonPath('0.student.student_id', $student->student_id)
            ->assertJsonPath('0.latitude', 14.5995)
            ->assertJsonPath('0.longitude', 120.9842);

        $this->getJson('/api/location/all?class=2027')
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_location_endpoint_rejects_out_of_range_coordinates(): void
    {
        $student = $this->createStudent();

        $this->postJson('/api/location', [
            'student_id' => $student->student_id,
            'latitude' => 95,
            'longitude' => 200,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['latitude', 'longitude']);

        $this->assertSame(0, Location::count());
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
