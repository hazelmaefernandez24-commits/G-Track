@if($grades->count() > 0)
    <div class="grades-container">
        <!-- Grades Table -->
        <div class="card shadow-sm mb-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Subject Code</th>
                                <th>Subject Name</th>
                                <th>Term</th>
                                <th>Academic Year</th>
                                <th>Units</th>
                                <th>Grade</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($grades as $subject)
                                @php
                                    // Handle both object and array access
                                    $subjectData = (object) $subject;
                                    $grade = $subjectData->grade ?? ($subjectData->pivot->grade ?? null);
                                    $status = strtolower($subjectData->status ?? ($subjectData->pivot->status ?? 'pending'));
                                    $statusClass = [
                                        'approved' => 'bg-success',
                                        'pending' => 'bg-warning',
                                        'rejected' => 'bg-danger',
                                        'incomplete' => 'bg-info',
                                        'no credit' => 'bg-secondary',
                                        'dropped' => 'bg-dark',
                                        'passed' => 'bg-success',
                                        'failed' => 'bg-danger'
                                    ][$status] ?? 'bg-secondary';

                                    // Format grade display
                                    $gradeDisplay = is_numeric($grade) ? number_format($grade, 2) : $grade;
                                    $subjectCode = $subjectData->subject_code ?? ($subjectData->code ?? '');
                                    $subjectName = $subjectData->subject_name ?? ($subjectData->name ?? 'Unnamed Subject');
                                    $units = isset($subjectData->units) ? (is_numeric($subjectData->units) ? number_format($subjectData->units, 1) : $subjectData->units) : '0.0';
                                @endphp
                                @if(!is_null($grade) && $grade !== '' && $grade !== 'N/A')
                                    <tr>
                                        <td class="subject-code">{{ $subjectCode }}</td>
                                        <td class="subject-name">{{ $subjectName }}</td>
                                        <td>{{ ucfirst($subject->term ?? 'N/A') }}</td>
                                        <td>{{ $subject->academic_year ?? 'N/A' }}</td>
                                        <td>{{ $units }}</td>
                                        <td class="grade-value {{ $grade < 75 ? 'text-danger' : 'text-success' }} fw-bold">
                                            {{ $gradeDisplay }}
                                        </td>
                                        <td>
                                            <span class="badge {{ $statusClass }}">
                                                {{ ucfirst($status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        .subject-code {
            font-weight: 600;
            color: #2c3e50;
        }
        .subject-name {
            color: #495057;
        }
        .grade-value {
            font-weight: 600;
        }
        .card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1rem 1.25rem;
        }
        .table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            color: #6c757d;
            border-top: none;
            border-bottom: 1px solid #e9ecef;
        }
        .table td {
            vertical-align: middle;
            border-color: #e9ecef;
        }
        .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
            font-size: 0.75em;
        }
    </style>
@endif