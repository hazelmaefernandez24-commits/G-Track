<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\PNUser;

class FixRoomAssignments extends Command
{
    protected $signature = 'room:fix-assignments';
    protected $description = 'Fix room assignments and sync data';

    public function handle()
    {
        $this->info('Starting room assignments fix...');
        
        try {
            // 1. Check migration status
            $migrationExists = DB::table('migrations')
                ->where('migration', '16_2025_07_21_000000_create_room_assignments_table')
                ->exists();
            
            if (!$migrationExists) {
                DB::table('migrations')->insert([
                    'migration' => '16_2025_07_21_000000_create_room_assignments_table',
                    'batch' => 4
                ]);
                $this->info('✓ Migration marked as run');
            } else {
                $this->info('✓ Migration already marked as run');
            }
            
            // 2. Check current data
            $roomCount = Room::count();
            $assignmentCount = RoomAssignment::count();
            $studentCount = PNUser::where('user_role', 'student')->where('status', 'active')->count();
            
            $this->info("Current rooms: {$roomCount}");
            $this->info("Current assignments: {$assignmentCount}");
            $this->info("Active students: {$studentCount}");
            
            // 3. Create rooms if they don't exist
            if ($roomCount == 0) {
                $this->info('Creating rooms...');
                $this->createRooms();
            }
            
            // 4. Generate room assignments if none exist
            if ($assignmentCount == 0 && $studentCount > 0) {
                $this->info('Generating room assignments...');
                $this->generateRoomAssignments();
            }
            
            $this->info('✅ Room assignments fix completed!');
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
    
    private function createRooms()
    {
        $rooms = [
            '201', '202', '203', '204', '205',
            '301', '302', '303', '304', '305',
            '401', '402', '403', '404', '405',
            '501', '502', '503', '504', '505',
            '601', '602', '603', '604', '605',
            '701', '702', '703', '704', '705'
        ];
        
        foreach ($rooms as $roomNumber) {
            Room::create([
                'room_number' => $roomNumber,
                'name' => "Room {$roomNumber}",
                'capacity' => 6,
                'status' => 'active',
                'description' => $this->getFloorDescription($roomNumber)
            ]);
        }
        
        $this->info('✓ Created ' . count($rooms) . ' rooms');
    }
    
    private function getFloorDescription($roomNumber)
    {
        $floor = intval(substr($roomNumber, 0, 1));
        $descriptions = [
            2 => 'Second floor room for students',
            3 => 'Third floor room for students',
            4 => 'Fourth floor room for students',
            5 => 'Fifth floor room for students',
            6 => 'Sixth floor room for students',
            7 => 'Seventh floor room for students'
        ];
        
        return $descriptions[$floor] ?? 'Student dormitory room';
    }
    
    private function generateRoomAssignments()
    {
        // Get all active students
        $students = PNUser::where('user_role', 'student')
            ->where('status', 'active')
            ->get(['user_id', 'user_fname', 'user_lname', 'gender']);
        
        if ($students->isEmpty()) {
            $this->warn('No active students found');
            return;
        }
        
        // Separate by gender
        $maleStudents = $students->where('gender', 'M')->values();
        $femaleStudents = $students->where('gender', 'F')->values();
        
        // Sort deterministically for consistent distribution
        $maleStudents = $maleStudents->sortBy('user_fname');
        $femaleStudents = $femaleStudents->sortBy('user_fname');
        
        // Define rooms with students (matching dashboard logic)
        $rooms = [
            '202', '204', '205', // Floor 2
            '302', '304', '305', // Floor 3
            '402', '403', '404', '405', // Floor 4
            '502', '504', '505', // Floor 5
            '601', '602', '603', '604', '605', // Floor 6
            '701', '702', '703', '704', '705'  // Floor 7
        ];
        
        $capacity = 6;
        $assignments = [];
        $roomIndex = 0;
        
        // Assign male students first
        $maleIndex = 0;
        while ($maleIndex < $maleStudents->count() && $roomIndex < count($rooms)) {
            $room = $rooms[$roomIndex];
            
            // Fill room with male students (up to capacity)
            for ($i = 0; $i < $capacity && $maleIndex < $maleStudents->count(); $i++) {
                $student = $maleStudents[$maleIndex];
                $assignments[] = [
                    'room_number' => $room,
                    'student_id' => $student->user_id,
                    'student_name' => $student->user_fname . ' ' . $student->user_lname,
                    'student_gender' => $student->gender,
                    'assignment_order' => $i,
                    'room_capacity' => $capacity,
                    'assigned_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                $maleIndex++;
            }
            $roomIndex++;
        }
        
        // Assign female students to remaining rooms
        $femaleIndex = 0;
        while ($femaleIndex < $femaleStudents->count() && $roomIndex < count($rooms)) {
            $room = $rooms[$roomIndex];
            
            // Fill room with female students (up to capacity)
            for ($i = 0; $i < $capacity && $femaleIndex < $femaleStudents->count(); $i++) {
                $student = $femaleStudents[$femaleIndex];
                $assignments[] = [
                    'room_number' => $room,
                    'student_id' => $student->user_id,
                    'student_name' => $student->user_fname . ' ' . $student->user_lname,
                    'student_gender' => $student->gender,
                    'assignment_order' => $i,
                    'room_capacity' => $capacity,
                    'assigned_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                $femaleIndex++;
            }
            $roomIndex++;
        }
        
        // Bulk insert assignments
        if (!empty($assignments)) {
            RoomAssignment::insert($assignments);
            $this->info('✓ Created ' . count($assignments) . ' room assignments');
            $this->info('✓ Male students assigned: ' . $maleIndex);
            $this->info('✓ Female students assigned: ' . $femaleIndex);
        }
    }
}
