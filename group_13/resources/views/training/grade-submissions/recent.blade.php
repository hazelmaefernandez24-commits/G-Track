@extends('layouts.nav')

@section('content')
<div class="page-container">
    <div class="page-header">
        <h1>Recent Grade Submissions</h1>
        <p class="subtitle">Latest 10 grade submissions</p>
    </div>

    <div class="card">
        <div class="card-content">
            @if($recentSubmissions->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <h2>No Recent Submissions</h2>
                    <p>Start by creating a new grade submission to track student progress</p>
                    <a href="{{ route('training.grade-submissions.create') }}" class="button primary">
                        <i class="fas fa-plus"></i> Create New Submission
                    </a>
                </div>
            @else
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>School</th>
                                <th>Class</th>
                                <th>Semester</th>
                                <th>Term</th>
                                <th>Academic Year</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentSubmissions as $submission)
                                <tr>
                                    <td>{{ $submission['school_name'] }}</td>
                                    <td>{{ $submission['class_name'] }}</td>
                                    <td>{{ $submission['semester'] }}</td>
                                    <td>{{ $submission['term'] }}</td>
                                    <td>{{ $submission['academic_year'] }}</td>
                                    <td>{{ $submission['created_at'] }}</td>
                                    <td>
                                        <form action="{{ route('training.grade-submissions.destroy', $submission['id']) }}" method="POST" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="button delete" title="Delete Submission" onclick="return confirm('Are you sure you want to delete this submission?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.page-container {
    padding: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    color: #333;
    font-size: 1.8rem;
    margin: 0;
    font-weight: 600;
}

.subtitle {
    color: #666;
    margin-top: 0.5rem;
}

.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.card-content {
    padding: 1.5rem;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
}

.empty-icon {
    font-size: 3rem;
    color: #22bbea;
    margin-bottom: 1rem;
}

.empty-state h2 {
    color: #333;
    margin-bottom: 0.5rem;
    font-size: 1.5rem;
}

.empty-state p {
    color: #666;
    margin-bottom: 1.5rem;
}

.button {
    display: inline-flex;
    align-items: center;
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    text-decoration: none;
}

.button i {
    margin-right: 0.5rem;
}

.button.primary {
    background: #22bbea;
    color: white;
}

.button.primary:hover {
    background: #1a9bc7;
}

.button.delete {
    background: #fee2e2;
    color: #dc2626;
    padding: 0.5rem;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
}

.button.delete:hover {
    background: #fecaca;
}

.button.delete i {
    margin: 0;
    font-size: 1rem;
}

.table-wrapper {
    overflow-x: auto;
    margin: 0 -1.5rem;
    padding: 0 1.5rem;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

.data-table th {
    background: #22bbea;
    color: white;
    font-weight: 500;
    text-align: left;
    padding: 1rem;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.data-table td {
    padding: 1rem;
    border-bottom: 1px solid #eee;
    color: #333;
}

.data-table tr:hover {
    background: #f8f9fa;
}

.delete-form {
    display: inline-block;
}

@media (max-width: 768px) {
    .page-container {
        padding: 1rem;
    }
    
    .data-table th,
    .data-table td {
        padding: 0.75rem;
    }
}
</style>
@endsection 