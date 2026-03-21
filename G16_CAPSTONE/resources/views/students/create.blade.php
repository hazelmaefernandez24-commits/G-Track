@extends('layouts.apps')

@section('content')
<h2>All Students</h2>
<table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Gender</th>
            <th>Batch</th>
        </tr>
    </thead>
    <tbody>
        @foreach($students as $student)
        <tr>
            <td>{{ $student->name }}</td>
            <td>{{ ucfirst($student->gender) }}</td>
            <td>{{ $student->batch }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection