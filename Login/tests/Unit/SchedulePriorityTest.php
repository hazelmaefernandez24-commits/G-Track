<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SchedulePriorityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that unique leisure schedule has priority over gender-based schedule
     */
    public function test_unique_leisure_schedule_has_priority_over_gender_schedule()
    {
        // Set up test data - simulate Tuesday
        Carbon::setTestNow(Carbon::parse('2025-01-14 06:30:00')); // Tuesday 6:30 AM
        
        $studentId = '2025010011C1';
        $gender = 'Male';
        
        // Create a gender-based schedule (going_out type) - Male schedule with 8:39 AM logout
        Schedule::create([
            'student_id' => null,
            'gender' => $gender,
            'batch' => null,
            'pn_group' => null,
            'day_of_week' => 'Tuesday',
            'schedule_type' => 'going_out',
            'time_out' => '08:39:00',
            'time_in' => '18:39:00',
            'schedule_name' => 'Male Going Out Schedule',
            'start_date' => null,
            'end_date' => null,
            'is_batch_schedule' => false,
            'valid_until' => null,
            'grace_period_logout_minutes' => 30,
            'grace_period_login_minutes' => 30,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'is_deleted' => false
        ]);
        
        // Create a unique leisure schedule for the specific student - 6:39 AM logout, 6:39 PM login
        Schedule::create([
            'student_id' => $studentId,
            'gender' => null,
            'batch' => null,
            'pn_group' => null,
            'day_of_week' => 'Tuesday',
            'schedule_type' => 'unique_leisure',
            'time_out' => '06:39:00',
            'time_in' => '18:39:00',
            'schedule_name' => 'Unique Leisure - Tuesday',
            'start_date' => null,
            'end_date' => null,
            'is_batch_schedule' => false,
            'valid_until' => Carbon::today()->addDays(1)->format('Y-m-d'), // Valid until tomorrow
            'grace_period_logout_minutes' => null,
            'grace_period_login_minutes' => null,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'is_deleted' => false
        ]);
        
        // Test the schedule retrieval logic (same as in LeisureLogController)
        $schedule = Schedule::getIrregLeisureSchedule($studentId);
        
        if (!$schedule) {
            $schedule = Schedule::getRegLeisureSchedule($gender);
        }
        
        // Assert that we got the unique leisure schedule (not the gender-based one)
        $this->assertNotNull($schedule, 'Should find a schedule');
        $this->assertEquals('unique_leisure', $schedule->schedule_type, 'Should prioritize unique leisure schedule');
        $this->assertEquals($studentId, $schedule->student_id, 'Should be the student-specific schedule');
        $this->assertEquals('06:39:00', $schedule->time_out, 'Should have the unique leisure logout time');
        
        // Verify that at 6:30 AM, the student should be able to log out (within grace period)
        $currentTime = Carbon::now(); // 6:30 AM
        $scheduledTimeOut = Carbon::parse($schedule->time_out); // 6:39 AM
        $graceTime = $schedule->grace_period_logout_minutes ?? 0; // null for unique leisure
        
        // For unique leisure schedules, if grace_period_logout_minutes is null, 
        // we should allow logout at the exact time or after
        $canLogout = $currentTime->gte($scheduledTimeOut->copy()->subMinutes($graceTime));
        
        $this->assertTrue($canLogout, 'Student should be able to log out at 6:30 AM for 6:39 AM schedule');
    }
    
    /**
     * Test that gender-based schedule is used when no unique leisure schedule exists
     */
    public function test_gender_schedule_used_when_no_unique_leisure_schedule()
    {
        // Set up test data - simulate Tuesday
        Carbon::setTestNow(Carbon::parse('2025-01-14 08:30:00')); // Tuesday 8:30 AM
        
        $studentId = '2025010011C1';
        $gender = 'Male';
        
        // Create only a gender-based schedule (no unique leisure schedule)
        Schedule::create([
            'student_id' => null,
            'gender' => $gender,
            'batch' => null,
            'pn_group' => null,
            'day_of_week' => 'Tuesday',
            'schedule_type' => 'going_out',
            'time_out' => '08:39:00',
            'time_in' => '18:39:00',
            'schedule_name' => 'Male Going Out Schedule',
            'start_date' => null,
            'end_date' => null,
            'is_batch_schedule' => false,
            'valid_until' => null,
            'grace_period_logout_minutes' => 30,
            'grace_period_login_minutes' => 30,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'is_deleted' => false
        ]);
        
        // Test the schedule retrieval logic
        $schedule = Schedule::getIrregLeisureSchedule($studentId);
        
        if (!$schedule) {
            $schedule = Schedule::getRegLeisureSchedule($gender);
        }
        
        // Assert that we got the gender-based schedule
        $this->assertNotNull($schedule, 'Should find a schedule');
        $this->assertEquals('going_out', $schedule->schedule_type, 'Should use gender-based schedule');
        $this->assertEquals($gender, $schedule->gender, 'Should be the gender-based schedule');
        $this->assertEquals('08:39:00', $schedule->time_out, 'Should have the gender-based logout time');
    }
    
    /**
     * Test that expired unique leisure schedules are not returned
     */
    public function test_expired_unique_leisure_schedule_not_returned()
    {
        // Set up test data - simulate Tuesday
        Carbon::setTestNow(Carbon::parse('2025-01-14 06:30:00')); // Tuesday 6:30 AM
        
        $studentId = '2025010011C1';
        $gender = 'Male';
        
        // Create a gender-based schedule
        Schedule::create([
            'student_id' => null,
            'gender' => $gender,
            'batch' => null,
            'pn_group' => null,
            'day_of_week' => 'Tuesday',
            'schedule_type' => 'going_out',
            'time_out' => '08:39:00',
            'time_in' => '18:39:00',
            'schedule_name' => 'Male Going Out Schedule',
            'start_date' => null,
            'end_date' => null,
            'is_batch_schedule' => false,
            'valid_until' => null,
            'grace_period_logout_minutes' => 30,
            'grace_period_login_minutes' => 30,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'is_deleted' => false
        ]);
        
        // Create an EXPIRED unique leisure schedule
        Schedule::create([
            'student_id' => $studentId,
            'gender' => null,
            'batch' => null,
            'pn_group' => null,
            'day_of_week' => 'Tuesday',
            'schedule_type' => 'unique_leisure',
            'time_out' => '06:39:00',
            'time_in' => '18:39:00',
            'schedule_name' => 'Unique Leisure - Tuesday',
            'start_date' => null,
            'end_date' => null,
            'is_batch_schedule' => false,
            'valid_until' => Carbon::yesterday()->format('Y-m-d'), // Expired yesterday
            'grace_period_logout_minutes' => null,
            'grace_period_login_minutes' => null,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'is_deleted' => false
        ]);
        
        // Test the schedule retrieval logic
        $schedule = Schedule::getIrregLeisureSchedule($studentId);
        
        if (!$schedule) {
            $schedule = Schedule::getRegLeisureSchedule($gender);
        }
        
        // Assert that we got the gender-based schedule (not the expired unique leisure)
        $this->assertNotNull($schedule, 'Should find a schedule');
        $this->assertEquals('going_out', $schedule->schedule_type, 'Should fall back to gender-based schedule');
        $this->assertEquals($gender, $schedule->gender, 'Should be the gender-based schedule');
        $this->assertEquals('08:39:00', $schedule->time_out, 'Should have the gender-based logout time');
    }
}
