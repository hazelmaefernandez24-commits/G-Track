@extends('layouts.educator')

@section('title', 'Manage Student Violations')

@section('css')
    <!-- External CSS and Script Dependencies -->
    <link rel="stylesheet" href="{{ asset('css/educator/violation.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
    .custom-pagination {
        display: flex;
        justify-content: center;
        margin-top: 1.5rem;
    }

    /* Pulse highlight for newly created rows */
    .pulse-highlight {
        animation: pulse 1s ease-in-out 0s 3;
    }
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(40,167,69, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(40,167,69, 0); }
        100% { box-shadow: 0 0 0 0 rgba(40,167,69, 0); }
    }
    .custom-pagination ul {
        list-style: none;
        padding: 0;
        display: flex;
        align-items: center;
    }
    .custom-pagination li {
        margin: 0 5px;
    }
    .custom-pagination a, .custom-pagination span {
        color: #0d6efd;
        text-decoration: none;
        padding: 8px 15px;
        display: block;
        border-radius: 5px;
        font-weight: 500;
    }
    .custom-pagination a:hover {
        background-color: #f0f0f0;
        text-decoration: none;
    }
    .custom-pagination .active span {
        font-weight: 700;
        color: #333;
    }
    .custom-pagination .disabled span {
        color: #6c757d;
        pointer-events: none;
    }

    /* Status container styling */
    .status-container {
        min-width: 120px;
        position: relative;
    }

    .status-badge {
        transition: all 0.2s ease;
        border: none !important;
        font-weight: 500;
    }

    .status-badge:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .status-actions {
        animation: fadeIn 0.2s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .status-actions .btn {
        transition: all 0.2s ease;
        border-radius: 4px;
    }

    .status-actions .btn:hover {
        transform: translateY(-1px);
    }

    </style>
@endsection

@section('content')
    <div class="container-fluid px-1">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="fw-bold">Manage Student Violations</h2>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @php
            $createdPenalties = session('created_penalties', []);
        @endphp
        @if(!empty($createdPenalties))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <strong>Applied penalties:</strong>
                <ul style="margin-bottom: 0;">
                    @foreach($createdPenalties as $sid => $pen)
                        @php
                            $studentName = $sid;
                            try {
                                $sd = \App\Models\StudentDetails::where('student_id', $sid)->with('user')->first();
                                if ($sd && $sd->user) {
                                    $studentName = $sd->user->user_fname . ' ' . $sd->user->user_lname . " ({$sid})";
                                }
                            } catch (Exception $ex) {
                                // ignore lookup errors
                            }
                        @endphp
                        <li>{{ $studentName }}: <strong>{{ $pen }}</strong></li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <main>
            <!-- Action Buttons Section -->
        

            <!-- Warning Statistics Section -->
            @php
                // Use controller-provided counts computed from the same filtered dataset as the table
                $pc = $penaltyCounts ?? ['VW'=>0,'WW'=>0,'Pro'=>0,'T'=>0];
            @endphp
            <section class="warning-section" style="padding: 20px; display: flex; justify-content: space-between; flex-wrap: nowrap;">
                <!-- Penalty Statistics Boxes -->
                <a href="{{ route('educator.studentsByPenalty', ['penalty' => 'VW']) }}" class="warning-box tall" style="flex: 1; margin: 0 8px; text-align: center;">
                    <div class="penalty-header" style="display: flex; flex-direction: column; align-items: center;">
                        <span>Verbal Warning<br>Student</span>
                        <span class="count-badge" style="background: none; width: auto; height: auto; margin-top: 10px; font-size: 36px; color: #333;">{{ $pc['VW'] }}</span>
                    </div>
                </a>

                <a href="{{ route('educator.studentsByPenalty', ['penalty' => 'WW']) }}" class="warning-box tall" style="flex: 1; margin: 0 8px; text-align: center;">
                    <div class="penalty-header" style="display: flex; flex-direction: column; align-items: center;">
                        <span>Written Warning<br>Student</span>
                        <span class="count-badge" style="background: none; width: auto; height: auto; margin-top: 10px; font-size: 36px; color: #333;">{{ $pc['WW'] }}</span>
                    </div>
                </a>

                <a href="{{ route('educator.studentsByPenalty', ['penalty' => 'Pro']) }}" class="warning-box tall" style="flex: 1; margin: 0 8px; text-align: center;">
                    <div class="penalty-header" style="display: flex; flex-direction: column; align-items: center;">
                        <span>Probationary Student</span>
                        <span class="count-badge" style="background: none; width: auto; height: auto; margin-top: 10px; font-size: 36px; color: #333;">{{ $pc['Pro'] }}</span>
                    </div>
                </a>
                
                <a href="{{ route('educator.studentsByPenalty', ['penalty' => 'T']) }}" class="warning-box tall" style="flex: 1; margin: 0 8px; text-align: center;">
                    <div class="penalty-header" style="display: flex; flex-direction: column; align-items: center;">
                        <span>Terminated Student</span>
                        <span class="count-badge" style="background: none; width: auto; height: auto; margin-top: 10px; font-size: 36px; color: #333;">{{ $pc['T'] }}</span>
                    </div>
                </a>
            </section>

            <div class="top-buttons d-flex align-items-center mb-3">
                <button type="button" class="btn me-3" data-bs-toggle="modal" data-bs-target="#addViolatorTypeModal">
                    <i class="fas fa-user-plus me-1"></i> Add Violator
                </button>
            </div>

            <!-- Add Violator Type Modal -->
            <div class="modal fade" id="addViolatorTypeModal" tabindex="-1" aria-labelledby="addViolatorTypeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addViolatorTypeModalLabel">Choose how to add violator</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <a href="{{ route('educator.add-violator-form') }}" class="btn btn-primary w-100" style="height:100%; display:flex; align-items:center; justify-content:center;">
                                        <div>
                                            <i class="fas fa-user me-2"></i> Individual Violator
                                        </div>
                                    </a>
                                </div>
                                <div class="col-12 col-md-6">
                                    <a href="{{ route('educator.add-violator-group-form') }}" class="btn btn-outline-primary w-100" style="height:100%; display:flex; align-items:center; justify-content:center;">
                                        <div>
                                            <i class="fas fa-users me-2"></i> Group Violator
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-3">Tip: In group mode you can select multiple students and the system will auto-calculate penalties per student.</small>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            

            <!-- Violations Table Section -->
            <section class="violation-table">
                <!-- Search and Filter Controls -->
                <div class="search-bar d-flex align-items-center" style="gap: 1rem;">
    <form id="searchForm" class="d-flex align-items-center" method="GET" action="{{ route('educator.violation') }}" style="gap: 1rem; flex: 1;">
        <div class="input-group" style="flex: 1; max-width: 400px;">
            <input type="text" name="search" id="searchInput" placeholder="Search by student or violation..." class="form-control" value="{{ request('search', '') }}" />
            <button class="btn btn-outline-secondary" type="submit" id="searchButton" title="Search">
                <i class="fas fa-search"></i>
            </button>
            <button class="btn btn-outline-secondary" type="button" id="clearSearchButton" title="Clear search">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <select name="severity" id="severityFilter" class="form-select" style="max-width: 180px;" onchange="this.form.submit()">
            <option value="">All Severity</option>
            <option value="Low">Low</option>
            <option value="Medium">Medium</option>
            <option value="High">High</option>
            <option value="Very High">Very High</option>
        </select>
        <select name="batch" class="form-select" id="batchSelect" style="max-width: 200px;" onchange="this.form.submit()">
            @php
                $currentBatch = request('batch', 'all');
                $years = collect();
                try {
                    if (\Schema::hasColumn('student_details', 'batch')) {
                        $explicit = \DB::table('student_details')
                            ->whereNotNull('batch')
                            ->pluck('batch')
                            ->filter(fn($b) => preg_match('/^\d{4}$/', (string)$b));
                        $years = $years->merge($explicit);
                    }
                    $sidYears = \DB::table('student_details')
                        ->whereNotNull('student_id')
                        ->pluck('student_id')
                        ->map(function ($sid) { if (preg_match('/^(\d{4})/', (string)$sid, $m)) { return $m[1]; } return null; })
                        ->filter();
                    $years = $years->merge($sidYears);
                    $vioYears = \DB::table('violations')
                        ->whereNotNull('student_id')
                        ->pluck('student_id')
                        ->map(function ($sid) { if (preg_match('/^(\d{4})/', (string)$sid, $m)) { return $m[1]; } return null; })
                        ->filter();
                    $years = $years->merge($vioYears)->unique()->sortDesc()->values();
                } catch (\Exception $e) {
                    $years = collect();
                }
            @endphp
            <option value="all" {{ $currentBatch === 'all' ? 'selected' : '' }}>All Classes</option>
            @foreach($years as $y)
                <option value="{{ $y }}" {{ (string)$currentBatch === (string)$y ? 'selected' : '' }}>Class {{ $y }}</option>
            @endforeach
        </select>
    </form>
                </div>

                <!-- Violations Data Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Student</th>
                                <th>Category</th>
                                <th>Violation</th>
                                <th>Severity</th>
                                <th>Penalty</th>
                                <th>Action Taken</th>
                                <th>Consequence Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="violation-table-body">
                            @php
                                $highlightIds = session('created_ids', []);
                            @endphp
                            @forelse ($violations as $violation)
                                <tr class="{{ in_array($violation->id, $highlightIds) ? 'table-success created-highlight' : '' }}" data-violation-id="{{ $violation->id }}">
                                    <td>{{ \Carbon\Carbon::parse($violation->violation_date)->format('M d, Y') }}</td>
                                    <td>{{ $violation->student ? $violation->student->user_fname.' '.$violation->student->user_lname : 'N/A' }}</td>
                                    <td>{{ $violation->violationType->offenseCategory->category_name ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            // Treat invalid-student rows as G16_CAPSTONE-originated
                                            $fromG16Row = isset($violation->is_invalid_student) && $violation->is_invalid_student;
                                        @endphp
                                        @if($fromG16Row)
                                            Not participating in general cleaning, center tasking and routines.
                                        @else
                                            {{ $violation->violationType->violation_name ?? 'N/A' }}
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            // Dynamic severity badge classes based on severity level
                                            $severityClass = match(strtolower($violation->severity)) {
                                                'low' => 'bg-warning',
                                                'medium' => 'bg-info',
                                                'high' => 'bg-danger',
                                                'very high' => 'bg-dark',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $severityClass }}">{{ $violation->severity }}</span>
                                    </td>
                                    <td>
                                        @php
                                            // Determine penalty display with overrides for no-action and appeal-approved
                                            $showNone = false;
                                            if (!$violation->action_taken) {
                                                $showNone = true;
                                            }
                                            if (strtoupper((string)$violation->penalty) === 'NONE') {
                                                $showNone = true;
                                            }
                                            if (method_exists($violation, 'isResolvedByAppeal') && $violation->isResolvedByAppeal()) {
                                                $showNone = true;
                                            }

                                            if ($showNone) {
                                                $penaltyLabel = 'NONE';
                                                $penaltyClass = 'bg-secondary';
                                            } else {
                                                // Get penalty configuration from database
                                                $penaltyConfig = \App\Models\PenaltyConfiguration::where('penalty_code', $violation->penalty)->first();
                                                $penaltyLabel = $penaltyConfig ? $penaltyConfig->short_label : ($violation->penalty ?? 'N/A');
                                                $penaltyClass = $penaltyConfig ? $penaltyConfig->badge_class : 'bg-secondary';
                                            }
                                        @endphp
                                        <span id="penalty-badge-{{ $violation->id }}" class="badge {{ $penaltyClass }}">{{ $penaltyLabel }}</span>
                                    </td>
                                    <td>
                                        <!-- Action Taken Toggle -->
                                        <div class="action-taken-container" style="min-width: 100px;">
                                            <div id="action-taken-display-{{ $violation->id }}">
                                                @if($violation->action_taken)
                                                    <span class="badge bg-success action-taken-badge"
                                                          style="cursor: pointer; font-size: 0.85rem; padding: 0.4rem 0.8rem;"
                                                          data-violation-id="{{ $violation->id }}" data-next-action="no"
                                                          title="Click to mark as 'No Action Taken'"
                                                          onclick="event.stopPropagation(); showActionTakenConfirmation({{ $violation->id }}, false);">
                                                        Yes
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning text-dark action-taken-badge"
                                                          style="cursor: pointer; font-size: 0.85rem; padding: 0.4rem 0.8rem;"
                                                          data-violation-id="{{ $violation->id }}" data-next-action="yes"
                                                          title="Click to mark as 'Action Taken'"
                                                          onclick="event.stopPropagation(); showActionTakenConfirmation({{ $violation->id }}, true);">
                                                        No
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            // Determine consequence status - allow 'pending', 'active', or 'resolved'
                                            if ($violation->consequence_status && in_array($violation->consequence_status, ['pending', 'active', 'resolved'])) {
                                                $consequenceStatus = $violation->consequence_status;
                                            } else {
                                                // If no valid consequence_status set, determine from action_taken
                                                $consequenceStatus = $violation->action_taken ? 'active' : 'resolved';
                                            }

                                            // Detect invalid and x_status rows to build proper details URL
                                            $isInvalidRow = isset($violation->is_invalid_student) && $violation->is_invalid_student;
                                            $submissionIdRow = $isInvalidRow ? ($violation->g16_submission_id ?? $violation->task_submission_id ?? null) : null;
                                            $isXStatusRow = isset($violation->is_x_status) && $violation->is_x_status;
                                            $xStatusId = $isXStatusRow ? ($violation->x_status_id ?? null) : null;
                                            // Extract numeric id fallback from string like 'x_status_12'
                                            if (!$xStatusId && $isXStatusRow) {
                                                try {
                                                    if (!empty($violation->id)) {
                                                        if (preg_match('/(\d+)/', (string)$violation->id, $m)) {
                                                            $xStatusId = (int)($m[1] ?? 0);
                                                        }
                                                    }
                                                } catch (Exception $ex) { /* ignore */ }
                                            }
                                            if ($isInvalidRow && $submissionIdRow) {
                                                $detailsUrl = route('educator.view-invalid-violation', ['submission_id' => $submissionIdRow]);
                                            } elseif ($isXStatusRow) {
                                                $detailsUrl = route('educator.view-xstatus-violation', ['id' => $xStatusId]);
                                            } else {
                                                $detailsUrl = route('educator.view-violation', ['id' => $violation->id]);
                                            }

                                            $statusClass = match(strtolower($consequenceStatus)) {
                                                'pending' => 'bg-warning',
                                                'active' => 'bg-danger',
                                                'resolved' => 'bg-success',
                                                default => 'bg-secondary'
                                            };
                                        @endphp

                                        <div class="status-container" style="min-width: 150px;">
                                            @if($consequenceStatus === 'pending')
                                                <button type="button"
                                                        class="btn btn-sm btn-warning pending-btn"
                                                        data-url="{{ $detailsUrl }}"
                                                        data-student="{{ $violation->student ? ($violation->student->user_fname.' '.$violation->student->user_lname) : 'N/A' }}"
                                                        data-category="{{ $violation->violationType->offenseCategory->category_name ?? 'N/A' }}"
                                                        data-violation="{{ $violation->violationType->violation_name ?? 'N/A' }}"
                                                        data-date="{{ \Carbon\Carbon::parse($violation->violation_date)->format('M d, Y') }}">
                                                    Pending
                                                </button>
                                                <small class="d-block text-muted mt-1">
                                                    <i class="fas fa-clock me-1"></i>Awaiting educator review
                                                </small>
                                            @else
                                                <span class="badge {{ $statusClass }}" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">
                                                    {{ ucfirst($consequenceStatus) }}
                                                </span>
                                                @if($consequenceStatus === 'resolved' && $violation->consequence_end_date)
                                                    <small class="d-block text-muted mt-1">
                                                        <i class="fas fa-check-circle me-1"></i>Ended {{ $violation->consequence_end_date->diffForHumans() }}
                                                    </small>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $isInvalid = isset($violation->is_invalid_student) && $violation->is_invalid_student;
                                            $submissionId = $isInvalid ? ($violation->g16_submission_id ?? $violation->task_submission_id ?? null) : null;
                                        @endphp
                                        @php
                                            $isX = isset($violation->is_x_status) && $violation->is_x_status;
                                            $xid = $isX ? ($violation->x_status_id ?? null) : null;
                                        @endphp
                                        @if($isInvalid && $submissionId)
                                            <a href="{{ route('educator.view-invalid-violation', ['submission_id' => $submissionId]) }}" class="btn btn-primary btn-sm">View</a>
                                        @elseif($isX)
                                            @php
                                                if (!$xid && !empty($violation->id) && preg_match('/(\d+)/', (string)$violation->id, $mm)) {
                                                    $xid = (int)($mm[1] ?? 0);
                                                }
                                            @endphp
                                            <a href="{{ route('educator.view-xstatus-violation', ['id' => $xid]) }}" class="btn btn-primary btn-sm">View</a>
                                        @else
                                            <a href="{{ route('educator.view-violation', ['id' => $violation->id]) }}" class="btn btn-primary btn-sm">View</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center">No violations found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Clean, Text-based Pagination --}}
                @if ($violations->hasPages())
                <nav class="custom-pagination">
                    <ul>
                        {{-- Previous Page Link --}}
                        @if ($violations->onFirstPage())
                            <li class="disabled"><span>&laquo; Previous</span></li>
                        @else
                            <li><a href="{{ $violations->previousPageUrl() }}" rel="prev">&laquo; Previous</a></li>
                        @endif

                        {{-- Pagination Elements --}}
                        @foreach ($violations->links()->elements as $element)
                            {{-- "Three Dots" Separator --}}
                            @if (is_string($element))
                                <li class="disabled"><span>{{ $element }}</span></li>
                            @endif

                            {{-- Array Of Links --}}
                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    @if ($page == $violations->currentPage())
                                        <li class="active"><span>{{ $page }}</span></li>
                                    @else
                                        <li><a href="{{ $url }}">{{ $page }}</a></li>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach

                        {{-- Next Page Link --}}
                        @if ($violations->hasMorePages())
                            <li><a href="{{ $violations->nextPageUrl() }}" rel="next">Next &raquo;</a></li>
                        @else
                            <li class="disabled"><span>Next &raquo;</span></li>
                        @endif
                    </ul>
                </nav>
                @endif
            </section>
        </main>
    </div>

    <!-- Pending Consequence Modal -->
    <div class="modal fade" id="pendingConsequenceModal" tabindex="-1" aria-labelledby="pendingConsequenceLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pendingConsequenceLabel">Consequence Pending</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2"><strong>Student:</strong> <span id="pc-student">N/A</span></p>
                    <p class="mb-2"><strong>Category:</strong> <span id="pc-category">N/A</span></p>
                    <p class="mb-2"><strong>Violation:</strong> <span id="pc-violation">N/A</span></p>
                    <p class="mb-3"><strong>Date:</strong> <span id="pc-date">N/A</span></p>
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <i class="fas fa-clock me-2"></i>
                        <div>
                            This record is awaiting educator review. You can review full details and update the consequence.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a id="pc-review-link" href="#" class="btn btn-primary">Review Now</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        loadAvailableBatches();
        // Delegate clicks for action-taken badges to avoid inline handlers and JS errors
        document.body.addEventListener('click', function(e) {
            const badge = e.target.closest('.action-taken-badge');
            if (badge && badge.dataset && badge.dataset.violationId) {
                const vid = parseInt(badge.dataset.violationId, 10);
                const next = (badge.dataset.nextAction || '').toLowerCase();
                if (!isNaN(vid)) {
                    const newActionTaken = next === 'yes';
                    showActionTakenConfirmation(vid, newActionTaken);
                }
            }
        });
        // Set batch filter from query if present
        const urlParams = new URLSearchParams(window.location.search);
        const selectedBatch = urlParams.get('batch') || 'all';
        const selectedSeverity = urlParams.get('severity') || '';
        const searchInput = document.getElementById('searchInput');
        const severityFilter = document.getElementById('severityFilter');
        const batchSelect = document.getElementById('batchSelect');
        const searchForm = document.getElementById('searchForm');

        // Set severity filter from query if present (case-insensitive match to option values)
        if (selectedSeverity) {
            const foundOpt = Array.from(severityFilter.options).find(o => (o.value || '').toLowerCase() === selectedSeverity.toLowerCase());
            if (foundOpt) {
                foundOpt.selected = true;
            } else {
                // fallback: set directly (useful if server uses exact casing)
                severityFilter.value = selectedSeverity;
            }
        }

        // Inline onchange on selects handles auto-submit
        // Helper: apply severity filter client-side to visible rows
        function applySeverityFilter() {
            const sel = severityFilter.value.trim();
            const rows = document.querySelectorAll('#violation-table-body tr');
            rows.forEach(r => {
                try {
                    const badge = r.querySelector('td:nth-child(5) .badge');
                    const text = badge ? badge.textContent.trim().toLowerCase() : '';
                    if (!sel || sel === '') {
                        r.style.display = '';
                    } else if (text === sel.toLowerCase()) {
                        r.style.display = '';
                    } else {
                        r.style.display = 'none';
                    }
                } catch (e) {
                    // ignore rows that don't match
                }
            });
        }

        // Inline onchange on selects handles auto-submit
        // Function to perform search
        function performSearch() {
            searchForm.requestSubmit();
        }

        // Search on Enter key
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });

        // Search button click (ensure button exists before binding)
        const searchBtn = document.getElementById('searchButton');
        if (searchBtn) {
            searchBtn.addEventListener('click', function() {
                performSearch();
            });
        }

        // Clear search button
        const clearBtn = document.getElementById('clearSearchButton');
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                performSearch();
            });
        }

        // Handle clicks on Pending buttons (works for both regular and invalid rows)
        document.body.addEventListener('click', function(e) {
            const btn = e.target.closest('.pending-btn');
            if (!btn) return;
            try {
                const student = btn.getAttribute('data-student') || 'N/A';
                const category = btn.getAttribute('data-category') || 'N/A';
                const violation = btn.getAttribute('data-violation') || 'N/A';
                const date = btn.getAttribute('data-date') || 'N/A';
                const url = btn.getAttribute('data-url') || '#';

                document.getElementById('pc-student').textContent = student;
                document.getElementById('pc-category').textContent = category;
                document.getElementById('pc-violation').textContent = violation;
                document.getElementById('pc-date').textContent = date;
                const link = document.getElementById('pc-review-link');
                link.setAttribute('href', url);

                const modalEl = document.getElementById('pendingConsequenceModal');
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            } catch (err) {
                console.error('Error showing pending modal', err);
            }
        });

        // Debounced search on input (optional - search as you type)
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                // Only search if there's text or if clearing the search
                const currentSearch = searchInput.value.trim();
                const urlParams = new URLSearchParams(window.location.search);
                const existingSearch = urlParams.get('search') || '';

                // Only perform search if the value has changed
                if (currentSearch !== existingSearch) {
                    performSearch();
                }
            }, 500); // Wait 500ms after user stops typing
        });

        function loadAvailableBatches() {
            fetch('{{ route('educator.available-batches') }}')
                .then(response => response.json())
                .then(data => {
                    batchSelect.innerHTML = '';
                    if (data.success) {
                        data.batches.forEach(batch => {
                            const option = document.createElement('option');
                            option.value = batch.value;
                            option.textContent = `${batch.label}`;
                            if (batch.value === selectedBatch) {
                                option.selected = true;
                            }
                            batchSelect.appendChild(option);
                        });
                    } else {
                        batchSelect.innerHTML = '';
                        const opt = document.createElement('option');
                        opt.value = 'all';
                        opt.textContent = 'All Classes';
                        if (selectedBatch === 'all') opt.selected = true;
                        batchSelect.appendChild(opt);
                    }
                })
                .catch(() => {
                    batchSelect.innerHTML = '';
                    const opt = document.createElement('option');
                    opt.value = 'all';
                    opt.textContent = 'All Classes';
                    if (selectedBatch === 'all') opt.selected = true;
                    batchSelect.appendChild(opt);
                });
        }
    });

    // Note: form submission is handled via native GET; no extra submit handler needed
    // Scroll to and pulse the first highlighted (created) row if present
    document.addEventListener('DOMContentLoaded', function() {
        try {
            const firstHighlight = document.querySelector('.created-highlight');
            if (firstHighlight) {
                firstHighlight.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstHighlight.classList.add('pulse-highlight');
                setTimeout(() => firstHighlight.classList.remove('pulse-highlight'), 3000);
            }
        } catch (e) {
            // ignore
        }
    });

    // Show confirmation dialog for changing action taken status
    function showActionTakenConfirmation(violationId, newActionTaken) {
        // Determine dialog content based on the new action taken value
        const isChangingToNo = !newActionTaken;
        const dialogTitle = isChangingToNo ? 'Confirm Action Change to "No"' : 'Confirm Action Change to "Yes"';
        const alertClass = isChangingToNo ? 'alert-warning' : 'alert-info';
        const iconClass = isChangingToNo ? 'fas fa-exclamation-triangle' : 'fas fa-info-circle';
        const buttonClass = isChangingToNo ? 'btn-warning' : 'btn-success';
        const buttonText = isChangingToNo ? 'Mark as No Action' : 'Mark as Action Taken';

        let alertMessage, questionText, contextText;

        if (isChangingToNo) {
            alertMessage = '<strong>Warning:</strong> Changing action taken to "No" will exclude this violation from penalty escalation counts.';
            questionText = 'Are you sure you want to mark this violation as "No Action Taken"?';
            contextText = 'This violation will still be recorded but won\'t count toward the student\'s penalty escalation.';
        } else {
            alertMessage = '<strong>Note:</strong> Changing action taken to "Yes" will include this violation in penalty escalation counts.';
            questionText = 'Are you sure you want to mark this violation as "Action Taken"?';
            contextText = 'This violation will count toward the student\'s penalty escalation and may trigger disciplinary actions.';
        }

        // Create confirmation dialog
        const confirmDialog = document.createElement('div');
        confirmDialog.className = 'modal fade';
        confirmDialog.id = 'actionTakenConfirmModal';
        confirmDialog.setAttribute('tabindex', '-1');
        confirmDialog.setAttribute('aria-labelledby', 'actionTakenConfirmModalLabel');
        confirmDialog.setAttribute('aria-hidden', 'true');

        confirmDialog.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="actionTakenConfirmModalLabel">${dialogTitle}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert ${alertClass}">
                            <i class="${iconClass} me-2"></i>
                            ${alertMessage}
                        </div>
                        <p>${questionText}</p>
                        <p class="text-muted small">${contextText}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn ${buttonClass}" id="confirmActionTakenBtn">
                            <i class="fas fa-check me-1"></i>
                            ${buttonText}
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(confirmDialog);

        // Initialize the Bootstrap modal
        const modal = new bootstrap.Modal(document.getElementById('actionTakenConfirmModal'));
        modal.show();

        // Handle confirmation
        document.getElementById('confirmActionTakenBtn').addEventListener('click', function() {
            modal.hide();
            updateActionTakenStatus(violationId, newActionTaken);
        });

        // Clean up modal after it's hidden
        confirmDialog.addEventListener('hidden.bs.modal', function() {
            document.body.removeChild(confirmDialog);
        });
    }

    // Update action taken status (extracted from original function)
    function updateActionTakenStatus(violationId, newActionTaken) {
        // Show loading state
        const displayElement = document.getElementById('action-taken-display-' + violationId);
        const originalContent = displayElement.innerHTML;
        displayElement.innerHTML = '<span class="badge bg-secondary">Updating...</span>';

        // Make AJAX request to update action taken status
        fetch('{{ route("educator.update-action-taken") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                violation_id: violationId,
                action_taken: newActionTaken
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the action taken display based on new status
                if (newActionTaken) {
                    displayElement.innerHTML = `
                        <span class="badge bg-success action-taken-badge"
                              style="cursor: pointer; font-size: 0.85rem; padding: 0.4rem 0.8rem;"
                              data-violation-id="${violationId}" data-next-action="no"
                              title="Click to mark as 'No Action Taken'">
                            Yes
                        </span>
                    `;
                } else {
                    displayElement.innerHTML = `
                        <span class="badge bg-warning text-dark action-taken-badge"
                              style="cursor: pointer; font-size: 0.85rem; padding: 0.4rem 0.8rem;"
                              data-violation-id="${violationId}" data-next-action="yes"
                              title="Click to mark as 'Action Taken'">
                            No
                        </span>
                    `;
                }

                // Update penalty and status based on action_taken value
                if (data.data) {
                    // Update penalty display
                    const penaltyElement = document.getElementById('penalty-badge-' + violationId);
                    if (penaltyElement) {
                        if (!newActionTaken) {
                            // Action taken = No: Set penalty to N/A
                            penaltyElement.textContent = 'N/A';
                            penaltyElement.className = 'badge bg-secondary';
                        } else {
                            // Action taken = Yes: Restore penalty if available
                            if (data.data.penalty) {
                                penaltyElement.textContent = data.data.penalty;
                                // Set appropriate penalty class based on penalty code
                                const penaltyClasses = {
                                    'VW': 'badge bg-warning text-dark',
                                    'WW': 'badge bg-danger',
                                    'Pro': 'badge bg-dark',
                                    'T': 'badge bg-danger'
                                };
                                penaltyElement.className = penaltyClasses[data.data.penalty] || 'badge bg-secondary';
                            }
                        }
                    }

                    // Update status display
                    const statusElement = document.getElementById('status-badge-' + violationId);
                    if (statusElement && data.data.status) {
                        if (data.data.status === 'resolved') {
                            statusElement.textContent = 'Resolved';
                            statusElement.className = 'badge bg-primary status-badge';
                            statusElement.style.cursor = 'pointer';
                            statusElement.style.fontSize = '0.85rem';
                            statusElement.style.padding = '0.4rem 0.8rem';
                            statusElement.onclick = function() { showStatusConfirmation(violationId, 'active'); };
                            statusElement.title = 'Click to reactivate this violation';
                        } else if (data.data.status === 'active') {
                            statusElement.textContent = 'Active';
                            statusElement.className = 'badge bg-success status-badge';
                            statusElement.style.cursor = 'pointer';
                            statusElement.style.fontSize = '0.85rem';
                            statusElement.style.padding = '0.4rem 0.8rem';
                            statusElement.onclick = function() { showStatusConfirmation(violationId, 'resolved'); };
                            statusElement.title = 'Click to resolve this violation';
                        }
                    }
                }

                // Update consequence status display
                updateConsequenceStatusDisplay(violationId, newActionTaken);
                }
            } else {
                // Restore original content on error
                displayElement.innerHTML = originalContent;
                alert('Error updating action taken status: ' + data.message);
            }
        })
        .catch(error => {
            // Restore original content on error
            displayElement.innerHTML = originalContent;
            console.error('Error:', error);
            alert('Error updating action taken status. Please try again.');
        });
    }

    // Show confirmation dialog for status changes (resolve/reactivate)
    function showStatusConfirmation(violationId, newStatus) {
        // Determine dialog content based on the new status
        const isResolving = newStatus === 'resolved';
        const dialogTitle = isResolving ? 'Confirm Violation Resolution' : 'Confirm Violation Reactivation';
        const alertClass = isResolving ? 'alert-success' : 'alert-warning';
        const iconClass = isResolving ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
        const buttonClass = isResolving ? 'btn-success' : 'btn-warning';
        const buttonText = isResolving ? 'Resolve Violation' : 'Reactivate Violation';

        let alertMessage, questionText, contextText;

        if (isResolving) {
            alertMessage = '<strong>Note:</strong> Resolving this violation will mark it as completed and handled.';
            questionText = 'Are you sure you want to resolve this violation?';
            contextText = 'This violation will be marked as resolved and will appear in the resolved violations section.';
        } else {
            alertMessage = '<strong>Warning:</strong> Reactivating this violation will move it back to active status.';
            questionText = 'Are you sure you want to reactivate this violation?';
            contextText = 'This violation will be moved back to active status and may require further action.';
        }

        // Create confirmation dialog
        const confirmDialog = document.createElement('div');
        confirmDialog.className = 'modal fade';
        confirmDialog.id = 'statusConfirmModal';
        confirmDialog.setAttribute('tabindex', '-1');
        confirmDialog.setAttribute('aria-labelledby', 'statusConfirmModalLabel');
        confirmDialog.setAttribute('aria-hidden', 'true');

        confirmDialog.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="statusConfirmModalLabel">${dialogTitle}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert ${alertClass}">
                            <i class="${iconClass} me-2"></i>
                            ${alertMessage}
                        </div>
                        <p>${questionText}</p>
                        <p class="text-muted small">${contextText}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn ${buttonClass}" id="confirmStatusBtn">
                            <i class="fas fa-check me-1"></i>
                            ${buttonText}
                        </button>
                    </div>
                </div>
            </div>
        `;

        // Add to DOM
        document.body.appendChild(confirmDialog);

        // Initialize and show modal
        const modal = new bootstrap.Modal(confirmDialog);
        modal.show();

        // Handle confirmation
        document.getElementById('confirmStatusBtn').addEventListener('click', function() {
            updateViolationStatus(violationId, newStatus);
            modal.hide();
        });

        // Clean up modal after it's hidden
        confirmDialog.addEventListener('hidden.bs.modal', function() {
            document.body.removeChild(confirmDialog);
        });
    }

    // Update violation status via AJAX
    function updateViolationStatus(violationId, newStatus) {
        console.log('Updating violation status:', violationId, newStatus); // Debug log

        fetch(`/educator/violation/${violationId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                status: newStatus
            })
        })
        .then(response => {
            console.log('Response status:', response.status); // Debug log
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data); // Debug log
            if (data.success) {
                // Reload the page to reflect the changes
                location.reload();
            } else {
                alert('Error updating violation status: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating violation status. Please try again.');
        });
    }

    // Update consequence status display
    function updateConsequenceStatusDisplay(violationId, actionTaken) {
        // Find the consequence status container for this violation
        const statusContainer = document.querySelector(`tr:has(a[href*="${violationId}"]) .status-container`);
        if (statusContainer) {
            if (!actionTaken) {
                // No action taken - consequence is resolved
                statusContainer.innerHTML = `
                    <span class="badge bg-success" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">
                        Resolved
                    </span>
                    <small class="d-block text-muted mt-1">
                        <i class="fas fa-times-circle me-1"></i>No action taken
                    </small>
                `;
            } else {
                // Action taken - consequence is active
                statusContainer.innerHTML = `
                    <span class="badge bg-danger" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">
                        Active
                    </span>
                `;
            }
        }
    }

</script>
@endsection