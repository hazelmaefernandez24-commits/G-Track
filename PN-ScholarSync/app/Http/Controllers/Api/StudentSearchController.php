<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StudentDetails;
use App\Models\User;

class StudentSearchController extends Controller
{
    /**
     * Search students for Select2 autocomplete.
     */
    public function search(Request $request)
    {
        $query = $request->input('q');
        if (!$query) {
            return response()->json([]);
        }

        // Search by name or student ID (adjust fields as needed)
        $students = User::whereHas('studentDetails')
            ->where(function($q) use ($query) {
                $q->where('user_fname', 'like', "%$query%")
                  ->orWhere('user_lname', 'like', "%$query%")
                  ->orWhereHas('studentDetails', function($sq) use ($query) {
                      $sq->where('student_id', 'like', "%$query%")
                  ;});
            })
            ->limit(20)
            ->get();

        $results = $students->map(function($student) {
            $studentId = $student->studentDetails->student_id ?? $student->user_id;
            return [
                'id' => $studentId,
                'text' => $student->user_fname . ' ' . $student->user_lname . ' (' . $studentId . ')'
            ];
        });

        return response()->json($results);
    }
}
