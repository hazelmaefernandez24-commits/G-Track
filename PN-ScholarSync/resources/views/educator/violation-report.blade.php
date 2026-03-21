@extends('layouts.educator')

@section('title', 'Violation Report')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/educator/violation-report.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .date-column {
            min-width: 80px;
            text-align: center;
            vertical-align: middle;
        }
        .date-display {
            line-height: 1.3;
            font-size: 0.9em;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid px-1">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap: 1rem;">
        <h2 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Violation Report</h2>
        <div class="d-flex align-items-center" style="gap: 1rem;">
            <input type="text" id="violationSearch" class="form-control" placeholder="Search by name..." style="max-width: 250px;">
            <div class="batch-filter" style="min-width:200px;">
                <label for="reportBatchSelect" class="form-label me-2 mb-0 fw-semibold visually-hidden">Class</label>
                <select class="form-select" id="reportBatchSelect">
                    <option value="all" selected>All Classes</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Additional Filters Row -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap: 1rem;">
        <div class="d-flex align-items-center" style="gap: 1rem;">
            <div style="min-width:150px;">
                <label for="reportYearSelect" class="form-label me-2 mb-0 fw-semibold visually-hidden">Year</label>
                <input type="number" id="reportYearSelect" class="form-control" value="{{ date('Y') }}" min="2020" max="{{ date('Y') + 5 }}" placeholder="Year">
            </div>
            <div style="min-width:150px;">
                <label for="reportMonthSelect" class="form-label me-2 mb-0 fw-semibold visually-hidden">Month</label>
                <select id="reportMonthSelect" class="form-select">
                    <option value="all" selected>All Months</option>
                    <option value="0">January</option>
                    <option value="1">February</option>
                    <option value="2">March</option>
                    <option value="3">April</option>
                    <option value="4">May</option>
                    <option value="5">June</option>
                    <option value="6">July</option>
                    <option value="7">August</option>
                    <option value="8">September</option>
                    <option value="9">October</option>
                    <option value="10">November</option>
                    <option value="11">December</option>
                </select>
            </div>
        </div>
        <div class="d-flex align-items-center" style="gap: 0.5rem;">
            <button type="button" class="btn btn-primary" id="generateReportBtn">
                <i class="fas fa-sync-alt me-1"></i>Generate Report
            </button>
            <button type="button" class="btn btn-success" id="printReportBtn">
                <i class="fas fa-print me-1"></i>Print Report
            </button>
            <a href="{{ route('educator.behavior') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="reportLoading" class="text-center py-5" style="display: none;">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3 h5">Generating report...</p>
    </div>

    <!-- Report Table -->
    <div class="table-responsive" id="reportTableCard" style="display: none;">
        <table class="table table-hover" id="violationReportTable">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Student</th>
                    <th>Violation</th>
                    <th>Severity</th>
                    <th>Penalty</th>
                    <th>Action Taken</th>
                    <th>Remarks</th>
                    <th>Prepared By</th>
                </tr>
            </thead>
            <tbody id="reportTableBody">
                <!-- Report data will be loaded here -->
            </tbody>
        </table>
    </div>

    <!-- No Data Message -->
    <div class="text-center py-5" id="noReportDataCard" style="display: none;">
        <i class="fas fa-clipboard-check fa-4x text-muted mb-3"></i>
        <h4>No Violations Found</h4>
        <p class="text-muted">No violations match the selected criteria. Try adjusting your filters.</p>
        <button type="button" class="btn btn-primary" onclick="document.getElementById('generateReportBtn').click()">
            <i class="fas fa-sync-alt me-1"></i>Refresh Report
        </button>
    </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize report functionality
            initializeReportPage();
        });

        function initializeReportPage() {
            // Load available batches
            loadAvailableBatches();

            // Add event listeners
            const generateReportBtn = document.getElementById('generateReportBtn');
            const printReportBtn = document.getElementById('printReportBtn');
            const violationSearch = document.getElementById('violationSearch');
            const reportBatchSelect = document.getElementById('reportBatchSelect');

            if (generateReportBtn) {
                generateReportBtn.addEventListener('click', generateViolationReport);
            }

            if (printReportBtn) {
                printReportBtn.addEventListener('click', printViolationReport);
            }

            // Add search functionality
            if (violationSearch) {
                violationSearch.addEventListener('input', filterViolationTable);
            }

            // Add batch filter functionality
            if (reportBatchSelect) {
                reportBatchSelect.addEventListener('change', filterViolationTable);
            }

            // Auto-generate report on page load
            setTimeout(() => {
                generateViolationReport();
            }, 1000);
        }

        function loadAvailableBatches() {
            const reportBatchSelect = document.getElementById('reportBatchSelect');
            if (!reportBatchSelect) return;

            fetch('/educator/available-batches')
                .then(response => response.json())
                .then(data => {
                    reportBatchSelect.innerHTML = '';
                    if (data.success && data.batches) {
                        data.batches.forEach(batch => {
                            const option = document.createElement('option');
                            option.value = batch.value;
                            option.textContent = batch.label;
                            if (batch.value === 'all') {
                                option.selected = true;
                            }
                            reportBatchSelect.appendChild(option);
                        });
                    } else {
                        reportBatchSelect.innerHTML = '<option value="all" selected>All Classes</option>';
                    }
                })
                .catch(() => {
                    reportBatchSelect.innerHTML = '<option value="all" selected>All Classes</option>';
                });
        }

        function filterViolationTable() {
            const searchInput = document.getElementById('violationSearch');
            const batchSelect = document.getElementById('reportBatchSelect');
            const table = document.getElementById('violationReportTable');

            if (!searchInput || !batchSelect || !table) return;

            const searchText = searchInput.value.toLowerCase();
            const selectedBatch = batchSelect.value;
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const studentCell = row.children[1]; // Student column
                const studentName = studentCell ? studentCell.textContent.toLowerCase() : '';
                const studentId = studentCell ? studentCell.querySelector('small')?.textContent || '' : '';

                // Check search match
                const matchesSearch = searchText === '' ||
                    studentName.includes(searchText) ||
                    studentId.toLowerCase().includes(searchText);

                // Check batch match (if batch filtering is needed based on student ID)
                const matchesBatch = selectedBatch === 'all' ||
                    studentId.startsWith(selectedBatch);

                // Show/hide row based on filters
                row.style.display = (matchesSearch && matchesBatch) ? '' : 'none';
            });
        }

        function generateViolationReport() {
            const year = document.getElementById('reportYearSelect').value;
            const month = document.getElementById('reportMonthSelect').value;
            const batch = document.getElementById('reportBatchSelect').value;

            // Show loading
            document.getElementById('reportLoading').style.display = 'block';
            document.getElementById('reportTableCard').style.display = 'none';
            document.getElementById('noReportDataCard').style.display = 'none';

            // Update button state
            const generateBtn = document.getElementById('generateReportBtn');
            const originalText = generateBtn.innerHTML;
            generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Generating...';
            generateBtn.disabled = true;

            // Build API URL
            const params = new URLSearchParams({
                year: year,
                month: month,
                batch: batch
            });

            fetch(`/educator/violation-report-data?${params}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('reportLoading').style.display = 'none';

                    if (data.success && data.data && data.data.length > 0) {
                        populateReportTable(data.data);
                        document.getElementById('reportTableCard').style.display = 'block';
                        document.getElementById('noReportDataCard').style.display = 'none';
                    } else {
                        document.getElementById('reportTableCard').style.display = 'none';
                        document.getElementById('noReportDataCard').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error generating report:', error);
                    document.getElementById('reportLoading').style.display = 'none';
                    document.getElementById('reportTableCard').style.display = 'none';
                    document.getElementById('noReportDataCard').style.display = 'block';
                })
                .finally(() => {
                    // Reset button state
                    generateBtn.innerHTML = originalText;
                    generateBtn.disabled = false;
                });
        }

        function formatDateForDisplay(dateString) {
            if (!dateString || dateString === 'N/A') {
                return 'N/A';
            }

            // Parse the date string (e.g., "Jul 06, 2025")
            const parts = dateString.split(', ');
            if (parts.length === 2) {
                const monthDay = parts[0]; // "Jul 06"
                const year = parts[1]; // "2025"
                return `<div class="date-display">${monthDay},<br>${year}</div>`;
            }

            return dateString; // fallback to original if parsing fails
        }

        function populateReportTable(data) {
            const tbody = document.getElementById('reportTableBody');
            tbody.innerHTML = '';

            data.forEach(violation => {
                // Create action taken checkboxes
                // Backend sends "Yes" or "No" as strings
                console.log('Violation ID:', violation.id, 'Action taken value:', violation.action_taken); // Debug log
                const actionTakenValue = violation.action_taken === 'Yes';
                const actionTakenHtml = `
                    <div class="action-taken-checkboxes" data-violation-id="${violation.id}">
                        <div class="checkbox-group">
                            <div class="checkbox-item">
                                <label>YES</label>
                                <div class="checkbox-box ${actionTakenValue ? 'checked' : ''}" data-value="yes" onclick="toggleActionTaken(${violation.id}, 'yes')">
                                    ${actionTakenValue ? '✓' : ''}
                                </div>
                            </div>
                            <div class="checkbox-item">
                                <label>NO</label>
                                <div class="checkbox-box ${!actionTakenValue ? 'checked' : ''}" data-value="no" onclick="toggleActionTaken(${violation.id}, 'no')">
                                    ${!actionTakenValue ? '✓' : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                // Format date to show month/day on first line, year on second line
                const dateFormatted = formatDateForDisplay(violation.date);

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="date-column">${dateFormatted}</td>
                    <td><strong>${violation.student}</strong><br><small class="text-muted">${violation.student_id}</small></td>
                    <td>${violation.violation}</td>
                    <td><span class="badge bg-${getSeverityColor(violation.severity)}">${violation.severity}</span></td>
                    <td>${violation.penalty}</td>
                    <td>${actionTakenHtml}</td>
                    <td>
                        <span class="badge ${violation.action_taken === 'Yes' ? 'bg-danger' : 'bg-success'}" id="remarks-${violation.id}">
                            ${violation.action_taken === 'Yes' ? 'Not Excused' : 'Excused'}
                        </span>
                    </td>
                    <td>${violation.prepared_by}</td>
                `;
                tbody.appendChild(row);
            });
        }



        function printViolationReport() {
            const table = document.getElementById('violationReportTable');
            if (!table || document.getElementById('reportTableCard').style.display === 'none') {
                alert('Please generate a report first before printing.');
                return;
            }

            // Get report filters for the header
            const year = document.getElementById('reportYearSelect').value;
            const month = document.getElementById('reportMonthSelect').value;
            const monthName = document.getElementById('reportMonthSelect').selectedOptions[0].text;
            const batch = document.getElementById('reportBatchSelect').value;
            const batchName = batch === 'all' ? 'All Batches' : document.getElementById('reportBatchSelect').selectedOptions[0].text;

            // Create a new window for printing
            const printWindow = window.open('', '_blank');

            // Build the print content
            let printContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Violation Report - ${monthName} ${year}</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .header h1 { margin: 0; color: #333; }
                        .header p { margin: 5px 0; color: #666; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
                        th { background-color: #f8f9fa; font-weight: bold; }
                        tr:nth-child(even) { background-color: #f9f9f9; }
                        .severity-low { color: #28a745; font-weight: bold; }
                        .severity-medium { color: #ffc107; font-weight: bold; }
                        .severity-high { color: #dc3545; font-weight: bold; }
                        @media print {
                            body { margin: 0; }
                            .no-print { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>Violation Report</h1>
                        <p><strong>Period:</strong> ${monthName} ${year}</p>
                        <p><strong>Batch:</strong> ${batchName}</p>
                        <p><strong>Generated on:</strong> ${new Date().toLocaleDateString()}</p>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Student</th>
                                <th>Violation</th>
                                <th>Severity</th>
                                <th>Penalty</th>
                                <th>Action Taken</th>
                                <th>Remarks</th>
                                <th>Prepared By</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            // Add table rows
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                // Only print visible rows
                if (row.style.display !== 'none') {
                    const cells = row.querySelectorAll('td');

                    // Handle action taken checkbox - check which one is checked
                    const actionTakenCell = cells[5];
                    const yesCheckbox = actionTakenCell.querySelector('.checkbox-item:first-child .checkbox-box');
                    const actionTakenValue = yesCheckbox && yesCheckbox.classList.contains('checked') ? 'YES' : 'NO';

                    printContent += '<tr>';
                    printContent += `<td>${cells[0].textContent.trim()}</td>`; // Date
                    printContent += `<td>${cells[1].querySelector('strong')?.textContent.trim() || cells[1].textContent.trim()}<br><small>${cells[1].querySelector('small')?.textContent.trim() || ''}</small></td>`; // Student name and ID
                    printContent += `<td>${cells[2].textContent.trim()}</td>`; // Violation

                    // Severity with color
                    const severityText = cells[3].textContent.trim();
                    const severityClass = severityText.toLowerCase() === 'low' ? 'severity-low' :
                                         severityText.toLowerCase() === 'medium' ? 'severity-medium' : 'severity-high';
                    printContent += `<td class="${severityClass}">${severityText}</td>`; // Severity

                    printContent += `<td>${cells[4].textContent.trim()}</td>`; // Penalty
                    printContent += `<td>${actionTakenValue}</td>`; // Action Taken
                    printContent += `<td>${cells[6].textContent.trim()}</td>`; // Remarks
                    printContent += `<td>${cells[7].textContent.trim()}</td>`; // Prepared By
                    printContent += '</tr>';
                }
            });

            printContent += `
                        </tbody>
                    </table>
                </body>
                </html>
            `;

            // Write content to print window and print
            printWindow.document.write(printContent);
            printWindow.document.close();

            // Wait for content to load then print
            printWindow.onload = function() {
                printWindow.print();
                printWindow.close();
            };
        }

        function getSeverityColor(severity) {
            const colors = {
                'Low': 'success',
                'Medium': 'warning',
                'High': 'danger',
                'Very High': 'dark'
            };
            return colors[severity] || 'secondary';
        }

        // Toggle action taken status
        function toggleActionTaken(violationId, value) {
            console.log('toggleActionTaken called with:', violationId, value); // Debug log

            // Show confirmation dialog for both directions
            showActionTakenConfirmationReport(violationId, value);
        }

        // Show confirmation dialog for changing action taken status in report
        function showActionTakenConfirmationReport(violationId, value) {
            // Determine dialog content based on the new action taken value
            const isChangingToNo = value === 'no';
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
            confirmDialog.id = 'actionTakenReportConfirmModal';
            confirmDialog.setAttribute('tabindex', '-1');
            confirmDialog.setAttribute('aria-labelledby', 'actionTakenReportConfirmModalLabel');
            confirmDialog.setAttribute('aria-hidden', 'true');

            confirmDialog.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="actionTakenReportConfirmModalLabel">${dialogTitle}</h5>
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
                            <button type="button" class="btn ${buttonClass}" id="confirmActionTakenReportBtn">
                                <i class="fas fa-check me-1"></i>
                                ${buttonText}
                            </button>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(confirmDialog);

            // Initialize the Bootstrap modal
            const modal = new bootstrap.Modal(document.getElementById('actionTakenReportConfirmModal'));
            modal.show();

            // Handle confirmation
            document.getElementById('confirmActionTakenReportBtn').addEventListener('click', function() {
                modal.hide();
                updateActionTakenVisual(violationId, value);
            });

            // Clean up modal after it's hidden
            confirmDialog.addEventListener('hidden.bs.modal', function() {
                document.body.removeChild(confirmDialog);
            });
        }

        // Update action taken visual state (extracted from original function)
        function updateActionTakenVisual(violationId, value) {
            // Find the checkbox container for this violation
            const container = document.querySelector(`[data-violation-id="${violationId}"]`);
            if (!container) {
                console.error('Container not found for violation ID:', violationId);
                return;
            }

            // Get both checkboxes
            const yesBox = container.querySelector('[data-value="yes"]');
            const noBox = container.querySelector('[data-value="no"]');

            // Update the visual state
            if (value === 'yes') {
                yesBox.classList.add('checked');
                yesBox.innerHTML = '✓';
                noBox.classList.remove('checked');
                noBox.innerHTML = '';
            } else {
                noBox.classList.add('checked');
                noBox.innerHTML = '✓';
                yesBox.classList.remove('checked');
                yesBox.innerHTML = '';
            }

            // Update the remarks badge automatically
            const remarksBadge = document.getElementById(`remarks-${violationId}`);
            if (remarksBadge) {
                if (value === 'yes') {
                    remarksBadge.textContent = 'Not Excused';
                    remarksBadge.className = 'badge bg-danger';
                } else {
                    remarksBadge.textContent = 'Excused';
                    remarksBadge.className = 'badge bg-success';
                }
            }

            // Send update to backend
            updateActionTaken(violationId, value);
        }

        // Update action taken in backend
        function updateActionTaken(violationId, value) {
            fetch('/educator/update-action-taken', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    violation_id: violationId,
                    action_taken: value === 'yes' ? 1 : 0
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Action taken updated successfully');
                    // Optionally show a success message to the user
                    // You could add a toast notification here
                } else {
                    console.error('Failed to update action taken:', data.message);
                    // Revert the visual change if the update failed
                    location.reload(); // Refresh to show correct state
                }
            })
            .catch(error => {
                console.error('Error updating action taken:', error);
                // Optionally show an error message to the user
            });
        }



    </script>
@endpush
