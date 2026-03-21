<?php

namespace App\Services;

use App\Models\PNUser;
use App\Models\StudentDetail;
use App\Models\RoomAssignment;
use App\Models\Room;
use Illuminate\Support\Facades\Log;

class StudentValidationService
{
    /**
     * Validate if a student exists in the Login database
     *
     * @param string $studentName
     * @return array
     */
    public static function validateStudentExists($studentName)
    {
        // Trim and normalize the student name
        $studentName = trim($studentName);

        if (empty($studentName)) {
            return [
                'valid' => false,
                'message' => 'Student name cannot be empty.',
                'student' => null
            ];
        }

        // Try exact match first
        $student = PNUser::where('user_role', 'student')
            ->where('status', 'active')
            ->whereRaw("CONCAT(user_fname, ' ', user_lname) = ?", [$studentName])
            ->first();

        // If no exact match, try case-insensitive search
        if (!$student) {
            $student = PNUser::where('user_role', 'student')
                ->where('status', 'active')
                ->whereRaw("LOWER(CONCAT(user_fname, ' ', user_lname)) = LOWER(?)", [$studentName])
                ->first();
        }

        if (!$student) {
            // Log the failed validation for debugging
            Log::info('Student validation failed', [
                'searched_name' => $studentName,
                'available_students' => PNUser::where('user_role', 'student')
                    ->where('status', 'active')
                    ->selectRaw("CONCAT(user_fname, ' ', user_lname) as full_name")
                    ->pluck('full_name')
                    ->take(10)
                    ->toArray()
            ]);

            return [
                'valid' => false,
                'message' => "Student '{$studentName}' not found in the Login database. Only registered students can be assigned to rooms. Please check the spelling or contact an administrator to add the student.",
                'student' => null,
                'suggestions' => self::getSimilarStudentNames($studentName)
            ];
        }

        return [
            'valid' => true,
            'message' => 'Student found in Login database.',
            'student' => $student
        ];
    }

    /**
     * Get similar student names for suggestions
     *
     * @param string $searchName
     * @param int $limit
     * @return array
     */
    public static function getSimilarStudentNames($searchName, $limit = 5)
    {
        $searchName = trim($searchName);

        if (empty($searchName)) {
            return [];
        }

        // Get students with similar names using LIKE search
        $similarStudents = PNUser::where('user_role', 'student')
            ->where('status', 'active')
            ->where(function ($query) use ($searchName) {
                $query->where('user_fname', 'LIKE', "%{$searchName}%")
                      ->orWhere('user_lname', 'LIKE', "%{$searchName}%")
                      ->orWhereRaw("CONCAT(user_fname, ' ', user_lname) LIKE ?", ["%{$searchName}%"]);
            })
            ->selectRaw("CONCAT(user_fname, ' ', user_lname) as full_name")
            ->limit($limit)
            ->pluck('full_name')
            ->toArray();

        return $similarStudents;
    }

