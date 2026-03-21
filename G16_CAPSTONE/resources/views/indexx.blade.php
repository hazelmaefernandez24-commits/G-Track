@extends('layouts.apps')

@section('content')
<h2>Assignments</h2>
<a href="{{ url('/assignments/create') }}">Assign Students to Task</a>
@foreach($assignments as $assignment)
    <h3>{{ $assignment->category->name }} ({{ $assignment->start_date }} - {{ $assignment->end_date }})</h3>
    <ul>
        @foreach($assignment->assignmentMembers as $member)
            <li>
                {{ $member->student->name }} 
                ({{ ucfirst($member->student->gender) }}, {{ $member->student->batch }})
                @if($member->is_coordinator)
                    <strong>- Coordinator</strong>
                @endif
            </li>
        @endforeach
    </ul>
@endforeach
@endsection