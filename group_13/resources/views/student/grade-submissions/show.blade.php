@extends('layouts.admin_layout')
@section('content')

<h2>Submit Grades</h2>
<form action="{{ route('student.grade-submissions.store', $gradeSubmission->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    <table>
        <thead>
            <tr>
                <th>Subject</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($gradeSubmission->subjects as $subject)
                <tr>
                    <td>{{ $subject->name }}</td>
                    <td>
                        <input type="hidden" name="grades[{{ $loop->index }}][subject_id]" value="{{ $subject->id }}">
                        <input type="number" name="grades[{{ $loop->index }}][grade]" required>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div>
        <label for="screenshot">Upload Screenshot</label>
        <input type="file" name="screenshot" id="screenshot" required>
    </div>
    <button type="submit">Submit Grades</button>
</form>

@endsection