    /**
     * Get all valid students from the Login database
     *
     * @return array
     */
    public static function getAllValidStudents()
    {
        try {
            $students = PNUser::where('user_role', 'student')
                ->where('status', 'active')
                ->select('user_id', 'user_fname', 'user_lname', 'gender')
                ->orderBy('user_fname')
                ->orderBy('user_lname')
                ->get()
                ->map(function ($student) {
                    return [
                        'id' => $student->user_id,
                        'name' => $student->user_fname . ' ' . $student->user_lname,
                        'gender' => $student->gender,
                        'batch' => self::getStudentBatch($student->user_id)
                    ];
                })
                ->toArray();

            return $students;
        } catch (\Exception $e) {
            Log::error('Error fetching valid students', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Get student batch information
     *
     * @param string $userId
     * @return string
     */
    public static function getStudentBatch($userId)
    {
        $studentDetail = StudentDetail::where('user_id', $userId)->first();
        return $studentDetail ? $studentDetail->batch : '2025'; // Default to 2025
    }

    /**
     * Validate room assignment constraints
     *
     * @param string $roomNumber
     * @param string $studentId
     * @param string $studentGender
     * @param string|null $excludeAssignmentId
     * @return array
     */
    public static function validateRoomAssignment($roomNumber, $studentId, $studentGender, $excludeAssignmentId = null)
    {
        try {
            // Find the room
            $room = Room::where('room_number', $roomNumber)->first();
            if (!$room) {
                return [
                    'valid' => false,
                    'message' => "Room {$roomNumber} not found in the system."
                ];
            }

            // Check if student is already assigned to this room
            $existingAssignment = RoomAssignment::where('room_number', $roomNumber)
                ->where('student_id', $studentId);

            if ($excludeAssignmentId) {
                $existingAssignment->where('id', '!=', $excludeAssignmentId);
            }

            if ($existingAssignment->exists()) {
                return [
                    'valid' => false,
                    'message' => 'Student is already assigned to this room.'
                ];
            }

            // Check room capacity
            $currentOccupancy = RoomAssignment::where('room_number', $roomNumber);
            if ($excludeAssignmentId) {
                $currentOccupancy->where('id', '!=', $excludeAssignmentId);
            }
            $currentOccupancy = $currentOccupancy->count();

            if ($currentOccupancy >= $room->capacity) {
                $availableRooms = self::getAvailableRoomsForGender($studentGender);
                $suggestion = !empty($availableRooms)
                    ? " Available rooms for this gender: " . implode(', ', array_slice($availableRooms, 0, 3))
                    : " No available rooms found for this gender.";

                return [
                    'valid' => false,
                    'message' => "Room {$roomNumber} is at full capacity ({$currentOccupancy}/{$room->capacity}).{$suggestion}"
                ];
            }

            // Check gender compatibility
            $existingGenderAssignment = RoomAssignment::where('room_number', $roomNumber);
            if ($excludeAssignmentId) {
                $existingGenderAssignment->where('id', '!=', $excludeAssignmentId);
            }
            $existingGenderAssignment = $existingGenderAssignment->first();

            if ($existingGenderAssignment && $existingGenderAssignment->student_gender !== $studentGender) {
                $genderText = $existingGenderAssignment->student_gender === 'M' ? 'male' : 'female';
                $studentGenderText = $studentGender === 'M' ? 'male' : 'female';
                $availableRooms = self::getAvailableRoomsForGender($studentGender);
                $suggestion = !empty($availableRooms)
                    ? " Available {$studentGenderText} rooms: " . implode(', ', array_slice($availableRooms, 0, 3))
                    : " No available {$studentGenderText} rooms found.";

                return [
                    'valid' => false,
                    'message' => "Gender mismatch. Room {$roomNumber} is assigned to {$genderText} students only.{$suggestion}"
                ];
            }

            return [
                'valid' => true,
                'message' => 'Room assignment is valid.',
                'room' => $room,
                'current_occupancy' => $currentOccupancy
            ];
        } catch (\Exception $e) {
            Log::error('Error validating room assignment', [
                'room_number' => $roomNumber,
                'student_id' => $studentId,
                'error' => $e->getMessage()
            ]);

            return [
                'valid' => false,
                'message' => 'An error occurred while validating the room assignment. Please try again.'
            ];
        }
    }

    /**
     * Get available students for a specific room (considering gender constraints)
     *
     * @param string $roomNumber
     * @return array
     */
    public static function getAvailableStudentsForRoom($roomNumber)
    {
        // Get room's current gender constraint
        $roomGender = null;
        $existingAssignment = RoomAssignment::where('room_number', $roomNumber)->first();
        if ($existingAssignment) {
            $roomGender = $existingAssignment->student_gender;
        }

        // Get all valid students
        $allStudents = self::getAllValidStudents();

        // Filter by gender if room has gender constraint
        if ($roomGender) {
            $allStudents = array_filter($allStudents, function ($student) use ($roomGender) {
                return $student['gender'] === $roomGender;
            });
        }

        // Remove students already assigned to this room
        $assignedStudentIds = RoomAssignment::where('room_number', $roomNumber)
            ->pluck('student_id')
            ->toArray();

        $availableStudents = array_filter($allStudents, function ($student) use ($assignedStudentIds) {
            return !in_array($student['id'], $assignedStudentIds);
        });

        return array_values($availableStudents);
    }

    /**
     * Get available rooms for a specific gender
     *
     * @param string $gender
     * @return array
     */
    public static function getAvailableRoomsForGender($gender)
    {
        try {
            $rooms = Room::where('status', 'active')
                ->whereRaw('capacity > (SELECT COUNT(*) FROM room_assignments WHERE room_assignments.room_number = rooms.room_number)')
                ->get();

            $availableRooms = [];

            foreach ($rooms as $room) {
                // Check if room has any gender constraint
                $existingAssignment = RoomAssignment::where('room_number', $room->room_number)->first();

                if (!$existingAssignment || $existingAssignment->student_gender === $gender) {
                    $availableRooms[] = $room->room_number;
                }
            }

            return $availableRooms;
        } catch (\Exception $e) {
            Log::error('Error getting available rooms for gender', [
                'gender' => $gender,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Search students by name (fuzzy matching)
     *
     * @param string $searchTerm
     * @param int $limit
     * @return array
     */
    public static function searchStudents($searchTerm, $limit = 10)
    {
        try {
            $searchTerm = trim($searchTerm);

            if (empty($searchTerm)) {
                return [];
            }

            $students = PNUser::where('user_role', 'student')
                ->where('status', 'active')
                ->where(function ($query) use ($searchTerm) {
                    $query->where('user_fname', 'LIKE', "%{$searchTerm}%")
                          ->orWhere('user_lname', 'LIKE', "%{$searchTerm}%")
                          ->orWhereRaw("CONCAT(user_fname, ' ', user_lname) LIKE ?", ["%{$searchTerm}%"]);
                })
                ->orderByRaw("CASE
                    WHEN CONCAT(user_fname, ' ', user_lname) LIKE ? THEN 1
                    WHEN user_fname LIKE ? THEN 2
                    WHEN user_lname LIKE ? THEN 3
                    ELSE 4
                END", ["{$searchTerm}%", "{$searchTerm}%", "{$searchTerm}%"])
                ->limit($limit)
                ->get()
                ->map(function ($student) {
                    return [
                        'id' => $student->user_id,
                        'name' => $student->user_fname . ' ' . $student->user_lname,
                        'gender' => $student->gender,
                        'batch' => self::getStudentBatch($student->user_id)
                    ];
                })
                ->toArray();

            return $students;
        } catch (\Exception $e) {
            Log::error('Error searching students', [
                'search_term' => $searchTerm,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Validate student name format
     *
     * @param string $studentName
     * @return array
     */
    public static function validateStudentNameFormat($studentName)
    {
        $studentName = trim($studentName);

        if (empty($studentName)) {
            return [
                'valid' => false,
                'message' => 'Student name cannot be empty.'
            ];
        }

        if (strlen($studentName) < 2) {
            return [
                'valid' => false,
                'message' => 'Student name must be at least 2 characters long.'
            ];
        }

        if (strlen($studentName) > 100) {
            return [
                'valid' => false,
                'message' => 'Student name cannot exceed 100 characters.'
            ];
        }

        // Allow letters, spaces, hyphens, apostrophes, and periods
        if (!preg_match("/^[a-zA-Z\s\-'.]+$/", $studentName)) {
            return [
                'valid' => false,
                'message' => 'Student name can only contain letters, spaces, hyphens, apostrophes, and periods.'
            ];
        }

        return [
            'valid' => true,
            'message' => 'Student name format is valid.'
        ];
    }
}
