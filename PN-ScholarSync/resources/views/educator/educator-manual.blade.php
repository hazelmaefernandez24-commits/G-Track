@extends('layouts.educator')

@section('title', 'Student Violation Manual')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/student/student-manual.css') }}">
    <style>
        .search-container {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }

        .search-highlight {
            background-color: #fff3cd;
            color: #856404;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: 500;
        }

        .input-group-text {
            background-color: #e9ecef;
            border-color: #ced4da;
        }

        #violationSearch {
            border-color: #ced4da;
        }

        #violationSearch:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        #searchResults {
            font-style: italic;
        }

        .category-section {
            transition: opacity 0.3s ease;
        }

        .category-section.hidden {
            opacity: 0.5;
        }
    </style>
@endsection

@section('content')
    <div class="container manual-full-width">
        <!-- Remove the first Edit Manual button -->
        
        <div class="main-heading">
            <h1 class="fw-bold mb-0 manual-heading-text">Student Code of Conduct</h1>
        </div>
        <h2>Empowering Responsible Center Life Through Awareness and Discipline.</h2>
        <p>Welcome, students! This code of conduct helps you understand the rules and expectations while living at the center. Staying informed is the first step to success and harmony!</p>

        <!-- Search functionality -->
        <div class="search-container mb-4">
            <div class="row">
                <div class="col-md-4">
                    <label for="categorySelect" class="form-label small fw-bold">Search by Category</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-tags"></i>
                        </span>
                        <select class="form-select" id="categorySelect">
                            <option value="">All Categories</option>
                            @php
                                $categoryOptions = collect($categories)->pluck('category_name')->unique()->sort()->values();
                            @endphp
                            @foreach($categoryOptions as $catName)
                                <option value="{{ $catName }}">{{ $catName }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="violationNameSearch" class="form-label small fw-bold">Search by Violation Name</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-file-alt"></i>
                        </span>
                        <input type="text"
                               class="form-control"
                               id="violationNameSearch"
                               placeholder="Enter violation name..."
                               autocomplete="off">
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="severitySearch" class="form-label small fw-bold">Search by Severity</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-exclamation-triangle"></i>
                        </span>
                        <select class="form-select" id="severitySearch">
                            <option value="">All Severities</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="very high">Very High</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="clearAllSearch">
                        <i class="fas fa-times"></i> Clear All Filters
                    </button>
                    <small class="text-muted ms-2" id="searchResults"></small>
                </div>
            </div>
        </div>
        <div class="violation-table">
            <div class="violation-header d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Violation Categories and Penalties</h3>
                @if(auth()->user()->user_role === 'educator')
                <a href="{{ route('educator.manual.edit') }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit
                </a>
                @endif
            </div>
        </div>

        @foreach($categories as $index => $category)
            <div class="category-section">
                <h4>{{ $index + 1 }}. {{ $category->category_name }}</h4>
                <table class="table table-bordered category-violation-table" data-category="{{ $category->category_name }}">
                    <thead>
                        <tr>
                            <th style="width: 8%; text-align: center;">#</th>
                            <th style="width: 65%; text-align: left; cursor:pointer;" class="sortable" data-sort="violation_name">
                                Violation Name
                                <span class="sort-icon" style="font-size:0.9em;">&#8597;</span>
                            </th>
                            <th style="width: 25%; text-align: center; cursor:pointer;" class="sortable" data-sort="severity">
                                Severity
                                <span class="sort-icon" style="font-size:0.9em;">&#8597;</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($category->violationTypes->unique('violation_name') as $typeIndex => $type)
                        <tr>
                            <td>{{ $index + 1 }}.{{ $typeIndex + 1 }}</td>
                            <td class="violation-name">{{ $type->violation_name }}</td>
                            <td class="violation-severity">
                                @php
                                    $severityName = '';
                                    if ($type->severityRelation) {
                                        $severityName = strtolower($type->severityRelation->severity_name);
                                    } elseif (!empty($type->severity_id)) {
                                        try {
                                            $sev = \App\Models\Severity::find($type->severity_id);
                                            if ($sev && !empty($sev->severity_name)) {
                                                $severityName = strtolower($sev->severity_name);
                                            }
                                        } catch (\Exception $e) {
                                            $severityName = '';
                                        }
                                    }
                                    if ($severityName === '') {
                                        // Fallback: determine severity based on default_penalty
                                        switch ($type->default_penalty) {
                                            case 'VW':
                                                $severityName = 'low';
                                                break;
                                            case 'WW':
                                                $severityName = 'medium';
                                                break;
                                            case 'Pro':
                                                $severityName = 'high';
                                                break;
                                            case 'T':
                                                $severityName = 'very high';
                                                break;
                                            default:
                                                $severityName = 'medium';
                                        }
                                    }
                                @endphp
                                @switch($severityName)
                                    @case('low')
                                        Low
                                        @break
                                    @case('medium')
                                        Medium
                                        @break
                                    @case('high')
                                        High
                                        @break
                                    @case('very high')
                                        Very High
                                        @break
                                    @default
                                        Medium
                                @endswitch
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach


        <div class="penalty-system-explanation mt-5">
            <h3 class="text-sm">Penalty Rules Based on Infraction Count and Severity</h3>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 20%;" class="text-sm">Infraction Count</th>
                            <th style="width: 20%;" class="text-sm">
                                <span class="badge" color: #000;">🟡 Low</span>
                            </th>
                            <th style="width: 20%;" class="text-sm">
                                <span class="badge" color: #fff;">🌸 Medium</span>
                            </th>
                            <th style="width: 20%;" class="text-sm">
                                <span class="badge" color: #fff;">🟠 High</span>
                            </th>
                            <th style="width: 20%;" class="text-sm">
                                <span class="badge" color: #fff;">🔴 Very High</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-sm"><strong>1st Infraction</strong></td>
                            <td class="text-sm">Verbal Warning (VW)</td>
                            <td class="text-sm">Written Warning (WW)</td>
                            <td class="text-sm">Probation (P)</td>
                            <td class="text-sm">Termination (T)</td>
                        </tr>
                        <tr>
                            <td class="text-sm"><strong>2nd Infraction</strong></td>
                            <td class="text-sm">Written Warning (WW)</td>
                            <td class="text-sm">Probation (P)</td>
                            <td class="text-sm">Termination (T)</td>
                            <td class="text-sm"></td>
                        </tr>
                        <tr>
                            <td class="text-sm"><strong>3rd Infraction</strong></td>
                            <td class="text-sm">Probation (P)</td>
                            <td class="text-sm">Termination (T)</td>
                            <td class="text-sm"></td>
                            <td class="text-sm"></td>
                        </tr>
                        <tr>
                            <td class="text-sm"><strong>4th Infraction</strong></td>
                            <td class="text-sm">Termination (T)</td>
                            <td class="text-sm"></td>
                            <td class="text-sm"></td>
                            <td class="text-sm"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="important-reminders mt-4">
                <div class="bg-light p-4 rounded-lg border">
                    <h3 class="text-sm mb-3">🔑 Important Things to Remember About Penalties</h3>
                    
                    <table class="table table-bordered table-sm mb-0">
                        <tbody>
                            <tr>
                                <td style="width: 30%;" class="align-middle bg-white">
                                    <h4 class="mb-0 small">📊 Penalties are based on the violation's severity and infraction number.</h4>
                                </td>
                                <td class="align-middle text-sm bg-white">
                                    Each penalty depends on how serious the violation is and how many times the student has violated overall.
                                </td>
                            </tr>
                            <tr>
                                <td class="align-middle bg-white">
                                    <h4 class="mb-0 small">⏳ The system tracks a total of 4 infractions only.</h4>
                                </td>
                                <td class="align-middle text-sm bg-white">
                                    Once a student commits 4 total violations (regardless of severity), they are automatically terminated — unless terminated earlier by severity.
                                </td>
                            </tr>
                            <tr>
                                <td class="align-middle bg-white">
                                    <h4 class="mb-0 small">⚠️ Serious violations can lead to termination immediately.</h4>
                                </td>
                                <td class="align-middle text-sm bg-white">
                                    For example, a Very High violation causes immediate termination, even if it's the student’s 1st or 2nd infraction.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Severity Maximum Counts Configuration -->
        @if(auth()->user()->user_role === 'educator')
        <div class="severity-config-section mt-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="text-sm">Severity Maximum Counts Configuration</h3>
                <button type="button" class="btn btn-primary btn-sm" id="editSeverityConfigBtn">
                    <i class="fas fa-edit"></i> Edit Configuration
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm" id="severityConfigTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 15%;" class="text-sm">Severity</th>
                            <th style="width: 15%;" class="text-sm">Max Count</th>
                            <th style="width: 20%;" class="text-sm">Base Penalty</th>
                            <th style="width: 20%;" class="text-sm">Escalated Penalty</th>
                            <th style="width: 30%;" class="text-sm">Description</th>
                        </tr>
                    </thead>
                    <tbody id="severityConfigTableBody">
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>

            <div class="mt-3" id="severityConfigActions" style="display: none;">
                <button type="button" class="btn btn-success btn-sm" id="saveSeverityConfigBtn">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <button type="button" class="btn btn-secondary btn-sm" id="cancelSeverityConfigBtn">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </div>
        @endif

        
    </div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let originalSeverityData = [];
    let isEditing = false;

    // Load severity configuration data
    function loadSeverityConfig() {
        $.ajax({
            url: '{{ route("educator.severity-max-counts") }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    originalSeverityData = response.data;
                    renderSeverityConfigTable(response.data, false);
                } else {
                    console.error('Failed to load severity configuration:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading severity configuration:', error);
            }
        });
    }

    // Render the severity configuration table
    function renderSeverityConfigTable(data, editMode = false) {
        const tbody = $('#severityConfigTableBody');
        tbody.empty();

        // Get penalty options from server-side data
        const penaltyOptions = @json($penaltyOptions ?? []);

        data.forEach(function(item) {
            let row = '<tr data-id="' + item.id + '">';

            // Severity name (read-only)
            row += '<td class="text-sm align-middle">' + item.severity_name + '</td>';

            // Max count
            if (editMode) {
                row += '<td class="text-sm align-middle">';
                row += '<input type="number" class="form-control form-control-sm max-count-input" ';
                row += 'value="' + item.max_count + '" min="1" max="10" style="width: 80px;">';
                row += '</td>';
            } else {
                row += '<td class="text-sm align-middle">' + item.max_count + '</td>';
            }

            // Base penalty
            if (editMode) {
                row += '<td class="text-sm align-middle">';
                row += '<select class="form-select form-select-sm base-penalty-select">';
                Object.keys(penaltyOptions).forEach(function(key) {
                    const selected = key === item.base_penalty ? 'selected' : '';
                    row += '<option value="' + key + '" ' + selected + '>' + penaltyOptions[key] + '</option>';
                });
                row += '</select>';
                row += '</td>';
            } else {
                row += '<td class="text-sm align-middle">' + penaltyOptions[item.base_penalty] + '</td>';
            }

            // Escalated penalty
            if (editMode) {
                row += '<td class="text-sm align-middle">';
                row += '<select class="form-select form-select-sm escalated-penalty-select">';
                Object.keys(penaltyOptions).forEach(function(key) {
                    const selected = key === item.escalated_penalty ? 'selected' : '';
                    row += '<option value="' + key + '" ' + selected + '>' + penaltyOptions[key] + '</option>';
                });
                row += '</select>';
                row += '</td>';
            } else {
                row += '<td class="text-sm align-middle">' + penaltyOptions[item.escalated_penalty] + '</td>';
            }

            // Description
            if (editMode) {
                row += '<td class="text-sm align-middle">';
                row += '<textarea class="form-control form-control-sm description-input" rows="2" maxlength="500">';
                row += item.description || '';
                row += '</textarea>';
                row += '</td>';
            } else {
                row += '<td class="text-sm align-middle">' + (item.description || '') + '</td>';
            }

            row += '</tr>';
            tbody.append(row);
        });
    }

    // Edit button click
    $('#editSeverityConfigBtn').click(function() {
        isEditing = true;
        renderSeverityConfigTable(originalSeverityData, true);
        $('#severityConfigActions').show();
        $(this).hide();
    });

    // Cancel button click
    $('#cancelSeverityConfigBtn').click(function() {
        isEditing = false;
        renderSeverityConfigTable(originalSeverityData, false);
        $('#severityConfigActions').hide();
        $('#editSeverityConfigBtn').show();
    });

    // Save button click
    $('#saveSeverityConfigBtn').click(function() {
        const severityConfigs = [];

        $('#severityConfigTableBody tr').each(function() {
            const row = $(this);
            const config = {
                id: row.data('id'),
                max_count: parseInt(row.find('.max-count-input').val()),
                base_penalty: row.find('.base-penalty-select').val(),
                escalated_penalty: row.find('.escalated-penalty-select').val(),
                description: row.find('.description-input').val()
            };
            severityConfigs.push(config);
        });

        $.ajax({
            url: '{{ route("educator.severity-max-counts.update") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                severity_configs: severityConfigs
            },
            success: function(response) {
                if (response.success) {
                    // Reload the data and exit edit mode
                    loadSeverityConfig();
                    isEditing = false;
                    $('#severityConfigActions').hide();
                    $('#editSeverityConfigBtn').show();

                    // Show success message
                    alert('Severity configurations updated successfully!');
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error saving severity configuration:', error);
                alert('Failed to save severity configurations. Please try again.');
            }
        });
    });

    // Load initial data
    loadSeverityConfig();

    // Search functionality
    let originalViolationData = [];

    // Store original violation data on page load
    function storeOriginalData() {
        $('.category-section').each(function() {
            const categoryElement = $(this);
            const categoryName = categoryElement.find('h4').text().trim();
            const violations = [];

            categoryElement.find('tbody tr').each(function() {
                const row = $(this);
                const violationName = row.find('td:nth-child(2)').text().trim();
                const severity = row.find('td:nth-child(3)').text().trim();

                violations.push({
                    element: row.clone(),
                    violationName: violationName.toLowerCase(),
                    severity: severity.toLowerCase(),
                    categoryName: categoryName.toLowerCase()
                });
            });

            originalViolationData.push({
                categoryElement: categoryElement,
                categoryName: categoryName.toLowerCase(),
                violations: violations
            });
        });
    }

    // Search function with multiple criteria
    function performSearch() {
        const violationNameTerm = $('#violationNameSearch').val().toLowerCase().trim();
        const categoryTerm = ($('#categorySelect').val() || '').toLowerCase().trim();
        const severityTerm = $('#severitySearch').val().toLowerCase().trim();

        let totalResults = 0;

        // If all search fields are empty, show everything
        if (violationNameTerm === '' && categoryTerm === '' && severityTerm === '') {
            $('.category-section').show();
            $('.category-section tbody tr').show();
            $('#searchResults').text('');
            return;
        }

        originalViolationData.forEach(function(categoryData) {
            let categoryHasResults = false;
            let categoryResults = 0;

            // Clear the category's table body
            categoryData.categoryElement.find('tbody').empty();

            // Check each violation in this category
            categoryData.violations.forEach(function(violation) {
                let matches = true;

                // Check violation name match (if search term provided)
                if (violationNameTerm !== '' && !violation.violationName.includes(violationNameTerm)) {
                    matches = false;
                }

                // Check category match (if search term provided)
                if (categoryTerm !== '' && !violation.categoryName.includes(categoryTerm)) {
                    matches = false;
                }

                // Check severity match (if search term provided)
                if (severityTerm !== '' && !violation.severity.includes(severityTerm)) {
                    matches = false;
                }

                if (matches) {
                    // Clone the violation element and highlight matches
                    const highlightedElement = highlightSearchTerms(violation.element.clone(), {
                        violationName: violationNameTerm,
                        category: categoryTerm,
                        severity: severityTerm
                    });
                    categoryData.categoryElement.find('tbody').append(highlightedElement);
                    categoryHasResults = true;
                    categoryResults++;
                }
            });

            // Show/hide category based on results and highlight category name if searched
            if (categoryHasResults) {
                categoryData.categoryElement.show();

                // Highlight category name if it matches the search
                const categoryHeader = categoryData.categoryElement.find('h4');
                let categoryHeaderText = categoryHeader.text();
                if (categoryTerm !== '' && categoryData.categoryName.includes(categoryTerm)) {
                    const regex = new RegExp(`(${escapeRegex(categoryTerm)})`, 'gi');
                    categoryHeaderText = categoryHeaderText.replace(regex, '<mark class="search-highlight">$1</mark>');
                    categoryHeader.html(categoryHeaderText);
                } else {
                    // Remove any existing highlights
                    categoryHeader.text(categoryHeader.text());
                }

                totalResults += categoryResults;
            } else {
                categoryData.categoryElement.hide();
            }
        });

        // Update search results count
        if (totalResults === 0) {
            $('#searchResults').text('No results found');
        } else {
            $('#searchResults').text(`${totalResults} violation${totalResults !== 1 ? 's' : ''} found`);
        }
    }

    // Highlight search terms in text with multiple criteria
    function highlightSearchTerms(element, searchTerms) {
        element.find('td').each(function(index) {
            const cell = $(this);
            let text = cell.text();

            // Apply highlighting based on column and search terms
            if (index === 1 && searchTerms.violationName !== '') { // Violation name column
                const regex = new RegExp(`(${escapeRegex(searchTerms.violationName)})`, 'gi');
                text = text.replace(regex, '<mark class="search-highlight">$1</mark>');
            }

            if (index === 2 && searchTerms.severity !== '') { // Severity column
                const regex = new RegExp(`(${escapeRegex(searchTerms.severity)})`, 'gi');
                text = text.replace(regex, '<mark class="search-highlight">$1</mark>');
            }

            cell.html(text);
        });

        return element;
    }

    // Escape special regex characters
    function escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    // Initialize search functionality
    storeOriginalData();

    // Search input event handlers
    $('#violationNameSearch').on('input', function() {
        performSearch();
    });
    $('#categorySelect').on('change', function() {
        performSearch();
    });

    $('#severitySearch').on('change', function() {
        performSearch();
    });

    // Clear all search button
    $('#clearAllSearch').click(function() {
        $('#violationNameSearch').val('');
        $('#categorySelect').val('');
        $('#severitySearch').val('');
        performSearch();
    });

    // Enter key support for text inputs
    $('#violationNameSearch').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            performSearch();
        }
    });

    // --- CATEGORY TABLE SORTING ---
    // Sorting state per table
    let sortStates = {};

    // Click handler for sortable headers
    $('.category-violation-table .sortable').on('click', function() {
        const $header = $(this);
        const $table = $header.closest('table');
        const category = $table.data('category');
        const sortKey = $header.data('sort');
        const $icon = $header.find('.sort-icon');

        // Toggle sort direction
        if (!sortStates[category]) sortStates[category] = {};
        if (sortStates[category].key === sortKey) {
            sortStates[category].asc = !sortStates[category].asc;
        } else {
            sortStates[category].key = sortKey;
            sortStates[category].asc = true;
        }

        // Remove active color from all icons in this table
        $table.find('.sort-icon').css('color', '');
        // Highlight the active sort icon
        $icon.css('color', '#3498db');

        // Get rows and sort
        const $rows = $table.find('tbody tr').get();
        $rows.sort(function(a, b) {
            let valA, valB;
            if (sortKey === 'violation_name') {
                valA = $(a).find('.violation-name').text().toLowerCase();
                valB = $(b).find('.violation-name').text().toLowerCase();
            } else if (sortKey === 'severity') {
                valA = $(a).find('.violation-severity').text().toLowerCase();
                valB = $(b).find('.violation-severity').text().toLowerCase();
            }
            if (valA < valB) return sortStates[category].asc ? -1 : 1;
            if (valA > valB) return sortStates[category].asc ? 1 : -1;
            return 0;
        });
        // Append sorted rows
        $.each($rows, function(idx, row) {
            $table.find('tbody').append(row);
        });
    });
});
</script>
@endsection






