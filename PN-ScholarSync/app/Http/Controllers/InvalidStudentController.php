<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\InvalidStudentCatcher;
use Illuminate\Support\Facades\Log;

class InvalidStudentController extends Controller
{
    protected $catcher;

    public function __construct(InvalidStudentCatcher $catcher)
    {
        $this->catcher = $catcher;
    }

    /**
     * Show a single invalid student details page
     */
    public function show($id)
    {
        try {
            $invalid = \Illuminate\Support\Facades\DB::table('invalid_students')->where('id', $id)->first();

            if (!$invalid) {
                return redirect()->route('educator.invalid-students')
                    ->with('error', 'Invalid record not found.');
            }

            // Build a Violation-like model so we can reuse educator.viewViolation
            $violation = new \App\Models\Violation();
            $violation->id = 0; // placeholder (not saved)
            $violation->violation_date = $invalid->validated_at ? \Carbon\Carbon::parse($invalid->validated_at)->toDateString() : now()->toDateString();
            $violation->severity = 'Low';
            $violation->penalty = 'WW'; // Written Warning display in view
            $violation->consequence = 'Pending educator review';
            $violation->consequence_status = 'pending';
            $violation->action_taken = false;
            $violation->incident_datetime = $invalid->caught_at ? \Carbon\Carbon::parse($invalid->caught_at) : null;
            $violation->incident_place = 'Center';
            $violation->incident_details = $invalid->description ?? null;
            $violation->prepared_by = $invalid->validated_by ?? null;
            $violation->status = 'active';

            // Relations expected by the view
            $student = \App\Models\User::where('user_id', $invalid->g16_user_id)->first();
            if ($student) {
                $violation->setRelation('student', $student);
            } else {
                // Create a lightweight placeholder with fname/lname split from student_name
                $nameParts = explode(' ', $invalid->student_name ?? '');
                $fname = array_shift($nameParts);
                $lname = implode(' ', $nameParts);
                $placeholder = new \App\Models\User();
                $placeholder->user_fname = $fname;
                $placeholder->user_lname = $lname;
                $violation->setRelation('student', $placeholder);
            }

            $type = new \App\Models\ViolationType();
            $type->violation_name = 'Invalid task submission';
            $violation->setRelation('violationType', $type);

            $category = new \App\Models\OffenseCategory();
            $category->category_name = ucfirst($invalid->task_category ?? 'General');
            $violation->setRelation('offenseCategory', $category);

            // Render the same details page used by regular violations
            return view('educator.viewViolation', [
                'violation' => $violation
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error showing invalid student: ' . $e->getMessage());
            return redirect()->route('educator.invalid-students')
                ->with('error', 'Unable to open details: ' . $e->getMessage());
        }
    }

    /**
     * Display the invalid students dashboard
     */
    public function index()
    {
        try {
            $caughtStudents = $this->catcher->getCaughtInvalidStudents();
            $totalCount = $this->catcher->getCaughtCount();

            return view('educator.invalid-students', [
                'caughtStudents' => $caughtStudents,
                'totalCount' => $totalCount
            ]);

        } catch (\Exception $e) {
            Log::error('Error in invalid students index: ' . $e->getMessage());
            return view('educator.invalid-students', [
                'caughtStudents' => collect(),
                'totalCount' => 0,
                'error' => 'Unable to load invalid students: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Catch invalid students from G16_CAPSTONE
     */
    public function catch(Request $request)
    {
        try {
            $result = $this->catcher->catchInvalidStudents();

            if ($result['success']) {
                $message = $result['message'];
                if ($result['count'] > 0) {
                    $message .= " ({$result['count']} out of {$result['total_found']} found)";
                }

                return redirect()->route('educator.invalid-students')
                    ->with('success', $message);
            } else {
                return redirect()->route('educator.invalid-students')
                    ->with('error', 'Failed to catch invalid students: ' . $result['error']);
            }

        } catch (\Exception $e) {
            Log::error('Error catching invalid students: ' . $e->getMessage());
            return redirect()->route('educator.invalid-students')
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Get invalid students data for AJAX
     */
    public function getData(Request $request)
    {
        try {
            $date = $request->query('date');
            
            if ($date) {
                $students = $this->catcher->getInvalidStudentsByDate($date);
            } else {
                $students = $this->catcher->getCaughtInvalidStudents();
            }

            return response()->json([
                'success' => true,
                'data' => $students,
                'count' => $students->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting invalid students data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark invalid student as processed
     */
    public function markProcessed(Request $request, $id)
    {
        try {
            $result = $this->catcher->markAsProcessed($id);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student marked as processed'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to mark as processed'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Error marking student as processed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
