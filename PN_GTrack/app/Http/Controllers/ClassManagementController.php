<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BatchClass;

class ClassManagementController extends Controller
{
    public function index()
    {
        $classes = BatchClass::withCount('students')->orderBy('year', 'asc')->get();
        return view('admin.classes.index', compact('classes'));
    }

    public function create()
    {
        return view('admin.classes.form');
    }

    public function edit($id)
    {
        $class = BatchClass::findOrFail($id);

        return view('admin.classes.form', compact('class'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
        ]);

        $request->validate([
            'name' => ['required', 'regex:/^\d+$/', 'integer', 'between:1,65535', 'unique:student_classes,year'],
        ], [
            'name.required' => 'Class name is required.',
            'name.regex' => 'Class name must contain numbers only.',
            'name.between' => 'Class year must be between 1 and 65535.',
            'name.unique' => 'This class already exists in the system.',
        ]);

        BatchClass::create([
            'year' => $request->name,
        ]);

        return redirect()->route('classes.index')->with('success', 'Class created successfully.');
    }

    public function update(Request $request, $id)
    {
        $class = BatchClass::findOrFail($id);
        $request->merge([
            'name' => trim((string) $request->input('name')),
        ]);

        $request->validate([
            'name' => ['required', 'regex:/^\d+$/', 'integer', 'between:1,65535', 'unique:student_classes,year,' . $id],
        ], [
            'name.required' => 'Class name is required.',
            'name.regex' => 'Class name must contain numbers only.',
            'name.between' => 'Class year must be between 1 and 65535.',
            'name.unique' => 'This class already exists in the system.',
        ]);

        $class->update(['year' => $request->name]);

        return redirect()->route('classes.index')->with('success', 'Class updated successfully.');
    }

    public function destroy($id)
    {
        $class = BatchClass::findOrFail($id);

        $studentCount = $class->students()->count();
        if ($studentCount > 0) {
            return redirect()->back()->with('error', "Cannot delete Class '{$class->name}' because {$studentCount} student(s) are currently enrolled in it. Please reassign the students first.");
        }

        $class->delete();

        return redirect()->back()->with('success', 'Class deleted successfully.');
    }
}
