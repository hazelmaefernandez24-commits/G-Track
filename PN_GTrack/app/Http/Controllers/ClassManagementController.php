<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BatchClass;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class ClassManagementController extends Controller
{
    public function index()
    {
        $classes = BatchClass::withCount('students')->orderBy('name', 'asc')->get();
        return view('admin.classes.index', compact('classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:student_classes,name'],
        ], [
            'name.required' => 'Class name is required.',
            'name.unique' => 'This class already exists in the system.',
        ]);

        BatchClass::create([
            'name' => trim($request->name),
        ]);

        return redirect()->back()->with('success', 'Class created successfully.');
    }

    public function update(Request $request, $id)
    {
        $class = BatchClass::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:student_classes,name,' . $id],
        ], [
            'name.required' => 'Class name is required.',
            'name.unique' => 'This class already exists in the system.',
        ]);

        $oldName = $class->name;
        $newName = trim($request->name);

        DB::transaction(function () use ($class, $oldName, $newName) {
            // If the name changed, cascade the change to enrolled students and class notifications
            if ($oldName !== $newName) {
                Student::where('class', $oldName)->update(['class' => $newName]);
                DB::table('notifications')->where('class', $oldName)->update(['class' => $newName]);
            }

            $class->update([
                'name' => $newName,
            ]);
        });

        return redirect()->back()->with('success', 'Class updated successfully.');
    }

    public function destroy($id)
    {
        $class = BatchClass::findOrFail($id);

        $studentCount = Student::where('class', $class->name)->count();
        if ($studentCount > 0) {
            return redirect()->back()->with('error', "Cannot delete Class '{$class->name}' because {$studentCount} student(s) are currently enrolled in it. Please reassign the students first.");
        }

        $class->delete();

        return redirect()->back()->with('success', 'Class deleted successfully.');
    }
}
