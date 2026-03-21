@extends('layouts.apps')

@section('content')
<h2>Assign Students to Task</h2>
<form method="POST" action="{{ url('/assignments') }}">
    @csrf
    <label>Category:</label>
    <select name="category_id" required>
        @foreach($categories as $category)
            <option value="{{ $category->id }}">{{ $category->name }}</option>
        @endforeach
    </select><br>
    <label>Start Date:</label>
    <input type="date" name="start_date" value="{{ date('Y-m-d') }}" required><br>
    <label>End Date:</label>
    <input type="date" name="end_date" value="{{ date('Y-m-d', strtotime('+7 days')) }}" required><br>
    <label>Students:</label>
    <select name="members[]" multiple required size="10">
        @foreach($students as $student)
            <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->gender }}, {{ $student->batch }})</option>
        @endforeach
    </select><br>
    <button type="submit">Assign</button>
</form>
@endsection