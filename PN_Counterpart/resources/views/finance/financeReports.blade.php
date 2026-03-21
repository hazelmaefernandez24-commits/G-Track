@extends('layouts.finance')

@section('title', 'Reports')
@section('page-title', 'Reports Management')

@push('styles')
<style>
    /* Ensure year range filters are completely hidden by default */
    #yearRangeGroup, #yearRangeGroupTo {
        display: none !important;
    }

    /* Only show when per_year is selected */
    .show-year-range #yearRangeGroup,
    .show-year-range #yearRangeGroupTo {
        display: block !important;
    }

    /* Ensure student month range filters are hidden by default */
    #studentMonthRangeFrom, #studentMonthRangeTo {
        display: none !important;
    }

    /* Only show when student month range is active */
    .show-student-month-range #studentMonthRangeFrom,
    .show-student-month-range #studentMonthRangeTo {
        display: block !important;
    }

    /* Style for month range toggle buttons */
    #monthRangeToggleStudent, #monthRangeToggleBackStudent {
        background: rgba(255, 255, 255, 0.9) !important;
        border: 1px solid #ddd !important;
        border-radius: 4px !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
        font-size: 14px !important;
        width: 28px !important;
        height: 28px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    #monthRangeToggleStudent:hover, #monthRangeToggleBackStudent:hover {
        background: #f8f9fa !important;
        transform: scale(1.05);
        box-shadow: 0 2px 5px rgba(0,0,0,0.2) !important;
    }

    #monthRangeToggleStudent:focus, #monthRangeToggleBackStudent:focus {
        outline: none !important;
        box-shadow: 0 0 0 2px rgba(255, 153, 51, 0.3) !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header text-white" style="background-color: #FF9933;">
                    <h5 class="mb-0">Finance Reports</h5>
                    
                </div>
                <div class="card-body">
                    <form id="reportForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="batchYear" class="form-label fw-semibold">Class Batch</label>
                                <select id="batchYear" name="batch_year" class="form-select">
                                    <option value="">All Batches</option>
                                    @foreach ($batches as $batch)
                                        <option value="{{ $batch->batch_year }}">{{ $batch->batch_year }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="reportType" class="form-label fw-semibold">Report Type</label>
                                <select id="reportType" name="report_type" class="form-select" onchange="toggleYearInputs()">
                                    <option value="total_paid_per_student">Per Student</option>
                                    <option value="total_paid_per_month">Per Month</option>
                                    <option value="per_year">Per Year</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="paymentMode" class="form-label fw-semibold">Mode of Payment</label>
                                <select id="paymentMode" name="payment_mode" class="form-select">
                                    <option value="">All Payment Methods</option>
                                    @foreach ($paymentModes as $mode)
                                        <option value="{{ $mode }}">{{ $mode }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3" id="singleMonthCol">
                                <label for="month" class="form-label fw-semibold">Month</label>
                                <div class="position-relative">
                                    <select id="month" name="month" class="form-select">
                                        <option value="">All Months</option>
                                        @foreach(range(1,12) as $m)
                                            <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-sm position-absolute top-50 end-0 translate-middle-y me-2"
                                            id="monthRangeToggleStudent"
                                            style="background: none; border: none; color:rgb(25, 146, 233); z-index: 10; padding: 2px 4px;"
                                            title="Switch to Month Range">
                                        <i class="fas fa-calendar-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3" id="singleYearCol">
                                <label for="year" class="form-label fw-semibold">Year</label>
                                <input type="number" id="year" name="year" class="form-control" value="{{ date('Y') }}">
                            </div>

                            <!-- Per-Year month selector (shown when Per Year report is active) -->
                            <div class="col-md-3" id="perYearMonthCol" style="display: none;">
                                <label for="perYearMonth" class="form-label fw-semibold">Month (optional)</label>
                                <select id="perYearMonth" name="per_year_month" class="form-select">
                                    <option value="">All Months</option>
                                    @foreach(range(1,12) as $m)
                                        <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Month Range Mode (hidden by default) -->
                            <div class="col-md-3" id="monthRangeCol" style="display: none;">
                                <label class="form-label fw-semibold">From</label>
                                <div class="row g-1">
                                    <div class="col-6">
                                        <select id="monthStart" name="month_start" class="form-select">
                                            @foreach(range(1,12) as $m)
                                                <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <input type="number" id="yearStart" name="year_start" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3" id="monthRangeColTo" style="display: none;">
                                <label class="form-label fw-semibold">To</label>
                                <div class="row g-1">
                                    <div class="col-6">
                                        <select id="monthEnd" name="month_end" class="form-select">
                                            @foreach(range(1,12) as $m)
                                                <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6 position-relative">
                                        <input type="number" id="yearEnd" name="year_end" class="form-control" value="{{ date('Y') }}">
                                        <i class="fas fa-calendar-alt position-absolute top-50 end-0 translate-middle-y me-3"
                                           id="monthRangeToggleBack"
                                           style="color: #28a745; z-index: 10; cursor:pointer;"
                                           title="Switch to Single Month"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Year Range Inputs (hidden by default) -->
                            <div class="col-md-3" id="yearRangeGroup" style="display: none !important;">
                                <label for="yearFrom" class="form-label fw-semibold">From Year</label>
                                <input type="number" id="yearFrom" name="year_from" class="form-control" min="2000" max="2100">
                            </div>
                            <div class="col-md-3" id="yearRangeGroupTo" style="display: none !important;">
                                <label for="yearTo" class="form-label fw-semibold">To Year</label>
                                <input type="number" id="yearTo" name="year_to" class="form-control"min="2000" max="2100">
                            </div>

                            <!-- Month Range for Per Student (hidden by default) -->
                            <div class="col-md-3" id="studentMonthRangeFrom" style="display: none;">
                                <label for="studentMonthFrom" class="form-label fw-semibold">From</label>
                                <div class="row g-1">
                                    <div class="col-7">
                                        <select id="studentMonthFrom" name="student_month_from" class="form-select">
                                            @foreach(range(1,12) as $m)
                                                <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-5">
                                        <input type="number" id="studentYearFrom" name="student_year_from" class="form-control" placeholder="Year" min="2000" max="2100" value="{{ date('Y') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3" id="studentMonthRangeTo" style="display: none;">
                                <label for="studentMonthTo" class="form-label fw-semibold">To</label>
                                <div class="row g-1">
                                    <div class="col-7">
                                        <select id="studentMonthTo" name="student_month_to" class="form-select">
                                            @foreach(range(1,12) as $m)
                                                <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-5 position-relative">
                                        <input type="number" id="studentYearTo" name="student_year_to" class="form-control" placeholder="Year" min="2000" max="2100" value="{{ date('Y') }}">
                                        <button type="button" class="btn btn-sm position-absolute top-50 end-0 translate-middle-y me-1"
                                                id="monthRangeToggleBackStudent"
                                                style="background: none; border: none; color:rgb(243, 144, 15); z-index: 10; padding: 1px 3px; font-size: 12px;"
                                                title="Switch back to Single Month">
                                            <i class="fas fa-times-circle"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="button" class="btn btn-primary" id="generateReportBtn">Generate Report</button>
                            <button type="button" class="btn btn-success" id="downloadReportBtn">Download Report</button>
                        </div>
                    </form>

                    <hr>
                    <div id="reportContainer" class="mt-4">
                        <h5 class="text-center text-muted">No report generated yet.</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const generateReportBtn = document.getElementById('generateReportBtn');
        const downloadReportBtn = document.getElementById('downloadReportBtn');
        const reportContainer = document.getElementById('reportContainer');
        const reportTypeSelect = document.getElementById('reportType');
        const monthFilter = document.getElementById('monthFilter');
        const monthRangeToggle = document.getElementById('monthRangeToggle');
        const monthRangeToggleBack = document.getElementById('monthRangeToggleBack');
        const singleMonthCol = document.getElementById('singleMonthCol');
        const singleYearCol = document.getElementById('singleYearCol');
        const monthRangeCol = document.getElementById('monthRangeCol');
        const monthRangeColTo = document.getElementById('monthRangeColTo');
        const yearRangeGroup = document.getElementById('yearRangeGroup');

        // Show/Hide filters based on report type
        reportTypeSelect.addEventListener('change', () => {
            toggleYearInputs();
            // Reset month range for students when report type changes
            resetStudentMonthRange();
        });

        // Show or hide the per-year month selector when report type changes
        const perYearMonthCol = document.getElementById('perYearMonthCol');
        reportTypeSelect.addEventListener('change', () => {
            if (reportTypeSelect.value === 'per_year') {
                if (perYearMonthCol) perYearMonthCol.style.setProperty('display', 'block', 'important');
            } else {
                if (perYearMonthCol) perYearMonthCol.style.setProperty('display', 'none', 'important');
            }
        });

        // Show month range fields, hide single month/year (only if not in per_year mode)
        if (monthRangeToggle) {
            monthRangeToggle.addEventListener('click', () => {
                const reportType = document.getElementById('reportType').value;
                if (reportType !== 'per_year') {
                    singleMonthCol.style.display = 'none';
                    singleYearCol.style.display = 'none';
                    monthRangeCol.style.display = '';
                    monthRangeColTo.style.display = '';
                }
            });
        }

        // Back to single month/year (only if not in per_year mode)
        if (monthRangeToggleBack) {
            monthRangeToggleBack.addEventListener('click', () => {
                const reportType = document.getElementById('reportType').value;
                if (reportType !== 'per_year') {
                    singleMonthCol.style.display = '';
                    singleYearCol.style.display = '';
                    monthRangeCol.style.display = 'none';
                    monthRangeColTo.style.display = 'none';
                }
            });
        }

        // Student/Month Range Toggle - Show month range for Per Student and Per Month
        const monthRangeToggleStudent = document.getElementById('monthRangeToggleStudent');
        const monthRangeToggleBackStudent = document.getElementById('monthRangeToggleBackStudent');
        const studentMonthRangeFrom = document.getElementById('studentMonthRangeFrom');
        const studentMonthRangeTo = document.getElementById('studentMonthRangeTo');

        if (monthRangeToggleStudent) {
            monthRangeToggleStudent.addEventListener('click', () => {
                const reportType = document.getElementById('reportType').value;
                if (reportType === 'total_paid_per_student' || reportType === 'total_paid_per_month') {
                    // Show student month range filters
                    document.body.classList.add('show-student-month-range');
                    studentMonthRangeFrom.style.setProperty('display', 'block', 'important');
                    studentMonthRangeTo.style.setProperty('display', 'block', 'important');
                    // Hide single month and year filters
                    singleMonthCol.style.setProperty('display', 'none', 'important');
                    singleYearCol.style.setProperty('display', 'none', 'important');
                }
            });
        }

        // Student/Month Range Toggle Back - Back to single month
        if (monthRangeToggleBackStudent) {
            monthRangeToggleBackStudent.addEventListener('click', () => {
                const reportType = document.getElementById('reportType').value;
                if (reportType === 'total_paid_per_student' || reportType === 'total_paid_per_month') {
                    // Hide student month range filters
                    document.body.classList.remove('show-student-month-range');
                    studentMonthRangeFrom.style.setProperty('display', 'none', 'important');
                    studentMonthRangeTo.style.setProperty('display', 'none', 'important');
                    // Show single month and year filters
                    singleMonthCol.style.setProperty('display', 'block', 'important');
                    singleYearCol.style.setProperty('display', 'block', 'important');
                }
            });
        }

        // Generate Report
        generateReportBtn.addEventListener('click', () => {
            let params = {};
            const studentMonthRangeFrom = document.getElementById('studentMonthRangeFrom');

            if (monthRangeCol.style.display !== 'none') {
                // Original month range (for other report types)
                params = {
                    batch_year: document.getElementById('batchYear').value,
                    report_type: document.getElementById('reportType').value,
                    payment_mode: document.getElementById('paymentMode').value,
                    month_start: document.getElementById('monthStart').value,
                    year_start: document.getElementById('yearStart').value,
                    month_end: document.getElementById('monthEnd').value,
                    year_end: document.getElementById('yearEnd').value
                };
            } else if (studentMonthRangeFrom && studentMonthRangeFrom.style.display !== 'none') {
                // Month range (for Per Student and Per Month report types)
                params = {
                    batch_year: document.getElementById('batchYear').value,
                    report_type: document.getElementById('reportType').value,
                    payment_mode: document.getElementById('paymentMode').value,
                    student_month_from: document.getElementById('studentMonthFrom').value,
                    student_year_from: document.getElementById('studentYearFrom').value,
                    student_month_to: document.getElementById('studentMonthTo').value,
                    student_year_to: document.getElementById('studentYearTo').value
                };
            } else if (reportTypeSelect.value === 'per_year') {
                // Year range (for Per Year report type)
                params = {
                    batch_year: document.getElementById('batchYear').value,
                    report_type: document.getElementById('reportType').value,
                    payment_mode: document.getElementById('paymentMode').value,
                    year_from: document.getElementById('yearFrom').value,
                    year_to: document.getElementById('yearTo').value,
                    month: document.getElementById('perYearMonth') ? document.getElementById('perYearMonth').value : ''
                };
            } else {
                // Single month/year (default)
                params = {
                    batch_year: document.getElementById('batchYear').value,
                    report_type: document.getElementById('reportType').value,
                    payment_mode: document.getElementById('paymentMode').value,
                    month: document.getElementById('month').value,
                    year: document.getElementById('year').value
                };
            }

            fetch(`/finance/reports/generate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(params)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    reportContainer.innerHTML = data.html;
                } else {
                    reportContainer.innerHTML = `<h5 class="text-center text-danger">${data.message}</h5>`;
                }
            })
            .catch(error => {
                console.error('Error generating report:', error);
                reportContainer.innerHTML = `<h5 class="text-center text-danger">An error occurred while generating the report.</h5>`;
            });
        });

        // Download Report - Simplified approach
        downloadReportBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            console.log('Download button clicked - creating POST form');

            // Get form values
            const batchYear = document.getElementById('batchYear').value;
            const reportType = document.getElementById('reportType').value;
            const paymentMode = document.getElementById('paymentMode').value;
            const year = document.getElementById('year').value;
            const month = document.getElementById('month').value;
            const yearFrom = document.getElementById('yearFrom').value;
            const yearTo = document.getElementById('yearTo').value;

            // Create form HTML
            let formHTML = `
                <form id="downloadForm" method="POST" action="{{ route('finance.downloadReport') }}" style="display: none;">
                    @csrf
                    <input type="hidden" name="batch_year" value="${batchYear}">
                    <input type="hidden" name="report_type" value="${reportType}">
                    <input type="hidden" name="payment_mode" value="${paymentMode}">
            `;

            // Add parameters based on report type
            if (reportType === 'per_year') {
                formHTML += `
                    <input type="hidden" name="year_from" value="${yearFrom}">
                    <input type="hidden" name="year_to" value="${yearTo}">
                    <input type="hidden" name="month" value="${document.getElementById('perYearMonth') ? document.getElementById('perYearMonth').value : ''}">
                `;
            } else {
                // Check if month range is visible
                const studentMonthRangeFrom = document.getElementById('studentMonthRangeFrom');
                if (studentMonthRangeFrom && studentMonthRangeFrom.style.display !== 'none') {
                    formHTML += `
                        <input type="hidden" name="student_month_from" value="${document.getElementById('studentMonthFrom').value}">
                        <input type="hidden" name="student_year_from" value="${document.getElementById('studentYearFrom').value}">
                        <input type="hidden" name="student_month_to" value="${document.getElementById('studentMonthTo').value}">
                        <input type="hidden" name="student_year_to" value="${document.getElementById('studentYearTo').value}">
                    `;
                } else {
                    formHTML += `
                        <input type="hidden" name="year" value="${year}">
                        <input type="hidden" name="month" value="${month}">
                    `;
                }
            }

            formHTML += '</form>';

            // Add form to page and submit
            document.body.insertAdjacentHTML('beforeend', formHTML);
            const form = document.getElementById('downloadForm');
            console.log('Submitting form with method:', form.method);
            form.submit();

            // Clean up
            setTimeout(() => {
                if (form && form.parentNode) {
                    form.parentNode.removeChild(form);
                }
            }, 1000);
        });

        // Initial call to set up UI
        toggleYearInputs();
    });

    function toggleYearInputs() {
        const reportType = document.getElementById('reportType').value;
        const yearRangeGroup = document.getElementById('yearRangeGroup');
        const yearRangeGroupTo = document.getElementById('yearRangeGroupTo');
        const singleYearCol = document.getElementById('singleYearCol');
        const singleMonthCol = document.getElementById('singleMonthCol');
        const monthRangeCol = document.getElementById('monthRangeCol');
        const monthRangeColTo = document.getElementById('monthRangeColTo');
        const studentMonthRangeFrom = document.getElementById('studentMonthRangeFrom');
        const studentMonthRangeTo = document.getElementById('studentMonthRangeTo');

        if (reportType === 'per_year') {
            // Show year range inputs (From and To) ONLY for per_year
            document.body.classList.add('show-year-range');
            document.body.classList.remove('show-student-month-range');
            yearRangeGroup.style.setProperty('display', 'block', 'important');
            yearRangeGroupTo.style.setProperty('display', 'block', 'important');
            // Hide all other filters completely
            singleYearCol.style.setProperty('display', 'none', 'important');
            singleMonthCol.style.setProperty('display', 'none', 'important');
            monthRangeCol.style.setProperty('display', 'none', 'important');
            monthRangeColTo.style.setProperty('display', 'none', 'important');
            studentMonthRangeFrom.style.setProperty('display', 'none', 'important');
            studentMonthRangeTo.style.setProperty('display', 'none', 'important');
        } else {
            // Hide year range inputs completely for non-per_year report types
            document.body.classList.remove('show-year-range');
            yearRangeGroup.style.setProperty('display', 'none', 'important');
            yearRangeGroupTo.style.setProperty('display', 'none', 'important');
            // Show month and year filters for other report types (unless student month range is active)
            if (!document.body.classList.contains('show-student-month-range')) {
                singleYearCol.style.setProperty('display', 'block', 'important');
                singleMonthCol.style.setProperty('display', 'block', 'important');
            }
            // Keep month range hidden by default (user can toggle if needed)
            monthRangeCol.style.setProperty('display', 'none', 'important');
            monthRangeColTo.style.setProperty('display', 'none', 'important');
        }
    }

    function resetStudentMonthRange() {
        const studentMonthRangeFrom = document.getElementById('studentMonthRangeFrom');
        const studentMonthRangeTo = document.getElementById('studentMonthRangeTo');
        const singleYearCol = document.getElementById('singleYearCol');
        const singleMonthCol = document.getElementById('singleMonthCol');

        // Reset student month range
        document.body.classList.remove('show-student-month-range');
        if (studentMonthRangeFrom) studentMonthRangeFrom.style.setProperty('display', 'none', 'important');
        if (studentMonthRangeTo) studentMonthRangeTo.style.setProperty('display', 'none', 'important');
    }
</script>
@endsection
