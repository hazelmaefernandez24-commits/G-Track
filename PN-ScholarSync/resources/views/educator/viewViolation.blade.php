@extends('layouts.educator')

@section('content')
    <!-- Main Container -->
    <div class="container">
        <h2>Violation Details</h2>
        
        <!-- Violation Details Section -->
        <div class="violation-details">
            @php
                // Detect violations coming from G16_CAPSTONE bridge
                $fromG16 = (isset($violation->is_invalid_student) && $violation->is_invalid_student)
                    || isset($violation->g16_submission_id)
                    || isset($violation->task_submission_id)
                    || (($violation->prepared_by ?? '') === 'G16 Bridge');
            @endphp
            <!-- Student Information -->
            <div class="detail-row">
                <div class="detail-label">Student:</div>
                <div class="detail-value">
                    @if($violation->student)
                        {{ $violation->student->user_fname }} {{ $violation->student->user_lname }}
                    @else
                        <span class="text-danger">Student data not available (ID: {{ $violation->student_id }})</span>
                    @endif
                </div>
            </div>
            
            <!-- Violation Date -->
            <div class="detail-row">
                <div class="detail-label">Violation Date:</div>
                <div class="detail-value">{{ \Carbon\Carbon::parse($violation->violation_date)->format('M d, Y') }}</div>
            </div>
            
            <!-- Category Information -->
            <div class="detail-row">
                <div class="detail-label">Category:</div>
                <div class="detail-value">
                    @if($violation->offenseCategory)
                        {{ $violation->offenseCategory->category_name }}
                    @else
                        <span class="text-danger">Category not available</span>
                    @endif
                </div>
            </div>
            
            <!-- Violation Type -->
            <div class="detail-row">
                <div class="detail-label">Violation Type:</div>
                <div class="detail-value">
                    @if($fromG16)
                        Center Tasking
                    @else
                        @if($violation->violationType)
                            {{ $violation->violationType->violation_name }}
                        @else
                            <span class="text-danger">Violation type not available</span>
                        @endif
                    @endif
                </div>
            </div>
            
            <!-- Severity Level -->
            <div class="detail-row">
                <div class="detail-label">Severity:</div>
                <div class="detail-value {{ strtolower($violation->severity) }}">{{ $violation->severity }}</div>
            </div>
            
            <!-- Offense Number -->
            <!-- <div class="detail-row">
                <div class="detail-label">Offense:</div>
                <div class="detail-value">{{ $violation->offense }}</div>
            </div>
             -->
            <!-- Penalty Information -->
            <div class="detail-row">
                <div class="detail-label">Penalty:</div>
                <div class="detail-value">
                    @php
                        $penaltyText = match($violation->penalty) {
                            'VW' => 'Verbal Warning',
                            'V' => 'Verbal Warning',
                            'WW' => 'Written Warning',
                            'W' => 'Written Warning',
                            'Pro' => 'Probation',
                            'P' => 'Probation',
                            'T' => 'Termination of Contract',
                            default => $violation->penalty ?? 'Not Assigned'
                        };
                    @endphp
                    {{ $penaltyText }}
                </div>
            </div>
            
            <!-- Consequence Details -->
            <div class="detail-row">
                <div class="detail-label">Consequence:</div>
                <div class="detail-value">
                    @if($fromG16)
                        N/A
                    @elseif(isset($violation->is_x_status) && $violation->is_x_status)
                        N/A
                    @elseif(($violation->consequence_status ?? null) === 'pending' || $violation->consequence === 'Pending educator review')
                        @if(!empty($violation->id))
                        <form id="consequenceForm" method="POST" action="{{ route('educator.update-consequence', $violation->id) }}">
                            @csrf
                            @method('PUT')
                            
                            <div class="consequence-edit-section">
                                <select class="form-control mb-2" id="consequence-select" name="consequence_select" style="max-width: 300px;">
                                    <option value="" selected disabled>Select a consequence</option>
                                    <option value="No cellphone">No cellphone</option>
                                    <option value="No going out">No going out</option>
                                    <option value="Community Service">Community Service</option>
                                    <option value="Kitchen team">Kitchen team</option>
                                    <option value="No internet access">No internet access</option>
                                    <option value="Extra assignment">Extra assignment</option>
                                    <option value="Suspension">Suspension</option>
                                    <option value="Detention">Detention</option>
                                    <option value="other">Other (specify below)</option>
                                </select>
                                
                                <input type="text" class="form-control mb-2" id="consequence-input" name="consequence" placeholder="Enter custom consequence" style="display:none; max-width: 300px;" />
                                
                                <!-- Duration Input -->
                                <div id="duration-group" style="display:none;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <input type="number" class="form-control" id="duration-value" name="duration_value" placeholder="Duration" min="1" max="365" style="max-width: 150px;" />
                                        </div>
                                        <div class="col-md-6">
                                            <select class="form-control" id="duration-unit" name="duration_unit" style="max-width: 150px;">
                                                <option value="days">Days</option>
                                                <option value="weeks">Weeks</option>
                                                <option value="months">Months</option>
                                                <option value="hours">Hours</option>
                                                <option value="minutes">Minutes</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-sm mt-2">Update Consequence</button>
                            </div>
                        </form>
                        @else
                            <span class="text-muted">Pending educator review</span>
                        @endif
                    @else
                        {{ $violation->consequence }}
                        @if($violation->consequence_duration_value && $violation->consequence_duration_unit)
                            for {{ $violation->consequence_duration_value }} {{ $violation->consequence_duration_unit }}
                        @endif
                    @endif
                </div>
            </div>

            <!-- Incident Details Section -->
            @if($fromG16 || $violation->incident_datetime || $violation->incident_place || $violation->incident_details || $violation->prepared_by)
                <div class="incident-details-section">
                    <h3 class="section-title">Incident Details</h3>
                    @if($fromG16)
                        <div class="detail-row">
                            <div class="detail-label">Incident Details:</div>
                            <div class="detail-value incident-description">Validated by admin</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Prepared By:</div>
                            <div class="detail-value">N/A</div>
                        </div>
                    @else
                        @if($violation->incident_datetime)
                            <div class="detail-row">
                                <div class="detail-label">Date & Time of Incident:</div>
                                <div class="detail-value">{{ \Carbon\Carbon::parse($violation->incident_datetime)->format('M d, Y g:i A') }}</div>
                            </div>
                        @endif

                        @if($violation->incident_place)
                            <div class="detail-row">
                                <div class="detail-label">Place of Incident:</div>
                                <div class="detail-value">{{ $violation->incident_place }}</div>
                            </div>
                        @endif

                        @if($violation->incident_details)
                            <div class="detail-row">
                                <div class="detail-label">Incident Details:</div>
                                <div class="detail-value incident-description">{{ $violation->incident_details }}</div>
                            </div>
                        @endif

                        @if($violation->prepared_by)
                            <div class="detail-row">
                                <div class="detail-label">Prepared By:</div>
                                <div class="detail-value">{{ $violation->prepared_by }}</div>
                            </div>
                        @endif
                    @endif
                </div>
            @endif

            <!-- Violation Status -->
            <div class="detail-row">
                <div class="detail-label">Status:</div>
                <div class="detail-value {{ strtolower($violation->status) }}">{{ ucfirst($violation->status) }}</div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="{{ route('educator.violation') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        /* Container Layout */
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Violation Details Card */
        .violation-details {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        /* Detail Row Layout */
        .detail-row {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        /* Label Styling */
        .detail-label {
            width: 150px;
            font-weight: bold;
            color: #666;
        }
        
        /* Value Styling */
        .detail-value {
            flex: 1;
        }

        /* Incident Details Section */
        .incident-details-section {
            margin: 25px 0;
            padding: 20px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            border-left: 4px solid #4299e1;
        }

        .section-title {
            color: #2d3748;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e2e8f0;
        }

        .incident-description {
            line-height: 1.6;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        /* Action Buttons Container */
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        /* Button Base Styling */
        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        /* Primary Button */
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        /* Secondary Button */
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        /* Button Hover Effect */
        .btn:hover {
            opacity: 0.9;
        }
        
        /* Severity Level Colors */
        .low { color: #28a745; }      /* Green */
        .medium { color: #ffc107; }   /* Yellow */
        .high { color: #fd7e14; }     /* Orange */
        .very-high { color: #dc3545; } /* Red */
        
        /* Status Colors */
        .pending { color: #ffc107; }   /* Yellow */
        .resolved { color: #28a745; }  /* Green */
        .cancelled { color: #dc3545; } /* Red */
        
        /* Consequence Edit Section */
        .consequence-edit-section {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            background-color: #f8f9fa;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const consequenceSelect = document.getElementById('consequence-select');
            const consequenceInput = document.getElementById('consequence-input');
            const durationGroup = document.getElementById('duration-group');
            const durationValue = document.getElementById('duration-value');
            const durationUnit = document.getElementById('duration-unit');

            if (consequenceSelect) {
                consequenceSelect.addEventListener('change', function() {
                    // Consequences that require duration
                    const consequencesWithDuration = [
                        'No cellphone', 'No going out', 'Community Service',
                        'Kitchen team', 'No internet access', 'Suspension', 'Detention'
                    ];

                    if (this.value === 'other') {
                        consequenceInput.style.display = 'block';
                        consequenceInput.required = true;
                        consequenceInput.value = '';
                        durationGroup.style.display = 'none';
                        durationValue.required = false;
                    } else if (consequencesWithDuration.includes(this.value)) {
                        consequenceInput.style.display = 'none';
                        consequenceInput.required = false;
                        durationGroup.style.display = 'block';
                        durationValue.required = true;
                    } else {
                        consequenceInput.style.display = 'none';
                        consequenceInput.required = false;
                        consequenceInput.value = this.value;
                        durationGroup.style.display = 'none';
                        durationValue.required = false;
                    }
                });
            }

            // Handle form submission
            const consequenceForm = document.getElementById('consequenceForm');
            if (consequenceForm) {
                consequenceForm.addEventListener('submit', function(e) {
                    const actionTaken = true; // Since this is an existing violation with action taken
                    const consequenceSelect = document.getElementById('consequence-select');
                    const consequenceInput = document.getElementById('consequence-input');
                    const durationValue = document.getElementById('duration-value');
                    const durationUnit = document.getElementById('duration-unit');

                    // Validate consequence
                    if (!consequenceSelect.value && !consequenceInput.value.trim()) {
                        e.preventDefault();
                        alert('Please select or enter a consequence.');
                        return false;
                    }

                    // Consequences that require duration
                    const consequencesWithDuration = [
                        'No cellphone', 'No going out', 'Community Service',
                        'Kitchen team', 'No internet access', 'Suspension', 'Detention'
                    ];

                    // Validate duration for consequences that require it
                    if (consequenceSelect.value && consequencesWithDuration.includes(consequenceSelect.value)) {
                        if (!durationValue.value || !durationUnit.value) {
                            e.preventDefault();
                            alert('Please specify the duration for this consequence.');
                            return false;
                        }

                        if (parseInt(durationValue.value) <= 0) {
                            e.preventDefault();
                            alert('Duration must be a positive number.');
                            return false;
                        }
                    }

                    // Ensure consequence field has appropriate value
                    if (consequenceSelect.value && consequenceSelect.value !== 'other') {
                        // Use the selected dropdown value
                        consequenceInput.value = consequenceSelect.value;
                        consequenceInput.name = 'consequence';
                    } else if (consequenceSelect.value === 'other') {
                        // Use the custom input value
                        consequenceInput.name = 'consequence';
                    }

                    return true;
                });
            }
        });
    </script>
@endsection 