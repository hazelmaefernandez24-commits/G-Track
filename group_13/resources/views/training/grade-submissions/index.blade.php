@extends('layouts.nav')

@section('content')

<h1>Grade Submissions</h1>
<hr>
<div class="container">


    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Subject</th>
                    <th>Grade</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($gradeSubmissions as $submission)
                    <tr>
                        <td>{{ $submission->student->name ?? '-' }}</td>
                        <td>{{ $submission->subject->name ?? '-' }}</td>
                        <td>{{ $submission->grade ?? '-' }}</td>
                        <td>
                            @if($submission->status == 'approved')
                                <span class="status approved">Approved</span>
                            @elseif($submission->status == 'rejected')
                                <span class="status rejected">Rejected</span>
                            @else
                                <span class="status pending">Pending</span>
                            @endif
                        </td>
                        <td class="actions">
                            <form action="{{ route('training.grade-submissions.verify', $submission->id) }}" method="POST" class="inline-form">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn-approve" onclick="return confirm('Approve this grade?')">Approve</button>
                            </form>
                            <form action="{{ route('training.grade-submissions.reject', $submission->id) }}" method="POST" class="inline-form">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn-reject" onclick="return confirm('Reject this grade?')">Reject</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="no-data">No grade submissions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Simple CSS Styling -->
<style>


.alert-success {
    background-color: #d4edda;
    padding: 10px 15px;
    border: 1px solid #c3e6cb;
    color: #155724;
    border-radius: 5px;
    margin-bottom: 20px;
}

.table-container {
    overflow-x: auto;
    width: 100%;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

thead {
    background-color: #f5f5f5;
}

th, td {
    border: 1px solid #ddd;
    padding: 12px;
    text-align: center;
}

th {
    font-weight: bold;
}

.status {
    padding: 5px 10px;
    border-radius: 12px;
    font-size: 0.9em;
}

.status.approved {
    background-color: #c8e6c9;
    color: #256029;
}

.status.rejected {
    background-color: #ffcdd2;
    color: #c62828;
}

.status.pending {
    background-color: #e0e0e0;
    color: #424242;
}

.actions {
    display: flex;
    justify-content: center;
    gap: 8px;
}

.inline-form {
    display: inline-block;
}

.btn-approve, .btn-reject {
    border: none;
    padding: 8px 12px;
    cursor: pointer;
    border-radius: 5px;
    font-size: 0.9em;
}

.btn-approve {
    background-color: #4CAF50;
    color: white;
}

.btn-reject {
    background-color: #F44336;
    color: white;
}

.no-data {
    text-align: center;
    color: #777;
    font-style: italic;
}
</style>
@endsection
