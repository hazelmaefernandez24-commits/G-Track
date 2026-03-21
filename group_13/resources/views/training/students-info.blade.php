@extends('layouts.nav')

@section('content')
<link rel="stylesheet" href="{{ asset('css/training/student-info.css') }}">

<div class="header-section">
       <h1 style="font-weight: 300;">Students Information</h1>
       <hr>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

<form method="GET" action="{{ route('training.students-info') }}" class="filter-form">
        <div class="form-group">
            <label for="batch">Filter Students</label>
            <select name="batch" id="batch" class="form-control" onchange="this.form.submit()">
                <option value="">All Students</option>
                <option value="N/A" {{ request('batch') === 'N/A' ? 'selected' : '' }}>No Student ID (N/A)</option>
                <option disabled>──────────</option>
                @foreach ($batches as $batch)
                    <option value="{{ $batch }}" {{ request('batch') == $batch ? 'selected' : '' }}>
                        Batch: {{ $batch }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>
    
<div class="page-container">
    


    

<br>

<!-- <div class="table-wrapper"> -->
        <div class="table-header">
            <div class="header-cell">USER ID</div>
            <div class="header-cell">STUDENT ID</div>
            <div class="header-cell">LAST NAME</div>
            <div class="header-cell">FIRST NAME</div>
            <div class="header-cell">MI</div>
            <div class="header-cell">SUFFIX</div>
            <div class="header-cell">SEX</div>
            <div class="header-cell">EMAIL</div>
            <div class="header-cell act1">ACTIONS</div>
        </div>
        
        @forelse($students as $student)
            <div class="table-row">
                <div class="cell">{{ $student->user_id }}</div>
                <div class="cell">{{ $student->studentDetail->student_id ?? 'N/A' }}</div>
                <div class="cell">{{ $student->user_lname }}</div>
                <div class="cell">{{ $student->user_fname }}</div>
                <div class="cell">{{ $student->user_mInitial }}</div>
                <div class="cell">{{ $student->user_suffix ?? '' }}</div>
                <div class="cell">{{ $student->studentDetail->gender ?? 'N/A' }}</div>
                <div class="cell">{{ $student->user_email }}</div>
                <div class="cell">
                    <div class="action-buttons">
                        <a href="{{ route('training.students.view', $student->user_id) }}" class="btn-icon" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('training.students.edit', $student->user_id) }}" class="btn-icon" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="table-row">
                <div class="cell empty-message">No students found</div>
            </div>
        @endforelse
    </div>
    @if ($students->hasPages())
    <div class="pagination-container">
        <div class="pagination-info">
            Showing {{ $students->firstItem() }} to {{ $students->lastItem() }} of {{ $students->total() }} entries
        </div>
        <div class="pagination-buttons">
            @if ($students->onFirstPage())
                <span class="pagination-button disabled">
                    <i class="fas fa-chevron-left"></i> Previous
                </span>
            @else
                <a href="{{ $students->previousPageUrl() }}" class="pagination-button">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            @endif

            <div class="page-info">
                Page {{ $students->currentPage() }} of {{ $students->lastPage() }}
            </div>

            @if ($students->hasMorePages())
                <a href="{{ $students->nextPageUrl() }}" class="pagination-button">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            @else
                <span class="pagination-button disabled">
                    Next <i class="fas fa-chevron-right"></i>
                </span>
            @endif
        </div>
    </div>
    @endif
</div>

<style>
/* Pagination */
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-top: 1px solid #eee;
    margin-top: 20px;
}

.pagination-info {
    color: #6c757d;
    font-size: 0.9rem;
}

.pagination-buttons {
    display: flex;
    align-items: center;
    gap: 10px;
}

.pagination-button {
    padding: 8px 16px;
    border-radius: 6px;
    background: white;
    border: 1px solid #ddd;
    color: #333;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    transition: all 0.2s;
}

.pagination-button:hover:not(.disabled) {
    background: #f5f5f5;
    border-color: #ccc;
}

.pagination-button.disabled {
    color: #aaa;
    cursor: not-allowed;
}

.page-info {
    margin: 0 10px;
    font-size: 0.9rem;
    color: #666;
}

@media (max-width: 768px) {
    .pagination-container {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
}
</style>
@endsection
