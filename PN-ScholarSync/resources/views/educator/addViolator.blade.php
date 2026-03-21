@extends('layouts.educator')

@section('css')
    <!-- External CSS Dependencies -->
    <link rel="stylesheet" href="{{ asset('css/educator/addViolator.css') }}">
    <!-- Select2 CSS for autocomplete -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
    <!-- Main Content Wrapper -->
    <div class="content-wrapper">
        <!-- Back Button -->

        <!-- Form Container -->
        <div class="form-container">
            <h2 class="form-title">Add New Violator {{ isset($groupMode) && $groupMode ? '(Group)' : '' }}</h2>
            <div id="termination-alert" class="alert alert-warning" style="display:none; margin-bottom: 15px; background-color: #fff3cd; border-color: #ffeeba; color: #856404; padding: 15px; border-radius: 4px;"></div>
            
            @if (session('success'))
                <div class="alert alert-success" style="margin-bottom: 15px;">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger" style="margin-bottom: 15px;">
                    {{ session('error') }}
                </div>
            @endif
            
            <!-- Violation Form -->
            <form id="violatorForm" class="violation-form" method="POST" action="{{ isset($groupMode) && $groupMode ? route('educator.add-violator-group') : route('educator.add-violator') }}">
                @csrf
                @if(isset($groupMode) && $groupMode)
                <input type="hidden" id="student_ids_json" name="student_ids_json" value="">
                @endif
                <!-- Display validation errors if any -->
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <!-- Student Selection -->
                    <div class="form-group">
                        <label for="student-select">{{ isset($groupMode) && $groupMode ? 'Students (search and select multiple)' : 'Student (search and select)' }}</label>
                        <select class="form-field" id="student-select" name="{{ isset($groupMode) && $groupMode ? 'student_ids[]' : 'student_id' }}" {{ isset($groupMode) && $groupMode ? 'multiple' : '' }} style="width:100%">
                            <!-- Options will be loaded via AJAX -->
                        </select>
                        @if(isset($groupMode) && $groupMode)
                            <small class="form-help">You can search and select multiple students.</small>
                        @endif
                    </div>

                <!-- Violation Date -->
                <div class="form-group">
                    <label for="violation-date">Violation Date</label>
                    <input type="date" class="form-field" id="violation-date" name="violation_date" value="{{ date('Y-m-d') }}" />
                </div>

                <!-- Violation Category -->
                <div class="form-group">
                    <label for="violation-category">Category</label>
                    <select class="form-field" id="violation-category" name="category_id">
                        <option value="" selected disabled>Select Category</option>
                        @foreach($offenseCategories as $category)
                            <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Violation Type -->
                <div class="form-group">
                    <label for="violation-type">Type of Violation <span class="text-danger">*</span></label>
                    <select class="form-field" id="violation-type" name="violation_type_id" required>
                        <option value="" selected disabled>Select Violation Type</option>
                    </select>
                </div>

                <!-- Severity Selection -->
                <div class="form-group" id="severity-group">
                    <label for="severity">Severity</label>
                    <input type="text" class="form-field" id="severity" name="severity" readonly />
                </div>

                <!-- Offense Count (Auto-filled, Read-only) -->
                <div class="form-group">
                    <label for="offense-count">Offense Count</label>
                    @if(isset($groupMode) && $groupMode)
                        <textarea id="offense-count" name="offense_count" class="form-field" rows="4" readonly placeholder="Select students, category and violation type"></textarea>
                    @else
                        <input type="text" id="offense-count" name="offense_count" class="form-field" readonly placeholder="Select student and violation type">
                    @endif
                </div>

                <!-- Penalty (Auto-filled, Read-only) -->
                <div class="form-group">
                    <label for="penalty">Penalty</label>
                    @if(isset($groupMode) && $groupMode)
                        <textarea id="penalty" name="penalty" class="form-field" rows="4" readonly placeholder="Calculated per student"></textarea>
                    @else
                        <input type="text" id="penalty" name="penalty" class="form-field" readonly>
                    @endif
                </div>

                <!-- Action Taken -->
                <div class="form-group">
                    <label for="action_taken">Action Taken</label>
                    <select id="action_taken" name="action_taken" class="form-field" required onchange="toggleConsequenceField()">
                        <option value="1" selected>Yes</option>
                        <option value="0">No</option>
                    </select>
                    <small class="form-help">Select "No" if no disciplinary action was taken. Violations with "No" action taken will not count towards penalty escalation but will still be recorded.</small>
                </div>

                <!-- Consequence Input -->
                <div class="form-group" id="consequence-group">
                    <label for="consequence">Consequence <span id="consequence-required" class="text-danger">*</span></label>
                    <select class="form-field" id="consequence-select" name="consequence_select">
                        <option value="" selected disabled>Select a recommended consequence</option>
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
                    <input type="text" class="form-field" id="consequence-input" name="consequence" placeholder="Enter custom consequence" style="display:none; margin-top:8px;" />
                    <small id="consequence-help" class="form-help">Select or specify the consequence for this violation.</small>
                </div>

                <!-- Duration Input -->
                <div class="form-group" id="duration-group" style="display:none;">
                    <label for="duration">Duration <span class="text-danger">*</span></label>
                    <div class="row">
                        <div class="col-md-6">
                            <input type="number" class="form-field" id="duration-value" name="duration_value" placeholder="Enter duration" min="1" max="365" />
                        </div>
                        <div class="col-md-6">
                            <select class="form-field" id="duration-unit" name="duration_unit">
                                <option value="days">Days</option>
                                <option value="weeks">Weeks</option>
                                <option value="months">Months</option>
                                <option value="hours">Hours</option>
                            </select>
                        </div>
                    </div>
                    <small class="form-help">Specify how long this consequence should last.</small>
                </div>



                <!-- Incident Details Section -->
                <div class="form-section">
                    <h3 class="section-title">Incident Details</h3>

                    <!-- Incident Date and Time -->
                    <div class="form-group">
                        <label for="incident-datetime">Date & Time of Incident</label>
                        <input type="datetime-local" class="form-field" id="incident-datetime" name="incident_datetime" />
                    </div>

                    <!-- Incident Place -->
                    <div class="form-group">
                        <label for="incident-place">Place of Incident</label>
                        <input type="text" class="form-field" id="incident-place" name="incident_place" placeholder="Enter location where incident occurred" />
                    </div>

                    <!-- Incident Details -->
                    <div class="form-group">
                        <label for="incident-details">Incident Details</label>
                        <textarea class="form-field" id="incident-details" name="incident_details" rows="4" placeholder="Describe what happened in detail..."></textarea>
                    </div>

                    <!-- Prepared By -->
                    <div class="form-group">
                        <label for="prepared-by">Prepared By</label>
                        <input type="text" class="form-field" id="prepared-by" name="prepared_by" value="{{ Auth::user()->user_fname }} {{ Auth::user()->user_lname }}" readonly />
                    </div>
                </div>

                <!-- Hidden Status Field -->
                <input type="hidden" name="status" value="active" />

                <!-- Form Action Buttons -->
                <div class="form-actions">
                    <button type="button" class="cancel-btn">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-plus"></i> Add Violator
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Select2 JS include
    const select2Script = document.createElement('script');
    select2Script.src = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js';
    document.head.appendChild(select2Script);

    select2Script.onload = function() {
        // Initialize Select2 for student selection
        $('#student-select').select2({
            placeholder: '{{ isset($groupMode) && $groupMode ? 'Search and select students' : 'Search and select a student' }}',
            minimumInputLength: 2,
            ajax: {
                url: '/api/search-students', // You need to implement this endpoint
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term // search term
                    };
                },
                processResults: function (data) {
                    // data should be an array of students: [{id:..., text:...}]
                    return {
                        results: data
                    };
                },
                cache: true
            },
            multiple: {{ isset($groupMode) && $groupMode ? 'true' : 'false' }},
            width: 'resolve'
        });
    };
    // =============================================
    // Navigation & Form Event Handlers
    // =============================================
    document.querySelector('.back-btn').addEventListener('click', (e) => {
        e.preventDefault();
        window.history.back();
    });

    document.querySelector('.cancel-btn').addEventListener('click', (e) => {
        e.preventDefault();
        window.history.back();
    });

    document.getElementById('violation-category').addEventListener('change', function() {
        const categoryId = this.value;
        const violationTypeSelect = document.getElementById('violation-type');
        
        // Reset dependent fields
        violationTypeSelect.innerHTML = '<option value="" selected disabled>Select Violation Type</option>';
        document.getElementById('severity').value = '';
        updatePenaltyAndCheckTermination();
        
        if (categoryId) {
            fetch(`/api/violation-types/${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        data.forEach(violation => {
                            const option = document.createElement('option');
                            option.value = violation.id;
                            option.textContent = violation.violation_name;
                            option.dataset.severity = violation.severity;
                            violationTypeSelect.appendChild(option);
                        });
                    } else {
                        violationTypeSelect.innerHTML = '<option value="" disabled>No types found</option>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching violation types:', error);
                    violationTypeSelect.innerHTML = '<option value="" disabled>Error loading types</option>';
                });
        }
    });

    document.getElementById('violation-type').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption && selectedOption.dataset.severity) {
            document.getElementById('severity').value = selectedOption.dataset.severity;
        } else {
            document.getElementById('severity').value = '';
        }
        updatePenaltyAndCheckTermination();
    });

    document.getElementById('student-select').addEventListener('change', async function() {
        const isGroup = {{ isset($groupMode) && $groupMode ? 'true' : 'false' }};
        const studentId = isGroup ? (this.selectedOptions[0]?.value || '') : this.value;
        const terminationAlert = document.getElementById('termination-alert');
        const submitButton = document.querySelector('.submit-btn');

        console.log('Student selected:', studentId);

        // Always reset form to a default state on student change
        terminationAlert.style.display = 'none';
        submitButton.disabled = false;

        if (!studentId) {
            updatePenaltyAndCheckTermination(); // Clears penalty and re-evaluates
            return;
        }

        // Update penalty calculation when student changes
        updatePenaltyAndCheckTermination();
    });

    document.getElementById('consequence-select').addEventListener('change', function() {
        const input = document.getElementById('consequence-input');
        const durationGroup = document.getElementById('duration-group');
        const durationValue = document.getElementById('duration-value');
        const durationUnit = document.getElementById('duration-unit');

        // Consequences that require duration
        const consequencesWithDuration = [
            'No cellphone', 'No going out', 'Community Service',
            'Kitchen team', 'No internet access', 'Suspension', 'Detention'
        ];

        if (this.value === 'other') {
            input.style.display = 'block';
            input.required = true;
            input.value = '';
            durationGroup.style.display = 'none';
            durationValue.required = false;
        } else if (consequencesWithDuration.includes(this.value)) {
            input.style.display = 'none';
            input.required = false;
            durationGroup.style.display = 'block';
            durationValue.required = true;
            // Don't set input.value yet - will be set when form is submitted
        } else {
            input.style.display = 'none';
            input.required = false;
            input.value = this.value;
            durationGroup.style.display = 'none';
            durationValue.required = false;
        }
    });

    // =============================================
    // Helper Functions
    // =============================================
    function getPenalty(severity, offenseCount = 1) {
        console.log('getPenalty called with severity:', severity, 'offenseCount:', offenseCount);

        if (!severity) {
            return '';
        }

        // Define penalty progression for each severity with escalation after maximum
        const severityConfig = {
            'Low': {
                maxCount: 3,
                basePenalty: 'Verbal Warning', // 1st-3rd offense
                escalatedPenalty: 'Written Warning' // 4th+ offense
            },
            'Medium': {
                maxCount: 2,
                basePenalty: 'Written Warning', // 1st-2nd offense
                escalatedPenalty: 'Probationary of Contract' // 3rd+ offense
            },
            'High': {
                maxCount: 2,
                basePenalty: 'Probationary of Contract', // 1st-2nd offense
                escalatedPenalty: 'Termination of Contract' // 3rd+ offense
            },
            'Very High': {
                maxCount: 1,
                basePenalty: 'Termination of Contract', // 1st offense
                escalatedPenalty: 'Termination of Contract' // 2nd+ offense (no escalation possible)
            }
        };

        const config = severityConfig[severity.trim()] || {maxCount: 3, basePenalty: 'Verbal Warning', escalatedPenalty: 'Written Warning'};
        const maxCount = config.maxCount;
        const basePenalty = config.basePenalty;
        const escalatedPenalty = config.escalatedPenalty;

        // Implement cyclic penalty escalation based on offense count
        return calculateEscalatedPenalty(offenseCount, maxCount, basePenalty, escalatedPenalty);
    }

    /**
     * Calculate escalated penalty based on offense count and maximum count thresholds
     * Implements cyclic escalation: every max_count range gets the next penalty level
     * Example for Low severity (max_count=3): 1-3=VW, 4-6=WW, 7-9=Pro, 10+=T
     */
    function calculateEscalatedPenalty(offenseCount, maxCount, basePenalty, escalatedPenalty) {
        // Define penalty progression order (from lowest to highest severity)
        const penalties = [
            'Verbal Warning',
            'Written Warning',
            'Probationary of Contract',
            'Termination of Contract'
        ];

        // Find the index of the base penalty
        const basePenaltyIndex = penalties.indexOf(basePenalty);
        if (basePenaltyIndex === -1) {
            // If base penalty not found, return escalated penalty as fallback
            return escalatedPenalty;
        }

        // Calculate which penalty range the offense count falls into
        // Range 1: 1 to maxCount (use base penalty)
        // Range 2: (maxCount + 1) to (2 * maxCount) (use next penalty)
        // Range 3: (2 * maxCount + 1) to (3 * maxCount) (use next penalty)
        // etc.

        const penaltyRangeIndex = Math.ceil(offenseCount / maxCount) - 1;

        // Calculate target penalty index
        let targetPenaltyIndex = basePenaltyIndex + penaltyRangeIndex;

        // Ensure we don't exceed the maximum penalty level
        const maxPenaltyIndex = penalties.length - 1;
        targetPenaltyIndex = Math.min(targetPenaltyIndex, maxPenaltyIndex);

        // Return the calculated penalty
        return penalties[targetPenaltyIndex];
    }

    async function updatePenaltyAndCheckTermination() {
        const severity = document.getElementById('severity').value;
        const isGroup = {{ isset($groupMode) && $groupMode ? 'true' : 'false' }};
        const studentSelect = document.getElementById('student-select');
        const studentId = isGroup ? (studentSelect.selectedOptions[0]?.value || '') : studentSelect.value;
        const violationTypeId = document.getElementById('violation-type').value;
        const penaltyInput = document.getElementById('penalty');
        const offenseCountInput = document.getElementById('offense-count');
        const submitButton = document.querySelector('.submit-btn');
        const terminationAlert = document.getElementById('termination-alert');

        let newPenalty = '';
        let offenseCount = 1;

        // Group mode multi-line aggregation for offense/penalty
        if (isGroup) {
            const selected = Array.from(studentSelect.selectedOptions);
            const violationTypeEl = document.getElementById('violation-type');
            const selectedTypeOption = violationTypeEl.options[violationTypeEl.selectedIndex];
            const currentSeverity = document.getElementById('severity').value;

            // Reset
            offenseCountInput.value = '';
            penaltyInput.value = '';

            if (selected.length > 0 && selectedTypeOption && currentSeverity) {
                const linesOffense = [];
                const linesPenalty = [];
                let terminatedCount = 0;
                for (const opt of selected) {
                    const sid = opt.value;
                    const label = opt.textContent || sid;
                    try {
                        const url = `/educator/check-existing-violations?student_id=${encodeURIComponent(sid)}&violation_type_id=${encodeURIComponent(violationTypeEl.value)}&t=${Date.now()}`;
                        const res = await fetch(url);
                        if (res.ok) {
                            const data = await res.json();
                            if (data && data.success) {
                                if (data.isTerminated) {
                                    terminatedCount++;
                                    // Annotate the line but do not block unless all are terminated
                                    linesOffense.push(`${label} - Already terminated`);
                                    linesPenalty.push(`${label} - N/A`);
                                } else {
                                    const num = data.offenseCount || 1;
                                    const ordinal = getOrdinalSuffix(num);
                                    linesOffense.push(`${label} - ${num}${ordinal} offense in ${data.severity} severity`);
                                    linesPenalty.push(`${label} - ${convertPenaltyCodeToText(data.finalPenalty || data.calculatedPenalty)}`);
                                }
                            } else {
                                linesOffense.push(`${label} - 1st offense in ${currentSeverity} severity`);
                                linesPenalty.push(`${label} - ${convertPenaltyCodeToText('VW')}`);
                            }
                        } else {
                            linesOffense.push(`${label} - 1st offense in ${currentSeverity} severity`);
                            linesPenalty.push(`${label} - ${convertPenaltyCodeToText('VW')}`);
                        }
                    } catch (_) {
                        linesOffense.push(`${label} - 1st offense in ${currentSeverity} severity`);
                        linesPenalty.push(`${label} - ${convertPenaltyCodeToText('VW')}`);
                    }
                }
                offenseCountInput.value = linesOffense.join('\n');
                penaltyInput.value = linesPenalty.join('\n');

                // If ALL selected are terminated, show error and disable submit; otherwise hide error
                if (terminatedCount > 0 && terminatedCount === selected.length) {
                    terminationAlert.innerHTML = '<strong>Error:</strong> This student has already been terminated. No additional violations can be added.';
                    terminationAlert.style.display = 'block';
                    terminationAlert.className = 'alert alert-danger';
                    submitButton.disabled = true;
                } else {
                    terminationAlert.style.display = 'none';
                    terminationAlert.className = 'alert alert-warning';
                    submitButton.disabled = false;
                }

                // Do not proceed to single-student logic when in group mode
                return;
            }
        }

        // If we have both student and violation type, fetch offense count information
        if (severity && studentId && violationTypeId) {
            try {
                const url = `/educator/check-existing-violations?student_id=${studentId}&violation_type_id=${violationTypeId}&t=${new Date().getTime()}`;
                console.log('Fetching offense count from:', url);

                const response = await fetch(url);
                if (response.ok) {
                    const data = await response.json();
                    console.log('Offense count API Response:', data);

                    if (data.success) {
                        // Check if student is already terminated
                        if (data.isTerminated) {
                            // Student is already terminated - prevent adding more violations
                            terminationAlert.innerHTML = '<strong>Error:</strong> This student has already been terminated. No additional violations can be added.';
                            terminationAlert.style.display = 'block';
                            terminationAlert.className = 'alert alert-danger';
                            submitButton.disabled = true;
                            
                            // Clear penalty and offense count
                            penaltyInput.value = 'Student Already Terminated';
                            offenseCountInput.value = 'Cannot add violations to terminated student';
                            
                            return; // Exit early, don't process further
                        }
                        
                        // Use the final penalty from the backend and offense count
                        newPenalty = convertPenaltyCodeToText(data.finalPenalty);
                        offenseCount = data.offenseCount || 1;

                        // Update offense count display with escalation information
                        const ordinalSuffix = getOrdinalSuffix(offenseCount);
                        if (data.isEscalated) {
                            offenseCountInput.value = `${offenseCount}${ordinalSuffix} offense in ${severity} severity (escalated - exceeds max: ${data.maxCount})`;
                        } else {
                            offenseCountInput.value = `${offenseCount}${ordinalSuffix} offense in ${severity} severity (max: ${data.maxCount})`;
                        }
                    } else {
                        // Fallback to simple severity-based penalty
                        newPenalty = getPenalty(severity, 1);
                        offenseCount = 1;

                        // Update offense count display for fallback
                        const ordinalSuffix = getOrdinalSuffix(offenseCount);
                        offenseCountInput.value = `${offenseCount}${ordinalSuffix} offense in ${severity} severity`;
                    }
                } else {
                    // Fallback to simple severity-based penalty
                    newPenalty = getPenalty(severity, 1);
                    offenseCount = 1;

                    // Update offense count display for fallback
                    const ordinalSuffix = getOrdinalSuffix(offenseCount);
                    offenseCountInput.value = `${offenseCount}${ordinalSuffix} offense in ${severity} severity`;
                }
            } catch (error) {
                console.error('Error fetching offense count:', error);
                // Fallback to simple severity-based penalty
                newPenalty = getPenalty(severity, 1);
                offenseCount = 1;

                // Update offense count display for error fallback
                const ordinalSuffix = getOrdinalSuffix(offenseCount);
                offenseCountInput.value = `${offenseCount}${ordinalSuffix} offense in ${severity} severity`;
            }
        } else if (severity) {
            // Default to 1st offense if we don't have student/violation type info
            newPenalty = getPenalty(severity, 1);
            offenseCount = 1;

            // Clear offense count display when missing student/violation type
            offenseCountInput.value = '';
        } else {
            // Clear offense count display when no severity
            offenseCountInput.value = '';
        }

        // Update penalty field (single-student mode only)
        if (!isGroup) {
            penaltyInput.value = newPenalty;
        }

        // --- Termination Check ---
        const isTerminationPenalty = newPenalty === 'Termination of Contract';

        if (isTerminationPenalty) {
            let message = '<strong>Warning:</strong> This violation results in termination of contract.';
            terminationAlert.innerHTML = message;
            terminationAlert.style.display = 'block';
            terminationAlert.className = 'alert alert-warning';
            submitButton.disabled = false; // Allow submission for new termination
        } else {
            terminationAlert.style.display = 'none';
            terminationAlert.className = 'alert alert-warning'; // Reset to warning style
            submitButton.disabled = false;
        }
    }

    // Helper function to convert penalty codes to display text
    function convertPenaltyCodeToText(penaltyCode) {
        // Get penalty mappings from server-side data
        const penaltyMap = @json($penaltyCodeMap ?? []);
        return penaltyMap[penaltyCode] || 'Verbal Warning';
    }

    // Helper function to get ordinal suffix (1st, 2nd, 3rd, 4th)
    function getOrdinalSuffix(num) {
        const j = num % 10;
        const k = num % 100;
        if (j == 1 && k != 11) {
            return 'st';
        }
        if (j == 2 && k != 12) {
            return 'nd';
        }
        if (j == 3 && k != 13) {
            return 'rd';
        }
        return 'th';
    }

    // =============================================
    // Toggle Consequence Field Based on Action Taken
    // =============================================
    function toggleConsequenceField() {
        const actionTaken = document.getElementById('action_taken').value;
        const consequenceGroup = document.getElementById('consequence-group');
        const durationGroup = document.getElementById('duration-group');
        const consequenceSelect = document.getElementById('consequence-select');
        const consequenceInput = document.getElementById('consequence-input');
        const durationValue = document.getElementById('duration-value');
        const consequenceRequired = document.getElementById('consequence-required');
        const consequenceHelp = document.getElementById('consequence-help');

        if (actionTaken === '0') { // No action taken
            // Hide consequence and duration fields and make them optional
            consequenceGroup.style.display = 'none';
            durationGroup.style.display = 'none';
            consequenceSelect.required = false;
            consequenceInput.required = false;
            durationValue.required = false;
            consequenceRequired.style.display = 'none';

            // Clear consequence and duration values
            consequenceSelect.value = '';
            consequenceInput.value = '';
            consequenceInput.style.display = 'none';
            durationValue.value = '';
        } else { // Action taken = Yes
            // Show consequence field and make it required
            consequenceGroup.style.display = 'block';
            consequenceSelect.required = true;
            consequenceRequired.style.display = 'inline';
        }
    }

    // =============================================
    // Consequence Field Helper Function
    // =============================================
    function ensureConsequenceValue() {
        const actionTaken = document.getElementById('action_taken').value;
        const consequenceSelect = document.getElementById('consequence-select');
        const consequenceInput = document.getElementById('consequence-input');
        const durationValue = document.getElementById('duration-value');
        const durationUnit = document.getElementById('duration-unit');

        // If no action taken, set consequence to empty
        if (actionTaken === '0') {
            consequenceInput.value = '';
            consequenceInput.name = 'consequence';
            return;
        }

        // Consequences that require duration
        const consequencesWithDuration = [
            'No cellphone', 'No going out', 'Community Service',
            'Kitchen team', 'No internet access', 'Suspension', 'Detention'
        ];

        // If action taken, ensure consequence has a value
        if (consequenceSelect.value && consequenceSelect.value !== 'other') {
            // Check if this consequence requires duration
            if (consequencesWithDuration.includes(consequenceSelect.value)) {
                if (durationValue.value && durationUnit.value) {
                    // Combine consequence with duration for display
                    const finalConsequence = `${consequenceSelect.value} for ${durationValue.value} ${durationUnit.value}`;
                    consequenceInput.value = finalConsequence;
                } else {
                    // Just set the base consequence if no duration
                    consequenceInput.value = consequenceSelect.value;
                }
            } else {
                // Use the selected dropdown value for consequences that don't require duration
                consequenceInput.value = consequenceSelect.value;
            }
            consequenceInput.name = 'consequence';
        } else if (consequenceSelect.value === 'other') {
            // Use the custom input value
            consequenceInput.name = 'consequence';
        } else {
            // No consequence selected, set a default
            consequenceInput.value = '';
            consequenceInput.name = 'consequence';
        }
    }

    // =============================================
    // Form Submission Handler
    // =============================================
    document.getElementById('violatorForm').addEventListener('submit', function(e) {
        console.log('Form submission started');
        console.log('Form action:', this.action);
        console.log('Form method:', this.method);

        // Check if student is already terminated
        const terminationAlert = document.getElementById('termination-alert');
        if (terminationAlert.style.display === 'block' && terminationAlert.className.includes('alert-danger')) {
            e.preventDefault();
            alert('Cannot submit: This student has already been terminated. No additional violations can be added.');
            return false;
        }

        const actionTaken = document.getElementById('action_taken').value;
        const consequenceSelect = document.getElementById('consequence-select');
        const consequenceInput = document.getElementById('consequence-input');
        const durationValue = document.getElementById('duration-value');
        const durationUnit = document.getElementById('duration-unit');
        const isGroup = {{ isset($groupMode) && $groupMode ? 'true' : 'false' }};
        const studentSelect = document.getElementById('student-select');

        // Validate consequence only if action taken is "Yes"
        if (actionTaken === '1') {
            if (!consequenceSelect.value && !consequenceInput.value.trim()) {
                e.preventDefault();
                alert('Please select or enter a consequence when action is taken.');
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
        }

        // Ensure consequence field has appropriate value
        ensureConsequenceValue();

        // In group mode, ensure student_ids[] are posted even with Select2
        if (isGroup && studentSelect) {
            // Remove any previously appended hidden inputs
            const existingHidden = this.querySelectorAll('input[name="student_ids[]"][data-generated="1"]');
            existingHidden.forEach(el => el.parentNode.removeChild(el));

            // Try to read via Select2 if available; fallback to native selectedOptions
            let values = [];
            try {
                if (window.$ && $('#student-select').length && typeof $('#student-select').val === 'function') {
                    const v = $('#student-select').val();
                    if (Array.isArray(v)) {
                        values = v;
                    }
                }
            } catch (_) {
                // ignore
            }
            if (values.length === 0) {
                values = Array.from(studentSelect.selectedOptions || []).map(o => o.value);
            }
            values.forEach(val => {
                if (!val) return;
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'student_ids[]';
                hidden.value = val;
                hidden.setAttribute('data-generated', '1');
                this.appendChild(hidden);
            });
            // Also provide a JSON payload as a fallback for server normalization
            const jsonField = document.getElementById('student_ids_json');
            if (jsonField) {
                try { jsonField.value = JSON.stringify(values); } catch(_) { jsonField.value = ''; }
            }
            // If still no values, prevent submit with a clear message
            if (values.length === 0) {
                e.preventDefault();
                alert('Please select at least one student.');
                return false;
            }
        }

        // Show loading state
        const submitBtn = document.querySelector('.submit-btn');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding Violator...';
            submitBtn.disabled = true;
        }

        console.log('Form submitting...');
        
        // Debug: Log form data
        const formData = new FormData(this);
        console.log('Form data being sent:');
        for (let [key, value] of formData.entries()) {
            console.log(key + ': ' + value);
        }

        // Allow form to submit normally
        return true;
    });

    // =============================================
    // Initialize form state on page load
    // =============================================
    document.addEventListener('DOMContentLoaded', function() {
        // Set initial state of consequence field
        toggleConsequenceField();
    });
</script>
@endpush