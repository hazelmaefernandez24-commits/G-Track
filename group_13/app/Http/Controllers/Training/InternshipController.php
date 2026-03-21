<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use App\Models\Internship;
use App\Models\School;
use App\Models\ClassModel;
use App\Models\PNUser;
use Illuminate\Http\Request;

class InternshipController extends Controller
{
    public function index(Request $request)
    {
        $batch = $request->query('batch');
        $company = $request->query('company');

        // Robust filtering even if some records use classes.id and others use classes.class_id
        $internships = Internship::with(['student', 'school', 'classModel'])
            ->leftJoin('classes', function ($join) {
                $join->on('internships.class_id', '=', 'classes.id')
                     ->orOn('internships.class_id', '=', 'classes.class_id');
            })
            ->leftJoin('student_details as sd', 'sd.user_id', '=', 'internships.student_id')
            ->when($batch, function ($q) use ($batch) {
                $q->where(function ($w) use ($batch) {
                    $w->where('classes.batch', $batch)
                      ->orWhere('sd.batch', $batch);
                });
            })
            ->when($company, function ($q) use ($company) {
                $q->where('internships.company', 'like', "%$company%");
            })
            ->orderByDesc('internships.created_at')
            ->select('internships.*')
            ->get();

        $schools = School::orderBy('name')->get(['school_id', 'name']);

        // Distinct batches and companies for filter dropdowns
        // Source 1: classes table (most reliable)
        $batches = \DB::table('classes')
            ->whereNotNull('batch')
            ->where('batch', '!=', '')
            ->distinct()
            ->orderBy('batch')
            ->pluck('batch');

        // Source 2: student_details table (if classes missing batch values)
        if ($batches->isEmpty() && \Schema::hasTable('student_details')) {
            $batches = \DB::table('student_details')
                ->whereNotNull('batch')
                ->where('batch', '!=', '')
                ->distinct()
                ->orderBy('batch')
                ->pluck('batch');
        }

        // Source 3: derive from internships joined to classes
        if ($batches->isEmpty()) {
            $batches = Internship::query()
                ->join('classes', 'internships.class_id', '=', 'classes.id')
                ->whereNotNull('classes.batch')
                ->where('classes.batch', '!=', '')
                ->distinct()
                ->orderBy('classes.batch')
                ->pluck('classes.batch');
        }
        $companies = Internship::select('company')
            ->whereNotNull('company')
            ->where('company', '!=', '')
            ->distinct()
            ->orderBy('company')
            ->pluck('company');

        return view('training.internship.index', [
            'title' => 'Internship',
            'internships' => $internships,
            'schools' => $schools,
            'batches' => $batches,
            'activeBatch' => $batch,
            'activeCompany' => $company,
            'companies' => $companies,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|string|exists:schools,school_id',
            'class_id' => 'required|integer',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'string|exists:pnph_users,user_id',
            'company' => 'nullable|string|max:255',
            'time_in' => 'required|date_format:H:i',
            'time_out' => 'required|date_format:H:i',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'days' => 'nullable|array',
            'days.*' => 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        ]);

        $timeOfDuty = null;
        if (!empty($validated['days'])) {
            $timeOfDuty = json_encode([
                'days' => array_values($validated['days']),
            ]);
        }

        foreach ($validated['student_ids'] as $studentId) {
            Internship::create([
                'school_id' => $validated['school_id'],
                'class_id' => $validated['class_id'],
                'student_id' => $studentId,
                'company' => $request->input('company'),
                'time_in' => $validated['time_in'],
                'time_out' => $validated['time_out'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'time_of_duty' => $timeOfDuty,
                'days' => $validated['days'] ?? null,
            ]);
        }

        return redirect()->route('training.internship.index')->with('success', 'Intern(s) set successfully.');
    }

    // Dependent dropdown: classes by school
    public function getClassesBySchool(string $schoolId)
    {
        $classes = ClassModel::where('school_id', $schoolId)
            ->orderBy('class_name')
            ->get(['id', 'class_id', 'class_name']);

        return response()->json($classes);
    }

    // Students by class
    public function getStudentsByClass(int $classPrimaryId)
    {
        $class = ClassModel::findOrFail($classPrimaryId);
        $students = $class->students()
            ->whereRaw('LOWER(pnph_users.user_role) = ?', ['student'])
            ->with('studentDetail')
            ->select('pnph_users.user_id', 'pnph_users.user_fname', 'pnph_users.user_lname')
            ->get();

        return response()->json($students);
    }

    public function edit(Internship $internship)
    {
        return view('training.internship.edit', [
            'title' => 'Edit Internship',
            'internship' => $internship,
        ]);
    }

    public function update(Request $request, Internship $internship)
    {
        $validated = $request->validate([
            'company' => 'nullable|string|max:255',
            'time_in' => 'required|date_format:H:i',
            'time_out' => 'required|date_format:H:i',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'days' => 'nullable|array',
            'days.*' => 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        ]);

        $update = $validated;

        // Keep both columns in sync for backward compatibility
        if (array_key_exists('days', $validated)) {
            $update['days'] = $validated['days'];
            $update['time_of_duty'] = $validated['days'] ? json_encode(['days' => array_values($validated['days'])]) : null;
        }

        $internship->update($update);

        return redirect()->route('training.internship.index')->with('success', 'Internship updated.');
    }

    public function destroy(Internship $internship)
    {
        $internship->delete();
        return redirect()->route('training.internship.index')->with('success', 'Internship removed.');
    }
}


