<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GradeSubmission;

class StudentGradeSubmissionController extends Controller
{
    public function show($id)
{
    $gradeSubmission = GradeSubmission::with('subjects')->findOrFail($id);

    return view('student.grade-submissions.show', compact('gradeSubmission'));
}

public function store(Request $request, $id)
{
    $validated = $request->validate([
        'grades' => 'required|array',
        'grades.*.subject_id' => 'required|exists:subjects,id',
        'grades.*.grade' => 'required|numeric|min:0|max:100',
        'screenshot' => 'required|image|max:2048', // Screenshot validation
    ]);

    // Save grades and screenshot logic here...

    return redirect()->route('student.dashboard')->with('success', 'Grades submitted successfully.');
}
}
