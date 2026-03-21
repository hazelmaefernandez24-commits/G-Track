@extends('layouts.app')

@section('content')
<h1>Task History</h1>
<table>
    <thead>
        <tr>
            <th>Student Name</th>
            <th>Assigned Area</th>
            <th>Description</th>
            <th>Status</th>
            <th>Completed At</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tasks as $task)
            <tr>
                <td>{{ $task->name }}</td>
                <td>{{ $task->area }}</td>
                <td>{{ $task->desc }}</td>
                <td>{{ $task->status }}</td>
                <td>{{ $task->completed_at }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection